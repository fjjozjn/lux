<?

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

$goodsForm = new My_Forms();
$formItems = array(
		
		);
$goodsForm->init($formItems);

if(!$myerror->getAny() && $goodsForm->check()){
	
}


if($myerror->getError()){
	require_once(ROOT_DIR.'model/inside_error.php');
}elseif($myerror->getOk()){
	require_once(ROOT_DIR.'model/inside_ok.php');
}else{
	if($myerror->getWarn()){
		require_once(ROOT_DIR.'model/inside_warn.php');
	}
	
	?>
<h1 class="green">产品送检单<em>* 项为必填</em></h1>
<fieldset class="center2col"> 
<legend class='legend'>添加产品送检单</legend>

<?
$goodsForm->begin();


$goodsForm->end();

}

?>

<script>


</script>