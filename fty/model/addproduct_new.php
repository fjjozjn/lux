<?php

die('20170318');

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

// form加这个参数才能上传文件，否则获取不到 $_FILE
$goodsForm = new My_Forms(array('multipart'=>true));
$formItems = array(

    'p_pid' => array('title' => 'Product ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'judgexid', 'required' => 1, 'info' => '请不要使用 # & ’ " 等特殊符号'),
    'p_cat_num' => array('title' => 'CAT.NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => '000-000'),
    'p_theme' => array('title' => 'Theme', 'type' => 'select', 'options' => getTheme()),
    'p_type' => array('title' => 'Type', 'type' => 'select', 'options' => get_bom_lb(3), 'required' => 1, 'info' => '请先选择Type，Product ID会自动填上'),

    'p_description' => array('title' => 'Description', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:400px"'),
    'p_description_chi' => array('title' => '中文描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:400px"'),

    'p_sid' => array('title' => 'Supplier', 'type' => 'select', 'options' => getSupplier(), 'required' => 1),
    'p_scode' => array('title' => 'Supplier Product code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1),
    'p_ccode' => array('title' => 'Customer code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
    'p_cost_rmb' => array('title' => 'Cost RMB', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'required' => 1, 'value' => 0),
    'p_cost_remark' => array('title' => 'Cost remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:200px"'),
    //'p_exclusive_to' => array('title' => 'Exclusive to', 'type' => 'select', 'options' => get_customer(), 'info' => '此项选择后才可以填写Suggested Price'),
    //20130715 加 select sample_order_no 改原来的 sample_order_no 为 sample_order_no_remark
    //'p_sample_order_no' => array('title' => 'Sample Order No.', 'type' => 'select', 'options' => get_sample_order_no()),
    'p_sample_order_no_remark' => array('title' => 'Sample Order No. Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'info' => '臨時的sample order,將會刪除'),
    'p_show_in_catalog' => array('title' => 'Show in catalog', 'type' => 'checkbox', 'options' => array('show')),
    'p_suggested_price' => array('title' => 'Suggested Price', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'readonly' => 'readonly'),

    //暂时设定不可以自由修改时间，如果修改了product的信息，就自动为其插入现在的日期
    //'p_in_date' => array('title' => 'In Date', 'type' => 'text', 'restrict' => 'date'),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);
$goodsForm->begin();


if(!$myerror->getAny() && $goodsForm->check()){

    //20130716
    //未上传图片是被允许的，一但上传则有格式和大小的限制
    //这里是用 $_FILES['photo'] photo是 input 的属性，而不是用 $_FILES['file']['type']
    if( ((@$_FILES['photo']['type'] == 'image/jpg' || @$_FILES['photo']['type'] == 'image/jpeg' || @$_FILES['photo']['type'] == 'image/gif' || @$_FILES['photo']['type'] == 'image/pjpeg' || @$_FILES['photo']['type'] == 'image/png') && (@$_FILES['photo']['size'] / 1024) <= 500) || @$_FILES['photo']['name'] == '' ){

        //4 是没有文件上传
        if (@$_FILES['photo']['error'] > 0 && @$_FILES['photo']['error'] != 4){
            $myerror->error('Upload photo Error ! Return Code: '.@$_FILES['photo']['error'], 'BACK');
        }else{

            //其他的add用了js来去除头尾空格和tab，但addproduct不用js检查，因为输出js图片session保存不了的关系，所以在这里处理数据。本来是在Mysql.php中处理的，但是连换行也去掉了，textarea就不大方便了。so。。。 mod 3.5
            $pid = trim($_POST['p_pid']);

            $target = '';
            $add_tip = '';
            if(@$_FILES['photo']['name'] != ''){
                // 转为大写字母(pid + 时间 + 原图片的后缀名)
                // 图片的格式一般都是3位字母吧，没有其他的吧。。。？
                $temp = substr(@$_FILES['photo']['name'], -4);
                $target = strtoupper($pid.'_'.date('YmdHis').$temp);
                //上传图片
                move_uploaded_file(@$_FILES['photo']['tmp_name'], iconv('UTF-8', 'GBK', $pic_path_com.$target));
                if(file_exists(iconv('UTF-8','GBK', $pic_path_com.$target))) {
                    $small_photo = 's_' . $target;
                    //小图片不存在才进行缩小操作
                    if (!is_file($pic_path_small . $small_photo) == true) {
                        makethumb($pic_path_com . $target, $pic_path_small . $small_photo, 's');
                    }

                    //20150119 show_in_catalog 则用l图生成水印图
                    $large_photo = 'l_' . $target;
                    $large_photo_water = 'l_water_' . $target;
                    if($_POST['p_show_in_catalog'] && $_POST['p_theme'] == '7'){
                        makethumb($pic_path_com.$target, $pic_path_small.$large_photo, 'l');
                        createWordsWatermark($pic_path_small.$large_photo, 'Lux Design Ltd', '12', '200,170,200', '5', ROOT_DIR.'font/arial.ttf', '0', $pic_path_watermark.$large_photo_water);
                    }

                    $add_tip = 'Upload photo '.$target.' success! ';
                }else{
                    $add_tip = 'Upload photo '.$target.' <i>failure</i>! ';
                }
            }

            //20130321 add theme
            $theme = $_POST['p_theme'];
            //20130226 add product type
            $type = $_POST['p_type'];
            $in_date = dateMore();
            $created_by = $_SESSION["logininfo"]["aName"];
            $cat_num = $_POST['p_cat_num'];
            $description = $_POST['p_description'];
            $description_chi = $_POST['p_description_chi'];
            $sid = $_POST['p_sid'];
            $scode = $_POST['p_scode'];
            $ccode = $_POST['p_ccode'];
            $cost_rmb = $_POST['p_cost_rmb'];
            $cost_remark = $_POST['p_cost_remark'];
            //$exclusive_to = $_POST['p_exclusive_to'];
            $photos = $target;
            //$sample_order_no = $_POST['p_sample_order_no'];
            $sample_order_no_remark = $_POST['p_sample_order_no_remark'];
            $show_in_catalog = isset($_POST['p_show_in_catalog'])?1:0;
            $suggested_price = $_POST['p_suggested_price'];

            //判断是否输入的pid已存在，因为存在的话由于数据库限制，就会新增失败
            $judge = $mysql->q('select pid from product where pid = ?', $pid);
            if(!$judge){
                $result = $mysql->q('insert into product (pid, theme, type, in_date, created_by, cat_num, description, description_chi, sid, scode, ccode, cost_rmb, cost_remark, photos, sample_order_no_remark, show_in_catalog, suggested_price) values ('.moreQm(17).')', $pid, $theme, $type, $in_date, $created_by, $cat_num, $description, $description_chi, $sid, $scode, $ccode, $cost_rmb, $cost_remark, /*$exclusive_to,*/ $photos, /*$sample_order_no,*/ $sample_order_no_remark, $show_in_catalog, $suggested_price);
                if($result){

                    changeProductSetting($pid);

                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], $ip_real
                        , ACTION_LOG_SYS_ADD_PRODUCT, $_SESSION["logininfo"]["aName"]." <i>add product</i> '".$pid."' in sys", ACTION_LOG_SYS_ADD_PRODUCT_S, "", "", 0);

                    $myerror->ok($add_tip.'Add Product ('.$pid.') success!', 'com-searchproduct_new&page=1');
                }else{
                    $myerror->error($add_tip.'System error, add product ('.$pid.') failure!', 'BACK');
                }
            }else{
                $myerror->error('Product ID ('.$pid.') already exist, add product failure!', 'BACK');
            }
        }
    }else{
        $myerror->error('上传图片 失败! 请选择JPG、PNG或者GIF格式的图片上传! 且图片大小不要超过 500 KB!', 'BACK');
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
<h1 class="green">PRODUCT<em>* indicates required fields</em></h1>

    <fieldset>
        <legend class='legend'>Add Product 1.Upload image</legend>
        <?
        /*if(isset($_SESSION['upload_photo_add']) && $_SESSION['upload_photo_add'] != ''){
            $arr = getimagesize($pic_path_com . $_SESSION['upload_photo_add']);
            $pic_width = $arr[0];
            $pic_height = $arr[1];
            //20121022 获取文件大小，在图片旁显示文件的属性
            $a = filesize($pic_path_com . $_SESSION['upload_photo_add']);
            $image_size = getimgsize(100, 60, $pic_width, $pic_height);
            echo 'image info：('.$_SESSION['upload_photo_add'].'&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com . $_SESSION['upload_photo_add'].'" class="tooltip2" target="_blank" title="'.$_SESSION['upload_photo_add'].'"><img src="/sys/'.$pic_path_com . $_SESSION['upload_photo_add'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
            echo "</div><div><b><a class='button' href='?act=com-delete_photo'>DELETE</a></b></div>";
        }else{*/
            //20130716
            echo "<div style='margin-left:28px;'><ul><li><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><input type='file' name='photo' id='photo' /></div>";
            /*
            <div class="line"></div>

            <img src="<?= (isset($Pic) ? $Pic : '../images/nopic.gif')?>" id="p_pkPic" border="0" height="70">
            <input name="pkPic" type="hidden" id="pkPic" value="<?=$Pic?>">
            <div class="line"></div>
            <iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="500" id="titleimg_up" frameborder="0"></iframe>
            */
        //}
        ?>
    </fieldset>
    <fieldset>
        <legend class='legend'>2.Fill the form</legend>
        <table width="100%" id="table">
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('p_type');?></td>
                <td width="25%"><? $goodsForm->show('p_pid');?></td>
                <td width="25%"><? $goodsForm->show('p_cat_num');?></td>
                <td width="25%"></td>
            </tr>
            <tr valign="top">
                <td width="25%"><? //$goodsForm->show('p_sample_order_no');?></td>
                <td width="25%"><? $goodsForm->show('p_sample_order_no_remark');?></td>
                <td width="25%"></td>
                <td width="25%"></td>
            </tr>
            <tr>
                <td colspan="2"><? $goodsForm->show('p_description');?></td>
                <td colspan="2"><? $goodsForm->show('p_description_chi');?></td>
            </tr>
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('p_sid');?></td>
                <td width="25%"><? $goodsForm->show('p_scode');?></td>
                <td width="25%"><? $goodsForm->show('p_cost_rmb');?></td>
                <td width="25%"><? $goodsForm->show('p_cost_remark');?></td>
            </tr>
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('p_ccode');?></td>
                <td width="25%"><? //$goodsForm->show('p_exclusive_to');?></td>
                <td width="25%"><? $goodsForm->show('p_theme');?></td>
                <td width="25%"><? $goodsForm->show('p_show_in_catalog');?></td>
            </tr>
            <tr>
                <td width="25%"><? $goodsForm->show('p_suggested_price');?></td>
                <td width="25%">&nbsp;</td>
                <td width="25%">&nbsp;</td>
                <td width="25%">&nbsp;</td>
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
        //$(function(){
        //有了這個photo的session沒法用，先去掉
        //judgeXid('p_pid')
        //})
        $(function(){
            $("#p_type").selectbox({onChange: changeBomType});
            $("#p_exclusive_to").selectbox({onChange: changeExclusiveTo});
        });

        function changeBomType(obj){
            var my_val = obj.selectedVal;
            var p_pid = $("#p_pid");
            var p_scode = $("#p_scode");

            var qs = 'ajax=1&act=ajax-get_sys_product_setting&value='+my_val;
            $.ajax({
                type: "GET",
                url: "index.php",
                data: qs,
                cache: false,
                dataType: "html",
                error: function(){
                    alert('系统错误，查询product id失败');
                },
                success: function(data){
                    if (data.indexOf('no-') >= 0) {
                        alert('系统错误！查询product id失败');
                        obj.val('');//id已存在则清空输入框
                        $('#p_type').val('');
                    } else {
                        var pid = my_val.charAt(my_val.length - 2) + data;
                        p_pid.val(pid);
                        p_scode.val(pid);
                    }
                }
            })
        }
    </script>