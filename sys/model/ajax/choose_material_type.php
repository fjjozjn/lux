<?
/*
if(isset($_GET['type'])){
	$rs = $mysql->q('select m_id, m_name from material where m_type = ?', $_GET['type']);
	if($rs){
		$rtn = $mysql->fetch();
		$str = '{';
		foreach($rtn as $v){
			$str .= '"' . $v['m_id'] .'":"'. $v['m_id'] . '：' . $v['m_name'] .'"' . ',';
		}
		echo $str. '}';
	}else{
		echo '{"0":"没有记录"}';
	}
}else{
	echo '{"0":"系统错误"}';
}
*/
if(isset($_GET['type'])){
	$rs = $mysql->q('select m_id, m_name from material where m_type = ?', unescape($_GET['type']));
	if($rs){
		$rtn = $mysql->fetch();
		$str = '';
		for($i = 0; $i < count($rtn); $i++){
			$str .= ($i == count($rtn) - 1)?($rtn[$i]['m_id'].':'.$rtn[$i]['m_name']):($rtn[$i]['m_id'].':'.$rtn[$i]['m_name'] . "|");
		}
		echo $str;
	}else{
		echo 'no-1';
	}
}else{
	echo 'no-2';
}