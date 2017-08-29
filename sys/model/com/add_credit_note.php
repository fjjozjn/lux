<?php

/*
change log


*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();


$goodsForm = new My_Forms();
$formItems = array(

    'cn_pvid' => array('title' => 'Proforma NO.', 'type' => 'select', 'options' => $pvid, 'required' => 1),
    'cn_cid' => array('title' => 'Customer', 'type' => 'select', 'options' => get_customer(), 'required' => 1),
    'cn_attention' => array('title' => 'Attention', 'type' => 'select', 'options' => '', 'required' => 1),
    'cn_reference' => array('title' => 'Customer PO No.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
    'cn_tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
    'cn_fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
    'cn_currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type()),
    'cn_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"'),
    'cn_address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2),
    'cn_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5),

    //20131101 把required都去掉了，不然删除item后提交不了
    'description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2),
    'amount' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
    //$myerror->info($_POST);
    //die();

    $cn_no = autoGenerationID();
    /*$pvid = $_POST['pvid'];

    $_POST = $mysql->qone('select send_to, attention, reference, tel, fax, currency, remark, remarks from proforma where pvid = ?', $pvid);
    if($_POST){*/
    //20130524 改为用户自己输入

        $send_to = combineSendTo($_POST['cn_cid'], '', $_POST['cn_address']);//$_POST['cn_send_to'];
        $cid = $_POST['cn_cid'];
        $attention = $_POST['cn_attention'];
        $reference = $_POST['cn_reference'];
        $tel = $_POST['cn_tel'];
        $fax = $_POST['cn_fax'];
        $currency = $_POST['cn_currency'];
        $remark = $_POST['cn_remark'];
        $remarks = $_POST['cn_remarks'];
        $pvid = $_POST['cn_pvid'];

        $i = 0;
        $prev_num = 11;//第一个post的是form的标识串，还有10个表单项
        $last_num = 1;//后面的post，有个submit
        $cn_item = array();
        foreach( $_POST as $v){
            if( $i < $prev_num){
                $i++;
            }elseif($i >= count($_POST) - $last_num){
                break;
            }else{
                $cn_item[] = $v;
                $i++;
            }
        }

        $in_date = dateMore();
        $mod_date = '';
        $printed_date = '';
        $created_by = $_SESSION["logininfo"]["aName"];
        $mod_by = '';
        $printed_by = '';

        //这个是设置每个ITEM的元素个数
        $each_item_num = 2;
        $cn_item_num = intval(count($cn_item)/$each_item_num);

        $description = array();
        $amount = array();
        $index = 0;

        for($j = 0; $j < $cn_item_num; $j++){
            $description[] = $cn_item[$index++];
            $amount[] = str_replace(',', '', ($cn_item[$index] != '')?$cn_item[$index++]:0);
        }

        //die();

        //判断是否自动生成的cn_no已存在，因为存在的话由于数据库限制，就会新增失败
        if(!($mysql->q('select cn_no from credit_note where cn_no = ?', $cn_no))){
            //20130725 不允许同一个pvid开多个单，因为在modify pi与invoice页面中的统计Credit只计算一个单
            if(!($mysql->q('select id from credit_note where pvid = ?', $pvid))){
                $result = $mysql->q('insert into credit_note values (NULL, '.moreQm(17).')', $cn_no, $cid, $send_to, $attention, $tel, $fax, $reference, $remark, $in_date, $currency, $created_by, $mod_by, $printed_by, $mod_date, $printed_date, $remarks,
                    $pvid);
                if($result){
                    for($k = 0; $k < $cn_item_num; $k++){
                        $rtn = $mysql->q('insert into credit_note_item values (NULL, '.moreQm(3).')', $cn_no, $description[$k], $amount[$k]);
                    }

                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], $ip_real
                        , ACTION_LOG_SYS_ADD_CREDIT_NOTE, $_SESSION["logininfo"]["aName"]." <i>add credit note</i> '".$cn_no."' in sys", ACTION_LOG_SYS_ADD_CREDIT_NOTE_S, "", "", 0);

                    $myerror->ok('Add Credit Note success!', 'com-search_credit_note&page=1');
                }else{
                    $myerror->error('Add Credit Note failure!', 'BACK');
                }
            }else{
                $myerror->error('所选的 Proforma NO. 已经开过 Credit Note 了，请不要重复开单。', 'BACK');
            }
        }else{
            $myerror->error('输入的 Credit Note NO.已存在，新增 Credit Note 失败', 'BACK');
        }
    /*}else{
        $myerror->error('选择的 Proforma NO.不存在，新增 Credit Note 失败', 'com-add_credit_note');
    }*/
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
    <h1 class="green">CREDIT NOTE<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>Add Credit Note</legend>
        <?php
        $goodsForm->begin();
        ?>
        <table width="100%" id="table">
            <tr class="formtitle">
                <td width="25%"><div class="set"><label class="formtitle">Credit Note NO.</label><br />(autogeneration)</div></td>
                <td width="25%"><? $goodsForm->show('cn_cid');?></td>
                <td width="25%"><? $goodsForm->show('cn_attention');?></td>
                <td width="25%"><? $goodsForm->show('cn_tel');?></td>
            </tr>
            <tr>
                <td width="25%" valign="top"><? $goodsForm->show('cn_fax');?></td>
                <td width="25%" colspan="2"><? $goodsForm->show('cn_address');?></td>
                <td width="25%"><? $goodsForm->show('cn_currency');?></td>
            </tr>
            <tr>
                <td width="25%" colspan="2"><? $goodsForm->show('cn_remark');?></td>
                <td width="25%"><? $goodsForm->show('cn_reference');?></td>
                <td width="25%"><? $goodsForm->show('cn_pvid');?></td>
            </tr>
            <tr valign="top">
                <td width="25%" colspan="2"><? $goodsForm->show('cn_remarks');?></td>
            </tr>
        </table>
        <div class="line"></div>
        <div style="margin-left:28px;">
<!--            <label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>-->
            <table width="80%" id="tableDnD">
                <tbody id="tbody">
                <tr class="formtitle nodrop nodrag">
<!--                    <td width="3%"></td>-->
                    <td width="50%">Description</td>
                    <td width="30%">Amount</td>
                    <td width="10%">&nbsp;</td>
<!--                    <td width="3%">&nbsp;</td>-->
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
<!--                    <td class="dragHandle"></td>-->
                    <td><? $goodsForm->show('description');?></td>
                    <td valign="top"><? $goodsForm->show('amount');?></td>
                    <td align="left"><img title="添加" style="opacity: 0.5;" onclick="addCreditNoteItem()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
<!--                    <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delCreditNoteItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td>-->
                </tr>
                </tbody>
                <tr>
<!--                    <td>&nbsp;</td>-->
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </table>

        </div>

        <!--div class="line"></div>
        <table>
            <tr>
                <td width="50%">
                    <?
                    //$goodsForm->show('cn_remarks');
                    ?>
                </td>
            </tr>
        </table-->
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
        selectCustomer("cn_");
        //table tr层表单可拖动
        //$("#tableDnD").tableDnD({dragHandle: ".dragHandle"});
    })
</script>
