<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/innamee_warn.php');
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

/*        'wh_name' => array(
            'type' => 'select',
            'options' => get_warehouse_info(),
            'value' => @$_SESSION['search_criteria']['wh_name'],
        ),*/
        'pid' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['pid'],
        ),
        //20130709 加type
        'type' => array(
            'type' => 'select',
            'options' => get_bom_lb(3),
            'value' => @$_SESSION['search_criteria']['type'],
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


// resetJSForm('text', 'admin_name');
// print_r_pre($gameList);
// print_r_pre($_GET);
// print_r_pre($GLOBALS);
    ?>
    <h1 class="green">Warehouse Item <!--span style="color: #F00">只有同一个仓库的同一个item才合并显示</span--><em>* indicates
            required
            fields</em></h1>

    <table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right">Warehouse Name : </td>
                            <td align="left" style="color: #F00"><? echo $_GET['wh_name']; //$form->show('wh_name')
                                ; ?></td>
                            <td align="right">Item Code : </td>
                            <td align="left"><? $form->show('pid'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Type : </td>
                            <td align="left"><? $form->show('type'); ?></td>
                            <td align="right">&nbsp;</td>
                            <td align="left">&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="right">Start Date : </td>
                            <td align="left"><? $form->show('start_date'); ?></td>
                            <td align="right">End Date : </td>
                            <td align="left"><? $form->show('end_date'); ?></td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr>
                            <td width="100%" colspan='4'>
                                <?
                                $form->show('submitbutton');
                                // $form->show('resetbutton');
                                ?><div style="padding-top: 9px;">&nbsp;&nbsp;<a class="button" href="model/com/warehouse_item_unique_pdf.php?wh_name=<?=$_GET['wh_name']?>" target='_blank' onclick="return submitForm()"><b>PDF</b></a><!--&nbsp;&nbsp;<a class="button" href="model/com/warehouse_item_unique_excel.php?wh_name=<?/*=$_GET['wh_name']*/?>" target='_blank' onclick="return submitForm()"><b>EXCEL</b></a>--></div>
                            </td>
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
        $rs->addnew_link = "?act=com-search_warehouse_item_unique";
        $rs->display_new_button = false;
        //$rs->sort_field = "wh_id";
        $rs->sort_seq = "DESC";

        $current_page = 1;
        $start_row = 0;
        $end_row = $rs->record_per_page;
        if (set($_GET['page'])){
            $current_page = intval($_GET['page']);
            $start_row = (($current_page-1) * $rs->record_per_page);
        }

        $where_sql = "";

        //20130617 导航栏过来，也就是没有post，就优先用get值，search按钮只用session值
/*        if(!$getAnyPost){
            if(isset($_GET['wh_name'])){
                $where_sql.= " AND w.wh_name Like '%".$_GET['wh_name'].'%\'';
            }else{
                if (strlen(@$_SESSION['search_criteria']['wh_name'])){
                    $where_sql.= " AND w.wh_name Like '%".$_SESSION['search_criteria']['wh_name'].'%\'';
                }
            }
        }else{
            if (strlen(@$_SESSION['search_criteria']['wh_name'])){
                $where_sql.= " AND w.wh_name Like '%".$_SESSION['search_criteria']['wh_name'].'%\'';
            }
        }*/
        //20130720 去掉了 wh_name 的 search 选择框
        if(isset($_GET['wh_name'])){
            $where_sql.= " AND w.wh_name = '".$_GET['wh_name']."'";
        }

        if (strlen(@$_SESSION['search_criteria']['pid'])){
            $where_sql.= " AND w.pid Like '%".$_SESSION['search_criteria']['pid'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['type'])){
            $where_sql.= " AND p.type Like '%".$_SESSION['search_criteria']['type'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['start_date'])){
            if (strlen(@$_SESSION['search_criteria']['end_date'])){
                $where_sql.= " AND w.in_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
            }else{
                $where_sql.= " AND w.in_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
            }
        }elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
            $where_sql.= " AND w.in_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
        }

        //普通用户只能搜索到自己添加的customer
        if (!isSysAdmin()){
            $where_sql .= " AND w.created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName'].'\'))';
        }

        //20130721 只显示qty 大于0 的项
        //20140503 我觉得也显示负数的比较清楚
        //20141104 改为只显示qty 大雨0 的项
        $where_sql.= ' AND w.qty > 0 ORDER BY w.mod_date desc ';
        $_SESSION['search_criteria']['page'] = $current_page;

        $temp_table = ' warehouse_item_unique w left join product p on w.pid = p.pid';
        $list_field = ' SQL_CALC_FOUND_ROWS w.*, p.type ';

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";
        $rs->SetRecordCol("Photo", "photo");
        $rs->SetRecordCol("Warehouse Name", "wh_name");
        $rs->SetRecordCol("Item Code", "pid");
        $rs->SetRecordCol("Type", "type");
        //$rs->SetRecordCol("Cost RMB", "cost_rmb");
        $rs->SetRecordCol("Quantity", "qty");
        $rs->SetRecordCol("Last Remark", "remark");
        $rs->SetRecordCol("Last Arrival Date", "arrival_date");
        $rs->SetRecordCol("Last Update", "mod_date");
        $rs->SetRecordCol("Last Modify by", "mod_by");

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;
        $rs->SetRecordCol("MODIFY", "id", $sort, $edit,"?act=com-modify_warehouse_item","modid");
        //$rs->SetRecordCol("DEL", "id", $sort, $edit,"?act=com-modify_warehouse_item","delid");
        //$rs->SetRecordCol("IN", "id", $sort, $edit,"?act=com-in_warehouse_item", "inid");
        //$rs->SetRecordCol("OUT", "id", $sort, $edit,"?act=com-out_warehouse_item", "outid");
        $rs->SetRecordCol("TRANSFER", array("id","pid","wh_id","wh_name","photo"), $sort, $edit,"?act=com-add_item_transfer_form", array("tranid","pid","wh_id","wh_name","photo"));
        //连接为空会报错
        $rs->SetRecordCol("HISTORY", array("wh_name", "pid"), $sort, $edit,"?act=com-search_warehouse_item_hist&page=1",array("wh_name", "pid"));
        $rs->SetRSSorting('?act=com-search_warehouse_item_unique');

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

<script>
function submitForm(){
    //confirm yes 自动提交表单
    var form = document.getElementById('gof0');
    form.submit();
}
</script>