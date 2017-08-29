<?php

$file_csv = substr(dirname(__FILE__), 0, -5).'\file\20150713.csv';
$file = fopen($file_csv, "r");
while(! feof($file)){
    $array[] = fgetcsv($file);//用fgetcsv这个函数来分隔csv中字段的数据，比用逗号分隔好，因为字段中有可能出现逗号，如"5,000.00"
}

/*echo '<pre>';
print_r($array);
echo '</pre>';*/

$i = 1;
foreach($array as $v){
    $cid = 'C'.sprintf("%04d", $i);
    $remark = $v['1'].'|'.$v['2'];
    echo "insert into fty_jg_customer set cid = '".$cid."', name = '".$v[0]."', remark = '".$remark."';<br />";
    echo "insert into fty_jg_contact set name = '".$v['4']."', address = '".$v[3]."', tel1 = '".$v['5']."', cid = '".$cid."';<br />";
    $i++;
}