<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);

//禁止其他用户进入（临时做法）
/*
if($_SESSION['ftylogininfo']['aName'] != 'zjn' && $_SESSION['ftylogininfo']['aName'] != 'KEVIN'){
	$myerror->error('测试中，未开放！', 'main');
}
*/

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
        // 'game_name' => array(
        // 'type' => 'text',
        // 'value' => @$_SESSION['search_criteria']['game_name'],
        // ),
//        'd_id' => array(
//            'type' => 'text',
//            'value' => @$_SESSION['search_criteria']['d_id'],
//        ),
        'po_id' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['po_id'],
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
        'supplier' => array(
            'type' => 'select',
            'options' => get_supplier(),
            'value' => @$_SESSION['search_criteria']['supplier'],
        ),
        'p_id' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['p_id'],
        ),
        'submitbutton' => array(
            'type' => 'submit',
            'value' => '确定',
        ),
    );
    $form->init($formItems);
    $form->begin();


// resetJSForm('text', 'admin_name');
// print_r_pre($gameList);
// print_r_pre($_GET);
// print_r_pre($GLOBALS);
    ?>
    <!--h1 class="green"><em>* indicates required fields</em></h1-->

    <table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>搜索出货单</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                        <tr>
                            <td>起始日期 : </td>
                            <td><? $form->show('start_date'); ?></td>

                            <td>结束日期 : </td>
                            <td><? $form->show('end_date'); ?></td>
                        </tr>
                        <tr>
                            <td>订单号 : </td>
                            <td><? $form->show('po_id'); ?></td>
                            <td>工厂名称 : </td>
                            <td><? $form->show('supplier'); ?></td>
                        </tr>
                        <tr>
                            <td>产品编号 : </td>
                            <td><? $form->show('p_id'); ?></td>
                            <td></td>
                            <td></td>
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
        $rs->addnew_link = "?act=com-view_factory_delivery";
        $rs->display_new_button = false;
        $rs->sort_field = "d_id";
        $rs->sort_seq = "DESC";

        $current_page = 1;
        $start_row = 0;
        $end_row = $rs->record_per_page;
        if (set($_GET['page'])){
            $current_page = intval($_GET['page']);
            $start_row = (($current_page-1) * $rs->record_per_page);
        }

        $where_sql = "";

//        if (strlen(@$_SESSION['search_criteria']['d_id'])){
//            $where_sql.= " AND d.d_id Like '%".$_SESSION['search_criteria']['d_id'].'%\'';
//        }
        if (strlen(@$_SESSION['search_criteria']['po_id'])){
            $where_sql.= " AND di.po_id Like '%".$_SESSION['search_criteria']['po_id'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['supplier'])){
            $where_sql.= " AND d.sid Like '%".$_SESSION['search_criteria']['supplier'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['p_id'])){
            $where_sql.= " AND di.p_id Like '%".$_SESSION['search_criteria']['p_id'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['start_date'])){
            if (strlen(@$_SESSION['search_criteria']['end_date'])){
                $where_sql.= " AND d.d_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND d.d_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
            $where_sql.= " AND d.d_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
        }

        //普通用户只能搜索到自己开的PO单对应有的出货单
        if (!isSysAdmin()){
            $where_sql .= " AND d.d_id = di.d_id AND d.sid = s.sid AND di.po_id in (select pcid from purchase where created_by IN (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup LIKE '%".$_SESSION['logininfo']['aName']."%' OR AdminName = '".$_SESSION['logininfo']['aName']."')) GROUP BY di.d_id, di.po_id";
        }else{
            $where_sql .= " AND d.d_id = di.d_id AND d.sid = s.sid GROUP BY di.d_id, di.po_id";//注意，这里要group by两个参数才行，只di.po_id这个不行（如果group di.d_id和di.po_id的话，如果那一个出货单中po_id不同，则又有可能被分成两条记录显示，如 S008-20141120-03 ！！所以现在改为 group by di.d_id，应该是正确的了吧）
        }

        // echo $where_sql;

        $where_sql.= ' ORDER BY d.d_date DESC ';
        $_SESSION['search_criteria']['page'] = $current_page;

        //$temp_table = ' delivery d, purchase p';
        //$list_field = ' SQL_CALC_FOUND_ROWS d.d_id, d.d_date, d.sid, p.expected_date ';
        $temp_table = ' delivery d, delivery_item di, supplier s';
        $list_field = ' SQL_CALC_FOUND_ROWS d.d_id, s.name, d.d_date, d.total_all, di.po_id ';

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";
        $rs->SetRecordCol("工厂出货单号", "d_id");
        $rs->SetRecordCol("订单号", "po_id");
        $rs->SetRecordCol("工厂名称", "name");
        $rs->SetRecordCol("出货日期", "d_date");
        //$rs->SetRecordCol("出货期限", "expected_date");
        //$rs->SetRecordCol("工厂编号", "sid");
        $rs->SetRecordCol("总金额", "total_all");

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;
        //有時太方便反而令人手容易錯
        //$rs->SetRecordCol("ADD TO PURCHASE", "po_id", $sort, $edit,"?act=com-modifypurchase","po_id");
        //$rs->SetRecordCol("ADD TO INVOICE", "po_id", $sort, $edit,"?act=com-modifyinvoice","po_id");
        $rs->SetRecordCol("出货发票", "d_id", $sort, $edit,"model/com/delivery_split_pdf.php?pdf=1","d_id");
        $rs->SetRecordCol("出货清单", "d_id", $sort, $edit,"model/com/delivery_pdf.php?pdf=1","d_id");
        //$rs->SetRecordCol("产品送检单", "d_id", $sort, $edit,"model/delivery_check_pdf.php?pdf=1","d_id");
        //$rs->SetRecordCol("PDF", "d_id", $sort, $edit,"model/com/delivery_split_pdf.php?pdf=1","d_id");
        $rs->SetRecordCol("查看", "d_id", $sort, $edit,"?act=com-view_factory_delivery_detail","modid");
        //$rs->SetRecordCol("删除", "d_id", $sort, $edit,"?act=modifydelivery","delid");
        $rs->SetRSSorting('?act=com-view_factory_delivery');

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

