<?

require_once('../in7/global.php');
require_once('../class/Mail/mail.php');

$account_info = array(
	'date' => date('Y-m-d'),
	);

$mailmsg = send_mail('232289219@qq.com', 'zjn', 'for test', 'mailcontents.html', $account_info);
echo $mailmsg;
