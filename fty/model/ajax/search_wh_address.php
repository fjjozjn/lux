<?

if( isset($_GET['value']) && $_GET['value'] != ''){
    $rtn = $mysql->qone('select address from warehouse where wh_name = ?', $_GET['value']);
    if($rtn){
        echo $rtn['address'];
    }else{
        echo 'no-2';
    }
}else{
    echo 'no-1';
}