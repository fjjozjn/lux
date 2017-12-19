<?php
/**
 * Author: zhangjn
 * Date: 2017/12/19
 * Time: 17:43
 */
if(isset($_GET['type'])){
    $rtn = null;
    if($_GET['type'] == 1){
        $rtn = $mysql->qone('select ap from fty_wlgy_customer where cid=?', $_GET['value']);
    }elseif($_GET['type'] == 2){
        $rtn = $mysql->qone('select ap from fty_jg_customer where cid=?', $_GET['value']);
    }else{
        echo 'no-3';
    }
    if($rtn){
        echo $str;
    }else{
        echo 'no-1';
    }
}else{
    echo 'no-2';
}