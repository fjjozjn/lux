<?php

/*
 change log

 */

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();if(!isset($_GET['pvid'])){

//检查权限
judgeUserPerm((isset($_GET['modid'])?$_GET['modid']:'').(isset($_GET['changeid'])?$_GET['changeid']:''));

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        $rs1 = $mysql->q('delete from fty_material_require_item where main_id = ?', $_GET['delid']);
        $rs2 = $mysql->q('delete from fty_material_require where id = ?', $_GET['delid']);
        if($rs2){
            $myerror->ok('删除物料需求单成功!', 'search_fty_material_require&page=1');
        }else{
            $myerror->error('删除物料需求单失败!', 'search_fty_material_require&page=1');
        }
    }elseif(isset($_GET['changeid']) && $_GET['changeid'] != ''){
        $rtn = $mysql->qone('select istatus from fty_material_require where id = ?', $_GET['changeid']);
        $str = '';
        if($rtn['istatus'] == '(D)'){
            $rs = $mysql->q('update fty_material_require set istatus = ? where id = ?', '(I)', $_GET['changeid']);
            $str = '(I)';
        }else{
            $rs = $mysql->q('update fty_material_require set istatus = ? where id = ?', '(D)',  $_GET['changeid']);
            $str = '(D)';
        }
        if($rs){
            $myerror->ok('更改 物料需求单 状态为 '.$str.'!', 'search_fty_material_require&page=1');
        }else{
            $myerror->error('更改 物料需求单 状态失败!', 'search_fty_material_require&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM fty_material_require WHERE id = ?', $_GET['modid']);
            $mysql->q('SELECT * FROM fty_material_require_item WHERE main_id = ?', $_GET['modid']);
            $jg_item_rtn = $mysql->fetch();
            $jg_item_num = count($jg_item_rtn);
        }elseif(isset($_GET['pcid']) && $_GET['pcid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM purchase WHERE pcid = ?', $_GET['pcid']);

            //str_replace('|', "','", trim($_GET['pid'], '|'))
            $quote_item_result = $mysql->q('SELECT p.pid, q.price, q.quantity, q.description_chi, p.photos, p.ccode, p.scode FROM product p, purchase_item q WHERE  p.pid = q.pid AND q.pcid = ?', $_GET['pcid']);
            $jg_item_rtn = $mysql->fetch();
            $jg_item_num = count($jg_item_rtn);
            //20150531 只显示订单插入的物料需求单ITEM
            $temp = array();
            foreach($jg_item_rtn as $v){
                if(strpos($_GET['pid'], $v['pid']) !== false){
                    $temp[] = $v;
                }
            }
            $jg_item_rtn = $temp;
            //fb($jg_item_rtn);
            $jg_item_num = count($jg_item_rtn);

            //特殊的参数
            $mod_result['pcid'] = $_GET['pcid'];
        }else{
            die('Need modid!');
        }

        $goodsForm = new My_Forms();
        $formItems = array(
            'mr_pcid' => array('title' => '订单号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => isset($mod_result['pcid'])?$mod_result['pcid']:'', 'readonly' => 'readonly', 'required' => 1),

            'mr_require_date' => array('title' => '日期', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['require_date'])?date('Y-m-d', strtotime($mod_result['require_date'])):date('Y-m-d'), 'required' => 1),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' 提交 '),
        );

        //第一個上面用了
        //原来从1开始，现在从0开始
        $index = 0;
        for($i = 0; $i < $jg_item_num; $i++){
            if(isset($_GET['modid']) && $_GET['modid'] != ''){
                $formItems['q_pid'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => $jg_item_rtn[$i]['pid'], 'readonly' => 'readonly', 'addon' => 'style="width:100px"');
                $formItems['q_m_id'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $jg_item_rtn[$i]['m_id'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                $formItems['q_m_type'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $jg_item_rtn[$i]['m_type'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                $formItems['q_m_name'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $jg_item_rtn[$i]['m_name'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                $formItems['q_m_color'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $jg_item_rtn[$i]['m_color'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                $formItems['q_m_unit'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $jg_item_rtn[$i]['m_unit'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                $formItems['q_m_loss'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $jg_item_rtn[$i]['m_loss'].'%', 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                $formItems['q_m_order_consume'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $jg_item_rtn[$i]['m_order_consume'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                $formItems['q_m_stock'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $jg_item_rtn[$i]['m_stock'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                $formItems['q_m_is_enough'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $jg_item_rtn[$i]['m_is_enough'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                $formItems['q_m_remark'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $jg_item_rtn[$i]['m_remark'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                $index++;
            }elseif(isset($_GET['pcid']) && $_GET['pcid'] != ''){
                //找出所有需要的物料
                if(isset($jg_item_rtn[$i]['pid']) && $jg_item_rtn[$i]['pid']){
                    $bom_material_rs = $mysql->q('select * from bom_material where bom_id = (select id from bom where g_id = ? order by id desc limit 1) order by id', $jg_item_rtn[$i]['pid']);
                    if ($bom_material_rs) {
                        $bom_material_rtn = $mysql->fetch();
                        foreach($bom_material_rtn as $bom_material_rtn_item){
                            $order_consume = round($bom_material_rtn_item['m_value']*$jg_item_rtn[$i]['quantity']*(1+$bom_material_rtn_item['m_loss']/100), 2);
                            $material_warehouse = $mysql->qone('select * from fty_material_warehouse where m_id = ?', $bom_material_rtn_item['m_id']);
                            $formItems['q_pid'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'value' => $jg_item_rtn[$i]['pid'], 'readonly' => 'readonly', 'addon' => 'style="width:100px"');
                            $formItems['q_m_id'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $bom_material_rtn_item['m_id'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                            $formItems['q_m_type'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $bom_material_rtn_item['m_type'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                            $formItems['q_m_name'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $bom_material_rtn_item['m_name'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                            $formItems['q_m_color'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $bom_material_rtn_item['m_color'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                            $formItems['q_m_unit'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $bom_material_rtn_item['m_unit'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                            $formItems['q_m_loss'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $bom_material_rtn_item['m_loss'].'%', 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                            $formItems['q_m_order_consume'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $order_consume, 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                            $formItems['q_m_stock'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => isset($material_warehouse['m_num'])?$material_warehouse['m_num']:0, 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                            $formItems['q_m_is_enough'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => ($material_warehouse['m_num']<$order_consume)?'欠料':'库存充足', 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                            $formItems['q_m_remark'.$index] = array('type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'value' => $bom_material_rtn_item['m_remark'], 'readonly' => 'readonly', 'addon' => 'style="width:50px"');
                            $index++;
                        }
                    }
                }
            } 
        }
        //die();
        //fb($formItems);
        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){

            fb($_POST);
            //die('error');

            $mr_id = isset($_GET['pcid'])?fty_autoGenerationID():$_GET['modid'];

            $mr_pcid = $_POST['mr_pcid'];
            $mr_require_date = $_POST['mr_require_date'];

            $i = 0;
            $prev_num = 3;//第一个post的是form的标识串，还有2个表单项
            $last_num = 1;//后面的post，有个submit
            $q_product = array();
            foreach( $_POST as $v){
                if( $i < $prev_num){
                    $i++;
                }elseif($i >= count($_POST) - $last_num){
                    break;
                }else{
                    $q_product[] = $v;
                    $i++;
                }
            }

            $in_date = $mod_date = dateMore();
            $created_by = $mod_by = $_SESSION["ftylogininfo"]["aName"];

            //这个是设置每个ITEM的元素个数
            $each_item_num = 11;
            $q_product_num = intval(count($q_product)/$each_item_num);

            $q_pid = array();
            $q_m_id = array();
            $q_m_type = array();
            $q_m_name = array();
            $q_m_color = array();
            $q_m_unit = array();
            $q_m_loss = array();
            $q_m_order_consume = array();
            $q_m_stock = array();
            $q_m_is_enough = array();
            $q_m_remark = array();

            $total = 0;
            $p_index = 0;
            for($j = 0; $j < $q_product_num; $j++){
                $q_pid[] = $q_product[$p_index++];
                $q_m_id[] = $q_product[$p_index++];
                $q_m_type[] = $q_product[$p_index++];
                $q_m_name[] = $q_product[$p_index++];
                $q_m_color[] = $q_product[$p_index++];
                $q_m_unit[] = $q_product[$p_index++];
                $q_m_loss[] = $q_product[$p_index++];
                $q_m_order_consume[] = $q_product[$p_index++];
                $q_m_stock[] = $q_product[$p_index++];
                $q_m_is_enough[] = $q_product[$p_index++];
                $q_m_remark[] = $q_product[$p_index++];
            }

            /*fb($q_pid);
            fb($q_m_id);
            fb($q_m_type);
            fb($q_m_name);
            fb($q_m_color);
            fb($q_m_unit);
            fb($q_m_loss);
            fb($q_m_order_consume);
            fb($q_m_stock);
            fb($q_m_is_enough);
            fb($q_m_remark);
            die('@');*/

            //还不知要怎么算这个
            $ex_total = 0;

            if (isset($_GET['modid']) && $_GET['modid'] != '') {

                $result = $mysql->q('update fty_material_require set require_date = ?, mod_date = ?, mod_by = ? where id = ?', $mr_require_date, $mod_date, $mod_by, $_GET['modid']);

                if ($result) {
                    //暂时不知道需要修改的字段
                    /*$rtn = $mysql->q('delete from fty_material_require_item where main_id = ?', $_GET['modid']);
                    for ($k = 0; $k < $q_product_num; $k++) {
                        //description 只寫入數據庫中的 description_chi字段，description保持為空
                        $mysql->q('insert into fty_material_require_item set main_id = ?, pid = ?, m_id = ?, m_type = ?, m_name = ?, m_color = ?, m_unit = ?, m_loss = ?, m_order_consume = ?, m_stock = ?, m_is_enough = ?, m_remark = ?', $_GET['modid'], $q_pid[$k], $q_m_id[$k], $q_m_type[$k], $q_m_name[$k], $q_m_color[$k], $q_m_unit[$k], $q_m_loss[$k], $q_m_order_consume[$k], $q_m_stock[$k], $q_m_is_enough[$k], $q_m_remark[$k]);
                    }*/

                    $myerror->ok('修改物料需求单成功!', 'search_fty_material_require&page=1');
                } else {
                    $myerror->error('修改物料需求单失败', 'BACK');
                }

            } elseif (isset($_GET['pcid']) && $_GET['pcid'] != '') {

                //判断是否输入的pcid已存在，因为存在的话由于数据库限制，就会新增失败
                $judge = $mysql->q('select mr_id from fty_material_require where mr_id = ?', $mr_id);
                if (!$judge) {
                    $result = $mysql->q('insert into fty_material_require set mr_id = ?, pcid = ?, require_date = ?, in_date = ?, mod_date = ?, created_by = ?, mod_by = ?, istatus = ?', $mr_id, $mr_pcid, $mr_require_date, $in_date, $mod_date, $created_by, $mod_by, '(I)');
                    $main_id = $mysql->id();

                    if ($result) {
                        for ($k = 0; $k < $q_product_num; $k++) {
                            //description 只寫入數據庫中的 description_chi字段，description保持為空
                            $mysql->q('insert into fty_material_require_item set main_id = ?, pid = ?, m_id = ?, m_type = ?, m_name = ?, m_color = ?, m_unit = ?, m_loss = ?, m_order_consume = ?, m_stock = ?, m_is_enough = ?, m_remark = ?', $main_id, $q_pid[$k], $q_m_id[$k], $q_m_type[$k], $q_m_name[$k], $q_m_color[$k], $q_m_unit[$k], $q_m_loss[$k], $q_m_order_consume[$k], $q_m_stock[$k], $q_m_is_enough[$k], $q_m_remark[$k]);
                        }

                        $myerror->ok('新增物料需求单成功!', 'search_fty_material_require&page=1');
                    } else {
                        $myerror->error('新增物料需求单失败', 'BACK');
                    }
                } else {
                    $myerror->error('系统错误，新增物料需求单失败', 'BACK');
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
        <h1 class="green">物料需求单<? //show_status_new($mod_result['istatus']);?></h1>
        <fieldset>
            <legend class='legend'>物料需求单</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="100%" id="table">
                <tr class="formtitle">
                    <?php if(isset($_GET['pcid']) && $_GET['pcid'] != ''){
                        ?>
                        <td width="25%"><div class="set"><label class="formtitle">物料需求单编号</label><br />(自动生成)</div></td>
                    <?php
                    }elseif(isset($_GET['modid']) && $_GET['modid'] != ''){
                        ?>
                        <td width="25%"><div class="set"><label class="formtitle">物料需求单编号</label><br /><?=$mod_result['mr_id']?></div></td>
                    <?php
                    }else{
                        ?>
                        <td width="25%"><div class="set"><label class="formtitle">物料需求单编号</label><br /></div></td>
                    <?php
                    }
                    ?>
                    <td width="25%"><? $goodsForm->show('mr_pcid');?></td>
                    <td width="25%"><? $goodsForm->show('mr_require_date');?></td>
                    <td width="25%"></td>
                </tr>
            </table>
            <div class="line"></div>
            <br />
            <div style="margin-left: 28px;"><label class="formtitle" for="g_cast"><font
                        size="+1">物料需求单明细</font></label>
                <table width="100%" id="tableDnD">
                    <tbody id="tbody">
                    <tr class="formtitle nodrop nodrag">
                        <td width="10%">编号</td>
                        <td width="10%">物料ID</td>
                        <td width="10%">物料类别</td>
                        <td width="10%">物料名称</td>
                        <td width="10%">规格颜色</td>
                        <td width="10%">单位</td>
                        <td width="10%">损耗率(%)</td>
                        <td width="10%">订单用量</td>
                        <td width="10%">库存数量</td>
                        <td width="10%">是否欠料</td>
                        <td width="10%">备注</td>
                    </tr>
                    <?
                    if ($index) {
                        for ($i = 0; $i < $index; $i++) {
                            ?>
                            <tr valign="top">
                                <td><? $goodsForm->show('q_pid' . $i); ?></td>
                                <td><? $goodsForm->show('q_m_id' . $i); ?></td>
                                <td><? $goodsForm->show('q_m_type' . $i); ?></td>
                                <td><? $goodsForm->show('q_m_name' . $i); ?></td>
                                <td><? $goodsForm->show('q_m_color' . $i); ?></td>
                                <td><? $goodsForm->show('q_m_unit' . $i); ?></td>
                                <td><? $goodsForm->show('q_m_loss' . $i); ?></td>
                                <td><? $goodsForm->show('q_m_order_consume' . $i); ?></td>
                                <td><? $goodsForm->show('q_m_stock' . $i); ?></td>
                                <td><? $goodsForm->show('q_m_is_enough' . $i); ?></td>
                                <td><? $goodsForm->show('q_m_remark' . $i); ?></td>
                                <!--td>
                                    <div id="del<?= $i ?>" onclick="delItem(this)"><img
                                            src="../../sys/images/del-icon.png"
                                            onmouseout="$(this).css('opacity','0.5')"
                                            onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;"
                                            title="Delete"/></div>
                                </td-->
                            </tr>
                        <?
                        }
                    } else {
                        ?>
                        <tr valign="top"><td colspan="10">无数据</td></tr>
                        <?
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <br />
            <br />
            <div class="line"></div>
            <?
            $goodsForm->show('submitbtn');
            ?></fieldset>
        <?
        $goodsForm->end();
    }
    ?>

    <script>
        $(function(){

        });

        function delItem(obj){
            if(confirm("确定要删除吗?（没保存之前，刷新页面可恢复删除的内容）")){
                $(obj).parents(".repeat").next().next().remove();
                $(obj).parents(".repeat").next().remove();
                $(obj).parents(".repeat").remove();
            }
        }
    </script>

<?
}
?>