<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 13-5-26
 * Time: 下午2:20
 * To change this template use File | Settings | File Templates.
 */

//20130227 mod by zjn
//$strScriptName = "http://122.128.109.116/product/";
//20131218
//$strScriptName = "http://223.197.254.157/product/";
//20150923
//$strScriptName = "http://www.luxerp.com/product/";
//20150928
//$strScriptName = "http://58.177.207.149:5900/product/";
//20171212
$strScriptName = "http://58.177.207.149/product/";

?>

<html>

<head>
    <meta http-equiv="Content-Language" content="zh-tw">
    <meta name="GENERATOR" content="Microsoft FrontPage 5.0">
    <meta name="ProgId" content="FrontPage.Editor.Document">
    <meta http-equiv="Content-Type" content="text/html; charset=big5">

    <title>Lux Design Limited</title>
    <style type="text/css">
        form.pos_abs
        {
            position:absolute;
            left:100px;
            top:45px
        }
    </style>
</head>

<body>
<center width="480px" height="250px">
    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="480px" height="250px" id="AutoNumber1" bgcolor="#FFFFFF">
        <tr>
            <td width="160"><a href="password_protect.php"></td>
            <td width="1">&nbsp;</td>
            <td width="347" colspan="3">
                <div align="center">
                    <form action="<?=$strScriptName?>" method="POST" target="_parent" class="pos_abs">
                        <h2><font face="Arial" size="3" color="Black">This page is access controlled.<BR>Please enter the password key.</font><br></h2>
                        <input type="password" name="MyPWD" size="10"><BR>
                        <input type="submit" value="Submit" style="float:center">
                    </form>
                </div>
            </td>
            <td><a href="password_protect.php"></td>
        </tr>
    </table>
</center>

</body>

</html>


