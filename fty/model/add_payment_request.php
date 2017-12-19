<?php
/**
 * Author: zhangjn
 * Date: 2017/10/4
 * Time: 20:56
 */
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//禁止其他用户进入（临时做法）
//201306131746 去除限制
/*if(!isSysAdmin()){
	$myerror->error('Without Permission To Access', 'main');
}*/

$goodsForm = new My_Forms();
$formItems = array(

    //这个是隐藏起来的
    'fpr_type' => array('type' => 'select', 'options' => get_fty_wlgy_jg_type(), 'addon' => 'onchange="searchFtyCustomer(this)"', 'disabled' => 'disabled'),
    'fpr_fty_customer' => array('type' => 'select', 'options' => array(), 'addon' => 'onchange="searchFtyCustomerDetail(this)"', 'disabled' => 'disabled'),
    'fpr_pay_amount' => array('type' => 'text', 'restrict' => 'number', 'disabled' => 'disabled'),

    //这个是显示的第一个
    'fpr_type1' => array('type' => 'select', 'options' => get_fty_wlgy_jg_type(), 'addon' => 'onchange="searchFtyCustomer(this)"'),
    'fpr_fty_customer1' => array('type' => 'select', 'options' => '', 'addon' => 'onchange="searchFtyCustomerDetail(this)"', 'disabled' => 'disabled'),
    'fpr_pay_amount1' => array('type' => 'text', 'restrict' => 'number', 'disabled' => 'disabled'),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){

    //var_dump($_POST);die();

    $today = dateMore();
    $staff = $_SESSION['ftylogininfo']['aName'];

    $i = 0;
    $prev_num = 1;//第一个post的是form的标识串，还有0个表单项，所以是1
    $last_num = 1;//后面的post，有个submit
    $item = array();
    foreach($_POST as $v){
        if( $i < $prev_num){
            $i++;
        }elseif($i >= count($_POST) - $last_num){
            break;
        }else{
            $item[] = $v;
            $i++;
        }
    }

    //这个是设置每个ITEM的元素个数
    $each_item_num = 3;
    $item_num = intval(count($item)/$each_item_num);

    $payment_request_arr = array();
    $fpr_index = 0;

    for($j = 0; $j < $item_num; $j++){
        $payment_request_arr[$j]['pay_amount'] = $item[$fpr_index++];
        $payment_request_arr[$j]['type'] = $item[$fpr_index++];
        $payment_request_arr[$j]['fty_cid'] = $item[$fpr_index++];
    }

    //fb($payment_request_arr);die('#');

    $result = $mysql->q('insert into fty_payment_request set created_by = ?, mod_by = ?, in_date = ?, mod_date = ?', $staff, $staff, $today, $today);
    if ($result) {
        foreach ($payment_request_arr as $v) {
            $mysql->q('insert into fty_payment_request_item set main_id = ?, type = ?, fty_cid = ?, pay_amount = ?', $result, $v['type'], $v['fty_cid'], $v['pay_amount']);
        }
        $myerror->ok('新增 付款申请单 成功!', 'search_payment_request&page=1');
    } else {
        $myerror->error('新增 付款申请单 失败', 'BACK');
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
    <h1 class="green">财务申请单<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>新增</legend>
        <?php
        $goodsForm->begin();
        ?>
        <div style="margin-left:28px;">
            <table width="100%" id="tableDnD_wl">
                <tbody id="tbody">
                <tr class="formtitle nodrop nodrag">
                    <td width="2%"></td>
                    <td>类别</td>
                    <td>供应商</td>
                    <td>应付</td>
                    <td>付款金额</td>
                    <td width="5%"></td>
                </tr>
                <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('fpr_type');?></td>
                    <td><? $goodsForm->show('fpr_fty_customer');?></td>
                    <td id="ap"></td>
                    <td><? $goodsForm->show('fpr_pay_amount');?></td>
                    <td><div id="del" onclick="delBomItem(this)"></div><input type="hidden" id="fpr_type_value" name="fpr_type_value" value="" disabled="disabled"/><input type="hidden" id="fpr_fty_customer_value" name="fpr_fty_customer_value" value="" disabled="disabled"/></td>
                </tr>
                <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('fpr_type1');?></td>
                    <td><? $goodsForm->show('fpr_fty_customer1');?></td>
                    <td id="ap"></td>
                    <td><? $goodsForm->show('fpr_pay_amount1');?></td>
                    <td><div id="del1" onclick="delBomItem(this)"></div><input type="hidden" id="fpr_type_value1" name="fpr_type_value1" value="" disabled="disabled"/><input type="hidden" id="fpr_fty_customer_value1" name="fpr_fty_customer_value1" value="" disabled="disabled"/></td>
                </tr>
                </tbody>
            </table>

            <div class="line"></div>
        </div>
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
        $(".template").hide();
        //table tr层表单可拖动
        $("#tableDnD").tableDnD({dragHandle: ".dragHandle"});
    })
</script>