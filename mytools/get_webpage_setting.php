<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/24
 * Time: 22:23
 */

require(substr(__DIR__, 0, -7).'in7/global.php');

header("Access-Control-Allow-Origin:*");

$rtn = $mysql->q('select * from tw_webpage_setting order by sort desc');
if($rtn){
    $result = $mysql->fetch();
    $jsonp = isset($_GET['jsoncallback'])?$_GET['jsoncallback']:'';
    echo $jsonp.'('.json_encode($result).')';
}