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
        // 'game_name' => array(
        // 'type' => 'text',
        // 'value' => @$_SESSION['search_criteria']['game_name'],
        // ),
        'pcid' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['pcid'],
        ),
        'send_to' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['send_to'],
        ),
        'attention' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['attention'],
        ),
        'created_by' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['created_by'],
        ),
        'created_start_date' => array(
            'type' => 'text',
            'restrict' => 'date',
            'value' => @$_SESSION['search_criteria']['created_start_date'],
        ),
        'created_end_date' => array(
            'type' => 'text',
            'restrict' => 'date',
            'value' => @$_SESSION['search_criteria']['created_end_date'],
        ),
        'etd_start_date' => array(
            'type' => 'text',
            'restrict' => 'date',
            'value' => @$_SESSION['search_criteria']['etd_start_date'],
        ),
        'etd_end_date' => array(
            'type' => 'text',
            'restrict' => 'date',
            'value' => @$_SESSION['search_criteria']['etd_end_date'],
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
        'reference' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['reference'],
        ),
        'istatus' => array(
            'type' => 'select',
            'options' => get_po_status(),
            'value' => @$_SESSION['search_criteria']['istatus'],
        ),
        'customer' => array(
            'type' => 'select',
            'options' => get_customer(),
            'value' => @$_SESSION['search_criteria']['customer'],
        ),
        'supplier' => array(
            'type' => 'select',
            'options' => get_supplier(),
            'value' => @$_SESSION['search_criteria']['supplier'],
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
    <h1 class="green">Factory PO<em>* indicates required fields</em></h1>

    <table width="850" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right">Factory PO No. : </td>
                            <td align="left"><? $form->show('pcid'); ?></td>
                            <td align="right">To : </td>
                            <td align="left"><? $form->show('send_to'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Attention : </td>
                            <td align="left"><? $form->show('attention'); ?></td>
                            <td align="right">Created by : </td>
                            <td align="left"><? $form->show('created_by'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Creation Date From : </td>
                            <td align="left"><? $form->show('created_start_date'); ?></td>
                            <td align="right">Creation Date To : </td>
                            <td align="left"><? $form->show('created_end_date'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">ETD From : </td>
                            <td align="left"><? $form->show('etd_start_date'); ?></td>
                            <td align="right">ETD To : </td>
                            <td align="left"><? $form->show('etd_end_date'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Last Update Date From : </td>
                            <td align="left"><? $form->show('start_date'); ?></td>
                            <td align="right">Last Update Date To : </td>
                            <td align="left"><? $form->show('end_date'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Proforma Invoice # : </td>
                            <td align="left"><? $form->show('reference'); ?></td>
                            <td align="right">Status : </td>
                            <td align="left"><? $form->show('istatus'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Customer : </td>
                            <td align="left"><? $form->show('customer'); ?></td>
                            <td align="right">Supplier : </td>
                            <td align="left"><? $form->show('supplier'); ?></td>
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
        $rs->addnew_link = "?act=com-searchpurchase";
        $rs->display_new_button = false;
        $rs->sort_field = "pcid";
        $rs->sort_seq = "DESC";

        //mod 20121126 为了next page等的链接
        if(set($_GET['sortby'])){
            $rs->sortby = $_GET['sortby'];
        }

        $current_page = 1;
        $start_row = 0;
        $end_row = $rs->record_per_page;
        if (set($_GET['page'])){
            $current_page = intval($_GET['page']);
            $start_row = (($current_page-1) * $rs->record_per_page);
        }

        //20130613 去掉了这个条件，因为直接开po就没有填正确的Proforma Invoice #
        //$where_sql = " AND p.reference = f.pvid ";
        $where_sql = "";

        if (strlen(@$_SESSION['search_criteria']['pcid'])){
            $where_sql.= " AND p.pcid Like '%".$_SESSION['search_criteria']['pcid'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['send_to'])){
            $where_sql.= " AND p.send_to Like '%".$_SESSION['search_criteria']['send_to'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['attention'])){
            $where_sql.= " AND p.attention Like '%".$_SESSION['search_criteria']['attention'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['created_by'])){
            $where_sql.= " AND p.created_by Like '%".$_SESSION['search_criteria']['created_by'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['istatus'])){
            $where_sql.= " AND p.istatus Like '%".$_SESSION['search_criteria']['istatus'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['created_start_date'])){
            if (strlen(@$_SESSION['search_criteria']['created_end_date'])){
                $where_sql.= " AND p.in_date between '".$_SESSION['search_criteria']['created_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['created_end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND p.in_date > '".$_SESSION['search_criteria']['created_start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['created_end_date'])){
            $where_sql.= " AND p.in_date < '".$_SESSION['search_criteria']['created_end_date']." 23:59:59'";
        }
        if (strlen(@$_SESSION['search_criteria']['etd_start_date'])){
            if (strlen(@$_SESSION['search_criteria']['etd_end_date'])){
                $where_sql.= " AND p.expected_date between '".$_SESSION['search_criteria']['etd_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['etd_end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND p.expected_date > '".$_SESSION['search_criteria']['etd_start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['etd_end_date'])){
            $where_sql.= " AND p.expected_date < '".$_SESSION['search_criteria']['etd_end_date']." 23:59:59'";
        }
        if (strlen(@$_SESSION['search_criteria']['start_date'])){
            if (strlen(@$_SESSION['search_criteria']['end_date'])){
                $where_sql.= " AND p.mark_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND p.mark_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
            $where_sql.= " AND p.mark_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
        }
        if (strlen(@$_SESSION['search_criteria']['reference'])){
            $where_sql.= " AND p.reference Like '%".$_SESSION['search_criteria']['reference'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['customer'])){
            $where_sql.= " AND p.customer = '".$_SESSION['search_criteria']['customer']."'";
        }
        if (strlen(@$_SESSION['search_criteria']['supplier'])){
            $where_sql.= " AND p.sid = '".$_SESSION['search_criteria']['supplier']."'";
        }

        //普通用户只能搜索到自己开的单
        if (!isSysAdmin()){
            //$where_sql .= " AND created_by in (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName'].'\'))';
            $where_sql .= " AND p.created_by in (SELECT AdminNameChi FROM tw_admin WHERE AdminLuxGroup LIKE '%".$_SESSION['logininfo']['aName']."%' OR AdminName = '".$_SESSION['logininfo']['aName']."')";
        }

        // echo $where_sql;

        //mod 20121126
        if(set($_GET['sortby'])){
            $where_sql.= ' ORDER BY p.'.substr($_GET['sortby'],0,strrpos($_GET['sortby'],'|')).' '.(substr($_GET['sortby'], -1) == 'a'?'ASC':'DESC').' ';
        }else{
            $where_sql.= ' ORDER BY p.mark_date DESC ';
        }

        $_SESSION['search_criteria']['page'] = $current_page;

        //20130613 用left join 避免了直接开po没有匹配的pvid的情况，就search不出结果
        //20130617 PI的cid用脚本导入到PO的customer，所以这里不用join proforma查询了
        //$temp_table = ' purchase p left join proforma f on p.reference = f.pvid';
        $temp_table = ' purchase p';
        //$list_field = ' SQL_CALC_FOUND_ROWS p.pcid, p.send_to, p.created_by, p.mark_date, p.istatus, p.reference, p.total, p.expected_date, f.cid ';
        $list_field = ' SQL_CALC_FOUND_ROWS p.pcid, p.send_to, p.created_by, p.in_date, p.mark_date, p.istatus, p.reference, p.total, p.expected_date, p.customer, p.sid, p.approved_by ';

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";
        $rs->SetRecordCol("Factory PO No.", "pcid");
        $rs->SetRecordCol("To", "send_to");
        $rs->SetRecordCol("Proforma Invoice #", "reference");
        $rs->SetRecordCol("Customer", "customer");
        $rs->SetRecordCol("Supplier", "sid");
        $rs->SetRecordCol("Total", "total");
        $rs->SetRecordCol("Created by", "created_by");
        $rs->SetRecordCol("ETD", "expected_date", true);
        $rs->SetRecordCol("Creation Date", "in_date");
        $rs->SetRecordCol("Last Update", "mark_date");
        $rs->SetRecordCol("Status", "istatus");

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;

        //20130217 不能在这里用 isSysAdmin 因为里面有select语句，会替代了上面的 backend_list_withfield 找出的数据，导致数据都不见了
        if ($_SESSION['logininfo']['aName'] == 'ZJN' || $_SESSION['logininfo']['aName'] == 'KEVIN'){
            $rs->SetRecordCol("APPROVE", "pcid", $sort, $edit,"?act=com-modifypurchase","approve_po_no");
        }
        $rs->SetRecordCol("Approved by", "approved_by");
        $rs->SetRecordCol("PDF", "pcid", $sort, $edit,"model/com/purchase_pdf2.php?pdf=1","pcid");
        //$rs->SetRecordCol("ADD TO PROFORMA", "pcid", $sort, $edit,"#","pcid");
        $rs->SetRecordCol("MODIFY", "pcid", $sort, $edit,"?act=com-modifypurchase","modid");
        $rs->SetRecordCol("DEL", "pcid", $sort, $edit,"?act=com-modifypurchase","delid");
        $rs->SetRSSorting('?act=com-searchpurchase');

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

