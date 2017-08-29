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

    if (isset($_GET['cid']) && $_GET['cid']){
        $_SESSION['search_criteria']['cid'] = $_GET['cid'];
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
            'readonly' => (isset($_GET['cid']) && $_GET['cid'])?true:false
        ),
        'send_style_list' => array(
            'type' => 'select',
            'options' => array(array('YES','1'), array('NO','2')),
            'value' => @$_SESSION['search_criteria']['send_style_list'],
        ),
        'first_name' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['first_name'],
        ),
        'family_name' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['family_name'],
        ),
        'address' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['address'],
        ),
        'email' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['email'],
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
    <h1 class="green">CUSTOMER CONTACT <?=(isset($_GET['cid']) && $_GET['cid'])?'<font color="red">'.$_GET['cid'].'</font>':''?><em>* indicates required fields</em></h1>

    <table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right">Customer ID : </td>
                            <td align="left"><? $form->show('cid'); ?></td>
                            <td align="right">Send Style List : </td>
                            <td align="left"><? $form->show('send_style_list'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">First Name : </td>
                            <td align="left"><? $form->show('first_name'); ?></td>
                            <td align="right">Family Name : </td>
                            <td align="left"><? $form->show('family_name'); ?></td>
                        </tr>
                        <tr>
                            <td align="right">Address : </td>
                            <td align="left"><? $form->show('address'); ?></td>
                            <td align="right">Email : </td>
                            <td align="left"><? $form->show('email'); ?></td>
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
        $rs->addnew_link = "?act=com-c_searchcontact";
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

        $where_sql = " AND c1.cid = c2.cid";

        if (strlen(@$_SESSION['search_criteria']['cid'])){
            $where_sql.= " AND c1.cid Like '%".$_SESSION['search_criteria']['cid'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['send_style_list'])){
            $where_sql.= " AND c1.send_style_list = '".$_SESSION['search_criteria']['send_style_list']."'";
        }
        if (strlen(@$_SESSION['search_criteria']['first_name'])){
            $where_sql.= " AND c1.name Like '%".$_SESSION['search_criteria']['first_name'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['family_name'])){
            $where_sql.= " AND c1.family_name Like '%".$_SESSION['search_criteria']['family_name'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['address'])){
            $where_sql.= " AND c1.address Like '%".$_SESSION['search_criteria']['address'].'%\'';
        }
        if (strlen(@$_SESSION['search_criteria']['email'])){
            $where_sql.= " AND c1.email Like '%".$_SESSION['search_criteria']['email'].'%\'';
        }
        //普通用户只能搜索到自己添加的contact
        if (!isSysAdmin()){
            //20150601 修改为普通用户只能查看自己group的用户的信息，也就是主可以查附属的，附属的不能查主的
            //$where_sql .= " AND cid in (SELECT cid FROM customer WHERE created_by in (SELECT AdminName FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName']."' OR AdminLuxGroup = '".$_SESSION['logininfo']['aName']."'))";
            //上面的只能查出ID排序最小的第一条，很奇怪
            //$where_sql .= " AND c1.cid = c2.cid and c2.created_by in (SELECT AdminName FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName']."' OR AdminLuxGroup = '".$_SESSION['logininfo']['aName']."'))";

            //20150626 把条件in里的子查询拿出来在外面拼接好，否则查不出记录
            $mysql->q('SELECT AdminName FROM tw_admin WHERE AdminName = ? OR AdminLuxGroup = ?', $_SESSION['logininfo']['aName'], $_SESSION['logininfo']['aName']);
            $rtn = $mysql->fetch();
            $temp_where = '';
            foreach($rtn as $v){
                $temp_where .= "'".$v['AdminName']."',";
            }
            $temp_where = trim($temp_where, ',');

            $where_sql .= " AND c1.cid = c2.cid and c2.created_by in (".$temp_where.")";
        }

        //20150409
        //$where_sql.= ' AND cid <> \'\' ORDER BY cid DESC ';
        $where_sql.= ' ORDER BY c1.cid DESC ';

        $_SESSION['search_criteria']['page'] = $current_page;

        $temp_table = ' contact c1, customer c2';
        $list_field = " SQL_CALC_FOUND_ROWS *, concat(c1.title, ' ', c1.name, ' ', c1.family_name) as full_name, IF(c1.send_style_list=1, 'Yes', 'No') as send_style_list ";

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;
        $rs->SetRecordCol("Customer ID", "cid");
        $rs->SetRecordCol("Customer Name", "name");
        $rs->SetRecordCol("Full Name", "full_name");
        $rs->SetRecordCol("Title", "title");
        $rs->SetRecordCol("Address", "address");
        $rs->SetRecordCol("Tel", "tel1");
        $rs->SetRecordCol("Fax", "fax");
        $rs->SetRecordCol("Email", "email");
        $rs->SetRecordCol("Send Style List", "send_style_list");

        $rs->SetRecordCol("MODIFY", "id", $sort, $edit,"?act=com-c_modifycontact&page=".$_GET['page'],"modid");
        $rs->SetRecordCol("DEL", "id", $sort, $edit,"?act=com-c_modifycontact&page=".$_GET['page'],"delid");
        $rs->SetRSSorting('?act=com-c_searchcontact');

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

