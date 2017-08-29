<?php
//201306170956
//根据purchase表中的reference(PI)，查询出PI的customer，更新到purchase表的customer字段。reference不为空的才更新，且覆盖purhcase表customer原来的值。

require('../in7/global.php');

$rs_purchase = $mysql->q('select pcid, reference from purchase where reference <> ?', '');
if($rs_purchase){
    $rtn_purchase = $mysql->fetch();
    foreach($rtn_purchase as $v){
        $mysql->q('update purchase set customer = (select cid from proforma where pvid = ?) where pcid = ?', $v['reference'], $v['pcid']);
    }
}