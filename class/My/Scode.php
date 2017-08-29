<?php

if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

/*
 * 生成验证码
 *
 * 可用来生成乾淨或杂乱的验证码
 * 
 *
 * 用法:
 * 
 * 
 *
 * @copyright  yb
 * @version    0.1.1
 * @since      2009.7.18
 *
 * changelog:
 v0.1.1		modify the _randStr mothed, remove some confused letter from 'all' section, like S/J/7/D/B/4/A
 2011-06-27		delete 9/p/q from 'all' section.
*/

class My_Scode{
	public $width				= 80;
	public $height				= 30;
	public $sessionName			= 'go_scode';
	public $ttfFontFile			= '';			//TTF path & filename
	public $bgImgColor			= '#FFFFFF';	//背景颜色代码，或者背景图片的路径. 背景图片可以是jpg/gif/png
	public $transFont			= 0;			//字体是否透明, 0不透明, 100完全透明
	public $charset				= 'all';
	public $distort				= 6;			//扭曲度 6为适中，大于 6 则扭得更厉害
	public $codeLength			= 4;
	public $bgStLine			= 2;			// 0 - 3 表示背景直线的宽度，0为不画背景直线
	public $bgStLineColor		= '#9ec1e4';	// 背景直线的颜色，如果值为空，则从字符颜色中随机抽取
	public $bgMess				= 2;			// 0 - 10 表示杂乱程度，0为关闭杂乱噪点
	public $messLine			= 0;			// 0 - 3 表示前景直线的数量，0为不画前景直线
	public $maxAngle			= 15;			// 字体倾斜最大角度
	
	private $_colorLimit		= 255;			//假设创建真彩图片也有此最大调色板颜色数
	private $_correct			= false;		//用户所填写的验证码是否正确
	private $_im;								//图片对象
	private $_colorPanel;						//颜色表
	private $_maxSpace;							//两个字之间最宽距离
	
    function __construct($sessionName = '', $codeLength = 0){
		if(!empty($sessionName)) $this->sessionName = $sessionName;
		if($codeLength != 0) $this->codeLength = $codeLength;
		if ( session_id() == '' ) {
			session_start();
		}
	}
	
	function __destruct(){}
	
	public function check($data){
		if($data && isset($_SESSION[$this->sessionName . '_SECURE'])
					 && !strcasecmp($data, $_SESSION[$this->sessionName . '_SECURE']))
		{
			$_SESSION[$this->sessionName . '_SECURE'] = '';
			return true;
		}else{
			//$GLOBALS['myerror']->info($data. '|' .$_SESSION[$this->sessionName . '_SECURE']);
			return false;
		}
	}
	
	public function show(){
		$this->_maxSpace = intval($this->width / $this->codeLength / 3);
		$this->_colorPanel = array();
		$_SESSION[$this->sessionName . '_SECURE'] = $this->_randStr();
		$this->_createBg();
		$this->_generateColorPanel();
		if($this->bgStLine){
			$this->_drawStLine();
		}
		if($this->bgMess){
			$this->_drawMess();
		}
		if($this->ttfFontFile && function_exists('imagecreatetruecolor')){
			//使用TTF字体
			$this->_drawTtfText();
		}else{
			$this->_drawGdText();
		}
		if($this->messLine){
			$this->_drawMessLine();
		}
		$this->_draw();
	}
	
	// 画面TTF字符
	private function _drawTtfText(){
		$x = $y = $lastwidth = $lasty = $lastangle = $angle = $initX = 0;
		$fontinfo = array('width' => 0, 'height' => 0, 'xOffset' => 0, 'yOffset' => 0, 'belowBasepoint' => 0);
		$im = imagecreatetruecolor($this->width, $this->height);
		imagefill($im, 0, 0, $this->_colorPanel[0]);
		imagecolortransparent($im, $this->_colorPanel[0]);
		
		for($i=0; $i<$this->codeLength; $i++){
			$char = $_SESSION[$this->sessionName . '_SECURE']{$i};
			$fontsize = mt_rand(intval($this->height / 3.5), intval($this->height / 2.5));
			$lastangle = $angle;
			$lastwidth = $fontinfo['width'];
			//$lastheight = $fontinfo['height'];
			
			$angle = mt_rand(0, 1) ? mt_rand(360 - $this->maxAngle, 360) : mt_rand(1, $this->maxAngle);
			$fontinfo = $this->_boxinfo(imagettfbbox ($fontsize, $angle, $this->ttfFontFile, $char));
			$fontcolor = $this->_colorPanel[$this->_colorLimit - $this->codeLength + $i];
			if(!$initX){
				$initX = mt_rand(3, $this->width - $this->codeLength * ($fontinfo['width'] + $this->_maxSpace));
				if($initX > ($this->width - $this->codeLength * ($fontinfo['width'] + $this->_maxSpace))) $this->_maxSpace = intval($this->_maxSpace/2);
				//fb(array('initX'=> $initX, 'space'=>$this->_maxSpace));
			}
			
			$extX = intval(sin(deg2rad($angle)) * $fontinfo['height']);
			$extY = intval(sin(deg2rad(360 - $angle)) * $fontinfo['width']) + (strcmp($char, strtoupper($char)) ? intval($fontinfo['height'] / 4) : 0);
			$initX = $initX < $extX ? $extX : $initX;
			
			if($angle < 100){
				//字符向左偏, 需要考虑左上x、右上y坐标加成
				if($lastangle > 100){
					//前一字符向右偏
					$y = mt_rand($fontinfo['height'] + $extX, $this->height);
					$x = $x ? $x + $lastwidth + mt_rand($extX-2, $this->_maxSpace) : $initX;
				}else{
					$y = mt_rand($fontinfo['height'] + 3, $this->height);
					if($lasty && $lasty > $y){
						// 如果上一个字符比这个字符靠下，则x可以为负数，令两个字符贴近
						$x = $x + $lastwidth + mt_rand(-intval($extX/2), $this->_maxSpace);
					}else{
						$x = $x ? $x + $lastwidth + mt_rand(0, $this->_maxSpace) : $initX;
					}
				}
			}else{
				//字符向右偏, 需要考虑左上y、右下y坐标加成
				$y = mt_rand($fontinfo['height'] + $extY, $this->height - $extY);
				if($lasty && $lasty > $y){
					// 如果上一个字符比这个字符靠下，则x可以为负数，令两个字符贴近
					$x = $x + $lastwidth + mt_rand(-intval($extX/2), $this->_maxSpace);
				}else{
					$x = $x ? $x + $lastwidth + mt_rand(0, $this->_maxSpace) : $initX;
				}
			}
			$lasty = $y;
			//fb(array('char' => $char, 'x' => $x, 'y' => $y,
			//			'angle' => $angle, 'fontinfo' => $fontinfo));
			imagettftext($im, $fontsize, $angle, $x, $y, $fontcolor, $this->ttfFontFile, $char);
		}
		
		$im2 = imagecreatetruecolor ($this->width * 1.5 , $this->height);
		imagefill($im2, 0, 0, $this->_colorPanel[0]);
		for ( $i=0; $i<$this->width; $i++) {
			for ( $j=0; $j<$this->height; $j++) {
				$rgb = imagecolorat($im, $i, $j);
				if( (int)($i+20+sin($j/$this->height*2*M_PI)*10) <= imagesx($im2) && (int)($i+20+sin($j/$this->height*2*M_PI)*10) >=0 ) {
					imagesetpixel ($im2, (int)($i+20+sin($j/$this->height*2*M_PI-M_PI*0.4)* $this->distort) , $j , $rgb);
				}
			}
		}
		imagecolortransparent($im2, $this->_colorPanel[0]);
		imagecopymerge($this->_im, $im2, 0, 0, 0, 0, $this->width, $this->height, 100 - $this->transFont);
		imagedestroy($im);
		imagedestroy($im2);
	}
	
	private function _drawMessLine(){
		for($i=0; $i < $this->messLine; $i++){
			$linecolor = $this->_colorPanel[$this->_colorLimit - $this->codeLength + $i];
			imageline($this->_im, mt_rand(0, intval($this->width / 3)), mt_rand(0, $this->height), mt_rand(intval($this->width / 3) + 10, $this->width), mt_rand(0, $this->height), $linecolor);
		}
	}
	
	// 最终输出图片
	private function _draw(){
		header("Expires: Sun, 1 Jan 2000 12:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	
		header("Content-Type: image/jpeg");
		imagejpeg($this->_im, NULL, 90);
		imagedestroy($this->_im);
	}
	
	//画GD字符
	private function _drawGdText(){
		$x = $y = 0;
		$gdFontWidth = 8;
		
		//随机的字串起始位置
		$initX = mt_rand(3, $this->width - $this->codeLength * ($gdFontWidth + $this->_maxSpace / 2));
		if($initX > ($this->width - $this->codeLength * ($gdFontWidth + $this->_maxSpace))) $this->_maxSpace = intval($this->_maxSpace/2);
		//fb(array('initX'=> $initX, 'space'=>$this->_maxSpace));
		for($i=0; $i < $this->codeLength; $i++)
		{
			$font = mt_rand(3, 5);
			$fontcolor = $this->_colorPanel[$this->_colorLimit - $this->codeLength + $i];
			$x = $x ? $x + mt_rand($gdFontWidth, $gdFontWidth + $this->_maxSpace) : $initX;
			$y = mt_rand(1, $this->height - mt_rand(15, 18));
			//fb(array('x' => $x, 'y' => $y));
			imagechar($this->_im, $font, $x, $y, $_SESSION[$this->sessionName . '_SECURE']{$i}, $fontcolor);
		}
	}
	
	//画噪点
	private function _drawMess(){
		$time = $this->bgMess * 10;
		$maxColor = $this->_colorLimit - $this->codeLength - 1;
		if($this->ttfFontFile){
			$fontsize = 15;
			$messStr = $this->_randStr(5);
			//$info = $this->_boxinfo(imagettfbbox ($fontsize, 0, $this->ttfFontFile, $bgfont));
			$width = $this->width - 10 * 5 + 10;
			for($i=1; $i<$time; $i++){
				imagettftext($this->_im, $fontsize, 0, mt_rand(-20, $width), mt_rand(-5,$this->height), $this->_colorPanel[mt_rand(2, $maxColor)], $this->ttfFontFile, $messStr);
			}
		}else{
			$fontsize = 5;
			$messStr = '***';
			$width = $this->width - 6 * strlen($messStr);
			for($i=1; $i<$time; $i++){
				imagestring($this->_im, $fontsize, mt_rand(-20, $this->width), mt_rand(-5, $this->height), $messStr, $this->_colorPanel[mt_rand(2, $maxColor)]);
			}
		}
	}
	
	//转化box系列函数的结果为可理解的表示
	private function _boxinfo($bbox) {
		if ($bbox[0] >= -1)
			$xOffset = -abs($bbox[0] + 1);
		else
			$xOffset = abs($bbox[0] + 2);
		$width = abs($bbox[2] - $bbox[0]);
		if ($bbox[0] < -1) $width = abs($bbox[2]) + abs($bbox[0]) - 1;
		$yOffset = abs($bbox[5] + 1);
		if ($bbox[5] >= -1) $yOffset = -$yOffset; // Fixed characters below the baseline.
		$height = abs($bbox[7]) - abs($bbox[1]);
		if ($bbox[3] > 0) $height = abs($bbox[7] - $bbox[1]) - 1;
		return array(
			'width' => $width,
			'height' => $height,
			//'xOffset' => $xOffset,
			//'yOffset' => $yOffset,
			//'belowBasepoint' => max(0, $bbox[1])
    	);
	}
	
	//画横竖线
	private function _drawStLine(){
		//$this->_colorPanel[2] =
		//因此颜色没有在其他地方使用，因此没有存入调色板
		if(!$this->bgStLineColor) $this->bgStLineColor = $this->_colorPanel[mt_rand($this->_colorLimit - $this->codeLength, $this->_colorLimit - 1)];
		$linecolor = $this->_setColor($this->_im, $this->bgStLineColor);
		imagesetthickness($this->_im, $this->bgStLine);
		
		//竖线
		for($x = mt_rand(0, 5); $x < $this->width; $x += ($this->bgStLine + mt_rand(2, 5))) {
			imageline($this->_im, $x, 0, $x, $this->height, $linecolor);
		}
		//横线
		for($y = mt_rand(0, 5); $y < $this->height; $y += ($this->bgStLine + mt_rand(2, 5))) {
			imageline($this->_im, 0, $y, $this->width, $y, $linecolor);
		}
	}
	
	// 创建噪点颜色与字体颜色
	private function _generateColorPanel(){
		for($i=2; $i < $this->_colorLimit; $i++){
			$this->_colorPanel[$i] = $i < ($this->_colorLimit - $this->codeLength)
						? imagecolorallocate ($this->_im, mt_rand(128,255), mt_rand(150,255), mt_rand(200,255))
						: imagecolorallocate ($this->_im, mt_rand(0,100),   mt_rand(0,150),   mt_rand(0,200));
		}
		//$this->_colorPanel[$this->_colorLimit] = imagecolorallocate ($this->_im, 0, 0, 255);
	}
	
	// 创建图像背景
	private function _createBg(){
		function_exists('imagecreatetruecolor') ? $this->_im = imagecreatetruecolor($this->width, $this->height) : $this->_im = imagecreate($this->width, $this->height);
		$this->_colorPanel[] = $this->_setColor($this->_im, '#FFFFFF');
		if(!$this->bgImgColor || ($this->bgImgColor && ($this->_isColor($this->bgImgColor) || !is_readable($this->bgImgColor)))){
			//fb($this->bgImgColor);
			if(!$this->bgImgColor || !$this->_isColor($this->bgImgColor)) $this->bgImgColor = '#FFFFFF';
			$this->_colorPanel[] = $this->_setColor($this->_im, $this->bgImgColor);
			//fb($this->bgImgColor);
			imagefill($this->_im, 0, 0, $this->_setColor($this->_im, $this->bgImgColor));
		}else{
			$this->_setBackgroundImg();
		}
	}
	
	private function _setBackgroundImg(){
		$dat = getimagesize($this->bgImgColor);
		if(!$dat) return;
		
		switch($dat[2]) {
		  case 1:  $newim = imagecreatefromgif($this->bgimg); break;
		  case 2:  $newim = imagecreatefromjpeg($this->bgimg); break;
		  case 3:  $newim = imagecreatefrompng($this->bgimg); break;
		  default: return;
		}
		if(!$newim) return;
		
		imagecopy($this->_im, $newim, 0, 0, 0, 0, $this->width, $this->height);
	}
	
	private function _isColor($color){
		return preg_match("/^[\#ABCDEFabcdef0123456789]{6,7}$/", $color);
	}
	
	private function _setColor (&$im, $hexColor){
		$int = hexdec($hexColor);
		return imagecolorallocate ($im,
			 0xFF & ($int >> 0x10),
			 0xFF & ($int >> 0x8),
			 0xFF & $int);
	}
	
	private function _randStr() {
		switch($this->charset) {
			case 'full':
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
				break;
			case 'upper':
				$chars='CEFHKMRTWXY23568';
				break;
			case 'all':
				$chars='CEFHKMQRTWXYcefhkmrtwxy23568';
				break;
			case 'letter':
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
				break;
			case 'number':
				$chars='0123456789';
				break;
			default :
				//all
				$chars='ABCDEFGHKMNPQRTWXYabcdefghkmnpqrtwxy234689';
		}
		$str = '';
		for($i = 0; $i < $this->codeLength; $i++){
			$str .= $chars{mt_rand(0, strlen($chars) - 1)};
		}
		return $str;
	}

}
