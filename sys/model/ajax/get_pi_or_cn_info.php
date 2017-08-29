<?php
if( isset($_GET['value']) && $_GET['value'] != ''){
    //非管理员只能查看自己group的 I 或 S 的单
    $total = '';
    $outstanding = '';
    if(strpos($_GET['value'], 'PI') !== false){
        $rtn = $mysql->qone('select sum(price*quantity) as total_pi from proforma_item where pvid = ?',
            $_GET['value']);
        if(isset($rtn['total_pi']) && $rtn['total_pi'] != ''){
            $total += $rtn['total_pi'];
        }

        $total_payment = 0;
        //20130825 payment_new 和 payment 重复了，不能减两次
/*        $rtn = $mysql->qone('select sum(amount) as total_amount from payment where pi_no = ?', $_GET['value']);
        if(isset($rtn['total_amount']) && $rtn['total_amount'] != ''){
            $total_payment += $rtn['total_amount'];
        }*/
        //20130824 payment_item_new 里的的 received 也一起减去
        $rtn = $mysql->qone('select sum(received) as total_received from payment_item_new where pi_or_cn = ? and pi_or_cn_no = ?', 'PI', $_GET['value']);
        if(isset($rtn['total_received']) && $rtn['total_received'] != ''){
            $total_payment += $rtn['total_received'];
        }

        $outstanding = $total - $total_payment;

        echo my_formatMoney($total).'|'.my_formatMoney($outstanding);

    }elseif(strpos($_GET['value'], 'CN') !== false){
        $rtn = $mysql->qone('select sum(amount) as total_amount from credit_note_item where cn_no = ?', $_GET['value']);
        if(isset($rtn['total_amount']) && $rtn['total_amount'] != ''){
            $total += $rtn['total_amount'];
        }

        $total_payment = 0;
        $rtn = $mysql->qone('select sum(received) as total_received from payment_item_new where pi_or_no = ? and pi_or_cn_no = ?', 'CN', $_GET['value']);
        if(isset($rtn['total_received']) && $rtn['total_received'] != ''){
            $total_payment += $rtn['total_received'];
        }
        $outstanding = $total - $total_payment;

        //20131030 outstanding 显示修改为 剩下多少
        //echo my_formatMoney($total).'|0.00';
        echo my_formatMoney($total).'|'.my_formatMoney($outstanding);
    }
}else{
    echo '!no-0';
}