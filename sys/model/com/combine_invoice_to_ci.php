<?php
// print_r_pre($_SESSION);
 // print_r_pre($_POST);
 
//check permission 
//die();
//checkAdminPermission(PERM_MANAGE_ADMIN);


if(!$myerror->getWarn()){

	$form = new My_Forms();	
	
	$formItems = array(	
		'percentage' => array(
			'type' => 'text', 
			'value' => '',
			'required' => GENERAL_YES,
			'minlen' => 1, 
			'maxlen' => 20,
			'nostar' => true,
			'value' => 50,
			),							
		'invoice' => array(
			'type' => 'select',
			'options' => get_invoice_no(),
			),	
		'invoice_group_textarea' => array(
			'type' => 'textarea',
			'readonly' => 'readonly',
			'minlen' => 0, 
			'maxlen' => 200,	
			'value' => '',	
			'info'=> '点击按钮添加需要合并的 Invoice NO. 到 Group 框'		
			),		
		'submitbtn'	=> array(
			'type' => 'submit', 'value' => ' Submit '),
	);	
	

	$form->init($formItems);
	// print_r_pre($formItems);
	// die();		
	if(!$myerror->getAny() && $form->check()){
		//跳转
		$percentage = $_POST['percentage'];
		$invoice_str = trim($_POST['invoice_group_textarea']);
		$invoice = substr($invoice_str, 0, -1);
		header('location:?act=com-modifycustomsinvoice&percent='.$percentage.'&combine_invoice='.$invoice);
	}
	
	// print_r_pre($_POST);
	// print_r_pre($_GET);
	// print_r_pre($GLOBALS);
	if($myerror->getError()){
		require_once(ROOT_DIR.'model/inside_error.php');
	}elseif($myerror->getOk()){
		require_once(ROOT_DIR.'model/inside_ok.php');
	}else{
		if($myerror->getWarn()){
			require_once(ROOT_DIR.'model/inside_warn.php');
		}
		$form->begin();	
		?>

	<table width="60%" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class='headertitle' align="center">Combine Multi-Invoices to CI</td>
		</tr>
		<tr>
			<td align="center">	
			<fieldset>
			<legend class='legend'>information</legend>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
					<tr align="right">
						<td width="40%">Invoice Value Down To : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td width="40%" align="left"><? $form->show('percentage');?></td>
                        <td align="left">%</td>
					</tr>                   
					<tr align="right">
						<td>Invoice NO.: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('invoice');?></td>
						<td align="left"><img title="add" style="opacity: 0.5;" onclick="addInvoice()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="images/add_small.png"></td>
            			<td align="left"><img title="delete" style="opacity: 0.5;" onclick="delInvoice()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="images/del_small.png"></td>
					</tr>	
					<tr align="right">
						<td valign="top">Group : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('invoice_group_textarea');?></td>
					</tr>                    					
							
					<tr>
						<td>&nbsp;</td>
						<td>
						<?
						$form->show('submitbtn');
						// $form->show('resetbutton');
						$form->end();
						?></td>	
					</tr>	
				</table>
			</fieldset>	
			</td>	
		</tr>
	</table>
	<?
	}
}else{
	require_once(ROOT_DIR.'model/inside_warn.php');
}
?>