<?php

//将po以ETD添加进qc_schedule表，如果qc_schedule已经存在的po，暂时不改动

require(substr(__DIR__, 0, -7).'in7/global.php');

$rs = $mysql->q('select p.expected_date, p.pcid, t.AdminName FROM purchase p LEFT JOIN tw_admin t ON p.created_by = t.AdminNameChi WHERE p.pcid NOT IN (SELECT pcid FROM qc_schedule)');
if($rs){
    $rtn = $mysql->fetch();

    $i = 1;
    foreach($rtn as $v){
        $mysql->q('insert into qc_schedule values (NULL, '.moreQm(6).')', $v['expected_date'], $v['pcid'], date('Y-m-d H:i:s'), '', $v['AdminName'], '');
        $i++;
    }
    echo $i;
}