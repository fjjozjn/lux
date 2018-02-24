<?php
/**
 * Author: zhangjn
 * Date: 2017/12/25
 * Time: 13:49
 */
if (!defined('BEEN_INCLUDE') || !is_object($myerror)) exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//禁止其他用户进入（临时做法）
//201306131746 去除限制
/*if(!isSysAdmin()){
	$myerror->error('Without Permission To Access', 'main');
}*/


if ($myerror->getWarn()) {
    require_once(ROOT_DIR . 'model/inside_warn.php');
} else {
    if (isset($_GET['approveId']) && $_GET['approveId'] != '') {
        $mod_result = $mysql->qone('SELECT * FROM fty_payment_request WHERE id = ?', $_GET['approveId']);
        $rs = $mysql->q('SELECT * FROM fty_payment_request_item WHERE main_id = ?', $_GET['approveId']);
        $item_num = 0;
        $mod_result_item = array();
        if ($rs) {
            $mod_result_item = $mysql->fetch();
            $item_num = count($mod_result_item);
        }
        if ($mod_result['status'] == 2) {
            //页面还要继续，还要填ap
        } elseif ($mod_result['status'] == 1) {
            $rs1 = $mysql->q('update fty_payment_request set status = ?, approved_by = ? where id = ?', 2, $_SESSION['ftylogininfo']['aName'].' disapproved', $_GET['approveId']);
            $rs2 = $mysql->q('update fty_payment_request_item set actual_pay_amount = 0 where main_id = ?', $_GET['approveId']);
            if ($rs1 && $rs2) {
                //操作回滚ap
                foreach ($mod_result_item as $item) {
                    $temp = explode(':', $item['fty_customer']);
                    handleFtyCustomerAp($item['type'], $temp[0], 1, $item['actual_pay_amount']);
                }
                $myerror->ok('付款单取消批核 成功!', 'search_payment_request&page=1');
            } else {
                $myerror->error('付款单取消批核 失败!', 'search_payment_request&page=1');
            }
        } else {
            die('error status!');
        }
    } else {
        die('Need modid!');
    }

    $goodsForm = new My_Forms();
    $formItems = array(
        'submitbtn' => array('type' => 'submit', 'value' => ' 批核 '),
    );

    for ($i = 0; $i < $item_num; $i++) {
        $formItems['fpr_id' . $i] = array('type' => 'hidden', 'value' => isset($mod_result_item[$i]['id']) ? $mod_result_item[$i]['id'] : '');
        $formItems['fpr_type' . $i] = array('type' => 'select', 'options' => get_fty_wlgy_jg_type(), 'value' => isset($mod_result_item[$i]['type']) ? $mod_result_item[$i]['type'] : '', 'disabled' => 'disabled');
        $formItems['fpr_fty_customer' . $i] = array('type' => 'select', 'options' => array(array($mod_result_item[$i]['fty_customer'], $mod_result_item[$i]['fty_customer'])), 'value' => isset($mod_result_item[$i]['fty_customer']) ? $mod_result_item[$i]['fty_customer'] : '', 'disabled' => 'disabled');
        $formItems['fpr_fty_customer_ap' . $i] = array('type' => 'text', 'restrict' => 'number', 'value' => isset($mod_result_item[$i]['fty_customer_ap']) ? $mod_result_item[$i]['fty_customer_ap'] : '', 'disabled' => 'disabled');
        $formItems['fpr_pay_amount' . $i] = array('type' => 'text', 'restrict' => 'number', 'value' => isset($mod_result_item[$i]['pay_amount']) ? $mod_result_item[$i]['pay_amount'] : '', 'disabled' => 'disabled');
        $formItems['fpr_remark' . $i] = array('type' => 'text', 'value' => isset($mod_result_item[$i]['remark']) ? $mod_result_item[$i]['remark'] : '', 'disabled' => 'disabled');
        $formItems['fpr_actual_pay_amount' . $i] = array('type' => 'text', 'restrict' => 'number');
    }

    $goodsForm->init($formItems);

    if (!$myerror->getAny() && $goodsForm->check()) {
        //var_dump($_POST);die('@');

        $today = dateMore();
        $staff = $_SESSION['ftylogininfo']['aName'];

        $i = 0;
        $prev_num = 1;//第一个post的是form的标识串，还有0个表单项，所以是1
        $last_num = 1;//后面的post，有个submit
        $item = array();
        foreach ($_POST as $v) {
            if ($i < $prev_num) {
                $i++;
            } elseif ($i >= count($_POST) - $last_num) {
                break;
            } else {
                $item[] = $v;
                $i++;
            }
        }

        //这个是设置每个ITEM的元素个数
        $each_item_num = 2;
        $item_num = intval(count($item) / $each_item_num);

        $payment_request_arr = array();
        $index = 0;
        for ($j = 0; $j < $item_num; $j++) {
            $payment_request_arr[$item[$index++]] = $item[$index++];
        }
        //fb($payment_request_arr);die('#');

        $result = $mysql->q('update fty_payment_request set status = ?, approved_by = ?, approved_date = ? where id = ?', 1, $staff, $today, $_GET['approveId']);

        if ($result) {
            $email_content = '<table><tr><td>类别</td><td>供应商</td><td>应付</td><td>付款金额</td><td>备注</td><td>实际付款金额</td></tr>';
            $type = transArrayFormat(get_fty_wlgy_jg_type());
            //操作扣除ap
            foreach ($mod_result_item as $item) {
                $temp = explode(':', $item['fty_customer']);
                $mysql->q('update fty_payment_request_item set actual_pay_amount = ? where id = ?', $payment_request_arr[$item['id']], $item['id']);
                handleFtyCustomerAp($item['type'], $temp[0], 2, $payment_request_arr[$item['id']]);
                $email_content .= '<tr><td>'.$type[$item['type']].'</td><td>'.$item['fty_customer'].'</td><td>'.$item['fty_customer_ap'].'</td><td>'.$item['pay_amount'].'</td><td>'.$item['remark'].'</td><td>'.$item['actual_pay_amount'].'</td></tr>';
            }
            $email_content .= '</table>';

            //发邮件
            require_once(ROOT_DIR.'class/Mail/mail.php');
            $rtn = $mysql->qone('select email_fty_user_info_to, email_admin_request_to from setting');
            $account_info = array('date' => date('Y-m-d'));
            //邮件的信息
            $info1 = "你好,<br />付款申请单已核批，内容如下<br />'.$email_content.'<br />详情请登入系统查看.<br />(此郵件為系統訊息, 請勿回覆)<br />Best Regards,<br />Lux Design Limited";
            $info2 = "你好,<br />付款申请单已核批，内容如下<br />'.$email_content.'<br />详情请登入系统查看.<br />(此郵件為系統訊息, 請勿回覆)<br />Best Regards,<br />Lux Design Limited";
            $info3 = $_SESSION['ftylogininfo']['aName']." 你好,<br />付款申请单已核批，内容如下<br />'.$email_content.'<br />详情请登入系统查看.<br />(此郵件為系統訊息, 請勿回覆)<br />Best Regards,<br />Lux Design Limited";

            send_mail(trim($rtn['email_fty_user_info_to']), '', "付款申请单 - ".$_GET['approveId'], $info1, $account_info);
            send_mail(trim($rtn['email_admin_request_to']), '', "付款申请单 - ".$_GET['approveId'], $info2, $account_info);
            send_mail(trim($_SESSION['ftylogininfo']['aAdminEmail']), '', "付款申请单 - ".$_GET['approveId'], $info3, $account_info);

            $myerror->ok('批核 付款申请单 成功!', 'search_payment_request&page=1');
        } else {
            $myerror->error('批核 付款申请单 失败', 'BACK');
        }
    }
}

if ($myerror->getError()) {
    require_once(ROOT_DIR . 'model/inside_error.php');
} elseif ($myerror->getOk()) {
    require_once(ROOT_DIR . 'model/inside_ok.php');
} else {
    if ($myerror->getWarn()) {
        require_once(ROOT_DIR . 'model/inside_warn.php');
    }
    ?>
    <h1 class="green">付款单<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>修改</legend>
        <?php
        $goodsForm->begin();
        ?>
        <div style="margin-left:28px;">
            <table width="100%" id="tableDnD_wl">
                <tbody id="tbody">
                <tr class="formtitle nodrop nodrag">
                    <td>类别</td>
                    <td>供应商</td>
                    <td>应付</td>
                    <td>付款金额</td>
                    <td>备注</td>
                    <td>实际付款金额</td>
                </tr>
                <?
                for ($i = 0; $i < $item_num; $i++) {
                    ?>
                    <tr class="repeat" valign="top">
                        <td><? $goodsForm->show('fpr_id' . $i); ?><? $goodsForm->show('fpr_type' . $i); ?></td>
                        <td><? $goodsForm->show('fpr_fty_customer' . $i); ?></td>
                        <td><? $goodsForm->show('fpr_fty_customer_ap' . $i); ?></td>
                        <td><? $goodsForm->show('fpr_pay_amount' . $i); ?></td>
                        <td><? $goodsForm->show('fpr_remark' . $i); ?></td>
                        <td><? $goodsForm->show('fpr_actual_pay_amount' . $i); ?></td>
                    </tr>
                    <?
                }
                ?>
                </tbody>
            </table>
            <div class="line"></div>
        </div>
        <?
        $goodsForm->show('submitbtn');
        ?>
    </fieldset>
    <?
    $goodsForm->end();
}
?>