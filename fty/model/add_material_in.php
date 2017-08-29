<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

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

    'm_in_date' => array('title' => '存仓日期', 'type' => 'text', 'restrict' => 'date', 'required' => 1),

    'remark' => array('title' => '备注', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 2),

    //物料 ： 这个是隐藏起来的
    'g_m_type' => array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"', 'disabled' => 'disabled'),
    'g_m_id_name' => array('type' => 'select', 'options' => '', 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"'),
    'g_m_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'disabled' => 'disabled'),
    'g_m_value' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'disabled' => 'disabled'),
    'g_m_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),

    //物料 ： 这个是显示的第一个
    'g_m_type1' => array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"'),
    'g_m_id_name1' => array('type' => 'select', 'options' => '', 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"'),
    'g_m_price1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'disabled' => 'disabled'),
    'g_m_value1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'disabled' => 'disabled'),
    'g_m_remark1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' 提交 '),
);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){

    //fb($_POST);die('@');

    $mi_id = fty_autoGenerationID();

    fb($mi_id);

    $m_in_date = $_POST['m_in_date'];
    $remark = $_POST['remark'];
    $today = dateMore();
    $staff = $_SESSION['ftylogininfo']['aName'];

    $i = 0;
    $prev_num = 3;//第一个post的是form的标识串，还有2个表单项，所以是3
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
    $judge = $mysql->q('select id from fty_material_in where mi_id = ?', $mi_id);
    if(!$judge){
        $result = $mysql->q('insert into fty_material_in set mi_id = ?, m_in_date = ?, remark = ?, in_date = ?, mod_date = ?, created_by = ?, mod_by = ?', $mi_id, $m_in_date, $remark, $today, $today, $staff, $staff);

        if($result){
            foreach($material_arr as $v){
                $rtn = $mysql->qone('select m_name, m_type, m_color, m_unit, m_loss from fty_material where m_id = ?', $v['m_id']);
                $mysql->q('insert into fty_material_in_item set main_id = ?, mi_type = ?, mi_id = ?, mi_name = ?, mi_color = ?, mi_unit = ?, mi_price = ?, mi_value = ?, mi_total = ?, mi_remark = ?', $result, $rtn['m_type'], $v['m_id'], $rtn['m_name'], $rtn['m_color'], $rtn['m_unit'], $v['price'], $v['value'], round($v['price']*$v['value']*(1+$rtn['m_loss']/100), 2), $v['remark']);
                //更改库存
                changeFtyMaterialNum($v['m_id'], $v['value']);
            }

            $myerror->ok('新增 物料存仓单 成功!', 'search_material_in&page=1');
        }else{
            $myerror->error('新增 物料存仓单 失败', 'BACK');
        }
    }else{
        $myerror->error('输入的 物料存仓单ID 已存在，新增失败', 'BACK');
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
    <h1 class="green">物料存仓单<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>新增</legend>
        <?php
        $goodsForm->begin();
        ?>
        <table width="100%" id="table">
            <tr class="formtitle" valign="top">
                <td width="25%"><div class="set"><label class="formtitle">物料存仓单编号</label><br />(自动生成)
                    </div></td>
                <td width="25%"><? $goodsForm->show('m_in_date');?></td>
                <td width="50%"><? $goodsForm->show('remark');?></td>
            </tr>
        </table>
        <div class="line"></div>
        <div style="margin-left:28px;">
            <label class="formtitle" for="g_cast"><font size="+1">存入物料</font></label>
            <table width="100%" id="tableDnD_wl">
                <tbody id="tbody">
                <tr class="formtitle nodrop nodrag">
                    <td width="2%"></td>
                    <td>类别</td>
                    <td>物料编号：名称</td>
                    <td>规格颜色</td>
                    <td>单位</td>
                    <td>单价</td>
                    <td>数量/重量值</td>
                    <td>损耗率(%)</td>
                    <td width="10%">价格</td>
                    <td width="20%">备注</td>
                    <td width="5%"></td>
                </tr>
                <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('g_m_type');?></td>
                    <td><? $goodsForm->show('g_m_id_name');?></td>
                    <td id="color"></td>
                    <td id="unit"></td>
                    <td><? $goodsForm->show('g_m_price');?></td>
                    <td><? $goodsForm->show('g_m_value');?></td>
                    <td><div id="m_loss"></div></td>
                    <td><div id="m_total"></div></td>
                    <td><? $goodsForm->show('g_m_remark');?></td>
                    <td><div id="del" onclick="delBomItem(this)"></div><input type="hidden" id="g_m_id" name="g_m_id" value="" disabled="disabled"/></td>
                </tr>
                <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('g_m_type1');?></td>
                    <td><? $goodsForm->show('g_m_id_name1');?></td>
                    <td id="color1"></td>
                    <td id="unit1"></td>
                    <td><? $goodsForm->show('g_m_price1');?></td>
                    <td><? $goodsForm->show('g_m_value1');?></td>
                    <td><div id="m_loss"></div></td>
                    <td><div id="m_total"></div></td>
                    <td><? $goodsForm->show('g_m_remark1');?></td>
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
        selectSupplier("pc_");
        searchProduct(15, '');
        searchProduct(15, '1');
        //table tr层表单可拖动
        $("#tableDnD").tableDnD({dragHandle: ".dragHandle"});
    })
</script>