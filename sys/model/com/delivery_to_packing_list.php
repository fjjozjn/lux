<?php


if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//禁止其他用户进入（临时做法）
/*
if($_SESSION['ftylogininfo']['aName'] != 'zjn' && $_SESSION['ftylogininfo']['aName'] != 'KEVIN'){
	$myerror->error('测试中，未开放！', 'main');
}
*/

$goodsForm = new My_Forms();
$formItems = array(
    'did' => array('type' => 'select', 'options' => $did),
    'ship_to' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2, 'required' => 1, 'nostar' => true, 'addon' => 'style="width:200px"'),
    'tel' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20),
    'wh_id' => array('type' => 'select', 'options' => get_warehouse_info(1), 'required' => 1, 'nostar' => true),
    'delivery_date' => array('type' => 'text', 'restrict' => 'date', 'value' => date('Y-m-d'), 'required' => 1, 'nostar' => true),
    'unit' => array('type' => 'select', 'options' => $unit, 'value' => 'PCS'),
    'reference' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50),
    'courier_or_forwarder' => array('type' => 'select', 'options' => get_courier_or_forwarder(), 'required' => 1, 'nostar' => true),
    'awb' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'required' => 1, 'nostar' => true),
    'cost' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'nostar' => true),
    'cost_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 30),
    'remark' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 500),
    'submitbtn'	=> array('type' => 'submit', 'value' => 'submit'),
);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){

    $pl_id= autoGenerationID();
    //fb($pl_id);die();


    //这个'8'值是除item外的post的个数，有个隐藏的箱数总和
    //去掉箱数总和，所以现在是7了
    //加了remark，所以现在是8了
    //20140604 加了 warehouse，现在是9了
    //20140622 加了 delivery date ，现在是10了
    //20140628 加了 cof awb cost costremark，现在是14了
    $num_except_item = 14;
    //'9'这个值随js里item的post个数改变而改变
    // ref delivery去掉了，所以现在是8了
    $num_item = 8;
    //这个只是item前的post个数
    $item_first = 12;


    //item_num 是 item 的个数
    $item_num = (count($_POST) - $num_except_item) / $num_item;
    //$myerror->info(count($_POST));
    //$myerror->info($item_num);
    //$myerror->info($_POST);

    $mypost = array();
    foreach($_POST as $v){
        $mypost[] = $v;
    }
    //fb($mypost);die();

    $cartno = array();

    $item = array();
    for($i = 0; $i < $item_num; $i++){
        $item[$i]['catno'] = $mypost[$i*$num_item+$item_first];

        //计算总箱数
        if(!in_array($item[$i]['catno'], $cartno)){
            $cartno[] = $item[$i]['catno'];
        }

        $item[$i]['qty'] = $mypost[$i*$num_item+$item_first+1];
        $item[$i]['weight'] = $mypost[$i*$num_item+$item_first+2];
        $item[$i]['size_l'] = $mypost[$i*$num_item+$item_first+3];
        $item[$i]['size_w'] = $mypost[$i*$num_item+$item_first+4];
        $item[$i]['size_h'] = $mypost[$i*$num_item+$item_first+5];
        $item[$i]['hidden_ref_no'] = $mypost[$i*$num_item+$item_first+6];
        $item[$i]['hidden_pid'] = $mypost[$i*$num_item+$item_first+7];
    }

    $now = dateMore();

    //fb($_POST);fb($item);//die();

    //工厂自己的出货单号必须是唯一的
    $rs = $mysql->q('select pl_id from packing_list where pl_id = ?', $pl_id);
    if(!$rs){
        $rs = $mysql->q('insert into packing_list (pl_id, ship_to, wh_id, delivery_date, po_no, tel, reference_no, unit, courier_or_forwarder, awb, cost, cost_remark, total_cart, total_qty, total_weight, total_cbm, in_date, mod_date, printed_date, created_by, mod_by, remark) values ('.moreQm(22).')', $pl_id, $_POST['ship_to'], $_POST['wh_id'], $_POST['delivery_date'], '', $_POST['tel'], $_POST['reference'], $_POST['unit'], $_POST['courier_or_forwarder'], $_POST['awb'], $_POST['cost'], $_POST['cost_remark'], count($cartno), '', '', '', $now, $now, '', $_SESSION['logininfo']['aName'], '', $_POST['remark']);
        if($rs){

            //合计
            $total_qty = 0;
            $total_weight = 0;
            $total_cbm = 0;

            $dtpl_shipment = array();

            for($i = 0; $i < $item_num; $i++){
                //找出packing_list_item信息，因为有些信息不是输入框的，不能随表单提交
                $rtn = $mysql->qone('select c_code from delivery_item where d_id = ? and p_id = ?', $item[$i]['hidden_ref_no'], $item[$i]['hidden_pid']);
                $cbm = ($item[$i]['size_l']/100)*($item[$i]['size_w']/100)*($item[$i]['size_h']/100);
                $mysql->q('insert into packing_list_item values (NULL, '.moreQm(11).')',
                    $item[$i]['hidden_ref_no'],
                    $pl_id,
                    $item[$i]['catno'],
                    $rtn['c_code'],
                    $item[$i]['hidden_pid'],
                    $item[$i]['qty'],
                    $item[$i]['weight'],
                    $item[$i]['size_l'],
                    $item[$i]['size_w'],
                    $item[$i]['size_h'],
                    $cbm
                );

                //20150404 添加进warehouse记录
                if(isset($_POST['wh_id']) && $_POST['wh_id'] != ''){
                    $wh = explode('|', $_POST['wh_id']);
                    $rs = $mysql->q("update warehouse_item_unique set qty = qty - ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $item[$i]['qty'], $now, $_SESSION['logininfo']['aName'], $wh[0], $item[$i]['hidden_pid']);
                    if($rs){
                        $product = $mysql->qone('select cost_rmb, photos from product where pid = ?', $item[$i]['hidden_pid']);
                        $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', '', $wh[0],
                            $wh[1], $item[$i]['hidden_pid'], '-', $product['cost_rmb'], $item[$i]['qty'], $product['photos'], $now, $now, $_SESSION['logininfo']['aName'], $pl_id);
                    }
                }

                $total_qty += $item[$i]['qty'];
                $total_weight += $item[$i]['weight'];
                $total_cbm += $cbm;

                //20140629 更新shipment表
                //20150123
                @$dtpl_shipment[$item[$i]['hidden_ref_no']] += $item[$i]['qty'];
            }

            //fb($dtpl_shipment);die('@');
            //20140629 更新shipment表
            //20150123
            $hints = '';
            $status = '';
            foreach($dtpl_shipment as $key=>$v){

                $shipment_status = 'Partial';
                //返回结果是数组，出货单和其关联的pi、invoice是否完成
                $rtn = check_packing_list_delivery_item($key);
                if(!empty($rtn[$key]['pi'])){
                    foreach($rtn[$key]['pi'] as $key => $v){
                        if($v){
                            $shipment_status = 'Complete';
                        }

                        $param['pi_no'] = $key;
                        $param['awb'] = $_POST['awb'];
                        $param['s_date'] = $_POST['delivery_date'];
                        $param['cost'] = '';
                        $param['cost_remark'] = '';
                        $param['s_status'] = $shipment_status;
                        $param['courier_or_forwarder'] = $_POST['courier_or_forwarder'];
                        $param['pl_id'] = $pl_id;

                        /*if($rtn[$key]['invoice'][$key]){
                            $status = 1;
                        }
                        if($v){
                            $status = 2;
                        }*/

                        //统一用这个来添加shipment record，且更新invoice和proforma的status，$status参数1只更新invoice，2更新invoice和proforma
                        //$hints .= insert_shipment_record($param, $status);
                        $hints .= add_shipment_record($param);
                    }
                }
            }

            //更新total，保存这些total值，省得以后要用还要去计算
            $mysql->q('update packing_list set total_qty = ?, total_weight = ?, total_cbm = ? where pl_id = ?', $total_qty, $total_weight, $total_cbm, $pl_id);

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_ADD_PACKING_LIST_FROM_DELIVERY, $_SESSION["logininfo"]["aName"]." <i>add packing list</i> '".$pl_id."' from delivery in sys", ACTION_LOG_SYS_ADD_PACKING_LIST_FROM_DELIVERY_S, "", "", 0);

            $myerror->ok('Add Packing List success! <span style="color:red">'.$hints.'</span>',
                'com-searchpackinglist&page=1');
        }else{
            $myerror->error('System error, Add Packing List failure (ERROR 201303041641)', 'BACK');
        }
    }else{
        $myerror->warn($pl_id.' Already Exists. Add Packing List failure.', 'com-searchpackinglist&page=1');
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
    <!--h1 class="green">PRODUCT<em>* indicates required fields</em></h1-->



    <?php
    $goodsForm->begin();
    ?>

    <table width="85%" align="center">
        <tr align="center">
            <td colspan="4" class='headertitle'>Add Packing List</td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr class="formtitle">
            <td width="25%" align="right">Delivery NO. : </td>
            <td width="25%"><? $goodsForm->show('did');?></td></td>
            <td width="10%" align="left"><img title="添加" style="opacity: 0.5;" onclick="addPackingList2()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
            <td width="40%" align="left"><img title="删除" style="opacity: 0.5;" onclick="delPackingList2()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"><!--a id="delpurchase" onclick="delPurchase()" href="#">删除</a--></td>
        </tr>
    </table>
    <div class="line"></div>
    <table width="85%" align="center">
        <tr class="formtitle" valign="top">
            <td width="25%" align="right">Ship To :<h6 class="required">*</h6></td>
            <td width="25%"><? $goodsForm->show('ship_to');?></td>
            <td width="25%" align="right">Tel : </td>
            <td width="25%"><? $goodsForm->show('tel');?></td>
        </tr>
        <tr class="formtitle" valign="top">
            <td width="25%" align="right">From Warehouse :<h6 class="required">*</h6></td>
            <td width="25%"><? $goodsForm->show('wh_id');?></td>
            <td width="25%" align="right">Delivery Date :<h6 class="required">*</h6></td>
            <td width="25%"><? $goodsForm->show('delivery_date');?></td>
        </tr>
        <tr class="formtitle" valign="top">
            <td width="25%" align="right">Unit : </td>
            <td width="25%"><? $goodsForm->show('unit');?></td>
            <td width="25%" align="right">Reference : </td>
            <td width="25%"><? $goodsForm->show('reference');?></td>
        </tr>
    </table>
    <div class="line"></div>
    <table width="85%" align="center">
        <tr class="formtitle" valign="top">
            <td width="25%" align="right">Courier / forwarder :<h6 class="required">*</h6></td>
            <td width="25%"><? $goodsForm->show('courier_or_forwarder');?></td>
            <td width="25%" align="right">Waybill # :<h6 class="required">*</h6></td>
            <td width="25%"><? $goodsForm->show('awb');?></td>
        </tr>
        <tr class="formtitle" valign="top">
            <td width="25%" align="right">Delivery Cost / FOB Charge(HKD) :<h6 class="required">*</h6></td>
            <td width="25%"><? $goodsForm->show('cost');?></td>
            <td width="25%" align="right">Cost Remark : </td>
            <td width="25%"><? $goodsForm->show('cost_remark');?></td>
        </tr>
    </table>

    <br />
    <table id="packing_list" width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3'>
        <tr bgcolor='#EEEEEE' align="center">
            <td>DELIVERY#</td>
            <td>CARTON#</td>
            <td>CLIENT#</td>
            <td>ITEM#</td>
            <td>QTY</td>
            <td>GROSS WEIGHT(KG)</td>
            <td colspan="3">MEASURMENT (L W H)(cm)</td>
            <td>CBM (L*W*H)(m³)</td>
            <td colspan="2">ACTION</td>
        </tr>
        <tbody id="tbody" class="packing_list" align="center"></tbody>
        <tr align="center">
            <td colspan="4">TOTAL : <span id="total_cart">0</span> CARTONS</td>
            <td><span id="total_qty"></span></td>
            <td><span id="total_weight"></span></td>
            <td colspan="3"></td>
            <td><span id="total_cbm"></span></td>
            <td>&nbsp;</td>
        </tr>
        <tr><td colspan="12">COUNTRY OF ORIGIN: CHINA</td></tr>
        <tr><td colspan="12">Remark : <? $goodsForm->show('remark');?></td></tr>
        <tr>
            <td colspan="9"></td>
            <td colspan="3"><? $goodsForm->show('submitbtn');?></td>
        </tr>
    </table>
    <br />
    <br />
    <br />
    <?
    $goodsForm->end();
}
?>
<script>
    //$(function(){

    //})
</script>