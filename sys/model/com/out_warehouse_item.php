<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

judgeUserPerm( (isset($_GET['outid'])?$_GET['outid']:'') );

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{

    if( isset($_GET['outid']) && $_GET['outid'] != '' ){
        $mod_result = $mysql->qone('SELECT * FROM warehouse_item_unique WHERE id = ?', $_GET['outid']);

        $photo_string = '';
        if (is_file($pic_path_com . $mod_result['photo']) == true) {
            $arr = getimagesize($pic_path_com . $mod_result['photo']);
            $pic_width = $arr[0];
            $pic_height = $arr[1];
            $image_size = getimgsize(80, 60, $pic_width, $pic_height);
            $photo_string = '<a href="/sys/'.$pic_path_com . $mod_result['photo'].'" target="_blank" title="'.$mod_result['photo'].'"><img src="/sys/'.$pic_path_com . $mod_result['photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
        }else{
            $photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60'>";
        }
    }else{
        die('Need modid!');
    }

    $goodsForm = new My_Forms();
    $formItems = array(

        'wh_id' => array('title' => 'Warehouse Name', 'type' => 'text', 'value' => isset($mod_result['wh_id'])?$mod_result['wh_name']:'', 'readonly' => 'readonly'),
        'q_pid' => array('title' => 'Product ID', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['pid'])?$mod_result['pid']:'', 'readonly' => 'readonly'),
        //qty 用户自己填
        'qty' => array('title' => 'Qty', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => ''),
        //显示库存量
        'stock' => array('title' => 'Stock', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['qty'])?$mod_result['qty']:'', 'readonly' => 'readonly'),
        'cost_rmb' => array('title' => 'Cost (RMB)', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['cost_rmb'])?$mod_result['cost_rmb']:'', 'readonly' => 'readonly'),
        'arrival_date' => array('title' => 'Arrival Date', 'type' => 'text', 'restrict' => 'date', 'value' => date('Y-m-d', strtotime(dateMore()))),
        'remark' => array('title' => 'Remark','type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:200px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),

        'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
    );

    $goodsForm->init($formItems);

    if(!$myerror->getAny() && $goodsForm->check()){

        $wh_id = $mod_result['wh_id'];
        $wh_name = $mod_result['wh_name'];
        $pid = $_POST['q_pid'];
        $qty = $_POST['qty'];
        $cost_rmb = $_POST['cost_rmb'];
        $arrival_date = $_POST['arrival_date'];
        $mod_date = dateMore();
        $mod_by = $_SESSION['logininfo']['aName'];
        $remark = $_POST['remark'];

        if($qty <= $_POST['stock']){
            //总数量减少
            $rs = $mysql->q('update warehouse_item_unique set qty = qty - ?, arrival_date = ?, mod_date = ?, mod_by = ?, remark = ? where id = ?', $qty, $arrival_date, $mod_date, $mod_by, $remark, $_GET['outid']);
            if($rs){
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." update warehouse <i>".$wh_name."</i> item <i>- ".$qty.' '.$pid."</i> <i>success(3)</i>", WAREHOUSE_ITEM_UPDATE_SUCCESS, "", "", 0);
                $result = $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', $wh_id, $wh_name, 1, $pid, '-', $cost_rmb, $qty, $mod_result['photo'], $arrival_date, dateMore(), $_SESSION["logininfo"]["aName"], $remark);
                if($result){
                    $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." insert warehouse <i>".$wh_name."</i> item <i>log - ".$qty.' '.$pid."</i> <i>success(3)</i>", WAREHOUSE_ITEM_LOG_INSERT_SUCCESS, "", "", 0);
                }else{
                    $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." insert warehouse <i>".$wh_name."</i> item <i>log - ".$qty.' '.$pid."</i> <i>failure(3)</i>", WAREHOUSE_ITEM_LOG_INSERT_FAILURE, "", "", 0);
                }

                $myerror->ok('Modify Warehouse Item success !', 'com-search_warehouse_item_unique&page=1&wh_name='.$wh_name);
            }else{
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." update warehouse <i>".$wh_name."</i> item <i>+ ".$qty.' '.$q_pid."</i> <i>failure(3)</i>", WAREHOUSE_ITEM_UPDATE_FAILURE, "", "", 0);
                $myerror->error('Modify Warehouse Item failure !', 'BACK');
            }
        }else{
            $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." update warehouse <i>".$wh_name."</i> item <i>+ ".$qty.' '.$q_pid."</i> <i>failure (qty must less than stock)</i>", WAREHOUSE_ITEM_UPDATE_FAILURE, "", "", 0);
            $myerror->error('Qty must less than Stock !', 'BACK');
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
        <h1 class="green">Out Warehouse Item<em>* item must be filled in</em></h1>

        <fieldset class="center2col" style="width:60%">
            <legend class='legend'>Out Warehouse Item</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%">
                <tr>
                    <td><div style="padding-left: 28px;"><?php echo $photo_string; ?></div></td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td width="33%"><? $goodsForm->show('wh_id');?></td>
                    <td width="33%"><? $goodsForm->show('q_pid');?></td>
                </tr>
                <tr>
                    <td width="33%"><? $goodsForm->show('qty');?></td>
                    <td width="33%"><? $goodsForm->show('stock');?></td>
                </tr>
                <tr valign="top">
                    <td width="33%"><? $goodsForm->show('cost_rmb');?></td>
                    <td width="33%"><? $goodsForm->show('arrival_date');?></td>
                    <td width="33%"><? $goodsForm->show('remark');?></td>
                </tr>
            </table>
            <br />
            <div class="line"></div>
            <?
            $goodsForm->show('submitbtn');
            ?>
        </fieldset>
        <?
        $goodsForm->end();
    }
}

