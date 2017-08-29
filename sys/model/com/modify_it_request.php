<?php

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');
//检查访问者IP，以确定本测试页可以显示
//ipRestrict();

if(isset($_GET['delid']) && $_GET['delid'] != ''){
    $rtn2 = $mysql->q('delete from it_request where id = ?', $_GET['delid']);
    if($rtn2){
        $myerror->ok('删除 IT Service Request 成功!', 'com-search_it_request&page=1');
    }else{
        $myerror->error('删除 IT Service Request 失败!', 'com-search_it_request&page=1');
    }
}else{
    if(isset($_GET['modid']) && $_GET['modid'] != ''){
        $mod_result = $mysql->qone('SELECT * FROM it_request WHERE id = ?', $_GET['modid']);
    }else{
        die('Need modid!');
    }

    $goodsForm = new My_Forms();
    $formItems = array(
        'content' => array('title' => 'Content', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'required' => 1, 'value' => isset($mod_result['content'])?$mod_result['content']:''),
        'location' => array('title' => 'Location', 'type' => 'select', 'options' => get_it_request_location(), 'required' => 1, 'value' => isset($mod_result['location'])?$mod_result['location']:''),
        'expected_completion_date' => array('title' => 'Expected Completion Date', 'type' => 'text', 'restrict' => 'date', 'required' => 1, 'value' => isset($mod_result['expected_completion_date'])?$mod_result['expected_completion_date']:''),
        'it_dept_respond' => array('title' => 'IT Dept Respond', 'type' => 'textarea', 'minlen' => 1, 'maxlen' => 1000, 'rows' => 5, 'value' => isset($mod_result['it_dept_respond'])?$mod_result['it_dept_respond']:''),
        'completion_date' => array('title' => 'Completion Date', 'type' => 'text', 'restrict' => 'date', 'value' => isset($mod_result['completion_date'])?$mod_result['completion_date']:''),
        'satisfaction_rate' => array('title' => 'Satisfaction Rate', 'type' => 'radio', 'options' => array(array(1, 1),array(2, 2),array(3, 3),array(4, 4),array(5, 5)), 'value' => isset($mod_result['satisfaction_rate'])?$mod_result['satisfaction_rate']:''),

        'submitbtn'	=> array('type' => 'submit', 'value' => ' Save '),
    );

    $goodsForm->init($formItems);

    if(!$myerror->getAny() && $goodsForm->check()){

        $expected_completion_date = $_POST['expected_completion_date'];
        $location = trim($_POST['location']);
        $content = trim($_POST['content']);
        $it_dept_respond = trim($_POST['it_dept_respond']);
        $completion_date = $_POST['completion_date'];
        $satisfaction_rate = isset($_POST['satisfaction_rate'])?$_POST['satisfaction_rate']:'';
        $mod_by = $_SESSION["logininfo"]["aName"];
        $mod_date = dateMore();

        $result = $mysql->q('update it_request set expected_completion_date = ?, location = ?, content = ?, it_dept_respond = ?, completion_date = ?,satisfaction_rate = ?, mod_by = ?, mod_date = ?', $expected_completion_date, $location, $content, $it_dept_respond, $completion_date, $satisfaction_rate, $mod_by, $mod_date);
        //這裡是因為result為0的時候就是update數據和原來一樣，所以判斷時用false
        if($result !== false){
            $myerror->ok('修改 IT Service Request 成功!', 'com-search_it_request&page=1');
        }else{
            $myerror->error('修改 IT Service Request 失败', 'BACK');
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
    <h1 class="green">IT Service Request<em>* item must be filled in</em></h1>
    <table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>Modify IT Service Request</legend>
                    <?php
                    $goodsForm->begin();
                    ?>
                    <table width="100%" id="table">
                        <tr class="formtitle">
                            <td width="50%"><div class="set"><label class="formtitle">Date</label><br /><?=$mod_result['in_date'];?></div></td>
                            <td width="50%"><div class="set"><label class="formtitle">User</label><br /><?=$mod_result['created_by'];?></div></td>
                        </tr>
                        <tr>
                            <td width="50%"><? $goodsForm->show('expected_completion_date');?></td>
                            <td width="50%"><? $goodsForm->show('location');?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><? $goodsForm->show('content');?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><? $goodsForm->show('it_dept_respond');?></td>
                        </tr>
                        <tr>
                            <td width="50%"><? $goodsForm->show('completion_date');?></td>
                            <td width="50%">&nbsp;</td>
                        </tr>
                        <tr id="satisfaction_rate">
                            <td colspan="2"><? $goodsForm->show('satisfaction_rate');?></td>
                        </tr>
                    </table>
                    <div class="line"></div>
                    <?
                    $goodsForm->show('submitbtn');
                    ?>
                </fieldset>
            </td>
        </tr>
    </table>
    <?
    $goodsForm->end();
}
?>

<style>
    tr#satisfaction_rate div.set{
        width:600px;
    }
</style>