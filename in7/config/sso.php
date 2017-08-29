<?php

//--------------------------sso 配置----------------------------

$ssoCookieName		= 'psi';			//為SSO所使用的cookie名
$ssoCookieDomain	= '.'. $domain;		//為SSO所使用的cookie域
$ssoCookieExpire	= 6;				//cookie在服務器的有效期，單位：小時
										//客戶端cookie將隨瀏覽器的關閉而失效
										//主要是為以防萬一而設
$ssoWsEncKey		= 'Gh8#$82Bo09)3_gnfz#$^#@naefd#sf*(i)na8*Yyt';
$ssoTimeout			= 1440;				//檢查member_session表時，lastUpdate過去多少秒即視為超時
										//此值應該與 php.ini 中的 session.gc_maxlifetime 保持一致

?>