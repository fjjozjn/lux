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
        if ($rs) {
            $mod_result = $mysql->fetch();
            $item_num = count($mod_result);
        }
        if ($mod_result['status'] == 2) {

        } elseif ($mod_result['status'] == 1) {

        } else {
            die('error status!');
        }
    } else {
        die('Need modid!');
    }

    $goodsForm = new My_Forms();
    $formItems = array(
        'fpr_actual_pay_amount' => array('title' => '实际付款金额', 'type' => 'text', 'restrict' => 'number'),
        'submitbtn' => array('type' => 'submit', 'value' => ' 保存 '),
    );

    for ($i = 0; $i < $item_num; $i++) {
        $formItems['fpr_type' . $i] = array('type' => 'select', 'options' => get_fty_wlgy_jg_type(), 'addon' => 'onchange="searchFtyCustomer(this)"', 'value' => isset($mod_result[$i]['type']) ? $mod_result[$i]['type'] : '', 'disabled' => 'disabled');
        $formItems['fpr_fty_customer' . $i] = array('type' => 'select', 'options' => array(array($mod_result[$i]['fty_customer'], $mod_result[$i]['fty_customer'])), 'addon' => 'onchange="searchFtyCustomerDetail(this)"', 'value' => isset($mod_result[$i]['fty_customer']) ? $mod_result[$i]['fty_customer'] : '', 'disabled' => 'disabled');
        $formItems['fpr_fty_customer_ap' . $i] = array('type' => 'text', 'restrict' => 'number', 'value' => isset($mod_result[$i]['fty_customer_ap']) ? $mod_result[$i]['fty_customer_ap'] : '', 'disabled' => 'disabled');
        $formItems['fpr_pay_amount' . $i] = array('type' => 'text', 'restrict' => 'number', 'value' => isset($mod_result[$i]['pay_amount']) ? $mod_result[$i]['pay_amount'] : '', 'disabled' => 'disabled');
    }

    $goodsForm->init($formItems);

    if (!$myerror->getAny() && $goodsForm->check()) {
        //var_dump($_POST);die('@');

        $today = dateMore();
        $staff = $_SESSION['ftylogininfo']['aName'];

        //fb($payment_request_arr);die('#');

        $result = $mysql->q('update fty_payment_request set actual_pay_amount = ?, status = ?, approved_by = ?, approved_date = ? where id = ?', $_POST['fpr_actual_pay_amount'], 1, $staff, $today, $_GET['approveId']);
        if ($result) {
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
                </tr>
                <?
                for ($i = 0; $i < $item_num; $i++) {
                    ?>
                    <tr class="repeat" valign="top">
                        <td><? $goodsForm->show('fpr_type' . $i);?></td>
                        <td><? $goodsForm->show('fpr_fty_customer' . $i);?></td>
                        <td><? $goodsForm->show('fpr_fty_customer_ap' . $i);?></td>
                        <td><? $goodsForm->show('fpr_pay_amount' . $i);?></td>
                    </tr>
                <?
                }
                ?>
                </tbody>
            </table>
            <div class="line"></div>
            <? $goodsForm->show('fpr_actual_pay_amount');?>
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