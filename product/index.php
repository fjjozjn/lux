<?
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

    $rs_theme = $mysql->q('select id, theme from theme order by id');
    $theme = array();
    if($rs_theme){
        $rtn = $mysql->fetch();
        foreach($rtn as $v){
            $theme[] = array($v['id'], $v['theme']);
        }
    }
    //echo "<pre>";
    //print_r($rtn);
    //echo "</pre>";
    //$goodsForm = new My_Forms();
    //$formItems = array(
    //'theme' => array('type' => 'select', 'options' => $theme, 'value' => isset($_GET['theme'])?$_GET['theme']:''),
    //'type' => array('type' => 'select', 'options' => $type_e, 'value' => isset($_GET['type'])?$_GET['type']:''),
    //);
    //$goodsForm->init($formItems);


    //4:3
    //$thumbnail_width = 76;
    //$thumbnail_height = 57;
    $thumbnail_width = 120;
    $thumbnail_height = 90;

    $where = '';
    $rs = false;
    if(isset($_GET['theme']) && $_GET['theme'] != '' && $_GET['theme'] != 'all'){
        //为了在点theme下的type时能拼接正确的url参数
        $_SESSION['theme'] = 'theme='.$_GET['theme'];

        //20130326 改为按货号排序
        if(isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] != 'all'){
            $rs = $mysql->q('select pid, mod_date, description, photos from product where show_in_catalog = 1 and theme = ? and type = ? order by in_date desc', $_GET['theme'], isset($_GET['type'])?$_GET['type']:'');
        }else{
            $rs = $mysql->q('select pid, mod_date, description, photos from product where show_in_catalog = 1 and theme = ? order by in_date desc', $_GET['theme']);
        }
    }else{
        $_SESSION['theme'] = 'all';

        if(isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] != 'all'){
            $rs = $mysql->q('select pid, mod_date, description, photos from product where show_in_catalog = 1 and type = ? order by in_date desc', isset($_GET['type'])?$_GET['type']:'');
        }else{
            //首次进来什么参数都没带，默认显示所有，即theme=all
            $rs = $mysql->q('select pid, mod_date,description, photos from product where show_in_catalog = 1 order by in_date desc');
        }
    }
    if($rs){
        $rtn = $mysql->fetch();
    }
    //echo "<pre>";
    //print_r($rtn);
    //echo "</pre>";

    $cars[3]='<img src="image/B102459-RHO_Eragent.jpg" alt="Classic & Elegant" width="135" height="90" align="left" style="border:1px solid #777">';
    $cars[4]='<img src="image/H802216-G_20140617112818_Fashion.jpg" alt="Fashion & Chic" width="135" height="90" align="left" style="border:1px solid #777">';
    $cars[5]='<img src="image/E530029-SMK_20140804122221_Swarovski.jpg" alt="Swarovski Elements" width="135" height="90" align="left" style="border:1px solid #777">';
    $cars[6]='<img src="image/E001250_20140324112355_CZ setting.jpg" alt="CZ setting" width="135" height="90" align="left" style="border:1px solid #777">';
    $cars[7]='<img src="image/N802874_20140924095437.jpg" alt="NEW" width="135" height="90" align="left" style="border:1px solid #777">';
    $cars[9]='<img src="image/N100929_20131226121850_Other.jpg" alt="Other" width="135" height="90" align="left" style="border:1px solid #777"><br/><br/><br/><br/><br/><br/><br/><br/><br/>';
    $cars[10]='<img src="image/R005340_Cocktail ring.jpg" alt="Cocktail ring" width="135" height="90" align="left" border="1" style="border:1px solid #777">';
    $cars[11]='<img src="image/N009706-S2_Oxidized.jpg" alt="Antique & Oxidized" width="135" height="90" align="left" border="1" style="border:1px solid #777">';
    $cars[12]='<img src="image/N100491-RG_20150317113855.JPG" alt="Basic" width="135" height="90" align="left" border="1" style="border:1px solid #777">';
    //echo "$cars[11]"
    $font[3]="Classic & Elegant";
    $font[4]="Fashion & Chic";
    $font[5]="Swarovski Elements";
    $font[6]="CZ setting";
    $font[7]="NEW";
    $font[9]="Other";
    $font[10]="Cocktail ring";
    $font[11]="Antique & Oxidized";
    $font[12]="Basic";
    ?>
    <html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8">
        <title>Lux e-Catalog</title>
        <link rel="stylesheet" href="css/basic.css" type="text/css" />
        <link rel="stylesheet" href="css/galleriffic.css" type="text/css" />

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
//                $("span.div").hide();
//                $(".sis-li li").hover(function(){
//                    $("span.div",this).slideToggle(500);
//                });
//
//                $(".imgtext").hide();
                $(".zzsc").hover(function(){
                    $(".imgtext",this).slideToggle(500);
                });
            });
        </script>
    </head>
    <body>

    <div id="page">

        <div id="" style=" width:10%; float:left;">
            <br/><h2><a id="" href="?theme=all"><font color="#EC008C">&nbsp;&nbsp;Catalog</font></a></h2>
            <h2><a id="" href="poster.php"><font color="#EC008C">&nbsp;&nbsp;Poster</font></a></h2>
            <h2><a id="" href="color_chart.php"><font color="#EC008C">&nbsp;&nbsp;Color Chart</font></a></h2>
            <h2><a id="" href="trend_books.php"><font color="#EC008C">&nbsp;&nbsp;Trend Books</font></a></h2>
        </div>
        <div id="logout" style=" float:right"><a href="?logout"><img style="border:none" width="50" height="50" src="../images/logout.gif" /></a></div>
        <div id="container">
            <h1><a id="underline_none" href="http://luxdesign.hk" target="_blank"><img style="border:none;" width="85%" src="image/Monthly report header.jpg" /></a></h1>


            <div id="catalog">
                <br /><br/><br/><div id="ALLdiv" style=" float:left;padding:53px 40px 53px 0px;"><a id="theme" href="?theme=all">ALL</a></div>
                <?
                $rs_theme = $mysql->q('select id, theme from theme order by sort_order desc');
                if($rs_theme){
                    $theme = $mysql->fetch();
                    foreach($theme as $v){
                        $aaa = $v['id'];
                        //echo '<a id="theme" href="?theme='.$v['id'].'">'.$cars[$aaa].'</a>';
                        echo '<div  class="zzsc"><a id="theme" href="?theme='.$v['id'].'">'.$cars[$aaa].'</a><div class="text">
						  <div id="top_type_'.$v['id'].'" class="imgtext"><center>'.$font[$aaa].'</center></div></div></div>';
                    }
                }
                ?>
            </div>

            <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
            <a id="type" href="?<?=(isset($_SESSION['theme'])?$_SESSION['theme']:'')?>&type=all">ALL</a><span style="width:50px">&nbsp;</span>
            <?
            //间距50px
            $type_e = get_bom_lb(3);
            foreach($type_e as $v){
                echo '<a id="type" href="?'.(isset($_SESSION['theme'])?$_SESSION['theme']:'').'&type='.$v[0].'">'.$v[0].'</a><span style="width:50px">&nbsp;</span>';
            }
            ?>
            <!--table>
                    	<tr>
                    		<td>Theme : </td>
                            <td><? //$goodsForm->show('theme');?></td>
                            <td>&nbsp;&nbsp;</td>
                            <td>Type : </td>
                            <td><? //$goodsForm->show('type');?></td>
                            <td>&nbsp;&nbsp;</td>
                            <td><input type="button" value="show" onClick="show()" /></td>
                        </tr>
                    </table-->
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
                        $img_dir = 'http://'.$host.'/sys/upload/lux/';
                        foreach($rtn as $v){
                            if(file_exists(ROOT_DIR.'sys/'.$pic_path_small . 's_' . $v['photos'])){
                                $thumb_img_dir = 'http://'.$host.'/sys/upload/luxsmall/'.'s_';//只能用远程地址，例子中也是这样的
                            }else{
                                $thumb_img_dir = 'http://'.$host.'/sys/upload/lux/';
                            }


                            ?>
                            <li>
                                <a class="thumb" name="leaf" href="<?=$img_dir.$v['photos']?>" title="<?=$v['pid']?>">
                                    <img width="<?=$thumbnail_width?>" height="<?=$thumbnail_height?>" src="<?=$thumb_img_dir.$v['photos']?>" alt="<?=$v['pid']?>" />
                                </a>
                                <div class="caption">
                                    <div class="download">
                                        <a href="#"></a>
                                    </div>
                                    <div class="image-title"><?=$v['pid']?></div>
                                    <div class="image-desc"><?=$v['description']?></div>
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
    /*
     function show(){
     var theme = $('#theme').val();
     var type = $('#type').val();
     var temp = '?1';
     if(theme != ''){
     temp = temp + '&theme='+theme;
     }
     if(type != ''){
     temp = temp + '&type='+type;
     }
     window.location.href=temp;
     }
     */
</script>

<?
if(isset($_GET['theme']) || isset($_GET['type']) || (!isset($_GET['theme']) && !isset($_GET['type'])) ){
    if(isset($_GET['theme']) && $_GET['theme'] != '' && $_GET['theme'] != 'all'){
        $theme = $mysql->qone('select id, theme from theme where id = ?', $_GET['theme']);
    }
    ?>
    <script>
        function change_color(theme, type, id){
            if(theme == '') theme = 'all';
            if(type == '') type = 'all';

            $("[id='theme']").each(function(){
                if( $(this).html() == theme ){
                    $(this).attr('style', 'color:#EC008C');
                }
            });

            $("[id='type']").each(function(){
                if( $(this).html() == type ){
                    $(this).attr('style', 'color:#EC008C');
                }
            });

            $(".imgtext").hide();
            $("#top_type_"+id).show();
        }

        //用 htmlspecialchars 是因为 $(this).html() 这个会将 & 自动转为 &amp  。。。
        change_color('<?=(isset($theme['theme'])?htmlspecialchars($theme['theme']):'')?>', '<?=(isset($_GET['type'])?$_GET['type']:'')?>', '<?=(isset($theme['id'])?$theme['id']:'')?>');

        /*禁鼠标右键*/
        function stop(){
            return false;
        }
        document.oncontextmenu=stop;
        /**********/
    </script>
<?
}
?>

