<?
require('../in7/global.php');
// define('SITE_NAME_VER', 'GO收費平台系統管理 BETA VER 2.00.001');

?>
<html>
<link href="stylesheet.css" rel="stylesheet" type="text/css">
<script type="text/javascript" language="JavaScript1.2" src="stm31.js"></script>
<script language="javascript" src="../ui/jquery.js"></script>
<script language="javascript" src="../ui/jquery.selectbox.js"></script>
<script language="javascript" src="../ui/form.js"></script>
<script language="javascript" src="../ui/main.js"></script>
<script language="javascript" type="text/javascript" src="../ui/cal/WdatePicker.js"></script>
<link rel="stylesheet" type="text/css" href="../ui/main.utf8.css" />
<link rel="stylesheet" type="text/css" href="../ui/selectbox.css" />
<script type="text/javascript" language="JavaScript1.2">
var now=new Date(); now.hrs='00'; now.min='00'; now.sec='00';
function onLoadFunc(){
	//current date time update
	now=new Date(); now.hrs=now.getHours(); now.min=now.getMinutes(); now.sec=now.getSeconds();
	
	now.hrs=((now.hrs<10)? "0" : "")+now.hrs;
	now.min=((now.min<10)? "0" : "")+now.min;
	now.sec=((now.sec<10)? "0" : "")+now.sec;
	
	year = now.getFullYear();
	month = 1+now.getMonth();
	date = now.getDate();

	month=((month<10)? "0" : "")+month;
	
	document.all.current_datetime.innerHTML=year + "-" + month + "-"+date + " "+now.hrs+':'+now.min+':'+now.sec;
	setTimeout('onLoadFunc()',1000);
	
	
	
}

window.onload=onLoadFunc;

</script>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=SITE_NAME_VER?></title>
<body bgcolor="#FFFFFF">
<table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' bgcolor='#FFFFFF'>
	<tr>
		<td rowspan='2' valign='top' align='left' width='200'><img src="images/gologo.jpg"  alt=""></td>
		<td width='5%'>&nbsp;</td>
		<td align='left' class='headertitle'><?=SITE_NAME_VER?></td>
		<td width='8%' align='left' class='normal'>現在時間 :</td>
		<td width='15%' align='left' id='current_datetime' class='normal_num'>&nbsp;</td>
	</tr>
	<tr>		
		<td width='5%'>&nbsp;</td>
		<td class='normal'>登入帳號 : </td>
		<!--td width='8%' align='left' class='normal'>在線人數 :</td>
		<td width='15%' align='left' class='normal_num'>9999</td-->
		<td width='8%' align='left' class='normal'>&nbsp;</td>
		<td width='15%' align='left' class='normal_num'>&nbsp;</td>		
	</tr>
</table>
<script type="text/javascript" language="JavaScript1.2" src="menu.js"></script>