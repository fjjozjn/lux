<?
if( isset($_GET['value']) && $_GET['value'] != ''){
	$setting_rtn = $mysql->qone('select markup from setting');
	$rtn = $mysql->qone('select description, cost_rmb, photos, scode, ccode, exclusive_to, suggested_price from product where pid = ?', $_GET['value']);
	if($rtn){
		if (is_file($pic_path_com . $rtn['photos']) == true) { 			
			$arr = getimagesize($pic_path_com . $rtn['photos']);
			$pic_width = $arr[0];
			$pic_height = $arr[1];
			
			//圖片大小超過100KB則進行壓縮
			//$rtn['photos']是原來的， $small_photo是縮小後的
			//$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
			$small_photo = 's_' . $rtn['photos'];
			if(filesize($pic_path_com . $rtn['photos']) > 100){
				//縮小的圖片不存在才進行縮小操作
				if (!is_file($pic_path_small . $small_photo) == true) { 	
					makethumb($pic_path_com . $rtn['photos'], $pic_path_small . $small_photo, 's');
				}
			}
			
			$image_size = getimgsize(80, 60, $pic_width, $pic_height);
			//連接到的是原始圖片，在頁面顯示的是縮小後的圖片
			$photo_string = '<a href="/sys/'.$pic_path_com . $rtn['photos'].'" target="_blank" title="'.$rtn['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
		}else{ 
			$photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60'>";
		}
		
		//如果有exclusive_to，則使用customer的markup值
		//if( $rtn['exclusive_to'] != ''){
		//20121023 当选择了customer ，有了session，才能使用 customer 的markup_ratio ，所以quotation就不能用了
		if( isset($_GET['customer']) && $_GET['customer'] != ''){
			$markup_rtn = $mysql->qone('select markup_ratio from customer where cid = ?', $_GET['customer']);
		}
		
		if( isset($_GET['currency']) && $_GET['currency'] != ''){
			$currency_rtn = $mysql->qone('select rate from currency where type = ?', $_GET['currency']);
            //20130923 加suggested_price的判断
            if($rtn['suggested_price'] && $rtn['suggested_price'] != 0){
                $price = $rtn['suggested_price'];
            }else{
			    $price = $rtn['cost_rmb'] * /*$setting_rtn['currency'] 20120423 弃用setting里的currency */ $currency_rtn['rate'] * ((isset($markup_rtn['markup_ratio']) && $markup_rtn['markup_ratio'] != '')?$markup_rtn['markup_ratio']:$setting_rtn['markup']);
            }

            //20131021 加返回 packing list num
            $rtn_packinglist['total_qty'] = '';
            if(isset($_GET['pvid']) && $_GET['pvid'] != ''){
                $rtn_packinglist = $mysql->qone('select sum(qty) as total_qty from packing_list_item where ref = ? and item = ?', $_GET['pvid'], $_GET['value']);
                if(!isset($rtn_packinglist['total_qty']) || $rtn_packinglist['total_qty'] == ''){
                    $rtn_packinglist['total_qty'] = 0;
                }
            }

			//price round為浮點數四捨五入，sprintf為保留兩位小數，不足的補0
			echo formatMoney($price).'|'.$rtn['description'].'|'.$photo_string.'|'.$rtn['photos'].'|'.$rtn['ccode'].'|'.$rtn['scode'].'|'.$rtn_packinglist['total_qty'];
		}else{
			echo '!no-2';	
		}
	}else{
		echo '!no-0';	
	}
}else{
	echo '!no-1';	
}