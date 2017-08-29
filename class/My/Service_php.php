<?php

if (!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

/*
* Service 操作类 
*	
* 用以操作各种 web service 接口
* ver:		v0.1
* created:	2009-09-16
* modified: 2009-09-22
*/
class My_Service extends SoapClient{
	
	private $_methodList;
	
	/*
	* 架构函数
	*/
    public function __construct($methodList, $wsdl = NULL, $option = NULL){
		if((!$wsdl && !$option) || !is_array($methodList)) return false;
		parent::__construct($wsdl, $option);
		$this->_methodList = $methodList;
    }
	
	/*
	* 解构函数
	*/
	function __destruct(){}
	
	/*
	* 主函数
	* 所有方法入口
	*/
	public function __call($method, $arguments) {
		if(!is_array($this->_methodList) || !in_array($method, $this->_methodList)){
			return false;
		}
		
		try{
			fb($method);
			fb($arguments);
			$rtn = $this->__soapCall($method, $arguments);
			if($rtn === 0){
				// 梦古的成功操作会返回 0
				// 神奇的接口...
				// 没办法，为了与本类返回的false有所区别，将 0 值改为 -1001 后返回
				return -1001;
			}
			return $rtn;
		}catch(SoapFault $fault){
			$this->_debug($fault);
			var_dump($fault);
			return false;
		}catch(Exception $e){
			$this->_debug($e);
			var_dump($e);
			return false;
		}
    }
	
	private function _debug($var){
		$GLOBALS['myerror']->info($var);
	}
	
	private function _error($var){
		$GLOBALS['myerror']->error($var);
	}
	
	private function _warn($var){
		$GLOBALS['myerror']->warn($var);
	}

}


?>