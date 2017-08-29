<?php
if(isset($_GET['sign'])){
    //modify
    $_SESSION['sales_item_m'][$_GET['index']]['qty']++;
}else{
    //add
    $_SESSION['sales_item'][$_GET['index']]['qty']++;
}