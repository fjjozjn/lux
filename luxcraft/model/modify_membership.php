<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        $rs = $mysql->q('delete from luxcraft_membership where id = ?', $_GET['delid']);
        if($rs){
            $myerror->ok('删除 Membership 成功!', 'search_membership&page=1');
        }else{
            $myerror->error('删除 Membership 失败!', 'search_membership&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM luxcraft_membership WHERE id = ?', $_GET['modid']);
        }else{
            die('Need modid!');
        }

        $goodsForm = new My_Forms();
        $formItems = array(
            'phone' => array('title' => 'Phone No.', 'type' => 'text', 'required' => 1, 'value' => isset($mod_result['phone'])?$mod_result['phone']:''),
            'title' => array('title' => 'Title', 'type' => 'select', 'options' => get_title(), 'required' => 1, 'value' => isset($mod_result['title'])?$mod_result['title']:''),
            'first_name' => array('title' => 'First Name', 'type' => 'text', 'value' => isset($mod_result['first_name'])?$mod_result['first_name']:''),
            'family_name' => array('title' => 'Family Name','type' => 'text', 'value' => isset($mod_result['family_name'])?$mod_result['family_name']:''),
            'email' => array('title' => 'Email','type' => 'text', 'value' => isset($mod_result['email'])?$mod_result['email']:''),
            'date_of_birth' => array('title' => 'Date of Birth','type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['date_of_birth'])?$mod_result['date_of_birth']:''),
            'address' => array('title' => 'Address','type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['address'])?$mod_result['address']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){
            $phone = $_POST['phone'];
            $title = $_POST['title'];
            $first_name = $_POST['first_name'];
            $family_name = $_POST['family_name'];
            $email = $_POST['email'];
            $date_of_birth = $_POST['date_of_birth'];
            $address = $_POST['address'];
            $mod_date = dateMore();
            $mod_by = $_SESSION['luxcraftlogininfo']['aName'];

            $rs = $mysql->q('update luxcraft_membership set phone = ?, title = ?, first_name = ?, family_name = ?, email = ?, date_of_birth
= ?, address = ?, mod_date = ?, mod_by = ? where id = ?', $phone,
                $title, $first_name, $family_name, $email, $date_of_birth, $address,
                $mod_date, $mod_by, $_GET['modid']);

            if($rs !== false){
                $myerror->ok('Modify Membership Success !', 'search_membership&page=1');
            }else{
                $myerror->error('Modify Membership failure !', 'BACK');
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
        <h1 class="green">MEMBERSHIP<em>* item must be filled in</em></h1>

        <fieldset class="center2col">
            <legend class='legend'>Modify Membership</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><? $goodsForm->show('title');?></td>
                    <td width="25%"><? $goodsForm->show('first_name');?></td>
                    <td width="25%"><? $goodsForm->show('family_name');?></td>
                    <td width="25%"><? $goodsForm->show('date_of_birth');?></td>
                </tr>
                <tr class="formtitle">
                    <td width="25%"><? $goodsForm->show('phone');?></td>
                    <td width="25%"><? $goodsForm->show('email');?></td>
                    <td width="50%" colspan="2"><? $goodsForm->show('address');?></td>
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

