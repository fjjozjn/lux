<?

//禁止其他用户进入（临时做法）
/*
if($_SESSION['logininfo']['aName'] != 'zjn' && $_SESSION['logininfo']['aName'] != 'KEVIN'){
	$myerror->error('Without Permission To Access', 'main');
}
*/

if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{	
	// 如果有post資料則給Session，并且清除附在上次翻頁時殘留的$_GET['page']
	if (count($_POST)){
		$_SESSION['search_criteria'] = $_POST;
	}
	
	$form = new My_Forms();
	$formItems = array(
			'start_date' => array(
				'type' => 'text', 
				'restrict' => 'date',
				//'required' => 1,
				//'nostar' => true,
				'value' => @$_SESSION['search_criteria']['start_date'], 
				),	
			'end_date' => array(
				'type' => 'text', 
				'restrict' => 'date',
				//'required' => 1,
				//'nostar' => true,
				'value' => @$_SESSION['search_criteria']['end_date'], 
				),
			'submitbutton' => array(
				'type' => 'submit', 
				'value' => 'Search', 
				'title' => ''),	
			);
	$form->init($formItems);
	$form->begin();	
	
	?>

<table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td class='headertitle' align="center">PRINT PORDUCT ID</td>
	</tr>
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">	
			<tr>
				<td height="35">Created Date Start :<h6 class="required"></h6></td>
				<td><?
				$form->show('start_date');
				?></td>		
                
                <td>Created Date End :<h6 class="required"></h6></td>
				<td><?
				$form->show('end_date');
				?></td>
			</tr>
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
</table><br />


<?
	$form->end();
	
	if (strlen(@$_SESSION['search_criteria']['start_date']) && strlen(@$_SESSION['search_criteria']['end_date'])){
		$info = $mysql->q('select pid from product where in_date between ? AND ? order by in_date desc', $_SESSION['search_criteria']['start_date'].' 00:00:00', $_SESSION['search_criteria']['end_date'].' 23:59:59');

		if($info){
			//$rtn = $mysql->fetch();
			$rtn = $mysql->fetch(0, 1);
			$print_nums = 0;
			//10列*27行		
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr valign='top'>
	<td align="center" width="80%">	
    <fieldset>
	<legend class='legend'>Information ( total : <?=count($rtn)?> )</legend>
        <fieldset class="center2col" style="width:30%"> 
        <legend class='legend'>Action</legend>
            <a class="button" href="model/com/printproduct_pdf.php" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a>
        </fieldset>
<br />
<br />
<?						
			while(count($rtn) > $print_nums){
				echo '<table width="100%" border="1" cellspacing="1" cellpadding="1" align="center">';	
				for($row = 0; $row < 27; $row++){
					echo '<tr>';
					for($col = 0; $col < 10; $col++){
						echo '<td width="10%" align="center">'.(isset($rtn[$row*10+$col+$print_nums]['pid'])?$rtn[$row*10+$col+$print_nums]['pid']:'&nbsp;').'</td>';
					}
					echo '</tr>';		
				}
				echo '</table><br />';
				$print_nums += 270;
			}
		}
		?>
    </fieldset>    
	</td>
    </tr>
</table>    
        <?
	}
}
?>

