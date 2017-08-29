<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//die();
//checkAdminPermission(PERM_MANAGE_ADMIN);

//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
    $myerror->error('Without Permission To Access', 'main');
}

if(!$myerror->getWarn()){

    //get admin group
    /*
   $mysql->sp('CALL backend_detail(?, ?, ?)', '1', 'luxcraft_usergrp', '1');
   $temp_grp = $mysql->fetch(0,1);
   for($i = 0 ; $i < count($temp_grp); $i++){
       $temp = array($temp_grp[$i]['AdminGrpName'],$temp_grp[$i]['AdminGrpID']);
       $row_grp[] = $temp;
   }
   */


    $row = array();
    //$gameperm_str = array();
    if (strlen(@$_GET['delid']) && isId(@$_GET['delid'])){
        $rtn = $mysql->q('delete from luxcraft_user where LuxcraftID = ?', $_GET['delid']);
        if($rtn){

            //add action log
            $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                , $_SESSION['logininfo']['aID'], $ip_real
                , ACTION_LOG_SYS_DEL_LUXCRAFT_USER, $_SESSION["logininfo"]["aName"]." <i>delete luxcraft user</i> ID:'".$_GET['delid']."' in sys", ACTION_LOG_SYS_DEL_LUXCRAFT_USER_S, "", "", 0);

            $myerror->ok('删除 Luxcraft User 成功!', 'luxcraft_searchuser');
        }else{
            $myerror->error('删除 Luxcraft User 失败!', 'luxcraft_searchuser');
        }
    }else{
        if (strlen(@$_GET['id']) && isId(@$_GET['id'])){
            //modfiy old record, need to get admin details
            $mysql->sp('CALL backend_detail(?, ?, ?)', @$_GET['id'], 'luxcraft_user', 'LuxcraftID');
            $row = $mysql->fetch(1);
            //$gameperm_str = explode(',', $row['LuxcraftGamePerm']);
            //$luxcraft_user_group = explode(',', $row['LuxcraftGrpID']);
        }


        // print_r_pre($row);
        // die();
        // print_r_pre($gameperm_str);
        // $mysql->sp('CALL member_update_session(?)', userSession('GOLogin'));

        $form = new My_Forms();

        $formItems = array(
            /*
                'luxcraft_id' => array(
                    'type' => 'text',
                    'value' => @$row['AdminID'],
                    'readonly' => GENERAL_YES,
                    ),
            */
            'luxcraft_login' => array(
                'type' => 'text',
                'value' => @$row['LuxcraftLogin'],
                'required' => GENERAL_YES,
                'minlen' => 5,
                'maxlen' => 20,
                'readonly' => strlen(@$_GET['id'])?GENERAL_YES:GENERAL_NO,
            ),
            'luxcraft_pw' => array(
                'type' => 'password',
                'value' => '',
                'minlen' => 6,
                'maxlen' => 20,
                'required' => GENERAL_YES,
            ),
            'luxcraft_repw' => array(
                'type' => 'password',
                'value' => '',
                'minlen' => 6,
                'maxlen' => 20,
                'required' => GENERAL_YES,
                'compare' => 'luxcraft_pw'
            ),
            'luxcraft_name' => array(
                'type' => 'text',
                'required' => GENERAL_YES,
                'minlen' => 2,
                'maxlen' => 20,
                'value' => @$row['LuxcraftName'],
                'readonly' => strlen(@$_GET['id'])?GENERAL_YES:GENERAL_NO,

            ),
            'luxcraft_name_chi' => array(
                'type' => 'text',
                'value' => @$row['LuxcraftNameChi'],
                'required' => GENERAL_YES,
                'minlen' => 2,
                'maxlen' => 20,
                'readonly' => strlen(@$_GET['id'])?GENERAL_YES:GENERAL_NO,
            ),
            'luxcraft_email' => array(
                'type' => 'text',
                'value' => @$row['LuxcraftEmail'],
                'required' => GENERAL_YES,
                'restrict' => 'email',
                'minlen' => 0,
                'maxlen' => 100,
            ),
/*            'luxcraft_user_group' => array(
                'type' => 'checkbox',
                'checked' => @$luxcraft_user_group,
                'options' => array(array('Partnership', '1'), array('Vendor', '2')),
                'required' => GENERAL_YES,
            ),*/
            /*
        'luxcraft_lux_group' => array(
            'type' => 'text',
            'value' => @$row['AdminLuxGroup'],
            'required' => GENERAL_NO,
            'minlen' => 1,
            'maxlen' => 20,
            ),
            */
            /*
        'luxcraft_grp' => array(
            'type' => 'select',
            'value' => @$row['AdminGrpID'],
            'required' => GENERAL_YES,
            'options' => $row_grp,
            ),
        'luxcraft_create_date' => array(
            'type' => 'text',
            'value' => @$row['AdminCreateDate'],
            'readonly' => GENERAL_YES,
            ),
        'luxcraft_lastlogin_ip' => array(
            'type' => 'text',
            'value' => @$row['AdminLastLoginIP'],
            'readonly' => GENERAL_YES,
            ),
        'luxcraft_lastlogin_date' => array(
            'type' => 'text',
            'value' => @$row['AdminLastLoginDate'],
            'readonly' => GENERAL_YES,
            ),
        'luxcraft_enabled' => array(
            'type' => 'radio',
            'value' => 0 ?: @$row['AdminEnabled'],
            'options' => array(array('是', '1'), array('否', '0')),
            'required' => GENERAL_YES,
            ),
        'luxcraft_gameperm' => array(
            'type' => 'checkbox',
            'options' =>$gameperm_arr,
            'checked' => $gameperm_str,
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

            //$gameperm_str = @implode(',',$_POST['luxcraft_gameperm']);

            if (strlen(@$_GET['id'])){
                //$luxcraft_user_group = implode(',', $_POST['luxcraft_user_group']);
                //modify or delete exist record
                // print_r_pre($_POST);
                // die();
                $result = $mysql->qone('select * from luxcraft_user where LuxcraftLogin = ?', $_POST['luxcraft_login']);
                //20121029 去掉了这个判断，觉得太麻烦了
                //20121030 加这个判断是为了当数据没有改变时，不会出现修改失败的提示。原来是在下面 if($rtn > 0) 现在改成了 if($rtn !== false) ，经测试，彻底解决了这个问题了
                //if($result['LuxcraftName'] != $_POST['luxcraft_name'] || $result['LuxcraftPassword'] != md5(ADMIN_PREFIX.$_POST['luxcraft_pw']. ADMIN_POSTFIX) || $result['LuxcraftNameChi'] != $_POST['luxcraft_name_chi'] || $result['LuxcraftEmail'] != $_POST['luxcraft_email'] ){
                $rtn = $mysql->q('UPDATE luxcraft_user SET LuxcraftName = ?, LuxcraftNameChi = ?, LuxcraftPassword = ?, LuxcraftEmail = ? WHERE LuxcraftLogin = ?', $_POST['luxcraft_name'], $_POST['luxcraft_name_chi'], md5(ADMIN_PREFIX.$_POST['luxcraft_pw']. ADMIN_POSTFIX), $_POST['luxcraft_email'], $_POST['luxcraft_login']);
                // echo "rtn : ".$rtn;

                if($rtn !== false){

                    //add action log
                    $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                        , $_SESSION['logininfo']['aID'], $ip_real
                        , ACTION_LOG_SYS_MOD_LUXCRAFT_USER, $_SESSION["logininfo"]["aName"]." <i>modify luxcraft user</i> '".$_GET['id']."'----'".$_POST['luxcraft_name']."' in sys", ACTION_LOG_SYS_MOD_LUXCRAFT_USER_S, "", "", 0);

                    $myerror->ok('修改 Luxcraft 用户帳號 成功!', 'luxcraft_searchuser');
                }else{
                    $myerror->warn('修改 Luxcraft 用户資料 失敗!', 'luxcraft_searchuser');
                }
                //}else{
                //$myerror->warn('用户资料与之前一样未作改动!', 'luxcraft_searchuser');
                //}

            }else{
                //check admin detail
                $rtn_check = $mysql->qone('select * from luxcraft_user where LuxcraftLogin = ?', $_POST['luxcraft_login']);
                if (!$rtn_check){
                    //$luxcraft_user_group = implode(',', $_POST['luxcraft_user_group']);
                    //add new record
                    $rtn = $mysql->q('insert into luxcraft_user (LuxcraftLogin, LuxcraftPassword, LuxcraftName, LuxcraftNameChi, LuxcraftEnabled, /*LuxcraftGrpID,*/ LuxcraftCreateDate, LuxcraftEmail) values (?, ?, ?, ?, ?, ?, ?)', $_POST['luxcraft_login'], md5(ADMIN_PREFIX.$_POST['luxcraft_pw']. ADMIN_POSTFIX), $_POST['luxcraft_name'], $_POST['luxcraft_name_chi'], 1, /*$luxcraft_user_group,*/ DateMore(), $_POST['luxcraft_email']);
                    // print_r_pre($_POST);
                    // die();
                    // echo 'CALL luxcraft_grp_insert("'.$_POST['luxcraft_name'].'", "'.$gameperm_str.'")<BR>';
                    // echo $rtn;
                    if($rtn > 0){

                        //add action log
                        $mysql->sp('CALL admin_log_insert(?, ?, ?, ?, ?, ?, ?, ?)'
                            , $_SESSION['logininfo']['aID'], $ip_real
                            , ACTION_LOG_SYS_ADD_LUXCRAFT_USER, $_SESSION["logininfo"]["aName"]." <i>add luxcraft user</i> '".$_POST['luxcraft_name']."' in sys", ACTION_LOG_SYS_ADD_LUXCRAFT_USER_S, "", "", 0);

                        $myerror->ok('新增 Luxcraft 用户帳號 成功!', 'luxcraft_searchuser');
                    }else{
                        $myerror->warn('新增 Luxcraft 用户帳號 失敗!', 'luxcraft_searchuser');
                    }
                }else{
                    //account exist
                    $myerror->warn('閣下填寫的用户登入名稱不可用，可能已被使用，或者含有不允許使用的字詞');
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

        <table width="50%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td class='headertitle' align="center"><? if (strlen(@$_GET['id'])){echo 'Luxcraft Account Management';}else{echo 'Create Luxcraft Account';}?></td>
            </tr>
            <tr>
                <td align="center">
                    <fieldset>
                        <legend class='legend'>information</legend>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                            <? /*
					<tr valign='top'>
						<td>NO. : </td>  
						<td align='left'><?$form->show('luxcraft_id');?></td>
					</tr>
					*/ ?>
                            <tr align="right">
                                <td width="40%">Luxcraft Account : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('luxcraft_login');?></td>
                            </tr>
                            <tr align="right">
                                <td>Password : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('luxcraft_pw');?></td>
                            </tr>
                            <tr align="right">
                                <td>Confirm Password : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('luxcraft_repw');?></td>
                            </tr>
                            <tr align="right">
                                <td>Name : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('luxcraft_name');?></td>
                            </tr>
                            <tr align="right">
                                <td>Name Chi : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('luxcraft_name_chi');?></td>
                            </tr>
                            <tr align="right">
                                <td>Email : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align="left"><? $form->show('luxcraft_email');?></td>
                            </tr>
<!--                            <tr align="right">
                                <td>Group :  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td align='left'><?/* $form->show('luxcraft_user_group');*/?></td>
                            </tr>-->
                            <? /*
					<tr align="right">
						<td>Group : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>  
						<td align="left"><? $form->show('luxcraft_lux_group');?></td>
					</tr>
					*/ ?>
                            <? /*
					<tr valign='top'>
						<td>所屬群組 : </td>  
						<td align='left'><?$form->show('luxcraft_grp');?></td>
					</tr>
					<tr valign='top'>
						<td>負責遊戲 : </td>  
						<td align='left'><?$form->show('luxcraft_gameperm');?></td>
					</tr>											
					<tr valign='top'>
						<td>帳號新增日期 : </td>  
						<td align='left'><?$form->show('luxcraft_create_date');?></td>
					</tr>	
					<tr valign='top'>
						<td>上次登入IP : </td>  
						<td align='left'><?$form->show('luxcraft_lastlogin_ip');?></td>
					</tr>	
					<tr valign='top'>
						<td>上次登入日期 : </td>  
						<td align='left'><?$form->show('luxcraft_lastlogin_date');?></td>
					</tr>	
					<tr valign='top'>
						<td>可使用 : </td>  
						<td align='left'><?$form->show('luxcraft_enabled');?></td>
					</tr>	
					*/ ?>
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