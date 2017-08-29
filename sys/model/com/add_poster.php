<?php

/*
change log

2010-12-07		修改用户已登录时的提示，去掉直接登出的按钮。

*/

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

// form加这个参数才能上传文件，否则获取不到 $_FILE
$goodsForm = new My_Forms(array('multipart'=>true));
$formItems = array(

    'poster_date' => array('title' => 'Date', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => date('Y-m-d')),

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

            //其他的add用了js来去除头尾空格和tab，但addproduct不用js检查，因为输出js图片session保存不了的关系，所以在这里处理数据。本来是在Mysql.php中处理的，但是连换行也去掉了，textarea就不大方便了。so。。。 mod 3.5

            $target = '';
            $add_tip = '';
            if(@$_FILES['photo']['name'] != ''){
                // 转为大写字母(pid + 时间 + 原图片的后缀名)
                // 图片的格式一般都是3位字母吧，没有其他的吧。。。？
                $temp = substr(@$_FILES['photo']['name'], -4);
                $target = strtoupper(date('YmdHis').$temp);
                //上传图片
                move_uploaded_file(@$_FILES['photo']['tmp_name'], iconv('UTF-8', 'GBK', $pic_path_com_poster.$target));
                if(file_exists(iconv('UTF-8','GBK', $pic_path_com_poster.$target))) {
                    $small_photo = 's_' . $target;
                    //小图片不存在才进行缩小操作
                    if (!is_file($pic_path_small . $small_photo) == true) {
                        makethumb($pic_path_com_poster . $target, $pic_path_small_poster . $small_photo, 's');
                    }

                    $add_tip = 'Upload photo '.$target.' success! ';
                }else{
                    $add_tip = 'Upload photo '.$target.' <i>failure</i>! ';
                }
            }

            $photos = $target;
            $poster_date = $_POST['poster_date'];
            $created_by = $mod_by = $_SESSION["logininfo"]["aName"];
            $in_date = $mod_date = dateMore();

            $result = $mysql->q('insert into poster set photo = ?, poster_date = ?, created_by = ?, mod_by = ?, in_date = ?, mod_date = ?', $photos, $poster_date, $created_by, $mod_by, $in_date, $mod_date);
            if($result){
                $myerror->ok($add_tip.'Add Poster success!', 'com-search_poster&page=1');
            }else{
                $myerror->error($add_tip.'System error, add Poster failure!', 'BACK');
            }
        }
    }else{
        $myerror->error('上传图片 失败! 请选择JPG、PNG或者GIF格式的图片上传! 且图片大小不要超过 500 KB!', 'BACK');
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
<h1 class="green">Poster<em>* indicates required fields</em></h1>

    <fieldset>
        <legend class='legend'>Add Poster 1.Upload image</legend>
        <?
        /*if(isset($_SESSION['upload_photo_add']) && $_SESSION['upload_photo_add'] != ''){
            $arr = getimagesize($pic_path_com . $_SESSION['upload_photo_add']);
            $pic_width = $arr[0];
            $pic_height = $arr[1];
            //20121022 获取文件大小，在图片旁显示文件的属性
            $a = filesize($pic_path_com . $_SESSION['upload_photo_add']);
            $image_size = getimgsize(100, 60, $pic_width, $pic_height);
            echo 'image info：('.$_SESSION['upload_photo_add'].'&nbsp;&nbsp;'.floatval($a/1000).'KB&nbsp;&nbsp;'.$pic_width.'x'.$pic_height.')<div class="shadow" style="margin-left:28px;"><ul><li><a href="/sys/'.$pic_path_com . $_SESSION['upload_photo_add'].'" class="tooltip2" target="_blank" title="'.$_SESSION['upload_photo_add'].'"><img src="/sys/'.$pic_path_com . $_SESSION['upload_photo_add'].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
            echo "</div><div><b><a class='button' href='?act=com-delete_photo'>DELETE</a></b></div>";
        }else{*/
            //20130716
            echo "<div style='margin-left:28px;'><ul><li><img src='../images/nopic.gif' border='0' width='80' height='60'><br /><input type='file' name='photo' id='photo' /></div>";
            /*
            <div class="line"></div>

            <img src="<?= (isset($Pic) ? $Pic : '../images/nopic.gif')?>" id="p_pkPic" border="0" height="70">
            <input name="pkPic" type="hidden" id="pkPic" value="<?=$Pic?>">
            <div class="line"></div>
            <iframe width="350" src="model/upload38n5.php?for=pkPic&oldpic=<?=str_replace($pic_path, '', $Pic)?>" scrolling="no" height="500" id="titleimg_up" frameborder="0"></iframe>
            */
        //}
        ?>
    </fieldset>
    <fieldset>
        <legend class='legend'>2.Fill the form</legend>
        <table width="100%" id="table">
            <tr>
                <td width="25%"><? $goodsForm->show('poster_date');?></td>
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

<?
    $goodsForm->end();
}
?>