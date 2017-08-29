<?php

//20130403 这里面用fb无法在控制台显示，只好用写文件的方法来调试与记录日志了
//include_once($_SERVER["DOCUMENT_ROOT"].'/class/FB/fb.php');
//fb('0');

//多维数组转为字符串
function arrayToString($arr)
{
	if (is_array($arr))
	{
		return implode(',', array_map('arrayToString', $arr));
	}
	return $arr;
}
file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log/test_log.txt', date('Y-m-d H:i:s').' : '.arrayToString($_FILES). "\r\n", FILE_APPEND);


	// Work-around for setting up a session because Flash Player doesn't send the cookies
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	session_start();
	

// Settings

    //20131121 图片sys/upload 下
    $save_path = substr(getcwd(), 0, -10) . "/upload/qc/";                // The path were we will save the file (getcwd() may not be reliable and should be tested in your environment)

    $upload_name = "Filedata";
    $max_file_size_in_bytes = 2147483647;                // 2GB in bytes
    $extension_whitelist = array("jpg", "gif", "png");    // Allowed file extensions
    $valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';                // Characters allowed in the file name (in a Regular Expression format)
    
// Other variables    
    $MAX_FILENAME_LENGTH = 260;
    $file_name = "";
    $file_extension = "";
    $uploadErrors = array(
        0=>"There is no error, the file uploaded with success",
        1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
        2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        3=>"The uploaded file was only partially uploaded",
        4=>"No file was uploaded",
        6=>"Missing a temporary folder"
    );


// Validate the upload
    if (!isset($_FILES[$upload_name])) {
        HandleError("No upload found in \$_FILES for " . $upload_name);
        exit(0);
    } else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
        HandleError($uploadErrors[$_FILES[$upload_name]["error"]]);
        exit(0);
    } else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
        HandleError("Upload failed is_uploaded_file test.");
        exit(0);
    } else if (!isset($_FILES[$upload_name]['name'])) {
        HandleError("File has no name.");
        exit(0);
    }
    
// Validate the file size (Warning: the largest files supported by this code is 2GB)
    $file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
    if (!$file_size || $file_size > $max_file_size_in_bytes) {
        HandleError("File exceeds the maximum allowed size");
        exit(0);
    }
    
    if ($file_size <= 0) {
        HandleError("File size outside allowed lower bound");
        exit(0);
    }


// Validate file name (for our purposes we'll just remove invalid characters)
    $file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
    if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
        HandleError("Invalid file name");
        exit(0);
    }


// Validate that we won't over-write an existing file
    if (file_exists($save_path . $file_name)) {
        HandleError("File with this name already exists");
        exit(0);
    }

// Validate file extension
    $path_info = pathinfo($_FILES[$upload_name]['name']);
    $file_extension = $path_info["extension"];
    $is_valid_extension = false;
    foreach ($extension_whitelist as $extension) {
        if (strcasecmp($file_extension, $extension) == 0) {
            $is_valid_extension = true;
            break;
        }
    }
    if (!$is_valid_extension) {
        HandleError("Invalid file extension");
        exit(0);
    }


    if (!move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path.$file_name)) {
        HandleError("File could not be saved.");
        exit(0);
    }

    exit(0);


/* Handles the error output. This error message will be sent to the uploadSuccess event handler.  The event handler
will have to check for any error messages and react as needed. */
function HandleError($message) {
    echo $message;
}

?>