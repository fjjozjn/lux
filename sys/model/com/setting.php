<?php
// print_r_pre($_SESSION);
 // print_r_pre($_POST);
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//check permission 
//checkAdminPermission(PERM_MAINTAIN_ONOFF);

//$isMt = $isMt ? 1 : 0;

//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}


//mod 20120720 这个页面现在能自动显示最后一个编号，即使数据库的setting表里面没有记录，这样就能让用户知道下一个编号是什么，提交后，能保存到setting表

if( /*!isset($_POST['currency']) && */!isset($_POST['markup'])){
	$rtn = $mysql->qone('select * from setting');
	
	//sample order
	$rtn_so = $mysql->qone('select so_no from sample_order order by so_no desc limit 1');
	$so = substr($rtn_so['so_no'], 0, 3).sprintf("%07d", substr($rtn_so['so_no'], 3)+1);
	
	//quotation
	$rtn_qu = $mysql->qone('select qid from quotation order by qid desc limit 1');
	$qu = substr($rtn_qu['qid'], 0, 3).sprintf("%07d", substr($rtn_qu['qid'], 3)+1);
	
	//proforma
	$rtn_pi = $mysql->qone('select pvid from proforma order by pvid desc limit 1');
	$pi = substr($rtn_pi['pvid'], 0, 3).sprintf("%07d", substr($rtn_pi['pvid'], 3)+1);	
	
	//invoice
	$rtn_in = $mysql->qone('select vid from invoice order by vid desc limit 1');
	$in = substr($rtn_in['vid'], 0, 3).sprintf("%07d", substr($rtn_in['vid'], 3)+1);
		
	//Customs Invoice 不这样做，因为直接创建的是以T开头，而由invoice转过来的是CI开头
	
	//purchase
	$rtn_pu = $mysql->qone('select pcid from purchase order by pcid desc limit 1');
	$pu = substr($rtn_pu['pcid'], 0, 3).sprintf("%07d", substr($rtn_pu['pcid'], 3)+1);
	
	//20130401 去掉bom的部分，因为每个厂商有各自的流水号，所以用不到这个了
	//bom material
	//$rtn_ma = $mysql->qone('select m_id from material order by m_id limit 1');
	//$ma = substr($rtn_ma['m_id'], 0, 3).sprintf("%07d", substr($rtn_ma['m_id'], 3)+1);
	
	//bom task
	//$rtn_ta = $mysql->qone('select t_id from task order by t_id desc limit 1');
	//$ta = substr($rtn_ta['t_id'], 0, 3).sprintf("%07d", substr($rtn_ta['t_id'], 3)+1);

    //20130528 credit note
    $rtn_cn = $mysql->qone('select cn_no from credit_note order by cn_no desc limit 1');
    $cn = substr($rtn_cn['cn_no'], 0, 3).sprintf("%07d", substr($rtn_cn['cn_no'], 3)+1);

    //20130529
    $rtn_pl = $mysql->qone('select pl_id from packing_list order by pl_id desc limit 1');
    $pl = substr($rtn_pl['pl_id'], 0, 3).sprintf("%07d", substr($rtn_pl['pl_id'], 3)+1);

    //20160507
    $rtn_fcb = $mysql->qone('select fcb_id from fty_chargeback_form order by fcb_id desc limit 1');
    $fcb = substr($rtn_fcb['fcb_id'], 0, 3).sprintf("%07d", substr($rtn_fcb['fcb_id'], 3)+1);
}

if(!$myerror->getAny()){

	$form = new My_Forms(/*array('noFocus' => true)*/);	
	// print_r_pre($tools_ip_setting);
	$formItems = array(
	/*
		'currency' => array(
			'type' => 'text', 
			'minlen' => 1, 
			'maxlen' => 20, 
			'required' => 1,
			'value' => isset($rtn['currency'])?$rtn['currency']:''
			),
			*/
		'markup' => array(
			'type' => 'text',	
			'minlen' => 1, 
			'maxlen' => 20, 
			'required' => 1,
			'value' => isset($rtn['markup'])?$rtn['markup']:''			
			),
        'pid' => array(
            'type' => 'text',
            'minlen' => 1,
            'maxlen' => 20,
            'restrict' => 'number',
            'value' => isset($rtn['pid'])?$rtn['pid']:''
        ),
        'so_no' => array(
			'type' => 'text',	
			'minlen' => 1, 
			'maxlen' => 20, 
			'value' => (isset($rtn['so_no']) && $rtn['so_no']!='')?$rtn['so_no']:@$so			
			),			
		'qid' => array(
			'type' => 'text',	
			'minlen' => 1, 
			'maxlen' => 20, 
			'value' => (isset($rtn['qid']) && $rtn['qid']!='')?$rtn['qid']:@$qu		
			),
		'pvid' => array(
			'type' => 'text',	
			'minlen' => 1, 
			'maxlen' => 20, 
			'value' => (isset($rtn['pvid']) && $rtn['pvid']!='')?$rtn['pvid']:@$pi			
			),		
		'vid' => array(
			'type' => 'text',	
			'minlen' => 1, 
			'maxlen' => 20, 
			'value' => (isset($rtn['vid']) && $rtn['vid']!='')?$rtn['vid']:@$in			
			),
		'cid' => array(
			'type' => 'text',	
			'minlen' => 1, 
			'maxlen' => 20, 
			'value' => isset($rtn['cid'])?$rtn['cid']:''			
			),
		'pcid' => array(
			'type' => 'text',	
			'minlen' => 1, 
			'maxlen' => 20, 
			'value' => (isset($rtn['pcid']) && $rtn['pcid']!='')?$rtn['pcid']:@$pu			
			),	
			/*	
		'm_id' => array(
			'type' => 'text',	
			'minlen' => 1, 
			'maxlen' => 20, 
			'value' => (isset($rtn['m_id']) && $rtn['m_id']!='')?$rtn['m_id']:@$ma			
			),
		't_id' => array(
			'type' => 'text',	
			'minlen' => 1, 
			'maxlen' => 20, 
			'value' => (isset($rtn['t_id']) && $rtn['t_id']!='')?$rtn['t_id']:@$ta		
			),	
			*/
        'cn_no' => array(
            'type' => 'text',
            'minlen' => 1,
            'maxlen' => 20,
            'value' => (isset($rtn['cn_no']) && $rtn['cn_no']!='')?$rtn['cn_no']:@$cn
        ),
        'pl_id' => array(
            'type' => 'text',
            'minlen' => 1,
            'maxlen' => 20,
            'value' => (isset($rtn['pl_id']) && $rtn['pl_id']!='')?$rtn['pl_id']:@$pl
        ),
        'fcb_id' => array(
            'type' => 'text',
            'minlen' => 1,
            'maxlen' => 20,
            'value' => (isset($rtn['fcb_id']) && $rtn['fcb_id']!='')?$rtn['fcb_id']:@$fcb
        ),
        'show_product_pwd' => array(
			'type' => 'text',	
			'minlen' => 1, 
			'maxlen' => 20, 
			'value' => (isset($rtn['show_product_pwd']) && $rtn['show_product_pwd']!='')?$rtn['show_product_pwd']:''		
			),
        'al_start_days' => array(
            'type' => 'text',
            'restrict' => 'number',
            'minlen' => 1,
            'maxlen' => 10,
            'addon' => 'style="width:40px; float:left"',
            'value' => (isset($rtn['al_start_days']) && $rtn['al_start_days']!='')?$rtn['al_start_days']:''
        ),
        'al_end_days' => array(
            'type' => 'text',
            'restrict' => 'number',
            'minlen' => 1,
            'maxlen' => 10,
            'addon' => 'style="width:40px; float:left"',
            'value' => (isset($rtn['al_end_days']) && $rtn['al_end_days']!='')?$rtn['al_end_days']:''
        ),
        'al_increase_days' => array(
            'type' => 'text',
            'restrict' => 'number',
            'minlen' => 1,
            'maxlen' => 10,
            'addon' => 'style="width:40px; float:left"',
            'value' => (isset($rtn['al_increase_days']) && $rtn['al_increase_days']!='')?$rtn['al_increase_days']:''
        ),
        'email_fty_user_info_to' => array(
            'type' => 'text',
            'minlen' => 1,
            'maxlen' => 50,
            'value' => (isset($rtn['email_fty_user_info_to']) && $rtn['email_fty_user_info_to']!='')?$rtn['email_fty_user_info_to']:''
        ),
        'email_admin_request_to' => array(
            'type' => 'text',
            'minlen' => 1,
            'maxlen' => 50,
            'value' => (isset($rtn['email_admin_request_to']) && $rtn['email_admin_request_to']!='')?$rtn['email_admin_request_to']:''
        ),
		'submitbtn'	=> array(
			'type' => 'submit', 'value' => ' Submit '),
	);	
	

	$form->init($formItems);
	if(!$myerror->getAny() && $form->check()){
		
		$rtn = $mysql->q('update setting set markup = ?, pid = ?, so_no = ?, qid = ?, pvid = ?, vid = ?, cid = ?, pcid = ?, cn_no = ?, pl_id = ?, fcb_id= ?,show_product_pwd = ?, al_start_days = ?, al_end_days = ?, al_increase_days = ?, email_fty_user_info_to = ?, email_admin_request_to = ?', $_POST['markup'], $_POST['pid'], $_POST['so_no'], $_POST['qid'], $_POST['pvid'], $_POST['vid'], $_POST['cid'], $_POST['pcid'], $_POST['cn_no'], $_POST['pl_id'], $_POST['fcb_id'], $_POST['show_product_pwd'], $_POST['al_start_days'], $_POST['al_end_days'], $_POST['al_increase_days'], $_POST['email_fty_user_info_to'], $_POST['email_admin_request_to']);
		
		if ($rtn){
			$myerror->ok('设定系统参数成功', 'com-setting');
		}else{
			$myerror->warn('设定系统参数没有改变，请稍后再试', 'main');
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
		$form->begin();	
	?>

	<table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
		<tr>
			<td class='headertitle' align="center">Setting</td>
		</tr>
		<tr>
			<td align="center">	
			<fieldset>
			<legend class='legend'>information</legend>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
					<!--tr valign='top'>
						<td height="35">currency : </td>  
						<td align='left'><? //$form->show('currency');?></td>
					</tr-->	
					<tr valign='top'>
						<td width="28%" height="35">markup : </td>
						<td align='left'><? $form->show('markup');?></td>
					</tr>
                    <tr valign='top'>
                        <td width="28%" height="35">Product NO. : </td>
                        <td align='left'><? $form->show('pid');?></td>
                    </tr>
                    <tr valign='top'>
						<td height="35">Sample Order NO. : </td>  
						<td align='left'><? $form->show('so_no');?></td>
					</tr>						
					<tr valign='top'>
						<td height="35">Quotation NO. : </td>  
						<td align='left'><? $form->show('qid');?></td>
					</tr>	
					<tr valign='top'>
						<td height="35">Proforma NO. : </td>  
						<td align='left'><? $form->show('pvid');?></td>
					</tr>			
					<tr valign='top'>
						<td height="35">Invoice NO. : </td>  
						<td align='left'><? $form->show('vid');?></td>
					</tr>
					<tr valign='top'>
						<td height="35">Customs Invoice NO. : </td>  
						<td align='left'><? $form->show('cid');?></td>
					</tr>
					<tr valign='top'>
						<td height="35">Purchase NO. : </td>  
						<td align='left'><? $form->show('pcid');?></td>
					</tr>	
					<!--tr valign='top'>
						<td height="35">Material NO. : </td>  
						<td align='left'><? //$form->show('m_id');?></td>
					</tr>	
					<tr valign='top'>
						<td height="35">Task NO. : </td>  
						<td align='left'><? //$form->show('t_id');?></td>
					</tr-->
                    <tr valign='top'>
                        <td height="35">Credit Note NO. : </td>
                        <td align='left'><? $form->show('cn_no');?></td>
                    </tr>
                    <tr valign='top'>
                        <td height="35">Packing List NO. : </td>
                        <td align='left'><? $form->show('pl_id');?></td>
                    </tr>
                    <tr valign='top'>
                        <td height="35">Factory Chargeback Form NO. : </td>
                        <td align='left'><? $form->show('fcb_id');?></td>
                    </tr>
					<tr valign='top'>
						<td height="35">Show Product PWD : </td>
						<td align='left'><? $form->show('show_product_pwd');?></td>
					</tr>
                    <tr valign='top'>
                        <td height="35">Annual Leave: </td>
                        <td align="left"><div style="float: left">From&nbsp;</div><? $form->show('al_start_days');?><div style="float: left">&nbsp;day(s) to&nbsp;</div><? $form->show('al_end_days');?><div style="float: left">&nbsp;day(s), increase&nbsp;</div><? $form->show('al_increase_days');?><div style="float: left">&nbsp;day(s) annually&nbsp;</div></td>
                    </tr>
                    <tr>
                        <td height="35">Email Fty User Info To : </td>
                        <td align='left'><? $form->show('email_fty_user_info_to');?></td>
                    </tr>
                    <tr>
                        <td height="35">Email Admin Request To : </td>
                        <td align='left'><? $form->show('email_admin_request_to');?></td>
                    </tr>

					<tr valign='top'>
						<td colspan='2' height="35">
						<?
						$form->show('submitbtn');
						?></td>	
					</tr>	
				</table>
			</fieldset>	
			</td>	
		</tr>
	</table>
	<?
		$form->end();
	}
}else{
	require_once(ROOT_DIR.'model/inside_warn.php');
}
?>