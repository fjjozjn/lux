<?php

if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

//20131219 系统地址
$host = '58.177.207.149:5900';

// 上傳設置
$pic_path_ht        = '../files/titleimg/'; //后台图片路径
$pic_path 		    = "files/titleimg/";			//圖片目錄
$cacheDir			= 'cache/';			//cache文件放在哪个目录下

$pic_path_small     = "upload/luxsmall/"; //圖片縮小後保存的目錄
$pic_path_small_poster= "upload/poster_small/";			//公司效果图上传目录缩小图 - 20160723
$pic_path_small_color_chart= "upload/color_chart_small/";			//公司效果图上传目录缩小图 - 20160820
$pic_path_small_trend_books= "upload/trend_books_small/";			//公司效果图上传目录缩小图 - 20160820
$file_exts			= array('.jpg','.png','.gif'); //允許上傳的文件類型
$allow_upload		= true;				//是否允許上傳
$max_size			= 30000;				//上傳文件大小限制，單位KB

//20150119
$pic_path_watermark = "upload/watermark/"; //加水印图保存目录

//S是頁面顯示的比例，M是pdf顯示的比例
//20150115 s尺寸由80*60改为160*120
$thumb_width		= array('s' => 160, 'm' => 240, 'l'=> 800);	//自動縮小圖片的寬度上限
$thumb_height		= array('s' => 120, 'm' => 180, 'l'=> 600);	//自動縮小圖片的高度上限
$thumb_quality		= 85;

$pic_path_com 		= "upload/lux/";			//公司圖片目錄
$pic_path_com_poster= "upload/poster/";			//公司效果图上传目录 - 20160723
$pic_path_com_color_chart= "upload/color_chart/";			//公司效果图上传目录 - 20160820
$pic_path_com_trend_books= "upload/trend_books/";			//公司效果图上传目录 - 20160820
$pic_path_qc_normal = 'upload/qc_normal/';
$pic_path_qc_small  = 'upload/qc_small/';
$product_file_path_com = "upload/product_file/"; //product附加的文件

$pic_path_fty_bom   = "upload/photo/";

define("GENERAL_YES", true);
define("GENERAL_NO", false);
/*
chagne log

2011.03.01		增加了几个通用的常量 WS_ZERO NO_ACCESS IS_MT BAD_SIGN
2011-05-09		Add shOptions, ih's config has special
2011-05-10		Add $alwaysDebugIpList
2011-06-15		Add 61.93.232.225 to $alwaysDebugIpList
2011-07-21		添加$verifyDomain，注释掉了深圳外网IP和香港测试服的资料，因为没用. 添加 web game id list
*/

//-------------------------------------------------------------
//--------------------------配置参数----------------------------
//-------------------------------------------------------------
$pageLimit			= 10;				//在列表页面上,每页显示多少条纪录
$timeShouldWait		= 10;				//多少秒后的刷新才不算为重複刷新
$resendFpEmail		= 300;				//多少秒后才可再次发送找回密码邮件

//-------------------------Mail 基本设定------------------------
// lux 邮件服务器
//login: system@luxdesign.hk
//password: a93112244
//smtp/pop3: mailcn.luxdesign.hk
$mailHeaders = array('From' => 'system@luxdesign.hk',
					 'Content-Type' => 'text/html; charset=utf8',
					 'Reply-To' => 'system@luxdesign.hk',
					 'Return-path' => 'system@luxdesign.hk',
					 );
$mailParams = array("host" => 'mailcn.luxdesign.hk', "port" => '1688');//POP3:1689 SMTP:1688

//以下是游戏名称及其对应ID列表
define('GAME_SH',	1);

define('IS_GAME', 1);
define('IS_APP', 2);

define('WS_ZERO',		-1006);
define('NO_ACCESS',		-1007);
define('IS_MT',			-1008);
define('BAD_SIGN',		-1009);

//20130208
define('FTY_CATG_REG', 'fty_reg');
define('FTY_ACTION_REG_SUCCESS', '1');
define('FTY_ACTION_REG_FAILURE', '-1');



//20131231 开始记录用户系统操作日志，这些日志在首页显示 例： define('ACTION_LOG_', 'action_log_');
//********** sys **********
define('ACTION_LOG_SYS_ADD_PRODUCT', 'action_log_sys_add_product');
define('ACTION_LOG_SYS_ADD_PRODUCT_S', '1');
define('ACTION_LOG_SYS_ADD_PRODUCT_F', '-1');
define('ACTION_LOG_SYS_DEL_PRODUCT', 'action_log_sys_del_product');
define('ACTION_LOG_SYS_DEL_PRODUCT_S', '2');
define('ACTION_LOG_SYS_DEL_PRODUCT_F', '-2');
define('ACTION_LOG_SYS_MOD_PRODUCT', 'action_log_sys_mod_product');
define('ACTION_LOG_SYS_MOD_PRODUCT_S', '3');
define('ACTION_LOG_SYS_MOD_PRODUCT_F', '-3');
define('ACTION_LOG_SYS_COPY_PRODUCT', 'action_log_sys_copy_product');
define('ACTION_LOG_SYS_COPY_PRODUCT_S', '4');
define('ACTION_LOG_SYS_COPY_PRODUCT_F', '-4');

define('ACTION_LOG_SYS_ADD_SAMPLE_ORDER', 'action_log_sys_add_sample_order');
define('ACTION_LOG_SYS_ADD_SAMPLE_ORDER_S', '5');
define('ACTION_LOG_SYS_ADD_SAMPLE_ORDER_F', '-5');
define('ACTION_LOG_SYS_DEL_SAMPLE_ORDER', 'action_log_sys_del_sample_order');
define('ACTION_LOG_SYS_DEL_SAMPLE_ORDER_S', '6');
define('ACTION_LOG_SYS_DEL_SAMPLE_ORDER_F', '-6');
define('ACTION_LOG_SYS_CHANGE_SAMPLE_ORDER_STATUS', 'action_log_sys_change_sample_order_status');
define('ACTION_LOG_SYS_CHANGE_SAMPLE_ORDER_STATUS_S', '7');
define('ACTION_LOG_SYS_CHANGE_SAMPLE_ORDER_STATUS_F', '-7');
define('ACTION_LOG_SYS_MOD_SAMPLE_ORDER', 'action_log_sys_mod_sample_order');
define('ACTION_LOG_SYS_MOD_SAMPLE_ORDER_S', '8');
define('ACTION_LOG_SYS_MOD_SAMPLE_ORDER_F', '-8');
define('ACTION_LOG_SYS_COPY_SAMPLE_ORDER', 'action_log_sys_copy_sample_order');
define('ACTION_LOG_SYS_COPY_SAMPLE_ORDER_S', '9');
define('ACTION_LOG_SYS_COPY_SAMPLE_ORDER_F', '-9');
define('ACTION_LOG_SYS_APPEND_SAMPLE_ORDER', 'action_log_sys_append_sample_order');
define('ACTION_LOG_SYS_APPEND_SAMPLE_ORDER_S', '10');
define('ACTION_LOG_SYS_APPEND_SAMPLE_ORDER_F', '-10');

define('ACTION_LOG_SYS_ADD_QUOTATION', 'action_log_sys_add_quotation');
define('ACTION_LOG_SYS_ADD_QUOTATION_S', '11');
define('ACTION_LOG_SYS_ADD_QUOTATION_F', '-11');
define('ACTION_LOG_SYS_DEL_QUOTATION', 'action_log_sys_del_quotation');
define('ACTION_LOG_SYS_DEL_QUOTATION_S', '12');
define('ACTION_LOG_SYS_DEL_QUOTATION_F', '-12');
define('ACTION_LOG_SYS_MOD_QUOTATION', 'action_log_sys_mod_quotation');
define('ACTION_LOG_SYS_MOD_QUOTATION_S', '13');
define('ACTION_LOG_SYS_MOD_QUOTATION_F', '-13');

define('ACTION_LOG_SYS_ADD_PROFORMA', 'action_log_sys_add_proforma');
define('ACTION_LOG_SYS_ADD_PROFORMA_S', '14');
define('ACTION_LOG_SYS_ADD_PROFORMA_F', '-14');
define('ACTION_LOG_SYS_DEL_PROFORMA', 'action_log_sys_del_proforma');
define('ACTION_LOG_SYS_DEL_PROFORMA_S', '15');
define('ACTION_LOG_SYS_DEL_PROFORMA_F', '-15');
define('ACTION_LOG_SYS_MOD_PROFORMA', 'action_log_sys_mod_proforma');
define('ACTION_LOG_SYS_MOD_PROFORMA_S', '16');
define('ACTION_LOG_SYS_MOD_PROFORMA_F', '-16');
define('ACTION_LOG_SYS_ADD_PROFORMA_FROM_QUOTATION', 'action_log_sys_add_proforma_from_quotation');
define('ACTION_LOG_SYS_ADD_PROFORMA_FROM_QUOTATION_S', '17');
define('ACTION_LOG_SYS_ADD_PROFORMA_FROM_QUOTATION_F', '-17');
define('ACTION_LOG_SYS_COPY_PROFORMA', 'action_log_sys_copy_proforma');
define('ACTION_LOG_SYS_COPY_PROFORMA_S', '18');
define('ACTION_LOG_SYS_COPY_PROFORMA_F', '-18');
define('ACTION_LOG_SYS_ADD_PROFORMA_FROM_SAMPLE_ORDER', 'action_log_sys_add_proforma_from_sample_order');
define('ACTION_LOG_SYS_ADD_PROFORMA_FROM_SAMPLE_ORDER_S', '19');
define('ACTION_LOG_SYS_ADD_PROFORMA_FROM_SAMPLE_ORDER_F', '-19');

define('ACTION_LOG_SYS_ADD_INVOICE', 'action_log_sys_add_invoice');
define('ACTION_LOG_SYS_ADD_INVOICE_S', '20');
define('ACTION_LOG_SYS_ADD_INVOICE_F', '-20');
define('ACTION_LOG_SYS_DEL_INVOICE', 'action_log_sys_del_invoice');
define('ACTION_LOG_SYS_DEL_INVOICE_S', '21');
define('ACTION_LOG_SYS_DEL_INVOICE_F', '-21');
define('ACTION_LOG_SYS_MOD_INVOICE', 'action_log_sys_mod_invoice');
define('ACTION_LOG_SYS_MOD_INVOICE_S', '22');
define('ACTION_LOG_SYS_MOD_INVOICE_F', '-22');
define('ACTION_LOG_SYS_ADD_INVOICE_FROM_PROFORMA', 'action_log_sys_add_invoice_from_proforma');
define('ACTION_LOG_SYS_ADD_INVOICE_FROM_PROFORMA_S', '23');
define('ACTION_LOG_SYS_ADD_INVOICE_FROM_PROFORMA_F', '-23');
define('ACTION_LOG_SYS_APPEND_INVOICE', 'action_log_sys_append_invoice');
define('ACTION_LOG_SYS_APPEND_INVOICE_S', '24');
define('ACTION_LOG_SYS_APPEND_INVOICE_F', '-24');

define('ACTION_LOG_SYS_ADD_PURCHASE', 'action_log_sys_add_purchase');
define('ACTION_LOG_SYS_ADD_PURCHASE_S', '25');
define('ACTION_LOG_SYS_ADD_PURCHASE_F', '-25');
define('ACTION_LOG_SYS_DEL_PURCHASE', 'action_log_sys_del_purchase');
define('ACTION_LOG_SYS_DEL_PURCHASE_S', '26');
define('ACTION_LOG_SYS_DEL_PURCHASE_F', '-26');
define('ACTION_LOG_SYS_MOD_PURCHASE', 'action_log_sys_mod_purchase');
define('ACTION_LOG_SYS_MOD_PURCHASE_S', '27');
define('ACTION_LOG_SYS_MOD_PURCHASE_F', '-27');
define('ACTION_LOG_SYS_ADD_PURCHASE_FROM_PROFORMA', 'action_log_sys_add_purchase_from_proforma');
define('ACTION_LOG_SYS_ADD_PURCHASE_FROM_PROFORMA_S', '28');
define('ACTION_LOG_SYS_ADD_PURCHASE_FROM_PROFORMA_F', '-28');
define('ACTION_LOG_SYS_COPY_PURCHASE', 'action_log_sys_copy_purchase');
define('ACTION_LOG_SYS_COPY_PURCHASE_S', '29');
define('ACTION_LOG_SYS_COPY_PURCHASE_F', '-29');

define('ACTION_LOG_SYS_ADD_CUSTOMS_INVOICE', 'action_log_sys_add_customs_invoice');
define('ACTION_LOG_SYS_ADD_CUSTOMS_INVOICE_S', '30');
define('ACTION_LOG_SYS_ADD_CUSTOMS_INVOICE_F', '-30');
define('ACTION_LOG_SYS_DEL_CUSTOMS_INVOICE', 'action_log_sys_del_customs_invoice');
define('ACTION_LOG_SYS_DEL_CUSTOMS_INVOICE_S', '31');
define('ACTION_LOG_SYS_DEL_CUSTOMS_INVOICE_F', '-31');
define('ACTION_LOG_SYS_MOD_CUSTOMS_INVOICE', 'action_log_sys_mod_customs_invoice');
define('ACTION_LOG_SYS_MOD_CUSTOMS_INVOICE_S', '32');
define('ACTION_LOG_SYS_MOD_CUSTOMS_INVOICE_F', '-32');
define('ACTION_LOG_SYS_ADD_CUSTOMS_INVOICE_FROM_INVOICE', 'action_log_sys_add_customs_invoice_from_invoice');
define('ACTION_LOG_SYS_ADD_CUSTOMS_INVOICE_FROM_INVOICE_S', '33');
define('ACTION_LOG_SYS_ADD_CUSTOMS_INVOICE_FROM_INVOICE_F', '-33');
define('ACTION_LOG_SYS_COMBINE_CUSTOMS_INVOICE_FROM_INVOICE', 'action_log_sys_combine_customs_invoice_from_invoice');
define('ACTION_LOG_SYS_COMBINE_CUSTOMS_INVOICE_FROM_INVOICE_S', '34');
define('ACTION_LOG_SYS_COMBINE_CUSTOMS_INVOICE_FROM_INVOICE_F', '-34');

define('ACTION_LOG_SYS_ADD_PACKING_LIST_FROM_INVOICE', 'action_log_sys_add_packing_list_from_invoice');
define('ACTION_LOG_SYS_ADD_PACKING_LIST_FROM_INVOICE_S', '35');
define('ACTION_LOG_SYS_ADD_PACKING_LIST_FROM_INVOICE_F', '-35');
define('ACTION_LOG_SYS_ADD_PACKING_LIST_FROM_DELIVERY', 'action_log_sys_add_packing_list_from_delivery');
define('ACTION_LOG_SYS_ADD_PACKING_LIST_FROM_DELIVERY_S', '36');
define('ACTION_LOG_SYS_ADD_PACKING_LIST_FROM_DELIVERY_F', '-36');
define('ACTION_LOG_SYS_DEL_PACKING_LIST', 'action_log_sys_del_packing_list');
define('ACTION_LOG_SYS_DEL_PACKING_LIST_S', '37');
define('ACTION_LOG_SYS_DEL_PACKING_LIST_F', '-37');
define('ACTION_LOG_SYS_MOD_PACKING_LIST', 'action_log_sys_mod_packing_list');
define('ACTION_LOG_SYS_MOD_PACKING_LIST_S', '38');
define('ACTION_LOG_SYS_MOD_PACKING_LIST_F', '-38');

define('ACTION_LOG_SYS_ADD_CREDIT_NOTE', 'action_log_sys_add_credit_note');
define('ACTION_LOG_SYS_ADD_CREDIT_NOTE_S', '39');
define('ACTION_LOG_SYS_ADD_CREDIT_NOTE_F', '-39');
define('ACTION_LOG_SYS_DEL_CREDIT_NOTE', 'action_log_sys_del_credit_note');
define('ACTION_LOG_SYS_DEL_CREDIT_NOTE_S', '40');
define('ACTION_LOG_SYS_DEL_CREDIT_NOTE_F', '-40');
define('ACTION_LOG_SYS_MOD_CREDIT_NOTE', 'action_log_sys_mod_credit_note');
define('ACTION_LOG_SYS_MOD_CREDIT_NOTE_S', '41');
define('ACTION_LOG_SYS_MOD_CREDIT_NOTE_F', '-41');

define('ACTION_LOG_SYS_ADD_PAYMENT_ADVICE', 'action_log_sys_add_payment_advice');
define('ACTION_LOG_SYS_ADD_PAYMENT_ADVICE_S', '42');
define('ACTION_LOG_SYS_ADD_PAYMENT_ADVICE_F', '-42');
define('ACTION_LOG_SYS_DEL_PAYMENT_ADVICE', 'action_log_sys_del_payment_advice');
define('ACTION_LOG_SYS_DEL_PAYMENT_ADVICE_S', '43');
define('ACTION_LOG_SYS_DEL_PAYMENT_ADVICE_F', '-43');
define('ACTION_LOG_SYS_MOD_PAYMENT_ADVICE', 'action_log_sys_mod_payment_advice');
define('ACTION_LOG_SYS_MOD_PAYMENT_ADVICE_S', '44');
define('ACTION_LOG_SYS_MOD_PAYMENT_ADVICE_F', '-44');

define('ACTION_LOG_SYS_ADD_QC_SCHEDULE', 'action_log_sys_add_qc_schedule');
define('ACTION_LOG_SYS_ADD_QC_SCHEDULE_S', '45');
define('ACTION_LOG_SYS_ADD_QC_SCHEDULE_F', '-45');

define('ACTION_LOG_SYS_ADD_CUSTOMER', 'action_log_sys_add_customer');
define('ACTION_LOG_SYS_ADD_CUSTOMER_S', '46');
define('ACTION_LOG_SYS_ADD_CUSTOMER_F', '-46');
define('ACTION_LOG_SYS_DEL_CUSTOMER', 'action_log_sys_del_customer');
define('ACTION_LOG_SYS_DEL_CUSTOMER_S', '47');
define('ACTION_LOG_SYS_DEL_CUSTOMER_F', '-47');
define('ACTION_LOG_SYS_MOD_CUSTOMER', 'action_log_sys_mod_customer');
define('ACTION_LOG_SYS_MOD_CUSTOMER_S', '48');
define('ACTION_LOG_SYS_MOD_CUSTOMER_F', '-48');

define('ACTION_LOG_SYS_ADD_CONTACT', 'action_log_sys_add_contact');
define('ACTION_LOG_SYS_ADD_CONTACT_S', '49');
define('ACTION_LOG_SYS_ADD_CONTACT_F', '-49');
define('ACTION_LOG_SYS_DEL_CONTACT', 'action_log_sys_del_contact');
define('ACTION_LOG_SYS_DEL_CONTACT_S', '50');
define('ACTION_LOG_SYS_DEL_CONTACT_F', '-50');
define('ACTION_LOG_SYS_MOD_CONTACT', 'action_log_sys_mod_contact');
define('ACTION_LOG_SYS_MOD_CONTACT_S', '51');
define('ACTION_LOG_SYS_MOD_CONTACT_F', '-51');

define('ACTION_LOG_SYS_ADD_SUPPLIER', 'action_log_sys_add_supplier');
define('ACTION_LOG_SYS_ADD_SUPPLIER_S', '52');
define('ACTION_LOG_SYS_ADD_SUPPLIER_F', '-52');
define('ACTION_LOG_SYS_DEL_SUPPLIER', 'action_log_sys_del_supplier');
define('ACTION_LOG_SYS_DEL_SUPPLIER_S', '53');
define('ACTION_LOG_SYS_DEL_SUPPLIER_F', '-53');
define('ACTION_LOG_SYS_MOD_SUPPLIER', 'action_log_sys_mod_contact');
define('ACTION_LOG_SYS_MOD_SUPPLIER_S', '54');
define('ACTION_LOG_SYS_MOD_SUPPLIER_F', '-54');

define('ACTION_LOG_SYS_ADD_SYS_USER', 'action_log_sys_add_sys_user');
define('ACTION_LOG_SYS_ADD_SYS_USER_S', '55');
define('ACTION_LOG_SYS_ADD_SYS_USER_F', '-55');
define('ACTION_LOG_SYS_DEL_SYS_USER', 'action_log_sys_del_sys_user');
define('ACTION_LOG_SYS_DEL_SYS_USER_S', '56');
define('ACTION_LOG_SYS_DEL_SYS_USER_F', '-56');
define('ACTION_LOG_SYS_MOD_SYS_USER', 'action_log_sys_mod_sys_user');
define('ACTION_LOG_SYS_MOD_SYS_USER_S', '57');
define('ACTION_LOG_SYS_MOD_SYS_USER_F', '-57');

define('ACTION_LOG_SYS_ADD_FTY_USER', 'action_log_sys_add_fty_user');
define('ACTION_LOG_SYS_ADD_FTY_USER_S', '55');
define('ACTION_LOG_SYS_ADD_FTY_USER_F', '-55');
define('ACTION_LOG_SYS_DEL_FTY_USER', 'action_log_sys_del_fty_user');
define('ACTION_LOG_SYS_DEL_FTY_USER_S', '56');
define('ACTION_LOG_SYS_DEL_FTY_USER_F', '-56');
define('ACTION_LOG_SYS_MOD_FTY_USER', 'action_log_sys_mod_fty_user');
define('ACTION_LOG_SYS_MOD_FTY_USER_S', '57');
define('ACTION_LOG_SYS_MOD_FTY_USER_F', '-57');

define('ACTION_LOG_SYS_ADD_LUXCRAFT_USER', 'action_log_sys_add_luxcraft_user');
define('ACTION_LOG_SYS_ADD_LUXCRAFT_USER_S', '58');
define('ACTION_LOG_SYS_ADD_LUXCRAFT_USER_F', '-58');
define('ACTION_LOG_SYS_DEL_LUXCRAFT_USER', 'action_log_sys_del_luxcraft_user');
define('ACTION_LOG_SYS_DEL_LUXCRAFT_USER_S', '59');
define('ACTION_LOG_SYS_DEL_LUXCRAFT_USER_F', '-59');
define('ACTION_LOG_SYS_MOD_LUXCRAFT_USER', 'action_log_sys_mod_luxcraft_user');
define('ACTION_LOG_SYS_MOD_LUXCRAFT_USER_S', '60');
define('ACTION_LOG_SYS_MOD_LUXCRAFT_USER_F', '-60');

//20140601
define('ACTION_LOG_SYS_PAYMENT_REQUEST_APPROVE', 'action_log_sys_payment_request_approve');
define('ACTION_LOG_SYS_PAYMENT_REQUEST_APPROVE_S', '61');
define('ACTION_LOG_SYS_PAYMENT_REQUEST_APPROVE_F', '-61');
define('ACTION_LOG_SYS_PAYMENT_REQUEST_DISAPPROVE', 'action_log_sys_payment_request_disapprove');
define('ACTION_LOG_SYS_PAYMENT_REQUEST_DISAPPROVE_S', '62');
define('ACTION_LOG_SYS_PAYMENT_REQUEST_DISAPPROVE_F', '-62');

//20141124
define('ACTION_LOG_SYS_ADD_ITEM_TRANSFER_FORM', 'action_log_sys_add_item_transfer_form');
define('ACTION_LOG_SYS_ADD_ITEM_TRANSFER_FORM_S', '63');
define('ACTION_LOG_SYS_ADD_ITEM_TRANSFER_FORM_F', '-63');
define('ACTION_LOG_SYS_DEL_ITEM_TRANSFER_FORM', 'action_log_sys_del_item_transfer_form');
define('ACTION_LOG_SYS_DEL_ITEM_TRANSFER_FORM_S', '64');
define('ACTION_LOG_SYS_DEL_ITEM_TRANSFER_FORM_F', '-64');
define('ACTION_LOG_SYS_MOD_ITEM_TRANSFER_FORM', 'action_log_sys_mod_item_transfer_form');
define('ACTION_LOG_SYS_MOD_ITEM_TRANSFER_FORM_S', '65');
define('ACTION_LOG_SYS_MOD_ITEM_TRANSFER_FORM_F', '-65');

//20150427
define('ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS', 'action_log_sys_itp_change_pi_status');
define('ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS_S', '66');
define('ACTION_LOG_SYS_ITP_CHANGE_PI_STATUS_F', '-66');

//********** fty **********
define('ACTION_LOG_FTY_ADD_PRODUCT', 'action_log_fty_add_product');
define('ACTION_LOG_FTY_ADD_PRODUCT_S', '500');
define('ACTION_LOG_FTY_ADD_PRODUCT_F', '-500');
define('ACTION_LOG_FTY_DEL_PRODUCT', 'action_log_fty_del_product');
define('ACTION_LOG_FTY_DEL_PRODUCT_S', '501');
define('ACTION_LOG_FTY_DEL_PRODUCT_F', '-501');
define('ACTION_LOG_FTY_MOD_PRODUCT', 'action_log_fty_mod_product');
define('ACTION_LOG_FTY_MOD_PRODUCT_S', '502');
define('ACTION_LOG_FTY_MOD_PRODUCT_F', '-502');

define('ACTION_LOG_FTY_ADD_DELIVERY', 'action_log_fty_add_delivery');
define('ACTION_LOG_FTY_ADD_DELIVERY_S', '503');
define('ACTION_LOG_FTY_ADD_DELIVERY_F', '-503');
define('ACTION_LOG_FTY_DEL_DELIVERY', 'action_log_fty_del_delivery');
define('ACTION_LOG_FTY_DEL_DELIVERY_S', '504');
define('ACTION_LOG_FTY_DEL_DELIVERY_F', '-504');
define('ACTION_LOG_FTY_MOD_DELIVERY', 'action_log_fty_mod_delivery');
define('ACTION_LOG_FTY_MOD_DELIVERY_S', '505');
define('ACTION_LOG_FTY_MOD_DELIVERY_F', '-505');

define('ACTION_LOG_FTY_ADD_CLIENT', 'action_log_fty_add_client');
define('ACTION_LOG_FTY_ADD_CLIENT_S', '506');
define('ACTION_LOG_FTY_ADD_CLIENT_F', '-506');
define('ACTION_LOG_FTY_DEL_CLIENT', 'action_log_fty_del_client');
define('ACTION_LOG_FTY_DEL_CLIENT_S', '507');
define('ACTION_LOG_FTY_DEL_CLIENT_F', '-507');
define('ACTION_LOG_FTY_MOD_CLIENT', 'action_log_fty_mod_client');
define('ACTION_LOG_FTY_MOD_CLIENT_S', '508');
define('ACTION_LOG_FTY_MOD_CLIENT_F', '-508');

define('ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS', 'action_log_fty_add_delivery_change_po_status');
define('ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS_S', '509');
define('ACTION_LOG_FTY_ADD_DELIVERY_CHANGE_PO_STATUS_F', '-509');



//********** luxcraft **********
define('ACTION_LOG_LUXCRAFT_ADD_SALES_INVOICE', 'action_log_luxcraft_add_sales_invoice');
define('ACTION_LOG_LUXCRAFT_ADD_SALES_INVOICE_S', '1000');
define('ACTION_LOG_LUXCRAFT_ADD_SALES_INVOICE_F', '-1000');
define('ACTION_LOG_LUXCRAFT_DEL_SALES_INVOICE', 'action_log_luxcraft_del_sales_invoice');
define('ACTION_LOG_LUXCRAFT_DEL_SALES_INVOICE_S', '1001');
define('ACTION_LOG_LUXCRAFT_DEL_SALES_INVOICE_F', '-1001');




/*$gameList =  array(
	array('', GAME_SH, 'sh', IS_GAME),
);*/


//以下IP可以访问 SSO web service
$ssoWebServiceCallerIp	= array(
								'192.168.1.204',	//local IP
								'202.105.134.28',	//local internet IP
								'210.242.175.199',	//tw gocs
								'210.242.175.201',	//tw store
							);

//以下IP可以访问所有测试页面，不受日期限制
$TestUserIp			= array();

//mycard 流程走完时的页面URL, 需要根据它判断Refer，刷新用户的GO点数。
define('MYCARD_END_PAGE',		'https://www.mycard520.com.tw/MyCardStore/MyCardStoreMsg.aspx');

define('LOCKUP',				-9);										//锁定状态的值

define('ENCRYPT_PASSWORD_KEY',	'5GHmrnIldfE342adfa#$)a)*&!834AdnD2835');	//加密用户密码所使用的附加字符
define('ENCRYPT_FORM_KEY',		'Kn8f2:lji85Ndafh#%#(na8(*3LNP;dFafaDf');	//加密FORM所使用的KEY
define('ENCRYPT_KEY',			'5@Hnig46hg)2Dh5jgdaa#%nH!h4%#ndi)(a42');	//XXTEA加密所使用的默认KEY. 会影响到SSO
define('GOM_WS_CRYPT_KEY',		'n45@%naks:L8w2fy)KKd0(dfFH%#sdjfa');		//Web service hash用key
define('GENERAL_APP_CRYPT_KEY',	'43k*dnq82@581812t8ghUODEgjE*&#3452dfa');	//与APP通讯用的普通key


//-------------------------------------------------------------
//---------------------------提示信息---------------------------
//-------------------------------------------------------------
 
$words = array(
		'INDEX'				=> '返回首页',
		'MAIN'				=> '返回首页',
		'CLOSE'				=> '点击闭关',
		'BACK'				=> '返回上页',
		'REFRESH'			=> '继续',
		'LOGOUT'			=> '登出平台',
		'wrongCode'			=> '验证码错误，请重新填写。如遇到看不清的验证码，点击验证码图片即可更换。',
		'badRequest'		=> '请求发生错误，可能是操作时间太长，网页已失效，或者网络存在故障。重试一次可以解决此问题。',
		'systemError'		=> '系统发生错误，请重试或者联繫客服。',
		'queryError'		=> '系统查找资料时发生错误，请重试或联繫客服。',
		);

$weeks = array('星期天', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六');
define('SYSTEM_MAINTAIN', '系统维护中，暂停服务，请稍后再试。');

//-------------------------------------------------------------
//---------------------------DB 资料---------------------------
//-------------------------------------------------------------

/* 以下为范例...
$goDbInfo = array(
	'host' => 'localhost',
	'user' => 'user',
	'passwd' => 'pass',
	'database' => 'dbname',
	'port' => NULL,
	'socket' => NULL,
	'charset' => 'utf8',
	'prefix' => 'tw_',
);
*/

if(in_array($_SERVER['SERVER_NAME'], array('192.168.1.200', 'fjjozjn.vicp.cc'))){
	// tw server
	$domain = 'gameone.com.tw';
	$goDomain = 'https://go.'. $domain;
	$gocsDomain = 'http://gocs.'. $domain;
	$storeDomain = 'http://store.'. $domain;
	$prizeDomain = 'http://prize.'. $domain;
	$verifyDomain = 'http://verify.'. $domain;
	
	$goDbInfo = array(
		'host' => '127.0.0.1',
		'user' => 'root',
		'passwd' => 'admin',
		'database' => 'krnt_db',
		'port' => NULL,
		'socket' => NULL,
		'charset' => 'utf8',
	);
	
	$luxDbInfo = array(
		'host' => '127.0.0.1',
		'user' => 'root',
		'passwd' => 'admin',
		'database' => 'lux',
		'port' => NULL,
		'socket' => NULL,
		'charset' => 'utf8',
	);
}else{
	// local test
	$domain = 'sz.com';
	$goDomain = 'http://go.'. $domain;
	$gocsDomain = 'http://gocs.'. $domain;
	$storeDomain = 'http://store.'. $domain;
	$prizeDomain = 'http://prize.'. $domain;
	$verifyDomain = 'http://ks.'. $domain;
	
	$goDbInfo = array(
		'host' => '127.0.0.1',
		'user' => 'root',
		'passwd' => '123456',
		'database' => 'krnt_db',
		'port' => NULL,
		'socket' => NULL,
		'charset' => 'utf8',
	);
	
	$luxDbInfo = array(
		'host' => '127.0.0.1',
		'user' => 'root',
		'passwd' => '3993979102',
		'database' => 'lux',
		'port' => NULL,
		'socket' => NULL,
		'charset' => 'utf8',
	);
}


$alwaysDebugIpList	= array('202.105.134.28', '192.168.1.169', '192.168.1.25', '61.93.232.225');			//始终显示DEBUG信息的来源IP