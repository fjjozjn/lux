<?
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');
require($_SERVER['DOCUMENT_ROOT'] . '/sys/in38/global_admin.php');

date_default_timezone_set('Asia/ShangHai');
/**
 * 以下是使用示例，对于以 //// 开头的行是不同的可选方式，请根据实际需要
 * 打开对应行的注释。
 * 如果使用 Excel5 ，输出的内容应该是GBK编码。
 */
require_once '../../../PHPExcel/Classes/PHPExcel.php';

if (isset($_GET['pvid']) && $_GET['pvid'] != '') {
    //判断是否有访问权限
    if (!isSysAdmin()) {
        $rtn = $mysql->qone("select printed_by from proforma where pvid = ?", $_GET['pvid']);
        if ($rtn['printed_by'] != $_SESSION['logininfo']['aName']) {
            if (!judgeUserPermGroup($rtn['printed_by'])) {
                die('Without Permission To Access');
            }
        }
    }

    //修改printed_date
    $mysql->q('update proforma set printed_date = ? where pvid = ?', dateMore(), $_GET['pvid']);

    $result1 = $mysql->qone('select * from proforma where pvid = ?', $_GET['pvid']);
    if (!$result1) {
        die('Error(1)');
    }

    // \r\n 换为 <br />，不然没法换行，数据库中存的不是 \r\n 。。。
    $send_to = str_replace("\r\n", '<br />', $result1['send_to']);
    $make_date = date('Y/m/d', strtotime($result1['mark_date']));
    $printed_date = date('Y/m/d', strtotime($result1['printed_date']));
    $rs2 = $mysql->q('select * from proforma_item where pvid = ?', $_GET['pvid']);
    if ($rs2) {
        $result2 = $mysql->fetch();
    } else {
        die('Error(2)');
    }


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
    $objActSheet->setCellValue('A1', 'Proforma Invoice');  // 字符串内容


    //输出内容
    //
    $outputFileName = $result1['pvid'] . ".xls";
    //到文件
    //$objWriter->save($outputFileName);
    //or
    //到浏览器

    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header('Content-Disposition:inline;filename="' . $outputFileName . '"');
    header("Content-Transfer-Encoding: binary");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: no-cache");
    $objWriter->save('php://output');

} else {
    die('Error!');
}