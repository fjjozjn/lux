<?php
$result = mysql_qone('select sales_vid from sales_invoice order by sales_vid desc');
if($result['sales_vid']){
    $_SESSION['luxcraftlogininfo']['open_pdf'] = 1;
    echo substr($result['sales_vid'], 0, 3).sprintf("%07d", substr($result['sales_vid'], 3)+1);
}elseif($result === false){
    echo '!no-0';
}else{
    $_SESSION['luxcraftlogininfo']['open_pdf'] = 1;
    echo 'INV0000001';
}