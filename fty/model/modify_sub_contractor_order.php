<?php

/*
 change log

 */

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();if(!isset($_GET['pvid'])){

//检查权限
judgeUserPerm((isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['changeid'])?$_GET['changeid']:''));

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        $rs1 = $mysql->q('delete from fty_sub_contractor_order_item where main_id = ?', $_GET['delid']);
        $rs2 = $mysql->q('delete from fty_sub_contractor_order where id = ?', $_GET['delid']);
        if($rs2){
            $myerror->ok('删除加工单成功!', 'search_sub_contractor_order&page=1');
        }else{
            $myerror->error('删除加工单失败!', 'search_sub_contractor_order&page=1');
        }
    }elseif(isset($_GET['changeid']) && $_GET['changeid'] != ''){
        $rtn = $mysql->qone('select istatus from fty_sub_contractor_order where id = ?', $_GET['changeid']);
        $str = '';
        if($rtn['istatus'] == '(D)'){
            $rs = $mysql->q('update fty_sub_contractor_order set istatus = ?, approved_by = ?, approved_date = ? where id = ?', '(I)', $_SESSION["ftylogininfo"]["aName"], dateMore(), $_GET['changeid']);
            $str = '(I)';
        }else{
            $rs = $mysql->q('update fty_sub_contractor_order set istatus = ?, approved_by = ?, approved_date = ? where id = ?', '(D)', $_SESSION["ftylogininfo"]["aName"], dateMore(), $_GET['changeid']);
            $str = '(D)';
        }
        if($rs){
            $myerror->ok('更改 加工单 状态为 '.$str.'!', 'search_sub_contractor_order&page=1');
        }else{
            $myerror->error('更改 加工单 状态失败!', 'search_sub_contractor_order&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM fty_sub_contractor_order WHERE id = ?', $_GET['modid']);
            $mysql->q('SELECT p.pid, q.price, q.quantity, q.description_chi, p.photos, p.ccode, p.scode, q.task FROM product p, fty_sub_contractor_order_item q WHERE p.pid = q.pid AND q.main_id = ?', $_GET['modid']);
            $jg_item_rtn = $mysql->fetch();
            $jg_item_num = count($jg_item_rtn);

            //20140323
            /*for($i = 0; $i < $jg_item_num; $i++){
                $rtn_delivery = $mysql->qone('select sum(quantity) as quantity from
                    delivery_item where po_id = ? and p_id = ?', $_GET['modid'], $jg_item_rtn[$i]['pid']);
                $jg_item_rtn[$i]['delivery_num'] = $rtn_delivery['quantity'];
                if(!isset($jg_item_rtn[$i]['delivery_num']) || $jg_item_rtn[$i]['delivery_num'] == ''){
                    $jg_item_rtn[$i]['delivery_num'] = 0;
                }
            }*/

            //因為一開始沒有attention的選項所以要加上
            $mysql->q('select name from fty_jg_contact where cid = ?', $mod_result['cid']);
            $contact_item_rtn = $mysql->fetch();
            foreach($contact_item_rtn as $v){
                $mod_customer_contact[] = array($v['name'], $v['name']);
            }

        }elseif(isset($_GET['pcid']) && $_GET['pcid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM purchase WHERE pcid = ?', $_GET['pcid']);

            //str_replace('|', "','", trim($_GET['pid'], '|'))
            $quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description_chi, p.photos, p.ccode, p.scode FROM product p, purchase_item q WHERE  p.pid = q.pid AND q.pcid = ?', $_GET['pcid']);
            $jg_item_rtn = $mysql->fetch();
            $jg_item_num = count($jg_item_rtn);
            //20150531 只显示订单插入的加工单ITEM
            $temp = array();
            foreach($jg_item_rtn as $v){
                if(strpos($_GET['pid'], $v['pid']) !== false){
                    $temp[] = $v;
                }
            }
            $jg_item_rtn = $temp;
            $jg_item_num = count($jg_item_rtn);


            //20140323
            for($i = 0; $i < $jg_item_num; $i++){
                $rtn_delivery = $mysql->qone('select sum(quantity) as quantity from
                delivery_item where po_id = ? and p_id = ?', $_GET['pcid'], $jg_item_rtn[$i]['pid']);
                $jg_item_rtn[$i]['delivery_num'] = $rtn_delivery['quantity'];
                if(!isset($jg_item_rtn[$i]['delivery_num']) || $jg_item_rtn[$i]['delivery_num'] == ''){
                    $jg_item_rtn[$i]['delivery_num'] = 0;
                }
            }

            //特殊的参数
            $mod_result['reference'] = $_GET['pcid'];
        }else{
            die('Need modid!');
        }

        $goodsForm = new My_Forms();
        $formItems = array(

            'jg_reference' => array('title' => '参考', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['reference'])?$mod_result['reference']:'', 'readonly' => 'readonly', 'required' => 1),

            //'jg_cid' => array('title' => '供应商', 'type' => 'select', 'options' => get_supplier(), 'value' => isset($fty_info[0])?$fty_info[0]:'', 'required' => 1),
            'jg_cid' => array('title' => '加工商', 'type' => 'select', 'value' => isset($_GET['modid'])?$mod_result['cid']:'', 'required' => 1, 'options' => get_jg_customer()),

            //'jg_attention' => array('title' => '致', 'type' => 'select', 'options' => '', 'value' => isset($mod_result['attention'])?$mod_result['attention']:'', 'required' => 1),
            'jg_attention' => array('title' => '致', 'type' => 'select', 'value' => isset($_GET['modid'])?$mod_result['attention']:'', 'required' => 1, 'options' => isset($_GET['modid'])?$mod_customer_contact:''),

            //'jg_created_by' => array('title' => '负责人', 'type' => 'text', 'value' => isset($mod_result['created_by'])?$mod_result['created_by']:'', 'required' => 1),

            //'jg_customer' => array('title' => '客户', 'type' => 'select', 'options' => get_customer(), 'value' => isset($mod_result['customer'])?$mod_result['customer']:'', 'required' => 1),

            //***** 这一部分是可以从订单转来
            'jg_customer' => array('title' => '客户', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['customer'])?$mod_result['customer']:''),

            'jg_customer_po' => array('title' => '客户单号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['customer_po'])?$mod_result['customer_po']:''),

            'jg_expected_date' => array('title' => '要求出货日期', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['expected_date'])?date('Y-m-d', strtotime($mod_result['expected_date'])):'', 'required' => 1),

            'jg_remark' => array('title' => '备注', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),
            //*****

            'jg_address' => array('title' => '地址', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2, 'value' => isset($_GET['modid'])?$mod_result['address']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' 提交 '),
        );

        //第一個上面用了
        //原来从1开始，现在从0开始
        for($i = 0; $i < $jg_item_num; $i++){
            $formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($jg_item_rtn[$i]['pid'])?$jg_item_rtn[$i]['pid']:'', 'readonly' => 'readonly');
            $formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => isset($jg_item_rtn[$i]['price'])?($jg_item_rtn[$i]['price']):'');
            $formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => (isset($jg_item_rtn[$i]['quantity']) && ($jg_item_rtn[$i]['quantity'] == 0 || $jg_item_rtn[$i]['quantity'] == ''))?1:intval($jg_item_rtn[$i]['quantity']));
            $formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'value' => isset($jg_item_rtn[$i]['description_chi'])?$jg_item_rtn[$i]['description_chi']:'', 'addon' => 'style="width:300px"');
            $formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => isset($jg_item_rtn[$i]['photos'])?$jg_item_rtn[$i]['photos']:'', 'readonly' => 'readonly');
            $formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => isset($jg_item_rtn[$i]['ccode'])?$jg_item_rtn[$i]['ccode']:'', 'readonly' => 'readonly');
            $formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => isset($jg_item_rtn[$i]['scode'])?$jg_item_rtn[$i]['scode']:'', 'readonly' => 'readonly');
            $formItems['q_p_task'.$i] = array('type' => 'checkbox', 'options' => get_fty_task($jg_item_rtn[$i]['pid']), 'addon' => 'style="width:150px"', 'value' => isset($jg_item_rtn[$i]['task'])?explode('|', $jg_item_rtn[$i]['task']):'');
        }
        //die();

        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){

            fb($_POST);
            //die('error');

            $sco_id = isset($_GET['pcid'])?fty_autoGenerationID():$_GET['modid'];

            //die($sco_id);

            $jg_reference = $_POST['jg_reference'];
            $jg_cid = $_POST['jg_cid'];

            $rtn_fty_customer = mysql_qone('select name from fty_jg_customer where cid = ?', $_POST['jg_cid']);
            $jg_send_to = isset($rtn_fty_customer['name'])?$rtn_fty_customer['name']:'';

            $jg_address = $_POST['jg_address'];
            $jg_attention = trim($_POST['jg_attention']);
            $jg_customer = $_POST['jg_customer'];
            $jg_customer_po = $_POST['jg_customer_po'];
            $jg_expected_date = $_POST['jg_expected_date'];
            $jg_remark = $_POST['jg_remark'];

            $i = 0;
            $prev_num = 9;//第一个post的是form的标识串，还有8个表单项
            $last_num = 1;//后面的post，有个submit
            $q_product = array();
            foreach( $_POST as $v){
                if( $i < $prev_num){
                    $i++;
                }elseif($i >= count($_POST) - $last_num){
                    break;
                }else{
                    $q_product[] = $v;
                    $i++;
                }
            }

            $in_date = $mod_date = dateMore();
            $created_by = $mod_by = $_SESSION["ftylogininfo"]["aName"];

            //这个是设置每个ITEM的元素个数
            $each_item_num = 8;
            $q_product_num = intval(count($q_product)/$each_item_num);

            $q_pid = array();
            $q_p_description = array();
            $q_p_quantity = array();
            $q_p_price = array();
            $q_p_photos = array();
            $q_p_ccode = array();
            $q_p_scode = array();
            $q_p_task = array();

            $total = 0;
            $p_index = 0;
            for($j = 0; $j < $q_product_num; $j++){
                $q_pid[] = $q_product[$p_index++];
                $q_p_description[] = $q_product[$p_index++];
                $q_p_quantity[] = $temp_q = ($q_product[$p_index] != '')?$q_product[$p_index++]:0;
                //mod 20120927 去除钱数中的逗号
                $q_p_price[] = $temp_p = str_replace(',', '', ($q_product[$p_index] != '')?$q_product[$p_index++]:0);//前後加兩次就出問題了，所以把前面的去掉
                $q_p_photos[] = $q_product[$p_index++];
                $q_p_ccode[] = $q_product[$p_index++];
                $q_p_scode[] = $q_product[$p_index++];
                $q_p_task[] = implode('|', $q_product[$p_index++]);
                $total += $temp_q * $temp_p;
            }

            /*fb($q_pid);
            fb($q_p_description);
            fb($q_p_quantity);
            fb($q_p_price);
            fb($q_p_photos);
            fb($q_p_ccode);
            fb($q_p_scode);
            fb($q_p_task);
            fb($total);
            die('@');*/

            //20130527 加限制，不允许在一个单中添加多个相同PID的item
            //if(!check_repeat_item($q_pid)){

                //还不知要怎么算这个
                $ex_total = 0;

                if(isset($_GET['modid']) && $_GET['modid'] != ''){

                    $result = $mysql->q('update fty_sub_contractor_order set send_to = ?, address = ?, attention = ?, reference = ?, remark = ?, total = ?, ex_total = ?, remarks = ?, cid = ?, ship_mark = ?, packaging = ?, customer_po = ?, customer = ?, mod_date = ?, expected_date = ?, mod_by = ? where id = ?', $jg_send_to, $jg_address, $jg_attention, $jg_reference, $jg_remark, $total, $ex_total, '', $jg_cid, '', '', $jg_customer_po, $jg_customer, $mod_date, $jg_expected_date, $mod_by, $_GET['modid']);

                    if($result){
                        $rtn = $mysql->q('delete from fty_sub_contractor_order_item where main_id = ?', $_GET['modid']);
                        for($k = 0; $k < $q_product_num; $k++){
                            //description 只寫入數據庫中的 description_chi字段，description保持為空
                            $mysql->q('insert into fty_sub_contractor_order_item set main_id = ?, pid = ?, price = ?, quantity = ?, description = ?, description_chi = ?, photos = ?, ccode = ?, scode = ?, task = ?', $_GET['modid'], $q_pid[$k], $q_p_price[$k], $q_p_quantity[$k], '', $q_p_description[$k], $q_p_photos[$k], $q_p_ccode[$k], $q_p_scode[$k], $q_p_task[$k]);
                        }

                        //加工商ap（应付欠款）修改
                        $mysql->q('update fty_jg_customer set ap = ap + ? where cid = ?', ($total - $mod_result['total']), $jg_cid);

                        $myerror->ok('修改加工单成功!', 'search_sub_contractor_order&page=1');
                    }else{
                        $myerror->error('修改加工单失败', 'BACK');
                    }

                }elseif(isset($_GET['pcid']) && $_GET['pcid'] != ''){

                    //判断是否输入的pcid已存在，因为存在的话由于数据库限制，就会新增失败
                    $judge = $mysql->q('select sco_id from fty_sub_contractor_order where sco_id = ?', $sco_id);
                    if(!$judge){
                        $result = $mysql->q('insert into fty_sub_contractor_order set sco_id = ?, send_to = ?, address = ?, attention = ?, reference = ?, remark = ?, total = ?, ex_total = ?, remarks = ?, cid = ?, ship_mark = ?, packaging = ?, customer_po = ?, customer = ?, in_date = ?, mod_date = ?, expected_date = ?, created_by = ?, mod_by = ?, istatus = ?', $sco_id, $jg_send_to, $jg_address, $jg_attention, $jg_reference, $jg_remark, $total, $ex_total, '', $jg_cid, '', '', $jg_customer_po, $jg_customer, $in_date, $mod_date, $jg_expected_date, $created_by, $mod_by, '(D)');
                        $main_id = $mysql->id();

                        if($result){
                            for($k = 0; $k < $q_product_num; $k++){
                                //description 只寫入數據庫中的 description_chi字段，description保持為空
                                $mysql->q('insert into fty_sub_contractor_order_item set main_id = ?, pid = ?, price = ?, quantity = ?, description = ?, description_chi = ?, photos = ?, ccode = ?, scode = ?, task = ?', $main_id, $q_pid[$k], $q_p_price[$k], $q_p_quantity[$k], '', $q_p_description[$k], $q_p_photos[$k], $q_p_ccode[$k], $q_p_scode[$k], $q_p_task[$k]);
                            }

                            //加工商ap（应付欠款）修改
                            $mysql->q('update fty_jg_customer set ap = ap + ? where cid = ?', $total, $jg_cid);

                            $myerror->ok('新增加工单成功!', 'search_sub_contractor_order&page=1');
                        }else{
                            $myerror->error('新增加工单失败', 'BACK');
                        }
                    }else{
                        $myerror->error('系统错误，新增加工单失败', 'BACK');
                    }

                }
            /*}else{
                $myerror->error('不允许在一个单中添加相同的产品', 'BACK');
            }*/
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
        <h1 class="green">加工单<? //show_status_new($mod_result['istatus']);?></h1>

        <fieldset>
            <legend class='legend'>加工单</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <?php if(isset($_GET['pcid']) && $_GET['pcid'] != ''){
                        ?>
                        <td width="25%"><div class="set"><label class="formtitle">加工单编号</label><br />(自动生成)</div></td>
                    <?php
                    }elseif(isset($_GET['modid']) && $_GET['modid'] != ''){
                        ?>
                        <td width="25%"><div class="set"><label class="formtitle">加工单编号</label><br /><?=$mod_result['sco_id']?></div></td>
                    <?php
                    }else{
                        ?>
                        <td width="25%"><div class="set"><label class="formtitle">加工单编号</label><br /></div></td>
                    <?php
                    }
                    ?>
                    <td width="25%"><? $goodsForm->show('jg_reference');?></td>
                    <td width="25%"><? $goodsForm->show('jg_cid');?></td>
                    <td width="25%"><? $goodsForm->show('jg_attention');?></td>
                </tr>
                <tr>
                    <td width="25%" colspan="2"><? $goodsForm->show('jg_address');?></td>
                    <td width="25%" valign="top"><? $goodsForm->show('jg_customer');?></td>
                    <td width="25%" valign="top"><? $goodsForm->show('jg_customer_po');?></td>
                </tr>
                <tr>
                    <td width="25%"><? $goodsForm->show('jg_expected_date');?></td>
                    <td width="25%" colspan="2"><? $goodsForm->show('jg_remark');?></td>
                    <td width="25%"><? //$goodsForm->show('jg_created_by');?></td>
                </tr>
            </table>
            <div class="line"></div>

            <br />

            <div style="margin-left: 28px;"><label class="formtitle" for="g_cast"><font
                        size="+1">加工单明细</font></label>
                <table width="100%" id="tableDnD">
                    <tbody id="tbody">
                    <tr class="formtitle nodrop nodrag">
                        <td width="15%">编号</td>
                        <td width="10%">客号</td>
                        <td width="28%">描述</td>
                        <td width="8%">数量</td>
                        <!--                        <td width="10%">已出数量</td>-->
                        <? /*<td width="20%">Product Remark</td>*/ ?>
                        <td width="9%">价格</td>
                        <td width="9%">合计</td>
                        <td width="9%">照片</td>
                        <? /*<td width="6%"><div class="del_all"><input name='' type='button' value='Del All' /></div></td>*/?>
                        <td width="1%">&nbsp;</td>
                        <td width="1%">&nbsp;</td>
                        <!--			<td width="3%">&nbsp;</td>-->
                        <td width="5%">&nbsp;</td>
                    </tr>
                    <?
                    for($i = 0; $i < $jg_item_num; $i++){
                        if (is_file('../sys/'.$pic_path_com . $jg_item_rtn[$i]['photos']) == true) {

                            //圖片壓縮
                            //$jg_item_rtn[$i]['photos']是原來的， $small_photo是縮小後的
                            //$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
                            $small_photo = 's_' . $jg_item_rtn[$i]['photos'];
                            //縮小的圖片不存在才進行縮小操作
                            if (!is_file('../sys/'.$pic_path_small . $small_photo) == true) {
                                makethumb('../sys/'.$pic_path_com . $jg_item_rtn[$i]['photos'], $pic_path_small . $small_photo, 's');
                            }

                            $photo_string = '<a href="../sys/'.$pic_path_com . $jg_item_rtn[$i]['photos'].'" target="_blank" title="'.$jg_item_rtn[$i]['photos'].'"><img src="../sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
                        }else{
                            $photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60' original='../images/nopic.gif'>";
                        }
                        ?>
                        <tr class="repeat" valign="top">
                            <td><? $goodsForm->show('q_pid'.$i);?></td>
                            <td align="center"><div id="scode"><?=$jg_item_rtn[$i]['scode']?></div></td>
                            <td><? $goodsForm->show('q_p_description'.$i);?></td>
                            <td><? $goodsForm->show('q_p_quantity'.$i);?></td>
                            <!--                            <td align="center">--><?//=@$jg_item_rtn[$i]['delivery_num']?><!--</td>-->
                            <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
                            <td><? $goodsForm->show('q_p_price'.$i); $goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
                            <td id="sub"><?=sprintf("%01.3f", $jg_item_rtn[$i]['price']*(($jg_item_rtn[$i]['quantity'] == 0 || $jg_item_rtn[$i]['quantity'] == '')?1:$jg_item_rtn[$i]['quantity']))?></td>
                            <td><?=$photo_string?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <!--			<td>&nbsp;</td>-->
                            <td><div id="del<?=$i?>" onclick="delItem(this)"><img src="../../sys/images/del-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Delete" /></div></td>
                        </tr>
                        <tr class="repeat" valign="top">
                            <td colspan="9"><input checked="checked" style="display:none" type="checkbox" value="test" class="test" id="q_p_task<?=$i?>" name="q_p_task<?=$i?>[]"> <? $goodsForm->show('q_p_task'.$i);?></td>
                        </tr>
                        <tr class="repeat" valign="top">
                            <td colspan="9">&nbsp;</td>
                        </tr>
                    <?
                    }
                    ?>
                    </tbody>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <!--                        <td>&nbsp;</td>-->
                        <td align="center">总计:</td>
                        <td id="sub">
                            <div id="total">0</div>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </table>

            </div>
            <br />
            <br />

            <div class="line"></div>
            <?
            $goodsForm->show('submitbtn');
            ?></fieldset>
        <?
        $goodsForm->end();

    }
    ?>

    <script>
        $(function(){
            //load頁面就更新total值
            UpdateTotal();
            //$(".template").hide()
            //selectSupplier("jg_")
            //***先加載當前屏幕的img，好像沒有效果。。。
            /*
             $("img").lazyload({
             placeholder : "/sys/images/grey.gif",
             effect      : "fadeIn"
             });
             */
            //***
            //table tr层表单可拖动
            //$("#tableDnD").tableDnD();

            //$("#jg_cid").not('.special').selectbox();
            //$("#jg_attention").not('.special').selectbox();

            selectFtyCustomer("jg_");
        })

        function delItem(obj){
            if(confirm("确定要删除吗?（没保存之前，刷新页面可恢复删除的内容）")){
                $(obj).parents(".repeat").next().next().remove();
                $(obj).parents(".repeat").next().remove();
                $(obj).parents(".repeat").remove();
            }
        }
    </script>

<?
}
?>