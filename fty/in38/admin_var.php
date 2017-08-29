<?
if(!defined('BEEN_INCLUDE'))exit('Welcome to The Matrix');

if(isset($_SESSION['ftylogininfo']['aName'])){
    $pic_path_fty       = "upload/fty/".$_SESSION['ftylogininfo']['aFtyName'].'/';           //工廠圖片目錄
    $pic_win_path_fty   = "upload\\fty\\".$_SESSION['ftylogininfo']['aFtyName']."\\";
    //20130730
    $pic_linux_path_fty = "upload/fty/".$_SESSION['ftylogininfo']['aFtyName']."/";
    $pic_full_path_fty  = "/fty/upload/fty/".$_SESSION['ftylogininfo']['aFtyName'].'/';
    //20130730 判断系统类型
    $os = (DIRECTORY_SEPARATOR=='\\')?"windows":'linux';
}

//20160121
//if(strpos($act, 'delivery') !== false || strpos($act, 'qc_schedule') !== false || strpos($act,
//    'searchpurchase') !== false || strpos($act, '_material_') !== false || strpos($act, '_qc_report') !== false){
//    //获取当前工厂用户的purchase
//    $i_purchase = array();
//    if(!isFtyAdmin()){
//
//        file_put_contents('../log/fty_pcid_empty.txt', dateMore().' : user '.$_SESSION['ftylogininfo']['aFtyName'].' '.$act, FILE_APPEND);
//
//        //$rs = $mysql->q('select pcid from purchase where sid = ? and istatus = ? order by pcid desc', $_SESSION['ftylogininfo']['aFtyName'], '(I)');
//        $rs = $mysql->q('select pcid from purchase where sid = ? and istatus = ? order by pcid', $_SESSION['ftylogininfo']['aFtyName'], '(I)');
//    }else{
//
//        file_put_contents('../log/fty_pcid_empty.txt', dateMore().' : admin '.$_SESSION['ftylogininfo']['aFtyName'].' '.$act, FILE_APPEND);
//
//        $rs = $mysql->q('select pcid from purchase where istatus = ? order by pcid', '(I)');
//    }
//    if($rs){
//
//        $rtn = $mysql->fetch();
//
//        file_put_contents('../log/fty_pcid_empty.txt', ' '.count($rtn)."\r\n", FILE_APPEND);
//
//        foreach($rtn as $v){
//            $i_purchase[$v['pcid']] = array($v['pcid'], $v['pcid']);
//        }
//    }else{
//
//        file_put_contents('../log/fty_pcid_empty.txt', ' none'."\r\n", FILE_APPEND);
//
//    }
//}


/*$type = array(
    array('项链(N)', '项链(N)'),
    array('耳环(E)', '耳环(E)'),
    array('戒子(R)', '戒子(R)'),
    array('手链(T)', '手链(T)'),
    array('手镯(H)', '手镯(H)'),
    array('心针(B)', '心针(B)'),
    array('套装(S)', '套装(S)'),
    array('其他(O)', '其他(O)')
);*/
//20130730 亮 显示中文，方便工厂填写
/*$type_e = array(
    array('项链(N)', 'Necklace'),
    array('耳环(E)', 'Earrings'),
    array('戒子(R)', 'Ring'),
    array('手链(T)', 'Bracelet'),
    array('手镯(H)', 'Bangle'),
    array('心针(B)', 'Brooch'),
    array('套装(S)', 'Set'),
    array('其他(O)', 'Other')
);*/
/*$material = array(
    array('C料', 'C料'),
    array('A料', 'A料'),
    array('O号料', 'O号料'),
    array('锌合金', '锌合金'),
    array('铜', '铜'),
    array('银', '银'),
    array('其他', '其他')
);*/

/*$process = array(
    array('拉粗沙', '拉粗沙'),
    array('拉幼沙', '拉幼沙'),
    array('拉沙', '拉沙'),
    array('油沙', '油沙'),
    array('闪沙', '闪沙')
);
$count1 = count($process);

$electroplate = array(
    array('氧化银', '氧化银'),
    array('氧化青铜', '氧化青铜'),
    array('电白金', '电白金'),
    array('电白钢', '电白钢'),
    array('枪色', '枪色'),
    array('电金', '电金'),
    array('电银', '电银'),
    array('电白K', '电白K')
);
$count2 = count($electroplate);

$electroplate_thick = array(
    array('普通电镀', '普通电镀'),
    array('0.25咪', '0.25咪'),
    array('0.5咪', '0.5咪'),
    array('1咪', '1咪'),
    array('2咪', '2咪'),
    array('5咪', '5咪'),
    array('10咪', '10咪')
);
$count3 = count($electroplate_thick);

$other = array(
    array('保层', '保层'),
    array('镭射', '镭射'),
    array('滴油', '滴油'),
    array('喷亚克', '喷亚克')
);
$count4 = count($other);*/

$m_type = array(
    array('工件', '工件'),
    array('石料', '石料'),
    array('配件', '配件'),
    array('其他', '其他')
);

$m_unit = array(
    array('数量', 1),
    array('重量', 2)
);

//$quality_test_result = array(
//    array('合格', 1),
//    array('退货', 0),
//);

define('ADMIN_CATG_FTY_LOGIN', 'fty_login');
define('ADMIN_CATG_FTY_LOGOUT', 'fty_logout');
define('ADMIN_ACTION_FTY_LOGIN_SUCCESS', '1');
define('ADMIN_ACTION_FTY_LOGIN_FAILURE', '-1');
define('ADMIN_ACTION_FTY_LOGOUT_SUCCESS', '1');
//登出时只unset session ，但是unset没有返回值，无法判断是否失败，只能认为都是成功的了
//define('ADMIN_ACTION_FTY_LOGOUT_FAILURE', '-1');

/*$allinarray = array($process, $electroplate, $electroplate_thick, $other);

$count_array = array($count1, $count2, $count3, $count4);
$select_max = 0;
for($i = 0; $i < 4; $i++){
    if($select_max < $count_array[$i])
        $select_max = $count_array[$i];
}*/

//if( strpos($act, 'form') !== false ){
//    $t_type = array();
//    if(isFtyAdmin()){
//        $rs = $mysql->q('select t_id, t_name from fty_task');
//    }else{
//        //$rs = $mysql->q('select t_id, t_name from fty_task where created_by in (select AdminName from tw_admin where FtyName = (select FtyName from tw_admin where AdminName = ?))', $_SESSION['ftylogininfo']['aName']);
//        //20141202 上面的不知道怎么不行，没解决，暂时用下面的
//        //$rs = $mysql->q('select t_id, t_name from fty_task where created_by = ?', $_SESSION['ftylogininfo']['aName']);
//        //20150102 用这个才行，不知道最上面的为什么不行
//        $rs = $mysql->q('SELECT f.t_id, f.t_name FROM fty_task f, tw_admin a WHERE f.created_by = a.AdminName AND a.FtyName = (SELECT FtyName FROM tw_admin WHERE AdminName = ?)', $_SESSION['ftylogininfo']['aName']);
//
//    }
//    if($rs){
//        $rtn = $mysql->fetch();
//        foreach($rtn as $v){
//            $t_type[] = array($v['t_id'].' : '.$v['t_name'], $v['t_id']);
//        }
//    }
//}

//if( strpos($act, 'delivery') !== false ){
//    $client_company = array();
//    if(isFtyAdmin()){
//        $rs = $mysql->q('select company from fty_client');
//    }else{
//        //默认都加一个本公司到工厂的客户名单去
//        $rs = $mysql->q('select company from fty_client where created_by = ? or created_by = ?', $_SESSION['ftylogininfo']['aName'], 'all');
//    }
//    if($rs){
//        $rtn = $mysql->fetch();
//        foreach($rtn as $v){
//            $client_company[] = array($v['company'], $v['company']);
//        }
//    }
//}