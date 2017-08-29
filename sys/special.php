<?php

switch($act){
	case 'login':
		$act = 'index'; //index page us
		break;	
	//case 'enq_mycard_w9df1b4':
	//	$act = 'enq_mycard_w9df1b4'; //index page us
	//	break;			
	//case 'index':
		// if (isset($_SESSION['logininfo'])){
			// $act = 'main';
		// }
		// break;
	// case 'main':
		// if (!isset($_SESSION['logininfo'])){
			// $act = 'index';			//not login yet, redirect to login page
		// }
		// break;	
	//case 'error':
		//echo '錯誤了';
		//顯然
}
require_once('./model/'. $act .'.php');

