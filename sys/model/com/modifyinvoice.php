<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();
if(!isset($_GET['pvid'])){
	judgeUserPermNew( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'')/*.(isset($_GET['copypvid'])?$_GET['copypvid']:'')*/ );
}
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	if(isset($_GET['delid']) && $_GET['delid'] != ''){
		//20120916 更新 product total_amount
		$invoice = $mysql->qone('select cid, currency from invoice where vid = ?', $_GET['delid']);
		$mysql->q('select pid, price, quantity from invoice_item where vid = ?', $_GET['delid']);
		$i_item_rtn = $mysql->fetch();
		//$customer_total_amount = 0;
		//foreach($i_item_rtn as $v){
			//20130407 去掉
			//$mysql->q('update product set total_nums = total_nums - ?, total_amount = total_amount - ? where pid = ?', $v['quantity'], currencyTo($v['price'], $invoice['currency'], 'RMB') * $v['quantity'], $v['pid']);
			//$customer_total_amount += currencyTo($v['price'], $invoice['currency'], 'USD') * $v['quantity'];
		//}
		//20120917 更新 customer total_amount
		//20130407 去除了每次更新invoice 的 total_amount 改为每日执行一次脚本来更新这个值，因为这样的每次操作都改很麻烦，而且已经出错了，和脚本运行的值对不起来。而如果在top_customer里面每次计算又太慢
		//$mysql->q('update customer set total_amount = total_amount - ? where cid = ?', $customer_total_amount, $invoice['cid']);
		
		if (!isSysAdmin()){
			$rtn = $mysql->q('update invoice set istatus = ? where vid = ?', 'delete', $_GET['delid']);
			if($rtn){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_DEL_INVOICE, $_SESSION["logininfo"]["aName"]." <i>delete invoice</i> '".$_GET['delid']."' (change status to delete) in sys", ACTION_LOG_SYS_DEL_INVOICE_S, "", "", 0);

				$myerror->ok('删除 Invoice 成功!', 'com-searchinvoice&page=1');
			}else{
				$myerror->error('删除 Invoice 失败!', 'com-searchinvoice&page=1');	
			}
		}else{		
			//由於指定了foreign key，所以要先刪invoice_item裏的內容
			$rtn1 = $mysql->q('delete from invoice_item where vid = ?', $_GET['delid']);
			$rtn2 = $mysql->q('delete from invoice where vid = ?', $_GET['delid']);
			if($rtn2){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_DEL_INVOICE, $_SESSION["logininfo"]["aName"]." <i>delete invoice</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_INVOICE_S, "", "", 0);

				$myerror->ok('删除 Invoice 成功!', 'com-searchinvoice&page=1');
			}else{
				$myerror->error('删除 Invoice 失败!', 'com-searchinvoice&page=1');	
			}
		}
	}else{
		if(isset($_GET['modid']) && $_GET['modid'] != ''){
			$mod_result = $mysql->qone('SELECT * FROM invoice WHERE vid = ?', $_GET['modid']);	
			//從send_to中拆出地址顯示
			$mod_result['address'] = substr($mod_result['send_to'], stripos($mod_result['send_to'], "\r\n"));
			
			$quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description, p.photos, p.ccode, p.scode FROM product p, invoice_item q WHERE  p.pid = q.pid AND q.vid = ?', $_GET['modid']);
			$i_item_rtn = $mysql->fetch();
			//$myerror->info($i_item_rtn);die();
			$i_item_num = count($i_item_rtn);
			//$myerror->info($i_item_num);
			//因為一開始沒有attention的選項所以要加上
			$mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
			//currency session
			//$_SESSION['currency'] = $mod_result['currency'];
			//pi 不包括 A、B、C后缀
			$pre_pi_no = substr($_GET['modid'], 0, 10);

            //20130726 PI TOTAL 和 CREDIT 数值统计
            $pi_total = 0;
            $rtn_pi = $mysql->qone('select sum(price * quantity) as total from proforma_item where pvid = ?', substr($_GET['modid'], 0, 10));
            if($rtn_pi){
                $pi_total = $rtn_pi['total'];
            }

            $total_amount = 0;
            $rtn_credit = $mysql->qone('select cn_no from credit_note where pvid = ?', substr($_GET['modid'], 0, 10));
            if($rtn_credit){
                $rs_credit_item = $mysql->q('select amount from credit_note_item where cn_no = ?', $rtn_credit['cn_no']);
                if($rs_credit_item){
                    $rtn_credit_item = $mysql->fetch();
                    foreach($rtn_credit_item as $v){
                        $total_amount += $v['amount'];
                    }
                }
            }

            //20131021 加返回 packing list num
            for($i = 0; $i < $i_item_num; $i++){
                $rtn_packinglist = $mysql->qone('select sum(qty) as total_qty from packing_list_item where ref = ? and item = ?', $_GET['modid'], $i_item_rtn[$i]['pid']);
                $i_item_rtn[$i]['packinglist_num'] = $rtn_packinglist['total_qty'];
                if(!isset($i_item_rtn[$i]['packinglist_num']) || $i_item_rtn[$i]['packinglist_num'] == ''){
                    $i_item_rtn[$i]['packinglist_num'] = 0;
                }
            }

		}elseif(isset($_GET['pvid']) && $_GET['pvid'] != ''){
			//剛好沒有vid這個字段，所以vid是空的，proforma表和invoice表結構相似，省去了很多麻煩
			$mod_result = $mysql->qone('SELECT * FROM proforma WHERE pvid = ?', $_GET['pvid']);
			//vid和pvid一样
			$mod_result['vid'] = $mod_result['pvid'];
			//mark_date初始为当天的日期，改在value里
			//從send_to中拆出地址顯示
			$mod_result['address'] = substr($mod_result['send_to'], stripos($mod_result['send_to'], "\r\n"));
            //因為一開始沒有attention的選項所以要加上
            $mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
            //currency session
            //$_SESSION['currency'] = $mod_result['currency'];
            //pi转为i，remarks结尾要加东西
            $mod_result['remarks'] = $mod_result['remarks'] . '
HS CODE: 7117.1900
MADE IN CHINA';

			//$quote_item_result = $mysql->q('SELECT p.pid, pi.price, pi.quantity, pi.description, p.photos, p.ccode, p.scode FROM product p, proforma_item pi WHERE  p.pid = pi.pid AND pi.pvid = ?', $_GET['pvid']);
			//$i_item_rtn = $mysql->fetch();
			//$myerror->info($i_item_rtn);die();
			//$i_item_num = count($i_item_rtn);
			//$myerror->info($i_item_num);

            //mod 20130625 扣除已转的item数量
            $i_item_num = 0;
            $i_item_rtn = pi_to_invoice_item_auto_calculate($_GET['pvid']);
            //即使返回集为空，也不报错，只是item为空，如果用户想自己填还是可以的
            //还是改为严格的限制转完所有item就不能再转了
            if($i_item_rtn){
                //$myerror->info($i_item_rtn);die();
                $i_item_num = count($i_item_rtn);
                //$myerror->info($i_item_num);
            }else{
                $myerror->error('此PI的所有ITEM已经转为Invoice，请不要再转换。', 'com-searchinvoice&page=1');
            }
		}elseif(isset($_GET['appendid']) && $_GET['appendid'] != ''){
			//为了能在页面的原来是autogeneration的地方显示将要添加的id。但是如果有两个人同时添加，就会造成后一个添加的id与显示的不同。
			$newid = autoGenerationAddID($_GET['appendid']);
			$mod_result = $mysql->qone('SELECT * FROM proforma WHERE pvid = ?', $_GET['appendid']);
			
			$i_item_result = $mysql->q('SELECT pid, quantity FROM invoice_item WHERE vid like ?', '%'.$_GET['appendid'].'%');
			//这个是记录invoice里面已有的product item的数量，用以计算还剩下多少数量没开单
			$temp_item_rtn = $mysql->fetch();
			//fb($temp_item_rtn);
			
			//!!!!为什么原来要连一下product表读出photos、ccode和scode，没必要啊，invoice表里都有
			//$pi_item_result = $mysql->q('SELECT p.pid, pi.price, pi.quantity, pi.description, p.photos, p.ccode, p.scode FROM product p, proforma_item pi WHERE p.pid = pi.pid AND pi.pvid = ?', $_GET['appendid']);
			$pi_item_result = $mysql->q('SELECT * FROM proforma_item WHERE pvid = ?', $_GET['appendid']);
			$i_item_rtn = $mysql->fetch();
			$i_item_num = count($i_item_rtn);
			//因為一開始沒有attention的選項所以要加上
			$mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
			//currency session
			//$_SESSION['currency'] = $mod_result['currency'];
			//fb($i_item_rtn);
			
			if($temp_item_rtn){
				$i = 0;
				foreach($i_item_rtn as $v){
					foreach($temp_item_rtn as $w){
						if($v['pid'] == $w['pid']){
							$i_item_rtn[$i]['quantity'] -= $w['quantity'];	
						}
					}
					$i++;
				}
			}
			
		}else{
			die('Need modid!');	
		}
		
		
				
		$goodsForm = new My_Forms();
		
		
		$formItems = array(		
				'i_cid' => array('title' => 'Customer', 'type' => 'select', 'options' => get_customer(), 'required' => 1, 'value' => isset($mod_result['cid'])?$mod_result['cid']:''),
				//'i_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['send_to'])?$mod_result['send_to']:''),
				'i_attention' => array('title' => 'Attention', 'type' => 'select', 'options' => $mod_customer_contact, 'required' => 1, 'value' => isset($mod_result['attention'])?$mod_result['attention']:''),
				'i_created_by' => array('title' => 'Created by', 'type' => 'select', 'required' => 1, 'options' => $user, 'value' => isset($mod_result['printed_by'])?$mod_result['printed_by']:''),				
				'i_reference' => array('title' => 'Customer PO No.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['reference'])?$mod_result['reference']:''),
				'i_reference_num' => array('title' => 'Ref NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['reference_num'])?$mod_result['reference_num']:''),
				'i_mark_date' => array('title' => 'Creation Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($_GET['pvid'])?date('Y-m-d'):(isset($mod_result['mark_date'])?date('Y-m-d', strtotime($mod_result['mark_date'])):'')),				
				'i_tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['tel'])?$mod_result['tel']:''),
				'i_fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['fax'])?$mod_result['fax']:''),
				'i_currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1, 'value' => isset($mod_result['currency'])?$mod_result['currency']:''),
				'i_unit' => array('title' => 'Unit', 'type' => 'select', 'options' => get_unit(), 'value' => isset($mod_result['unit'])?$mod_result['unit']:''),
				'i_packing_num' => array('title' => 'Packing List NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['packing_num'])?$mod_result['packing_num']:'', 'readonly' => 'readonly'),
				'i_discount' => array('title' => 'Discount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['discount'])?intval($mod_result['discount']):''),
				//'i_delivery_date' => array('title' => 'Delivery Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($_GET['pvid'])?date('Y-m-d'):(isset($mod_result['delivery_date'])?date('Y-m-d', strtotime($mod_result['delivery_date'])):'')),
				//'i_waybill_no' => array('title' => 'Waybill NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['waybill_no'])?intval($mod_result['waybill_no']):''),
				
				'i_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
				'i_address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 500, 'rows' => 2, 'value' => isset($mod_result['address'])?$mod_result['address']:''),
				'i_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => isset($mod_result['remarks'])?$mod_result['remarks']:''),
				
				'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
				'q_p_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
				'q_p_quantity' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
				//remark不加了，好像也沒什麼用
				//'pi_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
				'q_p_description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled'),
				'q_p_photos' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
				'q_p_ccode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
				'q_p_scode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
							
				'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
				);
				
		if(!isset($_GET['appendid'])){
			$formItems['i_vid'] = array('title' => 'Invoice NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, isset($_GET['pvid'])?'':'readonly' => 'readonly', 'value' => isset($mod_result['vid'])?$mod_result['vid']:'');
		}		
		
		//第一個上面用了
		//原来从1开始，现在从0开始
		for($i = 0; $i < $i_item_num; $i++){
			$formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($i_item_rtn[$i]['pid'])?$i_item_rtn[$i]['pid']:'', 'readonly' => 'readonly');
			$formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => isset($i_item_rtn[$i]['price'])?formatMoney($i_item_rtn[$i]['price']):'');
			//$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => (isset($i_item_rtn[$i]['quantity']) && ($i_item_rtn[$i]['quantity'] == 0 || $i_item_rtn[$i]['quantity'] == ''))?1:intval($i_item_rtn[$i]['quantity']));
			$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => isset($i_item_rtn[$i]['quantity'])?$i_item_rtn[$i]['quantity']:'');		
			$formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'value' => isset($i_item_rtn[$i]['description'])?$i_item_rtn[$i]['description']:'');
			$formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => isset($i_item_rtn[$i]['photos'])?$i_item_rtn[$i]['photos']:'');
			$formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => isset($i_item_rtn[$i]['ccode'])?$i_item_rtn[$i]['ccode']:'');
			$formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => isset($i_item_rtn[$i]['scode'])?$i_item_rtn[$i]['scode']:'');
		}
		
		//最后一个
		$formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20);
		$formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled');
		$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled');
		//remark不加了，好像也沒什麼用
		//'i_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		$formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled');
		$formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
		$formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
		$formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
		
		//$myerror->info($formItems);
		//die();
				
		$goodsForm->init($formItems);
		
		
		if(!$myerror->getAny() && $goodsForm->check()){
			//$myerror->info($_POST);
			
			if(isset($_GET['appendid'])){
				$i_vid = autoGenerationAddID($_GET['appendid']);
				//fb($i_vid);
			}else{
				$i_vid = $_POST['i_vid']; 
			}
			
			$i = 1;//第一个post的是form的标识串，所以会跳过
			$i_product = array();
			
			$i_cid = $_POST['i_cid']; 
			$i_send_to = combineSendTo($_POST['i_cid'], '', $_POST['i_address']);//$_POST['i_send_to'];
			$i_attention = $_POST['i_attention'];
			$i_reference = $_POST['i_reference']; 
			$i_reference_num = $_POST['i_reference_num']; 
			$i_tel = $_POST['i_tel']; 
			$i_fax = $_POST['i_fax'];
			$i_currency = $_POST['i_currency'];
			$i_unit = $_POST['i_unit'];
			//$i_printed_by = $_POST['i_printed_by'];
			$i_packing_num = $_POST['i_packing_num'];
			$i_discount = $_POST['i_discount'];
			//$i_delivery_date = $_POST['i_delivery_date']==''?'0000-00-00':$_POST['i_delivery_date'];
			//$i_waybill_no = $_POST['i_waybill_no'];
			$i_remark = $_POST['i_remark'];
			//这个变量没用的，写着方便计算下面的数（14），不然忘了这个以后麻烦
			$i_address = $_POST['i_address'];		
			
			//这个是在最后提交的哟
			$i_remarks = $_POST['i_remarks'];
			
			//remarks 在最後，所以這裡是14，有多了個mark_date所以是15了，又多了个created_by所以是16了，多了i_delivery_date和i_waybill_no所以是18，又不要了i_delivery_date和i_waybill_no，所以现在是16。如果是appendid，vid是自动生成的，则是15
			foreach( $_POST as $v){
				if( $i <= (isset($_GET['appendid'])?15:16)){
					$i++;
				}else{
					$i_product[] = $v;	
				}
			}
			//如果是proforma转过来的就用当天日期，如果是modid、如果日期没有修改，就保持和原来一样，连时分秒都一样，如果有修改就把时分秒改为00：00：00		
			$i_mark_date = isset($_GET['pvid'])?dateMore():((date('Y-m-d', strtotime($mod_result['mark_date'])) == $_POST['i_mark_date'])?$mod_result['mark_date']:$_POST['i_mark_date'].' 00:00:00');
			//暫時不知道這個打印日期是怎麼回事。。。
			$i_printed_date = isset($_GET['pvid'])?dateMore():$mod_result['printed_date'];
			$i_printed_by = $_POST['i_created_by'];
			
			//$myerror->info($i_vid);
			
			//這裡的整除會有個bug，就是如果下面未填的空表格超過7個，就會將那些空的pid組成一個新的product插入quote_item表，但又由於都為空，所以會插入不成功，應該也沒多大影響。。。
			//减1是因为最后一个是remarks
			$i_product_num = intval((count($i_product)-1)/7);
			
			$i_pid = array();
			$i_p_description = array();
			$i_p_quantity = array();
			$i_p_price = array();
			//$i_p_remark = array();
			$i_p_photos = array();
			$i_p_ccode = array();
			$i_p_scode = array();

            //20140315 新增invoice则更新invoice表的total字段和total_qty字段
            $total = 0;//item price总和
            $total_qty = 0;//item quantity总和

			$p_index = 0;
			for($j = 0; $j < $i_product_num; $j++){
				$i_pid[] = $i_product[$p_index++];
				$i_p_description[] = $i_product[$p_index++];

                $temp_qty = (($i_product[$p_index] != '')?$i_product[$p_index++]:0);
                $total_qty += $i_p_quantity[] = $temp_qty;

				//mod 20120927 去除钱数中的逗号
                $temp_price = str_replace(',', '', ($i_product[$p_index] != '')?$i_product[$p_index++]:0);//前後加兩次就出問題了，所以把前面的去掉
                $total += ($i_p_price[] = $temp_price)*$temp_qty;

				//$i_p_remark[] = $i_product[$p_index++];
				$i_p_photos[] = $i_product[$p_index++];
				$i_p_ccode[] = $i_product[$p_index++];
				$i_p_scode[] = $i_product[$p_index++];
			}

            //20130527 加限制，不允许在一个单中添加多个相同PID的item
            if(!check_repeat_item($i_pid)){

                $ex_total = 0;//这个字段不知道是记录什么值的，暂时给个0。。。

                //$myerror->info($i_pid);
                //$myerror->info($i_cost_rmb);
                //$myerror->info($i_p_quantity);
                ////$myerror->info($i_p_remark);
                //$myerror->info($i_p_description);
                //$myerror->info($i_p_photos);
                //$myerror->info($i_p_ccode);
                //$myerror->info($i_p_scode);

                //die();
                if(isset($_GET['modid']) && !isset($_GET['pvid'])){
                    $result = $mysql->q('update invoice set vid = ?, send_to = ?, attention = ?, tel = ?, fax = ?, reference = ?, remark = ?, mark_date = ?, reference_num = ?, packing_num = ?, currency = ?, unit = ?, printed_by = ?, printed_date = ?, total = ?, total_qty = ?, ex_total = ?, discount = ?, remarks = ?, cid = ? where vid = ?', $i_vid, $i_send_to, $i_attention, $i_tel, $i_fax, $i_reference, $i_remark, $i_mark_date, $i_reference_num, $i_packing_num, $i_currency, $i_unit, $i_printed_by, $i_printed_date, $total, $total_qty, $ex_total, $i_discount, $i_remarks, $i_cid, /*$i_delivery_date, $i_waybill_no,*/ $_GET['modid']);
                    //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                    if($result !== false){
                        //20120916 更新 product total_amount （先减去原来的，在加上更新的）
                        //$customer_total_amount = 0;
                        //foreach($i_item_rtn as $v){
                        //$mysql->q('update product set total_amount = total_amount - ? where pid = ?', currencyTo($v['price'], $i_currency, 'RMB') * $v['quantity'], $v['pid']);
                        //$customer_total_amount += currencyTo($v['price'], $i_currency, 'USD') * $v['quantity'];
                        //}
                        //20120917 更新 customer total_amount （先减去原来的，在加上更新的）
                        //$mysql->q('update customer set total_amount = total_amount - ? where cid = ?', $customer_total_amount, $i_cid);

                        $rtn = $mysql->q('delete from invoice_item where vid = ?', $_GET['modid']);
                        //$customer_total_amount = 0;
                        for($k = 0; $k < $i_product_num; $k++){
                            $rtn = $mysql->q('insert into invoice_item (pid, vid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $i_pid[$k], $i_vid, $i_p_price[$k], $i_p_quantity[$k], $i_p_description[$k], $i_p_photos[$k], $i_p_ccode[$k], $i_p_scode[$k]);
                            //20120916 更新 product total_amount
                            //$mysql->q('update product set total_amount = total_amount + ? where pid = ?', currencyTo($i_p_price[$k], $i_currency, 'RMB') * $i_p_quantity[$k], $i_pid[$k]);
                            //$customer_total_amount += currencyTo($i_p_price[$k], $i_currency, 'USD') * $i_p_quantity[$k];
                        }
                        //20120917 更新 customer total_amount
                        //$mysql->q('update customer set total_amount = total_amount + ? where cid = ?', $customer_total_amount, $i_cid);

                        //add action log
                        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                            , $_SESSION['logininfo']['aID'], $ip_real
                            , ACTION_LOG_SYS_MOD_INVOICE, $_SESSION["logininfo"]["aName"]." <i>modify invoice</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_INVOICE_S, "", "", 0);

                        $myerror->ok('修改 Invoice 成功!', 'BACK');

                    }else{
                        $myerror->error('修改 Invoice 失败', 'BACK');
                    }
                    //pi轉為invoice的相當與新增一個invoice，所以是insert
                }elseif(!isset($_GET['modid']) && (isset($_GET['pvid']) || isset($_GET['appendid']))){
                    //判断是否输入的vid已存在，因为存在的话由于数据库限制，就会新增失败
                    $judge = $mysql->q('select vid from invoice where vid = ?', $i_vid);
                    if(!$judge){
                        $result = $mysql->q('insert into invoice (vid, send_to, attention, tel, fax, reference, remark, mark_date, reference_num, packing_num, currency, unit, printed_by, printed_date, total, total_qty, ex_total, discount, remarks, cid, istatus) values ('.moreQm(21).')', $i_vid, $i_send_to, $i_attention, $i_tel, $i_fax, $i_reference, $i_remark, $i_mark_date, $i_reference_num, $i_packing_num, $i_currency, $i_unit, $i_printed_by, $i_printed_date, $total, $total_qty, $ex_total, $i_discount, $i_remarks, $i_cid, /*$i_delivery_date, $i_waybill_no,*/ $mod_result['istatus']);
                        if($result){
                            //$customer_total_amount = 0;
                            for($k = 0; $k < $i_product_num; $k++){
                                $rtn = $mysql->q('insert into invoice_item (pid, vid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $i_pid[$k], $i_vid, $i_p_price[$k], $i_p_quantity[$k], $i_p_description[$k], $i_p_photos[$k], $i_p_ccode[$k], $i_p_scode[$k]);
                                //20120916 更新 product total_amount
                                //$mysql->q('update product set total_nums = total_nums + ?, total_amount = total_amount + ? where pid = ?', $i_p_quantity[$k], currencyTo($i_p_price[$k], $i_currency, 'RMB') * $i_p_quantity[$k], $i_pid[$k]);
                                //$customer_total_amount += currencyTo($i_p_price[$k], $i_currency, 'USD') * $i_p_quantity[$k];
                            }
                            //20120917 更新 customer total_amount
                            //$mysql->q('update customer set total_amount = total_amount + ? where cid = ?', $customer_total_amount, $i_cid);

                            if(isset($_GET['pvid'])){
                                //add action log
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                                    , $_SESSION['logininfo']['aID'], $ip_real
                                    , ACTION_LOG_SYS_ADD_INVOICE_FROM_PROFORMA, $_SESSION["logininfo"]["aName"]." <i>add invoice</i> '".$i_vid."' from proforma '".$_GET['pvid']."' in sys", ACTION_LOG_SYS_ADD_INVOICE_FROM_PROFORMA_S, "", "", 0);
                            }elseif(isset($_GET['appendid'])){
                                //add action log
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                                    , $_SESSION['logininfo']['aID'], $ip_real
                                    , ACTION_LOG_SYS_APPEND_INVOICE, $_SESSION["logininfo"]["aName"]." <i>add invoice</i> from '".$_GET['appendid']."' to '".$i_vid."' in sys", ACTION_LOG_SYS_APPEND_INVOICE_S, "", "", 0);
                            }

                            $myerror->ok('新增 Invoice 成功! <font color="#FF0000">Continue to complete shipment</font>', 'com-shipment&pi_no='.$i_vid.'&page=1');
                        }else{
                            $myerror->error('新增 Invoice 失败', 'BACK');
                        }
                    }else{
                        $myerror->error('输入的 Invoice NO.已存在，新增 Invoice 失败', 'BACK');
                    }
                }
            }else{
                $myerror->error('不允许在一个单中添加相同的 Product Item ，新增或修改 Invoice 失败', 'BACK');
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

    <? //20130816 这个原来放在page页面的，但是只有这里用到了artDialog 所以放这里了，为提高别的页面的加载速度 ?>
    <link href="/sys/ui/artDialog.css" rel="stylesheet" />
    <script src="/sys/ui/artDialog.js"></script>

	<h1 class="green">INVOICE<em>* item must be filled in</em><? show_status_new($mod_result['istatus']);?></h1>
	<? if(!isset($_GET['pvid'])){ ?>
	<fieldset> 
	<legend class='legend'>Action</legend>
	<div style="margin-left:28px;"><a class="button" href="model/com/invoice_pdf.php?vid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a><a class="button" href="model/com/invoice_pdf2.php?vid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF2</b></a><a class="button" href="model/com/invoice_pdf_photo_list.php?vid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF - Photo List</b></a><a class="button" href="#" onclick="return setCiPercent('<?=$_GET['modid']?>')"><b>Add To CI</b></a><!--a class="button" href="?act=com-combine_invoice_to_ci&invoice=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Combine Invoice To CI</b></a--><a class="button" href="?act=com-shipment&page=1&pi_no=<?=$pre_pi_no?>" onclick="return pdfConfirm()"><b>Shipment</b></a><a class="button" href="?act=com-payment&page=1&pi_no=<?=$pre_pi_no?>" onclick="return pdfConfirm()"><b>Payment</b></a><a class="button" href="?act=com-invoice_to_packing_list&vid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Add To PL</b></a></div>
	</fieldset>
	<? } ?>
	<fieldset> 
	<legend class='legend'><? if(!isset($_GET['pvid'])){ echo 'Modify Invoice';}else{ echo 'Proforma To Invoice';}?></legend>
	<?php
	$goodsForm->begin();
	?>
	<table width="100%" id="table">
	  <tr class="formtitle">
      	<? if(isset($_GET['appendid'])){?>
      	<td width="25%"><div class="set"><label class="formtitle">Proforma Invoice NO.</label><br /><?=$newid?></div></td>
        <?
		}else{
		?>	
		<td width="25%"><? $goodsForm->show('i_vid');?></td>
		<?
        }
		?>
        <td width="25%"><? $goodsForm->show('i_cid');?></td>
		<td width="25%"><? $goodsForm->show('i_attention');?></td>
		<td width="25%"><? $goodsForm->show('i_mark_date');?></td>
	  </tr>
	  <tr>
		<td width="25%" valign="top"><? $goodsForm->show('i_tel');?></td>
		<td width="25%" valign="top"><? $goodsForm->show('i_fax');?></td>  	
		<td width="25%" colspan="2"><? $goodsForm->show('i_address');?></td>      
	  </tr> 
	  <tr>
		<td width="25%" valign="top"><? $goodsForm->show('i_reference_num');?></td>
		<td width="25%"><? $goodsForm->show('i_packing_num');?></td> 
		<td width="25%"><? $goodsForm->show('i_currency');?></td>   
		<td width="25%"><? $goodsForm->show('i_unit');?></td>
		<? /*<td width="25%"><? $goodsForm->show('i_printed_by');?></td>*/?>
	  </tr>
	  <tr>
		<td width="25%" valign="top"><? $goodsForm->show('i_reference');?></td>
		<td width="25%" colspan="2"><? $goodsForm->show('i_remark');?></td>
		<td width="25%"><? $goodsForm->show('i_discount');?></td> 
	  </tr> 
      <tr>
		<td width="25%"><? $goodsForm->show('i_created_by');?></td>      
      	<td width="25%"><? //$goodsForm->show('i_delivery_date');?></td>  
     	<td width="25%"><? //$goodsForm->show('i_waybill_no');?></td>        
      </tr>      
	</table>
	<div class="line"></div>
	<div style="margin-left:28px;">
	<label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label><?=(isset($_GET['pvid'])?'<font color="#FF0000">（已自动此删除此PI之前已转为Invoice的ITEM，或是减去已转为Invoice的ITEM的QUANTITY）</font>':'')?>
	<table width="100%" id="tableDnD">
	<tbody id="tbody">
	  <tr class="formtitle nodrop nodrag">
      	<td width="3%"></td>
		<td width="17%">Product ID</td>
		<td width="34%">Description</td>
		<td width="8%">Quantity</td>
		<td width="8%">Shipped</td>
		<? /*<td width="20%">Product Remark</td>*/ ?>
		<td width="8%">Price</td>
		<td width="8%">Subtotal</td>
		<td width="8%" align="center">Photo</td>
		<? /*<td width="6%"><div class="del_all"><input name='' type='button' value='Del All' /></div></td>*/?>
		<td width="3%">&nbsp;</td>
        <td width="3%">&nbsp;</td>
<!--        <td width="3%">&nbsp;</td>-->
<!--		<td width="5%">&nbsp;</td>-->
	  </tr>
	  <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
      	<td class="dragHandle"></td>
		<td><? $goodsForm->show('q_pid');?></td>
		<td><? $goodsForm->show('q_p_description');?></td>
		<td><? $goodsForm->show('q_p_quantity');?></td>
        <td align="right"><div id="packinglist_num"></div></td>
        <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
		<td><? $goodsForm->show('q_p_price'); $goodsForm->show('q_p_photos'); $goodsForm->show('q_p_ccode'); $goodsForm->show('q_p_scode');?></td>  
		<td id="sub">0</td>
		<td></td>
		<td><div id="his"></div></td>
<!--        <td><div id="clear"></div></td>-->
		<td><div id="del"></div></td>
	  </tr>
	<script>
		//注意！！！js語句不能放在<tr></tr>裏面喲，注意，js語句不是放在哪裡都行的。。。
		searchProduct(17, '');
	</script>  
	<?
    //20140116
    $total_qty = 0;
	for($i = 0; $i < $i_item_num; $i++){

        $total_qty += $i_item_rtn[$i]['quantity'];

		if (is_file($pic_path_com . $i_item_rtn[$i]['photos']) == true) { 
	
			//圖片壓縮
			//$i_item_rtn[$i]['photos']是原來的， $small_photo是縮小後的
			//$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
			$small_photo = 's_' . $i_item_rtn[$i]['photos'];
			if(filesize($pic_path_com . $i_item_rtn[$i]['photos']) > 100){
				//縮小的圖片不存在才進行縮小操作
				if (!is_file($pic_path_small . $small_photo) == true) { 	
					makethumb($pic_path_com . $i_item_rtn[$i]['photos'], $pic_path_small . $small_photo, 's');
				}
			}		
			/*
			$arr = getimagesize($pic_path_com . $i_item_rtn[$i]['photos']);
			$pic_width = $arr[0];
			$pic_height = $arr[1];
			$image_size = getimgsize(80, 60, $pic_width, $pic_height);
			$photo_string = '<a href="/sys/'.$pic_path_com . $i_item_rtn[$i]['photos'].'" target="_blank" title="'.$i_item_rtn[$i]['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
			*/
			$photo_string = '<a href="/sys/'.$pic_path_com . $i_item_rtn[$i]['photos'].'" target="_blank" title="'.$i_item_rtn[$i]['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
		}else{ 
			$photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60' original='../images/nopic.gif'>";
		}	
	?>
	  <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
      	<td id="index" class="dragHandle"><?=$i+1?></td>
		<td><? $goodsForm->show('q_pid'.$i);?></td>
		<td><? $goodsForm->show('q_p_description'.$i);?></td>
		<td><? $goodsForm->show('q_p_quantity'.$i);?></td>
        <td align="right"><div id="packinglist_num"><?=@$i_item_rtn[$i]['packinglist_num']?></div></td>
		<? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
		<td><? $goodsForm->show('q_p_price'.$i); $goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
		<td id="sub"><?=formatMoney($i_item_rtn[$i]['price']*(($i_item_rtn[$i]['quantity'] == 0 || $i_item_rtn[$i]['quantity'] == '')?1:$i_item_rtn[$i]['quantity']))?></td>
		<td><?=$photo_string?></td>
		<td><div id="his<?=$i?>"><img src="../../sys/images/Actions-edit-copy-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="History" /></div></td>
<!--        <td><div id="clear--><?//=$i?><!--"><img src="../../sys/images/clear.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Clear" /></div></td>-->
		<? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
		<td><div id="del<?=$i?>"><img src="../../sys/images/del-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Delete" /></div></td>
	  </tr>
	  
	
	<script>
        //修改后只要调用这个函数就行了，不用在写重复的代码在这个页面了
        searchProduct(17, <?=$i?>);
        //20130724 改为了blur出信息，所以q_pid的blur时间要unbind掉了
        $("#q_pid<?=$i?>").unbind();
	</script>
	<?	
	}
	?>  
	
	<? //这里是为了多出一个空行，来可以方便输入，不用在enter旧的来新增一行，因为这样旧的内容会改变。。。 ?>
	  <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
      	<td class="dragHandle"></td>
		<td><? $goodsForm->show('q_pid'.$i);?></td>
		<td><? $goodsForm->show('q_p_description'.$i);?></td>
		<td><? $goodsForm->show('q_p_quantity'.$i);?></td>
        <td align="right"><div id="packinglist_num"></div></td>
		<? /*<td><? $goodsForm->show('i_p_remark');?></td>*/ ?>
		<td><? $goodsForm->show('q_p_price'.$i);$goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
		<td id="sub">0</td>
		<td>&nbsp;</td>
		<td><div id="his<?=$i?>"></div></td>
<!--        <td><div id="clear--><?//=$i?><!--"></div></td>-->
		<? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
		<td><div id="del<?=$i?>"></div></td>
	  </tr>
	  <script>
          //修改后只要调用这个函数就行了，不用在写重复的代码在这个页面了
          searchProduct(17, <?=$i?>)
	  </script>
	  
	  
	</tbody>
        <tr><td>&nbsp;</td></tr>

        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td align="right">Total QTY: </td>
            <td><div style="text-align: left; padding-left: 20px;" id="qty"><?=$total_qty?></div></td>
            <td>&nbsp;</td>
            <td align="right">Total: </td>
            <td><div class="total" id="total">0</div></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <? if(isset($_GET['modid'])){ ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td align="right">PI Total: </td>
                <td><div class="total" id="pi_total"><?=formatMoney($pi_total)?></div></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td align="right">Credit: </td>
                <td><div class="total" id="credit"><?=formatMoney($total_amount)?></div></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        <? } ?>

	</table>
	</div>
	<div class="line"></div>
	<?
	$goodsForm->show('i_remarks');
	?>
	<div class="line"></div>
	<?
	$goodsForm->show('submitbtn');
	?>
	</fieldset>
	<? if(!isset($_GET['pvid'])){ ?>
	<fieldset> 
	<legend class='legend'>Action</legend>
	<div style="margin-left:28px;"><a class="button" href="model/com/invoice_pdf.php?vid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a><a class="button" href="model/com/invoice_pdf2.php?vid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF2</b></a><a class="button" href="model/com/invoice_pdf_photo_list.php?vid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF - Photo List</b></a><a class="button" href="#" onclick="return setCiPercent('<?=$_GET['modid']?>')"><b>Add To CI</b></a><!--a class="button" href="?act=com-combine_invoice_to_ci&invoice=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Combine Invoice To CI</b></a--><a class="button" href="?act=com-shipment&page=1&pi_no=<?=$pre_pi_no?>" onclick="return pdfConfirm()"><b>Shipment</b></a><a class="button" href="?act=com-payment&page=1&pi_no=<?=$pre_pi_no?>" onclick="return pdfConfirm()"><b>Payment</b></a><a class="button" href="?act=com-invoice_to_packing_list&vid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Add To PL</b></a></div>
	</fieldset>
	<? } ?>
	<?
	$goodsForm->end();
	
	}
	?>
	
	<script>
	$(function(){
		//load頁面就更新total值
		UpdateTotal();
		$(".template").hide();
		selectCustomer("i_");
		//***先加載當前屏幕的img，好像沒有效果。。。
		/*
		$("img").lazyload({
			placeholder : "/sys/images/grey.gif",
			effect      : "fadeIn"
		});
		*/
		//***	
		//table tr层表单可拖动
		$("#tableDnD").tableDnD({dragHandle: ".dragHandle"});
		currency("i_");
	})
	</script>

<?
}
?>