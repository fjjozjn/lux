<?
//为了帮她们批量上传product图片，这个脚本是把图片字段填上，这样上传图片后就能生效
/*
 *    1、图片名必须与pid相同，且有统一个后缀格式
 *    2、需要上传的图片需放在同一个文件夹下
 */

require('../in7/global.php');

$start_date = '2013-02-28';
$end_date = '2013-03-01';

$rs = $mysql->q('select pid, photos, in_date from product where in_date between ? and ?', $start_date, $end_date);
if($rs){
	$rtn = $mysql->fetch();
	foreach($rtn as $v){
		if($v['photos'] == ''){
			echo $v['pid'].' '.$v['in_date'].'<br />';
			$mysql->q('update product set photos = ? where pid = ?', $v['pid'].'.JPG', $v['pid']);	
		}
	}
}