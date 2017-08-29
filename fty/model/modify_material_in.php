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


if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        //更改库存
        delWarehouseMaterial('fty_material_in_item', $_GET['delid']);
        $rs1 = $mysql->q('delete from fty_material_in_item where main_id = ?', $_GET['delid']);
        $rs2 = $mysql->q('delete from fty_material_in where id = ?', $_GET['delid']);
        if($rs2){
            $myerror->ok('删除 物料存仓单 成功!', 'search_material_in&page=1');
        }else{
            $myerror->error('删除 物料存仓单 失败!', 'search_material_in&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){

            $mod_result = $mysql->qone('SELECT * FROM fty_material_in WHERE id = ?', $_GET['modid']);
            $rs_m = $mysql->q('SELECT * FROM fty_material_in_item WHERE main_id = ?', $_GET['modid']);
            $m_item_num = 0;
            if($rs_m){
                $mod_result_m = $mysql->fetch();
                //20130401 查找出损耗率
                for($i = 0; $i < count($mod_result_m); $i++){
                    $temp = $mysql->qone('select m_loss from fty_material where m_id = ?', $mod_result_m[$i]['mi_id']);
                    $mod_result_m[$i]['mi_loss'] = $temp['m_loss'];
                }

                $m_item_num = count($mod_result_m);
            }

        }else{
            die('Need modid!');
        }


        $goodsForm = new My_Forms();
        $formItems = array(

            'm_in_date' => array('title' => '存仓日期', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['m_in_date'])?substr($mod_result['m_in_date'],0,10):''),

            'remark' => array('title' => '备注', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 2, 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),

            //物料 ： 这个是隐藏起来的
            'g_m_type' => array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"', 'disabled' => 'disabled'),
            'g_m_id_name' => array('type' => 'select', 'options' => '', 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"'),
            'g_m_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'disabled' => 'disabled'),
            'g_m_value' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'disabled' => 'disabled'),
            'g_m_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' 保存 '),
        );

        //material
        for($i = 0; $i < $m_item_num; $i++){
            $formItems['g_m_type'.$i] = array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"', 'value' => isset($mod_result_m[$i]['mi_type'])?$mod_result_m[$i]['mi_type']:'', 'disabled' => 'disabled');
            $formItems['g_m_id_name'.$i] = array('type' => 'select', 'options' => array(array($mod_result_m[$i]['mi_id'].' : '.$mod_result_m[$i]['mi_name'], $mod_result_m[$i]['mi_id'].' : '.$mod_result_m[$i]['mi_name'])), 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"', 'value' => isset($mod_result_m[$i]['mi_id'])?$mod_result_m[$i]['mi_id'].' : '.$mod_result_m[$i]['mi_name']:'');
            $formItems['g_m_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'value' => isset($mod_result_m[$i]['mi_price'])?$mod_result_m[$i]['mi_price']:'');
            $formItems['g_m_value'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'value' => isset($mod_result_m[$i]['mi_value'])?$mod_result_m[$i]['mi_value']:'');
            $formItems['g_m_remark'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'value' => isset($mod_result_m[$i]['mi_remark'])?$mod_result_m[$i]['mi_remark']:'');
        }
        //material 最后一个
        $formItems['g_m_type'.$i] = array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"');
        $formItems['g_m_id_name'.$i] = array('type' => 'select', 'options' => '', 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"', 'disabled' => 'disabled');
        $formItems['g_m_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'disabled' => 'disabled');
        $formItems['g_m_value'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'disabled' => 'disabled');
        $formItems['g_m_remark'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled');

        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){
            //fb($_POST);die('@');

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
                $material_arr[$j]['mi_id'] = $item[$m_index++];
            }

            //fb($material_arr);die('#');

            $result = $mysql->q('update fty_material_in set m_in_date = ?, remark = ?, mod_date = ?, mod_by = ? where id = ?', $m_in_date, $remark, $today, $staff, $_GET['modid']);

            if($result){
                //更改库存
                delWarehouseMaterial('fty_material_in_item', $_GET['modid']);
                $rtn = $mysql->q('delete from fty_material_in_item where main_id = ?', $_GET['modid']);
                foreach($material_arr as $v){
                    $rtn = $mysql->qone('select m_name, m_type, m_color, m_unit, m_loss from fty_material where m_id = ?', $v['mi_id']);
                    $mysql->q('insert into fty_material_in_item set main_id = ?, mi_type = ?, mi_id = ?, mi_name = ?, mi_color = ?, mi_unit = ?, mi_price = ?, mi_value = ?, mi_total = ?, mi_remark = ?', $_GET['modid'], $rtn['m_type'], $v['mi_id'], $rtn['m_name'], $rtn['m_color'], $rtn['m_unit'], $v['price'], $v['value'], round($v['price']*$v['value']*(1+$rtn['m_loss']/100), 2), $v['remark']);
                    //更改库存
                    changeFtyMaterialNum($v['mi_id'], $v['value']);
                }

                $myerror->ok('修改 物料存仓单 成功!', 'search_material_in&page=1');
            }else{
                $myerror->error('修改 物料存仓单 失败', 'BACK');
            }
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
    <h1 class="green">物料存仓单<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>修改</legend>
        <?php
        $goodsForm->begin();
        ?>
        <table width="100%" id="table">
            <tr valign="top" class="formtitle">
                <td width="25%"><div class="set"><label class="formtitle">物料存仓单编号<br /><?php echo $mod_result['mi_id']; ?></label></div></td>
                <td width="25%"><? $goodsForm->show('m_in_date');?></td>
                <td width="50%"><? $goodsForm->show('remark');?></td>
            </tr>
        </table>
        <div class="line"></div>
        <div style="margin-left:28px;">
            <label class="formtitle" for="g_cast"><font size="+1">输入物料</font></label>
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
                    <td width="10%">合计</td>
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
                <?
                for($i = 0; $i < $m_item_num; $i++){
                    ?>
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                        <td id="index" class="dragHandle"></td>
                        <td><? $goodsForm->show('g_m_type'.$i);?></td>
                        <td><? $goodsForm->show('g_m_id_name'.$i);?></td>
                        <td id="color<?=$i?>"><?=$mod_result_m[$i]['mi_color']?></td>
                        <td id="unit<?=$i?>"><?=$mod_result_m[$i]['mi_unit']?></td>
                        <td><? $goodsForm->show('g_m_price'.$i);?></td>
                        <td><? $goodsForm->show('g_m_value'.$i);?></td>
                        <td><div id="m_loss"><?=$mod_result_m[$i]['mi_loss']?></div></td>
                        <td><div id="m_total"><?=$mod_result_m[$i]['mi_total']?></div></td>
                        <td><? $goodsForm->show('g_m_remark'.$i);?></td>
                        <td><div id="del<?=$i?>" onclick="delBomItem(this)"><img src="../../sys/images/del-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Delete" /></div><input type="hidden" id="g_m_id<?=$i?>" name="g_m_id<?=$i?>" value="<?=$mod_result_m[$i]['mi_id']?>" /></td>
                    </tr>
                <?
                }
                //下面是最后一个
                ?>
                <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('g_m_type'.$i);?></td>
                    <td><? $goodsForm->show('g_m_id_name'.$i);?></td>
                    <td id="color<?=$i?>"></td>
                    <td id="unit<?=$i?>"></td>
                    <td><? $goodsForm->show('g_m_price'.$i);?></td>
                    <td><? $goodsForm->show('g_m_value'.$i);?></td>
                    <td><div id="m_loss"></div></td>
                    <td><div id="m_total"></div></td>
                    <td><? $goodsForm->show('g_m_remark'.$i);?></td>
                    <td><div id="del<?=$i?>" onclick="delBomItem(this)"></div><input type="hidden" id="g_m_id<?=$i?>" name="g_m_id<?=$i?>" value="" disabled="disabled"/></td>
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

        searchProduct(15, '');
        searchProduct(15, '1');
        //table tr层表单可拖动
        $("#tableDnD").tableDnD({dragHandle: ".dragHandle"});
    })
</script>