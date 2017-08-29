<?php
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?=SITE_NAME.SITE_VER?></title>
<?php
if(!IS_DIST){
	//develop header
	//include too much little js/css file
?>
<link rel="stylesheet" type="text/css" href="/ui/reset.css" />
<link rel="stylesheet" type="text/css" href="/ui/main.css" />
<link rel="stylesheet" type="text/css" href="/ui/form.css" />
<link rel="stylesheet" type="text/css" href="/ui/selectbox.css" />
<link href="/ui/stylesheet.css" rel="stylesheet" type="text/css">
<link href="/ui/nav.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="/ui/jquery.js"></script>
<script language="javascript" type="text/javascript" src="/ui/selectbox.js"></script>
<script language="javascript" type="text/javascript" src="/ui/form.js"></script>
<script language="javascript" type="text/javascript" src="/ui/main.js"></script>
<script language="javascript" type="text/javascript" src="/ui/cal/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="/ui/nav.js"></script>
<?php
}else{
	//distribute header
	//put all of js/css files into one each file
?>
<link rel="stylesheet" type="text/css" href="/cac/?g=admincss" />
<script type="text/javascript" src="/cac/?g=adminjs"></script>
<?php
}
?>
</head>

<body>

<table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' bgcolor='#FFFFFF'>
	<tr>
    	<td width="38%">&nbsp;</td>
		<td align='center' width="20%" class='headertitle2'><?=SITE_NAME.SITE_VER?></td>
        <td width="42%">&nbsp;</td>
	</tr>	
	<tr>
		<td align='center' colspan="3">&nbsp;</td>		
	</tr>		
	<tr>
		<td width="38%">&nbsp;</td>
		<td align='center' width="20%" background="../sys/images/banner_bg.gif"><img src="images/gologo.png"  alt="" width="180" height="108"></td>
		<td width="42%">&nbsp;</td>
	</tr>	
</table>
<BR>
<BR>
<BR>

<?php
if(!$myerror->getError()){
	if($act == 'register'){
		require_once('register.php');	
	}else{
		require_once('login.php');
	}
}
?>

<BR><HR>
<div align='center'>	
	copyright &copy 2011-<?=date("Y");?> LUX DESIGN LTD All rights reserved
</div>

</body>
</html>