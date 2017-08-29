<?php
//20130807 帮她们批量更新po的item信息到最新的product信息

require('../in7/global.php');

//格式化货币
function fmoney($num) {
    $num=0+$num;
    $num = sprintf("%.02f",$num);
    if(strlen($num) <= 6) return $num;
    //从最后开始算起，每3个数它加一个","
    for($i=strlen($num)-1,$k=1, $j=100; $i >= 0; $i--,$k++) {
        $one_num = substr($num,$i,1);
        if($one_num ==".") {
            $numArray[$j--] = $one_num;
            $k=0;
            continue;
        }

        if($k%3==0 and $i!=0) {
            //如果正好只剩下3个数字，则不加','
            $numArray[$j--] = $one_num;
            $numArray[$j--] = ",";
            $k=0;
        } else {
            $numArray[$j--]=$one_num;
        }
    }
    ksort($numArray);
    return join("",$numArray);
}

//格式化显示钱：保留两位小数，不足的补0，整数部分每三位逗号隔开
function formatMoney($money){
    return fmoney(sprintf("%01.2f", round(floatval($money), 2)));
}

if(isset($_GET['value']) && $_GET['value'] != ''){
    $rs = $mysql->q('select pid from purchase_item where pcid = ?', $_GET['value']);
    if($rs){
        $rtn = $mysql->fetch();
        foreach($rtn as $v){
            $product_rtn = $mysql->qone('select * from product where pid = ?', $v['pid']);

            //把product更新的信息保存进item表里
            $mysql->q('update purchase_item set price = ?, description_chi = ?, photos = ?, ccode = ?, scode = ? where pcid = ? and pid = ?', formatMoney($product_rtn['cost_rmb']), $product_rtn['description_chi'], $product_rtn['photos'], $product_rtn['ccode'], $product_rtn['scode'], $_GET['value'], $v['pid']);
        }

        //返回modify页面，以显示更新结果
        echo "<script>";
        echo "alert('Update Complete !');";
        echo "history.go(-1)";
        echo "</script>";
    }else{
        die('No item info !');
    }
}else{
    die('Need value !');
}