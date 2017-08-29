<?php

if (!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

/*
* Service 操作类 
*	
* 用以操作各种 web service 接口
* ver:		v0.1
* created:	2009-09-16
* modified: 2010-02-23
* changelog:
* 			2010-02-23		将key/cookieName/cookieExpire加入默认值
*/
class My_SSOServer{
	
	private $_key = 'ngkasad8ahsdf8hg8weh34h63ff';
	private $_totalLen = 128;
	private $_splitLen = 22;
	private $_ssoCookieName = 'psi';
	private $_ssoCookieDomain = '.gameone.com.tw';
	private $_ip = '';

	/*
	* 架构函数
	*/
    public function __construct($key = '', $cookiename = '', $domain = '', $ip = ''){
		if($key && strlen($key) > 16){
			$this->_key = $key;
		}
		if($cookiename) $this->_ssoCookieName = $cookiename;
		if($domain) $this->_ssoCookieDomain = $domain;
		if($ip) {
			$this->_ip = $ip;
		}else{
			$this->_ip = $GLOBALS['ip'];
		}
    }
	
	/*
	* 解构函数
	*/
	function __destruct(){}
	
	public function create(){
		
		if(empty($this->_key)){
			return false;
		}
		
		$rnd = randomString(30);
		//XXTEA::setkey($this->_key);
		/*
		id, username, ip, agent(md5), time()
		*/
		$str = implode(randomString(3), array(userSession(), userSession('GOLogin'), $this->_ip, substr(md5($_SERVER['HTTP_USER_AGENT']), 11, 16), time()));
		//$this->_info(array(userSession(), userSession('GOLogin'), $this->_ip, substr(md5($_SERVER['HTTP_USER_AGENT']), 11, 16), time()));
		//$this->_info('BeforeEncode:' . $str);
		$str = XXTEA::encrypt($str);
		//$this->_info('Encode:' . $str);
		
		$len = strlen($str);
		//$this->_info($len);
		$str1 = substr($str, 0, $this->_splitLen);
		//$this->_info('S1:'. $str1);
		$str2 = substr($str, $this->_splitLen, $this->_splitLen);
		//$this->_info('S2:'. $str2);
		$str3 = substr($str, $this->_splitLen * 2);
		//$this->_info('S3:'. $str3);
		//$this->_info($str1.' -- '.$str2.' -- '.$str3);
		
		$value		= '';
		$rndStr		= XXTEA::encrypt($_SERVER['HTTP_USER_AGENT']. (time() * mt_rand(10, 99)). $rnd . session_id() .date('H:i:s'));
		$firstLen	= floor(($this->_totalLen - $len - 5)/3);
		$first		= substr($rndStr, mt_rand(4, 11), $firstLen);
		$value 		.= $first.$str2;
		//$this->_info('C1:'. $value);
		$middleLen	= $firstLen + 1;
		$middle		= substr($rndStr, mt_rand(13, 24), $middleLen);
		$value 		.= $middle.$str1;
		//$this->_info('C2:'. $value);
		$endLen		= $this->_totalLen - $len - $firstLen - $middleLen;
		$end 		= substr($rndStr, mt_rand(27, 35), $endLen);
		$value		.= $end.$str3.($len * 6 - 101);
		//$this->_info('Cookie:' . $value);
		//return $value;
		//header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		setcookie($this->_ssoCookieName, $value, 0, '/', $this->_ssoCookieDomain);
	}
	
	private function _info($var, $go = '', $auto = 0){
		$GLOBALS['myerror']->info($var, $go, $auto);
	}
}


?>