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
        $rtn = $mysql->q('delete from fty_chargeback_form where fcb_id = ?', $_GET['delid']);
        if($rtn){

            //add action log
            /*$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_CUSTOMER, $_SESSION["logininfo"]["aName"]." <i>delete customer</i> ID:'".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_CUSTOMER_S, "", "", 0);*/

            $myerror->ok('删除 Factory Chargeback Form 成功!', 'com-search_factory_chargeback_form&page=1');
        }else{
            $myerror->error('删除 Factory Chargeback Form 失败!', 'com-search_factory_chargeback_form&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM fty_chargeback_form WHERE fcb_id = ?', $_GET['modid']);
        }else{
            die('Need modid!');
        }
        //因為一開始沒有attention的選項所以要加上
        $pc_attention = array(array($mod_result['send_to'], $mod_result['send_to']));

        $goodsForm = new My_Forms();
        $formItems = array(

            'fcb_id' => array('title' => '扣款报告编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['fcb_id'])?$mod_result['fcb_id']:''),
            'pc_sid' => array('title' => '工厂', 'type' => 'select', 'options' => get_supplier(), 'required' => 1, 'value' => isset($mod_result['fty_sid'])?$mod_result['fty_sid']:''),
            'pc_attention' => array('title' => '致', 'type' => 'select', 'required' => 1, 'options' => $pc_attention, 'value' => isset($mod_result['send_to'])?$mod_result['send_to']:''),
            /*'staff' => array('title' => '负责同事', 'type' => 'text', 'required' => 1, 'value' => isset($mod_result['staff'])?$mod_result['staff']:''),*/
            'pcid' => array('title' => 'PO', 'type' => 'select', 'options' => get_pcid(), 'value' => isset($mod_result['pcid'])?$mod_result['pcid']:'', 'required' => 1),
            'form_date' => array('title' => '日期', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['form_date'])?date('Y-m-d', strtotime($mod_result['form_date'])):''),

            'remark' => array('title' => '备注', 'type' => 'text', 'minlen' => 1, 'maxlen' => 100, 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
            'reason' => array('title' => '扣款内容原因', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'value' => isset($mod_result['reason'])?$mod_result['reason']:''),
            'money' => array('title' => '扣款金额(RMB)', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['money'])?$mod_result['money']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' 保存 '),
        );

        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){

            $fcb_id = trim($_POST['fcb_id']);
            $pc_sid = trim($_POST['pc_sid']);
            $pc_attention = trim($_POST['pc_attention']);
            //$staff = trim($_POST['staff']);
            $pcid = trim($_POST['pcid']);
            $form_date = trim($_POST['form_date']);
            $remark = trim($_POST['remark']);
            $reason = trim($_POST['reason']);
            $money = trim($_POST['money']);
            $mod_date = dateMore();
            $mod_by = $_SESSION['logininfo']['aName'];

            $rs = $mysql->q('update fty_chargeback_form set fty_sid = ?, send_to = ?, staff = ?, pcid = ?, form_date = ?, remark = ?, reason = ?,money = ?, mod_date = ?, mod_by = ? where fcb_id = ?', $pc_sid, $pc_attention, $mod_by, $pcid, $form_date, $remark, $reason, $money, $mod_date, $mod_by, $fcb_id);
            if($rs){

                //add action log
                /*$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_MOD_CUSTOMER, $_SESSION["logininfo"]["aName"]." <i>modify customer</i> ID:'".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_CUSTOMER_S, "", "", 0);*/

                $myerror->ok('修改 Factory Chargeback Form 成功!', 'com-search_factory_chargeback_form&page=1');
            }else{
                $myerror->error('修改 Factory Chargeback Form 失败', 'BACK');
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
        <h1 class="green">Factory Chargeback Form<em>* item must be filled in</em></h1>
        <fieldset class="center2col" style="width:60%">
            <legend class='legend'>Modify</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%">
                <tr>
                    <td width="33%"><? $goodsForm->show('fcb_id');?></td>
                    <td width="33%"></td>
                    <td width="33%"></td>
                </tr>
                <tr>
                    <td width="33%"><? $goodsForm->show('pc_sid');?></td>
                    <td width="33%"><? $goodsForm->show('pc_attention');?></td>
                    <td width="33%"><div class="set"><label class="formtitle" for="staff">负责同事</label><div class="formfield"><?=$mod_result['staff']?></div></div></td>
                </tr>
                <tr>
                    <td width="33%"><? $goodsForm->show('pcid');?></td>
                    <td width="33%"><? $goodsForm->show('form_date');?></td>
                    <td width="33%"><? $goodsForm->show('remark');?></td>
                </tr>
                <tr>
                    <td colspan="3"><? $goodsForm->show('reason');?></td>
                </tr>
                <tr>
                    <td width="33%"><? $goodsForm->show('money');?></td>
                    <td width="33%"></td>
                    <td width="33%"></td>
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

<script>
    $(function(){
        selectSupplier("pc_");
    })
</script>