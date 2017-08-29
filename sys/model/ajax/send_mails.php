<?php
//send email to admin
require_once(ROOT_DIR.'class/Mail/mail.php');

//重新生成pdf作为发邮件的附件
require_once(ROOT_DIR.'sys/model/com/pdf_generate_style_list_file.php');


//附件路径
$attachmentFiles = array('../pdf/generate_style_list/Lux_Monthly_Style.pdf');
//附件名
$attachmentNames = array('Lux_Monthly_Style.pdf');
//poster
$rs_poster = $mysql->q('select * from poster where poster_date like ?', date('Y-m').'%');
$img_str = '';
if($rs_poster){
    $rtn_poster = $mysql->fetch();
    if($rtn_poster){
        foreach ($rtn_poster as $rtn_poster_item) {
            //$attachmentFiles[] = $pic_path_com_poster.$rtn_poster_item['photo'];
            //$attachmentNames[] = $rtn_poster_item['photo'];
            //暂时不放在附件里，直接加在邮件正文最上面
            $img_str .= "<img src='http://".$host."/sys/".$pic_path_com_poster.$rtn_poster_item['photo']."' /><br />";
        }
    }
}

$rtn_content = $mysql->qone('select generate_email_subject, generate_email_content from setting');
if(isset($_GET['ajax']) && $_GET['ajax'] == 'sendMails'){
    $rs = $mysql->q('select name, email, cid from contact where send_style_list = 1 and email <> ?', '');
    if ($rs) {
        $rtn = $mysql->fetch();
        if (!empty($rtn)) {
            $notice = '';
            foreach ($rtn as $v) {
                //20160911
                $mail_info = '';
                if ($v['cid']) {
                    $send_mail_from_rtn = $mysql->qone('select tw_admin.AdminEmail, tw_admin.AdminEmailRealName from tw_admin, customer where tw_admin.AdminName = customer.created_by and customer.cid = ?', $v['cid']);
                    $ReplyTo = isTrue($send_mail_from_rtn['AdminEmail']) ? $send_mail_from_rtn['AdminEmail'] : 'system@luxdesign.hk';
                    $ReplyToName = isTrue($send_mail_from_rtn['AdminEmailRealName']) ? $send_mail_from_rtn['AdminEmailRealName'] : 'LUX';
                    $mail_info = array(
                        'CharSet' => 'utf-8',
                        'Username' => 'system@luxdesign.hk',
                        'Password' => 'a93112244B',
                        'Host' => 'mailcn.luxdesign.hk',
                        'From' => 'system@luxdesign.hk',
                        'FromName' => 'Kevin Chan',
                        'ReplyTo' => $ReplyTo,
                        'ReplyToName' => $ReplyToName
                    );
                }
                //附件地址不能用远程地址
                $rs = send_mail($v['email'], '', $rtn_content['generate_email_subject'], $img_str.'Dear ' . $v['name'] . ',<br /><br />' .str_replace(array("\r\n", "{{name}}"), array("<br />", $ReplyToName), $rtn_content['generate_email_content']),
                    array('date' => date('Y-m-d')), $attachmentFiles, $attachmentNames, $mail_info);

                $notice .= $v['name'] . ":" . $v['email'] . ", ";
            }
            echo $notice ? trim($notice, ', ') : '!no-2';
        } else {
            echo '!no-1';
        }
    } else {
        echo '!no-0';
    }
}elseif(isset($_GET['ajax']) && $_GET['ajax'] == 'sendMailsTo'){
    //$rtn_admin = $mysql->qone('select AdminName, AdminEmail from tw_admin where AdminName = ?', 'KEVIN');
    //20161210 改成发给指定的人
    $temp = explode('@', $_GET['email']);
    $rtn_admin['AdminName'] = $temp[0];
    $rtn_admin['AdminEmail'] = $_GET['email'];
    if($rtn_admin) {
        //附件地址不能用远程地址
        $rs = send_mail($rtn_admin['AdminEmail'], '', $rtn_content['generate_email_subject'], $img_str.'Dear '.$rtn_admin['AdminName'].',<br /><br />' . str_replace(array("\r\n", "{{name}}"), array("<br />", 'Kevin Chan'), $rtn_content['generate_email_content']),
            array('date' => date('Y-m-d')), $attachmentFiles, $attachmentNames);

        $notice = $rtn_admin['AdminName'] . ":" . $rtn_admin['AdminEmail'] . ", ";
        echo $notice;
    }else{
        echo '!no-0';
    }
}