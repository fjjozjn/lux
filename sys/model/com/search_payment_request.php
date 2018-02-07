<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{

    //已用新DB，引用特殊的recordset class 文件
    require_once(ROOT_DIR.'sys/in38/recordset.class2.php');
    //$luxmysql = new My_Mysql($luxDbInfo);

    if (isset($_GET['is_approve'])) {
        $_SESSION['search_criteria']['is_approve'] = $_GET['is_approve'];
    }

// 如果有post资料则给Session，并且清除附在上次翻页时残留的$_GET['page']
    if (count($_POST)){
        $_SESSION['search_criteria'] = $_POST;
        $_GET['page'] = 1;
    }

//get staff group information
// $mysql->sp('CALL backend_detail(?, ?, ?)', '1', 'tw_admingrp', '1');
// $temp_grp = $mysql->fetch(0,1);	
// for($i = 0 ; $i < count($temp_grp); $i++){
    // $temp = array($temp_grp[$i]['AdminGrpName'],$temp_grp[$i]['AdminGrpID']);
    // $row_grp[] = $temp;
// }
// print_r_pre($temp_grp);
    $form = new My_Forms();
    $formItems = array(
        'sid' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['sid']
        ),
        'description' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['description']
        ),
        'start_date' => array(
            'type' => 'text',
            'restrict' => 'date',
            'value' => @$_SESSION['search_criteria']['start_date'],
        ),
        'end_date' => array(
            'type' => 'text',
            'restrict' => 'date',
            'value' => @$_SESSION['search_criteria']['end_date'],
        ),
        'created_by' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['created_by'],
        ),
        'is_approve' => array(
            'type' => 'select',
            'options' => get_payment_request_approve_status(),
            'value' => @$_SESSION['search_criteria']['is_approve'],
        ),
        'submitbutton' => array(
            'type' => 'submit',
            'value' => 'Search',
        ),
    );
    $form->init($formItems);
    $form->begin();


// resetJSForm('text', 'admin_name');
// print_r_pre($gameList);
// print_r_pre($_GET);
// print_r_pre($GLOBALS);
    ?>
    <h1 class="green">PAYMENT REQUEST<em>* indicates required fields</em></h1>

    <table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right">Supplier : </td>
                            <td align="left"><? $form->show('sid'); ?></td>
                            <td align="right">Description : </td>
                            <td align="left"><? $form->show('description'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Start Date : </td>
                            <td align="left"><? $form->show('start_date'); ?></td>
                            <td align="right">End Date : </td>
                            <td align="left"><? $form->show('end_date'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Created by : </td>
                            <td align="left"><? $form->show('created_by'); ?></td>
                            <td align="right"></td>
                            <td align="left"></td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr>
                            <td width="100%" colspan='4'><? $form->show('submitbutton'); ?></td>
                        </tr>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>

    <?
    $form->end();

    //如果有合法的提交，则 getAnyPost = true。
    //如果不是翻页而是普通的GET，则清除之前的Session，以显示一个空白的表单
    $getAnyPost = false;
    if ($form->check()){
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
        $rs->addnew_link = "?act=com-search_payment_request";
        $rs->display_new_button = false;
        $rs->sort_field = "id";
        $rs->sort_seq = "DESC";

        $current_page = 1;
        $start_row = 0;
        $end_row = $rs->record_per_page;
        if (set($_GET['page'])){
            $current_page = intval($_GET['page']);
            $start_row = (($current_page-1) * $rs->record_per_page);
        }

        $where_sql = "";

        if (strlen(@$_SESSION['search_criteria']['sid'])){
            $where_sql.= " AND sid Like '%".$_SESSION['search_criteria']['sid'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['description'])){
            $where_sql.= " AND description Like '%".$_SESSION['search_criteria']['description'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['created_by'])){
            $where_sql.= " AND created_by Like '%".$_SESSION['search_criteria']['created_by'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['start_date'])){
            if (strlen(@$_SESSION['search_criteria']['end_date'])){
                $where_sql.= " AND created_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND created_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
            $where_sql.= " AND created_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
        }
        // echo $where_sql;

        $where_sql.= ' AND is_approve = 1 ORDER BY id DESC';
        $_SESSION['search_criteria']['page'] = $current_page;

        $temp_table = ' payment_request';
        $list_field = ' SQL_CALC_FOUND_ROWS * ';

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";
        $rs->SetRecordCol("Supplier Name", "supplier");
        $rs->SetRecordCol("Description", "description");
        $rs->SetRecordCol("Order NO.", "pcid");
        $rs->SetRecordCol("Currency", "currency");
        $rs->SetRecordCol("Amount", "amount");
        $rs->SetRecordCol("Remark", "remark");
        $rs->SetRecordCol("Bank Details", "bank_details");
        $rs->SetRecordCol("Last Modify by", "mod_by");
        $rs->SetRecordCol("Last Update", "mod_date");

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;

        $rs->SetRecordCol("Approved by", "approved_by");
        $rs->SetRecordCol("Pay", "id", $sort, $edit,"?act=com-modify_payment_request", "payid");
        $rs->SetRecordCol("Paid by", "paid_by");
        $rs->SetRecordCol("Paid Date", "paid_date");
        $rs->SetRecordCol("MODIFY", "id", $sort, $edit, "?act=com-modify_payment_request", "modid");
        $rs->SetRecordCol("DEL", "id", $sort, $edit, "?act=com-modify_payment_request", "delid");
        $rs->SetRSSorting('?act=com-search_payment_request');

        /*
        $cur_page = 0;
        if (isset($_POST["page"])){
        $cur_page = $_POST["page"] - 1;
        }
        */

        $rs->ShowRecordSet($info);
    }
}