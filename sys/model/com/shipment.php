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

if( !isset($_POST['s_sign']) || $_POST['s_sign'] != 1){
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        //20150121 普通用户不能增删改查
        if(isSysAdmin()){
            $rtn = $mysql->qone('select pi_no, s_status from shipment where id = ?', $_GET['delid']);
            if($rtn['s_status'] == 'Complete'){
                $result = $mysql->qone('select istatus from proforma where pvid = ?', $rtn['pi_no']);
                if($result['istatus'] == '(C)'){
                    $add_info = '----' . $rtn['pi_no'] . ' status change from ( C ) to ( P ) !';
                    $mysql->q('update proforma set istatus = ? where pvid = ?', '(P)', $rtn['pi_no']);
                    $mysql->q('update invoice set istatus = ? where vid like ?', '(P)', '%'.$rtn['pi_no'].'%');
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$rtn['pi_no']." (C) TO (P)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                }elseif($result['istatus'] == '(S)'){
                    $add_info = '----' . $rtn['pi_no'] . ' status change from ( S ) to ( I ) !';
                    $mysql->q('update proforma set istatus = ? where pvid = ?', '(I)', $rtn['pi_no']);
                    $mysql->q('update invoice set istatus = ? where vid like ?', '(I)', '%'.$rtn['pi_no'].'%');
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$rtn['pi_no']." (S) TO (I)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                }
            }

            $rs = $mysql->q('delete from shipment WHERE id = ?', $_GET['delid']);
            if($rs){
                $myerror->ok('Success!'.$add_info, 'com-shipment&page=1');
            }else{
                $myerror->error('Failure!', 'com-shipment&page=1');
            }
        }else{
            $myerror->error('Without Permission To Access', 'main');
        }
    }
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
    $mod_result = $mysql->qone('select * from shipment where id = ?', $_GET['modid']);
}

$goodsForm = new My_Forms();
$formItems = array(

    'pi_no' => array('title' => 'Proforma Invoice NO.', 'type' => 'select', 'options' => get_proforma_no(), 'required' => 1, 'value' => isset($mod_result['pi_no'])?$mod_result['pi_no']:''),
    'awb' => array('title' => 'Waybill #', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'required' => 1, 'value' => isset($mod_result['awb'])?$mod_result['awb']:''),
    's_date' => array('title' => 'DATE', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['s_date'])?date('Y-m-d', strtotime($mod_result['s_date'])):''),
    'cost' => array('title' => 'Delivery Cost / FOB Charge(HKD)', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['cost'])?$mod_result['cost']:''),
    'cost_remark' => array('title' => 'Cost Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($mod_result['cost_remark'])?$mod_result['cost_remark']:''),
    's_status' => array('title' => 'Partial/Complete', 'type' => 'select', 'options' => $shipment, 'required' => 1, 'value' => isset($mod_result['s_status'])?$mod_result['s_status']:''),
    'courier_or_forwarder' => array('title' => 'Courier / forwarder', 'type' => 'select', 'options' => get_courier_or_forwarder(), 'value' => isset($mod_result['courier_or_forwarder'])?$mod_result['courier_or_forwarder']:'', 'required' => 1),

    //隱藏提交標識，用於分辨是goodsForm表的提交還是form表的提交
    's_sign' => array('type' => 'hidden', 'value' => 1),

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
    'search_awb' => array(
        'type' => 'text',
        'value' => @$_SESSION['search_criteria']['search_awb'],
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
    'search_s_status' => array(
        'type' => 'select',
        'options' => $shipment,
        'value' => @$_SESSION['search_criteria']['search_s_status'],
    ),
    /*
'search_s_sign' => array(
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

if(isset($_POST['s_sign']) && $_POST['s_sign'] == 1){
    if(!$myerror->getAny() && $goodsForm->check()){
        //20150121 普通用户不能增删改查
        if(isSysAdmin()){
            $pi_no = $_POST['pi_no'];
            $awb = $_POST['awb'];
            $s_date = $_POST['s_date'].' '.date('H:i:s');
            $cost = $_POST['cost'];
            $cost_remark = $_POST['cost_remark'];
            $s_status = $_POST['s_status'];
            $courier_or_forwarder = $_POST['courier_or_forwarder'];

            if( isset($_GET['modid']) && $_GET['modid'] != ''){
                //状态没有被修改，不会对PI和invoice的status产生影响
                if($mod_result['s_status'] == $s_status){
                    $result = $mysql->q('update shipment set pi_no = ?, awb = ?, s_date = ?, cost = ?, cost_remark = ?, s_status = ?, courier_or_forwarder = ? where id = ?', $pi_no, $awb, $s_date, $cost, $cost_remark, $s_status, $courier_or_forwarder, $_GET['modid']);
                    if($result){
                        $myerror->ok('Success!', 'com-shipment&page=1');
                    }else{
                        $myerror->error('Failure!', 'com-shipment&page=1');
                    }
                }
                //状态被修改了
                elseif($mod_result['s_status'] != $s_status){
                    //状态由 Partial 修改为 Complete
                    if($s_status == 'Complete'){
                        //如果同一个$pi_no已经存在complete就不能在插入complete项了
                        if(!$mysql->qone('select id from shipment where pi_no = ? where s_status = ?', $pi_no, 'Complete')){
                            $result = $mysql->q('update shipment set pi_no = ?, awb = ?, s_date = ?, cost = ?, cost_remark = ?, s_status = ? where id = ?', $pi_no, $awb, $s_date, $cost, $cost_remark, $s_status, $_GET['modid']);
                            if($result){
                                $rtn = $mysql->qone('select istatus from proforma where pvid = ?', $pi_no);
                                if($rtn['istatus'] == '(I)'){
                                    $add_info = '----' . $pi_no . ' status change from ( I ) to ( S ) !';
                                    $mysql->q('update proforma set istatus = ? where pvid = ?', '(S)', $pi_no);
                                    $mysql->q('update invoice set istatus = ? where vid like ?', '(S)', '%'.$pi_no.'%');
                                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (I) TO (S)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                                }elseif($rtn['istatus'] == '(P)'){
                                    $add_info = '----' . $pi_no . ' status change from ( P ) to ( C ) !';
                                    $mysql->q('update proforma set istatus = ? where pvid = ?', '(C)', $pi_no);
                                    $mysql->q('update invoice set istatus = ? where vid like ?', '(C)', '%'.$pi_no.'%');
                                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (P) TO (C)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                                }//因为前面判断没有Complete，所以不会有(C)
                                $myerror->ok('Success!'.$add_info, 'com-shipment&page=1');
                            }else{
                                $myerror->error('Failure!', 'com-shipment&page=1');
                            }
                        }else{
                            $myerror->warn('此PI已Complete，不允许再修改为Complete项', 'com-shipment&page=1');
                        }
                    }
                    //状态由 Complete 修改为 Partial
                    elseif($s_status == 'Partial'){
                        $result = $mysql->q('update shipment set pi_no = ?, awb = ?, s_date = ?, cost = ?, cost_remark = ?, s_status = ? where id = ?', $pi_no, $awb, $s_date, $cost, $cost_remark, $s_status, $_GET['modid']);
                        if($result){
                            $rtn = $mysql->qone('select istatus from proforma where pvid = ?', $pi_no);
                            if($rtn['istatus'] == '(C)'){
                                $add_info = '----' . $pi_no . ' status change from ( C ) to ( P ) !';
                                $mysql->q('update proforma set istatus = ? where pvid = ?', '(P)', $pi_no);
                                $mysql->q('update invoice set istatus = ? where vid like ?', '(P)', '%'.$pi_no.'%');
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (C) TO (P)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                            }elseif($rtn['istatus'] == '(S)'){
                                $add_info = '----' . $pi_no . ' status change from ( S ) to ( I ) !';
                                $mysql->q('update proforma set istatus = ? where pvid = ?', '(I)', $pi_no);
                                $mysql->q('update invoice set istatus = ? where vid like ?', '(I)', '%'.$pi_no.'%');
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (S) TO (I)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                            }//因为是由Complete转为Partial，所以不会有(I)
                            $myerror->ok('Success!'.$add_info, 'com-shipment&page=1');
                        }else{
                            $myerror->error('Failure!', 'com-shipment&page=1');
                        }
                    }
                }
            }else{
                //插入状态为 Complete
                if($s_status == 'Complete'){
                    //如果同一个$pi_no已经存在complete就不能在插入complete项了
                    if(!$mysql->qone('select id from shipment where pi_no = ? and s_status = ?', $pi_no, 'Complete')){
                        $result = $mysql->q('insert into shipment (pi_no, awb, s_date, cost, cost_remark, s_status, courier_or_forwarder) values ('.moreQm(7).')', $pi_no, $awb, $s_date, $cost, $cost_remark, $s_status, $courier_or_forwarder);
                        if($result){
                            $rtn = $mysql->qone('select istatus from proforma where pvid = ?', $pi_no);
                            if($rtn['istatus'] == '(I)'){
                                $add_info = '----' . $pi_no . ' status change from ( I ) to ( S ) !';
                                $mysql->q('update proforma set istatus = ? where pvid = ?', '(S)', $pi_no);
                                $mysql->q('update invoice set istatus = ? where vid like ?', '(S)', '%'.$pi_no.'%');
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (I) TO (S)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                            }elseif($rtn['istatus'] == '(P)'){
                                $add_info = '----' . $pi_no . ' status change from ( P ) to ( C ) !';
                                $mysql->q('update proforma set istatus = ? where pvid = ?', '(C)', $pi_no);
                                $mysql->q('update invoice set istatus = ? where vid like ?', '(C)', '%'.$pi_no.'%');
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (P) TO (C)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                            }elseif($rtn['istatus'] == ''){//为空是因为5月之前还没有加status变化的内容，所以5月之前的单status字段是空的
                                $add_info = '----' . $pi_no . ' status change to ( S ) !';
                                $mysql->q('update proforma set istatus = ? where pvid = ?', '(S)', $pi_no);
                                $mysql->q('update invoice set istatus = ? where vid like ?', '(S)', '%'.$pi_no.'%');
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (S)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                            }//因为前面判断没有Complete，所以不会有(C)
                            $myerror->ok('Success!'.$add_info, 'com-shipment&page=1');
                        }else{
                            $myerror->error('Failure!', 'com-shipment&page=1');
                        }
                    }else{
                        $myerror->warn('此PI已Complete，不允许再插入Complete项', 'com-shipment&page=1');
                    }
                }
                //如果插入状态为 Partial 一般不用改状态，但是原来status为空则要加上(I)
                elseif($s_status == 'Partial'){
                    $result = $mysql->q('insert into shipment (pi_no, awb, s_date, cost, cost_remark, s_status) values ('.moreQm(6).')', $pi_no, $awb, $s_date, $cost, $cost_remark, $s_status);
                    if($result){
                        $rtn = $mysql->qone('select istatus from proforma where pvid = ?', $pi_no);
                        if($rtn['istatus'] == ''){
                            //状态为空则改为(I)且不提示修改的信息
                            $mysql->q('update proforma set istatus = ? where pvid = ?', '(I)', $pi_no);
                            $mysql->q('update invoice set istatus = ? where vid like ?', '(I)', '%'.$pi_no.'%');
                            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, ADMIN_CHANGE_STATUS, $_SESSION['logininfo']['aName']." ".$pi_no." (I)", ADMIN_CHANGE_STATUS_SUCCESS, "", "", 0);
                        }
                        $myerror->ok('Success!', 'com-shipment&page=1');
                    }else{
                        $myerror->error('Failure!', 'com-shipment&page=1');
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
    <h1 class="green">Shipment Record<em>* item must be filled in</em></h1>
    <?
    $form->begin();
    ?>

    <table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                        <tr>
                            <td>Proforma Invoice NO. : </td>
                            <td><?
                                $form->show('search_pi_no');
                                //$form->show('search_s_sign');
                                ?></td>

                            <td>Waybill # : </td>
                            <td><?
                                $form->show('search_awb');
                                ?></td>
                        </tr>
                        <tr>
                            <td>Start Date : </td>
                            <td><?
                                $form->show('search_start_date');
                                ?></td>

                            <td>End Date : </td>
                            <td><?
                                $form->show('search_end_date');
                                ?></td>
                        </tr>
                        <tr>
                            <td>Partial/Complete : </td>
                            <td><?
                                $form->show('search_s_status');
                                ?></td>
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
//if(isset($_POST['search_s_sign']) && $_POST['search_s_sign'] == 1){
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
        $rs->addnew_link = "?act=com-shipment";
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

        $where_sql = "";

        //!!!! 20150228 如果这里用模糊查询，下面加上非admin的where语句后，就查不出内容了
        if (strlen(@$_SESSION['search_criteria']['search_pi_no'])){
            $where_sql.= " AND pi_no = '".$_SESSION['search_criteria']['search_pi_no']."'";
        }
        if (strlen(@$_SESSION['search_criteria']['search_awb'])){
            $where_sql.= " AND awb Like '%".$_SESSION['search_criteria']['search_awb'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['search_start_date'])){
            if (strlen(@$_SESSION['search_criteria']['search_end_date'])){
                $where_sql.= " AND s_date between '".$_SESSION['search_criteria']['search_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND s_date > '".$_SESSION['search_criteria']['search_start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['search_end_date'])){
            $where_sql.= " AND s_date < '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
        }
        if (strlen(@$_SESSION['search_criteria']['search_s_status'])){
            $where_sql.= " AND s_status Like '%".$_SESSION['search_criteria']['search_s_status'].'%\'';
        }

        //普通用户只能搜索到自己开的单
        if (!isSysAdmin()){
            $where_sql .= " AND pi_no in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName']."')))";
        }
        // echo $where_sql;

        $where_sql.= ' ORDER BY id DESC ';
        $_SESSION['search_criteria']['page'] = $current_page;

        $temp_table = ' shipment';
        $list_field = ' SQL_CALC_FOUND_ROWS * ';

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";
        $rs->SetRecordCol("Partial/Complete", "s_status");
        $rs->SetRecordCol("PI#", "pi_no");
        $rs->SetRecordCol("DATE", "s_date");
        $rs->SetRecordCol("Waybill #", "awb");
        $rs->SetRecordCol("Delivery Cost / FOB Charge(HKD)", "cost");
        $rs->SetRecordCol("Cost Remark", "cost_remark");
        $rs->SetRecordCol("Courier / forwarder", "courier_or_forwarder");


        $sort = GENERAL_NO;
        $edit = GENERAL_YES;
        $rs->SetRecordCol("MODIFY", "id", $sort, $edit,"?act=com-shipment","modid");
        $rs->SetRecordCol("DEL", "id", $sort, $edit,"?act=com-shipment","delid");
        $rs->SetRSSorting('?act=com-shipment');

        $rs->ShowRecordSet($info);

    }
//}

    $form->end();
    ?>

    <br />
    <br />
    <fieldset class="center2col" style="width:80%">
        <legend class='legend'><?=isset($_GET['modid'])?'Modify':'Add' ?> ( 非Admin用户不能在此新增或修改内容 )</legend>

        <?php
        $goodsForm->begin();
        ?>
        <table width="100%" id="table">
            <tr>
                <td width="25%"><? $goodsForm->show('s_status');?></td>
                <td width="25%"><? $goodsForm->show('pi_no');?></td>
                <td width="25%"><? $goodsForm->show('courier_or_forwarder');?></td>
                <td width="25%"><? $goodsForm->show('awb');?></td>
            </tr>
            <tr>
                <td width="25%"><? $goodsForm->show('cost');?></td>
                <td width="25%"><? $goodsForm->show('cost_remark');?></td>
                <td width="25%"><? $goodsForm->show('s_date');?></td>
                <td width="25%"><? $goodsForm->show('s_sign');?></td>
            </tr>
        </table>
        <div class="line"></div>
        <?
        $goodsForm->show('submitbtn');
        ?>

    </fieldset>
    <br />
    <?
    $goodsForm->end();
}
?>