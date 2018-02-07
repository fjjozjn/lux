<?
if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');
Class RecordSetControl2 {
	
	//default setting for recordset
	var $table_width = "100%"; //970 is fit to 1024x768 and no scroll bar appear
	var $recordset_title ="Information";
	var $recordset_total_row ="total";
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
	
	//mod 20120718
	var $sortby = '';
	
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
	function SetRecordCol($title, $field, $sort_allow=false ,$edit_view=false, $link="", $para_name="", $callBack = "", $valArray = array()){
		$temp = array();
		if (!$edit_view){
			//edit view is false
			//this is a data column
			$temp["title"] = $title;
			$temp["field"] = $field;
			$temp["width"] = $this->col_width;			
			$temp["align"] = $this->col_align;				
			$temp["link"] = "";		
			//mod 20120718	
			$temp["sort_allow"] = $sort_allow;			
			//$temp["sort_allow"] = false; //temp disabled this function			
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
		global $mysql, $act;
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
				
				//mod 20120718
				echo $this->col_content[$i]["title"];
				if($this->col_content[$i]["title"] == 'ETD'){
					//为了不同情况下箭头的透明或不透明，方便识别现在的排序状况，加了这么多代码。。。。
					$my_act = '';
					if(strpos($act, 'proforma') !== false){
						$my_act = 'proforma';
					}elseif(strpos($act, 'purchase') !== false){
						$my_act = 'purchase';
					}
					if($my_act != ''){
						if(substr($this->sortby, -1) == 'a'){
							echo '<a href="?act=com-search'.$my_act.'&page=1&sortby=expected_date|a"><img width="16" src="images/up.png" /></a>';
							echo '<a href="?act=com-search'.$my_act.'&page=1&sortby=expected_date|d"><img width="16" src="images/down.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" /></a>';
						}elseif(substr($this->sortby, -1) == 'd'){
							echo '<a href="?act=com-search'.$my_act.'&page=1&sortby=expected_date|a"><img width="16" src="images/up.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" /></a>';
							echo '<a href="?act=com-search'.$my_act.'&page=1&sortby=expected_date|d"><img width="16" src="images/down.png" /></a>';
						}else{
							echo '<a href="?act=com-search'.$my_act.'&page=1&sortby=expected_date|a"><img width="16" src="images/up.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" /></a>';
							echo '<a href="?act=com-search'.$my_act.'&page=1&sortby=expected_date|d"><img width="16" src="images/down.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" /></a>';						
						}
					}
				}else{
					echo "<a href='javascript:;' onclick='onSort(\"".$col."\", \"".$temp_seq."\")' ".$temp_class.">".$this->col_content[$i]["title"]."</a>";
				}
				//echo "</a>";
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
		//fb($value_arr);
		for($i = $start ; $i < $page_row; $i++){	
			if (isset($value_arr[$i])){
				
				if($act == 'com-searchproduct_e'){
					// 20121010 点击tr的任意位置都跳转到modify页面
					if($value_arr[$i]['exclusive_to'] != ''){
						echo " <tr bgcolor='#E8FFD0' onclick=\"parent.location='?act=com-modifyproduct_e&modid=".$value_arr[$i]['pid']."';\">";	
					}else{
						echo " <tr class='td_' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top' onclick=\"parent.location='?act=com-modifyproduct_e&modid=".$value_arr[$i]['pid']."';\">";
					}
				}else{
					echo " <tr class='td_' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";
				}
				
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
						if(!is_array($this->col_content[$j]["para_name"])){
							$temp_link.= "&".$this->col_content[$j]["para_name"];

                            //mod by zjn 20151212 参数里有&，则urlencode一下
                            if(strpos($value_arr[$i][$this->col_content[$j]["field"]], '&') !== false){
                                $temp_link.= "=".urlencode($value_arr[$i][$this->col_content[$j]["field"]]);
                            }else{
                                $temp_link.= "=".$value_arr[$i][$this->col_content[$j]["field"]];
                            }
						}else{
							//mod by zjn 20120415 接收array参数，实现url传多个参数的功能
							for($index = 0; $index < count($this->col_content[$j]["para_name"]); $index++){
								$temp_link.= "&".$this->col_content[$j]["para_name"][$index];
								$temp_link.= "=".$value_arr[$i][$this->col_content[$j]["field"][$index]];									
							}
						}
						//zjn 添加刪除前的確認，乱得一塌糊涂，搞不定啊。。。
						if( $this->col_content[$j]["title"] == 'DEL'){
							//文字外用雙引號就不行，轉義單引號就行。。。location也要轉義單引號框起連接。。。
							//mod 20130123 1.04 使用图片按钮
							echo '<a href="javascript:if(confirm(\'This operation will lead to the deletion of the data could not be resumed, confirmed to delete?\'))window.location=\''.$temp_link.'\'" ><img title="Delete" src="../../images/button_trash.png" /></a>';
							//echo '<a class="button" href="javascript:if(confirm(\'This operation will lead to the deletion of the data could not be resumed, confirmed to delete?\'))window.location=\''.$temp_link.'\'">'.$this->col_content[$j]["title"].'</a>';
							//echo "<a href='".$temp_link."' onclick='{if(confirm('确定要删除记录吗?')){return true;}return false;}' >".$this->col_content[$j]["title"]."</a>";
						}elseif( $this->col_content[$j]["title"] == 'DELETE'){
                            echo '<a class="button" href="javascript:if(confirm(\'此操作不会删除BOM信息，但是DELETE后的BOM将不会在Pending BOM里显示，确定要进新此操作吗？\'))window.location=\''.$temp_link.'\'" >'.$this->col_content[$j]["title"].'</a>';
                        }elseif( $this->col_content[$j]["title"] == 'MODIFY'){
							//20130409 去掉settlement 的特殊处理
							/*
							if(strpos($act, 'settlement') !== false){
								if($mysql->qone('select pcid from purchase where (istatus = ? OR istatus = ?) AND pcid = ?', '(I)', '(S)', $value_arr[$i]['po_no'])){
									echo "<a title='Modify' href='".$temp_link."'><img src='../../images/button_write.png'></a>";
								}else{
									echo "";
								}
							}else{
								*/
								echo "<a title='Modify' href='".$temp_link."'><img src='../../images/button_write.png'></a>";
							//}
						}elseif( $this->col_content[$j]["title"] == 'PDF'){
							//加了個，能彈出新頁面的連接
							//echo "<a class='button' target='_blank' href='".$temp_link."'>".$this->col_content[$j]["title"]."</a>";
							echo "<a target='_blank' title='PDF' href='".$temp_link."'><img src='../../images/button_document-pdf.png'></a>";
						}elseif( $this->col_content[$j]["title"] == '出货清单'){
                            //加了個，能彈出新頁面的連接
                            echo "<a target='_blank' href='".$temp_link."'><img title='出货清单' src='../../images/button_document-pdf.png'></a>";
                        }elseif( $this->col_content[$j]["title"] == '出货发票'){
                            //加了個，能彈出新頁面的連接
                            echo "<a target='_blank' href='".$temp_link."'><img title='出货发票' src='../../images/button_document-text.png'></a>";
                        }elseif( $this->col_content[$j]["title"] == 'SHIPPED'){
							if($value_arr[$i]['s_status'] == '(I)'){
								echo "<a title='Shipped' href='".$temp_link."'><img src='../../images/button_plane.png'></a>";
							}elseif($value_arr[$i]['s_status'] == '(S)'){
								echo "<a title='Unshipped' href='".$temp_link."'><img src='../../images/button_action-undo.png'></a>";
							}
						}elseif( $this->col_content[$j]["title"] == 'ADD'){
							echo "<a title='Add Contact' href='".$temp_link."'><img src='../../images/button_man.png'></a>";
						}elseif( $this->col_content[$j]["title"] == 'APPROVE'){
							if(@$value_arr[$i]['istatus'] == '(D)' || @$value_arr[$i]['s_status'] == '(D)'){
								echo '<a title="Approve" href="javascript:if(confirm(\'Approve后将会发通知邮件给相关工厂人员，是否继续?\'))window.location=\''.$temp_link.'\'"><img src="../../images/button_ok.png"></a>';
							}elseif((isset($value_arr[$i]['istatus']) && $value_arr[$i]['istatus'] != '(D)' && @$value_arr[$i]['istatus'] != 'delete') || (isset($value_arr[$i]['s_status']) && $value_arr[$i]['s_status'] != '(D)' && @$value_arr[$i]['s_status'] != 'delete')){
								echo "<a title='Disapprove' href='".$temp_link."'><img src='../../images/button_action-undo.png'></a>";
							}elseif($value_arr[$i]['is_approve'] == 0){
                                echo "<a title='Approve' href='".$temp_link."'><img src='../../images/button_ok.png'></a>";
                            }elseif($value_arr[$i]['is_approve'] == 1){
                                echo "<a title='Disapprove' href='".$temp_link."'><img src='../../images/button_action-undo.png'></a>";
                            }else{
                                echo "<a class='button' href='#'>UNDEFINED</a>";
                            }
						}elseif( $this->col_content[$j]["title"] == 'HR_APPROVE'){
							if($value_arr[$i]['is_approve'] == 0){
								echo "<a class='button' href='".$temp_link."'>APPROVE</a>";
							}elseif($value_arr[$i]['is_approve'] == 1){
								echo "<a class='button' href='".$temp_link."'>DISAPPROVE</a>";
							}else{
								echo "<a class='button' href='#'>UNDEFINED</a>";
							}
						}elseif( $this->col_content[$j]["title"] == 'HISTORY'){
                            //201306201402
                            if(strpos($act, 'warehouse') !== false){
                                echo '<a title="History" href="javascript:" onclick="window.open (\''.$temp_link.'\',\'lux\',\'height=600,width=1000,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no\')"><img src="../../images/button_info2.png"></a>';
                            }else{
							    echo '<a title="History(PI)" href="javascript:" onclick="window.open (\'model/com/proforma_pid_history.php'.$temp_link.'\',\'lux\',\'height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no\')"><img src="../../images/button_info2.png"></a>';
                            }
						}elseif( $this->col_content[$j]["title"] == 'TRANSFER'){
                            echo "<a title='Transfer' href='".$temp_link."'><img src='../../images/button_transfer.png'></a>";
                        }elseif( $this->col_content[$j]["title"] == 'EXCEL'){
                            echo "<a title='EXCEL' href='".$temp_link."'><img src='../../images/button_document-excel.png'></a>";
                        }elseif( $this->col_content[$j]["title"] == 'VIEW CONTACT'){
                            echo "<a title='VIEW CONTACT' href='".$temp_link."'><img src='../../images/button_info2.png'></a>";
                        }elseif( $this->col_content[$j]["title"] == 'BOM'){
                            $rtn = $mysql->qone('select id from bom where g_id = ?', $value_arr[$i]['pid']);
                            if($rtn){
                                echo '<a title="BOM" href="javascript:" onclick="window.open (\''.$temp_link.'\',\'lux\',\'height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no\')"><img src="../../images/button_bom.png"></a>';
                            }else{
                                echo '<a title="BOM" href="javascript:alert(\'none\')"><img src="../../images/button_bom.png"></a>';
                            }
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
								if($this->col_content[$j]["field"] == 'photos' || $this->col_content[$j]["field"] == 'photo'){
									//暫時驗證C盤的圖片，正式的時候驗證網站目錄下的圖片
									//mod 20130508 改显示小图，不然很卡
									if(is_file('upload/luxsmall/s_'.$value_arr[$i][$this->col_content[$j]["field"]]) == true){
										$arr = getimagesize('upload/luxsmall/s_'.$value_arr[$i][$this->col_content[$j]["field"]]);
										$pic_width = $arr[0];
										$pic_height = $arr[1];
										$image_size = getimgsize(100, 60, $pic_width, $pic_height);
										echo '<a href="/sys/upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]].'" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/sys/upload/luxsmall/s_'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
									}else{
										if (is_file('upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) { 
											$arr = getimagesize('upload/lux/'.$value_arr[$i][$this->col_content[$j]["field"]]);
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
									
								}elseif($this->col_content[$j]["field"] == 'photo'){
									//fb(ROOT_DIR.'fty/'.$value_arr[$i][$this->col_content[$j]["field"]]);
									if (is_file(ROOT_DIR.'fty/'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) { 
										$arr = getimagesize(ROOT_DIR.'fty/'.$value_arr[$i][$this->col_content[$j]["field"]]);
										$pic_width = $arr[0];
										$pic_height = $arr[1];
										$image_size = getimgsize(100, 60, $pic_width, $pic_height);
										//顯示的圖片在網站目錄下
										echo '<ul><li><a href="/fty/'.$value_arr[$i][$this->col_content[$j]["field"]].'" class="tooltip2" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/fty/'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
									}else{
										echo '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/>';
									}
									
								}elseif($this->col_content[$j]["field"] == 'search_fty_photo'){
                                    //fb(ROOT_DIR.'fty/'.$value_arr[$i][$this->col_content[$j]["field"]]);
                                    if (is_file(ROOT_DIR.'fty/upload/photo/'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) {
                                        $arr = getimagesize(ROOT_DIR.'fty/upload/photo/'.$value_arr[$i][$this->col_content[$j]["field"]]);
                                        $pic_width = $arr[0];
                                        $pic_height = $arr[1];
                                        $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                                        //顯示的圖片在網站目錄下
                                        echo '<ul><li><a href="/fty/upload/photo/'.$value_arr[$i][$this->col_content[$j]["field"]].'" class="tooltip2" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/fty/upload/photo/'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
                                    }else{
                                        echo '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/>';
                                    }

                                }elseif($this->col_content[$j]["field"] == 'fty_photo'){
									//暫時驗證C盤的圖片，正式的時候驗證網站目錄下的圖片
									if (is_file('../fty/upload/fty/'.iconv('UTF-8', 'GBK', $value_arr[$i]['fty_sid'].'/'.$value_arr[$i][$this->col_content[$j]["field"]])) == true) { 
										$arr = getimagesize('../fty/upload/fty/'.iconv('UTF-8', 'GBK', $value_arr[$i]['fty_sid'].'/'.$value_arr[$i][$this->col_content[$j]["field"]]));
										$pic_width = $arr[0];
										$pic_height = $arr[1];
										$image_size = getimgsize(100, 60, $pic_width, $pic_height);
										//顯示的圖片在網站目錄下
										echo '<ul><li><a href="/fty/upload/fty/'.$value_arr[$i]['fty_sid'].'/'.$value_arr[$i][$this->col_content[$j]["field"]].'" class="tooltip2" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/fty/upload/fty/'.$value_arr[$i]['fty_sid'].'/'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a></li></ul>';
									}else{
										echo '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/>';
									}
									
								}elseif($this->col_content[$j]["field"] == 'poster_photo'){
                                    if (is_file('upload/poster_small/s_'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) {
                                        $arr = getimagesize('upload/poster_small/s_' . $value_arr[$i][$this->col_content[$j]["field"]]);
                                        $pic_width = $arr[0];
                                        $pic_height = $arr[1];
                                        $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                                        echo '<a href="/sys/upload/poster/' . $value_arr[$i][$this->col_content[$j]["field"]] . '" target="_blank" title="' . $value_arr[$i][$this->col_content[$j]["field"]] . '"><img src="/sys/upload/poster_small/s_' . $value_arr[$i][$this->col_content[$j]["field"]] . '" border="0" align="middle" width="' . $image_size['width'] . '" height="' . $image_size['height'] . '"/></a>';
                                    } else {
                                        if (is_file('upload/poster/'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) {
                                            $arr = getimagesize('upload/poster/'.$value_arr[$i][$this->col_content[$j]["field"]]);
                                            $pic_width = $arr[0];
                                            $pic_height = $arr[1];
                                            $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                                            //顯示的圖片在網站目錄下
                                            //去掉弹出的图，也很卡
                                            echo '<a href="/sys/upload/poster/'.$value_arr[$i][$this->col_content[$j]["field"]].'" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/sys/upload/poster/'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
                                        }else{
                                            echo '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/>';
                                        }
                                    }

                                }elseif($this->col_content[$j]["field"] == 'color_chart_photo'){
                                    if (is_file('upload/color_chart_small/s_'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) {
                                        $arr = getimagesize('upload/color_chart_small/s_' . $value_arr[$i][$this->col_content[$j]["field"]]);
                                        $pic_width = $arr[0];
                                        $pic_height = $arr[1];
                                        $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                                        echo '<a href="/sys/upload/color_chart/' . $value_arr[$i][$this->col_content[$j]["field"]] . '" target="_blank" title="' . $value_arr[$i][$this->col_content[$j]["field"]] . '"><img src="/sys/upload/color_chart_small/s_' . $value_arr[$i][$this->col_content[$j]["field"]] . '" border="0" align="middle" width="' . $image_size['width'] . '" height="' . $image_size['height'] . '"/></a>';
                                    } else {
                                        if (is_file('upload/color_chart/'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) {
                                            $arr = getimagesize('upload/color_chart/'.$value_arr[$i][$this->col_content[$j]["field"]]);
                                            $pic_width = $arr[0];
                                            $pic_height = $arr[1];
                                            $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                                            //顯示的圖片在網站目錄下
                                            //去掉弹出的图，也很卡
                                            echo '<a href="/sys/upload/color_chart/'.$value_arr[$i][$this->col_content[$j]["field"]].'" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/sys/upload/color_chart/'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
                                        }else{
                                            echo '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/>';
                                        }
                                    }

                                }elseif($this->col_content[$j]["field"] == 'trend_books_photo'){
                                    if (is_file('upload/trend_books_small/s_'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) {
                                        $arr = getimagesize('upload/trend_books_small/s_' . $value_arr[$i][$this->col_content[$j]["field"]]);
                                        $pic_width = $arr[0];
                                        $pic_height = $arr[1];
                                        $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                                        echo '<a href="/sys/upload/trend_books/' . $value_arr[$i][$this->col_content[$j]["field"]] . '" target="_blank" title="' . $value_arr[$i][$this->col_content[$j]["field"]] . '"><img src="/sys/upload/trend_books_small/s_' . $value_arr[$i][$this->col_content[$j]["field"]] . '" border="0" align="middle" width="' . $image_size['width'] . '" height="' . $image_size['height'] . '"/></a>';
                                    } else {
                                        if (is_file('upload/trend_books/'.$value_arr[$i][$this->col_content[$j]["field"]]) == true) {
                                            $arr = getimagesize('upload/trend_books/'.$value_arr[$i][$this->col_content[$j]["field"]]);
                                            $pic_width = $arr[0];
                                            $pic_height = $arr[1];
                                            $image_size = getimgsize(100, 60, $pic_width, $pic_height);
                                            //顯示的圖片在網站目錄下
                                            //去掉弹出的图，也很卡
                                            echo '<a href="/sys/upload/trend_books/'.$value_arr[$i][$this->col_content[$j]["field"]].'" target="_blank" title="'.$value_arr[$i][$this->col_content[$j]["field"]].'"><img src="/sys/upload/trend_books/'.$value_arr[$i][$this->col_content[$j]["field"]].'" border="0" align="middle" width="'.$image_size['width'].'" height="'.$image_size['height'].'"/></a>';
                                        }else{
                                            echo '<img src="/images/nopic.gif" border="0" align="middle" width="80" height="60"/>';
                                        }
                                    }

                                }elseif($this->col_content[$j]["field"] == 's_status' || $this->col_content[$j]["field"] == 'p_status' || $this->col_content[$j]["field"] == 'st_status' || $this->col_content[$j]["field"] == 'istatus'){
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
											echo '<img title="Incomplete" width="32px" height="32px" src="/images/Incomplete.gif" />';
											break;	
										case '(S)':
											echo '<img title="Shipped" width="32px" height="32px" src="/images/shipped.gif" />';
											break;	
										case '(P)':
											echo '<img title="Paid" width="32px" height="32px" src="/images/paid.gif" />';
											break;
										case '(C)':
											echo '<img title="Complete" width="32px" height="32px" src="/images/complete.gif" />';
											break;
										case '(D)':
											echo '<img title="Draft" width="32px" height="32px" src="/images/draft_small.gif" />';
											break;													
										case 'delete':
											echo '<img title="Deleted" width="32px" height="32px" src="/images/deleted.gif" />';
											break;
										default:
											echo $value_arr[$i][$this->col_content[$j]["field"]]; 
									}
								}elseif($this->col_content[$j]["field"] == 'content'){
									//正则匹配出 id 加上链接
									echo match_id($value_arr[$i][$this->col_content[$j]["field"]]);
								}elseif(strpos($this->col_content[$j]["field"], 'date') != false && $act !=
                                    'searchhr'){
                                    //20130728 去掉上面的多余的条件，因为有strpos date了，且hr需要显示小时
									echo $value_arr[$i][$this->col_content[$j]["field"]]==''?$value_arr[$i][$this->col_content[$j]["field"]]:date('Y-m-d', strtotime($value_arr[$i][$this->col_content[$j]["field"]]));
								}elseif($this->col_content[$j]["field"] == 'cost_rmb' && $act == 'com-searchproduct_e'){
									$rtn_s = $mysql->qone('select markup from setting limit 1');
									$rtn_c = $mysql->qone('select rate from currency where type = ?', 'USD');
									echo formatMoney($value_arr[$i][$this->col_content[$j]["field"]] * $rtn_s['markup'] * $rtn_c['rate']);
								}elseif($this->col_content[$j]["field"] == 'send_to'){
									if(strpos($value_arr[$i][$this->col_content[$j]["field"]], "\r\n") === false){
										echo $value_arr[$i][$this->col_content[$j]["field"]];
									}else{
										$temp_arr = explode("\r\n", $value_arr[$i][$this->col_content[$j]["field"]]);
										echo $temp_arr[0];
									}
								}elseif($this->col_content[$j]["field"] == 'total'){
									//使 quotation proforma invoice customs_invoice purchase 能通用
									$table = substr($act, 10, strlen($act) - 10);
									$field = '';
									switch($table){
										case 'quotation':
											$table = 'quote';
											$field = 'qid';
											break;
										case 'proforma':
											$field = 'pvid';
											break;
										case 'invoice':
											$field = 'vid';
											break;
										case 'customsinvoice':
											$table = 'customs_invoice';
											$field = 'vid';
											break;		
										case 'purchase':
											$field = 'pcid';
                                            break;
                                        case '_retail_sales_memo':
                                            $table = 'retail_sales_memo';
                                            $field = 'rsm_id';
                                            break;
									}
									$rtn_items = $mysql->q('select price, quantity from '.$table.'_item where '.$field.' = ?', $value_arr[$i][$field]);
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
								}elseif(isset($value_arr[$i]['AdminEnabled']) && $value_arr[$i]['AdminEnabled'] == 2){
									echo '<font color="#FF0000">'.str_replace("\r\n", '<br />', $value_arr[$i][$this->col_content[$j]["field"]]).'</font>';
								}elseif($this->col_content[$j]["field"] == 'po_no'){
									echo "<a href='?act=com-modifypurchase&modid=".$value_arr[$i][$this->col_content[$j]["field"]]."'>".$value_arr[$i][$this->col_content[$j]["field"]]."</a>";
								}elseif($this->col_content[$j]["field"] == 'is_approve'){
									if($value_arr[$i][$this->col_content[$j]["field"]] == 1){
										echo redFont('Approved');	
									}elseif($value_arr[$i][$this->col_content[$j]["field"]] == 0){
										echo 'Pending';
									}else{
										echo 'Error! (201301221553)';	
									}
								}elseif($this->col_content[$j]["field"] == 'show_in_catalog'){
									if($value_arr[$i][$this->col_content[$j]["field"]] == 1){
										echo 'Yes';	
									}elseif($value_arr[$i][$this->col_content[$j]["field"]] == 0){
										echo 'No';
									}else{
										echo 'Error! (201302262345)';
									}
								}elseif($this->col_content[$j]["field"] == 'pid'){
                                    echo "<a href='?act=com-modifyproduct_new&modid=".$value_arr[$i][$this->col_content[$j]["field"]]."'>".$value_arr[$i][$this->col_content[$j]["field"]]."</a>";
                                }elseif( $this->col_content[$j]["field"] == 'cid'){
                                    echo "<a target='_blank' title='Customer' href='?act=com-modifycustomer&modid=".$value_arr[$i][$this->col_content[$j]["field"]]."'>".$value_arr[$i][$this->col_content[$j]["field"]]."</a>";
                                }elseif($this->col_content[$j]["field"] == 'product_file'){
                                    if (isset($value_arr[$i]['product_file']) && $value_arr[$i]['product_file']) {
                                        echo "<a target='_blank' href='/sys/upload/product_file/".$value_arr[$i]['product_file']."'><img src='../../images/button_document-text.png'></a>";
                                    }else{
                                        echo '';
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
			echo " 	<td align='center' width='20%'><a href='?act=main'>Return</a></td>";
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
		$paging_first = 'First';
		$paging_previous = 'Previous';
		$paging_next = 'Next';
		$paging_last = 'Last';
		$temp_link = $this->sort_link."&col=".@$_GET["col"];

        //20130720
        global $act;
        if($act == 'com-search_warehouse_item_unique'){
            $temp_link .= "&wh_name=".(isset($_GET["wh_name"])?$_GET["wh_name"]:'');
        }
        if($act == 'com-search_retail_sales_memo'){
            $temp_link .= "&wh_name=".(isset($_GET["wh_name"])?$_GET["wh_name"]:'');
        }

		if (empty($_GET["seq"])){
			$temp_link.= "&seq=DESC";
		}else{
			$temp_link.= "&seq=".$_GET["seq"];
		}
		
		//mod 20120718
		if($this->sortby == ''){
			$temp_link.= "&page=";
		}else{
			$temp_link.= "&sortby=".$this->sortby."&page=";
		}
		
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
		echo " 				<td align='center' width='40%'>Page ".$current_page." of ".$total_page."</td>";
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