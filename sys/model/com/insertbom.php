<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(isset($_GET['delid'])){

    $rs = $mysql->q('update bom set bom_isin = 1 where created_by = ? and g_id = ?', $_GET['insid'], $_GET['delid']);
    if($rs){
        $myerror->ok('BOM ID '.$_GET['delid'].' 已标记，将不再显示在 Pending BOM 中', 'com-fty_searchbom&page='.$_GET['page']);
    }

}else{

    if(!isset($_GET['inid']) || !isset($_GET['insid'])){

        $myerror->warn('缺少参数！', 'com-fty_searchbom&page=1');

    }else{
        $rtn = $mysql->qone('select b.*, bl.bom_lb_name_en, bd.bom_dcyl_name_en from bom b LEFT JOIN fty_bom_lb bl ON b.g_type = bl.bom_lb_name_chi LEFT JOIN fty_bom_dcyl bd ON b.g_material = bd.bom_dcyl_name_chi where b.created_by = ? and b.g_id = ?', $_GET['insid'], $_GET['inid']);

        //20150330
        $mod_result['scode'] = $rtn['g_id'];
        $mod_result['ccode'] = $rtn['g_ccode'];

        $mod_result['description'] = $rtn['g_plating'].' Plating '.$rtn['bom_dcyl_name_en'].' '.$rtn['bom_lb_name_en'].' （Size:'.$rtn['g_size'].',Weight: '.$rtn['g_weight'].',Qty of Stones:'.$rtn['g_gem_num'].',Qty of Casting:'.$rtn['g_cast'].'）';
        $mod_result['description_chi'] = $rtn['g_plating'].' 電鍍 '.$rtn['g_material'].' '.$rtn['g_type'].' （尺碼:'.$rtn['g_size'].'，重量: '.$rtn['g_weight'].'，成品石數:'.$rtn['g_gem_num'].'，鑄件數量:'.$rtn['g_cast'].'）';
        $user_fty_info = $mysql->qone('select FtyName from tw_admin where AdminName = ?', $rtn['created_by']);
        $mod_result['sid'] = $user_fty_info['FtyName'];
        $mod_result['sample_order_no'] = $rtn['g_sample_order_no'];

        $mod_result['pid'] = $rtn['g_id'];
        //20130226 add g_type to product type
        $temp_index = 0;
        $type = get_bom_lb(1);
        for($i = 0; $i < count($type); $i++){
            if($type[$i][0] == $rtn['g_type']){
                $temp_index = $i;
                break;
            }
        }
        $type_e = get_bom_lb(3);
        $mod_result['type'] = $type_e[$temp_index][0];
        $mod_result['cost_rmb'] = $rtn['p_total'];

        $goodsForm = new My_Forms();
        $formItems = array(

            'p_pid' => array('title' => 'Product ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['pid'])?$mod_result['pid']:''),
            'p_cat_num' => array('title' => 'CAT.NO.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['cat_num'])?$mod_result['cat_num']:'000-000'),
            'p_type' => array('title' => 'Type', 'type' => 'select', 'options' => $type_e, 'value' => isset($mod_result['type'])?$mod_result['type']:''),

            'p_description' => array('title' => 'Description', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5, 'value' => isset($mod_result['description'])?$mod_result['description']:'', 'addon' => 'style="width:400px"'),
            'p_description_chi' => array('title' => '中文描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5, 'value' => isset($mod_result['description_chi'])?$mod_result['description_chi']:'', 'addon' => 'style="width:400px"'),

            'p_sid' => array('title' => 'Supplier', 'type' => 'select', 'options' => $supplier, 'value' => isset($mod_result['sid'])?$mod_result['sid']:'', 'required' => 1),
            'p_scode' => array('title' => 'Supplier Product code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['scode'])?$mod_result['scode']:'', 'required' => 1),
            'p_ccode' => array('title' => 'Customer code', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['ccode'])?$mod_result['ccode']:''),
            'p_cost_rmb' => array('title' => 'Cost RMB', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['cost_rmb'])?$mod_result['cost_rmb']:'', 'required' => 1),
            'p_cost_remark' => array('title' => 'Cost remark', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['cost_remark'])?$mod_result['cost_remark']:''),
            'p_exclusive_to' => array('title' => 'Exclusive to', 'type' => 'select', 'options' => get_customer(), 'value' => isset($mod_result['exclusive_to'])?$mod_result['exclusive_to']:''),
            //暂时设定不可以自由修改时间，如果修改了product的信息，就自动为其插入现在的日期
            //現在定為可修改，新增product就指定當天的
            //'p_in_date' => array('title' => 'In Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['in_date'])?$mod_result['in_date']:''),
            'p_sample_order_no' => array('title' => 'Sample Order No.', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['sample_order_no'])?$mod_result['sample_order_no']:''),
            'p_show_in_catalog' => array('title' => 'Show in catalog', 'type' => 'checkbox', 'options' => array('show'), 'value' => (isset($mod_result['show_in_catalog']) && $mod_result['show_in_catalog'] == 1)?'show':''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
        );
        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){

            $pid = $_POST['p_pid'];
            $type = $_POST['p_type'];
            $in_date = /*$_POST['p_in_date'];*/dateMore();
            $cat_num = $_POST['p_cat_num'];
            $description = $_POST['p_description'];
            $description_chi = $_POST['p_description_chi'];
            $sid = $_POST['p_sid'];
            $scode= $_POST['p_scode'];
            $ccode = $_POST['p_ccode'];
            $cost_rmb = $_POST['p_cost_rmb'];
            $cost_remark = $_POST['p_cost_remark'];
            $exclusive_to = $_POST['p_exclusive_to'];
            $photos = isset($rtn['photo'])?$rtn['photo']:'';
            if($photos != ''){
                $temp = explode('.', $photos);
            }
            $sample_order_no = $_POST['p_sample_order_no'];
            $show_in_catalog = isset($_POST['p_show_in_catalog'])?1:0;


            //判断是否输入的pid已存在，因为存在的话由于数据库限制，就会新增失败
            $judge = $mysql->q('select pid from product where pid = ?', $pid);
            if(!$judge){
                $result = $mysql->q('insert into product (pid, type, in_date, cat_num, description, description_chi, sid, scode, ccode, cost_rmb, cost_remark, exclusive_to, photos, sample_order_no, show_in_catalog) values ('.moreQm(15).')', $pid, $type, $in_date, $cat_num, $description, $description_chi, $sid, $scode, $ccode, $cost_rmb, $cost_remark, $exclusive_to, $photos, $sample_order_no, $show_in_catalog);
                //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                if($result !== false){
                    //insert product 成功后才copy图片到sys的目录下
                    if($rtn['photo'] != '' && file_exists(iconv('UTF-8','GBK',
                        '../fty/'.$pic_path_fty_bom . $rtn['photo']))){
                        copy(iconv('UTF-8','GBK', '../fty/'.$pic_path_fty_bom . $rtn['photo']), iconv('UTF-8','GBK', 'upload/lux/'.$photos));
                    }

                    //加delete
                    //$mysql->q('update bom set bom_isin = 1 where g_id = ?', $rtn['g_id']);

                    $myerror->ok('新增产品资料 成功!', 'com-fty_searchbom&page='.$_GET['page']);
                }else{
                    $myerror->error('由于系统原因，新增产品资料 失败', 'com-fty_searchbom&page='.$_GET['page']);
                }
            }else{
                $myerror->error('Product ID ('.$pid.') already exist, add product failure!', 'com-fty_searchbom&page='.$_GET['page']);
            }
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
        <legend class='legend'>Insert Product</legend>
        <fieldset>
            <legend class='legend'>1.Upload image</legend>
            <?
            if (is_file('../fty/' . $pic_path_fty_bom . $rtn['photo']) == true) {
                $arr = getimagesize('../fty/' . $pic_path_fty_bom . $rtn['photo']);
                $pic_width = $arr[0];
                $pic_height = $arr[1];
                $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/fty/' . $pic_path_fty_bom . $rtn['photo'].'" class="tooltip2" target="_blank" title="'.$rtn['photo'].'"><img src="/fty/' . $pic_path_fty_bom . $rtn['photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
            }else{
                echo '<div style="margin-left:28px;"><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/><br />';
            }


            /*
            <div class="line"></div>

            <img src="<?= (isset($Pic) ? $Pic : '../images/nopic.gif')?>" id="p_pkPic" border="0" height="70">
            <input name="pkPic" type="hidden" id="pkPic" value="<?=$Pic?>">
            <div class="line"></div>
            <iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="500" id="titleimg_up" frameborder="0"></iframe>
            */

            ?>
        </fieldset>
        <fieldset>
            <legend class='legend'>2.Fill the form</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><? $goodsForm->show('p_pid');?></td>
                    <td width="25%"><? $goodsForm->show('p_cat_num');?></td>
                    <td width="25%"><? $goodsForm->show('p_type');?></td>
                    <td width="25%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2"><? $goodsForm->show('p_description');?></td>
                    <td colspan="2"><? $goodsForm->show('p_description_chi');?></td>
                </tr>
                <tr>
                    <td width="25%"><? $goodsForm->show('p_sid');?></td>
                    <td width="25%"><? $goodsForm->show('p_scode');?></td>
                    <td width="25%"><? $goodsForm->show('p_cost_rmb');?></td>
                    <td width="25%"><? $goodsForm->show('p_cost_remark');?></td>
                </tr>
                <tr>
                    <td width="25%"><? $goodsForm->show('p_ccode');?></td>
                    <td width="25%"><? $goodsForm->show('p_exclusive_to');?></td>
                    <td width="25%"><? $goodsForm->show('p_sample_order_no');?></td>
                    <td width="25%"><? $goodsForm->show('p_show_in_catalog');?></td>
                </tr>
            </table>
            <div class="line"></div>

            <?
            $goodsForm->show('submitbtn');
            ?>
        </fieldset>
    </fieldset>

    <?
    $goodsForm->end();

}
?>