<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();
//20130807 之前忘了加combine_invoice
if(!isset($_GET['vid']) && !isset($_GET['combine_invoice'])){
	judgeUserPermNew( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'')/*.(isset($_GET['copypvid'])?$_GET['copypvid']:'')*/ );
}
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	if(isset($_GET['delid']) && $_GET['delid'] != ''){
		if (!isSysAdmin()){
			$rtn = $mysql->q('update customs_invoice set istatus = ? where vid = ?', 'delete', $_GET['delid']);
			if($rtn){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_DEL_CUSTOMS_INVOICE, $_SESSION["logininfo"]["aName"]." <i>delete customs invoice</i> '".$_GET['delid']."' (change status to delete) in sys", ACTION_LOG_SYS_DEL_CUSTOMS_INVOICE_S, "", "", 0);

				$myerror->ok('删除 Customs Invoice 成功!', 'com-searchcustomsinvoice&page=1');
			}else{
				$myerror->error('删除 Customs Invoice 失败!', 'com-searchcustomsinvoice&page=1');	
			}
		}else{		
			//由於指定了foreign key，所以要先刪invoice_item裏的內容
			$rtn1 = $mysql->q('delete from customs_invoice_item where vid = ?', $_GET['delid']);
			$rtn2 = $mysql->q('delete from customs_invoice where vid = ?', $_GET['delid']);
			if($rtn2){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_DEL_CUSTOMS_INVOICE, $_SESSION["logininfo"]["aName"]." <i>delete customs invoice</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_CUSTOMS_INVOICE_S, "", "", 0);

				$myerror->ok('删除 Customs Invoice 成功!', 'com-searchcustomsinvoice&page=1');
			}else{
				$myerror->error('删除 Customs Invoice 失败!', 'com-searchcustomsinvoice&page=1');	
			}
		}
	}else{
		if(isset($_GET['modid']) && $_GET['modid'] != ''){
			$mod_result = $mysql->qone('SELECT * FROM customs_invoice WHERE vid = ?', $_GET['modid']);	
			//從send_to中拆出地址顯示
			$mod_result['address'] = substr($mod_result['send_to'], stripos($mod_result['send_to'], "\r\n"));
			
			$quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description, p.photos, p.ccode, p.scode FROM product p, customs_invoice_item q WHERE  p.pid = q.pid AND q.vid = ?', $_GET['modid']);
			$ci_item_rtn = $mysql->fetch();
			//$myerror->info($ci_item_rtn);die();
			$ci_item_num = count($ci_item_rtn);
			//$myerror->info($ci_item_num);
			//因為一開始沒有attention的選項所以要加上
			$mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
			//currency session
			//$_SESSION['currency'] = $mod_result['currency'];
		}elseif(isset($_GET['vid']) && isset($_GET['percent'])){
			//剛好沒有vid這個字段，所以vid是空的，proforma表和invoice表結構相似，省去了很多麻煩
			$mod_result = $mysql->qone('SELECT * FROM invoice WHERE vid = ?', $_GET['vid']);
			//mark_date初始为当天的日期，改在value里
			//從send_to中拆出地址顯示
			$mod_result['address'] = substr($mod_result['send_to'], stripos($mod_result['send_to'], "\r\n"));	
			
			$quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description, p.photos, p.ccode, p.scode FROM product p, invoice_item q WHERE  p.pid = q.pid AND q.vid = ?', $_GET['vid']);
			$ci_item_rtn = $mysql->fetch();
			//$myerror->info($ci_item_rtn);
			$ci_item_num = count($ci_item_rtn);
			//$myerror->info($ci_item_num);
			//因為一開始沒有attention的選項所以要加上
			$mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
			//vid 也要改
			//mod 4.3 不需要自动生成ID了 5.4 又改回來了
			$mod_result['vid'] = str_replace('PI', 'CI', $mod_result['vid']);
			//所有item 都要乘 percent
			for($i = 0; $i < $ci_item_num; $i++){
				$ci_item_rtn[$i]['price'] *= ($_GET['percent']/100);
			}
			//remarks 有改动
			$mod_result['remarks'] = 'THIS INVOICE IS FOR CUSTOMS CLEARANCE PURPOSE ONLY';
			//currency session
			//$_SESSION['currency'] = $mod_result['currency'];
			//i转为ci，remarks结尾要加东西
			$mod_result['remarks'] = $mod_result['remarks'] . '
HS CODE: 7117.1900
MADE IN CHINA';
	
		}elseif(isset($_GET['combine_invoice']) && $_GET['combine_invoice'] != '' && isset($_GET['percent'])){
			$invoice_arr = explode(',', $_GET['combine_invoice']);
			
			//剛好沒有vid這個字段，所以vid是空的，proforma表和invoice表結構相似，省去了很多麻煩
			//20130106 用combine列表里第一个invoice的信息填到前面
			$mod_result = $mysql->qone('SELECT * FROM invoice WHERE vid = ?', $invoice_arr[0]);
			//mark_date初始为当天的日期，改在value里
			//從send_to中拆出地址顯示
			$mod_result['address'] = substr($mod_result['send_to'], stripos($mod_result['send_to'], "\r\n"));	
			
			foreach($invoice_arr as $v){
				$quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description, p.photos, p.ccode, p.scode FROM product p, invoice_item q WHERE  p.pid = q.pid AND q.vid = ?', $v);
				$temp_item_rtn = $mysql->fetch();
				foreach($temp_item_rtn as $w){
					$ci_item_rtn[] = $w;
				}
				//$myerror->info($ci_item_rtn);
			}			
			$ci_item_num = count($ci_item_rtn);
			//$myerror->info($ci_item_num);
			//因為一開始沒有attention的選項所以要加上
			$mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
			//vid 也要改
			//mod 4.3 不需要自动生成ID了 5.4 又改回來了
			$mod_result['vid'] = str_replace('PI', 'CI', $mod_result['vid']);
			//所有item 都要乘 percent
			for($i = 0; $i < $ci_item_num; $i++){
				$ci_item_rtn[$i]['price'] *= ($_GET['percent']/100);
			}
			//remarks 有改动
			$mod_result['remarks'] = 'THIS INVOICE IS FOR CUSTOMS CLEARANCE PURPOSE ONLY';
			//currency session
			//$_SESSION['currency'] = $mod_result['currency'];
			//i转为ci，remarks结尾要加东西
			$mod_result['remarks'] = $mod_result['remarks'] . '
HS CODE: 7117.1900
MADE IN CHINA';			
		}else{
			die('Need modid!');	
		}
		
		
				
		$goodsForm = new My_Forms();
		$formItems = array(
				
				'ci_vid' => array('title' => 'Customs Invoice NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, (isset($_GET['vid'])||isset($_GET['combine_invoice']))?'':'readonly' => 'readonly', 'value' => isset($mod_result['vid'])?$mod_result['vid']:''),
				'ci_cid' => array('title' => 'Customer', 'type' => 'select', 'options' => get_customer(), 'required' => 1, 'value' => isset($mod_result['cid'])?$mod_result['cid']:''),
				//'ci_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['send_to'])?$mod_result['send_to']:''),
				'ci_attention' => array('title' => 'Attention', 'type' => 'select', 'options' => $mod_customer_contact, 'required' => 1, 'value' => isset($mod_result['attention'])?$mod_result['attention']:''),
				'ci_created_by' => array('title' => 'Created by', 'type' => 'select', 'required' => 1, 'options' => $user, 'value' => isset($mod_result['printed_by'])?$mod_result['printed_by']:''),				
				'ci_reference' => array('title' => 'Reference', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['reference'])?$mod_result['reference']:''),
				'ci_reference_num' => array('title' => 'Ref NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['reference_num'])?$mod_result['reference_num']:''),
				'ci_mark_date' => array('title' => 'Creation Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($_GET['pvid'])?date('Y-m-d'):(isset($mod_result['mark_date'])?date('Y-m-d', strtotime($mod_result['mark_date'])):'')),				
				'ci_tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['tel'])?$mod_result['tel']:''),
				'ci_fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['fax'])?$mod_result['fax']:''),
				'ci_currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1, 'value' => isset($mod_result['currency'])?$mod_result['currency']:''),
				'ci_unit' => array('title' => 'Unit', 'type' => 'select', 'options' => get_unit(), 'value' => isset($mod_result['unit'])?$mod_result['unit']:''),
				'ci_packing_num' => array('title' => 'Packing NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['packing_num'])?$mod_result['packing_num']:''),
				'ci_discount' => array('title' => 'Discount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['discount'])?intval($mod_result['discount']):''),
				
				'ci_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
				'ci_address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2, 'value' => isset($mod_result['address'])?$mod_result['address']:''),			
				'ci_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => isset($mod_result['remarks'])?$mod_result['remarks']:''),
				
				'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
				'q_p_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
				'q_p_quantity' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
				//remark不加了，好像也沒什麼用
				//'q_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
				'q_p_description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 400, 'rows' => 2, 'disabled' => 'disabled'),
				'q_p_photos' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
				'q_p_ccode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
				'q_p_scode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
							
				'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
				);
		
		//第一個上面用了
		//原来从1开始，现在从0开始
		for($i = 0; $i < $ci_item_num; $i++){
			$formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($ci_item_rtn[$i]['pid'])?$ci_item_rtn[$i]['pid']:'', 'readonly' => 'readonly');
			$formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => isset($ci_item_rtn[$i]['price'])?formatMoney($ci_item_rtn[$i]['price']):'');
			//$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => (isset($ci_item_rtn[$i]['quantity']) && ($ci_item_rtn[$i]['quantity'] == 0 || $ci_item_rtn[$i]['quantity'] == ''))?1:intval($ci_item_rtn[$i]['quantity']));
			$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => isset($ci_item_rtn[$i]['quantity'])?$ci_item_rtn[$i]['quantity']:'');		
			$formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 400, 'rows' => 2, 'value' => isset($ci_item_rtn[$i]['description'])?$ci_item_rtn[$i]['description']:'');
			$formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => isset($ci_item_rtn[$i]['photos'])?$ci_item_rtn[$i]['photos']:'');
			$formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => isset($ci_item_rtn[$i]['ccode'])?$ci_item_rtn[$i]['ccode']:'');
			$formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => isset($ci_item_rtn[$i]['scode'])?$ci_item_rtn[$i]['scode']:'');
		}
		
		//最后一个
		$formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20);
		$formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled');
		$formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled');
		//remark不加了，好像也沒什麼用
		//'ci_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
		$formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 400, 'rows' => 2, 'disabled' => 'disabled');
		$formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
		$formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
		$formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
		
		//$myerror->info($formItems);
		//die();
				
		$goodsForm->init($formItems);
		
		
		if(!$myerror->getAny() && $goodsForm->check()){
			//$myerror->info($_POST);
			
			$i = 1;//第一个post的是form的标识串，所以会跳过
			$ci_product = array();
			
			$ci_vid = $_POST['ci_vid']; 
			$ci_cid = $_POST['ci_cid']; 
			$ci_send_to = combineSendTo($_POST['ci_cid'], '', $_POST['ci_address']);//$_POST['ci_send_to'];
			$ci_attention = $_POST['ci_attention'];
			$ci_reference = $_POST['ci_reference']; 
			$ci_reference_num = $_POST['ci_reference_num']; 
			$ci_tel = $_POST['ci_tel']; 
			$ci_fax = $_POST['ci_fax'];
			$ci_currency = $_POST['ci_currency'];
			$ci_unit = $_POST['ci_unit'];
			//$ci_printed_by = $_POST['ci_printed_by'];
			$ci_packing_num = $_POST['ci_packing_num'];
			$ci_discount = $_POST['ci_discount'];
			$ci_remark = $_POST['ci_remark'];
			//这个变量没用的，写着方便计算下面的数（14），不然忘了这个以后麻烦
			$ci_address = $_POST['ci_address'];		
			
			//这个是在最后提交的哟
			$ci_remarks = $_POST['ci_remarks'];
			
			//remarks 在最後，所以這裡是14，有多了個mark_date所以是15了，又多了个created_by所以是16了
			foreach( $_POST as $v){
				if( $i <= 16){
					$i++;
				}else{
					$ci_product[] = $v;	
				}
			}
			//如果是proforma转过来的就用当天日期，如果是modid、如果日期没有修改，就保持和原来一样，连时分秒都一样，如果有修改就把时分秒改为00：00：00		
			$ci_mark_date = isset($_GET['pvid'])?dateMore():((date('Y-m-d', strtotime($mod_result['mark_date'])) == $_POST['ci_mark_date'])?$mod_result['mark_date']:$_POST['ci_mark_date'].' 00:00:00');
			//暫時不知道這個打印日期是怎麼回事。。。
			$ci_printed_date = isset($_GET['pvid'])?dateMore():$mod_result['printed_date'];
			$ci_printed_by = $_POST['ci_created_by'];
			
			//$myerror->info($ci_vid);
			
			//這裡的整除會有個bug，就是如果下面未填的空表格超過7個，就會將那些空的pid組成一個新的product插入quote_item表，但又由於都為空，所以會插入不成功，應該也沒多大影響。。。
			//减1是因为最后一个是remarks
			$ci_product_num = intval((count($ci_product)-1)/7);
			
			$ci_pid = array();
			$ci_p_description = array();
			$ci_p_quantity = array();
			$ci_p_price = array();
			//$ci_p_remark = array();
			$ci_p_photos = array();
			$ci_p_ccode = array();
			$ci_p_scode = array();
			
			$p_index = 0;
			for($j = 0; $j < $ci_product_num; $j++){
				$ci_pid[] = $ci_product[$p_index++];
				$ci_p_description[] = $ci_product[$p_index++];
				$ci_p_quantity[] = ($ci_product[$p_index] != '')?$ci_product[$p_index++]:0;
				//mod 20120927 去除钱数中的逗号
				$ci_p_price[] = str_replace(',', '', ($ci_product[$p_index] != '')?$ci_product[$p_index++]:0);//前後加兩次就出問題了，所以把前面的去掉
				//$ci_p_remark[] = $ci_product[$p_index++];
				$ci_p_photos[] = $ci_product[$p_index++];
				$ci_p_ccode[] = $ci_product[$p_index++];
				$ci_p_scode[] = $ci_product[$p_index++];
			}

            //20130527 加限制，不允许在一个单中添加多个相同PID的item
            if(!check_repeat_item($ci_pid)){

                $total = 0;//算出來的
                $ex_total = 0;//這個也不知道是怎麼得出來的。。。
                $discount = 0;

                //$myerror->info($ci_pid);
                //$myerror->info($ci_cost_rmb);
                //$myerror->info($ci_p_quantity);
                ////$myerror->info($ci_p_remark);
                //$myerror->info($ci_p_description);
                //$myerror->info($ci_p_photos);
                //$myerror->info($ci_p_ccode);
                //$myerror->info($ci_p_scode);

                //die();
                if(isset($_GET['modid']) && !isset($_GET['pvid'])){
                    $result = $mysql->q('update customs_invoice set vid = ?, send_to = ?, attention = ?, tel = ?, fax = ?, reference = ?, remark = ?, mark_date = ?, reference_num = ?, packing_num = ?, currency = ?, unit = ?, printed_by = ?, printed_date = ?, total = ?, ex_total = ?, discount = ?, remarks = ?, cid = ? where vid = ?', $ci_vid, $ci_send_to, $ci_attention, $ci_tel, $ci_fax, $ci_reference, $ci_remark, $ci_mark_date, $ci_reference_num, $ci_packing_num, $ci_currency, $ci_unit, $ci_printed_by, $ci_printed_date, $total, $ex_total, $ci_discount, $ci_remarks, $ci_cid, $_GET['modid']);
                    //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                    if($result !== false){
                        $rtn = $mysql->q('delete from customs_invoice_item where vid = ?', $_GET['modid']);
                        for($k = 0; $k < $ci_product_num; $k++){
                            $rtn = $mysql->q('insert into customs_invoice_item (pid, vid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $ci_pid[$k], $ci_vid, $ci_p_price[$k], $ci_p_quantity[$k], $ci_p_description[$k], $ci_p_photos[$k], $ci_p_ccode[$k], $ci_p_scode[$k]);

                        }

                        //add action log
                        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                            , $_SESSION['logininfo']['aID'], $ip_real
                            , ACTION_LOG_SYS_MOD_CUSTOMS_INVOICE, $_SESSION["logininfo"]["aName"]." <i>modify customs invoice</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_CUSTOMS_INVOICE_S, "", "", 0);

                        $myerror->ok('修改 Customs Invoice 成功!', 'BACK');
                    }else{
                        $myerror->error('修改 Customs Invoice 失败', 'BACK');
                    }
                    //pi轉為invoice的相當與新增一個invoice，所以是insert
                //20130807 之前忘了加 combine_invoice
                }elseif(!isset($_GET['modid']) && (isset($_GET['vid']) || isset($_GET['combine_invoice'])) && isset($_GET['percent'])){
                    //判断是否输入的vid已存在，因为存在的话由于数据库限制，就会新增失败
                    $judge = $mysql->q('select vid from customs_invoice where vid = ?', $ci_vid);
                    if(!$judge){
                        $result = $mysql->q('insert into customs_invoice (vid, send_to, attention, tel, fax, reference, remark, mark_date, reference_num, packing_num, currency, unit, printed_by, printed_date, total, ex_total, discount, remarks, cid) values ('.moreQm(19).')', $ci_vid, $ci_send_to, $ci_attention, $ci_tel, $ci_fax, $ci_reference, $ci_remark, $ci_mark_date, $ci_reference_num, $ci_packing_num, $ci_currency, $ci_unit, $ci_printed_by, $ci_printed_date, $total, $ex_total, $discount, $ci_remarks, $ci_cid);
                        if($result){
                            for($k = 0; $k < $ci_product_num; $k++){
                                $rtn = $mysql->q('insert into customs_invoice_item (pid, vid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $ci_pid[$k], $ci_vid, $ci_p_price[$k], $ci_p_quantity[$k], $ci_p_description[$k], $ci_p_photos[$k], $ci_p_ccode[$k], $ci_p_scode[$k]);
                            }

                            if(isset($_GET['vid'])){
                                //add action log
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                                    , $_SESSION['logininfo']['aID'], $ip_real
                                    , ACTION_LOG_SYS_ADD_CUSTOMS_INVOICE_FROM_INVOICE, $_SESSION["logininfo"]["aName"]." <i>add customs invoice</i> '".$ci_vid."' from invoice '".$_GET['vid']."' in sys", ACTION_LOG_SYS_ADD_CUSTOMS_INVOICE_FROM_INVOICE_S, "", "", 0);
                            }elseif(isset($_GET['combine_invoice'])){
                                //add action log
                                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                                    , $_SESSION['logininfo']['aID'], $ip_real
                                    , ACTION_LOG_SYS_COMBINE_CUSTOMS_INVOICE_FROM_INVOICE, $_SESSION["logininfo"]["aName"]." <i>combine customs invoice</i> '".$ci_vid."' from invoice '".$_GET['combine_invoice']."' in sys", ACTION_LOG_SYS_COMBINE_CUSTOMS_INVOICE_FROM_INVOICE_S, "", "", 0);
                            }

                            $myerror->ok('新增 Customs Invoice 成功!', 'com-searchcustomsinvoice&page=1');
                        }else{
                            $myerror->error('新增 Customs Invoice 失败', 'BACK');
                        }
                    }else{
                        $myerror->error('输入的 Customs Invoice NO.已存在，新增 Customs Invoice 失败', 'BACK');
                    }
                }
            }else{
                $myerror->error('不允许在一个单中添加相同的 Product Item ，新增或修改 Customs Invoice 失败', 'BACK');
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
	<h1 class="green">CUSTOMS INVOICE<em>* item must be filled in</em></h1>
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
				echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com.$v.'" class="tooltip2" target="_blank" title="'.$v.'"><img src="/sys/'.$pic_path_com.$v.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">【DELETE】</a></b></div>';
			}else{
				echo '<div class="shadow" style="margin-left:28px;"><ul><li><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/></li></ul><b><a href="?act=com-chooseproduct&delid='.$v.'">【DELETE】</a></b></div>';
			}
		}
	}else{
		echo '<div style="margin-left:28px;"><b>Not Exist Checked Product</b></div>';
	}
	?>
	</fieldset>
	*/
	?>
	<? if(!isset($_GET['pvid'])){ ?>
	<fieldset> 
	<legend class='legend'>Action</legend>
	<div style="margin-left:28px;"><a class="button" href="model/com/customs_invoice_pdf2.php?vid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a><a class="button" href="model/com/customs_invoice_excel.php?vid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>EXCEL</b></a></div>
	</fieldset>
	<? } ?>
	<fieldset> 
	<legend class='legend'><? if(!isset($_GET['pvid'])){ echo 'Modify Customs Invoice';}else{ echo 'Proforma To Invoice';}?></legend>
	<?php
	$goodsForm->begin();
	?>
	<table width="100%" id="table">
	  <tr class="formtitle">
		<td width="25%"><? $goodsForm->show('ci_vid');?></td>
		<td width="25%"><? $goodsForm->show('ci_cid');?></td>
		<td width="25%"><? $goodsForm->show('ci_attention');?></td>
		<td width="25%"><? $goodsForm->show('ci_mark_date');?></td>
	  </tr>
	  <tr>
		<td width="25%" valign="top"><? $goodsForm->show('ci_tel');?></td>
		<td width="25%" valign="top"><? $goodsForm->show('ci_fax');?></td>  	
		<td width="25%" colspan="2"><? $goodsForm->show('ci_address');?></td>      
	  </tr> 
	  <tr>
		<td width="25%" valign="top"><? $goodsForm->show('ci_reference_num');?></td>
		<td width="25%"><? $goodsForm->show('ci_packing_num');?></td> 
		<td width="25%"><? $goodsForm->show('ci_currency');?></td>   
		<td width="25%"><? $goodsForm->show('ci_unit');?></td>
		<? /*<td width="25%"><? $goodsForm->show('ci_printed_by');?></td>*/?>
	  </tr>
	  <tr>
		<td width="25%" valign="top"><? $goodsForm->show('ci_reference');?></td>
		<td width="25%" colspan="2"><? $goodsForm->show('ci_remark');?></td>
		<td width="25%"><? $goodsForm->show('ci_discount');?></td> 
	  </tr>   
	  <tr>
		<td width="25%"><? $goodsForm->show('ci_created_by');?></td>
	  </tr>                   
	</table>
	<div class="line"></div>
	<div style="margin-left:28px;">
	<label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>
	<table width="100%" id="tableDnD">
	<tbody id="tbody">
	  <tr class="formtitle nodrop nodrag">
      	<td width="3%"></td>
		<td width="17%">Product ID</td>
		<td width="34%">Description</td>
		<td width="8%">Quantity</td>
		<? /*<td width="20%">Product Remark</td>*/ ?>
		<td width="8%">Price</td>
		<td width="8%">Subtotal</td>
		<td width="8%">Photo</td>
		<? /*<td width="6%"><div class="del_all"><input name='' type='button' value='Del All' /></div></td>*/?>
		<td width="3%">&nbsp;</td>
		<td width="3%">&nbsp;</td>
<!--		<td width="3%">&nbsp;</td>-->
		<td width="5%">&nbsp;</td>
	  </tr>
	  <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
      	<td class="dragHandle"></td>
		<td><? $goodsForm->show('q_pid');?></td>
		<td><? $goodsForm->show('q_p_description');?></td>
		<td><? $goodsForm->show('q_p_quantity');?></td>
		<? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
		<td><? $goodsForm->show('q_p_price'); $goodsForm->show('q_p_photos'); $goodsForm->show('q_p_ccode'); $goodsForm->show('q_p_scode');?></td>  
		<td id="sub">0</td>
		<td></td>
		<td><div id="his"></div></td>
<!--		<td><div id="clear"></div></td>-->
		<td><div id="del"></div></td>
	  </tr>
	<script>
		//注意！！！js語句不能放在<tr></tr>裏面喲，注意，js語句不是放在哪裡都行的。。。
		searchProduct(17, '');
	</script>  
	<?
	for($i = 0; $i < $ci_item_num; $i++){
		if (is_file($pic_path_com . $ci_item_rtn[$i]['photos']) == true) { 
	
			//圖片壓縮
			//$ci_item_rtn[$i]['photos']是原來的， $small_photo是縮小後的
			//$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
			$small_photo = 's_' . $ci_item_rtn[$i]['photos'];
			if(filesize($pic_path_com . $ci_item_rtn[$i]['photos']) > 100){
				//縮小的圖片不存在才進行縮小操作
				if (!is_file($pic_path_small . $small_photo) == true) { 	
					makethumb($pic_path_com . $ci_item_rtn[$i]['photos'], $pic_path_small . $small_photo, 's');
				}
			}		
			/*
			$arr = getimagesize($pic_path_com . $ci_item_rtn[$i]['photos']);
			$pic_width = $arr[0];
			$pic_height = $arr[1];
			$image_size = getimgsize(80, 60, $pic_width, $pic_height);
			$photo_string = '<a href="/sys/'.$pic_path_com . $ci_item_rtn[$i]['photos'].'" target="_blank" title="'.$ci_item_rtn[$i]['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
			*/
			$photo_string = '<a href="/sys/'.$pic_path_com . $ci_item_rtn[$i]['photos'].'" target="_blank" title="'.$ci_item_rtn[$i]['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
		}else{ 
			$photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60' original='../images/nopic.gif'>";
		}	
	?>
	  <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
      	<td id="index" class="dragHandle"><?=$i+1?></td>
		<td><? $goodsForm->show('q_pid'.$i);?></td>
		<td><? $goodsForm->show('q_p_description'.$i);?></td>
		<td><? $goodsForm->show('q_p_quantity'.$i);?></td>
		<? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
		<td><? $goodsForm->show('q_p_price'.$i); $goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
		<td id="sub"><?=formatMoney($ci_item_rtn[$i]['price']*(($ci_item_rtn[$i]['quantity'] == 0 || $ci_item_rtn[$i]['quantity'] == '')?1:$ci_item_rtn[$i]['quantity']))?></td>
		<td><?=$photo_string?></td>
		<td><div id="his<?=$i?>"><img src="../../sys/images/Actions-edit-copy-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="History" /></div></td>
<!--		<td><div id="clear--><?//=$i?><!--"><img src="../../sys/images/clear.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Clear" /></div></td>-->
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
		<? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
		<td><? $goodsForm->show('q_p_price'.$i);$goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
		<td id="sub">0</td>
		<td>&nbsp;</td>
		<td><div id="his<?=$i?>"></div></td>
<!--		<td><div id="clear--><?//=$i?><!--"></div></td>-->
		<? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
		<td><div id="del<?=$i?>"></div></td>
	  </tr>
	  <script>
          //修改后只要调用这个函数就行了，不用在写重复的代码在这个页面了
          searchProduct(17, <?=$i?>);
	  </script>
	  
	  
	</tbody>
	<tr>
    	<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>  
		<td align="center">Total: </td>
		<td><div id="total">0</div></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr> 
	</table>
	</div>
	<div class="line"></div>
	<?
	$goodsForm->show('ci_remarks');
	?>
	<div class="line"></div>
	<?
	$goodsForm->show('submitbtn');
	?>
	</fieldset>
	<? if(!isset($_GET['pvid'])){ ?>
	<fieldset> 
	<legend class='legend'>Action</legend>
	<div style="margin-left:28px;"><a class="button" href="model/com/customs_invoice_pdf2.php?vid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a><a class="button" href="model/com/customs_invoice_excel.php?vid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>EXCEL</b></a></div>
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
		selectCustomer("ci_");
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
		currency("ci_");
	})
	</script>
	
<?
}
?>