<?php
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//check admin login
checkAdminLogin();
//20131120 for 360 加meta webkit
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="renderer" content="webkit">
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
<script language="javascript" type="text/javascript" src="/ui/nav.js"></script>

<?php
}else{
	//distribute header
	//put all of js/css files into one each file
	
	/*
	<link rel="stylesheet" type="text/css" href="/cac/?b=ui&f=selectbox.css,main.css" />
	<script type="text/javascript" src="/cac/?b=ui&amp;f=jquery.js,form.js,main.js,selectbox.js,swfobject.js"></script>
	
	*/
?>
<link rel="stylesheet" type="text/css" href="/cac/?g=admincss" />
<script type="text/javascript" src="/cac/?g=adminjs"></script>

<!--link href="/sys/ui/artDialog.css" rel="stylesheet" />
<script src="/sys/ui/artDialog.js"></script-->
<?php
}
?>

<script type="text/javascript" language="JavaScript1.2">
function jsClock(){
	//current date time update
	var now=new Date(); now.hrs=now.getHours(); now.min=now.getMinutes(); now.sec=now.getSeconds();
	
	now.hrs=((now.hrs<10)? "0" : "")+now.hrs;
	now.min=((now.min<10)? "0" : "")+now.min;
	now.sec=((now.sec<10)? "0" : "")+now.sec;
	year = now.getFullYear();
	month = 1+now.getMonth();
	date = ((now.getDate()<10)? "0" : "")+now.getDate();
	month=((month<10)? "0" : "")+month;
	$('#current_datetime').html(year + "-" + month + "-"+date + " "+now.hrs+':'+now.min+':'+now.sec);
	setTimeout('jsClock()',1000);
}

$(function(){ 
	jsClock(); 
});
</script>

<script type="text/javascript">
<? if($_SESSION['luxcraftlogininfo']['aName'] != 'ZJN'){ //我的测试不记录进去 ?>

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-19704593-5']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

<? } ?>
</script>
</head>

<body bgcolor="#FFFFFF">

<table width='100%' border='0' cellpadding='0' cellspacing='0' bgcolor='#FFFFFF' background="images/banner_bg.gif">
	<tr>
	  <td width='180' rowspan='3'><img src="images/gologo.png"  alt="" width="180" height="108"></td>
		<td width='35' height="35">&nbsp;</td>
    	<td width="400" align='left' class='headertitle'><?=SITE_NAME.SITE_VER?></td>
		<td align='left' class='normal'>&nbsp;</td>
		<td align='left' class='normal_num'>&nbsp;</td>	
	</tr>
	<tr>		
		<td>&nbsp;</td>
    	<td class='normal'>Login User : <?=@$_SESSION["luxcraftlogininfo"]["aName"]?></td>
		<td width='110' align='left' class='normal'>Current Time :</td>
		<td align='left' id='current_datetime' class='normal_num'>-</td>	
	</tr>
	<tr>
	  <td>&nbsp;</td>
      <td colspan="3" height="40">
      
<ul id="navmenu-h">
    <li><a href="?act=main" class="otherli">Home</a></li>

	<!--<li><a href="#">Roster<i>&gt;</i></a>
        <ul>
            <li><a href="?act=roster_add&wh_id=KH">Add</a></li>
            <li><a href="?act=roster_view&wh_id=KH">View</a></li>
        </ul>
    </li>-->

    <?php
    //20150323 只能看到自己所属的店的产品
    $my_wh_name = $mysql->qone("select wh_name from tw_admin where AdminName = ?", $_SESSION["luxcraftlogininfo"]["aName"]);
    $_SESSION["luxcraftlogininfo"]["wh_name"] = $my_wh_name['wh_name'];
    ?>

    <li><a href="javascript:void(0);">Sales<i>&gt;</i></a>
        <ul>
            <li><a href="?act=add_sales_invoice&wh_id=<?php echo $_SESSION["luxcraftlogininfo"]["wh_name"]; ?>">Add</a></li>
            <li><a href="?act=search_sales_invoice&page=1">View</a></li>
        </ul>
    </li>

    <li><a href="javascript:void(0);">Membership<i>&gt;</i></a>
        <ul>
            <li><a href="?act=add_membership">Add</a></li>
            <li><a href="?act=search_membership&page=1">View</a></li>
        </ul>
    </li>

    <li><a href="?act=search_item_transfer_form">Item Transfer Form<i>&gt;</i></a>
        <ul>
            <li><a href="?act=add_item_transfer_form">Add</a></li>
        </ul>
    </li>

    <li><a href="javascript:void(0);">Report<i>&gt;</i></a>
        <ul>
            <li><a href="?act=daily_sales_report">Daily Sales Report</a></li>
<!--            <li><a href="?act=monthly_sales_report">Monthly Sales Report</a></li>-->
        </ul>
    </li>
    
    <li><a href="?act=logout" class="otherli">Logout</a></li>
</ul>

      </td>
  </tr>
	
</table>
<BR clear="all" />
<div style="padding:1% 1%;">

	<!-- content start //-->
	<?php	
	//暂时不需要限制IP
	//checkAdminAllowedIP();
	if($model = modelExist3($act, true)){
		require($model);
	}else{
		$myerror->error('阁下发起了未知操作，请返回首页', 'INDEX');
		$act = 'inside_error';
		require(modelExist3($act, true));
	}
	?>
    <!-- content end //-->
</div>
<BR>
<? if(strpos($act, "sales_invoice") === false){//main页面由于div多，所以分割没法在正常的位置显示 ?>
    <HR>
<? } ?>
<div align='center'>
	copyright &copy 2011-<?=date("Y");?> LUX DESIGN LTD ALL RIGHTS RESERVED
</div>

</body>
</html>