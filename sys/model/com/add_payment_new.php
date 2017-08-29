<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}


$goodsForm = new My_Forms();
$formItems = array(
    'bank_ref' => array('title' => 'Bank Ref', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
    'bank_acc' => array('title' => 'Bank Acc', 'type' => 'select', 'options' => get_bank_acc(), 'required' => 1, 'addon' => 'style="width:200px"'),
    'value_date' => array('title' => 'Value Date', 'type' => 'text', 'restrict' => 'date', 'required' => 1),
    'remitting_amount' => array('title' => 'Remitting Amount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1),
    //20130801 去掉method
    //'method' => array('title' => 'Method', 'type' => 'select', 'options' => get_payment_method(), 'required' => 1),
    'currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1, 'addon' => 'style="width:200px"'),
    'total_bank_charges' => array('title' => 'Total Bank Charges (HKD)', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1),
    'remitter' => array('title' => 'Remitter', 'type' => 'select', 'options' => get_customer(), 'required' => 1, 'addon' => 'style="width:200px"'),//还不清楚是什么
    'message_remark' => array('title' => 'Message Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:200px"'),

    'pi_or_cn' => array('type' => 'select', 'options' => get_pi_or_cn_type(), 'nostar' => true, 'addon' => 'style="width:100px" onchange="select_pi_or_cn(this)" class="special"'),
    'pi_or_cn_no' => array('type' => 'select', 'options' => '', 'nostar' => true, 'addon' => 'style="width:150px" onchange="select_pi_or_cn_no(this)" class="special"'),
    'total' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:100px"', 'nostar' => true, 'readonly' => 'readonly'),
    'outstanding' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:100px"', 'nostar' => true, 'readonly' => 'readonly'),
    'received' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:100px" onblur="received_blur(this)"', 'nostar' => true),
    'balance' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:100px"', 'nostar' => true, 'readonly' => 'readonly'),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){

    //die();

//fb($_POST);die();

    $py_no = autoGenerationID();

    $bank_ref = $_POST['bank_ref'];
    $bank_acc = $_POST['bank_acc'];
    $value_date = $_POST['value_date'];
    $remitting_amount = number_format($_POST['remitting_amount'], 2, '.', '');
    $currency = $_POST['currency'];
    $total_bank_charges = number_format($_POST['total_bank_charges'], 2, '.', '');
    $remitter = $_POST['remitter'];
    $message_remark = $_POST['message_remark'];

    //******
    //第一个post的是form的标识串，还有8个表单项，所以是9
    $prev_num = 9;
    //后面的post，有个submit
    $last_num = 1;

    $i = 0;
    $pn_item = array();
    foreach( $_POST as $v){
        if( $i < $prev_num){
            $i++;
        }elseif($i >= count($_POST) - $last_num){
            break;
        }else{
            $pn_item[] = $v;
            $i++;
        }
    }
    //这个是设置每个ITEM的元素个数
    $each_item_num = 6;
    $pn_item_num = intval(count($pn_item)/$each_item_num);
    //******

//fb($pn_item);
    $in_date = dateMore();
    $created_by = $_SESSION["logininfo"]["aName"];

    $pi_or_cn = array();
    $pi_or_cn_no = array();
    $total = array();
    $outstanding = array();
    $received = array();
    $balance = array();

    $index = 0;

    for($j = 0; $j < $pn_item_num; $j++){
        $pi_or_cn[] = $pn_item[$index++];
        $pi_or_cn_no[] = trim($pn_item[$index++]);//20130805 不知道为什么前面会有 /r/n，所以加了trim
        $total[] = number_format($pn_item[$index++], 2, '.', '');
        $outstanding[] = number_format($pn_item[$index++], 2, '.', '');
        $received[] = number_format($pn_item[$index++], 2, '.', '');
        $balance[] = number_format($pn_item[$index++], 2, '.', '');
    }

    /*fb($pi_or_cn);
    fb($total);
    fb($total);
    fb($outstanding);
    fb($received);
    fb($balance);
    die();*/

    //默认填上最后修改时间与人，就是创建时间与人，因为search的时候显示的是Last update
    $rs = $mysql->q('insert into payment_new values (NULL, '.moreQm(15).')', $py_no, $bank_ref, $bank_acc, $value_date, $remitting_amount, '', $currency, $total_bank_charges, $remitter, $message_remark, $in_date, $in_date, $created_by,
        $created_by, '(I)');

    //20140324 插入失败时，因为是自增ID，所以会有ID空档，这里解决这个问题，但是这个操作会锁表，插入频繁时不能用
    //但是PY_NO的ID空档不是这个问题
    //$mysql->q('ALTER TABLE payment_new AUTO_INCREMENT = 1');

    if($rs){
        //20130806 add只是给Accounting Department用的，也就不添加item部分了
/*        for($k = 0; $k < $pn_item_num; $k++){
            $mysql->q('insert into payment_item_new values (NULL, '.moreQm(7).')', $py_no, $pi_or_cn[$k], $pi_or_cn_no[$k], $total[$k], $outstanding[$k], $received[$k], $balance[$k]);
        }*/

        //20130822
        //send email to admin
        require_once(ROOT_DIR.'class/Mail/mail.php');
        $account_info = array('date' => date('Y-m-d'));
        //发送的信息
        $info = $created_by . ' 添加了 ' . $py_no . ' ，请去看看。';

        //通过 remitter 获得 group user
        $rs = $mysql->q('select AdminName, AdminEmail from tw_admin where AdminEnabled = 1 and AdminName = (select created_by from customer where cid = ?)', $remitter);
        if($rs){
            $rtn = $mysql->fetch();
            foreach($rtn as $v){
                send_mail($v['AdminEmail'], $v['AdminName'], 'Payment Advice', $info,
                    $account_info);
            }
        }

        //add action log
        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['logininfo']['aID'], $ip_real
            , ACTION_LOG_SYS_ADD_PAYMENT_ADVICE, $_SESSION["logininfo"]["aName"]." <i>add payment advice</i> '".$py_no."' in sys", ACTION_LOG_SYS_ADD_PAYMENT_ADVICE_S, "", "", 0);

        $myerror->ok('Add Payment Advice success !', 'com-search_payment_new&page=1');
    }else{
        $myerror->error('Add Payment Advice failure !', 'BACK');
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
    <h1 class="green">Payment Advice<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>Add Payment Advice</legend>
        <?php
        $goodsForm->begin();
        ?>
        <div style="margin-left:28px; color: #FF0000"># For Accounting Department</div>
        <table width="100%" id="table">
            <tr class="formtitle">
                <td width="25%"><? $goodsForm->show('bank_ref');?></td>
                <td width="25%"><? $goodsForm->show('bank_acc');?></td>
                <td width="25%"><? $goodsForm->show('value_date');?></td>
                <td width="25%"><? $goodsForm->show('remitting_amount');?></td>
            </tr>
            <tr>
<!--                <td width="25%">--><?// $goodsForm->show('method');?><!--</td>-->
                <td width="25%" valign="top"><? $goodsForm->show('currency');?></td>
                <td width="25%" valign="top"><? $goodsForm->show('total_bank_charges');?></td>
                <td width="25%" valign="top"><? $goodsForm->show('remitter');?></td>
                <td width="25%" valign="top"><? $goodsForm->show('message_remark');?></td>
            </tr>
<!--            <tr>
                <td colspan="2"><?/* $goodsForm->show('message_remark');*/?></td>
            </tr>-->
        </table>

        <div class="line"></div>
        <br />
        <? //20130816 去掉下面这段，在Add的时候不能填下面的信息，是为了让Accounting Department先填好收款信息后，Sales Department在modify的页面才能填下面的信息。（下面屏蔽的信息，!--在标签里的是需要的，而<!-- 在标签外的是原来就屏蔽了的） ?>
        <!--div style="margin-left:28px;"-->
            <!--            <label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>-->
            <!--div style="color: #FF0000"># For Sales Department</div>
            <table width="100%" id="tableDnD">
                <tbody id="tbody">
                <tr class="formtitle nodrop nodrag"-->
                    <!--                    <td width="3%"></td>-->
                    <!--td width="15%">PI/CN</td-->
                    <!--                    <td width="18%">Description()</td>-->
                    <!--td width="15%">PI/CN #</td>
                    <td width="10%">Total(USD)</td>
                    <td width="10%">Outstanding</td>
                    <td width="10%">Received</td>
                    <td width="10%">Balance</td>
                    <td width="4%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                </tr>
                <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)"-->
                    <!--                    <td class="dragHandle"></td>-->
                    <!--td><? //$goodsForm->show('pi_or_cn');?></td>
                    <td><? //$goodsForm->show('pi_or_cn_no');?></td>
                    <td><? //$goodsForm->show('total');?></td>
                    <td><? //$goodsForm->show('outstanding');?></td>
                    <td><? //$goodsForm->show('received');?></td>
                    <td><? //$goodsForm->show('balance');?></td>
                    <td><img title="添加" style="opacity: 0.5;" onclick="addPaymentItemNew(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"></td-->
                    <!--                    <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delWarehouseItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td>-->
                    <!--td>&nbsp;</td>
                </tr>
                </tbody>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td align="center">Total : </td>
                    <td><div id="total" class="num_td">0</div></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </div>
        <div class="line"></div-->
        <?
        $goodsForm->show('submitbtn');
        ?>

    </fieldset>
    <?
    $goodsForm->end();
}
?>

