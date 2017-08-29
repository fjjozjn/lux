<?php


if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//引用特殊的recordset class 文件
require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

if(isset($_GET['delid']) && $_GET['delid'] != ''){
	$rs = $mysql->q('delete from unit WHERE unit = ?', $_GET['delid']);
	if($rs){
		$myerror->ok('刪除 Unit 成功!', 'com-unit');	
	}else{
		$myerror->error('系统出错，刪除 Unit 失败', 'com-unit');	
	}
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
	$mod_result = $mysql->qone('select * from unit where unit = ?', $_GET['modid']);
}
	
$form = new My_Forms();
$formItems = array(
		
		'unit' => array('title' => 'Unit', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['unit'])?$mod_result['unit']:''),
		'quantity' => array('title' => 'Quantity', 'type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'required' => 1, 'value' => isset($mod_result['quantity'])?$mod_result['quantity']:''),
		
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
		);
$form->init($formItems);


if(!$myerror->getAny() && $form->check()){
	$unit = $_POST['unit'];
	$quantity = $_POST['quantity'];
	
	if( isset($_GET['modid']) && $_GET['modid'] != ''){
		$result = $mysql->q('update unit set unit = ?, quantity = ? where unit = ?', $unit, $quantity, $_GET['modid']);
		if($result){
			$myerror->ok('修改 Unit 成功!', 'com-unit');	
		}else{
			$myerror->error('由于系统原因，修改 Unit 失败', 'com-unit');	
		}
	}else{
		$result = $mysql->q('insert into unit (unit, quantity) values (?, ?)', $unit, $quantity);
		if($result){
			$myerror->ok('新增 Unit 成功!', 'com-unit');		
		}else{
			$myerror->error('由于系统原因，新增 Unit 失败', 'com-unit');
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
	<fieldset class="center2col" style="width:60%">
	<legend class='legend'><? echo isset($_GET['modid'])?'Modify':'Add' ?> Unit</legend>
	
	
	<?php
	$form->begin();
	
	$form->show('unit');
	$form->show('quantity', '<div class="line"></div>');
	
	$form->show('submitbtn');
	?>
	</fieldset>
	<?
	$form->end();
	$rtn = $mysql->q('select * from unit');
	$result = $mysql->fetch();
	?>
	
	<fieldset class="center2col" style="width:60%">
	<legend class='legend'>Unit List</legend>
	<table width="100%" cellspacing="1" bordercolor='#ABABAB' cellpadding="3" border="1" bgcolor="#000000">
	<tbody>
	<tr bgcolor="#EEEEEE">
		<th height='30' align="center">Unit</th>
		<th align="center">Quantity</th>
		<th align="center">MODIFY</th>
		<th align="center">DEL</th>
	</tr>
	<?
	if($result){
		foreach($result as $v){
		?>
			<tr class="td_" valign="top" onmouseout="this.className='td_';" onmouseover="this.className='td_highlight';">
				<td align="left"><?=$v['unit']?></td>
				<td align="left"><?=$v['quantity']?></td>
				<td align="center"><a href="?act=com-unit&modid=<?=$v['unit']?>">MODIFY</a></td>
				<td align="center"><a href="?act=com-unit&delid=<?=$v['unit']?>">DEL</a></td>
			</tr>
		<?
		}
	}
	?>
	</table>
<?	
}
?>
</fieldset>