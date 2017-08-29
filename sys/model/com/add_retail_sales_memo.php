<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(
    //'rsm_id' => array('title' => 'Sales Memo #', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
    'wh_id' => array('title' => 'Shop', 'type' => 'select', 'options' => get_warehouse_info(1, 'Shop'), 'required' => 1),
    'sales_date' => array('title' => 'Sales Date', 'type' => 'text', 'restrict' => 'date', 'required' => 1),
    'currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1),

    'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'style="width:150px" onblur="rsm_pid_blur(this)"', 'nostar' => true),
    //'description_chi' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:180px"'),
    'payment_method' => array('type' => 'select', 'options' => get_payment_method(), 'required' => 1, 'nostar' => true),
    'qty' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'style="width:80px" onblur="rsmQtyBlur(this)"', 'nostar' => true),
    'price' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'style="width:80px" onblur="rsmPriceBlur(this)"', 'nostar' => true/*, 'readonly' => 'readonly'*/),
    'remark' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:200px"'),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){

    //die();

//fb($_POST);die();

    $wh = explode('|', $_POST['wh_id']);
    $wh_id = '';
    $wh_name = '';
    if(!empty($wh)){
        $wh_id = $wh[0];
        $wh_name = $wh[1];
    }
    $rsm_id = rsm_autoGenerationID($wh_name);

    $sales_date = $_POST['sales_date'];
    $currency = $_POST['currency'];

    //******
    //第一个post的是form的标识串，还有1个表单项 warehouse name
    //20130708 加了 sales date 和 currency ,所以现在是4了
    $prev_num = 4;
    //后面的post，有个submit
    $last_num = 1;

    $i = 0;
    $rsm_item = array();
    foreach( $_POST as $v){
        if( $i < $prev_num){
            $i++;
        }elseif($i >= count($_POST) - $last_num){
            break;
        }else{
            $rsm_item[] = $v;
            $i++;
        }
    }
    //这个是设置每个ITEM的元素个数
    $each_item_num = 6;
    $rsm_item_num = intval(count($rsm_item)/$each_item_num);
    //******

//fb($rsm_item);
    $in_date = dateMore();
    $created_by = $_SESSION["logininfo"]["aName"];

    $q_pid = array();
    //$description_chi = array();
    $payment_method = array();
    $qty = array();
    $price = array();
    $remark = array();
    $pt_input = array();

    $index = 0;

    for($j = 0; $j < $rsm_item_num; $j++){
        $q_pid[] = $rsm_item[$index++];
        //$description_chi[] = $rsm_item[$index++];
        $payment_method[] = $rsm_item[$index++];
        $price[] = str_replace(',', '', ($rsm_item[$index] != '')?$rsm_item[$index++]:0);
        $qty[] = $rsm_item[$index++];
        $remark[] = $rsm_item[$index++];
        $pt_input[] = $rsm_item[$index++];
    }

    /*fb($q_pid);
    //fb($description_chi);
    fb($payment_method);
    fb($qty);
    fb($price);
    fb($remark);
    fb($pt_input);
    die();*/

    //默认填上最后修改时间与人，就是创建时间与人，因为search的时候显示的是Last modify date
    $result = $mysql->q('insert into retail_sales_memo values (NULL, '.moreQm(9).')', $rsm_id, $wh_id, $wh_name, $sales_date, $currency, $in_date, $in_date, $created_by, $created_by);

    if($result){
        for($k = 0; $k < $rsm_item_num; $k++){
            $rs = $mysql->q('insert into retail_sales_memo_item values (NULL, '.moreQm(7).')', $rsm_id, $q_pid[$k], $payment_method[$k], $price[$k], $qty[$k], $pt_input[$k], $remark[$k]);
            if($rs){
                $rtn_wh = $mysql->qone('select qty from warehouse_item_unique where wh_id = ? and wh_name = ? and pid = ?', $wh_id, $wh_name, $q_pid[$k]);
                if($rtn_wh){
                    if($qty[$k] <= $rtn_wh['qty']){
                        $rs = $mysql->q('update warehouse_item_unique set qty = qty - ? where wh_id = ? and wh_name = ? and pid = ?', $qty[$k], $wh_id, $wh_name, $q_pid[$k]);
                        if($rs){
                            $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $created_by." retail update warehouse ".$wh_name." item ".$q_pid[$k]." - ".$qty[$k]." success(1)", RETAIL_SALES_MEMO_ITEM_UPDATE_SUCCESS, "", "", 0);
                        }else{
                            $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $created_by." retail update warehouse ".$wh_name." item ".$q_pid[$k]." - ".$qty[$k]." failure(1)", RETAIL_SALES_MEMO_ITEM_UPDATE_FAILURE, "", "", 0);
                        }
                    }else{
                        $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $created_by." retial item".$q_pid[$k]." num ".$qty[$k]." larger than warehouse ".$wh_name." item num ".$rtn_wh['qty']." (1)", RETAIL_SALES_MEMO_ITEM_UPDATE_FAILURE, "", "", 0);
                    }
                }else{
                    $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $created_by." retail can find item ".$q_pid[$k]." in warehouse ".$wh_name." (1)", RETAIL_SALES_MEMO_ITEM_UPDATE_FAILURE, "", "", 0);
                }
            }else{
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $created_by." add retail item record warehouse ".$wh_name." item ".$q_pid[$k]." num ".$qty[$k]." failure(1)", RETAIL_SALES_MEMO_ITEM_ADD_FAILURE, "", "", 0);
            }
        }
        $myerror->ok('Add Retail Sales Memo success !', 'com-search_retail_sales_memo&page=1&wh_name='.$wh_name);
    }else{
        $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $created_by." add retail record warehouse ".$wh_name." item ".$q_pid[$k]." num ".$qty[$k]." failure(1)", RETAIL_SALES_MEMO_ITEM_ADD_FAILURE, "", "", 0);
        $myerror->error('Add Retail Sales Memo failure !', 'BACK');
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
    <h1 class="green">Retail Sales Memo<em>* item must be filled in</em></h1>
    <fieldset class="center2col">
        <legend class='legend'>Add Retail Sales Memo</legend>

        <?php
        $goodsForm->begin();

        //$goodsForm->show('rsm_id');
        $goodsForm->show('wh_id');
        $goodsForm->show('sales_date');
        $goodsForm->show('currency');
        ?>

        <div class="line"></div>
        <br />
        <div style="margin-left:28px; color: #F00"># 输入 Product ID 后焦点离开输入框则查询产品资料自动填上，按+按钮是新增一行</div>
        <div style="margin-left:28px;">
            <!--            <label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>-->
            <table width="100%" id="tableDnD">
                <tbody id="tbody">
                <tr class="formtitle nodrop nodrag">
                    <!--                    <td width="3%"></td>-->
                    <td width="16%">Product ID</td>
<!--                    <td width="18%">Description()</td>-->
                    <td width="18%">Payment Method</td>
                    <td width="8%">Price(HKD)</td>
                    <td width="8%">Quantity</td>
                    <td width="8%">Stock</td>
                    <td width="8%">Subtotal</td>
                    <td width="8%">Photo</td>
                    <td width="21%">Remark</td>
                    <td width="4%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                    <!--                    <td class="dragHandle"></td>-->
                    <td><? $goodsForm->show('q_pid');?></td>
<!--                    <td>--><?// $goodsForm->show('description_chi');?><!--</td>-->
                    <td><? $goodsForm->show('payment_method');?></td>
                    <td><? $goodsForm->show('price');?></td>
                    <td><? $goodsForm->show('qty');?></td>
                    <td class="num_td">&nbsp;</td>
                    <td id="sub" class="num_td">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td><? $goodsForm->show('remark');?></td>
                    <td><img title="添加" style="opacity: 0.5;" onclick="addRetailSalesMemoItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"></td>
                    <!--                    <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delWarehouseItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td>-->
                    <td>&nbsp;</td>
                    <td><input type="hidden" name="pt_input" id="pt_input" value="" /></td>
                </tr>
                </tbody>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td align="center">Totla : </td>
                    <td><div id="total" class="num_td">0</div></td>
                    <td>&nbsp;</td>
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
    <?
    $goodsForm->end();
    ?>
    <script>
        $(function(){
            SearchPid('');//参数要加''，否则不行
        });
    </script>
<?
}
?>