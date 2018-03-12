<?php
/**
 * Author: zhangjn
 * Date: 2017/10/4
 * Time: 20:56
 */
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//禁止其他用户进入（临时做法）
//201306131746 去除限制
/*if(!isSysAdmin()){
	$myerror->error('Without Permission To Access', 'main');
}*/


if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        $fty_payment_request_info = $mysql->qone('select status from fty_payment_request where id = ?', $_GET['delid']);
        if ($fty_payment_request_info['status'] == 2) {
            $rs1 = $mysql->q('delete from fty_payment_request_item where main_id = ?', $_GET['delid']);
            $rs2 = $mysql->q('delete from fty_payment_request where id = ?', $_GET['delid']);
            if ($rs2) {
                $myerror->ok('删除 付款申请单 成功!', 'search_payment_request&page=1');
            } else {
                $myerror->error('删除 付款申请单 失败!', 'search_payment_request&page=1');
            }
        } else {
            $myerror->error('已核批或已付款的付款申请单不能删除!', 'search_payment_request&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM fty_payment_request WHERE id = ?', $_GET['modid']);
            $rs = $mysql->q('SELECT * FROM fty_payment_request_item WHERE main_id = ?', $_GET['modid']);
            $item_num = 0;
            $mod_result_item = array();
            if($rs){
                $mod_result_item = $mysql->fetch();
                $item_num = count($mod_result_item);
            }
        }else{
            die('Need modid!');
        }

        $goodsForm = new My_Forms();
        $formItems = array(
            //这个是隐藏起来的
            'fpr_type' => array('type' => 'select', 'options' => get_fty_wlgy_jg_type(), 'addon' => 'onchange="searchFtyCustomer(this)"', 'disabled' => 'disabled'),
            'fpr_fty_customer' => array('type' => 'select', 'options' => '', 'addon' => 'onchange="searchFtyCustomerDetail(this)"', 'disabled' => 'disabled'),
            'fpr_fty_customer_ap' => array('type' => 'text', 'restrict' => 'number', 'disabled' => 'disabled'),
            'fpr_pay_amount' => array('type' => 'text', 'restrict' => 'number', 'disabled' => 'disabled'),
            'fpr_remark' => array('type' => 'text', 'disabled' => 'disabled'),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' 保存 '),
        );

        for($i = 0; $i < $item_num; $i++){
            $formItems['fpr_type'.$i] = array('type' => 'select', 'options' => get_fty_wlgy_jg_type(), 'addon' => 'onchange="searchFtyCustomer(this)"', 'value' => isset($mod_result_item[$i]['type'])?$mod_result_item[$i]['type']:'', 'disabled' => 'disabled');
            $formItems['fpr_fty_customer'.$i] = array('type' => 'select', 'options' => array(array($mod_result_item[$i]['fty_customer'], $mod_result_item[$i]['fty_customer'])), 'addon' => 'onchange="searchFtyCustomerDetail(this)"', 'value' => isset($mod_result_item[$i]['fty_customer'])?$mod_result_item[$i]['fty_customer']:'', 'disabled' => 'disabled');
            $formItems['fpr_fty_customer_ap'.$i] = array('type' => 'text', 'restrict' => 'number', 'value' => isset($mod_result_item[$i]['fty_customer_ap'])?$mod_result_item[$i]['fty_customer_ap']:'', 'readonly' => 'readonly');
            if ($mod_result['status'] == 1) {
                $formItems['fpr_pay_amount'.$i] = array('type' => 'text', 'restrict' => 'number', 'value' => isset($mod_result_item[$i]['pay_amount'])?$mod_result_item[$i]['pay_amount']:'', 'readonly' => 'readonly');
                $formItems['fpr_remark'.$i] = array('type' => 'text', 'value' => isset($mod_result_item[$i]['remark'])?$mod_result_item[$i]['remark']:'', 'readonly' => 'readonly');
            } elseif ($mod_result['status'] == 2) {
                $formItems['fpr_pay_amount'.$i] = array('type' => 'text', 'restrict' => 'number', 'value' => isset($mod_result_item[$i]['pay_amount'])?$mod_result_item[$i]['pay_amount']:'');
                $formItems['fpr_remark'.$i] = array('type' => 'text', 'value' => isset($mod_result_item[$i]['remark'])?$mod_result_item[$i]['remark']:'');
            } elseif ($mod_result['status'] == 3) {
                $formItems['fpr_pay_amount'.$i] = array('type' => 'text', 'restrict' => 'number', 'value' => isset($mod_result_item[$i]['pay_amount'])?$mod_result_item[$i]['pay_amount']:'', 'readonly' => 'readonly');
                $formItems['fpr_remark'.$i] = array('type' => 'text', 'value' => isset($mod_result_item[$i]['remark'])?$mod_result_item[$i]['remark']:'', 'readonly' => 'readonly');
            }
        }
        //最后一个
        $formItems['fpr_type'.$i] = array('type' => 'select', 'options' => get_fty_wlgy_jg_type(), 'addon' => 'onchange="searchFtyCustomer(this)"');
        $formItems['fpr_fty_customer'.$i] = array('type' => 'select', 'options' => '', 'addon' => 'onchange="searchFtyCustomerDetail(this)"', 'disabled' => 'disabled');
        $formItems['fpr_fty_customer_ap'.$i] = array('type' => 'text', 'restrict' => 'number', 'disabled' => 'disabled');
        $formItems['fpr_pay_amount'.$i] = array('type' => 'text', 'restrict' => 'number', 'disabled' => 'disabled');
        $formItems['fpr_remark'.$i] = array('type' => 'text', 'disabled' => 'disabled');

        $goodsForm->init($formItems);

        if(!$myerror->getAny() && $goodsForm->check()){
            //fb($_POST);

            $today = dateMore();
            $staff = $_SESSION['ftylogininfo']['aName'];

            $i = 0;
            $prev_num = 1;//第一个post的是form的标识串，还有0个表单项，所以是1
            $last_num = 1;//后面的post，有个submit
            $item = array();
            foreach($_POST as $v){
                if( $i < $prev_num){
                    $i++;
                }elseif($i >= count($_POST) - $last_num){
                    break;
                }else{
                    $item[] = $v;
                    $i++;
                }
            }

            //这个是设置每个ITEM的元素个数
            $each_item_num = 5;
            $item_num = intval(count($item)/$each_item_num);

            $payment_request_arr = array();
            $index = 0;

            for($j = 0; $j < $item_num; $j++){
                $payment_request_arr[$j]['fty_customer_ap'] = $item[$index++];
                $payment_request_arr[$j]['pay_amount'] = $item[$index++];
                $payment_request_arr[$j]['remark'] = $item[$index++];
                $payment_request_arr[$j]['type'] = $item[$index++];
                $payment_request_arr[$j]['fty_customer'] = $item[$index++];
            }
            //fb($payment_request_arr);

            $result = $mysql->q('update fty_payment_request set mod_by = ?, mod_date = ? where id = ?', $staff, $today, $_GET['modid']);
            if($result){
                $rtn = $mysql->q('delete from fty_payment_request_item where main_id = ?', $_GET['modid']);
                foreach($payment_request_arr as $v){
                    $mysql->q('insert into fty_payment_request_item set main_id = ?, type = ?, fty_customer = ?, fty_customer_ap = ?, pay_amount = ?, remark = ?', $_GET['modid'], $v['type'], $v['fty_customer'], $v['fty_customer_ap'], $v['pay_amount'], $v['remark']);
                }
                $myerror->ok('修改 付款申请单 成功!', 'search_payment_request&page=1');
            }else{
                $myerror->error('修改 付款申请单 失败', 'BACK');
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
    <h1 class="green">付款申请单<em>* item must be filled in</em></h1>
    <fieldset>
        <legend class='legend'>修改<?php if ($mod_result['status'] == 1) {echo '（已批核的内容不能修改）';}?></legend>
        <?php
        $goodsForm->begin();
        ?>
        <div style="margin-left:28px;">
            <table width="100%" id="tableDnD_wl">
                <tbody id="tbody">
                <tr class="formtitle nodrop nodrag">
                    <td width="2%"></td>
                    <td>类别</td>
                    <td>供应商</td>
                    <td>应付</td>
                    <td>付款金额</td>
                    <td>备注</td>
                    <td>实际付款金额</td>
                    <td width="5%"></td>
                </tr>
                <tr class="template repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                    <td id="index" class="dragHandle"></td>
                    <td><? $goodsForm->show('fpr_type');?></td>
                    <td><? $goodsForm->show('fpr_fty_customer');?></td>
                    <td><? $goodsForm->show('fpr_fty_customer_ap');?></td>
                    <td><? $goodsForm->show('fpr_pay_amount');?></td>
                    <td><? $goodsForm->show('fpr_remark');?></td>
                    <td></td>
                    <td><div id="del" onclick="delBomItem(this)"></div><input type="hidden" id="fpr_type_value" name="fpr_type_value" value="" disabled="disabled"/><input type="hidden" id="fpr_fty_customer_value" name="fpr_fty_customer_value" value="" disabled="disabled"/></td>
                </tr>
                <?
                for($i = 0; $i < $item_num; $i++){
                    ?>
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)">
                        <td id="index" class="dragHandle"></td>
                        <td><? $goodsForm->show('fpr_type'.$i);?></td>
                        <td><? $goodsForm->show('fpr_fty_customer'.$i);?></td>
                        <td><? $goodsForm->show('fpr_fty_customer_ap'.$i);?></td>
                        <td><? $goodsForm->show('fpr_pay_amount'.$i);?></td>
                        <td><? $goodsForm->show('fpr_remark'.$i);?></td>
                        <td align="center"><?=(isset($mod_result_item[$i]['actual_pay_amount'])?$mod_result_item[$i]['actual_pay_amount']:'')?></td>
                        <td><div id="del<?=$i?>" onclick="delBomItem(this)"><img src="../../sys/images/del-icon.png" onmouseout="$(this).css('opacity','0.5')" onmouseover="$(this).css('opacity','1')" style="opacity: 0.5;" title="Delete" /></div><input type="hidden" id="fpr_type_value<?=$i?>" name="fpr_type_value<?=$i?>" value="<?=$mod_result_item[$i]['type']?>" /><input type="hidden" id="fpr_fty_customer_value<?=$i?>" name="fpr_fty_customer_value<?=$i?>" value="<?=$mod_result_item[$i]['fty_customer']?>" /></td>
                    </tr>
                <?
                }
                if ($mod_result['status'] == 2) {
                    //下面是最后一个
                    ?>
                    <tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)"
                        onmouseout="product_item_mouseout(this)">
                        <td id="index" class="dragHandle"></td>
                        <td><? $goodsForm->show('fpr_type' . $i); ?></td>
                        <td><? $goodsForm->show('fpr_fty_customer' . $i); ?></td>
                        <td><? $goodsForm->show('fpr_fty_customer_ap' . $i); ?></td>
                        <td><? $goodsForm->show('fpr_pay_amount' . $i); ?></td>
                        <td><? $goodsForm->show('fpr_remark' . $i); ?></td>
                        <td></td>
                        <td>
                            <div id="del<?= $i ?>" onclick="delBomItem(this)"></div>
                            <input type="hidden" id="fpr_type_value<?= $i ?>" name="fpr_type_value<?= $i ?>" value=""
                                   disabled="disabled"/><input type="hidden" id="fpr_fty_customer_value<?= $i ?>"
                                                               name="fpr_fty_customer_value<?= $i ?>" value=""
                                                               disabled="disabled"/></td>
                    </tr>
                    <?
                }
                ?>
                </tbody>
            </table>
            <div class="line"></div>
        </div>
        <?
        if ($mod_result['status'] == 2) {
            $goodsForm->show('submitbtn');
        }
        ?>
    </fieldset>
    <?
    $goodsForm->end();
}
?>

<script>
    $(function(){
        $(".template").hide();
        //table tr层表单可拖动
        $("#tableDnD").tableDnD({dragHandle: ".dragHandle"});
    })
</script>