<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//禁止其他用户进入（临时做法）
/*
if($_SESSION['ftylogininfo']['aName'] != 'zjn' && $_SESSION['ftylogininfo']['aName'] != 'KEVIN'){
	$myerror->error('测试中，未开放！', 'main');
}
*/

//judgeFtyPerm();

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        /*
        if ($_SESSION['ftylogininfo']['aName'] != 'zjn' && $_SESSION['ftylogininfo']['aName'] != 'KEVIN'){
            $rtn = $mysql->q('update delivery set istatus = ? where pvid = ?', 'delete', $_GET['delid']);
            if($rtn){
                $myerror->ok('删除 Proforma 成功!', 'com-searchproforma&page=1');
            }else{
                $myerror->error('删除 Proforma 失败!', 'com-searchproforma&page=1');
            }
        }else{
            */

        //20150404 添加进warehouse记录
        $pl_info = $mysql->qone('select * from packing_list where pl_id = ?', $_GET['delid']);
        if(isset($pl_info['wh_id']) && $pl_info['wh_id'] != ''){
            $wh = explode('|', $pl_info['wh_id']);
            $packing_list_rs = $mysql->q('select * from packing_list_item where pl_id = ?', $_GET['delid']);
            if($packing_list_rs){
                $pl_rtn = $mysql->fetch();
                foreach($pl_rtn as $v){
                    $rs = $mysql->q("update warehouse_item_unique set qty = qty + ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $v['qty'], dateMore(), $_SESSION['logininfo']['aName'], $wh[0], $v['item']);
                    if($rs){
                        $product = $mysql->qone('select cost_rmb, photos from product where pid = ?', $v['item']);
                        $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', '', $wh[0],
                            $wh[1], $v['item'], '+', $product['cost_rmb'], $v['qty'], $product['photos'], dateMore(), dateMore(), $_SESSION['logininfo']['aName'], $_GET['delid']);
                    }
                }
            }
        }

        //由於指定了foreign key，所以要先刪item裏的內容
        $rtn1 = $mysql->q('delete from packing_list_item where pl_id = ?', $_GET['delid']);
        $rtn2 = $mysql->q('delete from packing_list where pl_id = ?', $_GET['delid']);
        if($rtn2){
            //$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, 5, $_SESSION['ftylogininfo']['aName']." ".$_GET['delid']." (S) TO (I)", 15, "", "", 0);

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_PACKING_LIST, $_SESSION["logininfo"]["aName"]." <i>delete packing list</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_PACKING_LIST_S, "", "", 0);

            $myerror->ok('Delete Packing List success!', 'com-searchpackinglist&page=1');
        }else{
            $myerror->error('Delete Packing List failure!', 'com-searchpackinglist&page=1');
        }
        //}
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM packing_list WHERE pl_id = ?', $_GET['modid']);

            $mod_item_result = $mysql->q('SELECT * FROM packing_list_item WHERE pl_id = ?', $_GET['modid']);
            $pl_item_rtn = $mysql->fetch();
            //fb($pl_item_rtn);die();
            $pl_item_num_mod = count($pl_item_rtn);

            // $type 表示packing list 是从invoice或是从delivery转过来的
            $type = '';
            $cartno = array();
            //计算总箱数
            foreach($pl_item_rtn as $v){
                if($type == ''){
                    if(strpos($v['ref'], 'PI') !== false){
                        $type = 'invoice';
                    }elseif(strpos($v['ref'], '-') !== false){
                        $type = 'delivery';
                    }
                }
                if(!in_array($v['cart_no'], $cartno)){
                    $cartno[] = $v['cart_no'];
                }
            }
            $cartno_num = count($cartno);
        }else{
            die('Need modid!');
        }


        $goodsForm = new My_Forms();
        $formItems = array(
            'pl_id' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['pl_id'])?$mod_result['pl_id']:'', 'nostar' => true),
            //'vid' => array('type' => 'select', 'options' => get_invoice_no()),
            'ship_to' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2, 'required' => 1, 'value' => isset($mod_result['ship_to'])?$mod_result['ship_to']:'', 'nostar' => true),
            'tel' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['tel'])?$mod_result['tel']:''),
            'wh_id' => array('type' => 'select', 'options' => get_warehouse_info(1), 'required' => 1, 'value' => isset($mod_result['wh_id'])?$mod_result['wh_id']:'', 'nostar' => true),
            'delivery_date' => array('type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['delivery_date'])?substr($mod_result['delivery_date'], 0, 10):'', 'nostar' => true),
            'unit' => array('type' => 'select', 'options' => $unit, 'value' => isset($mod_result['unit'])?$mod_result['unit']:''),
            'reference' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($mod_result['reference_no'])?$mod_result['reference_no']:''),
            'courier_or_forwarder' => array('type' => 'select', 'options' => get_courier_or_forwarder(), 'required' => 1, 'value' => isset($mod_result['courier_or_forwarder'])?$mod_result['courier_or_forwarder']:'', 'nostar' => true),
            'awb' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'required' => 1, 'value' => isset($mod_result['awb'])?$mod_result['awb']:'', 'nostar' => true),
            'cost' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1,'value' => isset($mod_result['cost'])?$mod_result['cost']:'', 'nostar' => true),
            'cost_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($mod_result['cost_remark'])?$mod_result['cost_remark']:''),
            'remark' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 500, 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        for($i = 0; $i < $pl_item_num_mod; $i++){
            //去掉这里的 required => 1 是为了当直接删除dom后，无法通过js判断，就无法提交成功了 mod 20120717
            $formItems['cart_no'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($pl_item_rtn[$i]['cart_no'])?$pl_item_rtn[$i]['cart_no']:'', 'restrict' => 'number', /*'required' => 1, */'addon' => 'style="width:40px"; onblur="cart_blur(this)"');
            $formItems['qty'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($pl_item_rtn[$i]['qty'])?$pl_item_rtn[$i]['qty']:'', 'restrict' => 'number', 'addon' => 'style="width:40px" onblur="UpdateTotalQty()"');
            $formItems['gross_weight'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($pl_item_rtn[$i]['gross_weight'])?$pl_item_rtn[$i]['gross_weight']:'', 'addon' => 'style="width:80px" onblur="UpdateTotalWeight()"');
            $formItems['size_l'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($pl_item_rtn[$i]['size_l'])?$pl_item_rtn[$i]['size_l']:'', 'addon' => 'style="width:40px"; onblur="blurSizeL(this)"');
            $formItems['size_w'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($pl_item_rtn[$i]['size_w'])?$pl_item_rtn[$i]['size_w']:'', 'addon' => 'style="width:40px"; onblur="blurSizeW(this)"');
            $formItems['size_h'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($pl_item_rtn[$i]['size_h'])?$pl_item_rtn[$i]['size_h']:'', 'addon' => 'style="width:40px"; onblur="blurSizeH(this)"');
            $formItems['hidden_ref_no'.$i] = array('type' => 'hidden', 'value' => isset($pl_item_rtn[$i]['ref'])?$pl_item_rtn[$i]['ref']:'');
            $formItems['hidden_pid'.$i] = array('type' => 'hidden', 'value' => isset($pl_item_rtn[$i]['item'])?$pl_item_rtn[$i]['item']:'');
        }

        if($type == 'invoice'){
            $formItems['vid'] = array('type' => 'select', 'options' => get_invoice_no());
        }elseif($type == 'delivery'){
            $formItems['did'] = array('type' => 'select', 'options' => $did);
        }

        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){

            //fb($_POST);die();

            //这个'8'值是除item外的post的个数，有个隐藏的箱数总和
            //去掉箱数总和，所以现在是7了
            //modify 页面有个pl_id，所以现在是8了
            //加了remark，所以现在是9了
            //20140623 加了 wh_id 和 delivery date 所以现在是11了
            //20140628 加了 cof awb cost costremark，现在是15了
            $num_except_item = 15;
            //'8'这个值随item的post个数改变而改变
            $num_item = 8;
            //这个只是item前的post个数
            $item_first = 13;

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

            //fb($_POST);fb($item);die();

            $result = $mysql->q('update packing_list set ship_to = ?, tel = ?, wh_id = ?, delivery_date = ?, reference_no = ?, total_cart = ?, unit = ?, courier_or_forwarder = ?, awb = ?, cost = ?, cost_remark = ?, mod_date = ?, mod_by = ?, remark = ? where pl_id = ?', $_POST['ship_to'], $_POST['tel'], $_POST['wh_id'],$_POST['delivery_date'], $_POST['reference'], count($cartno), $_POST['unit'], $_POST['courier_or_forwarder'], $_POST['awb'], $_POST['cost'], $_POST['cost_remark'],
                $now,
                $_SESSION['logininfo']['aName'],
                $_POST['remark'],
                $_GET['modid']);

            //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
            if($result !== false){

                //20150404 添加进warehouse记录
                //注意加上的是旧的qty，不是新提交的qty
                if(isset($_POST['wh_id']) && $_POST['wh_id'] != ''){
                    $wh = explode('|', $_POST['wh_id']);
                    foreach($pl_item_rtn as $v){
                        $rs = $mysql->q("update warehouse_item_unique set qty = qty + ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $v['qty'], $now, $_SESSION['logininfo']['aName'], $wh[0], $v['item']);
                        if($rs){
                            $product = $mysql->qone('select cost_rmb, photos from product where pid = ?', $v['item']);
                            $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', '',
                                $wh[0], $wh[1], $v['item'], '+', $product['cost_rmb'], $v['qty'], $product['photos'], $now, $now, $_SESSION['logininfo']['aName'], $_GET['modid']);
                        }
                    }
                }

                $rtn = $mysql->q('delete from packing_list_item where pl_id = ?', $_GET['modid']);

                //合计
                $total_qty = 0;
                $total_weight = 0;
                $total_cbm = 0;

                $mpl_shipment = array();

                for($i = 0; $i < $item_num; $i++){
                    //找出packing_list_item信息，因为有些信息不是输入框的，不能随表单提交

                    //需要多一个get参数吗，不能统一吗
                    if($type == 'invoice'){
                        $rtn = $mysql->qone('select ccode from invoice_item where vid = ? and pid = ?', $item[$i]['hidden_ref_no'], $item[$i]['hidden_pid']);
                    }elseif($type == 'delivery'){
                        $rtn = $mysql->qone('select c_code as ccode from delivery_item where d_id = ? and p_id = ?', $item[$i]['hidden_ref_no'], $item[$i]['hidden_pid']);
                    }

                    $cbm = ($item[$i]['size_l']/100)*($item[$i]['size_w']/100)*($item[$i]['size_h']/100);
                    $mysql->q('insert into packing_list_item values (NULL, '.moreQm(11).')',
                        $item[$i]['hidden_ref_no'],
                        $_GET['modid'],
                        $item[$i]['catno'],
                        $rtn['ccode'],
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
                            $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', '',
                                $wh[0], $wh[1], $item[$i]['hidden_pid'], '-', $product['cost_rmb'], $item[$i]['qty'], $product['photos'], $now, $now, $_SESSION['logininfo']['aName'], $_GET['modid']);
                        }
                    }

                    $total_qty += $item[$i]['qty'];
                    $total_weight += $item[$i]['weight'];
                    $total_cbm += $cbm;

                    //20150212
                    @$mpl_shipment[$item[$i]['hidden_ref_no']] += $item[$i]['qty'];
                }

                //fb($shipment);
                //20140629 更新shipment表
                //20150212
                $hints = '';
                foreach($mpl_shipment as $key=>$v){

                    if($type == 'invoice'){

                        //返回结果是1：invoice(S)，2：invoice(S)和proforma(S)
                        //20150427
                        if(check_packing_list_invoice_item($key)){
                            $shipment_status = 'Complete';
                        }else{
                            $shipment_status = 'Partial';
                        }

                        $param['pi_no'] = $key;
                        $param['awb'] = $_POST['awb'];
                        $param['s_date'] = $_POST['delivery_date'];
                        $param['cost'] = '';
                        $param['cost_remark'] = '';
                        $param['s_status'] = $shipment_status;
                        $param['courier_or_forwarder'] = $_POST['courier_or_forwarder'];
                        $param['pl_id'] = $_GET['modid'];

                        //20150427
                        $hints .= add_shipment_record($param);

                    }elseif($type == 'delivery'){
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
                                $param['pl_id'] = $_GET['modid'];

                                $hints .= add_shipment_record($param);
                            }
                        }
                    }
                }

                //更新total，保存这些total值，省得以后要用还要去计算
                //20131013 cbm改保留3为小数
                $mysql->q('update packing_list set total_qty = ?, total_weight = ?, total_cbm = ? where pl_id = ?', $total_qty, $total_weight, round($total_cbm, 3), $_GET['modid']);

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_MOD_PACKING_LIST, $_SESSION["logininfo"]["aName"]." <i>modify packing list</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_PACKING_LIST_S, "", "", 0);

                $myerror->ok('Modify Packing List success!', 'com-searchpackinglist&page=1');
            }else{
                $myerror->error('Modify Packing List failure!', 'com-searchpackinglist&page=1');
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
        <!--h1 class="green">PROFORMA INVOICE<em>* item must be filled in</em></h1-->

        <?php
        $goodsForm->begin();
        ?>
        <table width="85%" align="center">
            <tr align="center">
                <td colspan="4" class='headertitle'>Modify Packing List</td>
            </tr>
            <tr class="formtitle">
                <td width="25%" align="right">Packing List :<h6 class="required">*</h6></td>
                <td width="25%"><? $goodsForm->show('pl_id');?></td>
            </tr>
            <tr class="formtitle">

                <? if($type == 'invoice'){ ?>
                    <td width="17%" align="right">Invoice NO. : </td>
                    <td width="33%"><? $goodsForm->show('vid');?></td></td>
                    <td width="10%" align="left"><img title="添加" style="opacity: 0.5;" onclick="addPackingList()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
                    <td width="40%" align="left"><img title="删除" style="opacity: 0.5;" onclick="delPackingList()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"><!--a id="delpurchase" onclick="delPurchase()" href="#">删除</a--></td>
                <? }elseif($type == 'delivery'){ ?>
                    <td width="17%" align="right">Delivery NO. : </td>
                    <td width="33%"><? $goodsForm->show('did');?></td></td>
                    <td width="10%" align="left"><img title="添加" style="opacity: 0.5;" onclick="addPackingList2()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
                    <td width="40%" align="left"><img title="删除" style="opacity: 0.5;" onclick="delPackingList2()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"><!--a id="delpurchase" onclick="delPurchase()" href="#">删除</a--></td>
                <? }?>

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
                <? if($type == 'invoice'){ ?>
                    <td>INVOICE#</td>
                <? }elseif($type == 'delivery'){ ?>
                    <td>DELIVERY#</td>
                <? } ?>
                <td>CARTON#</td>
                <td>CLIENT#</td>
                <td>ITEM#</td>
                <td>QTY</td>
                <td>GROSS WEIGHT(KG)</td>
                <td colspan="3">MEASURMENT (L W H)(cm)</td>
                <td>CBM (L*W*H)(m³)</td>
                <td colspan="2">ACTION</td>
            </tr>
            <tbody id="tbody" class="packing_list" align="center">
            <?
            if(isset($_GET['modid'])){

                $total_cart = 0;
                $total_qty = 0;
                $total_weight = 0;
                $total_cbm = 0;

                for($i = 0; $i < $pl_item_num_mod; $i++){
                    //fb($goodsForm->show('box_num'.$i));
                    echo '<tr id="'.$pl_item_rtn[$i]['item'].'" name="'.$pl_item_rtn[$i]['ref'].'">
				<td>'.$pl_item_rtn[$i]['ref'].'</td>
				<td>';
                    $goodsForm->show('cart_no'.$i);
                    echo '</td>
				<td>'.$pl_item_rtn[$i]['client_no'].'</td>
				<td>'.$pl_item_rtn[$i]['item'].'</td>
				<td>';
                    $goodsForm->show('qty'.$i);
                    echo '</td>
				<td>';
                    $goodsForm->show('gross_weight'.$i);
                    echo '</td>
				<td>';
                    $goodsForm->show('size_l'.$i);
                    echo '</td>
				<td>';
                    $goodsForm->show('size_w'.$i);
                    echo '</td>
				<td>';
                    $goodsForm->show('size_h'.$i);$goodsForm->show('hidden_ref_no'.$i);$goodsForm->show('hidden_pid'.$i);
                    echo '</td>
				<td id="cbm">'.$pl_item_rtn[$i]['cbm'].'</td>
				<td><img id="'.$pl_item_rtn[$i]['ref'].'" title="复制" style="opacity: 0.5;" onclick="copyItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/copy_small.png"></td>
				<td><img id="'.$pl_item_rtn[$i]['ref'].'" title="删除" style="opacity: 0.5;" onclick="delItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del2_small.png"></td>
				</tr>';

                    //$total_all += $pl_item_rtn[$i]['quantity']*$pl_item_rtn[$i]['price'];
                    //$total_weight += $pl_item_rtn[$i]['weight'];
                }
                //$total_all += $mod_result['express_cost'];
                echo '</tbody>';

                echo '<tr align="center">
				<td colspan="4">TOTAL : <span id="total_cart">'.$cartno_num.'</span> CARTONS</td>
				<td><span id="total_qty">'.$mod_result['total_qty'].'</span></td>
				<td><span id="total_weight">'.$mod_result['total_weight'].'</span></td>
				<td colspan="3"></td>
				<td><span id="total_cbm">'.$mod_result['total_cbm'].'</span></td>
			</tr> ';
                echo '<tr><td colspan="12">COUNTRY OF ORIGIN: CHINA`</td></tr> ';
                echo '<tr><td colspan="12">Remark :';
                $goodsForm->show('remark');
                echo '</td></tr> ';
                echo '<tr><td colspan="9"></td><td>';
                $goodsForm->show('submitbtn');
                echo '</td><td></td><td></td></tr>';
            }
            ?>
        </table>
        <br />
        <br />
        <br />
        <?
        $goodsForm->end();
    }
    ?>
    <script>
        $(function(){
            //20160512 发现js算出来的和数据库中保存的不同
//            UpdateTotalQty();
//            UpdateTotalWeight();
//            UpdateTotalCbm();
        })
    </script>

<?
}
?>