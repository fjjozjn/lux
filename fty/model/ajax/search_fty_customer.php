<?php
/**
 * Author: zhangjn
 * Date: 2017/12/8
 * Time: 17:08
 */
if(isset($_GET['type'])){
    $rs = null;
    if($_GET['type'] == 1){
        $rs = $mysql->q('select cid, name from fty_wlgy_customer');
    }elseif($_GET['type'] == 2){
        $rs = $mysql->q('select cid, name from fty_jg_customer');
    }else{
        echo 'no-3';
    }
    if($rs){
        $rtn = $mysql->fetch();
        $str = '';
        for($i = 0; $i < count($rtn); $i++){
            $str .= ($i == count($rtn) - 1)?($rtn[$i]['cid'].':'.$rtn[$i]['name']):($rtn[$i]['cid'].':'.$rtn[$i]['name'] . "|");
        }
        echo $str;
    }else{
        echo 'no-1';
    }
}else{
    echo 'no-2';
}