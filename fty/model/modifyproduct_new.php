<?php
/**
 * Author: zhangjn
 * Date: 2017/3/18
 * Time: 17:05
 */
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{
    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        $rtn = $mysql->q('delete from product where pid = ?', $_GET['delid']);
        if($rtn){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['ftylogininfo']['aID'], $ip_real
                , ACTION_LOG_FTY_DEL_PRODUCT, $_SESSION["ftylogininfo"]["aName"]." <i>delete product</i> '".$_GET['delid']."' in fty", ACTION_LOG_FTY_DEL_PRODUCT_S, "", "", 0);

            $myerror->ok('删除产品资料 ('.$_GET['delid'].') 成功!', 'searchproduct_new&page=1');
        }else{
            $myerror->error('删除产品资料 ('.$_GET['delid'].') 失败', 'searchproduct_new&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $_SESSION['modid'] = $_GET['modid'];
            //普通用户只能搜索到自己开的单
            /*			$where_sql = '';
                        if (!isFtyAdmin()){
                            $where_sql = " AND created_by = '".$_SESSION['ftylogininfo']['aName']."'";
                        }*/
            //20130806 加where有多了引号的问题,所以直接在参数里判断了
            $mod_result = $mysql->qone('SELECT * FROM product WHERE pid = ? and sid like ?', $_GET['modid'], (isFtyAdmin())?'%%':'%'.$_SESSION['ftylogininfo']['aFtyName'].'%');
        }/*elseif(isset($_GET['copypid']) && $_GET['copypid'] != ''){
			$_SESSION['modid'] = $_GET['copypid'];
			$mod_result = $mysql->qone('SELECT * FROM product WHERE pid = ?', $_GET['copypid']);
			$mod_result['pid'] = '';
		}*/



        $goodsForm = new My_Forms();
        $formItems = array(

            'pid' => array('title' => '产品编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, /*isset($_GET['copypid'])?'':*/'readonly' => 'readonly', 'value' => isset($mod_result['pid'])?$mod_result['pid']:''),
            'cost_rmb' => array('title' => '价格', 'type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'restrict' => 'number', 'value' => isset($mod_result['cost_rmb'])?$mod_result['cost_rmb']:'', 'required' => 1),
            'ccode' => array('title' => '客户编号', 'type' => 'text', 'minlen' => 1, 'maxlen' => 30, 'value' => isset($mod_result['ccode'])?$mod_result['ccode']:''),
            'description_chi' => array('title' => '产品描述', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'rows' => 5, 'required' => 1, 'value' => isset($mod_result['description_chi'])?$mod_result['description_chi']:''),

            'type' => array('title' => '类型', 'type' => 'select', 'options' => get_bom_lb(2), 'value' => isset($mod_result['type'])?$mod_result['type']:''),
            'sample_order_no' => array('title' => '板单编号', 'type' => 'select', 'options' => get_sample_order_no_fty(), 'value' => isset($mod_result['sample_order_no'])?$mod_result['sample_order_no']:''),

            'submitbtn'	=> array('type' => 'submit', 'value' => ' 保存 '),
        );
        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){

            $pid = $_POST['pid'];
            $sid = $_SESSION['ftylogininfo']['aFtyName'];//从session
            $ccode = $_POST['ccode'];
            $mod_date = dateMore();
            $description_chi = $_POST['description_chi'];
            $cost_rmb = $_POST['cost_rmb'];
            $photos = isset($mod_result['photos'])?$mod_result['photos']:'';
            $type = $_POST['type'];
            $sample_order_no = $_POST['sample_order_no'];

            /*
            if(isset($_GET['copypid'])){
                $result = $mysql->q('insert into product (pid, in_date, cat_num, description, description_chi, sid, ccode, ccode, cost_rmb, cost_remark, exclusive_to, photos) values ('.moreQm(12).')', $pid, $in_date, $cat_num, $description, $description_chi, $sid, $ccode, $ccode, $cost_rmb, $cost_remark, $exclusive_to, $photos);
                //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                if($result !== false){
                    $myerror->ok('新增产品资料 成功!', 'com-searchproduct&page=1');
                }else{
                    $myerror->error('由于系统原因，新增产品资料 失败', 'BACK');
                }
            }else{
                */
            $result = $mysql->q('update product set type = ?, sample_order_no = ?, description_chi = ?, cost_rmb = ?, ccode = ?, sid = ?, mod_date = ?, photos = ? where pid = ?', $type, $sample_order_no, $description_chi, $cost_rmb, $ccode, $sid, $mod_date, $photos, $pid);
            //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
            if($result !== false){
                //add action log
                $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                    , $_SESSION['ftylogininfo']['aID'], $ip_real
                    , ACTION_LOG_FTY_MOD_PRODUCT, $_SESSION["ftylogininfo"]["aName"]." <i>modify product</i> '".$_GET['modid']."' in fty", ACTION_LOG_FTY_MOD_PRODUCT_S, "", "", 0);

                $myerror->ok('修改产品资料 ('.$pid.') 成功!', 'searchproduct_new&page=1');
            }else{
                $myerror->error('由于系统原因，修改产品资料 ('.$pid.') 失败', 'BACK');
            }
            //}
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
        <fieldset class="center2col" style="width:60%">
            <legend class='legend'>1.上传图片</legend>
            <?
            if(isset($mod_result['photos']) && $mod_result['photos'] != ''){
                //20130715 管理员取图片的路径与普通用户不同
//                if(isFtyAdmin()){
//                    $pic_path_fty = "upload/fty/".$mod_result['sid'].'/';
//                }
                //非要转为GBK，不然中文 getimagesize 就认不出，太坑爹了
                $arr = getimagesize(ROOT_DIR .'sys/'. $pic_path_com . iconv('UTF-8', 'GBK', $mod_result['photos']));
                $pic_width = $arr[0];
                $pic_height = $arr[1];
                $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                echo '<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com . $mod_result['photos'].'" class="tooltip2" target="_blank" title="'.$mod_result['photos'].'"><img src="/sys/'. $pic_path_com . $mod_result['photos'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
                //echo "<b><a class='button' href='?act=upload_photo_mod&chg=".$mod_result['photos']."'>更换图片</a></b></div>";
            }else{
                echo "<div style='margin-left:28px;'><img src='../images/nopic.gif' border='0' width='80' height='60'><!--br /><a class='button' href='?act=upload_photo_mod'>上传图片</a--></div>";
                /*
                <div class="line"></div>

                <img src="<?= (isset($Pic) ? $Pic : '../images/nopic.gif')?>" id="p_pkPic" border="0" height="70">
                <input name="pkPic" type="hidden" id="pkPic" value="<?=$Pic?>">
                <div class="line"></div>
                <iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="500" id="titleimg_up" frameborder="0"></iframe>
                */
            }
            ?>
        </fieldset>
        <fieldset class="center2col" style="width:60%">
            <legend class='legend'>2.填表</legend>
            <?php
            $goodsForm->begin();
            ?>
            <table width="60%" id="table">
                <tr class="formtitle">
                    <td><? $goodsForm->show('pid');?></td>
                    <td><? $goodsForm->show('cost_rmb');?></td>
                    <td><? $goodsForm->show('ccode');?></td>
                </tr>
                <tr>
                    <td colspan="2"><? $goodsForm->show('description_chi');?></td>
                </tr>
                <tr>
                    <td><? $goodsForm->show('type');?></td>
                    <td><? $goodsForm->show('sample_order_no');?></td>
                    <td></td>
                </tr>
            </table>
            <div class="line"></div>
            <?
            $goodsForm->show('submitbtn');

            $goodsForm->end();
            ?>
        </fieldset>
        <?
        $goodsForm->end();

    }
}
?>