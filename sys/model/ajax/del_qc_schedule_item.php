<?
//for delete button
/*if( !isset($_GET['value']) || $_GET['value'] == ''){
    echo '!no-0';
}elseif( !isset($_GET['date']) || $_GET['date'] == ''){
    echo '!no-1';
}else{
    $rs = $mysql->q('delete from qc_schedule where pcid = ? and qcs_date like ?', $_GET['value'], $_GET['date'].'%');
    if($rs){
        echo 'yes';
    }else{
        echo '!no-2';
    }
}*/

if(isset($_GET['value']) && $_GET['value'] != ''){
    $rs = $mysql->q('delete from qc_schedule where id = ?', $_GET['value']);
    if($rs){
        echo 'yes';
    }else{
        echo '!no-2';
    }
}else{
    echo '!no-0';
}