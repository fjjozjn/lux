<?
function redirectTo($link){
    echo '<Script Language ="JavaScript">';
    echo "window.location.href = '".$link."';";
    echo "</Script>";
}
function checkAdminLogin(){	
	if (!isset($_SESSION['luxcraftlogininfo'])){
		//not login yet
		redirectTo('?act=login');
	}
}

//判断是否是luxcraft的admin
function isLuxcraftAdmin(){
	if(isset($_SESSION['luxcraftlogininfo']['aName'])){
		$rs = mysql_q('select AdminName from tw_admin where AdminLuxGroup = ?', 'admin');
		if($rs){
			$rtn = mysql_fetch();
			foreach($rtn as $v){
				if($_SESSION['luxcraftlogininfo']['aName'] == $v['AdminName']){
					return true;	
				}
			}
		}else{
			return false;	
		}
	}else{
		return false;	
	}
	return false;
}

//按比例縮小圖片
function getimgsize($oldwidth,$oldheight,$imgwidth,$imgheight)
{
    //$oldwidth設置的寬度，$oldheight設置的高度，$imgwidth圖片的寬度，$imgheight圖片的高度

    //單元格裝得進圖片，則按圖片的真實大小顯示
    if($imgwidth <= $oldwidth && $imgheight <= $oldheight)
    {
        $arraysize = array('width' => $imgwidth, 'height' => $imgheight);
        return $arraysize;
    }
    else
    {
        $suoxiaowidth = $imgwidth - $oldwidth;
        $suoxiaoheight = $imgheight - $oldheight;
        $suoxiaoheightper = $suoxiaoheight / $imgheight;
        $suoxiaowidthper = $suoxiaowidth / $imgwidth;
        if($suoxiaoheightper >= $suoxiaowidthper)
        {
            //單元格高度為準
            $aftersuoxiaowidth = $imgwidth * (1 - $suoxiaoheightper);
            $arraysize = array('width' => $aftersuoxiaowidth, 'height' => $oldheight);
            return $arraysize;
        }
        else
        {
            //單元格寬度為準
            $aftersuoxiaoheight = $imgheight * (1 - $suoxiaowidthper);
            $arraysize = array('width' => $oldwidth, 'height' => $aftersuoxiaoheight);
            return $arraysize;
        }
    }
}

function luxcraft_autoGenerationID(){
    global $act;

    //20140215 忘了之前是什么规则的了（之前写的注释//2-4位是工厂号，最后6位是流水号），但是有问题，所以重新改为新建加1的规则
    if(strpos($act, "sales") !== false){
        $result = mysql_qone('select sales_vid from sales_invoice order by sales_vid desc');
        if($result['sales_vid']){
            return substr($result['sales_vid'], 0, 3).sprintf("%07d", substr($result['sales_vid'], 3)+1);
        }else{
            return 'INV0000001';
        }
    }
}

//20130812 数字中有逗号不方便提交，所以只保留两位小数，不需要逗号分隔
function my_formatMoney($money){
    return sprintf("%01.2f", round(floatval($money), 2));
}