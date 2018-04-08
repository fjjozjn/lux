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
        if ($mod_result['status'] == 1 || $mod_result['status'] == 3) {
            $rs = $mysql->q('SELECT * FROM fty_payment_request_item WHERE main_id = ?', $_GET['paydoneId']);
            $item_num = 0;
            $mod_result_item = array();
            if ($rs) {
                $mod_result_item = $mysql->fetch();
                $item_num = count($mod_result_item);
            }
            $type = transArrayFormat(get_fty_wlgy_jg_type());
        } else {
            $myerror->error('付款申请单还未核批不能进行付款操作!', 'search_payment_request&page=1');
        }
    } else {
        die('Need modid!');
    }

    $goodsForm = new My_Forms();
    $formItems = array(
        'submitbtn' => array('type' => 'submit', 'value' => ' 付款 '),
    );

    for ($i = 0; $i < $item_num; $i++) {
        $formItems['fpr_paydone' . $i] = array('type' => 'checkbox', 'options' => array(array('付款', '1')), 'value' => isset($mod_result_item[$i]['is_paydone']) ? $mod_result_item[$i]['is_paydone'] : '');
    }

    $goodsForm->init($formItems);

    if (!$myerror->getAny() && $goodsForm->check()) {
        //fb($_POST);die('@');

        $today = dateMore();
        $staff = $_SESSION['ftylogininfo']['aName'];

        $i = 0;
        $paydone_num = 0;
        foreach ($mod_result_item as $update_item) {
            $now = dateMore();
            $temp = explode(':', $update_item['fty_customer']);
            if ($update_item['is_paydone'] == 1) {
                if (isset($_POST['fpr_paydone' . $i])) {
                    $paydone_num++;
                } else {
                    $mysql->q('update fty_payment_request_item set is_paydone = 2, paydone_date = ? where id = ?', '', $update_item['id']);
                    handleFtyCustomerAp($update_item['type'], $temp[0], 1, $update_item['actual_pay_amount']);
                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['ftylogininfo']['aID'], $ip_real
                        , ACTION_LOG_FTY_CUSTOMER_AP_CHANGE_STATUS, '操作人：'.$_SESSION["ftylogininfo"]["aName"]."，取消付款日期：".$now."，取消付款金额：".$update_item['actual_pay_amount'], ACTION_LOG_FTY_CUSTOMER_AP_CHANGE_STATUS_PLUS, "fty_payment_request", $temp[0], 0);
                }
            } else {
                if (isset($_POST['fpr_paydone' . $i])) {
                    $mysql->q('update fty_payment_request_item set is_paydone = 1, paydone_date = ? where id = ?', $now, $update_item['id']);
                    $paydone_num++;
                    handleFtyCustomerAp($update_item['type'], $temp[0], 2, $update_item['actual_pay_amount']);
                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['ftylogininfo']['aID'], $ip_real
                        , ACTION_LOG_FTY_CUSTOMER_AP_CHANGE_STATUS, '操作人：'.$_SESSION["ftylogininfo"]["aName"]."，付款日期：".$now."，付款金额：".$update_item['actual_pay_amount'], ACTION_LOG_FTY_CUSTOMER_AP_CHANGE_STATUS_MINUS, "fty_payment_request", $temp[0], 0);
                }
            }
            $i++;
        }
        if ($paydone_num == $item_num) {
            $mysql->q('update fty_payment_request set status = 3, paydone_by = ?, last_paydone_date = ? where id = ?', $staff, $today, $_GET['paydoneId']);
        } else {
            $mysql->q('update fty_payment_request set status = 1, paydone_by = ?, last_paydone_date = ? where id = ?', $staff, $today, $_GET['paydoneId']);
        }

        $myerror->ok('付款申请单 付款成功!', 'search_payment_request&page=1');
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
                    <td>申请付款金额</td>
                    <td>备注</td>
                    <td>实际付款金额</td>
                    <td>银行账号</td>
                    <td>付款</td>
                    <td>付款日期</td>
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
                        <td><?=$mod_result_item[$i]['bank_no']?></td>
                        <td><? $goodsForm->show('fpr_paydone' . $i); ?></td>
                        <td><?=$mod_result_item[$i]['paydone_date']?></td>
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