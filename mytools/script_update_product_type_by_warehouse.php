<?php

//20130721 将warehouse里存在的product item在product表里的type字段自动填上

require('../in7/global.php');

//从id第几开始，省去处理过的重复处理
//执行日志：201307220004 1-265
$id = 1;

function get_type_20130721($pid){
    if(strlen($pid) > 0){
        $first_word = substr($pid, 0, 1);
        if($first_word == 'E'){
            return 'Earrings';
        }elseif($first_word == 'T'){
            return 'Bracelet';
        }elseif($first_word == 'N'){
            return 'Necklace';
        }elseif($first_word == 'R'){
            return 'Ring';
        }else{
            return '';
        }
    }else{
        return '';
    }
}

$rs = $mysql->q('select distinct pid from warehouse_item_unique where id >= ? order by id', $id);
if($rs){
    $rtn = $mysql->fetch();
    foreach($rtn as $v){
        $type = get_type_20130721($v['pid']);
        if($type != ''){
            $rs = $mysql->q('update product set type = ? where pid = ?', $type, $v['pid']);
            if($rs !== false){
                echo $v['pid'].' type '.$type."<br />";
            }else{
                echo $v['pid']."<br />";
            }
        }
    }
}else{
    die('没有找到item');
}

//201307220004
/*N100520E-PRL type Necklace
N100664 type Necklace
T530013 type Bracelet
T100397-RGLD type Bracelet
T530001-BLU type Bracelet
E100449 type Earrings
E100362-CRY type Earrings
E100362-AQU type Earrings
E100362-BLU type Earrings
E100362-BDI type Earrings
E100362-LPU type Earrings
E100362-LRO type Earrings
E100362-ROS type Earrings
E100380-PUR type Earrings
E100380-LPU type Earrings
E100380-DIDG type Earrings
E100380-AQU type Earrings
E100380-CRY type Earrings
E100612 type Earrings
E560023 type Earrings
E100369 type Earrings
E100404-AQM type Earrings
E100404-CRY type Earrings
E100404-PNK type Earrings
E100404-PUR type Earrings
E100404-DIDG type Earrings
E100404-BDI type Earrings
E100404-PLU type Earrings
E560205-AQU type Earrings
E100368 type Earrings
E520203-BLK type Earrings
N100367-MLC type Necklace
N100367-LPS type Necklace
N100367-TEY type Necklace
N100363 type Necklace
N800065 type Necklace
N800016 type Necklace
N800034 type Necklace
N312018 type Necklace
N801096-PUR type Necklace
N100378-BID type Necklace
N550201 type Necklace
N800043 type Necklace
N530116 type Necklace
N800011 type Necklace
N008751 type Necklace
N100316-RHO type Necklace
N100316-RG type Necklace
N100317-RHO type Necklace
N100315-RHO type Necklace
N100315-RG type Necklace
N800031 type Necklace
N800249 type Necklace
N530001 type Necklace
N530031-PUR type Necklace
N560027 type Necklace
N560028 type Necklace
N530003 type Necklace
N530056 type Necklace
T100375-RHO type Bracelet
T100375-RS type Bracelet
T100379-WHT type Bracelet
T560001-BLU type Bracelet
T560001-GRN type Bracelet
T560001-PUR type Bracelet
T560002 type Bracelet
R100371-RSO type Ring
R100371-RHO type Ring
R100380 type Ring*/
