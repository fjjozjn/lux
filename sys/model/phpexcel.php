<?
require($_SERVER['DOCUMENT_ROOT'] . '\in7\global.php');
require($_SERVER['DOCUMENT_ROOT'] . '\sys\in38\global_admin.php');

date_default_timezone_set('Asia/ShangHai');
/** 
 * 以下是使用示例，对于以 //// 开头的行是不同的可选方式，请根据实际需要 
 * 打开对应行的注释。 
 * 如果使用 Excel5 ，输出的内容应该是GBK编码。 
 */  
require_once '../../PHPExcel/Classes/PHPExcel.php';  
  
  
if(isset($_GET['id']) && $_GET['id'] != ''){
	$result = $mysql->qone('select * from goodsform where id = ?', $_GET['id']);
	
	$m_id_array = explode('|', $result['m_id']);
	$m_detail_array = explode('|', $result['m_detail']);
	$m_value_array = explode('|', $result['m_value']);
	$t_id_array = explode('|', $result['t_id']);
	$t_detail_array = explode('|', $result['t_detail']);
	$t_value_array = explode('|', $result['t_value']);
	$g_process_array = explode('|', $result['g_process']);
	$electroplate_array = explode('|', $result['electroplate']);
	$electroplate_thick_array = explode('|', $result['electroplate_thick']);
	$other_array = explode('|', $result['other']);
	
	$all_array = array($g_process_array, $electroplate_array, $electroplate_thick_array, $other_array);

  
	// uncomment  
	////require_once '../../PHPExcel/Classes/PHPExcel/Writer/Excel5.php';    // 用于其他低版本xls  
	// or  
	////require_once '../../PHPExcel/Classes/PHPExcel/Writer/Excel2007.php'; // 用于 excel-2007 格式  
	  
	// 创建一个处理对象实例  
	$objExcel = new PHPExcel();  
	  
	// 创建文件格式写入对象实例, uncomment  
	$objWriter = new PHPExcel_Writer_Excel5($objExcel);    // 用于其他版本格式  
	// or  
	//$objWriter = new PHPExcel_Writer_Excel2007($objExcel); // 用于 2007 格式  
	//$objWriter->setOffice2003Compatibility(true);  
	
	
	//*************************************  
	//设置当前的sheet索引，用于后续的内容操作。  
	//一般只有在使用多个sheet的时候才需要显示调用。  
	//缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0  
	$objExcel->setActiveSheetIndex(0);  
	  
	  
	$objActSheet = $objExcel->getActiveSheet();
	
	//合并单元格  
	$objActSheet->mergeCells('A1:J1');
	
	//由PHPExcel根据传入内容自动判断单元格内容类型  
	$objActSheet->setCellValue('A1', '工廠產品物料清單');  // 字符串内容  
	
	$objActSheet->setCellValue('A2', '产品编号：');
	$objActSheet->setCellValue('B2', $result['g_id']);
	
	$objActSheet->setCellValue('A3', '类别：');
	$objActSheet->setCellValue('B3', $result['g_type']);
	
	$objActSheet->setCellValue('A4', '底材用料：');
	$objActSheet->setCellValue('B4', $result['g_material']);
	
	$objActSheet->setCellValue('A5', '成品总石数：');
	$objActSheet->setCellValue('B5', $result['g_gem_num']);
	
	$objActSheet->setCellValue('A6', '电镀：');  
	$objActSheet->setCellValue('B6', $result['g_plating']); 
	
	$objActSheet->setCellValue('C4', '尺码：');
	$objActSheet->setCellValue('D4', $result['g_size']);
	
	$objActSheet->setCellValue('C5', '铸件：');
	$objActSheet->setCellValue('D5', $result['g_cast']);
	
	$objActSheet->setCellValue('C6', '重量：'); 
	$objActSheet->setCellValue('D6', $result['g_weight']); 
	
	$objActSheet->mergeCells('B2:F2');
	$objActSheet->mergeCells('B3:F3');
	$objActSheet->mergeCells('D4:F4');
	$objActSheet->mergeCells('D5:F5');
	$objActSheet->mergeCells('D6:F6');
	$objActSheet->mergeCells('G2:J7');
	$objActSheet->mergeCells('A7:F7');
	
	//添加图片
	$objDrawing = new PHPExcel_Worksheet_Drawing();  
	$objDrawing->setName('LuxImg');  
	//$objDrawing->setDescription('Image inserted by Zeal');  
	$objDrawing->setPath('../'.$result['mysql_photo']);  
	$objDrawing->setHeight(120);  
	$objDrawing->setCoordinates('G2');  
	//$objDrawing->setOffsetX(10);  
	$objDrawing->setRotation(15);  
	$objDrawing->getShadow()->setVisible(true);  
	$objDrawing->getShadow()->setDirection(36);  
	$objDrawing->setWorksheet($objActSheet);  
	
	$objActSheet->setCellValue('A8', '物料编号'); 
	$objActSheet->setCellValue('B8', '名称'); 
	$objActSheet->setCellValue('C8', '规格颜色'); 
	$objActSheet->setCellValue('D8', '类别'); 
	$objActSheet->setCellValue('E8', '单价'); 
	$objActSheet->setCellValue('F8', '个数/重量'); 
	$objActSheet->setCellValue('G8', '件工序号'); 
	$objActSheet->setCellValue('H8', '工序名称'); 
	$objActSheet->setCellValue('I8', '工价'); 
	$objActSheet->setCellValue('J8', '工时'); 
	
	// explode 空的字符串，会得到 $m_id_array = array([0] => ) ，这也就导致，就算没东西，count($m_id_array) 也会为1，所以至少都会有一个没有任何东西的空行，就这样吧，也挺好
	$m_t_max = count($m_id_array);
	if(count($m_id_array) < count($t_id_array)){
		$m_t_max = count($t_id_array);
	}
	for($i = 0; $i < $m_t_max; $i++){
		if(isset($m_id_array[$i]) && $m_id_array[$i] != ''){
			$m_detail = explode(',', $m_detail_array[$i]);
			
			$objActSheet->setCellValue('A'.($i+9), $m_id_array[$i]); 
			$objActSheet->setCellValue('B'.($i+9), $m_detail[0]);
			$objActSheet->setCellValue('C'.($i+9), $m_detail[1]);
			$objActSheet->setCellValue('D'.($i+9), $m_detail[2]);
			$objActSheet->setCellValue('E'.($i+9), $m_detail[3]);
			$objActSheet->setCellValue('F'.($i+9), $m_detail[4].':'.$m_value_array[$i]);
		}
		

		if(isset($t_id_array[$i]) && $t_id_array[$i] != ''){
			$t_detail = explode(',', $t_detail_array[$i]);

			$objActSheet->setCellValue('G'.($i+9), $t_id_array[$i]); 
			$objActSheet->setCellValue('H'.($i+9), $t_detail[0]); 
			$objActSheet->setCellValue('I'.($i+9), $t_detail[1]); 
			$objActSheet->setCellValue('J'.($i+9), $t_value_array[$i]); 
		}
	}	
	
	$objActSheet->mergeCells('A'.($m_t_max+9).':J'.($m_t_max+9));
	
	$objActSheet->setCellValue('A'.($m_t_max+10), '工序：');
	$objActSheet->mergeCells('B'.($m_t_max+10).':J'.($m_t_max+10));
	
	$objActSheet->setCellValue('A'.($m_t_max+10+1), '电镀：'); 
	$objActSheet->mergeCells('B'.($m_t_max+10+1).':J'.($m_t_max+10+1));
	
	$objActSheet->setCellValue('A'.($m_t_max+10+2), '电镀厚度：'); 
	$objActSheet->mergeCells('B'.($m_t_max+10+2).':J'.($m_t_max+10+2));
	
	$objActSheet->setCellValue('A'.($m_t_max+10+3), '其他：'); 
	$objActSheet->mergeCells('B'.($m_t_max+10+3).':J'.($m_t_max+10+3));

	for($j = 0; $j < 4; $j++){
		$select_show = '';
		for($k = 1; $k <= count($allinarray[$j]); $k++){
			if(in_array($k, $all_array[$j])){
				$select_show .= $allinarray[$j][$k-1][0].' '/*.'√ '*/;
			}
		}
		$objActSheet->setCellValue('B'.($m_t_max+10+$j), $select_show); 
	}
	
	$objActSheet->mergeCells('A'.($m_t_max+14).':J'.($m_t_max+14));
	
	$objActSheet->mergeCells('A'.($m_t_max+15).':J'.($m_t_max+15));
	$money = '人工：'.$result['p_labour'].' 电镀：'.$result['p_plate'].' 工件：'.$result['p_workpiece'].' 石料：'.$result['p_stone'].' 配件：'.$result['p_parts'].' 其他：'.$result['p_other'].' 其他2：'.$result['p_other2'].' 利润：'.$result['p_profit'].' 合计：'.$result['p_total'];
	$objActSheet->setCellValue('A'.($m_t_max+15), $money);
	
	$objActSheet->setCellValue('A'.($m_t_max+16), '经手人：');
	$objActSheet->setCellValue('B'.($m_t_max+16), $result['people_h']);
	$objActSheet->setCellValue('C'.($m_t_max+16), '审核：');
	$objActSheet->setCellValue('D'.($m_t_max+16), $result['people_a']);
	
	//设置宽度  
	//$objActSheet->getColumnDimension('B')->setAutoSize(true);  
	//$objActSheet->getColumnDimension('A')->setWidth(20);  
	
	//*************************************  
	//输出内容  
	//  
	$outputFileName = "output.xls";  
	//到文件  
	//$objWriter->save($outputFileName);  
	//or  
	//到浏览器  
	
	header("Content-Type: application/force-download");  
	header("Content-Type: application/octet-stream");  
	header("Content-Type: application/download");  
	header('Content-Disposition:inline;filename="'.$outputFileName.'"');  
	header("Content-Transfer-Encoding: binary");  
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
	header("Pragma: no-cache");  
	$objWriter->save('php://output');  


}else{
	die('系統故障(1)');	
}