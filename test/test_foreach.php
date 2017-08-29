<?php

$a = array(1, 2, 3);
$b = array(4, 5, 6);
foreach($a as $v){
    echo $v.'<br />';
    foreach($b as $w){
        if($w == 5){
            break;
        }
        echo $w.'<br />';
    }
}