<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/innamee_warn.php');
}else{

    if (isset($_GET['cid'])){
        ?>
        <h1 class="green">加工商联系人 ---- <?php echo $_GET['cid']; ?></h1>

        <fieldset>
            <legend class='legend'>操作</legend>
            <div style="margin-left:28px;"><a class="button" href="?act=c_add_jg_contact&cid=<?php echo $_GET['cid']; ?>"><b>添加联系人</b></a></div>
        </fieldset>

        <?
        if($myerror->getAny()){
            require_once(ROOT_DIR.'model/inside_warn.php');
        }

        $rs = new RecordSetControl3;
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
        /*if (!isFtyAdmin()){

        }*/

        //20150409
        //$where_sql.= ' AND cid <> \'\' ORDER BY cid DESC ';
        $where_sql.= ' ORDER BY cid DESC ';

        $temp_table = ' fty_jg_contact';
        $list_field = " SQL_CALC_FOUND_ROWS *, concat(title, ' ', name, ' ', family_name) as full_name, IF(send_style_list=1, 'Yes', 'No') as send_style_list ";

        //get the row count for this seaching criteria
        //$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
        // echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
        //echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
        $info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
        //$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
        // echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';

        //$rs->col_width = "100";
        $rs->SetRecordCol("加工商编号", "cid");
        $rs->SetRecordCol("名字", "full_name");
        $rs->SetRecordCol("称谓", "title");
        $rs->SetRecordCol("地址", "address");
        $rs->SetRecordCol("电话", "tel1");
        $rs->SetRecordCol("传真", "fax");
        $rs->SetRecordCol("Email", "email");
        //$rs->SetRecordCol("Send Style List", "send_style_list");

        $sort = GENERAL_NO;
        $edit = GENERAL_YES;
        $rs->SetRecordCol("修改", "id", $sort, $edit,"?act=c_modify_jg_contact","modid");
        $rs->SetRecordCol("删除", "id", $sort, $edit,"?act=c_modify_jg_contact","delid");
        $rs->SetRSSorting('?act=c_all_jg_contact');

        $rs->ShowRecordSet($info);
    }else{
        die('error');
    }

}
?>

