<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

judgeFtyPerm( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if( !isset($_POST['m_sign']) || $_POST['m_sign'] != 1){
	if( isset($_GET['delid']) && $_GET['delid'] != ''){
		//$rtn = $mysql->q('select distinct gid from g_m where mid = ?', $_GET['delid']);
		//if(!$rtn){
			$rs = $mysql->q('delete from fty_material where m_id = ?', $_GET['delid']);
			if($rs){
				$myerror->ok('刪除物料编号资料 成功!', 'addmaterial&page=1');	
			}else{
				$myerror->error('系统出错，刪除物料资料 失败', 'addmaterial&page=1');
			}
        /*
		}else{
			$result = $mysql->fetch();
			$result_gid = '';
			for($i = 0; $i < count($result); $i++){
				$result_gid .= ($i!=0?',':'') . $result[$i]['gid'];
			}
			//如果已把此物料编号添加到form中，则不能删除
			$myerror->error('此物料编号 '.$_GET['delid'].' 已被使用，不能删除。如要删除此项，需在以下表单（'.$result_gid.'）中去除此物料的选定。', 'addmaterial');
		}
        */
	}
}


if( (isset($_GET['modid']) && $_GET['modid'] != '') || (isset($_GET['addid']) && $_GET['addid'] != '') ) {
	$mod_result = $mysql->qone('select * from fty_material where m_id = ?', isset($_GET['modid'])?$_GET['modid']:$_GET['addid']);
	
	foreach($m_type as $v){
		if( $v[0] == $mod_result['m_type']){
			$mod_result['m_type'] = $v[1];
			break;
		}
	}
	foreach($m_unit as $v){
		if( $v[0] == $mod_result['m_unit']){
			$mod_result['m_unit'] = $v[1];
			break;
		}
	}
}

		
$goodsForm = new My_Forms();
$formItems = array(
		
		//'m_id' => array('title' => '物料编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['m_id'])?$mod_result['m_id']:''),
		'm_name' => array('title' => '名称', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['m_name'])?$mod_result['m_name']:''),
		'm_color' => array('title' => '规格颜色', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['m_color'])?$mod_result['m_color']:''),
		'm_type' => array('title' => '类别', 'type' => 'select', 'options' => $m_type, 'required' => 1, 'value' => isset($mod_result['m_type'])?$mod_result['m_type']:''),
		'm_price' => array('title' => '单价', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['m_price'])?$mod_result['m_price']:''),
		'm_unit' => array('title' => '计算价格单位', 'type' => 'select', 'options' => $m_unit, 'required' => 1, 'value' => isset($mod_result['m_unit'])?$mod_result['m_unit']:''), 
		//'m_value' => array('title' => '值（数量/重量）', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20),
		'm_loss' => array('title' => '损耗率(%)', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['m_loss'])?$mod_result['m_loss']:''), 
		'm_description_en' => array('title' => '英文描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 500, 'value' => isset($mod_result['m_description_en'])?$mod_result['m_description_en']:'', 'addon' => 'style="width:200px"'), 
		
		//隱藏提交標識，用於分辨是goodsForm表的提交還是form表的提交
		'm_sign' => array('type' => 'hidden', 'value' => 1),
		
		'submitbtn'	=> array('type' => 'submit', 'value' => ' 提交 '),
		);

if(isset($_GET['modid'])){
	$formItems['m_id'] = array('title' => '物料编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['m_id'])?$mod_result['m_id']:'', 'readonly' => 'readonly');
}

$goodsForm->init($formItems);

$form = new My_Forms();
$formItems = array(

		'search_m_id' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['search_m_id'],
			'minlen' => 1, 
			'maxlen' => 20,
			),	
		'search_m_name' => array(
			'type' => 'text', 
			'value' => @$_SESSION['search_criteria']['search_m_name'], 
			),
		'search_m_type' => array(
			'type' => 'select', 
			'options' => $m_type,
			'value' => @$_SESSION['search_criteria']['search_m_type'], 
			),
        'search_m_color' => array(
            'type' => 'text',
            'value' => @$_SESSION['search_criteria']['search_m_color'],
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
		'search_m_sign' => array(
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

if(isset($_POST['m_sign']) && $_POST['m_sign'] == 1){
	if(!$myerror->getAny() && $goodsForm->check()){
		//$m_id = $_POST['m_id'];
		//20121023 添加material时改为自动生成
        if(isset($_GET['addid'])){
            $rtn_addid = $mysql->qone('select m_id from fty_material where m_id like ? order by id desc limit 1', substr($_GET['addid'], 0, 7).'%');
            $m_id = (strpos($rtn_addid['m_id'], '-')?(substr($rtn_addid['m_id'], 0, -3).sprintf("%03d", substr($rtn_addid['m_id'], -3)+1)):$rtn_addid['m_id'].'-001');
        }else{
            $m_id = isset($_POST['m_id'])?$_POST['m_id']:fty_autoGenerationID();
        }

        //fb($m_id);die('@');

		$m_name = $_POST['m_name'];
		$m_color = $_POST['m_color'];
		$m_loss = $_POST['m_loss'];
		$m_description_en = $_POST['m_description_en'];
		
		$m_type_insert = '';
		foreach( $m_type as $v){
			if( $v[1] == $_POST['m_type']){
				$m_type_insert = $v[0];
				break;	
			}
		}
		$m_price = $_POST['m_price'];
		
		$m_unit_insert = '';
		foreach( $m_unit as $v){
			if( $v[1] == $_POST['m_unit']){
				$m_unit_insert = $v[0];
				break;	
			}
		}

        $today = dateMore();
        $staff = $_SESSION["ftylogininfo"]["aName"];
		
		if( isset($_GET['modid']) && $_GET['modid'] != ''){
			$result = $mysql->q('update fty_material set m_name = ?, m_color = ?, m_type = ?, m_price = ?, m_unit = ?, m_loss = ?, description_en = ?, mod_by = ?, mod_date = ? where m_id = ?', $m_name, $m_color, $m_type_insert, $m_price, $m_unit_insert, $m_loss, $m_description_en, $staff, $today, $_GET['modid']);
			if($result){
				$myerror->ok('修改物料资料 成功!', 'addmaterial&page=1');
			}else{
				$myerror->error('由于系统原因，修改物料资料 失败', 'addmaterial&page=1');
			}			
		}else{//添加 或 新增副号
			$result = $mysql->q('insert into fty_material values (NULL, '.moreQm(12).')', $m_id, $m_name, $m_color, $m_type_insert, $m_price, $m_unit_insert, $m_loss, $m_description_en, $staff, '', $today, $today);
			if($result){
				$result = intval($result);
				if(is_int($result) && $result > 0){
					$myerror->ok('新增物料资料 成功!', 'addmaterial&page=1');
				}else{
					$myerror->error('由于返回值异常，新增物料资料 失败', 'addmaterial&page=1');
				}
			}else{
				$myerror->error('由于系统原因，新增物料资料 失败', 'addmaterial&page=1');
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
<h1 class="green">管理物料资料<em>*号为必填项</em></h1>
<fieldset>
<legend class='legend'><?=isset($_GET['modid'])?'修改物料资料':(isset($_GET['addid'])?'新增副号':'添加物料资料')?></legend>

<?php
$goodsForm->begin();
?>
<table width="100%" id="table">
  <tr>
  	<td width="25%">
    <? 
	if(isset($_GET['modid'])){ 
    	$goodsForm->show('m_id');	
	}else{
	?>
    <div class="set"><label class="formtitle"><?=isset($_GET['addid'])?$_GET['addid'].'副号':'物料编号'?></label><br />（自动产生）</div>
    <? 
	}
	?>
    </td>
    <td width="25%"><? $goodsForm->show('m_name');?></td>
	<td width="25%"><? $goodsForm->show('m_color');?></td>
	<td width="25%"><? $goodsForm->show('m_type');?></td>  
  </tr>
  <tr valign="top">
  	<td width="25%"><? $goodsForm->show('m_price');?></td>
    <td width="25%"><? $goodsForm->show('m_unit');?></td>
	<td width="25%"><? $goodsForm->show('m_loss');?></td>
	<td width="25%"><? $goodsForm->show('m_description_en');?><? $goodsForm->show('m_sign');?></td>  
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
<a name="search_material" id="search_material"></a>
<fieldset>
<legend class='legend'>管理物料资料</legend>
<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>搜索</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td>物料编号 : </td>
				<td><?
				$form->show('search_m_id');
				$form->show('search_m_sign');
				?></td>	

				<td>类别 : </td>
				<td><? $form->show('search_m_type'); ?></td>
			</tr>
            <tr>
            	<td>名称 : </td>
				<td><? $form->show('search_m_name'); ?></td>
                <td>规格颜色 : </td>
				<td><? $form->show('search_m_color'); ?></td>
            </tr>	
			<tr>
				<td>日期 : </td>
				<td><? $form->show('search_start_date'); ?></td>
                <td> 至 </td>
				<td><? $form->show('search_end_date'); ?></td>
			</tr>
            <tr><td>&nbsp;</td></tr>
			<tr>
				<td width="100%" colspan='4'>
				<? $form->show('submitbutton');// $form->show('resetbutton'); ?></td>
			</tr>				
		</table>
	</fieldset>	
	</td>	
	</tr>
</table>    
<?
//if(isset($_POST['search_m_sign']) && $_POST['search_m_sign'] == 1){
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
		$rs->addnew_link = "?act=addmaterial";
		$rs->display_new_button = false;
		$rs->sort_field = "m_id";
		$rs->sort_seq = "DESC";
	
		$current_page = 1;
		$start_row = 0;
		$end_row = $rs->record_per_page;
		if (set($_GET['page'])){
			$current_page = intval($_GET['page']);
			$start_row = (($current_page-1) * $rs->record_per_page);
		}
	
		$where_sql = "";
	
		if (strlen(@$_SESSION['search_criteria']['search_m_id'])){
			$where_sql.= " AND b.m_id Like '%".$_SESSION['search_criteria']['search_m_id'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['search_m_name'])){
			$where_sql.= " AND b.m_name Like '%".$_SESSION['search_criteria']['search_m_name'].'%\'';
		}
        if (strlen(@$_SESSION['search_criteria']['search_m_color'])){
			$where_sql.= " AND b.m_color Like '%".$_SESSION['search_criteria']['search_m_color'].'%\'';
		}
		if (strlen(@$_SESSION['search_criteria']['search_m_type'])){
			$m_type_search = '';
			foreach( $m_type as $v){
				if( $v[1] == $_SESSION['search_criteria']['search_m_type']){
					$m_type_search = $v[0];
					break;	
				}
			}
			$where_sql.= " AND b.m_type Like '%".$m_type_search.'%\'';
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
		
		$where_sql.= ' AND b.created_by = t.AdminName AND t.FtyName = s.sid ORDER BY b.m_id DESC ';
		$_SESSION['search_criteria']['page'] = $current_page;
	
		$temp_table = ' fty_material b, tw_admin t, supplier s';
		$list_field = ' SQL_CALC_FOUND_ROWS *, b.*, s.name ';
	
		//get the row count for this seaching criteria
		//$row_count = $mysql->sp('CALL backend_list_count(?, ?)', $temp_table,$where_sql);
		// echo 'CALL backend_list_count("'.$temp_table.'", "'.$where_sql.'");<BR>';
		//echo 'SELECT '.$list_field.' FROM '.$temp_table.' WHERE 1 '.$where_sql;
		$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);
		//$info = $mysql->sp('CALL backend_list(?, ?, ?, ?)', $start_row, $end_row, $temp_table,$where_sql);
		// echo 'CALL backend_list(0,10,"'.$temp_table.'", "'.$where_sql.'")';
	
		//$rs->col_width = "100";
		$rs->SetRecordCol("物料编号", "m_id");
		$rs->SetRecordCol("名称", "m_name");
		$rs->SetRecordCol("规格颜色", "m_color");
		$rs->SetRecordCol("类别", "m_type");
		$rs->SetRecordCol("单价", "m_price");
		$rs->SetRecordCol("计算价格单位", "m_unit");
        $rs->SetRecordCol("损耗率(%)", "m_loss");
        $rs->SetRecordCol("供应商", "name");
        $rs->SetRecordCol("建立", "created_by");
        $rs->SetRecordCol("修改", "mod_by");
        $rs->SetRecordCol("日期", "in_date");
		$rs->SetRecordCol("最后修改日期", "mod_date");

		$sort = GENERAL_NO;
		$edit = GENERAL_YES;
		$rs->SetRecordCol("修改", "m_id", $sort, $edit,"?act=addmaterial","modid");
		$rs->SetRecordCol("新增副号", "m_id", $sort, $edit,"?act=addmaterial","addid");
		$rs->SetRecordCol("删除", "m_id", $sort, $edit,"?act=addmaterial","delid");
		$rs->SetRSSorting('?act=addmaterial');
	
		$rs->ShowRecordSet($info);
	
	}
//}
?>    

</fieldset>
<?
$form->end();
}
?>