<?php
if(isset($_GET['sign'])){
    //modify
    if(isset($_GET['index']) && $_GET['index'] != ''){
        if(isset($_GET['pid']) && $_GET['pid'] != ''){
            if(isset($_GET['price']) && $_GET['price'] != ''){
                $_SESSION['sales_item_m'][$_GET['index']]['price'] = $_GET['price'];
                if(isset($_SESSION['sales_item_m'][$_GET['index']]['price']) && $_SESSION['sales_item_m'][$_GET['index']]['price'] != ''){
                    echo 'yes';
                }else{
                    echo '!no-03';
                }
            }else{
                echo '!no-02';
            }
        }else{
            echo '!no-01';
        }
    }else{
        echo '!no-00';
    }
}else{
    //add
    if(isset($_GET['index']) && $_GET['index'] != ''){
        if(isset($_GET['pid']) && $_GET['pid'] != ''){
            if(isset($_GET['price']) && $_GET['price'] != ''){
                $_SESSION['sales_item'][$_GET['index']]['price'] = $_GET['price'];
                if(isset($_SESSION['sales_item'][$_GET['index']]['price']) && $_SESSION['sales_item'][$_GET['index']]['price'] != ''){
                    echo 'yes';
                }else{
                    echo '!no-3';
                }
            }else{
                echo '!no-2';
            }
        }else{
            echo '!no-1';
        }
    }else{
        echo '!no-0';
    }
}