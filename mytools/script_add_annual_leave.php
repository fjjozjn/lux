<?
//要写完整的目录，否则linux命令执行此文件会找不到路径
//20131225 现在服务器在windows，所以又用相对路径了
//20131226 发现要用php.exe执行文件，还是不能用相对路径
require(substr(__DIR__, 0, -7).'in7/global.php');
//require('/root/Dropbox/luxerp/in7/global.php');

//20131225 加url传参数，且程序内的时间设定为变量，可自由改变，而不是写死为当天日期
$datetime = (isset($_GET['time']) && $_GET['time'] != '')?$_GET['time']:dateMore();

$rs_valid_user = $mysql->q('select AdminID, AdminName, AdminJoinDate from tw_admin where AdminEnabled = 1');
if($rs_valid_user){
    $rtn_valid_user = $mysql->fetch();

    $time_str = strtotime($datetime);
    $today_day = date('d', $time_str);
    $today = date('Y-m-d', $time_str);
    $this_month_first_day = date('Y-m-01', $time_str);
    $this_month_last_day = date('Y-m-t', $time_str);
    $next_month_first_day = date('Y-m-01', strtotime($this_month_first_day.' +1 month'));//！！如果是01-31 +1 month的话，就会是到3月了，所以要用每月的第一天来做计算

    foreach($rtn_valid_user as $v){

        //为防止手动执行这个脚本重复加一次，只要在hr_log里今天存在此用户的 type 是 ADD ANNUAL LEAVE HOURS 的一条记录，则不再加了
        //20131225 规则变动，为了防止某天脚本没有运行，第二天又运行了这种情况。现在是检测用户在给定日期当月里面有没有加年假的记录，没有则进入到里面的流程
        $rs = $mysql->q('select id from hr_log where created_by = ? and in_date > ? and in_date < ? and type = ?', $v['AdminName'], $this_month_first_day, $next_month_first_day, 'SYSTEM ADD ANNUAL LEAVE HOURS (AUTO)');
        if(!$rs){
            $user_join_day = date('d', strtotime($v['AdminJoinDate']));

            //因为不是每个月都有29、30、31号，所以计算出每月最后一天，如果当天是本月最后一天，而用户的创建日期在本月最后一天之后且在下月1号之前，则在本月最后一天加上小时数
            //除去入职当天和入职日期不能大于当前日期
            if($v['AdminJoinDate'] < $today){
                if($user_join_day <= $today_day){
                    add_annual_leave_hours($v['AdminID'], $v['AdminName'], $v['AdminJoinDate']);
                }else{
                    if($today == $this_month_last_day){
                        if($v['AdminJoinDate'] >= $this_month_last_day && $v['AdminJoinDate'] <= $next_month_first_day){
                            add_annual_leave_hours($v['AdminID'], $v['AdminName'], $v['AdminJoinDate']);
                        }
                    }
                }
            }
        }
    }
}

function add_annual_leave_hours($AdminID, $AdminName, $AdminJoinDate){

    $user_join_year = date('Y', strtotime($AdminJoinDate));
    $today_year = date('Y');
    $worked_years = $today_year - $user_join_year;

    //setting
    $hr_setting = mysql_qone('select al_start_days, al_end_days, al_increase_days from setting');

    $base_hours = 0;
    if($hr_setting['al_start_days'] + intval($worked_years) > $hr_setting['al_end_days']){
        $base_hours = $hr_setting['al_end_days'] * 8;
    }else{
        $base_hours = ($hr_setting['al_start_days'] + intval($worked_years)) * 8;
    }
    $monthly_increase_hours = round(($base_hours / 12), 2);

    mysql_q('update tw_admin set AdminTotalHours = AdminTotalHours + ? where AdminID = ?', $monthly_increase_hours, $AdminID);
    mysql_q('insert into hr_log values(NULL, '.moreQm(9).')', 'SYSTEM ADD ANNUAL LEAVE HOURS (AUTO)', $monthly_increase_hours, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', $AdminName, dateMore(), 1, '');

}