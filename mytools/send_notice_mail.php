<?php
require('../in7/global.php');
//send email to admin
require_once(ROOT_DIR.'class/Mail/mail.php');

$rs = $mysql->q('select * from bulletin_board where (b_date between ? and ?) and b_from = ?', date('Y-m-d').' 00:00:00', date('Y-m-d').' 23:59:59', 'system');
//$rs = $mysql->q('select * from bulletin_board where (b_date between ? and ?) and b_from = ?','2014-05-02 00:00:00', '2014-05-02 23:59:59', 'system');
if($rs){
    $rtn = $mysql->fetch();

    $info = array();
    foreach($rtn as $v){
        $info[$v['b_to']][] = $v['content'];
    }
}

$send = array();
$index = 0;
foreach($info as $key=>$v){
    $rtn = $mysql->qone('select AdminEmail from tw_admin where AdminName = ?', $key);
    $content = '';
    foreach($v as $w){
        $content .= ($w.'<br>');
    }
    $send[$index]['email'] = $rtn['AdminEmail'];
    $send[$index]['content'] = $content;
    $index++;
}

//send_mail('232289219@qq.com', '', 'Payment/Shipment Delay', -1, array('date' => date('Y-m-d')));
//fb($send);die();

$success = 0;
foreach($send as $v){
    $rs = send_mail($v['email'], '', 'Payment/Shipment Delay', $v['content'], array('date' => date('Y-m-d')));
    if($rs){
        $success++;
    }
    if($success == 1){
        //send_mail('232289219@qq.com', '', 'Payment/Shipment Delay', $success, array('date' => date('Y-m-d')));
    }
}