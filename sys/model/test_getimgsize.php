<? include("header.php"); ?>
<?php

//按比例縮小圖片
function getimgsize($oldwidth,$oldheight,$imgwidth,$imgheight)
{
	//$oldwidth設置的寬度，$oldheight設置的高度，$imgwidth圖片的寬度，$imgheight圖片的高度

	//單元格裝得進圖片，則按圖片的真實大小顯示
	if($imgwidth <= $oldwidth && $imgheight <= $oldheight)
	{
		$arraysize = array('width' => $imgwidth, 'height' => $imgheight);
		return $arraysize;
	}
	else
	{
		$suoxiaowidth = $imgwidth - $oldwidth;
		$suoxiaoheight = $imgheight - $oldheight;
		$suoxiaoheightper = $suoxiaoheight / $imgheight;
		$suoxiaowidthper = $suoxiaowidth / $imgwidth;
		if($suoxiaoheightper >= $suoxiaowidthper)
		{
			//單元格高度為準
			$aftersuoxiaowidth = $imgwidth * (1 - $suoxiaoheightper);
			$arraysize = array('width' => $aftersuoxiaowidth, 'height' => $oldheight);
			return $arraysize;
		}
		else
		{
			//單元格寬度為準
			$aftersuoxiaoheight = $imgheight * (1 - $suoxiaowidthper);
			$arraysize = array('width' => $oldwidth, 'height' => $aftersuoxiaoheight);
			return $arraysize;
		}
	}
}


if( isset($_GET["cid"]) && isset($_GET["tid"]) ) {
	
	$cid = $_GET["cid"];
	$tid = $_GET["tid"];
	if( $tid < 10 ) {
		$folder_num	= "0" . $tid;
	}
	else {
		$folder_num = $tid;	
	}
	switch( $cid ) {
		case 1:
			$file_url = "news/news" . $folder_num . "/";
			$xml_file = "news/news" . $folder_num . "/news" . $folder_num . ".xml";
			break;
		case 2:
			$file_url = "preview/preview" . $folder_num . "/";
			$xml_file = "preview/preview" . $folder_num . "/preview" . $folder_num . ".xml";
			break;
		case 3:
			$file_url = "game/game" . $folder_num . "/";
			$xml_file = "game/game" . $folder_num . "/game" . $folder_num . ".xml";
			break;
		case 4:
			$file_url = "tactic/tactic" . $folder_num . "/";
			$xml_file = "tactic/tactic" . $folder_num . "/tactic" . $folder_num . ".xml";
			break;
	}
	
	$xml = "";
	$f = fopen( "PCGameWeeklyIPhone/".$xml_file, 'r' );
	$time = date( 'm-d-Y', filemtime("PCGameWeeklyIPhone/" . $xml_file) );
	while( $data = fread( $f, 4096 ) ) 
	{ 
		$xml .= $data; 
	}
	fclose( $f );
	
	preg_match_all( "/\<chapter\>(.*?)\<\/chapter\>/s", $xml, $chapterblocks );
	 
	preg_match_all( "/\<chapterTitle\>(.*?)\<\/chapterTitle\>/s", $chapterblocks[1][0], $chapterTitleblocks );
	$c_title = iconv("utf-8","big5",$chapterTitleblocks[1][0]);
?>

  <tr>
    <td width="9" align="left" valign="top" bgcolor="181818"><img src="images/left.jpg" width="9" height="645"></td>
    <td width="979" valign="top" bgcolor="181818"><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#181818">
      <tr>
        <td valign="top"><table width="100%"  border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="147" valign="top"><? include("news_menu.php"); ?></td>
            <td align="center" valign="top"><table width="818" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td colspan="3"><img src="images/inner/title_news_01.jpg" width="860" height="70"></td>
                </tr>
              <tr>
                <td width="30" rowspan="4" valign="top" background="images/inner/inner_left2.jpg"><img src="images/inner/inner_left.jpg" width="31" height="513"></td>
                <td width="766" align="center" valign="top" bgcolor="1b1d1c"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td align="center">&nbsp;</td>
                  </tr>
                  <tr>
                    <td align="center"><span class="newstitle"><?=$c_title?></span></td>
                  </tr>
                  <tr>
                    <td align="center"><span class="newsdate"><?=$time?></span></td>
                  </tr>
                  <tr>
                    <td align="center">&nbsp;</td>
                  </tr>

<?php
	
	preg_match_all( "/\<chapterImage\>(.*?)\<\/chapterImage\>/s", $chapterblocks[1][0], $chapterImageblocks );
	$image_num = count($chapterImageblocks[1]);
	for( $i = 0; $i < $image_num; $i++ ) {
		$c_image = iconv("utf-8","big5",$chapterImageblocks[1][$i]);
		$arr = getimagesize("PCGameWeeklyIPhone/".$file_url.$c_image);
		$pic_width = $arr[0];
		$pic_height = $arr[1];
		$image_size = getimgsize(790, 10000, $pic_width, $pic_height);
		
?>		
		
                  <tr>
                    <td align="center"><img src="../PCGameWeeklyIPhone/<?=$file_url . $c_image?>" width="<?=$image_size['width']?>" height="<?=$image_size['height']?>"></td>
                  </tr>
                  <tr><td>&nbsp;</td><tr>
        
<?php		
	}
	
	
	preg_match_all( "/\<chapterTextBody\>(.*?)\<\/chapterTextBody\>/s", $chapterblocks[1][0], $chapterTextBodyblocks );
	$text_num = count($chapterTextBodyblocks[1]);
?>
				<tr>
                    <td align="left" class="content">
<?php	
	for(  $i = 0; $i < $text_num; $i++ ) {
		//$c_textbody = iconv("utf-8","big5//IGNORE",$chapterTextBodyblocks[1][$i]);
		$c_textbody = mb_convert_encoding($chapterTextBodyblocks[1][$i], "big5", "utf-8"); 
?>		
                    <p>
                    <?php echo $c_textbody;?>
                    </p>
                    <p>&nbsp;</p>
<?php		
	}
?>
					</td>
                </tr>   

<?php	
/*	MP4圖片
	preg_match_all( "/\<chapterMovieImage\>(.*?)\<\/chapterMovieImage\>/s", $chapterblocks[1][0], $chapterMovieImageblocks );
	$movieimage_num = count($chapterMovieImageblocks[1]);
	for( $i = 0; $i < $movieimage_num; $i++ ) {
		$c_movieimage = iconv("utf-8","big5",$chapterMovieImageblocks[1][$i]);	
?>
                  <tr>
                    <td align="center"><img src="PCGameWeeklyIPhone/<?=$file_url . $c_movieimage?>"></td>
                  </tr>
<?php		
	}

	//MP4
	preg_match_all( "/\<chapterMovie\>(.*?)\<\/chapterMovie\>/s", $chapterblocks[1][0], $chapterMovieblocks );
	$movie_num = count($chapterMovieblocks[1]);
	for( $i = 0; $i < $movie_num; $i++ ) {
		$c_movie = iconv("utf-8","big5",$chapterMovieblocks[1][$i]);

?>		


<?php		
	}
*/	
}
?>
               
                </table></td>
                <td width="22" rowspan="4" valign="top" background="images/inner/inner_right2.jpg"><img src="images/inner/inner_right.jpg" width="22" height="513"></td>
              </tr>
              <tr>
                <td align="center" valign="middle" background="images/inner/inn1er_left.jpg" bgcolor="1b1d1c"><table width="718" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td colspan="3"><img src="images/inner/top5_header.jpg" width="718" height="28"></td>
                    </tr>
                  <tr>
                    <td width="8" background="images/inner/top5_left.jpg"><img src="images/inner/top5_left.jpg" width="8" height="77"></td>
                    <td width="694" align="center" bgcolor="000000">未開放</td>
                    <td width="16" background="images/inner/top5_right.jpg"><img src="images/inner/top5_right.jpg" width="16" height="77"></td>
                  </tr>
                  <tr>
                    <td colspan="3"><img src="images/inner/top5_footer.jpg" width="718" height="12"></td>
                    </tr>
                </table></td>
              </tr>
              <tr>
                <td align="center" valign="middle" background="images/inner/inn1er_left.jpg" bgcolor="1b1d1c">&nbsp;</td>
              </tr>
              <tr>
                <td align="center" valign="middle" background="images/inner/inn1er_left.jpg" bgcolor="1b1d1c"><table width="718" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td colspan="3"><img src="images/inner/comment_header.jpg" width="718" height="28"></td>
                  </tr>
                  <tr>
                    <td width="8" rowspan="2" background="images/inner/top5_left.jpg"><img src="images/inner/top5_left.jpg" width="8" height="77"></td>
                    <td width="694" align="center" bgcolor="#000000">未開放</td>
                    <td width="16" rowspan="2" background="images/inner/top5_right.jpg"><img src="images/inner/top5_right.jpg" width="16" height="77"></td>
                  </tr>
                  <tr>
                    <td align="center"><INPUT id=SubmitBtn class="input_button input_button_main" value=" 發表 " type=submit name=SubmitBtn>
                      <INPUT id=cancel class=input_button value=" 取消 " type=button name=cancel></td>
                  </tr>
                  <tr>
                    <td colspan="3"><img src="images/inner/top5_footer.jpg" width="718" height="12"></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td colspan="3"><img src="images/inner/inner_footer.jpg" width="859" height="40"></td>
                </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
    <td width="7" align="right" valign="top" bgcolor="181818"><img src="images/right.jpg" width="7" height="645"></td>
  </tr>

<? include("footer.php"); ?>