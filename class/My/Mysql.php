<?php

if (!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

/*
* Mysql 操作基类

修改:	2009-06-24	only for PHP v5.3
		2009-07-03	multiquery处中使用了mysqli_fetch_all. 
					对返回数组放宽限制, 使用了默认的MYSQLI_BOTH
		2009-07-22	删除了multiquery的支持...因为没有可应用性
					删除了单件模式，为了应付需要操作多个db的场合(比如gocs)
		2009-08-22	一些BUG
		2009-09-03	一个与SQL_CALC_FOUND_ROWS有关的bug
		2009-09-28	增加了普通Query的DEBUG语句, 修改了一处bug(line: 186)
		2009-11-05	去掉版本检查，因正式机出错，无法解决，只能用旧的mysqli_connect替换mysqli_real_connect。以后再找机会恢复
		2009-11-09	去掉mysqli_connect检查，此检查移至global.php中
		2010-03-26	将_close()由private改为public，因为有时候需要在页面执行过程中释放mysql连接
		2011-06-21	修改_queryType，避免先执行sp后执行q时，查询不能正确执行
		2011-06-22	去掉php5.3兼容代码(?:)，修改部分私有变量与私有函数名，修改查询类型判断方式，增加_initVar()重置查询变量，修改错误常量的显示方式，立即回收$result资源
		2011-06-30	去掉@@autocommit侦测
		
需要改进	1. 在用到%的场合下应用 ? 通配 ----- 或考虑使用mysqli自带的prepare功能
		2. 在sql被查询前就应用_recordLimit限制查询记录的数量 (之前是select之后foreach的时候才用_recordLimit)

* 
*/

define('MY_QT_UNKNOWN', 0);
define('MY_QT_SELECTPAGES', 5);
define('MY_QT_SELECT', 4);
define('MY_QT_UPDATE', 3);
define('MY_QT_INSERT', 2);
define('MY_QT_SP', 1);
define('MY_RECORD_LIMIT', 100);

final class My_Mysql{

	private $_conn;						// mysqli resource
	private $_rtn;						// 查询结果记录条数
	private $_totalRecord;				// 查询总记录条数
	private $_recordRows;				// 查询结果集数组
	private $_insertedId;				// insert id
	private $_dbInfo;					// 最近一次db连接所使用的资料
	private $_magicGpc;					// 是否打开了magic_quotes_gpc
	private $_sybaseGpc;				// 是否打开了magic_sybase_gpc
	private $_recordLimit = MY_RECORD_LIMIT;		// 为避免某些sql语句不严谨而导致select海量数据设定一个最多记录限制，可以被setLimit()改变，0 为不限制
	private $_replacer = '?';			// 在prepare查询中起替换作用的符号, 默认为"?"
	private $_reReplacer = '?#A?';		// 被使用在prepare中，用以替换$_replacer的字符.
	
	private $_qt;						// 查询语句的类型
	
	/*
	  架构函数
	  初始化变量
	*/
    public function __construct($dbInfo){
		$this->_magicGpc = false;//get_magic_quotes_gpc();
		$this->_sybaseGpc = ini_get('magic_quotes_sybase');
		//$this->_dbInfo = array('host' => NULL, 'user' => NULL, 'passwd' => NULL, 'database' => NULL, 'port' => NULL, 'socket' => NULL, 'charset' => 'utf8');
		
		if(!$dbInfo['host'] || !$dbInfo['user'] || !$dbInfo['passwd'] || !$dbInfo['database']){
			$this->_error($this->_output('systemError'));
			$this->_debug('DB CONNECTION FAILED! NEED MORE DB INFO!');
			return;
		}
		
		$this->_dbInfo = $dbInfo;
		//$this->_connectToDB(); //延迟连接DB，发生Query时才连接
    }
	
	/*
	* 解构函数
	* 关闭mysql连接
	*/
	function __destruct(){
		$this->close();
	}
	
	//连接数据库
	private function _connectToDB(){
		
		/*
		//新连接方式，为了方便得到matched rows而非affected rows.
		$this->_conn = mysqli_init();
		//mysqli_options($this->_conn, MYSQLI_INIT_COMMAND, "SET AUTOCOMMIT=0");
		mysqli_options($this->_conn, MYSQLI_OPT_CONNECT_TIMEOUT, 15);
		@mysqli_real_connect($this->_conn,
							$this->_dbInfo['host'], 
							$this->_dbInfo['user'],
							$this->_dbInfo['passwd'],
							$this->_dbInfo['database'],
							$this->_dbInfo['port'],
							$this->_dbInfo['socket'],
							MYSQLI_CLIENT_FOUND_ROWS);
		*/

		//旧连接方式
        $this->_debug($this->_dbInfo);
		$this->_conn = mysqli_connect($this->_dbInfo['host'], $this->_dbInfo['user'], $this->_dbInfo['passwd'], $this->_dbInfo['database'], $this->_dbInfo['port'], $this->_dbInfo['socket']);
		if(!$this->_conn){
			$this->_error($this->_output('systemError'));
			$this->_debug('DB Connecting Failure!');
		}else{
			mysqli_set_charset($this->_conn, $this->_dbInfo['charset']);
			//mysqli_autocommit($this->_conn, false);
		}
		/*
		if ($rs = mysqli_query($this->_conn, "SELECT @@autocommit")) {
			$row = mysqli_fetch_row($rs);
			$this->_debug("Autocommit is ". $row[0]);
			mysqli_free_result($rs);
		}
		*/
		return $this->_conn ? true : false;
	}
	
	/* 在已经有mysql连接时，可使用已有的连接 */
	public function gotConnect(&$comingConn){
		if($comingConn && is_resource($comingConn)){
			$this->_conn = $comingConn;
			return true;
		}else{
			$this->_error($this->_output('systemError'));
			$this->_debug('Connect to database failed!');
			return false;
		}
	}
	
	// 设定select结果最大记录数限制
	public function setLimit($n){
		$this->_recordLimit = $n;
	}
	
	/*
	* 主函数
	method(sql, [arg1, arg2, ...])
	method可能是:
		1. q[uery]
		2. qone
		3. sp
	
	只接受两种sql:
		1. 未带有argN参数, sql 中值部分只可以包含数字
		2. 带有argN参数, sql 中不允许带有'/"
	
		其中情况1如果必须带有字串，则sql 必须以 force:开头.
		如 force:select * from table where name like '%adfa%'
	
	argN 在放入sql之前将会被trim，首尾处的空格等字符将被清除.
	
	返回值：
	q : 返回所影响的记录数
	qone : 返回所读取的首条记录，或所影响的记录数
	sp : sp中返回的值，或者所影响的记录数
	
	fetch记录的区别：
	q & qone : fetch(1) 第一条记录（如果是select查询）
	sp : fetch(1)
	*/
	public function __call($method, $arguments) {
		if(!in_array($method, array('sp', 'q', 'query', 'qone'))){
			$this->_error($this->_output('queryError'));
			$this->_debug('UNKNOWN QUERY ACTION!');
			return false;
		}
		$this->_initVar();
		$sql = $arguments[0];
		$forceQuotes = (substr($sql, 0, 6) == 'force:');
		if($forceQuotes) $sql = substr($sql, 6);
		$this->_qt = $this->_queryType($sql, $method);
		
		if(!$this->_conn && $this->_dbInfo['host'] && $this->_dbInfo['user'] && $this->_dbInfo['passwd'] && $this->_dbInfo['database']){
			if(!$this->_connectToDB())
				return false;
			/*
			if(!$this->_connectToDB()){
				$this->_error($this->_output('systemError'));
				$this->_debug('DB Connecting Failure!');
				return false;
			}
			*/
		}
		
		if(count($arguments) == 1){
			if(!$forceQuotes && (strpos($sql, "'") !== false || strpos($sql, '"') !== false)){
				$this->_error($this->_output('queryError'));
				$this->_debug('NO ARGUMENT, NO QUOTES. ON: '.$sql);
				return false;
			}
		}elseif((strpos($sql, "'") !== false || strpos($sql, '"') !== false)){
			$this->_error($this->_output('queryError'));
			$this->_debug('GOT ARGUMENTS, NO QUOTES! ON: '.$sql);
			return false;
		}else{
			$sql = $this->_prepare($arguments);
		}
		
		$rs = ($this->_qt == MY_QT_SP ? mysqli_multi_query($this->_conn, $sql) : mysqli_query($this->_conn, $sql));
		if(!$rs){
			//不抛出错误，让页面自行管理逻辑
			//$this->_error(set($GLOBALS['words']['queryError']) ? $GLOBALS['words']['queryError'] : 'Query Error!');
			
			$this->_debug(array('QUERY ERROR', $sql, mysqli_error($this->_conn)));
			$tmpRtn = false;
		}else{
			$this->_debug('the SQL statement is : '. $sql);
			if($this->_qt == MY_QT_SP){
				$tmpRtn = $this->_queryStoreProcedure();
			}else{
				$tmpRtn = $this->_queryNormalAndSingleRow($rs, $method);
			}
			if($this->_qt >= MY_QT_SELECT) mysqli_free_result($rs);
		}
		return $tmpRtn;
    }
	
	private function _queryStoreProcedure(){
		do {
			if ($result = mysqli_store_result($this->_conn)){
				if($row = mysqli_fetch_all($result, MYSQLI_ASSOC)){
					$this->_recordRows[$this->_rtn++] = $row;
				}else{
					$this->_recordRows[$this->_rtn++] = false;
				}
				mysqli_free_result($result);
			}
		} while(mysqli_next_result($this->_conn));
		$this->_debug($this->_recordRows);
		//sp必须返回一个名为'nums'的标志, 表示操作成功或失败, 或返回受影响的行数, 以供读取
		if(count($this->_recordRows) > 0){
			return intval($this->_recordRows[$this->_rtn-1][0]['nums']);
		}else{
			return $this->_rtn;
		}
	}
	
	private function _queryNormalAndSingleRow(&$rs, &$method){
		if($this->_qt >= MY_QT_SELECT){
			$this->_rtn = mysqli_num_rows($rs);
			if($this->_rtn > 0){
				while ($rows = mysqli_fetch_assoc ($rs)){
					$this->_recordRows[] = $rows;
				}
				if($this->_qt == MY_QT_SELECTPAGES){
					$rs2 = mysqli_query($this->_conn, 'SELECT FOUND_ROWS()');
					if($rs2){
						$row = mysqli_fetch_row($rs2);
						$this->_totalRecord = $row[0];
					}else{
						$this->_totalRecord = $this->_rtn;
					}
					$this->_debug('total:'. $this->_totalRecord);
				}
				$this->_debug($this->_recordRows);
				if($method == 'qone')
					return $this->_recordRows[0];
				else
					return $this->_qt == MY_QT_SELECTPAGES ? $this->_totalRecord : $this->_rtn;
			}else{
				$this->_debug('NO ROWS FOUND!');
				return false;
			}
		}else{
			$this->_rtn = mysqli_affected_rows($this->_conn);
			if($this->_qt == MY_QT_INSERT){
				$this->_insertedId = mysqli_insert_id($this->_conn);
				if($this->_rtn == 1 && $this->_insertedId){
					$this->_debug('insert id:'. $this->_insertedId);
					return $this->_insertedId;
				}else{
					$this->_debug('insert rows:'. $this->_rtn);
					return $this->_rtn;
				}
			}else{
				$this->_debug('effect:'. $this->_rtn);
				return $this->_rtn;
			}
		}
	}
	
	// 取得插入记录的自增长ID.
	public function id(){
		return $this->_insertedId ? $this->_insertedId : 0;
    }
	
	/*
	 * 取得记录集. 
	 * 参数自1计起, 比如取第1行，则传入$pickone = 1
	 * mainRecord是用来应付sp中有多行select查询的场合
	 * 比如要取第2个select查询的第一条记录： fetch(1,2)
	 * 普通场合（包括普通查询、SP中只含有一个select，都属于普通场合），使用fetch(1)即可取到第一条记录
	 */
    public function fetch($pickone = 0, $mainRecord = 0){
		if(!is_array($this->_recordRows) || count($this->_recordRows) == 0){
			$this->_debug('YOUR FETCH NO RESULT!');
			return false;
		}elseif(($this->_qt != MY_QT_SP && $pickone && count($this->_recordRows) < $pickone)){
			$pickone = count($this->_recordRows);
		}elseif($this->_qt == MY_QT_SP){
			if($mainRecord){
				if(count($this->_recordRows) >= $mainRecord){
					if(!is_array($this->_recordRows[$mainRecord-1])) return $this->_recordRows[$mainRecord-1];
					if($pickone > 0 && count($this->_recordRows[$mainRecord-1]) < $pickone){
						$pickone = count($this->_recordRows[$mainRecord-1]);
					}
				}else{
					$mainRecord = count($this->_recordRows);
					if(!is_array($this->_recordRows[$mainRecord-1])) $this->_recordRows[$mainRecord-1];
					if($pickone > 0 && count($this->_recordRows[$mainRecord-1]) < $pickone){
						$pickone = count($this->_recordRows[$mainRecord-1]);
					}
				}
			}else{
				if(!is_array($this->_recordRows[0])) return $this->_recordRows[0];
				//为返回第一个select的第N条记录，计算pickone是否大于第一条记录的总数
				/*
				if($pickone && count($this->_recordRows[0]) < $pickone){
					$pickone = count($this->_recordRows[0]);
				}
				*/
				
				//返回第N个select的第1条记录，计算pickone是否大于select查询的总数
				if($pickone && count($this->_recordRows) < $pickone){
					$pickone = count($this->_recordRows);
				}
			}
		}
		if($this->_qt == MY_QT_SP){
			if($mainRecord > 0){
				if($pickone){
					return @$this->_recordRows[$mainRecord-1][$pickone-1];
				}else{
					return @$this->_recordRows[$mainRecord-1];
				}
			}elseif($pickone){
				//返回第N个select的第1条记录
				return @$this->_recordRows[$pickone-1][0];
				
				//返回第一个select的第N条记录
				//return @$this->_recordRows[0][$pickone-1];
			}else{
				return $this->_recordRows;
			}
		}elseif($pickone){
			return @$this->_recordRows[$pickone-1];
		}else{
			return $this->_recordRows;
		}
    }
	
	public function total(){
		return $this->_totalRecord ? $this->_totalRecord : 0;
    }
	
	// 根据输入的参数，生成安全的sql语句
	private function _prepare(&$arguments){
		$arguments[0] = str_replace($this->_replacer, $this->_reReplacer, $arguments[0]);
		for($i=1; $i < count($arguments); $i++){
			//if($arguments[$i] !== false) $arguments[0] = $this->_replaceOnce($this->_reReplacer, $this->_addSlashes(trim($arguments[$i])), $arguments[0]);
			//trim去掉首位的空格換行等字符不利於此系統保存信息，安全性先放一邊吧。。。 mod by zjn
			if($arguments[$i] !== false) $arguments[0] = $this->_replaceOnce($this->_reReplacer, $this->_addSlashes($arguments[$i]), $arguments[0]);
		}
		return $arguments[0];
	}
	
	private function _replaceOnce($needle, $replace, $haystack) {
		$pos = strpos($haystack, $needle);
		if ($pos === false) {
			return $haystack;
		}
		return substr_replace($haystack, $replace, $pos, strlen($needle));
	}

	
	// 关闭DB连接
    public function close(){
        if($this->_conn && substr($this->_dbInfo['host'], 0, 2) !== 'p:'){
			@mysqli_close($this->_conn);
			$this->_conn = NULL;
		}
    }
	
	private function _addSlashes($string) {
		if($this->_magicGpc){
			if($this->_sybaseGpc){
				$string = str_replace("''", "'", $string);
			}else{
				$string = stripslashes($string);
			}
		}
		$string = "'".mysqli_real_escape_string($this->_conn, $string)."'";
		return $string;
	}
	
	// 返回需要执行的SQL语句类型
	private function _queryType(&$sql, &$method){
		if($method == 'sp'){
			return MY_QT_SP;
		}else{
			switch(strtoupper(substr($sql, 0, 6))){
				case 'SELECT':
					return (strpos($sql, 'SQL_CALC_FOUND_ROWS') !== false ? MY_QT_SELECTPAGES : MY_QT_SELECT);
				case 'INSERT':
					return MY_QT_INSERT;
				default:
					return MY_QT_UPDATE;
			}
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
	
	private function _output($constStr){
		return isset($GLOBALS['words'][$constStr]) ? $GLOBALS['words'][$constStr] : 'Error Occured: '. $constStr;
	}
	
	private function _initVar(){
		$this->_rtn = $this->_qt = $this->_insertedId = $this->_totalRecord = 0;
		$this->_recordRows = array();
		$this->_recordLimit = MY_RECORD_LIMIT;
	}

}

// 在非全局环境下，使用此函数执行查询
function mysql_q()
{
	$arguments = func_get_args();
	return call_user_func_array(array($GLOBALS['mysql'], 'q'), $arguments);
}

// 在非全局环境下，使用此函数执行查询
function mysql_sp()
{
	$arguments = func_get_args();
	return call_user_func_array(array($GLOBALS['mysql'], 'sp'), $arguments);
}

// 在非全局环境下，使用此函数执行查询
function mysql_qone()
{
	$arguments = func_get_args();
	return call_user_func_array(array($GLOBALS['mysql'], 'qone'), $arguments);
}

// 在非全局环境下，使用此函数执行查询
function mysql_id()
{
	return $GLOBALS['mysql']->id();
}

// 在非全局环境下，使用此函数执行查询
function mysql_fetch()
{
	$arguments = func_get_args();
	return call_user_func_array(array($GLOBALS['mysql'], 'fetch'), $arguments);
}

// 在非全局环境下，使用此函数执行查询
function mysql_total()
{
	return $GLOBALS['mysql']->total();
}
