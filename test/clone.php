<?

include "../in7/global.php";
?>
<script language="javascript" type="text/javascript" src="/ui/jquery.js"></script>
<table>
	<tr id="222">
    	<td><input id="abc" name="abc" type="text" value="www.zqgame.com"></input></td>
        <td><input id="def" name="def" type="text" value="en.zqgame.com"></input></td>
    </tr>
	<tr id="333">
    	<td>www.xxx.com</td>
    </tr>    
</table>




<script>
$(function(){
	var my = $("#222");
	var myclone = my.clone(true);
	$("<tr>" + myclone.html() + "</tr>").insertAfter(my);
	
	var myclone_input_id = myclone.find('input').attr("id");
	var timestamp = Date.parse(new Date())/1000;		
		
	myclone.find("input").each(function(){
		var myid = $(this).attr("id");
		alert(myid + timestamp);
		//this.id = myid + timestamp;
		$(this).attr("id", myid + timestamp);
	})
	
})
</script>