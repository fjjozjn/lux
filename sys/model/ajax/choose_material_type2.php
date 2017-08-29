<?
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