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
    <script>alert('帐号未设置工厂，不能添加bom！');</script>
<?php
}

/*if(isset($_GET['delimg']) && $_SESSION['fty_upload_photo'] != ''){
    unlink($_SESSION['fty_upload_photo']);
    $_SESSION['fty_upload_photo'] = '';
    $myerror->ok('刪除上传的图片 成功!', 'BACK');
}*/

//添加的前一个bom 编号
//20130401 去掉改为 自动编号 了
//$prev_no = $mysql->qone('select g_id from bom where created_by = ? order by g_time desc limit 1', $_SESSION["ftylogininfo"]["aName"]);

$image_path = '';

$process = get_bom_bmcl(1);
$electroplate = get_bom_dd(1);
$electroplate_thick = get_bom_ddhd(1);
$other = get_bom_qt(1);
$t_type = get_fty_t_type();

$setting = $mysql->qone('select bom_id from fty_bom_setting');

// form加这个参数才能上传文件，否则获取不到 $_FILE
$goodsForm = new My_Forms(array('multipart'=>true));

$formItems = array(
    'g_id' => array('title' => '产品编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'onblur="judgeBomID(this)"' , 'value' => isset($mod_result['g_id'])?$mod_result['g_id']:'', 'info' => 'N123456-001 (N-类别; 123456-流水號; 001-版本)' /*, 'info' => '(<a href="javascript:" onclick="auto_id()">自动编号</a>) 2-4位是工厂号，后面是流水号'*/),
    'g_type' => array('title' => '类别', 'type' => 'select', 'options' => get_bom_lb(1), 'addon' => 'style="width:200px" onChange="changeBomType(this)"', 'required' => 1),
    'g_material' => array('title' => '底材用料', 'type' => 'select', 'options' => get_bom_dcyl(1), 'addon' => 'style="width:200px"'),
    'g_size' => array('title' => '尺码', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'info' => '请注明单位'),
    //填写的数字允许小数吗？管他是整数还是小数，全部用字符串处理，我勒个去
    'g_gem_num' => array('title' => '成品总石数', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：粒'),
    'g_cast' => array('title' => '铸件数量', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：件'),
    'g_plating' => array('title' => '电镀描述', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
    'g_weight' => array('title' => '重量', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'info' => '单位：克'),
    'g_ccode' => array('title' => '客号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),
    'g_sample_order_no' => array('title' => '板单编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20),

    //'m_id' => array('title' => '物料编号 <font size="-1">（输入框中填写<b> 数量/重量的值 </b>，只有选中后，输入框才能使用。点击小图标显示详情）</font>', 'type' => 'checkbox', 'options' => $m_id, 'fatherclass' => 'lux', 'addinput' => 1, 'modid' => isset($mod_result['id'])?$mod_result['id']:'', 'mytype' => 'm'),

    //'t_id' => array('title' => '工序号 <font size="-1">（输入框中填写<b> 工时 </b>，只有选中后，输入框才能使用。点击小图标显示详情）</font>', 'type' => 'checkbox', 'options' => $t_id, 'fatherclass' => 'lux', 'addinput' => 1, 'modid' => isset($mod_result['id'])?$mod_result['id']:'', 'mytype' => 't'),

    //物料 ： 这个是隐藏起来的
    'g_m_type' => array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"', 'disabled' => 'disabled'),
    'g_m_id_name' => array('type' => 'select', 'options' => '', 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"'),
    'g_m_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'disabled' => 'disabled'),
    'g_m_value' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'disabled' => 'disabled'),
    'g_m_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),

    //物料 ： 这个是显示的第一个
    'g_m_type1' => array('type' => 'select', 'options' => $m_type, 'addon' => 'onchange="searchMaterial(this)"'),
    'g_m_id_name1' => array('type' => 'select', 'options' => '', 'disabled' => 'disabled', 'addon' => 'onchange="searchMaterialDetail(this)"'),
    'g_m_price1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialPriceBlur(this)"', 'disabled' => 'disabled'),
    'g_m_value1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="materialValueBlur(this)"', 'disabled' => 'disabled'),
    'g_m_remark1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),

    //工序 ：　这个是隐藏起来的
    'g_t_type_name' => array('type' => 'select', 'options' => $t_type, 'addon' => 'onchange="searchTask(this)"', 'disabled' => 'disabled'),
    'g_t_price' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskPriceBlur(this)"', 'disabled' => 'disabled'),
    'g_t_time' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskTimeBlur(this)"', 'disabled' => 'disabled'),
    'g_t_remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),

    //工序　：　这个是显示的第一个
    'g_t_type_name1' => array('type' => 'select', 'options' => $t_type, 'addon' => 'onchange="searchTask(this)"'),
    'g_t_price1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskPriceBlur(this)"', 'disabled' => 'disabled'),
    'g_t_time1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:80px" onblur="taskTimeBlur(this)"', 'disabled' => 'disabled'),
    'g_t_remark1' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:160px"', 'disabled' => 'disabled'),


    // 這些是用radio還是checkbox，即是是可以多選還是單選？（用checkbox就不必一定要选择）
    'process' => array('title' => '表面处理', 'type' => 'checkbox', 'options' => $process),
    'electroplate' => array('title' => '电镀', 'type' => 'checkbox', 'options' => $electroplate),
    'electroplate_thick' => array('title' => '电镀厚度', 'type' => 'checkbox', 'options' => $electroplate_thick),
    'other'	=> array('title' => '其他', 'type' => 'checkbox', 'options' => $other),

    'p_plate' => array('title' => '电镀人工价', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'onblur="updateBomTotal()"', 'value' => '0'),
    'p_other' => array('title' => '其他成本', 'type' => 'text', 'restrict' => 'number',  'minlen' => 1, 'maxlen' => 20, 'addon' => 'onblur="updateBomTotal()"', 'value' => '0'),
    'p_profit' => array('title' => '利润(倍) ', 'type' => 'text', 'restrict' => 'number',  'minlen' => 1, 'maxlen' => 20, 'addon' => 'onblur="updateBomTotal()"', 'value' => '1.0'),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' 确定 '),
);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){

    //20150308 检查用户是否设置了所属工厂
    if($_SESSION['ftylogininfo']['aFtyName'] != ''){

        //fb($_POST);die();

        //20150429
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

                fb($_FILES);

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

                //先设定一定要上传图片，以后有需要可以为空，但是要屏蔽处理图片函数的报错
                //if( !isset($_SESSION['fty_upload_photo']) || $_SESSION['fty_upload_photo'] == ''){
                //$myerror->error('必须先上传图片', 'sendform');
                //}else{


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
                    if( $i <= 10){
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
                //加 round 是防止在js计算中出现 97.96000000000001 这类值，多了00...001
                $m_total = round($_POST['post_m_total'], 2);
                $t_total = round($_POST['post_t_total'], 2);
                $p_total = round($_POST['post_total'], 2);

                $created_by = $_SESSION["ftylogininfo"]["aName"];//$_POST['people_h'];

                $time = dateMore();

                $photo = $target;
                $mysql_photo = '';
                /*if(isset($_SESSION['fty_upload_photo']) && $_SESSION['fty_upload_photo'] != ''){
                    $temp = explode('.', $_SESSION['fty_upload_photo']);
                    //$photo = str_replace('/temp/', '/photo/', $_SESSION['fty_upload_photo']);
                    $photo = $_SESSION['fty_upload_photo'];
                    $mysql_photo = 'upload/mysql/'.time().'.'.$temp[1];
                }*/

                //20121102 pending bom 是否加入sys product的状态
                $bom_isin = 0;

                $bom_rtn = $mysql->qone('select id from bom where g_id = ?', $g_id);
                if(isset($bom_rtn['id']) && $bom_rtn['id'] >0){
                    $myerror->error('此 产品编号 已存在，新增bom失败', 'sendform');
                }else{
                    $result = $mysql->q('insert into bom values (NULL, '.moreQm(28).')', $time, $g_id, $g_type, $g_material,
                        $g_size, $g_gem_num, $g_cast, $g_plating, $g_weight, $photo, $mysql_photo, $process, $electroplate, $electroplate_thick, $other, $m_total, $t_total, $p_plate, $p_other, $p_profit, $p_total, $created_by, '', '未完成', $g_ccode, $g_sample_order_no, $bom_isin, $time);
                    if($result){
                        $id = intval($result);

                        //更新BOM参数设置
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
                            @copy(iconv('UTF-8','GBK', $_SESSION['fty_upload_photo']), iconv('UTF-8','GBK', $mysql_photo));
                            //@unlink($_SESSION['fty_upload_photo']);
                            $_SESSION['fty_upload_photo'] = '';
                            $myerror->ok($add_tip.' '.'新增产品资料 成功! ', 'searchform&page=1');
                        }else{
                            $myerror->error($add_tip.' '.'由于返回值异常，新增产品资料 失败', 'sendform');
                        }
                    }else{
                        $myerror->error('由于系统原因，新增产品资料 失败', 'sendform');
                    }
                }
                //}
            }
        }else{
            $myerror->error('上传图片 失败! 请选择JPG、PNG或者GIF格式的图片上传! 且图片大小不要超过 500 KB!', 'BACK');
        }
    }else{
        $myerror->error('帐号未设置工厂，不能添加bom！', 'main');
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
    <h1 class="green">填写物料清单（BOM）<em>*号为必填项</em></h1>
    <fieldset>
        <legend class='legend'>物料清单（BOM）</legend>

        <fieldset>
            <legend class='legend'>第一步：上传图片</legend>
            <?
            /*if(isset($_SESSION['fty_upload_photo']) && $_SESSION['fty_upload_photo'] != ''){
                $arr = getimagesize($_SESSION['fty_upload_photo']);
                $pic_width = $arr[0];
                $pic_height = $arr[1];
                $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/fty/' . $_SESSION['fty_upload_photo'].'" class="tooltip2" target="_blank" title="'.$_SESSION['fty_upload_photo'].'"><img src="/fty/' . $_SESSION['fty_upload_photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
                echo "<b><a class='button' href='?act=upload_photo'>CHANGE</a></b></div>";
            }else{
                echo "<div style='margin-left:28px;'><ul><li><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><a class='button' href='?act=upload_photo'>上传图片</a></div>";
            }*/
            echo "<div style='margin-left:28px;'><ul><li><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><input type='file' name='photo' id='photo' /></div>";
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
            <?php
            //$goodsForm->show('m_id', '<div class="line"></div>');
            //$goodsForm->show('t_id', '<div class="line"></div>');
            ?>
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
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                        <td id="index" class="dragHandle"></td>
                        <td><? $goodsForm->show('g_m_type1');?></td>
                        <td><? $goodsForm->show('g_m_id_name1');?></td>
                        <td id="color1"></td>
                        <td id="unit1"></td>
                        <td><? $goodsForm->show('g_m_price1');?></td>
                        <td><? $goodsForm->show('g_m_value1');?></td>
                        <td><div id="m_loss"></div></td>
                        <td><div id="m_total"></div></td>
                        <td><? $goodsForm->show('g_m_remark1');?></td>
                        <td><div id="del1" onclick="delBomItem(this)"></div><input type="hidden" id="g_m_id1" name="g_m_id1" value="" disabled="disabled"/></td>
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
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                        <td id="index" class="dragHandle"></td>
                        <td><? $goodsForm->show('g_t_type_name1');?></td>
                        <td><? $goodsForm->show('g_t_price1');?></td>
                        <td><? $goodsForm->show('g_t_time1');?></td>
                        <td><div id="t_total"></div></td>
                        <td><? $goodsForm->show('g_t_remark1');?></td>
                        <td><div id="del1" onclick="delBomItem(this)"></div><input type="hidden" id="g_t_id1" name="g_t_id1" value="" disabled="disabled"/></td>
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
    });

    function judgeBomID(obj){
        var my = $(obj);
        var my_val = my.val();
        my_val = my_val.trim();
        my_val = my_val.trim_tab();
        my.val(my_val);
        my_val = my.val();
        var my_val_num1 = my_val.substr(1, 6);
        var my_val_sign = my_val.substr(7, 1);
        var my_val_num2 = my_val.substr(8);
        //20150413 N123456 这种是合法的，N123456-001 这种也是合法的
        //20150512 N123456-001，001这后面的，如果全是数字，则必须3位，如果非数字则填1-3位
        //20150515 -后面部分不做限制
        if( (my_val != '' && !isNaN(my_val_num1) && my_val_num1.length == 6 && my_val.length == 7) || (my_val != '' && !isNaN(my_val_num1) && my_val_num1.length == 6 && my_val_sign == '-' && my_val.length > 8) /*(my_val != '' && !isNaN(my_val_num1) && my_val_num1.length == 6 && my_val_sign == '-' && !isNaN(my_val_num2) && my_val_num2.length == 3 ) || (my_val != '' && !isNaN(my_val_num1) && my_val_num1.length == 6 && my_val_sign == '-' && isNaN(my_val_num2) && my_val_num2.length >= 1 && my_val_num2.length <= 3)*/ ){
            var qs = 'ajax=1&act=ajax-judge_bom_id&value='+my_val;
            $.ajax({
                type: "GET",
                url: "index.php",
                data: qs,
                cache: false,
                dataType: "html",
                error: function(){
                    alert('系统错误，查询bom id失败');
                },
                success: function(data){
                    if(data.indexOf('yes') >= 0){
                        //不输出任何东西
                    }else if(data.indexOf('no-') >= 0){
                        if(data.indexOf('no-1') >= 0){
                            alert('此 产品编号 '+my_val+' 已存在，请输入其他 产品编号 !');
                            my.val('');//id已存在则清空输入框
                            $('#g_type').val('');
                        }else if(data.indexOf('no-2') >= 0){
                            alert('此 产品编号 '+my_val+' 已存在于系统 product 中 !');
                        }else{
                            alert('系统错误 !');
                            my.val('');//id已存在则清空输入框
                            $('#g_type').val('');
                        }
                    }
                }
            })
        }else{
            alert('产品编号格式错误');
            my.val('');
            //my.focus();//不起作用
            $('#g_type').val('');
        }
    }

    function changeBomType(obj){
        var my = $(obj);
        var my_val = my.val();
        var bom_id = $("#g_id");
        var type = my_val.charAt(my_val.length - 2);

        var g_id = type+<?php echo $setting['bom_id']; ?>

        var qs = 'ajax=1&act=ajax-judge_bom_id&value='+g_id;
        $.ajax({
            type: "GET",
            url: "index.php",
            data: qs,
            cache: false,
            error: function(){
                alert('系统错误，查询bom id失败');
            },
            success: function(data){
                if(data.indexOf('yes') >= 0){
                    bom_id.val(g_id);
                }else if(data.indexOf('no-') >= 0){
                    if(data.indexOf('no-1') >= 0){
                        alert('此 产品编号 '+g_id+' 已存在，请输入其他 产品编号 !');
                        my.val('');//id已存在则清空输入框
                        $('#g_type').val('');
                    }else if(data.indexOf('no-2') >= 0){
                        alert('此 产品编号 '+g_id+' 已存在于系统 product 中 !');
                    }else{
                        alert('系统错误 !');
                        my.val('');//id已存在则清空输入框
                        $('#g_type').val('');
                    }
                }
            }
        })
    }

    function auto_id(){
        var type = $('#g_type').val();
        if(type == ''){
            alert('请先选择类别！');
            //$('#g_type').click();//模拟鼠标点击，弹出下拉框提醒用户选择
            return;
        }else{
            //从倒数第二个字符开始，截取1个字符
            var auto_type = type.substr(-2, 1);
            var fty_type = '<?=$_SESSION['ftylogininfo']['aFtyName']?>';
            //如果是管理员
            /*if(fty_type == 'ZJN' || fty_type == 'KEVIN'){
                alert('此帐号是管理员帐号，请使用工厂帐号！');
                return;
            }else{*/
                if(fty_type.substr(0, 1) == 'S'){
                    var fty_type_num = fty_type.substr(1);
                    var serial_num = '<?
					$last_bom_id = $mysql->qone('select g_id from bom where created_by = ? order by g_time desc', $_SESSION['ftylogininfo']['aName']);
					if($last_bom_id){
						$temp = sprintf("%05d", intval(substr($last_bom_id['g_id'], -5))+1);//生成5位流水号，不足在前面补0
						echo $temp;//直接sprintf不行，非要echo才行。。。
					}else{
						echo '00001';	
					}
				?>';
                    $('#g_id').val(auto_type+fty_type_num+serial_num);//格式：type（1位）+工厂号（3位，如果不填充前面的0，只用整数，就无法分辨1和10了，所以这里固定3位工厂号，不足的前面填充0）+产品流水号（5位）
                }else{
                    alert('非法的工厂帐号！');
                    return;
                }
            //}
        }
    }

</script>