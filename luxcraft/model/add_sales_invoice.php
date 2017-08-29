<?php
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//unset($_SESSION['sales_item']);
//fb($_SESSION);

//给这个页面加上 All 这个特殊选项
$warehouse_info = get_warehouse_info('', 'Shop', true);
$product_type = get_product_type();

//20140212 去掉all
//array_unshift($warehouse_info, array('All', 'All'));
array_unshift($product_type, array('All', 'All'));

//20140405
//20150323
//$user_wh = $mysql->qone('select wh_name from tw_admin where AdminID = ?', $_SESSION['luxcraftlogininfo']['aID']);

$goodsForm = new My_Forms();
$formItems = array(
    'payment_method' => array('title' => 'Payment Method', 'type' => 'select', 'options' => get_payment_method(), 'required' => 1, 'value' => 'Cash'),
    'wh_id' => array('type' => 'select', 'options' => $warehouse_info, 'nostar' => true),
    'product_type' => array('type' => 'select', 'options' => $product_type, 'nostar' => true),
    'shop' => array('title' => 'Shop', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'readonly' => 'readonly', 'required' => 1, 'value' => $_SESSION["luxcraftlogininfo"]["wh_name"]),
    'remark' => array('title' => 'Remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2),
    'invoice_date' => array('title' => 'Date', 'type' => 'text', 'restrict' => 'date', 'required' => 1,
        'value' => date('Y-m-d')),
    'vip_phone' => array('title' => 'VIP Phone', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'info' =>
        '关联Membership'),
    'discount' => array('type' => 'text', 'restrict' => 'number', 'addon' => 'style="width:50px" onblur="discount_blur()"'),
    'clear' => array('type' => 'button', 'value' => 'Clear', 'addon' => 'onclick="return clear_all()"'),
    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit ', 'addon' => 'onclick="return confirm_price()"'),
);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
    //有提交数据的情况
//fb($_POST);
    $sales_vid = luxcraft_autoGenerationID();

    $i = 1;//第一个post的是form的标识串，所以会跳过
    $salse_invoice_item = array();

    //item前面表单的个数
    //20131129 多了个payment method，所以是2了
    //20140109 多了个日期，所以现在是3了
    //20140226 加了shop和remark，所以现在是5了
    //20160507 加了个vip_phone，所以现在是6了
    $pre_num_index = 6;
    //每行item的表单数
    $item_input_num = 4;
    //item后面表单的个数
    //最后有个submit
    //20140110 加了discount所以现在是2了
    $after_num_index = 2;

    foreach( $_POST as $v){
        if( $i <= $pre_num_index){
            $i++;
        }else{
            $salse_invoice_item[] = $v;
        }
    }

//fb($salse_invoice_item);

    $salse_invoice_item_num = intval((count($salse_invoice_item)-$after_num_index)/$item_input_num);

    $si_index = 0;
    $si_qty = array();
    $si_price = array();
    $si_pid = array();
    $si_photo = array();

    //20140110 加total = price总和 - discount
    $price_total = 0;

    for($j = 0; $j < $salse_invoice_item_num; $j++){
        $temp_qty = $salse_invoice_item[$si_index];
        $si_qty[] = $salse_invoice_item[$si_index++];

        $temp_price = $salse_invoice_item[$si_index];
        $si_price[] = $salse_invoice_item[$si_index++];

        $price_total += $temp_qty*$temp_price;

        $si_pid[] = $salse_invoice_item[$si_index++];
        $si_photo[] = $salse_invoice_item[$si_index++];
    }

    /*fb($salse_invoice_item_num);
    fb($si_qty);
    fb($si_price);
    fb($si_pid);
    fb($si_photo);
    die();*/

    $payment_method = $_POST['payment_method'];
    $staff = $_SESSION['luxcraftlogininfo']['aName'];
    $in_date = date('Y-m-d H:i:s');

    $rs = $mysql->q('insert into sales_invoice (sales_vid, wh_name, remark, payment_method, discount, total, invoice_date,vip_phone,created_by, mod_by, in_date, mod_date) values ('.moreQm(12).')', $sales_vid, $_POST['shop'], $_POST['remark'], $payment_method, my_formatMoney($_POST['discount']), my_formatMoney($price_total - $_POST['discount']), $_POST['invoice_date'],$_POST['vip_phone'], $staff, $staff, $in_date, $in_date);
    if($rs){
        for($k = 0; $k < $salse_invoice_item_num; $k++){
            $rtn = $mysql->q('insert into sales_invoice_item (sales_vid, pid, price, qty, discount, photo) values ('.moreQm(6).')', $sales_vid, $si_pid[$k], $si_price[$k], $si_qty[$k], my_formatMoney($_POST['discount']*(($si_price[$k]*$si_qty[$k])/$price_total)), $si_photo[$k]);
        }

        //add action log
        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['luxcraftlogininfo']['aID'], $ip_real
            , ACTION_LOG_LUXCRAFT_ADD_SALES_INVOICE, $_SESSION["luxcraftlogininfo"]["aName"]." <i>add sales invoice</i> '".$sales_vid."' in luxcraft", ACTION_LOG_LUXCRAFT_ADD_SALES_INVOICE_S, "", "", 0);

        $myerror->ok('Add Sales Invoice success!', 'add_sales_invoice&wh_id='.$_SESSION["luxcraftlogininfo"]["wh_name"]);
    }else{
        $myerror->error('Add Sales Invoice failure', 'BACK');
    }

    //提交后都清掉session
    unset($_SESSION['sales_item']);

}else{
    $rtn_warehouse = array();
    $rs = $mysql->q('select wh_name from warehouse where type = 2');
    if($rs){
        $rtn_warehouse = $mysql->fetch();
    }

    //左边部分的数据
    $mysql->q('select p.pid, p.photos, p.suggested_price, w.wh_name, w.qty from warehouse_item_unique w left join product p on w.pid = p.pid where p.exclusive_to = ? and w.qty > 0 and p.type like ? and w.wh_name = ? and p.pid like ? order by p.pid', 'LUX', (isset($_GET['product_type']) && $_GET['product_type'] != '' && $_GET['product_type'] != 'All')?('%'.$_GET['product_type'].'%'):'%', (isset($_SESSION["luxcraftlogininfo"]["wh_name"]) && $_SESSION["luxcraftlogininfo"]["wh_name"] != '')?($_SESSION["luxcraftlogininfo"]["wh_name"]):'', (isset($_GET['search']) && $_GET['search'] != '')?('%'.$_GET['search'].'%'):'%');

    //右边部分的数据
    $item_data_session = '';
    if(isset($_SESSION['sales_item']) && is_array($_SESSION['sales_item']) && !empty($_SESSION['sales_item'])){
        foreach($_SESSION['sales_item'] as $v){
            $item_data_session .= '<tr align="center">';
            $item_data_session .= '<td><a href="'.str_replace("luxsmall/s_","lux/",$v['img']).'" target="_blank"><img width="80" height="60" src="'.$v['img'].'" /></a></td>';
            $item_data_session .= '<td id="pid">'.$v['pid'].'</td>';
            $item_data_session .= '<td><div class="formfield"><input id="qty'.$v['index'].'" class="textinit textinitb readonly" type="text" style="width:50px" tabindex="" strlen="1,20" required="1" maxlength="20" onblur="changeQty(this)" name="qty'.$v['index'].'" value="'.$v['qty'].'" readonly="readonly"></div></td>';
            $item_data_session .= '<td><div class="formfield"><input id="price'.$v['index'].'" class="textinit textinitb" type="text" style="width:50px" tabindex="" strlen="1,20" restrict="number" required="1" maxlength="20" name="price'.$v['index'].'" value="'.$v['price'].'" onblur="changePrice(this)"></div></td>';
            $item_data_session .= '<td><div class="formfield" id="amount'.$v['index'].'">'.$v['qty']*$v['price'].'</div></td>';
            $item_data_session .= '<td><input type="hidden" id="pid'.$v['index'].'" name="pid'.$v['index'].'" value="'.$v['pid'].'" /><input type="hidden" id="photo'.$v['index'].'" name="photo'.$v['index'].'" value="'.$v['img'].'" /><img id="'.$v['index'].'" title="minus" style="opacity: 0.5;" onclick="delItemFromInvoice(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../images/minus.png"></td>';
            $item_data_session .= '</tr>';
        }
    }
}


if($myerror->getError()){
    require_once(ROOT_DIR.'model/inside_error.php');
}elseif($myerror->getOk()){

    //echo "<script>window.open('/luxcraft/model/pdf_sales_invoice.php?pdf=1&sales_vid=".$sales_vid."','','height=500,width=611,scrollbars=yes,status=yes');</script>";

    require_once(ROOT_DIR.'model/inside_ok.php');
}else{
    if($myerror->getWarn()){
        require_once(ROOT_DIR.'model/inside_warn.php');
    }
    ?>

    <script language="javascript" type="text/javascript" src="/ui/jquery.scrollLoading.js"></script>

    <div style="float: left;">
        Shop : <? $goodsForm->show('wh_id');?>
    </div>
    <div style="float: left;">
        Product Type : <? $goodsForm->show('product_type');?>
    </div>

    Search Product : <br /><input type="text" id="search_item" /><input type="button" value="Search" onclick="search_item(this)" />
<br />
<br />
    <? //下面同行显示的两个div，左边的要小于等于右边的高度，否则foot的公司信息会附在右边下方，而不是最底下 ?>
    <div style="float:left; width: 50%; height:600px; overflow-y:auto;" id="my_scroll_loading">

    <?php
        $rtn = $mysql->fetch();
        if(!empty($rtn)){
            foreach($rtn as $v){
                $img = '';
                $invoice_img = '';
                if(is_file(ROOT_DIR.'sys/upload/luxsmall/s_'.$v['photos']) == true){
                    $arr = getimagesize(ROOT_DIR.'sys/upload/luxsmall/s_'.$v['photos']);
                    $pic_width = $arr[0];
                    $pic_height = $arr[1];
                    $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                    //$img = '<a href="/sys/upload/lux/'.$v['photos'].'" target="_blank" title="'.$v['photos'].'"><img src="/sys/upload/luxsmall/s_'.$v['photos'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
                    $img = '<a title="添加到INVOICE" href="javascript:void(0);" onclick="addItemToInvoice(this)"><img class="my_s_l"  data-url="/sys/upload/luxsmall/s_'.$v['photos'].'" src="/images/pixel.gif" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
                    $invoice_img = '/sys/upload/luxsmall/s_'.$v['photos'];
                }else{
                    if (is_file(ROOT_DIR.'sys/upload/lux/'.$v['photos']) == true) {
                        $arr = getimagesize(ROOT_DIR.'sys/upload/lux/'.$v['photos']);
                        $pic_width = $arr[0];
                        $pic_height = $arr[1];
                        $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                        //顯示的圖片在網站目錄下
                        //去掉弹出的图，也很卡
                        //echo '<ul><li><a href="/sys/upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]].'" class="tooltip2" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/sys/upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
                        //$img = '<a href="/sys/upload/lux/'.$v['photos'].'" target="_blank" title="'.$v['photos'].'"><img src="/sys/upload/lux/'.$v['photos'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
                        $img = '<a title="添加到INVOICE" href="javascript:void(0);" onclick="addItemToInvoice(this)"><img class="my_s_l"  data-url="/sys/upload/lux/'.$v['photos'].'" src="/images/pixel.gif" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
                        $invoice_img = '/sys/upload/lux/'.$v['photos'];
                    }else{
                        //$img = '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/>';
                        $img = '<a title="添加到INVOICE" href="javascript:void(0);" onclick="addItemToInvoice(this)"><img class="my_s_l" data-url="/images/nopic.gif" src="/images/pixel.gif" border="0" align="middle" width="80" height="60"/></a>';
                        $invoice_img = '/images/nopic.gif';
                    }
                }
                //fb($image_size);
                echo '<div style="float: left; width: 100px; height: 120px; text-align: center;">';
                //echo $img.'<br />'.$v['wh_name'].'<br /><span style="display:none">'.$invoice_img.'|'.$v['pid'].'|'.$v['cost_rmb'].'</span><a title="添加到INVOICE" href="javascript:void(0);" onclick="addItemToInvoice(this)">'.$v['pid'].'</a>';
                echo $img.'<br /><span style="display:none">'.$invoice_img.'|'.$v['pid'].'|'.$v['suggested_price'].'</span>Qty : '.$v['qty'].'<br />'.$v['pid'];
                echo '</div>';
            }
        }
        ?>
    </div>

    <?php
    $goodsForm->begin();
    ?>
    <div style="float:left; width: 50%; height: 600px; overflow-y:auto;">
        <div style="text-align: center; font-size: 20px;">INVOICE</div>
        <br />

        <table>
            <tr valign="top">
                <td><? $goodsForm->show('shop');?></td>
                <td><? $goodsForm->show('remark');?></td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td><? $goodsForm->show('payment_method');?></td>
                <td><? $goodsForm->show('invoice_date');?></td>
                <td><? $goodsForm->show('vip_phone');?></td>
            </tr>
        </table>

        <div>
            <br />
            <br />
            <table width="100%" style="padding-left:20px;">
                <tr>
                    <td width="20%" align="center">Pic</td>
                    <td width="20%" align="center">Item</td>
                    <td width="17%" align="center">Qty</td>
                    <td width="17%" align="center">Unit Price</td>
                    <td width="18%" align="center">Amount</td>
                    <td width="10%">Minus</td>
                </tr>
                <tbody id="invoice"><?=$item_data_session?></tbody>
            </table>
            <div class="line"></div>

            <table width="100%" style="padding-left: 20px;" id="discount_table">
                <tr>
                    <td width="20%">&nbsp;</td>
                    <td width="20%">&nbsp;</td>
                    <td width="17%">&nbsp;</td>
                    <td width="17%" align="right">Discount : </td>
                    <td width="18%" align="center"><? $goodsForm->show('discount');?></td>
                    <td width="10%">&nbsp;</td>
                </tr>
                <tr>
                    <td width="20%">&nbsp;</td>
                    <td width="20%">&nbsp;</td>
                    <td width="17%">&nbsp;</td>
                    <td width="17%" align="right">Total : </td>
                    <td width="18%" align="left" style="padding-left: 20px;" id="price_total"></td>
                    <td width="10%">&nbsp;</td>
                </tr>
            </table>
            <?
            $goodsForm->show('clear');
            $goodsForm->show('submitbtn');
            $goodsForm->end();
            ?>
        </div>
    </div>

    <script>

        $(function(){

            //图片当滚动条滚动到时才加载
            $(".my_s_l").scrollLoading({
                container: $("#my_scroll_loading"),
                callback: function() {
                    //this.style.border = "3px solid #a0b3d6";
                }
            });

            $('#wh_id').selectbox({onChange: select_shop});
            $('#product_type').selectbox({onChange: select_type});

            UpdateSITotal();
        });

        //debug js in chrome console
        //console.log(2);

        //*** for select warehouse and type and search item
        function getQueryStringRegExp(name)
        {
            var reg = new RegExp("(^|\\?|&)"+ name +"=([^&]*)(\\s|&|$)", "i");
            if (reg.test(location.href)) return unescape(RegExp.$2.replace(/\+/g, " ")); return "";
        }

        function select_shop(){
            var param = $("#wh_id").val();
            if(param != ''){
                var sign = 'wh_id';
                var url = window.location.href;
                if(url.indexOf('&'+sign) >= 0){
                    var value = getQueryStringRegExp(sign);
                    url = url.replace('='+value, '='+param);
                }else{
                    url = (url + '&' + sign + '=' + param);
                }
                window.location.href = url;
            }
        }

        function select_type(){
            var param = $("#product_type").val();
            var sign = 'product_type';
            var url = window.location.href;
            if(url.indexOf('&'+sign) >= 0){
                var value = getQueryStringRegExp(sign);
                url = url.replace('='+value, '='+param);
            }else{
                url = (url + '&' + sign + '=' + param);
            }
            window.location.href = url;
        }

        function search_item(obj){
            var item_text = $(obj).prev().val();
            if(item_text != ''){
                var url = window.location.href;
                if(url.indexOf('&search') >= 0){
                    var search_value = getQueryStringRegExp('search');
                    url = url.replace('&search='+search_value, '&search='+item_text);
                }else{
                    url = (url + '&search=' + item_text);
                }
                window.location.href = url;
            }
        }

        $('#search_item').val('<?php echo isset($_GET['search'])?$_GET['search']:''; ?>');
        //***

        function is_show_btn(){
            if($('#invoice').html() == ''){
                $('#discount_table').hide();
                $('#divsubmitbtn').hide();
                $('#divclear').hide();
            }else{
                $('#discount_table').show();
                $('#divsubmitbtn').show();
                $('#divclear').show();
            }
        }

        is_show_btn();

        function change_color(param1, param2){
            if(param1 == '') param1 = 'All';
            if(param2 == '') param2 = 'All';

            $("[id='shop']").each(function(){
                if( $(this).html() == param1 ){
                    $(this).attr('style', 'color:#EC008C');
                }
            });

            $("[id='type']").each(function(){
                if( $(this).html() == param2 ){
                    $(this).attr('style', 'color:#EC008C');
                }
            })
        }
        change_color('<?=(isset($_GET['shop'])?$_GET['shop']:'')?>', '<?=(isset($_GET['type'])?$_GET['type']:'')?>');

        //！！！！！！ 所有ajax操作为同步，会有页面停顿的现象 ！！！！！！

        function addItemToInvoice(obj){
            //! br也算一个next。。。
            var info = $(obj).next().next().html();

            var info_array = info.split("|");

            var sign = true;
            $('#invoice #pid').each(function(){
                if($(this).html() == info_array[1]){
                    //alert(info_array[1]+' 已存在，请不要重复添加！');
                    //20140213 直接qty加1
                    var qty = $(this).next().children().children();
                    qty.val(parseInt(qty.val())+1);
                    sign = false;

                    var qty_attr_id = qty.attr('id');
                    var item_qty_index = qty_attr_id.substr(3);
                    $.ajax({
                        type: "GET",
                        async: false,
                        url: "index.php",
                        data: {ajax:"1", act:"addItemQtyNum", index:item_qty_index}
                        /*success: function(data){
                            if(data.indexOf('!no-') >= 0){

                            }
                        }*/
                    })

                    $(this).next().next().next().children().html($(this).next().children().children().val()*$(this)
                        .next().next()
                        .children().children().val());
                    UpdateSITotal();
                }
            });

            //20131227 添加的pid在已有的里面不存在才添加
            if(sign){
                //用精确到毫秒的时间戳来区别表单项
                var myDate = new Date();
                var item_index = myDate.valueOf();

                $.ajax({
                    type: "GET",
                    async: false,
                    url: "index.php",
                    data: {ajax:"1", act:"addItemToInvoice", index:item_index, img:info_array[0], pid:info_array[1], price:info_array[2]},
                    success: function(data){
                        if(data.indexOf('yes') >= 0){

                            var pic_url = info_array[0].replace('luxsmall/s_', 'lux/');
                            var all_html = '<tr align="center">';
                            all_html += '<td><a href="'+pic_url+'" target="_blank"><img width="80" height="60" src="'+info_array[0]+'" /></a></td>';
                            all_html += '<td id="pid">'+info_array[1]+'</td>';
                            all_html +=	'<td><div class="formfield"><input id="qty'+item_index+'" class="textinit textinitb readonly" type="text" style="width:50px" tabindex="" strlen="1,20" required="1" maxlength="20" onblur="changeQty(this)" name="qty'+item_index+'" value="1" readonly="readonly"></div></td>';
                            all_html +=	'<td><div class="formfield"><input id="price'+item_index+'" class="textinit textinitb" type="text" style="width:50px" tabindex="" strlen="1,20" restrict="number" required="1" maxlength="20" name="price'+item_index+'" onblur="changePrice(this)" value="'+info_array[2]+'"></div></td>';
                            all_html +=	'<td><div class="formfield" id="amount'+item_index+'">'+info_array[2]+'</div></td>';
                            //按钮
                            all_html += '<td><input type="hidden" id="pid'+item_index+'" name="pid'+item_index+'" value="'+info_array[1]+'" /><input type="hidden" id="photo'+item_index+'" name="photo'+item_index+'" value="'+info_array[0]+'" /><img id="'+item_index+'" title="minus" style="opacity: 0.5;" onclick="delItemFromInvoice(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../images/minus.png"></td>';
                            all_html += '</tr>';

                            $('#invoice').append(all_html);

                            is_show_btn();

                            UpdateSITotal();
                        }else{
                            alert('Add item error');
                        }
                    }
                })
            }
        }

        function delItemFromInvoice(obj){
            var item_index = $(obj).attr('id');
            $.ajax({
                type: "GET",
                async:false,
                url: "index.php",
                data: {ajax:"1", act:"delItemFromInvoice", index:item_index},
                success: function(data){
                    if(data.indexOf('clear') >= 0){
                        $(obj).parent().parent().remove();
                        is_show_btn();
                    }else{
                        var qty = $(obj).parent().prev().prev().prev().children().children();
                        qty.val(parseInt(qty.val())-1);
                        $(obj).parent().prev().children().html(qty.val()*qty.parent().parent().next().children()
                            .children().val());
                        //alert('delete item error');
                    }
                }
            })

            UpdateSITotal();
        }

        function changeQty(obj){
            var qty = $(obj).val();
            var price = $(obj).parent().parent().next().children().children().val();
            $(obj).parent().parent().next().next().children().html(price*qty);
            var pid = $(obj).parent().parent().prev().html();
            var item_index = $(obj).attr('id').substr(3);
            $.ajax({
                type: "GET",
                async:false,
                url: "index.php",
                data: {ajax:"1", act:"changeQty", index:item_index, pid:pid, qty:qty},
                success: function(data){
                    if(data.indexOf('!no-') >= 0){
                        if(data == '!no-3'){
                            alert('Invoice qty must less than warehouse qty !');
                        }
                    }
                }
            })
        }

        function changePrice(obj){
            var price = $(obj).val();
            var qty = $(obj).parent().parent().prev().children().children().val();
            var pid = $(obj).parent().parent().prev().prev().html();
            $(obj).parent().parent().next().children().html(price*qty);
            var item_index = $(obj).attr('id').substr(5);
            $.ajax({
                type: "GET",
                async:false,
                url: "index.php",
                data: {ajax:"1", act:"changePrice", index:item_index, pid:pid, price:price},
                success: function(data){
                    if(data.indexOf('!no-') >= 0){
                        alert('Invoice item change price failure !');
                    }
                }
            })

            UpdateSITotal();
        }

        //更新price总和
        function UpdateSITotal(){
            var total_price = 0;
            var discount_value = $("#discount").val();
            var price_obj = $("#invoice").find("[id^='amount']");
            price_obj.each(
                function(){
                    var vTempValue = $(this).html();
                    vTempValue = vTempValue.replace(/,/g,"");
                    if(vTempValue == "")
                    {
                        vTempValue = 0;
                    }
                    //这了用accAdd就不行。。。
                    total_price += parseFloat(vTempValue);//這個parseFloat，好像會出好多小數點後面的0
                }
            );//遍历结束
            $("#price_total").html(total_price - discount_value);
        }

        function discount_blur(){
            UpdateSITotal();
        }

        //注意js关键字confirm不能用了做函数名，切记
        function confirm_price(){

            if(confirm("The total is $"+$('#price_total').html()+", confirm?")){

                //20150325 获取将要生成的ID，并设置session，打开pdf页面，此时还没有生成订单，session存在则等2秒，再刷新页面，订单就应该生成好了
                $.ajax({
                    type: "GET",
                    async:false,
                    url: "index.php",
                    data: {ajax:"1", act:"getLastSalesInvoiceID"},
                    success: function(data){
                        if(data.indexOf('!no-') >= 0){
                            alert('Error!');
                        }else{
                            window.open('/luxcraft/model/pdf_sales_invoice.php?pdf=1&sales_vid='+data,'','height=500,width=611,scrollbars=yes,status=yes');
                        }
                    }
                });

                return true;
            }else{
                return false;
            }
        }

        function clear_all(){
            if(confirm('Do you want to clear?')){
                $.ajax({
                    type: "GET",
                    async:false,
                    url: "index.php",
                    data: {ajax:"1", act:"clearAllItem"},
                    success: function(data){
                        if(data.indexOf('!no-') >= 0){
                            alert('Clear failure !');
                        }else{
                            $('#invoice').html('');
                            is_show_btn();
                        }
                    }
                })
            }
        }
    </script>

<?
}
?>