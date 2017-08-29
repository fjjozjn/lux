<?php
  //if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
  $warehouse_info = get_warehouse_info('', 'Shop', true);
  //$user_wh = $mysql->qone('select wh_name from tw_admin where AdminID = ?', $_SESSION['luxcraftlogininfo']['aID']);

  $goodsForm = new My_Forms();
  $formItems = array(
	'wh_id' => array('type' => 'select', 'options' => $warehouse_info, 'nostar' => true),
  );
  $goodsForm->init($formItems);

?>

<div style="float: left;">

        Shop : <? $goodsForm->show('wh_id');?>
</div>

		
		
<?php
$form = new My_Forms();
$formItems = array(
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
$form->end();
$getAnyPost = false;
	if ($form->check()){
		$getAnyPost = true;
	}
if ($getAnyPost || isset($_GET['today'])){
		$chart_date = array();

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
				//顶部表头**
				foreach($chart_date as $v){
					if($v == date('Y-m-d')){
						echo '<td align="center" style="font-size:'.$font_size.'px; background-color:#FF0000; width:'.$td_width.'px;"><b><font color="#FFFFFF">'.date('d', strtotime($v)).'<br />'.date('m', strtotime($v)).'</font></b></td>';
					}else{
        				echo '<td align="center" style="font-size:'.$font_size.'px; width:'.$td_width.'px;">'.date('d', strtotime($v)).'<br />'.date('m', strtotime($v)).'</td>';
					}
				}
			
?>

<?
}
?>
<?php
 echo "<br/><br/><br/><br/>";
 include 'testing/demo.html';
 echo "<br/><br/><br/><br/>";
 include 'demo.html';
 echo "<br/><br/><br/><br/>";
 //include 'testing/demo2.html';
  
?>
