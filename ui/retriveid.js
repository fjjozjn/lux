// JavaScript Document
var timer = 5;

window.onload=function(){
	if(account != ''){
		try{
			window.opener.document.getElementById('account').value=account;
			//window.opener.focus();
			window.opener.document.getElementById('account').focus();
			closeSelf();
		}catch(e){
			//nothing
		}
	}
}

function closeSelf(time){
	timer--;
	try{
		document.getElementById('windowcloser').value='窗口将在 ' + timer + ' 秒后关闭';
	}catch(e){}
	if(timer<0){
		window.close();
	}else{
		setTimeout('closeSelf()', 1000);
	}
}

function copyAccount(obj){
	if(account.length > 0){
		if(copy2Clipboard(obj.value)!=false){
			document.getElementById('notice').style.display='block';
		}else{
			document.getElementById('alert').style.display='block';
		}
		account = '';
	}
	if(obj.value.length > 0) obj.select();
}

function copy2Clipboard(txt){
    if(window.clipboardData){
        window.clipboardData.clearData();
        window.clipboardData.setData("Text",txt);
    }
    else if(navigator.userAgent.indexOf("Opera")!=-1){
        window.location=txt;
    }
    else if(window.netscape){
        try{
            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        }
        catch(e){
            return false;
        }
        var clip=Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
        if(!clip)return;
        var trans=Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
        if(!trans)return;
        trans.addDataFlavor('text/unicode');
        var str=new Object();
        var len=new Object();
        var str=Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
        var copytext=txt;str.data=copytext;
        trans.setTransferData("text/unicode",str,copytext.length*2);
        var clipid=Components.interfaces.nsIClipboard;
        if(!clip)return false;
        clip.setData(trans,null,clipid.kGlobalClipboard);
    }
}