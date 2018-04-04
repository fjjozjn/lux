<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//judgeUserPerm_new( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        $rtn = $mysql->q('delete from fty_jg_customer where cid = ?', $_GET['delid']);
        if($rtn){
            $myerror->ok('删除 加工商 成功!', 'search_jg_customer&page=1');
        }else{
            $myerror->error('删除 加工商 失败!', 'search_jg_customer&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM fty_jg_customer WHERE cid = ?', $_GET['modid']);
        }else{
            die('Need modid!');
        }

        $goodsForm = new My_Forms();
        $formItems = array(

            'cid' => array('title' => '加工商编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 5, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['cid'])?$mod_result['cid']:''),
            //'created_by' => array('title' => 'Created by', 'type' => 'select', 'required' => 1, 'options' => $user, 'value' => isset($mod_result['created_by'])?$mod_result['created_by']:''),
            'name' => array('title' => '加工商名', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 80, 'required' => 1, 'value' => isset($mod_result['name'])?$mod_result['name']:''),
            'markup_ratio' => array('title' => 'Markup Ratio', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20/*, 'required' => 1*/, 'value' => isset($mod_result['markup_ratio'])?$mod_result['markup_ratio']:''),
            //'terms' => array('title' => 'Terms', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['terms'])?$mod_result['terms']:''),
            'deposit' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 5, 'value' => isset($mod_result['deposit'])?$mod_result['deposit']:'0', 'addon' => 'style="width:50px"'/*, 'required' => 1*/),
            'balance' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 5, 'value' => isset($mod_result['balance'])?$mod_result['balance']:'0', 'addon' => 'style="width:50px"'/*, 'required' => 1*/),
            'website' => array('title' => '网站', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['website'])?$mod_result['website']:''),
            'remark' => array('title' => '备注', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),

            'production_packaging' => array('title' => 'Production Packaging', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['production_packaging'])?$mod_result['production_packaging']:''),
            'production_shipmark' => array('title' => 'Production Shipmark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['production_shipmark'])?$mod_result['production_shipmark']:''),
            'production_remarks' => array('title' => 'Production Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['production_remarks'])?$mod_result['production_remarks']:''),

            'ap' => array('title' => 'AP（临时栏位）', 'type' => 'text', 'restrict' => 'number', 'value' => isset($mod_result['ap'])?$mod_result['ap']:'0'),
            'bank_no' => array('title' => '银行账号', 'type' => 'text', 'restrict' => 'number', 'maxlen' => 50, 'value' => isset($mod_result['bank_no'])?$mod_result['bank_no']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' 保存 '),
        );

        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){

            $name = $_POST['name'];
            $markup_ratio = '';//$_POST['markup_ratio'];
            //$terms = $_POST['terms'];
            $deposit = '';//$_POST['deposit'];
            $balance = '';//$_POST['balance'];
            $website = $_POST['website'];
            $remark = $_POST['remark'];
            $created_by = $mod_result['created_by'];//$_POST['created_by'];

            //add 201305241334
            $production_packaging = '';//$_POST['production_packaging'];
            $production_shipmark = '';//$_POST['production_shipmark'];
            $production_remarks = '';//$_POST['production_remarks'];

            //add 180201
            $ap = $_POST['ap'];

            $bank_no = $_POST['bank_no'];

            $result = $mysql->q('update fty_jg_customer set name = ?, markup_ratio = ?, deposit = ?, balance = ?, website = ?, remark = ?, created_by = ?, production_packaging = ?, production_shipmark = ?, production_remarks = ?, ap = ?, bank_no = ? where cid = ?', $name, $markup_ratio, $deposit, $balance, $website, $remark, $created_by, $production_packaging, $production_shipmark, $production_remarks, $ap, $bank_no, $_GET['modid']);
            if($result){
                $myerror->ok('修改 加工商 成功!', 'search_jg_customer&page=1');
            }else{
                $myerror->error('修改 加工商 失败', 'BACK');
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
        <h1 class="green">加工商<em>* item must be filled in</em></h1>

        <fieldset class="center2col" style="width:60%">
            <legend class='legend'>操作</legend>
            <div style="margin-left:28px;"><a class="button" href="?act=add_jg_contact&cid=<?=$mod_result['cid']?>" onclick="return pdfConfirm()">添加联系人</a><a class="button" href="javascript:" onclick="window.open ('model/customer_ap_change_record.php?cid=<?=$mod_result['cid']?>','lux','height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no')">付款记录</a></div>
        </fieldset>

        <fieldset class="center2col" style="width:60%">
            <legend class='legend'>修改加工商</legend>
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
                <tr>
                <tr class="formtitle">
                    <td width="6%">&nbsp;</td>
                    <td>Payment Terms:&nbsp;</td>
                    <td><?/* $goodsForm->show('deposit');*/?></td>
                    <td>&nbsp;% Deposit, Balance&nbsp;</td>
                    <td><?/* $goodsForm->show('balance');*/?></td>
                    <td>&nbsp;days after delivery</td>
                </tr>
                </tr>
            </table>-->
            <table>
                <tr valign="top">
                    <td colspan="2"><? $goodsForm->show('remark');?></td>
                    <td width="33%"><? //$goodsForm->show('created_by');?></td>
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
                    <td width="33%"><? $goodsForm->show('ap');?></td>
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
}
