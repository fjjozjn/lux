<?php
require 'class.phpmailer.php';
require 'class.pop3.php';

//20160706
/*$mail_info = array(
	'CharSet' => 'utf-8',
	'Username' => 'system@luxdesign.hk',
	'Password' => 'a93112244B',
	'Host' => 'mailcn.luxdesign.hk',
	'From' => 'system@luxdesign.hk',
	'FromName' => 'Kevin Chan',
	'ReplyTo' => 'system@luxdesign.hk',
	'ReplyToName' => 'Kevin Chan'
	);*/

//20151123
//$mail_info = array(
//	'CharSet' => 'utf-8',
//	'Username' => 'system@luxdesign.hk',
//	'Password' => 'a93112244B',
//	'Host' => 'mailcn.luxdesign.hk',
//	'From' => 'kevin@luxdesign.hk',
//	'FromName' => 'Kevin Chan',
//	'ReplyTo' => 'kevin@luxdesign.hk',
//	'ReplyToName' => 'Kevin Chan'
//	);

//20150121 测试代发的情况
/*$mail_info = array(
    'CharSet' => 'utf-8',
    'Username' => 'system@luxdesign.hk',
    'Password' => 'a93112244B',
    'Host' => 'mailcn.luxdesign.hk',
    'From' => 'service@10086.com',
    'FromName' => 'Service',
    'ReplyTo' => 'service@10086.com',
    'ReplyToName' => 'Service'
);*/

function send_mail($to, $toname, $subject, $body, $msg, $attachmentFile = '', $attachmentName = '', $mail_info = '')
{

    //20160911
    //global $mail_info;
    if (!$mail_info) {
        $mail_info = array(
            'CharSet' => 'utf-8',
            'Username' => 'system@luxdesign.hk',
            'Password' => 'a93112244B#',
            'Host' => 'mail.luxdesign.hk',
            'From' => 'system@luxdesign.hk',
            'FromName' => 'Lux ERP',
            'ReplyTo' => 'system@luxdesign.hk',
            'ReplyToName' => 'Lux ERP'
        );
    }

    if (!check_email($to)) {
        return '收件地址不是合法的邮箱地址';
    }
    $mail = new PHPMailer();

    $mail->IsSMTP();
    $mail->IsHTML(true);
    $mail->Username = $mail_info['Username'];
    $mail->Password = $mail_info['Password'];
    $mail->SMTPAuth = true;
    $mail->Port = 1688;
    $mail->SMTPSecure = "ssl";
    $mail->CharSet = $mail_info['CharSet'];
    $mail->Priority = 1;
    $mail->Timeout = 30;

    $mail->Host = $mail_info['Host'];
    $mail->From = $mail_info['From'];
    $mail->FromName = $mail_info['FromName'];
    $mail->SMTPDebug = 1;

    $mail->Subject = $subject;
    //old version body.
    //$mail->Body     =  $message;

    //new version body.
    //mod 20130208
    //$body = $mail->getFile($body);
    //$body = eregi_replace("[\]",'',$body);
    if (is_array($msg)) {
        foreach ($msg as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }
    }
    //$mail->AltBody = "To view the message, please use an HTML compatible email viewer!";
    $mail->MsgHTML($body);
    //new version body end.

    //发送附件
    if (is_array($attachmentFile)) {
        for ($i = 0; $i < count($attachmentFile); $i++) {
            $mail->AddAttachment($attachmentFile[$i], $attachmentName[$i]);
        }
    } else {
        $mail->AddAttachment($attachmentFile, $attachmentName);
    }

    $mail->AddReplyTo($mail_info['ReplyTo'], $mail_info['ReplyToName']);
    $mail->AddAddress($to, $toname);

    if (!$mail->Send()) {
        return $mail->ErrorInfo;
    } else {
        return '';
    }
}

function check_email($mailaddr)
{
    if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$", $mailaddr)) return false;
    return true;
}

?>