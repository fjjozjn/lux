<?php
if( isset($_GET['value']) && $_GET['value'] != ''){
    //非管理员只能查看自己group的 I 或 S 的单
    $pvid = '';
    $cn_no = '';
    if($_GET['value'] == 'PI'){
        if (isSysAdmin()){
            //20130812 先把 I S 的限制去掉，方便绑定旧的payment数据
            //20130823 只选择 I 和 S 的
            $rs = $mysql->q('select pvid from proforma where istatus <> ? and (istatus = ? or istatus = ?) order by mark_date desc', 'delete', '(I)', '(S)');
            //$rs = $mysql->q('select pvid from proforma where istatus <> ? order by mark_date desc', 'delete');
        }else{
            //20130812 先把 I S 的限制去掉，方便绑定旧的payment数据
            //20130823 只选择 I 和 S 的
            //20140319 修改，原来的sql语句多了个select，导致LINDA CHAN的单CICI XIAO查不出来了
            $rs = $mysql->q('select pvid from proforma where istatus <> ? and (istatus = ? or istatus = ?) and printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?) order by pvid desc', 'delete', '(I)', '(S)', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
            //$rs = $mysql->q('select pvid from proforma where istatus <> ? and (istatus = ? or istatus = ?) and pvid in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?)) order by pvid desc', 'delete', '(I)', '(S)', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
            //$rs = $mysql->q('select pvid from proforma where istatus <> ? and pvid in (select pvid from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?)) order by mark_date desc', 'delete', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        }

        if($rs){
            $rows = $mysql->fetch();
            foreach($rows as $v){
                $pvid .= $v['pvid'].'|';
            }
            $pvid = trim($pvid, '|');
        }
        echo $pvid;

    }elseif($_GET['value'] == 'CN'){
        if (isSysAdmin()){
            $rs = $mysql->q('select cn_no from credit_note order by in_date desc');
        }else{
            $rs = $mysql->q('select cn_no from credit_note where cn_no in (select cn_no from credit_note where created_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup like ? OR AdminName = ?)) order by in_date desc', '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
        }

        if($rs){
            $rows = $mysql->fetch();
            foreach($rows as $v){
                $cn_no .= $v['cn_no'].'|';
            }
            $cn_no = trim($cn_no, '|');
        }
        echo $cn_no;
    }
}else{
    echo '!no-0';
}