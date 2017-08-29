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
        delWarehouseMaterial('fty_material_buy_item', $_GET['delid']);
        $rs1 = $mysql->q('delete from fty_material_buy_item where main_id = ?', $_GET['delid']);
        $rs2 = $mysql->q('delete from fty_material_buy where id = ?', $_GET['delid']);
        if($rs2){
            $myerror->ok('删除 物料采购单 成功!', 'search_material_buy&page=1');
        }else{
            $myerror->error('删除 物料采购单 失败!', 'search_material_buy&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){

            $mod_result = $mysql->qone('SELECT * FROM fty_material_buy WHERE id = ?', $_GET['modid']);
            $rs_m = $mysql->q('SELECT * FROM fty_material_buy_item WHERE main_id = ?', $_GET['modid']);
            $m_item_num = 0;
            if($rs_m){
                $mod_result_m = $mysql->fetch();
                //20130401 查找出损耗率
                for($i = 0; $i < count($mod_result_m); $i++){
                    $temp = $mysql->qone('select m_loss from fty_material where m_id = ?', $mod_result_m[$i]['m_id']);
                    $mod_result_m[$i]['m_loss'] = $temp['m_loss'];
                }

                $m_item_num = count($mod_result_m);
            }
            //因為一開始沒有attention的選項所以要加上
            $mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));

        }else{
            die('Need modid!');
        }


        $goodsForm = new My_Forms();
        $formItems = array(

            'wlgy_cid' => array('title' => '物料供应商', 'type' => 'select', 'required' => 1, 'options' => get_wlgy_customer(), 'value' => isset($mod_result['cid'])?$mod_result['cid']:''),
            'wlgy_reference' => array('title' => '生产单编号', 'type' => 'select', 'options' => get_fty_purchase(), 'value' => isset($mod_result['reference'])?$mod_result['reference']:''),
            'wlgy_attention' => array('title' => '联络人', 'type' => 'select', 'required' => 1, 'options' => $mod_customer_contact, 'value' => isset($mod_result['attention'])?$mod_result['attention']:''),

            'wlgy_expected_date' => array('title' => '预计交料日期', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['expected_date'])?substr($mod_result['expected_date'],0,10):''),

            'wlgy_remark' => array('title' => '备注', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200,
                'addon' => 'style="width:400px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
            'wlgy_address' => array('title' => '地址', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2, 'value' => isset($mod_result['address'])?$mod_result['address']:''),

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
            $formItems['g_m_type'.$i] = array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"', 'value' => isset($mod_result_m[$i]['m_type'])?$mod_result_m[$i]['m_type']:'', 'disabled' => 'disabled');
            $formItems['g_m_id_name'.$i] = array('type' => 'select', 'options' => array(array($mod_result_m[$i]['m_id'].' : '.$mod_result_m[$i]['m_name'], $mod_result_m[$i]['m_id'].' : '.$mod_result_m[$i]['m_name'])), 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"', 'value' => isset($mod_result_m[$i]['m_id'])?$mod_result_m[$i]['m_id'].' : '.$mod_result_m[$i]['m_name']:'');
            $formItems['g_m_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'value' => isset($mod_result_m[$i]['m_price'])?$mod_result_m[$i]['m_price']:'');
            $formItems['g_m_value'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'value' => isset($mod_result_m[$i]['m_value'])?$mod_result_m[$i]['m_value']:'');
            $formItems['g_m_remark'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'value' => isset($mod_result_m[$i]['m_remark'])?$mod_result_m[$i]['m_remark']:'');
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

            $result = $mysql->q('update fty_material_buy set cid = ?, attention = ?, address = ?, reference = ?, expected_date = ?, remark = ?, mod_date = ?, mod_by = ? where id = ?', $wlgy_cid, $wlgy_attention, $wlgy_address, $wlgy_reference, $wlgy_expected_date, $wlgy_remark, $today, $staff, $_GET['modid']);

            if($result){
                //更改库存
                delWarehouseMaterial('fty_material_buy_item', $_GET['modid']);
                $rtn = $mysql->q('delete from fty_material_buy_item where main_id = ?', $_GET['modid']);
                foreach($material_arr as $v){
                    $rtn = $mysql->qone('select m_name, m_type, m_color, m_unit, m_loss from fty_material where m_id = ?', $v['m_id']);
                    $mysql->q('insert into fty_material_buy_item set main_id = ?, m_type = ?, m_id = ?, m_name = ?, m_color = ?, m_unit = ?, m_price = ?, m_value = ?, m_total = ?, m_remark = ?', $_GET['modid'], $rtn['m_type'], $v['m_id'], $rtn['m_name'], $rtn['m_color'], $rtn['m_unit'], $v['price'], $v['value'], round($v['price']*$v['value']*(1+$rtn['m_loss']/100), 2), $v['remark']);
                    //更改库存
                    changeFtyMaterialNum($v['m_id'], $v['value']);
                }

                $myerror->ok('修改 物料采购单 成功!', 'search_material_buy&page=1');
            }else{
                $myerror->error('修改 物料采购单 失败', 'BACK');
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
    <h1 class="green">物料采购单<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>修改</legend>
        <?php
        $goodsForm->begin();
        ?>
        <table width="100%" id="table">
            <tr class="formtitle">
                <td width="25%"><div class="set"><label class="formtitle">物料采购单编号<br /><?php echo $mod_result['m_id']; ?></label></div></td>
                <td width="25%"><? $goodsForm->show('wlgy_reference');?></td>
                <td width="25%"><? $goodsForm->show('wlgy_cid');?></td>
                <td width="25%"><? $goodsForm->show('wlgy_attention');?></td>
            </tr>
            <tr>
                <td width="25%" colspan="2"><? $goodsForm->show('wlgy_address');?></td>
                <td width="25%" valign="top"><? $goodsForm->show('wlgy_expected_date');?></td>
                <td width="25%" valign="top"></td>
            </tr>
            <tr>
                <td width="25%" colspan="2"><? $goodsForm->show('wlgy_remark');?></td>
                <td width="25%"></td>
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
                        <td id="color<?=$i?>"><?=$mod_result_m[$i]['m_color']?></td>
                        <td id="unit<?=$i?>"><?=$mod_result_m[$i]['m_unit']?></td>
                        <td><? $goodsForm->show('g_m_price'.$i);?></td>
                        <td><? $goodsForm->show('g_m_value'.$i);?></td>
                        <td><div id="m_loss"><?=$mod_result_m[$i]['m_loss']?></div></td>
                        <td><div id="m_total"><?=$mod_result_m[$i]['m_total']?></div></td>
                        <td><? $goodsForm->show('g_m_remark'.$i);?></td>
                        <td><div id="del<?=$i?>" onclick="delBomItem(this)"><img src="../../sys/images/del-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Delete" /></div><input type="hidden" id="g_m_id<?=$i?>" name="g_m_id<?=$i?>" value="<?=$mod_result_m[$i]['m_id']?>" /></td>
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

        $("#wlgy_cid").not('.special').selectbox();
        $("#wlgy_attention").not('.special').selectbox();

        selectFtyCustomer("wlgy_");
    })
</script>