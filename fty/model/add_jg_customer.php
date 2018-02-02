<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(

    'cid' => array('title' => '加工商编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 5, 'required' => 1),
    'name' => array('title' => '加工商名', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 80, 'required' => 1),
    'markup_ratio' => array('title' => 'Markup Ratio', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20/*, 'required' => 1*/),
    //'terms' => array('title' => 'Terms', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20),
    'deposit' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 5, 'addon' => 'style="width:50px"'/*, 'required' => 1*/, 'value' => '0'),
    'balance' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 5, 'addon' => 'style="width:50px"'/*, 'required' => 1*/, 'value' => '0'),
    'website' => array('title' => '网站', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'remark' => array('title' => '备注', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50),

    'production_packaging' => array('title' => 'Production Packaging', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000),
    'production_shipmark' => array('title' => 'Production Shipmark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000),
    'production_remarks' => array('title' => 'Production Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000),

    'bank_no' => array('title' => '银行账号', 'type' => 'text', 'restrict' => 'number', 'maxlen' => 50),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
    $cid = $_POST['cid'];
    $name = trim($_POST['name']);//20121026 加name的时候在最前面加了个空格，能保存进数据库，但是，在选择custoemr的时候，ajax传这个值的时候，就把这个开头的空格给去掉了，就在数据库中找不到了，所以这里我就trim了一下
    $markup_ratio = '';//$_POST['markup_ratio'];
    //$terms = $_POST['terms'];
    $deposit = '';//$_POST['deposit'];
    $balance = '';//$_POST['balance'];
    $website = $_POST['website'];
    $remark = $_POST['remark'];
    $created_by = $_SESSION['ftylogininfo']['aName'];

    //add 201305241334
    $production_packaging = '';//$_POST['production_packaging'];
    $production_shipmark = '';//$_POST['production_shipmark'];
    $production_remarks = '';//$_POST['production_remarks'];

    $bank_no = $_POST['bank_no'];

    $judge = $mysql->q('select cid from fty_jg_customer where cid = ?', $cid);
    if(!$judge){
        $result = $mysql->q('insert into fty_jg_customer set cid = ?, name = ?, website = ?, markup_ratio = ?, deposit = ?, balance = ?, remark = ?, created_by = ?, production_packaging = ?, production_shipmark = ?, production_remarks = ?, bank_no = ?', $cid, $name, $website, $markup_ratio, $deposit, $balance, $remark, $created_by, $production_packaging, $production_shipmark, $production_remarks, $bank_no);
        if($result){
            $myerror->ok('新增 加工商 成功!', 'search_jg_customer&page=1');
        }else{
            $myerror->error('新增 加工商 失败', 'add_jg_customer');
        }
    }else{
        $myerror->error('加工商编号重复，新增失败', 'add_jg_customer');
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
    <h1 class="green">加工商<em>* item must be filled in</em></h1>
    <fieldset class="center2col" style="width:60%">
        <legend class='legend'>新增加工商</legend>

        <?php
        $goodsForm->begin();
        ?>
        <table width="100%">
            <tr>
                <td width="33%"><? $goodsForm->show('cid');?></td>
                <td width="33%"><? $goodsForm->show('website');?></td>
                <td width="33%"><? //$goodsForm->show('markup_ratio');?></td>
            </tr>
            <tr>
                <td colspan="2"><? $goodsForm->show('name');?></td>
                <td width="33%">&nbsp;</td>
            </tr>
        </table>
        <!--<br />
        <table>
            <tr class="formtitle">
                <td width="6%">&nbsp;</td>
                <td>Payment Terms:&nbsp;</td>
                <td><?/* $goodsForm->show('deposit');*/?></td>
                <td>&nbsp;% Deposit, Balance&nbsp;</td>
                <td><?/* $goodsForm->show('balance');*/?></td>
                <td>&nbsp;days after delivery</td>
            </tr>
        </table>-->
        <table>
            <tr>
                <td colspan="2"><? $goodsForm->show('remark');?></td>
                <td width="33%">&nbsp;</td>
            </tr>
        </table>
        <!--<div class="line"></div>
        <table>
            <tr>
                <td colspan="2"><?/* $goodsForm->show('production_packaging');*/?></td>
                <td width="33%">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><?/* $goodsForm->show('production_shipmark');*/?></td>
                <td width="33%">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><?/* $goodsForm->show('production_remarks');*/?></td>
                <td width="33%">&nbsp;</td>
            </tr>
        </table>-->
        <table>
            <tr valign="top">
                <td width="33%"><? $goodsForm->show('bank_no');?></td>
                <td width="33%">&nbsp;</td>
                <td width="33%">&nbsp;</td>
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