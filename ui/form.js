/*
 * form 自动验证
 * 阻止输入不可接受的字符
 *
 * 用法：
 * 在input中可以设置以下属性:
 *   通用属性:
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
				2011.04.06		adding isfalse() to deal the problem that some new browsers return "undefined" value as "false" / and change all of undefined judge
				2011.06.20		adding 'init' case to inputStyleChanger()
				2011.06.30		fix a bug about password not working with '.noinit', 将变量包装到formVars对象中，添加noCssEffects相关处理, showTooltip中加入对special select的处理
*/

/**************设定值**************/
var formVars = {
	tooltipAutoCloseTime : 6000,	//tooltip 自动消失的时间 单位：秒
	tooltipGoRight : 40,/*80*/			//tooltip 显示偏移. 单位：像素
	tooltipWidth : 276,				//tooltip 宽度. 单位：像素
	tooltipHeight : 35,				//tooltip 高度. 单位：像素
	scrolltop : 200,				//滚动屏幕时，顶部高度. 单位：像素
	submitTime : 0,					//中间时间变量
	reSubmitTime : 2000,			//短于多少毫秒的表单提交视为重複提交，并被忽略
	noCssEffects : false,			//是否对表单应用css效果(init/focus/error)
	keyList : [9, 13, 32, 8, 16, 17, 18, 20, 144, 109, 189, 37, 38, 39, 40, 35, 36, 45, 46, 33, 64, 42, 41, 40, 47] //mod by zjn 加了个 / ，就是47
}

/**************变量**************/
var tooltipClock;
var isfalse = function(v){return v==undefined || v==false}

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
	}else if(!isfalse(key) && (obj.type == "text" || obj.type == "password") && !isfalse($(obj).attr('restrict')) && $(obj).attr('restrict')){
		if(in_array(key, formVars.keyList) || charAllowed(key)){
			if(((evt.shiftKey && key != 16) || !evt.shiftKey) && charAllowed(key)){
				var msg = checkChar($(obj).attr('restrict').toLowerCase(), key);
				if (msg != 1){
					showTooltip(obj, msg);
				}else{
					return true;
				}
			}else if(in_array(key, formVars.keyList)){
				return true;
			}
		}else{
			showTooltip(obj, Lang.rightChar);
		}
		return cancelKeypress(evt);
	}
}

//取消用户非法输入
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
	//form提交时卷动
	if(submiting){
		var s = pos.top - formVars.scrolltop<0 ? 0 : pos.top - formVars.scrolltop;
		$.browser.safari ? $('body').animate({scrollTop:s}, 250, null, function(){try{obj.focus();}catch(e){}}) : jQuery('html').animate({scrollTop: s}, 250, null, function(){try{obj.focus();}catch(e){}});
	}
	left = revise < 0 ? revise : (obj.type == 'radio') ? 30 : formVars.tooltipGoRight;
	if(left > 0 && document.body.clientWidth < formVars.tooltipWidth + pos.left + left)left = 0 - left;
	$('#mytt').removeClass().addClass(left < 0 ? 'ttreverse' : 'ttnormal').data('reverse', (left < 0 ? 'r' : '')).html(msg).css({'top': pos.top - formVars.tooltipHeight, 'left': pos.left + left});
	clearTimeout(tooltipClock);
	tooltipClock = setTimeout('closeTooltip()', formVars.tooltipAutoCloseTime);
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
		//mod 20130131 去掉了-符号
        //20140518 加上了-，因为payment new输入框customer bank charge的received里要用负数
		msg = /[0-9\.-]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '数字与符号 . -';
		break;
		
		case 'letter':
		msg = /[A-Za-z]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文字元';
		break;
		
		case 'date':
		msg = /[0-9\-]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '数字与符号 -';
		break;
		
		case 'card':
		msg = /[A-Za-z0-9\-]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、数字及 -';
		break;
		
		case 'account':
		msg = /[A-Za-z0-9\_]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、数字及 _';
		break;
		
		case 'cnidcard':
		case 'twidcard':
		case 'password':
		case 'scode':
		//msg = /[A-Za-z0-9\!\@\#\$\*\(\)]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、数字及符号!@#$*()';
		msg = /[A-Za-z0-9]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、数字';
		break;
		
		case 'email':
		msg = /[A-Za-z0-9\.\@\-\_]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、数字及符号 @-_.';
		break;
		
		case 'ihridername':
		msg = /[A-Za-z0-9 ]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '中文、英文或数字!';
		break;
		
		case 'judgexid':
		msg = /[A-Za-z0-9\-\/]{1}/.exec(k) ? 1 : Lang.prmptPrefix2 + '英文、数字及 -/';
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
	//alert($(obj).attr('id') + ':' + req);
	if(isfalse(req) && isfalse(type)){
		return 1;
	}else{
		if(!isfalse(strlen)){
			var lenArr = strlen.split(',');
			minLen = lenArr[0];
			if(lenArr.length > 1) maxLen = lenArr[1];
		}else{
			minLen = 3, maxLen = 30;
		}
		lenStr = '{' + minLen + (maxLen ? ',' + maxLen : '') + '}';
		lenMsg = '长度(' + minLen + (maxLen ? '-' + maxLen : '') + ')';
	}
	//if(req != undefined && str.length == 0) return '请填写内容';
	if(isfalse(type)) type = 'nothing';
	switch(type){
		case 'number':
		//mod 20130131 去掉了-符号
        //20140518 加上了-，因为payment new输入框customer bank charge的received里要用负数
		eval('ptn = /^[0-9\.-]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的数字与符号 . -';
		break;
		
		case 'letter':
		eval('ptn = /^[A-Za-z]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的英文字元!';
		break;
		
		case 'date':
		eval('ptn = /^[0-9\-: ]' + lenStr + '$/;');//20130116 加：和空格这两个允许字符
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的日期!';
		break;
		
		case 'card':
		eval('ptn = /^[A-Za-z0-9\-]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的英文、数字及符号 -';
		break;
		
		case 'account':
		eval('ptn = /^[A-Za-z0-9\_]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的英文、数字及 _';
		break;
		
		case 'judgexid':
		eval('ptn = /^[A-Za-z0-9\-\/]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '英文、数字及 -/';
		break;
		
		case 'twidcard':
		msg = twValidIdcard(str) ? 1 : Lang.prmptPrefix + '正确的身份证';
		break;
		
		case 'cnidcard':
		msg = verifyIdCard(str) ? 1 : Lang.prmptPrefix + '正确的18位二代身份证号码';
		break;
		
		case 'password':
		case 'scode':
		//eval('ptn = /^[A-Za-z0-9\!\@\#\$\*\(\)]' + lenStr + '$/;');
		eval('ptn = /^[A-Za-z0-9]' + lenStr + '$/;');
		msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的英文、数字'; //及!@#$*()
		break;
		
		case 'email':
		msg = (str.search(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/) != -1 && str.length < 100) ? 1 : '请填写正确的电子邮箱地址';
		break;
		
		case 'nothing':
		msg = (minLen <= strLength(str) && strLength(str) <= maxLen) ? 1 : Lang.prmptPrefix + lenMsg + '的内容!';
		break;
		
		case 'ihridername':
		msg = (minLen <= strLength(str) && strLength(str) <= maxLen) ? 1 : Lang.prmptPrefix + lenMsg + '中文、英文或数字!';
		break;	
		
		/* 这样的方法用不了的
		case 'productid':
		msg = '';
		$("#p_pid").blur(function(){
			pidText = $("#p_pid").val()
			if(pidText != ''){
				var qs = 'ajax=1&act=ajax-judge_xid&field=p_pid&value='+pidText;
				$.ajax({
					type: "GET",
					url: "index.php",
					data: qs,
					cache: false,
					dataType: "html",
					error: function(){
						alert('系统错误，查询id失败');
					},
					success: function(data){
						if(data.indexOf('yes') >= 0){
							//不输出任何东西
							msg = 1;
						}else if(data.indexOf('no') >= 0){
							msg = 'The ID already exists. Please change another one!';
						}
					}
				})
			}
		})
		break;
		*/
		
		default:
		//eval('ptn = /^[A-Za-z0-9]' + lenStr + '$/;');
		//msg = ptn.exec(str) ? 1 : Lang.prmptPrefix + lenMsg + '的英文字元与数字!';
		msg = 1;
	}
	return msg;
}

function checkSubmit(){
	var thisSubmitTime = new Date();
	var input = $(this).find(':input').not('.selectbox');
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
							error = Lang.choose + (minChecks > 1 ? (': ' + minChecks + (maxChecks > 0 ? '-' + maxChecks : '') + '项') : '');
						}
					}else{
						if($(this).find('input[id=' + $(input[i]).attr('id') + ']:checked').length == 0){
                            //20160605 暂时把radio的选择限制去掉
                            console.log('暂时把radio的选择限制去掉');
							//error = Lang.choose;
						}
					}
				}
			}else if(input[i].type == 'select-one'){
				var req = $(input[i]).attr('required');
				if(!isfalse(req) && input[i].selectedIndex == 0 && ($(input[i]).val()=='' || $(input[i]).val()=='0')){
					error = Lang.choose;
					showTooltip($(input[i]).hasClass('special') ? input[i] : $('#' + $(input[i]).attr('name') + '_input')[0], error, true);
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
	
	if(!formVars.submitTime){
		formVars.submitTime = thisSubmitTime.getTime();
	}else if(thisSubmitTime.getTime() - formVars.submitTime < formVars.reSubmitTime){
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
		if(!isfalse($(obj).attr('required'))){
			var depend = $(obj).attr('depend');
			if(!isfalse(depend)){
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
						//被依赖的其他类型
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
		if(!isfalse(needCompare)){
			if($(obj).val() != $('#' + needCompare).val()) compare = false;
			
		}
		if(restrict == 1 && compare){
			restrict = '';
		}else if(restrict == 1 && !compare){
			restrict = '请输入一致的内容';
		}
	}else if(type == 'ta' || type == 'textarea'){
		if(!isfalse($(obj).attr('required'))){
			restrict = checkRestrict(obj);
		}
	}
	if(restrict == 1) restrict = '';
	if(!isfalse(style.focus)){
		inputStyleChanger(obj, type, 'focus');
	}else if(!isfalse(style.error) && restrict != ''){
		$(obj).data('error', 1);
		inputStyleChanger(obj, type, 'err');
	}else{
		$(obj).data('error', 0);
		inputStyleChanger(obj, type, 'ok');
	}
	return restrict;
}

function formRebuilder(){
	//某些目录下用selectbox就太复杂了，所以不用 mod 20120814
    //20130801 加payment_new
    //20130805 payment_new 里改用 class=special
	if(location.href.indexOf('add_payment_request') < 0 && location.href.indexOf('modify_payment_request') < 0 && location.href.indexOf('sendform') < 0 && location.href.indexOf('modifyform') < 0 && location.href.indexOf('add_material_buy') < 0 && location.href.indexOf('modify_material_buy') < 0 && location.href.indexOf('add_material_in') < 0 && location.href.indexOf('modify_material_in') < 0 && location.href.indexOf('add_material_out') < 0 && location.href.indexOf('modify_material_out') < 0/* && location.href.indexOf('add_payment_new') < 0 && location.href.indexOf('modify_payment_new') < 0*/){
		$('select').not('.special').selectbox();
	}
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
				//mod: 不立即显示错误
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
				//mod: 不立即显示错误
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
	}else if($(o).is(':password') && !$(o).hasClass('noinit')){
		type = 'text';
	}else if(o.type == 'textarea' && !$(o).hasClass('noinit')){
		type = 'ta';
	}
	if(type != ''){
		if(!formVars.noCssEffects){
			$(o).addClass(type + 'init ' + type + 'initb')
		}
		$(o).focus(function(){
				showTooltip($(this)[0], checkErr(this, type, {focus:1, error:1}));
			})
			.blur(function(){
				checkErr(this, type, {ok:1, error:1});
			});
	}
}

function inputStyleChanger(o, type, style){
	if(formVars.noCssEffects) return;
	switch(style){
		case 'err':
			$(o).removeClass(type + 'ok ' + type + 'focus ' + type + 'initb')
				.addClass(type + 'err');
			//showTooltip($(this)[0], error);
			//mod: 不立即显示错误
			break;
		case 'ok':
			$(o).removeClass('' + type + 'focus ' + type + 'err ' + type + 'initb')
				.addClass(type + 'ok');
			break;
		case 'focus':
			$(o).addClass(type + 'focus');
			break;
		case 'init':
			$(o).removeClass('' + type + 'focus ' + type + 'err ')
				.addClass(type + 'initb');
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


//检查身份证:必须是18位
function verifyIdCard(sId){
	var aCity={11:"北京",12:"天津",13:"河北",14:"山西",15:"内蒙古",21:"辽宁",22:"吉林",23:"黑龙江",31:"上海",32:"江苏",33:"浙江",34:"安徽",35:"福建",36:"江西",37:"山东",41:"河南",42:"湖北",43:"湖南",44:"广东",45:"广西",46:"海南",50:"重庆",51:"四川",52:"贵州",53:"云南",54:"西藏",61:"陕西",62:"甘肃",63:"青海",64:"宁夏",65:"新疆",71:"台湾",81:"香港",82:"澳门",91:"国外"};
	var iSum=0;
	var info="";
	var idCardLength = sId.length;
	if(/^\d{17}(\d|x)$/i.test(sId) == false){
		if(/^\d{15}$/i.test(sId) == false){
			return false;
		}
	}
	sId=sId.replace(/x$/i,"a");//在后面的运算中x相当于数字10,所以转换成a  
	if(aCity[parseInt(sId.substr(0,2))]==null)
		{return false;}
	
	sBirthday=sId.substr(6,4)+"-"+Number(sId.substr(10,2))+"-"+Number(sId.substr(12,2));
	var d=new Date(sBirthday.replace(/-/g,"/"));
	if(sBirthday!=(d.getFullYear()+"-"+ (d.getMonth()+1) + "-" + d.getDate()))
		{return false;}
	for(var i = 17;i>=0;i --) iSum += (Math.pow(2,i) % 11) * parseInt(sId.charAt(17 - i),11)
	if(iSum%11!=1)return false;
	return true;
}
