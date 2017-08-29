<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//负责同事默认用当前登入用户
$staff = $_SESSION['logininfo']['aName'];

$goodsForm = new My_Forms();
$formItems = array(

    'pc_sid' => array('title' => '工厂', 'type' => 'select', 'options' => get_supplier(), 'required' => 1),
    'pc_attention' => array('title' => '致', 'type' => 'select', 'required' => 1, 'options' => ''),
//    'staff' => array('title' => '负责同事', 'type' => 'select', 'options' => get_user('sys'), 'required' => 1),
    'about' => array('title' => '有关SO/PO(如有)', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'form_date' => array('title' => '日期', 'type' => 'text', 'restrict' => 'date', 'required' => 1),
    'remark' => array('title' => '备注', 'type' => 'text', 'minlen' => 1, 'maxlen' => 100),

    'detail' => array('title' => '具体内容', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000),
    /*'fty_reply' => array('title' => '工厂回应/行动', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000,
        'disabled' => 'disabled'),*/

    'submitbtn'	=> array('type' => 'submit', 'value' => ' 提交 '),

);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){

    $fc_id = autoGenerationID();//自动生成
    $pc_sid = trim($_POST['pc_sid']);
    $pc_attention = trim($_POST['pc_attention']);
    $form_date = trim($_POST['form_date']);
    $about = trim($_POST['about']);
    $remark = trim($_POST['remark']);
    $detail = trim($_POST['detail']);
    //$fty_reply = trim($_POST['fty_reply']);
    $in_date = $mod_date = dateMore();
    $created_by = $mod_by = $_SESSION['logininfo']['aName'];

    $rs = $mysql->q('insert into fty_complaint_form set fc_id = ?, fty_sid = ?, send_to = ?, staff = ?, form_date = ?, about = ?, remark = ?, detail = ?, in_date = ?, mod_date = ?, created_by = ?, mod_by = ?', $fc_id, $pc_sid, $pc_attention, $staff, $form_date, $about, $remark, $detail, /*$fty_reply,*/ $in_date, $mod_date, $created_by,
        $mod_by);
    if($rs){

        //add action log
        /*$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['logininfo']['aID'], $ip_real
            , ACTION_LOG_SYS_ADD_CUSTOMER, $_SESSION["logininfo"]["aName"]." <i>add customer</i> ".$cid."----'".$name."' in sys", ACTION_LOG_SYS_ADD_CUSTOMER_S, "", "", 0);*/

        $myerror->ok('新增 Factory Complaint Form 成功!', 'com-search_factory_complaint_form&page=1');
    }else{
        $myerror->error('新增 Factory Complaint Form 失败', 'com-add_factory_complaint_form');
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
    <h1 class="green">Factory Complaint Form<em>* item must be filled in</em></h1>
    <fieldset class="center2col" style="width:60%">
        <legend class='legend'>Add</legend>

        <?php
        $goodsForm->begin();
        ?>

        <table width="100%">
            <tr>
                <td width="33%"><? $goodsForm->show('pc_sid');?></td>
                <td width="33%"><? $goodsForm->show('pc_attention');?></td>
                <td width="33%"><div class="set"><label class="formtitle" for="staff">负责同事</label><div class="formfield"><?=$staff?></div></div></td>
            </tr>
            <tr>
                <td width="33%"><? $goodsForm->show('form_date');?></td>
                <td width="33%"><? $goodsForm->show('about');?></td>
                <td width="33%"><? $goodsForm->show('remark');?></td>
            </tr>
            <tr>
                <td colspan="3"><? $goodsForm->show('detail');?></td>
            </tr>
<!--            <tr>-->
<!--                <td colspan="3">--><?// $goodsForm->show('fty_reply');?><!--</td>-->
<!--            </tr>-->
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

<script>
    $(function(){
        selectSupplier("pc_");
    })
</script>