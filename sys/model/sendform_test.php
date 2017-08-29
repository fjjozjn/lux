<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。
2011-02-22		增加overseas选项，与idcard联动，可以令海外人士不必填写身份证。修改address为textarea，及相关版式。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$Pic = '';

if(isset($_GET['delimg']) && $_SESSION['upload_photo'] != ''){
	unlink($_SESSION['upload_photo']);
	$_SESSION['upload_photo'] = '';
	$myerror->ok('刪除上传的图片 成功!', 'BACK');
}

if(isset($_GET['modid']) && $_GET['modid'] != ''){
	$mod_result = $mysql->qone('SELECT * FROM goodsform WHERE id = ?', $_GET['modid']);	
	
	$m_id_array = explode('|', $mod_result['m_id']); /*($mod_result['m_id'] != '')?explode('|', $mod_result['m_id']):'';*/
	$t_id_array = explode('|', $mod_result['t_id']); /*($mod_result['t_id'] != '')?explode('|', $mod_result['t_id']):'';*/
	
	$process_array = explode('|', $mod_result['g_process']);
	$electroplate_array = explode('|', $mod_result['electroplate']);
	$electroplate_thick_array = explode('|', $mod_result['electroplate_thick']);
	$other_array = explode('|', $mod_result['other']);
	
}

if(isset($_GET['delid']) && $_GET['delid'] != ''){

	$rs = $mysql->q('DELETE FROM goodsform WHERE id = ?', $_GET['delid']);
	if($rs){
		$myerror->ok('刪除产品资料表资料 成功!', 'main');	
	}else{
		$myerror->error('由于系统原因，刪除产品资料表资料 失败', 'main');	
	}

}

$image_path = '';
		
$goodsForm = new My_Forms();

$formItems = array(
		'g_id' => array('title' => '货品编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1/*, 'addon' => 'style="width:100px"' */, 'value' => isset($mod_result['g_id'])?$mod_result['g_id']:''),
		'g_type' => array('title' => '类别', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['g_type'])?$mod_result['g_type']:''),
		'g_material' => array('title' => '底材用料', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['g_material'])?$mod_result['g_material']:''),
		'g_size' => array('title' => '尺码', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'value' => isset($mod_result['g_size'])?$mod_result['g_size']:''),
		//填写的数字允许小数吗？管他是整数还是小数，全部用字符串处理，我勒个去
		'g_gem_num' => array('title' => '成品总石数', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：粒', 'value' => isset($mod_result['g_gem_num'])?$mod_result['g_gem_num']:''),
		'g_cast' => array('title' => '铸件', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：件', 'value' => isset($mod_result['g_cast'])?$mod_result['g_cast']:''),
		'g_plating' => array('title' => '电镀', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['g_plating'])?$mod_result['g_plating']:''),
		'g_weight' => array('title' => '重量', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：克', 'value' => isset($mod_result['g_weight'])?$mod_result['g_weight']:''),
		
		'm_id' => array('title' => '物料编号 <font size="-1">（输入框中填写<b> 数量/重量的值 </b>，只有选中后，输入框才能使用。点击小图标显示详情）</font>', 'type' => 'checkbox', 'options' => $m_id, 'fatherclass' => 'lux', 'addinput' => 1, 'modid' => isset($mod_result['id'])?$mod_result['id']:'', 'mytype' => 'm', 'value' => isset($m_id_array)?$m_id_array:''),
		
		't_id' => array('title' => '件工序号 <font size="-1">（输入框中填写<b> 工时 </b>，只有选中后，输入框才能使用。点击小图标显示详情）</font>', 'type' => 'checkbox', 'options' => $t_id, 'fatherclass' => 'lux', 'addinput' => 1, 'modid' => isset($mod_result['id'])?$mod_result['id']:'', 'mytype' => 't','value' => isset($t_id_array)?$t_id_array:''),
		
		// 這些是用radio還是checkbox，即是是可以多選還是單選？（用checkbox就不必一定要选择）
		'process' => array('title' => '工序', 'type' => 'checkbox', 'options' => $process, 'value' => isset($process_array)?$process_array:''),
		'electroplate' => array('title' => '电镀', 'type' => 'checkbox', 'options' => $electroplate, 'value' => isset($electroplate_array)?$electroplate_array:''),
		'electroplate_thick' => array('title' => '电镀厚度', 'type' => 'checkbox', 'options' => $electroplate_thick, 'value' => isset($electroplate_thick_array)?$electroplate_thick_array:''),
		'other'	=> array('title' => '其他', 'type' => 'checkbox', 'options' => $other, 'value' => isset($other_array)?$other_array:''),
		
		//'p_labour' => array('title' => '人工', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_labour'])?$mod_result['p_labour']:''),
		//'p_workpiece' => array('title' => '工件', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_workpiece'])?$mod_result['p_workpiece']:''),
		'p_plate' => array('title' => '电镀', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_plate'])?$mod_result['p_plate']:''),
		//'p_stone' => array('title' => '石料', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_stone'])?$mod_result['p_stone']:''),
		//'p_parts' => array('title' => '配件', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_parts'])?$mod_result['p_parts']:''),
		//'p_other' => array('title' => '其他', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_other'])?$mod_result['p_other']:''),
		'p_other2' => array('title' => '其他2', 'type' => 'text', 'restrict' => 'number',  'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_other2'])?$mod_result['p_other2']:''),
		'p_profit' => array('title' => '利润', 'type' => 'text', 'restrict' => 'number',  'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_profit'])?$mod_result['p_profit']:''),
		//'p_total' => array('title' => '合计', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_total'])?$mod_result['p_total']:''),
		
		'people_h' => array('title' => '经手人', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['people_h'])?$mod_result['people_h']:''),
		'people_a' => array('title' => '审核', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['people_a'])?$mod_result['people_a']:''),
		
		'submitbtn'	=> array('type' => 'submit', 'value' => ' 确定 '),
		);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
	//先设定一定要上传图片，以后有需要可以为空，但是要屏蔽处理图片函数的报错
	//if( !isset($_SESSION['upload_photo']) || $_SESSION['upload_photo'] == ''){
		//$myerror->error('必须先上传图片', 'sendform');	
	//}else{
	
		$g_id = $_POST['g_id'];
		$g_type = $_POST['g_type'];
		$g_material = $_POST['g_material'];
		$g_size = $_POST['g_size'];
		$g_gem_num = $_POST['g_gem_num'];
		$g_cast = $_POST['g_cast'];
		$g_plating = $_POST['g_plating'];
		$g_weight = $_POST['g_weight'];
		
		$m_id = '';
		$m_value = '';
		$m_workpiece_sum = 0;
		$m_stone_sum = 0;
		$m_parts_sum = 0;
		$m_other_sum = 0;
		
		//当m_id或t_id总数（不是指选择一个）只有一个的时候，$_POST['m_id']就不是数组，而只是一个字符串，所以要先做这样的处理		
		$post_m_id = '';
		if( isset($_POST['m_id'])){
			if( count($_POST['m_id']) == 1 && !is_array($_POST['m_id'])){
				$post_m_id = array( $_POST['m_id']);//字符串包成数组，后面处理会方便一点
			}else{
				$post_m_id = $_POST['m_id'];
			}

			for($i = 0; $i < count($post_m_id); $i++){
				$m_id .= ($i!=0?'|':'') . $post_m_id[$i];
				$m_value .= ($i!=0?'|':'') . $_POST[$post_m_id[$i]];
			}
		
			//计算”工件”, “石料”, 配件”, “其他”（各项之和，没有则为0）
			$temp_m = explode('|', $m_id);
			$temp_m_value = explode('|', $m_value);
			$temp_m_i = 0;
			foreach($temp_m as $v){
				$rs_m = $mysql->qone('select * from material where m_id = ?', $v);
				if($rs_m['m_type'] == '工件'){
					$m_workpiece_sum += ($rs_m['m_price'] + 0) * ($temp_m_value[$temp_m_i] + 0);
				}
				if($rs_m['m_type'] == '石料'){
					$m_stone_sum += ($rs_m['m_price'] + 0) * ($temp_m_value[$temp_m_i] + 0);
				}
				if($rs_m['m_type'] == '配件'){
					$m_parts_sum += ($rs_m['m_price'] + 0) * ($temp_m_value[$temp_m_i] + 0);
				}
				if($rs_m['m_type'] == '其他'){
					$m_other_sum += ($rs_m['m_price'] + 0) * ($temp_m_value[$temp_m_i] + 0);
				}
				$temp_m_i++;
			}
		}
		
		
		$t_id = '';
		$t_value = '';
		$t_sum = 0;
		$post_t_id = '';
		if( isset($_POST['t_id'])){
			if( count($_POST['t_id']) == 1 && !is_array($_POST['t_id'])){
				$post_t_id = array( $_POST['t_id']);//字符串包成数组，后面处理会方便一点
			}else{
				$post_t_id = $_POST['t_id'];
			}
			
			for($i = 0; $i < count($post_t_id); $i++){
				$t_id .= ($i!=0?'|':'') .$post_t_id[$i];
				$t_value .= ($i!=0?'|':'') . $_POST[$post_t_id[$i]];
			}
			
			//计算人工（如果有多条则取所有之和为最后的人工值，如果无则为0）
			$temp_t = explode('|', $t_id);
			$temp_t_value = explode('|', $t_value);
			$temp_t_i = 0;
			foreach($temp_t as $v){
				$rs_t = $mysql->qone('select t_price, t_time from task where t_id = ?', $v);
				$t_sum += ($rs_t['t_price'] + 0) * ($temp_t_value[$temp_t_i] + 0);
				$temp_t_i++;
			}
			
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
	
		$p_labour = $t_sum;
		$p_workpiece = $m_workpiece_sum;
		if($_POST['p_plate'] == ''){
			$p_plate = 0;
		}else{
			$p_plate = $_POST['p_plate'];
		}
		$p_stone = $m_stone_sum;
		$p_parts = $m_parts_sum;
		$p_other = $m_other_sum;
		if($_POST['p_other2'] == ''){
			$p_other2 = 0;
		}else{
			$p_other2 = $_POST['p_other2'];
		}
		if($_POST['p_profit'] == ''){
			$p_profit = 1;
		}else{
			$p_profit = $_POST['p_profit'];
		}
		//算合计
		$p_total = ($p_labour + $p_workpiece + ($p_plate + 0) + $p_stone + $p_parts + $p_other + ($p_other2 + 0)) * $p_profit;
		
		$people_h = $_POST['people_h'];
		$people_a = $_POST['people_a'];
		
		$time = dateMore();
		
		if(isset($_SESSION['upload_photo']) && $_SESSION['upload_photo'] != ''){
			$temp = explode('.', $_SESSION['upload_photo']);
			$photo = str_replace('/temp/', '/photo/', $_SESSION['upload_photo']);
			$mysql_photo = 'upload/mysql/'.time().'.'.$temp[1];
		}
		
		if(isset($_GET['modid']) && $_GET['modid'] != ''){
			// x_stutas 為 m_id 和 t_id 統計使用次數，以防誤刪使用過的項造成數據缺失 ( 太麻烦了，这种方法已弃用，而是直接把m_id和t_id的详细内容都写进了goodsform表中，这样m_id和t_id就可以任意删除了 )
			/*
			if($m_id_array != ''){
				foreach($m_id_array as $v){
					$rtn = $mysql->q('update material set m_stutas = m_stutas - 1 where m_id = ?', $v);	
				}
			}
			if(isset($_POST['m_id'])){
				if(is_array($_POST['m_id'])){
					foreach($_POST['m_id'] as $v){
						$rtn = $mysql->q('update material set m_stutas = m_stutas + 1 where m_id = ?', $v);
					}
				}else{
					$rtn = $mysql->q('update material set m_stutas = m_stutas + 1 where m_id = ?', $_POST['m_id']);
				}
			}
			if($t_id_array != ''){
				foreach($t_id_array as $v){
					$rtn = $mysql->q('update task set t_stutas = t_stutas - 1 where t_id = ?', $v);	
				}
			}
			if(isset($_POST['t_id'])){
				if(is_array($_POST['t_id'])){
					foreach($_POST['t_id'] as $v){
						$rtn = $mysql->q('update task set t_stutas = t_stutas + 1 where t_id = ?', $v);
					}			
				}else{
					$rtn = $mysql->q('update task set t_stutas = t_stutas + 1 where t_id = ?', $_POST['t_id']);
				}
			}
			*/
			
			$m_detail = '';
			for($i = 0; $i < count($post_m_id); $i++){
				$rtn = $mysql->qone('select * from material where m_id = ?', $post_m_id[$i]);
				$m_detail .= ($i!=0?'|':'').$rtn['m_name'].','.$rtn['m_color'].','.$rtn['m_type'].','.$rtn['m_price'].','.$rtn['m_unit'];
			}
					
			$t_detail = '';
			for($i = 0; $i < count($post_t_id); $i++){
				$rtn = $mysql->qone('select * from task where t_id = ?', $post_t_id[$i]);
				$t_detail .= ($i!=0?'|':'').$rtn['t_name'].','.$rtn['t_price'];
			}
			
			$result = $mysql->q("UPDATE goodsform SET g_time = ?, g_id = ?, g_type = ?, g_material = ?, g_size = ?, g_gem_num = ?, g_cast = ?, g_plating = ?, g_weight = ?, m_id = ?, m_detail = ?, m_value = ?, t_id = ?, t_detail = ?, t_value = ?, photo = ?, mysql_photo = ?, g_process = ?, electroplate = ?, electroplate_thick = ?, other = ?, p_labour = ?, p_workpiece = ?, p_plate = ?, p_stone = ?, p_parts = ?, p_other = ?, p_other2 = ?, p_profit = ?, p_total = ?, people_h = ?, people_a = ? WHERE id = ?", $time, $g_id, $g_type, $g_material, $g_size, $g_gem_num, $g_cast, $g_plating, $g_weight, $m_id, $m_detail, $m_value, $t_id, $t_detail, $t_value, $mod_result['photo'], $mod_result['mysql_photo'], $process, $electroplate, $electroplate_thick, $other, $p_labour, $p_workpiece, $p_plate, $p_stone, $p_parts, $p_other, $p_other2, $p_profit, $p_total, $people_h, $people_a, $_GET['modid']);
			if($result){
				//删除关系表g_m 中 $_GET['modid'] 的内容
				$mysql->q('delete from g_m where gid = ?', $g_id);
				//写入关系到表g_m
				foreach($post_m_id as $v){
					$mysql->q('insert into g_m (gid, mid) values (?, ?)', $g_id, $v);
				}
				//删除关系表g_m 中 $_GET['modid'] 的内容
				$mysql->q('delete from g_t where gid = ?', $g_id);				
				//写入关系到表g_t	
				foreach($post_t_id as $v){
					$mysql->q('insert into g_t (gid, tid) values (?, ?)', $g_id, $v);
				}				
				$_SESSION['upload_photo'] = '';
				$myerror->ok('修改产品资料 成功!', 'searchform');
			}else{
				$myerror->error('由于系统原因，修改产品资料 失败', 'main');
			}
		}else{
			// x_stutas 為 m_id 和 t_id 統計使用次數，以防誤刪使用過的項造成數據缺失（！！棄用做法1）
			/*
			if(isset($_POST['m_id'])){
				if(is_array($_POST['m_id'])){
					foreach($_POST['m_id'] as $v){
						$rtn = $mysql->q('update material set m_stutas = m_stutas + 1 where m_id = ?', $v);
					}
				}else{
					$rtn = $mysql->q('update material set m_stutas = m_stutas + 1 where m_id = ?', $_POST['m_id']);
				}
			}
			if(isset($_POST['t_id'])){
				if(is_array($_POST['t_id'])){
					foreach($_POST['t_id'] as $v){
						$rtn = $mysql->q('update task set t_stutas = t_stutas + 1 where t_id = ?', $v);
					}			
				}else{
					$rtn = $mysql->q('update task set t_stutas = t_stutas + 1 where t_id = ?', $_POST['t_id']);
				}			
			}
			*/
			//查询出 m_id 和 t_id 的所有东西，记入goodsform表，以便，m和t可以任意删除，他们实际上只是起到方便选择减少输入文字的作用 （！！棄用做法2）
			//現在 _detail的作用是，以便formdetail 和 pdf 頁面取資料的時候不用在查詢 material表和task表，直接在goodsform表中就能拿到，提高效率
			$m_detail = '';
			for($i = 0; $i < count($post_m_id); $i++){
				$rtn = $mysql->qone('select * from material where m_id = ?', $post_m_id[$i]);
				$m_detail .= ($i!=0?'|':'').$rtn['m_name'].','.$rtn['m_color'].','.$rtn['m_type'].','.$rtn['m_price'].','.$rtn['m_unit'];
			}
					
			$t_detail = '';
			for($i = 0; $i < count($post_t_id); $i++){
				$rtn = $mysql->qone('select * from task where t_id = ?', $post_t_id[$i]);
				$t_detail .= ($i!=0?'|':'').$rtn['t_name'].','.$rtn['t_price'];
			}

			$result = $mysql->sp('CALL sendform('.moreQm(32).')', $time, $g_id, $g_type, $g_material, $g_size, $g_gem_num, $g_cast, $g_plating, $g_weight, $m_id, $m_detail, $m_value, $t_id, $t_detail, $t_value, $photo, $mysql_photo, $process, $electroplate, $electroplate_thick, $other, $p_labour, $p_workpiece, $p_plate, $p_stone, $p_parts, $p_other, $p_other2, $p_profit, $p_total, $people_h, $people_a);
			if($result){
				$result = intval($result);
				//写入关系到表g_m
				foreach($post_m_id as $v){
					$mysql->q('insert into g_m (gid, mid) values (?, ?)', $g_id, $v);
				}
				//写入关系到表g_t	
				foreach($post_t_id as $v){
					$mysql->q('insert into g_t (gid, tid) values (?, ?)', $g_id, $v);
				}
				
				if(is_int($result) && $result > 0){
					@copy(iconv('UTF-8','GBK', $_SESSION['upload_photo']), iconv('UTF-8','GBK', $photo));
					@copy(iconv('UTF-8','GBK', $_SESSION['upload_photo']), iconv('UTF-8','GBK', $mysql_photo));
					@unlink($_SESSION['upload_photo']);
					$_SESSION['upload_photo'] = '';
					$myerror->ok('新增产品资料 成功!', 'searchform');	
				}else{
					$myerror->error('由于返回值异常，新增产品资料 失败', 'sendform');	
				}
			}else{
				$myerror->error('由于系统原因，新增产品资料 失败', 'sendform');	
			}
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
<h1 class="green">填写产品资料<em>*号为必填项</em></h1>
<fieldset class="center2col">
<legend class='legend'>产品资料</legend>

<fieldset class="center2col"> 
<legend class='legend'>第一步：上传图片（注：一张表格只能添加一张图片）</legend>
<?
if(!isset($_GET['modid'])){
	if(isset($_SESSION['upload_photo']) && $_SESSION['upload_photo'] != ''){
		$image_path = $_SESSION['upload_photo'];
		echo '<div style="margin-left:28px;">图片上传成功，图片保存路径：' . $_SESSION['upload_photo']."--------<b><a href='?act=sendform&delimg'>【删除图片】</a></b></div><br />";
		if( $_SESSION['upload_photo'] && $_SESSION['upload_photo'] != ''){
			$arr = getimagesize($_SESSION['upload_photo']);
			$pic_width = $arr[0];
			$pic_height = $arr[1];
			$image_size = getimgsize(230, 150, $pic_width, $pic_height);
			echo '<div class="shadow" style="margin-left:28px;"><img src="/sys/'.$_SESSION['upload_photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></div>';
		}
	}else{
		
		?>
        <img src="<?= (isset($_GET['modid']) ? htpic($Pic) : (isset($_SESSION['temp_upload']) && $_SESSION['temp_upload'] ? $pic_path_ht .$_SESSION['temp_upload'] : '../images/nopic.gif'))?>" id="p_pkPic" border="0" height="70">
<div class="line1"></div>
        <iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="25" id="titleimg_up" frameborder="0"></iframe>
        <? /*
		<!--img src="../images/nopic.gif" border="0" height="40"--><div style="margin-left:28px;"><a href="?act=upload_photo"><b>【上传图片】</b></a><div>
		*/
		?>
		<?		
	}
}else{	
	if( $mod_result['mysql_photo'] && $mod_result['mysql_photo'] != ''){
		echo '<div style="margin-left:28px;">图片保存路径：' . $mod_result['photo'].' | '.$mod_result['mysql_photo'].'</div><br />';
		$arr = getimagesize($mod_result['mysql_photo']);
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(230, 150, $pic_width, $pic_height);
		echo '<div class="shadow" style="margin-left:28px;"><img src="/sys/'.$mod_result['mysql_photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></div>';
	}else{
		?>
        <img src="<?= (isset($_GET['modid']) ? htpic($Pic) : (isset($_SESSION['temp_upload']) && $_SESSION['temp_upload'] ? $pic_path_ht . $_SESSION['temp_upload'] : '../images/nopic.gif'))?>" id="p_pkPic" border="0" height="70">
<div class="line1"></div>
        <iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="25" id="titleimg_up" frameborder="0"></iframe>
        <? /*
		<!--img src="../images/nopic.gif" border="0" height="40"--><div style="margin-left:28px;"><a href="?act=upload_photo"><b>【上传图片】</b></a></div>
		*/
		?>
		<?		
	}
/*
<div class="line"></div>

<img src="<?= (isset($Pic) ? $Pic : '../images/nopic.gif')?>" id="p_pkPic" border="0" height="70">
<input name="pkPic" type="hidden" id="pkPic" value="<?=$Pic?>">
<div class="line"></div>
<iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="500" id="titleimg_up" frameborder="0"></iframe>
*/?>
<?
}
?>
</fieldset>
<div class="line"></div>
<fieldset class="center2col"> 
<legend class='legend'>第二步：填写并提交</legend>
<?php
$goodsForm->begin();

$goodsForm->show('g_id');
$goodsForm->show('g_type');
$goodsForm->show('g_material');
$goodsForm->show('g_size');
$goodsForm->show('g_gem_num');
$goodsForm->show('g_cast');
$goodsForm->show('g_plating');
$goodsForm->show('g_weight');
?>
<div class="line"></div>
<?php
$goodsForm->show('m_id', '<div class="line"></div>');

$goodsForm->show('t_id', '<div class="line"></div>');

$goodsForm->show('process');
$goodsForm->show('electroplate');
$goodsForm->show('electroplate_thick');
$goodsForm->show('other', '<div class="line"></div>');

//$goodsForm->show('p_labour');
//$goodsForm->show('p_workpiece');
$goodsForm->show('p_plate');
//$goodsForm->show('p_stone');
//$goodsForm->show('p_parts');
//$goodsForm->show('p_other');
$goodsForm->show('p_other2');
$goodsForm->show('p_profit', '<div class="line"></div>');
//$goodsForm->show('p_total', '<div class="line"></div>');

$goodsForm->show('people_h');
$goodsForm->show('people_a', '<div class="line"></div>');

$goodsForm->show('submitbtn');
?>
</fieldset>
</fieldset>
<?
	$goodsForm->end();
?>
<script>
<?
foreach($m_id as $v){
?>
$('#<?=$v?>').click(function(){
				$(this).attr('src', '/images/loading3.gif');
				var qs = 'ajax=1&act=ajax-sendform_query&sign=m&value=<?=$v?>';
				$.ajax({
				   type: "GET",
				   url: "index.php",
				   data: qs,
				   cache: false,
				   dataType: "html",
				   error: function(){
						showTooltip($('#<?=$v?>')[0], '查询时发生错误，请重试', true, -270);
						$('#<?=$v?>').attr('src', '/images/helper1.gif');
						//inputStyleChanger($('#' + tempVar[1])[0], 'text', 'err');
						//tempVar2.data('checking', 0);
				   },
				   success: function(data){
						if(data.substr(0, 2) != 'no'){
							showTooltip($('#<?=$v?>')[0], data, true, -270);
						}else{
							showTooltip($('#<?=$v?>')[0], '查询出错，请重试', true, -270);
						}
						$('#<?=$v?>').attr('src', '/images/helper1.gif');
				   }
				 });
			});

<?
//修改的时候，如果选中，输入框就直接可用，因为在Form.php中默认这个输入框是disabled的
if(isset($_GET['modid'])){
?>	
	if($('.<?=$v?>').attr("checked")==true){
		$("input[name='<?=$v?>']").attr({disabled:false,required:1});
	}
<?			
}
?>	
			
//为了方便选择到，在Form.php里给input加上了个class等于$m_id。。。。。。（实现了只有选择checkbox，后面的输入框才能输入）
$('.<?=$v?>').change(function(){
	if($(this).attr("checked")==true){
		$("input[name='<?=$v?>']").attr({disabled:false,required:1});
	}else{
		$("input[name='<?=$v?>']").attr({disabled:true,required:0});	
	}
});
<?
}
foreach($t_id as $v){
?>
$('#<?=$v?>').click(function(){
				$(this).attr('src', '/images/loading3.gif');
				var qs = 'ajax=1&act=ajax-sendform_query&sign=t&value=<?=$v?>';
				$.ajax({
				   type: "GET",
				   url: "index.php",
				   data: qs,
				   cache: false,
				   dataType: "html",
				   error: function(){
						showTooltip($('#<?=$v?>')[0], '查询时发生错误，请重试', true, -270);
						$('#<?=$v?>').attr('src', '/images/helper1.gif');
						//inputStyleChanger($('#' + tempVar[1])[0], 'text', 'err');
						//tempVar2.data('checking', 0);
				   },
				   success: function(data){
						if(data.substr(0, 2) != 'no'){
							showTooltip($('#<?=$v?>')[0], data, true, -270);
						}else{
							showTooltip($('#<?=$v?>')[0], '查询出错，请重试', true, -270);
						}
						$('#<?=$v?>').attr('src', '/images/helper1.gif');
				   }
				 });
			});
			
<?
if(isset($_GET['modid'])){
?>	
	if($('.<?=$v?>').attr("checked")==true){
		$("input[name='<?=$v?>']").attr({disabled:false,required:1});
	}
<?			
}
?>	
		
$('.<?=$v?>').change(function(){
	if($(this).attr("checked")==true){
		$("input[name='<?=$v?>']").attr({disabled:false,required:1});
	}else{
		$("input[name='<?=$v?>']").attr({disabled:true,required:0});	
	}
});			
<?
}
?>
</script>
<?
}
?>