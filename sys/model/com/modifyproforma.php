<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();
if(!isset($_GET['qid'])){
	judgeUserPermNew( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'').(isset($_GET['copypvid'])?$_GET['copypvid']:'') );
}
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	if(isset($_GET['delid']) && $_GET['delid'] != ''){
		if (!isSysAdmin()){
			$rtn = $mysql->q('update proforma set istatus = ? where pvid = ?', 'delete', $_GET['delid']);
			if($rtn){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_DEL_PROFORMA, $_SESSION["logininfo"]["aName"]." <i>delete proforma</i> '".$_GET['delid']."' (change status to delete) in sys", ACTION_LOG_SYS_DEL_PROFORMA_S, "", "", 0);

				$myerror->ok('删除 Proforma 成功!', 'com-searchproforma&page=1');
			}else{
				$myerror->error('删除 Proforma 失败!', 'com-searchproforma&page=1');	
			}
		}else{
			//由於指定了foreign key，所以要先刪proforma_item裏的內容
			$rtn1 = $mysql->q('delete from proforma_item where pvid = ?', $_GET['delid']);
			$rtn2 = $mysql->q('delete from proforma where pvid = ?', $_GET['delid']);
			if($rtn2){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_DEL_PROFORMA, $_SESSION["logininfo"]["aName"]." <i>delete proforma</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_PROFORMA_S, "", "", 0);

				$myerror->ok('删除 Proforma 成功!', 'com-searchproforma&page=1');
			}else{
				$myerror->error('删除 Proforma 失败!', 'com-searchproforma&page=1');	
			}
		}
	}else{
		if(isset($_GET['modid']) && $_GET['modid'] != ''){
			$mod_result = $mysql->qone('SELECT * FROM proforma WHERE pvid = ?', $_GET['modid']);	
			//從send_to中拆出地址顯示
			$mod_result['address'] = substr($mod_result['send_to'], stripos($mod_result['send_to'], "\r\n"));
			
			$quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description, p.photos, p.ccode, p.scode FROM product p, proforma_item q WHERE  p.pid = q.pid AND q.pvid = ?', $_GET['modid']);
			$pi_item_rtn = $mysql->fetch();
			//$myerror->info($pi_item_rtn);die();
			$pi_item_num = count($pi_item_rtn);
			//$myerror->info($pi_item_num);
			//因為一開始沒有attention的選項所以要加上
			$mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
			//currency session
			//$_SESSION['currency'] = $mod_result['currency'];

            //20130726 加CREDIT数值统计
            //一个pvid只能创建一个cn
            $total_amount = 0;
            $rtn_credit = $mysql->qone('select cn_no from credit_note where pvid = ?', $_GET['modid']);
            if($rtn_credit){
                $rs_credit_item = $mysql->q('select amount from credit_note_item where cn_no = ?', $rtn_credit['cn_no']);
                if($rs_credit_item){
                    $rtn_credit_item = $mysql->fetch();
                    foreach($rtn_credit_item as $v){
                        $total_amount += $v['amount'];
                    }
                }
            }

            //20140629 加返回 packing list num
            for($i = 0; $i < $pi_item_num; $i++){
                $rtn_packinglist = $mysql->qone('select sum(qty) as total_qty from packing_list_item where ref like ? and item = ?', $_GET['modid'].'%', $pi_item_rtn[$i]['pid']);
                $pi_item_rtn[$i]['packinglist_num'] = $rtn_packinglist['total_qty'];
                if(!isset($pi_item_rtn[$i]['packinglist_num']) || $pi_item_rtn[$i]['packinglist_num'] == ''){
                    $pi_item_rtn[$i]['packinglist_num'] = 0;
                }
            }

            //20151129 根据pcid在py里查找所有已支付款总和
            $rtn_payment_new = $mysql->qone('select sum(received) as total_paid from payment_item_new where pi_or_cn_no = ?', $_GET['modid']);

		}elseif(isset($_GET['qid']) && $_GET['qid'] != ''){
			$mod_result = $mysql->qone('SELECT * FROM quotation WHERE qid = ?', $_GET['qid']);	
			//20121107 从quotation转过来自动填上pvid
			$mod_result['pvid'] = autoGenerationID();
			$quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description, p.photos, p.ccode, p.scode FROM product p, quote_item q WHERE  p.pid = q.pid AND q.qid = ?', $_GET['qid']);
			$pi_item_rtn = $mysql->fetch();
			//$myerror->info($pi_item_rtn);die();
			$pi_item_num = count($pi_item_rtn);
			//$myerror->info($pi_item_num);
			//因為一開始沒有attention的選項所以要加上
			$mod_customer_contact = '';
			//currency session
			//$_SESSION['currency'] = $mod_result['currency'];
		}elseif(isset($_GET['copypvid']) && $_GET['copypvid'] != ''){
			$mod_result = $mysql->qone('SELECT * FROM proforma WHERE pvid = ?', $_GET['copypvid']);	
			//從send_to中拆出地址顯示
			$mod_result['address'] = substr($mod_result['send_to'], stripos($mod_result['send_to'], "\r\n"));
            //20130528 pi copy 自动填上 pvid
            //$mod_result['pvid'] = '';
            $mod_result['pvid'] = autoGenerationID();
			$quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description, p.photos, p.ccode, p.scode FROM product p, proforma_item q WHERE  p.pid = q.pid AND q.pvid = ?', $_GET['copypvid']);
			$pi_item_rtn = $mysql->fetch();
			//$myerror->info($pi_item_rtn);die();
			$pi_item_num = count($pi_item_rtn);
			//$myerror->info($pi_item_num);
			//因為一開始沒有attention的選項所以要加上
			$mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
            //20130520 这个有modid，所以下面的条件要改了
            //20150320 去掉copypvid里的modid
			//$_GET['modid'] = $_GET['copypvid'];

			//currency session
			//$_SESSION['currency'] = $mod_result['currency'];
		}elseif(isset($_GET['so_no']) && $_GET['so_no'] != ''){
			//20121011 sample_order 转为 proforma
			$so_rtn = $mysql->qone('select customer from sample_order where so_no = ?', $_GET['so_no']);
			$mod_result['cid'] = $so_rtn['customer'];
			$mod_result['currency'] = 'USD';
			//currency session 设置默认值 USD
			//$_SESSION['currency'] = 'USD';
            //20130715
            $mod_result['pvid'] = autoGenerationID();

            $pi_item_rtn = array();
            $mod_customer_contact = array();
            $pi_item_num = '';

			$rs = $mysql->q('select pid from product where sample_order_no = ?', $_GET['so_no']);
            if($rs){
                $pi_item_temp_rtn = $mysql->fetch();
                $index = 0;

                foreach(@$pi_item_temp_rtn as $v){
                    $rtn = choose_product_new($v['pid'], $mod_result['currency']);
                    $rtn_array = explode('|', $rtn);
                    $pi_item_rtn[$index]['pid'] = $v['pid'];
                    $pi_item_rtn[$index]['price'] = $rtn_array[0];
                    $pi_item_rtn[$index]['quantity'] = 1;//默认为1
                    $pi_item_rtn[$index]['description'] = $rtn_array[1];
                    $pi_item_rtn[$index]['photos'] = $rtn_array[3];
                    $pi_item_rtn[$index]['ccode'] = $rtn_array[4];
                    $pi_item_rtn[$index]['scode'] = $rtn_array[5];
                    $index++;
                }
                $pi_item_num = count($pi_item_rtn);

                for($i = 0; $i < $pi_item_num; $i++){
                    $pi_item_rtn[$i]['quantity'] = 0;
                }

                //因為一開始沒有attention的選項所以要加上
                $mysql->q('select name from contact where cid = ?', $so_rtn['customer']);
                $contact_item_rtn = $mysql->fetch();
                foreach($contact_item_rtn as $v){
                    $mod_customer_contact[] = array($v['name'], $v['name']);
                }
            }else{
                $myerror->error('此 Sample Order NO. 没有关联任何 Product ID !', 'BACK');
            }

		}else{
			die('Need modid!');	
		}
		
				
		$goodsForm = new My_Forms();
		$formItems = array(
				
				'pi_pvid' => array('title' => 'Proforma Invoice NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, (isset($_GET['copypvid']))?'':'readonly' => 'readonly', 'value' => isset($mod_result['pvid'])?$mod_result['pvid']:''),
				'pi_cid' => array('title' => 'Customer', 'type' => 'select', 'options' => get_customer(), 'required' => 1, 'value' => isset($mod_result['cid'])?$mod_result['cid']:''),
				//'pi_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['send_to'])?$mod_result['send_to']:''),
				'pi_attention' => array('title' => 'Attention', 'type' => 'select', 'options' => $mod_customer_contact, 'required' => 1, 'value' => isset($mod_result['attention'])?$mod_result['attention']:''),
				'pi_created_by' => array('title' => 'Created by', 'type' => 'select', 'required' => 1, 'options' => get_user('sys'), 'value' => isset($mod_result['printed_by'])?$mod_result['printed_by']:''),
				'pi_reference' => array('title' => 'Customer PO No.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['reference'])?$mod_result['reference']:''),
				'pi_reference_num' => array('title' => 'Ref NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['reference_num'])?$mod_result['reference_num']:''),	
				'pi_mark_date' => array('title' => 'Creation Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($_GET['qid'])?date('Y-m-d'):(isset($mod_result['mark_date'])?date('Y-m-d', strtotime($mod_result['mark_date'])):'')),
				'pi_tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['tel'])?$mod_result['tel']:''),
				'pi_fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['fax'])?$mod_result['fax']:''),
				'pi_currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1, 'value' => isset($mod_result['currency'])?$mod_result['currency']:''),
				'pi_unit' => array('title' => 'Unit', 'type' => 'select', 'options' => get_unit(), 'value' => isset($mod_result['unit'])?$mod_result['unit']:''),
				'pi_packing_num' => array('title' => 'Packing NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['packing_num'])?$mod_result['packing_num']:''),
				'pi_discount' => array('title' => 'Discount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['discount'])?intval($mod_result['discount']):''),
				'pi_expected_date' => array('title' => 'ETD', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['expected_date'])?date('Y-m-d', strtotime($mod_result['expected_date'])):''),
				
				'pi_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
				'pi_address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2, 'value' => isset($mod_result['address'])?$mod_result['address']:''),
				'pi_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => isset($mod_result['remarks'])?$mod_result['remarks']:''),
				
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
				
		//add 2012.6.19 让管理员能修改PI的状态
		if(isSysAdmin() && isset($_GET['modid'])){
			$formItems['pi_status'] = array('title' => 'Status', 'type' => 'select', 'options' => $pi_status, 'required' => 1, 'value' => isset($mod_result['istatus'])?$mod_result['istatus']:'');
		}				
		
		//第一個上面用了
		//原来从1开始，现在从0开始
		for($i = 0; $i < $pi_item_num; $i++){
			$formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($pi_item_rtn[$i]['pid'])?$pi_item_rtn[$i]['pid']:'', 'readonly' => 'readonly');
			$formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => isset($pi_item_rtn[$i]['price'])?formatMoney($pi_item_rtn[$i]['price']):'');
			//$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => (isset($pi_item_rtn[$i]['quantity']) && ($pi_item_rtn[$i]['quantity'] == 0 || $pi_item_rtn[$i]['quantity'] == ''))?1:intval($pi_item_rtn[$i]['quantity']));
			$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => isset($pi_item_rtn[$i]['quantity'])?$pi_item_rtn[$i]['quantity']:'');
			$formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'value' => isset($pi_item_rtn[$i]['description'])?$pi_item_rtn[$i]['description']:'');
			$formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => isset($pi_item_rtn[$i]['photos'])?$pi_item_rtn[$i]['photos']:'');
			$formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => isset($pi_item_rtn[$i]['ccode'])?$pi_item_rtn[$i]['ccode']:'');
			$formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => isset($pi_item_rtn[$i]['scode'])?$pi_item_rtn[$i]['scode']:'');
		}
		
		//最后一个
		$formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20);
		$formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled');
		$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled');
		//remark不加了，好像也沒什麼用
		//'pi_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		$formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled');
		$formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
		$formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
		$formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
		
		//$myerror->info($formItems);
		//die();
				
		$goodsForm->init($formItems);
		
		
		if(!$myerror->getAny() && $goodsForm->check()){
			//fb($_POST);

            //20141216 proforma的total里记录的统一用USD
            $currency = get_currency();

			$i = 1;//第一个post的是form的标识串，所以会跳过
			$pi_product = array();
			
			$pi_pvid = $_POST['pi_pvid']; 
			$pi_cid = $_POST['pi_cid']; 
			$pi_send_to = combineSendTo($_POST['pi_cid'], '', $_POST['pi_address']);//$_POST['pi_send_to'];
			$pi_attention = $_POST['pi_attention'];
			$pi_reference = $_POST['pi_reference']; 
			$pi_reference_num = $_POST['pi_reference_num']; 
			$pi_tel = $_POST['pi_tel']; 
			$pi_fax = $_POST['pi_fax'];
			$pi_currency = $_POST['pi_currency'];
			$pi_unit = $_POST['pi_unit'];
			//$pi_printed_by = $_POST['pi_printed_by'];
			$pi_packing_num = $_POST['pi_packing_num'];
			$pi_discount = $_POST['pi_discount'];
			$pi_expected_date = $_POST['pi_expected_date'];
			$pi_remark = $_POST['pi_remark'];
			//这个变量没用的，写着方便计算下面的数（14），不然忘了这个以后麻烦
			$pi_address = $_POST['pi_address'];
			
			
			//这个是在最后提交的哟
			$pi_remarks = $_POST['pi_remarks'];
			
			//remarks 在最後，所以這裡是14，有多了個mark_date所以是15了，又多了个created_by所以是16了，多了ETD所以现在是17了, admin多了status，所以admin是18了，而普通用户还是17
			if(isSysAdmin() && isset($_GET['modid'])){
				$num_index = 18;
			}
			else{
				$num_index = 17;
			}
			foreach( $_POST as $v){
				if( $i <= $num_index){
					$i++;
				}else{
					$pi_product[] = $v;	
				}
			}
			
			//如果是quotation转过来的就用当天日期，如果是modid、如果日期没有修改，就保持和原来一样，连时分秒都一样，如果有修改就把时分秒改为00：00：00
			$pi_mark_date = (isset($_GET['qid']) || isset($_GET['so_no']))?dateMore():((date('Y-m-d', strtotime($mod_result['mark_date'])) == $_POST['pi_mark_date'])?$mod_result['mark_date']:$_POST['pi_mark_date'].' 00:00:00');
			//暫時不知道這個打印日期是怎麼回事。。。
			//已改為起始日期與mark_date一樣，轉換PDF時會修改這個日期
			//现在只在生成pdf时改printed_date，修改表单的时候不作改动，如果是quotation转过来，没有printed_date就指定为当前日期
			$pi_printed_date = (isset($_GET['qid']) || isset($_GET['so_no']))?dateMore():$mod_result['printed_date'];
			$pi_printed_by = $_POST['pi_created_by'];
			
		    //fb($pi_product);
			
			//這裡的整除會有個bug，就是如果下面未填的空表格超過7個，就會將那些空的pid組成一個新的product插入quote_item表，但又由於都為空，所以會插入不成功，應該也沒多大影響。。。
			//减1是因为最后一个是remarks
			$pi_product_num = intval((count($pi_product)-1)/7);
			
			$pi_pid = array();
			$pi_p_description = array();
			$pi_p_quantity = array();
			$pi_p_price = array();
			//$pi_p_remark = array();
			$pi_p_photos = array();
			$pi_p_ccode = array();
			$pi_p_scode = array();
			
			$p_index = 0;
			for($j = 0; $j < $pi_product_num; $j++){
				$pi_pid[] = $pi_product[$p_index++];
				$pi_p_description[] = $pi_product[$p_index++];
				$pi_p_quantity[] = ($pi_product[$p_index] != '')?$pi_product[$p_index++]:0;
				//mod 20120927 去除钱数中的逗号
				$pi_p_price[] = str_replace(',', '', ($pi_product[$p_index] != '')?$pi_product[$p_index++]:0);//前後加兩次就出問題了，所以把前面的去掉
				//$pi_p_remark[] = $pi_product[$p_index++];
				$pi_p_photos[] = $pi_product[$p_index++];
				$pi_p_ccode[] = $pi_product[$p_index++];
				$pi_p_scode[] = $pi_product[$p_index++];
			}

            //fb($pi_product_num);

            //20130527 加限制，不允许在一个单中添加多个相同PID的item
            //20130614 vivian 说不能修改，所以先去掉这个限制
            //20160630 重新加上这个限制
            if(!check_repeat_item($pi_pid)){

                $total = 0;//算出來的
                $ex_total = 0;//這個也不知道是怎麼得出來的。。。
                $discount = 0;

                //fb($pi_pid);
                //$myerror->info($pi_cost_rmb);
                //$myerror->info($pi_p_quantity);
                //$myerror->info($pi_p_remark);
                //$myerror->info($pi_p_description);
                //$myerror->info($pi_p_photos);
                //$myerror->info($pi_p_ccode);
                //$myerror->info($pi_p_scode);
                //fb($_GET);
                //die();
                if(isset($_GET['modid']) && !isset($_GET['qid']) && !isset($_GET['copypvid']) && !isset($_GET['so_no'])){
                    if(isSysAdmin() && isset($_GET['modid'])){
                        $result = $mysql->q('update proforma set send_to = ?, attention = ?, tel = ?, fax = ?, reference = ?, remark = ?, mark_date = ?, reference_num = ?, packing_num = ?, currency = ?, unit = ?, printed_by = ?, printed_date = ?, total = ?, ex_total = ?, discount = ?, remarks = ?, cid = ?, expected_date = ?, istatus = ? where pvid = ?', $pi_send_to, $pi_attention, $pi_tel, $pi_fax, $pi_reference, $pi_remark, $pi_mark_date, $pi_reference_num, $pi_packing_num, $pi_currency, $pi_unit, $pi_printed_by, $pi_printed_date, $total, $ex_total, $pi_discount, $pi_remarks, $pi_cid, $pi_expected_date, $_POST['pi_status'], $_GET['modid']);
                    }else{
                        $result = $mysql->q('update proforma set send_to = ?, attention = ?, tel = ?, fax = ?, reference = ?, remark = ?, mark_date = ?, reference_num = ?, packing_num = ?, currency = ?, unit = ?, printed_by = ?, printed_date = ?, total = ?, ex_total = ?, discount = ?, remarks = ?, cid = ?, expected_date = ? where pvid = ?', $pi_send_to, $pi_attention, $pi_tel, $pi_fax, $pi_reference, $pi_remark, $pi_mark_date, $pi_reference_num, $pi_packing_num, $pi_currency, $pi_unit, $pi_printed_by, $pi_printed_date, $total, $ex_total, $pi_discount, $pi_remarks, $pi_cid, $pi_expected_date, $_GET['modid']);
                    }
                    //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                    if($result !== false){
                        $rtn = $mysql->q('delete from proforma_item where pvid = ?', $_GET['modid']);
                        for($k = 0; $k < $pi_product_num; $k++){
                            $rtn = $mysql->q('insert into proforma_item (pid, pvid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $pi_pid[$k], $pi_pvid, $pi_p_price[$k], $pi_p_quantity[$k], $pi_p_description[$k], $pi_p_photos[$k], $pi_p_ccode[$k], $pi_p_scode[$k]);

                            //20141216 proforma的total里记录的统一用USD
                            $total += ($pi_p_price[$k]/$currency[$pi_currency]*$currency['USD']*$pi_p_quantity[$k]);
                        }

                        //20140509 更新total
                        $mysql->q('update proforma set total = ? where pvid = ?', $total, $_GET['modid']);

                        //add action log
                        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                            , $_SESSION['logininfo']['aID'], $ip_real
                            , ACTION_LOG_SYS_MOD_PROFORMA, $_SESSION["logininfo"]["aName"]." <i>modify proforma</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_PROFORMA_S, "", "", 0);

                        $myerror->ok('修改 Proforma 成功!', 'BACK');
                    }else{
                        $myerror->error('修改 Proforma 失败', 'com-searchproforma&page=1');
                    }
                }elseif(!isset($_GET['modid']) && isset($_GET['qid']) && !isset($_GET['copypvid']) && !isset($_GET['so_no'])){
                    //判断是否输入的pvid已存在，因为存在的话由于数据库限制，就会新增失败
                    $judge = $mysql->q('select pvid from proforma where pvid = ?', $pi_pvid);
                    if(!$judge){
                        $result = $mysql->q('insert into proforma (pvid, send_to, attention, tel, fax, reference, remark, mark_date, reference_num, packing_num, currency, unit, printed_by, printed_date, total, ex_total, discount, remarks, cid, expected_date, istatus) values ('.moreQm(21).')', $pi_pvid, $pi_send_to, $pi_attention, $pi_tel, $pi_fax, $pi_reference, $pi_remark, $pi_mark_date, $pi_reference_num, $pi_packing_num, $pi_currency, $pi_unit, $pi_printed_by, $pi_printed_date, $total, $ex_total, $discount, $pi_remarks, $pi_cid, $pi_expected_date, '(I)');
                        if($result){
                            for($k = 0; $k < $pi_product_num; $k++){
                                $rtn = $mysql->q('insert into proforma_item (pid, pvid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $pi_pid[$k], $pi_pvid, $pi_p_price[$k], $pi_p_quantity[$k], $pi_p_description[$k], $pi_p_photos[$k], $pi_p_ccode[$k], $pi_p_scode[$k]);

                                //20141216 proforma的total里记录的统一用USD
                                $total += ($pi_p_price[$k]/$currency[$pi_currency]*$currency['USD']*$pi_p_quantity[$k]);
                            }

                            //20140509 更新total
                            $mysql->q('update proforma set total = ? where pvid = ?', $total, $pi_pvid);

                            //add action log
                            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                                , $_SESSION['logininfo']['aID'], $ip_real
                                , ACTION_LOG_SYS_ADD_PROFORMA_FROM_QUOTATION, $_SESSION["logininfo"]["aName"]." <i>add proforma</i> '".$pi_pvid."' from quotation '".$_GET['qid']."' in sys", ACTION_LOG_SYS_ADD_PROFORMA_FROM_QUOTATION_S, "", "", 0);

                            $myerror->ok('新增 Proforma 成功!', 'com-searchproforma&page=1');
                        }else{
                            $myerror->error('新增 Proforma 失败', 'BACK');
                        }
                    }else{
                        $myerror->error('输入的 Proforma NO.已存在，新增 Proforma 失败', 'BACK');
                    }
                }elseif(!isset($_GET['modid']) && !isset($_GET['qid']) && isset($_GET['copypvid']) && !isset($_GET['so_no'])){
                    //判断是否输入的pvid已存在，因为存在的话由于数据库限制，就会新增失败
                    $judge = $mysql->q('select pvid from proforma where pvid = ?', $pi_pvid);
                    if(!$judge){
                        $result = $mysql->q('insert into proforma (pvid, send_to, attention, tel, fax, reference, remark, mark_date, reference_num, packing_num, currency, unit, printed_by, printed_date, total, ex_total, discount, remarks, cid, expected_date, istatus) values ('.moreQm(21).')', $pi_pvid, $pi_send_to, $pi_attention, $pi_tel, $pi_fax, $pi_reference, $pi_remark, dateMore(), $pi_reference_num, $pi_packing_num, $pi_currency, $pi_unit, $pi_printed_by, $pi_printed_date, $total, $ex_total, $discount, $pi_remarks, $pi_cid, $pi_expected_date, '(I)');
                        if($result){
                            for($k = 0; $k < $pi_product_num; $k++){
                                $rtn = $mysql->q('insert into proforma_item (pid, pvid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $pi_pid[$k], $pi_pvid, $pi_p_price[$k], $pi_p_quantity[$k], $pi_p_description[$k], $pi_p_photos[$k], $pi_p_ccode[$k], $pi_p_scode[$k]);

                                //20141216 proforma的total里记录的统一用USD
                                $total += ($pi_p_price[$k]/$currency[$pi_currency]*$currency['USD']*$pi_p_quantity[$k]);
                            }

                            //20140509 更新total
                            $mysql->q('update proforma set total = ? where pvid = ?', $total, $pi_pvid);

                            //add action log
                            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                                , $_SESSION['logininfo']['aID'], $ip_real
                                , ACTION_LOG_SYS_COPY_PROFORMA, $_SESSION["logininfo"]["aName"]." <i>copy proforma</i> '".$_GET['copypvid']."' to '".$pi_pvid."' in sys", ACTION_LOG_SYS_COPY_PROFORMA_S, "", "", 0);

                            $myerror->ok('Copy 新增 Proforma 成功!', 'com-searchproforma&page=1');
                        }else{
                            $myerror->error('Copy 新增 Proforma 失败', 'BACK');
                        }
                    }else{
                        $myerror->error('输入的 Proforma NO.已存在，新增 Proforma 失败', 'BACK');
                    }
                }elseif(!isset($_GET['modid']) && !isset($_GET['qid']) && !isset($_GET['copypvid']) && isset($_GET['so_no'])){
                    //判断是否输入的pvid已存在，因为存在的话由于数据库限制，就会新增失败
                    $judge = $mysql->q('select pvid from proforma where pvid = ?', $pi_pvid);
                    if(!$judge){
                        $result = $mysql->q('insert into proforma (pvid, send_to, attention, tel, fax, reference, remark, mark_date, reference_num, packing_num, currency, unit, printed_by, printed_date, total, ex_total, discount, remarks, cid, expected_date, istatus) values ('.moreQm(21).')', $pi_pvid, $pi_send_to, $pi_attention, $pi_tel, $pi_fax, $pi_reference, $pi_remark, $pi_mark_date, $pi_reference_num, $pi_packing_num, $pi_currency, $pi_unit, $pi_printed_by, $pi_printed_date, $total, $ex_total, $discount, $pi_remarks, $pi_cid, $pi_expected_date, '(I)');
                        if($result){
                            for($k = 0; $k < $pi_product_num; $k++){
                                $rtn = $mysql->q('insert into proforma_item (pid, pvid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $pi_pid[$k], $pi_pvid, $pi_p_price[$k], $pi_p_quantity[$k], $pi_p_description[$k], $pi_p_photos[$k], $pi_p_ccode[$k], $pi_p_scode[$k]);

                                //20141216 proforma的total里记录的统一用USD
                                $total += ($pi_p_price[$k]/$currency[$pi_currency]*$currency['USD']*$pi_p_quantity[$k]);
                            }

                            //20140509 更新total
                            $mysql->q('update proforma set total = ? where pvid = ?', $total, $pi_pvid);

                            //add action log
                            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                                , $_SESSION['logininfo']['aID'], $ip_real
                                , ACTION_LOG_SYS_ADD_PROFORMA_FROM_SAMPLE_ORDER, $_SESSION["logininfo"]["aName"]."
                                <i>add proforma</i> '".$pi_pvid."' from sample order '".$_GET['so_no']."' in sys", ACTION_LOG_SYS_ADD_PROFORMA_FROM_SAMPLE_ORDER_S, "", "", 0);

                            $myerror->ok('新增 Proforma 成功!', 'com-searchproforma&page=1');
                        }else{
                            $myerror->error('新增 Proforma 失败', 'BACK');
                        }
                    }else{
                        $myerror->error('输入的 Proforma NO.已存在，新增 Proforma 失败', 'BACK');
                    }
                }else{
                    fb('没有匹配的操作');
                }
            }else{
                $myerror->error('不允许在一个单中添加相同的 Product Item ，新增或修改 Proforma 失败', 'BACK');
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
	<h1 class="green">PROFORMA INVOICE<em>* item must be filled in</em><? show_status_new($mod_result['istatus'])?></h1>
	<? /*
	<fieldset> 
	<legend class='legend'>Selected products</legend>
	<?
	if( isset($_SESSION['choose']) && !empty($_SESSION['choose'])){
		foreach($_SESSION['choose'] as $v){
			if (is_file($pic_path_com.$v) == true) { 
				$arr = getimagesize($pic_path_com.$v);
				$pic_width = $arr[0];
				$pic_height = $arr[1];
				$image_size = getimgsize(150, 100, $pic_width, $pic_height);
				echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com.$v.'" class="tooltip2" target="_blank" title="'.$v.'"><img src="/sys/'.$pic_path_com.$v.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">DELETE</a></b></div>';
			}else{
				echo '<div class="shadow" style="margin-left:28px;"><ul><li><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">DELETE</a></b></div>';
			}
		}
	}else{
		echo '<div style="margin-left:28px;"><b>Not Exist Checked Product</b></div>';
	}
	?>
	</fieldset>
	*/
	?>
	<? if(!isset($_GET['qid'])){ ?>
	<fieldset> 
	<legend class='legend'>Action</legend>
	<div style="margin-left:28px;"><a class="button" href="model/com/proforma_pdf.php?pvid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a><a class="button" href="model/com/proforma_pdf2.php?pvid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF2</b></a><a class="button" href="model/com/proforma_pdf3.php?pvid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF(支持中文)</b></a><a class="button" href="model/com/proforma_excel.php?pvid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>EXCEL</b></a><a class="button" href="model/com/proforma_pdf_with_photo.php?pvid=<?=$_GET['modid']?>&photo" target='_blank' onclick="return pdfConfirm()"><b>PDF - with Photo</b></a><a class="button" href="model/com/proforma_pdf_with_photo2.php?pvid=<?=$_GET['modid']?>&photo" target='_blank' onclick="return pdfConfirm()"><b>PDF - with Photo(支持中文)</b></a><a class="button" href="model/com/proforma_pdf_photo_list.php?pvid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF - Photo List</b></a><a class="button" href="?act=com-modifypurchase&pvid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Add to fty PO</b></a> <br /> <a class="button" href="?act=com-modifyinvoice&pvid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Add To Invoice</b></a><a class="button" href="?act=com-modifyinvoice&appendid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>ADD TO SUB-INV</b></a><a class="button" href="?act=com-modifyproforma&copypvid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Copy</b></a><a class="button" href="?act=com-shipment&page=1&pi_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Shipment</b></a><a class="button" href="?act=com-payment&page=1&pi_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Payment</b></a></div>
	</fieldset>
	<? } ?>
	<fieldset>
	<legend class='legend'><? if(isset($_GET['qid'])){ echo 'Quotation To Proforma Invoice';}elseif(isset($_GET['copypvid'])){ echo 'Copy Proforma Invoice';}else{ echo 'Modify Proforma Invoice';}?></legend>
	<?php
	$goodsForm->begin();
	?>
	<table width="100%" id="table">
	  <tr class="formtitle">
		<td width="25%"><? $goodsForm->show('pi_pvid');?></td>
		<td width="25%"><? $goodsForm->show('pi_cid');?></td>
		<td width="25%"><? $goodsForm->show('pi_attention');?></td>
		<td width="25%"><? $goodsForm->show('pi_mark_date');?></td>
	  </tr>
	  <tr>
		<td width="25%" valign="top"><? $goodsForm->show('pi_tel');?></td>
		<td width="25%" valign="top"><? $goodsForm->show('pi_fax');?></td>
		<td width="25%" colspan="2"><? $goodsForm->show('pi_address');?></td>
	  </tr> 
	  <tr>
		<td width="25%"><? $goodsForm->show('pi_reference_num');?></td> 
		<td width="25%"><? $goodsForm->show('pi_packing_num');?></td>
		<td width="25%"><? $goodsForm->show('pi_currency');?></td>   
		<td width="25%"><? $goodsForm->show('pi_unit');?></td>
		<? /*<td width="25%"><? $goodsForm->show('pi_printed_by');?></td>*/?>
	  </tr>
	  <tr>
		<td width="25%"><? $goodsForm->show('pi_reference');?></td>
		<td width="25%" colspan="2"><? $goodsForm->show('pi_remark');?></td>
		<td width="25%"><? $goodsForm->show('pi_discount');?></td>
	  </tr>    
      <tr>
		<td width="25%"><? $goodsForm->show('pi_created_by');?></td>  
      	<td width="25%"><? $goodsForm->show('pi_expected_date');?></td> 
        <? if(isSysAdmin() && isset($_GET['modid'])){?>
        <td width="25%"><? $goodsForm->show('pi_status');?></td> 
        <? }?>
      </tr>               
	</table>
	<div class="line"></div>
	<div style="margin-left:28px;">
	<label class="formtitle" for="g_cast"><font size="+1">Input Product</font><? if(isset($_GET['modid'])){ ?><a id="update_all" href="/mytools/script_update_item_info.php?value=<?=$_GET['modid']?>" style="float: right"><img title="Update all items info" src="../../../images/loop.png" alt="Update All" width="45px" /></a><? } ?><?=''//不用加提示语，因为用的POST的数据，我暂时还没发现有错误 //isset($_GET['modid'])?'<font color="#FF0000">（请先save，再修改。直接修改可能会丢失修改的内容。）</font>':''?></label>
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
    //20131216
    $total_qty = 0;
	for($i = 0; $i < $pi_item_num; $i++){

        $total_qty += $pi_item_rtn[$i]['quantity'];

		if (is_file($pic_path_com . $pi_item_rtn[$i]['photos']) == true) { 
			
			//圖片壓縮
			//$pi_item_rtn[$i]['photos']是原來的， $small_photo是縮小後的
			//$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
			$small_photo = 's_' . $pi_item_rtn[$i]['photos'];
			//縮小的圖片不存在才進行縮小操作
			if (!is_file($pic_path_small . $small_photo) == true) { 	
				makethumb($pic_path_com . $pi_item_rtn[$i]['photos'], $pic_path_small . $small_photo, 's');
			}
			/*
			$arr = getimagesize($pic_path_com . $pi_item_rtn[$i]['photos']);
			$pic_width = $arr[0];
			$pic_height = $arr[1];
			$image_size = getimgsize(80, 60, $pic_width, $pic_height);
			$photo_string = '<a href="/sys/'.$pic_path_com . $pi_item_rtn[$i]['photos'].'" target="_blank" title="'.$pi_item_rtn[$i]['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
			*/
			$photo_string = '<a href="/sys/'.$pic_path_com . $pi_item_rtn[$i]['photos'].'" target="_blank" title="'.$pi_item_rtn[$i]['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
		}else{ 
			$photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60' original='../images/nopic.gif'>";
		}	
	?>
	  <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
      	<td id="index" class="dragHandle"><?=$i+1?></td>
		<td><? $goodsForm->show('q_pid'.$i);?></td>
		<td><? $goodsForm->show('q_p_description'.$i);?></td>
		<td><? $goodsForm->show('q_p_quantity'.$i);?></td>
          <td align="right"><div id="packinglist_num"><?=@$pi_item_rtn[$i]['packinglist_num']?></div></td>
          <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
		<td><? $goodsForm->show('q_p_price'.$i); $goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
		<td id="sub"><?=/*formatMoney($pi_item_rtn[$i]['price']*(($pi_item_rtn[$i]['quantity'] == 0 || $pi_item_rtn[$i]['quantity'] == '')?1:$pi_item_rtn[$i]['quantity']))*/ /*20150428*/ formatMoney($pi_item_rtn[$i]['price']*$pi_item_rtn[$i]['quantity'])?></td>
		<td><?=$photo_string?></td>
	
		<? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
		<td><div id="his<?=$i?>"><img src="../../sys/images/Actions-edit-copy-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="History" /></div></td>
<!--        <td><div id="clear--><?//=$i?><!--"><img src="../../sys/images/clear.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Clear" /></div></td>-->
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
          <? /*<td><? $goodsForm->show('pi_p_remark');?></td>*/ ?>
		<td><? $goodsForm->show('q_p_price'.$i);$goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
		<td id="sub">0</td>
		<td>&nbsp;</td>
		<? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
		<td><div id="his<?=$i?>"></div></td>
<!--        <td><div id="clear--><?//=$i?><!--"></div></td>-->
		<td><div id="del<?=$i?>"></div></td>
	  </tr>
	  <script>
	    //修改后只要调用这个函数就行了，不用在写重复的代码在这个页面了
	    searchProduct(17, <?=$i?>);
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
            <td><div style="text-align: right; padding-right: 10px;" id="total">0</div></td>
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
                <td align="right">Credit: </td>
                <td><div style="text-align: right; padding-right: 10px;" id="credit"><?=formatMoney($total_amount)?></div></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td align="right">Total Paid: </td>
                <td><div style="text-align: right; padding-right: 10px;" id="credit"><?=formatMoney
                        ($rtn_payment_new['total_paid'])
                        ?></div></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        <? } ?>

	</table>
	</div>
	<div class="line"></div>
	<?
	$goodsForm->show('pi_remarks');
	?>
	<div class="line"></div>
	<?
	$goodsForm->show('submitbtn');
	?>
	</fieldset>
	<? if(!isset($_GET['qid'])){ ?>
	<fieldset> 
	<legend class='legend'>Action</legend>
        <div style="margin-left:28px;"><a class="button" href="model/com/proforma_pdf.php?pvid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a><a class="button" href="model/com/proforma_pdf2.php?pvid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF2</b></a><a class="button" href="model/com/proforma_pdf3.php?pvid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF(支持中文)</b></a><a class="button" href="model/com/proforma_excel.php?pvid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>EXCEL</b></a><a class="button" href="model/com/proforma_pdf_with_photo.php?pvid=<?=$_GET['modid']?>&photo" target='_blank' onclick="return pdfConfirm()"><b>PDF - with Photo</b></a><a class="button" href="model/com/proforma_pdf_with_photo2.php?pvid=<?=$_GET['modid']?>&photo" target='_blank' onclick="return pdfConfirm()"><b>PDF - with Photo(支持中文)</b></a><a class="button" href="model/com/proforma_pdf_photo_list.php?pvid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF - Photo List</b></a><a class="button" href="?act=com-modifypurchase&pvid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Add to fty PO</b></a> <br /> <a class="button" href="?act=com-modifyinvoice&pvid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Add To Invoice</b></a><a class="button" href="?act=com-modifyinvoice&appendid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>ADD TO SUB-INV</b></a><a class="button" href="?act=com-modifyproforma&copypvid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Copy</b></a><a class="button" href="?act=com-shipment&page=1&pi_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Shipment</b></a><a class="button" href="?act=com-payment&page=1&pi_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Payment</b></a></div>
	</fieldset>
	<? } ?>
	<?
	$goodsForm->end();
	
	}
	?>
	
	<script>
	$(function(){

        //20130807 for update all link to get customer and currency
        var href = $("#update_all").attr("href");
        var customer_value = $("#pi_cid").val();
        var currency_value = $("#pi_currency").val();
        $("#update_all").attr("href", href+'&customer='+customer_value+'&currency='+currency_value);
        //*****

		//load頁面就更新total值
		UpdateTotal();
		$(".template").hide();
		
		//20121012 加当有sample_order转过来的时候要多一个点击contact的事件，以便一开始就可以选择contact就能显示联系人的资料
		if(location.href.indexOf('&so_no=') > 0){
			selectCustomer("pi_");
			$("#pi_attention_container li").click(function(){
				attentionSelectText = $("#pi_attention_container li").parent().parent().prev().val();
				var qs2 = 'ajax=customer&act=ajax-search_contact_info&value='+attentionSelectText;
				$.ajax({
					type: "GET",
					url: "index.php",
					data: qs2,
					cache: false,
					dataType: "html",
					error: function(){
						alert('系统错误，查询customer失败');
					},
					success: function(data){
						if(data.indexOf('no-') < 0){
							var data_array = data.split("|");
							$("#pi_tel").val(data_array[0]);
							$("#pi_fax").val(data_array[1]);
							$("#pi_address").val(data_array[2])
						}else{
							//没有contact的customer ID的，都显示一下错误提示，太烦了，所以去掉了。。。
							//alert('无此Customer的contact信息！');							
							$("#pi_tel").val("");
							$("#pi_fax").val("");
							$("#pi_address").val("")
						}
					}
				})
			})			
		}else{
			selectCustomer("pi_")
		}
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
		currency("pi_");
	})
	</script>

<?
}
?>