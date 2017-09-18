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

    //應該要加一個 pcid 這樣的不允許重複的 blur事件，來判斷是否輸入的已經在數據庫中存在。應儘量減少提交失敗的情況，否則填半天都白填了。。。！！
    //'wlgy_pcid' => array('title' => 'Factory PO No.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20/*, 'restrict' => 'judgexid'*/, 'required' => 1),
    'wlgy_cid' => array('title' => '物料供应商', 'type' => 'select', 'required' => 1, 'options' => get_wlgy_customer()),
    //'wlgy_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
    'wlgy_reference' => array('title' => '生产单编号', 'type' => 'select', 'options' => get_fty_purchase(), 'required' => 1),
    'wlgy_attention' => array('title' => '联络人', 'type' => 'select', 'required' => 1, 'options' => ''),

    'wlgy_expected_date' => array('title' => '预计交料日期', 'type' => 'text', 'restrict' => 'date', 'required' => 1),

    'wlgy_remark' => array('title' => '备注', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200,
        'addon' => 'style="width:400px"'),
    'wlgy_address' => array('title' => '地址', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2),

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

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
    //fb($_POST);die('@');

    $m_id = fty_autoGenerationID();

    fb($m_id);

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
    <h1 class="green">物料采购单<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>新增</legend>

        <? /*
<fieldset> 
<legend class='legend'>Selected products</legend>
<?
if( isset($_SESSION['choose']) && !empty($_SESSION['choose'])){
	foreach($_SESSION['choose'] as $v){
		if (is_file($pic_path_com.$v) == true) { 
			$arr = getimagesize($pic_path_com.$v);
			$pic_width = $arr[0];
			$pic_height = $arr[1];
			$image_size = getimgsize(150, 100, $pic_width, $pic_height);
			echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com.$v.'" class="tooltip2" target="_blank" title="'.$v.'"><img src="/sys/'.$pic_path_com.$v.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">【DELETE】</a></b></div>';
		}else{
			echo '<div class="shadow" style="margin-left:28px;"><ul><li><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">【DELETE】</a></b></div>';
		}
	}
}else{
	echo '<div style="margin-left:28px;"><b>Not Exist Checked Product</b></div>';
}
?>
</fieldset>
*/
        ?>

        <?php
        $goodsForm->begin();
        ?>
        <table width="100%" id="table">
            <tr class="formtitle">
                <td width="25%"><div class="set"><label class="formtitle">物料采购单编号</label><br />(自动生成)
                    </div></td>
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

        searchProduct(15, '');
        searchProduct(15, '1');
        //table tr层表单可拖动
        $("#tableDnD").tableDnD({dragHandle: ".dragHandle"});

        $("#wlgy_cid").not('.special').selectbox();
        $("#wlgy_attention").not('.special').selectbox();

        selectFtyCustomer("wlgy_");
    })
</script>