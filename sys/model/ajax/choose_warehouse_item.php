<?
if( isset($_GET['value']) && $_GET['value'] != '' && isset($_GET['wh']) && $_GET['wh'] != ''){
    if(strpos($_GET['wh'], '|') !== false){
        $wh = explode('|', $_GET['wh']);
        $wh_id = $wh[0];
    }else{
        $wh_id = $_GET['wh'];
    }

    $rtn = $mysql->qone('select qty, photo from warehouse_item_unique where pid = ? and wh_id = ?', $_GET['value'],
        $wh_id);
    if($rtn){
        if (is_file($pic_path_com . $rtn['photo']) == true) {
            $arr = getimagesize($pic_path_com . $rtn['photo']);
            $pic_width = $arr[0];
            $pic_height = $arr[1];
            $image_size = getimgsize(80, 60, $pic_width, $pic_height);
            $photo_string = '<a href="/sys/'.$pic_path_com . $rtn['photo'].'" target="_blank" title="'.$rtn['photo'].'"><img src="/sys/'.$pic_path_com . $rtn['photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
        }else{
            $photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60'>";
        }

        echo $photo_string.'|'.$rtn['qty'];
    }else{
        echo '!no-0';
    }
}else{
    echo '!no-1';
}