<?
if( isset($_GET['value']) && $_GET['value'] != ''){
	$setting_rtn = $mysql->qone('select * from setting');
	$rtn = $mysql->qone('select description_chi, cost_rmb, photos, scode, ccode, exclusive_to from product where pid = ?', $_GET['value']);
	if($rtn){
		if (is_file($pic_path_com . $rtn['photos']) == true) { 
			$arr = getimagesize($pic_path_com . $rtn['photos']);
			$pic_width = $arr[0];
			$pic_height = $arr[1];
			$image_size = getimgsize(80, 60, $pic_width, $pic_height);
			$photo_string = '<a href="/sys/'.$pic_path_com . $rtn['photos'].'" target="_blank" title="'.$rtn['photos'].'"><img src="/sys/'.$pic_path_com . $rtn['photos'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
		}else{ 
			$photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60'>";
		}

        //20130625 如果是 retail sales memo ，则需要查warehouse
        if(isset($_GET['wh_id']) && $_GET['wh_id'] != ''){
            $wh_id_array = explode('|', $_GET['wh_id']);
            $rsm_rtn = $mysql->qone('select qty from warehouse_item_unique where wh_id = ? and pid = ?', $wh_id_array[0], $_GET['value']);
/*            $price = '';
            if(isset($_GET['warehouse'])){
                $price = formatMoney($rtn['cost_rmb']);
            }elseif(isset($_GET['retail'])){
                $price = formatMoney(currencyTo($rtn['cost_rmb'], 'RMB', 'HKD'));
            }*/
            //price round為浮點數四捨五入，sprintf為保留兩位小數，不足的補0
            //20130720 不返回价格
            echo /*$price.*/'0|'.$rtn['description_chi'].'|'.$photo_string.'|'.$rtn['photos'].'|'.$rtn['ccode'].'|'.$rtn['scode'].'|'.(isset($rsm_rtn['qty'])?$rsm_rtn['qty']:0);
        }else{
            if(isset($_GET['warehouse'])){
                //20130720 加warehouse情况，且不返回价格
                echo '0|'.$rtn['description_chi'].'|'.$photo_string.'|'.$rtn['photos'].'|'.$rtn['ccode'].'|'
                    .$rtn['scode'];
            }else{
                //20131020 加返回delivery出货数量
                $rtn_delivery['total_quantity'] = '';
                if( isset($_GET['pcid']) && $_GET['pcid'] != '' ){
                    $rtn_delivery = $mysql->qone('select sum(quantity) as total_quantity from delivery_item where po_id = ?
                    and p_id = ?', $_GET['pcid'], $_GET['value']);
                    if(!isset($rtn_delivery['total_quantity']) || $rtn_delivery['total_quantity'] == ''){
                        $rtn_delivery['total_quantity'] = 0;
                    }
                }

                //price round為浮點數四捨五入，sprintf為保留兩位小數，不足的補0
                echo formatMoney($rtn['cost_rmb']).'|'.$rtn['description_chi'].'|'.$photo_string.'|'.$rtn['photos'].'|'.$rtn['ccode'].'|'.$rtn['scode'].'|'.$rtn_delivery['total_quantity'];
            }
        }
	}else{
		echo '!no-0';	
	}
}else{
	echo '!no-1';	
}