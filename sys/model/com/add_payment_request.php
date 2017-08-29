<?php
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

$goodsForm = new My_Forms();
$formItems = array(
    'supplier' => array('title' => 'Supplier Name', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1),
    'description' => array('title' => 'Description', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'addon' => 'style="width:200px"', 'required' => 1),
    'pcid' => array('title' => 'PO NO. (optional)', 'type' => 'select', 'options' => get_pcid()),
    'currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1),
    'amount' => array('title' => 'Amount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1),
    'remark' => array('title' => 'Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 500,
        'addon' => 'style="width:200px"'),
    'bank_details' => array('title' => 'Bank Details', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 500,
        'addon' => 'style="width:200px"'),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);
$goodsForm->begin();


if(!$myerror->getAny() && $goodsForm->check()){
    $supplier = $_POST['supplier'];
    $description = $_POST['description'];
    $pcid = $_POST['pcid'];
    $currency = $_POST['currency'];
    $amount = $_POST['amount'];
    $remark = $_POST['remark'];
    $bank_details = $_POST['bank_details'];
    $time = dateMore();

    $rs = $mysql->q('insert into payment_request (supplier, description, pcid, currency, amount, remark, bank_details, created_by, mod_by, created_date, mod_date) values ('.moreQm(11).')', $supplier, $description, $pcid, $currency, $amount, $remark, $bank_details, $_SESSION["logininfo"]["aName"], $_SESSION["logininfo"]["aName"], $time, $time);
    if($rs){

        $staff = $mysql->qone('select AdminEmail from tw_admin where AdminName = ?', $_SESSION["logininfo"]["aName"]);

        //send email to admin
        require_once(ROOT_DIR.'class/Mail/mail.php');
        //邮件正文
        $info = 'Add Payment Request id:'.$rs.' by '.$_SESSION["logininfo"]["aName"].' '.$time.'<br>';
        $info .= 'SUPPLIER : '.$supplier.'<br>';
        $info .= 'DESCRIPTION : '.$description.'<br>';
        $info .= 'PO NO. (optional) : '.$currency.'<br>';
        $info .= 'CURRENCY : '.$pcid.'<br>';
        $info .= 'AMOUNT : '.$amount.'<br>';
        $info .= 'REMARK : '.$remark.'<br>';
        $info .= 'BANK DETAILS : '.$bank_details.'<br>';
        $setting_rtn = $mysql->qone('select email_admin_request_to from setting');
        $rs = send_mail($setting_rtn['email_admin_request_to'], '', $_SESSION["logininfo"]["aName"].' Add Payment Request', $info, array('date' => date('Y-m-d')));

        $myerror->ok('Add Payment Request Success!', 'com-search_payment_request&page=1');
    }else{
        $myerror->error('Add Payment Request Failure!', 'BACK');
    }
}

if($myerror->getError()){
    require_once(ROOT_DIR.'model/inside_error.php');
}elseif($myerror->getOk()){
    require_once(ROOT_DIR.'model/inside_ok.php');
}else{
    if($myerror->getWarn()){
        require_once(ROOT_DIR.'model/inside_warn.php');
    }
?>
    <h1 class="green">PURCHASE REQUEST<em>* indicates required fields</em></h1>
    <fieldset class="center2col">
        <legend class='legend'>Add Payment Request</legend>
        <table width="100%" id="table">
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('supplier');?></td>
                <td width="25%"><? $goodsForm->show('description');?></td>
                <td width="25%"><? $goodsForm->show('pcid');?></td>
                <td width="25%"><? $goodsForm->show('currency');?></td>
            </tr>
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('amount');?></td>
                <td width="25%"><? $goodsForm->show('remark');?></td>
                <td width="25%"><? $goodsForm->show('bank_details');?></td>
                <td width="25%"></td>
            </tr>
        </table>
        <div class="line"></div>
        <? $goodsForm->show('submitbtn'); ?>
    </fieldset>
<?
    $goodsForm->end();
}