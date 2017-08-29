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
        'dr_currency' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'addon' => 'style="width:50px"', 'value' => 'HK$'),
        'dr' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:100px"'),
        'cr_currency' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'addon' => 'style="width:50px"', 'value' => 'HK$'),
        'cr' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:100px"'),

        'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
    );

    $goodsForm->init($formItems);

    if(!$myerror->getAny() && $goodsForm->check()){

        $prv_no = autoGenerationID();

        $today = dateMore();
        $staff = $_SESSION['logininfo']['aName'];

        //******
        //第一个post的是form的标识串，所以是1
        $prev_num = 1;
        //后面的post，有个submit
        $last_num = 0;

        $i = 0;
        $prv_item = array();
        foreach( $_POST as $v){
            if( $i < $prev_num){
                $i++;
            }elseif($i >= count($_POST) - $last_num){
                break;
            }else{
                $prv_item[] = $v;
                $i++;
            }
        }
        //这个是设置每个ITEM的元素个数
        $each_item_num = 6;
        $prv_item_num = intval(count($prv_item)/$each_item_num);
        //******

        //因为前端没有require的限制了，所以后端要限制，有填写item才能继续
        if($prv_item_num >= 1){
            $prv_account_name = array();
            $prv_description = array();
            $prv_dr_currency = array();
            $prv_dr = array();
            $prv_cr_currency = array();
            $prv_cr = array();

            $index = 0;

            for($j = 0; $j < $prv_item_num; $j++){
                $prv_account_name[] = trim($prv_item[$index++]);
                $prv_description[] = trim($prv_item[$index++]);
                $prv_dr_currency[] = trim($prv_item[$index++]);
                $prv_dr[] = trim($prv_item[$index++]);
                $prv_cr_currency[] = trim($prv_item[$index++]);
                $prv_cr[] = trim($prv_item[$index++]);
            }

            $rs = $mysql->q('insert into sys_p_r_voucher set prv_id = ?, in_date = ?, mod_date = ?, created_by = ?, mod_by = ?', $prv_no, $today, $today, $staff, $staff);
            if($rs){
                $new_id = $mysql->id();

                for($j = 0; $j < $prv_item_num; $j++){
                    $mysql->q('insert into sys_p_r_voucher_item set main_id = ?, account_name = ?, description = ?,
                dr_currency = ?, dr = ?, cr_currency = ?, cr = ?', $new_id, $prv_account_name[$j], $prv_description[$j], $prv_dr_currency[$j], $prv_dr[$j], $prv_cr_currency[$j], $prv_cr[$j]);
                }

                $myerror->ok('Add P/R Voucher success !', 'com-search_p_r_voucher&page=1');
            }else{
                $myerror->error('Add P/R Voucher failure', 'com-search_p_r_voucher&page=1');
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
        <h1 class="green">PAYMENT / RECEIVED VOUCHER<em>* item must be filled in</em></h1>
        <fieldset class="center2col">
            <legend class='legend'>Add P/R Voucher</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><div class="set"><label class="formtitle">P/R Voucher NO.</label><br />(autogeneration)</div></td>
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
                        <td width="20%">Account Name</td>
                        <td width="20%">Description</td>
                        <td width="7%">Dr Currency</td>
                        <td width="15%">Dr</td>
                        <td width="7%">Cr Currency</td>
                        <td width="15%">Cr</td>
                        <td width="8%">&nbsp;</td>
                        <td width="8%">&nbsp;</td>
                    </tr>
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                        <td><? $goodsForm->show('account_name');?></td>
                        <td><? $goodsForm->show('description');?></td>
                        <td><? $goodsForm->show('dr_currency');?></td>
                        <td><? $goodsForm->show('dr');?></td>
                        <td><? $goodsForm->show('cr_currency');?></td>
                        <td><? $goodsForm->show('cr');?></td>
                        <td><img title="添加" style="opacity: 0.5;" onclick="addPRVoucherItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../sys/images/add_small.png"></td>
                        <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delPRVoucherItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../sys/images/del_small.png"></td>
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