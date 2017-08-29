<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(

    'wh_id' => array('title' => 'Warehouse Name', 'type' => 'select', 'options' => get_warehouse_info(1), 'required' => 1),
    'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'style="width:150px" onblur="wh_pid_blur(this)"', 'nostar' => true),
    'description_chi' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2,'addon' => 'style="width:200px"', 'readonly' => 'readonly'),
    'qty' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'style="width:80px" onblur="whQtyBlur(this)"', 'nostar' => true),
    //20130720 不需要显示成本价给
    //'cost_rmb' => array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'style="width:80px"', 'nostar' => true/*, 'readonly' => 'readonly'*/),
    'arrival_date' => array('type' => 'text', 'restrict' => 'date', 'addon' => 'style="width:100px"',
        'value' => date('Y-m-d')),
    'remark' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:200px"'),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){

    //die();

//fb($_POST);die();

    $wh = explode('|', $_POST['wh_id']);
    $wh_id = '';
    $wh_name = '';
    if(!empty($wh)){
        $wh_id = $wh[0];
        $wh_name = $wh[1];
    }

    $i = 0;
    $prev_num = 2;//第一个post的是form的标识串，还有1个表单项
    $last_num = 1;//后面的post，有个submit
    $wh_item = array();
    foreach( $_POST as $v){
        if( $i < $prev_num){
            $i++;
        }elseif($i >= count($_POST) - $last_num){
            break;
        }else{
            $wh_item[] = $v;
            $i++;
        }
    }
//fb($wh_item);
    $in_date = dateMore();
    $created_by = $_SESSION["logininfo"]["aName"];

    //这个是设置每个ITEM的元素个数
    $each_item_num = 6;
    $wh_item_num = intval(count($wh_item)/$each_item_num);

    $q_pid = array();
    $description_chi = array();
    $qty = array();
    //$cost_rmb = array();
    $arrival_date = array();
    $remark = array();
    $pt_input = array();

    $index = 0;

    for($j = 0; $j < $wh_item_num; $j++){
        $q_pid[] = $wh_item[$index++];
        $description_chi[] = $wh_item[$index++];
        $qty[] = $wh_item[$index++];
        //$cost_rmb[] = str_replace(',', '', ($wh_item[$index] != '')?$wh_item[$index++]:0);
        $arrival_date[] = $wh_item[$index++];
        $remark[] = $wh_item[$index++];
        $pt_input[] = $wh_item[$index++];
    }

/*fb($q_pid);
fb($description_chi);
fb($qty);
//fb($cost_rmb);
fb($arrival_date);
fb($remark);
fb($pt_input);
die();*/

    $success_num = 0;

    for($k = 0; $k < $wh_item_num; $k++){
        //20130620 以后search都显示mod_date 和 mod_by
        //$result = $mysql->q('insert into warehouse_item values (NULL, '.moreQm(14).')', $wh_id, $wh_name, $q_pid[$k], '', '', $qty[$k], $cost_rmb[$k], $pt_input[$k], $arrival_date[$k], $in_date, dateMore(), $created_by, $_SESSION["logininfo"]["aName"], $remark[$k]);

        //20130620 加 warehouse_item_unique 表，将相同的item数量合并起来，只显示一条记录
        //20130624 之前忘了加wh_id的查询条件了，同一个pid在不同的wh是允许的
        $rs = $mysql->qone('select id from warehouse_item_unique where pid = ? and wh_id = ? and wh_name = ?', $q_pid[$k], $wh_id, $wh_name);
        $wh_rs = false;
        if($rs){
            $wh_rs = $mysql->q('update warehouse_item_unique set qty = qty + ?, photo = ?, arrival_date = ?, mod_date = ?, mod_by = ?, remark = ? where pid = ? and wh_id = ? and wh_name = ?', $qty[$k], $pt_input[$k], $arrival_date[$k], dateMore(), $_SESSION["logininfo"]["aName"], $remark[$k], $q_pid[$k], $wh_id, $wh_name);
            if($wh_rs){
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $created_by." update warehouse ".$wh_name." item ".$q_pid[$k]." + ".$qty[$k]." success(1)", WAREHOUSE_ITEM_UPDATE_SUCCESS, "", "", 0);
            }else{
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $created_by." update warehouse ".$wh_name." item ".$q_pid[$k]." + ".$qty[$k]." failure(1)", WAREHOUSE_ITEM_UPDATE_FAILURE, "", "", 0);
            }
        }else{
            //因为在 search unique 里显示的是 mod_date 和 mod_by 所以这里在 insert 也要插入这两个值
            $wh_rs = $mysql->q('insert into warehouse_item_unique values (NULL, '.moreQm(14).')', $wh_id, $wh_name, $q_pid[$k], '', $description_chi[$k], $qty[$k], '', $pt_input[$k], $arrival_date[$k], $in_date, dateMore(), $created_by, $_SESSION["logininfo"]["aName"], $remark[$k]);
            if($wh_rs){
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $created_by." insert warehouse ".$wh_name." item ".$q_pid[$k]." + ".$qty[$k]." success(1)", WAREHOUSE_ITEM_INSERT_SUCCESS, "", "", 0);
            }else{
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $created_by." insert warehouse ".$wh_name." item ".$q_pid[$k]." + ".$qty[$k]." failure(1)", WAREHOUSE_ITEM_INSERT_FAILURE, "", "", 0);
            }
        }

        //如果对 unique 表操作成功，还要向item_log表插入一条记录， 对 unique 表操作失败，也就没必要在item_log表记录了
        if($wh_rs){
            $success_num++;
            //20130701 废弃了warehouse_item表，改为记录warehouse_item_log表
            $result = $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', $wh_id, $wh_name, 1, $q_pid[$k], '+', '', $qty[$k], $pt_input[$k], $arrival_date[$k], dateMore(), $_SESSION["logininfo"]["aName"], $remark[$k]);
            if($result){
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $created_by." insert warehouse ".$wh_name." item log + ".$qty[$k].' '.$q_pid[$k]." success(1)", WAREHOUSE_ITEM_LOG_INSERT_SUCCESS, "", "", 0);
            }else{
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $created_by." insert warehouse ".$wh_name." item log + ".$qty[$k].' '.$q_pid[$k]." failure(2)", WAREHOUSE_ITEM_LOG_INSERT_FAILURE, "", "", 0);
            }
        }
    }

    if($success_num == $wh_item_num){
        //add log
        $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $created_by." add warehouse ".$wh_name." ".$success_num." kinds of items success(1)", WAREHOUSE_ITEM_ADD_SUCCESS, "", "", 0);
        $myerror->ok('Add Warehouse Item success !', 'com-search_warehouse_item_unique&page=1&wh_name='.$wh_name);
    }elseif($success_num == 0){
        //add log
        $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $created_by." add warehouse ".$wh_name." all kinds of items failure(1)", WAREHOUSE_ITEM_ADD_FAILURE, "", "", 0);
        $myerror->error('Add all Warehouse Item failure !', 'BACK');
    }else{
        //add log
        $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $created_by." add warehouse ".$wh_name." (".$success_num.'/'.$wh_item_num.") kinds of items success, other failure(1)", WAREHOUSE_ITEM_ADD_FAILURE, "", "", 0);
        $myerror->warn('Add Warehouse Item success! But ('.$success_num.'/'.$wh_item_num.') !', 'BACK');
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
    <h1 class="green">Warehouse Item<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>Add Warehouse Item</legend>

        <?php
        $goodsForm->begin();
        ?>
        <table width="100%">
            <tr>
                <td><? $goodsForm->show('wh_id');?></td>
            </tr>
        </table>
        <div class="line"></div>
        <br />
        <div style="margin-left:28px; color: #F00"># 输入 Product ID 后焦点离开输入框则查询产品资料自动填上，按+按钮是新增一行</div>
        <div style="margin-left:28px;">
            <!--            <label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>-->
            <table width="100%" id="tableDnD">
                <tbody id="tbody">
                <tr class="formtitle nodrop nodrag">
                    <!--                    <td width="3%"></td>-->
                    <td width="15%">Product ID</td>
                    <td width="21%">Description(Chi)</td>
                    <td width="10%">Quantity</td>
<!--                    <td width="10%">Cost(RMB)</td>-->
<!--                    <td width="8%">Subtotal</td>-->
                    <td width="12%">Arrival Date</td>
                    <td width="8%">Photo</td>
                    <td width="21%">Remark</td>
                    <td width="6%">&nbsp;</td>
                    <td width="6%">&nbsp;</td>
                    <td width="4%">&nbsp;</td>
                </tr>
                <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                    <!--                    <td class="dragHandle"></td>-->
                    <td><? $goodsForm->show('q_pid');?></td>
                    <td><? $goodsForm->show('description_chi');?></td>
                    <td><? $goodsForm->show('qty');?></td>
<!--                    <td>--><?// $goodsForm->show('cost_rmb');?><!--</td>-->
<!--                    <td>&nbsp;</td>-->
                    <td><? $goodsForm->show('arrival_date');?></td>
                    <td>&nbsp;</td>
                    <td><? $goodsForm->show('remark');?></td>
                    <td><img title="添加" style="opacity: 0.5;" onclick="addWarehouseItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"></td>
                    <!--                    <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delWarehouseItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td>-->
                    <td>&nbsp;</td>
                    <td><input type="hidden" name="pt_input" id="pt_input" value="" /></td>
                </tr>
                </tbody>
                <tr>
                    <!--                    <td>&nbsp;</td>-->
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </div>
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
?>

