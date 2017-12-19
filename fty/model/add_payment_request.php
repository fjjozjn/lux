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

    $wlgy_cid = $_POST['wlgy_cid'];
    $wlgy_reference = $_POST['wlgy_reference'];
    $wlgy_attention = trim($_POST['wlgy_attention']);
    $wlgy_address = $_POST['wlgy_address'];
    $wlgy_expected_date = $_POST['wlgy_expected_date'];
    $wlgy_remark = $_POST['wlgy_remark'];
    $today = dateMore();
    $staff = $_SESSION['ftylogininfo']['aName'];

    $i = 0;
    $prev_num = 7;//第一个post的是form的标识串，还有6个表单项，所以是7
    $last_num = 1;//后面的post，有个submit
    $item = array();
    foreach( $_POST as $v){
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
    $each_item_num = 4;
    $item_num = intval(count($item)/$each_item_num);

    $material_arr = array();
    $m_index = 0;

    for($j = 0; $j < $item_num; $j++){
        $material_arr[$j]['price'] = $item[$m_index++];
        $material_arr[$j]['value'] = $item[$m_index++];
        $material_arr[$j]['remark'] = $item[$m_index++];
        $material_arr[$j]['m_id'] = $item[$m_index++];
    }

    //fb($material_arr);die('#');

    //判断是否输入的ID已存在，因为存在的话由于数据库限制，就会新增失败
    $judge = $mysql->q('select id from fty_material_buy where m_id = ?', $m_id);
    if(!$judge){
        $result = $mysql->q('insert into fty_material_buy set m_id = ?, cid = ?, attention = ?, address = ?, reference = ?, expected_date = ?, remark = ?, in_date = ?, mod_date = ?, created_by = ?, mod_by = ?', $m_id, $wlgy_cid, $wlgy_attention, $wlgy_address, $wlgy_reference, $wlgy_expected_date, $wlgy_remark, $today, $today, $staff, $staff);

        if($result){
            $total = 0;
            foreach($material_arr as $v){
                $rtn = $mysql->qone('select m_name, m_type, m_color, m_unit, m_loss from fty_material where m_id = ?', $v['m_id']);
                $m_total = round($v['price']*$v['value']*(1+$rtn['m_loss']/100), 2);
                $mysql->q('insert into fty_material_buy_item set main_id = ?, m_type = ?, m_id = ?, m_name = ?, m_color = ?, m_unit = ?, m_price = ?, m_value = ?, m_total = ?, m_remark = ?', $result, $rtn['m_type'], $v['m_id'], $rtn['m_name'], $rtn['m_color'], $rtn['m_unit'], $v['price'], $v['value'], $m_total, $v['remark']);
                //更改库存
                changeFtyMaterialNum($v['m_id'], $v['value']);
                $total += $m_total;
            }

            //物料供应商ap（应付欠款）修改
            $mysql->q('update fty_wlgy_customer set ap = ap + ? where cid = ?', $total, $wlgy_cid);

            $myerror->ok('新增 物料采购单 成功!', 'search_material_buy&page=1');
        }else{
            $myerror->error('新增 物料采购单 失败', 'BACK');
        }
    }else{
        $myerror->error('输入的 物料采购单ID 已存在，新增失败', 'BACK');
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
                    <td><div id="del" onclick="delBomItem(this)"></div><input type="hidden" id="g_m_id" name="g_m_id" value="" disabled="disabled"/></td>
                </tr>
                <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('fpr_type1');?></td>
                    <td><? $goodsForm->show('fpr_fty_customer1');?></td>
                    <td id="ap"></td>
                    <td><? $goodsForm->show('fpr_pay_amount1');?></td>
                    <td><div id="del1" onclick="delBomItem(this)"></div><input type="hidden" id="g_m_id1" name="g_m_id1" value="" disabled="disabled"/></td>
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