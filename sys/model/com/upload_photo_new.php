<?php
//require($_SERVER['DOCUMENT_ROOT'] . '\in7\global.php');
//require($_SERVER['DOCUMENT_ROOT'] . '\sys\in38\global_admin.php');
date_default_timezone_set('Asia/ShangHai');

// 上傳設置
$thumb_width		= array('s' => 60, 'm' => 150, 'l'=> 214);	//自動縮小圖片的寬度上限
$thumb_height		= array('s' => 45, 'm' => 112, 'l'=> 0);	//自動縮小圖片的高度上限
$thumb_quality		= 85;

$pic_path = '/sys/upload/lux_test/';
$pic_dir_phy = $_SERVER['DOCUMENT_ROOT'] . $pic_path;
//echo $pic_dir_phy;die();
$file_exts = array('.jpg','.png','.gif');
$max_size = 300000000;

function is__writable($path) {
    if ($path{strlen($path)-1}=='/'){
        return is__writable($path.uniqid(mt_rand()).'.tmp');
	} else if (is_dir($path)){
		return is__writable($path.'/'.uniqid(mt_rand()).'.tmp');
	}
    $rm = file_exists($path);
    $f = @fopen($path, 'a');
    if ($f===false){
        return false;
	}
    fclose($f);
    if (!$rm){
        unlink($path);
	}
    return true;
}
/*
function do_upload($upload_dir,$temp_name,$file_ext) {
	$newname = date("Ymdis").mt_rand(100,999).$file_ext;
	$sfile_path = $upload_dir.'s_'.$newname;
	$mfile_path = $upload_dir.$newname;
	makethumb($temp_name, $sfile_path, 's');
	makethumb($temp_name, $mfile_path, 'm');
	unlink($temp_name);
	return $newname;
}

function do_upload_pk($upload_dir,$temp_name,$file_ext) {
	$newname = date("Ymdis").mt_rand(100,999).$file_ext;
	$sfile_path = $upload_dir.'s_'.$newname;
	makethumb($temp_name, $upload_dir.$newname);
	makethumb($upload_dir.$newname, $sfile_path, 'l');
	unlink($temp_name);
	return $newname;
}
*/
function makethumb($srcFile, $dstFile, $type=NULL, $markwords=NULL,$markimage=NULL){
	global $thumb_width, $thumb_height, $thumb_quality;
	$data = getimagesize($srcFile);
	switch($data[2])
	{
		case 1:
			$im=@imagecreatefromgif($srcFile);
			break;
		case 2:
			$im=@imagecreatefromjpeg($srcFile);
			break;
		case 3:
			$im=@imagecreatefrompng($srcFile);
			break;
	}
	if(!$im) return false;
	$srcW=imagesx($im);
	$srcH=imagesy($im);

	$dstX=$dstY=$fPosW=$fPosH=0;
	if($type){
		$dstH = $thumb_height[$type];
		$dstW = $thumb_width[$type];
		if($dstH == 0){
			$fdstH = $dstH = round($srcH*$dstW/$srcW);
			$fdstW = $dstW;
		}elseif($dstW == 0){
			$fdstW = $dstW = round($srcW*$dstH/$srcH);
			$fdstH = $dstH;
		}else{
			if($srcW>$srcH){
				if($srcW / $srcH < $dstW / $dstH){
					$fdstW = round($srcW*$dstH/$srcH);
					$fdstH = $dstH;
				}else{
					$fdstH = round($srcH*$dstW/$srcW);
					$fdstW = $dstW;
				}
			}else{
				$fdstW = round($srcW*$dstH/$srcH);
				$fdstH = $dstH;
			}
			$fPosW = floor(($dstW - $fdstW) / 2);
			$fPosH = floor(($dstH - $fdstH) / 2);
		}
	}else{
		$fdstH = $dstH = $srcH;
		$fdstW = $dstW = $srcW;
	}
	
	$ni=imagecreatetruecolor($dstW,$dstH);
	if($dstH > 0 && $dstW > 0 && $type){
		$white = imagecolorallocate($ni,255,255,255);
		//$black = imagecolorallocate($ni,0,0,0);
		imagefilledrectangle($ni,0,0,$dstW,$dstH,$white); 
	}
	imagecopyresized($ni,$im,$fPosW,$fPosH,0,0,$fdstW,$fdstH,$srcW,$srcH);
	switch($data[2])
	{
		case 1:
			imagegif($ni,$dstFile);
			break;
		case 2:
			imagejpeg($ni,$dstFile, $thumb_quality);
			break;
		case 3:
			imagepng($ni,$dstFile);
			break;
	}
	imagedestroy($im);
	imagedestroy($ni);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>upload</title>
<style>
body,td{font-size:10pt;}
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
</style>
<script language="javascript">
function check()
{
	if ( document.getElementById('UpPic').value.length == 0 )
	{
		alert("请选择需要上传的文件");
		return false;
	}
	return true;
}

function update_pform(forwhat, url){
	parent.document.getElementById("Submit").disabled=false;
	parent.document.getElementById(forwhat).value=url.replace('s_', '');
	parent.document.getElementById('p_'+forwhat).src=url;
}
</script>
</head>

<body>
<?
$upit = '';
$msg = '';
if(isset($_POST["Submit"]) && !is_null($_POST["Submit"]))
{
	if($_FILES['UpPic']['error'] == 0 && $_FILES['UpPic']['size'] > 0)
	{
		$file_name = basename($_FILES['UpPic']['name']);
		$temp_name = $_FILES['UpPic']['tmp_name'];
		$file_ext = strtolower(substr($file_name,strrpos($file_name,".")));
		$error = false;
		if ( $_FILES['UpPic']['size'] > $max_size)
		{
			$error = true;
			$msg .="文件超过限定大小(".$max_size." bytes);";
		}
		if (!in_array($file_ext, $file_exts))
		{
			$error = true;
			$msg .="文件类型未被允许;";
		}
		if (!is__writable($pic_dir_phy)){
			$error = true;
			$msg .="伺服器错误，文件读写未被允许;";
		}
		if(!$error)
		{
			/*
			switch($_POST["for"]){
				case 'Pic':
				$upit = do_upload($pic_dir_phy,$temp_name,$file_ext);
				break;
				
				case 'pkPic':
				$upit = do_upload_pk($pic_dir_phy,$temp_name,$file_ext);
				break;
			}
			*/
			if( $_POST['for'] == 'Pic'){
				
				//上传缩小后的图，前缀为s，意为small
				$sfile_path = $pic_dir_phy.'s_'.$file_name;
				$upit = makethumb($temp_name, $sfile_path, 's');
				
				//上传原图（原来move_uploaded_file会自动删掉temp图片）
				//move_uploaded_file 和 file_exists 只支持GBK格式中文（因为系统的格式是GBK），UTF-8格式的中文不行
				move_uploaded_file($temp_name, iconv('UTF-8', 'GBK', $pic_dir_phy.$file_name));				
			}
			
			if(false === $upit)
			{
				$error = true;
				$msg ="文件上传错误;";
			}
			else
			{
				$msg ="文件上传完成;";
				$msg .='<script language="javascript">update_pform("'.$_POST["for"].'", "/'.$pic_path.$upit.'");</script>';
				@unlink($pic_dir_phy.$_GET['oldpic']);
				@unlink($pic_dir_phy.'s_'.$_GET['oldpic']);
			}
		}
	}
	else
	{
		switch($_FILES['UpPic']['error']){
		case 1:
		$msg .= "上传的文件大小超过了系统限制值; ";
		break;
		case 2:
		$msg .= "上传文件大小超过了 $max_size bytes大小限制; ";
		break;
		case 3:
		$msg .= "文件只有部分被上传成功, 请检查你的网络连接是否正常; ";
		break;
		case 4:
		$msg .= "没有文件被上传, 请选择要上传的文件; ";
		break;
		case 6:
		$msg .= "系统错误: 找不到临时文件夹; ";
		break;
		case 7:
		$msg .= "文件写入失败; ";
		break;
		}
	}
	echo $msg;
?>
&nbsp;<a href="upload_photo_new.php?for=<?= $_GET["for"]; ?>&oldpic=<?=$upit?>">&lt;&lt;更换</a>
<?
	
}
else
{
?>
<form name="form" method="post" action="" enctype="multipart/form-data" onSubmit="return check();">
<input type="hidden" name="for" value="<?= $_GET["for"]; ?>">
<input type="hidden" name="MAX_FILE_SIZE" value="<?= $max_size ?>">
<input name="UpPic" type="file" id="UpPic" size="28">
<input type="submit" name="Submit" value="上传" onClick="parent.document.getElementById('Submit').disabled=true;">
</form>
<?
}
?>
</body>
</html>
