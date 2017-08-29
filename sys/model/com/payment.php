<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//引用特殊的recordset class 文件
require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(isset($_GET['pi_no'])){
    $_SESSION['search_criteria']['search_pi_no'] = $_GET['pi_no'];
}

//提示状态变化的信息
$add_info = '';

if( !isset($_POST['p_sign']) || $_POST['p_sign'] != 1){
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        //20141222 普通用户不能增删改查
        if(isSysAdmin()){
            $rtn = $mysql->qone('select pi_no, p_status from payment where id = ?', $_GET['delid']);
            if($rtn['p_status'] == 'Balance'){
                $result = $mysql->qone('select istatus from proforma where pvid = ?', $rtn['pi_no']);
                if($result['istatus'] == '(C)'){
                    $add_info = $rtn['pi_no'].' status change from ( C ) to ( S ) !';
                    $mysql->q('update proforma set istatus = ? where pvid = ?', '(S)', $rtn['pi_no']);
                    $mysql->q('update invoice set istatus = ? where vid like ?', '(S)', '%'.$rtn['pi_no'].'%');
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$rtn['pi_no']." (C) TO (S)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                }elseif($result['istatus'] == '(P)'){
                    $add_info = $rtn['pi_no'].' status change from ( P ) to ( I ) !';
                    $mysql->q('update proforma set istatus = ? where pvid = ?', '(I)', $rtn['pi_no']);
                    $mysql->q('update invoice set istatus = ? where vid like ?', '(I)', '%'.$rtn['pi_no'].'%');
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$rtn['pi_no']." (P) TO (I)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                }
            }

            $rs = $mysql->q('delete from payment WHERE id = ?', $_GET['delid']);
            if($rs){
                $myerror->ok('Success! '.$add_info, 'com-payment&page=1');
            }else{
                $myerror->error('Failure! (0)', 'com-payment&page=1');
            }
        }else{
            $myerror->error('Without Permission To Access', 'main');
        }
    }

}

if( isset($_GET['modid']) && $_GET['modid'] != ''){

    $mod_result = $mysql->qone('select * from payment where id = ?', $_GET['modid']);

    $rtn_payment_advice = $mysql->qone('select value_date from payment_new where py_no = ?', $mod_result['py_no']);

}

$goodsForm = new My_Forms();
$formItems = array(

    'pi_no' => array('title' => 'Proforma Invoice NO.', 'type' => 'select', 'options' => get_proforma_no(), 'required' => 1, 'value' => isset($mod_result['pi_no'])?$mod_result['pi_no']:''),
    'amount' => array('title' => 'Amount(USD)', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1, 'value' => isset($mod_result['amount'])?$mod_result['amount']:''),
    'p_date' => array('title' => 'Last Modify Date', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['p_date'])?date('Y-m-d', strtotime($mod_result['p_date'])):''),
    //20130816 payment advice 里没有method，所以这里改为不强制填
    'method' => array('title' => 'Method', 'type' => 'select', 'options' => $method, /*'required' => 1,*/ 'value' => isset($mod_result['method'])?$mod_result['method']:''),
    //20130815 在payment advice里面有了，这里就不要写了
    //'bank_ref' => array('title' => 'Bank Ref', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'required' => 1, 'value' => isset($mod_result['bank_ref'])?$mod_result['bank_ref']:''),
    'p_status' => array('title' => 'Deposit/Balance', 'type' => 'select', 'options' => $payment, 'required' => 1, 'value' => isset($mod_result['p_status'])?$mod_result['p_status']:''),
    'bank_charge' => array('title' => 'Bank charge (HKD)', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1, 'value' => isset($mod_result['bank_charge'])?$mod_result['bank_charge']:''),
    'remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
    //20130808 加py_no
    'py_no' => array('title' => 'Payment Advice #', 'type' => 'select', 'options' => get_py_no(), 'value' => isset($mod_result['py_no'])?$mod_result['py_no']:''),

    //20150106 加value_date
    'value_date' => array('title' => 'Value Date', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($rtn_payment_advice['value_date'])?date('Y-m-d', strtotime($rtn_payment_advice['value_date'])):''),

    //隱藏提交標識，用於分辨是goodsForm表的提交還是form表的提交
    'p_sign' => array('type' => 'hidden', 'value' => 1),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);


$form = new My_Forms();
$formItems = array(

    'search_pi_no' => array(
        'type' => 'select',
        'options' => get_proforma_no(),
        'value' => @$_SESSION['search_criteria']['search_pi_no'],
    ),
    'search_bank_ref' => array(
        'type' => 'text',
        'value' => @$_SESSION['search_criteria']['search_bank_ref'],
    ),
    'search_start_date' => array(
        'type' => 'text',
        'restrict' => 'date',
        'value' => @$_SESSION['search_criteria']['search_start_date'],
    ),
    'search_end_date' => array(
        'type' => 'text',
        'restrict' => 'date',
        'value' => @$_SESSION['search_criteria']['search_end_date'],
    ),
    'search_p_status' => array(
        'type' => 'select',
        'options' => $payment,
        'value' => @$_SESSION['search_criteria']['search_p_status'],
    ),
    'search_method' => array(
        'type' => 'select',
        'options' => $method,
        'value' => @$_SESSION['search_criteria']['search_method'],
    ),
    'search_remark' => array(
        'type' => 'text',
        'value' => @$_SESSION['search_criteria']['search_remark'],
    ),
    /*
'search_p_status' => array(
    'type' => 'hidden',
    'value' => 1
    ),
    */
    'submitbutton' => array(
        'type' => 'submit',
        'value' => 'Search',
    )
);
$form->init($formItems);

if(isset($_POST['p_sign']) && $_POST['p_sign'] == 1){
    if(!$myerror->getAny() && $goodsForm->check()){
        //20141222 普通用户不能增删改查
        if(isSysAdmin()){
            $pi_no = $_POST['pi_no'];
            $amount = $_POST['amount'];
            $p_date = $_POST['p_date'].' '.date('H:i:s');
            $method = $_POST['method'];
            //$bank_ref = $_POST['bank_ref'];
            $p_status = $_POST['p_status'];
            $remark = $_POST['remark'];
            $bank_charge = $_POST['bank_charge'];
            //20130808 加py_no
            $py_no = $_POST['py_no'];
            //20150106
            $value_date = $_POST['value_date'];

            if( isset($_GET['modid']) && $_GET['modid'] != ''){
                //状态没有被修改，不会对PI和invoice的status产生影响
                if($mod_result['p_status'] == $p_status){
                    $result = $mysql->q('update payment set pi_no = ?, amount = ?, p_date = ?, method = ?, p_status = ?, remark = ?, bank_charge = ?, py_no = ?, value_date = ? where id = ?', $pi_no, $amount, $p_date, $method, $p_status, $remark, $bank_charge, $py_no, $value_date, $_GET['modid']);

                    //20150120
                    $mysql->q('update payment_new set value_date = ? where py_no = ?', $value_date, $py_no);

                    if($result){
                        $myerror->ok('Success!', 'com-payment&page=1');
                    }else{
                        $myerror->error('Failure! (1)', 'com-payment&page=1');
                    }
                }
                //状态被修改了
                elseif($mod_result['p_status'] != $p_status){
                    //状态由 Deposit 修改为 Balance
                    if($p_status == 'Balance'){
                        //如果同一个$pi_no已经存在Balance就不能在插入Balance项了
                        if(!$mysql->qone('select id from payment where pi_no = ? where p_status = ?', $pi_no, 'Balance')){
                            $result = $mysql->q('update payment set pi_no = ?, amount = ?, p_date = ?, method = ?, p_status = ?, remark = ?, bank_charge = ?, py_no = ?, value_date = ? where id = ?', $pi_no, $amount, $p_date, $method, $p_status, $remark, $bank_charge, $py_no, $value_date, $_GET['modid']);

                            //20150120
                            $mysql->q('update payment_new set value_date = ? where py_no = ?', $value_date, $py_no);

                            if($result){
                                $rtn = $mysql->qone('select istatus from proforma where pvid = ?', $pi_no);
                                if($rtn['istatus'] == '(I)'){
                                    $add_info = '----' . $pi_no . ' status change from ( I ) to ( P ) !';
                                    $mysql->q('update proforma set istatus = ? where pvid = ?', '(P)', $pi_no);
                                    $mysql->q('update invoice set istatus = ? where vid like ?', '(P)', '%'.$pi_no.'%');
                                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (I) TO (P)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                                }elseif($rtn['istatus'] == '(S)'){
                                    $add_info = '----' . $pi_no . ' status change from ( S ) to ( C ) !';
                                    $mysql->q('update proforma set istatus = ? where pvid = ?', '(C)', $pi_no);
                                    $mysql->q('update invoice set istatus = ? where vid like ?', '(C)', '%'.$pi_no.'%');
                                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (S) TO (C)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                                }//因为前面判断没有Balance，所以不会有(C)
                                $myerror->ok('Success! '.$add_info, 'com-payment&page=1');
                            }else{
                                $myerror->error('Failure! (2)', 'com-payment&page=1');
                            }
                        }else{
                            $myerror->warn('此PI已Balance，不允许再修改为Balance项', 'com-payment&page=1');
                        }
                    }
                    //状态由 Balance 修改为 Deposit
                    elseif($p_status == 'Deposit'){
                        $result = $mysql->q('update payment set pi_no = ?, amount = ?, p_date = ?, method = ?, p_status = ?, remark = ?, bank_charge = ?, py_no = ?, value_date = ? where id = ?', $pi_no, $amount, $p_date, $method, $p_status, $remark, $bank_charge, $py_no, $value_date, $_GET['modid']);

                        //20150120
                        $mysql->q('update payment_new set value_date = ? where py_no = ?', $value_date, $py_no);

                        if($result){
                            $rtn = $mysql->qone('select istatus from proforma where pvid = ?', $pi_no);
                            if($rtn['istatus'] == '(C)'){
                                $add_info = '----' . $pi_no . ' status change from ( C ) to ( S ) !';
                                $mysql->q('update proforma set istatus = ? where pvid = ?', '(S)', $pi_no);
                                $mysql->q('update invoice set istatus = ? where vid like ?', '(S)', '%'.$pi_no.'%');
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (C) TO (S)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                            }elseif($rtn['istatus'] == '(P)'){
                                $add_info = '----' . $pi_no . ' status change from ( P ) to ( I ) !';
                                $mysql->q('update proforma set istatus = ? where pvid = ?', '(I)', $pi_no);
                                $mysql->q('update invoice set istatus = ? where vid like ?', '(I)', '%'.$pi_no.'%');
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (P) TO (I)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                            }//因为是由Balance转为Deposit，所以不会有(I)
                            $myerror->ok('Success! '.$add_info, 'com-payment&page=1');
                        }else{
                            $myerror->error('Failure! (3)', 'com-payment&page=1');
                        }
                    }
                }
            }else{
                //插入状态为 Balance
                if($p_status == 'Balance'){
                    //如果同一个$pi_no已经存在Balance就不能在插入Balance项了
                    if(!$mysql->qone('select id from payment where pi_no = ? and p_status = ?', $pi_no, 'Balance')){
                        $result = $mysql->q('insert into payment (pi_no, amount, p_date, method, p_status, remark, bank_charge, py_no, value_date) values ('.moreQm(9).')', $pi_no, $amount, $p_date, $method, $p_status, $remark, $bank_charge, $py_no, $value_date);

                        //20150120
                        $mysql->q('update payment_new set value_date = ? where py_no = ?', $value_date, $py_no);

                        if($result){
                            $rtn = $mysql->qone('select istatus from proforma where pvid = ?', $pi_no);
                            if($rtn['istatus'] == '(I)'){
                                $add_info = '----' . $pi_no . ' status change from ( I ) to ( P ) !';
                                $mysql->q('update proforma set istatus = ? where pvid = ?', '(P)', $pi_no);
                                $mysql->q('update invoice set istatus = ? where vid like ?', '(P)', '%'.$pi_no.'%');
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (I) TO (P)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                            }elseif($rtn['istatus'] == '(S)'){
                                $add_info = '----' . $pi_no . ' status change from ( S ) to ( C ) !';
                                $mysql->q('update proforma set istatus = ? where pvid = ?', '(C)', $pi_no);
                                $mysql->q('update invoice set istatus = ? where vid like ?', '(C)', '%'.$pi_no.'%');
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (S) TO (C)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                            }elseif($rtn['istatus'] == ''){//为空是因为5月之前还没有加status变化的内容，所以5月之前的单status字段是空的
                                $add_info = '----' . $pi_no . ' status change to ( P ) !';
                                $mysql->q('update proforma set istatus = ? where pvid = ?', '(P)', $pi_no);
                                $mysql->q('update invoice set istatus = ? where vid like ?', '(P)', '%'.$pi_no.'%');
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (P)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                            }//因为前面判断没有Balance，所以不会有(C)
                            $myerror->ok('Success! '.$add_info, 'com-payment&page=1');
                        }else{
                            $myerror->error('Failure! (4)', 'com-payment&page=1');
                        }
                    }else{
                        $myerror->warn('此PI已Balance，不允许再插入Balance项', 'com-payment&page=1');
                    }
                }
                //如果插入状态为 Deposit  一般不用改状态，但是原来status为空则要加上(I)
                elseif($p_status == 'Deposit'){
                    $result = $mysql->q('insert into payment (pi_no, amount, p_date, method, p_status, remark, bank_charge, py_no, value_date) values ('.moreQm(9).')', $pi_no, $amount, $p_date, $method, $p_status, $remark, $bank_charge, $py_no, $value_date);

                    //20150120
                    $mysql->q('update payment_new set value_date = ? where py_no = ?', $value_date, $py_no);

                    if($result){
                        $rtn = $mysql->qone('select istatus from proforma where pvid = ?', $pi_no);
                        if($rtn['istatus'] == ''){
                            //状态为空则改为(I)且不提示修改的信息
                            $mysql->q('update proforma set istatus = ? where pvid = ?', '(I)', $pi_no);
                            $mysql->q('update invoice set istatus = ? where vid like ?', '(I)', '%'.$pi_no.'%');
                            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (I)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                        }
                        $myerror->ok('Success!', 'com-payment&page=1');
                    }else{
                        $myerror->error('Failure! (5)', 'com-payment&page=1');
                    }
                }
            }
        }else{
            $myerror->error('Without Permission To Access', 'main');
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
    <h1 class="green">Payment Record<em>* item must be filled in</em></h1>

    <?php
    $form->begin();
    ?>

    <table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right">Proforma Invoice NO. : </td>
                            <td align="left"><? $form->show('search_pi_no'); //$form->show('search_p_status'); ?></td>
                            <td align="right">Bank Ref : </td>
                            <td align="left"><? $form->show('search_bank_ref'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Start Date : </td>
                            <td align="left"><? $form->show('search_start_date'); ?></td>
                            <td align="right">End Date : </td>
                            <td align="left"><? $form->show('search_end_date'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Deposit/Balance : </td>
                            <td align="left"><? $form->show('search_p_status'); ?></td>
                            <td align="right">Method : </td>
                            <td align="left"><? $form->show('search_method'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Remark : </td>
                            <td align="left"><? $form->show('search_remark'); ?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr>
                            <td width="100%" colspan='4'>
                                <?
                                $form->show('submitbutton');
                                ?></td>
                        </tr>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>
<?
//if(isset($_POST['search_p_status']) && $_POST['search_p_status'] == 1){
    // 如果有post资料则给Session，并且清除附在上次翻页时残留的$_GET['page']
    if (count($_POST)){
        $_SESSION['search_criteria'] = $_POST;
        $_GET['page'] = 1;
    }

    //如果有合法的提交，则 getAnyPost = true。
    //如果不是翻页而是普通的GET，则清除之前的Session，以显示一个空白的表单
    $getAnyPost = false;

    if($form->check()){
        $getAnyPost = true;
    }elseif(!isset($_GET['page'])){
        unset($_SESSION['search_criteria']);
    }

    if($myerror->getAny()){
        require_once(ROOT_DIR.'model/inside_warn.php');
    }

    if ($getAnyPost || isset($_GET['page'])){

        $rs = new RecordSetControl2;
        $rs->record_per_page = ADMIN_ROW_PER_PAGE;
        $rs->addnew_link = "?act=com-payment";
        $rs->display_new_button = false;
        $rs->sort_field = "pi_no";
        $rs->sort_seq = "DESC";

        $current_page = 1;
        $start_row = 0;
        $end_row = $rs->record_per_page;
        if (set($_GET['page'])){
            $current_page = intval($_GET['page']);
            $start_row = (($current_page-1) * $rs->record_per_page);
        }

        $where_sql = " AND p.py_no = pn.py_no";

        //!!!! 20150228 如果这里用模糊查询，下面加上非admin的where语句后，就查不出内容了
        if (strlen(@$_SESSION['search_criteria']['search_pi_no'])){
            $where_sql.= " AND p.pi_no = '".$_SESSION['search_criteria']['search_pi_no']."'";
        }
        if (strlen(@$_SESSION['search_criteria']['search_bank_ref'])){
            $where_sql.= " AND p.bank_ref Like '%".$_SESSION['search_criteria']['search_bank_ref'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['search_start_date'])){
            if (strlen(@$_SESSION['search_criteria']['search_end_date'])){
                $where_sql.= " AND p.p_date between '".$_SESSION['search_criteria']['search_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND p.p_date > '".$_SESSION['search_criteria']['search_start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['search_end_date'])){
            $where_sql.= " AND p.p_date < '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
        }
        if (strlen(@$_SESSION['search_criteria']['search_p_status'])){
            $where_sql.= " AND p.p_status Like '%".$_SESSION['search_criteria']['search_p_status'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['search_method'])){
            $where_sql.= " AND p.method Like '%".$_SESSION['search_criteria']['search_method'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['search_remark'])){
            $where_sql.= " AND p.remark Like '%".$_SESSION['search_criteria']['search_remark'].'%\'';
        }

        //普通用户只能搜索到自己开的单
        if (!isSysAdmin()){
            $where_sql .= " AND p.pi_no in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName']."')))";
        }
        // echo $where_sql;

        $where_sql.= ' ORDER BY p.id DESC ';
        $_SESSION['search_criteria']['page'] = $current_page;

        //20150105 payment表里加value_date
        //20150120 改为显示payment_new表的value_date
        $temp_table = ' payment p, payment_new pn ';
        //$temp_table = ' payment p ';
        $list_field = ' SQL_CALC_FOUND_ROWS p.*, pn.value_date ';
        //$list_field = ' SQL_CALC_FOUND_ROWS p.* ';

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";
        $rs->SetRecordCol("Payment Advice Value Date", "value_date");
        $rs->SetRecordCol("Deposit/Balance", "p_status");
        $rs->SetRecordCol("PI#", "pi_no");
        $rs->SetRecordCol("Amount(USD)", "amount");
        $rs->SetRecordCol("Method", "method");
        $rs->SetRecordCol("Payment Advice #", "py_no");
        $rs->SetRecordCol("Bank Ref", "bank_ref");
        $rs->SetRecordCol("Bank charge (HKD)", "bank_charge");
        $rs->SetRecordCol("Remark", "remark");

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;
        $rs->SetRecordCol("MODIFY", "id", $sort, $edit,"?act=com-payment","modid");
        $rs->SetRecordCol("DEL", "id", $sort, $edit,"?act=com-payment","delid");
        $rs->SetRSSorting('?act=com-payment');

        $rs->ShowRecordSet($info);

    }
//}

    $form->end();
    ?>



    <br />
    <br />

<?
//20130828 旧的payment不让手动添加和修改了，都由payment advice自动生成
//20130904 这个功能又加回来了，用了验证状态是否正确
    ?>
    <fieldset class="center2col" style="width:80%;">
        <legend class='legend'><?=isset($_GET['modid'])?'Modify':'Add' ?> ( 非Admin用户不能在此新增或修改内容 )</legend>
        <?
        $goodsForm->begin();
        ?>
        <table width="100%" id="table">
            <tr>
                <td width="25%"><? $goodsForm->show('p_status');?></td>
                <td width="25%"><? $goodsForm->show('pi_no');?></td>
                <td width="25%"><? $goodsForm->show('p_date');?></td>
                <td width="25%"><? $goodsForm->show('amount');?></td>
            </tr>
            <tr>
                <td width="25%"><? $goodsForm->show('method');?></td>
                <td width="25%"><? $goodsForm->show('bank_charge');//$goodsForm->show('bank_ref');?></td>
                <td width="25%"><? $goodsForm->show('remark');?></td>
                <td width="25%"><? $goodsForm->show('py_no');$goodsForm->show('p_sign');?></td>
            </tr>
            <tr>
                <td width="25%"><? $goodsForm->show('value_date');?></td>
            </tr>
        </table>
        <div class="line"></div>
        <?
        $goodsForm->show('submitbtn');
        ?>

    </fieldset>
    <?
    $goodsForm->end();

}
?>