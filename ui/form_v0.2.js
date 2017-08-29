/*
 * form 自動驗證
 * 阻止輸入不可接受的字符
 *
 * 用法：
 * 在input中可以設置以下屬性:
 *   通用屬性:
 *   
 *   特殊属性:
 *
 *
 * @copyright  yb
 * @version    0.2
 * @since      2009.7.15
 * @change     2010.09.27       change the part of how "reverse" works;still not good enough.
 				2010.12.29		add 'noinit' to textarea.
				2011.02.22		change 'checkErr', add text depend on checkbox.
*/

/**************可配置**************/
var tooltipAutoCloseTime = 6000;	//tooltip 自動消失的時間 單位：秒
var tooltipGoRight = 80;			//tooltip 顯示偏移. 單位：像素
var tooltipWidth = 276;				//tooltip 寬度. 單位：像素
var tooltipHeight = 35;				//tooltip 高度. 單位：像素
var scrolltop = 200;				//滾動屏幕時，頂部高度. 單位：像素
var submitTime = 0;

/**************變量**************/
var tooltipClock;
var keyList = Array(9, 13, 32, 8, 16, 17, 18, 20, 144, 109, 189, 37, 38, 39, 40, 35, 36, 45, 46, 33, 64, 42, 41, 40);

/**************functinos**************/
function keyCatcher(e) {
	var evt = e||window.event;
	var key = evt.keyCode||evt.which;
	var obj =evt.srcElement||evt.target;
	var charAllowed = function(key){
		return ((key <= 90 && key >= 65) || (key <= 57 && key >= 48) || (key <= 122 && key >= 96) || in_array(key, Array(45, 33, 64, 38, 39, 42, 41, 40, 95)));
	}
	
	if (key == 8 && (obj.type!= "text" && obj.type!= "textarea" && obj.type!= "password")) {
		return cancelKeypress(evt); //backspace
	}else if(key != "undefined" && key && (obj.type == "text" || obj.type == "password") && $(obj).attr('restrict')!= "undefined" && $(obj).attr('restrict')){
		if(in_array(key, keyList) || charAllowed(key)){
			if(((evt.shiftKey && key != 16) || !evt.shiftKey) && charAllowed(key)){
				var msg = checkChar($(obj).attr('restrict').toLowerCase(), key);
				if (msg != 1){
					showTooltip(obj, msg);
				}else{
					return true;
				}
			}else if(in_array(key, keyList)){
				return true;
			}
		}else{
			showTooltip(obj, Lang.rightChar);
		}
		return cancelKeypress(evt);
	}
}

//取消用戶非法輸入
function cancelKeypress(e){
	if (e.preventDefault) { 
		e.preventDefault(); 
		return false; 
	} else { 
		e.keyCode = 0; 
		e.returnValue = false; 
		return false;
	}
}

/* my own tooltip function
 * argument:
 *   (obj, msg, focus, revise)
 * obj : which form element that you put tooltip on.
 * msg : what you gonna say.
 * focus & scroll: need focus on this element? default will be no.
 * left: how many px tooltip goes left.
*/
function showTooltip(){
	var obj = arguments[0];
	var msg = arguments[1];
	if(msg.length == 0){
		closeTooltip();
		return false;
	}
	var submiting = false;
	var revise = 0;
	if(arguments.length > 2)submiting = arguments[2];
	if(arguments.length > 3)revise = arguments[3];
	var left = 0;
	
	var pos=$(obj).position();
	//form提交時卷動
	if(submiting){
		var s = pos.top - scrolltop<0 ? 0 : pos.top - scrolltop;
		$.browser.safari ? $('body').animate({scrollTop:s}, 250, null, function(){try{obj.focus();}catch(e){}}) : jQuery('html').animate({scrollTop: s}, 250, null, function(){try{obj.focus();}catch(e){}});
	}
	left = revise < 0 ? revise : (obj.type == 'radio') ? 30 : tooltipGoRight;
	if(left > 0 && document.body.clientWidth < tooltipWidth + pos.left + left)left = 0 - left;
	$('#mytt').removeClass().addClass(left < 0 ? 'ttreverse' : 'ttnormal').data('reverse', (left < 0 ? 'r' : '')).html(msg).css({'top': pos.top - tooltipHeight, 'left': pos.left + left});
	clearTimeout(tooltipClock);
	tooltipClock = setTimeout('closeTooltip()', tooltipAutoCloseTime);
	if($('#mytt').is(':visible')){
		$('#mytt').stop(true, true);
	}
	$('#mytt').slideDown('fast');
	return false;
}

function closeTooltip(){
	if($('#mytt').is(':visible'))
		$('#mytt').slideUp('fast');
}

function checkChar(type, c){
	var k = String.fromCharCode(c);
	var msg;
	switch(type){
		case 'number':
		msg = /[0-9\-\.]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '數字與符號 . -';
		break;
		
		case 'letter':
		msg = /[A-Za-z]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文字元';
		break;
		
		case 'date':
		msg = /[0-9\-]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '數字與符號 -';
		break;
		
		case 'card':
		msg = /[A-Za-z0-9\-]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、數字及 -';
		break;
		
		case 'account':
		msg = /[A-Za-z0-9\_]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、數字及 _';
		break;
		
		case 'twidcard':
		case 'password':
		case 'scode':
		//msg = /[A-Za-z0-9\!\@\#\$\*\(\)]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、數字及符號!@#$*()';
		msg = /[A-Za-z0-9]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、數字';
		break;
		
		case 'email':
		msg = /[A-Za-z0-9\.\@\-\_]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、數字及符號 @-_.';
		break;
		
		case 'ihridername':
		msg = /[A-Za-z0-9 ]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '中文、英文或數字!';
		break;
		
		default:
		msg = /[A-Za-z]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文字元!';
		break;
	}
	return msg;
}

function checkRestrict(obj){
	var msg, ptn;
	var type = $(obj).attr('restrict');
	var req = $(obj).attr('required');
	var str = $(obj).val();
	var strlen = $(obj).attr('strlen');
	var minLen = 0, maxLen = 0;
	var lenStr, lenMsg;
	alert($(obj).attr('id') + ':' + req);
	if(req == "undefined" && type == "undefined"){
		return 1;
	}else{
		if(strlen != "undefined"){
			var lenArr = strlen.split(',');
			minLen = lenArr[0];
			if(lenArr.length > 1) maxLen = lenArr[1];
		}else{
			minLen = 3, maxLen = 30;
		}
		lenStr = '{' + minLen + (maxLen ? ',' + maxLen : '') + '}';
		lenMsg = '長度(' + minLen + (maxLen ? '-' + maxLen : '') + ')';
	}
	//if(req != "undefined" && str.length == 0) return '請填寫內容';
	if(type == "undefined") type = 'nothing';
	switch(type){
		case 'number':
		eval('ptn = /^[0-9\-\.]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的數字與符號 . -';
		break;
		
		case 'letter':
		eval('ptn = /^[A-Za-z]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的英文字元!';
		break;
		
		case 'date':
		eval('ptn = /^[0-9\-]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的日期!';
		break;
		
		case 'card':
		eval('ptn = /^[A-Za-z0-9\-]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的英文、數字及符號 -';
		break;
		
		case 'account':
		eval('ptn = /^[A-Za-z0-9\_]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的英文、數字及 _';
		break;
		
		case 'twidcard':
		msg = twValidIdcard(str) ? 1 : Lang.prmptPrefix + '正確的身份證';
		break;
		
		case 'password':
		case 'scode':
		//eval('ptn = /^[A-Za-z0-9\!\@\#\$\*\(\)]' + lenStr + '$/;');
		eval('ptn = /^[A-Za-z0-9]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的英文、數字'; //及!@#$*()
		break;
		
		case 'email':
		msg = (str.search(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/) != -1 && str.length < 100) ? 1 : '請填寫正確的電子郵箱地址';
		break;
		
		case 'nothing':
		msg = (minLen <= strLength(str) && strLength(str) <= maxLen) ? 1 : Lang.prmptPrefix + lenMsg + '的內容!';
		break;
		
		case 'ihridername':
		msg = (minLen <= strLength(str) && strLength(str) <= maxLen) ? 1 : Lang.prmptPrefix + lenMsg + '中文、英文或數字!';
		break;		
		
		default:
		//eval('ptn = /^[A-Za-z0-9]' + lenStr + '$/;');
		//msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的英文字元與數字!';
		msg = 1;
	}
	return msg;
}

function checkSubmit(){
	var thisSubmitTime = new Date();
	var input = $(this).find(':input');
	var a = Array();
	var msg;
	
	if(input.length > 0){
		for(var i = 0; i < input.length; i++){
			if(input[i].type == 'text' || input[i].type == 'password' || input[i].type == 'textarea'){
				error = checkErr(input[i], input[i].type, {ok:1, error:1});
			}else if(input[i].type == 'radio' || input[i].type == 'checkbox'){
				if(in_array($(input[i]).attr('id'), a)){
					continue;
				}else{
					a.push($(input[i]).attr('id'));
					if(input[i].type == 'checkbox' && $('input[id=' + $(input[i]).attr('id') + '_len]').length > 0){
						var lenArr = $('input[id=' + $(input[i]).attr('id') + '_len]').val().split(',');
						var minChecks = parseInt(lenArr[0]);
						var maxChecks = 0;
						if(lenArr.length > 1) maxChecks = parseInt(lenArr[1]);
						var checks = $(this).find('input[id=' + $(input[i]).attr('id') + ']:checked').length;
						if(checks < minChecks || (maxChecks > 0 && checks > maxChecks)){
							error = Lang.choose + (minChecks > 1 ? (': ' + minChecks + (maxChecks > 0 ? '-' + maxChecks : '') + '項') : '');
						}
					}else{
						if($(this).find('input[id=' + $(input[i]).attr('id') + ']:checked').length == 0){
							error = Lang.choose;
						}
					}
				}
			}else if(input[i].type == 'select-one'){
				var req = $(input[i]).attr('required');
				if(req != "undefined" && $(input[i]).val().length == 0){
					error = Lang.choose;
					showTooltip($('#' + $(input[i]).attr('name') + '_input')[0], error, true);
					return false;
				}
			}else{
				error = '';
			}
			if(error != ''){
				showTooltip(input[i], error, true);
				return false;
			}
		}
	}
	
	if(!submitTime){
		submitTime = thisSubmitTime.getTime();
	}else if(thisSubmitTime.getTime() - submitTime < 2500){
		return false;
	}
	
	try{
		if(typeof(formSubmitAddon) == 'function'){
			return formSubmitAddon();
		}
	}catch(e){}
	return true;
}

function checkErr(obj, type, style){
	var restrict = 1;
	if(type == 'text' || type == 'password'){
		if($(obj).attr('required') != "undefined"){
			var depend = $(obj).attr('depend');
			if(depend != "undefined"){
				restrict = 2;
				var s = depend.split('|');
				var dependon = $('#' + s[0]);
				if(dependon.length > 0){
					if(dependon.attr('type') == 'checkbox'){
						if((s[1]=='nocheck' && dependon.attr('checked')==true) || (s[1]=='checked' && dependon.attr('checked')==false)){
							$(obj).val('');
							restrict = 1;
						}
					}
					//else if{
						//被依賴的其他類型
					//}
					if(restrict == 2){
						restrict = checkRestrict(obj);
					}
				}else{
					restrict = checkRestrict(obj);
				}
			}else{
				restrict = checkRestrict(obj);
			}
			//	if($(obj).attr('name') == 'repassword') $('#msg').text(restrict);
			//}else{
				//restrict = Lang.fillit;
			//}
		}
		var needCompare = $(obj).attr('compare');
		var compare = true;
		if(needCompare != "undefined"){
			if($(obj).val() != $('#' + needCompare).val()) compare = false;
			
		}
		if(restrict == 1 && compare){
			restrict = '';
		}else if(restrict == 1 && !compare){
			restrict = '請輸入一致的內容';
		}
	}else if(type == 'ta' || type == 'textarea'){
		if($(obj).attr('required') != "undefined"){
			restrict = checkRestrict(obj);
		}
	}
	if(restrict == 1) restrict = '';
	if(style.focus != "undefined"){
		inputStyleChanger(obj, type, 'focus');
	}else if(style.error != "undefined" && restrict != ''){
		$(obj).data('error', 1);
		inputStyleChanger(obj, type, 'err');
	}else{
		$(obj).data('error', 0);
		inputStyleChanger(obj, type, 'ok');
	}
	return restrict;
}

function formRebuilder(){
	$('select').not('.special').selectbox();
	$('body').append('<div id="mytt"></div>').children('#mytt')
		.click(function(){$(this).hide(); clearTimeout(tooltipClock);})
		.hover(function () {$(this).addClass("heavy" + $(this).data('reverse'));},
		function () {$(this).removeClass("heavy" + $(this).data('reverse'));});
	$('form').submit(checkSubmit).attr('autocomplete', 'off');
	$(':input').each(function(){bindEventFor(this);});
	/*
	$(':text,:password').not('.selectbox').addClass('textinit text')
		.focus(function(){
			$(this).addClass('textfocus');
			showTooltip($(this)[0], checkErr(this));
		})
		.blur(function(){
			var error = checkErr(this);
			if(error != ''){
				$(this).data('error', 1)
					.removeClass('textok textfocus text')
					.addClass('texterr');
				//showTooltip($(this)[0], error);
				//mod: 不立即顯示錯誤
			}else{
				$(this).data('error', 0)
					.removeClass('textfocus texterr text')
					.addClass('textok');
			}
		});
	*/
	$(':submit,:reset,:button').not('.smallbutton').addClass('defautButton');
	/*
	$('textarea').addClass('textareainit textareainitborder')
		.focus(function(){
			$(this).addClass('textareafocus');
			showTooltip($(this)[0], checkErr(this));
		})
		.blur(function(){
			var error = checkErr(this);
			if(error != ''){
				$(this).data('error', 1)
					.removeClass('textareaok textareafocus textareainitborder')
					.addClass('textareaerr');
				//showTooltip($(this)[0], error);
				//mod: 不立即顯示錯誤
			}else{
				$(this).data('error', 0)
					.removeClass('textareafocus textareaerr textareainitborder')
					.addClass('textareaok');
			}
		});
	*/
}

function bindEventFor(o){
	var type = '';
	if(o.type == 'text' && $(o).not('.selectbox,.noinit').length > 0){
		type = 'text';
	}else if($(o).is(':password')){
		type = 'text';
	}else if(o.type == 'textarea' && !$(o).hasClass('noinit')){
		type = 'ta';
	}
	if(type != ''){
		$(o).addClass(type + 'init ' + type + 'initb')
			.focus(function(){
				showTooltip($(this)[0], checkErr(this, type, {focus:1, error:1}));
			})
			.blur(function(){
				checkErr(this, type, {ok:1, error:1});
			});
	}
}

function inputStyleChanger(o, type, style){
	switch(style){
		case 'err':
			$(o).removeClass(type + 'ok ' + type + 'focus ' + type + 'initb')
				.addClass(type + 'err');
			//showTooltip($(this)[0], error);
			//mod: 不立即顯示錯誤
			break;
		case 'ok':
			$(o).removeClass('' + type + 'focus ' + type + 'err ' + type + 'initb')
				.addClass(type + 'ok');
			break;
		case 'focus':
			$(o).addClass(type + 'focus');
			break;
	}
}

function twValidIdcard(id){
    var city = new Array(1,10,19,28,37,46,55,64,39,73,82, 2,11,20,48,29,38,47,56,65,74,83,21, 3,12,30);
    id = id.toUpperCase();
    if (id.search(/^[A-Z](1|2)\d{8}$/i) == -1) {
        return false;
    } else {
        id = id.split('');
        var total=city[id[0].charCodeAt(0)-65];
        for(var i=1;i<=8;i++){
            total+=eval(id[i]) * (9-i);
        }
        total += eval(id[9]);
        return (total%10==0);
    }
}


