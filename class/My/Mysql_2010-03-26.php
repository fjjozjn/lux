<?php

if (!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

/*
* Mysql 操作基類

修改:	2009-06-24	only for PHP v5.3
		2009-07-03	multiquery處中使用了mysqli_fetch_all. 
					對返回數組放寬限制, 使用了默認的MYSQLI_BOTH
		2009-07-22	刪除了multiquery的支持...因為沒有可應用性
					刪除了單件模式，為了應付需要操作多個db的場合(比如gocs)
		2009-08-22	一些BUG
		2009-09-03	一個與SQL_CALC_FOUND_ROWS有關的bug
		2009-09-28	增加了普通Query的DEBUG語句, 修改了一處bug(line: 186)
		2009-11-05	去掉版本檢查，因正式機出錯，無法解決，只能用舊的mysqli_connect替換mysqli_real_connect。以後再找機會恢復
		2009-11-09	去掉mysqli_connect檢查，此檢查移至global.php中
		2010-03-26	將_close()由private改為public，因為有時候需要在頁面執行過程中釋放mysql連接
		
需要改進	1. 驗證sp類型查詢獲取sp返回狀態是否正常工作
		2. 在用到%的場合下應用 ? 通配變量 ----- 或考慮使用mysqli自帶的prepare功能

* 
*/
final class My_Mysql{

	private $_conn;						// mysqli connect resource
	private $_rtn;						// 查詢結果記錄條數
	private $_rtnTotal;					// 查詢總記錄條數
	private $_rowsArray;				// 查詢結果集數組
	private $_insertId;					// insert id
	//private $_phpVer;					// php版本 數字
	//private $_mysqlVer;				// mysql版本 數字
	private $_dbInfo;					// 最近一次db連接所使用的資料
	private $_magicGpc;					// 是否打開了magic_quotes_gpc
	private $_sybaseGpc;					// 是否打開了magic_sybase_gpc
	private $_recordLimit = 1000;		// 為避免某些sql語句不嚴謹而導致select了海量數據
										// 設定一個記錄數限制
										// 0 為不限制
	private $_prefix = '';				// 暫未啟用
	private $_replacer = '?';			// 在prepare查詢中起替換作用的符號, 默認并且建議為"?"
	private $_reReplacer = '?#A?';		// 被使用在prepare中，用以替換$_replacer的字符.
	
	private $_gotResult; 				// 查詢操作類型：是否普通select查詢
	private $_gotTotal;					// 查詢操作類型：是否帶有limit的查詢
	private $_gotNewid;					// 查詢操作類型：是否插入查詢
	private $_gotEffect; 				// 查詢操作類型：是否更新、刪除或其他查詢
	private $_spQuery;					// 查詢操作類型：是否sp查詢
	
	/*
	  架構函數
	  初始化變量
	*/
    public function __construct($dbInfo){
		$this->_magicGpc = get_magic_quotes_gpc();
		$this->_sybaseGpc = ini_get('magic_quotes_sybase');
		//$this->_dbInfo = array('host' => NULL, 'user' => NULL, 'passwd' => NULL, 'database' => NULL, 'port' => NULL, 'socket' => NULL, 'charset' => 'utf8');
		
		if(!$dbInfo['host'] || !$dbInfo['user'] || !$dbInfo['passwd'] || !$dbInfo['database']){
			$this->_error(set($GLOBALS['words']['systemError']) ? $GLOBALS['words']['systemError'] : 'System configure Error!');
			$this->_debug('DB CONNECTION FAILED! NEED MORE DB INFO!');
			return;
		}
		
		$this->_dbInfo = $dbInfo;
		//$this->connect();
    }
	
	/*
	* 解構函數
	* 關閉mysql連接
	*/
	function __destruct(){
		$this->close();
	}
	
	//連接數據庫
	private function connect(){
		
//		//新連接方式，為了方便得到matched rows而非affected rows.
//		$this->_conn = mysqli_init();
//		//mysqli_options($this->_conn, MYSQLI_INIT_COMMAND, "SET AUTOCOMMIT=0");
//		mysqli_options($this->_conn, MYSQLI_OPT_CONNECT_TIMEOUT, 15);
//		@mysqli_real_connect($this->_conn, $this->_dbInfo['host'], $this->_dbInfo['user'], $this->_dbInfo['passwd'], $this->_dbInfo['database'], $this->_dbInfo['port'], $this->_dbInfo['socket'], MYSQLI_CLIENT_FOUND_ROWS);		

		//舊連接方式
		$this->_conn = mysqli_connect($this->_dbInfo['host'], $this->_dbInfo['user'], $this->_dbInfo['passwd'], $this->_dbInfo['database'], $this->_dbInfo['port'], $this->_dbInfo['socket']);
		if(!$this->_conn){
			$this->_error(set($GLOBALS['words']['systemError']) ? $GLOBALS['words']['systemError'] : 'System configure Error!');
			$this->_debug('DB Connecting Failure!');
		}else{
			//$this->_mysqlVer = mysqli_get_server_version($this->_conn);
			mysqli_set_charset($this->_conn, $this->_dbInfo['charset']);
			//mysqli_autocommit($this->_conn, false);
		}
		
		return $this->_conn ? true : false;
	}
	
	/* 兼容函數，在已經有mysql連接的地址下，可以使用已有的連接 */
	public function gotConnect(&$comingConn){
		if($comingConn && is_resource($comingConn)){
			$this->_conn = $comingConn;
			return true;
		}else{
			$this->_error(set($GLOBALS['words']['systemError']) ? $GLOBALS['words']['systemError'] : 'System configure Error!');
			$this->_debug('Connect to database failed!');
			return false;
		}
	}
	
	// 設定select結果最大記錄數限制
	public function limit($n){
		$this->_recordLimit = $n;
	}
	
	/*
	* 主函數
	method(sql, [arg1, arg2, ...])
	method可能是:
		1. q[uery]
		2. qone
		3. sp
	
	只接受兩種sql:
		1. 未帶有argN參數, sql 中值部分只可以包含數字
		2. 帶有argN參數, sql 中不允許帶有'/"
	
		其中情況1如果必須帶有字串，則sql 必須以 force:開頭.
		如 force:select * from table where name like '%adfa%'
	
	argN 在放入sql之前將會被trim，首尾處的空格等字符將被清除.
	
	返回值：
	q : 返回所影響的記錄數
	qone : 返回所讀取的首條記錄，或所影響的記錄數
	sp : sp中返回的值，或者所影響的記錄數
	
	fetch記錄的區別：
	q & qone : fetch(1) 第一條記錄（如果是select查詢）
	sp : fetch(1)
	*/
	public function __call($method, $arguments) {
		if(!in_array($method, array('sp', 'q', 'query', 'qone'))){
			$this->_error(set($GLOBALS['words']['queryError']) ? $GLOBALS['words']['queryError'] : 'System Error!');
			$this->_debug('UNKNOWN QUERY ACTION!');
			return false;
		}
		$sql = $arguments[0];
		$forceQuotes = false ?: substr($sql, 0, 6) == 'force:';
		if($forceQuotes) $sql = substr($sql, 6);
		if($method == 'sp'){
			$this->_spQuery = true;
		}else{
			$this->_queryType($sql);
		}
		
		if(!$this->_conn && $this->_dbInfo['host'] && $this->_dbInfo['user'] && $this->_dbInfo['passwd'] && $this->_dbInfo['database']){
			if(!$this->connect())
				return false;
			/*
			if(!$this->connect()){
				$this->_error(set($GLOBALS['words']['systemError']) ? $GLOBALS['words']['systemError'] : 'System configure Error!');
				$this->_debug('DB Connecting Failure!');
				return false;
			}
			*/
		}
		
		if(count($arguments) == 1){
			if(!$forceQuotes && (strpos($sql, "'") !== false || strpos($sql, '"') !== false)){
				$this->_error(set($GLOBALS['words']['queryError']) ? $GLOBALS['words']['queryError'] : 'System Error!');
				$this->_debug('NO ARGUMENT, NO QUOTES. ON: '.$sql);
				return false;
			}
		}elseif((strpos($sql, "'") !== false || strpos($sql, '"') !== false)){
			$this->_error(set($GLOBALS['words']['queryError']) ? $GLOBALS['words']['queryError'] : 'System Error!');
			$this->_debug('GOT ARGUMENTS, NO QUOTES! ON: '.$sql);
			return false;
		}else{
			$sql = $this->_prepare($arguments);
		}
		
		$this->_rowsArray = array();
		$this->_rtn = $this->_insertId = 0;
		$rs = ($this->_spQuery) ? mysqli_multi_query($this->_conn, $sql) : mysqli_query($this->_conn, $sql);
		if(!$rs){
			//$this->_error(set($GLOBALS['words']['queryError']) ? $GLOBALS['words']['queryError'] : 'Query Error!');
			$this->_debug(array('QUERY ERROR', $sql, mysqli_error($this->_conn)));
			return false;
		}else{
			$this->_debug('the SQL statement is : '. $sql);
		}
		if($this->_spQuery){
			return $this->queryStoreProcedure();
		}else{
			return $this->queryNormalAndSingleRow($rs, $method);
		}
    }
	
	private function queryStoreProcedure(){
		do {
			if ($result = mysqli_store_result($this->_conn)){
				if($row = mysqli_fetch_all($result, MYSQLI_ASSOC)){
					$this->_rowsArray[$this->_rtn++] = $row;
				}else{
					$this->_rowsArray[$this->_rtn++] = false;
				}
				mysqli_free_result($result);
			}
		} while(mysqli_next_result($this->_conn));
		$this->_debug($this->_rowsArray);
		//sp必須返回一個名为'nums'的標誌, 表示操作成功或失敗, 或返回受影响的行数, 以供讀取
		if(count($this->_rowsArray) > 0){
			return intval($this->_rowsArray[$this->_rtn-1][0]['nums']);
		}else{
			return $this->_rtn;
		}
	}
	
	private function queryNormalAndSingleRow(&$rs, &$method){
		if($this->_gotResult){
			$this->_rtn = mysqli_num_rows($rs);
			if($this->_rtn > 0){
				$i = 0;
				while ($rows = mysqli_fetch_assoc ($rs)){
					$this->_rowsArray[$i++] = $rows;
					if($this->_recordLimit == $i) break;
				}
				if($this->_gotTotal){
					$rs = mysqli_query($this->_conn, 'SELECT FOUND_ROWS()');
					if($rs){
						$num = mysqli_fetch_row($rs);
						$this->_rtnTotal = $num[0];
					}else{
						$this->_rtnTotal = $this->_rtn;
					}
					$this->_debug('total:'. $this->_rtnTotal);
				}
				$this->_debug($this->_rowsArray);
				if($method == 'qone')
					return $this->_rowsArray[0];
				else
					return $this->_gotTotal ? $this->_rtnTotal : $this->_rtn;
			}else{
				$this->_debug('NO ROWS FOUND!');
				return false;
			}
		}else{
			$this->_rtn = mysqli_affected_rows($this->_conn);
			$this->_debug('effect:'. $this->_rtn);
			if($this->_gotNewid){
				$this->_insertId = mysqli_insert_id($this->_conn);
				if($this->_rtn == 1 && $this->_insertId){
					$this->_debug('insert id:'. $this->_insertId);
					return $this->_insertId;
				}else{
					$this->_debug('insertrows:'. $this->_rtn);
					return $this->_rtn;
				}
			}else{
				//$this->_debug('NO ROWS AFFECTED!');
				return $this->_rtn;
			}
		}
	}
	
	// 取得插入記錄的自增長ID.
	public function id(){
		return 0 ?: $this->_insertId;
    }
	
	/*
	 * 取得記錄集. 
	 * 參數自1計起, 比如取第1行，則傳入$pickone = 1
	 * mainRecord是用來應付sp中有多行select查詢的場合
	 * 比如要取第2個select查詢的第一條記錄： fetch(1,2)
	 * 普通場合（包括普通查詢、SP中只含有一個select，都屬於普通場合），使用fetch(1)即可取到第一條記錄
	 */
    public function fetch($pickone = 0, $mainRecord = 0){
		if(!is_array($this->_rowsArray) || count($this->_rowsArray) == 0){
			$this->_debug('YOUR FETCH NO RESULT!');
			return false;
		}elseif((!$this->_spQuery && $pickone && count($this->_rowsArray) < $pickone)){
			$pickone = count($this->_rowsArray);
		}elseif($this->_spQuery){
			if($mainRecord){
				if(count($this->_rowsArray) >= $mainRecord){
					if(!is_array($this->_rowsArray[$mainRecord-1])) return $this->_rowsArray[$mainRecord-1];
					if($pickone > 0 && count($this->_rowsArray[$mainRecord-1]) < $pickone){
						$pickone = count($this->_rowsArray[$mainRecord-1]);
					}
				}else{
					$mainRecord = count($this->_rowsArray);
					if(!is_array($this->_rowsArray[$mainRecord-1])) $this->_rowsArray[$mainRecord-1];
					if($pickone > 0 && count($this->_rowsArray[$mainRecord-1]) < $pickone){
						$pickone = count($this->_rowsArray[$mainRecord-1]);
					}
				}
			}else{
				if(!is_array($this->_rowsArray[0])) return $this->_rowsArray[0];
				//為返回第一個select的第N條記錄，計算pickone是否大於第一條記錄的總數
				/*
				if($pickone && count($this->_rowsArray[0]) < $pickone){
					$pickone = count($this->_rowsArray[0]);
				}
				*/
				
				//返回第N個select的第1條記錄，計算pickone是否大於select查詢的總數
				if($pickone && count($this->_rowsArray) < $pickone){
					$pickone = count($this->_rowsArray);
				}
			}
		}
		if($this->_spQuery){
			if($mainRecord > 0){
				if($pickone){
					return @$this->_rowsArray[$mainRecord-1][$pickone-1];
				}else{
					return @$this->_rowsArray[$mainRecord-1];
				}
			}elseif($pickone){
				//返回第N個select的第1條記錄
				return @$this->_rowsArray[$pickone-1][0];
				
				//返回第一個select的第N條記錄
				//return @$this->_rowsArray[0][$pickone-1];
			}else{
				return $this->_rowsArray;
			}
		}elseif($pickone){
			return @$this->_rowsArray[$pickone-1];
		}else{
			return $this->_rowsArray;
		}
    }
	
	public function total(){
		return 0 ?: $this->_rtnTotal;
    }
	
	// 根據輸入的參數，生成安全的sql語句
	private function _prepare(&$arguments){
		$arguments[0] = str_replace($this->_replacer, $this->_reReplacer, $arguments[0]);
		for($i=1; $i < count($arguments); $i++){
			if($arguments[$i] !== false) $arguments[0] = $this->_replaceOnce($this->_reReplacer, $this->_addSlashes(trim($arguments[$i])), $arguments[0]);
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

	
	// 關閉DB連接
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
	
	// 返回需要執行的SQL語句類型
	private function _queryType($sql){
		$this->_gotResult = $this->_gotTotal = $this->_gotNewid = $this->_gotEffect = false;
		switch(strtolower(substr($sql, 0, 4))){
			case 'sele':
				//select sql
				$this->_gotResult = true;
				if(strpos($sql, 'SQL_CALC_FOUND_ROWS') !== false){
					// can get total record number
					$this->_gotTotal = true;
				}
				break;
			case 'inse':
				//insert sql
				$this->_gotNewid = true;
				break;
			default:
				//others, like update, delete, etc.
				$this->_gotEffect = true;
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

// 在非全局环境下，使用此函數執行查詢
function mysql_q()
{
	$arguments = func_get_args();
	return call_user_func_array(array($GLOBALS['mysql'], 'q'), $arguments);
}

// 在非全局环境下，使用此函數執行查詢
function mysql_sp()
{
	$arguments = func_get_args();
	return call_user_func_array(array($GLOBALS['mysql'], 'sp'), $arguments);
}

// 在非全局环境下，使用此函數執行查詢
function mysql_qone()
{
	$arguments = func_get_args();
	return call_user_func_array(array($GLOBALS['mysql'], 'qone'), $arguments);
}

// 在非全局环境下，使用此函數執行查詢
function mysql_id()
{
	return $GLOBALS['mysql']->id();
}

// 在非全局环境下，使用此函數執行查詢
function mysql_fetch()
{
	$arguments = func_get_args();
	return call_user_func_array(array($GLOBALS['mysql'], 'fetch'), $arguments);
}

// 在非全局环境下，使用此函數執行查詢
function mysql_total()
{
	return $GLOBALS['mysql']->total();
}

