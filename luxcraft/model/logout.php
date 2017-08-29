<?
//add logout log

//20131217 加session过期，记录日志内容的判断
$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
		, $_SESSION['luxcraftlogininfo']['aID'], $ip_real
		, ADMIN_CATG_LUXCRAFT_LOGOUT, ((isset($_SESSION["luxcraftlogininfo"]["aName"]) && $_SESSION["luxcraftlogininfo"]["aName"] != '')?($_SESSION["luxcraftlogininfo"]["aName"]." logout luxcraft system"):'luxcraft user session timeout'), ADMIN_ACTION_LUXCRAFT_LOGOUT_SUCCESS, "", "", 0);

unset($_SESSION['luxcraftlogininfo']);
//20150409
unset($_SESSION['search_criteria']);

//20121024 sys退出了，fty也退出了，所以去掉了这个，希望没其他的问题出现
//session_destroy();
$myerror->ok('Logout!', 'INDEX');
?>
<div class="boxshadow">
<div class="msgbox">
<h1>Hints</h1>
<div class="boxicon"></div>
<div class="boxtext">
<?php
    $myerror->getMsg('ok');
?>
</div>
<div class="clearfix"></div>
</div>
</div>