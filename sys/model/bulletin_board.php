<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//引用特殊的recordset class 文件
require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//为这个页面加个system的用户，这样select就能显示了
$user['system'] = array('system', 'system');

if(isset($_GET['pi_no'])){
	$_SESSION['search_criteria']['search_pi_no'] = $_GET['pi_no'];
}

if( !isset($_POST['b_sign']) || $_POST['b_sign'] != 1){
	if(isset($_GET['delid']) && $_GET['delid'] != ''){	
		$rtn = $mysql->qone('select b_from from bulletin_board where id = ?', $_GET['delid']);
		if($rtn){
			if(isSysAdmin() || $rtn['b_from'] == $_SESSION['logininfo']['aName']){
				$rs = $mysql->q('delete from bulletin_board WHERE id = ?', $_GET['delid']);
				if($rs){
					$myerror->ok('Success!', 'main');	
				}else{
					$myerror->error('Failure!', 'main');	
				}
			}else{
				$myerror->error('Without Permission To Delete!', 'main');	
			}
		}
	}
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
	$mod_result = $mysql->qone('select * from bulletin_board where id = ?', $_GET['modid']);
}

$goodsForm = new My_Forms();
if(isset($_GET['modid'])){
	$formItems = array(
			'b_from' => array('title' => 'From', 'type' => 'select', 'options' => $user, 'required' => 1, 'value' => isset($mod_result['b_from'])?$mod_result['b_from']:''),
			'b_date' => array('title' => 'DATE', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['b_date'])?date('Y-m-d', strtotime($mod_result['b_date'])):''),
			'content' => array('title' => 'Content', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'required' => 1, 'value' => isset($mod_result['content'])?$mod_result['content']:''),
			
			//隱藏提交標識，用於分辨是goodsForm表的提交還是form表的提交
			'b_sign' => array('type' => 'hidden', 'value' => 1),
			
			'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
			);
}else{
	$formItems = array(
			'content' => array('title' => 'Content', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'required' => 1),
			
			//隱藏提交標識，用於分辨是goodsForm表的提交還是form表的提交
			'b_sign' => array('type' => 'hidden', 'value' => 1),
			
			'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
			);	
}

$goodsForm->init($formItems);


$form = new My_Forms();
$formItems = array(

		'search_b_from' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['search_b_from'], 
			),	
		'search_content' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['search_content'],
			),				
		'search_start_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['search_start_date'], 
			),	
		'search_end_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['search_end_date'], 
			),	
			/*							
		'search_b_sign' => array(
			'type' => 'hidden', 
			'value' => 1
			),
			*/
		'submitbutton' => array(
			'type' => 'submit', 
			'value' => 'Search', 
			)			
		);
$form->init($formItems);

if(isset($_POST['b_sign']) && $_POST['b_sign'] == 1){
	if(!$myerror->getAny() && $goodsForm->check()){
		if(isset($_GET['modid'])){
			$b_date = $_POST['b_date'].' '.date('H:i:s');
			$b_from = $_POST['b_from'];		
		}else{
			$b_date = dateMore();
			$b_from = $_SESSION['logininfo']['aName'];
		}
		$content = $_POST['content'];
		
		if( isset($_GET['modid']) && $_GET['modid'] != ''){
			if(isSysAdmin()){
				$result = $mysql->q('update bulletin_board set b_from = ?, b_date = ?, content = ? where id = ?', $b_from, $b_date, $content, $_GET['modid']);
				if($result){
					$myerror->ok('Success!', 'main');	
				}else{
					$myerror->error('Failure!', 'main');	
				}
			}else{
				//非管理员只能修改自己的信息
				if($b_from == $_SESSION['logininfo']['aName']){
					$result = $mysql->q('update bulletin_board set b_from = ?, b_date = ?, content = ? where id = ?', $b_from, $b_date, $content, $_GET['modid']);
					if($result){
						$myerror->ok('Success!', 'main');	
					}else{
						$myerror->error('Failure!', 'main');	
					}
				}else{
					$myerror->error('Without Permission To Modify!', 'main');
				}
			}
			

		}else{
			$result = $mysql->q('insert into bulletin_board (b_from, b_date, content) values ('.moreQm(3).')', $b_from, $b_date, $content);
			if($result){
				$myerror->ok('Success!', 'main');
			}else{
				$myerror->error('Failure!', 'main');
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
<!--h1 class="green">Bulletin Board<em>* item must be filled in</em></h1-->
<table width="550" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td class='headertitle' align="center">Bulletin Board</td>
	</tr>
</table>	

<?
$form->begin();
?>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td>From: </td>
				<td><?
				$form->show('search_b_from');
				?></td>
				<td>Content : </td>
				<td><?
				$form->show('search_content');
				//$form->show('search_b_sign');
				?></td>	
			</tr>
			<tr>
				<td>Start Date : </td>
				<td><?
				$form->show('search_start_date');
				?></td>		
                
                <td>End Date : </td>
				<td><?
				$form->show('search_end_date');
				?></td>
			</tr>			
            <tr><td>&nbsp;</td></tr>
			<tr>
				<td width="100%" colspan='4'>
				<?
				$form->show('submitbutton');				
				?></td>
			</tr>				
		</table>
	</fieldset>	
	</td>	
	</tr>
</table>    
<?
//if(isset($_POST['search_b_sign']) && $_POST['search_b_sign'] == 1){
	// 如果有post资料则给Session，并且清除附在上次翻页时残留的$_GET['page']
	if (count($_POST)){
		$_SESSION['search_criteria'] = $_POST;
		$_GET['page'] = 1;
	}
	
	//如果有合法的提交，则 getAnyPost = true。
	//如果不是翻页而是普通的GET，则清除之前的Session，以显示一个空白的表单
	$getAnyPost = false;
	
	if($form->check()){
		$getAnyPost = true;
	}elseif(!isset($_GET['page'])){
		unset($_SESSION['search_criteria']);
	}	
	
	if($myerror->getAny()){
		require_once(ROOT_DIR.'model/inside_warn.php');
	}	
		
	if ($getAnyPost || isset($_GET['page'])){
		
		$rs = new RecordSetControl2;
		$rs->record_per_page = ADMIN_ROW_PER_PAGE;
		$rs->addnew_link = "?act=bulletin_board";
		$rs->display_new_button = false;
		$rs->sort_field = "id";
		$rs->sort_seq = "DESC";
	
		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}
	
		$where_sql = "";
	
		if (strlen(@$_SESSION['search_criteria']['search_b_from'])){
			$where_sql.= " AND b_from Like '%".$_SESSION['search_criteria']['search_b_from'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['search_start_date'])){
			if (strlen(@$_SESSION['search_criteria']['search_end_date'])){
				$where_sql.= " AND b_date between '".$_SESSION['search_criteria']['search_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND b_date > '".$_SESSION['search_criteria']['search_start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['search_end_date'])){
			$where_sql.= " AND b_date < '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
		}
		
		//普通用户只能搜索到自己开的单
		/*
		if ($_SESSION['logininfo']['aName'] != 'zjn' && $_SESSION['logininfo']['aName'] != 'KEVIN'){
			$where_sql .= " AND pi_no in (select pvid b_from proforma where printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup = (SELECT AdminLuxGroup FROM tw_admin WHERE AdminName = '".$_SESSION['logininfo']['aName']."')))";
		}
		*/
		// echo $where_sql;
		
		$where_sql.= ' ORDER BY b_date DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;
	
		$temp_table = ' bulletin_board';
		$list_field = ' SQL_CALC_FOUND_ROWS * ';
	
		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';
	
		//$rs->col_width = "100";
		$rs->SetRecordCol("DATE", "b_date");
		$rs->SetRecordCol("From", "b_from");
		$rs->SetRecordCol("Content", "content");
	
		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		//$rs->SetRecordCol("MODIFY", "id", $sort, $edit,"?act=bulletin_board","modid");
		//$rs->SetRecordCol("DEL", "id", $sort, $edit,"?act=bulletin_board","delid");
		$rs->SetRSSorting('?act=bulletin_board');
	
		$rs->ShowRecordSet($info);
	
	}
//}

$form->end();	
?>

<br />
<br />
<!--<fieldset class="center2col" style="width:45%">
<legend class='legend'><?/*=isset($_GET['modid'])?'Modify':'Add' */?></legend>

<?php
/*$goodsForm->begin();
*/?>
<table width="100%" id="table">
<?/* if(isset($_GET['modid'])){*/?>
  <tr>
	<td width="50%"><?/* $goodsForm->show('b_date');*/?></td>
	<td width="50%"><?/* $goodsForm->show('b_from');*/?></td>
  </tr> 
<?/* }*/?>
  <tr>
  	<td width="50%"><?/* $goodsForm->show('content');*/?><?/* $goodsForm->show('b_sign');*/?></td>
  </tr>
</table>
<div class="line"></div>
<?/*
$goodsForm->show('submitbtn');
*/?>

</fieldset>-->
<br />
<?
$goodsForm->end();
}
?>


