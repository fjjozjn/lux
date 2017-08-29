<style>
/*20130427*/
.td_chart_highlight {
	background-color:#F3F3F3;
}
</style>

<? //20130504 不知道为什么，用了下面这个，会使日期显示间隔前紧后松,前面的表头更宽度小，后面的表头宽度大 。。。?>
<script>
jQuery.fn.fixedtableheader = function(options) { var settings = jQuery.extend({ headerrowsize: 1, highlightrow: false, highlightclass: "highlight" }, options); this.each(function(i) { var $tbl = $(this); var $tblhfixed = $tbl.find("tr:lt(" + settings.headerrowsize + ")"); var headerelement = "th"; if ($tblhfixed.find(headerelement).length == 0) headerelement = "td"; if ($tblhfixed.find(headerelement).length > 0) { $tblhfixed.find(headerelement).each(function() { $(this).css("width", $(this).width()); }); var $clonedTable = $tbl.clone().empty(); var tblwidth = GetTblWidth($tbl); $clonedTable.attr("id", "fixedtableheader" + i).css({ "position": "fixed", "top": "0", "left": $tbl.offset().left }).append($tblhfixed.clone()).width(tblwidth).hide().appendTo($("body")); if (settings.highlightrow) $("tr:gt(" + (settings.headerrowsize - 1) + ")", $tbl).hover(function() { $(this).addClass(settings.highlightclass); }, function() { $(this).removeClass(settings.highlightclass); }); $(window).scroll(function() { if (jQuery.browser.msie && jQuery.browser.version == "6.0") $clonedTable.css({ "position": "absolute", "top": $(window).scrollTop(), "left": $tbl.offset().left }); else $clonedTable.css({ "position": "fixed", "top": "0", "left": $tbl.offset().left - $(window).scrollLeft() }); var sctop = $(window).scrollTop(); var elmtop = $tblhfixed.offset().top; if (sctop > elmtop && sctop <= (elmtop + $tbl.height() - $tblhfixed.height())) $clonedTable.show(); else $clonedTable.hide(); }); $(window).resize(function() { if ($clonedTable.outerWidth() != $tbl.outerWidth()) { $tblhfixed.find(headerelement).each(function(index) { var w = $(this).width(); $(this).css("width", w); $clonedTable.find(headerelement).eq(index).css("width", w); }); $clonedTable.width($tbl.outerWidth()); } $clonedTable.css("left", $tbl.offset().left); }); } }); function GetTblWidth($tbl) { var tblwidth = $tbl.outerWidth(); return tblwidth; } };

$(function () {
	//$(".tableheader").fixedtableheader({ highlightrow: true, highlightclass: "td_chart_highlight", headerrowsize: 1 });
});
</script>

<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{

	//已用新DB，引用特殊的recordset class 文件
	require_once(ROOT_DIR.'sys/in38/recordset.class2.php');
	//$luxmysql = new My_Mysql($luxDbInfo);

// 如果有post资料则给Session，并且清除附在上次翻页时残留的$_GET['page']
if (count($_POST)){
	$_SESSION['search_criteria'] = $_POST;
	$_GET['page'] = 1;
}

$form = new My_Forms();
$formItems = array(
/*
		'chart_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['start_date'], 
			),	
		'number_of_days' => array(
			'type' => 'text',
			'minlen' => 1, 
			'maxlen' => 2,
			'value' => @$_SESSION['search_criteria']['number_of_days'], 
			),
			*/
		'start_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['start_date'], 
			),	
		'end_date' => array(
			'type' => 'text', 
			'restrict' => 'date',
			'value' => @$_SESSION['search_criteria']['end_date'], 
			),		
		'submitbutton' => array(
			'type' => 'submit', 
			'value' => 'Search', 
			),	
);
$form->init($formItems);
$form->begin();


?>
<h1 class="green">CHART<em>* indicates required fields</em></h1>

<table width="700" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">            	
			<!--tr>
				<td>Date : </td>
				<td><?
				//$form->show('chart_date');
				?></td>		
                
                <td>Number of Days</td>
				<td><?
				//$form->show('number_of_days');
				?></td>
			</tr-->
 			<tr>
				<td>Start Date : </td>
				<td><?
				$form->show('start_date');
				?></td>		
                
                <td>End Date : </td>
				<td><?
				$form->show('end_date');
				?></td>
			</tr>           
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td width="100%" colspan='4'>
				<?
				$form->show('submitbutton');				
				?></td>
			</tr>				
		</table>
	</fieldset>	
	</td>	
	</tr>
</table>

<?
	$form->end();
	
	//如果有合法的提交，则 getAnyPost = true。
	//如果不是翻页而是普通的GET，则清除之前的Session，以显示一个空白的表单
	$getAnyPost = false;
	if ($form->check()){
		$getAnyPost = true;
	}/*elseif(!isset($_GET['page'])){
		unset($_SESSION['search_criteria']);
	}*/
	
	if($myerror->getAny()){
		require_once(ROOT_DIR.'model/inside_warn.php');
	}
	
	//默认加today参数，显示当前天的最近一个月记录
	if ($getAnyPost || isset($_GET['today'])){
		$chart_date = array();
		
		/* 方案1
		//记录查看的日期前6天后26天的日期数组（前后都多1天是为了显示边界点是否开始结束点，如果不是则在这加个标志，如渐淡的图）
		$before = 6;
		
		if (strlen(@$_SESSION['search_criteria']['number_of_days'])){
			$after = $_SESSION['search_criteria']['number_of_days'] + 1;
		}else{
			$after = 26;
		}
		
		if (strlen(@$_SESSION['search_criteria']['chart_date'])){
			$chart_date = cal_date($_SESSION['search_criteria']['chart_date'], $before, $after);		
		}else{
			$chart_date = cal_date(date('Y-m-d'), $before, $after);			
		}
		*/
		
		//方案2
		if (strlen(@$_SESSION['search_criteria']['start_date']) && strlen(@$_SESSION['search_criteria']['end_date'])){
			$chart_date = cal_date_new($_SESSION['search_criteria']['start_date'], $_SESSION['search_criteria']['end_date']);
		}else{
			if(isset($_GET['today'])){
				//从导航栏点过带的会带 today 参数，这样默认显示当天的前5后24天，加当天共30天
				$chart_date = cal_date_new(date('Y-m-d',strtotime('-5 day')), date('Y-m-d',strtotime('+24 day')));
			}else{
				echo '<script>alert("Please fill in the start date and end date.");</script>';	
				die();
			}
		}
		
		
		//找出在区间内的pi和po
		$start_date = $chart_date[0];
		$end_date = $chart_date[count($chart_date) -1];
		$pipo = array();
		if(isSysAdmin()){
			$rs = $mysql->q('select pvid, mark_date, expected_date from proforma where mark_date < ? and expected_date > ? order by mark_date desc', $end_date, $start_date);
		}else{
			$rs = $mysql->q('select pvid, mark_date, expected_date from proforma where mark_date < ? and expected_date > ? AND printed_by in (SELECT AdminName FROM tw_admin WHERE AdminLuxGroup LIKE ? OR AdminName = ?) order by mark_date desc', $end_date, $start_date, '%'.$_SESSION['logininfo']['aName'].'%', $_SESSION['logininfo']['aName']);
		}
		if($rs){
			$rtn = $mysql->fetch();
			$index = 0;
			foreach($rtn as $v){
				$pipo[$index]['pi'][] = $v['pvid'];
				$pipo[$index]['pi'][] = date('Y-m-d', strtotime($v['mark_date']));
				$pipo[$index]['pi'][] = date('Y-m-d', strtotime($v['expected_date']));
				//20130504
				//由PI查询VI的shipment
				$shipment_date = $mysql->qone('select s_date from shipment where pi_no = ? and s_status = ?', $v['pvid'], 'Complete');
				if($shipment_date){
					$pipo[$index]['pi']['s_date'] = date('Y-m-d', strtotime($shipment_date['s_date']));
				}
				//由PI查询VI的payment
				$payment_date = $mysql->qone('select p_date from payment where pi_no = ? and p_status = ?', $v['pvid'], 'Balance');
				if($payment_date){
					$pipo[$index]['pi']['p_date'] = date('Y-m-d', strtotime($payment_date['p_date']));
				}
				
				//我看了purchase表 每个pi会对应多个po，只不过不是在po号后加A、B、C，而是新的po号
				$po_rs = $mysql->q('select pcid, mark_date, expected_date from purchase where reference = ?', $v['pvid']);
				if($po_rs){
					$po_rtn = $mysql->fetch();
					$index_po = 0;
					foreach($po_rtn as $w){
						$pipo[$index]['po'][$index_po][] = $w['pcid'];
						$pipo[$index]['po'][$index_po][] = date('Y-m-d', strtotime($w['mark_date']));	
						$pipo[$index]['po'][$index_po][] = date('Y-m-d', strtotime($w['expected_date']));	
						$index_po++;	
					}
				}
				$index++;
			}
		}
		
		
		//fb($pipo);
		//fb($chart_date);
		
		//*******************参数设置*******************
		//设置日期和pi po的字体大小
		$font_size = 10;
		//设置日期表头每个单元格宽度
		$td_width = 30;
		//设置除顶部表头外每行的高度
		$tr_height = 20;
		//设置pi进度条背景颜色
		$pi_bg_color = '#CCE8CF';
		//设置po进度条背景颜色
		$po_bg_color = '#BDE719';
		//设置pi与po重复了的进度条背景颜色
		$pipo_bg_color = '#40AA53';		
		//设置表格只有横线的border
		$attr = 'style=" border-collapse:collapse; border:1px solid #DADADA" rules="rows"';
		//*******************参数设置*******************
?>
<div align="center">
<br />
<font color="#FF0000"><font size="+1"><b>Gantt Chart</b></font> <span style="background-color:<?=$pi_bg_color?>">PI Colour</span> <span style="background-color:<?=$po_bg_color?>">PO Colour</span> <span style="background-color:<?=$pipo_bg_color?>">PI & PO Overlap</span><br /></font>
<br />
<br />
		<table <?=@$attr?> class="tableheader">
            <thead>
            <tr>
            	<td align="right"><font style="font-size:<?=$font_size?>px">Day<br />Month</font></td>
        	<?
				//顶部表头**
				foreach($chart_date as $v){
					if($v == date('Y-m-d')){
						echo '<td align="center" style="font-size:'.$font_size.'px; background-color:#FF0000; width:'.$td_width.'px;"><b><font color="#FFFFFF">'.date('d', strtotime($v)).'<br />'.date('m', strtotime($v)).'</font></b></td>';
					}else{
        				echo '<td align="center" style="font-size:'.$font_size.'px; width:'.$td_width.'px;">'.date('d', strtotime($v)).'<br />'.date('m', strtotime($v)).'</td>';
					}
				}
				//*********
			?>
			</tr>
            </thead>

            <tbody>
            <?
				foreach($pipo as $v){
					echo '<tr align="left" height="'.$tr_height.'px">';
					
					//左侧表头td***
					echo '<td style="font-size:'.$font_size.'px">';
					echo "<a href='?act=com-modifyproforma&modid=".$v['pi'][0]."'>".$v['pi'][0]."</a>&nbsp;";
					if(isset($v['po'])){
						foreach($v['po'] as $u){
							echo "<a href='?act=com-modifypurchase&modid=".$u[0]."'>".$u[0]."</a>&nbsp;";
						}
					}
					echo '</td>';
					//************
					
					foreach($chart_date as $w){
						//标志：确定这个格用什么颜色的背景。0无色，1 pi色（最浅），2 po色（略深），3 pi和po重复的色（最深）
						//同时符合pi 和 po ，则显示po 的颜色
						$sign = 0;
						
						//pi**
						if($v['pi'][1] <= $w && $v['pi'][2] >= $w){
							$sign = 1;
						}
						//****
						
						//po**
						if(isset($v['po'])){
							foreach($v['po'] as $u){
								if($u[1] <= $w && $u[2] >= $w){
									if($sign == 1){
										$sign = 3;
									}elseif($sign == 0){
										$sign = 2;	
									}
								}
							}
						}
						//****

						//20130504 加s与p的状态
						$status = '';
						if(isset($v['pi']['s_date']) && $v['pi']['s_date'] == $w){
							$status .= '<font color="#FF7D00"><b>S</b></font>';	
						}
						if(isset($v['pi']['p_date']) && $v['pi']['p_date'] == $w){
							$status .= '<font color="#FFFF00"><b>P</b></font>';	
						}
						
						if($sign == 1){
							echo '<td align="center" style="background-color:'.$pi_bg_color.'">'.$status.'</td>';	
						}elseif($sign == 2){
							echo '<td align="center" style="background-color:'.$po_bg_color.'">'.$status.'</td>';		
						}elseif($sign == 3){
							echo '<td align="center" style="background-color:'.$pipo_bg_color.'">'.$status.'</td>';		
						}else{
							echo '<td align="center">'.$status.'</td>';	
						}
					}
					echo '</tr>';
					
					//每个pi和相应的po块结束后，下面有个空行
					//echo '<tr><td>&nbsp;</td></tr>';
				}
			?>
            </tbody>
		</table>
</div>        
<?
	}

}
?>

