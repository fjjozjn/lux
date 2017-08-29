<?php

/*
 change log

 */

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();if(!isset($_GET['pvid'])){

//检查权限
judgeUserPerm($_GET['viewid']);

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['viewid']) && $_GET['viewid'] != ''){
        $mod_result = $mysql->qone('SELECT * FROM purchase WHERE pcid = ?', $_GET['viewid']);
        //從send_to中拆出地址顯示
        $fty_info = explode("\r\n", $mod_result['send_to']);
        $quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description_chi, p.photos, p.ccode, p.scode FROM product p, purchase_item q WHERE  p.pid = q.pid AND q.pcid = ?', $_GET['viewid']);
        $pc_item_rtn = $mysql->fetch();
        $pc_item_num = count($pc_item_rtn);

        //20140323
        for($i = 0; $i < $pc_item_num; $i++){
            $rtn_delivery = $mysql->qone('select sum(quantity) as quantity from
                delivery_item where po_id = ? and p_id = ?', $_GET['viewid'], $pc_item_rtn[$i]['pid']);
            $pc_item_rtn[$i]['delivery_num'] = $rtn_delivery['quantity'];
            if(!isset($pc_item_rtn[$i]['delivery_num']) || $pc_item_rtn[$i]['delivery_num'] == ''){
                $pc_item_rtn[$i]['delivery_num'] = 0;
            }

            //原来是用ajax，但是会被浏览器阻止，所以链接要写在标签里
            $pc_item_rtn[$i]['bom_link'] = '';
            $rtn = $mysql->qone('select id from bom where g_id = ?', @$pc_item_rtn[$i]['pid']);
            if($rtn){
                $pc_item_rtn[$i]['bom_link'] = "javascript:void(window.open('?act=formdetail&id=".$rtn['id']."', 'lux', 'height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no'))";
            }else{
                $pc_item_rtn[$i]['bom_link'] = "javascript:alert('none')";
            }
        }

        //20150103 加工厂生成计划日期
        $pp_result = $mysql->qone('SELECT * FROM fty_production_plan WHERE pcid = ?', $_GET['viewid']);

        //计算对应的此生产单对应的加工单总额
        $jg_total = $mysql->qone('select sum(total) as jg_total from fty_sub_contractor_order where reference = ?', $_GET['viewid']);

    }else{
        die('Need modid!');
    }

    $goodsForm = new My_Forms();
    $formItems = array(

        'pc_pcid' => array('title' => '订单编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, isset($_GET['copypcid'])?'':'readonly' => 'readonly', 'value' => isset($mod_result['pcid'])?$mod_result['pcid']:''),
        'pc_sid' => array('title' => '供应商', 'type' => 'text', 'value' => isset($fty_info[0])?$fty_info[0]:'', 'disabled' => 'disabled'),
        //'pc_send_to' => array('title' => 'To', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['send_to'])?$mod_result['send_to']:''),
        'pc_reference' => array('title' => '参考', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['reference'])?$mod_result['reference']:'', 'disabled' => 'disabled'),
        'pc_attention' => array('title' => '致', 'type' => 'text', 'value' => isset($mod_result['attention'])?$mod_result['attention']:'', 'disabled' => 'disabled'),
        'pc_created_by' => array('title' => '负责人', 'type' => 'text', 'value' => isset($mod_result['created_by'])?$mod_result['created_by']:'', 'disabled' => 'disabled'),
        //'pc_mark_date' => array('title' => '创建日期', 'type' => 'text', 'restrict' => 'date', 'value' => isset($_GET['pvid'])?date('Y-m-d'):(isset($mod_result['mark_date'])?date('Y-m-d', strtotime($mod_result['mark_date'])):''), 'disabled' => 'disabled'),
        'pc_customer' => array('title' => '客户', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['customer'])?$mod_result['customer']:'', 'disabled' => 'disabled'),
        'pc_customer_po' => array('title' => '客户单号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['customer_po'])?$mod_result['customer_po']:'', 'disabled' => 'disabled'),

        'pc_expected_date' => array('title' => '要求出货日期', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['expected_date'])?date('Y-m-d', strtotime($mod_result['expected_date'])):'', 'disabled' => 'disabled'),

        'pc_remark' => array('title' => '备注', 'type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:400px"', 'value' => isset($mod_result['remark'])?$mod_result['remark']:'', 'disabled' => 'disabled'),
        'pc_address' => array('title' => '地址', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 150, 'rows' => 2, 'value' => isset($fty_info[1])?$fty_info[1]:'', 'disabled' => 'disabled'),

        'pc_packaging' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:350px"', 'value' => isset($mod_result['packaging'])?$mod_result['packaging']:'', 'disabled' => 'disabled'),
        'pc_ship_mark' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:350px"', 'value' => isset($mod_result['ship_mark'])?$mod_result['ship_mark']:'', 'disabled' => 'disabled'),
        //proforma传过来的时候不用传remarks值
        'pc_remarks' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'addon' => 'style="width:350px"', 'value' => isset($_GET['pvid'])?'':(isset($mod_result['remarks'])?$mod_result['remarks']:''), 'disabled' => 'disabled'),


        //20150103 工厂生成计划日期填写
        'pc_date1' => array('title' => '起板期', 'type' => 'text', 'restrict' => 'date', 'value' => isset($pp_result['pc_date1'])?date('Y-m-d', strtotime($pp_result['pc_date1'])):''),
        'pc_date2' => array('title' => '半成品期', 'type' => 'text', 'restrict' => 'date', 'value' => isset($pp_result['pc_date2'])?date('Y-m-d', strtotime($pp_result['pc_date2'])):''),
        'pc_date3' => array('title' => '成品期', 'type' => 'text', 'restrict' => 'date', 'value' => isset($pp_result['pc_date3'])?date('Y-m-d', strtotime($pp_result['pc_date3'])):''),
        'pc_date4' => array('title' => 'QC日期', 'type' => 'text', 'restrict' => 'date', 'value' => isset($pp_result['pc_date4'])?date('Y-m-d', strtotime($pp_result['pc_date4'])):''),
        'pc_date5' => array('title' => '出货日期', 'type' => 'text', 'restrict' => 'date', 'value' => isset($pp_result['pc_date5'])?date('Y-m-d', strtotime($pp_result['pc_date5'])):''),

        'sco_select_all'	=> array('type' => 'checkbox', 'options' => array(array("全部选中", "select_all")),
            'addon' => 'onclick="select_all()"'),
        'submitbtn'	=> array('type' => 'submit', 'value' => ' 提交 '),
        'submit_sco_btn' => array('type' => 'button', 'value' => ' 插入加工单 ', 'addon' => 'onclick="insert_sco()"'),
        'submit_fmr_btn' => array('type' => 'button', 'value' => ' 插入物料需求单 ', 'addon' => 'onclick="insert_fmr()"'),
    );

    //第一個上面用了
    //原来从1开始，现在从0开始
    for($i = 0; $i < $pc_item_num; $i++){
        $formItems['sco_'.$i] = array('type' => 'checkbox', 'options' => array(array("", $pc_item_rtn[$i]['pid'])));
        $formItems['q_pid'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($pc_item_rtn[$i]['pid'])?$pc_item_rtn[$i]['pid']:'', 'disabled' => 'disabled');
        $formItems['q_p_price'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => isset($pc_item_rtn[$i]['price'])?formatMoney($pc_item_rtn[$i]['price']):'', 'disabled' => 'disabled');
        $formItems['q_p_quantity'.$i] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:50px"', 'value' => (isset($pc_item_rtn[$i]['quantity']) && ($pc_item_rtn[$i]['quantity'] == 0 || $pc_item_rtn[$i]['quantity'] == ''))?1:intval($pc_item_rtn[$i]['quantity']), 'disabled' => 'disabled');
        $formItems['q_p_description'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 2, 'value' => isset($pc_item_rtn[$i]['description_chi'])?$pc_item_rtn[$i]['description_chi']:'', 'disabled' => 'disabled', 'addon' => 'style="width:300px"');
        $formItems['q_p_photos'.$i] = array('type' => 'hidden', 'value' => isset($pc_item_rtn[$i]['photos'])?$pc_item_rtn[$i]['photos']:'', 'disabled' => 'disabled');
        $formItems['q_p_ccode'.$i] = array('type' => 'hidden', 'value' => isset($pc_item_rtn[$i]['ccode'])?$pc_item_rtn[$i]['ccode']:'', 'disabled' => 'disabled');
        $formItems['q_p_scode'.$i] = array('type' => 'hidden', 'value' => isset($pc_item_rtn[$i]['scode'])?$pc_item_rtn[$i]['scode']:'', 'disabled' => 'disabled');
    }
    //$myerror->info($formItems);
    //die();

    $goodsForm->init($formItems);

    if(!$myerror->getAny() && $goodsForm->check()){
        //fb($_POST);die();

        $pc_pcid = $_POST['pc_pcid'];
        $pc_date1 = $_POST['pc_date1'];
        $pc_date2 = $_POST['pc_date2'];
        $pc_date3 = $_POST['pc_date3'];
        $pc_date4 = $_POST['pc_date4'];
        $pc_date5 = $_POST['pc_date5'];

        if($pc_date1 <= $pc_date2 && $pc_date2 <= $pc_date3 && $pc_date3 <= $pc_date4 && $pc_date4 <= $pc_date5){
            $rtn = $mysql->qone('select id from fty_production_plan where pcid = ?', $pc_pcid);
            if(isset($rtn['id']) && $rtn['id']){
                $rs = $mysql->q('update fty_production_plan set pc_date1 = ?, pc_date2 = ?, pc_date3 = ?, pc_date4 = ?, pc_date5 = ? where pcid = ?', $pc_date1, $pc_date2, $pc_date3, $pc_date4, $pc_date5, $pc_pcid);
            }else{
                $rs = $mysql->q('insert into fty_production_plan (pcid, pc_date1, pc_date2,
            pc_date3, pc_date4, pc_date5) values ('.moreQm(6).')', $pc_pcid, $pc_date1, $pc_date2, $pc_date3, $pc_date4, $pc_date5);
            }
            if($rs !== false){
                //20150112 生成计划添加后记录进 QC_SCHEDULE
                $mysql->q('insert into qc_schedule values (NULL, '.moreQm(6).')', $pc_date4, $pc_pcid, dateMore(), '', $_SESSION['ftylogininfo']['aName'], '');

                $myerror->ok('生产计划 记录成功!', 'searchpurchase&page=1');
            }else{
                $myerror->error('生产计划 记录失败，请联系管理员', 'BACK');
            }
        }else{
            $myerror->error('生产计划日期有误，请填写正确的日期（起板期最早，出货日期最迟）', 'BACK');
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
        <h1 class="green">订单<? show_status_new($mod_result['istatus']);?></h1>

        <fieldset><legend class='legend'>查看订单</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="25%"><? $goodsForm->show('pc_pcid');?></td>
                    <td width="25%"><? $goodsForm->show('pc_reference');?></td>
                    <td width="25%"><? $goodsForm->show('pc_sid');?></td>
                    <td width="25%"><? $goodsForm->show('pc_attention');?></td>
                </tr>
                <tr>
                    <td width="25%" colspan="2"><? $goodsForm->show('pc_address');?></td>
                    <td width="25%" valign="top"><? $goodsForm->show('pc_customer');?></td>
                    <td width="25%" valign="top"><? $goodsForm->show('pc_customer_po');?></td>
                </tr>
                <tr>
                    <td width="25%"><? $goodsForm->show('pc_expected_date');?></td>
                    <td width="25%" colspan="2"><? $goodsForm->show('pc_remark');?></td>
                    <td width="25%"><? $goodsForm->show('pc_created_by');?></td>
                </tr>
                <tr>
                    <td width="25%"><div class="set"><label class="formtitle">创建日期</label><br /><?=isset($mod_result['in_date'])?$mod_result['in_date']:'None'?></div></td>
                    <td width="25%"><div class="set"><label class="formtitle">最后修改日期</label><br /><?=isset($mod_result['mark_date'])?$mod_result['mark_date']:'None'?></div></td>
                </tr>
            </table>
            <div class="line"></div>

            <br />
            <div style="margin-left: 28px;"><label class="formtitle" for="g_cast"><font size="+1">生产计划</font></label></div>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <td width="20%"><? $goodsForm->show('pc_date1');?></td>
                    <td width="20%"><? $goodsForm->show('pc_date2');?></td>
                    <td width="20%"><? $goodsForm->show('pc_date3');?></td>
                    <td width="20%"><? $goodsForm->show('pc_date4');?></td>
                    <td width="20%"><? $goodsForm->show('pc_date5');?></td>
                </tr>
            </table>
            <br />
            <div class="line"></div>

            <div style="margin-left: 28px;"><label class="formtitle" for="g_cast"><font
                        size="+1">订单明细</font></label>
                <table width="100%" id="tableDnD">
                    <tbody id="tbody">
                    <tr class="formtitle nodrop nodrag">
                        <td width="1%">选择</td>
                        <td width="14%">编号</td>
                        <td width="10%">客号</td>
                        <td width="28%">描述</td>
                        <td width="8%">数量</td>
                        <td width="8%">已出数量</td>
                        <? /*<td width="20%">Product Remark</td>*/ ?>
                        <td width="9%">价格</td>
                        <td width="8%">合计</td>
                        <td width="9%">照片</td>
                        <? /*<td width="6%"><div class="del_all"><input name='' type='button' value='Del All' /></div></td>*/?>
                        <td width="4%">BOM</td>
                        <td width="1%">&nbsp;</td>
                        <!--			<td width="3%">&nbsp;</td>-->
                        <!--			<td width="5%">&nbsp;</td>-->
                    </tr>
                    <?
                    for($i = 0; $i < $pc_item_num; $i++){
                        if (is_file('../sys/'.$pic_path_com . $pc_item_rtn[$i]['photos']) == true) {

                            //圖片壓縮
                            //$pc_item_rtn[$i]['photos']是原來的， $small_photo是縮小後的
                            //$pic_path_com是原來的路徑， $pic_path_small是縮小後的路徑
                            $small_photo = 's_' . $pc_item_rtn[$i]['photos'];
                            //縮小的圖片不存在才進行縮小操作
                            if (!is_file('../sys/'.$pic_path_small . $small_photo) == true) {
                                makethumb('../sys/'.$pic_path_com . $pc_item_rtn[$i]['photos'], $pic_path_small . $small_photo, 's');
                            }

                            $photo_string = '<a href="../sys/'.$pic_path_com . $pc_item_rtn[$i]['photos'].'" target="_blank" title="'.$pc_item_rtn[$i]['photos'].'"><img src="../sys/'.$pic_path_small . $small_photo.'" border="0" align="middle" original="/sys/'.$pic_path_small . $small_photo.'"/></a>';
                        }else{
                            $photo_string = "<img src='../images/nopic.gif' border='0' width='80' height='60' original='../images/nopic.gif'>";
                        }
                        ?>
                        <tr class="repeat" valign="top">
                            <td><? $goodsForm->show('sco_'.$i);?></td>
                            <td><? $goodsForm->show('q_pid'.$i);?></td>
                            <td align="center"><div id="scode"><?=$pc_item_rtn[$i]['scode']?></div></td>
                            <td><? $goodsForm->show('q_p_description'.$i);?></td>
                            <td><? $goodsForm->show('q_p_quantity'.$i);?></td>
                            <td align="center"><?=@$pc_item_rtn[$i]['delivery_num']?></td>
                            <? /*<td><? $goodsForm->show('q_p_remark');?></td>*/ ?>
                            <td><? $goodsForm->show('q_p_price'.$i); $goodsForm->show('q_p_photos'.$i); $goodsForm->show('q_p_ccode'.$i); $goodsForm->show('q_p_scode'.$i);?></td>
                            <td id="sub"><?=formatMoney($pc_item_rtn[$i]['price']*(($pc_item_rtn[$i]['quantity'] == 0 || $pc_item_rtn[$i]['quantity'] == '')?1:$pc_item_rtn[$i]['quantity']))?></td>
                            <td><?=$photo_string?></td>
                            <td><a href="<?=$pc_item_rtn[$i]['bom_link']?>"><img src="../images/button_bom.png" alt="<?=$pc_item_rtn[$i]['pid']?>" /></a></td>
                            <td>&nbsp;</td>
                            <!--			<td>&nbsp;</td>-->
                            <!--			<td>&nbsp;</td>-->
                        </tr>
                    <?
                    }
                    ?>
                    </tbody>
                    <tr>
                        <td><? $goodsForm->show('sco_select_all');?></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="center">总计:</td>
                        <td id="sub">
                            <div id="total">0</div>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align="center">加工单总额：</td>
                        <td id="sub">
                            <div id="total"><?php echo isset($jg_total['jg_total'])?$jg_total['jg_total']:'0.000'; ?></div>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                </table>

                <div class="line"></div>

                <table width="100%" id="table">
                    <tr class="formtitle">
                        <th width="33%">包装</th>
                        <th width="33%">船麥</th>
                        <th width="33%">备注</th>
                    </tr>
                    <tr>
                        <td width="33%"><? $goodsForm->show('pc_packaging');?></td>
                        <td width="33%"><? $goodsForm->show('pc_ship_mark');?></td>
                        <td width="50%"><? $goodsForm->show('pc_remarks');?></td>
                    </tr>
                </table>

            </div>
            <br />
            <br />

            <div class="line"></div>
            <?
            $goodsForm->show('submitbtn');
            $goodsForm->show('submit_sco_btn');
            $goodsForm->show('submit_fmr_btn');
            ?></fieldset>
        <?
        $goodsForm->end();

    }
    ?>

    <script>
        $(function(){
            //load頁面就更新total值
            UpdateTotal();
            //$(".template").hide()
            //selectSupplier("pc_")
            //***先加載當前屏幕的img，好像沒有效果。。。
            /*
             $("img").lazyload({
             placeholder : "/sys/images/grey.gif",
             effect      : "fadeIn"
             });
             */
            //***
            //table tr层表单可拖动
            //$("#tableDnD").tableDnD();
        })

        function select_all(){
            if(document.getElementById("sco_select_all").checked == true){
                $('input[name^="sco_"]').each(function () {
                    $(this).attr("checked", true);
                });
            }else{
                $('input[name^="sco_"]').each(function () {
                    $(this).attr("checked", false);
                });
            }
        }

        function insert_sco(){
            var pcid = $('#pc_pcid').val();
            var param = '';

            $('input[name^="sco_"]').each(function () {
                if(this.checked == true){
                    param = param + this.value + '|';
                }
            });

            if(param != ''){
                //window.open('/fty/?act=modify_sub_contractor_order&pcid='+pcid+'&pid='+param, 'newwindow', 'height=500,width=800,scrollbars=yes,status=yes');
                location.href = "/fty/?act=modify_sub_contractor_order&pcid="+pcid+"&pid="+param;
            }else{
                alert('请选择要插入加工单的产品');
            }
        }

        function insert_fmr(){
            var pcid = $('#pc_pcid').val();
            var param = '';

            $('input[name^="sco_"]').each(function () {
                if(this.checked == true){
                    param = param + this.value + '|';
                }
            });

            if(param != ''){
                //window.open('/fty/?act=modify_sub_contractor_order&pcid='+pcid+'&pid='+param, 'newwindow', 'height=500,width=800,scrollbars=yes,status=yes');
                location.href = "/fty/?act=modify_fty_material_require&pcid="+pcid+"&pid="+param;
            }else{
                alert('请选择要插入物料需求单的产品');
            }
        }
    </script>

<?
}
?>