<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>图片提示</title>
 <script src="/ajaxjs/jquery1.3.2.js" type="text/javascript"></script>
<style type="text/css">
body{
	margin:0;
	padding:40px;
	background:#fff;
	font:80% Arial, Helvetica, sans-serif;
	color:#555;
	line-height:180%;
}
img{border:none;}
ul,li{
	margin:0;
	padding:0;
}
li{
	list-style:none;
	float:left;
	display:inline;
	margin-right:10px;
	border:1px solid #AAAAAA;
}

/* tooltip */
#tooltip{
	position:absolute;
	border:1px solid #ccc;
	background:#333;
	padding:2px;
	display:none;
	color:#fff;
}
</style>
<script type="text/javascript">
//<![CDATA[
$(function(){
	var x = 10;
	var y = 20;
	$("a.tooltip").mouseover(function(e){
		this.myTitle = this.title;
		this.title = "";	
		var imgTitle = this.myTitle? "<br/>" + this.myTitle : "";
		var tooltip = "<div id='tooltip'><img src='"+ this.href +"' alt='产品预览图'/>"+imgTitle+"<\/div>"; //创建 div 元素
		$("body").append(tooltip);	//把它追加到文档中						 
		$("#tooltip")
			.css({
				"top": (e.pageY+y) + "px",
				"left":  (e.pageX+x)  + "px"
			}).show("fast");	  //设置x坐标和y坐标，并且显示
    }).mouseout(function(){
		this.title = this.myTitle;	
		$("#tooltip").remove();	 //移除 
    }).mousemove(function(e){
		$("#tooltip")
			.css({
				"top": (e.pageY+y) + "px",
				"left":  (e.pageX+x)  + "px"
			});
	});
})
//]]>
</script>

</head>
<body>
<h3>有效果：</h3>
	<ul>
<li><a href="/jscss/demoimg/wall1.jpg" class="tooltip" title="风光 iPod"><img src="/jscss/demoimg/wall_s1.jpg" alt="风光 iPod" /></a></li>
	</ul>


<br/><br/><br/><br/>
<br/><br/><br/><br/>


<h3>无效果：</h3>
<ul>
<li><a href="/jscss/demoimg/wall1.jpg" title="风光 iPod"><img src="/jscss/demoimg/wall_s1.jpg" alt="风光 iPod" /></a></li>
	</ul>
</body>
</html>