<html> 
<head> 
<title></title> 
</head> 
<body> 
<?php 
if(isset($_GET['action']) && $_GET['action'] == 'upfile') 
{ 
	$target_path = $_FILES['photo']['name']; 
	echo '上传的临时文件：' .$_FILES['photo']['tmp_name'] . '<br/>';
	echo '上传的目标文件：' .$target_path . '<br/>';
	echo $_SERVER["SCRIPT_FILENAME"] . '<br/>';
	echo $_SERVER["OS"] . '<br/>';
	//测试函数:　move_uploaded_file
	//也可以用函数：copy
	move_uploaded_file($_FILES['photo']['tmp_name'], $target_path); 
	echo "Upload result:"; 
	if(file_exists($target_path)) { 
	/*
 		if($_SERVER["OS"]!="Windows_NT"){
  			@chmod($target_path,0604);
 		}
		*/
 		echo '<font color="green">Succeed!</font><br /><a href="http://' .$_SERVER["SERVER_NAME"] . "/" .$target_path .'"><img src=' .$target_path .' border="0">'; 
	} else { 
 		echo '<font color="red">Failed!</font>'; 
	} 
	exit; 
} 
?> 
<h1>Registration</h1> 
<form action="?act=upload_photo_test&action=upfile" method="post" name="UForm" enctype="multipart/form-data"> 
<fieldset> 
<legend>Your information</legend> 
<ul> 
<li>Your Phot<input type="file" name="photo"></li> 
</ul> 
</fieldset> 
<button type="submit">上传</button> 
</form> 
</body> 
</html>