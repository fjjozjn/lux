<?php


if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//引用特殊的recordset class 文件
require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

if(isset($_GET['delid']) && $_GET['delid'] != ''){
	$rs = $mysql->q('delete from theme WHERE id = ?', $_GET['delid']);
	if($rs){
		$myerror->ok('Delete Theme success!', 'com-theme');	
	}else{
		$myerror->error('System error, delete Theme failure', 'com-theme');	
	}
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
	$mod_result = $mysql->qone('select * from theme where id = ?', $_GET['modid']);
}
	
$form = new My_Forms();
$formItems = array(
		
		'theme' => array('title' => 'theme', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1, 'value' => isset($mod_result['theme'])?$mod_result['theme']:''),
		'sort_order' => array('title' => 'Sort Order', 'type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'restrict' => 'number', 'value' => isset($mod_result['sort_order'])?$mod_result['sort_order']:'', 'info' => '数字大的theme排在前面'),
		
		'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
		);
$form->init($formItems);


if(!$myerror->getAny() && $form->check()){
	$theme = $_POST['theme'];
	$sort_order = $_POST['sort_order'];
	
	if( isset($_GET['modid']) && $_GET['modid'] != ''){
		$result = $mysql->q('update theme set theme = ?, sort_order = ? where id = ?', $theme, $sort_order, $_GET['modid']);
		if($result){
			$myerror->ok('Modify Theme success!', 'com-theme');	
		}else{
			$myerror->error('System error, modify Theme failure, please check whether the theme is exist.', 'com-theme');	
		}
	}else{
		$result = $mysql->q('insert into theme values (NULL, ?, ?)', $theme, $sort_order);
		if($result){
			$myerror->ok('Add Theme success!', 'com-theme');		
		}else{
			$myerror->error('System error, add Theme failure, please check whether the theme is exist.', 'com-theme');
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
	<legend class='legend'><? echo isset($_GET['modid'])?'Modify':'Add' ?> Theme</legend>
	
	
	<?php
	$form->begin();
	
	$form->show('theme');
	$form->show('sort_order', '<div class="line"></div>');	
	$form->show('submitbtn');
	?>
	</fieldset>
	<?
	$form->end();
	$rtn = $mysql->q('select * from theme order by sort_order desc');
	$result = $mysql->fetch();
	?>
	
	<fieldset class="center2col" style="width:60%">
	<legend class='legend'>Theme List</legend>
	<table width="100%" cellspacing="1" bordercolor='#ABABAB' cellpadding="3" border="1" bgcolor="#000000">
	<tbody>
	<tr bgcolor="#EEEEEE">
		<th height='30' align="center">Theme</th>
        <th height='30' align="center">Sort Order</th>
		<th align="center">MODIFY</th>
		<th align="center">DEL</th>
	</tr>
	<?
	if($result){
		foreach($result as $v){
		?>
			<tr class="td_" valign="top" onmouseout="this.className='td_';" onmouseover="this.className='td_highlight';">
				<td align="left"><?=$v['theme']?></td>
                <td align="left"><?=$v['sort_order']?></td>
				<td align="center"><a href="?act=com-theme&modid=<?=$v['id']?>">MODIFY</a></td>
				<td align="center"><a href="?act=com-theme&delid=<?=$v['id']?>">DEL</a></td>
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