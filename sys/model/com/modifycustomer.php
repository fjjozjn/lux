<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

judgeUserPermNew( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        //20170507 去除了外键限制，所以这里不直接删除，把删除的记录存到*_del表里
//        $rtn = $mysql->q('delete from customer where cid = ?', $_GET['delid']);
//        if($rtn){
//
//            //add action log
//            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
//                , $_SESSION['logininfo']['aID'], $ip_real
//                , ACTION_LOG_SYS_DEL_CUSTOMER, $_SESSION["logininfo"]["aName"]." <i>delete customer</i> ID:'".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_CUSTOMER_S, "", "", 0);
//
//            $myerror->ok('删除 customer 成功!', 'com-searchcustomer&page=1');
//        }else{
//            $myerror->error('删除 customer 失败!', 'com-searchcustomer&page=1');
//        }

        $rtn_customer = $mysql->qone('select * from customer where cid = ?', $_GET['delid']);
        if ($rtn_customer) {

            $mysql->q('START TRANSACTION');

            $rs1 = $mysql->q('insert into customer_del set cid=?,name=?,website=?,remark=?,markup_ratio=?,terms=?,deposit=?,balance=?,created_by=?,total_amount=?,production_packaging=?,production_shipmark=?,production_remarks=?,nature=?,country=?,service_required=?,company_owner=?,brands_owned=?,number_of_staff=?,year_of_formation=?,expected_order_qty=?,affordable_pricing=?,quality_requirement=?,business_potential=?,restricted_substance_requirement=?,lab_test_required=?,factory_audit=?,business_contract_required=?,valuable=?,del_date=?', $rtn_customer['cid'], $rtn_customer['name'], $rtn_customer['website'], $rtn_customer['remark'], $rtn_customer['markup_ratio'], $rtn_customer['terms'], $rtn_customer['deposit'], $rtn_customer['balance'], $rtn_customer['created_by'], $rtn_customer['total_amount'], $rtn_customer['production_packaging'], $rtn_customer['production_shipmark'], $rtn_customer['production_remarks'], $rtn_customer['nature'], $rtn_customer['country'], $rtn_customer['service_required'], $rtn_customer['company_owner'], $rtn_customer['brands_owned'], $rtn_customer['number_of_staff'], $rtn_customer['year_of_formation'], $rtn_customer['expected_order_qty'], $rtn_customer['affordable_pricing'], $rtn_customer['quality_requirement'], $rtn_customer['business_potential'], $rtn_customer['restricted_substance_requirement'], $rtn_customer['lab_test_required'], $rtn_customer['factory_audit'], $rtn_customer['business_contract_required'], $rtn_customer['valuable'], dateMore());
            $rs2 = $mysql->q('delete from customer where cid = ?', $_GET['delid']);
            if ($rs1 && $rs2) {

                $mysql->q('COMMIT');

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_DEL_CUSTOMER, $_SESSION["logininfo"]["aName"] . " <i>delete customer</i> ID:'" . $_GET['delid'] . "' in sys", ACTION_LOG_SYS_DEL_CUSTOMER_S, "", "", 0);

                $myerror->ok('删除 customer 成功!', 'com-searchcustomer&page=1');
            } else {

                $mysql->q('ROLLBACK');

                $myerror->error('删除 customer 失败!', 'com-searchcustomer&page=1');
            }
        }else{
            $myerror->error('删除 customer 失败', 'com-searchcustomer&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM customer WHERE cid = ?', $_GET['modid']);
        }else{
            die('Need modid!');
        }

        $goodsForm = new My_Forms();
        $formItems = array(

            'cid' => array('title' => 'Customer ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 5, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['cid'])?$mod_result['cid']:''),
            'created_by' => array('title' => 'Created by', 'type' => 'select', 'required' => 1, 'options' => $user, 'value' => isset($mod_result['created_by'])?$mod_result['created_by']:''),
            'name' => array('title' => 'Company Name', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 80, 'required' => 1, 'value' => isset($mod_result['name'])?$mod_result['name']:''),
            'valuable' => array('type' => 'checkbox', 'options' => array(array('Valuable', 1)), 'value' => isset($mod_result['valuable'])?$mod_result['valuable']:''),
            'markup_ratio' => array('title' => 'Markup Ratio', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['markup_ratio'])?$mod_result['markup_ratio']:''),
            //'terms' => array('title' => 'Terms', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['terms'])?$mod_result['terms']:''),
            'deposit' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 5, 'value' => isset($mod_result['deposit'])?$mod_result['deposit']:'0', 'addon' => 'style="width:50px"', 'required' => 1),
            'balance' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 5, 'value' => isset($mod_result['balance'])?$mod_result['balance']:'0', 'addon' => 'style="width:50px"', 'required' => 1),
            'website' => array('title' => 'Website', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['website'])?$mod_result['website']:''),
            'remark' => array('title' => 'Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),

            'nature' => array('title' => 'Nature', 'type' => 'select', 'options' => get_nature(), 'value' => isset($mod_result['nature'])?$mod_result['nature']:'', 'required' => 1),
            'country' => array('title' => 'Country', 'type' => 'select', 'options' => get_all_country(), 'value' => isset($mod_result['country'])?$mod_result['country']:'', 'required' => 1),
            'service_required' => array('title' => 'Service Required', 'type' => 'select', 'options' => get_service_required(), 'value' => isset($mod_result['service_required'])?$mod_result['service_required']:''),
            'company_owner' => array('title' => 'Company Owner', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['company_owner'])?$mod_result['company_owner']:''),
            'brands_owned' => array('title' => 'Brands owned', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['brands_owned'])?$mod_result['brands_owned']:''),
            'number_of_staff' => array('title' => 'Number of Staff', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['number_of_staff'])?$mod_result['number_of_staff']:''),
            'year_of_formation' => array('title' => 'Year of Formation', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['year_of_formation'])?$mod_result['year_of_formation']:''),
            'expected_order_qty' => array('title' => 'Expected Order Qty', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['expected_order_qty'])?$mod_result['expected_order_qty']:''),
            'affordable_pricing' => array('title' => 'Affordable pricing', 'type' => 'select', 'options' => get_affordable_pricing(), 'value' => isset($mod_result['affordable_pricing'])?$mod_result['affordable_pricing']:''),
            'quality_requirement' => array('title' => 'Quality requirement', 'type' => 'select', 'options' => get_quality_requirement(), 'value' => isset($mod_result['quality_requirement'])?$mod_result['quality_requirement']:''),
            'business_potential' => array('title' => 'Business Potential', 'type' => 'select', 'options' => get_business_potential(), 'value' => isset($mod_result['business_potential'])?$mod_result['business_potential']:''),
            'restricted_substance_requirement' => array('title' => 'Restricted Substance requirement', 'type' => 'select', 'options' => get_restricted_substance_requirement(), 'value' => isset($mod_result['restricted_substance_requirement'])?$mod_result['restricted_substance_requirement']:''),
            'lab_test_required' => array('title' => 'Lab Test Required', 'type' => 'select', 'options' => get_lab_test_required(), 'value' => isset($mod_result['lab_test_required'])?$mod_result['lab_test_required']:''),
            'factory_audit' => array('title' => 'Factory Audit', 'type' => 'select', 'options' => get_factory_audit(), 'value' => isset($mod_result['factory_audit'])?$mod_result['factory_audit']:''),
            'business_contract_required' => array('title' => 'Business Contract Required', 'type' => 'select', 'options' => get_business_contract_required(), 'value' => isset($mod_result['business_contract_required'])?$mod_result['business_contract_required']:''),

            'production_packaging' => array('title' => 'Production Packaging', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['production_packaging'])?$mod_result['production_packaging']:''),
            'production_shipmark' => array('title' => 'Production Shipmark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['production_shipmark'])?$mod_result['production_shipmark']:''),
            'production_remarks' => array('title' => 'Production Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['production_remarks'])?$mod_result['production_remarks']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){

            $name = $_POST['name'];
            $valuable = isset($_POST['valuable'])?$_POST['valuable']:2;
            $markup_ratio = $_POST['markup_ratio'];
            //$terms = $_POST['terms'];
            $deposit = $_POST['deposit'];
            $balance = $_POST['balance'];
            $website = $_POST['website'];
            $remark = $_POST['remark'];
            $created_by = $_POST['created_by'];

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

            $result = $mysql->q('update customer set name = ?, markup_ratio = ?, deposit = ?, balance = ?, website = ?, remark = ?, created_by = ?, nature = ?, country = ?, service_required = ?, company_owner = ?, brands_owned = ?, number_of_staff = ?, year_of_formation = ?, expected_order_qty = ?, affordable_pricing = ?, quality_requirement = ?, business_potential = ?, restricted_substance_requirement = ?, lab_test_required = ?, factory_audit = ?, business_contract_required = ?, production_packaging = ?, production_shipmark = ?, production_remarks = ?, valuable = ? where cid = ?', $name, $markup_ratio, $deposit, $balance, $website, $remark, $created_by, $nature, $country, $service_required, $company_owner, $brands_owned, $number_of_staff, $year_of_formation, $expected_order_qty, $affordable_pricing, $quality_requirement, $business_potential, $restricted_substance_requirement, $lab_test_required, $factory_audit, $business_contract_required, $production_packaging, $production_shipmark, $production_remarks, $valuable, $_GET['modid']);
            if($result){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_MOD_CUSTOMER, $_SESSION["logininfo"]["aName"]." <i>modify customer</i> ID:'".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_CUSTOMER_S, "", "", 0);

                $myerror->ok('修改 customer 成功!', 'com-searchcustomer&page=1');
            }else{
                $myerror->error('修改 customer 失败', 'BACK');
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


        ?>
        <h1 class="green">CUSTOMER<em>* item must be filled in</em></h1>

        <fieldset class="center2col" style="width:60%">
            <legend class='legend'>Action</legend>
            <div style="margin-left:28px;"><a class="button" href="?act=com-c_addcontact&cid=<?=$mod_result['cid']?>" onclick="return pdfConfirm()">Add Contact</a><a target="_blank" class="button" href="?act=com-c_searchcontact&page=1&cid=<?=$mod_result['cid']?>">View Contact</a></div>
        </fieldset>

        <fieldset class="center2col" style="width:60%">
            <legend class='legend'>Modify Customer</legend>
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
                <tr>
                <tr class="formtitle">
                    <td width="6%">&nbsp;</td>
                    <td>Payment Terms:&nbsp;</td>
                    <td><? $goodsForm->show('deposit');?></td>
                    <td>&nbsp;% Deposit, Balance&nbsp;</td>
                    <td><? $goodsForm->show('balance');?></td>
                    <td>&nbsp;days after delivery</td>
                </tr>
                </tr>
            </table>
            <table>
                <tr valign="top">
                    <td colspan="2"><? $goodsForm->show('remark');?></td>
                    <td width="33%"><? $goodsForm->show('created_by');?></td>
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
}
?>
