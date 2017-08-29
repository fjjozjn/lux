<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//20140502 admin与普通用户能修改的不同
$is_admin = false;
if(isSysAdmin()){
    $is_admin = true;
}

judgeUserPermNew( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        $hints = '';
        $pi_array = array();
        $rs = $mysql->q('select pi_or_cn_no from payment_item_new where pi_or_cn = ? and py_no = ?', 'PI',
            $_GET['delid']);
        if($rs){
            $rtn = $mysql->fetch();
            foreach($rtn as $v){
                $pi_array[] = $v['pi_or_cn_no'];
            }
        }

        //20151020 不删除，只改状态为delete
        $mysql->q('update payment_new set istatus = ? where py_no = ?', 'delete', $_GET['delid']);

        //由於指定了foreign key，所以要先刪item裏的內容
        //$rtn1 = $mysql->q('delete from payment_item_new where py_no = ?', $_GET['delid']);
        //$rtn2 = $mysql->q('delete from payment_new where py_no = ?', $_GET['delid']);

        //20140324 删除数据时，因为是自增ID，所以下次插入数据时会跳过这个ID，这里解决这个问题，但是这个操作会锁表，插入频繁时不能用
        //但是PY_NO的ID空档不是这个问题
        //$mysql->q('ALTER TABLE payment_new AUTO_INCREMENT = 1');

        $mysql->q('delete from payment where py_no = ?', $_GET['delid']);
        if($rtn2){
            //fb($pi_array);
            $hints .= update_pi_status($pi_array);
            //fb($hints);

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_PAYMENT_ADVICE, $_SESSION["logininfo"]["aName"]." <i>delete payment advice</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_PAYMENT_ADVICE_S, "", "", 0);

            $myerror->ok('Delete Payment success! '.$hints, 'com-search_payment_new&page=1');
        }else{
            $myerror->error('Delete Payment failure!', 'com-search_payment_new&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM payment_new WHERE py_no = ?', $_GET['modid']);

            $item_result = $mysql->q('SELECT * FROM payment_item_new WHERE py_no = ?', $_GET['modid']);
            $py_item_rtn = $mysql->fetch();

            //20130827 因为函数取出的pi是不包含P和C的，所以，导致按modify进来查看的时候也没有这个选项显示，所以在选项框的选项里另外加上已经保存在payment_item_new里的项，以供查看，但不会使在modify 添加的时候把P和C的也包含进来，导致选项过多
            $pi_array = get_payment_no_py();

            //total 是 remitting_amount 减去 所有item的received和
            $total = 0;
            //20140106 加 total received 在modify页面底部
            //20140404 算$total_received PI为加CN为减
            $total_received = 0;
            //20130925 没有item也要能显示total
            if($item_result){
                foreach($py_item_rtn as $v){

                    //20140520 可以填负号，所以都用加
                    $total_received += $v['received'];
/*                    if($v['pi_or_cn'] == 'PI'){
                        $total_received += $v['received'];
                    }else if($v['pi_or_cn'] == 'CN' || $v['pi_or_cn'] == 'CUSTOMER BANK CHARGE'){
                        $total_received -= $v['received'];
                    }else{
                        $total_received += $v['received'];
                    }*/

                    $pi_array[$v['pi_or_cn_no']] = array($v['pi_or_cn_no'], $v['pi_or_cn_no']);
                }
                $total = $mod_result['remitting_amount'] - $total_received;
            }else{
                $total = $mod_result['remitting_amount'];
            }

            $py_item_num = count($py_item_rtn);
            //fb($py_item_num);
        }else{
            die('Need modid!');
        }

        $goodsForm = new My_Forms();
        $formItems = array(

            'bank_ref' => array('title' => 'Bank Ref', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['bank_ref'])?$mod_result['bank_ref']:'', 'readonly' => ($is_admin?false:'readonly')),
            'bank_acc' => array('title' => 'Bank Acc', 'type' => 'select', 'options' => get_bank_acc(), 'required' => 1, 'addon' => 'style="width:200px"', 'value' => isset($mod_result['bank_acc'])?$mod_result['bank_acc']:'', 'readonly' => ($is_admin?false:'readonly')),
            'value_date' => array('title' => 'Value Date', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['value_date'])?date('Y-m-d', strtotime($mod_result['value_date'])):'', 'readonly' => ($is_admin?false:'readonly')),
            'remitting_amount' => array('title' => 'Remitting Amount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1, 'value' => isset($mod_result['remitting_amount'])?number_format($mod_result['remitting_amount'], 2, '.', ''):'', 'readonly' => ($is_admin?false:'readonly')),
            //20130801 去掉method
            //'method' => array('title' => 'Method', 'type' => 'select', 'options' => get_payment_method(), 'required' => 1),
            'currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1, 'addon' => 'style="width:200px"', 'value' => isset($mod_result['currency'])?$mod_result['currency']:'', 'readonly' => ($is_admin?false:'readonly')),
            'total_bank_charges' => array('title' => 'Total Bank Charges (HKD)', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1, 'value' => isset($mod_result['total_bank_charges'])?number_format($mod_result['total_bank_charges'], 2, '.', ''):'', 'readonly' => ($is_admin?false:'readonly')),
            'remitter' => array('title' => 'Remitter', 'type' => 'select', 'options' => get_customer(), 'required' => 1, 'addon' => 'style="width:200px"', 'value' => isset($mod_result['remitter'])?$mod_result['remitter']:'', 'readonly' => ($is_admin?false:'readonly')),//还不清楚是什么
            'message_remark' => array('title' => 'Message Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:200px"', 'value' => isset($mod_result['message_remark'])?$mod_result['message_remark']:'', 'readonly' => ($is_admin?false:'readonly')),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        //序号从0开始
        for($i = 0; $i < $py_item_num; $i++){

            //20130807 为了给Accounting Department能够在下面没有内容的情况下修改上面的内容
            //20131101 required由1改为0了，因为删除了item后提交还会提示让填写被删除的项
            $required = 0;
            if($i == 0){
                $required = 0;
            }

            $formItems['pi_or_cn'.$i] = array('type' => 'select', 'options' => get_pi_or_cn_type(), 'required' => $required, 'nostar' => true, 'addon' => 'style="width:210px" onchange="select_pi_or_cn(this)" class="special"', 'value' => isset($py_item_rtn[$i]['pi_or_cn'])?$py_item_rtn[$i]['pi_or_cn']:'');

            //20130805 要判断加上初始的列表 get_credit_note_no() 或 get_credit_note_no()
            if($py_item_rtn[$i]['pi_or_cn'] == 'PI'){
                $formItems['pi_or_cn_no'.$i] = array('type' => 'select', 'options' => $pi_array, 'required' => $required, 'nostar' => true, 'addon' => 'style="width:150px" onchange="select_pi_or_cn_no(this)" class="special"', 'value' => isset($py_item_rtn[$i]['pi_or_cn_no'])?$py_item_rtn[$i]['pi_or_cn_no']:'');
            }elseif($py_item_rtn[$i]['pi_or_cn'] == 'CN'){
                $formItems['pi_or_cn_no'.$i] = array('type' => 'select', 'options' => get_credit_note_no(), 'required' => $required, 'nostar' => true, 'addon' => 'style="width:150px" onchange="select_pi_or_cn_no(this)" class="special"', 'value' => isset($py_item_rtn[$i]['pi_or_cn_no'])?$py_item_rtn[$i]['pi_or_cn_no']:'');
            }else{
                $formItems['pi_or_cn_no'.$i] = array('type' => 'select', 'options' => '', 'required' => $required, 'nostar' => true, 'addon' => 'style="width:150px" onchange="select_pi_or_cn_no(this)" class="special"');
            }

            $formItems['total'.$i] = array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => $required, 'addon' => 'style="width:100px"', 'nostar' => true, 'value' => isset($py_item_rtn[$i]['total'])?number_format($py_item_rtn[$i]['total'], 2, '.', ''):'', 'readonly' => 'readonly');
            $formItems['outstanding'.$i] = array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => $required, 'addon' => 'style="width:100px"', 'nostar' => true, 'value' => isset($py_item_rtn[$i]['outstanding'])?number_format($py_item_rtn[$i]['outstanding'], 2, '.', ''):'', 'readonly' => 'readonly');
            $formItems['received'.$i] = array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => $required, 'addon' => 'style="width:100px" onblur="received_blur(this)"', 'nostar' => true, 'value' => isset($py_item_rtn[$i]['received'])?number_format($py_item_rtn[$i]['received'], 2, '.', ''):'');
            $formItems['balance'.$i] = array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => $required, 'addon' => 'style="width:100px"', 'nostar' => true, 'value' => isset($py_item_rtn[$i]['balance'])?number_format($py_item_rtn[$i]['balance'], 2, '.', ''):'', 'readonly' => 'readonly');
        }

        //$myerror->info($formItems);
        //die();

        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){

            //die();

//fb($_POST);die();

            $bank_ref = $_POST['bank_ref'];
            $bank_acc = $_POST['bank_acc'];
            $value_date = $_POST['value_date'];
            $remitting_amount = round($_POST['remitting_amount'], 2);
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
            $py_item = array();
            foreach( $_POST as $v){
                if( $i < $prev_num){
                    $i++;
                }elseif($i >= count($_POST) - $last_num){
                    break;
                }else{
                    $py_item[] = $v;
                    $i++;
                }
            }
            //这个是设置每个ITEM的元素个数
            $each_item_num = 6;
            $py_item_num = intval(count($py_item)/$each_item_num);
            //******

//fb($py_item);
            $mod_date = dateMore();
            $mod_by = $_SESSION["logininfo"]["aName"];

            $pi_or_cn = array();
            $pi_or_cn_no = array();
            $total = array();
            $outstanding = array();
            $received = array();
            $balance = array();

            //20130828 计算total 以便分摊 bank charge
            $total_received = 0;
            //20140106 计算total的总和，要显示在search页面
            $all_total = 0;

            $index = 0;

            for($j = 0; $j < $py_item_num; $j++){
                $pi_or_cn[] = $temp = $py_item[$index++];
                $pi_or_cn_no[] = trim($py_item[$index++]);//20130805 不知道为什么前面会有 /r/n，所以加了trim
                $all_total += $py_item[$index];
                $total[] = @number_format($py_item[$index++], 2, '.', '');
                $outstanding[] = @number_format($py_item[$index++], 2, '.', '');

                //20140520 可以填负号，所以都用加
                $total_received += $received[] = number_format($py_item[$index++], 2, '.', '');
/*                if($temp = 'PI'){
                    $total_received += $received[] = number_format($py_item[$index++], 2, '.', '');
                }else if($temp = 'CN' || $temp == 'CUSTOMER BANK CHARGE'){
                    $total_received -= $received[] = number_format($py_item[$index++], 2, '.', '');
                }else{
                    $total_received += $received[] = number_format($py_item[$index++], 2, '.', '');
                }*/

                $balance[] = number_format($py_item[$index++], 2, '.', '');
            }

            /*            fb($pi_or_cn);
                        fb($total);
                        fb($total);
                        fb($outstanding);
                        fb($received);
                        fb($balance);
                        die();*/
            //fb($total_received);die();

            fb($remitting_amount);

            $result = $mysql->q('update payment_new set bank_ref = ?, bank_acc = ?, value_date = ?, remitting_amount = ?, all_total = ?, currency = ?, total_bank_charges = ?, remitter = ?, message_remark = ?, mod_by = ?, mod_date = ? where py_no = ?', $bank_ref, $bank_acc, $value_date, $remitting_amount, $all_total, $currency, $total_bank_charges, $remitter, $message_remark, $mod_by, $mod_date, $_GET['modid']);

            //$total 为 0 的时候，istatus才改成(C)
            $result_total = $remitting_amount;

            //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
            if($result !== false){

                //20140506 以后可能要划分权限accounting department不能修改下面，而这里的admin应该是有权限修改下面部分的，所以暂时先去掉
                //if(!$is_admin){
                    //20140429 原来是在删除就数据后才去除item号，但是这样已删除的就更新不了了，所以要在 删除payment_item_new 之前就要取出所有pi no
                    $hints = '';
                    $pi_array = array();
                    $rs = $mysql->q('select pi_or_cn_no from payment_item_new where pi_or_cn = ? and py_no = ?', 'PI', $_GET['modid']);
                    if($rs){
                        $rtn = $mysql->fetch();
                        foreach($rtn as $v){
                            $pi_array[] = $v['pi_or_cn_no'];
                        }
                    }

                    $mysql->q('delete from payment_item_new where py_no = ?', $_GET['modid']);
                    //20130824 同时把payment里面的也要删了
                    $mysql->q('delete from payment where py_no = ?', $_GET['modid']);

                    for($k = 0; $k < $py_item_num; $k++){
                        $mysql->q('insert into payment_item_new values (NULL, '.moreQm(7).')', $_GET['modid'], $pi_or_cn[$k], $pi_or_cn_no[$k], $total[$k], $outstanding[$k], $received[$k], $balance[$k]);

                        $result_total -= $received[$k];

                        //20130816 向旧的payment里自动添加，以后旧的payment就不能手动添加了，只能用于查看
                        if($pi_or_cn[$k] == 'PI'){

                            //需确定是填 Deposit 还是 Balance ????????
                            $p_status = '';
                            //remark 还要给个框填吗 ???????
                            $remark = '';
                            //20130828 旧的payment里的bank charge的记录是为了GP的计算
                            $bank_charge = my_formatMoney($total_bank_charges*($received[$k]/$total_received));

                            //py.received => p.amount
                            $mysql->q('insert into payment values (NULL, '.moreQm(10).')', $pi_or_cn_no[$k], $received[$k], '', $bank_ref, $mod_date, $p_status, $remark, $bank_charge, $_GET['modid'], $value_date);

                            //$pi_array[] = $pi_or_cn_no[$k];
                        }

                        //CN不要在这里自动往credit_note 表加记录 20130822
                    }

                    //fb($result_total);
                    //确认 payment_new 的状态，如果不用my_formatMoney则不等于0，为很小的无限不循环小数
                    if(my_formatMoney($result_total) == 0){
                        $mysql->q('update payment_new set istatus = ? where py_no = ?', '(C)', $_GET['modid']);
                        $hints .= $_GET['modid'].' status change from (I) to (C).';
                        //fb($hints);
                    }else{
                        $mysql->q('update payment_new set istatus = ? where py_no = ?', '(I)', $_GET['modid']);
                        $hints .= $_GET['modid'].' status (I).';
                    }

                    //确认 payment_new 里的 pi 的状态
                    //fb($pi_array);
                    $hints .= update_pi_status($pi_array);
                    //fb($hints);

                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], $ip_real
                        , ACTION_LOG_SYS_MOD_PAYMENT_ADVICE, $_SESSION["logininfo"]["aName"]." <i>modify payment advice</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_PAYMENT_ADVICE_S, "", "", 0);
                    $myerror->ok('Modify Payment Advice success! '.$hints, 'com-search_payment_new&page=1');

                /*}else{

                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], $ip_real
                        , ACTION_LOG_SYS_MOD_PAYMENT_ADVICE, $_SESSION["logininfo"]["aName"]." <i>modify payment advice</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_PAYMENT_ADVICE_S, "", "", 0);
                    $myerror->ok('Modify Payment Advice success! ', 'com-search_payment_new&page=1');
                }*/

            }else{
                $myerror->error('Modify Payment Advice failure!', 'BACK');
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
        <h1 class="green">Payment Advice<em>* item must be filled in</em><? show_status_payment_new($mod_result['istatus'])?></h1>
        <!--        <fieldset>
            <legend class='legend'>Action</legend>
            <div style="margin-left:28px;"><a class="button" href="model/com/proforma_pdf.php?pvid=<?/*=$_GET['modid']*/?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a></div>
        </fieldset>-->
        <fieldset>
            <legend class='legend'>Modify Payment Advice</legend>
            <?php
            $goodsForm->begin();
            ?>
            <div style="margin-left:28px; color: #FF0000"># For Accounting Department</div>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><div class="set"><label class="formtitle">Payment Advice NO.</label><br /><?=isset($mod_result['py_no'])?$mod_result['py_no']:'Error'?></div></td>
                    <td width="25%"><? $goodsForm->show('bank_ref');?></td>
                    <td width="25%"><? $goodsForm->show('bank_acc');?></td>
                    <td width="25%"><? $goodsForm->show('value_date');?></td>
                </tr>
                <tr>
                    <td width="25%"><? $goodsForm->show('remitting_amount');?></td>
                    <td width="25%"><? $goodsForm->show('currency');?></td>
                    <td width="25%"><? $goodsForm->show('total_bank_charges');?></td>
                    <td width="25%"><? $goodsForm->show('remitter');?></td>
                </tr>
                <tr>
                    <td width="25%"><? $goodsForm->show('message_remark');?></td>
                </tr>
            </table>
            <div class="line"></div>
            <? //if(!$is_admin){ ?>
                <div style="margin-left:28px;">
                    <!--<label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>-->
                    <div style="color: #FF0000"># For Sales Department</div>
                    <table width="100%" id="tableDnD">
                        <tbody id="tbody">
                        <tr class="formtitle nodrop nodrag">
                            <!--                    <td width="3%"></td>-->
                            <td width="17%">PI/CN/CUSTOMER BANK CHARGE</td>
                            <!--                    <td width="18%">Description()</td>-->
                            <td width="13%">PI/CN #</td>
                            <td width="10%">Total(USD)</td>
                            <td width="10%">Outstanding</td>
                            <td width="10%">Received</td>
                            <td width="10%">Balance</td>
                            <td width="4%">&nbsp;</td>
                            <td width="4%">&nbsp;</td>
                        </tr>
                        <?
                        for($i = 0; $i < $py_item_num; $i++){
                            ?>
                            <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                                <!--                    <td class="dragHandle"></td>-->
                                <td><? $goodsForm->show('pi_or_cn'.$i);?></td>
                                <td><? $goodsForm->show('pi_or_cn_no'.$i);?></td>
                                <td><? $goodsForm->show('total'.$i);?></td>
                                <td><? $goodsForm->show('outstanding'.$i);?></td>
                                <td><? $goodsForm->show('received'.$i);?></td>
                                <td><? $goodsForm->show('balance'.$i);?></td>
                                <td align="left"><img title="添加" style="opacity: 0.5;" onclick="addPaymentItemNew(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
                                <? //if($i != 0){ ?>
                                <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delPaymentItemNew(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td>
                                <? //} ?>
                            </tr>
                        <?
                        }
                        ?>
                        </tbody>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td align="right">Total Received : </td>
                            <td><div id="total" style="padding-left:22px"><?=my_formatMoney($total_received)?></div></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td align="right">Balance : </td>
                            <td><div id="balance" style="padding-left:22px"><?=my_formatMoney($total)?></div></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                </div>
                <div class="line"></div>
            <? //} ?>
            <?
            $goodsForm->show('submitbtn');
            ?>
        </fieldset>
        <!--        <fieldset>
            <legend class='legend'>Action</legend>
            <div style="margin-left:28px;"><a class="button" href="model/com/proforma_pdf.php?pvid=<?/*=$_GET['modid']*/?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a></div>
        </fieldset>-->
        <?
        $goodsForm->end();

    }
}
?>