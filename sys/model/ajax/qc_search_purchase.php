<?
if(isset($_GET['value'])){
    //qc_report 一个pcid只能添加一个单
    $rs = $mysql->q('select id from qc_report where pcid = ?', $_GET['value']);
    if(!$rs){
        //找出purchase_item信息
        $rs = $mysql->q('select pid, quantity, photos from purchase_item where pcid = ?', $_GET['value']);
        if($rs){
            $rtn = $mysql->fetch();
            $str = '';
            for($i = 0; $i < count($rtn); $i++){
                //因为是在js中输出代码，所以要判断一下图片是否真的存在，存在才返回图片名
                if (!is_file($pic_path_small.'s_'.$rtn[$i]['photos']) == true){
                    $rtn[$i]['photos'] = '';
                }
                $str .= ($i == count($rtn) - 1) ? ($rtn[$i]['pid'].'|'.$rtn[$i]['photos'].'|'.$rtn[$i]['quantity']) : ($rtn[$i]['pid'].'|'.$rtn[$i]['photos'].'|'.$rtn[$i]['quantity'].',');
            }
            echo $str;
        }else{
            echo '!no-1';
        }
    }else{
        echo '!no-3';
    }
}else{
	echo '!no-2';
}