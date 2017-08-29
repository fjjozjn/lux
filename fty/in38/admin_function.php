<?

function checkAdminLogin(){	
	if (!isset($_SESSION['ftylogininfo'])){
		//not login yet
		redirectTo('?act=login');
	}
}

function redirectTo($link){	
	echo '<Script Language ="JavaScript">';
	echo "window.location.href = '".$link."';";
	echo "</Script>";	
}

//按比例縮小圖片
function getimgsize($oldwidth,$oldheight,$imgwidth,$imgheight)
{
	//$oldwidth設置的寬度，$oldheight設置的高度，$imgwidth圖片的寬度，$imgheight圖片的高度

	//單元格裝得進圖片，則按圖片的真實大小顯示
	if($imgwidth <= $oldwidth && $imgheight <= $oldheight)
	{
		$arraysize = array('width' => $imgwidth, 'height' => $imgheight);
		return $arraysize;
	}
	else
	{
		$suoxiaowidth = $imgwidth - $oldwidth;
		$suoxiaoheight = $imgheight - $oldheight;
		$suoxiaoheightper = $suoxiaoheight / $imgheight;
		$suoxiaowidthper = $suoxiaowidth / $imgwidth;
		if($suoxiaoheightper >= $suoxiaowidthper)
		{
			//單元格高度為準
			$aftersuoxiaowidth = $imgwidth * (1 - $suoxiaoheightper);
			$arraysize = array('width' => $aftersuoxiaowidth, 'height' => $oldheight);
			return $arraysize;
		}
		else
		{
			//單元格寬度為準
			$aftersuoxiaoheight = $imgheight * (1 - $suoxiaowidthper);
			$arraysize = array('width' => $oldwidth, 'height' => $aftersuoxiaoheight);
			return $arraysize;
		}
	}
}



//2012.6.25 工厂用户只能浏览自己创建的内容
function judgeFtyPerm($modid){ 
	global $act, $myerror;
    $result = true;
	if(!isFtyAdmin()){
		if(strpos($act, "modifydelivery") !== false){
			$sql = 'select sid from delivery_order where po_id = ?';
            $rtn = mysql_qone("$sql", $modid);
            if($rtn['sid'] != $_SESSION['ftylogininfo']['aName']){
                $result = false;
            }
		}elseif(strpos($act, "modifyform") !== false || strpos($act, "formdetail") !== false){
			$sql = 'select FtyName from tw_admin where AdminName = (select created_by from bom where id = ?)';
            $rtn = mysql_qone("$sql", $modid);
            if($rtn['FtyName'] != $_SESSION['ftylogininfo']['aFtyName']){
                $result = false;
            }
		}elseif(strpos($act, 'addmaterial') !== false){
			//20121024 这里是因为addmaterial只有一个页面，包含了add和modify，所以要特殊处理，没有modid的时候为add情况，不作权限限制
            //20141117 加 非普通用户只能看到同工厂添加的
			if($modid == ''){
				$sql = '';
			}else{
				//$sql = 'select created_by as fty_user from material where m_id = ?';
				$sql = 'select AdminName from tw_admin where AdminPlatform like ? AND FtyName = (select FtyName from tw_admin where AdminName = (select created_by from fty_material where m_id = ?))';
			}
            if($sql != ''){
                mysql_q("$sql", '%fty%', $modid);
                $rtn = mysql_fetch();
                $result = false;
                foreach($rtn as $v){
                    if($_SESSION['ftylogininfo']['aName'] == $v['AdminName']){
                        $result = true;
                    }
                }
            }
		}elseif(strpos($act, 'addtask') !== false){
            //20141117 加 非普通用户只能看到同工厂添加的
			if($modid == ''){
				$sql = '';
			}else{
				//$sql = 'select created_by as fty_user from task where t_id = ?';
                $sql = 'select AdminName from tw_admin where AdminPlatform like ? AND FtyName = (select FtyName from tw_admin where AdminName = (select created_by from fty_task where t_id = ?))';
			}
            if($sql != ''){
                mysql_q("$sql", '%fty%', $modid);
                $rtn = mysql_fetch();
                $result = false;
                foreach($rtn as $v){
                    if($_SESSION['ftylogininfo']['aName'] == $v['AdminName']){
                        $result = true;
                    }
                }
            }
		}

        //$result=true能通过，$result=false不能通过
        if(!$result){
            $myerror->error('Without Permission To Access', 'main');
        }
	}
}



//2012.9.11 用户只能浏览与自己相关的内容
function judgeUserPerm($modid){
	global $act, $myerror;
	$sql = '';
	if(!isFtyAdmin()){
		if(strpos($act, "viewpurchase") !== false){
			$sql = 'select sid, istatus from purchase where pcid = ?';
		}
		if($sql != ''){
			$rtn = mysql_qone("$sql", $modid);
			if($_SESSION['ftylogininfo']['aFtyName'] != $rtn['sid'] || $rtn['istatus'] == '(D)'){
				$myerror->error('Without Permission To Access', 'main');
			}
		}		
	}
}


//格式化显示钱：保留两位小数，不足的补0，整数部分每三位逗号隔开
function formatMoney($money){
	return fmoney(sprintf("%01.2f", round(floatval($money), 2)));
}
//格式化货币
function fmoney($num) {
	$num=0+$num;
	$num = sprintf("%.02f",$num);
	if(strlen($num) <= 6) return $num;
	//从最后开始算起，每3个数它加一个","
	for($i=strlen($num)-1,$k=1, $j=100; $i >= 0; $i--,$k++) {
		$one_num = substr($num,$i,1);
		if($one_num ==".") {
			$numArray[$j--] = $one_num;
			$k=0;
			continue;
		}

		if($k%3==0 and $i!=0) {
			//如果正好只剩下3个数字，则不加','
			$numArray[$j--] = $one_num;
			$numArray[$j--] = ",";
			$k=0;
		} else {
			$numArray[$j--]=$one_num;
		}
	}
	ksort($numArray);
	return join("",$numArray);
}


//php對ajax傳來的中文escape後的信息進行解碼
function unescape($str){
	$ret = '';
	$len = strlen($str);
	for ($i = 0; $i < $len; $i++){
		if ($str[$i] == '%' && $str[$i+1] == 'u'){
			$val = hexdec(substr($str, $i+2, 4));
			if ($val < 0x7f) $ret .= chr($val);
			else if($val < 0x800) $ret .= chr(0xc0|($val>>6)).chr(0x80|($val&0x3f));
			else $ret .= chr(0xe0|($val>>12)).chr(0x80|(($val>>6)&0x3f)).chr(0x80|($val&0x3f));
			$i += 5;
		}
		else if ($str[$i] == '%'){
			$ret .= urldecode(substr($str, $i, 3));
			$i += 2;
		}
		else $ret .= $str[$i];
	}
	return $ret;
} 


//20121023
//mod 20121125 生成的编号减少2位
function fty_autoGenerationID(){
	global $act;
	$sql = '';

    //20141116 material和task的规则改为 如C000001 (物料), P000001 (工序)，不同工厂的编号分别递加，可以相同
	if(strpos($act, "addmaterial") !== false){
		//2-4位是工厂号，最后6位是流水号
		//$result = mysql_qone('select m_id from material where created_by = ? order by m_id desc limit 1', $_SESSION['ftylogininfo']['aName']);
		$result = mysql_qone('select m_id from fty_material where created_by in (select AdminName from tw_admin where AdminPlatform like ? and FtyName = (select FtyName from tw_admin where AdminName = ?)) order by m_id desc limit 1', "%fty%", $_SESSION['ftylogininfo']['aName']);
		if($result['m_id']){
			return substr($result['m_id'], 0, -6).sprintf("%06d", substr($result['m_id'], -6)+1);
		}else{
			//return 'C'.get_fty_no().'000001';
			return 'C000001';
		}
    }elseif(strpos($act, "addtask") !== false){
		//2-4位是工厂号，最后6位是流水号
		//$result = mysql_qone('select t_id from task where created_by = ? order by t_id desc limit 1', $_SESSION['ftylogininfo']['aName']);
		$result = mysql_qone('select t_id from fty_task where created_by in (select AdminName from tw_admin where AdminPlatform like ? and FtyName = (select FtyName from tw_admin where AdminName = ?)) order by t_id desc limit 1', "%fty%", $_SESSION['ftylogininfo']['aName']);
		if($result['t_id']){
			return substr($result['t_id'], 0, -6).sprintf("%06d", substr($result['t_id'], -6)+1);
		}else{
			//return 'P'.get_fty_no().'000001';
			return 'P000001';
		}
	}elseif(strpos($act, "adddelivery") !== false){
		//流水号，各自的公司有各自的流水号，默认从1开始
		$liushui = 0;
		//在现有的流水号中找出最大的
		$largest = 0;
		//mod 20130408 同一天流水号累加，过了一天后就又从1开始了
		$sql = 'select d_id from delivery where sid = ? and STR_TO_DATE(in_date, ?) = ?';
		$rs = mysql_q($sql, $_SESSION['ftylogininfo']['aFtyName'], '%Y-%m-%d', date('Y-m-d'));
		if($rs){
			$rtn = mysql_fetch();
			foreach($rtn as $v){
				$id_array = explode('-', $v['d_id']);
				if(isset($id_array[2]) && $id_array[2] != ''){
					if($id_array['2'] > $largest){
						$largest = $id_array['2'];
					}
				}
			}
		}
		if($largest > 0){
			$liushui = $largest + 1;	
		}else{
			//这种情况是在前面$largest都没有被赋值，也就是此工厂的单还一个都没有，没有流水号，于是流水号值为1	
			$liushui = 1;
		}
		return $_SESSION['ftylogininfo']['aFtyName'] .'-'. date('Ymd') .'-'. sprintf("%02d", $liushui);

	}elseif(strpos($act, "modify_sub_contractor_order") !== false && $_GET['pcid'] != ''){
        //例：SCO00001348-001
        $result = mysql_qone('select sco_id from fty_sub_contractor_order where sco_id like ? order by id desc limit 1', str_replace('PO00', 'SCO0', $_GET['pcid']).'%');
        if($result['sco_id']){
            $temp = explode('-', $result['sco_id']);
            return str_replace('PO00', 'SCO0', $temp[0]).'-'.sprintf("%03d", $temp[1]+1);
        }else{
            return str_replace('PO00', 'SCO0', $_GET['pcid']).'-001';
        }
    }elseif(strpos($act, "add_material_in") !== false){
        //例：MI00000001
        $result = mysql_qone('select mi_id from fty_material_in order by id desc limit 1');
        if($result['mi_id']){
            return substr($result['mi_id'], 0, -6).sprintf("%06d", substr($result['mi_id'], -6)+1);
        }else{
            return 'MI00000001';
        }
    }elseif(strpos($act, "add_material_out") !== false){
        //例：MO00000001
        $result = mysql_qone('select mo_id from fty_material_out order by id desc limit 1');
        if($result['mo_id']){
            return substr($result['mo_id'], 0, -6).sprintf("%06d", substr($result['mo_id'], -6)+1);
        }else{
            return 'MO00000001';
        }
    }elseif(strpos($act, "add_material_buy") !== false){
        //例：M000000001
        $result = mysql_qone('select m_id from fty_material_buy order by id desc limit 1');
        if($result['m_id']){
            return substr($result['m_id'], 0, -6).sprintf("%06d", substr($result['m_id'], -6)+1);
        }else{
            return 'M000000001';
        }
    }elseif(strpos($act, "modify_fty_material_require") !== false){
        //例：MR00000001
        $result = mysql_qone('select mr_id from fty_material_require order by id desc limit 1');
        if($result['mr_id']){
            return substr($result['mr_id'], 0, -6).sprintf("%06d", substr($result['mr_id'], -6)+1);
        }else{
            return 'MR00000001';
        }
    }
}


//判断是否是fty的admin
function isFtyAdmin(){
	if(isset($_SESSION['ftylogininfo']['aName'])){
		$rs = mysql_q('select AdminName from tw_admin where AdminLuxGroup = ? and AdminPlatform like ?', 'admin', '%fty%');
		if($rs){
			$rtn = mysql_fetch();
			foreach($rtn as $v){
				if($_SESSION['ftylogininfo']['aName'] == $v['AdminName']){
					return true;	
				}
			}
		}else{
			return false;	
		}
	}else{
		return false;	
	}
	return false;
}

//获取工厂编号S001即返回1
function get_fty_no(){
	if(!isFtyAdmin()){
		return substr($_SESSION['ftylogininfo']['aFtyName'], 1);
	}else{
		return 'TEST';
	}
}
