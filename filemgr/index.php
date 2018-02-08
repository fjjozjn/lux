<?php
require($_SERVER['DOCUMENT_ROOT'] . '/in7/global.php');

//界面使用 datatables js插件(https://www.datatables.net/) http://www.jb51.net/article/95944.htm
//1、不允许修改每页条数，"bLengthChange" true改为false
//2、设置为每页显示20条，"iDisplayLength": 20
//3、设置按第一个字段倒序排列，"aaSorting": [[0,'desc']]

if (isset($_POST['submit']) != "") {
    $name = $_FILES['photo']['name'];
    $size = $_FILES['photo']['size'];
    $type = $_FILES['photo']['type'];
    $temp = $_FILES['photo']['tmp_name'];

    move_uploaded_file($temp, "files/" . $name);

    $rs = $mysql->q("insert into filemgr set name = ?, user_id = ?, in_date = ?", $name, $_SESSION["logininfo"]["aID"], dateMore());
    if ($rs) {
        header("location:index.php");
    } else {
        die('error');
    }
}
?>
<html>
<head>
    <title>File Manager</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="screen">
    <link rel="stylesheet" type="text/css" href="css/DT_bootstrap.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css"/>
    <script src="js/jquery.js" type="text/javascript"></script>
    <script src="js/bootstrap.js" type="text/javascript"></script>
    <script type="text/javascript" charset="utf-8" language="javascript" src="js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf-8" language="javascript" src="js/DT_bootstrap.js"></script>
    <style>
        .table tr th {

            border: #eee 1px solid;

            position: relative;
            #font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            text-transform: uppercase;
        }

        table tr td {

            border: #eee 1px solid;
            color: #000;
            position: relative;
            #font-family: "Times New Roman", Times, serif;
            font-size: 12px;

            text-transform: uppercase;
        }

        #wb_Form1 {
            background-color: #00BFFF;
            border: 0px #000 solid;
        }

        #photo {
            border: 1px #A9A9A9 solid;
            background-color: #00BFFF;
            color: #fff;
            font-family: Arial;
            font-size: 20px;
        }
    </style>
</head>

<body>
<div class="alert alert-info">


    &nbsp;&nbsp;FILE MANAGER, User : <?php echo $_SESSION["logininfo"]["aName"]; ?>
</div>
<table cellpadding="0" cellspacing="0" border="0" class="table table-bordered">
    <tr>
        <form enctype="multipart/form-data" action="" id="wb_Form1" name="form" method="post">
            <td><input type="file" name="photo" id="photo" required="required"></td>
            <td><input type="submit" class="btn btn-danger" value="SUBMIT" name="submit"></td>
        </form>
        <strong>SUBMIT HERE</strong>
    </tr>
</table>
<div class="col-md-18">
    <div class="container-fluid" style="margin-top:0px;">
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        <form method="post" action="delete.php">
                            <table cellpadding="0" cellspacing="0" border="0" class="table table-condensed"
                                   id="example">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>FILE NAME</th>
                                    <th>USER</th>
                                    <th>Date</th>
                                    <th>Download</th>
                                    <th>Remove</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if(isSysAdmin()){
                                    $rs = $mysql->q("select * from filemgr order by id desc");
                                }else{
                                    $rs = $mysql->q("select * from filemgr where user_id = ? order by id desc", $_SESSION["logininfo"]["aID"]);
                                }
                                if ($rs) {
                                    $rows = $mysql->fetch();
                                    foreach ($rows as $row) {
                                        ?>
                                        <tr>
                                            <td><?php echo $row['id'] ?></td>
                                            <td><?php echo $row['name'] ?></td>
                                            <td><?php echo $row['user_name'] ?></td>
                                            <td><?php echo $row['in_date'] ?></td>
                                            <td>
                                                <a href="download.php?filename=<?php echo $row['name']; ?>"
                                                   title="click to download"><span class="glyphicon glyphicon-paperclip"
                                                                                   style="font-size:20px; color:blue"></span></a>
                                            </td>
                                            <td>
                                                <a href="delete.php?del=<?php echo $row['id'] ?>"><span
                                                            class="glyphicon glyphicon-trash"
                                                            style="font-size:20px; color:red"></span></a>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                }
                                ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


