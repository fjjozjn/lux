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
        $rtn1 = $mysql->q('delete from sys_petty_cash_voucher_item where main_id = ?', $_GET['delid']);
        $rtn2 = $mysql->q('delete from sys_petty_cash_voucher where id = ?', $_GET['delid']);
        if($rtn2){
            $myerror->ok('Delete Petty Cash Voucher success !', 'com-search_petty_cash_voucher&page=1');
        }else{
            $myerror->error('Delete Petty Cash failure', 'com-search_petty_cash_voucher&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM sys_petty_cash_voucher WHERE id = ?', $_GET['modid']);

            $item_result = $mysql->q('SELECT * FROM sys_petty_cash_voucher_item WHERE main_id = ?', $_GET['modid']);
            $pcv_item_rtn = $mysql->fetch();
            //fb($pcv_item_rtn);die();
            $pcv_item_num = count($pcv_item_rtn);
            //fb($pcv_item_num);

        }else{
            die('Need modid!');
        }


        $goodsForm = new My_Forms();
        $formItems = array(

            'pcv_id' => array('title' => 'Petty Cash NO.', 'type' => 'text', 'readonly' => 'readonly', 'value' => isset($mod_result['pcv_id'])?$mod_result['pcv_id']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        //序号从0开始
        for($i = 0; $i < $pcv_item_num; $i++){
            // 把required都去掉了，不然删除item后提交不了
            $formItems['account_name'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($pcv_item_rtn[$i]['account_name'])?$pcv_item_rtn[$i]['account_name']:'');
            $formItems['description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 500, 'value' => isset($pcv_item_rtn[$i]['description'])?$pcv_item_rtn[$i]['description']:'', 'rows' => 2,
                'addon' => 'style="width:200px"');
            $formItems['cny'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'value' => isset($pcv_item_rtn[$i]['cny'])?$pcv_item_rtn[$i]['cny']:'', 'addon' => 'onblur="pcvCNYBlur(this)"');
            $formItems['rate'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'value' => isset($pcv_item_rtn[$i]['rate'])?$pcv_item_rtn[$i]['rate']:'', 'addon' => 'onblur="pcvRateBlur(this)"');
            $formItems['amount'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'value' => isset($pcv_item_rtn[$i]['amount'])?$pcv_item_rtn[$i]['amount']:'');
        }
        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){

            $today = dateMore();
            $staff = $_SESSION['logininfo']['aName'];

            //******
            //第一个post的是form的标识串，还有1个表单项，所以是2（比add多一个pcv_id）
            $prev_num = 2;
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

                $rs = $mysql->q('update sys_petty_cash_voucher set mod_date = ?, mod_by = ? where id = ?', $today, $staff, $_GET['modid']);

                //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                if($rs !== false){

                    $mysql->q('delete from sys_petty_cash_voucher_item where main_id = ?', $_GET['modid']);

                    for($j = 0; $j < $pcv_item_num; $j++){

                        $rs = $rs = $mysql->q('insert into sys_petty_cash_voucher_item set main_id = ?, account_name = ?, description = ?, cny = ?, rate = ?, amount = ?', $_GET['modid'], $pcv_account_name[$j], $pcv_description[$j], $pcv_cny[$j], $pcv_rate[$j], $pcv_amount[$j]);
                    }

                    $myerror->ok('Modify Petty Cash success !', 'com-search_petty_cash_voucher&page=1');
                }else{
                    $myerror->error('Modify Petty Cash success', 'com-search_petty_cash_voucher&page=1');
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
        <h1 class="green">Petty Cash<em>* item must be filled in</em></h1>

        <fieldset class="center2col">
            <legend class='legend'>Modify</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><? $goodsForm->show('pcv_id');?></td>
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
                    <?
                    for($i = 0; $i < $pcv_item_num; $i++){
                        ?>
                        <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                            <!--                    <td class="dragHandle"></td>-->
                            <td><? $goodsForm->show('account_name'.$i);?></td>
                            <td><? $goodsForm->show('description'.$i);?></td>
                            <td><? $goodsForm->show('cny'.$i);?></td>
                            <td><? $goodsForm->show('rate'.$i);?></td>
                            <td><? $goodsForm->show('amount'.$i);?></td>
                            <td align="left"><img title="添加" style="opacity: 0.5;" onclick="addPCVoucherItem()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../sys/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
                            <? if($i != 0){ ?>
                                <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delPCVoucherItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity',
                                '0.5')" src="../sys/images/del_small.png"></td>
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

