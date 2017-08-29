<?

if( isset($_GET['search_text']) && $_GET['search_text'] != '' && isset($_GET['wh']) && $_GET['wh'] != ''){
    if(strpos($_GET['wh'], '|') !== false){
        $wh = explode('|', $_GET['wh']);
        $wh_id = $wh[0];
    }else{
        $wh_id = $_GET['wh'];
    }

    $key = $_GET['search_text'];
    $rtn = $mysql->q("select pid from warehouse_item_unique where pid like ? and wh_id = ? order by in_date desc limit 20", "%$key%", $wh_id);
    if($rtn){
        $result = $mysql->fetch();
        $pid_rtn = "[";
        for($i = 0; $i < count($result); $i++){
            if($i != count($result) - 1){
                $pid_rtn .= "\"" . $result[$i]['pid'] . "\"" . ",";
            }else{
                $pid_rtn .= "\"" . $result[$i]['pid'] . "\"";
            }
        }
        $pid_rtn .= "]";
        echo $pid_rtn;
    }else{
        echo 'no-1';
    }
}else{
    echo 'no-2';
}