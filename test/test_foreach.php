<?php

//测试
//echo md5('charset=UTF-8&data=<mchPayConft><merchantId>100560000037</merchantId><payTypeId>221</payTypeId><billRate>6</billRate></mchPayConf>&dataType=xml&partner=100590006610&serviceName=normal_mch_pay_conf_add35aa6c203b7b4218713153f6d8dc39ec');

//正式
echo md5('charset=UTF-8&data=<mchPayConft><merchantId>101550291020</merchantId><payTypeId>325</payTypeId><billRate>6</billRate></mchPayConf>&dataType=xml&partner=101540282222&serviceName=normal_mch_pay_conf_add95e751ad03847d523282f18d33f9de8c');

//echo md5('charset=UTF-8&data=<picUpload><picType>1</picType></picUpload>&dataType=xml&partner=100590006610&serviceName=pic_upload35aa6c203b7b4218713153f6d8dc39ec');

//$a = array(1, 2, 3);
//$b = array(4, 5, 6);
//foreach($a as $v){
//    echo $v.'<br />';
//    foreach($b as $w){
//        if($w == 5){
//            break;
//        }
//        echo $w.'<br />';
//    }
//}