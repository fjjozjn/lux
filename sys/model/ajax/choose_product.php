<?
if( isset($_GET['value']) && $_GET['value'] != ''){
	$setting_rtn = $mysql->qone('select * from setting');
	$rtn = $mysql->qone('select description, cost_rmb, photos, scode, ccode, exclusive_to from product where pid = ?', $_GET['value']);
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
		if( $rtn['exclusive_to'] != ''){
			$markup_rtn = $mysql->qone('select markup_ratio from customer where cid = ?', $rtn['exclusive_to']);	
		}
		
		$price = $rtn['cost_rmb'] * $setting_rtn['currency'] * ((isset($markup_rtn['markup_ratio']) && $markup_rtn['markup_ratio'] != '')?$markup_rtn['markup_ratio']:$setting_rtn['markup']);
		//price round為浮點數四捨五入，sprintf為保留兩位小數，不足的補0
		echo formatMoney($price).'|'.$rtn['description'].'|'.$photo_string.'|'.$rtn['photos'].'|'.$rtn['ccode'].'|'.$rtn['scode'];	

			
	}else{
		echo '!no-0';	
	}
}else{
	echo '!no-1';	
}