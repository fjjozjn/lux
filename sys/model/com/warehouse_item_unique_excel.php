<?php

if(!isset($_SESSION['logininfo'])){
    die('Please login!');
}
if(!isset($_GET['wh_name']) || $_GET['wh_name'] == ''){
    die('Need warehouse ID!');
}

require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');
require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');

date_default_timezone_set('Asia/ShangHai');
/**
 * 以下是使用示例，对于以 //// 开头的行是不同的可选方式，请根据实际需要
 * 打开对应行的注释。
 * 如果使用 Excel5 ，输出的内容应该是GBK编码。
 */
require_once '../../PHPExcel/Classes/PHPExcel.php';


$temp_table = ' warehouse_item_unique w left join product p on w.pid = p.pid';

$where_sql = '';
if (strlen(@$_SESSION['search_criteria']['pid'])){
    $where_sql.= " AND w.pid Like '%".$_SESSION['search_criteria']['pid'].'%\'';
}
if (strlen(@$_SESSION['search_criteria']['type'])){
    $where_sql.= " AND p.type Like '%".$_SESSION['search_criteria']['type'].'%\'';
}
if (strlen(@$_SESSION['search_criteria']['start_date'])){
    if (strlen(@$_SESSION['search_criteria']['end_date'])){
        $where_sql.= " AND w.in_date between '".$_SESSION['search_criteria']['start_date']." 00:00:00' AND '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
    }else{
        $where_sql.= " AND w.in_date > '".$_SESSION['search_criteria']['start_date']." 00:00:00'";
    }
}elseif (strlen(@$_SESSION['search_criteria']['end_date'])){
    $where_sql.= " AND w.in_date < '".$_SESSION['search_criteria']['end_date']." 23:59:59'";
}

$where_sql .= " AND w.qty > 0 AND w.wh_name = '".$_GET['wh_name']."' ORDER BY w.pid";
$list_field = ' w.pid, w.qty, w.photo ';
$start_row = 0;
//默认值100000，相当于一页显示无限多条的记录了
$end_row = 100000;

$info = $mysql->sp('CALL backend_list_withfield(?, ?, ?, ?, ?)', $start_row, $end_row, $temp_table, $where_sql, $list_field);

if($info){
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

    //由PHPExcel根据传入内容自动判断单元格内容类型
    $objActSheet->setCellValue('A1', 'Photo');
    $objActSheet->setCellValue('A1', 'Warehouse');
    $objActSheet->setCellValue('A1', 'Item Code');
    $objActSheet->setCellValue('A1', 'Qty');

    $objActSheet->setCellValue('A1', 'Photo');
    $objActSheet->setCellValue('A1', 'Warehouse');
    $objActSheet->setCellValue('A1', 'Item Code');
    $objActSheet->setCellValue('A1', 'Qty');




    //*************************************
    //输出内容
    //
    $outputFileName = "warehouse_item.xls";
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
    die('No data !');
}