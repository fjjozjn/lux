<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
<link href="ui/stylesheet.css" rel="stylesheet" type="text/css">
<link href="ui/nav.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="/ui/jquery.js"></script>
<script language="javascript" type="text/javascript" src="/ui/selectbox.js"></script>
<script language="javascript" type="text/javascript" src="/ui/form.js"></script>
<script language="javascript" type="text/javascript" src="/ui/main.js"></script>
<script language="javascript" type="text/javascript" src="/ui/cal/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="ui/nav.js"></script>
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
		<td align='center' class='headertitle2'><?=SITE_NAME.SITE_VER?></td>
	</tr>	
	<tr>
		<td align='center'>&nbsp;</td>		
	</tr>		
	<tr>
		<td align='center'><img src="images/gologo.jpg"  alt=""></td>		
	</tr>	
</table>
<BR>
<BR>
<BR>
<table width="500" border="0" align="center" cellpadding="5" cellspacing="5">
<tr>
  <td>
<?php
$myerror->getMsg();
?>
</td></tr></table>
<BR><HR>
<div align='center'>	
	copyright &copy <?=date("Y");?>  LUX DESIGN LTD All rights reserved
</div>

</body>
</html>