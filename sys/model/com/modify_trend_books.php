<?php
/**
 * Author: night
 * Date: 2016/8/20
 * Time: 15:41
 */

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(isset($_GET['delid']) && $_GET['delid'] != ''){

    $p = $mysql->qone('select photo from trend_books where id = ?', $_GET['delid']);
    @unlink($pic_path_com_trend_books.$p['photos']);
    @unlink($pic_path_small_trend_books.'s_'.$p['photos']);
    @unlink($pic_path_small_trend_books.'m_'.$p['photos']);
    @unlink($pic_path_small_trend_books.'l_'.$p['photos']);
    @unlink($pic_path_watermark.'l_water_'.$p['photos']);

    $rtn = $mysql->q('delete from trend_books where id = ?', $_GET['delid']);
    if($rtn){
        $myerror->ok('删除Trend Books资料 ('.$_GET['delid'].') 成功!', 'com-search_trend_books&page=1');
    }else{
        $myerror->error('删除Trend Books资料 ('.$_GET['delid'].') 失败', 'com-search_trend_books&page=1');
    }
}else{
    if(isset($_GET['modid']) && $_GET['modid'] != ''){
        $mod_result = $mysql->qone('SELECT * FROM trend_books WHERE id = ?', $_GET['modid']);
    }

    // form加这个参数才能上传文件，否则获取不到 $_FILE
    $goodsForm = new My_Forms(array('multipart'=>true));
    $formItems = array(
        'trend_books_date' => array('title' => 'Date', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['trend_books_date'])?substr($mod_result['trend_books_date'], 0, 10):''),
        'submitbtn'	=> array('type' => 'submit', 'value' => ' Submit '),
    );
    $goodsForm->init($formItems);
    $goodsForm->begin();

    if(!$myerror->getAny() && $goodsForm->check()){

        //20130716
        //未上传图片是被允许的，一但上传则有格式和大小的限制
        //这里是用 $_FILES['photo'] photo是 input 的属性，而不是用 $_FILES['file']['type']
        if( ((@$_FILES['photo']['type'] == 'image/jpg' || @$_FILES['photo']['type'] == 'image/jpeg' || @$_FILES['photo']['type'] == 'image/gif' || @$_FILES['photo']['type'] == 'image/pjpeg' || @$_FILES['photo']['type'] == 'image/png') && (@$_FILES['photo']['size'] / 1024) <= 500) || @$_FILES['photo']['name'] == '' ){

            //4 是没有文件上传
            if (@$_FILES['photo']['error'] > 0 && @$_FILES['photo']['error'] != 4){
                $myerror->error('Upload photo Error ! Return Code: '.@$_FILES['photo']['error'], 'BACK');
            }else{
                $target = '';
                $add_tip = '';
                if(@$_FILES['photo']['name'] == ''){
                    $target = $mod_result['photo'];

                    $add_tip = '';
                }else{
                    // 转为大写字母(pid + 时间 + 原图片的后缀名)
                    // 图片的格式一般都是3位字母吧，没有其他的吧。。。？
                    $temp = substr(@$_FILES['photo']['name'], -4);
                    $target = strtoupper(date('YmdHis').$temp);
                    //上传图片
                    move_uploaded_file(@$_FILES['photo']['tmp_name'], iconv('UTF-8', 'GBK', $pic_path_com_trend_books.$target));

                    $add_tip = 'Upload photo '.$target.' success! ';
                }

                if(file_exists(iconv('UTF-8','GBK', $pic_path_com_trend_books.$target))) {
                    $small_photo = 's_' . $target;
                    //小图片不存在才进行缩小操作
                    if (!is_file($pic_path_small_trend_books . $small_photo) == true) {
                        makethumb($pic_path_com_trend_books . $target, $pic_path_small_trend_books . $small_photo, 's');
                    }
                }else{
                    $add_tip = 'Upload photo '.$target.' <i>failure</i>! ';
                }

                $trend_books_date = $_POST['trend_books_date'];
                $mod_by = $_SESSION["logininfo"]["aName"];
                $mod_date = dateMore();

                if(isset($_GET['modid'])){

                    // 1、修改的时候可以上传（能获取到$_FILE）；
                    // 2、也可以不上传（不能获取到$_FILE）；
                    // 3、也可以保持原有的图片不变（不能获取到$_FILE）。
                    if(isset($_POST['sign'])){
                        $photo = $target;
                    }else{
                        $photo = $mod_result['photo'];
                    }

                    $result = $mysql->q('update trend_books set photo = ?, trend_books_date = ?, mod_by = ?, mod_date = ? where id = ?', $photo, $trend_books_date, $mod_by, $mod_date, $mod_result['id']);
                    //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
                    if($result !== false){
                        $myerror->ok($add_tip.'Modify trend_books ('.$mod_result['id'].') success!', 'com-search_trend_books&page=1');
                    }else{
                        $myerror->error($add_tip.'System error, modify trend_books ('.$mod_result['id'].') failure!', 'BACK');
                    }
                }
            }
        }else{
            $myerror->error('上传图片 失败! 请选择JPG、PNG或者GIF格式的图片上传! 且图片大小不要超过 500 KB!', 'BACK');
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
    <h1 class="green">Trend Books<em>* indicates required fields</em></h1>
    <fieldset>
        <legend class='legend'>Modify Trend Books</legend>
        <fieldset>
            <legend class='legend'>1.Upload image</legend>
            <?
            /*if(isset($_SESSION['upload_photo_mod']) && $_SESSION['upload_photo_mod'] != ''){
                if (is_file($pic_path_com . $_SESSION['upload_photo_mod']) == true) {
                    $arr = getimagesize($pic_path_com . $_SESSION['upload_photo_mod']);
                    $pic_width = $arr[0];
                    $pic_height = $arr[1];
                    //20121022 获取文件大小，在图片旁显示文件的属性
                    $a = filesize($pic_path_com . $_SESSION['upload_photo_mod']);
                    $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                    echo 'image info：('.$_SESSION['upload_photo_mod'].'&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com . $_SESSION['upload_photo_mod'].'" class="tooltip2" target="_blank" title="'.$_SESSION['upload_photo_mod'].'"><img src="/sys/'.$pic_path_com . $_SESSION['upload_photo_mod'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
                }else{
                    echo '<div style="margin-left:28px;"><img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/><br />';
                }
                if(isset($_GET['modid'])){//copy的时候，删了图片，就连原来的product的图片也空了，所以这里去掉了
                    echo "</div><div><b><a class='button' href='?act=com-delete_photo&pid=".$mod_result['pid']."'>DELETE</a></b></div>";
                }
            }else{
                echo "<div style='margin-left:28px;'><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><a class='button' href='?act=com-upload_photo_mod'>UPLOAD</a></div>";*/

            /*
            <div class="line"></div>

            <img src="<?= (isset($Pic) ? $Pic : '../images/nopic.gif')?>" id="p_pkPic" border="0" height="70">
            <input name="pkPic" type="hidden" id="pkPic" value="<?=$Pic?>">
            <div class="line"></div>
            <iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="500" id="titleimg_up" frameborder="0"></iframe>
            */
            //}

            //20130716
            if(is_file($pic_path_com_trend_books . $mod_result['photo']) == true){
                $arr = getimagesize($pic_path_com_trend_books . $mod_result['photo']);
                $pic_width = $arr[0];
                $pic_height = $arr[1];
                //20121022 获取文件大小，在图片旁显示文件的属性
                $a = filesize($pic_path_com_trend_books . $mod_result['photo']);
                $image_size = getimgsize(100, 60, $pic_width, $pic_height);

                if(isset($_GET['modid'])){
                    echo 'image info：('.$mod_result['photo'].'&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com_trend_books . $mod_result['photo'].'" class="tooltip2" target="_blank" title="'.$mod_result['photo'].'"><img src="/sys/'.$pic_path_com_trend_books . $mod_result['photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul></div><div><b><a class="button" href="?act=com-delete_photo_new&trend_books_id='.$mod_result['id'].'&photo='.$mod_result['photo'].'">DELETE</a></b></div>';
                }elseif(isset($_GET['copypid'])){//copy的时候，删了图片，就连原来的product的图片也空了，所以这里去掉了
                    echo 'image info：(将会复制此图片并重新命名&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><img src="/sys/'.$pic_path_com_trend_books . $mod_result['photo'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></div>';
                }
            }else{
                echo '<div style="margin-left:28px;"><img src="../images/nopic.gif" border="0" width="80" height="60"><br /><input type="file" name="photo" id="photo" /><input type="hidden" name="sign" value="1"></div>';
            }

            ?>
        </fieldset>
        <fieldset>
            <legend class='legend'>2.Fill the form</legend>
            <table width="100%" id="table">
                <tr>
                    <td width="25%"><? $goodsForm->show('trend_books_date'); ?></td>
                    <td width="25%">&nbsp;</td>
                    <td width="25%">&nbsp;</td>
                    <td width="25%">&nbsp;</td>
                </tr>
            </table>
            <div class="line"></div>

            <?
            $goodsForm->show('submitbtn');
            ?>
        </fieldset>
    </fieldset>
    <?
    $goodsForm->end();

}
?>