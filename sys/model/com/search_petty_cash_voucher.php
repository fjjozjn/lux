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

    $form = new My_Forms();
    $formItems = array(
        'pcv_id' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['pcv_id'],
        ),
        'created_by' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['created_by'],
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
        'submitbutton' => array(
            'type' => 'submit',
            'value' => 'Search',
        ),
    );
    $form->init($formItems);
    $form->begin();


    ?>
    <h1 class="green">PettyCash Voucher<em>* indicates required fields</em></h1>

    <table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right">PettyCash Voucher NO. : </td>
                            <td align="left"><? $form->show('pcv_id'); ?></td>
                            <td align="right">Created by : </td>
                            <td align="left"><? $form->show('created_by'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Start Date : </td>
                            <td align="left"><? $form->show('start_date'); ?></td>
                            <td align="right">End Date : </td>
                            <td align="left"><? $form->show('end_date'); ?></td>
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
        $rs->addnew_link = "?act=com-search_petty_cash_voucher";
        $rs->display_new_button = false;
        $rs->sort_field = "pcv_id";
        $rs->sort_seq = "DESC";

        $current_page = 1;
        $start_row = 0;
        $end_row = $rs->record_per_page;
        if (set($_GET['page'])){
            $current_page = intval($_GET['page']);
            $start_row = (($current_page-1) * $rs->record_per_page);
        }

        $where_sql = "";

        if (strlen(@$_SESSION['search_criteria']['pcv_id'])){
            $where_sql.= " AND pcv_id Like '%".$_SESSION['search_criteria']['pcv_id'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['created_by'])){
            $where_sql.= " AND created_by Like '%".$_SESSION['search_criteria']['created_by'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['start_date'])){
            if (strlen(@$_SESSION['search_criteria']['end_date'])){
                $where_sql.= " AND in_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND in_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
            $where_sql.= " AND in_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
        }

        //普通用户只能搜索到自己开的单
        if (!isSysAdmin()){
            $where_sql .= " AND created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup LIKE '%".$_SESSION['logininfo']['aName']."%' OR AdminName = '".$_SESSION['logininfo']['aName']."')";
        }

        $where_sql.= ' ORDER BY mod_date DESC ';
        $_SESSION['search_criteria']['page'] = $current_page;

        $temp_table = ' sys_petty_cash_voucher';
        $list_field = ' SQL_CALC_FOUND_ROWS id, pcv_id, in_date, mod_date, created_by, mod_by ';

        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);

        $rs->SetRecordCol("PettyCash Voucher ID", "pcv_id");
        $rs->SetRecordCol("Created by", "created_by");
        $rs->SetRecordCol("Date", "in_date");

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;
        $rs->SetRecordCol("PDF", "id", $sort, $edit, "model/com/pdf_petty_cash_voucher.php?pdf=1", "pcv_id");
        $rs->SetRecordCol("MODIFY", "id", $sort, $edit,"?act=com-modify_petty_cash_voucher", "modid");
        $rs->SetRecordCol("DEL", "id", $sort, $edit,"?act=com-modify_petty_cash_voucher", "delid");
        $rs->SetRSSorting('?act=com-search_petty_cash_voucher');

        $rs->ShowRecordSet($info);
    }

}