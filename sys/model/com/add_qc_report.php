<?php


if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

//禁止其他用户进入（临时做法）
/*
if($_SESSION['logininfo']['aName'] != 'zjn' && $_SESSION['logininfo']['aName'] != 'KEVIN'){
	$myerror->error('测试中，未开放！', 'main');
}
*/

//fb($_SESSION);

//注意啊，上传文件一定要开启multipart这个，否则$_FILES获取不到值，也就是在form头里面加上 enctype="multipart/form-data"
//加了这个之后type=file的项就不属于$_POST了，而是属于$_FILES
$goodsForm = new My_Forms(array('multipart' => true));
$formItems = array(
    //'d_id' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20, 'required' => 1, 'nostar' => true),
    'fty_id' => array('type' => 'select', 'options' => get_pcid(), 'required' => 1, 'nostar' => true),
    //'staff' => array('type' => 'text', 'minlen' => 1, 'maxlen' => 20),
    'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
);
$goodsForm->init($formItems);


if(!$myerror->getAny() && $goodsForm->check()){

    fb($_FILES);

    $item = array();

    $index = 0;
    $judge1 = false;//type
    $judge2 = false;//size
    $judge3 = false;//error
    $judge4 = false;//upload file

    //没有$_FILES是没有上传的框的情况
    if(!empty($_FILES)){
        foreach($_FILES as $key=>$v){

            //有$_FILE而name为空，就是有框，但是没有选择文件的情况
            if($v['name'] != ''){

                //用key里面包含的index信息来赋值数组
                $temp = explode('_', $key);
                $index = $temp[1];

                foreach($v['type'] as $x){
                    $judge1 = false;
                    if($x == 'image/jpg' || $x == 'image/jpeg' || $x == 'image/pjpeg' || $x == 'image/gif' || $x == 'image/png'){
                        $judge1 = true;
                    }else{
                        $judge1 = false;
                        break;
                    }
                }

                foreach($v['size'] as $y){
                    $judge2 = false;
                    if( ($y / 1024) <= 500 ){
                        $judge2 = true;
                    }else{
                        $judge2 = false;
                        break;
                    }
                }

                foreach($v['error'] as $z){
                    $judge3 = false;
                    if ($z > 0 && $z != 4){
                        $judge3 = false;
                    }else{
                        $judge3 = true;
                    }
                }

                for($i = 0; $i < count($v['name']); $i++){
                    $judge4 = false;

                    $ext = substr($v['name'][$i], -4);
                    $file_name = dateMore('mt').$ext;
                    @$item[$index]['photo'] .= $file_name.'|';

                    move_uploaded_file($v['tmp_name'][$i], iconv('UTF-8', 'GBK', $pic_path_qc_normal.$file_name));
                    if(file_exists(iconv('UTF-8','GBK', $pic_path_qc_normal.$file_name))) {
                        $small_photo = 's_' . $file_name;
                        //小图片不存在才进行缩小操作
                        if (!is_file($pic_path_qc_small . $small_photo) == true) {
                            makethumb($pic_path_qc_normal . $file_name, $pic_path_qc_small . $small_photo, 's');
                        }
                        $judge4 = true;
                    }else{
                        $judge4 = false;
                    }
                }
                $item[$index]['photo'] = trim($item[$index]['photo'], '|');

                $index++;
            }else{
                //什么图片也不传也是可以的
                $judge1 = $judge2 = $judge3 = $judge4 = true;
            }
        }
    }else{
        //什么图片也不传也是可以的
        $judge1 = $judge2 = $judge3 = $judge4 = true;
    }

    //只有图片上传成功，才能继续数据库操作
    if(!$judge1 || !$judge2 || !$judge3 || !$judge4){
        $myerror->error('图片上传失败! 请检测图片格式和大小,如果都没问题,请联系管理员! ('.var_dump($judge1).var_dump($judge2).var_dump($judge3).var_dump($judge4).')', 'BACK');
    }else{
        //fb($_POST);

        // $other_num 这个值是item之前post的个数
        $pre_num = 2;
        // $last_num 这个值是item之后post的个数
        $last_num = 1;
        // 这个值随每个item的post个数改变而改变（有一个隐藏表单pid）
        $each_num = 9;

        // item 的个数
        $item_num = (count($_POST) - $pre_num - $last_num) / $each_num;
        //fb($item_num);

        $mypost = array();
        foreach($_POST as $v){
            $mypost[] = $v;
        }
        fb($mypost);

        for($i = 0; $i < $item_num; $i++){
            $index = 0;
            $item[$i]['pid'] = $mypost[$i * $each_num + $pre_num + $index++];
            $item[$i]['remarka'] = $mypost[$i * $each_num + $pre_num + $index++];
            $item[$i]['num_pass'] = $mypost[$i * $each_num + $pre_num + $index++];
            $item[$i]['num_a'] = $mypost[$i * $each_num + $pre_num + $index++];
            $item[$i]['num_b'] = $mypost[$i * $each_num + $pre_num + $index++];
            $item[$i]['num_c'] = $mypost[$i * $each_num + $pre_num + $index++];
            $item[$i]['is_pass'] = $mypost[$i * $each_num + $pre_num + $index++];
            $item[$i]['remarkb'] = $mypost[$i * $each_num + $pre_num + $index++];
            $item[$i]['remarkc'] = $mypost[$i * $each_num + $pre_num + $index++];
        }
        fb($item);

        //die('maintance');

        $qc_id = autoGenerationID();
        $in_date = dateMore();

        $rs = $mysql->q('select id from qc_report where qc_id = ?', $qc_id);
        if(!$rs){
            $rs = $mysql->q('select id from qc_report where pcid = ?', $_POST['fty_id']);
            if(!$rs){
                $rs = $mysql->q('insert into qc_report values (NULL, '.moreQm(7).')', $qc_id, $_POST['fty_id'], $in_date, $in_date, '', $_SESSION['logininfo']['aName'], '');
                if($rs){
                    $success = true;
                    for($i = 0; $i < $item_num; $i++){
                        $rs_qci = $mysql->q('insert into qc_report_item values (NULL, '.moreQm(11).')',
                            $qc_id,
                            $_POST['pid'.$i],
                            $item[$i]['remarka'],
                            @$item[$i]['photo'],
                            $item[$i]['num_pass'],
                            $item[$i]['num_a'],
                            $item[$i]['num_b'],
                            $item[$i]['num_c'],
                            $item[$i]['is_pass'],
                            $item[$i]['remarkb'],
                            $item[$i]['remarkc']
                        );
                        if(!$rs_qci){
                            $success = false;
                        }
                    }

                    $myerror->ok('新增 QC Report 成功'.($success?'':'（新增item部分失败）').'!', 'com-search_qc_report&page=1');
                }else{
                    $myerror->error('由于系统原因，新增 QC Report 失败(ERROR 1)', 'BACK');
                }
            }else{
                $myerror->error('FTY ID '.$_POST['fty_id'].' already been added', 'BACK');
            }
        }else{
            $myerror->error('QC Report ID '. $qc_id .' already exist.', 'BACK');
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
    <!--h1 class="green">PRODUCT<em>* indicates required fields</em></h1-->



    <?php
    $goodsForm->begin();
    ?>
    <table width="60%" align="center">
        <tr align="center">
            <td colspan="4" class='headertitle'>Add QC</td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <!--        <tr class="formtitle">
            <td width="16%">QC NO. : <h6 class="required">*</h6></td>
            <td width="34%"><?/* $goodsForm->show('d_id');*/?></td>
            <td width="15%">审核 ： </td>
            <td width="35%"><?/* $goodsForm->show('staff');*/?></td>
        </tr>-->
    </table>
    <table width="60%" align="center">
        <tr class="formtitle">
            <td width="16%">厂单号 : </td>
            <td align="left"><? $goodsForm->show('fty_id');?></td>
            <!--td width="10%" align="left"><img title="add" style="opacity: 0.5;" onclick="addQc()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/add_small.png"></td>
            <td width="40%" align="left"><img title="delete" style="opacity: 0.5;" onclick="delQc()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="../fty/images/del_small.png"></td-->
        </tr>
    </table>
    <div class="line"></div>
    <br />
    <div style="text-align: center; color: #FF0000;">注：每张图片大小不得超过500KB，图片格式为jpg、png或gif。图片是与表单一同提交上传的，如果图片格式不正确，则表单也提交失败！</div>
    <br />
    <table id="delivery" width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' align="center">
        <tr bgcolor='#EEEEEE'>
            <th>款号 & 订单总数</th>
            <!--            <th>图片</th>-->
            <!--            <th>总数（件）</th>-->
            <!--            <th>已检（件）</th>-->
            <th>问题</th>
            <th>程度：数量</th>
            <th>结果/行动</th>
            <th>工厂回应</th>
            <!--th colspan="2">操作</th-->
        </tr>
        <tbody id="tbody" class="qc" align="center"></tbody>
        <tr>
            <td colspan="4"></td>
            <td><? $goodsForm->show('submitbtn');?></td>
            <!--td></td>
            <td></td-->
        </tr>
    </table>
    <br />
    <br />
    <br />
    <?
    $goodsForm->end();
}

//20140211 改为不能直接add qc report 而要从，qc schedule点过来带参数，才能添加
if(isset($_GET['fty_id']) && $_GET['fty_id'] != ''){
    echo "<script>changeQcNew($('#fty_id'))</script>";
}

?>
<!--link href="/ui/swfupload/css/default.css" rel="stylesheet" type="text/css" />
<script src="/ui/swfupload/js/swfupload.js"></script>
<script src="/ui/swfupload/js/handlers.js"></script>
<script src="/ui/swfupload/js/fileprogress.js"></script-->

<link href="/ui/swfupload/css/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/ui/swfupload/js/swfupload.js"></script>
<script type="text/javascript" src="/ui/swfupload/js/swfupload.queue.js"></script>
<script type="text/javascript" src="/ui/swfupload/js/fileprogress.js"></script>
<script type="text/javascript" src="/ui/swfupload/js/handlers.js"></script>

<!--<script>
    //20140211 改为不能直接add qc report 而要从，qc schedule点过来带参数，才能添加
    $(function(){
        //20131203 改用changeQcNew 不用之前的 changeQc swfupload了，太麻烦了，而且不是提交表单才上传图片，需要先上传图片
        $("#fty_id").selectbox({onChange: changeQcNew});
    })
</script>-->