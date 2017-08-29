<?php
require($_SERVER['DOCUMENT_ROOT'] . '\in7\global.php');
require($_SERVER['DOCUMENT_ROOT'] . '\sys\in38\global_admin.php');
if(isset($_GET['id']) && $_GET['id'] != ''){
	$result = $mysql->qone('select * from goodsform where id = ?', $_GET['id']);
	
	$m_id_array = explode('|', $result['m_id']);
	$m_value_array = explode('|', $result['m_value']);
	$t_id_array = explode('|', $result['t_id']);
	$t_value_array = explode('|', $result['t_value']);
	$g_process_array = explode('|', $result['g_process']);
	$electroplate_array = explode('|', $result['electroplate']);
	$electroplate_thick_array = explode('|', $result['electroplate_thick']);
	$other_array = explode('|', $result['other']);
	
	$all_array = array($g_process_array, $electroplate_array, $electroplate_thick_array, $other_array);
	
	$image_size = '';
	if($result['mysql_photo'] && $result['mysql_photo'] != ''){
		$arr = getimagesize('../'.$result['mysql_photo']);
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(450, 280, $pic_width, $pic_height);
	}

	header('Content-type: application/vnd.ms-excel; charset=UTF-8');
	header('Content-Disposition: filename='.time().'.xls');
?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<body>
<h1 align="center">工廠產品物料清單</h1>
<table width="1000" border="1">
  <tr height="60">
    <td width="130"><b>产品编号</b></td>
    <td colspan="5" align="center"><?=$result['g_id']?></td>
    <td colspan="4" rowspan="5" align="center"><img src="<?=$result['mysql_photo']?>" align="middle" width="<?=$image_size['width']?>" height="<?=$image_size['height']?>"/></img></td>
  </tr>
  <tr height="60">
    <td><b>类别</b></td>
    <td colspan="5" align="center"><?=$result['g_type']?></td>
  </tr>
  <tr height="60">
    <td><b>底材用料</b></td>
    <td align="center" width="70"><?=$result['g_material']?></td>
    <td width="100"><b>尺码</b></td>
    <td colspan="3" align="center"><?=$result['g_size']?></td>
  </tr>
  <tr height="60">
    <td><b>成品总石数</b></td>
    <td align="center"><?=$result['g_gem_num']?><b>粒</b></td>
    <td><b>铸件</b></td>
    <td colspan="3" align="center"><?=$result['g_cast']?><b>件</b></td>
  </tr>
  <tr height="60">
    <td><b>电镀</b></td>
    <td align="center"><?=$result['g_plating']?></td>
    <td><b>重量</b></td>
    <td colspan="3" align="center"><?=$result['g_weight']?><b>克</b></td>
  </tr>
  <tr>
    <td width="100"><b>物料编号</b></td>
    <td width="100"><b>名称</b></td>
    <td width="100"><b>规格颜色</b></td>
    <td width="50"><b>类别</b></td>
    <td width="70"><b>单价</b></td>
    <td width="80"><b>个数/重量</b></td>
    <td width="125"><b>件工序号</b></td>
    <td width="125"><b>工序名称</b></td>
    <td width="125"><b>工价</b></td>
    <td width="125"><b>工时</b></td>
  </tr>
<?
for($i = 0; $i < 13; $i++){
	if(isset($m_id_array[$i])){
		$m_rows = $mysql->qone('select * from material where m_id = ?', $m_id_array[$i]);
		echo '<tr><td>' . $m_rows['m_id'] . '</td><td>' . $m_rows['m_name'] . '</td><td>' . $m_rows['m_color'] . '</td><td>' . $m_rows['m_type'] . '</td><td>' . $m_rows['m_price'] . '</td><td>' . ($m_rows['m_unit'].':'.$m_value_array[$i]) . '</td>';
	}else{
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	}
	if(isset($t_id_array[$i])){
		$t_rows = $mysql->qone('select * from task where t_id = ?', $t_id_array[$i]);
		echo '<td>' . $t_rows['t_id'] . '</td><td>' . $t_rows['t_name'] . '</td><td>' . $t_rows['t_price'] . '</td><td>' . $t_value_array[$i] . '</td></tr>';			
	}else{
		echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
	}
}
for($i = 13; $i < 21; $i++){
	if($i == 13){
		if(isset($m_id_array[$i])){
			echo '<tr><td>' . $m_rows['m_id'] . '</td><td>' . $m_rows['m_name'] . '</td><td>' . $m_rows['m_color'] . '</td><td>' . $m_rows['m_type'] . '</td><td>' . $m_rows['m_price'] . '</td><td>' . $m_value_array[$i] . '</td><td colspan="4" rowspan="8">&nbsp;</td></tr>';
		}else{
			echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td colspan="4" rowspan="8"><table><tr><td width="100"><b>工序</b></td><td width="100"><b>电镀</b></td><td width="100"><b>电镀厚度</b></td><td width="100"><b>其他</b></td></tr>';
			for($j = 1; $j <= $select_max; $j++){
				echo '<tr>';
				for($k = 0; $k < 4; $k++){
					echo '<td>';
					if(isset($allinarray[$k][$j-1][0])){
						if(in_array($j, $all_array[$k])){
							echo $allinarray[$k][$j-1][0].'√';
						}else{
							echo $allinarray[$k][$j-1][0];
						}	
					}
					echo '</td>';	
				}
				echo '</tr>';
			}
			echo '</table></td></tr>';
		}
	}else{
		if(isset($m_id_array[$i])){
			echo '<tr><td>' . $m_rows['m_id'] . '</td><td>' . $m_rows['m_name'] . '</td><td>' . $m_rows['m_color'] . '</td><td>' . $m_rows['m_type'] . '</td><td>' . $m_rows['m_price'] . '</td><td>' . $m_value_array[$i] . '</td></tr>';
		}else{
			echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
		}		
	}
}
?>  
  <tr>
    <td colspan="10"><b>人工</b>：<?=$result['p_labour']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>电镀</b>：<?=$result['p_plate']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>工件</b>：<?=$result['p_workpiece']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>石料</b>：<?=$result['p_stone']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>配件</b>：<?=$result['p_parts']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>其他</b>：<?=$result['p_other']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>其他2</b>：<?=$result['p_other2']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>利润</b>：<?=$result['p_profit']?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>合计</b>：<?=$result['p_total']?> </td>
    </tr>
</table>
<p><b>经手人</b>：<?=$result['people_h']?>&nbsp;&nbsp;&nbsp;&nbsp; <b>审核</b>：<?=$result['people_a']?> </p>

</body>
</html>

<?
}else{
	die('系統故障(1)');	
}
?>