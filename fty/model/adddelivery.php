<?php


if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//禁止其他用户进入（临时做法）
/*
if($_SESSION['ftylogininfo']['aName'] != 'zjn' && $_SESSION['ftylogininfo']['aName'] != 'KEVIN'){
	$myerror->error('测试中，未开放！', 'main');
}
*/
//默认给工厂用户显示其客户为本公司
$select = $mysql->qone('select company from fty_client where created_by = ?', 'all');

$goodsForm = new My_Forms();
$formItems = array(
    //'d_id' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'nostar' => true),
    'fty_id' => array('type' => 'select', 'options' => get_fty_purchase()),
    'client_company' => array('type' => 'select', 'options' => get_fty_client_company(), 'selected' => $select['company']),
//    'client_address' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 100),
    'wh_id' => array('type' => 'select', 'options' => get_warehouse_info(2), 'required' => 1, 'nostar' => true),
    'address' => array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 100, 'addon' => 'style="width:200px"'),
    'staff' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:100px"'),
    'express_cost' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'restrict' => 'number', 'addon' => 'style="width:40px"', 'nostar' => true),
    'express_id' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'addon' => 'style="width:100px"'),
    'remark' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 200, 'addon' => 'style="width:250px"'),
    //add d_date 20130813
    'd_date' => array('type' => 'text', 'restrict' => 'date', 'addon' => 'style="width:100px"', 'required' => 1, 'nostar' => true),
    'submitbtn'	=> array('type' => 'submit', 'value' => '提交'),
);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){
    //fb($_POST);
    //mod 20130127 自动生成ID如: "S001-20130117-01"  supplier ID – 日期 – 流水號
    //mod 20130206 修改了生成的规则，改为不同的工厂用各自的流水号
    $d_id = fty_autoGenerationID();

    //product item 的个数
    //'7'这个值是除item外的post的个数
    //mod 20130127 自动生成ID，就变成6了,开始的index变成3了
    //mod 20130228 多了个备注，就变成7了，审核放到下面，item_first就变成2了
    //mod 20130228 多了client_company和client_address，就变成9了。而且这两个都是在前面，所以item_first就变成4了
    //mod 20130813 加了日期d_date，现在是10了
    //mod 20130228 多了warehouse，就变成11了。而且这个是在前面，所以item_first就变成5了
    $item_num = (count($_POST) - 11) / 10;//！！后面这个'10'这个值随js里item的post个数改变而改变
    $item_first = 5;

    $wh = explode('|', $_POST['wh_id']);
    $wh_id = '';
    $wh_name = '';
    if(!empty($wh)){
        $wh_id = $wh[0];
        $wh_name = $wh[1];
    }

    //$myerror->info(count($_POST));
    //$myerror->info($item_num);
    //$myerror->info($_POST);

    $mypost = array();
    foreach($_POST as $v){
        $mypost[] = $v;
    }
    //fb($mypost);die();

    $item = array();
    for($i = 0; $i < $item_num; $i++){
        $item[$i]['box_num'] = $mypost[$i*10+$item_first];
        $item[$i]['inner_box_num'] = $mypost[$i*10+$item_first+1];
        $item[$i]['po_id'] = $mypost[$i*10+$item_first+2];
        $item[$i]['p_id'] = $mypost[$i*10+$item_first+3];
        $item[$i]['quantity'] = $mypost[$i*10+$item_first+4];
        $item[$i]['weight'] = $mypost[$i*10+$item_first+5];
        $item[$i]['size_l'] = $mypost[$i*10+$item_first+6];
        $item[$i]['size_w'] = $mypost[$i*10+$item_first+7];
        $item[$i]['size_h'] = $mypost[$i*10+$item_first+8];
        $item[$i]['remark'] = $mypost[$i*10+$item_first+9];
    }

    //fb($d_id);fb($item);die();

    $time = dateMore();

    //工厂自己的出货单号必须是唯一的
    $rs = $mysql->q('select id from delivery where d_id = ?', $d_id);
    if(!$rs){
        $rs = $mysql->q('insert into delivery values (NULL, '.moreQm(16).')', $d_id, $_POST['d_date'], $_POST['express_cost'], $_POST['express_id'], '', $_POST['staff'], $_SESSION['ftylogininfo']['aFtyName'], $_POST['remark'], $_POST['client_company'], /*$_POST['client_address']*/'', $_POST['address'], $wh_id, $time, $time, $_SESSION['ftylogininfo']['aName'], '');
        if($rs){
            $success = true;

            //合计
            $total_all = 0;

            for($i = 0; $i < $item_num; $i++){
                //找出purchase_item信息
                //mod 20130219 客户显示改为cid
                $rtn_purchase = $mysql->qone('select p.cid, i.price, i.ccode, d.photos, d.description, d.description_chi from purchase u, purchase_item i, proforma p, product d where i.pcid = u.pcid and u.pcid = ? and p.pvid = u.reference and i.pid = ? and d.pid = i.pid', $item[$i]['po_id'], $item[$i]['p_id']);

                $rs_delivery = $mysql->q('insert into delivery_item values (NULL, '.moreQm(15).')',
                    $d_id,
                    $item[$i]['po_id'],
                    $item[$i]['box_num'],
                    $item[$i]['inner_box_num'],
                    $rtn_purchase['cid'],
                    $item[$i]['p_id'],
                    $rtn_purchase['ccode'],
                    $item[$i]['quantity'],
                    $rtn_purchase['price'],
                    $item[$i]['quantity']*$rtn_purchase['price'],
                    $item[$i]['weight'],
                    $item[$i]['size_l'],
                    $item[$i]['size_w'],
                    $item[$i]['size_h'],
                    $item[$i]['remark']
                );
                if(!$rs_delivery){
                    $success = false;
                }

                $total_all += $item[$i]['quantity']*$rtn_purchase['price'];


                //20130912 加记录到warehouse里面去
                //(不知道wh_type是干嘛的。。)
                //(工厂出货，在添加的界面应该都是加吧)
                //(arrival_date 用post 的 出货日期，in_date 用当前时间)
                //(remark 加了来源的标志)
                //20131020 先不对warehouse有操作了,以后再说
                //20140503 再做更新warehouse的事
                $rs_warehouse = $mysql->q('insert into warehouse_item_hist (wh_id, wh_name, pid, action, cost_rmb, qty, photo, arrival_date, in_date, created_by, remark) values ('.moreQm(11).')', $wh_id, $wh_name, $item[$i]['p_id'], '+', $rtn_purchase['price'], $item[$i]['quantity'], $rtn_purchase['photos'], $_POST['d_date'], dateMore(), $_SESSION['ftylogininfo']['aName'], 'Add from delivery. '.$_POST['remark']);

                if($rs_warehouse){
                    if($mysql->q('select id from warehouse_item_unique where wh_id = ? and pid = ?', $wh_id, $item[$i]['p_id'])){
                        $mysql->q('update warehouse_item_unique set qty = qty + ?, cost_rmb = ? where wh_id = ? and wh_name = ? and pid = ?', $item[$i]['quantity'], $rtn_purchase['price'], $wh_id, $wh_name, $item[$i]['p_id']);
                    }else{
                        $mysql->q('insert into warehouse_item_unique (wh_id, wh_name, pid, description, description_chi, qty, cost_rmb, photo, arrival_date, in_date, created_by, remark) values ('.moreQm(12).')', $wh_id, $wh_name, $item[$i]['p_id'], $rtn_purchase['description'], $rtn_purchase['description_chi'], $item[$i]['quantity'], $rtn_purchase['price'], $rtn_purchase['photos'], $_POST['d_date'], dateMore(), $_SESSION['ftylogininfo']['aName'], 'Add from delivery. '.$_POST['remark']);
                    }
                }
            }

            //更新total_all，保存一下这个值，省得以后要用还要去计算
            $total_all += $_POST['express_cost'];
            $mysql->q('update delivery set total_all = ? where d_id = ?', $total_all, $d_id);


            //更新 purchase 状态
            //20141216 检查此出货单中的po item是否全部出完，如果是则改po状态
            $mysql->q('select po_id from delivery_item where d_id = ? group by po_id', $d_id);
            $po_rtn = $mysql->fetch();
            $po_status_tips = '';
            foreach($po_rtn as $v){
                if(checkPurchaseItemIsOut($v['po_id'])){
                    $status = changePurchaseStatus($v['po_id'], '(S)');
                    $po_status_tips .= '(Change '.$v['po_id'].' status '.$status.')';
                }
            }


            //快递费记录进overheads
            //2012.7.2 快递费现在是基于工厂的单子的，而不是基于每个purchase的，所以现在无法更新purchase的overheads
            //$mysql->q('insert into overheads (po_no, po_date, description, cost, cost_remark) values (?, ?, ?, ?, ?)', $_POST['fty_id'], dateMore(), 'Freight cost', $_POST['express_cost'], 'add by system');

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_FTY_ADD_DELIVERY, $_SESSION["ftylogininfo"]["aName"]." <i>add delivery</i> '".$d_id."' in fty", ACTION_LOG_FTY_ADD_DELIVERY_S, "", "", 0);

            $myerror->ok('新增出货单 成功'.$po_status_tips.($success?'':'（新增item部分失败）').'!', 'searchdelivery&page=1');
        }else{
            $myerror->error('由于系统原因，新增出货单 失败(ERROR 1)', 'BACK');
        }
    }else{
        $myerror->warn('出货单 '. $d_id.' 已存在，请不要重复添加', 'searchdelivery&page=1');
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
    <!--h1 class="green">PRODUCT<em>* indicates required fields</em></h1-->



    <?php
    $goodsForm->begin();
    ?>
    <table width="55%" align="center">
        <tr align="center">
            <td colspan="4" class='headertitle'>新增出货单</td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr class="formtitle" valign="top">
            <td width="17%">客户公司 ：</td>
            <td width="33%"><? $goodsForm->show('client_company');?></td>
            <td width="17%"><? //客户公司地址 ：?></td>
            <td width="33%"><? //$goodsForm->show('client_address');?></td>
        </tr>
        <tr class="formtitle" valign="top">
            <td width="17%">仓库 ：</td>
            <td width="33%"><? $goodsForm->show('wh_id');?></td>
            <td width="17%">地址 ： </td>
            <td width="33%"><? $goodsForm->show('address');?></td>
        </tr>
    </table>
    <div class="line"></div>
    <div class="line"></div>
    <table width="55%" align="center">
        <tr class="formtitle">
            <td width="17%">订单号 ： </td>
            <td width="33%"><? $goodsForm->show('fty_id');?></td></td>
            <td width="10%" align="left"><img title="添加" style="opacity: 0.5;" onclick="addPurchase()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"><!--a id="addpurchase" onclick="addPurchase()" href="#">添加</a--></td>
            <td width="40%" align="left"><img title="删除" style="opacity: 0.5;" onclick="delPurchase()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"><!--a id="delpurchase" onclick="delPurchase()" href="#">删除</a--></td>
        </tr>
    </table>
    <div class="line"></div>
    <br />
    <table id="delivery" width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' align="center">
        <tr bgcolor='#EEEEEE'>
            <th>箱数</th>
            <th>内箱</th>
            <th>订单号</th>
            <th>客户</th>
            <th>款号</th>
            <th>客号</th>
            <th>数量</th>
            <th>单价（元）</th>
            <th>金额（元）</th>
            <th>外箱重量（KG）</th>
            <th>尺寸-长（CM）</th>
            <th>尺寸-宽（CM）</th>
            <th>尺寸-高（CM）</th>
            <th>备注</th>
            <th colspan="2">操作</th>
        </tr>
        <tbody id="tbody" class="delivery" align="center"></tbody>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td align="right">运费：</td>
            <td align="right"><? $goodsForm->show('express_cost');?></td>
            <td></td>
            <td></td>
            <td></td>
            <td align="right">运单编号：</td>
            <td align="center"><? $goodsForm->show('express_id');?></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td align="right">总数：</td>
            <td id="totalQ" align="right"></td>
            <td align="right">合计：</td>
            <td id="total" align="right"></td>
            <td id="totalW" align="right"></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td align="right" colspan="7">备注：（如送货地址与上述不同，请在此填上）</td>
            <td colspan="6"><? $goodsForm->show('remark');?></td>
            <td align="right">审核：</td>
            <td colspan="2"><? $goodsForm->show('staff');?></td>
        </tr>
        <tr>
            <td align="right" colspan="7"></td>
            <td colspan="6"></td>
            <td align="right">出货日期：</td>
            <td colspan="2"><? $goodsForm->show('d_date');?></td>
        </tr>
        <tr>
            <td colspan="13"></td>
            <td><? $goodsForm->show('submitbtn');?></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    <br />
    <br />
    <br />
    <?
    $goodsForm->end();
}
?>
<script>
    $(function(){
        //默认选中本公司，显示地址
        changeClientCompany();
        //选择事件绑定
        $('#client_company').selectbox({onChange: changeClientCompany});
        $('#wh_id').selectbox({onChange: changeWarehouse});
        //下面这句里面加了.selectbox(),上面的onChange事件绑定就无效了
        //$("#client_company").selectOptions("bbb").selectbox();
    })
</script>