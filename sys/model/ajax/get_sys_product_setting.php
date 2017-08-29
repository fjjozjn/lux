<?php
/**
 * Author: zhangjn
 * Date: 2017/2/26
 * Time: 15:08
 */
if(isset($_GET['value'])){
    $rtn = $mysql->qone('select pid from setting');
    if($rtn){
        echo $rtn['pid'];
    }else{
        echo 'no-1';
    }
}else{
    echo 'no-2';
}