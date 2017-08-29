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
<?php
 //echo "<br/><br/><br/><br/>";
 //include 'testing/demo.html';
 //echo "<br/><br/><br/><br/>";
 //include 'demo.html';
 //echo "<br/><br/><br/><br/>";
 //include 'testing/demo2.html';
 include 'testing/grid/index.html';
?>
