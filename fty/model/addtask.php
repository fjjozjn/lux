<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

judgeFtyPerm( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if( !isset($_POST['t_sign']) || $_POST['t_sign'] != 1){
	if(isset($_GET['delid']) && $_GET['delid'] != ''){
		//$rtn = $mysql->q('select distinct gid from g_t where tid = ?', $_GET['delid']);
		//if(!$rtn){
			$rs = $mysql->q('delete from fty_task WHERE t_id = ?', $_GET['delid']);
			if($rs){
				$myerror->ok('刪除工序资料 成功!', 'addtask&page=1');
			}else{
				$myerror->error('系统出错，刪除工序资料 失败', 'addtask&page=1');
			}
        /*
		}else{
			$result = $mysql->fetch();
			$result_gid = '';
			for($i = 0; $i < count($result); $i++){
				$result_gid .= ($i!=0?',':'') . $result[$i]['gid'];
			}
			//如果已把此件工序号添加到form中，则不能删除
			$myerror->error('此件工序号 '.$_GET['delid'].' 已被使用，不能删除。如要删除此项，需在以下表单（'.$result_gid.'）中去除此工序的选定。', 'addtask');
		}
        */
	}
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
	$mod_result = $mysql->qone('select * from fty_task where t_id = ?', $_GET['modid']);
}

$goodsForm = new My_Forms();
$formItems = array(
		
		//'t_id' => array('title' => '工序号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['t_id'])?$mod_result['t_id']:''),
		't_name' => array('title' => '工序名称', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['t_name'])?$mod_result['t_name']:''),
		't_price' => array('title' => '工价', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['t_price'])?$mod_result['t_price']:''),
		//'t_time' => array('title' => '工时', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20),
		't_remark' => array('title' => '备注', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($mod_result['t_remark'])?$mod_result['t_remark']:''),
		
		//隱藏提交標識，用於分辨是goodsForm表的提交還是form表的提交
		't_sign' => array('type' => 'hidden', 'value' => 1),
		
		'submitbtn'	=> array('type' => 'submit', 'value' => ' 确定 '),
		);
if(isset($_GET['modid'])){
	$formItems['t_id'] = array('title' => '工序号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['t_id'])?$mod_result['t_id']:'', 'readonly' => 'readonly');
}
		
$goodsForm->init($formItems);


$form = new My_Forms();
$formItems = array(

		'search_t_id' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['search_t_id'],
			'minlen' => 1, 
			'maxlen' => 20,
			),	
		'search_t_name' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['search_t_name'], 
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
		'search_t_sign' => array(
			'type' => 'hidden', 
			'value' => 1
			),
		'submitbutton' => array(
			'type' => 'submit', 
			'value' => '搜索', 
			'title' => '这个也可以提交'
			),	
		'resetbutton' => array(
			'type' => 'button', 
			'value' => '清除', 
			'title' => '这个也可以提交'
			),			
		);
$form->init($formItems);

if(isset($_POST['t_sign']) && $_POST['t_sign'] == 1){
	if(!$myerror->getAny() && $goodsForm->check()){
		//$t_id = $_POST['t_id'];
		//20121023 添加task时改为自动生成
		$t_id = isset($_POST['t_id'])?$_POST['t_id']:fty_autoGenerationID();		
		$t_name = $_POST['t_name'];
		$t_price = $_POST['t_price'];
		$t_remark = $_POST['t_remark'];

        $today = dateMore();
        $staff = $_SESSION["ftylogininfo"]["aName"];
		
		if( isset($_GET['modid']) && $_GET['modid'] != ''){
			$result = $mysql->q('update fty_task set t_name = ?, t_price = ?, t_remark = ?, mod_by = ?, mod_date = ? where t_id = ?', $t_name, $t_price, $t_remark, $staff, $today, $_GET['modid']);
			if($result){
				$myerror->ok('修改工序资料 成功!', 'addtask&page=1');
			}else{
				$myerror->error('由于系统原因，修改工序资料 失败', 'addtask&page=1');
			}
		}else{
			$result = $mysql->q('insert into fty_task values (NULL, '.moreQm(8).')', $t_id, $t_name, $t_price, $t_remark, $staff, '', $today, $today);
			if($result){
				$result = intval($result);
				if(is_int($result) && $result > 0){
					$myerror->ok('新增工序号资料 成功!', 'addtask&page=1');		
				}else{
					$myerror->error('由于返回值异常，新增工序资料 失败', 'addtask&page=1');
				}
			}else{
				$myerror->error('由于系统原因，新增工序资料 失败', 'addtask&page=1');
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
<h1 class="green">填写工序资料<em>*号为必填项</em></h1>
<fieldset>
<legend class='legend'><?=(isset($_GET['modid']))?'修改':'添加'?>工序资料</legend>

<?php
$goodsForm->begin();
?>
<table width="100%" id="table">
  <tr>
  	<td width="25%">
    <? 
	if(isset($_GET['modid'])){ 
    	$goodsForm->show('t_id');	
	}else{
	?>
    <div class="set"><label class="formtitle">工序号</label><br />（自动产生）</div>
    <? 
	}
	?>    
    </td>
    <td width="25%"><? $goodsForm->show('t_name');?></td>
	<td width="25%"><? $goodsForm->show('t_price');?></td>
	<td width="25%"><? $goodsForm->show('t_remark'); $goodsForm->show('t_sign');?></td>  
  </tr> 
</table>
<div class="line"></div>
<?
$goodsForm->show('submitbtn');
?>

</fieldset>
<?
$goodsForm->end();

$form->begin();
?>
<a name="search_task" id="search_task"></a>
<fieldset>
<legend class='legend'>工序资料</legend>
<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>搜索</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td>工序号 : </td>
				<td><?
				$form->show('search_t_id');
				$form->show('search_t_sign');
				?></td>	
            
				<td>工序名称 : </td>
				<td><?
				$form->show('search_t_name');
				?></td>
			</tr>
			<tr>
				<td>日期 : </td>
				<td><?
				$form->show('search_start_date');
				?></td>		
                
                <td> 至 </td>
				<td><?
				$form->show('search_end_date');
				?></td>
			</tr>
            <tr><td>&nbsp;</td></tr>
			<tr>
				<td width="100%" colspan='4'>
				<?
				$form->show('submitbutton');
				// $form->show('resetbutton');
				
				?></td>
			</tr>				
		</table>
	</fieldset>	
	</td>	
	</tr>
</table>    
<?
//if(isset($_POST['search_t_sign']) && $_POST['search_t_sign'] == 1){
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
		$rs = new RecordSetControl3;
		$rs->record_per_page = ADMIN_ROW_PER_PAGE;
		$rs->addnew_link = "?act=addtask";
		$rs->display_new_button = false;
		$rs->sort_field = "t_id";
		$rs->sort_seq = "DESC";
	
		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}
	
		$where_sql = "";
	
		if (strlen(@$_SESSION['search_criteria']['search_t_id'])){
			$where_sql.= " AND b.t_id Like '%".$_SESSION['search_criteria']['search_t_id'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['search_t_name'])){
			$where_sql.= " AND b.t_name Like '%".$_SESSION['search_criteria']['search_t_name'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['search_start_date'])){
			if (strlen(@$_SESSION['search_criteria']['search_end_date'])){
				$where_sql.= " AND b.in_date between '".$_SESSION['search_criteria']['search_start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
			}else{
				$where_sql.= " AND b.in_date > '".$_SESSION['search_criteria']['search_start_date']." 00:00:00'";
			}
		}elseif (strlen(@$_SESSION['search_criteria']['search_end_date'])){
			$where_sql.= " AND b.in_date < '".$_SESSION['search_criteria']['search_end_date']." 23:59:59'";
		}

        //20141117 加 非普通用户只能看到同工厂添加的
        if(!isFtyAdmin()){
            $where_sql .= ' AND b.created_by in (select AdminName from tw_admin where AdminPlatform like "%fty%" and FtyName = (select FtyName from tw_admin where AdminName = "'.$_SESSION["ftylogininfo"]["aName"].'"))';
        }
		// echo $where_sql;
		
		$where_sql.= ' AND b.created_by = t.AdminName AND t.FtyName = s.sid ORDER BY b.t_id DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;
	
		$temp_table = ' fty_task b, tw_admin t, supplier s';
		$list_field = ' SQL_CALC_FOUND_ROWS *, b.*, s.name ';
	
		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';
	
		//$rs->col_width = "100";
		$rs->SetRecordCol("工序号", "t_id");
		$rs->SetRecordCol("工序名称", "t_name");
		$rs->SetRecordCol("工价", "t_price");
		$rs->SetRecordCol("备注", "t_remark");
        $rs->SetRecordCol("供应商", "name");
		$rs->SetRecordCol("建立", "created_by");
		$rs->SetRecordCol("修改", "mod_by");
		$rs->SetRecordCol("日期", "in_date");
		$rs->SetRecordCol("最后修改日期", "mod_date");

		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		$rs->SetRecordCol("修改", "t_id", $sort, $edit,"?act=addtask","modid");
		$rs->SetRecordCol("删除", "t_id", $sort, $edit,"?act=addtask","delid");
		$rs->SetRSSorting('?act=addtask');
	
		$rs->ShowRecordSet($info);
	
	}
//}
?>    

</fieldset>
<?
$form->end();	
}
?>