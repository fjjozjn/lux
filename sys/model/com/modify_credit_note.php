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
        //由於指定了foreign key，所以要先刪item裏的內容
        $rtn1 = $mysql->q('delete from credit_note_item where cn_no = ?', $_GET['delid']);
        $rtn2 = $mysql->q('delete from credit_note where cn_no = ?', $_GET['delid']);
        if($rtn2){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_CREDIT_NOTE, $_SESSION["logininfo"]["aName"]." <i>delete credit note</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_CREDIT_NOTE_S, "", "", 0);

            $myerror->ok('删除 Credit Note 成功!', 'com-search_credit_note&page=1');
        }else{
            $myerror->error('删除 Credit Note 失败!', 'com-search_credit_note&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM credit_note WHERE cn_no = ?', $_GET['modid']);
            //從send_to中拆出地址顯示
            $mod_result['address'] = substr($mod_result['send_to'], stripos($mod_result['send_to'], "\r\n"));

            $item_result = $mysql->q('SELECT description, amount FROM credit_note_item WHERE cn_no = ?', $_GET['modid']);
            $cn_item_rtn = $mysql->fetch();
            //$myerror->info($cn_item_rtn);die();
            $cn_item_num = count($cn_item_rtn);
            //$myerror->info($cn_item_num);
            //因為一開始沒有attention的選項所以要加上
            $mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
        }else{
            die('Need modid!');
        }

        $goodsForm = new My_Forms();
        $formItems = array(

            //'cn_no' => array('title' => 'Credit Note NO.', 'type' => 'text', 'readonly' => 'readonly', 'value' => isset($mod_result['cn_no'])?$mod_result['cn_no']:''),
            'cn_pvid' => array('title' => 'Proforma Invoice NO.', 'type' => 'select', 'options' => $pvid, 'value' => isset($mod_result['pvid'])?$mod_result['pvid']:'', 'required' => 1),
            'cn_cid' => array('title' => 'Customer', 'type' => 'select', 'options' => get_customer(), 'value' => isset($mod_result['cid'])?$mod_result['cid']:''),
            'cn_attention' => array('title' => 'Attention', 'type' => 'select', 'options' => $mod_customer_contact, 'value' => isset($mod_result['attention'])?$mod_result['attention']:''),
            'cn_reference' => array('title' => 'Customer PO No.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['reference'])?$mod_result['reference']:''),
            'cn_tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['tel'])?$mod_result['tel']:''),
            'cn_fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['fax'])?$mod_result['fax']:''),
            'cn_currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'value' => isset($mod_result['currency'])?$mod_result['currency']:''),

            'cn_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
            'cn_address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2, 'value' => isset($mod_result['address'])?$mod_result['address']:''),
            'cn_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => isset($mod_result['remarks'])?$mod_result['remarks']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        //序号从0开始
        for($i = 0; $i < $cn_item_num; $i++){
            //20131101 把required都去掉了，不然删除item后提交不了
            $formItems['description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'value' => isset($cn_item_rtn[$i]['description'])?$cn_item_rtn[$i]['description']:'');
            $formItems['amount'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($cn_item_rtn[$i]['amount'])?$cn_item_rtn[$i]['amount']:'');
        }

        //最后一个
        //$formItems['description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'required' => 1);
        //$formItems['amount'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1);

        //$myerror->info($formItems);
        //die();

        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){
            //$myerror->info($_POST);

//            $cn_no = $_POST['cn_no'];
            $cn_pvid = $_POST['cn_pvid'];
            $cn_cid = $_POST['cn_cid'];
            $cn_send_to = combineSendTo($_POST['cn_cid'], '', $_POST['cn_address']);//$_POST['cn_send_to'];
            $cn_attention = $_POST['cn_attention'];
            $cn_reference = $_POST['cn_reference'];
            $cn_tel = $_POST['cn_tel'];
            $cn_fax = $_POST['cn_fax'];
            $cn_currency = $_POST['cn_currency'];
            $cn_remark = $_POST['cn_remark'];
            //这个变量没用的，写着方便计算下面的数，不然忘了这个以后麻烦
            $cn_address = $_POST['cn_address'];

            //这个是在最后提交的哟
            $cn_remarks = $_POST['cn_remarks'];

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

            $mod_by = $_SESSION["logininfo"]["aName"];
            $mod_date = dateMore();

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

            /*fb($_POST);
            fb($cn_item);
            fb($cn_item_num);
            fb($description);
            fb($amount);
            die();*/

            $result = $mysql->q('update credit_note set send_to = ?, attention = ?, tel = ?, fax = ?, reference = ?, remark = ?, currency = ?, mod_by = ?, mod_date = ?, remarks = ?, pvid = ? where cn_no = ?', $cn_send_to, $cn_attention, $cn_tel, $cn_fax, $cn_reference, $cn_remark, $cn_currency, $mod_by, $mod_date, $cn_remarks, $cn_pvid, $_GET['modid']);

            //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
            if($result !== false){
                $rtn = $mysql->q('delete from credit_note_item where cn_no = ?', $_GET['modid']);
                for($k = 0; $k < $cn_item_num; $k++){
                    $rtn = $mysql->q('insert into credit_note_item values (NULL, '.moreQm(3).')', $_GET['modid'], $description[$k], $amount[$k]);
                }

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_MOD_CREDIT_NOTE, $_SESSION["logininfo"]["aName"]." <i>modify credit note</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_CREDIT_NOTE_S, "", "", 0);

                $myerror->ok('修改 Credit Note 成功!', 'com-search_credit_note&page=1');
            }else{
                $myerror->error('修改 Credit Note 失败', 'BACK');
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
        <h1 class="green">CREDIT NOTE<em>* item must be filled in</em></h1>
        <fieldset>
            <legend class='legend'>Action</legend>
            <div style="margin-left:28px;"><!--<a class="button" href="model/com/proforma_pdf.php?pvid=<?/*=$_GET['modid']*/?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a>--></div>
        </fieldset>
        <fieldset>
            <legend class='legend'>Modify Credit Note</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><div class="set"><label class="formtitle">Credit Note NO.</label><br /><?=isset($mod_result['cn_no'])?$mod_result['cn_no']:'Error'?></div></td>
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
                <!--<label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>-->
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
                    <?
                    for($i = 0; $i < $cn_item_num; $i++){
                        ?>
                        <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                            <!--                    <td class="dragHandle"></td>-->
                            <td><? $goodsForm->show('description'.$i);?></td>
                            <td valign="top"><? $goodsForm->show('amount'.$i);?></td>
                            <td align="left"><img title="添加" style="opacity: 0.5;" onclick="addCreditNoteItem()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
                            <? if($i != 0){ ?>
                                <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delCreditNoteItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td>
                            <? }?>
                        </tr>
                    <?
                    }
                    ?>

                    <? //这里是为了多出一个空行，本来是为了方便新增，但是不输入的话就不能提交，有点麻烦，所以去掉了 ?>
                    <? /*
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                        <!--                    <td class="dragHandle"></td>-->
                        <td><? $goodsForm->show('description'.$i);?></td>
                        <td valign="top"><? $goodsForm->show('amount'.$i);?></td>
                        <td align="left"><img title="添加" style="opacity: 0.5;" onclick="addCreditNoteItem()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
                        <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delCreditNoteItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td>
                    </tr>
                    */ ?>


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
            <div class="line"></div>
            <?
            $goodsForm->show('submitbtn');
            ?>
        </fieldset>
        <fieldset>
            <legend class='legend'>Action</legend>
            <div style="margin-left:28px;"><!--<a class="button" href="model/com/proforma_pdf.php?pvid=<?/*=$_GET['modid']*/?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a>--></div>
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

<?
}
?>