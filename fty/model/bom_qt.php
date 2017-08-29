<?php


if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();
//禁止其他用户进入（临时做法）
/*if(!isFtyAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}*/

//引用特殊的recordset class 文件
require_once(ROOT_DIR.'fty/in38/recordset.class3.php');

if(isset($_GET['delid']) && $_GET['delid'] != ''){
    $rs = $mysql->q('delete from fty_bom_qt WHERE id = ?', $_GET['delid']);
    if($rs){
        $myerror->ok('删除 BOM其他 成功!', 'bom_qt');
    }else{
        $myerror->error('系统错误，删除 BOM其他 失败', 'bom_qt');
    }
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
    $mod_result = $mysql->qone('select * from fty_bom_qt where id = ?', $_GET['modid']);
}

$form = new My_Forms();
$formItems = array(

    'bom_qt_name_chi' => array('title' => 'BOM其他（中文）', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1, 'value' => isset($mod_result['bom_qt_name_chi'])?$mod_result['bom_qt_name_chi']:''),
    'bom_qt_name_en' => array('title' => 'BOM其他（英文）', 'type' => 'text', 'minlen' => 1, 'maxlen' => 50, 'required' => 1, 'value' => isset($mod_result['bom_qt_name_en'])?$mod_result['bom_qt_name_en']:''),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' 保存 '),
);
$form->init($formItems);


if(!$myerror->getAny() && $form->check()){
    $bom_qt_name_chi = trim($_POST['bom_qt_name_chi']);
    $bom_qt_name_en = trim($_POST['bom_qt_name_en']);

    $staff = $_SESSION['ftylogininfo']['aName'];
    $now = dateMore();

    if( isset($_GET['modid']) && $_GET['modid'] != ''){
        $result = $mysql->q('update fty_bom_qt set bom_qt_name_chi = ?, bom_qt_name_en = ?, mod_by = ?, mod_date = ? where id = ?', $bom_qt_name_chi, $bom_qt_name_en, $staff, $now, $_GET['modid']);
        if($result !== false){
            $myerror->ok('修改 BOM其他 成功!', 'bom_qt');
        }else{
            $myerror->error('系统错误，修改 BOM其他 失败', 'bom_qt');
        }
    }else{
        $result = $mysql->q('insert into fty_bom_qt set bom_qt_name_chi = ?, bom_qt_name_en = ?, created_by = ?, mod_by = ?, in_date = ?, mod_date = ?', $bom_qt_name_chi, $bom_qt_name_en, $staff, $staff, $now, $now);
        if($result){
            $mysql->q('update fty_bom_qt set sort = ? where id = ?', $result, $result);

            $myerror->ok('新增 BOM其他 成功!', 'bom_qt');
        }else{
            $myerror->error('系统错误，新增 BOM其他 失败', 'bom_qt');
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
<fieldset class="center2col" style="width:60%">
    <legend class='legend'><? echo isset($_GET['modid'])?'修改':'新增' ?> BOM其他</legend>
    <?php
    $form->begin();

    $form->show('bom_qt_name_chi');
    $form->show('bom_qt_name_en', '<div class="line"></div>');

    $form->show('submitbtn');

    $form->end();
    ?>
</fieldset>
<?
$rtn = $mysql->q('select * from fty_bom_qt');
$result = $mysql->fetch();
?>

<fieldset class="center2col" style="width:60%">
    <legend class='legend'>BOM其他列表</legend>
    <table width="100%" cellspacing="1" bordercolor='#ABABAB' cellpadding="3" border="1" bgcolor="#000000">
        <tbody>
        <tr bgcolor="#EEEEEE">
            <th height='30' align="center">BOM其他（中文）</th>
            <th height='30' align="center">BOM其他（英文）</th>
            <th align="center">修改</th>
            <th align="center">删除</th>
        </tr>
        <?
        if($result){
            foreach($result as $v){
                ?>
                <tr class="td_" valign="top" onmouseout="this.className='td_';" onmouseover="this.className='td_highlight';">
                    <td align="left"><?=$v['bom_qt_name_chi']?></td>
                    <td align="left"><?=$v['bom_qt_name_en']?></td>
                    <td align="center"><a href="?act=bom_qt&modid=<?=$v['id']?>">修改</a></td>
                    <td align="center"><a href="?act=bom_qt&delid=<?=$v['id']?>">删除</a></td>
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