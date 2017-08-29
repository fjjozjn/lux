<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{

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
        'cid' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['cid'],
        ),
        'name' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['name'],
        ),
        'website' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['website'],
        ),
        'created_by' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['created_by'],
        ),
        'submitbutton' => array(
            'type' => 'submit',
            'value' => ' 搜索 ',
        ),
    );
    $form->init($formItems);
    $form->begin();


// resetJSForm('text', 'admin_name');
// print_r_pre($gameList);
// print_r_pre($_GET);
// print_r_pre($GLOBALS);
    ?>
    <h1 class="green">物料供应商<em>* indicates required fields</em></h1>

    <table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>搜索</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right">物料供应商编号 : </td>
                            <td align="left"><? $form->show('cid'); ?></td>
                            <td align="right">名字 : </td>
                            <td align="left"><? $form->show('name'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">网站 : </td>
                            <td align="left"><? $form->show('website'); ?></td>
                            <td align="right">创建者 : </td>
                            <td align="left"><? $form->show('created_by'); ?></td>
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
        $rs = new RecordSetControl3;
        $rs->record_per_page = ADMIN_ROW_PER_PAGE;
        $rs->addnew_link = "?act=search_wlgy_customer";
        $rs->display_new_button = false;
        $rs->sort_field = "cid";
        $rs->sort_seq = "DESC";

        $current_page = 1;
        $start_row = 0;
        $end_row = $rs->record_per_page;
        if (set($_GET['page'])){
            $current_page = intval($_GET['page']);
            $start_row = (($current_page-1) * $rs->record_per_page);
        }

        $where_sql = "";

        if (strlen(@$_SESSION['search_criteria']['cid'])){
            $where_sql.= " AND cid Like '%".$_SESSION['search_criteria']['cid'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['name'])){
            $where_sql.= " AND name Like '%".$_SESSION['search_criteria']['name'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['website'])){
            $where_sql.= " AND website Like '%".$_SESSION['search_criteria']['website'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['created_by'])){
            $where_sql.= " AND created_by Like '%".$_SESSION['search_criteria']['created_by'].'%\'';
        }
        //普通用户只能搜索到自己添加的customer
        /*if (!isFtyAdmin()){

        }*/

        $where_sql.= ' ORDER BY cid DESC ';
        $_SESSION['search_criteria']['page'] = $current_page;

        $temp_table = ' fty_wlgy_customer';
        $list_field = ' SQL_CALC_FOUND_ROWS * ';

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";
        $rs->SetRecordCol("物料供应商编号", "cid");
        $rs->SetRecordCol("名字", "name");
        $rs->SetRecordCol("网站", "website");
        $rs->SetRecordCol("备注", "remark");
        //$rs->SetRecordCol("Markup Ratio", "markup_ratio");
        //$rs->SetRecordCol("Terms", "terms");
        //$rs->SetRecordCol("Deposit ( % )", "deposit");
        //$rs->SetRecordCol("Balance ( day )", "balance");
        $rs->SetRecordCol("创建者", "created_by");

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;
        $rs->SetRecordCol("修改", "cid", $sort, $edit,"?act=modify_wlgy_customer","modid");
        //$rs->SetRecordCol("ADD", "cid", $sort, $edit,"?act=com-c_addcontact","cid");
        //20150622
        $rs->SetRecordCol("查看联系人", "cid", $sort, $edit,"?act=all_wlgy_contact","cid");
        $rs->SetRecordCol("删除", "cid", $sort, $edit,"?act=modify_wlgy_customer","delid");
        $rs->SetRSSorting('?act=search_wlgy_customer');

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

