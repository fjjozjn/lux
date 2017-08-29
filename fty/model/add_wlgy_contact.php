<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(

    'cid' => array('title' => '物料供应商编号', 'type' => 'select', 'minlen' => 1, 'maxlen' => 20, 'options' => get_fty_wlgy_customer()),
    //'sid' => array('title' => 'Supplier ID', 'type' => 'select', 'minlen' => 1, 'maxlen' => 20, 'options' => $supplier),
    'first_name' => array('title' => '名', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1),
    'family_name' => array('title' => '姓', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1),
    'title' => array('title' => '称谓', 'type' => 'select', 'options' => get_title()),
    'address' => array('title' => '地址', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200),
    'position' => array('title' => 'Position', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50),
    'fax' => array('title' => '传真', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'tel1' => array('title' => '电话1', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'tel2' => array('title' => '电话2', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'email' => array('title' => 'Email', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1),
    //'send_style_list' => array('title' => 'Send Style List', 'type' => 'radio', 'options' => array(array('Yes', '1'), array('No', '2')), 'required' => 1, 'value' => 0),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
    $cid = isset($_POST['cid'])?trim($_POST['cid']):'';
    //$sid = isset($_POST['sid'])?$_POST['sid']:'';
    $first_name = trim($_POST['first_name']);//20121026 加name的时候在最前面加了个空格，能保存进数据库，但是，在选择custoemr contact的时候，ajax传这个值的时候，就把这个开头的空格给去掉了，就在数据库中找不到了，所以这里我就trim了一下
    $family_name = trim($_POST['family_name']);//20150122
    $title = trim($_POST['title']);
    $address = trim($_POST['address']);
    $position = trim($_POST['position']);
    $fax = trim($_POST['fax']);
    $tel1 = trim($_POST['tel1']);
    $tel2 = trim($_POST['tel2']);
    $email = trim($_POST['email']);
    $send_style_list = '';//trim($_POST['send_style_list']);

    $result = $mysql->q('insert into fty_wlgy_contact (name, family_name, title, address, position, tel1, tel2, fax, email, cid, send_style_list) values ('.moreQm(11).')', $first_name, $family_name, $title, $address, $position, $tel1, $tel2, $fax, $email, $cid, $send_style_list);
    if($result){
        $myerror->ok('新增 物料供应商联系人 成功!', 'search_wlgy_contact&page=1');
    }else{
        $myerror->error('新增 物料供应商联系人 失败', 'add_wlgy_contact');
    }

}


if($myerror->getError()){
    require_once(ROOT_DIR.'model/inside_error.php');
}elseif($myerror->getOk()){
    require_once(ROOT_DIR.'model/inside_ok.php');
}else{

    if($myerror->getWarn()){
        require_once(ROOT_DIR.'model/inside_warn.php');
    }


    ?>
    <h1 class="green">物料供应商联系人<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>添加物料供应商联系人</legend>

        <?php
        $goodsForm->begin();
        ?>
        <table width="100%">
            <tr>
                <td width="25%"><? $goodsForm->show('cid');?></td>
                <td width="25%"><? $goodsForm->show('title');?></td>
                <td width="25%"><? $goodsForm->show('first_name');?></td>
                <td width="25%"><? $goodsForm->show('family_name');?></td>
            </tr>
            <!--<tr>
                <td width="25%"><?/* $goodsForm->show('send_style_list');*/?></td>
                <td width="25%"></td>
                <td width="25%"></td>
                <td width="25%"></td>
            </tr>-->
            <tr>
                <td colspan="2"><? $goodsForm->show('address');?></td>
                <td colspan="2"><? $goodsForm->show('position');?></td>
            </tr>
            <tr>
                <td width="25%"><? $goodsForm->show('fax');?></td>
                <td width="25%"><? $goodsForm->show('tel1');?></td>
                <td width="25%"><? $goodsForm->show('tel2');?></td>
                <td width="25%"><? $goodsForm->show('email');?></td>
            </tr>
        </table>
        <div class="line"></div>
        <?

        $goodsForm->show('submitbtn');
        ?>

    </fieldset>
    <?
    $goodsForm->end();
}
?>