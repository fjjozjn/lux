<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

judgeUserPerm( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{

    //delete 先去掉了，先在不需要
    /*if(isset($_GET['delid']) && $_GET['delid'] != ''){
        $rtn = $mysql->q('delete from warehouse_item where id = ?', $_GET['delid']);
        if($rtn){
            $myerror->ok('Delete Warehouse Item success !', 'com-search_warehouse_item_unique&page=1&wh_name='.$rtn_warehouse_item['wh_name']);
        }else{
            $myerror->error('Delete Warehouse Item failure !', 'BACK');
        }
    }else{*/
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM warehouse_item_unique WHERE id = ?', $_GET['modid']);

            $photo_string = '';
            if (is_file($pic_path_com . $mod_result['photo']) == true) {
                $arr = getimagesize($pic_path_com . $mod_result['photo']);
                $pic_width = $arr[0];
                $pic_height = $arr[1];
                $image_size = getimgsize(80, 60, $pic_width, $pic_height);
                //$photo_string = '<a href="/sys/'.$pic_path_com . $mod_result['photo'].'" target="_blank" title="'.$mod_result['photo'].'"><img src="/sys/'.$pic_path_com . $mod_result['photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';

                $photo_string = '<div class="shadow" style="margin-left:8px;"><ul><li><a href="/sys/'.$pic_path_com . $mod_result['photo'].'" class="tooltip2" target="_blank" title="'.$mod_result['photo'].'"><img src="/sys/'.$pic_path_com . $mod_result['photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul></div>';
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
            'qty' => array('title' => 'Qty', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['qty'])?$mod_result['qty']:''),
            //显示库存量
            //20130720 修改库存数量，不需要再显示库存了
            //'stock' => array('title' => 'Stock', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['qty'])?$mod_result['qty']:'', 'readonly' => 'readonly'),
            //20130720 不需要显示成本价给
            //'cost_rmb' => array('title' => 'Cost (RMB)', 'type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'value' => isset($mod_result['cost_rmb'])?$mod_result['cost_rmb']:'', 'readonly' => 'readonly'),
            'arrival_date' => array('title' => 'Arrival Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['arrival_date'])?date('Y-m-d', strtotime($mod_result['arrival_date'])):''),
            'remark' => array('title' => 'Remark','type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:200px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){

            $wh_id = $mod_result['wh_id'];
            $wh_name = $mod_result['wh_name'];
            $pid = $_POST['q_pid'];
            $qty = $_POST['qty'];
            //$cost_rmb = $_POST['cost_rmb'];
            $arrival_date = $_POST['arrival_date'];
            $mod_date = dateMore();
            $mod_by = $_SESSION['logininfo']['aName'];
            $remark = $_POST['remark'];

            //if($qty <= $_POST['stock']){
                $result = $mysql->q('update warehouse_item_unique set qty = ?, arrival_date = ?, mod_date = ?, mod_by = ?, remark = ? where id = ?', $qty, $arrival_date, $mod_date, $mod_by, $remark, $_GET['modid']);
                if($result){
                    $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." update warehouse ".$wh_name." item ".$pid." from ".$mod_result['qty']." change to ".$qty." success(5)", WAREHOUSE_ITEM_UPDATE_SUCCESS, "", "", 0);
                    $myerror->ok('Modify Warehouse Item success !', 'com-search_warehouse_item_unique&page=1&wh_name='.$wh_name);
                }else{
                    $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." update warehouse ".$wh_name." item ".$pid." from ".$mod_result['qty']." change to ".$qty." failure(5)", WAREHOUSE_ITEM_UPDATE_FAILURE, "", "", 0);
                    $myerror->error('Modify Warehouse Item failure !', 'BACK');
                }
            /*}else{
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." update warehouse ".$wh_name." item ".$pid." from ".$mod_result['qty']." change to ".$qty." failure(5) (qty must less than stock)", WAREHOUSE_ITEM_UPDATE_FAILURE, "", "", 0);
                $myerror->error('Qty must less than Stock !', 'BACK');
            }*/
        }
    //}


    if($myerror->getError()){
        require_once(ROOT_DIR.'model/inside_error.php');
    }elseif($myerror->getOk()){
        require_once(ROOT_DIR.'model/inside_ok.php');
    }else{

        if($myerror->getWarn()){
            require_once(ROOT_DIR.'model/inside_warn.php');
        }
        ?>
        <h1 class="green">Warehouse Item<em>* item must be filled in</em></h1>

        <fieldset style="width:50%">
            <legend class='legend'>Modify Warehouse Item</legend>
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
<!--                    <td width="33%">--><?// $goodsForm->show('stock');?><!--</td>-->
                </tr>
                <tr valign="top">
<!--                    <td width="33%">--><?// $goodsForm->show('cost_rmb');?><!--</td>-->
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
        ?>
        <script>
            $(function(){
                SearchPid('');//参数要加''，否则不行
            });
        </script>
<?
    }
}
?>
