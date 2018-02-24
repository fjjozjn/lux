<?php
/**
 * Author: zhangjn
 * Date: 2018/2/24
 * Time: 16:30
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
    if (isset($_GET['paydoneId']) && $_GET['paydoneId'] != '') {
        $mod_result = $mysql->qone('SELECT * FROM fty_payment_request WHERE id = ?', $_GET['paydoneId']);
        $rs = $mysql->q('SELECT * FROM fty_payment_request_item WHERE main_id = ?', $_GET['paydoneId']);
        $item_num = 0;
        $mod_result_item = array();
        if ($rs) {
            $mod_result_item = $mysql->fetch();
            $item_num = count($mod_result_item);
        }
        $type = transArrayFormat(get_fty_wlgy_jg_type());
    } else {
        die('Need modid!');
    }

    $goodsForm = new My_Forms();
    $formItems = array(
        'submitbtn' => array('type' => 'submit', 'value' => ' 付款 '),
    );

    for ($i = 0; $i < $item_num; $i++) {
        $formItems['fpr_paydone' . $i] = array('type' => 'checkbox', 'options' => array(''), 'value' => isset($mod_result_item[$i]['is_paydone']) ? $mod_result_item[$i]['is_paydone'] : '');
    }

    $goodsForm->init($formItems);

    if (!$myerror->getAny() && $goodsForm->check()) {
        fb($_POST);die('@');

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

        $result = $mysql->q('update fty_payment_request set status = ?, approved_by = ?, approved_date = ? where id = ?', 1, $staff, $today, $_GET['paydoneId']);

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

            send_mail(trim($rtn['email_fty_user_info_to']), '', "付款申请单 - ".$_GET['paydoneId'], $info1, $account_info);
            send_mail(trim($rtn['email_admin_request_to']), '', "付款申请单 - ".$_GET['paydoneId'], $info2, $account_info);
            send_mail(trim($_SESSION['ftylogininfo']['aAdminEmail']), '', "付款申请单 - ".$_GET['paydoneId'], $info3, $account_info);

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
        <legend class='legend'>付款状态修改</legend>
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
                    <td>付款</td>
                </tr>
                <?
                for ($i = 0; $i < $item_num; $i++) {
                    ?>
                    <tr class="repeat" valign="top">
                        <td><?=$type[$mod_result_item[$i]['type']]?></td>
                        <td><?=$mod_result_item[$i]['fty_customer']?></td>
                        <td><?=$mod_result_item[$i]['fty_customer_ap']?></td>
                        <td><?=$mod_result_item[$i]['pay_amount']?></td>
                        <td><?=$mod_result_item[$i]['remark']?></td>
                        <td><?=$mod_result_item[$i]['actual_pay_amount']?></td>
                        <td><? $goodsForm->show('fpr_paydone' . $i); ?></td>
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