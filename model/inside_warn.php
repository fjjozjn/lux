<div class="boxshadow">
<div id="warnbox" class="msgbox">
<h1><?=(strpos($_SERVER['SCRIPT_NAME'], '/sys/') !== false)?'Hints':'提示'?></h1>
<div class="boxicon"></div>
<div class="boxtext">
<?php
    $myerror->getMsg();
?>
</div>
<div class="clearfix"></div>
</div>
</div>