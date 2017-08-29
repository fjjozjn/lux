<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。
2011-02-22		增加overseas选项，与idcard联动，可以令海外人士不必填写身份证。修改address为textarea，及相关版式。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(isset($_GET['delimg']) && $_SESSION['upload_photo'] != ''){
	unlink($_SESSION['upload_photo']);
	$_SESSION['upload_photo'] = '';
	$myerror->ok('刪除上传的图片 成功!', 'BACK');
}

//添加的前一个bom 编号
$prev_no = $mysql->qone('select g_id from bom order by g_time desc limit 1');

$image_path = '';
		
$goodsForm = new My_Forms();

$formItems = array(
		'g_id' => array('title' => '产品编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'onblur="judgeBomID(this)"' , 'value' => isset($mod_result['g_id'])?$mod_result['g_id']:'', 'info' => '(上一个'.$prev_no['g_id'].')'),
		'g_type' => array('title' => '类别', 'type' => 'select', 'options' => get_bom_lb(1), 'addon' => 'style="width:200px"'),
		'g_material' => array('title' => '底材用料', 'type' => 'select', 'options' => get_bom_dcyl(1), 'addon' => 'style="width:200px"'),
		'g_size' => array('title' => '尺码', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '请注明单位'),
		//填写的数字允许小数吗？管他是整数还是小数，全部用字符串处理，我勒个去
		'g_gem_num' => array('title' => '成品总石数', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：粒'),
		'g_cast' => array('title' => '铸件数量', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：件'),
		'g_plating' => array('title' => '电镀描述', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'g_weight' => array('title' => '重量', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：克'),
		'g_ccode' => array('title' => '客号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		'g_sample_order_no' => array('title' => '板单编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
		
		//'m_id' => array('title' => '物料编号 <font size="-1">（输入框中填写<b> 数量/重量的值 </b>，只有选中后，输入框才能使用。点击小图标显示详情）</font>', 'type' => 'checkbox', 'options' => $m_id, 'fatherclass' => 'lux', 'addinput' => 1, 'modid' => isset($mod_result['id'])?$mod_result['id']:'', 'mytype' => 'm'),
		
		//'t_id' => array('title' => '工序号 <font size="-1">（输入框中填写<b> 工时 </b>，只有选中后，输入框才能使用。点击小图标显示详情）</font>', 'type' => 'checkbox', 'options' => $t_id, 'fatherclass' => 'lux', 'addinput' => 1, 'modid' => isset($mod_result['id'])?$mod_result['id']:'', 'mytype' => 't'),
		
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
		
		//工序 ：　这个是隐藏起来的
		'g_t_type_name' => array('type' => 'select', 'options' => $t_type, 'addon' => 'onchange="searchTask(this)"', 'disabled' => 'disabled'),
		'g_t_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskPriceBlur(this)"', 'disabled' => 'disabled'),
		'g_t_time' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskTimeBlur(this)"', 'disabled' => 'disabled'),
		'g_t_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),
		
		//工序　：　这个是显示的第一个
		'g_t_type_name1' => array('type' => 'select', 'options' => $t_type, 'addon' => 'onchange="searchTask(this)"'),
		'g_t_price1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskPriceBlur(this)"', 'disabled' => 'disabled'),
		'g_t_time1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskTimeBlur(this)"', 'disabled' => 'disabled'),
		'g_t_remark1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),
		
		
		// 這些是用radio還是checkbox，即是是可以多選還是單選？（用checkbox就不必一定要选择）
		'process' => array('title' => '表面处理', 'type' => 'checkbox', 'options' => $process),
		'electroplate' => array('title' => '电镀', 'type' => 'checkbox', 'options' => $electroplate),
		'electroplate_thick' => array('title' => '电镀厚度', 'type' => 'checkbox', 'options' => $electroplate_thick),
		'other'	=> array('title' => '其他', 'type' => 'checkbox', 'options' => $other),

		'p_plate' => array('title' => '电镀人工价', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'onblur="updateBomTotal()"', 'value' => '0'),
		'p_other' => array('title' => '其他成本', 'type' => 'text', 'restrict' => 'number',  'minlen' => 1, 'maxlen' => 20, 'addon' => 'onblur="updateBomTotal()"', 'value' => '0'),
		'p_profit' => array('title' => '利润(倍) ', 'type' => 'text', 'restrict' => 'number',  'minlen' => 1, 'maxlen' => 20, 'addon' => 'onblur="updateBomTotal()"', 'value' => '1.0'),
		
		'submitbtn'	=> array('type' => 'submit', 'value' => ' 确定 '),
		);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
	//先设定一定要上传图片，以后有需要可以为空，但是要屏蔽处理图片函数的报错
	//if( !isset($_SESSION['upload_photo']) || $_SESSION['upload_photo'] == ''){
		//$myerror->error('必须先上传图片', 'sendform');	
	//}else{
	
	//fb($_POST);
	
		$g_id = $_POST['g_id'];//autoGenerationID();
	//fb($g_id);
		$g_type = $_POST['g_type'];
		$g_material = $_POST['g_material'];
		$g_size = $_POST['g_size'];
		$g_gem_num = $_POST['g_gem_num'];
		$g_cast = $_POST['g_cast'];
		$g_plating = $_POST['g_plating'];
		$g_weight = $_POST['g_weight'];
		$g_ccode = $_POST['g_ccode'];
		$g_sample_order_no = $_POST['g_sample_order_no'];
		
		//因为checkbox一个都没有选的时候是没有post数据的，所以这里要记录checkbox有post的数据，为下面计算除物料和工序外的post个数
		$checkbox_num = 0;
		if(isset($_POST['process'])) $checkbox_num++;	
		if(isset($_POST['electroplate'])) $checkbox_num++;
		if(isset($_POST['electroplate_thick'])) $checkbox_num++;
		if(isset($_POST['other'])) $checkbox_num++;
		
		$i = 0;
		$sign = 0;//标志是将$_POST的值赋给material还是task
		$material = array();
		$task = array();
		foreach( $_POST as $key=>$v){
			//前面9个是非物料和工序的，不处理，所以是序号小于等于8。20120929 加了客号和版单编号，所以是10
			if( $i <= 10){
				$i++;
			}
			//后面有7个（有3个是隐藏的）是非物料和工序的，不处理
			elseif( $i >= count($_POST) - (7 + $checkbox_num) ){
				$i++;
			}else{
				if(strpos($key, 'g_m_type') !== false){
					$sign = 1;
					$material[] = $v;
					$i++;
					continue;
				}
				if($sign == 0){
					$material[] = $v;
				}else{
					$task[]	= $v;
				}
				$i++;	
			}
		}
	
	//fb($material);
	//fb($task);
	
		$m_num = intval(count($material) / 4);
		//整理好的material数据
		$material_arr = array();
		$m_index = 0;
		for($i = 0; $i < $m_num; $i++){
			$material_arr[$i]['price'] = $material[$m_index++];
			$material_arr[$i]['value'] = $material[$m_index++];
			$material_arr[$i]['remark'] = $material[$m_index++];
			$material_arr[$i]['m_id'] = $material[$m_index++];
		}
		
		$t_num = intval(count($task) / 4);
		//整理好的task数据
		$task_arr = array();
		$t_index = 0;
		for($i = 0; $i < $t_num; $i++){
			$task_arr[$i]['price'] = $task[$t_index++];
			$task_arr[$i]['time'] = $task[$t_index++];
			$task_arr[$i]['remark'] = $task[$t_index++];
			$task_arr[$i]['t_id'] = $task[$t_index++];
		}
				
	//fb($material_arr);
	//fb($task_arr);
	//die();
	
		$process = '';
		if(isset($_POST['process'])){
			if(count($_POST['process']) == 1 && !is_array($_POST['process'])){
				$process = $_POST['process'];
			}else{
				for($i = 0; $i < count($_POST['process']); $i++){
					if($i == 0){
						$process = $_POST['process'][$i];
						continue;
					}
					$process .= '|' . $_POST['process'][$i];
				}
			}
		}		
		
		$electroplate = '';
		if(isset($_POST['electroplate'])){
			if(count($_POST['electroplate']) == 1 && !is_array($_POST['electroplate'])){
				$electroplate = $_POST['electroplate'];
			}else{
				for($i = 0; $i < count($_POST['electroplate']); $i++){
					if($i == 0){
						$electroplate = $_POST['electroplate'][$i];
						continue;
					}
					$electroplate .= '|' . $_POST['electroplate'][$i];
				}
			}
		}
		
		$electroplate_thick = '';
		if(isset($_POST['electroplate_thick'])){
			if(count($_POST['electroplate_thick']) == 1 && !is_array($_POST['electroplate_thick'])){
				$electroplate_thick = $_POST['electroplate_thick'];
			}else{
				for($i = 0; $i < count($_POST['electroplate_thick']); $i++){
					if($i == 0){
						$electroplate_thick = $_POST['electroplate_thick'][$i];
						continue;
					}
					$electroplate_thick .= '|' . $_POST['electroplate_thick'][$i];
				}
			}
		}
		
		$other = '';
		if(isset($_POST['other'])){
			if(count($_POST['other']) == 1 && !is_array($_POST['other'])){
				$other = $_POST['other'];
			}else{
				for($i = 0; $i < count($_POST['other']); $i++){
					if($i == 0){
						$other = $_POST['other'][$i];
						continue;
					}
					$other .= '|' . $_POST['other'][$i];
				}
			}
		}

		//电镀人工价
		$p_plate = $_POST['p_plate'];
		
		//其他成本
		$p_other = $_POST['p_other'];
		
		//利润
		$p_profit = $_POST['p_profit'];
		
		//！！！合计（在js中计算并赋值给hidden input，这里获取的是其post的值）
		//加 round 是防止在js计算中出现 97.96000000000001 这类值，多了00...001
		$m_total = round($_POST['post_m_total'], 2);
		$t_total = round($_POST['post_t_total'], 2);
		$p_total = round($_POST['post_total'], 2);
		
		$created_by = $_SESSION["logininfo"]["aName"];//$_POST['people_h'];
		
		$time = dateMore();
		
		$photo = '';
		$mysql_photo = '';
		if(isset($_SESSION['upload_photo']) && $_SESSION['upload_photo'] != ''){
			$temp = explode('.', $_SESSION['upload_photo']);
			$photo = str_replace('/temp/', '/photo/', $_SESSION['upload_photo']);
			$mysql_photo = 'upload/mysql/'.time().'.'.$temp[1];
		}
		
		


		$result = $mysql->q('insert into bom values (NULL, '.moreQm(25).')', $time, $g_id, $g_type, $g_material, $g_size, $g_gem_num, $g_cast, $g_plating, $g_weight, $photo, $mysql_photo, $process, $electroplate, $electroplate_thick, $other, $m_total, $t_total, $p_plate, $p_other, $p_profit, $p_total, $created_by, '未完成', $g_ccode, $g_sample_order_no);
		if($result){
			$id = intval($result);
			//写入数据到关系表bom_material
			foreach($material_arr as $v){
				$rtn = $mysql->qone('select m_name, m_type, m_color, m_unit from material where m_id = ?', $v['m_id']);
				$mysql->q('insert into bom_material values (NULL, '.moreQm(10).')', $id, $rtn['m_type'], $v['m_id'], $rtn['m_name'], $rtn['m_color'], $rtn['m_unit'], $v['price'], $v['value'], $v['price']*$v['value'], $v['remark']);
			}
			//写入数据到关系表bom_task
			foreach($task_arr as $v){
				$rtn = $mysql->qone('select t_name from task where t_id = ?', $v['t_id']);
				$mysql->q('insert into bom_task values (NULL, '.moreQm(7).')', $id, $v['t_id'], $rtn['t_name'], $v['price'], $v['time'], $v['price']*$v['time'], $v['remark']);
			}
			
			if(is_int($id) && $id > 0){
				@copy(iconv('UTF-8','GBK', $_SESSION['upload_photo']), iconv('UTF-8','GBK', $photo));
				@copy(iconv('UTF-8','GBK', $_SESSION['upload_photo']), iconv('UTF-8','GBK', $mysql_photo));
				@unlink($_SESSION['upload_photo']);
				$_SESSION['upload_photo'] = '';
				$myerror->ok('新增产品资料 成功!', 'searchform&page=1');	
			}else{
				$myerror->error('由于返回值异常，新增产品资料 失败', 'sendform');	
			}
		}else{
			$myerror->error('由于系统原因，新增产品资料 失败', 'sendform');	
		}
		
	//}
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
<h1 class="green">填写物料清单（BOM）<em>*号为必填项</em></h1>
<fieldset class="center2col">
<legend class='legend'>物料清单（BOM）</legend>

<fieldset class="center2col"> 
<legend class='legend'>第一步：上传图片（注：一张表格只能添加一张图片，档案名称以货号命名，如：N12345.jpg）</legend>
<?
if(isset($_SESSION['upload_photo']) && $_SESSION['upload_photo'] != ''){
	$arr = getimagesize($_SESSION['upload_photo']);
	$pic_width = $arr[0];
	$pic_height = $arr[1];
	$image_size = getimgsize(100, 60, $pic_width, $pic_height);
	echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/' . $_SESSION['upload_photo'].'" class="tooltip2" target="_blank" title="'.$_SESSION['upload_photo'].'"><img src="/sys/' . $_SESSION['upload_photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
	echo "<b><a class='button' href='?act=upload_photo'>CHANGE</a></b></div>";
}else{
	echo "<div style='margin-left:28px;'><ul><li><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><a class='button' href='?act=upload_photo'>上传图片</a></div>";
}
?>
</fieldset>
<div class="line"></div>
<fieldset class="center2col"> 
<legend class='legend'>第二步：填写并提交</legend>
<?php
$goodsForm->begin();
?>
<table width="90%" id="table">
	<tr valign="top">
      	<td width="25%"><? $goodsForm->show('g_id');?></td>
        <td width="25%"><? $goodsForm->show('g_type');?></td>
        <td width="25%"><? $goodsForm->show('g_material');?></td>
        <td width="25%"><? $goodsForm->show('g_size');?></td>
	</tr>
    <tr valign="top">
      	<td width="25%"><? $goodsForm->show('g_gem_num');?></td>
        <td width="25%"><? $goodsForm->show('g_cast');?></td>
        <td width="25%"><? $goodsForm->show('g_plating');?></td>
        <td width="25%"><? $goodsForm->show('g_weight');?></td>    
    </tr>
    <tr valign="top">
      	<td width="25%"><? $goodsForm->show('g_ccode');?></td>
        <td width="25%"><? $goodsForm->show('g_sample_order_no');?></td>
        <td width="25%">&nbsp;</td>
        <td width="25%">&nbsp;</td>    
    </tr>
</table>
<div class="line"></div>
<?php
//$goodsForm->show('m_id', '<div class="line"></div>');
//$goodsForm->show('t_id', '<div class="line"></div>');
?>
<div style="margin-left:28px;">
<label class="formtitle" for="g_cast">物料</label>
<table width="100%" id="table">
<tbody id="tbody">
  <tr class="formtitle">
    <td>类别</td>
    <td>物料编号：名称</td>
    <td>规格颜色</td>
    <td>单位</td>
    <td>单价</td>
    <td>数量/重量值</td>
    <td width="10%">价格</td>
    <td width="20%">备注</td>
    <td width="5%"></td>
  </tr>
  <tr class="template repeat">
    <td><? $goodsForm->show('g_m_type');?></td>
    <td><? $goodsForm->show('g_m_id_name');?></td>
    <td id="color"></td>
    <td id="unit"></td>
    <td><? $goodsForm->show('g_m_price');?></td>
    <td><? $goodsForm->show('g_m_value');?></td>
    <td><div id="m_total"></div></td>
    <td><? $goodsForm->show('g_m_remark');?></td>
    <td><div id="del" onclick="delBomItem(this)"></div><input type="hidden" id="g_m_id" name="g_m_id" value="" disabled="disabled"/></td>
  </tr>
  <tr class="repeat">
    <td><? $goodsForm->show('g_m_type1');?></td>
    <td><? $goodsForm->show('g_m_id_name1');?></td>
    <td id="color1"></td>
    <td id="unit1"></td>
    <td><? $goodsForm->show('g_m_price1');?></td>
    <td><? $goodsForm->show('g_m_value1');?></td>
    <td><div id="m_total"></div></td>
    <td><? $goodsForm->show('g_m_remark1');?></td>
    <td><div id="del1" onclick="delBomItem(this)"></div><input type="hidden" id="g_m_id1" name="g_m_id1" value="" disabled="disabled"/></td>
  </tr>  
</tbody> 
</table>
</div>
<div class="line"></div>
<div style="margin-left:28px;">
<label class="formtitle" for="g_cast">工序</label>
<table width="100%">
<tbody id="tbody1">
  <tr class="formtitle">
    <td>工序号：工序名称</td>
    <td>工价</td>
    <td>工时</td>
    <td width="10%">价格</td>
    <td width="20%">备注</td>
    <td width="5%"></td>
  </tr>
  <tr class="template repeat">
    <td><? $goodsForm->show('g_t_type_name');?></td>
    <td><? $goodsForm->show('g_t_price');?></td>
    <td><? $goodsForm->show('g_t_time');?></td>
    <td><div id="t_total"></div></td>
    <td><? $goodsForm->show('g_t_remark');?></td>
    <td><div id="del" onclick="delBomItem(this)"></div><input type="hidden" id="g_t_id" name="g_t_id" value="" disabled="disabled"/></td>
  </tr>
  <tr class="repeat">
    <td><? $goodsForm->show('g_t_type_name1');?></td>
    <td><? $goodsForm->show('g_t_price1');?></td>
    <td><? $goodsForm->show('g_t_time1');?></td>
    <td><div id="t_total"></div></td>
    <td><? $goodsForm->show('g_t_remark1');?></td>
    <td><div id="del1" onclick="delBomItem(this)"></div><input type="hidden" id="g_t_id1" name="g_t_id1" value="" disabled="disabled"/></td>
  </tr>  
</tbody>
</table>
</div>
<div class="line"></div>
<?
$goodsForm->show('process');
$goodsForm->show('electroplate');
$goodsForm->show('electroplate_thick');
$goodsForm->show('other', '<div class="line"></div>');
?>

<div class="set"><label class="formtitle">物料总价</label><br /><div id="materialTotal">0.00</div></div>
<div class="set"><label class="formtitle">工序总价</label><br /><div id="taskTotal">0.00</div></div>
<?
//$goodsForm->show('p_labour');
//$goodsForm->show('p_workpiece');
$goodsForm->show('p_plate');
//$goodsForm->show('p_stone');
//$goodsForm->show('p_parts');
$goodsForm->show('p_other');
$goodsForm->show('p_profit');
?>
<div class="set"><label class="formtitle">总价</label><br /><div id="allTotal" style="color:#F00; font-size:24px">0.00</div><input type="hidden" name="post_m_total" id="post_m_total" value="" /><input type="hidden" name="post_t_total" id="post_t_total" value="" /><input type="hidden" name="post_total" id="post_total" value="" /></div>
<div class="line"></div>
<?
//$goodsForm->show('p_total', '<div class="line"></div>');

//$goodsForm->show('people_h', '<div class="line"></div>');
//$goodsForm->show('people_a', '<div class="line"></div>');

$goodsForm->show('submitbtn');
?>
</fieldset>
</fieldset>
<?
	$goodsForm->end();
}
?>


<script>
$(function(){
	$(".template").hide()	
})

function judgeBomID(obj){
	var my = $(obj);
	var my_val = my.val();
	my_val = my_val.trim();
	my_val = my_val.trim_tab();
	my.val(my_val);
	my_val = my.val();
	if(my_val != ''){
		var qs = 'ajax=1&act=ajax-judge_bom_id&&value='+my_val;
		$.ajax({
			type: "GET",
			url: "index.php",
			data: qs,
			cache: false,
			dataType: "html",
			error: function(){
				alert('系统错误，查询bom id失败');
			},
			success: function(data){
				if(data.indexOf('yes') >= 0){
					//不输出任何东西
				}else if(data.indexOf('no-') >= 0){
					alert('此 BOM ID 已存在，请输入其他 ID !');
					my.val('');//id已存在则清空输入框
				}
			}
		})		
	}
}
</script>