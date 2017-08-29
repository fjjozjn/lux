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
        $rtn = $mysql->q('delete from fty_complaint_form where fc_id = ?', $_GET['delid']);
        if($rtn){

            //add action log
            /*$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_CUSTOMER, $_SESSION["ftylogininfo"]["aName"]." <i>delete customer</i> ID:'".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_CUSTOMER_S, "", "", 0);*/

            $myerror->ok('删除 Factory Complaint Form 成功!', 'search_factory_complaint_form&page=1');
        }else{
            $myerror->error('删除 Factory Complaint Form 失败!', 'search_factory_complaint_form&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM fty_complaint_form WHERE fc_id = ?', $_GET['modid']);
        }else{
            die('Need modid!');
        }
        //因為一開始沒有attention的選項所以要加上
        $pc_attention = array(array($mod_result['send_to'], $mod_result['send_to']));

        $goodsForm = new My_Forms();
        $formItems = array(

            'fc_id' => array('title' => '投诉报告编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['fc_id'])?$mod_result['fc_id']:''),
            'pc_sid' => array('title' => '工厂', 'type' => 'select', 'options' => get_supplier(), 'required' => 1, 'value' => isset($mod_result['fty_sid'])?$mod_result['fty_sid']:''),
            'pc_attention' => array('title' => '致', 'type' => 'select', 'options' => $pc_attention, 'required' => 1, 'value' => isset($mod_result['send_to'])?$mod_result['send_to']:''),
            /*'staff' => array('title' => '负责同事', 'type' => 'select', 'options' => get_user('sys'), 'required' => 1, 'value' => isset($mod_result['staff'])?$mod_result['staff']:''),*/
            'about' => array('title' => '有关SO/PO(如有)', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['about'])?$mod_result['about']:'', 'readonly' => 'readonly'),
            'form_date' => array('title' => '日期', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['form_date'])?date('Y-m-d', strtotime($mod_result['form_date'])):'', 'readonly' => 'readonly'),
            'remark' => array('title' => '备注', 'type' => 'text', 'minlen' => 1, 'maxlen' => 100, 'value' => isset($mod_result['remark'])?$mod_result['remark']:'', 'readonly' => 'readonly'),

            'detail' => array('title' => '具体内容', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'value' => isset($mod_result['detail'])?$mod_result['detail']:'', 'readonly' => 'readonly', 'addon' => 'style="background-color:#ddd;"'),
            'fty_reply' => array('title' => '工厂回应/行动', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'value' => isset($mod_result['fty_reply'])?$mod_result['fty_reply']:''),
            'reply_satisfaction' => array('title' => '回复满意度', 'type' => 'radio', 'options' => array(array(1,1),array(2,2),array(3,3),array(4,4),array(5,5)), 'value' => isset($mod_result['reply_satisfaction'])?$mod_result['reply_satisfaction']:'', 'disabled' => 'disabled'),
            'reply_remark' => array('title' => '备注', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'value' => isset($mod_result['reply_remark'])?$mod_result['reply_remark']:'', 'disabled' => 'disabled'),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' 保存 '),
        );

        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){

            $fc_id = trim($_POST['fc_id']);
            $pc_sid = trim($_POST['pc_sid']);
            $pc_attention = trim($_POST['pc_attention']);
            //$staff = trim($_POST['staff']);
            $form_date = trim($_POST['form_date']);
            $about = trim($_POST['about']);
            $remark = trim($_POST['remark']);
            $detail = trim($_POST['detail']);
            $fty_reply = trim($_POST['fty_reply']);
            $mod_date = $fty_reply_date = dateMore();
            $mod_by = $fty_staff = $_SESSION['ftylogininfo']['aName'];

            $result = $mysql->q('update fty_complaint_form set fty_sid = ?, send_to = ?, form_date = ?, about = ?, remark = ?, detail = ?, fty_reply = ?, fty_staff = ?, fty_reply_date = ?, mod_date = ?, mod_by = ? where fc_id = ?', $pc_sid, $pc_attention, $form_date, $about, $remark, $detail, $fty_reply, $fty_staff, $fty_reply_date, $mod_date, $mod_by, $fc_id);
            if($result){

                //add action log
                /*$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['ftylogininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_MOD_CUSTOMER, $_SESSION["ftylogininfo"]["aName"]." <i>modify customer</i> ID:'".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_CUSTOMER_S, "", "", 0);*/

                $myerror->ok('修改 Factory Complaint Form 成功!', 'search_factory_complaint_form&page=1');
            }else{
                $myerror->error('修改 Factory Complaint Form 失败', 'BACK');
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
        <h1 class="green">投诉报告<em>*号为必填项</em></h1>
        <fieldset class="center2col" style="width:60%">
            <legend class='legend'>Modify</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%">
                <tr>
                    <td width="33%"><? $goodsForm->show('fc_id');?></td>
                    <td width="33%"></td>
                    <td width="33%"></td>
                </tr>
                <tr>
                    <td width="33%"><? $goodsForm->show('pc_sid');?></td>
                    <td width="33%"><? $goodsForm->show('pc_attention');?></td>
                    <td width="33%"><div class="set"><label class="formtitle" for="staff">负责同事</label><div class="formfield"><?=isset($mod_result['staff'])?$mod_result['staff']:''?></div></div></td>
                </tr>
                <tr>
                    <td width="33%"><? $goodsForm->show('form_date');?></td>
                    <td width="33%"><? $goodsForm->show('about');?></td>
                    <td width="33%"><? $goodsForm->show('remark');?></td>
                </tr>
                <tr>
                    <td colspan="3"><? $goodsForm->show('detail');?></td>
                </tr>
                <tr valign="top">
                    <td colspan="2"><? $goodsForm->show('fty_reply');?></td>
                    <td width="33%"><div class="set"><label class="formtitle" for="staff">回应人</label><div class="formfield"><?=isset($mod_result['fty_staff'])?$mod_result['fty_staff']:''?></div></div></td>
                </tr>
                <tr valign="top">
                    <td width="33%"><div class="set"><label class="formtitle" for="fty_reply_date">回应日期</label><div class="formfield"><?=isset($mod_result['fty_reply_date'])?$mod_result['fty_reply_date']:'none'?></div></div></td>
                </tr>
                <tr id="reply_satisfaction">
                    <td colspan="3"><? $goodsForm->show('reply_satisfaction');?></td>
                </tr>
                <tr>
                    <td colspan="3"><? $goodsForm->show('reply_remark');?></td>
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
<style>
    tr#reply_satisfaction div.set{
        width:700px;
    }
</style>

<script>
    $(function(){
        selectSupplier("pc_");
    })
</script>