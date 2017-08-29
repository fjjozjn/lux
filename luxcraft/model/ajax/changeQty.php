<?php
if(isset($_GET['sign'])){
    //modify
    if(isset($_GET['index']) && $_GET['index'] != ''){
        if(isset($_GET['pid']) && $_GET['pid'] != ''){
            if(isset($_GET['qty']) && $_GET['qty'] != ''){

                //因为现在没法确认查的是那个warehouse的qty，所以这里不能查
                //$rtn = $mysql->qone('select qty from warehouse_item_unique where pid = ?', $_GET['pid']);
                //if($_GET['qty'] <= $rtn['qty']){
                $_SESSION['sales_item_m'][$_GET['index']]['qty'] = $_GET['qty'];
                if(isset($_SESSION['sales_item_m'][$_GET['index']]['qty']) && $_SESSION['sales_item_m'][$_GET['index']]['qty'] != ''){
                    echo 'yes';
                }else{
                    echo '!no-04';
                }
                /*}else{
                    echo '!no-3';
                }*/
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
            if(isset($_GET['qty']) && $_GET['qty'] != ''){

                //因为现在没法确认查的是那个warehouse的qty，所以这里不能查
                //$rtn = $mysql->qone('select qty from warehouse_item_unique where pid = ?', $_GET['pid']);
                //if($_GET['qty'] <= $rtn['qty']){
                $_SESSION['sales_item'][$_GET['index']]['qty'] = $_GET['qty'];
                if(isset($_SESSION['sales_item'][$_GET['index']]['qty']) && $_SESSION['sales_item'][$_GET['index']]['qty'] != ''){
                    echo 'yes';
                }else{
                    echo '!no-4';
                }
                /*}else{
                    echo '!no-3';
                }*/
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