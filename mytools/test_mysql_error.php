<?php
require(substr(__DIR__, 0, -7).'in7/global.php');

file_put_contents(substr(__DIR__, 0, -7).'log/fty_pcid_empty_schedule.txt', dateMore().' : start ', FILE_APPEND);

$rs = $mysql->q('select pcid from purchase where sid = ? and istatus = ? order by pcid desc', 'S008', '(I)');

if($rs){

    file_put_contents(substr(__DIR__, 0, -7).'log/fty_pcid_empty_schedule.txt', "\r\n", FILE_APPEND);

}else{

    file_put_contents(substr(__DIR__, 0, -7).'log/fty_pcid_empty_schedule.txt', ' none'."\r\n", FILE_APPEND);

}