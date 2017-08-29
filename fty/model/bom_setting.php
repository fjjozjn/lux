<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//check permission 
//checkAdminPermission(PERM_MAINTAIN_ONOFF);

//$isMt = $isMt ? 1 : 0;

//禁止其他用户进入（临时做法）
/*if(!isFtyAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}*/


//mod 20120720 这个页面现在能自动显示最后一个编号，即使数据库的setting表里面没有记录，这样就能让用户知道下一个编号是什么，提交后，能保存到setting表

if(!isset($_POST['bom_id'])){
    $rtn = $mysql->qone('select * from fty_bom_setting');
}

if(!$myerror->getAny()){

    $form = new My_Forms(/*array('noFocus' => true)*/);
    // print_r_pre($tools_ip_setting);
    $formItems = array(

        'bom_id' => array(
            'type' => 'text',
            'minlen' => 1,
            'maxlen' => 6,
            'required' => 1,
            'restrict' => 'number',
            'info' => '为当前流水号的下一个ID',
            'value' => isset($rtn['bom_id'])?$rtn['bom_id']:''
        ),

        'submitbtn'	=> array(
            'type' => 'submit', 'value' => ' Submit '),
    );


    $form->init($formItems);
    if(!$myerror->getAny() && $form->check()){

        $rtn = $mysql->q('update fty_bom_setting set bom_id = ?', $_POST['bom_id']);

        if ($rtn){
            $myerror->ok('设定BOM参数成功', 'bom_setting');
        }else{
            $myerror->error('系统错误，设定BOM参数失败', 'bom_setting');
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

        <table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td class='headertitle' align="center">Setting</td>
            </tr>
            <tr>
                <td align="center">
                    <fieldset>
                        <legend class='legend'>information</legend>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">

                            <tr valign='top'>
                                <td align='right' width="200px" height="35" valign="top">BOM ID : </td>
                                <td align='left' valign="middle"><? $form->show('bom_id');?></td>
                            </tr>


                            <tr valign='top'>
                                <td colspan='2' height="35" style="padding-left: 100px;"><?$form->show('submitbtn');
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
}else{
    require_once(ROOT_DIR.'model/inside_warn.php');
}
?>