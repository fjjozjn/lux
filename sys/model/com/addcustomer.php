<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(

    'cid' => array('title' => 'Customer ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 5, 'required' => 1),
    'name' => array('title' => 'Company Name', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 80, 'required' => 1),
    'valuable' => array('type' => 'checkbox', 'options' => array(array('Valuable', 1))),
    'markup_ratio' => array('title' => 'Markup Ratio', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
    //'terms' => array('title' => 'Terms', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20),
    'deposit' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 5, 'addon' => 'style="width:50px"', 'required' => 1, 'value' => '0'),
    'balance' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 5, 'addon' => 'style="width:50px"', 'required' => 1, 'value' => '0'),
    'website' => array('title' => 'Website', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'remark' => array('title' => 'Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50),

    'nature' => array('title' => 'Nature', 'type' => 'select', 'options' => get_nature(), 'required' => 1),
    'country' => array('title' => 'Country', 'type' => 'select', 'options' => get_all_country(), 'required' => 1),
    'service_required' => array('title' => 'Service Required', 'type' => 'select', 'options' => get_service_required()),
    'company_owner' => array('title' => 'Company Owner', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'brands_owned' => array('title' => 'Brands owned', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'number_of_staff' => array('title' => 'Number of Staff', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'year_of_formation' => array('title' => 'Year of Formation', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'expected_order_qty' => array('title' => 'Expected Order Qty', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'affordable_pricing' => array('title' => 'Affordable pricing', 'type' => 'select', 'options' => get_affordable_pricing()),
    'quality_requirement' => array('title' => 'Quality requirement', 'type' => 'select', 'options' => get_quality_requirement()),
    'business_potential' => array('title' => 'Business Potential', 'type' => 'select', 'options' => get_business_potential()),
    'restricted_substance_requirement' => array('title' => 'Restricted Substance requirement', 'type' => 'select', 'options' => get_restricted_substance_requirement()),
    'lab_test_required' => array('title' => 'Lab Test Required', 'type' => 'select', 'options' => get_lab_test_required()),
    'factory_audit' => array('title' => 'Factory Audit', 'type' => 'select', 'options' => get_factory_audit()),
    'business_contract_required' => array('title' => 'Business Contract Required', 'type' => 'select', 'options' => get_business_contract_required()),

    'production_packaging' => array('title' => 'Production Packaging', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000),
    'production_shipmark' => array('title' => 'Production Shipmark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000),
    'production_remarks' => array('title' => 'Production Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
    $cid = $_POST['cid'];
    $name = trim($_POST['name']);//20121026 加name的时候在最前面加了个空格，能保存进数据库，但是，在选择custoemr的时候，ajax传这个值的时候，就把这个开头的空格给去掉了，就在数据库中找不到了，所以这里我就trim了一下
    $valuable = isset($_POST['valuable'])?$_POST['valuable']:2;
    $markup_ratio = $_POST['markup_ratio'];
    //$terms = $_POST['terms'];
    $deposit = $_POST['deposit'];
    $balance = $_POST['balance'];
    $website = $_POST['website'];
    $remark = $_POST['remark'];
    $created_by = $_SESSION['logininfo']['aName'];

    //add 20151202
    $nature = $_POST['nature'];
    $country = $_POST['country'];
    $service_required = $_POST['service_required'];
    $company_owner = $_POST['company_owner'];
    $brands_owned = $_POST['brands_owned'];
    $number_of_staff = $_POST['number_of_staff'];
    $year_of_formation = $_POST['year_of_formation'];
    $expected_order_qty = $_POST['expected_order_qty'];
    $affordable_pricing = $_POST['affordable_pricing'];
    $quality_requirement = $_POST['quality_requirement'];
    $business_potential = $_POST['business_potential'];
    $restricted_substance_requirement = $_POST['restricted_substance_requirement'];
    $lab_test_required = $_POST['lab_test_required'];
    $factory_audit = $_POST['factory_audit'];
    $business_contract_required = $_POST['business_contract_required'];

    //add 201305241334
    $production_packaging = $_POST['production_packaging'];
    $production_shipmark = $_POST['production_shipmark'];
    $production_remarks = $_POST['production_remarks'];

    $result = $mysql->q('insert into customer set cid = ?, name = ?, website = ?, markup_ratio = ?, deposit = ?, balance = ?, remark = ?, created_by = ?, nature = ?, country = ?, service_required = ?, company_owner = ?, brands_owned = ?, number_of_staff = ?, year_of_formation = ?, expected_order_qty = ?, affordable_pricing = ?, quality_requirement = ?, business_potential = ?, restricted_substance_requirement = ?, lab_test_required = ?, factory_audit = ?, business_contract_required = ?, production_packaging = ?, production_shipmark = ?, production_remarks = ?, valuable = ?', $cid, $name, $website, $markup_ratio, $deposit, $balance, $remark, $created_by, $nature, $country, $service_required, $company_owner, $brands_owned, $number_of_staff, $year_of_formation, $expected_order_qty, $affordable_pricing, $quality_requirement, $business_potential, $restricted_substance_requirement, $lab_test_required, $factory_audit, $business_contract_required, $production_packaging, $production_shipmark, $production_remarks, $valuable);
    if($result){

        //add action log
        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['logininfo']['aID'], $ip_real
            , ACTION_LOG_SYS_ADD_CUSTOMER, $_SESSION["logininfo"]["aName"]." <i>add customer</i> ".$cid."----'".$name."' in sys", ACTION_LOG_SYS_ADD_CUSTOMER_S, "", "", 0);

        $myerror->ok('新增 customer 成功!', 'com-searchcustomer&page=1');
    }else{
        $myerror->error('新增 customer 失败', 'com-addcustomer');
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


    ?>
    <h1 class="green">CUSTOMER<em>* item must be filled in</em></h1>
    <fieldset class="center2col" style="width:60%">
        <legend class='legend'>Add Customer</legend>

        <?php
        $goodsForm->begin();
        ?>
        <table width="100%">
            <tr>
                <td width="33%"><? $goodsForm->show('cid');?></td>
                <td width="33%"><? $goodsForm->show('markup_ratio');?></td>
                <td width="33%"><? $goodsForm->show('website');?></td>
            </tr>
            <tr>
                <td colspan="2"><? $goodsForm->show('name');?></td>
                <td width="33%"><? $goodsForm->show('valuable');?></td>
            </tr>
        </table>
        <br />
        <table>
            <tr class="formtitle">
                <td width="6%">&nbsp;</td>
                <td>Payment Terms:&nbsp;</td>
                <td><? $goodsForm->show('deposit');?></td>
                <td>&nbsp;% Deposit, Balance&nbsp;</td>
                <td><? $goodsForm->show('balance');?></td>
                <td>&nbsp;days after delivery</td>
            </tr>
        </table>
        <table>
            <tr>
                <td colspan="2"><? $goodsForm->show('remark');?></td>
                <td width="33%">&nbsp;</td>
            </tr>
        </table>
        <div class="line"></div>
        <table width="100%">
            <tr valign="top">
                <td width="33%"><? $goodsForm->show('nature');?></td>
                <td width="33%"><? $goodsForm->show('country');?></td>
                <td width="33%"><? $goodsForm->show('service_required');?></td>
            </tr>
            <tr valign="top">
                <td width="33%"><? $goodsForm->show('company_owner');?></td>
                <td width="33%"><? $goodsForm->show('brands_owned');?></td>
                <td width="33%"><? $goodsForm->show('number_of_staff');?></td>
            </tr>
            <tr valign="top">
                <td width="33%"><? $goodsForm->show('year_of_formation');?></td>
                <td width="33%"><? $goodsForm->show('expected_order_qty');?></td>
                <td width="33%"><? $goodsForm->show('affordable_pricing');?></td>
            </tr>
            <tr valign="top">
                <td width="33%"><? $goodsForm->show('quality_requirement');?></td>
                <td width="33%"><? $goodsForm->show('business_potential');?></td>
                <td width="33%"><? $goodsForm->show('restricted_substance_requirement');?></td>
            </tr>
            <tr valign="top">
                <td width="33%"><? $goodsForm->show('lab_test_required');?></td>
                <td width="33%"><? $goodsForm->show('factory_audit');?></td>
                <td width="33%"><? $goodsForm->show('business_contract_required');?></td>
            </tr>
        </table>
        <div class="line"></div>
        <table>
            <tr>
                <td colspan="2"><? $goodsForm->show('production_packaging');?></td>
                <td width="33%">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><? $goodsForm->show('production_shipmark');?></td>
                <td width="33%">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2"><? $goodsForm->show('production_remarks');?></td>
                <td width="33%">&nbsp;</td>
            </tr>
        </table>
        <div class="line"></div>
        <?

        $goodsForm->show('submitbtn');
        ?>

    </fieldset>
    <?
    $goodsForm->end();
}
?>