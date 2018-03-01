<?php
/**
 * Author: zhangjn
 * Date: 2018/3/1
 * Time: 14:27
 */
?>
<fieldset style="width:70%">
    <legend class='legend'>按AP多到少排列，AP为0的不显示</legend>
    <br />
    <table width="80%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000' align="center">
        <tr bgcolor='#EEEEEE'>
            <th></th>
            <th>物料供应商编号</th>
            <th>名字</th>
            <th>AP</th>
        </tr>
        <?
        $i = 1;
        $rs = $mysql->q('select cid, name, ap from fty_wlgy_customer where ap > 0 order by ap desc');
        if($rs){
            $rtn = $mysql->fetch();
            foreach($rtn as $v){
                echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";
                echo "	<td>".$i."</td>";
                echo "	<td>".$v['cid']."</td>";
                echo "	<td>".$v['name']."</td>";
                echo "	<td>".$v['ap']."</td>";
                echo "</tr>";
                $i++;
            }
        }
        ?>
    </table>
</fieldset>
