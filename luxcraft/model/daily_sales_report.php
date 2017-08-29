<?

//禁止其他用户进入（临时做法）
if(!isLuxcraftAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    //引用特殊的recordset class 文件
    //require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

    // 如果有post資料則給Session，并且清除附在上次翻頁時殘留的$_GET['page']
/*    if (count($_POST)){
        $_SESSION['search_criteria'] = $_POST;
    }*/

    $form = new My_Forms(array('action' => 'model/pdf_daily_sales_report.php', 'addon' => 'target="_blank"'));
    $formItems = array(
        'start_date' => array(
            'type' => 'text',
            'restrict' => 'date',
            'value' => @$_SESSION['search_criteria']['start_date'],
            'required' => 1,
            'info' => '只填写Start Date则只查询一天的数据'
        ),
        'end_date' => array(
            'type' => 'text',
            'restrict' => 'date',
            'value' => @$_SESSION['search_criteria']['end_date'],
            'info' => '可不填写。如果填写了则查询时间范围内的数据'
        ),
        'submitbutton' => array(
            'type' => 'submit',
            'value' => 'PDF',
        ),
    );
    $form->init($formItems);
    $form->begin();
    ?>

    <table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td class='headertitle' align="center">DAILY SALES REPORT</td>
        </tr>
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Search</legend>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="right" valign="top">Date Start :<h6 class="required"></h6></td>
                            <td align="left"><? $form->show('start_date'); ?></td>
                            <td align="right" valign="top">Date End :<h6 class="required"></h6></td>
                            <td align="left"><? $form->show('end_date'); ?></td>
                        </tr>
                        <tr>
                            <td width="100%" colspan='4'>
                                <? $form->show('submitbutton'); ?>
                                <!--div style="padding-top: 9px;">&nbsp;&nbsp;<a class="button" href="model/pdf_daily_sales_report.php" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a>&nbsp;&nbsp;<a class="button" href="model/gp_excel.php" target='_blank' onclick="return pdfConfirm()"><b>EXCEL</b></a></div-->
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>
    <?
    $form->end();
}
?>