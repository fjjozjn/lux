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

    if(isset($_GET['delid']) && $_GET['delid'] != ''){

        //删除的话，warehouse_item_unique 和 warehouse_item_hist 这两个表都需要更新
        $del_result = $mysql->qone('SELECT * FROM warehouse_item_transfer_form WHERE id = ?', $_GET['modid']);

        $del_item_result = $mysql->q('SELECT pid, qty FROM warehouse_item_transfer_form_detail WHERE trans_form_id = ?', $del_result['trans_form_id']);
        $del_itf_item_rtn = $mysql->fetch();
        $del_itf_item_num = count($del_itf_item_rtn);

        $today = dateMore();
        $staff = $_SESSION['luxcraftlogininfo']['aName'];

        //删除warehouse_item_hist表相关内容
        $mysql->q('delete from warehouse_item_hist where item_transfer_form_id = ?', $_GET['delid']);
        //更新warehouse_item_unique表相关内容
        for($j = 0; $j < $del_itf_item_num; $j++){
            //transfer from的item总量增加
            $mysql->q("update warehouse_item_unique set qty = qty + ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $del_itf_item_rtn[$j]['qty'], $today, $staff, $tran_from_wh_id, $del_itf_item_rtn[$j]['pid']);
            //transfer to的item总量减少
            $mysql->q("update warehouse_item_unique set qty = qty - ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $del_itf_item_rtn[$j]['qty'], $today, $staff, $tran_to_wh_id, $del_itf_item_rtn[$j]['pid']);
        }

        //由於指定了foreign key，所以要先刪item裏的內容
        $rtn1 = $mysql->q('delete from warehouse_item_transfer_form_detail where id = ?', $_GET['delid']);
        $rtn2 = $mysql->q('delete from warehouse_item_transfer_form where id = ?', $_GET['delid']);
        if($rtn2){
            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['luxcraftlogininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_ITEM_TRANSFER_FORM, $_SESSION["luxcraftlogininfo"]["aName"]." <i>delete item transfer form</i> '".$_GET['delid']."' success in sys", ACTION_LOG_SYS_DEL_ITEM_TRANSFER_FORM_S, "", "", 0);

            $myerror->ok('Delete Item Transfer Form success !', 'com-search_item_transfer_form&page=1');
        }else{

            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['luxcraftlogininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_ITEM_TRANSFER_FORM, $_SESSION["luxcraftlogininfo"]["aName"]." <i>delete item transfer form</i> '".$_GET['delid']."' failure in sys", ACTION_LOG_SYS_DEL_ITEM_TRANSFER_FORM_F, "", "", 0);

            $myerror->error('Delete Item Transfer Form failure !', 'com-search_item_transfer_form&page=1');
        }
    }elseif(isset($_GET['approve_itf_no']) && $_GET['approve_itf_no'] != ''){
        $rtn = $mysql->qone('select istatus from warehouse_item_transfer_form where id = ?', $_GET['approve_itf_no']);
        if($rtn['istatus'] == '(D)'){
            if(isSysAdmin()){
                $rs = $mysql->q('update warehouse_item_transfer_form set istatus = ? where id = ?', '(I)', $_GET['approve_itf_no']);
                if($rs){
                    $myerror->ok('Status change from (D) to (I) !', 'com-search_item_transfer_form&page=1');
                }
            }else{
                $myerror->error('Without Permission To Access', 'main');
            }
        }elseif($rtn['istatus'] == '(I)'){
            //mod 普通用户不能把已核批的状态改回为未核批
            if(isSysAdmin()){
                $rs = $mysql->q('update warehouse_item_transfer_form set istatus = ? where id = ?', '(D)', $_GET['approve_itf_no']);
                if($rs){
                    $myerror->ok('Status change from (I) to (D) !', 'com-search_item_transfer_form&page=1');
                }
            }else{
                $myerror->error('Without Permission To Access', 'main');
            }
        }else{
            $myerror->error('Status error !', 'com-search_item_transfer_form&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM warehouse_item_transfer_form WHERE id = ?', $_GET['modid']);

            $item_result = $mysql->q('SELECT pid, qty FROM warehouse_item_transfer_form_detail WHERE trans_form_id = ?', $mod_result['trans_form_id']);
            $itf_item_rtn = $mysql->fetch();
            for($i = 0; $i < count($itf_item_rtn); $i++){
                $product = $mysql->qone('select photos from product where pid = ?', $itf_item_rtn[$i]['pid']);
                if (is_file($pic_path_com . $product['photos']) == true) {
                    $arr = getimagesize($pic_path_com . $product['photos']);
                    $pic_width = $arr[0];
                    $pic_height = $arr[1];
                    $image_size = getimgsize(80, 60, $pic_width, $pic_height);
                    $itf_item_rtn[$i]['photo'] = '<a href="/sys/'.$pic_path_com . $product['photos'].'" target="_blank" title="'.$product['photos'].'"><img src="/sys/'.$pic_path_com . $product['photos'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
                }else{
                    $itf_item_rtn[$i]['photo'] = "<img src='../images/nopic.gif' border='0' width='80' height='60'>";
                }
            }
            //$myerror->info($itf_item_rtn);die();
            $itf_item_num = count($itf_item_rtn);
            //$myerror->info($itf_item_num);

        }else{
            die('Need modid!');
        }


        $goodsForm = new My_Forms();
        $formItems = array(

            'trans_form_id' => array('title' => 'Item Transfer Form NO.', 'type' => 'text', 'readonly' => 'readonly', 'value' => isset($mod_result['trans_form_id'])?$mod_result['trans_form_id']:''),
            'transfer_from' => array('title' => 'Transfer From', 'type' => 'select', 'options' => get_warehouse_info(1), 'required' => 1, 'value' => isset($mod_result['trans_from_wh_id'])?($mod_result['trans_from_wh_id'].'|'.$mod_result['trans_from_wh_name']):''),
            //转移到新的仓库
            'transfer_to' => array('title' => 'Transfer To', 'type' => 'select', 'options' => get_warehouse_info(1), 'required' => 1, 'value' => isset($mod_result['trans_to_wh_id'])?($mod_result['trans_to_wh_id'].'|'.$mod_result['trans_to_wh_name']):''),
            'estimated_arrival_date' => array('title' => 'Estimated Arrival Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['estimated_arrival_date'])?date('Y-m-d', strtotime($mod_result['estimated_arrival_date'])):''),
            'remark' => array('title' => 'Remark','type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
        );

        //序号从0开始
        for($i = 0; $i < $itf_item_num; $i++){
            // 把required都去掉了，不然删除item后提交不了
            $formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($itf_item_rtn[$i]['pid'])?$itf_item_rtn[$i]['pid']:'');
            $formItems['qty'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($itf_item_rtn[$i]['qty'])?$itf_item_rtn[$i]['qty']:'');
        }
        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){

            $trans_form_id = $_POST['trans_form_id'];

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
                //第一个post的是form的标识串，还有5个表单项，所以是6（比add多一个trans_form_id）
                $prev_num = 6;
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
                    $itf_t_qty_old = array();//表内旧的数据，被用于下面更新warehouse_item_unique表

                    $index = 0;

                    for($j = 0; $j < $itf_item_num; $j++){

                        $temp = $mysql->qone('select qty from warehouse_item_transfer_form_detail where trans_form_id = ? and pid = ?', $mod_result['trans_form_id'], $itf_item[$index]);
                        $itf_t_qty_old[] = $temp['qty'];

                        $itf_pid[] = trim($itf_item[$index++]);
                        $itf_t_qty[] = trim($itf_item[$index++]);
                    }

                    //20141109 暂时都不记录日志
                    //记录 warehouse_transfer_item_form

                    $rs = $mysql->q('update warehouse_item_transfer_form set trans_from_wh_id = ?, trans_from_wh_name = ?, trans_to_wh_id = ?, trans_to_wh_name = ?, estimated_arrival_date = ?, remark = ?, mod_date = ?, mod_by = ? where id = ?', $tran_from_wh_id, $tran_from_wh_name, $tran_to_wh_id, $tran_to_wh_name, $estimated_arrival_date, $remark, $today, $staff, $_GET['modid']);

                    //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                    if($rs !== false){

                        $mysql->q('delete from warehouse_item_transfer_form_detail where trans_form_id = ?', $mod_result['trans_form_id']);
                        //有修改时hist表的相关记录也要删了重建
                        $mysql->q('delete from warehouse_item_hist where item_transfer_form_id = ?', $_GET['modid']);

                        for($j = 0; $j < $itf_item_num; $j++){

                            //transfer from的item先加上旧的数量
                            $rs = $mysql->q("update warehouse_item_unique set qty = qty + ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $itf_t_qty_old[$j], $today, $staff, $tran_from_wh_id, $itf_pid[$j]);
                            //transfer from的item在减去新的数
                            $rs = $mysql->q("update warehouse_item_unique set qty = qty - ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $itf_t_qty[$j], $today, $staff, $tran_from_wh_id, $itf_pid[$j]);

                            //transfer to的item先减去旧的数量
                            $rs = $mysql->q("update warehouse_item_unique set qty = qty - ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $itf_t_qty_old[$j], $today, $staff, $tran_to_wh_id, $itf_pid[$j]);
                            //transfer to的item再加上新的数
                            $rs = $mysql->q("update warehouse_item_unique set qty = qty + ?, mod_date = ?, mod_by = ? where wh_id = ? and pid = ?", $itf_t_qty[$j], $today, $staff, $tran_to_wh_id, $itf_pid[$j]);


                            //获取product item信息
                            $item_info = $mysql->qone('select cost_rmb, photos from product where pid = ?', $itf_pid[$j]);

                            //记录hist表
                            $rs = $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', $_GET['modid'],$tran_from_wh_id,$tran_from_wh_name, $itf_pid[$j], '-', $item_info['cost_rmb'], $itf_t_qty[$j], $item_info['photos'], $estimated_arrival_date, $today, $staff, $remark);
                            $rs = $mysql->q('insert into warehouse_item_hist values (NULL, '.moreQm(12).')', $_GET['modid'],$tran_to_wh_id, $tran_to_wh_name, $itf_pid[$j], '+', $item_info['cost_rmb'], $itf_t_qty[$j], $item_info['photos'], $estimated_arrival_date, $today, $staff, $remark);

                            //记录 warehouse_transfer_item_form_detail
                            $rs = $rs = $mysql->q('insert into warehouse_item_transfer_form_detail values (NULL, '.moreQm(3).')',
                                $trans_form_id, $itf_pid[$j], $itf_t_qty[$j]);
                        }
                    }

                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['luxcraftlogininfo']['aID'], $ip_real
                        , ACTION_LOG_SYS_MOD_ITEM_TRANSFER_FORM, $_SESSION["luxcraftlogininfo"]["aName"]." <i>modify item transfer form</i> '".$_GET['modid']."' in sys", ACTION_LOG_SYS_MOD_ITEM_TRANSFER_FORM_S, "", "", 0);

                    $myerror->ok('Modify Item Transfer Form success !', 'com-search_item_transfer_form&page=1');
                }else{
                    $myerror->error('Item none !', 'BACK');
                }
            }else{
                $myerror->error('Do not transfer to the same warehouse !', 'BACK');
            }


            /*
            $wh_id = $mod_result['wh_id'];
            $wh_name = $mod_result['wh_name'];
            $pid = $_POST['q_pid'];
            $qty = $_POST['qty'];

            $tran_to_wh = explode('|', $_POST['transfer_to']);
            $tran_to_wh_id = '';
            $tran_to_wh_name = '';
            if(!empty($wh)){
                $tran_to_wh_id = $wh[0];
                $tran_to_wh_name = $wh[1];
            }

            //$cost_rmb = $_POST['cost_rmb'];
            $arrival_date = $_POST['arrival_date'];
            $mod_date = dateMore();
            $mod_by = $_SESSION['luxcraftlogininfo']['aName'];
            $remark = $_POST['remark'];


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
            <legend class='legend'>Modify Item Transfer Form</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><? $goodsForm->show('trans_form_id');?></td>
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
                    <?
                    for($i = 0; $i < $itf_item_num; $i++){
                        ?>
                        <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                            <!--                    <td class="dragHandle"></td>-->
                            <td><? $goodsForm->show('q_pid'.$i);?></td>
                            <td><? echo $itf_item_rtn[$i]['photo'];?></td>
                            <td valign="top"><? $goodsForm->show('qty'.$i);?></td>
                            <td align="left"><img title="添加" style="opacity: 0.5;" onclick="addTransferItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
                            <? if($i != 0){ ?>
                                <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delTransferItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td>
                            <? }?>
                            <td></td>
                            <script>$(function(){SearchWarehousePid('<?=$i?>');});</script>
                        </tr>
                    <?
                    }
                    ?>
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
}

