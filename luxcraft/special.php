<?php

switch($act){
	case 'login':
		$act = 'index'; //index page us
		require_once('./model/'. $act .'.php');
		break;	
	case 'register';
		$act = 'register';
		require_once('./model/index.php');
		break;			
	//case 'enq_mycard_w9df1b4':
	//	$act = 'enq_mycard_w9df1b4'; //index page us
	//	break;			
	//case 'index':
		// if (isset($_SESSION['ftylogininfo'])){
			// $act = 'main';
		// }
		// break;
	// case 'main':
		// if (!isset($_SESSION['ftylogininfo'])){
			// $act = 'index';			//not login yet, redirect to login page
		// }
		// break;	
	//case 'error':
		//echo '錯誤了';
		//顯然
	default:
		require_once('./model/index.php');
		break;		
}


