<?
//add logout log

//20131217 加session过期，记录日志内容的判断
$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
		, $_SESSION['ftylogininfo']['aID'], $ip_real
		, ADMIN_CATG_FTY_LOGOUT, ((isset($_SESSION["ftylogininfo"]["aName"]) && $_SESSION["ftylogininfo"]["aName"] != '')?($_SESSION["ftylogininfo"]["aName"]." logout fty system"):'fty user session timeout'), ADMIN_ACTION_FTY_LOGOUT_SUCCESS, "", "", 0);
		
unset($_SESSION['ftylogininfo']);
//20150409
unset($_SESSION['search_criteria']);

//20121024 fty退出，sys也退出了，所以去掉这个，希望不会有其他的问题
//session_destroy();
$myerror->ok('您已经登出!', 'INDEX');
?>
<div class="boxshadow">
<div class="msgbox">
<h1>提示</h1>
<div class="boxicon"></div>
<div class="boxtext">
<?php
    $myerror->getMsg('ok');
?>
</div>
<div class="clearfix"></div>
</div>
</div>