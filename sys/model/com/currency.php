<?php


if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//引用特殊的recordset class 文件
require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

if(isset($_GET['delid']) && $_GET['delid'] != ''){
	$rs = $mysql->q('delete from currency WHERE type = ?', $_GET['delid']);
	if($rs){
		$myerror->ok('刪除 Currency 成功!', 'com-currency');	
	}else{
		$myerror->error('系统出错，刪除 Currency 失败', 'com-currency');	
	}
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
	$mod_result = $mysql->qone('select * from currency where type = ?', $_GET['modid']);
}
	
$form = new My_Forms();
$formItems = array(
		
		'type' => array('title' => 'Currency', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['type'])?$mod_result['type']:''),
		'rate' => array('title' => 'Rate', 'type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'required' => 1, 'value' => isset($mod_result['rate'])?$mod_result['rate']:''),
		
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
		);
$form->init($formItems);


if(!$myerror->getAny() && $form->check()){
	$type = $_POST['type'];
	$rate = $_POST['rate'];
	
	if( isset($_GET['modid']) && $_GET['modid'] != ''){
		$result = $mysql->q('update currency set type = ?, rate = ? where type = ?', $type, $rate, $_GET['modid']);
		if($result){
			$myerror->ok('修改 Currency 成功!', 'com-currency');	
		}else{
			$myerror->error('由于系统原因，修改 Currency 失败', 'com-currency');	
		}
	}else{
		$result = $mysql->q('insert into currency (type, rate) values (?, ?)', $type, $rate);
		if($result){
			$myerror->ok('新增 Currency 成功!', 'com-currency');		
		}else{
			$myerror->error('由于系统原因，新增 Currency 失败', 'com-currency');
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
	<legend class='legend'>Add Currency</legend>
	
	
	<?php
	$form->begin();
	
	$form->show('type');
	$form->show('rate', '<div class="line"></div>');
	
	$form->show('submitbtn');
	?>
	</fieldset>
	<?
	$form->end();
	$rtn = $mysql->q('select * from currency');
	$result = $mysql->fetch();
	?>
	
	<fieldset class="center2col" style="width:60%">
	<legend class='legend'>Currency List</legend>
	<table width="100%" cellspacing="1" bordercolor='#ABABAB' cellpadding="3" border="1" bgcolor="#000000">
	<tbody>
	<tr bgcolor="#EEEEEE">
		<th height='30' align="center">Currency</th>
		<th align="center">Rate</th>
		<th align="center">MODIFY</th>
		<th align="center">DEL</th>
	</tr>
	<?
	if($result){
		foreach($result as $v){
		?>
			<tr class="td_" valign="top" onmouseout="this.className='td_';" onmouseover="this.className='td_highlight';">
				<td align="left"><?=$v['type']?></td>
				<td align="left"><?=$v['rate']?></td>
				<td align="center"><a href="?act=com-currency&modid=<?=$v['type']?>">MODIFY</a></td>
				<td align="center"><a href="?act=com-currency&delid=<?=$v['type']?>">DEL</a></td>
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