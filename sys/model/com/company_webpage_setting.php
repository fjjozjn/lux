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
    $rs = $mysql->q('delete from tw_webpage_setting WHERE id = ?', $_GET['delid']);
    if($rs){
        $myerror->ok('Delete Company Webpage Setting success!', 'com-company_webpage_setting');
    }else{
        $myerror->error('System error, delete Company Webpage Setting failure', 'com-company_webpage_setting');
    }
}

if( isset($_GET['modid']) && $_GET['modid'] != ''){
    $mod_result = $mysql->qone('select * from tw_webpage_setting where id = ?', $_GET['modid']);
}

$form = new My_Forms();
$formItems = array(

    'title' => array('title' => 'Title', 'type' => 'text', 'minlen' => 1, 'maxlen' => 255, 'required' => 1, 'value' => isset($mod_result['title'])?$mod_result['title']:''),
    'content' => array('title' => 'Content', 'type' => 'textarea', 'value' => isset($mod_result['content'])?$mod_result['content']:''),
    'sort' => array('title' => 'Sort', 'type' => 'text', 'minlen' => 1, 'maxlen' => 5, 'restrict' => 'number', 'value' => isset($mod_result['sort'])?$mod_result['sort']:'', 'info' => '数值大的排前面'),

    'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
);
$form->init($formItems);


if(!$myerror->getAny() && $form->check()){
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $sort = trim($_POST['sort']);
    $time = date('Y-m-d H:i:s');
    $staff = $_SESSION['logininfo']['aName'];

    if( isset($_GET['modid']) && $_GET['modid'] != ''){
        $mod_date = $time;
        $mod_by = $staff;
        $result = $mysql->q('update tw_webpage_setting set title = ?, content = ?, sort = ?, mod_date = ?, mod_by = ? where id = ?', $title, $content, $sort, $mod_date, $mod_by, $_GET['modid']);
        if($result !== false){
            $myerror->ok('Modify Company Webpage Setting success!', 'com-company_webpage_setting');
        }else{
            $myerror->error('System error, modify Company Webpage Setting failure', 'com-company_webpage_setting');
        }
    }else{
        $in_date = $mod_date = $time;
        $created_by = $mod_by = $staff;
        $result = $mysql->q('insert into tw_webpage_setting set title = ?, content = ?, sort = ?, in_date = ?, mod_date = ?, created_by = ?, mod_by
 = ?', $title, $content, $sort, $in_date, $mod_date, $created_by, $mod_by);
        if($result){
            $myerror->ok('Add Company Webpage Setting success!', 'com-company_webpage_setting');
        }else{
            $myerror->error('System error, add Company Webpage Setting failure', 'com-company_webpage_setting');
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
<fieldset class="center2col" style="width:50%">
    <legend class='legend'><? echo isset($_GET['modid'])?'Modify':'Add' ?> Company Webpage Setting</legend>

    <?php
    $form->begin();

    $form->show('title');
    ?>
    <br />
    <?php
    $form->show('content', '<br />');
    $form->show('sort', '<div class="line"></div>');
    $form->show('submitbtn');
    ?>
</fieldset>
<?
$form->end();
$rtn = $mysql->q('select * from tw_webpage_setting order by sort desc');
$result = $mysql->fetch();
?>

<fieldset class="center2col" style="width:50%">
    <legend class='legend'>List</legend>
    <table width="100%" cellspacing="1" bordercolor='#ABABAB' cellpadding="3" border="1" bgcolor="#000000">
        <tbody>
        <tr bgcolor="#EEEEEE">
            <th height='30' align="center">Title</th>
            <th height='30' align="center">Content</th>
            <th height='30' align="center">Sort</th>
            <th align="center">MODIFY</th>
            <th align="center">DEL</th>
        </tr>
        <?
        if($result){
            foreach($result as $v){
                ?>
                <tr class="td_" valign="top" onmouseout="this.className='td_';" onmouseover="this.className='td_highlight';">
                    <td align="left"><?=$v['title']?></td>
                    <td align="left"><?=$v['content']?></td>
                    <td align="left"><?=$v['sort']?></td>
                    <td align="center"><a href="?act=com-company_webpage_setting&modid=<?=$v['id']?>">MODIFY</a></td>
                    <td align="center"><a href="?act=com-company_webpage_setting&delid=<?=$v['id']?>">DEL</a></td>
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