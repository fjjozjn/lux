<?
if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');
Class RecordSetControl3 {
	
	//default setting for recordset
	var $table_width = "100%"; //970 is fit to 1024x768 and no scroll bar appear
	var $recordset_title ="资料列";
	var $recordset_total_row ="总共";
	var $total_rows = 0;
	var $record_per_page = 50;
	var $sort_field ="";
	var $sort_field_index = 0;
	var $sort_seq ="DESC";
	var $addnew_link = "";
	var $link_sort_paging = "";
	var $display_back = true;
	var $display_paging = true;
	var $display_new_button = true;
	var $value_content = array();
	var $special_content = GENERAL_NO;
	
	//default column setting
	var $col_width = "";	
	var $col_align = "left";
	//var $col_content = array(); 有點模糊不明，它沒辦法做到對某一列使用枚舉，已改用在SetRecordCol()中設定$valAraay參數
	var $sort_list = array();	
	var $sort_link = "";
	
	//text content for recordset
	var $col_edit_col = "";	
	var $col_select_col = "";	
	
	
	//function SetRecordCol($title, $field, $sort_allow=true ,$edit_view=false, $link="", $para_name="", $popup_select="", $popup_js="", $popup_jsvalue="", $callBack = ""){
	function SetRecordCol($title, $field, $sort_allow=true ,$edit_view=false, $link="", $para_name="", $callBack = "", $valArray = array()){
		$temp = array();
		if (!$edit_view){
			//edit view is false
			//this is a data column
			$temp["title"] = $title;
			$temp["field"] = $field;
			$temp["width"] = $this->col_width;			
			$temp["align"] = $this->col_align;				
			$temp["link"] = "";			
			// $temp["sort_allow"] = $sort_allow;			
			$temp["sort_allow"] = false; //temp disabled this function			
			$temp["para_name"] = "";			
			//$temp["popup_select"] = "";			
			//$temp["popup_js"] = "";			
			//$temp["popup_jsvalue"] = "";
		}else{
			//edit view is ture
			//this column is a link to access edit form
			// if (empty($link)){
				// $link = $this->col_edit_col;
			// }			
			$temp["title"] = $title ? $title : $this->col_edit_col;
			$temp["field"] = $field; //in this case, this field must be the id field
			$temp["width"] = $this->col_width;	
			$temp["align"] = "center";	
			$temp["link"] = $link;
			$temp["sort_allow"] = false; //edit view is not allow for sorting		
			$temp["para_name"] = $para_name;
			//$temp["popup_select"] = $popup_select;
			//$temp["popup_js"] = $popup_js;
			//$temp["popup_jsvalue"] = $popup_jsvalue;
			
		}
		$temp["callBack"] = $callBack;
		$temp["valArray"] = $valArray;
		$this->sort_list[] = $temp["field"];
		$this->col_content[] = $temp;
		unset($temp);
		
		//reset default column setting 
		$this->ResetColSetting();
	}
	
	function ShowRecordSet($row_count){		
		global $mysql;
		echo "<div align='center'>";
		echo "<fieldset>";
		// echo "<legend class='legend'>".$recordset_title." (".$recordset_total_row." : ".count($value_arr).")"."</legend>";		
		echo "<legend class='legend'>".$this->recordset_title." ( ".$this->recordset_total_row." : ".$row_count." )"."</legend>";		
		echo "<table width='".$this->table_width."' border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000'>";
		echo " <tr bgcolor='#EEEEEE'> ";
		for($i = 0 ; $i < count($this->col_content); $i++ ){
			echo "<th height='30' align='center'";
			if (!empty($this->col_content[$i]["width"])){
				//have assign width of column
				echo " width='".$this->col_content[$i]["width"]."' ";
			}				
			echo ">";
			
			if ($this->col_content[$i]["sort_allow"]){
				//this field allow sorting
				$col = $i;
				$temp_seq = "DESC";
				$temp_class = " class='th_sort_off' ";
				// $temp_link = "?col=".$i."&seq=".$temp_seq;
				// if ($this->sort_field_index == $this->col_content[$i]["field"]){
				if ($this->sort_field_index == $i){
					//this column is sorted now, then reverse the sorting seq
					if ($this->sort_seq == "DESC"){
						$temp_seq = "ASC";
					}
					
					//this column already choose as sorting field
					$temp_class = " class='th_sort_on' ";
					// $temp_link = "?col=".$this->col_content[$i]["field"]."&seq=".$temp_seq;
					// $temp_link = "?col=".$i."&seq=".$temp_seq;
					// $this->link_sort_paging = $temp_link;
				}				
					
				// echo "<a href='".$temp_link."'".$temp_class.">";
				echo "<a href='javascript:;' onclick='onSort(\"".$col."\", \"".$temp_seq."\")' ".$temp_class.">";
				echo $this->col_content[$i]["title"];
				echo "</a>";
			}else{
				//this field is not allow sorting
				echo $this->col_content[$i]["title"];		
			}			
			echo"</th>";
		}
		echo " </tr>";
		
		//set the start row and number of row in this page
		$start = 0;
		$page_row = $this->record_per_page;
		if ($row_count < $this->record_per_page){
			//value row number is less than max row per page
			$page_row = $row_count;
		}
		
		//temporay use for array content
		$value_arr =$mysql->fetch(0, 1);
		//print_r_pre($value_arr);
		for($i = $start ; $i < $page_row; $i++){	
			if (isset($value_arr[$i])){
				echo " <tr class='td_' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";
				for ($j = 0; $j < count($this->col_content); $j++){
					//display column data
					echo "	<td";
					if (!empty($this->col_content[$j]["width"])){
						//have assign width of column
						echo " width='".$this->col_content[$j]["width"]."' ";
					}				
					if (!empty($this->col_content[$j]["align"])){
						//have assign alignment of column
						echo " align='".$this->col_content[$j]["align"]."' ";
					}
					echo ">";
					
					if (strlen($this->col_content[$j]["link"])){
						//this is link column
						$temp_link = $this->col_content[$j]["link"];
						$temp_link.= "&".$this->col_content[$j]["para_name"];
						$temp_link.= "=".$value_arr[$i][$this->col_content[$j]["field"]];	
						//zjn 添加刪除前的確認，乱得一塌糊涂，搞不定啊。。。
						if( $this->col_content[$j]["title"] == '删除'){
							//文字外用雙引號就不行，轉義單引號就行。。。location也要轉義單引號框起連接。。。
							echo '<a href="javascript:if(confirm(\'此操作将导致的删除数据无法恢复,确认删除?\'))window.location=\''.$temp_link.'\'"><img title="删除" src="../../images/button_trash.png" /></a>';
							//echo '<a class="button" href="javascript:if(confirm(\'此操作将导致的删除数据无法恢复,确认删除?\'))window.location=\''.$temp_link.'\'">'.$this->col_content[$j]["title"].'</a>';
							//echo "<a href='".$temp_link."' onclick='{if(confirm('确定要删除记录吗?')){return true;}return false;}' >".$this->col_content[$j]["title"]."</a>";
						}elseif( $this->col_content[$j]["title"] == '修改'){
							echo "<a href='".$temp_link."'><img title='修改' src='../../images/button_write.png'></a>";
						}elseif( $this->col_content[$j]["title"] == '查看'){
							echo "<a href='".$temp_link."'><img title='查看' src='../../images/button_search.png'></a>";
						}elseif( $this->col_content[$j]["title"] == 'PDF'){
							//加了個，能彈出新頁面的連接
							echo "<a target='_blank' href='".$temp_link."'><img title='PDF' src='../../images/button_document-pdf.png'></a>";
						}elseif( $this->col_content[$j]["title"] == '生产单'){
							//加了個，能彈出新頁面的連接
							echo "<a target='_blank' href='".$temp_link."'><img title='生产单' src='../../images/button_document-text.png'></a>";
						}elseif( $this->col_content[$j]["title"] == '出货清单'){
							//加了個，能彈出新頁面的連接
							echo "<a target='_blank' href='".$temp_link."'><img title='出货清单' src='../../images/button_document-pdf.png'></a>";
						}elseif( $this->col_content[$j]["title"] == '出货发票'){
							//加了個，能彈出新頁面的連接
							echo "<a target='_blank' href='".$temp_link."'><img title='出货发票' src='../../images/button_document-text.png'></a>";
						}elseif( $this->col_content[$j]["title"] == '产品送检单'){
							//加了個，能彈出新頁面的連接
							echo "<a target='_blank' href='".$temp_link."'><img title='产品送检单' src='../../images/button_document-text.png'></a>";
						}elseif( $this->col_content[$j]["title"] == '批核'){
							if(@$value_arr[$i]['p_status'] == '未完成' || @$value_arr[$i]['istatus'] == '(D)' || @$value_arr[$i]['status'] == '2'){
								echo "<a href='".$temp_link."'><img title='批核' src='../../images/button_ok.png'></a>";
							}else{
								echo "<a href='".$temp_link."'><img title='取消批核' src='../../images/button_action-undo.png'></a>";
							}
						}elseif( $this->col_content[$j]["title"] == '插入BOM'){
                            //加了個，能彈出新頁面的連接
                            echo "<a href='".$temp_link."'><img title='插入BOM' src='../../images/button_bom.png'></a>";
                        }elseif( $this->col_content[$j]["title"] == 'Sketches'){
                            if(isset($value_arr[$i]['sample_order_file']) && $value_arr[$i]['sample_order_file']){
                                echo "<a target='_blank' href='http://58.177.207.149/sys/upload/sample_order_file/".$value_arr[$i]['sample_order_file']."'><img title='Sketches' src='../../images/button_document-pdf.png'></a>";
                            }
                        }else{
							echo "<a class='button' href='".$temp_link."'>".$this->col_content[$j]["title"]."</a>";
						}
					/*
					}elseif (strlen($this->col_content[$j]["popup_select"])){	
						//this is select column for popup page
						echo "<a href='javascript:;' ";
						echo "onclick='".$this->col_content[$j]["popup_js"];
						echo "(\"".$value_arr[$i][$this->col_content[$j]["field"]];
						if (strlen($value_arr[$i][$this->col_content[$j]["popup_jsvalue"]])){
							//have add extra js value but only for 1 value now
							echo ",\"".$value_arr[$i][$this->col_content[$j]["popup_jsvalue"]]."\"";
						}
						echo "\")'>";
						echo "<img src='img/select.gif'  border='0'>";
						echo "</a>";
					*/	
					}else{
						//link is null and this is not a link
						if ($this->col_content[$j]["valArray"]){
							//need to display  content from specified array
							
							$defaultValue = "异常";
							// $field = getTitleByFromSelect($this->value_content, $field);
							foreach($this->col_content[$j]["valArray"] as $row){
								// echo $row[1]. " + ". $row[0]."<BR>";
								
								if ($row[1] == $value_arr[$i][$this->col_content[$j]["field"]]){
									// echo $defaultValue." VS ".$row[1]. " VS ". $value_arr[$i][$this->col_content[$j]["field"]]."<BR>";
									$defaultValue = $row[0];
									break;
								}
							}
							echo $defaultValue;	
							// echo $value_arr[$i][$this->col_content[$j]["field"]];
							
						}else{
							//normal display from DB content
							if($this->col_content[$j]["callBack"]){
								eval('echo '. $this->col_content[$j]["callBack"] .'(\''. $value_arr[$i][$this->col_content[$j]["field"]] .'\');');
							}else{
								
								//查看product的特殊處理，所以這個 recordset.class2.php 是專門為product的查看使用的!!!!!!!!!
								if($this->col_content[$j]["field"] == 'fty_photo'){
									//每个工厂的上传图片目录都不同，所以要用变量
									global $pic_path_fty, $pic_full_path_fty;

                                    //20130715
                                    if(isFtyAdmin()){
                                        $pic_path_fty = "upload/fty/".$value_arr[$i]['fty_sid'].'/';
                                        $pic_full_path_fty = "/fty/upload/fty/".$value_arr[$i]['fty_sid'].'/';
                                    }
									
									if (is_file(iconv('UTF-8', 'GBK', $pic_path_fty.$value_arr[$i][$this->col_content[$j]["field"]])) == true) { 
										$arr = getimagesize(iconv('UTF-8', 'GBK', $pic_path_fty.$value_arr[$i][$this->col_content[$j]["field"]]));
										$pic_width = $arr[0];
										$pic_height = $arr[1];
										$image_size = getimgsize(100, 60, $pic_width, $pic_height);
										//顯示的圖片在網站目錄下
										echo '<ul><li><a href="'.$pic_full_path_fty.$value_arr[$i][$this->col_content[$j]["field"]].'" class="tooltip2" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="'.$pic_full_path_fty.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
									}else{
										echo '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/>';
									}
									
								}elseif($this->col_content[$j]["field"] == 'photo'){
									//暫時驗證C盤的圖片，正式的時候驗證網站目錄下的圖片
									if (is_file('upload/photo/'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) {
										$arr = getimagesize('upload/photo/'
                                            .$value_arr[$i][$this->col_content[$j]["field"]]);
										$pic_width = $arr[0];
										$pic_height = $arr[1];
										$image_size = getimgsize(100, 60, $pic_width, $pic_height);
										//顯示的圖片在網站目錄下
										echo '<ul><li><a href="upload/photo/'.$value_arr[$i][$this->col_content[$j]["field"]].'" class="tooltip2" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="upload/photo/'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
									}else{
										echo '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/>';
									}
									
								}elseif($this->col_content[$j]["field"] == 'photos'){
                                    //mod 20130508 改显示小图，不然很卡
                                    if(is_file(ROOT_DIR.'/sys/upload/luxsmall/s_'.$value_arr[$i][$this->col_content[$j]["field"]]) == true){
                                        $arr = getimagesize(ROOT_DIR.'/sys/upload/luxsmall/s_'.$value_arr[$i][$this->col_content[$j]["field"]]);
                                        $pic_width = $arr[0];
                                        $pic_height = $arr[1];
                                        $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                                        echo '<a href="/sys/upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]].'" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/sys/upload/luxsmall/s_'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
                                    }else{
                                        if (is_file(ROOT_DIR.'/sys/upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) {
                                            $arr = getimagesize(ROOT_DIR.'/sys/upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]]);
                                            $pic_width = $arr[0];
                                            $pic_height = $arr[1];
                                            $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                                            //顯示的圖片在網站目錄下
                                            //去掉弹出的图，也很卡
                                            //echo '<ul><li><a href="/sys/upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]].'" class="tooltip2" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/sys/upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
                                            echo '<a href="/sys/upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]].'" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/sys/upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
                                        }else{
                                            echo '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/>';
                                        }
                                    }

                                }elseif($this->col_content[$j]["field"] == 'd_date' || $this->col_content[$j]["field"] == 'expected_date' || $this->col_content[$j]["field"] == 'mark_date'){
									echo date('Y-m-d', strtotime($value_arr[$i][$this->col_content[$j]["field"]]));
								}elseif($this->col_content[$j]["field"] == 'istatus' || $this->col_content[$j]["field"] == 's_status'){
									//不同的状态用不同颜色的字显示
									switch($value_arr[$i][$this->col_content[$j]["field"]]){
										case 'Complete':
											echo '<font color="#40AA53"><b>Complete</b></font>';
											break;	
										case 'Partial':
											echo '<font color="#FF0000"><b>Partial</b></font>';
											break;
										case 'Deposit':
											echo '<font color="#FF00FF"><b>Deposit</b></font>';
											break;	
										case 'Balance':
											echo '<font color="#0066FF"><b>Balance</b></font>';
											break;
										case '(I)':
											//echo '<font color="#8000FF"><b>( I )</b></font>';
											echo '<img title="未完成" width="32px" height="32px" src="/images/Incomplete.gif" />';
											break;	
										case '(S)':
											//echo '<font color="#FF6600"><b>( S )</b></font>';
											echo '<img title="已寄货" width="32px" height="32px" src="/images/shipped.gif" />';
											break;	
										case '(P)':
											//echo '<font color="#FF8080"><b>( P )</b></font>';
											echo '<img title="已付款" width="32px" height="32px" src="/images/paid.gif" />';
											break;
										case '(C)':
											//echo '<font color="#40AA53"><b>( C )</b></font>';
											echo '<img title="完成" width="32px" height="32px" src="/images/complete.gif" />';
											break;
                                        case '(D)':
                                            echo '<img title="Draft" width="32px" height="32px" src="/images/draft_small.gif" />';
                                            break;
                                        case 'delete':
											echo '<img title="已删除" width="32px" height="32px" src="/images/deleted.gif" />';
											break;
										default:
											echo $value_arr[$i][$this->col_content[$j]["field"]]; 
									}
								}elseif($this->col_content[$j]["field"] == 'p_status'){
									if($value_arr[$i][$this->col_content[$j]["field"]] == '未完成'){
										echo '<b><font color="#FF0000">未完成</font></b>';	
									}elseif($value_arr[$i][$this->col_content[$j]["field"]] == '已完成'){
										echo '<b><font color="#40AA53">已完成</font></b>';
									}
								}elseif($this->col_content[$j]["field"] == 'send_to'){
									if(strpos($value_arr[$i][$this->col_content[$j]["field"]], "\r\n") === false){
										echo $value_arr[$i][$this->col_content[$j]["field"]];
									}else{
										$temp_arr = explode("\r\n", $value_arr[$i][$this->col_content[$j]["field"]]);
										echo $temp_arr[0];
									}
								}elseif($this->col_content[$j]["field"] == 'total'){
									$rtn_items = $mysql->q('select price, quantity from purchase_item where pcid = ?', $value_arr[$i]['pcid']);
									if($rtn_items){
										$rtn = $mysql->fetch();
										$total = 0;
										foreach($rtn as $v){
											$total += $v['price'] * $v['quantity'];	
										}
										echo formatMoney($total);
									}else{
										echo 'error';	
									}
								}else{
									//為了將數據庫中存儲的 \r\n 轉為<br />
									echo str_replace("\r\n", '<br />', $value_arr[$i][$this->col_content[$j]["field"]]);
									//echo $value_arr[$i][$this->col_content[$j]["field"]];
								}
							}
						}
					}
					echo "</td>";
				}
				echo " </tr>";		
			}
			
		}
		
		echo "</table>";
		echo "<table width='".$this->table_width."' border='0' cellspacing='1' cellpadding='3' bgcolor='#CCCCCC'>";
		echo " <tr bgcolor='#FFFFFF' valign='top'> ";			
		if($this->display_back){
			echo " 	<td align='center' width='20%'><a href='?act=main'>返回</a></td>";
		}else{
			echo " 	<td align='center' width='20%'>&nbsp;</td>";
		}
		echo " 	<td align='center' width='60%'>";		
		
		if ($this->display_paging == GENERAL_YES){		
			$this->RSPaging($row_count);
		}else{
			echo "&nbsp;";
		}		
		echo "	</td>";
		echo " 	<td align='center' width='20%'>";
		if ($this->display_new_button == GENERAL_YES){
			echo "<a href='".$this->addnew_link."' >新增</a>";
		}else{
			echo "&nbsp;";
		}
		echo " 	</td>";
		echo " </tr>";	
		echo "</table>";		
		echo "</fieldset>";	
		echo "</div>";
		// echo "COL:<input type='text' name='col' value='".@$_POST['col']."'>";
		// echo "SEQ:<input type='text' name='seq' value='".@$_POST['seq']."'>";
		// echo "<script type=\"text/javascript\">";
		// echo " function onSort(col, seq){";
		// echo "  document.form1.col.value = col;";
		// echo "  document.form1.seq.value = seq;";
		// echo "  document.form1.action = '';";
		// echo "  document.form1.method = 'POST';";
		// echo "  document.form1.submit();";
		// echo "  ";
		// echo " }";
		// echo "< /script>";		
		//reset default setting for recordset
		$this->ResetRSSetting();		
	}


	function RSPaging($total_rows){
		// global $paging_first, $paging_previous, $paging_next, $paging_last;
		$paging_first = '最前';
		$paging_previous = '前一页';
		$paging_next = '下一页';
		$paging_last = '最后';
		$temp_link = $this->sort_link."&col=".@$_GET["col"];
		
		if (empty($_GET["seq"])){
			$temp_link.= "&seq=DESC";
		}else{
			$temp_link.= "&seq=".$_GET["seq"];
		}
		$temp_link.= "&page=";
		$current_page = $_SESSION['search_criteria']['page'];
		// $current_page = 1;
		// if (!empty($_GET["page"])){
			// $current_page = @$_GET["page"];
		// }
		// echo "<BR>===".$this->total_rows . " VS ". $this->record_per_page."===<BR>";
		//calculate total page 
		$total_page = ceil($total_rows / $this->record_per_page);
		if ($total_page == 0){
			//set to 1 because last page should not be 0
			$total_page = 1;
		}

		// echo "<BR>===".$current_page."====<BR>";
		
		//link for 1st page		
		$first_page = $temp_link."1";
		
		//link for previous page	
		$pre_page = $current_page - 1;			
		if ($pre_page < 1){
			//this is the first page, so set the link same as link for 1st page
			$pre_page = 1;
		}
				
		//link for next page	
		$next_page = $current_page + 1;			
		if ($next_page > $total_page){
			//this is the last page, so set the link same as link for last page			
			$next_page = $total_page;
		}
		
		//link for last page		
		$last_page = $temp_link.$total_page;
		
		echo "		<table width='100%' border='0' cellspacing='0' cellpadding='0'>";
		echo " 			<tr bgcolor='#FFFFFF'> ";	
		echo " 				<td align='center' width='15%'><a href='".$first_page."'>".$paging_first."</a></td>";
		echo " 				<td align='center' width='15%'><a href='".$temp_link.$pre_page."'>".$paging_previous."</td>";
		echo " 				<td align='center' width='40%'>第 ".$current_page." 页，共 ".$total_page." 页</td>";
		echo " 				<td align='center' width='15%'><a href='".$temp_link.$next_page."'>".$paging_next."</td>";
		echo " 				<td align='center' width='15%'><a href='".$last_page."'>".$paging_last."</td>";
		echo " 			</tr>";	
		echo "		</table>";	
		// echo "PAGE: <input type='text' name='page' value='".$current_page."'>";
		// echo "<script type=\"text/javascript\">";
		// echo " function onPage(input){";
		// echo "alert(input);";
		// echo "  document.form1.page.value = input;";
		// echo "  document.form1.action = '';";
		// echo "  document.form1.method = 'POST';";
		// echo "  document.form1.submit();";
		// echo "  ";
		// echo " }";
		// echo "< /script>";
		
		
	}
	
	function SetRSSorting($sort_link){
		$this->sort_link = $sort_link;
		if (strlen(@$_POST["seq"])){
			$this->sort_seq = $_POST["seq"];
		}
		if (strlen(@$_POST["col"])){
			$this->sort_field_index = $_POST["col"];
		}
		if (strlen(@$this->sort_list[$this->sort_field_index])){
			$this->sort_field = $this->sort_list[$this->sort_field_index];
		}
		// print_r_pre($this->sort_list) ;
		
	}
	
	function ResetColSetting(){
	
	}

	function ResetRSSetting(){
	
	}	
}

?>