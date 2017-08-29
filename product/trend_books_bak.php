<?php
/**
 * Author: night
 * Date: 2016/8/20
 * Time: 23:47
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
    $trend_books_date = array();
    while($poster_end >= $start){
        $trend_books_date[] = $poster_end;
        $poster_end = date('Y-m', strtotime($poster_end.'-01 00:00:01 -1 month'));
    }

//    echo "<pre>";
//    print_r($trend_books_date);
//    echo "</pre>";
//    die('@');

    //4:3
    //$thumbnail_width = 76;
    //$thumbnail_height = 57;
    $thumbnail_width = 120;
    $thumbnail_height = 90;

    if(isset($_GET['trend_books_date']) && $_GET['trend_books_date'] != '') {
        $_SESSION['trend_books_date'] = $_GET['trend_books_date'];
        $sql_trend_books_date = $_GET['trend_books_date'];
    }else{
        $_SESSION['trend_books_date'] = date('Y-m');
        $sql_trend_books_date = date('Y-m');
    }
    $rs = $mysql->q('select id, trend_books_date, photo from trend_books where trend_books_date like ? order by id desc', $sql_trend_books_date.'%');
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
        <link rel="stylesheet" href="css/galleriffic-2.css" type="text/css" />

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
        <!--<script type="text/javascript" src="js/jquery-1.3.2.js"></script>20150203更换插件-->
        <script type="text/javascript" src="js/jquery.galleriffic.js"></script>
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
            <?
            //间距50px
            foreach($trend_books_date as $trend_books_date_item){
                echo '<a id="trend_books_date" href="?trend_books_date='.$trend_books_date_item.'">'.$trend_books_date_item.'</a><span style="width:50px">&nbsp;</span>';
            }
            ?>
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
                        $img_dir = 'http://'.$host.'/sys/'.$pic_path_com_trend_books;
                        foreach($rtn as $v){
                            if(file_exists(ROOT_DIR.'sys/'.$pic_path_small_trend_books . 's_' . $v['photo'])){
                                $thumb_img_dir = 'http://'.$host.'/sys/'.$pic_path_small_trend_books.'s_';//只能用远程地址，例子中也是这样的
                            }else{
                                $thumb_img_dir = 'http://'.$host.'/sys/'.$pic_path_com_trend_books;
                            }
                            ?>
                            <li>
                                <a class="thumb" name="leaf" href="<?=$img_dir.$v['photo']?>" title="<?=$v['id']?>">
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
                        $('div.navigation').css({'width' : '45%', 'float' : 'left'});
                        $('div.content').css('display', 'block');

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
                            numThumbs:                 18,
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
    function change_color(trend_books_date){
        var now = new Date();
        var this_month = now.getFullYear()+"-"+((now.getMonth()+1)<10?"0":"")+(now.getMonth()+1);
        if(trend_books_date == '') trend_books_date = this_month;

        $("[id='trend_books_date']").each(function(){
            if( $(this).html() == trend_books_date ){
                $(this).attr('style', 'color:#EC008C');
            }
        })

    }

    //用 htmlspecialchars 是因为 $(this).html() 这个会将 & 自动转为 &amp  。。。
    change_color('<?=(isset($_GET['trend_books_date'])?htmlspecialchars($_GET['trend_books_date']):'')?>');

    /*禁鼠标右键*/
    function stop(){
        return false;
    }
    document.oncontextmenu=stop;
    /**********/
</script>