<?php
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//获取
$setting_rtn = $mysql->qone('select email_admin_request_to from setting');

if(isset($_GET['delid']) && $_GET['delid'] != ''){
    $rs = $mysql->q('delete from payment_request where id = ?', $_GET['delid']);
    if($rs){

        //send email to admin
        require_once(ROOT_DIR.'class/Mail/mail.php');
        //邮件正文
        $info = 'Delete Payment Request id:'.$_GET['delid'].' by '.$_SESSION["logininfo"]["aName"].' '.dateMore();
        $rs = send_mail($setting_rtn['email_admin_request_to'], '', $_SESSION["logininfo"]["aName"].' Delete Payment Request', $info, array('date' => date('Y-m-d')));

        $myerror->ok('Delete Payment Request Success !', 'com-search_payment_request&page=1');
    }else{
        $myerror->error('Delete Payment Request Failure', 'com-search_payment_request&page=1');
    }
}elseif(isset($_GET['approveid']) && isId($_GET['approveid'])){
    if(isSysAdmin()){
        $rtn = $mysql->qone('select * from payment_request where id = ?', $_GET['approveid']);
        $staff = $mysql->qone('select AdminEmail from tw_admin where AdminName = ?', $rtn['created_by']);
        if($rtn['is_approve'] == 0){
            $rs = $mysql->q('update payment_request set is_approve = 1, approved_by = ? where id = ?', $_SESSION['logininfo']['aNameChi'], $_GET['approveid']);
            if($rs){
                //send email to admin
                require_once(ROOT_DIR.'class/Mail/mail.php');
                //邮件正文
                $info = 'Approve Payment Request id:'.$_GET['approveid'].' by '.$_SESSION["logininfo"]["aName"].' '.dateMore().'<br>';
                $info .= 'SUPPLIER : '.$rtn['supplier'].'<br>';
                $info .= 'DESCRIPTION : '.$rtn['description'].'<br>';
                $info .= 'PO NO. (optional) : '.$rtn['currency'].'<br>';
                $info .= 'CURRENCY : '.$rtn['pcid'].'<br>';
                $info .= 'AMOUNT : '.$rtn['amount'].'<br>';
                $info .= 'REMARK : '.$rtn['remark'].'<br>';
                $info .= 'BANK DETAILS : '.$rtn['bank_details'].'<br>';

                $msg = 'Success change status to approved (Email Already sent to: '.$setting_rtn['email_admin_request_to'].' ; '.$staff['AdminEmail'].' )';

                send_mail($setting_rtn['email_admin_request_to'], '', $_SESSION["logininfo"]["aName"].' Approve Payment Request '.$_GET['approveid'], $info, array('date' => date('Y-m-d')));
                send_mail($staff['AdminEmail'], '', $_SESSION["logininfo"]["aName"].' Approve Payment Request '.$_GET['approveid'], $info, array('date' => date('Y-m-d')));

                //add tw_admin_hist log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', $_SESSION['logininfo']['aID'], $ip_real, ACTION_LOG_SYS_PAYMENT_REQUEST_APPROVE, $_SESSION["logininfo"]["aName"].' '.$msg, ACTION_LOG_SYS_PAYMENT_REQUEST_APPROVE_S, "", "", 0);

                $myerror->ok($msg, 'com-search_payment_request&page=1');
            }else{
                $msg = 'Failed change status to approved';
                //add tw_admin_hist log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', $_SESSION['logininfo']['aID'], $ip_real, ACTION_LOG_SYS_PAYMENT_REQUEST_APPROVE, $_SESSION["logininfo"]["aName"].' '.$msg, ACTION_LOG_SYS_PAYMENT_REQUEST_APPROVE_F, "", "", 0);

                $myerror->error($msg, 'com-search_payment_request&page=1');
            }
        }elseif($rtn['is_approve'] == 1){
            $rs = $mysql->q('update payment_request set is_approve = 0, approved_by = ? where id = ?', $_SESSION["logininfo"]["aName"].' disapproved', $_GET['approveid']);
            if($rs){
                //send email to admin
                require_once(ROOT_DIR.'class/Mail/mail.php');
                //邮件正文
                $info = 'Disapprove Payment Request id:'.$_GET['approveid'].' by '.$_SESSION["logininfo"]["aName"].' '.dateMore().'<br>';
                $info .= 'SUPPLIER : '.$rtn['supplier'].'<br>';
                $info .= 'DESCRIPTION : '.$rtn['description'].'<br>';
                $info .= 'PO NO. (optional) : '.$rtn['currency'].'<br>';
                $info .= 'CURRENCY : '.$rtn['pcid'].'<br>';
                $info .= 'AMOUNT : '.$rtn['amount'].'<br>';
                $info .= 'REMARK : '.$rtn['remark'].'<br>';
                $info .= 'BANK DETAILS : '.$rtn['bank_details'].'<br>';

                $msg = 'Success change status to pending (Email Already sent to: '.$setting_rtn['email_admin_request_to'].' ; '.$staff['AdminEmail'].' )';

                send_mail($setting_rtn['email_admin_request_to'], '', $_SESSION["logininfo"]["aName"].' Disapprove Payment Request '.$_GET['approveid'], $msg, array('date' => date('Y-m-d')));
                send_mail($staff['AdminEmail'], '', $_SESSION["logininfo"]["aName"].' Disapprove Payment Request '.$_GET['approveid'], $msg, array('date' => date('Y-m-d')));

                //add tw_admin_hist log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', $_SESSION['logininfo']['aID'], $ip_real, ACTION_LOG_SYS_PAYMENT_REQUEST_DISAPPROVE, $_SESSION["logininfo"]["aName"].' '.$info, ACTION_LOG_SYS_PAYMENT_REQUEST_DISAPPROVE_S, "", "", 0);

                $myerror->ok($msg, 'com-search_payment_request&page=1');
            }else{
                $msg = 'Failed change status to pending';
                //add tw_admin_hist log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', $_SESSION['logininfo']['aID'], $ip_real, ACTION_LOG_SYS_PAYMENT_REQUEST_DISAPPROVE, $_SESSION["logininfo"]["aName"].' '.$msg, ACTION_LOG_SYS_PAYMENT_REQUEST_DISAPPROVE_F, "", "", 0);

                $myerror->error($msg, 'com-search_payment_request&page=1');
            }
        }else{
            $myerror->error('Failure !', 'com-search_payment_request&page=1');
        }
    }
}elseif(isset($_GET['payid']) && isId($_GET['payid'])){
    $rs = $mysql->q('update payment_request set is_approve = 2, paid_by = ?, paid_date = ? where id = ?', $_SESSION['logininfo']['aNameChi'], dateMore(), $_GET['payid']);
    if($rs){
        $myerror->ok('Success !', 'com-search_payment_request&page=1');
    }else{
        $myerror->error('Failure !', 'com-search_payment_request&page=1');
    }
}else{
    if(isset($_GET['modid']) && $_GET['modid'] != ''){
        $mod_result = $mysql->qone('SELECT * FROM payment_request WHERE id = ?', $_GET['modid']);
        $payment_request_approve_status = transArrayFormat(get_payment_request_approve_status());
    }

    $goodsForm = new My_Forms();
    $formItems = array(
        'supplier' => array('title' => 'Supplier Name', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1,
            'value' => isset($mod_result['supplier'])?$mod_result['supplier']:''),
        'description' => array('title' => 'Description', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'addon' => 'style="width:200px"', 'value' => isset($mod_result['description'])?$mod_result['description']:'', 'required' => 1),
        'pcid' => array('title' => 'PO NO. (optional)', 'type' => 'select', 'options' => get_pcid(),
            'value' => isset($mod_result['pcid'])?$mod_result['pcid']:''),
        'currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(),
            'required' => 1, 'value' => isset($mod_result['currency'])?$mod_result['currency']:''),
        'amount' => array('title' => 'Amount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1, 'value' => isset($mod_result['amount'])?$mod_result['amount']:''),
        'remark' => array('title' => 'Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 500,
            'addon' => 'style="width:200px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
        'bank_details' => array('title' => 'Bank Details', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 500,
            'addon' => 'style="width:200px"', 'value' => isset($mod_result['bank_details'])?$mod_result['bank_details']:''),
        'created_by' => array('title' => 'Created By', 'type' => 'text', 'value' => isset($mod_result['created_by'])?$mod_result['created_by']:'', 'disabled' => 'disabled'),
        'mod_by' => array('title' => 'Last Modify By', 'type' => 'text', 'value' => isset($mod_result['mod_by'])?$mod_result['mod_by']:'', 'disabled' => 'disabled'),
        'created_date' => array('title' => 'Creation Date', 'type' => 'text', 'value' => isset($mod_result['created_date'])?$mod_result['created_date']:'', 'disabled' => 'disabled'),
        'mod_date' => array('title' => 'Last Update', 'type' => 'text', 'value' => isset($mod_result['mod_date'])?$mod_result['mod_date']:'', 'disabled' => 'disabled'),
        'is_approve' => array('title' => 'Status', 'type' => 'text', 'value' => isset($mod_result['is_approve'])?$payment_request_approve_status[$mod_result['is_approve']]:'', 'disabled' => 'disabled'),
        'approved_by' => array('title' => 'Approved By', 'type' => 'text', 'value' => isset($mod_result['approved_by'])?$mod_result['approved_by']:'', 'disabled' => 'disabled'),
        'paid_by' => array('title' => 'Paid By', 'type' => 'text', 'value' => isset($mod_result['paid_by'])?$mod_result['paid_by']:'', 'disabled' => 'disabled'),
        'paid_date' => array('title' => 'Paid Date', 'type' => 'text', 'value' => isset($mod_result['paid_date'])?$mod_result['paid_date']:'', 'disabled' => 'disabled'),

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

        $rs = $mysql->q('update payment_request set supplier = ?, description = ?, pcid = ?, currency = ?, amount = ?, remark = ?, bank_details = ?, mod_by = ?, mod_date = ? where id = ?', $supplier, $description, $pcid, $currency, $amount, $remark, $bank_details, $_SESSION["logininfo"]["aName"], $time, $_GET['modid']);
        if($rs){

            //send email to admin
            require_once(ROOT_DIR.'class/Mail/mail.php');
            //邮件正文
            $info = 'Modify Payment Request id:'.$_GET['modid'].' by '.$_SESSION["logininfo"]["aName"].' '.$time.'<br>';
            $info .= 'SUPPLIER : '.$supplier.'<br>';
            $info .= 'DESCRIPTION : '.$description.'<br>';
            $info .= 'PO NO. (optional) : '.$pcid.'<br>';
            $info .= 'CURRENCY : '.$pcid.'<br>';
            $info .= 'AMOUNT : '.$amount.'<br>';
            $info .= 'REMARK : '.$remark.'<br>';
            $info .= 'BANK DETAILS : '.$bank_details.'<br>';
            $rs = send_mail($setting_rtn['email_admin_request_to'], '', $_SESSION["logininfo"]["aName"].' Modify Payment Request', $info, array('date' => date('Y-m-d')));

            $myerror->ok('Modify Payment Request Success!', 'com-search_payment_request&page=1');
        }else{
            $myerror->error('Modify Payment Request Failure!', 'BACK');
        }
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
        <legend class='legend'>Action</legend>
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
        <table width="100%" id="table">
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('created_by');?></td>
                <td width="25%"><? $goodsForm->show('mod_by');?></td>
                <td width="25%"><? $goodsForm->show('created_date');?></td>
                <td width="25%"><? $goodsForm->show('mod_date');?></td>
            </tr>
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('is_approve');?></td>
                <td width="25%"><? $goodsForm->show('approved_by');?></td>
                <td width="25%"><? $goodsForm->show('paid_by');?></td>
                <td width="25%"><? $goodsForm->show('paid_date');?></td>
            </tr>
        </table>
        <div class="line"></div>
        <? $goodsForm->show('submitbtn'); ?>
    </fieldset>
    <?
    $goodsForm->end();
}