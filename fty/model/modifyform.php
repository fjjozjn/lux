<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。
2011-02-22		增加overseas选项，与idcard联动，可以令海外人士不必填写身份证。修改address为textarea，及相关版式。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//20150308 检查用户是否设置了所属工厂
if($_SESSION['ftylogininfo']['aFtyName'] == ''){
    ?>
    <script>alert('帐号未设置工厂，不能修改bom！');</script>
<?php
}

judgeFtyPerm( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'').(isset($_GET['changeid'])?$_GET['changeid']:'').(isset($_GET['copyid'])?$_GET['copyid']:'') );

/*if(isset($_GET['delimg']) && $_SESSION['fty_upload_photo'] != ''){
	unlink($_SESSION['fty_upload_photo']);
	$_SESSION['fty_upload_photo'] = '';
	$myerror->ok('刪除上传的图片 成功!', 'BACK');
}else*/if(isset($_GET['delid']) && $_GET['delid'] != ''){
    $p = $mysql->qone('select photo from bom where id = ?', $_GET['delid']);
    @unlink($pic_path_fty_bom.$p['photo']);

    $mysql->q('delete from bom_material where bom_id = ?', $_GET['delid']);
    $mysql->q('delete from bom_task where bom_id = ?', $_GET['delid']);
    $rs = $mysql->q('DELETE FROM bom WHERE id = ?', $_GET['delid']);
    if($rs){
        $myerror->ok('刪除产品资料表资料 成功!', 'searchform&page=1');
    }else{
        $myerror->error('由于系统原因，刪除产品资料表资料 失败', 'searchform&page=1');
    }
}elseif(isset($_GET['changeid']) && $_GET['changeid'] != ''){
    $rtn = $mysql->qone('select p_status from bom where id = ?', $_GET['changeid']);
    $str = '';
    if($rtn['p_status'] == '未完成'){
        $rs = $mysql->q('update bom set p_status = ?, check_by = ? where id = ?', '已完成', $_SESSION["ftylogininfo"]["aName"], $_GET['changeid']);
        $str = '已完成';
    }else{
        $rs = $mysql->q('update bom set p_status = ?, check_by = ? where id = ?', '未完成', '',  $_GET['changeid']);
        $str = '未完成';
    }
    if($rs){
        $myerror->ok('更改 bom 状态为 '.$str.'!', 'searchform&page=1');
    }else{
        $myerror->error('更改 bom 状态失败!', 'searchform&page=1');
    }
}else {
    if(isset($_GET['modid']) && $_GET['modid'] != ''){
        $mod_result = $mysql->qone('SELECT * FROM bom WHERE id = ?', $_GET['modid']);
        $title_g_id = $mod_result['g_id'];
        $rs_m = $mysql->q('SELECT * FROM bom_material WHERE bom_id = ? order by id', $_GET['modid']);
        $m_item_num = 0;
        if($rs_m){
            $mod_result_m = $mysql->fetch();
            //20130401 查找出损耗率
            for($i = 0; $i < count($mod_result_m); $i++){
                $temp = $mysql->qone('select m_loss from fty_material where m_id = ?', $mod_result_m[$i]['m_id']);
                $mod_result_m[$i]['m_loss'] = $temp['m_loss'];
            }

            $m_item_num = count($mod_result_m);
        }

        $rs_t = $mysql->q('SELECT * FROM bom_task WHERE bom_id = ? order by id', $_GET['modid']);
        $t_item_num = 0;
        if($rs_t){
            $mod_result_t = $mysql->fetch();
            $t_item_num = count($mod_result_t);
        }

        $process_array = explode('|', $mod_result['g_process']);
        $electroplate_array = explode('|', $mod_result['electroplate']);
        $electroplate_thick_array = explode('|', $mod_result['electroplate_thick']);
        $other_array = explode('|', $mod_result['other']);
        if( !isset($_SESSION['fty_upload_photo']) || $_SESSION['fty_upload_photo'] == ''){
            $_SESSION['fty_upload_photo'] = $mod_result['photo'];
        }
    }elseif(isset($_GET['copyid']) && $_GET['copyid'] != ''){
        $mod_result = $mysql->qone('SELECT * FROM bom WHERE id = ?', $_GET['copyid']);
        $title_g_id = $mod_result['g_id'];
        $mod_result['g_id'] = '';
        $rs_m = $mysql->q('SELECT * FROM bom_material WHERE bom_id = ? order by id', $_GET['copyid']);
        $m_item_num = 0;
        if($rs_m){
            $mod_result_m = $mysql->fetch();
            //20130401 查找出损耗率
            for($i = 0; $i < count($mod_result_m); $i++){
                $temp = $mysql->qone('select m_loss from fty_material where m_id = ?', $mod_result_m[$i]['m_id']);
                $mod_result_m[$i]['m_loss'] = $temp['m_loss'];
            }

            $m_item_num = count($mod_result_m);
        }

        $rs_t = $mysql->q('SELECT * FROM bom_task WHERE bom_id = ? order by id', $_GET['copyid']);
        $t_item_num = 0;
        if($rs_t){
            $mod_result_t = $mysql->fetch();
            $t_item_num = count($mod_result_t);
        }

        $process_array = explode('|', $mod_result['g_process']);
        $electroplate_array = explode('|', $mod_result['electroplate']);
        $electroplate_thick_array = explode('|', $mod_result['electroplate_thick']);
        $other_array = explode('|', $mod_result['other']);
        if( !isset($_SESSION['fty_upload_photo']) || $_SESSION['fty_upload_photo'] == ''){
            $_SESSION['fty_upload_photo'] = $mod_result['photo'];
        }
    } elseif (isset($_GET['pid']) && $_GET['pid'] != '') {
        $product = $mysql->qone('select * from product where pid = ?', $_GET['pid']);
        $mod_result['g_id'] = $_GET['pid'];
        //复制图片
        copy(ROOT_DIR.'sys/'.$pic_path_com.$product['photos'], ROOT_DIR.'fty/'.$pic_path_fty_bom.$product['photos']);
        $mod_result['photo'] = $product['photos'];
        $mod_result['p_other'] = $product['cost_rmb'];
    }

    $image_path = '';

    $process = get_bom_bmcl(1);
    $electroplate = get_bom_dd(1);
    $electroplate_thick = get_bom_ddhd(1);
    $other = get_bom_qt(1);

    // form加这个参数才能上传文件，否则获取不到 $_FILE
    $goodsForm = new My_Forms(array('multipart'=>true));

    $formItems = array(
        'g_id' => array('title' => '产品编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1/*, 'addon' => 'style="width:100px"' */, 'value' => isset($mod_result['g_id'])?$mod_result['g_id']:'', isset($_GET['copyid'])?'':'readonly' => 'readonly'),
        'g_type' => array('title' => '类别', 'type' => 'select', 'options' => get_bom_lb(1), 'value' => isset($mod_result['g_type'])?$mod_result['g_type']:'', 'addon' => 'style="width:200px"', 'required' => 1),
        'g_material' => array('title' => '底材用料', 'type' => 'select', 'options' => get_bom_dcyl(1), 'value' => isset($mod_result['g_material'])?$mod_result['g_material']:'', 'addon' => 'style="width:200px"'),
        'g_size' => array('title' => '尺码', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['g_size'])?$mod_result['g_size']:'', 'info' => '请注明单位'),
        //填写的数字允许小数吗？管他是整数还是小数，全部用字符串处理，我勒个去
        'g_gem_num' => array('title' => '成品总石数', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：粒', 'value' => isset($mod_result['g_gem_num'])?$mod_result['g_gem_num']:''),
        'g_cast' => array('title' => '铸件数量', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：件', 'value' => isset($mod_result['g_cast'])?$mod_result['g_cast']:''),
        'g_plating' => array('title' => '电镀描述', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['g_plating'])?$mod_result['g_plating']:''),
        'g_weight' => array('title' => '重量', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：克', 'value' => isset($mod_result['g_weight'])?$mod_result['g_weight']:''),
        'g_ccode' => array('title' => '客号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['g_ccode'])?$mod_result['g_ccode']:''),
        'g_sample_order_no' => array('title' => '板单编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['g_sample_order_no'])?$mod_result['g_sample_order_no']:''),

        //'m_id' => array('title' => '物料编号 <font size="-1">（输入框中填写<b> 数量/重量的值 </b>，只有选中后，输入框才能使用。点击小图标显示详情）</font>', 'type' => 'checkbox', 'options' => $m_id, 'fatherclass' => 'lux', 'addinput' => 1, 'modid' => isset($mod_result['id'])?$mod_result['id']:'', 'mytype' => 'm', 'value' => isset($m_id_array)?$m_id_array:''),

        //'t_id' => array('title' => '工序号 <font size="-1">（输入框中填写<b> 工时 </b>，只有选中后，输入框才能使用。点击小图标显示详情）</font>', 'type' => 'checkbox', 'options' => $t_id, 'fatherclass' => 'lux', 'addinput' => 1, 'modid' => isset($mod_result['id'])?$mod_result['id']:'', 'mytype' => 't','value' => isset($t_id_array)?$t_id_array:''),

        //物料 ： 这个是隐藏起来的
        'g_m_type' => array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"', 'disabled' => 'disabled'),
        'g_m_id_name' => array('type' => 'select', 'options' => '', 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"'),
        'g_m_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'disabled' => 'disabled'),
        'g_m_value' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'disabled' => 'disabled'),
        'g_m_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),

        //工序 ：　这个是隐藏起来的
        'g_t_type_name' => array('type' => 'select', 'options' => get_fty_t_type(), 'addon' => 'onchange="searchTask(this)"', 'disabled' => 'disabled'),
        'g_t_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskPriceBlur(this)"', 'disabled' => 'disabled'),
        'g_t_time' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskTimeBlur(this)"', 'disabled' => 'disabled'),
        'g_t_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),


        // 這些是用radio還是checkbox，即是是可以多選還是單選？（用checkbox就不必一定要选择）
        'process' => array('title' => '表面处理', 'type' => 'checkbox', 'options' => $process, 'value' => isset($process_array)?$process_array:''),
        'electroplate' => array('title' => '电镀', 'type' => 'checkbox', 'options' => $electroplate, 'value' => isset($electroplate_array)?$electroplate_array:''),
        'electroplate_thick' => array('title' => '电镀厚度', 'type' => 'checkbox', 'options' => $electroplate_thick, 'value' => isset($electroplate_thick_array)?$electroplate_thick_array:''),
        'other'	=> array('title' => '其他', 'type' => 'checkbox', 'options' => $other, 'value' => isset($other_array)?$other_array:''),

        //'p_labour' => array('title' => '人工', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_labour'])?$mod_result['p_labour']:''),
        //'p_workpiece' => array('title' => '工件', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_workpiece'])?$mod_result['p_workpiece']:''),
        'p_plate' => array('title' => '电镀人工价', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_plate'])?$mod_result['p_plate']:'', 'addon' => 'onblur="updateBomTotal()"'),
        //'p_stone' => array('title' => '石料', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_stone'])?$mod_result['p_stone']:''),
        //'p_parts' => array('title' => '配件', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_parts'])?$mod_result['p_parts']:''),
        'p_other' => array('title' => '其他成本', 'type' => 'text', 'restrict' => 'number',  'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_other'])?$mod_result['p_other']:'', 'addon' => 'onblur="updateBomTotal()"'),
        'p_profit' => array('title' => '利润', 'type' => 'text', 'restrict' => 'number',  'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_profit'])?$mod_result['p_profit']:1, 'addon' => 'onblur="updateBomTotal()"'),
        //'p_total' => array('title' => '合计', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['p_total'])?$mod_result['p_total']:''),

        //'people_h' => array('title' => '经手人', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['people_h'])?$mod_result['people_h']:''),
        //'people_a' => array('title' => '审核', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['people_a'])?$mod_result['people_a']:''),

        'submitbtn'	=> array('type' => 'submit', 'value' => ' 确定 '),
    );

    //material
    for($i = 0; $i < @$m_item_num; $i++){
        $formItems['g_m_type'.$i] = array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"', 'value' => isset($mod_result_m[$i]['m_type'])?$mod_result_m[$i]['m_type']:'', 'disabled' => 'disabled');
        $formItems['g_m_id_name'.$i] = array('type' => 'select', 'options' => array(array($mod_result_m[$i]['m_id'].' : '.$mod_result_m[$i]['m_name'], $mod_result_m[$i]['m_id'].' : '.$mod_result_m[$i]['m_name'])), 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"', 'value' => isset($mod_result_m[$i]['m_id'])?$mod_result_m[$i]['m_id'].' : '.$mod_result_m[$i]['m_name']:'');
        $formItems['g_m_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'value' => isset($mod_result_m[$i]['m_price'])?$mod_result_m[$i]['m_price']:'');
        $formItems['g_m_value'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'value' => isset($mod_result_m[$i]['m_value'])?$mod_result_m[$i]['m_value']:'');
        $formItems['g_m_remark'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'value' => isset($mod_result_m[$i]['m_remark'])?$mod_result_m[$i]['m_remark']:'');
    }
    //material 最后一个
    $formItems['g_m_type'.$i] = array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"');
    $formItems['g_m_id_name'.$i] = array('type' => 'select', 'options' => '', 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"', 'disabled' => 'disabled');
    $formItems['g_m_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'disabled' => 'disabled');
    $formItems['g_m_value'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'disabled' => 'disabled');
    $formItems['g_m_remark'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled');


    //task
    for($i = 0; $i < @$t_item_num; $i++){
        $formItems['g_t_type_name'.$i] = array('type' => 'select', 'options' => get_fty_t_type(), 'addon' => 'onchange="searchTask(this)"', 'value' => isset($mod_result_t[$i]['t_id'])?$mod_result_t[$i]['t_id']:'', 'disabled' => 'disabled');
        $formItems['g_t_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskPriceBlur(this)"', 'value' => isset($mod_result_t[$i]['t_price'])?$mod_result_t[$i]['t_price']:'');
        $formItems['g_t_time'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskTimeBlur(this)"', 'value' => isset($mod_result_t[$i]['t_time'])?$mod_result_t[$i]['t_time']:'');
        $formItems['g_t_remark'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'value' => isset($mod_result_t[$i]['t_remark'])?$mod_result_t[$i]['t_remark']:'');
    }
    //task最后一个
    $formItems['g_t_type_name'.$i] = array('type' => 'select', 'options' => get_fty_t_type(), 'addon' => 'onchange="searchTask(this)"');
    $formItems['g_t_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskPriceBlur(this)"', 'disabled' => 'disabled');
    $formItems['g_t_time'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskTimeBlur(this)"', 'disabled' => 'disabled');
    $formItems['g_t_remark'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled');

    $goodsForm->init($formItems);

    if(!$myerror->getAny() && $goodsForm->check()){
        //20150308 检查用户是否设置了所属工厂
        if($_SESSION['ftylogininfo']['aFtyName'] != ''){
            //先设定一定要上传图片，以后有需要可以为空，但是要屏蔽处理图片函数的报错
            //if( !isset($_SESSION['fty_upload_photo']) || $_SESSION['fty_upload_photo'] == ''){
            //$myerror->error('必须先上传图片', 'sendform');
            //}else{

            //fb($_POST);die('@');

            //20130716
            //未上传图片是被允许的，一但上传则有格式和大小的限制
            //这里是用 $_FILES['photo'] photo是 input 的属性，而不是用 $_FILES['file']['type']
            if( ((@$_FILES['photo']['type'] == 'image/jpg' || @$_FILES['photo']['type'] == 'image/jpeg' || @$_FILES['photo']['type'] == 'image/gif' || @$_FILES['photo']['type'] == 'image/pjpeg' || @$_FILES['photo']['type'] == 'image/png') && (@$_FILES['photo']['size'] / 1024) <= 500) || @$_FILES['photo']['name'] == '' ){
                //4 是没有文件上传
                if (@$_FILES['photo']['error'] > 0 && @$_FILES['photo']['error'] != 4){
                    $myerror->error('Upload photo Error ! Return Code: '.@$_FILES['photo']['error'], 'BACK');
                }else{
                    $g_id = $_POST['g_id'];//autoGenerationID();
                    //fb($g_id);

                    $target = '';
                    $add_tip = '';
                    if(@$_FILES['photo']['name'] != ''){
                        // 转为大写字母(pid + 时间 + 原图片的后缀名)
                        // 图片的格式一般都是3位字母吧，没有其他的吧。。。？
                        $temp = substr(@$_FILES['photo']['name'], -4);
                        $target = strtoupper($g_id.'_'.date('YmdHis').$temp);
                        //上传图片
                        move_uploaded_file(@$_FILES['photo']['tmp_name'], iconv('UTF-8', 'GBK', $pic_path_fty_bom.$target));
                        if(file_exists(iconv('UTF-8','GBK', $pic_path_fty_bom.$target))) {
                            /*$small_photo = 's_' . $target;
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
                            }*/

                            $add_tip = 'Upload photo '.$target.' success! ';
                        }else{
                            $add_tip = 'Upload photo '.$target.' <i>failure</i>! ';
                        }
                    }

                    $g_type = $_POST['g_type'];
                    $g_material = $_POST['g_material'];
                    $g_size = $_POST['g_size'];
                    $g_gem_num = $_POST['g_gem_num'];
                    $g_cast = $_POST['g_cast'];
                    $g_plating = $_POST['g_plating'];
                    $g_weight = $_POST['g_weight'];
                    $g_ccode = $_POST['g_ccode'];
                    $g_sample_order_no = $_POST['g_sample_order_no'];

                    //因为checkbox一个都没有选的时候是没有post数据的，所以这里要记录checkbox有post的数据，为下面计算除物料和工序外的post个数
                    $checkbox_num = 0;
                    if(isset($_POST['process'])) $checkbox_num++;
                    if(isset($_POST['electroplate'])) $checkbox_num++;
                    if(isset($_POST['electroplate_thick'])) $checkbox_num++;
                    if(isset($_POST['other'])) $checkbox_num++;

                    $i = 0;
                    $sign = 0;//标志是将$_POST的值赋给material还是task
                    $material = array();
                    $task = array();
                    foreach( $_POST as $key=>$v){
                        //前面9个是非物料和工序的，不处理，所以是序号小于等于8。20120929 加了客号和版单编号，所以是10
                        //20150503 修改bom的时候有图片上传，多了一个post参数sign
                        if(isset($_POST['sign'])){
                            $before = 11;
                        }else{
                            $before = 10;
                        }
                        if( $i <= $before){
                            $i++;
                        }
                        //后面有7个（有3个是隐藏的）是非物料和工序的，不处理
                        elseif( $i >= count($_POST) - (7 + $checkbox_num) ){
                            $i++;
                        }else{
                            if(strpos($key, 'g_m_type') !== false){
                                $sign = 1;
                                $material[] = $v;
                                $i++;
                                continue;
                            }
                            if($sign == 0){
                                $material[] = $v;
                            }else{
                                $task[]	= $v;
                            }
                            $i++;
                        }
                    }

                    //fb($material);
                    //fb($task);
                    //die();
                    $m_num = intval(count($material) / 4);
                    //整理好的material数据
                    $material_arr = array();
                    $m_index = 0;
                    for($i = 0; $i < $m_num; $i++){
                        $material_arr[$i]['price'] = $material[$m_index++];
                        $material_arr[$i]['value'] = $material[$m_index++];
                        $material_arr[$i]['remark'] = $material[$m_index++];
                        $material_arr[$i]['m_id'] = $material[$m_index++];
                    }

                    $t_num = intval(count($task) / 4);
                    //整理好的task数据
                    $task_arr = array();
                    $t_index = 0;
                    for($i = 0; $i < $t_num; $i++){
                        $task_arr[$i]['price'] = $task[$t_index++];
                        $task_arr[$i]['time'] = $task[$t_index++];
                        $task_arr[$i]['remark'] = $task[$t_index++];
                        $task_arr[$i]['t_id'] = $task[$t_index++];
                    }

                    //fb($material_arr);
                    //fb($task_arr);
                    //die();

                    $process = '';
                    if(isset($_POST['process'])){
                        if(count($_POST['process']) == 1 && !is_array($_POST['process'])){
                            $process = $_POST['process'];
                        }else{
                            for($i = 0; $i < count($_POST['process']); $i++){
                                if($i == 0){
                                    $process = $_POST['process'][$i];
                                    continue;
                                }
                                $process .= '|' . $_POST['process'][$i];
                            }
                        }
                    }

                    $electroplate = '';
                    if(isset($_POST['electroplate'])){
                        if(count($_POST['electroplate']) == 1 && !is_array($_POST['electroplate'])){
                            $electroplate = $_POST['electroplate'];
                        }else{
                            for($i = 0; $i < count($_POST['electroplate']); $i++){
                                if($i == 0){
                                    $electroplate = $_POST['electroplate'][$i];
                                    continue;
                                }
                                $electroplate .= '|' . $_POST['electroplate'][$i];

                            }
                        }
                    }

                    $electroplate_thick = '';
                    if(isset($_POST['electroplate_thick'])){
                        if(count($_POST['electroplate_thick']) == 1 && !is_array($_POST['electroplate_thick'])){
                            $electroplate_thick = $_POST['electroplate_thick'];
                        }else{
                            for($i = 0; $i < count($_POST['electroplate_thick']); $i++){
                                if($i == 0){
                                    $electroplate_thick = $_POST['electroplate_thick'][$i];
                                    continue;
                                }
                                $electroplate_thick .= '|' . $_POST['electroplate_thick'][$i];
                            }
                        }
                    }

                    $other = '';
                    if(isset($_POST['other'])){
                        if(count($_POST['other']) == 1 && !is_array($_POST['other'])){
                            $other = $_POST['other'];
                        }else{
                            for($i = 0; $i < count($_POST['other']); $i++){
                                if($i == 0){
                                    $other = $_POST['other'][$i];
                                    continue;
                                }
                                $other .= '|' . $_POST['other'][$i];
                            }
                        }
                    }

                    //电镀人工价
                    $p_plate = $_POST['p_plate'];

                    //其他成本
                    $p_other = $_POST['p_other'];

                    //利润
                    $p_profit = $_POST['p_profit'];

                    //！！！合计（在js中计算并赋值给hidden input，这里获取的是其post的值）
                    $m_total = $_POST['post_m_total'];
                    $t_total = $_POST['post_t_total'];
                    $p_total = $_POST['post_total'];

                    $created_by = $_SESSION["ftylogininfo"]["aName"];//$_POST['people_h'];

                    $time = dateMore();

                    $mysql_photo = '';
                    /*if(isset($_SESSION['fty_upload_photo']) && $_SESSION['fty_upload_photo'] != ''){
                        $temp = explode('.', $_SESSION['fty_upload_photo']);
                        $photo = $_SESSION['fty_upload_photo'];
                        $mysql_photo = 'upload/mysql/'.time().'.'.$temp[1];
                    }*/

                    if(isset($_GET['copyid'])){

                        $bom_rtn = $mysql->qone('select id from bom where g_id = ?', $g_id);
                        if(isset($bom_rtn['id']) && $bom_rtn['id'] >0){
                            $myerror->error('此 产品编号 已存在，新增bom失败', 'sendform');
                        }else{

                            // 1、修改的时候可以上传（能获取到$_FILE）；
                            // 2、也可以不上传（不能获取到$_FILE）；
                            // 3、也可以保持原有的图片不变（不能获取到$_FILE）。
                            if(isset($_POST['sign'])){
                                $photo = $target;
                            }else{
                                $temp = substr($mod_result['photo'], -4);
                                $photo = strtoupper($g_id.'_'.date('YmdHis').$temp);
                                //复制图片
                                copy($pic_path_fty_bom.$mod_result['photo'], $pic_path_fty_bom.$photo);
                            }

                            //20121102 pending bom 是否加入sys product的状态
                            $bom_isin = 0;

                            $result = $mysql->q('insert into bom values (NULL, '.moreQm(28).')', $time, $g_id, $g_type, $g_material,$g_size, $g_gem_num, $g_cast, $g_plating, $g_weight, $photo, $mysql_photo, $process, $electroplate, $electroplate_thick, $other, $m_total, $t_total, $p_plate, $p_other, $p_profit, $p_total, $created_by, '', '未完成', $g_ccode, $g_sample_order_no, $bom_isin, $time);
                            if($result){
                                $id = intval($result);

                                //更新BOM参数设置
                                $setting = $mysql->qone('select bom_id from fty_bom_setting');
                                $change_bom_setting = substr($g_id, 1);
                                if($change_bom_setting >= $setting['bom_id']){
                                    $mysql->q('update fty_bom_setting set bom_id = ?', $change_bom_setting+1);
                                }

                                //写入数据到关系表bom_material
                                foreach($material_arr as $v){
                                    $rtn = $mysql->qone('select m_name, m_type, m_color, m_unit, m_loss from fty_material where m_id = ?', $v['m_id']);
                                    $mysql->q('insert into bom_material values (NULL, '.moreQm(11).')', $id, $rtn['m_type'], $v['m_id'], $rtn['m_name'], $rtn['m_color'], $rtn['m_unit'], $v['price'], $v['value'], $rtn['m_loss'], round($v['price']*$v['value']*(1+$rtn['m_loss']/100), 2), $v['remark']);
                                }

                                //写入数据到关系表bom_task
                                foreach($task_arr as $v){
                                    $rtn = $mysql->qone('select t_name from fty_task where t_id = ?', $v['t_id']);
                                    $mysql->q('insert into bom_task values (NULL, '.moreQm(7).')', $id, $v['t_id'], $rtn['t_name'], $v['price'], $v['time'], $v['price']*$v['time'], $v['remark']);
                                }

                                if(is_int($id) && $id > 0){
                                    //@copy(iconv('UTF-8','GBK', $_SESSION['fty_upload_photo']), iconv('UTF-8','GBK', $photo));
                                    //@copy(iconv('UTF-8','GBK', $_SESSION['fty_upload_photo']), iconv('UTF-8','GBK', $mysql_photo));
                                    //@unlink($_SESSION['fty_upload_photo']);
                                    //$_SESSION['fty_upload_photo'] = '';
                                    $myerror->ok($add_tip.' '.'复制产品资料 成功! ', 'searchform&page=1');
                                }else{
                                    $myerror->error($add_tip.' '.'由于返回值异常，复制产品资料 失败', 'searchform&page=1');
                                }
                            }else{
                                $myerror->error('由于系统原因，复制产品资料 失败', 'searchform&page=1');
                            }
                        }
                    }elseif(isset($_GET['modid'])){

                        // 1、修改的时候可以上传（能获取到$_FILE）；
                        // 2、也可以不上传（不能获取到$_FILE）；
                        // 3、也可以保持原有的图片不变（不能获取到$_FILE）。
                        if(isset($_POST['sign'])){
                            $photo = $target;
                        }else{
                            $photo = $mod_result['photo'];
                        }

                        $result = $mysql->q("UPDATE bom SET g_time = ?, g_type = ?, g_material = ?, g_size = ?, g_gem_num = ?, g_cast = ?, g_plating = ?, g_weight = ?, photo = ?, mysql_photo = ?, g_process = ?, electroplate = ?, electroplate_thick = ?, other = ?, p_plate = ?, p_other = ?, p_profit = ?, p_total = ?, g_ccode = ?, g_sample_order_no = ? WHERE id = ?", $time, $g_type, $g_material, $g_size, $g_gem_num, $g_cast, $g_plating, $g_weight, $photo, $mysql_photo, $process, $electroplate, $electroplate_thick, $other, $p_plate, $p_other, $p_profit, $p_total, $g_ccode, $g_sample_order_no, $_GET['modid']);
                        if($result){
                            //删除关系表dom_material 中 $_GET['modid'] 的内容
                            $mysql->q('delete from bom_material where bom_id = ?', $_GET['modid']);
                            //写入数据到关系表bom_material
                            foreach($material_arr as $v){
                                $rtn = $mysql->qone('select m_name, m_type, m_color, m_unit, m_loss from fty_material where m_id = ?', $v['m_id']);
                                $mysql->q('insert into bom_material values (NULL, '.moreQm(10).')', $_GET['modid'], $rtn['m_type'], $v['m_id'], $rtn['m_name'], $rtn['m_color'], $rtn['m_unit'], $v['price'], $v['value'], round($v['price']*$v['value']*(1+$rtn['m_loss']/100), 2), $v['remark']);
                            }

                            //删除关系表dom_task 中 $_GET['modid'] 的内容
                            $mysql->q('delete from bom_task where bom_id = ?', $_GET['modid']);
                            //写入数据到关系表bom_task
                            foreach($task_arr as $v){
                                $rtn = $mysql->qone('select t_name from fty_task where t_id = ?', $v['t_id']);
                                $mysql->q('insert into bom_task values (NULL, '.moreQm(7).')', $_GET['modid'], $v['t_id'], $rtn['t_name'], $v['price'], $v['time'], $v['price']*$v['time'], $v['remark']);
                            }

                            //@copy(iconv('UTF-8','GBK', $_SESSION['fty_upload_photo']), iconv('UTF-8','GBK', $photo));
//                            @copy(iconv('UTF-8','GBK', $_SESSION['fty_upload_photo']), iconv('UTF-8','GBK', $mysql_photo));
                            //@unlink($_SESSION['fty_upload_photo']);
                            //$_SESSION['fty_upload_photo'] = '';
                            $myerror->ok($add_tip.' '.'修改产品资料 成功!', 'searchform&page=1');
                        }else{
                            $myerror->error($add_tip.' '.'由于系统原因，修改产品资料 失败', 'searchform&page=1');
                        }
                    }elseif(isset($_GET['pid'])){

                        $bom_rtn = $mysql->qone('select id from bom where g_id = ?', $g_id);
                        if(isset($bom_rtn['id']) && $bom_rtn['id'] >0){
                            $myerror->error('此 产品编号 已存在，新增bom失败', 'sendform');
                        }else{

                            // 1、修改的时候可以上传（能获取到$_FILE）；
                            // 2、也可以不上传（不能获取到$_FILE）；
                            // 3、也可以保持原有的图片不变（不能获取到$_FILE）。
                            if(isset($_POST['sign'])){
                                $photo = $target;
                            }else{
                                $photo = $mod_result['photo'];
                            }

                            //20121102 pending bom 是否加入sys product的状态
                            $bom_isin = 0;

                            $result = $mysql->q('insert into bom values (NULL, '.moreQm(28).')', $time, $g_id, $g_type, $g_material,$g_size, $g_gem_num, $g_cast, $g_plating, $g_weight, $photo, $mysql_photo, $process, $electroplate, $electroplate_thick, $other, $m_total, $t_total, $p_plate, $p_other, $p_profit, $p_total, $created_by, '', '未完成', $g_ccode, $g_sample_order_no, $bom_isin, $time);
                            if($result){
                                $id = intval($result);

                                //更新BOM参数设置
                                $setting = $mysql->qone('select bom_id from fty_bom_setting');
                                $change_bom_setting = substr($g_id, 1);
                                if($change_bom_setting >= $setting['bom_id']){
                                    $mysql->q('update fty_bom_setting set bom_id = ?', $change_bom_setting+1);
                                }

                                //写入数据到关系表bom_material
                                foreach($material_arr as $v){
                                    $rtn = $mysql->qone('select m_name, m_type, m_color, m_unit, m_loss from fty_material where m_id = ?', $v['m_id']);
                                    $mysql->q('insert into bom_material values (NULL, '.moreQm(11).')', $id, $rtn['m_type'], $v['m_id'], $rtn['m_name'], $rtn['m_color'], $rtn['m_unit'], $v['price'], $v['value'], $rtn['m_loss'], round($v['price']*$v['value']*(1+$rtn['m_loss']/100), 2), $v['remark']);
                                }

                                //写入数据到关系表bom_task
                                foreach($task_arr as $v){
                                    $rtn = $mysql->qone('select t_name from fty_task where t_id = ?', $v['t_id']);
                                    $mysql->q('insert into bom_task values (NULL, '.moreQm(7).')', $id, $v['t_id'], $rtn['t_name'], $v['price'], $v['time'], $v['price']*$v['time'], $v['remark']);
                                }

                                if(is_int($id) && $id > 0){
                                    //@copy(iconv('UTF-8','GBK', $_SESSION['fty_upload_photo']), iconv('UTF-8','GBK', $photo));
                                    //@copy(iconv('UTF-8','GBK', $_SESSION['fty_upload_photo']), iconv('UTF-8','GBK', $mysql_photo));
                                    //@unlink($_SESSION['fty_upload_photo']);
                                    //$_SESSION['fty_upload_photo'] = '';
                                    $myerror->ok($add_tip.' '.'新增产品资料 成功! ', 'searchform&page=1');
                                }else{
                                    $myerror->error($add_tip.' '.'由于返回值异常，新增产品资料 失败', 'searchform&page=1');
                                }
                            }else{
                                $myerror->error('由于系统原因，新增产品资料 失败', 'searchform&page=1');
                            }
                        }
                    }
                    //}
                }
            }else{
                $myerror->error('上传图片 失败! 请选择JPG、PNG或者GIF格式的图片上传! 且图片大小不要超过 500 KB!', 'BACK');
            }
        }else{
            $myerror->error('帐号未设置工厂，不能修改bom！', 'main');
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

    $goodsForm->begin();

    ?>
    <h1 class="green"><?php echo (isset($_GET['modid'])?'修改':(isset($_GET['copyid'])?'复制':'')).' '.$title_g_id; ?> 物料清单（BOM）<em>*号为必填项</em></h1>
    <fieldset>
    <legend class='legend'>物料清单（BOM）</legend>

    <fieldset>
        <legend class='legend'>第一步：上传图片</legend>
        <?
        /*if(isset($_SESSION['fty_upload_photo']) && $_SESSION['fty_upload_photo'] != ''){
            if (is_file($_SESSION['fty_upload_photo']) == true) {
                $arr = getimagesize($_SESSION['fty_upload_photo']);
                $pic_width = $arr[0];
                $pic_height = $arr[1];
                $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/fty/'.$_SESSION['fty_upload_photo'].'" class="tooltip2" target="_blank" title="'.$_SESSION['fty_upload_photo'].'"><img src="/fty/'.$_SESSION['fty_upload_photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
            }else{
                echo '<div style="margin-left:28px;"><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/><br />';
            }
            echo "<b><a class='button' href='?act=upload_photo&chg'>更换图片</a></b></div>";
        }else{
            echo "<div style='margin-left:28px;'><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><a class='button' href='?act=upload_photo&chg'>上传图片</a></div>";
        }*/

        if(is_file($pic_path_fty_bom . $mod_result['photo']) == true){
            $arr = getimagesize($pic_path_fty_bom . $mod_result['photo']);
            $pic_width = $arr[0];
            $pic_height = $arr[1];
            //20121022 获取文件大小，在图片旁显示文件的属性
            $a = filesize($pic_path_fty_bom . $mod_result['photo']);
            $image_size = getimgsize(100, 60, $pic_width, $pic_height);

            if(isset($_GET['modid'])){
                echo 'image info：('.$mod_result['photo'].'&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/fty/'.$pic_path_fty_bom . $mod_result['photo'].'" class="tooltip2" target="_blank" title="'.$mod_result['photo'].'"><img src="/fty/'.$pic_path_fty_bom . $mod_result['photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul></div><div><b><a class="button" href="?act=delete_photo_new&bom_id='.$mod_result['id'].'&photo='.$mod_result['photo'].'">DELETE</a></b></div>';
            }elseif(isset($_GET['copyid'])){
                echo 'image info：(将会复制此图片并重新命名&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/fty/'.$pic_path_fty_bom . $mod_result['photo'].'" class="tooltip2" target="_blank" title="'.$mod_result['photo'].'"><img src="/fty/'.$pic_path_fty_bom . $mod_result['photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul></div>';
            }elseif(isset($_GET['pid'])){
                echo 'image info：('.$mod_result['photo'].'&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/fty/'.$pic_path_fty_bom . $mod_result['photo'].'" class="tooltip2" target="_blank" title="'.$mod_result['photo'].'"><img src="/fty/'.$pic_path_fty_bom . $mod_result['photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul></div>';
            }
        }else{
            echo '<div style="margin-left:28px;"><img src="../images/nopic.gif" border="0" width="80" height="60"><br /><input type="file" name="photo" id="photo" /><input type="hidden" name="sign" value="1"></div>';
        }

        ?>
    </fieldset>
    <div class="line"></div>
    <fieldset>
        <legend class='legend'>第二步：填写并提交</legend>
        <table width="90%" id="table">
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('g_type');?></td>
                <td width="25%"><? $goodsForm->show('g_id');?></td>
                <td width="25%"><? $goodsForm->show('g_material');?></td>
                <td width="25%"><? $goodsForm->show('g_size');?></td>
            </tr>
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('g_gem_num');?></td>
                <td width="25%"><? $goodsForm->show('g_cast');?></td>
                <td width="25%"><? $goodsForm->show('g_plating');?></td>
                <td width="25%"><? $goodsForm->show('g_weight');?></td>
            </tr>
            <tr valign="top">
                <td width="25%"><? $goodsForm->show('g_ccode');?></td>
                <td width="25%"><? $goodsForm->show('g_sample_order_no');?></td>
                <td width="25%">&nbsp;</td>
                <td width="25%">&nbsp;</td>
            </tr>
        </table>
        <div class="line"></div>

        <div style="margin-left:28px;">
            <label class="formtitle" for="g_cast">物料&nbsp;<a href="javascript:void(window.open('/fty/?act=addmaterial&page=1#search_material', 'newwindow', 'height=500,width=1200,scrollbars=yes,status=yes'))">【快速查询】</a></label>
            <table width="100%" id="tableDnD_wl">
                <tbody id="tbody">
                <tr class="formtitle nodrop nodrag">
                    <td width="2%"></td>
                    <td>类别</td>
                    <td>物料编号：名称</td>
                    <td>规格颜色</td>
                    <td>单位</td>
                    <td>单价</td>
                    <td>数量/重量值</td>
                    <td>损耗率(%)</td>
                    <td width="10%">价格</td>
                    <td width="20%">备注</td>
                    <td width="5%"></td>
                </tr>
                <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('g_m_type');?></td>
                    <td><? $goodsForm->show('g_m_id_name');?></td>
                    <td id="color"></td>
                    <td id="unit"></td>
                    <td><? $goodsForm->show('g_m_price');?></td>
                    <td><? $goodsForm->show('g_m_value');?></td>
                    <td><div id="m_loss"></div></td>
                    <td><div id="m_total"></div></td>
                    <td><? $goodsForm->show('g_m_remark');?></td>
                    <td><div id="del" onclick="delBomItem(this)"></div><input type="hidden" id="g_m_id" name="g_m_id" value="" disabled="disabled"/></td>
                </tr>
                <?
                for($i = 0; $i < @$m_item_num; $i++){
                    ?>
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                        <td id="index" class="dragHandle"></td>
                        <td><? $goodsForm->show('g_m_type'.$i);?></td>
                        <td><? $goodsForm->show('g_m_id_name'.$i);?></td>
                        <td id="color<?=$i?>"><?=$mod_result_m[$i]['m_color']?></td>
                        <td id="unit<?=$i?>"><?=$mod_result_m[$i]['m_unit']?></td>
                        <td><? $goodsForm->show('g_m_price'.$i);?></td>
                        <td><? $goodsForm->show('g_m_value'.$i);?></td>
                        <td><div id="m_loss"><?=$mod_result_m[$i]['m_loss']?></div></td>
                        <td><div id="m_total"><?=$mod_result_m[$i]['m_total']?></div></td>
                        <td><? $goodsForm->show('g_m_remark'.$i);?></td>
                        <td><div id="del<?=$i?>" onclick="delBomItem(this)"><img src="../../sys/images/del-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Delete" /></div><input type="hidden" id="g_m_id<?=$i?>" name="g_m_id<?=$i?>" value="<?=$mod_result_m[$i]['m_id']?>" /></td>
                    </tr>
                <?
                }
                //下面是最后一个
                ?>
                <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('g_m_type'.$i);?></td>
                    <td><? $goodsForm->show('g_m_id_name'.$i);?></td>
                    <td id="color<?=$i?>"></td>
                    <td id="unit<?=$i?>"></td>
                    <td><? $goodsForm->show('g_m_price'.$i);?></td>
                    <td><? $goodsForm->show('g_m_value'.$i);?></td>
                    <td><div id="m_loss"></div></td>
                    <td><div id="m_total"></div></td>
                    <td><? $goodsForm->show('g_m_remark'.$i);?></td>
                    <td><div id="del<?=$i?>" onclick="delBomItem(this)"></div><input type="hidden" id="g_m_id<?=$i?>" name="g_m_id<?=$i?>" value="" disabled="disabled"/></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="line"></div>
        <div style="margin-left:28px;">
            <label class="formtitle" for="g_cast">工序&nbsp;<a href="javascript:void(window.open('/fty/?act=addtask&page=1#search_task', 'newwindow', 'height=500,width=1200,scrollbars=yes,status=yes'))">【快速查询】</a></label>
            <table width="100%" id="tableDnD_gx">
                <tbody id="tbody1">
                <tr class="formtitle nodrop nodrag">
                    <td width="2%"></td>
                    <td>工序号：工序名称</td>
                    <td>工价</td>
                    <td>工时</td>
                    <td width="10%">价格</td>
                    <td width="20%">备注</td>
                    <td width="5%"></td>
                </tr>
                <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('g_t_type_name');?></td>
                    <td><? $goodsForm->show('g_t_price');?></td>
                    <td><? $goodsForm->show('g_t_time');?></td>
                    <td><div id="t_total"></div></td>
                    <td><? $goodsForm->show('g_t_remark');?></td>
                    <td><div id="del" onclick="delBomItem(this)"></div><input type="hidden" id="g_t_id" name="g_t_id" value="" disabled="disabled"/></td>
                </tr>
                <?
                for($i = 0; $i < @$t_item_num; $i++){
                    ?>
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                        <td id="index" class="dragHandle"></td>
                        <td><? $goodsForm->show('g_t_type_name'.$i);?></td>
                        <td><? $goodsForm->show('g_t_price'.$i);?></td>
                        <td><? $goodsForm->show('g_t_time'.$i);?></td>
                        <td><div id="t_total"><?=$mod_result_t[$i]['t_total']?></div></td>
                        <td><? $goodsForm->show('g_t_remark'.$i);?></td>
                        <td><div id="del<?=$i?>" onclick="delBomItem(this)"><img src="../../sys/images/del-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Delete" /></div><input type="hidden" id="g_t_id<?=$i?>" name="g_t_id<?=$i?>" value="<?=$mod_result_t[$i]['t_id']?>" /></td>
                    </tr>
                <?
                }
                //下面是最后一个
                ?>
                <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('g_t_type_name'.$i);?></td>
                    <td><? $goodsForm->show('g_t_price'.$i);?></td>
                    <td><? $goodsForm->show('g_t_time'.$i);?></td>
                    <td><div id="t_total"></div></td>
                    <td><? $goodsForm->show('g_t_remark'.$i);?></td>
                    <td><div id="del<?=$i?>" onclick="delBomItem(this)"></div><input type="hidden" id="g_t_id<?=$i?>" name="g_t_id<?=$i?>" value="" disabled="disabled"/></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="line"></div>
        <?
        $goodsForm->show('process');
        $goodsForm->show('electroplate');
        $goodsForm->show('electroplate_thick');
        $goodsForm->show('other', '<div class="line"></div>');
        ?>

        <div class="set"><label class="formtitle">物料总价</label><br /><div id="materialTotal">0.00</div></div>
        <div class="set"><label class="formtitle">工序总价</label><br /><div id="taskTotal">0.00</div></div>
        <?
        //$goodsForm->show('p_labour');
        //$goodsForm->show('p_workpiece');
        $goodsForm->show('p_plate');
        //$goodsForm->show('p_stone');
        //$goodsForm->show('p_parts');
        $goodsForm->show('p_other');
        $goodsForm->show('p_profit');
        ?>
        <div class="set"><label class="formtitle">总价</label><br /><div id="allTotal" style="color:#F00; font-size:24px">0.00</div><input type="hidden" name="post_m_total" id="post_m_total" value="" /><input type="hidden" name="post_t_total" id="post_t_total" value="" /><input type="hidden" name="post_total" id="post_total" value="" /></div>
        <div class="line"></div>
        <?
        //$goodsForm->show('p_total', '<div class="line"></div>');

        //$goodsForm->show('people_h', '<div class="line"></div>');
        //$goodsForm->show('people_a', '<div class="line"></div>');

        $goodsForm->show('submitbtn');
        ?>
    </fieldset>
    </fieldset>
    <?
    $goodsForm->end();
}
?>


<script>
    $(function(){
        $(".template").hide();
        updateMTotal();
        updateTTotal();

        //table tr层表单可拖动, mod 20120921 显示可拖动的标志，按住标志就可拖动
        $('#tableDnD_wl').tableDnD({
            /*
             onDrop: function(table, row) {
             generateIndex();//更新item index
             },
             */
            dragHandle: ".dragHandle"
        });

        $('#tableDnD_gx').tableDnD({
            /*
             onDrop: function(table, row) {
             generateIndex();//更新item index
             },
             */
            dragHandle: ".dragHandle"
        });
    })
</script>