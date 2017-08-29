<?php

if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');
!defined('AJAX') && define('AJAX', '');

/*
 * 处理页面错误信息
 *
 * 用来存、取错误/警告/调试信息
 * 其中，显示 "调试" 信息有一的使用条件：
 * 必须在FireFox上，并且安装FireBug & FirePHP两个插件
 * Get FireBug: https://addons.mozilla.org/zh-CN/firefox/addon/1843
 * Get FirePHP: https://addons.mozilla.org/en-US/firefox/addon/6149
 * 
 * 概念
 * 错误：并非指PHP错误，而是指页面不应继续显示，失去下一步操作必要时。
 * 警告：一些提示信息，比如用户填写的信息不正确，提示用户在做一些修正后继续提交。
 * 调试：任何时候，都可以将PHP中的值，或者自定义的信息，显示在FireBug控制台中。
 *
 * 用法:
 * $myerror->$method($msg, $go, $auto)
 * $method 可以是 error, warn, info, ok，其作用请顾名思义
 * 		$msg	需要显示的信息
 *		$go		下一步操作，INDEX/BACK/REFRESH，或者某个具体URL
 				默认无操作
 * 		$auto	上述下一步操作是否在页面显示的几秒后自动进行
 				此值可以设为0, 或其他数字。大于0的数字表示 几秒 后即自动操作
				默认为0，即不自动操作
 
 * 错误：$myerror->error('阁下未注册此游戏的帐号，无法进行储值操作', 'INDEX');
 * 警告：$myerror->warn('验证码错误，请重新输入正确的验证码');
 * 调试：
 *   $rows = $mysql->fetch();
     $myerror->info($rows);
 * 成功(并且页面可继续显示)：$myerror->ok('操作成功，请继续');
 * 成功(并且页面终止显示)：$myerror->over('操作成功，请继续');
 * 
 * 判断是否有错误发生：
 * if($myerror->getError()){
	   $myerror->getMsg('error');
   }else{
       //正常输出表单、或其他资料
   }
 *
 * 判断是否有警告：
 * if($myerror->getWarn()){
       $myerror->getMsg(); // 'warn'参数默认, 也可以取其他错误信息
   }
 *
 * 判断是否成功并结束：
 * if($myerror->getOver()){
      $myerror->getMsg('over');
   }
 *
 * 判断是否成功并继续显示：
 * if($myerror->getOk()){
      $myerror->getMsg('ok');
   }
 *
 * @copyright  yb
 * @version    0.2
 * @since      2009.7.4
 * @modified   2009.9.24
 
 changelog:
 
 2010-05-11		修改了auto goto url中对本地url的php/htm/html的判断方式(原来是strpos，改为正则)
*/

final class My_Errors{
	private static $_instance; 				// 存储单件模式下的实例
	private $_hasWarn		= false;		// 是否有错误发生
	private $_die			= false;		// 是否有警告信息
	private $_ok			= false;		// 是否有成功信息
	private $_over			= false;		// 是否有导致页面结束的成功信息
	private $_msgBox		= array();		// 存放错误与警告信息的数组
	private $_goWhere;						// 存放与以上信息相对应的下一步操作的操作类型
	private $_autoGo;						// 存放下一步操作是否自动
	private $_errorLevel	= array('warn'	=> 1,
								  'info'	=> 0,
								  'ok'		=> 2,
								  'over'	=> 3,
								  'error'	=> 99
								);
	
    function __construct(){}
	function __destruct(){}
	
	//静态函数,配合静态变量使用,实现singleton设计模式
	public static function get(){
		if (!self::$_instance){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/*
	* 添加错误/警告/调试信息
	* 为之指定下一步操作的类型	参见 in7/var.inc.php
	*/
	public function __call($method, $arguments) {
		if(!array_key_exists($method, $this->_errorLevel) || !count($arguments))
			$method = 'warn';
		if(in_array($method, array('error', 'warn', 'ok', 'over'))){
			//fb($arguments);
			$this->_msgBox[$this->_errorLevel[$method]][] = $arguments[0];
			!isset($arguments[1]) && $arguments[1] = '';
			!isset($arguments[2]) && $arguments[2] = -1;
			
			//fb($arguments);
			
			$this->_goWhere[$this->_errorLevel[$method]][] = $arguments[1];
			$this->_autoGo[$this->_errorLevel[$method]][] = $arguments[2];
			if(!in_array($method, array('ok', 'over'))){
				$this->_hasWarn = true;
			}else{
				if($method == 'over'){
					$this->_over = true;
				}else{
					$this->_ok = true;
				}
			}
			$method == 'error' && $this->_die = true;
		}
		
		if(defined('SHOW_ERROR') && SHOW_ERROR && !in_array($method, array('ok', 'over'))){
			FB::$method($arguments[0]);
		}
	}
	
	/* 是否有致命错误 */
	public function getError(){
		return $this->_die;
	}
	
	/* 是否有普通错误以及致命错误 */
	public function getWarn(){
		return $this->_hasWarn;
	}
	
	/* 是否有普通错误以及致命错误 */
	public function getAny(){
		if($this->_hasWarn || $this->_die){
			return true;
		}else{
			return false;
		}
	}
	
	/* 是否有普通错误 */
	public function getOk(){
		return $this->_ok;
	}
	
	/* 是否有普通错误 */
	public function getOver(){
		return $this->_over;
	}
	
	/*
	* 返回指定类别的错误信息
	*/
	public function getMsg($level = 'warn', $return = false){
		if($level == 'info'){
			if($return)
				return $this->_getDebug();
			else
				echo $this->_getDebug();
		}else{
			// 优先显示致命错误信息
			if($level == 'warn' && $this->_die) $level = 'error';
			if(!set($this->_msgBox[$this->_errorLevel[$level]])) return '';
			$theLast = count($this->_msgBox[$this->_errorLevel[$level]]) - 1;
			$str = $this->_msgBox[$this->_errorLevel[$level]][$theLast] . $this->_getGoWhere($level, $theLast);
			if($return)
				return $str;
			else
				echo $str;
		}
	}
	
	private function _getGoWhere(&$level, &$theLast){
		$str = $this->_goWhere[$this->_errorLevel[$level]][$theLast];
		if(!empty($str)){
			switch($str){
				case 'MAIN':
					$url = "goto('?act=main');";
					break;
				case 'INDEX':
					$url = "goto('?act=index');";
					break;
				case 'CLOSE':
					//mod 20130208 因为很多浏览器不支持 window.close ，所以缓存跳转到空白页
					$url = "goto('about:blank');";
					//$url = 'window.close();';
					break;
				case 'BACK':
					$url = 'history.go(-1);';
					break;
				case 'LOGOUT':
					$url = "goto('?act=member-logout');";
					break;
				case 'REFRESH':
					//$url = 'javascript:location=location;'; // the js way.
					$url = "goto('" . ((!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off') ? 'http' : 'https')
						. "://"  . $_SERVER['SERVER_NAME'] .(!in_array($_SERVER['SERVER_PORT'], array(80, 443)) ? ':'. $_SERVER['SERVER_PORT'] : '')
						. $_SERVER["REQUEST_URI"] . "');";
					// replace it with $_SERVER["SCRIPT_NAME"], if don't need querystrings.
					break;
				default:

                    //20130624 加去除跳转url字符限制的参数 $zjn
                    global $act;
                    $zjn = false;
                    if(strpos($act, 'warehouse') !== false){
                        $zjn = true;
                    }

                    //20130624 最大长度由50改为100
					if(vChar($str, 1, 100, '', $zjn)){
						$url = "goto('?act=$str');";
					}elseif($str[0] == '/' 
							|| strtolower(substr($str,0, 4)) == 'http' 
							|| preg_match("/\.(php|html|htm)+/i", $str)){
						$url = "goto('$str');";
					}else{
						$url = "goto('?act=index');";
					}
			}
			// mod 20121010 工厂页面提示用中文，公司页面提示用英文
			if(strpos($_SERVER['SCRIPT_NAME'], '/sys/') !== false){
				$str = '<input type="button" class="smallbutton" value=" ' . (set($GLOBALS['words'][$str]) ? $GLOBALS['words'][$str] : ' Proceed ') . ' " onclick="'. $url .'" />';
			}else{
				$str = '<input type="button" class="smallbutton" value=" ' . (set($GLOBALS['words'][$str]) ? $GLOBALS['words'][$str] : ' 点击继续 ') . ' " onclick="'. $url .'" />';
			}
			$auto = $this->_autoGo[$this->_errorLevel[$level]][$theLast];
			if($auto >= 0) {
				$str .= printJs("setTimeout(function(){". $url ."}, ". $auto * 1000 .");");
			}
		}
		return $str;
	}
}

//产生单件对象
$myerror = My_Errors::get();
