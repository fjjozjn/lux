<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();
//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}
if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    $goodsForm = new My_Forms();
    $formItems = array(

        //'i_vid' => array('title' => 'Invoice NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20/*, 'restrict' => 'judgexid'*/, 'required' => 1),
        'i_cid' => array('title' => 'Customer', 'type' => 'select', 'options' => get_customer(), 'required' => 1),
        //'i_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
        'i_attention' => array('title' => 'Attention', 'type' => 'select', 'options' => '', 'required' => 1),
        'i_reference' => array('title' => 'Customer PO No.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
        'i_reference_num' => array('title' => 'Ref NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
        'i_tel' => array('title' => 'Tel', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
        'i_fax' => array('title' => 'Fax', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
        'i_currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1),
        'i_unit' => array('title' => 'Unit', 'type' => 'select', 'options' => get_unit(), 'value' => 'PCS'),
        //'i_printed_by' => array('title' => 'Printed By', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
        'i_packing_num' => array('title' => 'Packing List NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20,
            'readonly' => 'readonly'),
        'i_discount' => array('title' => 'Discount', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
        'i_delivery_date' => array('title' => 'Delivery Date', 'type' => 'text', 'restrict' => 'date'),
        'i_waybill_no' => array('title' => 'Waybill NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30),

        'i_remark' => array('title' => 'Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"'),
        'i_address' => array('title' => 'Address', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2),

        //默认的remarks value，数据中有个换行
        'i_remarks' => array('title' => 'Remarks', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => 'HS CODE: 7117.1900
MADE IN CHINA'),

        'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
        'q_p_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
        'q_p_quantity' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
        //remark不加了，好像也沒什麼用
        //'i_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
        'q_p_description' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled'),
        'q_p_photos' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
        'q_p_ccode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
        'q_p_scode' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),

        'q_pid1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20),
        'q_p_price1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
        'q_p_quantity1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'disabled' => 'disabled'),
        //remark不加了，好像也沒什麼用
        //'i_p_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'disabled' => 'disabled'),
        'q_p_description1' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'disabled' => 'disabled'),
        'q_p_photos1' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
        'q_p_ccode1' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),
        'q_p_scode1' => array('type' => 'hidden', 'value' => '', 'disabled' => 'disabled'),

        'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
    );
    $goodsForm->init($formItems);


    if(!$myerror->getAny() && $goodsForm->check()){
        //$myerror->info($_POST);

        $i = 1;//第一个post的是form的标识串，所以会跳过
        $i_product = array();

        $i_vid = autoGenerationID();
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
        $i_delivery_date = $_POST['i_delivery_date']==''?'0000-00-00':$_POST['i_delivery_date'];
        $i_waybill_no = $_POST['i_waybill_no'];
        $i_remark = $_POST['i_remark'];
        //这个变量没用的，写着方便计算下面的数（14），不然忘了这个以后麻烦
        $i_address = $_POST['i_address'];

        //这个是在最后提交的哟
        $i_remarks = $_POST['i_remarks'];

        //remarks 在最後，所以這裡是14，vid现在是自动生成所以这里变13了，多了i_delivery_date和i_waybill_no所以是15
        foreach( $_POST as $v){
            if( $i <= 15){
                $i++;
            }else{
                $i_product[] = $v;
            }
        }
        $i_mark_date = dateMore();
        //暫時不知道這個打印日期是怎麼回事。。。
        $i_printed_date = dateMore();
        $i_printed_by = $_SESSION["logininfo"]["aName"];

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

            //判断是否输入的vid已存在，因为存在的话由于数据库限制，就会新增失败
            $judge = $mysql->q('select vid from invoice where vid = ?', $i_vid);
            if(!$judge){
                $result = $mysql->q('insert into invoice (vid, send_to, attention, tel, fax, reference, remark, mark_date, reference_num, packing_num, currency, unit, printed_by, printed_date, total, total_qty, ex_total, discount, remarks, cid, delivery_date, waybill_no, istatus) values ('.moreQm(23).')', $i_vid, $i_send_to, $i_attention, $i_tel, $i_fax, $i_reference, $i_remark, $i_mark_date, $i_reference_num, $i_packing_num, $i_currency, $i_unit, $i_printed_by, $i_printed_date, $total, $total_qty, $ex_total, $i_discount, $i_remarks, $i_cid, $i_delivery_date, $i_waybill_no,
                    '(I)');
                if($result){
                    $customer_total_amount = 0;
                    for($k = 0; $k < $i_product_num; $k++){
                        $rtn = $mysql->q('insert into invoice_item (pid, vid, price, quantity, description, photos, ccode, scode) values ('.moreQm(8).')', $i_pid[$k], $i_vid, $i_p_price[$k], $i_p_quantity[$k], $i_p_description[$k], $i_p_photos[$k], $i_p_ccode[$k], $i_p_scode[$k]);
                        //20120916 更新 product total_amount
                        //20130407 去掉
                        //$mysql->q('update product set total_nums = total_nums + ?, total_amount = total_amount + ? where pid = ?', $i_p_quantity[$k], currencyTo($i_p_price[$k], $i_currency, 'RMB') * $i_p_quantity[$k], $i_pid[$k]);
                        //$customer_total_amount += currencyTo($i_p_price[$k], $i_currency, 'USD') * $i_p_quantity[$k];
                    }
                    //20120917 更新 customer total_amount
                    //20130407 去除了每次更新invoice 的 total_amount 改为每日执行一次脚本来更新这个值，因为这样的每次操作都改很麻烦，而且已经出错了，和脚本运行的值对不起来。而如果在top_customer里面每次计算又太慢
                    //$mysql->q('update customer set total_amount = total_amount + ? where cid = ?', $customer_total_amount, $i_cid);

                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], $ip_real
                        , ACTION_LOG_SYS_ADD_INVOICE, $_SESSION["logininfo"]["aName"]." <i>add invoice</i> '".$i_vid."' in sys", ACTION_LOG_SYS_ADD_INVOICE_S, "", "", 0);

                    $myerror->ok('新增 Invoice 成功! <font color="#FF0000">Continue to complete shipment</font>', 'com-shipment&pi_no='.$i_vid.'&page=1');
                    //$myerror->ok('新增 Invoice 成功! ', 'com-searchinvoice&page=1');
                }else{
                    $myerror->error('新增 Invoice 失败', 'BACK');
                }
            }else{
                $myerror->error('输入的 Invoice NO.已存在，新增 Invoice 失败', 'BACK');
            }
        }else{
            $myerror->error('不允许在一个单中添加相同的 Product Item ，新增 Invoice 失败', 'BACK');
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
        <h1 class="green">INVOICE<em>* item must be filled in</em></h1>
        <fieldset>
            <legend class='legend'>Add Invoice</legend>

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

            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><div class="set"><label class="formtitle">Invoice NO.</label><br />(autogeneration)</div></td>
                    <td width="25%"><? $goodsForm->show('i_cid');?></td>
                    <td width="25%"><? $goodsForm->show('i_attention');?></td>
                    <td width="25%"><? $goodsForm->show('i_tel');?></td>
                </tr>
                <tr>
                    <td width="25%" valign="top"><? $goodsForm->show('i_fax');?></td>
                    <td width="25%" colspan="2"><? $goodsForm->show('i_address');?></td>
                    <td width="25%" valign="top"><? $goodsForm->show('i_reference_num');?></td>
                </tr>
                <tr>
                    <td width="25%"><? $goodsForm->show('i_packing_num');?></td>
                    <td width="25%"><? $goodsForm->show('i_currency');?></td>
                    <td width="25%"><? $goodsForm->show('i_unit');?></td>
                    <td width="25%"><? $goodsForm->show('i_reference');?></td>
                    <? /*<td width="25%"><? $goodsForm->show('i_printed_by');?></td>*/?>
                </tr>
                <tr>
                    <td width="25%" colspan="2"><? $goodsForm->show('i_remark');?></td>
                    <td width="25%"><? $goodsForm->show('i_discount');?></td>
                    <td width="25%"><? $goodsForm->show('i_delivery_date');?></td>
                </tr>
                <tr>
                    <td width="25%"><? $goodsForm->show('i_waybill_no');?></td>
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
                        <td width="8%" align="center">Photo</td>
                        <? /*<td width="6%"><div class="del_all"><input name='' type='button' value='Del All' /></div></td>*/?>
                        <td width="3%">&nbsp;</td>
                        <td width="3%">&nbsp;</td>
<!--                        <td width="3%">&nbsp;</td>-->
                        <td width="5%">&nbsp;</td>
                    </tr>
                    <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                        <td class="dragHandle"></td>
                        <td><? $goodsForm->show('q_pid');?></td>
                        <td><? $goodsForm->show('q_p_description');?></td>
                        <td><? $goodsForm->show('q_p_quantity');?></td>
                        <? /*<td><? $goodsForm->show('i_p_remark');?></td>*/ ?>
                        <td><? $goodsForm->show('q_p_price');$goodsForm->show('q_p_photos'); $goodsForm->show('q_p_ccode'); $goodsForm->show('q_p_scode');?></td>
                        <td id="sub">0</td>
                        <td>&nbsp;</td>
                        <td><div id="his"></div></td>
<!--                        <td><div id="clear"></div></td>-->
                        <td><div id="del"></div></td>
                    </tr>
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                        <td class="dragHandle"></td>
                        <td><? $goodsForm->show('q_pid1');?></td>
                        <td><? $goodsForm->show('q_p_description1');?></td>
                        <td><? $goodsForm->show('q_p_quantity1');?></td>
                        <? /*<td><? $goodsForm->show('pi_p_remark');?></td>*/ ?>
                        <td><? $goodsForm->show('q_p_price1');$goodsForm->show('q_p_photos1'); $goodsForm->show('q_p_ccode1'); $goodsForm->show('q_p_scode1');?></td>
                        <td id="sub">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><div id="his1"></div></td>
<!--                        <td><div id="clear1"></div></td>-->
                        <? /*<td><div class="del"><input name='' type='button' value='Del' /></div></td>*/?>
                        <td><div id="del1"></div></td>
                    </tr>

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
            <table>
                <tr>
                    <td width="50%">
                        <?
                        $goodsForm->show('i_remarks');
                        ?>
                    </td>
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

    <script>
        $(function(){
            $(".template").hide()
            selectCustomer("i_")
            searchProduct(17, '')
            searchProduct(17, '1')
            //table tr层表单可拖动
            $("#tableDnD").tableDnD({dragHandle: ".dragHandle"});
            currency("i_");
        })
    </script>

<?
}
?>