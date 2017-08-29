<?php
/**
 * Author: zhangjn
 * Date: 2017/2/19
 * Time: 5:34
 */

if(isset($_GET['value'])){
    $rtn = $mysql->qone('select bom_id from fty_bom_setting');
    if($rtn){
        echo $rtn['bom_id'];
    }else{
        echo 'no-1';
    }
}else{
    echo 'no-2';
}