<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    //引用特殊的recordset class 文件
    require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

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
        'py_no' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['py_no'],
        ),
/*        'pvid' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['vid'],
        ),*/
        'istatus' => array(
            'type' => 'select',
            'options' => get_payment_advice_status(),
            'value' => @$_SESSION['search_criteria']['istatus'],
        ),
        'value_date_start' => array(
            'type' => 'text',
            'restrict' => 'date',
            'value' => @$_SESSION['search_criteria']['value_date_start'],
        ),
        'value_date_end' => array(
            'type' => 'text',
            'restrict' => 'date',
            'value' => @$_SESSION['search_criteria']['value_date_end'],
        ),
		'remitter' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['remitter'],
        ),
        'pvid' => array(
            'type' => 'text',
            //'type' => 'select',
            //'options' => get_proforma_no(),
            'value' => @$_SESSION['search_criteria']['pvid'],
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
    <h1 class="green">Payment Advice<em>* indicates required fields</em></h1>

    <table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right">Payment NO. : </td>
                            <td align="left"><? $form->show('py_no'); ?></td>
                            <td align="right">Status : </td>
                            <td align="left"><? $form->show('istatus'); ?></td>
<!--                            <td align="right">PI NO. : </td>
                            <td align="left"><?/* $form->show('pvid'); */?></td>-->
                        </tr>
                        <tr>
                            <td align="right">Start Value Date : </td>
                            <td align="left"><? $form->show('value_date_start'); ?></td>
                            <td align="right">End Value Date : </td>
                            <td align="left"><? $form->show('value_date_end'); ?></td>
                        </tr>
						<tr>
                            <td align="right">Remitter : </td>
                            <td align="left"><? $form->show('remitter'); ?></td>
                            <td align="right">Proforma Invoice NO. : </td>
                            <td align="left"><? $form->show('pvid'); ?></td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr>
                            <td width="100%" colspan='4'>
                                <?
                                $form->show('submitbutton');
                                // $form->show('resetbutton');

                                ?></td>
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
        $rs->addnew_link = "?act=com-search_payment_new";
        $rs->display_new_button = false;
        $rs->sort_field = "py_no";
        $rs->sort_seq = "DESC";

        $current_page = 1;
        $start_row = 0;
        $end_row = $rs->record_per_page;
        if (set($_GET['page'])){
            $current_page = intval($_GET['page']);
            $start_row = (($current_page-1) * $rs->record_per_page);
        }

        $where_sql = "";

        if (strlen(@$_SESSION['search_criteria']['py_no'])){
            $where_sql.= " AND pn.py_no Like '%".$_SESSION['search_criteria']['py_no'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['istatus'])){
            $where_sql.= " AND pn.istatus Like '%".$_SESSION['search_criteria']['istatus'].'%\'';
        }
		if (strlen(@$_SESSION['search_criteria']['remitter'])){
            $where_sql.= " AND pn.remitter Like '%".$_SESSION['search_criteria']['remitter'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['pvid'])){
            $where_sql.= " AND pin.pi_or_cn_no Like '%".$_SESSION['search_criteria']['pvid'].'%\'';
        }
/*        if (strlen(@$_SESSION['search_criteria']['pvid'])){
            $where_sql.= " AND pvid Like '%".$_SESSION['search_criteria']['pvid'].'%\'';
        }*/
        if (strlen(@$_SESSION['search_criteria']['value_date_start'])){
            if (strlen(@$_SESSION['search_criteria']['value_date_end'])){
                $where_sql.= " AND pn.value_date between '".$_SESSION['search_criteria']['value_date_start']." 00:00:00' AND '".$_SESSION['search_criteria']['value_date_end']." 23:59:59'";
            }else{
                $where_sql.= " AND pn.value_date > '".$_SESSION['search_criteria']['value_date_start']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['value_date_end'])){
            $where_sql.= " AND pn.value_date < '".$_SESSION['search_criteria']['value_date_end']." 23:59:59'";
        }

        //20130916 改为由remitter来区分用户能够看到哪些单
        if (!isSysAdmin()){
            //20150626 把条件in里的子查询拿出来在外面拼接好，否则只能查出一条记录
            $mysql->q('select cid from customer where created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup LIKE ? OR AdminName = ?)', "%".$_SESSION['logininfo']['aName']."%", $_SESSION['logininfo']['aName']);
            $rtn = $mysql->fetch();
            $temp_where = '';
            foreach($rtn as $v){
                $temp_where .= "'".$v['cid']."',";
            }
            $temp_where = trim($temp_where, ',');

            $where_sql .= ' AND pn.remitter in ('.$temp_where.')';
        }

        // echo $where_sql;

        $where_sql.= ' group by pn.py_no ORDER BY pn.in_date DESC ';
        $_SESSION['search_criteria']['page'] = $current_page;

        //20150116 财务填的没有item信息，所以要用left join才能显示出单
        $temp_table = ' payment_new pn left join payment_item_new pin on pn.py_no = pin.py_no';
        $list_field = ' SQL_CALC_FOUND_ROWS *, pn.*, sum(pin.received) as received_total ';

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";
        $rs->SetRecordCol("Payment NO.", "py_no");
//        $rs->SetRecordCol("PI NO.", "pvid");
        $rs->SetRecordCol("Bank Ref", "bank_ref");
        $rs->SetRecordCol("Bank Acc", "bank_acc");
        $rs->SetRecordCol("Value Date", "value_date");
//        $rs->SetRecordCol("Total Bank Charges (HKD)", "total_bank_charges");
        $rs->SetRecordCol("Remitting Amount", "remitting_amount");
        $rs->SetRecordCol("Received Total", "received_total");
        $rs->SetRecordCol("Remitter", "remitter");
        $rs->SetRecordCol("Created by", "created_by");
        $rs->SetRecordCol("Last Updated by", "mod_by");
        $rs->SetRecordCol("Created Date", "in_date");
        $rs->SetRecordCol("Last Update", "mod_date");
        $rs->SetRecordCol("Status", "istatus");

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;
        $rs->SetRecordCol("PDF", "py_no", $sort, $edit,"model/com/pdf_payment_new.php?pdf=1","py_no");
        $rs->SetRecordCol("MODIFY", "py_no", $sort, $edit,"?act=com-modify_payment_new","modid");
        $rs->SetRecordCol("DEL", "py_no", $sort, $edit,"?act=com-modify_payment_new","delid");
        $rs->SetRSSorting('?act=com-search_payment_new');

        /*
        $cur_page = 0;
        if (isset($_POST["page"])){
        $cur_page = $_POST["page"] - 1;
        }
        */

        $rs->ShowRecordSet($info);
    }

}
?>

