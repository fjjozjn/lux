<?php
/*
* Service 操作类
*	
* 用以操作各种 web service 接口
* created:	2009-09-16
* 
* changelog:
		?		some change about deal with 'rtn'.
		?		增加了处理java的ws可能会返回的 out 结果数组. 增加 WS_ZERO 定义，取消var.inc.php中的定义
		?		增加了quiet初始化选项，用来不输出错误信息
		?		针对自家应用，增加了三种接口预设返回值，分别表示：1、无权限 2、WS所在系统维护中 3、错误的MD5签名
	2011.03.01	为以上常量加上了if(!defined()...)
	2011-06-16	增加打印调用web service的firebug info
	2011-06-28	调整打印调用结果的代码
*/

if (!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

!defined('WS_ZERO') && define('WS_ZERO',	-1006);
!defined('NO_ACCESS') && define('NO_ACCESS',		-1007);
!defined('IS_MT') && define('IS_MT',			-1008);
!defined('BAD_SIGN') && define('BAD_SIGN',		-1009);

require_once(ROOT_DIR.'class/My/soap_ngo.php');

class My_Service extends nusoap_client{
	
	private $_ws;
	//private $_methodList;
	private $_systemError;
	private $_connected;
	private $_quiet;
	
	/*
	* 架构函数
	*/
    public function __construct($url, $wsdl = true, $quiet = false){
		$this->_systemError = isset($GLOBALS['words']['systemError']) ? $GLOBALS['words']['systemError'] : 'System configure Error!';
		$this->_connected = false;
		$this->_quiet = $quiet;
		parent::__construct($url, $wsdl);
		if($err = $this->getError()){
			$this->_error();
			$this->_debug($err);
		}else{
			$this->_connected = true;
		}
    }
	
	/*
	* 解构函数
	*/
	function __destruct(){}
	
	public function connected(){
		return $this->_connected;
	}
	
	/*
	* 调用接口
	* 
	* 返回false的出口，都是非正常出口，未能拿到接口正常返回值
	* 其他值表示接收到接口的值。在很多接口中，会返回0表示成功或失败，因此在此将0换成 WS_ZERO 常量(见顶部define)
	*/
	public function soapCall($method, $arguments = NULL, $namespace = NULL) {
		$this->_debug('Calling WebService Method:' . $method);
		$this->_debug('Arguments were:');
		$this->_debug($arguments);
		
		$rtn = $this->call($method, $arguments, $namespace);
		$this->_debug('Results were:');
		$this->_debug($rtn);
		/*
		echo '<h2>Request</h2><pre>' . htmlspecialchars($this->request, ENT_QUOTES) . '</pre>';
		echo '<h2>Response</h2><pre>' . htmlspecialchars($this->response, ENT_QUOTES) . '</pre>';
		echo '<h2>Debug</h2><pre>' . htmlspecialchars($this->debug_str, ENT_QUOTES) . '</pre>';
		die();
		*/
		if($this->fault){
			$this->_error();
			return false;
		}elseif($err = $this->getError()){
			$this->_error();
			$this->_debug($err);
			return false;
		}elseif(is_null($rtn)){
			$this->_error();
			$this->_debug('Return value from WS is NULL.');
			return false;
		}else{
			if(is_array($rtn)){
				if((!isset($rtn[$method . 'Result']) || is_null($rtn[$method . 'Result'])) && (!isset($rtn['out']) || is_null($rtn['out']))){
					$this->_error();
					$this->_debug('return value missed key');
					return false;
				}else{
					if(strlen($rtn[$method . 'Result']) == 1 && isId($rtn[$method . 'Result']) && intval($rtn[$method . 'Result']) === 0){
						return WS_ZERO;
					}else{
						return isset($rtn[$method . 'Result']) ? $rtn[$method . 'Result'] : $rtn['out'];
					}
				}
			}else{
				if(strlen($rtn) == 1 && isId($rtn) && intval($rtn) === 0){
					return WS_ZERO;
				}else{
					return $rtn;
				}
			}
		}
    }
	
	// 测试，如果使用proxy方式调用 ws...使用此方法判断rtn是否false、有没有出错，并返回json_decode
	public function proxyCalled(){
		return true;
	}
	
	private function _debug($var){
		$GLOBALS['myerror']->info($var);
	}
	
	private function _error($var = ''){
		if($this->_quiet) return false;
		if(empty($var)) $var = $this->_systemError;
		if($GLOBALS['ajax'])
			$GLOBALS['myerror']->error($var);
		else
			$GLOBALS['myerror']->error($var, 'REFRESH');
	}
	
	private function _warn($var){
		$GLOBALS['myerror']->warn($var);
	}

}


?>