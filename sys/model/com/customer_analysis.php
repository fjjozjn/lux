<?

//禁止其他用户进入（临时做法）
if(!isSysAdmin()){
	$myerror->error('Without Permission To Access', 'main');
}

if($myerror->getWarn()){
	require_once(ROOT_DIR.'model/inside_warn.php');	
}else{
	//引用特殊的recordset class 文件
	require_once(ROOT_DIR.'sys/in38/recordset.class2.php');
	
	// 如果有post資料則給Session，并且清除附在上次翻頁時殘留的$_GET['page']
	if (count($_POST)){
		$_SESSION['search_criteria'] = $_POST;
	}
	
	$form = new My_Forms();
	$formItems = array(
			'customer' => array(
				'type' => 'select',
				'options' => get_customer(),
				'value' => @$_SESSION['search_criteria']['customer'], 
				),			
			'submitbutton' => array(
				'type' => 'submit', 
				'value' => 'Search', 
				'title' => ''),	
			);
	$form->init($formItems);
	$form->begin();	
	
	?>

<table width="500" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td class='headertitle' align="center">Customer Analysis</td>
	</tr>
	<tr>
	<td align="center">	
	<fieldset>
	<legend class='legend'>Search</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr>
				<td height="35">Customer : </td>  
				<td><?
				$form->show('customer');
				?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>	
			<tr>
				<td width="100%" colspan='4'>
				<?
				$form->show('submitbutton');
				// $form->show('resetbutton');
				
				?></td>
			</tr>				
		</table>
	</fieldset>	
	</td>	
	</tr>
</table><br />
<?
	$form->end();

?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr valign='top'>
	<td align="center" width="80%">	

<?	
	if (strlen(@$_SESSION['search_criteria']['customer'])){	
	
	//die();
		
		$s_name = $mysql->qone('select name from customer where cid = ?', $_SESSION['search_criteria']['customer']);
		$rs = $mysql->q('select total, total_qty, mark_date, currency from invoice where cid = ? and istatus <> ? order by mark_date', $_SESSION['search_criteria']['customer'], 'delete');
		if($rs){
			$rtn = $mysql->fetch();
			$invoice_total = array();
            $invoice_total_qty = array();
			foreach($rtn as $v){
				$the_mark_date = explode('-', $v['mark_date']);
				if($v['currency'] == 'USD'){
					@$invoice_total[$the_mark_date[0]][intval($the_mark_date[1])] += $v['total'];
				}else{
					@$invoice_total[$the_mark_date[0]][intval($the_mark_date[1])] += currencyTo($v['total'], $v['currency'], 'USD');
				}
                @$invoice_total_qty[$the_mark_date[0]][intval($the_mark_date[1])] += $v['total_qty'];
            }
	?>
	<fieldset>
	<legend class='legend'>Information ( 1.Counted by Invoices; 2.all status except "delete"; 3.currency:USD; 4
        .AMOUNT----invoice items total prices; 5.QTY----invoice items total quantity; )
    </legend>
    	<div><?=$s_name['name']?></div>
        <br />
		<table width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000' align="center">	
			<tr bgcolor='#EEEEEE' align="center">
				<th rowspan="2"></th>
				<th colspan="2">Jan</th>
				<th colspan="2">Feb</th>
				<th colspan="2">Mar</th>
				<th colspan="2">Apr</th>
				<th colspan="2">May</th>
                <th colspan="2">Jun</th>
				<th colspan="2">Jul</th>
				<th colspan="2">Aug</th>
				<th colspan="2">Sep</th>
                <th colspan="2">Oct</th>
				<th colspan="2">Nov</th>
                <th colspan="2">Dec</th>
				<th rowspan="2">AMOUNT</th>
				<th rowspan="2">QTY</th>
			</tr>
            <tr bgcolor='#EEEEEE'>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
                <th>Amount</th><th>Qty</th>
            </tr>
	<?
			for($i = 2011; $i <= date('Y'); $i++){
				echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";
				echo "<td>".$i."</td>";
				$all_total = 0;
                $all_total_qty = 0;
				for($j = 1; $j <= 12; $j++){	
					$all_total += @$invoice_total[$i][$j];	
					$all_total_qty += @$invoice_total_qty[$i][$j];
					echo "<td>".(isset($invoice_total[$i][$j])?formatMoney($invoice_total[$i][$j]):'')."</td>";
					echo "<td>".(isset($invoice_total_qty[$i][$j])?$invoice_total_qty[$i][$j]:'')."</td>";
				}
				echo "<td>".formatMoney($all_total)."</td>";
				echo "<td>".($all_total_qty)."</td>";
				echo "</tr>";
			}
		}else{
?>
	<fieldset style="width:475px;">
	<legend class='legend'>Information</legend>
    	<div><?=$s_name['name']?> No Records !</div>
    
<?			
		}
	}

?>	
                </table>
            </fieldset>
            </td>
            </tr>
        </table>
<br />
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr valign='top'>
            <td align="center" width="80%">

                <?
                if (strlen(@$_SESSION['search_criteria']['customer'])){

                //die();

                $s_name = $mysql->qone('select name from customer where cid = ?', $_SESSION['search_criteria']['customer']);
                $rs = $mysql->q('select creation_date, product_total from sample_order where customer = ? and s_status <> ? order by creation_date', $_SESSION['search_criteria']['customer'], 'delete');
                if($rs){
                $rtn = $mysql->fetch();
                $sample_order_total_qty = array();
                foreach($rtn as $v){
                    $the_mark_date = explode('-', $v['creation_date']);
                    @$sample_order_total_qty[$the_mark_date[0]][intval($the_mark_date[1])] += $v['product_total'];
                }
                ?>
                <fieldset>
                    <legend class='legend'>Information ( 1.Counted by Sample Order; 2.all status except "delete"; 3.QTY----总款数; )
                    </legend>
                    <div><?=$s_name['name']?></div>
                    <br />
                    <table width="100%" border='1' bordercolor='#ABABAB' cellspacing='1' cellpadding='3' bgcolor='#000000' align="center">
                        <tr bgcolor='#EEEEEE' align="center">
                            <th rowspan="2"></th>
                            <th>Jan</th>
                            <th>Feb</th>
                            <th>Mar</th>
                            <th>Apr</th>
                            <th>May</th>
                            <th>Jun</th>
                            <th>Jul</th>
                            <th>Aug</th>
                            <th>Sep</th>
                            <th>Oct</th>
                            <th>Nov</th>
                            <th>Dec</th>
                            <th rowspan="2">Total QTY</th>
                        </tr>
                        <tr bgcolor='#EEEEEE'>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                        </tr>
                        <?
                        for($i = 2011; $i <= date('Y'); $i++){
                            echo "<tr class='td_' align='center' onMouseOver=\"this.className='td_highlight';\" onMouseOut=\"this.className='td_';\" valign='top'>";
                            echo "<td>".$i."</td>";
                            $all_total_qty = 0;
                            for($j = 1; $j <= 12; $j++){
                                $all_total_qty += @$sample_order_total_qty[$i][$j];
                                echo "<td>".(isset($sample_order_total_qty[$i][$j])?$sample_order_total_qty[$i][$j]:'')."</td>";
                            }
                            echo "<td>".($all_total_qty)."</td>";
                            echo "</tr>";
                        }
                        }else{
                        ?>
                        <fieldset style="width:475px;">
                            <legend class='legend'>Information</legend>
                            <div><?=$s_name['name']?> No Records !</div>

                            <?
                            }
                            }

                            ?>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>
<?	
}
?>
