<?php
/**
 * Author: zhangjn
 * Date: 2016/8/27
 * Time: 16:00
 */

include_once('../in7/global.php');
include_once('../sys/in38/admin_var.php');

if(isset($_GET['logout'])){
    unset($_SESSION['fty_product']);
    echo '<script>replace("You already logout!");</script>';
    echo '<script>window.location.href="http://www.luxdesign.hk/catalog.html";</script>';
    exit();
}

//20131218 从系统点过来不需要密码
if(isset($_SERVER['HTTP_REFERER'])){
    if(strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false){
        $rtn = $mysql->qone('select show_product_pwd from setting');
        if(isset($_POST['MyPWD']) && $_POST['MyPWD'] == $rtn['show_product_pwd']){
            $_SESSION['fty_product'] = 'access';
            /*echo '<script>alert("通过密码验证！");</script>';*/
        }
    }else{
        $_SESSION['fty_product'] = 'access';
    }
}

if(isset($_SESSION['fty_product']) && $_SESSION['fty_product'] == 'access'){

    //从这个时间开始显示
    $start = '2015-08';

    $poster_end = date('Y-m');
    $poster_date = array();
    while($poster_end >= $start){
        $poster_date[] = $poster_end;
        $poster_end = date('Y-m', strtotime($poster_end.'-01 00:00:01 -1 month'));
    }

//    echo "<pre>";
//    print_r($poster_date);
//    echo "</pre>";
//    die('@');

    //4:3
    //$thumbnail_width = 76;
    //$thumbnail_height = 57;
    $thumbnail_width = 120;
    $thumbnail_height = 90;

    if(isset($_GET['poster_date']) && $_GET['poster_date'] != '') {
        $_SESSION['poster_date'] = $_GET['poster_date'];
        $sql_poster_date = $_GET['poster_date'];
    }else{
        $_SESSION['poster_date'] = date('Y-m');
        $sql_poster_date = date('Y-m');
    }
//    $rs = $mysql->q('select id, poster_date, photo from poster where poster_date like ? order by id desc', $sql_poster_date.'%');
    $rs = $mysql->q('select id, poster_date, photo from poster order by id desc');
    $rtn = '';
    if($rs){
        $rtn = $mysql->fetch();
    }

//    echo "<pre>";
//    print_r($rtn);
//    echo "</pre>";
//    die('#');
    ?>
    <html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8">
        <title>Lux e-Catalog</title>
        <link rel="stylesheet" href="css/basic.css" type="text/css" />
        <link rel="stylesheet" href="css/galleriffic1.css" type="text/css" />

        <!--        <link href="css/style.css" rel="stylesheet" type="text/css" />-->
        <link rel="shortcut icon" href="image/icon2.ico">
        <style type="text/css">
            body {
                font-family: Verdana, Geneva, sans-serif;
                font-size: 11px;
                /*background-image: url(image/Monthly report header.jpg);
                background-repeat: repeat;*/
            }
            img { border:none; }
            .zzsc { width:135px; height:90px; float: left; position:relative; }
            .text { width:135px; height:auto; background:#000; FILTER:alpha(opacity=60); opacity:0.7; -moz-opacity:0.7; position:absolute; left:0; bottom:0; }
            .imgtext { width:135px; height:auto; color:#C0C0C0; font-size:14px; line-height:200%; }
        </style>
        <script src="js/jquery-1.7.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.galleriffic1.js"></script>
        <script type="text/javascript" src="js/jquery.opacityrollover.js"></script>
        <!-- We only want the thunbnails to display when javascript is disabled -->
        <script type="text/javascript">
            document.write('<style>.noscript { display: none; }</style>');
        </script>
        <script type="text/javascript">
            $(document).ready(function(){
                $("span.div").hide();
                $(".sis-li li").hover(function(){
                    $("span.div",this).slideToggle(500);
                });

                $(".imgtext").hide();
                $(".zzsc").hover(function(){
                    $(".imgtext",this).slideToggle(500);
                });
            });
        </script>
    </head>
    <body>

    <div id="page">

        <div id="" style=" width:10%; float:left;">
            <br/><h2><a id="" href="index.php?theme=all"><font color="#EC008C">&nbsp;&nbsp;catalog</font></a></h2>
            <h2><a id="" href="poster.php"><font color="#EC008C">&nbsp;&nbsp;poster</font></a></h2>
            <h2><a id="" href="color_chart.php"><font color="#EC008C">&nbsp;&nbsp;Color Chart</font></a></h2>
            <h2><a id="" href="trend_books.php"><font color="#EC008C">&nbsp;&nbsp;Trend Books</font></a></h2>
        </div>
        <div id="logout" style=" float:right"><a href="?logout"><img style="border:none" width="50" height="50" src="../images/logout.gif" /></a></div>
        <div id="container">
            <h1><a id="underline_none" href="http://luxdesign.hk" target="_blank"><img style="border:none;" width="85%" src="image/Monthly report header.jpg" /></a></h1>
            <br />
            <br />
            <br />
            <!-- Start Advanced Gallery Html Containers -->
            <?
            if($rs){
                ?>
                <div id="gallery" class="content">
                    <div id="controls" class="controls"></div>
                    <div class="slideshow-container">
                        <div id="loading" class="loader"></div>
                        <div id="slideshow" class="slideshow"></div>
                    </div>
                    <div id="caption" class="caption-container"></div>
                </div>

                <div id="thumbs" class="navigation">
                    <ul class="thumbs noscript">
                        <?
                        $img_dir = 'http://'.$host.'/sys/'.$pic_path_com_poster;
                        $this_date_month = '';
                        $first = true;
                        foreach($rtn as $v){
                            $date_month = substr($v['poster_date'], 0, 7);
                            if($first){
                                $this_date_month = $date_month;
                                $first = false;
                            }else{
                                if($this_date_month == $date_month){
                                    $date_month = '';
                                }else{
                                    $this_date_month = $date_month;
                                }
                            }
                            if(file_exists(ROOT_DIR.'sys/'.$pic_path_small_poster . 's_' . $v['photo'])){
                                $thumb_img_dir = 'http://'.$host.'/sys/'.$pic_path_small_poster.'s_';//只能用远程地址，例子中也是这样的
                            }else{
                                $thumb_img_dir = 'http://'.$host.'/sys/'.$pic_path_com_poster;
                            }
                            ?>
                            <li>
                                <a class="thumb" name="leaf" href="<?=$img_dir.$v['photo']?>" title="<?=$v['id']?>">
                                    <font color="red"><strong><?=$date_month?></strong></font>
                                    <br />
                                    <img width="<?=$thumbnail_width?>" height="<?=$thumbnail_height?>" src="<?=$thumb_img_dir.$v['photo']?>" alt="<?=$v['id']?>" />
                                </a>
                                <div class="caption">
                                    <div class="download">
                                        <a href="#"></a>
                                    </div>
                                    <div class="image-title"><?=$v['id']?></div>
                                    <div class="image-desc"></div>
                                </div>
                            </li>
                        <?
                        }
                        ?>
                    </ul>
                </div>

                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        // We only want these styles applied when javascript is enabled
                        $('div.navigation').css({'width' : '35%', 'float' : 'left'});
                        $('div.content').css({'width' : '60%', 'display' : 'block'});

                        // Initially set opacity on thumbs and add
                        // additional styling for hover effect on thumbs
                        var onMouseOutOpacity = 0.6;
                        $('#thumbs ul.thumbs li').opacityrollover({
                            mouseOutOpacity:   onMouseOutOpacity,
                            mouseOverOpacity:  1.0,
                            fadeSpeed:         'fast',
                            exemptionSelector: '.selected'
                        });

                        // Initialize Advanced Galleriffic Gallery
                        var gallery = $('#thumbs').galleriffic({
                            delay:                     2500,
                            numThumbs:                 9,
                            preloadAhead:              10,
                            enableTopPager:            true,
                            enableBottomPager:         true,
                            maxPagesToShow:            7,
                            imageContainerSel:         '#slideshow',
                            controlsContainerSel:      '#controls',
                            captionContainerSel:       '#caption',
                            loadingContainerSel:       '#loading',
                            renderSSControls:          true,
                            renderNavControls:         true,
                            playLinkText:              'Play Slideshow',
                            pauseLinkText:             'Pause Slideshow',
                            prevLinkText:              '&lsaquo; Previous Photo',
                            nextLinkText:              'Next Photo &rsaquo;',
                            nextPageLinkText:          'Next &rsaquo;',
                            prevPageLinkText:          '&lsaquo; Prev',
                            enableHistory:             false,
                            autoStart:                 false,
                            syncTransitions:           true,
                            defaultTransitionDuration: 900,
                            onSlideChange:             function(prevIndex, nextIndex) {
                                // 'this' refers to the gallery, which is an extension of $('#thumbs')
                                this.find('ul.thumbs').children()
                                    .eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
                                    .eq(nextIndex).fadeTo('fast', 1.0);
                            },
                            onPageTransitionOut:       function(callback) {
                                this.fadeTo('fast', 0.0, callback);
                            },
                            onPageTransitionIn:        function() {
                                this.fadeTo('fast', 1.0);
                            }
                        });
                    });
                </script>

            <?
            }
            ?>
            <div style="clear: both;"></div>
        </div>
    </div>

    <div id="footer">copyright &copy <?=date("Y");?> LUX DESIGN LTD ALL RIGHTS RESERVED</div>

    </body>
    </html>

<?
}else{
    echo '<script>alert("Invalid Password!");</script>';
    echo '<script>window.location.href="http://luxdesign.hk/product.htm";</script>';
}
?>

<script>
    /*禁鼠标右键*/
    function stop(){
        return false;
    }
    document.oncontextmenu=stop;
    /**********/
</script>