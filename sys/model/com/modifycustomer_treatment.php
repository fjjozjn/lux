<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();


if(isset($_GET['delid']) && $_GET['delid'] != ''){
	if (isSysAdmin()){
		$rtn2 = $mysql->q('delete from customer_treatment where id = ?', $_GET['delid']);
		if($rtn2){
			$myerror->ok('删除 Customer Treatment 成功!', 'com-searchcustomer_treatment&page=1');
		}else{
			$myerror->error('删除 Customer Treatment 失败!', 'com-searchcustomer_treatment&page=1');
		}
	}else{
		$myerror->error('无权限删除，删除 Customer Treatment 失败!', 'com-searchcustomer_treatment&page=1');
	}
}else{
	if(isset($_GET['modid']) && $_GET['modid'] != ''){
		$mod_result = $mysql->qone('SELECT * FROM customer_treatment WHERE id = ?', $_GET['modid']);	
	}else{
		die('Need modid!');	
	}
		
	$goodsForm = new My_Forms();
	$formItems = array(
			
			'customer_code' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['customer_code']),
			'customer_name' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['customer_name']),
			'mailing_address' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 100, 'addon' => 'style="width:300px"', 'value' => $mod_result['mailing_address']),
			'email_address' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['email_address']),
			'tel_and_fax' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['tel_and_fax']),
			'contact_person_or_position' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['contact_person_or_position']),
			'business_nature' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['business_nature']),
			'year_of_establishment' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['year_of_establishment']),
			'customer_client_base' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['customer_client_base']),
			'interested_product_range' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['interested_product_range']),
			'special_requirement_for_product' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 100, 'addon' => 'style="width:300px"', 'value' => $mod_result['special_requirement_for_product']),
			'target_price_range' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['target_price_range']),
			'order_quantity_per_style' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['order_quantity_per_style']),
			'annual_sales_turnover' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['annual_sales_turnover']),
			'annual_cost_turnover' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['annual_cost_turnover']),
			'buying_cost' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['buying_cost']),
			'nos_of_vendor_customer_has' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['nos_of_vendor_customer_has']),
			'customer_buying_season' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['customer_buying_season']),
			'customer_celebration_season' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['customer_celebration_season']),
			'trade_term' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['trade_term']),
			'payment_term' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['payment_term']),
			'banker_and_address' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['banker_and_address']),
			'mark_up_formula' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['mark_up_formula']),
			'shipping_company' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['shipping_company']),
			'shipping_marks' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['shipping_marks']),
			'port_of_loading' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['port_of_loading']),
			'port_of_discharge' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'style="width:300px"', 'value' => $mod_result['port_of_discharge']),
			'special_shipping_documents' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 100, 'addon' => 'style="width:300px"', 'value' => $mod_result['special_shipping_documents']),
			'creation_date' => array('type' => 'text', 'required' => 1, 'restrict' => 'date', 'value' => isset($mod_result['creation_date'])?date('Y-m-d', strtotime($mod_result['creation_date'])):''),
						
			'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
			);			
	$goodsForm->init($formItems);
	
	if(!$myerror->getAny() && $goodsForm->check()){
		$customer_code = $_POST['customer_code'];
		$customer_name = $_POST['customer_name'];
		$mailing_address = $_POST['mailing_address'];
		$email_address = $_POST['email_address'];
		$tel_and_fax = $_POST['tel_and_fax'];
		$contact_person_or_position = $_POST['contact_person_or_position'];
		$business_nature = $_POST['business_nature'];
		$year_of_establishment = $_POST['year_of_establishment'];
		$customer_client_base = $_POST['customer_client_base'];
		$interested_product_range = $_POST['interested_product_range'];
		$special_requirement_for_product = $_POST['special_requirement_for_product'];
		$target_price_range = $_POST['target_price_range'];
		$order_quantity_per_style = $_POST['order_quantity_per_style'];
		$annual_sales_turnover = $_POST['annual_sales_turnover'];
		$annual_cost_turnover = $_POST['annual_cost_turnover'];
		$buying_cost = $_POST['buying_cost'];
		$nos_of_vendor_customer_has = $_POST['nos_of_vendor_customer_has'];
		$customer_buying_season = $_POST['customer_buying_season'];
		$customer_celebration_season = $_POST['customer_celebration_season'];
		$trade_term = $_POST['trade_term'];
		$payment_term = $_POST['payment_term'];
		$banker_and_address = $_POST['banker_and_address'];
		$mark_up_formula = $_POST['mark_up_formula'];
		$shipping_company = $_POST['shipping_company'];
		$shipping_marks = $_POST['shipping_marks'];
		$port_of_loading = $_POST['port_of_loading'];
		$port_of_discharge = $_POST['port_of_discharge'];
		$special_shipping_documents = $_POST['special_shipping_documents'];
		$creation_date = $_POST['creation_date'];
		
		$result = $mysql->q('update customer_treatment set customer_code = ?, customer_name = ?, mailing_address = ?, email_address = ?, tel_and_fax = ?, contact_person_or_position = ?, business_nature = ?, year_of_establishment = ?, customer_client_base = ?, interested_product_range = ?, special_requirement_for_product = ?, target_price_range = ?, order_quantity_per_style = ?, annual_sales_turnover = ?, annual_cost_turnover = ?, buying_cost = ?, nos_of_vendor_customer_has = ?, customer_buying_season = ?, customer_celebration_season = ?, trade_term = ?, payment_term = ?, banker_and_address = ?, mark_up_formula = ?, shipping_company = ?, shipping_marks = ?, port_of_loading = ?, port_of_discharge = ?, special_shipping_documents = ?, creation_date = ?', $customer_code, $customer_name, $mailing_address, $email_address, $tel_and_fax, $contact_person_or_position, $business_nature, $year_of_establishment, $customer_client_base, $interested_product_range, $special_requirement_for_product, $target_price_range, $order_quantity_per_style, $annual_sales_turnover, $annual_cost_turnover, $buying_cost, $nos_of_vendor_customer_has, $customer_buying_season, $customer_celebration_season, $trade_term, $payment_term, $banker_and_address, $mark_up_formula, $shipping_company, $shipping_marks, $port_of_loading, $port_of_discharge, $special_shipping_documents, $creation_date);
		if($result){
			$myerror->ok('Modify Success!', 'com-searchcustomer_treatment&page=1');	
		}else{
			$myerror->error('Modify Failure!', 'com-searchcustomer_treatment&page=1');	
		}	
	}
}


if($myerror->getError()){
	require_once(ROOT_DIR.'model/inside_error.php');
}elseif($myerror->getOk()){
	require_once(ROOT_DIR.'model/inside_ok.php');
}else{
	if($myerror->getWarn()){
		require_once(ROOT_DIR.'model/inside_warn.php');	
	}

$goodsForm->begin();
?>
<table width="70%" id="table" class="formtitle" align="center">
	<tr><td class='headertitle' align="center">Customer Treatment</td></tr>
    <tr><td>
        <fieldset class="center2col"> 
        <legend class='legend'>Fill in the form</legend>
        <table width="100%">
            <tr>    
                <td style="font-size:20px">Creation Date : </td>
                <td><? $goodsForm->show('creation_date');?></td>
            </tr>        
            <tr>
                <td width="40%" style="font-size:20px">Customer Code : <!--h6 class="required">*</h6--></td>
                <td width="60%"><? $goodsForm->show('customer_code');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Customer Name : </td>
                <td><? $goodsForm->show('customer_name');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Mailing Address : </td>
                <td><? $goodsForm->show('mailing_address');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Email Address : </td>
                <td><? $goodsForm->show('email_address');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Tel. No. & Fax No. : </td>
                <td><? $goodsForm->show('tel_and_fax');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Contact Person/Position</td>
                <td><? $goodsForm->show('contact_person_or_position');?></td>
            </tr>  
            <tr>
                <td style="font-size:20px">Business Nature : </td>
                <td><? $goodsForm->show('business_nature');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Year of Establishment : </td>
                <td><? $goodsForm->show('year_of_establishment');?></td>
            </tr>  
            <tr>
                <td style="font-size:20px">Customer's Client Base : </td>
                <td><? $goodsForm->show('customer_client_base');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Interested Product Range : </td>
                <td><? $goodsForm->show('interested_product_range');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Special Requirement for product : </td>
                <td><? $goodsForm->show('special_requirement_for_product');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Target Price Range : </td>
                <td><? $goodsForm->show('target_price_range');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Order quantity per style : </td>
                <td><? $goodsForm->show('order_quantity_per_style');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Annual Sales Turnover : </td>
                <td><? $goodsForm->show('annual_sales_turnover');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Annual Cost Turnover : </td>
                <td><? $goodsForm->show('annual_cost_turnover');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Buying Cost : </td>
                <td><? $goodsForm->show('buying_cost');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Nos.of Vendor Customer has : </td>
                <td><? $goodsForm->show('nos_of_vendor_customer_has');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Customer buying season : </td>
                <td><? $goodsForm->show('customer_buying_season');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Customer celebration season : </td>
                <td><? $goodsForm->show('customer_celebration_season');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Trade Term : </td>
                <td><? $goodsForm->show('trade_term');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Payment Term : </td>
                <td><? $goodsForm->show('payment_term');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Banker & Address : </td>
                <td><? $goodsForm->show('banker_and_address');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Mark Up Formula : </td>
                <td><? $goodsForm->show('mark_up_formula');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Shipping Company : </td>
                <td><? $goodsForm->show('shipping_company');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Shipping Marks : </td>
                <td><? $goodsForm->show('shipping_marks');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Port of Loading : </td>
                <td><? $goodsForm->show('port_of_loading');?></td>
            </tr>
            <tr>
                <td style="font-size:20px">Port of Discharge : </td>
                <td><? $goodsForm->show('port_of_discharge');?></td>
            </tr>
            <tr>    
                <td style="font-size:20px">Special Shipping Documents : </td>
                <td><? $goodsForm->show('special_shipping_documents');?></td>
            </tr>                                                                                                             			
        </table>
        
        <div class="line"></div>
        <?
        $goodsForm->show('submitbtn');
        ?>
        </fieldset>
    </td></tr>
</table>        
<?
$goodsForm->end();

}
?>

