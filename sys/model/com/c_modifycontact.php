<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();
judgeUserPermNew( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        $rtn = $mysql->q('delete from contact where id = ?', $_GET['delid']);
        if($rtn){
            $myerror->ok('删除 Customer Contact 成功!', 'com-c_searchcontact&page='.$_GET['page']);
        }else{
            $myerror->error('删除 Customer Contact 失败!', 'com-c_searchcontact&page='.$_GET['page']);
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM contact WHERE id = ?', $_GET['modid']);
        }else{
            die('Need modid!');
        }

        $goodsForm = new My_Forms();
        $formItems = array(

            'cid' => array('title' => 'Customer ID', 'type' => 'select', 'minlen' => 1, 'maxlen' => 20, 'options' => get_customer(), 'value' => isset($mod_result['cid'])?$mod_result['cid']:''),
            //'sid' => array('title' => 'Supplier ID', 'type' => 'select', 'minlen' => 1, 'maxlen' => 20, 'options' => $supplier, 'value' => isset($mod_result['sid'])?$mod_result['sid']:''),
            'first_name' => array('title' => 'First Name', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1, 'value' => isset($mod_result['name'])?$mod_result['name']:''),
            'family_name' => array('title' => 'Family Name', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1, 'value' => isset($mod_result['family_name'])?$mod_result['family_name']:''),
            'title' => array('title' => 'Title', 'type' => 'select', 'options' => get_title(), 'value' => isset($mod_result['title'])?$mod_result['title']:''),
            'address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($mod_result['address'])?$mod_result['address']:''),
            'position' => array('title' => 'Position', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['position'])?$mod_result['position']:''),
            'fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['fax'])?$mod_result['fax']:''),
            'tel1' => array('title' => 'Tel 1', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['tel1'])?$mod_result['tel1']:''),
            'tel2' => array('title' => 'Tel 2', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['tel2'])?$mod_result['tel2']:''),
            'email' => array('title' => 'Email', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['email'])?$mod_result['email']:'', 'required' => 1),
            'send_style_list' => array('title' => 'Send Style List', 'type' => 'radio', 'options' => array(array('Yes', '1'), array('No', '2')), 'required' => 1, 'value' => isset($mod_result['send_style_list'])?$mod_result['send_style_list']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){

            $cid = trim($_POST['cid']);
            //$sid = $_POST['sid'];
            $first_name = trim($_POST['first_name']);
            $family_name = trim($_POST['family_name']);
            $title = trim($_POST['title']);
            $address = trim($_POST['address']);
            $position = trim($_POST['position']);
            $fax = trim($_POST['fax']);
            $tel1 = trim($_POST['tel1']);
            $tel2 = trim($_POST['tel2']);
            $email = trim($_POST['email']);
            $send_style_list = trim($_POST['send_style_list']);

            $result = $mysql->q('update contact set name = ?, family_name = ?, title = ?, address = ?, position = ?, tel1 = ?, tel2 = ?, fax = ?, email = ?, cid = ?, send_style_list = ? where id = ?', $first_name, $family_name, $title, $address, $position, $tel1, $tel2, $fax, $email, $cid, $send_style_list, $_GET['modid']);
            if($result){
                $myerror->ok('修改 Customer Contact 成功!', 'com-c_searchcontact&page='.$_GET['page']);
            }else{
                $myerror->error('修改 Customer Contact 失败', 'com-c_searchcontact&page='.$_GET['page']);
            }
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
        <h1 class="green">CUSTOMER CONTACT<em>* item must be filled in</em></h1>
        <fieldset>
            <legend class='legend'>Modify Customer Contact</legend>
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
                <tr>
                    <td width="25%"><? $goodsForm->show('send_style_list');?></td>
                    <td width="25%"></td>
                    <td width="25%"></td>
                    <td width="25%"></td>
                </tr>
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
}
?>
