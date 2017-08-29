<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);
if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//check permission 
//checkAdminPermission(PERM_MAINTAIN_ONOFF);

//$isMt = $isMt ? 1 : 0;

//禁止其他用户进入（临时做法）
//if(!isSysAdmin()){
//    $myerror->error('Without Permission To Access', 'main');
//}


if( !isset($_POST['generate_email_content']) ){
    $rtn = $mysql->qone('select generate_email_subject, generate_email_content from setting');

}

if(!$myerror->getAny()){

    $form = new My_Forms(/*array('noFocus' => true)*/);
    $formItems = array(
        'generate_email_subject' => array(
            'type' => 'text',
            'minlen' => 1,
            'maxlen' => 500,
            'value' => isset($rtn['generate_email_subject'])?$rtn['generate_email_subject']:'',
            'addon' => 'style="width:400px"',
        ),
        'generate_email_content' => array(
            'type' => 'textarea',
            'minlen' => 1,
            'maxlen' => 1000,
            'required' => 1,
            'value' => isset($rtn['generate_email_content'])?$rtn['generate_email_content']:'',
            'addon' => 'style="width:400px"',
            'rows' => 10,
        ),
        'submitbtn'	=> array(
            'type' => 'submit',
            'value' => ' Submit '
        ),
        'sendMailsbtn'	=> array(
            'type' => 'button',
            'addon' => 'onclick="javascript:confirm(\'确定要发送邮件给所有勾选的客户吗？\')?sendMails():\'\'"',
            'value' => ' Send E-Mail to Customer '
        ),
        'sendMailsTobtn'	=> array(
            'type' => 'button',
            'addon' => 'onclick="sendMailsTo()"',
            'value' => ' Send E-Mail to : '
        ),
        'sendToEmail' => array(
            'type' => 'text',
            'minlen' => 1,
            'maxlen' => 100,
            'addon' => 'style="width:300px"'
        ),
    );

    $form->init($formItems);

    if(!$myerror->getAny() && $form->check()){

        $rs = $mysql->q('update setting set generate_email_subject = ?, generate_email_content = ?', $_POST['generate_email_subject'], $_POST['generate_email_content']);

        if($rs){
            $myerror->ok('Modify Email Content Success', 'com-generate_email_content');
        }else{
            $myerror->warn('Modify Email Content Failure', 'main');
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
                <td class='headertitle' align="center">Email Content</td>
            </tr>
            <tr>
                <td align="center">
                    <fieldset>
                        <legend class='legend'>information</legend>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">

                            <tr valign='top'>
                                <td width="20%" height="35" align="right">Email Subject : </td>
                                <td align='left'><? $form->show('generate_email_subject');?></td>
                            </tr>

                            <tr valign='top'>
                                <td width="20%" height="35" align="right">Email Content : </td>
                                <td align='left'><? $form->show('generate_email_content');?></td>
                            </tr>

                            <tr valign='top'>
                                <td colspan='2' height="35" style="padding-left: 15px;">
                                    <? $form->show('submitbtn'); ?>
                                </td>
                            </tr>

                            <tr valign="center">
                                <td colspan="2" height="35" style="padding-left: 15px;">
                                    <? $form->show('sendMailsbtn'); ?>&nbsp;&nbsp;&nbsp;&nbsp;<div class="buttonfield"><input type="button" onclick="javascript:window.open('/sys/model/com/pdf_generate_style_list.php');" value=" Great Generate Style List " class="defautButton"></div>
                                </td>
                            </tr>
                            <tr valign="center">
                                <td height="35" style="padding-left: 15px;">
                                    <? $form->show('sendMailsTobtn'); ?>
                                </td>
                                <td align="left" style="padding-left: 5px;">
                                    <? $form->show('sendToEmail'); ?>
                                </td>
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
<script>
function sendMails(){
    $('#sendMailsbtn').attr('disabled','disabled');
    var qs = 'ajax=sendMails&act=ajax-send_mails';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: qs,
        cache: false,
        dataType: "html",
        error: function(){
            $('#sendMailsbtn').removeAttr('disabled');
            alert('system error!');
        },
        success: function(data){
            if(data.indexOf('!no-') < 0){
                alert('邮件发送成功，已发送的客户有（'+data+'）');
            }else{
                if(data.indexOf('!no-0') >= 0){
                    alert('查询失败');
                }else if(data.indexOf('!no-1') >= 0){
                    alert('没有需要发邮件的客户');
                }else if(data.indexOf('!no-2') >= 0){
                    alert('邮件发送全部失败');
                }else{
                    alert('出错了');
                }
            }
            $('#sendMailsbtn').removeAttr('disabled');
        }
    })
}

function sendMailsTo(){
    var sendToEmail = $('#sendToEmail').val();
    if(sendToEmail){
        if(confirm('确定要发送邮件给'+sendToEmail+'吗？')){
            $('#sendMailsTobtn').attr('disabled','disabled');
            var qs = 'ajax=sendMailsTo&act=ajax-send_mails&email='+sendToEmail;
            $.ajax({
                type: "GET",
                url: "index.php",
                data: qs,
                cache: false,
                dataType: "html",
                error: function(){
                    $('#sendMailsTobtn').removeAttr('disabled');
                    alert('system error!');
                },
                success: function(data){
                    if(data.indexOf('!no-') < 0){
                        alert('邮件发送成功，已发送的客户有（'+data+'）');
                    }else{
                        if(data.indexOf('!no-0') >= 0){
                            alert('查询失败');
                        }else if(data.indexOf('!no-1') >= 0){
                            alert('没有需要发邮件的客户');
                        }else if(data.indexOf('!no-2') >= 0){
                            alert('邮件发送全部失败');
                        }else{
                            alert('出错了');
                        }
                    }
                    $('#sendMailsTobtn').removeAttr('disabled');
                }
            })
        }
    }else{
        alert('请在后面输入email');
    }
}
</script>