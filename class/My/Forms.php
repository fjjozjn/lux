<?php

if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

/*
 * My_Forms 类
 *
 * 作用：
 * 产生Form校验信息、可定制的Form项，并验证Form提交及校验信息是否合法
 *
 * 使用：
 * $form = new My_Forms();	此处可以初始化表单
 * 							提供 action, method, name, multipart等属性
 * 
 * $form->init($formItems);	此处可接受数组形式的表单定义
 * $from->begin();			输出表单头
 * $form->show($field);		输出表单内容
 * $form->end()				输出表单尾
 *
 
 * 剩余工作
 * 添加一次性输出所有表单项的方法 $form->showall();
 * 完成File类型
 *
 * @copyright	yb
 * @since		2009.7.9
 *

changelog:
xxxx-xx-xx		修改focus相关代码，令表单出错时，焦点在出错的表单上。
2010-06-09		修改_showSelect & _showTextarea，增加nostar参数
2010-06-10		修改js datePicker的路径，由/ui/..改为ui/..，以令后台可使用不同配置的datePicker
2010-07-07		为value加上了htmlentities($v, ENT_COMPAT, 'UTF-8')，避免"导致表单值出错
   				将autoSubmit设为了自动提交的时间
2010-11-26		增加了ihridername Restrict.
2011-02-22		修改_checkField()，增加something depend on checkbox
2011-04-06		修改了begin(),为 form加了一个 novalidate 属性，以关闭html5浏览器的自动验证功能
2011-06-17		修正了多处checkbox/select中对options[2]未检查isset的问题
2011-06-30		修改_whomFocus为空时，end()仍然输出$(#).focus()的bug, 添加noCssEffects/noStars/noTitles Form属性, 为image增加width/height属性
2011-07-01		修改不允许select的options为空的bug (因为有时候允许options由ajax填充)
2011-07-04		修改不允许un-required的select值为空的bug
*/

class My_Forms{
	// form序列
	static private $formId = 0;
	static private $allFormIndex = 1;
	// 当前form的识别key
	private $_formKey;
	// 当前form的签名串
	private $_signature;
	// 存放form元素的数组
	private $_fields;
	// form的tabIndex序列
	private $_thisFormIndex = 1;
	// 非提交: 第一个可用元素在页面开始显示时获得焦点
	// 提交后: 第一个出错元素在页面开始显示时获取焦点
	private $_whomFocus = '';
	// 记录哪些表单需要显示日期
	private $_dateInput = '';
	// 记录哪个输入框需要显示验证码
	private $_scode		= '';
	// 是否存在select
	private $_hasSelectForm	= false;
	
	private $luxindex = 1;
	/*
	// 已废弃
	
	// 表单CSS样式. 可以在声明对象后直接修改
	// 例如:
	// $form->css['formtitle'] = 'othercss';
	public $css = array(
						'formtitle'	=> 'formtitle',			// 标题
						'formfield'	=> 'formfield',			// form项
						'forminfo'	=> 'forminfo',			// form说明
						'selectfield'	=> 'selectfield',	// select field样式
						'buttonfield'	=> 'buttonfield'	// 按钮样式
						);
	*/
	public $formAttr	= array(
						'action'	=> '',					//表单提交地址. 必须是完整的url或以?开头
						'method'	=> 'POST',				//表单提交方式
						'formName'	=> '',					//此表单的名称 (允许页面中有多个表单).此值即使不指定也可自动生成不一致的form名
						'multipart'	=> false,				//此表单是否转换编码(上传文件时需要设定为true)
						'lifeTime'	=> 14400,				//表单生存期，单位：秒（mod by zjn 原來的1200秒太少了，改為了4個小時。。。）
						'freTime'	=> 2,					//表单从生成到被提交的最少时间间隔
						'noFocus'	=> false,				//页面初始化时，不需要focus.
						'hasPage'	=> false,				//此表单是否与分页查询有关
						'noExtHtml'	=> false,				//不使用任何样式修饰表单项
						'autoSubmit'=> -1,					//是否自动提交表单. 大于等于0 表示在多少秒后自动提交表单
						'noCssEffects'	=> false,			//是否跳过为表单添加init,focus,error效果
						'noStars'	=> false,				//是否显示红星
						'noTitles'	=> false,				//是否显示标题
                        'addon'     => '',                  //20140227 加addon 因为原来的没有target属性
						);
	
	/*
	* 构造函数
	* 参数数组参考 $formDefault
	* 只需要指定需要改变的参数值
	*/
    function __construct($parm = false){
		$this->_field = array();
		if($parm && is_array($parm)){
			$this->formAttr = array_merge($this->formAttr, $parm);
		}
		if(!setTrue($this->formAttr['formName']) || !vChar($this->formAttr['formName'], 2, 30)){
			$this->formAttr['formName'] = 'gof';
		}
		$this->formAttr['formName'] .= self::$formId++;
		$this->formAttr['formKeyName'] = $this->formAttr['formName'] . 'key';
		$this->_generateKey();
	}
		
	function __destruct(){}
	
	/*
	* 初始化表单
	*/
	public function init($fieldArray){
		if(!is_array($fieldArray)){
			$this->_warn(isset($GLOBALS['words']['systemError']) ? $GLOBALS['words']['systemError'] : 'System configure Error!');
			$this->_debug('缺少表单数据，无法显示页面');
			return false;
		}
		$this->_fields = $fieldArray;
		foreach($this->_fields as $key => $field){
			if($this->formAttr['noStars']){
				$this->_fields[$key]['nostar'] = true;
			}
			if($this->formAttr['noTitles']){
				$this->_fields[$key]['noTitle'] = true;
			}
			if($this->formAttr['noExtHtml']){
				$this->_fields[$key]['nohtml'] = true;
			}
			if(set($field['type'], 'select')){
				$this->_hasSelectForm = true;
			}
			if(!setTrue($field['restrict'])) continue;
			switch($field['restrict']){
				case 'date':
					$this->_dateInput .= (empty($this->_dateInput) ? '' : ',') . '#' . (setTrue($field['id']) ? $field['id'] : $key);
					break;
				case 'scode':
					$this->_scode = (setTrue($field['id']) ? $field['id'] : $key);
					break;
			}
		}
	}
	
	/*
	* 显示form头
	* 20140207 加addon属性
	*/
	public function begin(){
		echo '<FORM novalidate id="'. $this->formAttr['formName'] .'" name="'. $this->formAttr['formName'] .'" method="' .
			$this->formAttr['method'] .'" ' . ($this->formAttr['addon'] ? $this->formAttr['addon'] : '') .
			($this->formAttr['multipart'] ? 'enctype="Multipart/Form-data" ' : '') .
			' action="'. ($this->formAttr['action'] 
							? $this->formAttr['action'] 
							: ($_SERVER['QUERY_STRING']
								? '?'. $_SERVER['QUERY_STRING']
								: '')) .'">';
		if($this->formAttr['hasPage']){
			$tempField = 'page';
			$this->_fields[$tempField] = array('id'=> $this->formAttr['formName'].'page', 'type'=> 'hidden', 'value'=> 1); //$GLOBALS['page']
			$this->_showHidden($tempField);
		}
		$showKey = true;
		if($this->formAttr['action'] && !strcasecmp(substr($this->formAttr['action'], 0, 4), 'http') && $url = parse_url($this->formAttr['action'])){
			if($url['host'] != $_SERVER['SERVER_NAME']){
				//非本地的form提交，不生成key
				$showKey = false;
			}
		}
		if($showKey) $this->_showkey();
	}
	
	/*
	* 显示form尾
	* 输出操作表单的js代码
	*/
	public function end($jsAppend = ''){
		if(!empty($this->_dateInput)){
            //20130816 把这个放到cac的adminjs里面去了，为gzip生效
            //20130816 不能放到cac里，插件的默认年份变为1990，且css文件路径出错
			echo '<script src="ui/cal/WdatePicker.js" language="javascript"></script>';
		}
		$this->_outputJavascript($jsAppend);
		echo '</FORM>';
	}
	
	/*
	* 输出与jQuery及form.js结合的一些javascript语句，处理表单中的date/focus事件
	*/
	private function _outputJavascript(&$jsAppend){
		$str = '';
		if($this->formAttr['noCssEffects']){
			$str .= "formVars.noCssEffects = true;\n";
		}
		$str .=	"$(function(){\n".
				"if(!init) initAll();\n";
		if($jsAppend){
			$str .= $jsAppend. "\n";
		}
		if(!empty($this->_scode))
			$str .= "generateCode('".$this->_scode."');\n";
		if(!empty($this->_dateInput)){
			//load in datepicker css & js?
			//$str .= "$('".$this->_dateInput."').click(function(){WdatePicker();});\n";
			//mod 20130116 为了请假加班可以选择小时
			global $act;
			if( strpos($act, 'managehr') !== false){
				//20130117 $dp.$D 放这里会报错，所以这两句放到addhr里去了
				//$this->_warn($this->_dateInput);
				//$str .= "$('#hr_start_date').click(function(){WdatePicker({dateFmt:'yyyy-MM-dd HH:00:00',lang:'en',maxDate:'#F{$dp.$D(\'hr_end_date\')}'});});\n";
				//$str .= "$('#hr_end_date').click(function(){WdatePicker({dateFmt:'yyyy-MM-dd HH:00:00',lang:'en',minDate:'#F{$dp.$D(\'hr_start_date\')}'});});\n";
			}else{
				$str .= "$('".$this->_dateInput."').click(function(){WdatePicker();});\n";
			}
		}
		if(!$this->formAttr['noFocus'] && $this->_whomFocus){
			$str .= "$('#". $this->_whomFocus ."').focus();\n";
		}
		if($this->formAttr['autoSubmit'] >= 0){
			$str .= "setTimeout(function(){\$('#". $this->formAttr['formName'] ."').trigger('submit');}, ". ($this->formAttr['autoSubmit'] * 1000) .");\n";
		}
		if($this->_hasSelectForm){
			//load in select css & js?
		}
		$str .= "});";
		echo printJs($str);
	}
	
	
	/*
	* 校验form提交是否有效
	*/
	public function check(){
		if(!$this->_checkFormKey()) return false;
		foreach($this->_fields as $key => $field){
			if(!$this->_checkField($key, $field)){
				$this->_whomFocus = $key;
				return false;
			}
		}
		return true;
	}
	
	/*
	* 显示各种类型的form
	*/
	public function show($field, $withHtml = ''){
		if(!isset($this->_fields[$field]) || !is_array($this->_fields[$field])){
			echo '<div class="gray">表单初始化错误，没有找到此定义</div>';
			$this->_debug('表单 '. $field .' 初始化错误，没有找到其定义');
			return false;
		}
		if(!setTrue($this->_fields[$field]['id'])) $this->_fields[$field]['id'] = $field;
		
		switch(strtolower($this->_fields[$field]['type'])){
			case 'text':
			case 'password':
				$this->_showText($field);
				break;
			case 'file':
				$this->_showFile($field);
				break;
			case 'checkbox':
				$this->_showCheckbox($field);
				break;
			case 'radio':
				$this->_showRadio($field);
				break;
			case 'button':
			case 'submit':
			case 'reset':
				$this->_showButton($field);
				break;
			case 'hidden':
				$this->_showHidden($field);
				break;
			case 'image':
				$this->_showImage($field);
				break;
			case 'textarea':
				$this->_showTextarea($field);
				break;
			case 'select':
				$this->_showSelect($field);
				break;
			default:
				$this->_fields[$field]['type'] = 'text';
				$this->_showText($field);
		}
		
		// 寻找在页面载入时获得焦点的输入框，一般即第一个
		if(!$this->formAttr['noFocus'] && empty($this->_whomFocus) && $this->_thisFormIndex == 2 && !setTrue($this->_fields[$field]['readonly']) && !setTrue($this->_fields[$field]['disabled'])){
			$this->_whomFocus = $field;
		}
		
		if(!empty($withHtml)) echo $withHtml;
	}
	
	private function _addIndex(){
		$this->_thisFormIndex++;
		return self::$allFormIndex++;
	}
	
	/*
	* 检查某一项提交表单值是否合法
	*/
	private function _checkField(&$fieldName, &$field){
		//check required.
		if(setTrue($field['disabled'])) return true;
		if(setTrue($field['required'])){
			if(setTrue($field['depend'])){
				$depend = explode('|', $field['depend']);
				if(isset($this->_fields[$depend[0]])){
					if($this->_fields[$depend[0]]['type']=='checkbox'){
						$dependValue = isset($_REQUEST[$depend[0]]) ? $_REQUEST[$depend[0]] : false;
						if(($depend[1]=='nocheck' && $dependValue) || ($depend[1]=='checked' && $dependValue==false)){
							return true;
						}
					}
					//elseif{
					//	其他类型的depend判断
					//}
				}
			}
			
			if(!isset($_REQUEST[$fieldName])){
				$this->_warn('请填写以下必填内容: '. (setTrue($field['title']) ? $field['title'] : $fieldName.'项') .'.');
				return false;
			}
		}else{
			//非必填项，值为空时，直接返回true
			if(!setTrue($_REQUEST[$fieldName])) return true;
		}
		
		//check length.
		if(setTrue($field['minlen'])){
			if($field['type'] != 'checkbox' && mb_strlen($_REQUEST[$fieldName]) < intval($field['minlen'])){
				$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '最少需填写'. $field['minlen'].'个字.');
				return false;
			}
			if($field['type'] == 'checkbox' && ((is_array($_REQUEST[$fieldName]) && count($_REQUEST[$fieldName]) < intval($field['minlen'])) || (!is_array($_REQUEST[$fieldName]) && intval($field['minlen']) > 1))){
				$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '需要最少选中 '. $field['minlen'] .' 项.');
				return false;
			}
		}
		if(setTrue($field['maxlen'])){
			if($field['type'] != 'checkbox' && mb_strlen($_REQUEST[$fieldName]) > intval($field['maxlen'])){
				$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '最多允许填写'. $field['maxlen'].'个字.');
				return false;
			}
			if($field['type'] == 'checkbox' && (is_array($_REQUEST[$fieldName]) && count($_REQUEST[$fieldName]) > intval($field['maxlen']))){
				$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '最多可选中 '. $field['maxlen'] .' 项.');
				return false;
			}
		}
		if(setTrue($field['minlen']) && !setTrue($field['maxlen'])){
			if($field['type'] != 'checkbox' && mb_strlen($_REQUEST[$fieldName]) != intval($field['minlen'])){
				$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '长度必须是'. $field['minlen'].'个字.');
				return false;
			}
			if($field['type'] == 'checkbox' && ((is_array($_REQUEST[$fieldName]) && count($_REQUEST[$fieldName]) != intval($field['minlen'])) || (!is_array($_REQUEST[$fieldName]) && intval($field['minlen']) > 1))){
				$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '长度必须是 '. $field['minlen'] .'个字.');
				return false;
			}
		}
		//check readonly & disabled
		if(setTrue($field['readonly'])) $_REQUEST[$fieldName] = (setTrue($field['value']) ? $field['value'] : '');
		if(setTrue($field['disabled'])) $_REQUEST[$fieldName] = '';
		
		//check each type form field.
		$rtn = false;
		switch($field['type']){
			case 'text':
			case 'password':
				$rtn = $this->_checkText($fieldName, $field);
				break;
			case 'file':
				$rtn = $this->_checkFile($fieldName, $field);
				break;
			case 'checkbox':
				$rtn = $this->_checkCheckbox($fieldName, $field);
				break;
			case 'radio':
				$rtn = $this->_checkRadio($fieldName, $field);
				break;
			case 'textarea':
				$rtn = $this->_checkTextarea($fieldName, $field);
				break;
			case 'select':
				$rtn = $this->_checkSelect($fieldName, $field);
				break;
			default:
				$field['type'] = 'text';
				$rtn = $this->_checkText($fieldName, $field);
		}
		if(!$rtn) {
			return false;
		}
		
		//check restrict
		if(setTrue($field['restrict']) && !$this->_checkRestrict($fieldName, $field)) return false;
		
		//finally
		return true;
	}
	
	/*
	* 检查约束 restrict
	*/
	private function _checkRestrict(&$fieldName, &$field){
		switch($field['restrict']){
			case 'number':
				$ptn = "/^[0-9\-\.]+$/";
                //20130624 输入框value为0时也会进到里面导致报错
                if($_REQUEST[$fieldName] != 0){
                    if(!preg_match($ptn, $_REQUEST[$fieldName])){
                        $this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '只能由数字与符号 .- 组成.');
                        return false;
                    }
                }
				break;
			
			case 'letter':
				$ptn = "/^[A-Za-z]+$/";
				if(!preg_match($ptn, $_REQUEST[$fieldName])){
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '只能由纯英文组成.');
					return false;
				}
				break;
			
			case 'card':
				$ptn = "/^[A-Za-z0-9\-]+$/";
				if(!preg_match($ptn, $_REQUEST[$fieldName])){
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '只能由英文, 数字及-号组成.');
					return false;
				}
				break;
			
			case 'date':
				//mod 20130117 $_REQUEST[$fieldName] 改为了 $_POST[$fieldName]， 因前一个获取不到值
				if(strtotime($_POST[$fieldName]) === false){
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '必须是有效的日期.');
					return false;
				}
				break;
				
			case 'twidcard':
				if(!$this->_checkTwIdCard($_REQUEST[$fieldName])){
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '必须是有效的身份证.');
					return false;
				}
				break;
				
			case 'cnidcard':
				if(!$this->_checkCnIdCard($_REQUEST[$fieldName])){
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '必须是有效的身份证.');
					return false;
				}
				break;	
			
			case 'account':
				$ptn = "/^[A-Za-z0-9\_]+$/";
				if(!preg_match($ptn, $_REQUEST[$fieldName])){
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '只能由英文、数字组成.');
					return false;
				}
				break;
			
			case 'password':
				//$ptn = "/^[A-Za-z0-9\!\@\#\$\*\(\)]+$/";
				$ptn = "/^[A-Za-z0-9]+$/";
				if(!preg_match($ptn, $_REQUEST[$fieldName])){
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '只能由英文、数字组成.'); //及!@#$*()符号
					return false;
				}
				break;
			
			case 'email':
				$ptn = "/^[a-zA-Z0-9_\-.]+@[a-zA-Z0-9_\-.]+\.[a-zA-Z0-9_\-.]+$/";
				if(!preg_match($ptn, $_REQUEST[$fieldName])){
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '必须填写正确的E-mail.');
					return false;
				}
				break;
			
			case 'scode':
				$scode = new My_Scode($fieldName);
				if(!$scode->check($_REQUEST[$fieldName])){
					$this->_warn($GLOBALS['words']['wrongCode']);
					return false;
				}
				break;
				
			case 'ihridername':
				$pattern_c = '/^[\x{4e00}-\x{9fff}]{2,4}$/u';
				$pattern_e = '/^[0-9a-zA-Z][0-9a-zA-Z ]{2,8}[0-9a-zA-Z]$/u';
						
				preg_match($pattern_c, $_REQUEST[$fieldName], $match_c);
				preg_match($pattern_e, $_REQUEST[$fieldName], $match_e);
				if ( (count($match_e) > 0 && count($match_c) == 0) || (count($match_c) > 0 && count($match_e) == 0) ) {
					
				}
				else {
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '必须全角中文2-4字或半型英文4-10字，不可中英文溷搭。');
					return false;
				}
				break;
			
			//判断ID是否已存在，目前只使用在addproduct页面。。。	
			case 'judgexid':
				$rs = false;
				$text = '';
				global $act;
				if( strpos($act, 'addproduct')){
					$text = 'Product ID';
					$rs = mysql_q('select pid from product where pid = ?', $_REQUEST[$fieldName]);
				}elseif( strpos($act, 'addquotation')){
					//clone出来的Product的信息，会在显示warn的时候清除，所以以下还未用到。。。！！！
					$text = 'Quotation NO.';
					$rs = mysql_q('select qid from quotation where qid = ?', $_REQUEST[$fieldName]);
				}elseif( strpos($act, 'addproforma')){
					$text = 'Proforma NO.';
					$rs = mysql_q('select pvid from proforma where pvid = ?', $_REQUEST[$fieldName]);
				}elseif( strpos($act, 'addpurchase')){
					$text = 'Purchase NO.';
					$rs = mysql_q('select pcid from purchase where pcid = ?', $_REQUEST[$fieldName]);
				}elseif( strpos($act, 'addinvoice')){
					$text = 'Invoice NO.';
					$rs = mysql_q('select vid from invoice where vid = ?', $_REQUEST[$fieldName]);
				}
				if($rs){
					$this->_warn('The '.$text.' already exists. Please change another one!');
					return false;
				}
				break;
			
			default:
				$ptn = "/^[A-Za-z0-9]+$/";
				if(!preg_match($ptn, $_REQUEST[$fieldName])){
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '只能由英文、数字组成.');
					return false;
				}
		}
		return true;
	}
	
	
	/*
	* 检查text/pass/number类型的输入框
	*/
	private function _checkText(&$fieldName, &$field){
		if(setTrue($field['compare']) && $_REQUEST[$fieldName] != $_REQUEST[$field['compare']]){
			$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '的值必须与'.
				(setTrue($this->_fields[$field['compare']]['title']) ? $this->_fields[$field['compare']]['title'] : $field['compare'].'项') .'保持一致.');
			return false;
		}
		return true;
	}
	
	/*
	* 显示text/pass/number类型的输入框
	* 接受以下属性:
	* type, class, name, id, title, info, value, size, maxlength, minlen, maxlen, required, restrict, compare, readonly, disabled, addon, keepval(password允许保留值), nostar, nohtml, noTitle
	* restrict 可以是以下值: number, letter, card, date, account, password, email, scode
	*/
	private function _showText(&$field){
		$classAddon = setTrue($this->_fields[$field]['readonly']) ? 'readonly' : (setTrue($this->_fields[$field]['disabled']) ? 'disabled' : '');
		if(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['nohtml'])){
			echo '<div class="set'.(setTrue($this->_fields[$field]['fatherclass']) 
									? ' '.$this->_fields[$field]['fatherclass'] : '').
				'"><label for="'. $field.'" class="formtitle">'. $this->_fields[$field]['title'] .
				(setTrue($this->_fields[$field]['required']) && !setTrue($this->_fields[$field]['nostar']) ? $this->_redStar() : '') .'</label>';
		}elseif(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['noTitle'])){
			echo $this->_fields[$field]['title'].': ';
		}
		
		echo (!setTrue($this->_fields[$field]['nohtml']) && !setTrue($this->_fields[$field]['nohtml']) ? '<div class="formfield">' : '').'<input type="'.$this->_fields[$field]['type'].'" '.
			(setTrue($this->_fields[$field]['class'])
					? 'class="'.$this->_fields[$field]['class'].
						($classAddon ? ' '.$classAddon : '').'" ' 
					: ($classAddon ? 'class="'. $classAddon .'" ' : '')).
			'name="'.$field.'" '.
			'id="'.$this->_fields[$field]['id'].'" '.
			(isset($_REQUEST[$field]) && ($this->_fields[$field]['type'] != 'password' || setTrue($this->_fields[$field]['keepval'])) && !set($this->_fields[$field]['restrict'], 'scode')
					? 'value="'. htmlentities($_REQUEST[$field], ENT_COMPAT, 'UTF-8') .'" '
					: (!isset($_REQUEST[$field]) && isset($this->_fields[$field]['value']) 
						? 'value="'. htmlentities($this->_fields[$field]['value'], ENT_COMPAT, 'UTF-8') .'" ' : '')).
			(setTrue($this->_fields[$field]['size']) ? 'size="'.$this->_fields[$field]['size'].'" ' : '').
			(setTrue($this->_fields[$field]['maxlength']) 
					? 'maxlength="'.$this->_fields[$field]['maxlength'].'" ' 
					: (setTrue($this->_fields[$field]['maxlen'])
							? 'maxlength="'.$this->_fields[$field]['maxlen'].'" ' 
							: (setTrue($this->_fields[$field]['minlen'])
								? 'maxlength="'.$this->_fields[$field]['minlen'].'" ' 
								: ''))).
			(setTrue($this->_fields[$field]['required']) ? 'required="1" ' : '').
			(setTrue($this->_fields[$field]['depend']) ? 'depend="'.$this->_fields[$field]['depend'].'" ' : '').
			(setTrue($this->_fields[$field]['restrict'])
					? 'restrict="'.$this->_fields[$field]['restrict'].'" '
					: ($this->_fields[$field]['type'] != 'text'
							? 'restrict="'. $this->_fields[$field]['type'].'" '
							: '')).
			(setTrue($this->_fields[$field]['compare']) ? ' compare="' .$this->_fields[$field]['compare']. '" ' : '').
			(setTrue($this->_fields[$field]['minlen'])
					? 'strlen="' .$this->_fields[$field]['minlen'].
					(setTrue($this->_fields[$field]['maxlen'])
							? ','.$this->_fields[$field]['maxlen'] : '').'" '
					: '').
			
			(setTrue($this->_fields[$field]['readonly'])
					? 'readonly="readonly" '
					: (setTrue($this->_fields[$field]['disabled'])
						? 'disabled="disabled" '
						: '')).
			(!setTrue($this->_fields[$field]['readonly']) && !setTrue($this->_fields[$field]['disabled'])
					? 'tabindex="'. $this->_addIndex() .'" '
					: 'tabindex="-1" ').
			(setTrue($this->_fields[$field]['addon']) ? $this->_fields[$field]['addon'] : ''). ' />'.
			(!setTrue($this->_fields[$field]['title']) && setTrue($this->_fields[$field]['required']) && !setTrue($this->_fields[$field]['nostar']) ? $this->_redStar() : '').
			(setTrue($this->_fields[$field]['info']) ? '<p class="forminfo">'. $this->_fields[$field]['info'] .'</p>' : '').
			(!setTrue($this->_fields[$field]['nohtml']) ? '</div>' : '');
		if(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['nohtml'])){ echo '</div>'; }
	}
	
	/*
	* 检查file类型的输入框
	*/
	private function _checkFile(&$fieldName, &$field){
		
		return true;
	}
	
	/*
	* 显示File表单
	*/
	private function _showFile(&$field){
		echo 'Notice: File input is unavailable yet.';
	}
	
	/*
	* 检查checkbox类型的输入框
	*/
	private function _checkCheckbox(&$fieldName, &$field){
		$v = array();
		foreach($field['options'] as $option){
			if(is_array($option)){
				if(isset($option[2]) && $option[2] == '1') continue;
				$v[] = setTrue($option[1]) ? $option[1] : $option[0];
			}else{
				$v[] = $option;
			}
		}
        //20150731 checkbox在未选中任何一项的时候，没有post参数，所以额外加了个默认选中的隐藏表单项，但是这里不通过，所以去掉
		/*if(is_array($_REQUEST[$fieldName])){
			foreach($_REQUEST[$fieldName] as $u){
				if(!in_array($u, $v)){
					$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '的值超出允许范围.');
					return false;
				}
			}
		}elseif(!in_array($_REQUEST[$fieldName], $v)){
			$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '的值超出允许范围.');
			return false;
		}*/
		return true;
	}
	
	/*
	* 显示checobox类型的输入框
	* 接受以下属性:
	* type, class, name, id, title, info, minlen, maxlen, required, disabled, addon, options, checked(value)
	* 其中 options 接受以下形式的值
	*		array(title1, title2)					//值
	*		array(array(title1, value1), ...)		//键值对
	*		array(array(title1, value1, 1), ...)	//第3个值表示disabled某个单项
	*		或者以上溷合
	*/
	// 為lux加的三個 addinput , mytype , modid
	private function _showCheckbox(&$field){
		$defVal = isset($_REQUEST[$field]) 
					? $_REQUEST[$field] 
					: (isset($this->_fields[$field]['value'])
						? $this->_fields[$field]['value'] 
						: (isset($this->_fields[$field]['checked'])
							? $this->_fields[$field]['checked'] 
							: false));
		$phpFix = (count($this->_fields[$field]['options']) > 1 ? '[]' : '');
		$tabIndex = (!setTrue($this->_fields[$field]['readonly']) && !setTrue($this->_fields[$field]['disabled']));
		$strlen = (setTrue($this->_fields[$field]['minlen'])
					? $this->_fields[$field]['minlen'].
					(setTrue($this->_fields[$field]['maxlen'])
							? ','.$this->_fields[$field]['maxlen'] : '')
					: (setTrue($this->_fields[$field]['required']) ? '1' : '0'));
		
		if(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['nohtml'])){
			//zjn 11.10.29 为lux改动这里，如要知道原来是什么，去对比以往的代码吧，改得亂七八糟，複雜死了，好痛苦
			echo '<div class="set'.(setTrue($this->_fields[$field]['fatherclass']) 
									? $this->_fields[$field]['fatherclass'] : '').
				'"><label class="formtitle">'. $this->_fields[$field]['title'] .
				($strlen ? $this->_redStar() : '') .'</label>';	
		}elseif(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['noTitle'])){
			echo $this->_fields[$field]['title'].': ';
		}
		
		echo (!setTrue($this->_fields[$field]['nohtml']) ? '<div class="formfield'.(setTrue($this->_fields[$field]['fatherclass']) ? $this->_fields[$field]['fatherclass'] : '').'" id="div'.$field.'">' : '');
		
		if(setTrue($this->_fields[$field]['addinput']) && setTrue($this->_fields[$field]['modid'])){ 
			//要想通用，則要注意特定的格式拼接 x_value
			$rtn = mysql_qone("select ".$this->_fields[$field]['mytype']."_value from goodsform where id = ".$this->_fields[$field]['modid']);	
			if($rtn[$this->_fields[$field]['mytype']."_value"] != ''){
				$rtn_array = explode('|', $rtn[$this->_fields[$field]['mytype']."_value"]);
			}
		}
		$value_i = 0;
		foreach($this->_fields[$field]['options'] as $option){
			$dis = false;
			if(is_array($option)){
				$n = $option[0];
				$v = isset($option[1]) ? $option[1] : $option[0];
				if(isset($option[2]) && $option[2] == '1') $dis = true;
			}else{
				$n = $v = $option;
			}
			
			if(!setTrue($this->_fields[$field]['nohtml'])){
				if(setTrue($this->_fields[$field]['addinput'])) echo '<div class="luxdiv"><img src="../images/helper1.gif" alt="刷新" width="14" height="14" id="'.htmlentities($v, ENT_COMPAT, 'UTF-8').'" />';
				echo '<label '. 
				(setTrue($this->_fields[$field]['class']) ? 'class="'. $this->_fields[$field]['class'] .'" ' : '').
				(setTrue($this->_fields[$field]['addon']) ? $this->_fields[$field]['addon'] : '').'>';
			}
			
			echo '<input type="checkbox" '.
				'name="'. $field . $phpFix .'" '.
				'id="'.$this->_fields[$field]['id'].'" '.
				//'title="'. $n .'" '.
				'class="'. htmlentities($v, ENT_COMPAT, 'UTF-8') .'" '.
				'value="'. htmlentities($v, ENT_COMPAT, 'UTF-8') .'" '.
				(is_array($defVal) && in_array($v, $defVal)
				? 'checked="checked" '
				: (!is_array($defVal) && !strcmp($v, $defVal)
					? 'checked="checked" '
					: '')).
				($tabIndex ? ($dis ? 'tabindex="-1" disabled="disabled" ' : 'tabindex="'. $this->_addIndex() .'" ') : 'tabindex="-1" ').
				' /> '. $n;
			
			if(!setTrue($this->_fields[$field]['nohtml'])) echo '</label>';
			if(setTrue($this->_fields[$field]['addinput'])){
				$value = '';
				if( (is_array($defVal) && in_array($v, $defVal)) || (!is_array($defVal) && !strcmp($v, $defVal)) ){
					if(isset($rtn_array)){	
						$value = $rtn_array[$value_i];
						$value_i++;
					}
				}
			}
			if(setTrue($this->_fields[$field]['addinput'])) echo ' <input strlen="1,10" maxlength="10" restrict="number" disabled="disabled" class="luxinput" type="text" value="'.$value.'"name="'.htmlentities($v, ENT_COMPAT, 'UTF-8').'" /></div>';
			$this->luxindex++;	
		}
		
		echo ' <input type="hidden" id="'. $this->_fields[$field]['id'] .'_len" value="'.$strlen.'" />'.
			(!setTrue($this->_fields[$field]['title']) && $strlen ? $this->_redStar(!setTrue($this->_fields[$field]['nohtml'])) : '').
			(setTrue($this->_fields[$field]['info']) ? '<p class="forminfo">'. $this->_fields[$field]['info'] .'</p>' : '').
			(!setTrue($this->_fields[$field]['nohtml']) ? '</div>' : '');
		if(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['nohtml'])){ echo '</div>'; }
	}
	
	/*
	* 检查radio类型的输入框
	*/
	private function _checkRadio(&$fieldName, &$field){
		$v = array();
		$originField = $field['options'];

		foreach($field['options'] as $option){
			if(is_array($option)){
				if(isset($option[2]) && $option[2] == '1') continue;
				$v[] = setTrue($option[1]) ? $option[1] : $option[0];//mod by zjn 2012.6.18 setTrue($option[1]) 改为 (setTrue($option[1]) || $option[1] === 0)//又改回去了。。。还是不用value为0的了
			}else{
				$v[] = $option;
			}
		}
		// 如果未设定原始option(有可能用ajax生成，或者本来就没有值)，则跳过此检查
		if(is_array($_REQUEST[$fieldName]) || ($originField && !in_array($_REQUEST[$fieldName], $v))){
			$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '的值超出允许范围.');
			return false;
		}
		return true;
	}
	
	/*
	* 显示radio类型的输入框
	* 接受以下属性:
	* type, class, name, id, title, info, required, disabled, addon, options, checked(value)
	* 其中 options 接受以下形式的值
	*		array(title1, title2)					//值
	*		array(array(title1, value1), ...)		//键值对
	*		array(array(title1, value1, 1), ...)	//第3个值表示disabled某个单项
	*		或者以上溷合
	*/
	private function _showRadio(&$field){
		$defVal = isset($_REQUEST[$field]) 
					? $_REQUEST[$field] 
					: (isset($this->_fields[$field]['value'])
						? $this->_fields[$field]['value'] 
						: (isset($this->_fields[$field]['checked'])
							? $this->_fields[$field]['checked'] 
							: false));
		$readonly = setTrue($this->_fields[$field]['readonly']);
		$disabled = setTrue($this->_fields[$field]['disabled']);
		
		if(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['nohtml'])){
			echo '<div class="set'.(setTrue($this->_fields[$field]['fatherclass']) 
									? ' '.$this->_fields[$field]['fatherclass'] : '').
				'"><label class="formtitle">'. $this->_fields[$field]['title'] .
				(setTrue($this->_fields[$field]['required']) ? $this->_redStar() : '') .'</label>';
		}elseif(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['noTitle'])){
			echo $this->_fields[$field]['title'].': ';
		}
		
		echo (!setTrue($this->_fields[$field]['nohtml']) ? '<div class="formfield" id="div'.$field.'">' : '');
		foreach($this->_fields[$field]['options'] as $option){
			$dis = false;
			if(is_array($option)){
				$n = $option[0];
				$v = isset($option[1]) ? $option[1] : $option[0];
				if(isset($option[2]) && $option[2] == '1') $dis = true;
			}else{
				$n = $v = $option;
			}
			if(!setTrue($this->_fields[$field]['nohtml'])){
				echo '<label '. 
				(setTrue($this->_fields[$field]['class']) ? 'class="'. $this->_fields[$field]['class'] .'" ' : '').
				(setTrue($this->_fields[$field]['addon']) ? $this->_fields[$field]['addon'] : '').'>';
			}
			echo '<input type="radio" '.
				'name="'. $field .'" '.
				'id="'.$this->_fields[$field]['id'].'" '.
				//'title="'. $n .'" '.
				'value="'. htmlentities($v, ENT_COMPAT, 'UTF-8') .'" '.
				(!strcmp($v, $defVal)
					? 'checked="checked" '
					: '').
				(!($readonly || $disabled)
					? ($dis ? 'tabindex="-1" ' : 'tabindex="'. $this->_addIndex() .'" ')
					: ($readonly 
					   ? 'readonly="readonly" ' 
					   : ($disabled 
						  ? 'disabled="disabled" ' 
						  : '')) .'tabindex="-1" ').
				
				' /> '. $n;
			if(!setTrue($this->_fields[$field]['nohtml'])) echo '</label>';
		}
		echo ' <input type="hidden" id="'. $this->_fields[$field]['id'] .'_len" value="'.
			(setTrue($this->_fields[$field]['required']) ? '1' : '0').'" />'.
			(!setTrue($this->_fields[$field]['title']) && setTrue($this->_fields[$field]['required']) 
				? $this->_redStar(!setTrue($this->_fields[$field]['nohtml'])) : '').
			(setTrue($this->_fields[$field]['info']) ? '<p class="forminfo">'. $this->_fields[$field]['info'] .'</p>' : '').
			(!setTrue($this->_fields[$field]['nohtml']) ? '</div>' : '');
		if(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['nohtml'])){ echo '</div>'; }
	}
	
	/*
	* 显示各种按钮
	* 接受以下属性:
	* type, class, name, id, title, info, disabled, addon, click
	*/
	private function _showButton(&$field){
		if(!setTrue($this->_fields[$field]['nohtml'])){
			echo '<div class="buttonfield'.(setTrue($this->_fields[$field]['fatherclass']) 
									? ' '.$this->_fields[$field]['fatherclass'] : '').
				'" id="div'.$field.'">';
		}
		echo '<input type="'. $this->_fields[$field]['type'] .'" '.
			(setTrue($this->_fields[$field]['class']) ? 'class="'. $this->_fields[$field]['class'] .'" ' : '').
			'name="'. $field .'" '.
			'id="'.$this->_fields[$field]['id'].'" '.
			'value="'. htmlentities($this->_fields[$field]['value'], ENT_COMPAT, 'UTF-8') .'" '.
			(setTrue($this->_fields[$field]['disabled']) 
					? 'disabled="disabled" tabindex="-1" '

					: 'tabindex="'. $this->_addIndex() .'" ').
			(setTrue($this->_fields[$field]['addon']) ? $this->_fields[$field]['addon'] : '').
			(setTrue($this->_fields[$field]['click']) ? 'onClick="'. $this->_fields[$field]['click'] .'" ' : '').
			'/>'.
			(setTrue($this->_fields[$field]['info']) ? '<p class="forminfo">'. $this->_fields[$field]['info'] .'</p>' : '');
		if(!setTrue($this->_fields[$field]['nohtml'])) echo '</div>';
	}
	
	/*
	* 输出隐藏域
	* 接受以下属性:
	* type, name, id, value, (add by zjn)disabled
	*/
	private function _showHidden(&$field){
		//mod by zjn 加上了接收disabled的属性
		echo '<input type="hidden" name="'. $field .'" '.
			'id="'.$this->_fields[$field]['id'].'" '.
			'value="'. htmlentities($this->_fields[$field]['value'], ENT_COMPAT, 'UTF-8') .'" '.
					(setTrue($this->_fields[$field]['disabled']) 
					? 'disabled="disabled" tabindex="-1" '
					: 'tabindex="'. $this->_addIndex() .'" ').' />';
	}
	
	/*
	* 显示图片按钮
	* 接受以下属性:
	* type, name, id, title, info, disabled, addon, click, width, height
	*/
	private function _showImage(&$field){
		if(!setTrue($this->_fields[$field]['nohtml'])){
			echo '<div class="buttonfield'.(setTrue($this->_fields[$field]['fatherclass']) 
									? ' '.$this->_fields[$field]['fatherclass'] : '').
				'" id="div'.$field.'">';
		}
		echo '<input type="image" name="'. $field .'" '.
			'id="'.$this->_fields[$field]['id'].'" '.
			'src="'. (setTrue($this->_fields[$field]['src'])
					? $this->_fields[$field]['src'] 
					: '').'" '.
			(setTrue($this->_fields[$field]['disabled']) 
					? 'disabled="disabled" tabindex="-1" '
					: 'tabindex="'. $this->_addIndex() .'" ').
			(setTrue($this->_fields[$field]['addon']) ? $this->_fields[$field]['addon'] : '').
			(setTrue($this->_fields[$field]['width']) ? 'width="'.$this->_fields[$field]['width'].'" ' : '').
			(setTrue($this->_fields[$field]['height']) ? 'height="'.$this->_fields[$field]['height'].'" ' : '').
			(setTrue($this->_fields[$field]['click']) ? 'onClick="'. $this->_fields[$field]['click'] .'" ' : '').
			'" alt="'. (setTrue($this->_fields[$field]['title']) 
					? $this->_fields[$field]['title']
					: '提交') .'" />'.
			(setTrue($this->_fields[$field]['info']) ? '<p class="forminfo">'. $this->_fields[$field]['info'] .'</p>' : '');
		if(!setTrue($this->_fields[$field]['nohtml'])) echo '</div>';
	}
	
	/*
	* 检查textarea类型的输入框
	*/
	private function _checkTextarea(&$fieldName, &$field){
		return true;
	}
	
	/*
	* 显示textarea类型的输入框
	* 接受以下属性:
	* type, class, name, id, title, info, title, rows, cols, minlen, maxlen, value, required, readonly, disabled, addon
	*/
	private function _showTextarea(&$field){
		//add by zjn textarea也加了disabled的樣式
		$classAddon = setTrue($this->_fields[$field]['readonly']) ? 'readonly' : (setTrue($this->_fields[$field]['disabled']) ? 'disabled' : '');
		if(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['nohtml'])){
			echo '<div class="set'.(setTrue($this->_fields[$field]['fatherclass']) 
									? ' '.$this->_fields[$field]['fatherclass'] : '').
				'"><label for="'. $field.'" class="formtitle">'. $this->_fields[$field]['title'] .
				(setTrue($this->_fields[$field]['required']) && !setTrue($this->_fields[$field]['nostar']) ? $this->_redStar() : '') .'</label>';
		}elseif(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['noTitle'])){
			echo $this->_fields[$field]['title'].': ';
		}
		//mod by zjn 這裡的class也有改動
		echo (!setTrue($this->_fields[$field]['nohtml']) ? '<div class="formfield" id="div'.$field.'">' : '').'<textarea '.
			(setTrue($this->_fields[$field]['class']) ? 'class="'. $this->_fields[$field]['class'] . ($classAddon ? ' '.$classAddon : '').'" ' : ($classAddon ? 'class="'. $classAddon .'" ' : '')).
			'name="'. $field .'" '.
			'id="'.$this->_fields[$field]['id'].'" '.
			//(setTrue($this->_fields[$field]['title']) ? 'title="'.$this->_fields[$field]['title'].'" ' : '').			//textarea's title?
			(setTrue($this->_fields[$field]['required']) ? 'required="1" ' : '').
			(setTrue($this->_fields[$field]['rows']) ? 'rows="'.$this->_fields[$field]['rows'].'" ' : '').
			(setTrue($this->_fields[$field]['cols']) ? 'cols="'.$this->_fields[$field]['cols'].'" ' : '').
			(setTrue($this->_fields[$field]['disabled']) 
					? 'disabled="disabled" tabindex="-1" '
					: (setTrue($this->_fields[$field]['readonly']) 
						? 'readonly="readonly" tabindex="-1" '
						: 'tabindex="'. $this->_addIndex() .'" ')).
			(setTrue($this->_fields[$field]['addon']) ? $this->_fields[$field]['addon'] : '').
			(setTrue($this->_fields[$field]['minlen'])
					? 'strlen="' .$this->_fields[$field]['minlen'].
					(setTrue($this->_fields[$field]['maxlen'])
							? ','.$this->_fields[$field]['maxlen'] : '').'" '
					: '').
			' >'.
			(isset($_REQUEST[$field]) 
					? $_REQUEST[$field]
					: (!isset($_REQUEST[$field]) && isset($this->_fields[$field]['value']) 
						? $this->_fields[$field]['value'] : '')).
			'</textarea>'.
			(!setTrue($this->_fields[$field]['title']) && setTrue($this->_fields[$field]['required']) && !setTrue($this->_fields[$field]['nostar']) ? $this->_redStar() : '').
			(setTrue($this->_fields[$field]['info']) ? '<p class="forminfo">'. $this->_fields[$field]['info'] .'</p>' : '').
			(!setTrue($this->_fields[$field]['nohtml']) ? '</div>' : '');
		if(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['nohtml'])){ echo '</div>'; }
	}
	
	/*
	* 检查Select类型的输入框
	*/
	private function _checkSelect(&$fieldName, &$field){
		/*
		select可能是关联的，所以要允许options为空
		
		if(!isset($field['options'])){
			$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '的列表值不能为空.');
			return false;
		}
		*/
		if(!setTrue($field['options'])){
			return true;
		}
		$v = array();
		$originField = $field['options'];
		if(isset($field['all'])){
			$field['options'][] = $field['all'];
		}
		foreach($field['options'] as $option){
			if(is_array($option)){
				if(isset($option[2]) && $option[2] == '1') continue;
				$v[] = isset($option[1]) ? $option[1] : $option[0];
			}else{
				$v[] = $option;
			}
		}
		// 如果select不是required，并且没有post值或值为空，则跳过合法值的不判断
		if(!setTrue($field['required']) && !setTrue($_REQUEST[$fieldName])){
			return true;
		}
		// 如果未设定原始option(有可能用ajax生成，或者本来就没有值)，则跳过此检查
		// mod by zjn 因為ajax動態生成的，所以後面提交的和原來的option內容有可能不同，所以去掉下面的檢測
		/*
		if(is_array($_REQUEST[$fieldName]) || ($originField && !in_array($_REQUEST[$fieldName], $v))){
			$this->_warn((setTrue($field['title']) ? $field['title'] : $fieldName.'项'). '的值超出允许范围.');
			return false;
		}
		*/
		return true;
	}
	
	/*
	* 显示下拉菜单类型
	* 接受以下属性:
	* type, class, name, id, title, info, title, options, required, disabled, addon, all, selected(value), nostar
	* 其中 options 接受以下形式的值
	*		array(title1, title2)					//值
	*		array(array(title1, value1), ...)		//键值对
	*		array(array(title1, value1, 1), ...)	//第3个值表示disabled某个单项
	*		或者以上溷合
	*/
	private function _showSelect(&$field){
		$defVal = isset($_REQUEST[$field]) 
					? $_REQUEST[$field] 
					: (isset($this->_fields[$field]['value'])
						? $this->_fields[$field]['value'] 
						: (isset($this->_fields[$field]['selected'])
							? $this->_fields[$field]['selected'] 
							: false));
		if(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['nohtml'])){
			echo '<div class="set'.(setTrue($this->_fields[$field]['fatherclass']) 
									? ' '.$this->_fields[$field]['fatherclass'] : '').
				'"><label for="'. $field.'" class="formtitle">'. $this->_fields[$field]['title'] .
				(setTrue($this->_fields[$field]['required']) && !setTrue($this->_fields[$field]['nostar']) ? $this->_redStar() : '') .'</label>';
		}elseif(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['noTitle'])){
			echo $this->_fields[$field]['title'].': ';
		}
		
		echo (!setTrue($this->_fields[$field]['nohtml']) ? '<div class="selectfield" id="div'.$field.'">' : '').'<select '.
			(setTrue($this->_fields[$field]['class']) ? 'class="'. $this->_fields[$field]['class'] .'" ' : '').
			'size="1" name="'. $field .'" '.
			'id="'.$this->_fields[$field]['id'].'" '.
			//(setTrue($this->_fields[$field]['title']) ? 'title="'.$this->_fields[$field]['title'].'" ' : '').		//select's title ??
			(setTrue($this->_fields[$field]['required']) ? 'required="1" ' : '').
			(setTrue($this->_fields[$field]['addon']) ? $this->_fields[$field]['addon'] : '').
			(setTrue($this->_fields[$field]['disabled']) 
					? 'disabled="disabled" tabindex="-1" '
					: 'tabindex="'. $this->_addIndex() .'" ').
			'>';
		if(isset($this->_fields[$field]['all'])){
			if(is_array($this->_fields[$field]['all'])){
				echo '<option value="'. htmlentities($this->_fields[$field]['all'][1], ENT_COMPAT, 'UTF-8') .'">'. $this->_fields[$field]['all'][0] .'</option>';
			}else{
				echo '<option value="">'. $this->_fields[$field]['all'] .'</option>';
			}
		}else{
			echo '<option value="">- select -</option>';
		}
		
		if($this->_fields[$field]['options']){
			foreach($this->_fields[$field]['options'] as $option){
				$dis = false;
				if(is_array($option)){
					$n = $option[0];
					$v = isset($option[1]) ? $option[1] : $option[0];
					if(isset($option[2]) && $option[2] == '1') $dis = true;
				}else{
					$n = $v = $option;
				}
				echo '<option value="'. htmlentities($v, ENT_COMPAT, 'UTF-8') .'" '.
					(!strcmp($v, $defVal)
						? 'selected="selected" '
						: '').
					($dis ? 'disabled="disabled" ' : '').
					'>'. $n .'</option>';
			}
		}
		echo '</select>'.
			(!setTrue($this->_fields[$field]['title']) && setTrue($this->_fields[$field]['required']) && !setTrue($this->_fields[$field]['nostar']) ? $this->_redStar() : '').
			(setTrue($this->_fields[$field]['info']) ? '<p class="forminfo">'. $this->_fields[$field]['info'] .'</p>' : '').
			(!setTrue($this->_fields[$field]['nohtml']) ? '</div>' : '');
		if(setTrue($this->_fields[$field]['title']) && !setTrue($this->_fields[$field]['nohtml'])){ echo '</div>'; }
	}
	
	// 向FORM输出此校验值，使用XXTEA加密
	private function _showKey() {
		$this->_fields[$this->formAttr['formKeyName']] = array('id'=> $this->formAttr['formKeyName'], 'type'=> 'hidden', 'value'=>$this->_formKey);
		$this->_showHidden($this->formAttr['formKeyName']);
		echo "\n";
	}
	
	/*
	判断表单提交是否合法
	需与 showKey() 组合使用
	判断主要关係到3个因素：
		1. 域名 $act 浏览器资料 用户IP SessionID 等信息的MD5签名是否相符
		2. 表单生成的时间是否在xx分钟以内（在formAttr 中指定）
		3. 验证码（如果有）是否正确
	Get提交在事实上已经被抛弃（虽然还是支持Get方法提交表单），
	因为签名受SessionID/url等因素所限制，如果使用Url直接Get提交，MD5签名必然不相符，于是提交会失败。
	*/
	private function _checkFormKey() {

        //20130625 不管了，问题很多，提交表单不方便。不管安全性了
        //20130626 直接在最开头return会有问题，必填信息的输入提示会直接在页面显示出来
        //return true;

		if(!setTrue($_REQUEST[$this->formAttr['formKeyName']])) {
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				$this->_warn($GLOBALS['words']['badRequest'].'1');
			}
			return false;
		} else {

            //20130626
            return true;

			if($this->_checkKey()
				&& ($this->formAttr['method'] == 'GET'
					|| ($this->formAttr['method'] == $_SERVER['REQUEST_METHOD']
					&& (empty($_SERVER['HTTP_REFERER']) 
						|| preg_replace("/(http|https):\/\/([^\:\/]+).*/i", "\\2", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST']))))) {
				return true;
			} else {
				$this->_warn($GLOBALS['words']['badRequest'].'2');
				$this->_debug(preg_replace("/(http|https):\/\/([^\:\/]+).*/i", "\\2", $_SERVER['HTTP_REFERER']));
				$this->_debug(preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST']));
				
				return false;
			}
		}
	}

	/*
	 * 生成一段用以校验FORM是否产生自服务器的序号，以及FORM产生时间
	 * 使用：在html中，将此值放在form隐藏域值中
	 *
	 * 注意：如使用此key验证，则FORM只能提交至本页
	 *		而提交给其他页面或接受其他页面提交时，都将报错
	 */
	private function _generateKey(){
		$this->_signature	= md5($_SERVER["SERVER_NAME"] . $GLOBALS['act'] . ENCRYPT_FORM_KEY . session_id()
							. $_SERVER['HTTP_USER_AGENT'] . $GLOBALS['ip']);
		$this->_debug('generate: ' . substr($this->_signature, 6, 21));
		$randomKey	= md5(randomString(10));
		$this->_formKey = XXTEA::encrypt(substr($this->_signature, 6, 21) . substr($randomKey, 4, 11)
						. time() . '|' . substr($randomKey, 17, 8));
	}
	
	/* 检查formkey
	 * 所包含的签名值是否合法
	 * 所包含的时间信息是否显示表单超时
	 */
	private function _checkKey(){
		$decrypt = trim(XXTEA::decrypt($_REQUEST[$this->formAttr['formKeyName']]));
		$timeStop = strpos($decrypt, '|');
		
		if(substr($decrypt, 0, 21) != substr($this->_signature, 6, 21) || false === $timeStop){
			$this->_debug('decode: '. $decrypt);
			$this->_debug('wrong form key or no timestop.');
			return false;
		}elseif(!$formTime = intval(substr($decrypt, 32, $timeStop - 32))){
			$this->_debug('cant get formtime.');
			return false;
		}elseif(time() - $formTime > $this->formAttr['lifeTime']){
			$this->_debug('form timeout.');
			return false;
		}elseif(time() - $formTime < $this->formAttr['freTime']){
			$this->_debug('refresh action so quickly.');
			sleep($this->formAttr['freTime']);
			return true;
		}else{
			return true;
		}
	}
	
	/* 检查台湾身份证 */
	private function _checkTwIdCard($id){
		$city = array(1,10,19,28,37,46,55,64,39,73,82,2,11,20,48,29,38,47,56,65,74,83,21,3,12,30);
		$id = strtoupper($id);
		if(!preg_match("/^[A-Z]{1}[1-2]{1}[0-9]{7,8}$/", $id)){
			return false;
		} else {
			$total = $city[ord($id[0])-65];
			for($i=1; $i<=8;$i++){
				$total += intval($id[$i]) * (9-$i);
			}
			$total += (strlen($id) == 10 ? intval($id[9]) : 0);
			return ($total % 10 == 0);
		}
	}
	
	/* 检查中国大陆身份证 */
	private function _checkCnIdCard($id){
		return $this->_checkIdcard($id);
	}
	private function _checkIdcard($idcard){
		if(strlen($idcard) == 18){
			return $this->_idcard_checksum18($idcard);
		}
		else{
			return false;
		}
	}	
	// 18位身份证校验码有效性检查
	private function _idcard_checksum18($idcard){
		$idcard_base = substr($idcard, 0, 17);
		if ($this->_idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){
			return false;
		}else{
			return true;
		}
	}
	// 计算身份证校验码，根据国家标准GB 11643-1999
	private function _idcard_verify_number($idcard_base){
		if(strlen($idcard_base) != 17){
			return false;
		}
		//加权因子
		$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
		
		//校验码对应值
		$verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
		$checksum = 0;
		for ($i = 0; $i < strlen($idcard_base); $i++){
			$checksum += substr($idcard_base, $i, 1) * $factor[$i];
		}
		$mod = $checksum % 11;
		$verify_number = $verify_number_list[$mod];
		return $verify_number; 
	}	

	
	private function _redStar($float = false){
		return '<h6 class="required'. ($float ? ' float' : '') .'">*</h6>';
	}
	
	private function _debug($var, $go = '', $auto = 0){
		$GLOBALS['myerror']->info($var, $go, $auto);
	}
	
	private function _error($var, $go = '', $auto = 0){
		$GLOBALS['myerror']->error($var, $go, $auto);
	}
	
	private function _warn($var, $go = '', $auto = 0){
		$GLOBALS['myerror']->warn($var, $go, $auto);
	}
}

?>