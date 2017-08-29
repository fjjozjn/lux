<?php
if(isset($_GET['sign'])){
    //modify
    if(isset($_GET['index']) && $_GET['index'] != ''){
        //index也要保存，不然刷新页面的时候，ID信息就没了
        $_SESSION['sales_item_m'][$_GET['index']]['index'] = $_GET['index'];

        if(isset($_GET['img']) && $_GET['img'] != ''){
            $_SESSION['sales_item_m'][$_GET['index']]['img'] = $_GET['img'];

            if(isset($_GET['pid']) && $_GET['pid'] != ''){
                $_SESSION['sales_item_m'][$_GET['index']]['pid'] = $_GET['pid'];

                if(isset($_GET['price']) && $_GET['price'] != ''){
                    $_SESSION['sales_item_m'][$_GET['index']]['price'] = $_GET['price'];

                    $_SESSION['sales_item_m'][$_GET['index']]['qty'] = 1;

                    if(isset($_SESSION['sales_item_m'][$_GET['index']]['img']) && $_SESSION['sales_item_m'][$_GET['index']]['img'] != '' && isset($_SESSION['sales_item_m'][$_GET['index']]['pid']) && $_SESSION['sales_item_m'][$_GET['index']]['pid'] != '' && isset($_SESSION['sales_item_m'][$_GET['index']]['price']) && $_SESSION['sales_item_m'][$_GET['index']]['price'] != ''){
                        echo 'yes';
                    }else{
                        echo '!no-04';
                    }
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
        //index也要保存，不然刷新页面的时候，ID信息就没了
        $_SESSION['sales_item'][$_GET['index']]['index'] = $_GET['index'];

        if(isset($_GET['img']) && $_GET['img'] != ''){
            $_SESSION['sales_item'][$_GET['index']]['img'] = $_GET['img'];

            if(isset($_GET['pid']) && $_GET['pid'] != ''){
                $_SESSION['sales_item'][$_GET['index']]['pid'] = $_GET['pid'];

                if(isset($_GET['price']) && $_GET['price'] != ''){
                    $_SESSION['sales_item'][$_GET['index']]['price'] = $_GET['price'];

                    $_SESSION['sales_item'][$_GET['index']]['qty'] = 1;

                    if(isset($_SESSION['sales_item'][$_GET['index']]['img']) && $_SESSION['sales_item'][$_GET['index']]['img'] != '' && isset($_SESSION['sales_item'][$_GET['index']]['pid']) && $_SESSION['sales_item'][$_GET['index']]['pid'] != '' && isset($_SESSION['sales_item'][$_GET['index']]['price']) && $_SESSION['sales_item'][$_GET['index']]['price'] != ''){
                        echo 'yes';
                    }else{
                        echo '!no-4';
                    }
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