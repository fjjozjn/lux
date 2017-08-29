<?

if( isset($_GET['search_text']) && $_GET['search_text'] != ''){
	$key = $_GET['search_text'];
	$rtn = $mysql->q("select pid from product where pid like ? order by in_date desc limit 20", "%$key%");
	if($rtn){
		$result = $mysql->fetch();
		$pid_rtn = "[";
		for($i = 0; $i < count($result); $i++){
			if($i != count($result) - 1){
				$pid_rtn .= "\"" . $result[$i]['pid'] . "\"" . ",";
			}else{
				$pid_rtn .= "\"" . $result[$i]['pid'] . "\"";	
			}
		}
		$pid_rtn .= "]";
		echo $pid_rtn;
	}else{
		echo 'no-1';	
	}
}else{
	echo 'no-2';	
}



/*
<%@ page language="java" import="java.util.*" pageEncoding="utf-8"%>
<%
String []words = {"amani","abc","apple","abstract","an","bike","byebye",
"beat","be","bing","come","cup","class","calendar","china"};
if(request.getParameter("search-text") != null) {
	if(key.length() != 0){
		String json="[";
		for(int i = 0; i < words.length; i++) {
			if(words[i].startsWith(key)){
				json += "\""+ words[i] + "\"" + ",";
			}
		}
		json = json.substring(0,json.length()-1>0?json.length()-1:1);
		json += "]";
		System.out.println("json:" + json);
		out.println(json);
	}
}
%> 
*/
