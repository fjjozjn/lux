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

        //20140503 update warehouse
        $rtn_delivery = $mysql->qone('select * from delivery where d_id = ?', $_GET['delid']);
        $rs = $mysql->q('select * from delivery_item where d_id = ?', $_GET['delid']);
        if($rs){
            $rtn_delivery_item = $mysql->fetch();

            foreach($rtn_delivery_item as $v){

                $rtn_wh = $mysql->qone('select wh_name from warehouse where id = ?', $rtn_delivery['wh_id']);
                $rtn_p = $mysql->qone('select description, description_chi, photos from product where pid = ?', $v['p_id']);

                $rs_warehouse = $mysql->q('insert into warehouse_item_hist (wh_id, wh_name, pid, action, cost_rmb, qty, photo, arrival_date, in_date, created_by, remark) values ('.moreQm(11).')', $rtn_delivery['wh_id'], $rtn_wh['wh_name'], $v['p_id'], '-', $v['price'], $v['quantity'], $rtn_p['photos'], $rtn_delivery['d_date'], dateMore(), $_SESSION['ftylogininfo']['aName'], 'Delete delivery item. '.$v['remark']);
                if($rs_warehouse){
                    if($mysql->q('select id from warehouse_item_unique where wh_id = ? and pid = ?', $rtn_delivery['wh_id'], $v['p_id'])){
                        $mysql->q('update warehouse_item_unique set qty = qty - ?, cost_rmb = ? where wh_id = ? and wh_name = ? and pid = ?', $v['quantity'], $v['price'], $rtn_delivery['wh_id'], $rtn_wh['wh_name'], $v['p_id']);
                    }else{
                        $mysql->q('insert into warehouse_item_unique (wh_id, wh_name, pid, description, description_chi, qty, cost_rmb, photo, arrival_date, in_date, created_by, remark) values ('.moreQm(12).')', $rtn_delivery['wh_id'], $rtn_wh['wh_name'], $v['p_id'], $rtn_p['description'], $rtn_p['description_chi'], -$v['quantity'], $v['price'], $rtn_p['photos'], $rtn_delivery['d_date'], dateMore(), $_SESSION['ftylogininfo']['aName'], 'Add from delivery. '.$v['remark']);
                    }
                }

            }
        }

			//由於指定了foreign key，所以要先刪proforma_item裏的內容
			$rtn1 = $mysql->q('delete from delivery_item where d_id = ?', $_GET['delid']);
			$rtn2 = $mysql->q('delete from delivery where d_id = ?', $_GET['delid']);
			if($rtn2){
				//更新 purchase 状态
				$mysql->q('update purchase set istatus = ? where pcid = ?', '(I)', $_GET['delid']);
				$mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)', 0, $ip_real, 5, $_SESSION['ftylogininfo']['aName']." ".$_GET['delid']." (S) TO (I)", 15, "", "", 0);
				//删除overheads项
				//$mysql->q('delete from overheads where po_no = ? and cost_remark = ?', $_GET['delid'], 'add by system');

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['ftylogininfo']['aID'], $ip_real
                    , ACTION_LOG_FTY_DEL_DELIVERY, $_SESSION["ftylogininfo"]["aName"]." <i>delete delivery</i> '".$_GET['delid']."' in fty", ACTION_LOG_FTY_DEL_DELIVERY_S, "", "", 0);

				$myerror->ok('删除 出货单 成功!', 'searchdelivery&page=1');
			}else{
				$myerror->error('删除 出货单 失败!', 'searchdelivery&page=1');	
			}
		//}
	}else{
		if(isset($_GET['modid']) && $_GET['modid'] != ''){
			$mod_result = $mysql->qone('SELECT * FROM delivery WHERE d_id = ?', $_GET['modid']);

            if(isset($mod_result['wh_id']) && $mod_result['wh_id'] != ''){
                $rtn_wh = $mysql->qone('select wh_name from warehouse where id = ?', $mod_result['wh_id']);
            }

            //20140222 获取工厂名
            $rtn_supplier = $mysql->qone('select name from supplier where sid = ?', $mod_result['sid']);
			
			$mod_item_result = $mysql->q('SELECT * FROM delivery_item WHERE d_id = ?', $_GET['modid']);
			$d_item_rtn = $mysql->fetch();
			//fb($d_item_rtn);die();
			$d_item_num_mod = count($d_item_rtn);
			
		}else{
			die('Need modid!');	
		}


        $goodsForm = new My_Forms();
		$formItems = array(
				
			'd_id' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['d_id'])?$mod_result['d_id']:'', 'nostar' => true),
			'fty_id' => array('type' => 'select', 'options' => get_fty_purchase()),
			'client_company' => array('type' => 'select', 'options' => get_fty_client_company(), 'value' => isset($mod_result['client_company'])?$mod_result['client_company']:''),
//			'client_address' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 100, 'value' => isset($mod_result['client_address'])?$mod_result['client_address']:''),
            'wh_id' => array('type' => 'select', 'options' => get_warehouse_info(2), 'required' => 1, 'value' => isset($mod_result['wh_id'])?($mod_result['wh_id'].'|'.$rtn_wh['wh_name']):'', 'nostar' => true),
            'address' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 100, 'value' => isset($mod_result['address'])?$mod_result['address']:'', 'addon' => 'style="width:200px"'),
            'express_cost' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['express_cost'])?$mod_result['express_cost']:'', 'restrict' => 'number', 'addon' => 'style="width:40px"', 'nostar' => true),
			'express_id' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['express_id'])?$mod_result['express_id']:'', 'addon' => 'style="width:100px"'),
			'staff' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['staff'])?$mod_result['staff']:'', 'addon' => 'style="width:100px"'),
			'd_date' => array('type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['d_date'])?date('Y-m-d', strtotime($mod_result['d_date'])):'', 'addon' => 'style="width:100px"', 'required' => 1, 'nostar' => true),
			'remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:250px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
						
			'submitbtn'	=> array('type' => 'submit', 'value' => '保存'),
			);
				
		for($i = 0; $i < $d_item_num_mod; $i++){
			//去掉这里的 required => 1 是为了当直接删除dom后，无法通过js判断，就无法提交成功了 mod 20120717
			$formItems['box_num'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($d_item_rtn[$i]['box_num'])?$d_item_rtn[$i]['box_num']:'', 'restrict' => 'number', /*'required' => 1, */'addon' => 'style="width:40px" onblur="boxnumBlur(this)"', 'nostar' => true);
			$formItems['inner_box_num'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($d_item_rtn[$i]['inner_box_num'])?$d_item_rtn[$i]['inner_box_num']:'', 'restrict' => 'number', /*'required' => 1,*/ 'addon' => 'style="width:40px"', 'nostar' => true);
			$formItems['po_id'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($d_item_rtn[$i]['po_id'])?$d_item_rtn[$i]['po_id']:'', 'addon' => 'style="width:105px"');
			$formItems['p_id'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($d_item_rtn[$i]['p_id'])?$d_item_rtn[$i]['p_id']:'');
			$formItems['quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($d_item_rtn[$i]['quantity'])?$d_item_rtn[$i]['quantity']:'', 'restrict' => 'number', 'addon' => 'style="width:40px" onblur="quantity_blur(this)"');
			$formItems['weight'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($d_item_rtn[$i]['weight'])?$d_item_rtn[$i]['weight']:'', 'addon' => 'style="width:40px" onblur="weight_blur()"');
			$formItems['size_l'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($d_item_rtn[$i]['size_l'])?$d_item_rtn[$i]['size_l']:'', 'addon' => 'style="width:40px"');
			$formItems['size_w'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($d_item_rtn[$i]['size_w'])?$d_item_rtn[$i]['size_w']:'', 'addon' => 'style="width:40px"');
			$formItems['size_h'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 10, 'value' => isset($d_item_rtn[$i]['size_h'])?$d_item_rtn[$i]['size_h']:'', 'addon' => 'style="width:40px"');
			$formItems['remark'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($d_item_rtn[$i]['remark'])?$d_item_rtn[$i]['remark']:'', 'addon' => 'style="width:100px"');
		}
				
		$goodsForm->init($formItems);
		
		if(!$myerror->getAny() && $goodsForm->check()){
			
			//'6'这个值是除item外的post的个数
			//'7'，这里比add情况多了个date的post，所以不是6是7
			//'8'，现在又有了个express_id了
			//20130228 多了个备注，所以是 '9' 了
			//20130228 多了client_company和client_address，就变成11了, 比add多了d_id框的显示所以item_first变成5了
			//2013 多了warehouse，就变成12了, 因为在前面，所以item_first变成6了
			$d_item_num = (count($_POST) - 12) / 10;//！！后面这个'10'这个值随js里item的post个数改变而改变
			$item_first = 6;
			//$myerror->info($item_num);		
			//$myerror->info($_POST);

            $wh = explode('|', $_POST['wh_id']);
            $wh_id = '';
            $wh_name = '';
            if(!empty($wh)){
                $wh_id = $wh[0];
                $wh_name = $wh[1];
            }

			$mypost = array();
			foreach($_POST as $v){
				$mypost[] = $v;	
			}
			//$myerror->info($mypost);

			$item = array();
			for($i = 0; $i < $d_item_num; $i++){		
				$item[$i]['box_num'] = $mypost[$i*10+$item_first];
				$item[$i]['inner_box_num'] = $mypost[$i*10+$item_first+1];
				$item[$i]['po_id'] = $mypost[$i*10+$item_first+2];
				$item[$i]['p_id'] = $mypost[$i*10+$item_first+3];
				$item[$i]['quantity'] = $mypost[$i*10+$item_first+4];
				$item[$i]['weight'] = $mypost[$i*10+$item_first+5];
				$item[$i]['size_l'] = $mypost[$i*10+$item_first+6];
				$item[$i]['size_w'] = $mypost[$i*10+$item_first+7];
				$item[$i]['size_h'] = $mypost[$i*10+$item_first+8];
				$item[$i]['remark'] = $mypost[$i*10+$item_first+9];		
			}

			//fb($_POST);fb($item);die();
			
			$result = $mysql->q('update delivery set d_date = ?, express_cost = ?, express_id = ?, staff = ?, remark = ?, client_company = ?, address = ?, client_address = ?, wh_id = ?, mod_date = ?, mod_by = ? where d_id = ?', $_POST['d_date'], $_POST['express_cost'], $_POST['express_id'], $_POST['staff'], $_POST['remark'], /*$_POST['client_company']*/'', $_POST['address'], /*$_POST['client_address']*/'', $wh_id, dateMore(), $_SESSION['ftylogininfo']['aName'], $_GET['modid']);
			
			//這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
			if($result !== false){
                //20130913 for warehouse
                $rs_old_delivery_item = $mysql->q('select p_id, quantity from delivery_item where d_id = ?', $_GET['modid']);
                if($rs_old_delivery_item){
                    $rtn_old_delivery_item = $mysql->fetch();
                }

				$rtn = $mysql->q('delete from delivery_item where d_id = ?', $_GET['modid']);
				$total_all = 0;
				for($k = 0; $k < $d_item_num; $k++){
					//找出purchase_item信息	
					//$rtn_purchase = $mysql->qone('select pc.price, pc.ccode, p.photos, p.description, p.description_chi from purchase_item pc, product p where pc.pid = p.pid and pc.pcid = ? and pc.pid = ?', $item[$k]['po_id'], $item[$k]['p_id']);
                    //mod 20141222 客户显示改为cid
                    $rtn_purchase = $mysql->qone('select p.cid, i.price, i.ccode, d.photos, d.description, d.description_chi from purchase u, purchase_item i, proforma p, product d where i.pcid = u.pcid and u.pcid = ? and p.pvid = u.reference and i.pid = ? and d.pid = i.pid', $item[$k]['po_id'], $item[$k]['p_id']);
					$rtn = $mysql->q('insert into delivery_item values (NULL, '.moreQm(15).')',
																 $_POST['d_id'], 
																 $item[$k]['po_id'], 
																 $item[$k]['box_num'], 
																 $item[$k]['inner_box_num'],
																 $rtn_purchase['cid'],
																 $item[$k]['p_id'], 
																 $rtn_purchase['ccode'], 
																 $item[$k]['quantity'], 
																 $rtn_purchase['price'], 
																 $item[$k]['quantity']*$rtn_purchase['price'], 
																 $item[$k]['weight'], 
																 $item[$k]['size_l'], 
																 $item[$k]['size_w'], 
																 $item[$k]['size_h'], 
																 $item[$k]['remark']
																 );
					$total_all += $item[$k]['quantity']*$rtn_purchase['price'];

                    //20130913 修改出货单也同时修改warehouse
                    //(remark 加了来源的标志)
                    //20131020 先不对warehouse有操作了,以后再说
                    //20140503 update warehouse
                    $update_qty = $item[$k]['quantity'] - $rtn_old_delivery_item[$k]['quantity'];
                    if($update_qty != 0){
                        $action = '';
                        if($update_qty > 0){
                            $action = '+';
                        }else{
                            $action = '-';
                        }
                        $rs_warehouse = $mysql->q('insert into warehouse_item_hist (wh_id, wh_name, pid, action, cost_rmb, qty, photo, arrival_date, in_date, created_by, remark) values ('.moreQm(11).')', $wh_id, $wh_name, $item[$k]['p_id'], $action, $rtn_purchase['price'], abs($update_qty), $rtn_purchase['photos'], $_POST['d_date'], dateMore(), $_SESSION['ftylogininfo']['aName'], 'Add from modify delivery. '.$item[$k]['remark']);

                        if($rs_warehouse){
                            if($mysql->q('select id from warehouse_item_unique where wh_id = ? and pid = ?', $wh_id, $item[$k]['p_id'])){
                                if($update_qty > 0){
                                    $mysql->q('update warehouse_item_unique set qty = qty + ?, cost_rmb = ? where wh_id = ? and wh_name = ? and pid = ?', abs($update_qty), $rtn_purchase['price'], $wh_id, $wh_name, $item[$k]['p_id']);
                                }else{
                                    $mysql->q('update warehouse_item_unique set qty = qty - ?, cost_rmb = ? where wh_id = ? and wh_name = ? and pid = ?', abs($update_qty), $rtn_purchase['price'], $wh_id, $wh_name, $item[$k]['p_id']);
                                }
                            }else{
                                $mysql->q('insert into warehouse_item_unique (wh_id, wh_name, pid, description, description_chi, qty, cost_rmb, photo, arrival_date, in_date, created_by, remark) values ('.moreQm(12).')', $wh_id, $wh_name, $item[$k]['p_id'], $rtn_purchase['description'], $rtn_purchase['description_chi'], $item[$k]['quantity'], $rtn_purchase['price'], $rtn_purchase['photos'], $_POST['d_date'], dateMore(), $_SESSION['ftylogininfo']['aName'], 'Add from modify delivery. '.$item[$k]['remark']);
                            }
                        }
                    }

				}
				$total_all += $_POST['express_cost'];
				//单独更新total_all，因直接能在insert item的循环里计算，不用再另外开一个循环
				$mysql->q('update delivery set total_all = ? where d_id = ?', $total_all, $_GET['modid']);


                //更新 purchase 状态
                //20141216 检查此出货单中的po item是否全部出完，如果是则改po状态
                $mysql->q('select po_id from delivery_item where d_id = ? group by po_id', $_GET['modid']);
                $po_rtn = $mysql->fetch();
                $po_status_tips = '';
                foreach($po_rtn as $v){
                    if(checkPurchaseItemIsOut($v['po_id'])){
                        $status = changePurchaseStatus($v['po_id'], '(S)');
                        $po_status_tips .= '(Change '.$v['po_id'].' status '.$status.')';
                    }else{
                        //20170730 如果修改了已经完成的，状态要回退
                        $status = changePurchaseStatus($v['po_id'], 'Not-Out');
                        if ($status) {
                            $po_status_tips .= '(Change '.$v['po_id'].' status '.$status.')';
                        }
                    }
                }


                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['ftylogininfo']['aID'], $ip_real
                    , ACTION_LOG_FTY_MOD_DELIVERY, $_SESSION["ftylogininfo"]["aName"]." <i>modify delivery</i> '".$_GET['modid']."' in fty", ACTION_LOG_FTY_MOD_DELIVERY_S, "", "", 0);

				$myerror->ok('修改 出货单 成功!'.$po_status_tips, 'searchdelivery&page=1');
		
			}else{
				$myerror->error('修改 出货单 失败', 'searchdelivery&page=1');	
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
    <table width="55%" align="center">
        <tr align="center">
            <td colspan="4" class='headertitle'>修改出货单</td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr class="formtitle">
            <td width="17%">出货单号 ：</td>
            <td width="33%"><? $goodsForm->show('d_id');?></td>
            <td width="17%">工厂名 ：</td>
            <td width="33%"><?=$rtn_supplier['name']?></td>
        </tr>
        <tr class="formtitle" valign="top">
            <td width="17%">客户公司 ：</td>
            <td width="33%"><? $goodsForm->show('client_company');?></td>
            <td width="17%"><? //客户公司地址 ：?></td>
            <td width="33%"><? //$goodsForm->show('client_address');?></td>
        </tr>
        <tr class="formtitle" valign="top">
            <td width="17%">仓库 ：</td>
            <td width="33%"><? $goodsForm->show('wh_id');?></td>
            <td width="17%">地址 ：</td>
            <td width="33%"><? $goodsForm->show('address');?></td>
        </tr>
    </table>    
    <div class="line"></div>
    <div class="line"></div>
    <table width="55%" align="center">       
        <tr class="formtitle">
            <td width="17%">订单号 ： </td>
            <td width="33%"><? $goodsForm->show('fty_id');?></td></td>
            <td width="10%" align="left"><img title="添加" style="opacity: 0.5;" onclick="addPurchase()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
            <td width="20%" align="left"><img title="删除" style="opacity: 0.5;" onclick="delPurchase()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"><!--a id="delpurchase" onclick="delPurchase()" href="#">删除</a--></td>
            <td width="20%"><a href="model/delivery_pdf.php?pdf=1&d_id=<?=$_GET['modid']?>" target="_blank"><img src="../../images/button_document-pdf.png" title="出货清单"></a</td>
        </tr>
    </table>
    <div class="line"></div>
    <br />
    <table width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' align="center">    
        <br />    
        <tr bgcolor='#EEEEEE'>  
            <th>箱数</th>
            <th>内箱</th>
            <th>订单号</th>
            <th>客户</th>
            <th>款号</th>
            <th>客号</th>
            <th>数量</th>
            <th>单价（元）</th>
            <th>金额（元）</th>
            <th>外箱重量（KG）</th>
            <th>尺寸-长（CM）</th>
            <th>尺寸-宽（CM）</th>
            <th>尺寸-高（CM）</th>
            <th>备注</th>
            <th colspan="2">操作</th>
        </tr>
        <tbody id="tbody" class="delivery" align="center">
        <?
		if(isset($_GET['modid'])){
			$total_all = 0;
			$total_weight = 0;
			for($i = 0; $i < $d_item_num_mod; $i++){
				//fb($goodsForm->show('box_num'.$i));
				echo '<tr id="'.$d_item_rtn[$i]['p_id'].'" name="'.$d_item_rtn[$i]['po_id'].'">
				<td>';
				$goodsForm->show('box_num'.$i);
				echo '</td>
				<td>';
				$goodsForm->show('inner_box_num'.$i);
				echo '</td>
				<td>';
				$goodsForm->show('po_id'.$i);
				echo '</td>
				<td>'.$d_item_rtn[$i]['sid'].'</td>
				<td>';
				$goodsForm->show('p_id'.$i);
				echo '</td>
				<td>'.$d_item_rtn[$i]['c_code'].'</td>
				<td>';
				$goodsForm->show('quantity'.$i);
				echo '</td>
				<td>'.$d_item_rtn[$i]['price'].'</td>
				<td id="sub" align="right">'.formatMoney($d_item_rtn[$i]['quantity']*$d_item_rtn[$i]['price']).'</td>
				<td>';
				$goodsForm->show('weight'.$i);
				echo '</td>
				<td>';
				$goodsForm->show('size_l'.$i);
				echo '</td>
				<td>';
				$goodsForm->show('size_w'.$i);
				echo '</td>
				<td>';
				$goodsForm->show('size_h'.$i);
				echo '</td>
				<td>';
				$goodsForm->show('remark'.$i);
				echo '</td>
				<td><img id="'.$d_item_rtn[$i]['po_id'].'" title="复制" style="opacity: 0.5;" onclick="copyProduct(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/copy_small.png"></td>
				<td><img id="'.$d_item_rtn[$i]['po_id'].'" title="删除" style="opacity: 0.5;" onclick="delProduct(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del2_small.png"></td>
				</tr>';	
				
				$total_all += $d_item_rtn[$i]['quantity']*$d_item_rtn[$i]['price'];
				$total_weight += $d_item_rtn[$i]['weight'];
			}
			$total_all += $mod_result['express_cost'];
			echo '</tbody>';
			
			echo '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td align="right">运费：</td><td align="right">';
			$goodsForm->show('express_cost');
			echo '</td><td></td><td></td><td></td><td align="right">运单编号：</td><td align="center">';
			$goodsForm->show('express_id');
			echo '</td><td></td><td></td></tr>';
			
			echo '<tr><td></td><td></td><td></td><td></td><td></td><td align="right">总数：</td><td id="totalQ" align="right"></td><td align="right">合计：</td><td id="total" align="right">'.formatMoney($total_all).'<td id="totalW" align="right">'.$total_weight.'</td><td></td><td></td><td align="right"></td><td align="center">';
			echo '</td><td></td></tr>';
			
			echo '<tr><td align="right" colspan="7">备注：（如送货地址与上述不同，请在此填上）</td><td colspan="6">';
			$goodsForm->show('remark');
			echo '</td><td align="right">审核：</td><td colspan="2">';
			$goodsForm->show('staff');
			echo '</td></tr>';

            echo '<tr><td align="right" colspan="7"></td><td colspan="6">';
            echo '</td><td align="right">出货日期：</td><td colspan="2">';
            $goodsForm->show('d_date');
            echo '</td></tr>';
			
			echo '<tr><td colspan="13"></td><td>';
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
            //默认选中本公司，显示地址
            changeClientCompany();
            //选择事件绑定
            $('#client_company').selectbox({onChange: changeClientCompany});
            express_blur();
            UpdateTotalQuantity();
            UpdateTotalWeight();
        });

        //20130930 箱数最上面的一个可以填写重 长 宽 高，其下面的，如果箱数相同，则将重 长 宽 高的输入框改为readonly
        function add_readonly_attr(){
            var vTable = $("#tbody");
            var vTr = vTable.find("tr");
            var myarr = new Array();
            vTr.each(
                function()
                {
                    var box_num_obj = $(this).children().find("[id^=box_num]");
                    if($.inArray(box_num_obj.val(), myarr) < 0){
                        myarr.push(box_num_obj.val());
                    }else{
                        var weight_obj = $(this).children().find("[id^=weight]");
                        var size_l_obj = $(this).children().find("[id^=size_l]");
                        var size_w_obj = $(this).children().find("[id^=size_w]");
                        var size_h_obj = $(this).children().find("[id^=size_h]");
                        if(weight_obj.val() == '' && size_l_obj.val() == '' && size_w_obj.val() == '' && size_h_obj.val() == ''){
                            weight_obj.attr("readonly", "readonly").addClass("readonly");
                            size_l_obj.attr("readonly", "readonly").addClass("readonly");
                            size_w_obj.attr("readonly", "readonly").addClass("readonly");
                            size_h_obj.attr("readonly", "readonly").addClass("readonly");
                        }
                    }
                }
            );//遍历结束
        }
        add_readonly_attr();
	</script>

<?
}
?>