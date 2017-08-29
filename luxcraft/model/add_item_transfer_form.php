<?php

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    $goodsForm = new My_Forms();
    $formItems = array(

        'transfer_from' => array('title' => 'Transfer From', 'type' => 'select', 'options' => get_warehouse_info(1), 'required' => 1),
        //转移到新的仓库
        'transfer_to' => array('title' => 'Transfer To', 'type' => 'select', 'options' => get_warehouse_info(1), 'required' => 1),
        'estimated_arrival_date' => array('title' => 'Estimated Arrival Date', 'type' => 'text', 'restrict' => 'date', 'value' => date('Y-m-d', strtotime(dateMore()))),
        'remark' => array('title' => 'Remark','type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:400px"'),

        'q_pid' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'addon' => 'onblur="itf_pid_blur(this)"'),
        'q_p_quantity' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20),

        'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
    );

    $goodsForm->init($formItems);

    if(!$myerror->getAny() && $goodsForm->check()){

        $itf_no = autoGenerationID();

        $tran_from_wh = explode('|', $_POST['transfer_from']);
        $tran_from_wh_id = '';
        $tran_from_wh_name = '';
        if(!empty($tran_from_wh)){
            $tran_from_wh_id = $tran_from_wh[0];
            $tran_from_wh_name = $tran_from_wh[1];
        }

        $tran_to_wh = explode('|', $_POST['transfer_to']);
        $tran_to_wh_id = '';
        $tran_to_wh_name = '';
        if(!empty($tran_to_wh)){
            $tran_to_wh_id = $tran_to_wh[0];
            $tran_to_wh_name = $tran_to_wh[1];
        }

        if($tran_from_wh_id != $tran_to_wh_id){

            $estimated_arrival_date = $_POST['estimated_arrival_date'];
            $today = dateMore();
            $staff = $_SESSION['luxcraftlogininfo']['aName'];
            $remark = $_POST['remark'];

            //******
            //第一个post的是form的标识串，还有4个表单项，所以是5
            $prev_num = 5;
            //后面的post，有个submit
            $last_num = 0;

            $i = 0;
            $itf_item = array();
            foreach( $_POST as $v){
                if( $i < $prev_num){
                    $i++;
                }elseif($i >= count($_POST) - $last_num){
                    break;
                }else{
                    $itf_item[] = $v;
                    $i++;
                }
            }
            //这个是设置每个ITEM的元素个数
            $each_item_num = 2;
            $itf_item_num = intval(count($itf_item)/$each_item_num);
            //******

            //因为前端没有require的限制了，所以后端要限制，有填写item才能继续
            if($itf_item_num >= 1){
                $itf_pid = array();
                $itf_t_qty = array();

                $index = 0;

                for($j = 0; $j < $itf_item_num; $j++){
                    $itf_pid[] = trim($itf_item[$index++]);
                    $itf_t_qty[] = trim($itf_item[$index++]);
                }

                //20141109 暂时都不记录日志
                //记录 warehouse_transfer_item_form
                $rs = $mysql->q('insert into warehouse_item_transfer_form values (NULL, '.moreQm(12).')', $itf_no,
                    $tran_from_wh_id, $tran_from_wh_name, $tran_to_wh_id, $tran_to_wh_name, $estimated_arrival_date, $remark,
                    '(I)' ,$today, '', $staff, '');
                $itf_id = $mysql->id();

                for($j = 0; $j < $itf_item_num; $j++){

                    //transfer from的item总量减少
                    $rs = $mysql->q("update warehouse_item_unique set qty = qty - ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $itf_t_qty[$j], $today, $staff, $tran_from_wh_id, $itf_pid[$j]);
                    //transfer to的item总量增加
                    $rs = $mysql->q("update warehouse_item_unique set qty = qty + ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $itf_t_qty[$j], $today, $staff, $tran_to_wh_id, $itf_pid[$j]);

                    //获取product item信息
                    $item_info = $mysql->qone('select cost_rmb, photos from product where pid = ?', $itf_pid[$j]);

                    //记录hist表
                    $rs = $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', $itf_id, $tran_from_wh_id,
                        $tran_from_wh_name, $itf_pid[$j], '-', $item_info['cost_rmb'], $itf_t_qty[$j], $item_info['photos'], $estimated_arrival_date, $today, $staff, $remark);
                    $rs = $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', $itf_id, $tran_to_wh_id, $tran_to_wh_name, $itf_pid[$j], '+', $item_info['cost_rmb'], $itf_t_qty[$j], $item_info['photos'], $estimated_arrival_date, $today, $staff, $remark);

                    //记录 warehouse_transfer_item_form_detail
                    $rs = $rs = $mysql->q('insert into warehouse_item_transfer_form_detail values (NULL, '.moreQm(3).')',
                        $itf_no, $itf_pid[$j], $itf_t_qty[$j]);
                }

                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['luxcraftlogininfo']['aID'], $ip_real
                    , ACTION_LOG_SYS_ADD_ITEM_TRANSFER_FORM, $_SESSION["luxcraftlogininfo"]["aName"]." <i>add item transfer form</i> '".$itf_id."' in sys", ACTION_LOG_SYS_ADD_ITEM_TRANSFER_FORM_S, "", "", 0);
                $myerror->ok('Transfer success !', 'com-search_item_transfer_form&page=1');

//旧的
                /*
                        if($qty <= $_POST['stock']){
                            //总数量减少
                            $rs = $mysql->q('update warehouse_item_unique set qty = qty - ?, arrival_date = ?, mod_date = ?, mod_by = ?, remark = ? where id = ?', $qty, $arrival_date, $mod_date, $mod_by, $remark, $_GET['outid']);
                            if($rs){
                                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." update warehouse ".$wh_name." item ".$pid." - ".$qty." success(4)", WAREHOUSE_ITEM_UPDATE_SUCCESS, "", "", 0);
                                $result = $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', $wh_id, $wh_name, 1, $pid, '-', $cost_rmb, $qty, $mod_result['photo'], $arrival_date, dateMore(), $_SESSION["luxcraftlogininfo"]["aName"], $remark);
                                if($result){
                                    $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." insert warehouse ".$wh_name." item log - ".$qty.' '.$pid." success(4)", WAREHOUSE_ITEM_LOG_INSERT_SUCCESS, "", "", 0);
                                }else{
                                    $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." insert warehouse ".$wh_name." item log - ".$qty.' '.$pid." failure(4)", WAREHOUSE_ITEM_LOG_INSERT_FAILURE, "", "", 0);
                                }

                                $rtn_transfer_to_wh = $mysql->qone('select id from warehouse_item_unique where wh_id = ? and pid = ?', $tran_to_wh_id, $pid);
                                if($rtn_transfer_to_wh){
                                    //transfer to wh 总数量增加
                                    $rs = $mysql->q('update warehouse_item_unique set qty = qty + ?, arrival_date = ?, mod_date = ?, mod_by = ?, remark = ? where id = ?', $qty, $arrival_date, $mod_date, $mod_by, $remark, $rtn_transfer_to_wh['id']);
                                    if($rs){
                                        $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." update warehouse ".$tran_to_wh_name." item ".$pid." + ".$qty." success(4)", WAREHOUSE_ITEM_UPDATE_SUCCESS, "", "", 0);
                                        $result = $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', $tran_to_wh_id, $tran_to_wh_name, 1, $pid, '+', $cost_rmb, $qty, $mod_result['photo'], $arrival_date, dateMore(), $_SESSION["luxcraftlogininfo"]["aName"], $remark);
                                        if($result){
                                            $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." insert warehouse ".$tran_to_wh_name." item log + ".$qty.' '.$pid." success(4)", WAREHOUSE_ITEM_LOG_INSERT_SUCCESS, "", "", 0);
                                        }else{
                                            $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." insert warehouse ".$tran_to_wh_name." item log + ".$qty.' '.$pid." failure(4)", WAREHOUSE_ITEM_LOG_INSERT_FAILURE, "", "", 0);
                                        }
                                        $myerror->ok('Transfer Warehouse Item success !', 'com-search_warehouse_item_unique&page=1&wh_name='.$wh_name);
                                    }else{
                                        $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." update warehouse ".$tran_to_wh_name." item ".$pid." + ".$qty." failure(4)", WAREHOUSE_ITEM_UPDATE_FAILURE, "", "", 0);
                                        $myerror->error('Transfer Warehouse item failure !(0)', 'BACK');
                                    }
                                }else{
                                    $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." find warehouse ".$wh_name." item ".$pid." failure(4)", WAREHOUSE_ITEM_UPDATE_FAILURE, "", "", 0);
                                    $myerror->error('Transfer Warehouse item failure !(1)', 'BACK');
                                }
                            }else{
                                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." update warehouse ".$wh_name." item ".$pid." - ".$qty." failure(4)", WAREHOUSE_ITEM_UPDATE_FAILURE, "", "", 0);
                                $myerror->error('Transfer Warehouse Item failure !(2)', 'BACK');
                            }
                        }else{
                            $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, WAREHOUSE_LOG_TYPE, $mod_by." transfer warehouse ".$wh_name." item ".$pid." ".$qty." to warehouse ".$tran_to_wh_name." failure (transfer qty must less than stock)", WAREHOUSE_ITEM_UPDATE_FAILURE, "", "", 0);
                            $myerror->error('Qty must less than Stock !', 'BACK');
                        }
                */
            }else{
                $myerror->error('Item none !', 'BACK');
            }
        }else{
            $myerror->error('Do not transfer to the same warehouse !', 'BACK');
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
        <h1 class="green">Item Transfer Form<em>* item must be filled in</em></h1>
        <fieldset class="center2col">
            <legend class='legend'>Add Item Transfer Form</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><div class="set"><label class="formtitle">Item Transfer Form NO.</label><br />(autogeneration)</div></td>
                    <td width="25%"><? $goodsForm->show('transfer_from');?></td>
                    <td width="25%"><? $goodsForm->show('transfer_to');?></td>
                    <td width="25%"></td>
                </tr>
                <tr>
                    <td width="25%" valign="top"><? $goodsForm->show('estimated_arrival_date');?></td>
                    <td width="25%" colspan="2"><? $goodsForm->show('remark');?></td>
                    <td width="25%" valign="top"></td>
                </tr>
            </table>

            <div class="line"></div>
            <br />
            <div style="margin-left:28px;">
                <label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>
                <table width="100%" id="tableDnD">
                    <tbody id="tbody">
                    <tr class="formtitle nodrop nodrag">
                        <td width="25%">Product ID</td>
                        <td width="10%">Photo</td>
                        <td width="25%">Transfer Quantity</td>
                        <td width="10%">&nbsp;</td>
                        <td width="10%">&nbsp;</td>
                        <td width="20%">&nbsp;</td>
                    </tr>
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                        <td><? $goodsForm->show('q_pid');?></td>
                        <td></td>
                        <td><? $goodsForm->show('q_p_quantity');?></td>
                        <td><img title="添加" style="opacity: 0.5;" onclick="addTransferItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"></td>
                        <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delTransferItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </div>
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
            SearchWarehousePid('');//参数要加''，否则不行
            if(document.getElementById("q_pid").value){
                itf_pid_blur(document.getElementById("q_pid"));
            }
        });
    </script>
<?
}