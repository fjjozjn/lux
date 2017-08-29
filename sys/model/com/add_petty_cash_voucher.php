<?php

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    $goodsForm = new My_Forms();
    $formItems = array(

        /*'prv_id' => array('title' => 'P/R Voucher NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1),*/

        'account_name' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 200),
        'description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 500, 'rows' => 2,
            'addon' => 'style="width:200px"'),
        'cny' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'onblur="pcvCNYBlur(this)"'),
        'rate' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'onblur="pcvRateBlur(this)"', 'value' => "1.00"),
        'amount' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20),

        'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
    );

    $goodsForm->init($formItems);

    if(!$myerror->getAny() && $goodsForm->check()){

        $pcv_no = autoGenerationID();

        $today = dateMore();
        $staff = $_SESSION['logininfo']['aName'];

        //******
        //第一个post的是form的标识串，所以是1
        $prev_num = 1;
        //后面的post，有个submit
        $last_num = 0;

        $i = 0;
        $pcv_item = array();
        foreach( $_POST as $v){
            if( $i < $prev_num){
                $i++;
            }elseif($i >= count($_POST) - $last_num){
                break;
            }else{
                $pcv_item[] = $v;
                $i++;
            }
        }
        //这个是设置每个ITEM的元素个数
        $each_item_num = 5;
        $pcv_item_num = intval(count($pcv_item)/$each_item_num);
        //******

        //因为前端没有require的限制了，所以后端要限制，有填写item才能继续
        if($pcv_item_num >= 1){
            $pcv_account_name = array();
            $pcv_description = array();
            $pcv_cny = array();
            $pcv_rate = array();
            $pcv_amount = array();

            $index = 0;

            for($j = 0; $j < $pcv_item_num; $j++){
                $pcv_account_name[] = trim($pcv_item[$index++]);
                $pcv_description[] = trim($pcv_item[$index++]);
                $pcv_cny[] = trim($pcv_item[$index++]);
                $pcv_rate[] = trim($pcv_item[$index++]);
                $pcv_amount[] = trim($pcv_item[$index++]);
            }

            $rs = $mysql->q('insert into sys_petty_cash_voucher set pcv_id = ?, in_date = ?, mod_date = ?, created_by = ?, mod_by = ?', $pcv_no, $today, $today, $staff, $staff);
            if($rs){
                $new_id = $mysql->id();

                for($j = 0; $j < $pcv_item_num; $j++){
                    $mysql->q('insert into sys_petty_cash_voucher_item set main_id = ?, account_name = ?, description = ?,
                cny = ?, rate = ?, amount = ?', $new_id, $pcv_account_name[$j], $pcv_description[$j], $pcv_cny[$j], $pcv_rate[$j], $pcv_amount[$j]);
                }

                $myerror->ok('Add Petty Cash Voucher success !', 'com-search_petty_cash_voucher&page=1');
            }else{
                $myerror->error('Add Petty Cash Voucher failure', 'com-search_petty_cash_voucher&page=1');
            }
        }else{
            $myerror->error('Item none !', 'BACK');
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
        <h1 class="green">PETTY CASH VOUCHER<em>* item must be filled in</em></h1>
        <fieldset class="center2col">
            <legend class='legend'>Add Petty Cash Voucher</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><div class="set"><label class="formtitle">Petty Cash Voucher NO.</label><br />(autogeneration)</div></td>
                    <td width="25%"></td>
                    <td width="25%"></td>
                    <td width="25%"></td>
                </tr>
            </table>

            <div class="line"></div>
            <br />
            <div style="margin-left:28px;">
                <label class="formtitle" for="g_cast"><font size="+1">Input</font></label>
                <table width="100%" id="tableDnD">
                    <tbody id="tbody">
                    <tr class="formtitle nodrop nodrag">
                        <td width="18%">Account Name</td>
                        <td width="18%">Description</td>
                        <td width="18%">CNY</td>
                        <td width="18%">Rate</td>
                        <td width="18%">Amount</td>
                        <td width="5%">&nbsp;</td>
                        <td width="5%">&nbsp;</td>
                    </tr>
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                        <td><? $goodsForm->show('account_name');?></td>
                        <td><? $goodsForm->show('description');?></td>
                        <td><? $goodsForm->show('cny');?></td>
                        <td><? $goodsForm->show('rate');?></td>
                        <td><? $goodsForm->show('amount');?></td>
                        <td><img title="添加" style="opacity: 0.5;" onclick="addPCVoucherItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../sys/images/add_small.png"></td>
                        <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delPCVoucherItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../sys/images/del_small.png"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="line"></div>
            <?
            $goodsForm->show('submitbtn');
            ?>
        </fieldset>
        <?
        $goodsForm->end();
    }
}