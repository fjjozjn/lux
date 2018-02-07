<?php
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//check admin login
checkAdminLogin();
//20131120 for 360 加meta webkit
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="renderer" content="webkit">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?=SITE_NAME.SITE_VER?></title>
    <?php

    if(!IS_DIST){
        //develop header
        //include too much little js/css file
        ?>
    <link rel="stylesheet" type="text/css" href="/ui/reset.css" />
    <link rel="stylesheet" type="text/css" href="/ui/main.css" />
    <link rel="stylesheet" type="text/css" href="/ui/form.css" />
    <link rel="stylesheet" type="text/css" href="/ui/selectbox.css" />
    <link href="/ui/stylesheet.css" rel="stylesheet" type="text/css">
    <link href="/ui/nav.css" rel="stylesheet" type="text/css">
    <link href="/ui/artDialog.css" rel="stylesheet" />
        <script language="javascript" type="text/javascript" src="/ui/jquery.js"></script>
        <script language="javascript" type="text/javascript" src="/ui/selectbox.js"></script>
        <script language="javascript" type="text/javascript" src="/ui/form.js"></script>
        <script language="javascript" type="text/javascript" src="/ui/main.js"></script>
        <script language="javascript" type="text/javascript" src="/ui/nav.js"></script>
        <script language="javascript" type="text/javascript" src="/ui/artDialog.js"></script>
        <script language="javascript" type="text/javascript" src="/ui/jquery.tablednd.0.7.min.js"></script>
        <script language="javascript" type="text/javascript" src="/ui/jquery.tablesorter.js"></script>
    <?php
    }else{
    //distribute header
    //put all of js/css files into one each file

    /*
    <link rel="stylesheet" type="text/css" href="/cac/?b=ui&f=selectbox.css,main.css" />
    <script type="text/javascript" src="/cac/?b=ui&amp;f=jquery.js,form.js,main.js,selectbox.js,swfobject.js"></script>

    */
    ?>
    <link rel="stylesheet" type="text/css" href="/cac/?g=admincss" />
        <script type="text/javascript" src="/cac/?g=adminjs"></script>
    <?php
    }
    ?>

    <script type="text/javascript" language="JavaScript1.2">
        function jsClock(){
            //current date time update
            var now=new Date(); now.hrs=now.getHours(); now.min=now.getMinutes(); now.sec=now.getSeconds();

            now.hrs=((now.hrs<10)? "0" : "")+now.hrs;
            now.min=((now.min<10)? "0" : "")+now.min;
            now.sec=((now.sec<10)? "0" : "")+now.sec;
            year = now.getFullYear();
            month = 1+now.getMonth();
            date = ((now.getDate()<10)? "0" : "")+now.getDate();
            month=((month<10)? "0" : "")+month;
            $('#current_datetime').html(year + "-" + month + "-"+date + " "+now.hrs+':'+now.min+':'+now.sec);
            setTimeout('jsClock()',1000);
        }

        $(function(){
            jsClock();
        });
    </script>

    <script type="text/javascript">
        <? if($_SESSION['logininfo']['aName'] != 'ZJN'){ //我的测试不记录进去 ?>

        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-19704593-5']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();

        <? } ?>
    </script>
</head>

<body bgcolor="#FFFFFF">

<?php
//20150331
if(strpos($act, 'formdetail') !== 0){
    ?>

    <table width='100%' border='0' cellpadding='0' cellspacing='0' bgcolor='#FFFFFF' background="images/banner_bg.gif">
    <tr>
        <td width='180' rowspan='3'><img src="images/gologo.jpg"  alt="" width="180" height="108"></td>
        <td width='35' height="35">&nbsp;</td>
        <td width="400" align='left' class='headertitle'><?=SITE_NAME.SITE_VER?></td>
        <td align='left' class='normal'>&nbsp;</td>
        <td align='left' class='normal_num'>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class='normal'>Login User : <?=@$_SESSION["logininfo"]["aName"]?></td>
        <td width='110' align='left' class='normal'>Current Time :</td>
        <td align='left' id='current_datetime' class='normal_num'>-</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td colspan="3" height="40">

    <ul id="navmenu-h">
    <li><a href="?act=main" class="otherli">Home</a></li>
    <!--li><a href="javascript:void(0);">Factory<i>&gt;</i></a>
    <ul>
        <li><a href="?act=searchform&page=1">BOM<i>&gt;</i></a>
          <ul>
            <li><a href="?act=sendform">填写BOM</a></li>
            <li><a href="?act=addmaterial&page=1">管理物料</a></li>
            <li><a href="?act=addtask&page=1">管理工序</a></li>
          </ul>
        </li>
    </ul>
    </li-->

    <?php
    $mode = $mysql->qone('select mode from tw_admin where AdminName = ?', $_SESSION["logininfo"]["aName"]);
    if(isset($mode['mode']) && $mode['mode'] == 1){
        ?>

        <li><a href="javascript:void(0);">Design<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-search_poster&page=1">Poster<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_poster">Add</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_color_chart&page=1">Color Chart<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_color_chart">Add</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_trend_books&page=1">Trend Books<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_trend_books">Add</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-generate_email_content">Style List</a></li>
            </ul>
        </li>

        <li><a href="javascript:void(0);">Sales<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-searchproduct_new&page=1">Product<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addproduct_new">Add</a></li>
                        <li><a href="?act=com-fty_searchproduct&page=1">Pending Product</a></li>
                        <li><a href="?act=com-fty_searchbom&page=1">Pending BOM</a></li>
                        <li><a href="?act=com-printproduct">Print</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-searchsample_order&page=1">Sample Order<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addsample_order">Add</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-searchquotation&page=1">Quotation<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addquotation">Add</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchproforma&page=1">Proforma Invoice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addproforma">Add</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchinvoice&page=1">Invoice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addinvoice">Add</a></li>
                        <li><a href="?act=com-shipment&page=1">Shipment Record</a></li>
                        <li><a href="?act=com-payment&page=1">Payment Record</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchcustomsinvoice&page=1">Customs Invoice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addcustomsinvoice">Add</a></li>
                        <li><a href="?act=com-combine_invoice_to_ci">Combine Invoices</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchpackinglist&page=1">Packing List<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-invoice_to_packing_list">Invoice To PL</a></li>
                        <li><a href="?act=com-delivery_to_packing_list">Delivery To PL</a></li>
                        <li><a href="?act=com-view_factory_delivery&page=1">View Factory DN</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_credit_note&page=1">Credit Note<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_credit_note">Add</a></li>
                    </ul>
                </li>
                <!--li><a href="javascript:void(0);">Retail Sales Memo<i>&gt;</i></a>
                    <ul>
                        <?php
                        /*
                            $rs_sales_memo = $mysql->q('select id, wh_name from warehouse where type = ?', 'Shop');
                            $rtn_sales_memo = $mysql->fetch();
                            foreach($rtn_sales_memo as $v){
                                echo '<li><a href="?act=com-search_retail_sales_memo&page=1&wh_name='.$v["wh_name"].'">'.$v["wh_name"].'<i>&gt;</i></a>
                                    <ul>
                                        <li><a href="?act=com-add_retail_sales_memo&wh_id='.$v["id"].'|'.$v["wh_name"].'&currency=HKD">Add</a></li>
                                    </ul>
                                </li>';
                            }
                        */
                        ?>
                    </ul>
                </li-->
                <li><a href="?act=com-searchcustomer&page=1">Customer<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addcustomer">Add</a></li>
                        <li><a href="?act=com-c_searchcontact&page=1">Contact</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_payment_new&page=1">Payment Advice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_payment_new">Add</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li><a href="javascript:void(0);">Purchasing<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-searchpurchase&page=1">Factory PO<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addpurchase">Add</a></li>
                        <li><a href="?act=com-settlement&page=1">Settlement</a></li>
                        <li><a href="?act=com-overheads&page=1">Overheads</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-search_factory_complaint_form&page=1">Complaint Form<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_factory_complaint_form">Add</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-search_factory_chargeback_form&page=1">Chargeback Form<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_factory_chargeback_form">Add</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchsupplier&page=1">Supplier<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addsupplier">Add</a></li>
                        <li><a href="?act=com-s_searchcontact&page=1">Contact</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li><a href="javascript:void(0);">Warehouse<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-search_item_transfer_form&page=1">Item Transfer Form<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_item_transfer_form">Add</a></li>
                    </ul>
                </li>
                <?
                $rs_warehouse = $mysql->q('select id, wh_name from warehouse order by id');
                $rtn_warehouse = $mysql->fetch();
                foreach($rtn_warehouse as $v){
                    echo '<li><a href="?act=com-search_warehouse_item_unique&page=1&wh_name='.$v["wh_name"].'">'.$v["wh_name"].'<i>&gt;</i></a>
                                <ul>
                                    <li><a href="?act=com-add_warehouse_item&wh_id='.$v["id"].'|'.$v["wh_name"].'">Add</a></li>
                                </ul>
                            </li>';
                }
                ?>
            </ul>
        </li>

        <li><a href="javascript:void(0);">QC<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-qc_schedule">QC Schedule</a></li>
                <li><a href="?act=com-search_qc_report&page=1">QC Report</a>
                    <!--<ul>
                        <li><a href="?act=com-add_qc_report">Add</a></li>
                    </ul>-->
                </li>
                <!--            <li><a href="?act=com-addqc">Add QC Report</a></li>
                                <li><a href="?act=com-searchqc&page=1">Search QC Report</a></li>-->
            </ul>
        </li>

        <? if(isSysAdmin()){ ?>
            <li><a href="javascript:void(0);">Accounting<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=com-search_payment_new&page=1">Payment Advice<i>&gt;</i></a>
                        <ul>
                            <li><a href="?act=com-add_payment_new">Add</a></li>
                        </ul>
                    </li>
                    <li><a href="javascript:void(0);">Voucher<i>&gt;</i></a>
                        <ul>
                            <li><a href="?act=com-search_p_r_voucher&page=1">P/R Voucher<i>&gt;</i></a>
                                <ul>
                                    <li><a href="?act=com-add_p_r_voucher">Add</a></li>
                                </ul>
                            </li>
                            <li><a href="?act=com-search_petty_cash_voucher&page=1">PettyCash Voucher<i>&gt;</i></a>
                                <ul>
                                    <li><a href="?act=com-add_petty_cash_voucher">Add</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li><a href="?act=com-search_payment_request&is_approve=0&page=1">Payment Request</a></li>
                </ul>
            </li>
        <? } ?>

        <li><a href="javascript:void(0);">Report<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-gp">GP</a></li>
                <li><a href="?act=com-overdue_shipment&status=(I)|(P)">Overdue Shipment</a></li>
                <li><a href="?act=com-overdue_payment&status=(I)|(S)">Overdue Payment</a></li>
                <li><a href="?act=com-top_customer">Top Customer</a></li>
                <li><a href="?act=com-top_product">Top Product</a></li>
                <li><a href="?act=com-factory_analysis">Factory Analysis</a></li>
                <li><a href="?act=com-customer_analysis">Customer Analysis</a></li>
                <li><a href="?act=com-staff_analysis">Staff Analysis</a></li>
                <!--li><a href="?act=com-chart&today">Gantt Chart</a></li-->
                <li><a target="_blank" href="model/com/pdf_luxcraft_stock_list.php">Luxcraft Stock List</a></li>
                <li><a href="?act=com-monthly_sales_figure">Monthly Sales Figure</a></li>
                <li><a href="?act=com-monthly_payment_figure">Monthly Payment Figure</a></li>
            </ul>
        </li>

        <li><a href="javascript:void(0);">Admin<i>&gt;</i></a>
            <ul>
                <li><a href="?act=searchhr&page=1">OT/Annual Leave<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=managehr">apply</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_purchase_request&is_approve=0&page=1">Purchase Request<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_payment_request">Add</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_it_request&page=1">IT Service Request<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_it_request">Add</a></li>
                    </ul>
                </li>
                <li><a href="javascript:void(0);">Useful Links<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=lux_webmail">Webmail</a></li>
                        <li><a href="?act=company_contact&page=1">Company Contact</a></li>
                        <li><a href="?act=lux_qsync">Server Files</a></li>
                        <li><a href="/product/index.php" target="_blank">Lux catalog</a></li>
                    </ul>
                </li>
                <li><a target="_blank" href="/filemgr">File Mgr</a></li>
            </ul>
        </li>

    <?php
    } elseif (isset($mode['mode']) && $mode['mode'] == 2) { ?>
        <li><a href="javascript:void(0);">Exhibition<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-searchproduct_e&page=1">Product Search</a>
                </li>
                <li><a href="?act=com-addcustomer_treatment">Customer treatment<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-searchcustomer_treatment&page=1">Search</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li><a href="javascript:void(0);">Sales<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-searchquotation&page=1">Quotation<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addquotation">Add</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchproforma&page=1">Proforma Invoice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addproforma">Add</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchinvoice&page=1">Invoice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addinvoice">Add</a></li>
                        <li><a href="?act=com-shipment&page=1">Shipment Record</a></li>
                        <li><a href="?act=com-payment&page=1">Payment Record</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchcustomsinvoice&page=1">Customs Invoice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addcustomsinvoice">Add</a></li>
                        <li><a href="?act=com-combine_invoice_to_ci">Combine Invoices</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchpackinglist&page=1">Packing List<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-invoice_to_packing_list">Invoice To PL</a></li>
                        <li><a href="?act=com-delivery_to_packing_list">Delivery To PL</a></li>
                        <li><a href="?act=com-view_factory_delivery&page=1">View Factory DN</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_credit_note&page=1">Credit Note<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_credit_note">Add</a></li>
                    </ul>
                </li>
                <!--li><a href="javascript:void(0);">Retail Sales Memo<i>&gt;</i></a>
                    <ul>
                        <?php
                        /*
                        $rs_sales_memo = $mysql->q('select id, wh_name from warehouse where type = ?', 'Shop');
                        $rtn_sales_memo = $mysql->fetch();
                        foreach($rtn_sales_memo as $v){
                            echo '<li><a href="?act=com-search_retail_sales_memo&page=1&wh_name='.$v["wh_name"].'">'.$v["wh_name"].'<i>&gt;</i></a>
                                    <ul>
                                        <li><a href="?act=com-add_retail_sales_memo&wh_id='.$v["id"].'|'.$v["wh_name"].'&currency=HKD">Add</a></li>
                                    </ul>
                                </li>';
                        }
                        */
                        ?>
                    </ul>
                </li-->
                <li><a href="?act=com-searchcustomer&page=1">Customer<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addcustomer">Add</a></li>
                        <li><a href="?act=com-c_searchcontact&page=1">Contact</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_payment_new&page=1">Payment Advice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_payment_new">Add</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li><a href="javascript:void(0);">Contact<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-searchcustomer&page=1">Customer<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addcustomer">Add</a></li>
                        <li><a href="?act=com-c_searchcontact&page=1">Contact</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchsupplier&page=1">Supplier<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addsupplier">Add</a></li>
                        <li><a href="?act=com-s_searchcontact&page=1">Contact</a></li>
                    </ul>
                </li>
            </ul>
        </li>
    <?php }else{ ?>
        <li><a href="javascript:void(0);">Exhibition<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-searchproduct_e&page=1">Product Search</a>
                </li>
                <li><a href="?act=com-addcustomer_treatment">Customer treatment<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-searchcustomer_treatment&page=1">Search</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        <li><a href="javascript:void(0);">Design<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-search_poster&page=1">Poster<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_poster">Add</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_color_chart&page=1">Color Chart<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_color_chart">Add</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_trend_books&page=1">Trend Books<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_trend_books">Add</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-generate_email_content">Style List</a></li>
            </ul>
        </li>

        <li><a href="javascript:void(0);">Sales<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-searchproduct_new&page=1">Product<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addproduct_new">Add</a></li>
                        <li><a href="?act=com-fty_searchproduct&page=1">Pending Product</a></li>
                        <li><a href="?act=com-fty_searchbom&page=1">Pending BOM</a></li>
                        <li><a href="?act=com-printproduct">Print</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-searchsample_order&page=1">Sample Order<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addsample_order">Add</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-searchquotation&page=1">Quotation<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addquotation">Add</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchproforma&page=1">Proforma Invoice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addproforma">Add</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchinvoice&page=1">Invoice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addinvoice">Add</a></li>
                        <li><a href="?act=com-shipment&page=1">Shipment Record</a></li>
                        <li><a href="?act=com-payment&page=1">Payment Record</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchcustomsinvoice&page=1">Customs Invoice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addcustomsinvoice">Add</a></li>
                        <li><a href="?act=com-combine_invoice_to_ci">Combine Invoices</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchpackinglist&page=1">Packing List<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-invoice_to_packing_list">Invoice To PL</a></li>
                        <li><a href="?act=com-delivery_to_packing_list">Delivery To PL</a></li>
                        <li><a href="?act=com-view_factory_delivery&page=1">View Factory DN</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_credit_note&page=1">Credit Note<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_credit_note">Add</a></li>
                    </ul>
                </li>
                <!--li><a href="javascript:void(0);">Retail Sales Memo<i>&gt;</i></a>
                    <ul>
                        <?php
                        /*
                        $rs_sales_memo = $mysql->q('select id, wh_name from warehouse where type = ?', 'Shop');
                        $rtn_sales_memo = $mysql->fetch();
                        foreach($rtn_sales_memo as $v){
                            echo '<li><a href="?act=com-search_retail_sales_memo&page=1&wh_name='.$v["wh_name"].'">'.$v["wh_name"].'<i>&gt;</i></a>
                                    <ul>
                                        <li><a href="?act=com-add_retail_sales_memo&wh_id='.$v["id"].'|'.$v["wh_name"].'&currency=HKD">Add</a></li>
                                    </ul>
                                </li>';
                        }
                        */
                        ?>
                    </ul>
                </li-->
                <li><a href="?act=com-searchcustomer&page=1">Customer<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addcustomer">Add</a></li>
                        <li><a href="?act=com-c_searchcontact&page=1">Contact</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_payment_new&page=1">Payment Advice<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_payment_new">Add</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li><a href="javascript:void(0);">Purchasing<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-searchpurchase&page=1">Factory PO<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addpurchase">Add</a></li>
                        <li><a href="?act=com-settlement&page=1">Settlement</a></li>
                        <li><a href="?act=com-overheads&page=1">Overheads</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-search_factory_complaint_form&page=1">Complaint Form<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_factory_complaint_form">Add</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-search_factory_chargeback_form&page=1">Chargeback Form<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_factory_chargeback_form">Add</a></li>
                    </ul>
                </li>

                <li><a href="?act=com-searchsupplier&page=1">Supplier<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-addsupplier">Add</a></li>
                        <li><a href="?act=com-s_searchcontact&page=1">Contact</a></li>
                    </ul>
                </li>
            </ul>
        </li>

        <li><a href="javascript:void(0);">Warehouse<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-search_item_transfer_form&page=1">Item Transfer Form<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_item_transfer_form">Add</a></li>
                    </ul>
                </li>
                <?
                $rs_warehouse = $mysql->q('select id, wh_name from warehouse order by id');
                $rtn_warehouse = $mysql->fetch();
                foreach($rtn_warehouse as $v){
                    echo '<li><a href="?act=com-search_warehouse_item_unique&page=1&wh_name='.$v["wh_name"].'">'.$v["wh_name"].'<i>&gt;</i></a>
                                <ul>
                                    <li><a href="?act=com-add_warehouse_item&wh_id='.$v["id"].'|'.$v["wh_name"].'">Add</a></li>
                                </ul>
                            </li>';
                }
                ?>
            </ul>
        </li>

        <li><a href="javascript:void(0);">QC<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-qc_schedule">QC Schedule</a></li>
                <li><a href="?act=com-search_qc_report&page=1">QC Report</a>
                    <!--<ul>
                        <li><a href="?act=com-add_qc_report">Add</a></li>
                    </ul>-->
                </li>
                <!--                <li><a href="?act=com-addqc">Add QC Report</a></li>
                                <li><a href="?act=com-searchqc&page=1">Search QC Report</a></li>-->
            </ul>
        </li>

        <? if(isSysAdmin()){ ?>
            <li><a href="javascript:void(0);">Accounting<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=com-search_payment_new&page=1">Payment Advice<i>&gt;</i></a>
                        <ul>
                            <li><a href="?act=com-add_payment_new">Add</a></li>
                        </ul>
                    </li>
                    <li><a href="javascript:void(0);">Voucher<i>&gt;</i></a>
                        <ul>
                            <li><a href="?act=com-search_p_r_voucher&page=1">P/R Voucher<i>&gt;</i></a>
                                <ul>
                                    <li><a href="?act=com-add_p_r_voucher">Add</a></li>
                                </ul>
                            </li>
                            <li><a href="?act=com-search_petty_cash_voucher&page=1">PettyCash Voucher<i>&gt;</i></a>
                                <ul>
                                    <li><a href="?act=com-add_petty_cash_voucher">Add</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li><a href="?act=com-search_payment_request&is_approve=0&page=1">Payment Request</a></li>
                </ul>
            </li>
        <? } ?>

        <li><a href="javascript:void(0);">Report<i>&gt;</i></a>
            <ul>
                <li><a href="?act=com-gp">GP</a></li>
                <li><a href="?act=com-overdue_shipment&status=(I)|(P)">Overdue Shipment</a></li>
                <li><a href="?act=com-overdue_payment&status=(I)|(S)">Overdue Payment</a></li>
                <li><a href="?act=com-top_customer">Top Customer</a></li>
                <li><a href="?act=com-top_product">Top Product</a></li>
                <li><a href="?act=com-factory_analysis">Factory Analysis</a></li>
                <li><a href="?act=com-customer_analysis">Customer Analysis</a></li>
                <li><a href="?act=com-staff_analysis">Staff Analysis</a></li>
                <!--li><a href="?act=com-chart&today">Gantt Chart</a></li-->
                <li><a target="_blank" href="model/com/pdf_luxcraft_stock_list.php">Luxcraft Stock List</a></li>
                <li><a href="?act=com-monthly_sales_figure">Monthly Sales Figure</a></li>
                <li><a href="?act=com-monthly_payment_figure">Monthly Payment Figure</a></li>
            </ul>
        </li>

        <li><a href="javascript:void(0);">Admin<i>&gt;</i></a>
            <ul>
                <li><a href="?act=searchhr&page=1">OT/Annual Leave<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=managehr">apply</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_purchase_request&is_approve=0&page=1">Purchase Request<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_payment_request">Add</a></li>
                    </ul>
                </li>
                <li><a href="?act=com-search_it_request&page=1">IT Service Request<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=com-add_it_request">Add</a></li>
                    </ul>
                </li>
                <li><a href="javascript:void(0);">Useful Links<i>&gt;</i></a>
                    <ul>
                        <li><a href="?act=lux_webmail">Webmail</a></li>
                        <li><a href="?act=company_contact&page=1">Company Contact</a></li>
                        <li><a href="?act=lux_qsync">Server Files</a></li>
                        <li><a href="/product/index.php" target="_blank">Lux catalog</a></li>
                    </ul>
                </li>
                <li><a target="_blank" href="/filemgr">File Mgr</a></li>
            </ul>
        </li>

    <?php } ?>

    <li><a href="javascript:void(0);">Setting<i>&gt;</i></a>
        <ul>
            <!--        <li><a href="?act=com-mode">Mode</a></li>-->
            <li><a href="?act=com-setting">Setting</a></li>
            <li><a href="?act=com-company_webpage_setting">Company Webpage</a></li>
            <li><a href="?act=com-warehouse">Location</a></li>
            <li><a href="?act=com-currency">Currency</a></li>
            <li><a href="?act=com-unit">Unit</a></li>
            <li><a href="?act=com-theme">Theme</a></li>
            <li><a href="?act=searchuser">System User<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=manageuser">Add</a></li>
                </ul>
            </li>
            <!--li><a href="?act=searchhr&page=1">HR<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=managehr">Add</a></li>
                </ul>
            </li-->
            <!--        <li><a href="?act=fty_searchuser">Factory User<i>&gt;</i></a>
                        <ul>
                            <li><a href="?act=fty_manageuser">Add</a></li>
                        </ul>
                    </li>-->
            <!--<li><a href="?act=luxcraft_searchuser">Luxcraft User<i>&gt;</i></a>
                <ul>
                    <li><a href="?act=luxcraft_manageuser">Add</a></li>
                </ul>
            </li>-->
            <li><a href="?act=admin_log&page=1">User Log</a></li>
            <li><a href="../fty" target="_blank">Fty Login</a></li>
        </ul>
    </li>
    <li><a href="?act=logout" class="otherli">Logout</a></li>

    </ul>

    </td>
    </tr>
    </table>

<?php
}
?>

<BR clear="all" />
<div style="padding:1% 1%;">

    <!-- content start //-->
    <?php
    //暂时不需要限制IP
    //checkAdminAllowedIP();
    if($model = modelExist($act, true)){
        require($model);
    }else{
        redirectTo('404.html');//这里跳转到了自制的404页面，下面的就不执行了
        $myerror->error('阁下发起了未知操作，请返回首页', 'INDEX');
        $act = 'inside_error';
        require(modelExist($act, true));
    }
    ?>
    <!-- content end //-->
</div>
<BR>
<? if($act != 'main'){//main页面由于div多，所以分割没法在正常的位置显示 ?>
    <HR>
<? } ?>
<div align='center'>
    copyright &copy 2011-<?=date("Y");?> LUX DESIGN LTD ALL RIGHTS RESERVED
</div>

</body>
</html>