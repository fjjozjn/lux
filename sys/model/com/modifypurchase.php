<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();
if(!isset($_GET['pvid'])){
    judgeUserPermNew( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'').(isset($_GET['copypcid'])?$_GET['copypcid']:'') );
}
if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    //20130529 加 overheads 、paid 、balance 的显示
    $overheads = 0;
    $paid = 0;
    $balance = 0;

    if(isset($_GET['delid']) && $_GET['delid'] != ''){

        //20141210 不论是否管理员删除，都清除 qc schedule
        //$mysql->q('delete from qc_schedule where pcid = ?', $_GET['delid']);

        if (!isSysAdmin()){
            $rtn = $mysql->q('update purchase set istatus = ? where pcid = ?', 'delete', $_GET['delid']);
            if($rtn){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_DEL_PURCHASE, $_SESSION["logininfo"]["aName"]." <i>delete purchase</i> '".$_GET['delid']."' (change status to delete) in sys", ACTION_LOG_SYS_DEL_PURCHASE_S, "", "", 0);

                $myerror->ok('删除 Purchase 成功!', 'com-searchpurchase&page=1');
            }else{
                $myerror->error('删除 Purchase 失败!', 'com-searchpurchase&page=1');
            }
        }else{
            //由於指定了foreign key，所以要先刪purchase_item裏的內容
            $rtn1 = $mysql->q('delete from purchase_item where pcid = ?', $_GET['delid']);
            $rtn2 = $mysql->q('delete from purchase where pcid = ?', $_GET['delid']);
            if($rtn2){

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['logininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_DEL_PURCHASE, $_SESSION["logininfo"]["aName"]." <i>delete purchase</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_PURCHASE_S, "", "", 0);

                $myerror->ok('删除 Purchase 成功!', 'com-searchpurchase&page=1');
            }else{
                $myerror->error('删除 Purchase 失败!', 'com-searchpurchase&page=1');
            }
        }
    }elseif(isset($_GET['approve_po_no']) && $_GET['approve_po_no'] != ''){
        $rtn = $mysql->qone('select attention, expected_date, istatus, sid from purchase where pcid = ?', $_GET['approve_po_no']);
        if($rtn['istatus'] == '(D)'){
            $rs = $mysql->q('update purchase set istatus = ?, approved_by = ? where pcid = ?', '(I)', $_SESSION['logininfo']['aNameChi'], $_GET['approve_po_no']);
            if($rs){

                //20141210 改为在approve了后添加 qc_schedule 记录
                //$mysql->q('insert into qc_schedule values (NULL, '.moreQm(6).')', $rtn['expected_date'], $_GET['approve_po_no'], dateMore(), '', $_SESSION['logininfo']['aName'], '');

                //20150412
                //send email to fty
                require_once(ROOT_DIR.'class/Mail/mail.php');

                $notice = '';
                /*$rs = $mysql->q('select AdminName, AdminEmail from tw_admin where FtyName = ? and AdminEmail <> ?', $rtn['sid'], '');
                if($rs){
                    $rtn_user = $mysql->fetch();
                    $notice .= '已发送通知邮件给 ';
                    foreach($rtn_user as $v){
                        $account_info = array('date' => date('Y-m-d'));
                        //邮件的信息
                        //$info = "你好,<br />附件為新的工廠訂單 ".$_GET['approve_po_no'].", 要求出货日期為".$rtn['expected_date'].", 你亦可以在樂思系統內查看及覆期.<br />(此郵件為系統訊息, 請勿回覆)<br />Best Regards,<br />Lux Design Limited";
                        $info = $v['AdminName']." 你好,<br />新的工廠訂單 ".$_GET['approve_po_no'].", 要求出货日期為".$rtn['expected_date'].", 你亦可以在樂思系統內查看及覆期.<br />(此郵件為系統訊息, 請勿回覆)<br />Best Regards,<br />Lux Design Limited";

                        send_mail($v['AdminEmail'], '', "新的工廠訂廠 - ".$_GET['approve_po_no']." (ETD: ".$rtn['expected_date'].")", $info, $account_info);
                        $notice .= $v['AdminName'].' ';
                    }
                }*/

                //20150423 只发邮件给purchase里的attention的人
                $user_rtn = $mysql->qone('select email from contact where concat(title, ?, name, ?, family_name) like ? and email <> ?', ' ', ' ', '%'.trim($rtn['attention']).'%', '');
                if($user_rtn){
                    $notice .= 'send mail to ';
                    $account_info = array('date' => date('Y-m-d'));
                    //邮件的信息
                    //$info = "你好,<br />附件為新的工廠訂單 ".$_GET['approve_po_no'].", 要求出货日期為".$rtn['expected_date'].", 你亦可以在樂思系統內查看及覆期.<br />(此郵件為系統訊息, 請勿回覆)<br />Best Regards,<br />Lux Design Limited";
                    $info = trim($rtn['attention'])." 你好,<br />新的工廠訂單 ".$_GET['approve_po_no'].", 要求出货日期為".$rtn['expected_date'].", 你亦可以在樂思系統內查看及覆期.<br />(此郵件為系統訊息, 請勿回覆)<br />Best Regards,<br />Lux Design Limited";

                    send_mail($user_rtn['email'], '', "新的工廠訂廠 - ".$_GET['approve_po_no']." (ETD: ".$rtn['expected_date'].")", $info, $account_info);
                    $notice .= trim($rtn['attention']).' ';
                }

                $myerror->ok('The Status of Factory PO changed from (D) to (I)! ( <span style="color:red">'.$notice.'</span> )',
                    'com-searchpurchase&page=1');
            }
        }elseif($rtn['istatus'] == '(I)'){
            //mod 20130209普通用户不能把已核批的状态改回为未核批
            if(isSysAdmin()){
                $rs = $mysql->q('update purchase set istatus = ?, approved_by = concat(approved_by,?) where pcid = ?', '(D)', 'disapproved', $_GET['approve_po_no']);
                if($rs){

                    //20141210 disapprove，都清除 qc schedule
                    //$mysql->q('delete from qc_schedule where pcid = ?', $_GET['approve_po_no']);

                    $myerror->ok('状态由(I)改为(D)!', 'com-searchpurchase&page=1');
                }
            }else{
                $myerror->error('Without Permission To Access', 'main');
            }
        }else{
            $myerror->error('状态为(D)时才能approve!', 'com-searchpurchase&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM purchase WHERE pcid = ?', $_GET['modid']);
            //從send_to中拆出地址顯示
            $mod_result['address'] = substr($mod_result['send_to'], stripos($mod_result['send_to'], "\r\n"));

            $quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description_chi, p.photos, p.ccode, p.scode FROM product p, purchase_item q WHERE  p.pid = q.pid AND q.pcid = ?', $_GET['modid']);
            $pc_item_rtn = $mysql->fetch();
            //$myerror->info($pc_item_rtn);die();
            $pc_item_num = count($pc_item_rtn);
            //$myerror->info($pc_item_num);
            //因為一開始沒有attention的選項所以要加上
            $mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
            //為去掉時分秒，因為輸入框不允許時分秒顯示
            //已改在下面value處
            //$mod_result['expected_date'] = date('Y-m-d', strtotime($mod_result['expected_date']));

            //20130529 加 overheads 、paid 、balance 的显示
            $rs_overheads = $mysql->q('select cost from overheads where po_no = ?', $_GET['modid']);
            if($rs_overheads){
                $rtn_overheads = $mysql->fetch();
                foreach($rtn_overheads as $v){
                    $overheads += $v['cost'];
                }
            }

            $rs_paid = $mysql->q('select amount from settlement where po_no = ?', $_GET['modid']);
            if($rs_paid){
                $rtn_paid = $mysql->fetch();
                foreach($rtn_paid as $v){
                    $paid += $v['amount'];
                }
            }

            $balance = $mod_result['total'] + $overheads - $paid;

            //20131020
            for($i = 0; $i < $pc_item_num; $i++){
                $rtn_delivery = $mysql->qone('select sum(quantity) as quantity from
                delivery_item where po_id = ? and p_id = ?', $_GET['modid'], $pc_item_rtn[$i]['pid']);
                $pc_item_rtn[$i]['delivery_num'] = $rtn_delivery['quantity'];
                if(!isset($pc_item_rtn[$i]['delivery_num']) || $pc_item_rtn[$i]['delivery_num'] == ''){
                    $pc_item_rtn[$i]['delivery_num'] = 0;
                }

                //20160717 加bom_link
                $pc_item_rtn[$i]['bom_link'] = '';
                $rtn = $mysql->qone('select id from bom where g_id = ?', $pc_item_rtn[$i]['pid']);
                if($rtn){
                    $pc_item_rtn[$i]['bom_link'] = "javascript:void(window.open('?act=formdetail&gid=".$pc_item_rtn[$i]['pid']."', 'lux', 'height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no'))";
                }else{
                    $pc_item_rtn[$i]['bom_link'] = "javascript:alert('none')";
                }
            }

            //20150105 加上生产计划的显示
            $pp_result = $mysql->qone('select * from fty_production_plan where pcid = ?', $_GET['modid']);

        }elseif(isset($_GET['pvid']) && $_GET['pvid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM proforma WHERE pvid = ?', $_GET['pvid']);
            //自动生成pcid
            $mod_result['pcid'] = autoGenerationID();
            //reference保存的是pi的ID
            $mod_result['reference'] = $_GET['pvid'];
            //20130426
            $mod_result['customer'] = $mod_result['cid'];
            //20130525
            $customer_rtn = $mysql->qone('select production_packaging, production_shipmark, production_remarks from customer where cid = ?', $mod_result['cid']);
            if(!empty($customer_rtn)){
                $mod_result['packaging'] = $customer_rtn['production_packaging'];
                $mod_result['ship_mark'] = $customer_rtn['production_shipmark'];
                $mod_result['production_remarks'] = $customer_rtn['production_remarks'];
            }

            //purchase比较特别用的是product的cost（但还是用的price的别名，因为不想改下面了。。。）和des-chi
            //$quote_item_result = $mysql->q('SELECT p.pid, p.cost_rmb as price, pi.quantity, p.description_chi, p.photos, p.ccode, p.scode FROM product p, proforma_item pi WHERE  p.pid = pi.pid AND pi.pvid = ?', $_GET['pvid']);
            //$pc_item_rtn = $mysql->fetch();

            //因為一開始沒有attention的選項所以要加上
            $mod_customer_contact = '';
            $pc_item_num = 0;

            //mod 20130325 扣除已转的item数量
            $pc_item_rtn = pi_to_po_item_auto_calculate($_GET['pvid']);
            //即使返回集为空，也不报错，只是item为空，如果用户想自己填还是可以的
            //还是改为严格的限制转完所有item就不能再转了
            if($pc_item_rtn){
                //$myerror->info($pc_item_rtn);die();
                $pc_item_num = count($pc_item_rtn);
                //$myerror->info($pc_item_num);
            }else{
                $myerror->error('此PI的所有ITEM已经转为PO，请不要再转换。', 'com-searchpurchase&page=1');
            }
        }elseif(isset($_GET['copypcid']) && $_GET['copypcid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM purchase WHERE pcid = ?', $_GET['copypcid']);
            //20130828 自动编号
            $mod_result['pcid'] = autoGenerationID();
            //從send_to中拆出地址顯示
            $mod_result['address'] = substr($mod_result['send_to'], stripos($mod_result['send_to'], "\r\n"));

            $quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description_chi, p.photos, p.ccode, p.scode FROM product p, purchase_item q WHERE  p.pid = q.pid AND q.pcid = ?', $_GET['copypcid']);
            $pc_item_rtn = $mysql->fetch();
            //$myerror->info($pc_item_rtn);die();
            $pc_item_num = count($pc_item_rtn);
            //$myerror->info($pc_item_num);
            //因為一開始沒有attention的選項所以要加上
            $mod_customer_contact = array(array($mod_result['attention'], $mod_result['attention']));
            //為去掉時分秒，因為輸入框不允許時分秒顯示
            //已改在下面value處
            //$mod_result['expected_date'] = date('Y-m-d', strtotime($mod_result['expected_date']));
            $_GET['modid'] = $_GET['copypcid'];
        }else{
            die('Need modid!');
        }



        $goodsForm = new My_Forms();
        $formItems = array(

            'pc_pcid' => array('title' => 'Factory PO No.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'readonly' => 'readonly', 'value' => isset($mod_result['pcid'])?$mod_result['pcid']:''),
            'pc_sid' => array('title' => 'Supplier ID', 'type' => 'select', 'options' => $supplier, 'required' => 1, 'value' => isset($mod_result['sid'])?$mod_result['sid']:''),
            //'pc_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['send_to'])?$mod_result['send_to']:''),
            'pc_reference' => array('title' => 'Proforma Invoice #', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['reference'])?$mod_result['reference']:''),
            'pc_attention' => array('title' => 'Attention', 'type' => 'select', 'required' => 1, 'options' => $mod_customer_contact, 'value' => isset($mod_result['attention'])?$mod_result['attention']:''),
            'pc_created_by' => array('title' => 'Created by', 'type' => 'select', 'required' => 1, 'options' => $user, 'value' => isset($mod_result['created_by'])?$mod_result['created_by']:''),
            //20130828 不论modify还是pvid或copy，都用当天的时间
            //20131013 Edit Date 改为不能修改 所以这里去掉了
            /*'pc_mark_date' => array('title' => 'Edit Date', 'type' => 'text', 'restrict' => 'date',
        'value' => date('Y-m-d'), 'readonly' => 'readonly'),*/
            'pc_customer' => array('title' => 'Customer', 'type' => 'select', 'options' => get_customer(), 'value' => isset($mod_result['customer'])?$mod_result['customer']:'', 'required' => 1),
            'pc_customer_po' => array('title' => 'Customer PO#', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['customer_po'])?$mod_result['customer_po']:''),

            'pc_expected_date' => array('title' => 'ETD', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['expected_date'])?date('Y-m-d', strtotime($mod_result['expected_date'])):''),

            'pc_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
            'pc_address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2, 'value' => isset($mod_result['address'])?$mod_result['address']:''),

            'pc_packaging' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:350px"', 'value' => isset($mod_result['packaging'])?$mod_result['packaging']:''),
            'pc_ship_mark' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:350px"', 'value' => isset($mod_result['ship_mark'])?$mod_result['ship_mark']:''),
            //proforma传过来的时候不用传remarks值
            'pc_remarks' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:350px"', 'value' => isset($_GET['pvid'])?(isset($mod_result['production_remarks'])?$mod_result['production_remarks']:''):(isset($mod_result['remarks'])?$mod_result['remarks']:'')),

            'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled', 'addon' => 'style="width:130px"'),
            'q_p_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
            'q_p_quantity' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
            //remark不加了，好像也沒什麼用
            //'q_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
            'q_p_description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled', 'addon' => 'style="width:300px"'),
            'q_p_photos' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
            'q_p_ccode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
            'q_p_scode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        //add 2013.11.6 让管理员能修改PO的状态
        if(isSysAdmin() && isset($_GET['modid'])){
            $formItems['pc_status'] = array('title' => 'Status', 'type' => 'select', 'options' => get_po_status(), 'required' => 1, 'value' => isset($mod_result['istatus'])?$mod_result['istatus']:'');
        }

        //第一個上面用了
        //原来从1开始，现在从0开始
        for($i = 0; $i < $pc_item_num; $i++){
            $formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($pc_item_rtn[$i]['pid'])?$pc_item_rtn[$i]['pid']:'', 'readonly' => 'readonly', 'addon' => 'style="width:130px"');
            $formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => isset($pc_item_rtn[$i]['price'])?formatMoney($pc_item_rtn[$i]['price']):'');
            $formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => (isset($pc_item_rtn[$i]['quantity']) && ($pc_item_rtn[$i]['quantity'] == 0 || $pc_item_rtn[$i]['quantity'] == ''))?1:intval($pc_item_rtn[$i]['quantity']));
            $formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'value' => isset($pc_item_rtn[$i]['description_chi'])?$pc_item_rtn[$i]['description_chi']:'', 'addon' => 'style="width:300px"');
            $formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => isset($pc_item_rtn[$i]['photos'])?$pc_item_rtn[$i]['photos']:'');
            $formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => isset($pc_item_rtn[$i]['ccode'])?$pc_item_rtn[$i]['ccode']:'');
            $formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => isset($pc_item_rtn[$i]['scode'])?$pc_item_rtn[$i]['scode']:'');
        }

        //最后一个
        $formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:130px"');
        $formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled');
        $formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled');
        //remark不加了，好像也沒什麼用
        //'pc_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
        $formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled', 'addon' => 'style="width:300px"');
        $formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
        $formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');
        $formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled');

        //$myerror->info($formItems);
        //die();

        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){
            //$myerror->info($_POST);

            $i = 1;//第一个post的是form的标识串，所以会跳过
            $pc_product = array();

            $pc_pcid = $_POST['pc_pcid'];
            $pc_sid = $_POST['pc_sid'];
            $pc_send_to = combineSendTo('', $_POST['pc_sid'], $_POST['pc_address']);//$_POST['pc_send_to'];
            $pc_reference = $_POST['pc_reference'];
            $pc_attention = $_POST['pc_attention'];
            $pc_customer = $_POST['pc_customer'];
            $pc_customer_po = $_POST['pc_customer_po'];
            $pc_expected_date = $_POST['pc_expected_date'];
            $pc_remark = $_POST['pc_remark'];
            //这个变量没用的，写着方便计算下面的数（10），不然忘了这个以后麻烦
            $pc_address = $_POST['pc_address'];

            //这些是在最后提交的哟
            $pc_packaging = $_POST['pc_packaging'];
            $pc_ship_mark = $_POST['pc_ship_mark'];
            $pc_remarks = $_POST['pc_remarks'];

            //有3个 在最後，所以這裡是10，又多了個mark_date所以是11了，又多了个created_by所以是12了
            //20131013 mark_date 去掉了，所以是11了
            //20131106 多了istatus 所以是管理员12了，普通用户还是11
            if(isSysAdmin() && isset($_GET['modid'])){
                $num_index = 12;
            }
            else{
                $num_index = 11;
            }
            foreach( $_POST as $v){
                if( $i <= $num_index){
                    $i++;
                }else{
                    $pc_product[] = $v;
                }
            }
            //如果是proforma转过来的就用当天日期，如果是modid、如果日期没有修改，就保持和原来一样，连时分秒都一样，如果有修改就把时分秒改为00：00：00
            //20131013 加in_date为加入时间，原来的mark_date为修改时间，所以下面这条规则也去掉了，所有时间都在下面指定
            //$mark_date = isset($_GET['pvid'])?dateMore():((date('Y-m-d', strtotime($mod_result['mark_date'])) == $_POST['pc_mark_date'])?$mod_result['mark_date']:$_POST['pc_mark_date'].' 00:00:00');
            //$attention = $_SESSION["logininfo"]["aName"];

            //$myerror->info($pc_product);

            //這裡的整除會有個bug，就是如果下面未填的空表格超過7個，就會將那些空的pid組成一個新的product插入quote_item表，但又由於都為空，所以會插入不成功，應該也沒多大影響。。。
            //减3是因为最后還有 packaging, ship mark, remarks
            $pc_product_num = intval((count($pc_product)-3)/7);

            $pc_pid = array();
            $pc_p_description = array();
            $pc_p_quantity = array();
            $pc_p_price = array();
            //$pc_p_remark = array();
            $pc_p_photos = array();
            $pc_p_ccode = array();
            $pc_p_scode = array();

            $total = 0;
            $p_index = 0;
            for($j = 0; $j < $pc_product_num; $j++){
                $pc_pid[] = $pc_product[$p_index++];
                $pc_p_description[] = $pc_product[$p_index++];
                $pc_p_quantity[] = $temp_q = ($pc_product[$p_index] != '')?$pc_product[$p_index++]:0;
                //mod 20120927 去除钱数中的逗号
                $pc_p_price[] = $temp_p = str_replace(',', '', ($pc_product[$p_index] != '')?$pc_product[$p_index++]:0);//前後加兩次就出問題了，所以把前面的去掉
                //$pc_p_remark[] = $pc_product[$p_index++];
                $pc_p_photos[] = $pc_product[$p_index++];
                $pc_p_ccode[] = $pc_product[$p_index++];
                $pc_p_scode[] = $pc_product[$p_index++];
                $total += $temp_q * $temp_p;
            }

            //20130527 加限制，不允许在一个单中添加多个相同PID的item
            if(!check_repeat_item($pc_pid)){

                $ex_total = 0;//這個也不知道是怎麼得出來的。。。

/*                $myerror->info($pc_pid);
                $myerror->info($pc_cost_rmb);
                $myerror->info($pc_p_quantity);
                $myerror->info($pc_p_remark);
                $myerror->info($pc_p_description);
                $myerror->info($pc_p_photos);
                $myerror->info($pc_p_ccode);
                $myerror->info($pc_p_scode);

                die();*/
                if(isset($_GET['modid']) && !isset($_GET['pvid']) && !isset($_GET['copypcid'])){
                    if(isSysAdmin() && isset($_GET['modid'])){
                        $result = $mysql->q('update purchase set pcid = ?, send_to = ?, attention = ?, reference = ?, remark = ?, mark_date = ?, total = ?, ex_total = ?, remarks = ?, sid = ?, ship_mark = ?, packaging = ?, customer_po = ?, customer = ?, expected_date = ?, created_by = ?, istatus = ? where pcid = ?', $pc_pcid, $pc_send_to, $pc_attention, $pc_reference, $pc_remark, dateMore(), $total, $ex_total, $pc_remarks, $pc_sid, $pc_ship_mark, $pc_packaging, $pc_customer_po, $pc_customer, $pc_expected_date, $_POST['pc_created_by'], $_POST['pc_status'], $_GET['modid']);
                    }else{
                        $result = $mysql->q('update purchase set pcid = ?, send_to = ?, attention = ?, reference = ?, remark = ?, mark_date = ?, total = ?, ex_total = ?, remarks = ?, sid = ?, ship_mark = ?, packaging = ?, customer_po = ?, customer = ?, expected_date = ?, created_by = ? where pcid = ?', $pc_pcid, $pc_send_to, $pc_attention, $pc_reference, $pc_remark, dateMore(), $total, $ex_total, $pc_remarks, $pc_sid, $pc_ship_mark, $pc_packaging, $pc_customer_po, $pc_customer, $pc_expected_date, $_POST['pc_created_by'], $_GET['modid']);
                    }

                    //20141203 加修改 qc_schedule 记录
                    //$mysql->q('update qc_schedule set qcs_date = ?, mod_date = ?, mod_by = ? where pcid = ?', $pc_expected_date, dateMore(), $_SESSION['logininfo']['aName'], $pc_pcid);

                    //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                    if($result !== false){
                        $rtn = $mysql->q('delete from purchase_item where pcid = ?', $_GET['modid']);
                        for($k = 0; $k < $pc_product_num; $k++){
                            //description 只寫入數據庫中的 description_chi字段，description保持為空
                            $rtn = $mysql->q('insert into purchase_item (pcid, pid, price, quantity, description, description_chi, photos, ccode, scode) values ('.moreQm(9).')', $pc_pcid, $pc_pid[$k], $pc_p_price[$k], $pc_p_quantity[$k], '', $pc_p_description[$k], $pc_p_photos[$k], $pc_p_ccode[$k], $pc_p_scode[$k]);
                        }

                        //add action log
                        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                            , $_SESSION['logininfo']['aID'], $ip_real
                            , ACTION_LOG_SYS_MOD_PURCHASE, $_SESSION["logininfo"]["aName"]." <i>modify purchase</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_PURCHASE_S, "", "", 0);

                        $myerror->ok('修改 Purchase 成功!', 'com-searchpurchase&page=1');
                    }else{
                        $myerror->error('修改 Purchase 失败', 'BACK');
                    }
                    //pi轉為purchase的相當與新增一個purchase，所以是insert
                }elseif(!isset($_GET['modid']) && isset($_GET['pvid']) && !isset($_GET['copypcid'])){
                    //判断是否输入的pcid已存在，因为存在的话由于数据库限制，就会新增失败
                    $judge = $mysql->q('select pcid from purchase where pcid = ?', $pc_pcid);
                    if(!$judge){
                        $in_date = $mark_date = dateMore();
                        $result = $mysql->q('insert into purchase (pcid, send_to, attention, reference, remark, in_date, mark_date, total, ex_total, remarks, sid, ship_mark, packaging, customer_po, customer, expected_date, created_by, istatus) values ('.moreQm(18).')', $pc_pcid, $pc_send_to, $pc_attention, $pc_reference, $pc_remark, $in_date, $mark_date, $total, $ex_total, $pc_remarks, $pc_sid, $pc_ship_mark, $pc_packaging, $pc_customer_po, $pc_customer, $pc_expected_date, $_POST['pc_created_by'], '(D)');

                        //20141203 加添加 qc_schedule 记录
                        //20141210 改为在approve了后添加 qc_schedule 记录
                        //$mysql->q('insert into qc_schedule values (NULL, '.moreQm(6).')', $pc_expected_date, $pc_pcid, $in_date, '', $_SESSION['logininfo']['aName'], '');

                        if($result){
                            for($k = 0; $k < $pc_product_num; $k++){
                                //description 只寫入數據庫中的 description_chi字段，description保持為空
                                $rtn = $mysql->q('insert into purchase_item (pcid, pid, price, quantity, description, description_chi, photos, ccode, scode) values ('.moreQm(9).')', $pc_pcid, $pc_pid[$k], $pc_p_price[$k], $pc_p_quantity[$k], '', $pc_p_description[$k], $pc_p_photos[$k], $pc_p_ccode[$k], $pc_p_scode[$k]);
                            }

                            //add action log
                            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                                , $_SESSION['logininfo']['aID'], $ip_real
                                , ACTION_LOG_SYS_ADD_PURCHASE_FROM_PROFORMA, $_SESSION["logininfo"]["aName"]." <i>add purchase</i> '".$pc_pcid."' from proforma '".$_GET['pvid']."' in sys", ACTION_LOG_SYS_ADD_PURCHASE_FROM_PROFORMA_S, "", "", 0);

                            $myerror->ok('新增 Purchase 成功!', 'com-searchpurchase&page=1');
                        }else{
                            $myerror->error('新增 Purchase 失败', 'BACK');
                        }
                    }else{
                        $myerror->error('输入的 Purchase NO.已存在，新增 Purchase 失败', 'BACK');
                    }
                }elseif(isset($_GET['copypcid'])){
                    //判断是否输入的pcid已存在，因为存在的话由于数据库限制，就会新增失败
                    $judge = $mysql->q('select pcid from purchase where pcid = ?', $pc_pcid);
                    if(!$judge){
                        $in_date = $mark_date = dateMore();
                        $result = $mysql->q('insert into purchase (pcid, send_to, attention, reference, remark, in_date, mark_date, total, ex_total, remarks, sid, ship_mark, packaging, customer_po, customer, expected_date, created_by, istatus) values ('.moreQm(18).')', $pc_pcid, $pc_send_to, $pc_attention, $pc_reference, $pc_remark, $in_date, $mark_date, $total, $ex_total, $pc_remarks, $pc_sid, $pc_ship_mark, $pc_packaging, $pc_customer_po, $pc_customer, $pc_expected_date, $_POST['pc_created_by'], '(D)');

                        //20141203 加添加 qc_schedule 记录
                        //20141210 改为在approve了后添加 qc_schedule 记录
                        //$mysql->q('insert into qc_schedule values (NULL, '.moreQm(6).')', $pc_expected_date, $pc_pcid, $in_date, '', $_SESSION['logininfo']['aName'], '');

                        if($result){
                            for($k = 0; $k < $pc_product_num; $k++){
                                //description 只寫入數據庫中的 description_chi字段，description保持為空
                                $rtn = $mysql->q('insert into purchase_item (pcid, pid, price, quantity, description, description_chi, photos, ccode, scode) values ('.moreQm(9).')', $pc_pcid, $pc_pid[$k], $pc_p_price[$k], $pc_p_quantity[$k], '', $pc_p_description[$k], $pc_p_photos[$k], $pc_p_ccode[$k], $pc_p_scode[$k]);
                            }

                            //add action log
                            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                                , $_SESSION['logininfo']['aID'], $ip_real
                                , ACTION_LOG_SYS_COPY_PURCHASE, $_SESSION["logininfo"]["aName"]." <i>copy purchase</i> '".$_GET['copypcid']."' to '".$pc_pcid."' in sys", ACTION_LOG_SYS_COPY_PURCHASE_S, "", "", 0);

                            $myerror->ok('新增 Purchase 成功!', 'com-searchpurchase&page=1');
                        }else{
                            $myerror->error('新增 Purchase 失败', 'BACK');
                        }
                    }else{
                        $myerror->error('输入的 Purchase NO.已存在，新增 Purchase 失败', 'BACK');
                    }
                }
            }else{
                $myerror->error('不允许在一个单中添加相同的 Product Item ，新增或修改 Purchase 失败', 'BACK');
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
        <h1 class="green">Factory PO<em>* item must be filled in</em><? show_status_new($mod_result['istatus']);?></h1>
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
        <? if(!isset($_GET['pvid'])){ ?>
            <fieldset>
                <legend class='legend'>Action</legend>
                <div style="margin-left:28px;">
                    <?
                    if (isSysAdmin()){
                        ?>
                        <a class="button" href="?act=com-modifypurchase&approve_po_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b><?=($mod_result['istatus']=='(D)')?'Approve':'Disapprove'?></b></a>
                    <?
                    }
                    ?>
                    <a class="button" href="model/com/purchase_pdf2.php?pcid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a><a class="button" href="?act=com-modifypurchase&copypcid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Copy</b></a><a class="button" href="?act=com-overheads&page=1&po_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Overheads</b></a><a class="button" href="?act=com-settlement&page=1&po_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Settlement</b></a><a class="button" href="?act=com-search_qc_report&page=1&pcid=<?=$_GET['modid']?>" ><b>QC REPORT</b></a><!--<a class="button" href="javascript:if(confirm('This operation will lead to the deletion of the data could not be resumed, confirmed to delete?'))window.location='?act=com-modifypurchase&delid=<?/*=$_GET['modid']*/?>'"><b>DEL</b></a>--></div>
            </fieldset>
        <? } ?>

        <fieldset>
        <legend class='legend'><? if(isset($_GET['pvid'])){ echo 'Proforma To Purchase';}elseif(isset($_GET['copypcid'])){ echo 'Copy Factory PO';}else{ echo 'Modify Factory PO';} ?></legend>
        <?php
        $goodsForm->begin();
        ?>
        <table width="100%" id="table">
            <tr class="formtitle">
                <td width="25%"><? $goodsForm->show('pc_pcid');?></td>
                <td width="25%"><? $goodsForm->show('pc_reference');?></td>
                <td width="25%"><? $goodsForm->show('pc_sid');?></td>
                <td width="25%"><? $goodsForm->show('pc_attention');?></td>
            </tr>
            <tr>
                <td width="25%" colspan="2"><? $goodsForm->show('pc_address');?></td>
                <td width="25%" valign="top"><? $goodsForm->show('pc_customer');?></td>
                <td width="25%" valign="top"><? $goodsForm->show('pc_customer_po');?></td>
            </tr>
            <tr>
                <!--		<td width="25%">--><?// $goodsForm->show('pc_mark_date');?><!--</td>-->
                <td width="25%"><? $goodsForm->show('pc_expected_date');?></td>
                <td width="25%" colspan="2"><? $goodsForm->show('pc_remark');?></td>
                <td width="25%"><? $goodsForm->show('pc_created_by');?></td>
            </tr>
            <tr>
                <? if(isSysAdmin() && isset($_GET['modid'])){?>
                    <td width="25%"><? $goodsForm->show('pc_status');?></td>
                <? }?>
                <td width="25%"><div class="set"><label class="formtitle">Creation Date</label><br /><?=isset($mod_result['in_date'])?$mod_result['in_date']:'None'?></div></td>
                <td width="25%"><div class="set"><label class="formtitle">Last Update Date</label><br /><?=isset($mod_result['mark_date'])?$mod_result['mark_date']:'None'?></div></td>
                <td width="25%"><div class="set"><label class="formtitle">Approved by</label><br /><?=isset($mod_result['approved_by'])?$mod_result['approved_by']:'None'?></div></td>
            </tr>
        </table>

        <?php if(isset($_GET['modid']) && $_GET['modid'] != ''){ ?>
            <div class="line"></div>
            <br />
            <div style="margin-left: 28px;"><label class="formtitle" for="g_cast"><font size="+1">生产计划</font></label></div>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="20%"><div class="set"><label class="formtitle">起板期</label><br /><?=(isset($pp_result['pc_date1'])?$pp_result['pc_date1']:'(None)')?></div></td>
                    <td width="20%"><div class="set"><label class="formtitle">半成品期</label><br /><?=(isset($pp_result['pc_date2'])?$pp_result['pc_date2']:'(None)')?></div></td>
                    <td width="20%"><div class="set"><label class="formtitle">成品期</label><br /><?=(isset($pp_result['pc_date3'])?$pp_result['pc_date3']:'(None)')?></div></td>
                    <td width="20%"><div class="set"><label class="formtitle">QC日期</label><br /><?=(isset($pp_result['pc_date4'])?$pp_result['pc_date4']:'(None)')?></div></td>
                    <td width="20%"><div class="set"><label class="formtitle">出货日期</label><br /><?=(isset($pp_result['pc_date5'])?$pp_result['pc_date5']:'(None)')?></div></td>
                </tr>
            </table>
            <br />

        <?php } ?>

        <div class="line"></div>
        <div style="margin-left:28px;">
            <label class="formtitle" for="g_cast"><font size="+1">Input Product</font><? if(isset($_GET['modid'])){ ?><a id="update_all" href="/mytools/script_update_item_info2.php?value=<?=$_GET['modid']?>" style="float: right">Update All</a><? } ?></label><?=(isset($_GET['pvid'])?'<font color="#FF0000">（已自动此删除此PI之前已转为PO的ITEM，或是减去已转为PO的ITEM的QUANTITY）</font>':'')?>
            <table width="100%" id="tableDnD">
                <tbody id="tbody">
                <tr class="formtitle nodrop nodrag">
                    <td width="3%"></td>
                    <td width="13%">Product ID</td>
                    <td width="8%" align="center">S Code</td>
                    <td width="25%">Description(Chi)</td>
                    <td width="9%">Quantity</td>
                    <td width="9%">Fty Shipped</td>
                    <? /*<td width="20%">Product Remark</td>*/ ?>
                    <td width="8%">Cost(RMB)</td>
                    <td width="8%">Subtotal</td>
                    <td width="8%" align="center">Photo</td>
                    <? /*<td width="6%"><div class="del_all"><input name='' type='button' value='Del All' /></div></td>*/?>
                    <td width="3%">&nbsp;</td>
                    <td width="3%">&nbsp;</td>
                    <td width="3%">&nbsp;</td>
                </tr>
                <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                    <td class="dragHandle"></td>
                    <td><? $goodsForm->show('q_pid');?></td>
                    <td align="center"><div id="scode"></div></td>
                    <td><? $goodsForm->show('q_p_description');?></td>
                    <td><? $goodsForm->show('q_p_quantity');?></td>
                    <td align="right"><div id="delivery_num"></div></td>
                    <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
                    <td><? $goodsForm->show('q_p_price'); $goodsForm->show('q_p_photos'); $goodsForm->show('q_p_ccode'); $goodsForm->show('q_p_scode');?></td>
                    <td id="sub">0</td>
                    <td></td>
                    <td><div id="his"></div></td>
                    <!--		<td><div id="clear"></div></td>-->
                    <td><div id="del"></div></td>
                    <td>&nbsp;</td>
                </tr>
                <script>
                    //注意！！！js語句不能放在<tr></tr>裏面喲，注意，js語句不是放在哪裡都行的。。。
                    searchProduct(15, '');
                </script>
                <?
                //20131216
                $total_qty = 0;
                for($i = 0; $i < $pc_item_num; $i++){

                    $total_qty += $pc_item_rtn[$i]['quantity'];

                    if (is_file($pic_path_com . $pc_item_rtn[$i]['photos']) == true) {

                        //圖片壓縮
                        //$pc_item_rtn[$i]['photos']是原來的， $small_photo是縮小後的
                        //$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
                        $small_photo = 's_' . $pc_item_rtn[$i]['photos'];
                        //縮小的圖片不存在才進行縮小操作
                        if (!is_file($pic_path_small . $small_photo) == true) {
                            makethumb($pic_path_com . $pc_item_rtn[$i]['photos'], $pic_path_small . $small_photo, 's');
                        }
                        /*
                        $arr = getimagesize($pic_path_com . $pc_item_rtn[$i]['photos']);
                        $pic_width = $arr[0];
                        $pic_height = $arr[1];
                        $image_size = getimgsize(80, 60, $pic_width, $pic_height);
                        $photo_string = '<a href="/sys/'.$pic_path_com . $pc_item_rtn[$i]['photos'].'" target="_blank" title="'.$pc_item_rtn[$i]['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
                        */
                        $photo_string = '<a href="/sys/'.$pic_path_com . $pc_item_rtn[$i]['photos'].'" target="_blank" title="'.$pc_item_rtn[$i]['photos'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
                    }else{
                        $photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60' original='../images/nopic.gif'>";
                    }
                    ?>
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                        <td id="index" class="dragHandle"><?=$i+1?></td>
                        <td><? $goodsForm->show('q_pid'.$i);?></td>
                        <td align="center"><div id="scode"><?=$pc_item_rtn[$i]['scode']?></div></td>
                        <td><? $goodsForm->show('q_p_description'.$i);?></td>
                        <td><? $goodsForm->show('q_p_quantity'.$i);?></td>
                        <td align="right"><div id="delivery_num"><?=@$pc_item_rtn[$i]['delivery_num']?></div></td>
                        <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
                        <td><? $goodsForm->show('q_p_price'.$i); $goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
                        <td id="sub"><?=formatMoney($pc_item_rtn[$i]['price']*(($pc_item_rtn[$i]['quantity'] == 0 || $pc_item_rtn[$i]['quantity'] == '')?1:$pc_item_rtn[$i]['quantity']))?></td>
                        <td><?=$photo_string?></td>
                        <td><div id="his<?=$i?>"><img src="../../sys/images/history-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="History" /></div></td>
                        <!--		<td><div id="clear--><?//=$i?><!--"><img src="../../sys/images/clear.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Clear" /></div></td>-->
                        <? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
                        <td><div id="del<?=$i?>"><img src="../../sys/images/del-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Delete" /></div></td>
                        <td><a href="<?=(isset($pc_item_rtn[$i]['bom_link'])?$pc_item_rtn[$i]['bom_link']:"")?>"><img src="../images/button_bom.png" alt="<?=$pc_item_rtn[$i]['pid']?>" /></a></td>
                    </tr>


                    <script>
                        //修改后只要调用这个函数就行了，不用在写重复的代码在这个页面了
                        searchProduct(15, <?=$i?>);
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
                    <td align="center"><div id="scode"></div></td>
                    <td><? $goodsForm->show('q_p_description'.$i);?></td>
                    <td><? $goodsForm->show('q_p_quantity'.$i);?></td>
                    <td align="right"><div id="delivery_num"></div></td>
                    <? /*<td><? $goodsForm->show('pc_p_remark');?></td>*/ ?>
                    <td><? $goodsForm->show('q_p_price'.$i);$goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
                    <td id="sub">0</td>
                    <td>&nbsp;</td>
                    <td><div id="his<?=$i?>"></div></td>
                    <!--		<td><div id="clear--><?//=$i?><!--"></div></td>-->
                    <? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
                    <td><div id="del<?=$i?>"></div></td>
                    <td><a href=""><img src="../images/button_bom.png" alt="" /></a></td>
                </tr>
                <script>
                    //修改后只要调用这个函数就行了，不用在写重复的代码在这个页面了
                    searchProduct(15, <?=$i?>);
                </script>


                </tbody>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td align="right">Total QTY: </td>
                    <td><div style="text-align: left; padding-left: 20px;" id="qty"><?=$total_qty?></div></td>
                    <td>&nbsp;</td>
                    <td style="text-align: right">Total : </td>
                    <td><div class="total" id="total">0</div></td>
                    <td>&nbsp;</td>
                </tr>
                <? if(isset($_GET['modid'])){ ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="text-align: right">Overheads : </td>
                        <td><div class="total" id="overhead"><?=formatMoney($overheads)?></div></td>
                        <td style="text-align: center">[<a
                                href="?act=com-overheads&page=1&po_no=<?=$_GET['modid']?>&po_date=<?=date('Y-m-d')?>">+</a>]</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="text-align: right">Paid : </td>
                        <td><div class="total" id="paid"><?=formatMoney($paid)?></div></td>
                        <td style="text-align: center">[<a
                                href="?act=com-settlement&page=1&po_no=<?=$_GET['modid']?>&st_date=<?=date('Y-m-d')?>">+</a>]</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="text-align: right">Balance : </td>
                        <td><div class="total" id="balance"><?=formatMoney($balance)?></div></td>
                        <td>&nbsp;</td>
                    </tr>
                <? } ?>
            </table>

            <div class="line"></div>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <th width="33%">Packaging</th>
                    <th width="33%">Ship mark</th>
                    <th width="33%">Remarks</th>
                </tr>
                <tr>
                    <td width="33%"><? $goodsForm->show('pc_packaging');?></td>
                    <td width="33%"><? $goodsForm->show('pc_ship_mark');?></td>
                    <td width="50%"><? $goodsForm->show('pc_remarks');?></td>
                </tr>
            </table>
            <div class="line"></div>
        </div>
        <?
        $goodsForm->show('submitbtn');
        ?>
        </fieldset>
        <? if(!isset($_GET['pvid'])){ ?>
            <fieldset>
                <legend class='legend'>Action</legend>
                <div style="margin-left:28px;">
                    <?
                    if (isSysAdmin()){
                        ?>
                        <a class="button" href="?act=com-modifypurchase&approve_po_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b><?=($mod_result['istatus']=='(D)')?'Approve':'Disapprove'?></b></a>
                    <?
                    }
                    ?>
                    <a class="button" href="model/com/purchase_pdf2.php?pcid=<?=$_GET['modid']?>" target='_blank' onclick="return pdfConfirm()"><b>PDF</b></a><a class="button" href="?act=com-modifypurchase&copypcid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Copy</b></a><a class="button" href="?act=com-overheads&page=1&po_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Overheads</b></a><a class="button" href="?act=com-settlement&page=1&po_no=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Settlement</b></a><a class="button" href="?act=com-search_qc_report&page=1&pcid=<?=$_GET['modid']?>" ><b>QC REPORT</b></a><!--<a class="button" href="javascript:if(confirm('This operation will lead to the deletion of the data could not be resumed, confirmed to delete?'))window.location='?act=com-modifypurchase&delid=<?/*=$_GET['modid']*/?>'"><b>DEL</b></a>--></div>
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
            selectSupplier("pc_");
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
        })
    </script>

<?
}
?>