<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//check permission 
//checkAdminPermission(PERM_MAINTAIN_ONOFF);
//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}


$rtn = $mysql->qone('select mode from mode');

$form = new My_Forms();
$formItems = array(
    'mode' => array(
        'type' => 'radio',
        'value' => isset($rtn['mode'])?$rtn['mode']:'',
        'options' => array(array('Normal', '1'), array('Exhibition', '2')),
        'required' => GENERAL_YES,
    ),
    'submitbtn'	=> array(
        'type' => 'submit', 'value' => ' Submit '
    ),
);
$form->init($formItems);

if(!$myerror->getAny() && $form->check()){
    //20130605 mode表只有一条记录，要给mode字段设置一个默认值，如0，否则update不了
    $rtn = $mysql->q('update mode set mode = ?',$_POST['mode']);
    if ($rtn !== false){
        $myerror->ok('Update mode success !', 'com-mode');
    }else{
        $myerror->warn('Update mode failure !', 'main');
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
    $form->begin();
    ?>

    <table width="50%" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td class='headertitle' align="center">Mode</td>
        </tr>
        <tr>
            <td align="center">
                <fieldset>
                    <legend class='legend'>information</legend>
                    <div style="color:#F00; margin-left:30px;" align="left"># Normal mode 不显示 Exhibition</div>
                    <div style="color:#F00; margin-left:30px;" align="left"># Exhibition mode 只显示 Exhibition、Sales、Contact、Setting</div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                        <tr>
                            <td width="20%" height="100px" align="right">Mode : </td>
                            <td width="10%">&nbsp;</td>
                            <td width="40%" align='left'><? $form->show('mode');?></td>
                        </tr>
                        <tr valign='top'>
                            <td>&nbsp;</td>
                            <td colspan='2' height="35">
                                <?
                                $form->show('submitbtn');
                                ?></td>
                        </tr>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>
    <?
    $form->end();
}

