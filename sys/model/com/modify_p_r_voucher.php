<?php

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

judgeUserPerm( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{

    if(isset($_GET['delid']) && $_GET['delid'] != ''){

        //由於指定了foreign key，所以要先刪item裏的內容
        $rtn1 = $mysql->q('delete from sys_p_r_voucher_item where main_id = ?', $_GET['delid']);
        $rtn2 = $mysql->q('delete from sys_p_r_voucher where id = ?', $_GET['delid']);
        if($rtn2){
            $myerror->ok('Delete P/R Voucher success !', 'com-search_p_r_voucher&page=1');
        }else{
            $myerror->error('Delete P/R Voucher failure', 'com-search_p_r_voucher&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM sys_p_r_voucher WHERE id = ?', $_GET['modid']);

            $item_result = $mysql->q('SELECT * FROM sys_p_r_voucher_item WHERE main_id = ?', $_GET['modid']);
            $prv_item_rtn = $mysql->fetch();
            //fb($prv_item_rtn);die();
            $prv_item_num = count($prv_item_rtn);
            //fb($prv_item_num);

        }else{
            die('Need modid!');
        }


        $goodsForm = new My_Forms();
        $formItems = array(

            'prv_id' => array('title' => 'P/R Voucher NO.', 'type' => 'text', 'readonly' => 'readonly', 'value' => isset($mod_result['prv_id'])?$mod_result['prv_id']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        //序号从0开始
        for($i = 0; $i < $prv_item_num; $i++){
            // 把required都去掉了，不然删除item后提交不了
            $formItems['account_name'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($prv_item_rtn[$i]['account_name'])?$prv_item_rtn[$i]['account_name']:'');
            $formItems['description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 500, 'value' => isset($prv_item_rtn[$i]['description'])?$prv_item_rtn[$i]['description']:'', 'rows' => 2,
                'addon' => 'style="width:200px"');
            $formItems['dr_currency'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'addon' => 'style="width:50px"', 'value' => isset($prv_item_rtn[$i]['dr_currency'])?$prv_item_rtn[$i]['dr_currency']:'');
            $formItems['dr'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:100px"', 'restrict' => 'number', 'value' => isset($prv_item_rtn[$i]['dr'])?$prv_item_rtn[$i]['dr']:'');
            $formItems['cr_currency'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'addon' => 'style="width:50px"', 'value' => isset($prv_item_rtn[$i]['cr_currency'])?$prv_item_rtn[$i]['cr_currency']:'');
            $formItems['cr'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:100px"', 'restrict' => 'number', 'value' => isset($prv_item_rtn[$i]['cr'])?$prv_item_rtn[$i]['cr']:'');
        }
        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){

            $today = dateMore();
            $staff = $_SESSION['logininfo']['aName'];

            //******
            //第一个post的是form的标识串，还有1个表单项，所以是2（比add多一个prv_id）
            $prev_num = 2;
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

                $rs = $mysql->q('update sys_p_r_voucher set mod_date = ?, mod_by = ? where id = ?', $today, $staff, $_GET['modid']);

                //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                if($rs !== false){

                    $mysql->q('delete from sys_p_r_voucher_item where main_id = ?', $_GET['modid']);

                    for($j = 0; $j < $prv_item_num; $j++){

                        $rs = $rs = $mysql->q('insert into sys_p_r_voucher_item set main_id = ?, account_name = ?, description = ?, dr_currency = ?, dr = ?, cr_currency = ?, cr = ?', $_GET['modid'], $prv_account_name[$j], $prv_description[$j], $prv_dr_currency[$j], $prv_dr[$j], $prv_cr_currency[$j], $prv_cr[$j]);
                    }

                    $myerror->ok('Modify P/R Voucher success !', 'com-search_p_r_voucher&page=1');
                }else{
                    $myerror->error('Modify P/R Voucher success', 'com-search_p_r_voucher&page=1');
                }
            }else{
                $myerror->error('Item none !', 'BACK');
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
        <h1 class="green">P/R Voucher<em>* item must be filled in</em></h1>

        <fieldset class="center2col">
            <legend class='legend'>Modify</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><? $goodsForm->show('prv_id');?></td>
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
                    <?
                    for($i = 0; $i < $prv_item_num; $i++){
                        ?>
                        <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                            <!--                    <td class="dragHandle"></td>-->
                            <td><? $goodsForm->show('account_name'.$i);?></td>
                            <td><? $goodsForm->show('description'.$i);?></td>
                            <td><? $goodsForm->show('dr_currency'.$i);?></td>
                            <td><? $goodsForm->show('dr'.$i);?></td>
                            <td><? $goodsForm->show('cr_currency'.$i);?></td>
                            <td><? $goodsForm->show('cr'.$i);?></td>
                            <td align="left"><img title="添加" style="opacity: 0.5;" onclick="addPRVoucherItem()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../sys/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
                            <? if($i != 0){ ?>
                                <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delPRVoucherItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity', '0.5')" src="../sys/images/del_small.png"></td>
                            <? }?>
                        </tr>
                    <?
                    }
                    ?>
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

