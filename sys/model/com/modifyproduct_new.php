<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(isset($_GET['delid']) && $_GET['delid'] != ''){

    $p = $mysql->qone('select photos, product_file from product where pid = ?', $_GET['delid']);
    @unlink($pic_path_com.$p['photos']);
    @unlink($pic_path_small.'s_'.$p['photos']);
    @unlink($pic_path_small.'m_'.$p['photos']);
    @unlink($pic_path_small.'l_'.$p['photos']);
    @unlink($pic_path_watermark.'l_water_'.$p['photos']);
    @unlink($product_file_path_com.$p['product_file']);

    $rtn = $mysql->q('delete from product where pid = ?', $_GET['delid']);
    if($rtn){

        //add action log
        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
            , $_SESSION['logininfo']['aID'], $ip_real
            , ACTION_LOG_SYS_DEL_PRODUCT, $_SESSION["logininfo"]["aName"]." <i>delete product</i> '".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_PRODUCT_S, "", "", 0);

        $myerror->ok('删除产品资料 ('.$_GET['delid'].') 成功!', 'com-searchproduct_new&page=1');
    }else{
        $myerror->error('删除产品资料 ('.$_GET['delid'].') 失败', 'com-searchproduct_new&page=1');
    }
}else{
    if(isset($_GET['modid']) && $_GET['modid'] != ''){
        $mod_result = $mysql->qone('SELECT * FROM product WHERE pid = ?', $_GET['modid']);

        //20130725 查询pid在warehouse里的情况
        $pid_warehouse_info = get_pid_warehouse_info($_GET['modid']);
    }elseif(isset($_GET['copypid']) && $_GET['copypid'] != ''){
        $mod_result = $mysql->qone('SELECT * FROM product WHERE pid = ?', $_GET['copypid']);
        $mod_result['pid'] = '';
        $mod_result['scode'] = '';
    }

    //原来是用ajax，但是会被浏览器阻止，所以链接要写在标签里
    $bom_link = '';
    $rtn = $mysql->qone('select id from bom where g_id = ?', @$_GET['modid']);
    if($rtn){
        $bom_link = "javascript:void(window.open('?act=formdetail&gid=".$_GET['modid']."', 'lux', 'height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no'))";
    }else{
        $bom_link = "javascript:alert('none')";
    }

    // form加这个参数才能上传文件，否则获取不到 $_FILE
    $goodsForm = new My_Forms(array('multipart'=>true));
    $formItems = array(

        'p_pid' => array('title' => 'Product ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['pid'])?$mod_result['pid']:'', 'info' => '请不要使用 # & ‘ “ 等特殊符号', 'readonly' => true),
        'p_cat_num' => array('title' => 'CAT.NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['cat_num'])?$mod_result['cat_num']:'000-000'),
        'p_theme' => array('title' => 'Theme', 'type' => 'select', 'options' => getTheme(), 'value' => isset($mod_result['theme'])?$mod_result['theme']:''),
        //'p_type' => array('title' => 'Type', 'type' => 'select', 'options' => get_bom_lb(3), 'value' => isset($mod_result['type'])?$mod_result['type']:''),

        'p_description' => array('title' => 'Description', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => isset($mod_result['description'])?$mod_result['description']:'', 'addon' => 'style="width:400px"'),
        'p_description_chi' => array('title' => '中文描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => isset($mod_result['description_chi'])?$mod_result['description_chi']:'', 'addon' => 'style="width:400px"'),

        'p_sid' => array('title' => 'Supplier', 'type' => 'select', 'options' => getSupplier(), 'value' => isset($mod_result['sid'])?$mod_result['sid']:'', 'required' => 1),
        'p_scode' => array('title' => 'Supplier Product code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['scode'])?$mod_result['scode']:'', 'required' => 1),
        'p_ccode' => array('title' => 'Customer code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['ccode'])?$mod_result['ccode']:''),
        'p_cost_rmb' => array('title' => 'Cost RMB', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['cost_rmb'])?$mod_result['cost_rmb']:'', 'required' => 1),
        'p_cost_remark' => array('title' => 'Cost remark', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($mod_result['cost_remark'])?$mod_result['cost_remark']:'', 'addon' => 'style="width:200px"'),
        'p_exclusive_to' => array('title' => 'Exclusive to', 'type' => 'select', 'options' => get_customer(), 'value' => isset($mod_result['exclusive_to'])?$mod_result['exclusive_to']:''),
        //暂时设定不可以自由修改时间，如果修改了product的信息，就自动为其插入现在的日期
        //現在定為可修改，新增product就指定當天的
        //20130619 去掉自己改时间，in_date 字段为新增时间，或者修改时间
        //'p_in_date' => array('title' => 'In Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['in_date'])?$mod_result['in_date']:''),
        //20130715 加 select sample_order_no 改原来的 sample_order_no 为 sample_order_no_remark
        //20170826 加required
        'p_sample_order_no' => array('title' => 'Sample Order No.', 'type' => 'select', 'options' => get_sample_order_no(), 'value' => isset($mod_result['sample_order_no'])?$mod_result['sample_order_no']:'', 'required' => 1),
        'p_sample_order_no_remark' => array('title' => 'Sample Order No. Remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['sample_order_no_remark'])?$mod_result['sample_order_no_remark']:'', 'info' => '臨時的sample order,將會刪除'),
        'p_show_in_catalog' => array('title' => 'Show in catalog', 'type' => 'checkbox', 'options' => array('show'), 'value' => (isset($mod_result['show_in_catalog']) && $mod_result['show_in_catalog'] == 1)?'show':''),
        'p_suggested_price' => array('title' => 'Suggested Price', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'readonly' => (isset($mod_result['suggested_price']) && $mod_result['suggested_price'] != '')?false:true, 'value' => isset($mod_result['suggested_price'])
        ?$mod_result['suggested_price']:''),


        'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
    );

    if(isset($_GET['copypid']) && $_GET['copypid'] != ''){
		$formItems['p_pid'] = array('title' => 'Product ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['pid'])?$mod_result['pid']:'', 'info' => '请不要使用 # & ‘ “ 等特殊符号');
        $formItems['p_type'] = array('title' => 'Type', 'type' => 'select', 'options' => get_bom_lb(3), 'value' => '', 'required' => 1, 'info' => '请先选择Type，Product ID会自动填上');
    }

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
                $pid = $_POST['p_pid'];

                $target = '';
                $add_tip = '';
                if(@$_FILES['photo']['name'] == ''){
                    $target = $mod_result['photos'];

                    $add_tip = '';
                }else{
                    // 转为大写字母(pid + 时间 + 原图片的后缀名)
                    // 图片的格式一般都是3位字母吧，没有其他的吧。。。？
                    $temp = substr(@$_FILES['photo']['name'], -4);
                    $target = strtoupper($pid.'_'.date('YmdHis').$temp);
                    //上传图片
                    move_uploaded_file(@$_FILES['photo']['tmp_name'], iconv('UTF-8', 'GBK', $pic_path_com.$target));

                    $add_tip = 'Upload photo '.$target.' success! ';
                }

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
                }else{
                    $add_tip = 'Upload photo '.$target.' <i>failure</i>! ';
                }

                //20130321 add theme
                $theme = $_POST['p_theme'];
                //20130226 add product type
                $type = isset($_POST['p_type']) ? $_POST['p_type'] : '';
                //20130619 cindy 让我改为显示修改的时间
                //20130717 加mod_date 字段
                //$in_date = dateMore();//$_POST['p_in_date'];
                $mod_date = dateMore();
                $mod_by = $_SESSION["logininfo"]["aName"];
                $cat_num = $_POST['p_cat_num'];
                $description = $_POST['p_description'];
                $description_chi = $_POST['p_description_chi'];
                $sid = $_POST['p_sid'];
                $scode= $_POST['p_scode'];
                $ccode = $_POST['p_ccode'];
                $cost_rmb = $_POST['p_cost_rmb'];
                $cost_remark = $_POST['p_cost_remark'];
                $exclusive_to = $_POST['p_exclusive_to'];
                $sample_order_no = $_POST['p_sample_order_no'];
                $sample_order_no_remark = $_POST['p_sample_order_no_remark'];
                $show_in_catalog = isset($_POST['p_show_in_catalog'])?1:0;
                $suggested_price = $_POST['p_suggested_price'];

                if(isset($_GET['copypid'])){
                    //判断是否输入的pid已存在，因为存在的话由于数据库限制，就会新增失败
                    $judge = $mysql->q('select pid from product where pid = ?', $pid);
                    if(!$judge){

                        // 1、copy之前没图片，copy后可以上传（能获取到$_FILE）；
                        // 2、copy之前没图片，copy后也可以不上传（不能获取到$_FILE）；
                        // 3、也可以保持原有的图片不变（不能获取到$_FILE），但是要重新复制一张原来的图片，并重新命名。
                        if(isset($_POST['sign'])){
                            $photos = $target;
                        }else{
                            $temp = substr($mod_result['photos'], -4);
                            $photos = strtoupper($pid.'_'.date('YmdHis').$temp);
                            //复制图片
                            copy($pic_path_com.$mod_result['photos'], $pic_path_com.$photos);
                        }

                        //20130726 在数据库中如果 in_date 等于 mod_date 就是copy的
                        $result = $mysql->q('insert into product (pid, theme, type, in_date, mod_date, created_by, mod_by, cat_num, description, description_chi, sid, scode, ccode, cost_rmb, cost_remark, exclusive_to, photos, sample_order_no, sample_order_no_remark, show_in_catalog, suggested_price) values ('.moreQm(21).')', $pid, $theme, $type, $mod_date, $mod_date, $_SESSION["logininfo"]["aName"], $mod_by, $cat_num, $description, $description_chi, $sid, $scode, $ccode, $cost_rmb, $cost_remark, $exclusive_to, $photos, $sample_order_no, $sample_order_no_remark, $show_in_catalog, $suggested_price);
                        //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                        if($result !== false){

                            changeProductSetting($pid);

                            //add action log
                            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                                , $_SESSION['logininfo']['aID'], $ip_real
                                , ACTION_LOG_SYS_COPY_PRODUCT, $_SESSION["logininfo"]["aName"]." <i>copy product</i> '".$_GET['copypid']."' to '".$pid."' in sys", ACTION_LOG_SYS_COPY_PRODUCT_S, "", "", 0);

                            $myerror->ok($add_tip.'Add product ('.$pid.') success!', 'com-searchproduct_new&page=1');
                        }else{
                            $myerror->error($add_tip.'System error, add product ('.$pid.') failure!', 'BACK');
                        }
                    }else{
                        $myerror->error($add_tip.'Product ID ('.$pid.') already exsit, please fill in another one. copy product failure!', 'BACK');
                    }
                }elseif(isset($_GET['modid'])){

                    // 1、修改的时候可以上传（能获取到$_FILE）；
                    // 2、也可以不上传（不能获取到$_FILE）；
                    // 3、也可以保持原有的图片不变（不能获取到$_FILE）。
                    if(isset($_POST['sign'])){
                        $photos = $target;
                    }else{
                        $photos = $mod_result['photos'];
                    }

                    $result = $mysql->q('update product set pid = ?, theme = ?, mod_date = ?, mod_by = ?, cat_num = ?, description = ?, description_chi = ?, sid = ?, scode = ?, ccode = ?, cost_rmb = ?, cost_remark = ?, exclusive_to = ?, photos = ?, sample_order_no = ?, sample_order_no_remark = ?, show_in_catalog = ?, suggested_price = ? where pid = ?', $pid, $theme, /*$type,*/ $mod_date, $mod_by, $cat_num, $description, $description_chi, $sid, $scode, $ccode, $cost_rmb, $cost_remark, $exclusive_to, $photos, $sample_order_no, $sample_order_no_remark, $show_in_catalog, $suggested_price, $mod_result['pid']);
                    //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                    if($result !== false){

                        //add action log
                        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                            , $_SESSION['logininfo']['aID'], $ip_real
                            , ACTION_LOG_SYS_MOD_PRODUCT, $_SESSION["logininfo"]["aName"]." <i>modify product</i> '".$pid."' in sys", ACTION_LOG_SYS_MOD_PRODUCT_S, "", "", 0);

                        $myerror->ok($add_tip.'Modify product ('.$pid.') success!', 'com-searchproduct_new&page=1');
                    }else{
                        $myerror->error($add_tip.'System error, modify product ('.$pid.') failure!', 'BACK');
                    }
                }
            }
        }else{
            $myerror->error('上传图片 失败! 请选择JPG、PNG或者GIF格式的图片上传! 且图片大小不要超过 500 KB!', 'BACK');
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
    <h1 class="green">PRODUCT<em>* indicates required fields</em></h1>
    <fieldset>
        <legend class='legend'>Action</legend>
        <?php
        if(isset($_GET['modid'])){
            ?>
            <div style="margin-left:28px;"><a class="button" href="?act=com-modifyproduct_new&copypid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Copy</b></a><!--<a id="<?/*=$_GET['modid']*/?>" class="button" href="#" onclick="bomConfirm(this)"><b>Bom</b></a>--><a class="button" href="<?=$bom_link?>"><b>Bom</b></a><a class="button" href="#" onclick="window.open ('model/com/proforma_pid_history.php?pid='+$('#p_pid').val(),'lux','height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no')"><b>History(PI)</b></a></div>
        <?php
        }
        ?>
    </fieldset>
    <fieldset>
        <legend class='legend'><? if(isset($_GET['copypid'])){ echo 'Copy Product';}else{ echo 'Modify Product';}?></legend>
        <fieldset>
            <legend class='legend'>1.Upload image</legend>
            <?
            /*if(isset($_SESSION['upload_photo_mod']) && $_SESSION['upload_photo_mod'] != ''){
                if (is_file($pic_path_com . $_SESSION['upload_photo_mod']) == true) {
                    $arr = getimagesize($pic_path_com . $_SESSION['upload_photo_mod']);
                    $pic_width = $arr[0];
                    $pic_height = $arr[1];
                    //20121022 获取文件大小，在图片旁显示文件的属性
                    $a = filesize($pic_path_com . $_SESSION['upload_photo_mod']);
                    $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                    echo 'image info：('.$_SESSION['upload_photo_mod'].'&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com . $_SESSION['upload_photo_mod'].'" class="tooltip2" target="_blank" title="'.$_SESSION['upload_photo_mod'].'"><img src="/sys/'.$pic_path_com . $_SESSION['upload_photo_mod'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
                }else{
                    echo '<div style="margin-left:28px;"><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/><br />';
                }
                if(isset($_GET['modid'])){//copy的时候，删了图片，就连原来的product的图片也空了，所以这里去掉了
                    echo "</div><div><b><a class='button' href='?act=com-delete_photo&pid=".$mod_result['pid']."'>DELETE</a></b></div>";
                }
            }else{
                echo "<div style='margin-left:28px;'><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><a class='button' href='?act=com-upload_photo_mod'>UPLOAD</a></div>";*/

                /*
                <div class="line"></div>

                <img src="<?= (isset($Pic) ? $Pic : '../images/nopic.gif')?>" id="p_pkPic" border="0" height="70">
                <input name="pkPic" type="hidden" id="pkPic" value="<?=$Pic?>">
                <div class="line"></div>
                <iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="500" id="titleimg_up" frameborder="0"></iframe>
                */
            //}

            //20130716
            if(is_file($pic_path_com . $mod_result['photos']) == true){
                $arr = getimagesize($pic_path_com . $mod_result['photos']);
                $pic_width = $arr[0];
                $pic_height = $arr[1];
                //20121022 获取文件大小，在图片旁显示文件的属性
                $a = filesize($pic_path_com . $mod_result['photos']);
                $image_size = getimgsize(100, 60, $pic_width, $pic_height);

                if(isset($_GET['modid'])){
                    echo 'image info：('.$mod_result['photos'].'&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com . $mod_result['photos'].'" class="tooltip2" target="_blank" title="'.$mod_result['photos'].'"><img src="/sys/'.$pic_path_com . $mod_result['photos'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul></div><div><b><a class="button" href="?act=com-delete_photo_new&pid='.$mod_result['pid'].'&photo='.$mod_result['photos'].'">DELETE</a></b></div>';
                }elseif(isset($_GET['copypid'])){//copy的时候，删了图片，就连原来的product的图片也空了，所以这里去掉了
                    echo 'image info：(将会复制此图片并重新命名&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><img src="/sys/'.$pic_path_com . $mod_result['photos'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></div>';
                }
            }else{
                echo '<div style="margin-left:28px;"><img src="../images/nopic.gif" border="0" width="80" height="60"><br /><input type="file" name="photo" id="photo" /><input type="hidden" name="sign" value="1"></div>';
            }

            ?>
        </fieldset>
        <fieldset>
            <legend class='legend'>2.Fill the form</legend>
            <table width="100%" id="table">
                <tr valign="top">
                    <?php
                    if(isset($_GET['copypid'])){
                        ?>
                        <td width="25%"><? $goodsForm->show('p_type') ?></td>
                    <?php
                    }else{
                        ?>
                        <td width="25%"><div class="set"><label class="formtitle">Type<br /><?=$mod_result['type']?></label></div></td>
                    <?php
                    }
                    ?>
                    <td width="25%"><? $goodsForm->show('p_pid');?></td>
                    <td width="25%"><? $goodsForm->show('p_cat_num');?></td>
                    <td width="25%"></td>
                </tr>
                <tr valign="top">
                    <td width="25%"><? $goodsForm->show('p_sample_order_no');?></td>
                    <td width="25%"><? $goodsForm->show('p_sample_order_no_remark');?></td>
                    <td width="25%"><div class="set"><label class="formtitle">Warehouse</label><br />
                            <?
                                if(!empty($pid_warehouse_info)){
                                    foreach($pid_warehouse_info as $v){
                                        echo $v."<br />";
                                    }
                                }
                            ?></div></td>
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
                <tr>
                    <td width="25%"><? $goodsForm->show('p_ccode');?></td>
                    <td width="25%"><? $goodsForm->show('p_exclusive_to');?></td>
                    <td width="25%"><? $goodsForm->show('p_theme');?></td>
                    <td width="25%"><? $goodsForm->show('p_show_in_catalog');?></td>
                </tr>
                <tr>
                    <td width="25%"><? $goodsForm->show('p_suggested_price');//$goodsForm->show('p_in_date');?></td>
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
    </fieldset>
    <fieldset>
        <legend class='legend'>Action</legend>
        <?php
        if(isset($_GET['modid'])){
        ?>
            <div style="margin-left:28px;"><a class="button" href="?act=com-modifyproduct_new&copypid=<?=$_GET['modid']?>" onclick="return pdfConfirm()"><b>Copy</b></a><!--a id="<?=$_GET['modid']?>" class="button" href="#" onclick="bomConfirm(this)"><b>Bom</b></a--><a class="button" href="<?=$bom_link?>"><b>Bom</b></a><a class="button" href="#" onclick="window.open ('model/com/proforma_pid_history.php?pid='+$('#p_pid').val(),'lux','height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no')"><b>History(PI)</b></a></div>
        <?php
        }
        ?>
    </fieldset>
    <?
    $goodsForm->end();

}
?>
<script>
    $(function(){
        $("#p_type").selectbox({onChange: changeBomType});
        $("#p_exclusive_to").selectbox({onChange: changeExclusiveTo});
    })

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
                    //var pid = my_val.charAt(my_val.length - 2) + data;
                    //170429改为截取括号内的内容
                    var s = my_val.match(/\([^\)]+\)/g)[0];
                    var pid = s.substring(1,s.length-1) + data;
                    p_pid.val(pid);
                    p_scode.val(pid);
                }
            }
        })
    }
</script>