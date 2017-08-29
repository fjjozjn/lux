<?
if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');
Class RecordSetControl {
	
	//default setting for recordset
	var $table_width = "100%"; //970 is fit to 1024x768 and no scroll bar appear
	var $recordset_title ="資料列";
	var $recordset_total_row ="總共";
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
		echo "<legend class='legend'>".$this->recordset_title." (".$this->recordset_total_row." : ".$row_count.")"."</legend>";		
		echo "<table width='".$this->table_width."' border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000'>";
		echo " <tr bgcolor='#EEEEEE'> ";
		for($i = 0 ; $i < count($this->col_content); $i++ ){
			echo "<th align='center'";
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
						if(!is_array($this->col_content[$j]["para_name"])){
							$temp_link.= "&".$this->col_content[$j]["para_name"];
							$temp_link.= "=".$value_arr[$i][$this->col_content[$j]["field"]];	
						}else{
							//mod by zjn 20120415 接收array参数，实现url传多个参数的功能
							for($index = 0; $index < count($this->col_content[$j]["para_name"]); $index++){
								$temp_link.= "&".$this->col_content[$j]["para_name"][$index];
								$temp_link.= "=".$value_arr[$i][$this->col_content[$j]["field"][$index]];									
							}
						}
						
						//zjn 添加刪除前的確認
						if( $this->col_content[$j]["title"] == '删除'){
							//文字外用雙引號就不行，轉義單引號就行。。。location也要轉義單引號框起連接。。。
							echo '<a class="button" href="javascript:if(confirm(\'确定要删除吗?\'))window.location=\''.$temp_link.'\'">'.$this->col_content[$j]["title"].'</a>';
							//echo "<a href='".$temp_link."' onclick='{if(confirm('确定要删除记录吗?')){return true;}return false;}' >".$this->col_content[$j]["title"]."</a>";
						}elseif( $this->col_content[$j]["title"] == 'PDF'){
							//加了個，能彈出新頁面的連接
							echo "<a class='button' target='_blank' href='".$temp_link."'>".$this->col_content[$j]["title"]."</a>";
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
								echo $value_arr[$i][$this->col_content[$j]["field"]];
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
		$paging_previous = '前一頁';
		$paging_next = '下一頁';
		$paging_last = '最後';
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
		echo " 				<td align='center' width='40%'>第 ".$current_page." 頁,共 ".$total_page." 頁</td>";
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