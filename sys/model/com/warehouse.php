<?php


if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();
//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}

//引用特殊的recordset class 文件
require_once(ROOT_DIR.'sys/in38/recordset.class2.php');

if(isset($_GET['delid']) && $_GET['delid'] != ''){
    $rs = $mysql->q('delete from warehouse WHERE id = ?', $_GET['delid']);
    if($rs){
        $myerror->ok('Delete Warehouse success!', 'com-warehouse');
    }else{
        $myerror->error('System error, delete Warehouse failure', 'com-warehouse');
    }
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
    $mod_result = $mysql->qone('select * from warehouse where id = ?', $_GET['modid']);
}

$form = new My_Forms();
$formItems = array(

    'wh_name' => array('title' => 'Location Name', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1, 'value' => isset($mod_result['wh_name'])?$mod_result['wh_name']:''),
    'wh_name_chi' => array('title' => '地点名称', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1, 'value' => isset($mod_result['wh_name_chi'])?$mod_result['wh_name_chi']:''),
    'type' => array('title' => 'Type', 'type' => 'select', 'options' => array(array("Warehouse", "Warehouse"), array("Shop", "Shop"), array("Office", "Office")), 'value' => isset($mod_result['type'])?$mod_result['type']:''),
    'description' => array('title' => 'Description', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($mod_result['description'])?$mod_result['description']:''),
    'address' => array('title' => 'Address(Chi)', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($mod_result['address'])?$mod_result['address']:''),
    'address_en' => array('title' => 'Address(EN)', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 200, 'value' => isset($mod_result['address_en'])?$mod_result['address_en']:''),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
);
$form->init($formItems);


if(!$myerror->getAny() && $form->check()){
    $wh_name = $_POST['wh_name'];
    $wh_name_chi = $_POST['wh_name_chi'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    //20140323 新增地址，在工厂出货单中使用
    $address = $_POST['address'];
    $address_en = $_POST['address_en'];

    if( isset($_GET['modid']) && $_GET['modid'] != ''){
        $result = $mysql->q('update warehouse set wh_name = ?, wh_name_chi = ?, type = ?, description = ?, address = ?, address_en = ? where id = ?', $wh_name, $wh_name_chi, $type, $description, $address, $address_en, $_GET['modid']);
        if($result !== false){
            $myerror->ok('Modify Location success!', 'com-warehouse');
        }else{
            $myerror->error('System error, modify Location failure, please check whether the Location Name is exist.', 'com-warehouse');
        }
    }else{
        //默认type是1
        //20131124 默认type改为2
        //20141105 新增type选项
        $result = $mysql->q('insert into warehouse values (NULL, ?, ?, ?, ?, ?, ?, ?)', $wh_name, $wh_name_chi, $description, $address, $address_en, $type);
        if($result){
            $myerror->ok('Add Location success!', 'com-warehouse');
        }else{
            $myerror->error('System error, add Location failure, please check whether the Location Name is exist.', 'com-warehouse');
        }
    }
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
<fieldset class="center2col" style="width:80%">
    <legend class='legend'><? echo isset($_GET['modid'])?'Modify':'Add' ?> Location</legend>


    <?php
    $form->begin();

    $form->show('wh_name');
    $form->show('wh_name_chi');
    $form->show('type');
    ?>
    <br />
    <?php
    $form->show('description', '<br />');
    $form->show('address', '<br />');
    $form->show('address_en', '<div class="line"></div>');
    $form->show('submitbtn');
    ?>
</fieldset>
<?
$form->end();
$rtn = $mysql->q('select * from warehouse');
$result = $mysql->fetch();
?>

<fieldset class="center2col" style="width:80%">
    <legend class='legend'>Theme List</legend>
    <table width="100%" cellspacing="1" bordercolor='#ABABAB' cellpadding="3" border="1" bgcolor="#000000">
        <tbody>
        <tr bgcolor="#EEEEEE">
            <th height='30' align="center">Location Name</th>
            <th height='30' align="center">地点名称</th>
            <th height='30' align="center">Type</th>
            <th height='30' align="center">Description</th>
            <th height='30' align="center">Address(Chi)</th>
            <th height='30' align="center">Address(EN)</th>
            <th align="center">MODIFY</th>
            <th align="center">DEL</th>
        </tr>
        <?
        if($result){
            foreach($result as $v){
                ?>
                <tr class="td_" valign="top" onmouseout="this.className='td_';" onmouseover="this.className='td_highlight';">
                    <td align="left"><?=$v['wh_name']?></td>
                    <td align="left"><?=$v['wh_name_chi']?></td>
                    <td align="left"><?=$v['type']?></td>
                    <td align="left"><?=$v['description']?></td>
                    <td align="left"><?=$v['address']?></td>
                    <td align="left"><?=$v['address_en']?></td>
                    <td align="center"><a href="?act=com-warehouse&modid=<?=$v['id']?>">MODIFY</a></td>
                    <td align="center"><a href="?act=com-warehouse&delid=<?=$v['id']?>">DEL</a></td>
                </tr>
            <?
            }
        }
        ?>
    </table>
    <?
    }
    ?>
</fieldset>