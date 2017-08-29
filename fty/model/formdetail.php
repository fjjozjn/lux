<?php
// print_r_pre($_SESSION);
// print_r_pre($_POST);

//check permission 
//checkAdminPermission(PERM_ENQ_GAME_ACC);
judgeFtyPerm( (isset($_GET['id'])?$_GET['id']:'') );

if($myerror->getError()){
    require_once(ROOT_DIR.'model/inside_error.php');
}else{

    if(isset($_GET['id']) && $_GET['id'] != ''){
        $result = $mysql->qone('select * from bom where id = ?', $_GET['id']);
        $rs_m = $mysql->q('select * from bom_material where bom_id = ? order by id', $_GET['id']);
        if($rs_m){
            $result_m = $mysql->fetch();
        }
        $rs_t = $mysql->q('select * from bom_task where bom_id = ? order by id', $_GET['id']);
        if($rs_t){
            $result_t = $mysql->fetch();
        }

        $g_process_array = explode('|', $result['g_process']);
        $electroplate_array = explode('|', $result['electroplate']);
        $electroplate_thick_array = explode('|', $result['electroplate_thick']);
        $other_array = explode('|', $result['other']);

        $all_array = array($g_process_array, $electroplate_array, $electroplate_thick_array, $other_array);

        $image_size = '';
        if($result['photo'] && $result['photo'] != ''){
            $arr = getimagesize($pic_path_fty_bom.$result['photo']);
            $pic_width = $arr[0];
            $pic_height = $arr[1];
            $image_size = getimgsize(450, 280, $pic_width, $pic_height);
        }
        //物料总价
        $m_total = 0;
        //工序总价
        $t_total = 0;


        $process = get_bom_bmcl(1);
        $count1 = count($process);

        $electroplate = get_bom_dd(1);
        $count2 = count($electroplate);

        $electroplate_thick = get_bom_ddhd(1);
        $count3 = count($electroplate_thick);

        $other = get_bom_qt(1);
        $count4 = count($other);

        $allinarray = array($process, $electroplate, $electroplate_thick, $other);

        $count_array = array($count1, $count2, $count3, $count4);
        $select_max = 0;
        for($i = 0; $i < 4; $i++){
            if($select_max < $count_array[$i])
                $select_max = $count_array[$i];
        }

        ?>
        <h1 class="green">工廠產品物料清單</h1>

        <fieldset>
            <legend class='legend'>操作</legend>
            <div style="margin-left:28px;"><a class="button" href="?act=modifyform&modid=<?=$_GET['id']?>"><b>修改</b></a><a class="button" href="?act=modifyform&copyid=<?=$_GET['id']?>"><b>复制</b></a><a class="button" href="javascript:if(confirm('确认要删除吗?'))location='?act=modifyform&delid=<?=$_GET['id']?>'"><b>删除</b></a><a class="button" target="_blank" href="model/pdf.php?id=<?=$_GET['id']?>"><b>PDF</b></a><!--a class="button" href="model/phpexcel.php?id=<?php //echo $_GET['id']; ?>">❷<b>【下載excel文件】</b></a--></div>
        </fieldset>

        <fieldset>
            <legend class='legend'>产品资料表</legend>

            <table width="1050" border="1">
                <tr height="60">
                    <td width="110"><b>产品编号</b></td>
                    <td colspan="6" align="center"><?=$result['g_id']?></td>
                    <td colspan="5" rowspan="5" align="center"><img src="<?=$pic_path_fty_bom.$result['photo']?>" align="middle" width="<?=$image_size['width']?>" height="<?=$image_size['height']?>"/></img></td>
                </tr>
                <tr height="60">
                    <td><b>类别</b></td>
                    <td colspan="6" align="center"><?=$result['g_type']?></td>
                </tr>
                <tr height="60">
                    <td><b>底材用料</b></td>
                    <td align="center" width="70"><?=$result['g_material']?></td>
                    <td width="90"><b>尺码</b></td>
                    <td colspan="4" align="center"><?=$result['g_size']?></td>
                </tr>
                <tr height="60">
                    <td><b>成品总石数</b></td>
                    <td align="center"><?=($result['g_gem_num']!='')?$result['g_gem_num'].' 粒':''?></td>
                    <td><b>铸件</b></td>
                    <td colspan="4" align="center"><?=($result['g_cast']!='')?$result['g_cast'].' 件':''?></td>
                </tr>
                <tr height="60">
                    <td><b>电镀</b></td>
                    <td align="center"><?=$result['g_plating']?></td>
                    <td><b>重量</b></td>
                    <td colspan="4" align="center"><?=($result['g_weight']!='')?$result['g_weight'].' 克':''?></td>
                </tr>
                <tr>
                    <td width="100"><b>物料编号</b></td>
                    <td width="100"><b>名称</b></td>
                    <td width="90"><b>规格颜色</b></td>
                    <td width="50"><b>类别</b></td>
                    <!--                <td width="70"><b>单价</b></td>-->
                    <td width="80"><b>个数/重量</b></td>
                    <td width="70"><b>价格</b></td>
                    <td width="75"><b>备注</b></td>
                    <td width="125"><b>件工序号</b></td>
                    <td width="125"><b>工序名称</b></td>
                    <td width="125"><b>工时</b></td>
                    <td width="125"><b>价格</b></td>
                    <td width="75"><b>备注</b></td>
                </tr>
                <?
                for($i = 0; $i < 13; $i++){
                    if(isset($result_m[$i]['m_id']) && $result_m[$i]['m_id'] != ''){
                        $m_info = $mysql->qone('select m_loss from fty_material where m_id = ?', $result_m[$i]['m_id']);
                        echo '<tr><td>' . $result_m[$i]['m_id'] . '</td><td>' . $result_m[$i]['m_name'] . '</td><td>' . $result_m[$i]['m_color'] . '</td><td>' . $result_m[$i]['m_type'] . '</td><td>' . ($result_m[$i]['m_unit'].':'.$result_m[$i]['m_value']) . '</td><td>' . ($result_m[$i]['m_price']*$result_m[$i]['m_value']*(1+$m_info['m_loss']/100)) . '</td><td>' . $result_m[$i]['m_remark'] . '</td>';
                        $m_total += ($result_m[$i]['m_price']*$result_m[$i]['m_value']*(1+$m_info['m_loss']/100));
                    }else{
                        echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                    }
                    if(isset($result_t[$i]['t_id']) && $result_t[$i]['t_id'] != ''){
                        echo '<td>' . $result_t[$i]['t_id'] . '</td><td>' . $result_t[$i]['t_name'] . '</td><td>' . $result_t[$i]['t_time'] . '</td><td>' . ($result_t[$i]['t_price']*$result_t[$i]['t_time']) . '</td><td>' . $result_t[$i]['t_remark'] . '</td></tr>';
                        $t_total += ($result_t[$i]['t_price']*$result_t[$i]['t_time']);
                    }else{
                        echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
                    }
                }
                for($i = 13; $i < 21; $i++){
                    if($i == 13){
                        if(isset($result_m[$i]['m_id'])){
                            $m_info = $mysql->qone('select m_loss from fty_material where m_id = ?', $result_m[$i]['m_id']);
                            echo '<tr><td>' . $result_m[$i]['m_id'] . '</td><td>' . $result_m[$i]['m_name'] . '</td><td>' . $result_m[$i]['m_color'] . '</td><td>' . $result_m[$i]['m_type'] . '</td><td>' . ($result_m[$i]['m_unit'].':'.$result_m[$i]['m_value']) . '</td><td>' . ($result_m[$i]['m_price']*$result_m[$i]['m_value']*(1+$m_info['m_loss']/100)) . '</td><td>' . $result_m[$i]['m_remark'] . '</td><td colspan="5" rowspan="8">&nbsp;</td></tr>';
                            $m_total += ($result_m[$i]['m_price']*$result_m[$i]['m_value']*(1+$m_info['m_loss']/100));
                        }else{
                            echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td colspan="5" rowspan="8"><table><tr><td width="100"><b>工序</b></td><td width="100"><b>电镀</b></td><td width="100"><b>电镀厚度</b></td><td width="100"><b>其他</b></td></tr>';
                            for($j = 1; $j <= $select_max; $j++){
                                echo '<tr>';
                                for($k = 0; $k < 4; $k++){
                                    echo '<td>';
                                    if(isset($allinarray[$k][$j-1][0])){
                                        if(in_array($allinarray[$k][$j-1][0], $all_array[$k])){
                                            echo '<input type="checkbox" checked="checked"/>'.$allinarray[$k][$j-1][0];
                                        }else{
                                            echo '<input type="checkbox" />'.$allinarray[$k][$j-1][0];
                                        }
                                    }
                                    echo '</td>';
                                }
                                echo '</tr>';
                            }
                            echo '</table></td></tr>';
                        }
                    }else{
                        if(isset($result_m[$i]['m_id'])){
                            echo '<tr><td>' . $result_m[$i]['m_id'] . '</td><td>' . $result_m[$i]['m_name'] . '</td><td>' . $result_m[$i]['m_color'] . '</td><td>' . $result_m[$i]['m_type'] . '</td><td>' . $result_m[$i]['m_price'] . '</td><td>' . ($result_m[$i]['m_unit'].':'.$result_m[$i]['m_value']) . '</td><td>' . $result_m[$i]['m_remark'] . '</td></tr>';
                        }else{
                            echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
                        }
                    }
                }
                ?>
                <tr>
                    <td colspan="12"><b>物料总价</b>：<?=formatNumToMoney($m_total)?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>工序总价</b>：<?=formatNumToMoney($t_total)?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>电镀人工价</b>：<?=formatNumToMoney($result['p_plate'])?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>其他成本</b>：<?=formatNumToMoney($result['p_other'])?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>利润</b>：<?=formatNumToMoney($result['p_profit'])?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>合计</b>：<?=formatNumToMoney($result['p_total'])?> </td>
                </tr>
            </table>
            <p><b>建立人</b>：<?=$result['created_by']?>&nbsp;&nbsp;&nbsp;&nbsp; <b>审核</b>：<?=$result['check_by']?>&nbsp;&nbsp;&nbsp;&nbsp; <b>最后修改时间</b>：<?=$result['g_time']?></p>

        </fieldset>

        <fieldset>
            <legend class='legend'>操作</legend>
            <div style="margin-left:28px;"><a class="button" href="?act=modifyform&modid=<?=$_GET['id']?>"><b>修改</b></a><a class="button" href="?act=modifyform&copyid=<?=$_GET['id']?>"><b>复制</b></a><a class="button" href="javascript:if(confirm('确认要删除吗?'))location='?act=modifyform&delid=<?=$_GET['id']?>'"><b>删除</b></a><a class="button" target="_blank" href="model/pdf.php?id=<?=$_GET['id']?>"><b>PDF</b></a><!--a class="button" href="model/phpexcel.php?id=<?php //echo $_GET['id']; ?>">❷<b>【下載excel文件】</b></a--></div>
        </fieldset>

    <?
    }
}
?>