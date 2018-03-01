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
<script language="javascript" type="text/javascript" src="/ui/jquery.tablednd.0.7.min.js"></script>
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
<? if($_SESSION['ftylogininfo']['aName'] != 'ZJN'){ //我的测试不记录进去 ?>

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
	  <td width='180' rowspan='3'><img src="images/gologo.jpg"  alt="" width="180" height="108"></td>
		<td width='35' height="35">&nbsp;</td>
    	<td width="400" align='left' class='headertitle'><?=SITE_NAME.SITE_VER?></td>
		<td align='left' class='normal'>&nbsp;</td>
		<td align='left' class='normal_num'>&nbsp;</td>	
	</tr>
	<tr>		
		<td>&nbsp;</td>
    	<td class='normal'>登入用户 : <?=@$_SESSION["ftylogininfo"]["aNameChi"]?></td>
		<td width='90' align='left' class='normal'>现在时间 :</td>
		<td align='left' id='current_datetime' class='normal_num'>-</td>	
	</tr>
	<tr>
	  <td>&nbsp;</td>
      <td colspan="3" height="40">
      
<ul id="navmenu-h">
    <li><a href="?act=main" class="otherli">首页</a></li>
<?
$rtn = $mysql->qone('select FtyGrpID from tw_admin where AdminID = ?', $_SESSION["ftylogininfo"]["aID"]);
if(strpos($rtn['FtyGrpID'], '1') !== false){
?>
    <li><a href="#">样板<i>&gt;</i></a>
        <ul>
		<?
		if(strpos($rtn['FtyGrpID'], '2') !== false){
		?>
			<li><a href="?act=searchproduct_new&page=1">产品</a></li>
		<?
		}
		?>
			<li><a href="?act=searchform&page=1">BOM<i>&gt;</i></a>
                <ul>
            		<li><a href="?act=sendform">新增</a></li>
        		</ul>
            </li>
            <li><a href="?act=addtask&page=1">管理工序</a></li>
            <li><a href="?act=searchsample_order&page=1">样板订单<!--<i>&gt;</i>--></a>
                <!--<ul>
                    <li><a href="?act=addsample_order">新增</a></li>
                </ul>-->
            </li>
        </ul>
    </li>
<?
}
?>
    <li><a href="#">物料<i>&gt;</i></a>
        <ul>
            <li><a href="?act=addmaterial&page=1">管理物料</a></li>
            <li><a href="?act=search_fty_material_require&page=1">物料需求单</a></li>
            <li><a href="?act=search_material_buy&page=1">物料采购单<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=add_material_buy">新增</a></li>
                </ul>
            </li>
            <li><a href="?act=search_material_warehouse&page=1">物料仓库<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=search_material_in&page=1">物料存仓单<i>&gt;</i></a>
                        <ul>
                            <li><a href="?act=add_material_in">新增</a></li>
                        </ul>
                    </li>
                    <li><a href="?act=search_material_out&page=1">物料领取单<i>&gt;</i></a>
                        <ul>
                            <li><a href="?act=add_material_out">新增</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li><a href="?act=search_wlgy_customer&page=1">物料供应商<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=add_wlgy_customer">新增</a></li>
                    <li><a href="?act=search_wlgy_contact&page=1">物料供应商联系人</a></li>
                </ul>
            </li>
        </ul>
    </li>

    <li><a href="#">生产<i>&gt;</i></a>
        <ul>
            <li><a href="?act=searchpurchase&page=1">查看订单</a></li>
            <li><a href="?act=search_sub_contractor_order&page=1">查看加工单</a></li>
            <li><a href="?act=search_jg_customer&page=1">加工商<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=add_jg_customer">新增</a></li>
                    <li><a href="?act=search_jg_contact&page=1">加工商联系人</a></li>
                </ul>
            </li>
            <li><a href="?act=searchdelivery&page=1">出货单<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=adddelivery">新增</a></li>
                </ul>
            </li>
            <li><a href="?act=search_factory_complaint_form&page=1">投诉报告</a></li>
        </ul>
    </li>

    <li><a href="#">质检<i>&gt;</i></a>
        <ul>
            <li><a href="?act=qc_schedule">QC 计划表</a></li>
            <li><a href="?act=search_qc_report&page=1">QC 报告</a></li>
        </ul>
    </li>

    <li><a href="#">财务<i>&gt;</i></a>
        <ul>
            <li><a href="?act=search_payment_request&page=1">付款申请单<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=add_payment_request">新增</a></li>
                </ul>
            </li>
        </ul>
    </li>

    <li><a href="#">行政<i>&gt;</i></a>
        <ul>
            <li><a href="?act=search_it_request&page=1">IT服务申请<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=add_it_request">新增</a></li>
                </ul>
            </li>
        </ul>
    </li>

    <!--<li><a href="#">成品<i>&gt;</i></a>
        <ul>

        </ul>
    </li>-->

    <li><a href="#">报表<i>&gt;</i></a>
        <ul>
            <li><a href="?act=delivery_analysis">每月出货统计</a></li>
            <li><a href="?act=sub_contractor_order_analysis">每月外发统计</a></li>
            <li><a href="?act=fty_jg_customer_ap">加工商应付表</a></li>
            <li><a href="?act=fty_wlgy_customer_ap">物料商应付表</a></li>
        </ul>
    </li>

    <li><a href="#">管理<i>&gt;</i></a>
        <ul>
            <li><a href="?act=searchclient&page=1">客户<i>&gt;</i></a>
                <ul>
            		<li><a href="?act=manageclient">新增</a></li>
        		</ul>
            </li>
            <li><a href="javascript:void(0);">BOM 设定<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=bom_setting">参数设置</a></li>
                    <li><a href="?act=bom_lb">类别</a></li>
                    <li><a href="?act=bom_dcyl">底材用料</a></li>
                    <li><a href="?act=bom_bmcl">表面处理</a></li>
                    <li><a href="?act=bom_dd">电镀</a></li>
                    <li><a href="?act=bom_ddhd">电镀厚度</a></li>
                    <li><a href="?act=bom_qt">其他</a></li>
                </ul>
            </li>
        </ul>
    </li>

    <li><a href="?act=logout" class="otherli">登出</a></li>
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
	if($model = modelExist2($act, true)){
		require($model);
	}else{
		$myerror->error('阁下发起了未知操作，请返回首页', 'INDEX');
		$act = 'inside_error';
		require(modelExist2($act, true));
	}
	?>
    <!-- content end //-->
</div>
<BR><HR>
<div align='center'>	
	copyright &copy 2011-<?=date("Y");?> LUX DESIGN LTD ALL RIGHTS RESERVED
</div>

</body>
</html>