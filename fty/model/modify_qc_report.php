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

if($myerror->getWarn()){
    require_once(ROOT_DIR.'model/inside_warn.php');
}else{

    if(isset($_GET['delid']) && $_GET['delid'] != ''){
        //由於指定了foreign key，所以要先刪item裏的內容
        $rtn1 = $mysql->q('delete from qc_report_item where qc_id = ?', $_GET['delid']);
        $rtn2 = $mysql->q('delete from qc_report where qc_id = ?', $_GET['delid']);
        if($rtn2){
            $myerror->ok('Delete QC Report success!', 'com-search_qc_report&page=1');
        }else{
            $myerror->error('Delete QC Report failure!', 'com-search_qc_report&page=1');
        }
    }else{
        if(isset($_GET['modid']) && $_GET['modid'] != ''){
            $mod_result = $mysql->qone('SELECT * FROM qc_report WHERE qc_id = ?', $_GET['modid']);

            $item_result = $mysql->q('SELECT qci.*, p.photos, po.quantity FROM qc_report qc, qc_report_item qci, product p, purchase_item po WHERE qci.qc_id = ? and qci.pid = p.pid and qc.pcid = po.pcid and qc.qc_id = qci.qc_id and qci.pid = po.pid', $_GET['modid']);
            $qc_item_rtn = $mysql->fetch();
            //die();
            $qc_item_num = count($qc_item_rtn);
            //fb($qc_item_num);

            //图片如果没有重新上传，则使用旧的图片信息！！！！！而下面如果有重新上传图片，图片名会覆盖旧的值！！！！！
            for($i = 0; $i < $qc_item_num; $i++){
                $item[$i]['photo'] = $qc_item_rtn[$i]['photo'];
            }
            //fb($item);
        }else{
            die('Need modid!');
        }

        //注意啊，上传文件一定要开启multipart这个，否则$_FILES获取不到值，也就是在form头里面加上 enctype="multipart/form-data"
        //加了这个之后type=file的项就不属于$_POST了，而是属于$_FILES
        $goodsForm = new My_Forms(array('multipart' => true));
        $formItems = array(
            'fty_id' => array('type' => 'select', 'options' => get_fty_purchase(), 'required' => 1, 'nostar' => true, 'value' => isset($mod_result['pcid'])?$mod_result['pcid']:''),
            'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
        );

        //序号从0开始
        for($i = 0; $i < $qc_item_num; $i++){
            //因为加号的图标无发加在textarea div里面
            //$formItems['remarka'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 500, 'rows' => 2, 'addon' => 'style="width:300px"', 'value' => isset($qc_item_rtn[$i]['remarka'])?$qc_item_rtn[$i]['remarka']:'');
            $formItems['num_pass'.$i] = array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 10, 'addon' => 'style="width:40px" onblur=""', 'value' => isset($qc_item_rtn[$i]['num_pass'])?$qc_item_rtn[$i]['num_pass']:0);
            $formItems['num_a'.$i] = array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 10, 'addon' => 'style="width:40px" onblur=""', 'value' => isset($qc_item_rtn[$i]['num_a'])?$qc_item_rtn[$i]['num_a']:0);
            $formItems['num_b'.$i] = array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 10, 'addon' => 'style="width:40px" onblur=""', 'value' => isset($qc_item_rtn[$i]['num_b'])?$qc_item_rtn[$i]['num_b']:0);
            $formItems['num_c'.$i] = array('type' => 'text', 'restrict' => 'number', 'minlen' => 1, 'maxlen' => 10, 'addon' => 'style="width:40px" onblur=""', 'value' => isset($qc_item_rtn[$i]['num_c'])?$qc_item_rtn[$i]['num_c']:0);
            $formItems['is_pass'.$i] = array('type' => 'select', 'options' => array(array('合格', '1'), array('不合格', '2'), array('待验', '3')), 'required' => 1, 'nostar' => true, 'value' => isset($qc_item_rtn[$i]['is_pass'])?$qc_item_rtn[$i]['is_pass']:'');
            $formItems['remarkb'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 500, 'rows' => 2, 'addon' => 'style="width:100px"', 'value' => isset($qc_item_rtn[$i]['remarkb'])?$qc_item_rtn[$i]['remarkb']:'');
            $formItems['remarkc'.$i] = array('type' => 'textarea', 'minlen' => 1, 'maxlen' => 500, 'rows' => 2, 'addon' => 'style="width:100px"', 'value' => isset($qc_item_rtn[$i]['remarkc'])?$qc_item_rtn[$i]['remarkc']:'');
        }
        $goodsForm->init($formItems);


        if(!$myerror->getAny() && $goodsForm->check()){

            fb($_FILES);

            $index = 0;
            $judge1 = false;//type
            $judge2 = false;//size
            $judge3 = false;//error
            $judge4 = false;//upload file

            //没有$_FILES是没有上传的框的情况
            if(!empty($_FILES)){
                foreach($_FILES as $key=>$v){

                    //有$_FILE而name为空，就是有框，但是没有选择文件的情况
                    for($i = 0; $i < count($v['name']); $i++){

                        if($v['name'][$i] == ''){
                            //什么图片也不传也是可以的
                            $judge1 = $judge2 = $judge3 = $judge4 = true;
                        }else{
                            //用key里面包含的index信息来赋值数组
                            $temp = explode('_', $key);
                            $index = $temp[1];

                            $judge1 = false;
                            if($v['type'][$i] == 'image/jpg' || $v['type'][$i] == 'image/jpeg' || $v['type'][$i] == 'image/pjpeg' || $v['type'][$i] == 'image/gif' || $v['type'][$i] == 'image/png'){
                                $judge1 = true;
                            }else{
                                $judge1 = false;
                                break;
                            }

                            $judge2 = false;
                            if( ($v['size'][$i] / 1024) <= 500 ){
                                $judge2 = true;
                            }else{
                                $judge2 = false;
                                break;
                            }

                            $judge3 = false;
                            if ($v['error'][$i] > 0 && $v['error'][$i] != 4){
                                $judge3 = false;
                            }else{
                                $judge3 = true;
                            }

                            $judge4 = false;
                            $ext = substr($v['name'][$i], -4);
                            $file_name = dateMore('mt').$ext;
                            @$item[$index]['photo'] .= '|'.$file_name;

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

                            $item[$index]['photo'] = trim($item[$index]['photo'], '|');
                        }
                        $index++;
                    }
                }
            }else{
                //什么图片也不传也是可以的
                $judge1 = $judge2 = $judge3 = $judge4 = true;
            }

            //fb($item);

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
                //fb($mypost);

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

                $rs = $mysql->q('update qc_report set pcid = ?, mod_date = ?, mod_by = ? where qc_id = ?', $_POST['fty_id'], dateMore(), $_SESSION['logininfo']['aName'], $_GET['modid']);
                if($rs !== false){
                    $rtn = $mysql->q('delete from qc_report_item where qc_id = ?', $_GET['modid']);

                    $success = true;
                    for($i = 0; $i < $item_num; $i++){
                        $rs_qci = $mysql->q('insert into qc_report_item values (NULL, '.moreQm(11).')',
                            $_GET['modid'],
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

                    $myerror->ok('修改 QC Report 成功'.($success?'':'（修改item部分失败，item信息丢失）').'!', 'com-search_qc_report&page=1');
                }else{
                    $myerror->error('由于系统原因，修改 QC Report 失败(ERROR 1)', 'BACK');
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
            <tbody id="tbody" class="qc" align="center">
            <?
            for($i = 0; $i < $qc_item_num; $i++){

                $photo = '';
                $photo_array = explode('|', $qc_item_rtn[$i]['photo']);
                if(!empty($photo_array) && $qc_item_rtn[$i]['photo'] != ''){
                    foreach($photo_array as $v){
                        $photo .= '<div><a href="/fty/'.$pic_path_qc_normal.$v.'" target="_blank"><img src="/fty/'.$pic_path_qc_normal.$v.'" width="100" height="80" /></a><input type="file" name="photo_'.$i.'[]" /><img style="cursor: pointer; width: 16px;" src="../images/del_small.png" onclick="del_upload_photo(this)" required=1 /></div>';
                    }
                }
                ?>
                <tr class="formtitle">

                    <td>
                        <input type="hidden" value="<?=$qc_item_rtn[$i]['pid']?>" name="pid<?=$i?>" />
<!--                        <input type="hidden" value="--><?//=$qc_item_rtn[$i]['quantity']?><!--" name="quantity--><?//=$i?><!--" />-->
                        <?=$qc_item_rtn[$i]['pid']?>
                        <br />
                        订单总数：<?=$qc_item_rtn[$i]['quantity']?>
                        <br />
                        <?=product_img($qc_item_rtn[$i]['photos'])?>
                    </td>

                    <td>
                        <div id="divremarka<?=$i?>" class="formfield">
                            <textarea id="remarka<?=$i?>" class="tainit tainit" strlen="1,500" maxlength="500" style="width:300px" rows="2" name="remarka<?=$i?>"><?=$qc_item_rtn[$i]['remarka']?></textarea><img onclick="add_upload_field(this)" id="<?=$i?>" style="cursor: pointer; margin:0px 5px 25px;" src="../images/add_small.png" id="up_button<?=$i?>" />
                        </div>
                        <div id="divupload_photo<?=$i?>" class="formfield"><?=$photo?></div>
                    </td>

                    <td>
                        <? $goodsForm->show('num_pass'.$i) ?>
                        <? $goodsForm->show('num_a'.$i) ?>
                        <? $goodsForm->show('num_b'.$i) ?>
                        <? $goodsForm->show('num_c'.$i) ?>
                        <div class="formfield" style="text-align: left;">总检(件)：<span id="num_all"></span></div>
                    </td>

                    <td>
                        <div style="float:left"><? $goodsForm->show('is_pass'.$i) ?></div>
                        <br />
                        <br />
                        <? $goodsForm->show('remarkb'.$i) ?>
                    </td>

                    <td>
                        <? $goodsForm->show('remarkc'.$i) ?>
                    </td>
                </tr>

            <?
            }
            ?>
            </tbody>
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

    <script>
        $(function(){
            //20131203 改用changeQcNew 不用之前的 changeQc swfupload了，太麻烦了，而且不是提交表单才上传图片，需要先上传图片
            $("#fty_id").selectbox({onChange: changeQcNew});
        })

        function getQueryStringRegExp(name)
        {
            var reg = new RegExp("(^|\\?|&)"+ name +"=([^&]*)(\\s|&|$)", "i");
            if (reg.test(location.href)) return unescape(RegExp.$2.replace(/\+/g, " ")); return "";
        };

        function del_upload_photo(obj){
            if(confirm('This operation will lead to the deletion of the photo could not be resumed, ' +
                'confirmed to delete?')){
                var qc_id = getQueryStringRegExp('modid');
                var pid = $(obj).parent().parent().parent().prev().children().val();
                var pic_path = $(obj).prev().prev().children().attr('src');
                var pic_arr = pic_path.split('/');
                var pic = pic_arr[pic_arr.length-1];

                qs = 'ajax=1&act=ajax-del_qc_report_photo&qc_id='+qc_id+'&pid='+pid+'&pic='+pic;
                $.ajax({
                    type: "GET",
                    url: "index.php",
                    data: qs,
                    cache: false,
                    dataType: "html",
                    error: function(){
                        alert('系统错误，删除图片失败');
                    },
                    success: function(data){
                        if(data.indexOf('!no-') < 0){
                            alert('Delete photo success !');
                            $(obj).parent().remove();
                        }else{
                            alert('Delete photo failure !');
                        }
                    }
                })
            }
        }
    </script>

<?
}
?>