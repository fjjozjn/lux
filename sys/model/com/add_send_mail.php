<?php
include('../../../in7/global.php');
require_once(ROOT_DIR.'class/Mail/mail.php');

print_r($mail_info);
echo '<br />';

$account_info = array('date' => date('Y-m-d'));
//发送的信息
$created_by = 'test';
$py_no = 'PY00000000';
$info = $created_by . ' 添加了 ' . $py_no . ' ，请去看看。';

send_mail('232289219@qq.com', 'ZJN', 'Payment Advice', $info, $account_info);