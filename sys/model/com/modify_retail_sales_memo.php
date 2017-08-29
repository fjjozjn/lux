<?php

/*
change log

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

judgeUserPermNew( (isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['delid'])?$_GET['delid']:'') );

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        //由於指定了foreign key，所以要先刪item裏的內容
        $rtn1 = $mysql->q('delete from retail_sales_memo_item where rsm_id = ?', $_GET['delid']);
        $rtn2 = $mysql->q('delete from retail_sales_memo where rsm_id = ?', $_GET['delid']);
        if($rtn2){
            $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $_SESSION["logininfo"]["aName"]." retail delete ".$_GET['delid']." success(2)", RETAIL_SALES_MEMO_ITEM_UPDATE_SUCCESS, "", "", 0);
            $myerror->ok('Delete Retail Sales Memo success!', 'com-search_retail_sales_memo&page=1');
        }else{
            $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $_SESSION["logininfo"]["aName"]." retail delete ".$_GET['delid']." failure(2)", RETAIL_SALES_MEMO_ITEM_UPDATE_FAILURE, "", "", 0);
            $myerror->error('Delete Retail Sales Memo failure!', 'com-search_retail_sales_memo&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM retail_sales_memo WHERE rsm_id = ?', $_GET['modid']);

            $item_result = $mysql->q('SELECT * FROM retail_sales_memo_item WHERE rsm_id = ?', $_GET['modid']);
            $rsm_item_rtn = $mysql->fetch();
            //$myerror->info($rsm_item_rtn);die();
            $rsm_item_num = count($rsm_item_rtn);
            //$myerror->info($rsm_item_num);

            //获取stock
            $i = 0;
            foreach($rsm_item_rtn as $v){
                $warehouse_rtn = $mysql->qone('select qty from warehouse_item_unique where pid = ? and wh_id = ? and wh_name = ?', $v['pid'], $mod_result['wh_id'], $mod_result['wh_name']);
                $rsm_item_rtn[$i++]['stock'] = $warehouse_rtn['qty'];
            }
        }else{
            die('Need modid!');
        }

        $goodsForm = new My_Forms();
        $formItems = array(
            'wh_id' => array('title' => 'Shop', 'type' => 'select', 'options' => get_warehouse_info(1, 'Shop'), 'required' => 1, 'value' => isset($mod_result['wh_id'])?$mod_result['wh_id'].'|'.$mod_result['wh_name']:''),
            'sales_date' => array('title' => 'Sales Date', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['sales_date'])?date('Y-m-d', strtotime($mod_result['sales_date'])):''),
            'currency' => array('title' => 'Currency', 'type' => 'select', 'options' => get_currency_type(), 'required' => 1, 'value' => isset($mod_result['currency'])?$mod_result['currency']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
        );

        //序号从0开始
        for($i = 0; $i < $rsm_item_num; $i++){
            $formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'style="width:150px" onblur="rsm_pid_blur(this)"', 'nostar' => true, 'value' => isset($rsm_item_rtn[$i]['pid'])?$rsm_item_rtn[$i]['pid']:'');
            //$formItems['description_chi'.$i] => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:180px"'),
            $formItems['payment_method'.$i] = array('type' => 'select', 'options' => get_payment_method(), 'required' => 1, 'nostar' => true, 'value' => isset($rsm_item_rtn[$i]['payment_method'])?$rsm_item_rtn[$i]['payment_method']:'');
            $formItems['quantity'.$i] = array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'style="width:80px" onblur="rsmQtyBlur(this)"', 'nostar' => true, 'value' => isset($rsm_item_rtn[$i]['quantity'])?$rsm_item_rtn[$i]['quantity']:0);
            $formItems['price'.$i] = array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'addon' => 'style="width:80px" onblur="rsmPriceBlur(this)"', 'nostar' => true/*, 'readonly' => 'readonly'*/, 'value' => isset($rsm_item_rtn[$i]['price'])?$rsm_item_rtn[$i]['price']:'');
            $formItems['remark'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'addon' => 'style="width:200px"', 'value' => isset($rsm_item_rtn[$i]['remark'])?$rsm_item_rtn[$i]['remark']:'');
        }

        //$myerror->info($formItems);
        //die();

        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){
            //fb($_POST);

            $wh = explode('|', $_POST['wh_id']);
            $wh_id = '';
            $wh_name = '';
            if(!empty($wh)){
                $wh_id = $wh[0];
                $wh_name = $wh[1];
            }
            $rsm_id = $wh_name . date('YmdHis');

            $sales_date = $_POST['sales_date'];
            $currency = $_POST['currency'];

            //******
            $prev_num = 4;//第一个post的是form的标识串，还有3个表单项，所以这里是4
            $last_num = 1;//后面的post，有个submit
            $rsm_item = array();
            $i = 0;
            foreach( $_POST as $v){
                if( $i < $prev_num){
                    $i++;
                }elseif($i >= count($_POST) - $last_num){
                    break;
                }else{
                    $rsm_item[] = $v;
                    $i++;
                }
            }
            //fb($rsm_item);
            //这个是设置每个ITEM的元素个数
            $each_item_num = 6;
            $rsm_item_num = intval(count($rsm_item)/$each_item_num);
            //******
            //fb($rsm_item_num);
            $mod_by = $_SESSION["logininfo"]["aName"];
            $mod_date = dateMore();

            $q_pid = array();
            //$description_chi = array();
            $payment_method = array();
            $qty = array();
            $price = array();
            $remark = array();
            $pt_input = array();

            $index = 0;

            for($j = 0; $j < $rsm_item_num; $j++){
                $q_pid[] = $rsm_item[$index++];
                //$description_chi[] = $rsm_item[$index++];
                $payment_method[] = $rsm_item[$index++];
                $price[] = str_replace(',', '', ($rsm_item[$index] != '')?$rsm_item[$index++]:0);
                $qty[] = $rsm_item[$index++];
                $remark[] = $rsm_item[$index++];
                $pt_input[] = $rsm_item[$index++];
            }

/*            fb($q_pid);
            //fb($description_chi);
            fb($payment_method);
            fb($qty);
            fb($price);
            fb($remark);
            fb($pt_input);
            die();*/

            $result = $mysql->q('update retail_sales_memo set sales_date = ?, currency = ?, mod_date = ?, mod_by = ? where rsm_id = ?', $sales_date, $currency, $mod_date, $mod_by, $_GET['modid']);
            //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
            if($result !== false){
                $rtn = $mysql->q('delete from retail_sales_memo_item where rsm_id = ?', $_GET['modid']);
                for($k = 0; $k < $rsm_item_num; $k++){
                    $rs = $mysql->q('insert into retail_sales_memo_item values (NULL, '.moreQm(7).')', $_GET['modid'], $q_pid[$k], $payment_method[$k], $price[$k], $qty[$k], $pt_input[$k], $remark[$k]);
                    if(!$rs){
                        $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $mod_by." retail item ".$_GET['modid']." re-add warehouse ".$wh_name." item ".$q_pid[$k]." num ".$qty[$k]." failure(2)", RETAIL_SALES_MEMO_ITEM_UPDATE_FAILURE, "", "", 0);
                    }
                }
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $mod_by." retail update ".$_GET['modid']." success(2)", RETAIL_SALES_MEMO_ITEM_UPDATE_SUCCESS, "", "", 0);
                $myerror->ok('Modify Retail Sales Memo success!', 'com-search_retail_sales_memo&page=1&wh_name='.$wh_name);
            }else{
                $mysql->sp('CALL admin_log_insert('.moreQm(8).')', 0, $ip_real, RETAIL_SALES_MEMO_LOG_TYPE, $mod_by." retail update ".$_GET['modid']." failure(2)", RETAIL_SALES_MEMO_ITEM_UPDATE_FAILURE, "", "", 0);
                $myerror->error('Modify Retail Sales Memo failure', 'BACK');
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
        <h1 class="green">Retail Sales Memo<em>* item must be filled in</em></h1>
        <fieldset class="center2col">
            <legend class='legend'>Modify Retail Sales Memo</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><div class="set"><label class="formtitle">Retail Sales Memo NO.</label><br /><?=isset($mod_result['rsm_id'])?$mod_result['rsm_id']:'Error'?></div></td>
                    <td width="25%"><? $goodsForm->show('wh_id');?></td>
                    <td width="25%"><? $goodsForm->show('sales_date');?></td>
                    <td width="25%"><? $goodsForm->show('currency');?></td>
                </tr>
            </table>
            <div class="line"></div>
            <div style="margin-left:28px;">
                <!--<label class="formtitle" for="g_cast"><font size="+1">Input Product</font></label>-->
                <table width="100%" id="tableDnD">
                    <tbody id="tbody">
                    <tr class="formtitle nodrop nodrag">
                        <!--                    <td width="3%"></td>-->
                        <td width="16%">Product ID</td>
                        <!--                    <td width="18%">Description()</td>-->
                        <td width="18%">Payment Method</td>
                        <td width="8%">Price(HKD)</td>
                        <td width="8%">Quantity</td>
                        <td width="8%">Stock</td>
                        <td width="8%">Subtotal</td>
                        <td width="8%">Photo</td>
                        <td width="21%">Remark</td>
                        <td width="4%">&nbsp;</td>
                        <td width="4%">&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?
                    for($i = 0; $i < $rsm_item_num; $i++){
                        if (is_file($pic_path_com . $rsm_item_rtn[$i]['photo']) == true) {

                            //圖片壓縮
                            //$rsm_item_rtn[$i]['photo']是原來的， $small_photo是縮小後的
                            //$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
                            $small_photo = 's_' . $rsm_item_rtn[$i]['photo'];
                            //縮小的圖片不存在才進行縮小操作
                            if (!is_file($pic_path_small . $small_photo) == true) {
                                makethumb($pic_path_com . $rsm_item_rtn[$i]['photo'], $pic_path_small . $small_photo, 's');
                            }
                            /*
                            $arr = getimagesize($pic_path_com . $rsm_item_rtn[$i]['photo']);
                            $pic_width = $arr[0];
                            $pic_height = $arr[1];
                            $image_size = getimgsize(80, 60, $pic_width, $pic_height);
                            $photo_string = '<a href="/sys/'.$pic_path_com . $rsm_item_rtn[$i]['photo'].'" target="_blank" title="'.$rsm_item_rtn[$i]['photo'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
                            */
                            $photo_string = '<a href="/sys/'.$pic_path_com . $rsm_item_rtn[$i]['photo'].'" target="_blank" title="'.$rsm_item_rtn[$i]['photo'].'"><img src="/sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
                        }else{
                            $photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60' original='../images/nopic.gif'>";
                        }
                        ?>
                        <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">
                            <!--                    <td class="dragHandle"></td>-->
                            <td><? $goodsForm->show('q_pid'.$i);?></td>
                            <!--                    <td>--><?// $goodsForm->show('description_chi');?><!--</td>-->
                            <td><? $goodsForm->show('payment_method'.$i);?></td>
                            <td><? $goodsForm->show('price'.$i);?></td>
                            <td><? $goodsForm->show('quantity'.$i);?></td>
                            <td class="num_td"><?=(isset($rsm_item_rtn[$i]['stock']) && $rsm_item_rtn[$i]['stock'] != '')?$rsm_item_rtn[$i]['stock']:0 ?></td>
                            <td class="num_td"><?=formatMoney($rsm_item_rtn[$i]['price'] * $rsm_item_rtn[$i]['quantity'])?></td>
                            <td><?=$photo_string?></td>
                            <td><? $goodsForm->show('remark'.$i);?></td>
                            <td><img title="添加" style="opacity: 0.5;" onclick="addRetailSalesMemoItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"></td>
                            <? if($i != 0){ ?>
                                <td align="left"><img title="Delete" style="opacity: 0.5;" onclick="delRetailSalesMemoItem(this)" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td>
                            <? } ?>
                            <td>&nbsp;</td>
                            <td><input type="hidden" name="pt_input<?=$i?>" id="pt_input<?=$i?>"
                            value="<?=$rsm_item_rtn[$i]['photo']?>" /></td>
                        </tr>
                    <?
                    }
                    ?>

                    </tbody>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
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

    }
}
?>