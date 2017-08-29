<?php
if( (isset($_GET['qc_id']) && $_GET['qc_id'] != '') && (isset($_GET['pid']) && $_GET['pid'] != '') && (isset($_GET['pic']) && $_GET['pic'] != '') ){

    $rtn = $mysql->qone('select photo from qc_report_item where qc_id = ? and pid = ?', $_GET['qc_id'], $_GET['pid']);
    if(isset($rtn['photo']) && $rtn['photo'] != ''){
        $new_pic = '';
        if(strpos($rtn['photo'], '|'.$_GET['pic']) !== false){
            $new_pic = str_replace("|".$_GET['pic'], "", $rtn['photo']);
        }else{
            //最前面一个删除，会留下最左边的|，所以要用trim
            $new_pic = trim(str_replace($_GET['pic'], "", $rtn['photo']), '|');
        }
        $rs = $mysql->q('update qc_report_item set photo = ? where qc_id = ? and pid = ?', $new_pic, $_GET['qc_id'],
            $_GET['pid']);
        if($rs === false){
            echo '!no-2';
        }else{
            echo 'yes';
        }
    }else{
        echo '!no-1';
    }
}else{
    echo '!no-0';
}