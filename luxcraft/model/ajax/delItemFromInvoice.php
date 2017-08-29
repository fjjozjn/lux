<?php
if(isset($_GET['sign'])){
    // modify
    if(isset($_GET['index']) && $_GET['index'] != ''){

        if($_SESSION['sales_item_m'][$_GET['index']]['qty'] - 1 <= 0){
            unset($_SESSION['sales_item_m'][$_GET['index']]);
        }else{
            $_SESSION['sales_item_m'][$_GET['index']]['qty']--;
        }

        if(!isset($_SESSION['sales_item_m'][$_GET['index']])){
            echo 'clear';
        }else{
            echo '!no-01';
        }
    }else{
        echo '!no-00';
    }
}else{
    // add
    if(isset($_GET['index']) && $_GET['index'] != ''){

        if($_SESSION['sales_item'][$_GET['index']]['qty'] - 1 <= 0){
            unset($_SESSION['sales_item'][$_GET['index']]);
        }else{
            $_SESSION['sales_item'][$_GET['index']]['qty']--;
        }

        if(!isset($_SESSION['sales_item'][$_GET['index']])){
            echo 'clear';
        }else{
            echo '!no-1';
        }
    }else{
        echo '!no-0';
    }
}