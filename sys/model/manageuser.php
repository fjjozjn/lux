<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

if(!defined('BEEN_INCLUDE') || !is_object($myerror))exit('Welcome to The Matrix');

//check permission 
//die();
//checkAdminPermission(PERM_MANAGE_ADMIN);

//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}

$adminPlatform = array(array('sys', 'sys'), array('fty', 'fty'), array('luxcraft', 'luxcraft'));

if(!$myerror->getWarn()){

    //get admin group
    /*
   $mysql->sp('CALL backend_detail(?, ?, ?)', '1', 'tw_admingrp', '1');
   $temp_grp = $mysql->fetch(0,1);
   for($i = 0 ; $i < count($temp_grp); $i++){
       $temp = array($temp_grp[$i]['AdminGrpName'],$temp_grp[$i]['AdminGrpID']);
       $row_grp[] = $temp;
   }
   */


    $row = array();
    $adminperm_str = array();
    if (strlen(@$_GET['delid']) && isId(@$_GET['delid'])){
        $rtn = $mysql->q('delete from tw_admin where AdminID = ?', $_GET['delid']);
        if($rtn){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_SYS_USER, $_SESSION["logininfo"]["aName"]." <i>delete sys user</i> ID:'".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_SYS_USER_S, "", "", 0);

            $myerror->ok('删除 user 成功!', 'searchuser');
        }else{
            $myerror->error('删除 user 失败!', 'searchuser');
        }
    }else{
        //20130922
        $hr_setting = $mysql->qone('select al_start_days, al_end_days, al_increase_days from setting');

        if (strlen(@$_GET['id']) && isId(@$_GET['id'])){
            //modfiy old record, need to get admin details
            $mysql->sp('CALL backend_detail(?, ?, ?)', @$_GET['id'], 'tw_admin', 'AdminID');
            $row = $mysql->fetch(1);
            $adminperm_str = explode(',',$row['AdminPerm']);
            //20140225 加admin type
            $admin_type = explode('|', $row['AdminPlatform']);
            $fty_grp_id = explode(',', $row['FtyGrpID']);
        }


        // print_r_pre($row);
        // die();
        // print_r_pre($adminperm_str);
        // $mysql->sp('CALL member_update_session(?)', userSession('GOLogin'));

        $form = new My_Forms();

        $formItems = array(
            /*
                'admin_id' => array(
                    'type' => 'text',
                    'value' => @$row['AdminID'],
                    'readonly' => GENERAL_YES,
                    ),
            */
            'admin_login' => array(
                'type' => 'text',
                'value' => @$row['AdminLogin'],
                'required' => GENERAL_YES,
                'minlen' => 5,
                'maxlen' => 20,
                'readonly' => strlen(@$_GET['id'])?GENERAL_YES:GENERAL_NO,
            ),
            'admin_pw' => array(
                'type' => 'password',
                'value' => '',
                'minlen' => 6,
                'maxlen' => 20,
                'required' => strlen(@$_GET['id'])?GENERAL_NO:GENERAL_YES,	//mod 20130124 改为不用必须输入，如果不输入密码，就不修改密码
                'info' => '修改用户信息时，如果不输入密码，则保持旧密码不变'
            ),
            'admin_repw' => array(
                'type' => 'password',
                'value' => '',
                'minlen' => 6,
                'maxlen' => 20,
                'required' => strlen(@$_GET['id'])?GENERAL_NO:GENERAL_YES,
                'compare' => 'admin_pw'
            ),
            'admin_name' => array(
                'type' => 'text',
                'value' => @$row['AdminName'],
                'required' => GENERAL_YES,
                'minlen' => 0,
                'maxlen' => 100,
                'readonly' => strlen(@$_GET['id'])?GENERAL_YES:GENERAL_NO,
            ),
            'admin_name_chi' => array(
                'type' => 'text',
                'value' => @$row['AdminNameChi'],
                'required' => GENERAL_YES,
                'minlen' => 2,
                'maxlen' => 20,
                'readonly' => strlen(@$_GET['id'])?GENERAL_YES:GENERAL_NO,
            ),
            'admin_email' => array(
                'type' => 'text',
                'value' => @$row['AdminEmail'],
                'required' => GENERAL_YES,
                'restrict' => 'email',
                'minlen' => 0,
                'maxlen' => 100,
            ),
            //20160908
            'admin_email_real_name' => array(
                'type' => 'text',
                'value' => @$row['AdminEmailRealName'],
                'required' => GENERAL_YES,
                'minlen' => 0,
                'maxlen' => 50,
                'info' => '发邮件给客户时，邮件内显示的名字'
            ),
            'admin_annual_leave' => array(
                'type' => 'text',
                'value' => isset($row['AdminAnnualLeave'])?$row['AdminAnnualLeave']:56, //第一年年假是七天，56小时
                'required' => GENERAL_YES,
                'minlen' => 1,
                'maxlen' => 20,
                'readonly' => 'readonly',
                'info' => '入职第一年'.$hr_setting['al_start_days'].'天，每多1年加'.$hr_setting['al_increase_days'].'天，最多'.$hr_setting['al_end_days'].'天'
            ),
            'admin_total_hours' => array(
                'type' => 'text',
                'value' => isset($row['AdminTotalHours'])?$row['AdminTotalHours']:0,//20130922 由56改为0
                'required' => GENERAL_YES,
                'minlen' => 1,
                'maxlen' => 20,
            ),
            'admin_join_date' => array(
                'type' => 'text',
                'restrict' => 'date',
                'required' => GENERAL_NO,
                'value' => isset($row['AdminJoinDate'])?$row['AdminJoinDate']:date('Y-m-d'),
                //'info' => '每年1月1日会自动根据入职时间加上相应的年假小时数'
            ),
            'admin_lux_group' => array(
                'type' => 'select',
                'options' => get_sys_group(),
                'info' => '删除Group中名字，须选中人，再点删除（注：ERP用户才分group）',
            ),
            'admin_lux_group_textarea' => array(
                'type' => 'textarea',
                'readonly' => 'readonly',
                'minlen' => 0,
                'maxlen' => 200,
                'value' => @$row['AdminLuxGroup'],
            ),
            'department' => array(
                'type' => 'select',
                'options' => get_sys_department(),
                'value' => @$row['department'],
            ),
            'job_title' => array(
                'type' => 'text',
                'value' => @$row['job_title'],
                'minlen' => 1,
                'maxlen' => 50,
            ),
            'mobile' => array(
                'type' => 'text',
                'value' => @$row['mobile'],
                'restrict' => 'number',
                'minlen' => 0,
                'maxlen' => 20,
            ),
            'extension' => array(
                'type' => 'text',
                'value' => @$row['extension'],
                'restrict' => 'number',
                'minlen' => 0,
                'maxlen' => 20,
                'info' => '公司座机号及分机号',
            ),
            'qq' => array(
                'type' => 'text',
                'value' => @$row['qq'],
                'minlen' => 0,
                'maxlen' => 20,
            ),
            'admin_type' => array(
                'type' => 'checkbox',
                'options' => $adminPlatform,
                'value' => @$admin_type,
                'required' => GENERAL_YES,
            ),
            'fty_name' => array(
                'type' => 'select',
                'options' => $supplier,
                'value' => @$row['FtyName'],
            ),
            'fty_user_group' => array(
                'type' => 'checkbox',
                'checked' => @$fty_user_group,
                'options' => array(array('Partnership', '1'), array('Vendor', '2')),
                'value' => @$fty_grp_id,
            ),
            'admin_enabled' => array(
                'type' => 'radio',
                'value' => @$row['AdminEnabled'],
                'options' => array(array('Yes', '1'), array('No', '2')),
                'required' => GENERAL_YES,
            ),
            //20140405
            'wh_name' => array(
                'type' => 'select',
                'options' => get_warehouse_info('', 'Shop'),
                'value' => @$row['wh_name'],
            ),
            'mode' => array(
                'type' => 'radio',
                'value' => isset($row['mode'])?$row['mode']:'',
                'options' => array(array('Normal', '1'), array('Exhibition', '2')),
                'required' => GENERAL_YES,
            ),
            /*
            'admin_gameperm' => array(
                'type' => 'checkbox',
                'options' =>$gameperm_arr,
                'checked' => $adminperm_str,
                // 'minlen' => 0,
                // 'maxlen' => 0,
                'class' => 'cb_permission',
                ),
                */
            'submitbtn'	=> array(
                'type' => 'submit', 'value' => ' Submit '),
        );


        $form->init($formItems);
        // print_r_pre($formItems);
        // die();
        if(!$myerror->getAny() && $form->check()){

            $adminperm_str = @implode(',',$_POST['admin_gameperm']);

            if (strlen(@$_GET['id'])){

                //modify or delete exist record
                // print_r_pre($_POST);
                // die();
                //$result = $mysql->qone('select * from tw_admin where AdminLogin = ?', $_POST['admin_login']);
                //if($result['AdminName'] != $_POST['admin_name'] || $result['AdminPassword'] != md5(ADMIN_PREFIX.$_POST['admin_pw']. ADMIN_POSTFIX) || $result['AdminLuxGroup'] != $_POST['admin_lux_group'] || $result['AdminNameChi'] != $_POST['admin_name_chi'] || $result['AdminEmail'] != $_POST['admin_email'] || $result['AdminEnabled'] != $_POST['admin_enabled']){

                //mod 20130124 用户填密码，才修改密码，否则保留旧密码
                $sign = 0;
                if($_POST['admin_pw'] != '' && $_POST['admin_repw'] != '' && $_POST['admin_pw'] == $_POST['admin_repw']){
                    $mysql->q('update tw_admin set AdminPassword = ? where AdminLogin = ?', md5(ADMIN_PREFIX.$_POST['admin_pw']. ADMIN_POSTFIX), $_POST['admin_login']);
                    $sign = 1;
                }

                //20140225 加admin type checkbox
                $admin_type = implode('|', $_POST['admin_type']);
                $ftyGrpID = '';
                if(isset($_POST['fty_user_group']) && $_POST['fty_user_group'] != ''){
                    $ftyGrpID = implode(',', $_POST['fty_user_group']);
                }

                $rtn = $mysql->q('UPDATE tw_admin SET AdminName = ?, AdminNameChi = ?, AdminEmail = ?, AdminEmailRealName = ?, AdminLuxGroup = ? , AdminEnabled = ?, AdminAnnualLeave = ?, AdminTotalHours = ?, AdminJoinDate = ?, department = ?, job_title = ?, mobile = ?, extension = ?, qq = ?, AdminPlatform = ?, FtyName = ?, FtyGrpID = ?, wh_name = ?, mode = ?  WHERE AdminLogin = ?', strtoupper($_POST['admin_name']), $_POST['admin_name_chi'], $_POST['admin_email'], $_POST['admin_email_real_name'], trim($_POST['admin_lux_group_textarea']), $_POST['admin_enabled'], $_POST['admin_annual_leave'], $_POST['admin_total_hours'], $_POST['admin_join_date'], $_POST['department'], $_POST['job_title'], $_POST['mobile'], $_POST['extension'], $_POST['qq'], $admin_type, strtoupper($_POST['fty_name']), $ftyGrpID, $_POST['wh_name'], $_POST['mode'], $_POST['admin_login']);

                if($rtn > 0 || $sign == 1){
                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], $ip_real
                        , ACTION_LOG_SYS_MOD_SYS_USER, $_SESSION["logininfo"]["aName"]." <i>modify sys user</i> '".strtoupper($_POST['admin_name'])."' in sys", ACTION_LOG_SYS_MOD_SYS_USER_S, "", "", 0);

                    $myerror->ok('修改用户帐号 成功!', 'searchuser');
                }else{
                    $myerror->warn('修改用户帐号资料 失败!', 'searchuser');
                }
                //}else{
                //$myerror->warn('用户资料与之前一样未作改动!', 'searchuser');
                //}

            }else{
                //check admin detail
                $rtn_check = $mysql->sp('CALL admin_check_name(?, ?)', $_POST['admin_login'], $_POST['admin_name']);
                if ($rtn_check){
                    //add new record
                    //20130626 trim($_POST['admin_lux_group_textarea']) 去除尾部换行符，因为添加的时候，为了能重复添加，自动加了换行符
                    //20130808 AdminName 统一改为大写的 strtoupper
                    //20140225 加admin type checkbox
                    $admin_type = implode('|', $_POST['admin_type']);
                    $ftyGrpID = '';
                    if(isset($_POST['fty_user_group']) && $_POST['fty_user_group'] != ''){
                        $ftyGrpID = implode(',', $_POST['fty_user_group']);
                    }

                    $rtn = $mysql->q('insert into tw_admin set AdminLogin = ?, AdminPassword = ?, AdminName = ?, AdminNameChi = ?, AdminEmail = ?, AdminEmailRealName = ?, AdminEnabled = ?, AdminGrpID = ?, AdminCreateDate = ?, AdminPerm = ?, AdminLuxGroup = ?, AdminAnnualLeave = ?, AdminTotalHours = ?, AdminJoinDate = ?, department = ?, job_title = ?, mobile = ?, extension = ?, qq = ?, AdminPlatform = ?, FtyName = ?, FtyGrpID = ?, wh_name = ?, mode = ?', $_POST['admin_login'], md5(ADMIN_PREFIX.$_POST['admin_pw']. ADMIN_POSTFIX), strtoupper($_POST['admin_name']), $_POST['admin_name_chi'], $_POST['admin_email'], $_POST['admin_email_real_name'], $_POST['admin_enabled'], 1, DateMore(), -1, trim($_POST['admin_lux_group_textarea']), $_POST['admin_annual_leave'], $_POST['admin_total_hours'], $_POST['admin_join_date'], $_POST['department'], $_POST['job_title'], $_POST['mobile'], $_POST['extension'], $_POST['qq'], $admin_type, strtoupper($_POST['fty_name']), $ftyGrpID, $_POST['wh_name'], $_POST['mode']);
                    if($rtn > 0){
                        //add action log
                        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                            , $_SESSION['logininfo']['aID'], $ip_real
                            , ACTION_LOG_SYS_ADD_SYS_USER, $_SESSION["logininfo"]["aName"]." <i>add sys user</i> '".strtoupper($_POST['admin_name'])."' in sys", ACTION_LOG_SYS_ADD_SYS_USER_S, "", "", 0);

                        $myerror->ok('新增用户帐号 成功!', 'searchuser');
                    }else{
                        $myerror->warn('新增用户帐号 失败!', 'searchuser');
                    }
                }else{
                    //account exist
                    $myerror->warn('閣下填寫的Account与Name不可用，可能已被使用，或者含有不允許使用的字詞');
                }
            }

            // print_r_pre($_POST);
        }
    }
    // print_r_pre($_POST);
    // print_r_pre($_GET);
    // print_r_pre($GLOBALS);
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

        <table width="65%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td class='headertitle' align="center"><? if (strlen(@$_GET['id'])){echo 'Account Management';}else{echo 'Create Account';}?></td>
            </tr>
            <tr>
                <td align="center">
                    <fieldset>
                        <legend class='legend'>information</legend>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                            <? /*
					<tr valign='top'>
						<td>NO. : </td>  
						<td align='left'><?$form->show('admin_id');?></td>
					</tr>
					*/ ?>
                            <tr align="right">
                                <td width="40%">Account : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td width="40%" align="left"><? $form->show('admin_login');?></td>
                            </tr>
                            <tr align="right" valign="top">
                                <td>New Password : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_pw');?></td>
                            </tr>
                            <tr align="right">
                                <td>Confirm Password : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_repw');?></td>
                            </tr>
                            <tr align="right">
                                <td>Name : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_name');?></td>
                            </tr>
                            <tr align="right">
                                <td>中文名 : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_name_chi');?></td>
                            </tr>
                            <tr align="right">
                                <td>Email : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_email');?></td>
                            </tr>
                            <tr align="right">
                                <td>Email Name : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_email_real_name');?></td>
                            </tr>
                            <!-- 这个没用了，现在是按月自动加年假数，但是我又不想修改提交数据的程序，所以只是不显示这个输入框 -->
                            <tr align="right" valign="top" style="display: none">
                                <td>Adding Leave hours (every 1-Jan) : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_annual_leave');?></td>
                            </tr>
                            <tr align="right">
                                <td>Accumulated Leave hours : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_total_hours');?></td>
                            </tr>
                            <tr align="right">
                                <td valign="top">Job Commencement Date : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_join_date');?></td>
                            </tr>
                            <tr align="right">
                                <td valign="top">Group : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_lux_group');?></td>
                                <td align="left"><img title="添加" style="opacity: 0.5;" onclick="addGroup()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="images/add_small.png"></td>
                                <td align="left"><img title="删除" style="opacity: 0.5;" onclick="delGroup()" onmouseover="$(this).css('opacity','1')" onmouseout="$(this).css('opacity','0.5')" src="images/del_small.png"></td>
                            </tr>
                            <tr align="right">
                                <td valign="top">Group : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('admin_lux_group_textarea');?></td>
                            </tr>
                            <tr align="right">
                                <td>Department : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><?$form->show('department');?></td>
                            </tr>
                            <tr align="right">
                                <td>Job Title : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><?$form->show('job_title');?></td>
                            </tr>
                            <tr align="right">
                                <td>Mobile# : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><?$form->show('mobile');?></td>
                            </tr>
                            <tr align="right" valign="top">
                                <td>Extension# : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><?$form->show('extension');?></td>
                            </tr>
                            <tr align="right">
                                <td>QQ : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><?$form->show('qq');?></td>
                            </tr>
                            <tr align="right" valign="top">
                                <td>User Type : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><? $form->show('admin_type');?></td>
                            </tr>
                            <tr align="right">
                                <td>&nbsp;</td>
                                <td align='left'><p class="forminfo">User Type 用于确定此用户可以登入哪个系统，可多选</p></td>
                            </tr>
                            <tr align="right">
                                <td>Supplier : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><?$form->show('fty_name');?></td>
                            </tr>
                            <tr align="right">
                                <td>&nbsp;</td>
                                <td align='left'><p class="forminfo" style="color: #FF0000;">( 注意： Supplier 此项 FTY 用户才需要填 )</p></td>
                            </tr>
                            <tr align="right">
                                <td>Supplier Group : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><?$form->show('fty_user_group');?></td>
                            </tr>
                            <tr align="right">
                                <td>&nbsp;</td>
                                <td align='left'><p class="forminfo" style="color: #FF0000;">( 注意： Supplier Group 此项 FTY 用户才需要填 )</p></td>
                            </tr>
                            <tr align="right">
                                <td>Luxcraft : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><?$form->show('wh_name');?></td>
                            </tr>
                            <tr align="right">
                                <td>&nbsp;</td>
                                <td align='left'><p class="forminfo" style="color: #FF0000;">( 注意： Luxcraft 此项 LUXCRAFT 用户才需要填 )</p></td>
                            </tr>
                            <tr align="right">
                                <td>Mode : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><?$form->show('mode');?></td>
                            </tr>
                            <tr align="right">
                                <td>&nbsp;</td>
                                <td align='left'><p class="forminfo" style="color: #FF0000;">选择 Normal mode 不显示 Exhibition<br>选择 Exhibition mode 只显示 Exhibition、Sales、Contact、Setting</p></td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr align="right">
                                <td>Vaild : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><? $form->show('admin_enabled');?></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>
                                    <?
                                    $form->show('submitbtn');
                                    // $form->show('resetbutton');
                                    $form->end();
                                    ?></td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
        </table>
    <?
    }
}else{
    require_once(ROOT_DIR.'model/inside_warn.php');
}
?>