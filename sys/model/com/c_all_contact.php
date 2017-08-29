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

    if (isset($_GET['cid'])){
        ?>
        <h1 class="green">CUSTOMER CONTACT ---- <?php echo $_GET['cid']; ?></h1>

        <fieldset>
            <legend class='legend'>Action</legend>
            <div style="margin-left:28px;"><a class="button" href="?act=com-c_addcontact&cid=<?php echo $_GET['cid']; ?>"><b>ADD</b></a></div>
        </fieldset>

        <?
        if($myerror->getAny()){
            require_once(ROOT_DIR.'model/inside_warn.php');
        }

        $rs = new RecordSetControl2;
        $rs->record_per_page = 10000;//不分页
        $rs->display_new_button = false;
        $rs->sort_field = "cid";
        $rs->sort_seq = "DESC";

        $start_row = 0;
        $end_row = $rs->record_per_page;

        $where_sql = "";

        if (isset($_GET['cid'])){
            $where_sql.= " AND cid Like '%".$_GET['cid'].'%\'';
        }

        //普通用户只能搜索到自己添加的contact
        if (!isSysAdmin()){
            //$where_sql .= " AND cid in (SELECT cid FROM customer WHERE created_by in (select AdminName from tw_admin where AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName'].'\')))';

            //20150626 把条件in里的子查询拿出来在外面拼接好，否则查不出记录
            $mysql->q('SELECT cid FROM customer WHERE created_by in (select AdminName from tw_admin where AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = ?))', $_SESSION['logininfo']['aName']);
            $rtn = $mysql->fetch();
            $temp_where = '';
            foreach($rtn as $v){
                $temp_where .= "'".$v['cid']."',";
            }
            $temp_where = trim($temp_where, ',');
            $where_sql .= " AND cid in (".$temp_where.")";
        }

        //20150409
        //$where_sql.= ' AND cid <> \'\' ORDER BY cid DESC ';
        $where_sql.= ' ORDER BY cid DESC ';

        $temp_table = ' contact';
        $list_field = " SQL_CALC_FOUND_ROWS *, concat(title, ' ', name, ' ', family_name) as full_name, IF(send_style_list=1, 'Yes', 'No') as send_style_list ";

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";
        $rs->SetRecordCol("Customer ID", "cid");
        $rs->SetRecordCol("Full Name", "full_name");
        $rs->SetRecordCol("Title", "title");
        $rs->SetRecordCol("Address", "address");
        $rs->SetRecordCol("Tel", "tel1");
        $rs->SetRecordCol("Fax", "fax");
        $rs->SetRecordCol("Email", "email");
        $rs->SetRecordCol("Send Style List", "send_style_list");

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;
        $rs->SetRecordCol("MODIFY", "id", $sort, $edit,"?act=com-c_modifycontact","modid");
        $rs->SetRecordCol("DEL", "id", $sort, $edit,"?act=com-c_modifycontact","delid");
        $rs->SetRSSorting('?act=com-c_all_contact');

        $rs->ShowRecordSet($info);
    }else{
        die('error');
    }

}
?>

