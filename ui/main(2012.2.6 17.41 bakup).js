/*

主js文件

changelog

2011-06-30		去掉generateCode中html的样式，改在main.css中定义

*/

/************* 变量 *****************/
var Lang = {
	sysError: '系统繁忙，请稍后再试',
	fillit: '请填写以下选项',
	choose: '请选择以下选项',
	rightChar: '请输入正确的字元',
	prmptPrefix: '请填写',
	prmptPrefix2: '此项只接受',
	notAvril: '此帐号无法使用，请试其他帐号'
}
var tempVar, tempVar2;
var init = false;
var checkNameInited = false;

/********** 普通函数 ************/
var goto = function(url) { window.location.href = url; }
var strLength = function(str) {  return str.replace(/[^\x00-\xff]/g,"**").length}
var isId = function(s){	return (!isNaN(s) && /^[0-9]+$/.exec(s));}
var vChar = function(s){ return /^[A-Za-z0-9\-]+$/.exec(s);}
var trim =  function(str) {return str.replace(/(^\s*)|(\s*$)/g, ""); }
var getAct = function(){
	var url = window.location.href;
	var start = url.indexOf('act=');
	if(start > -1){
		var next = url.indexOf('&', start + 1);
		var length = next == -1 ? 20 : (next - start - 4);
		return url.substr(start + 4, length);
	}else{
		return '';
	}
}

function G(objName){
	if(document.getElementById){
		return document.getElementById(objName);
	}else if(document.all){
		return eval('document.all.' + objName);
	}else{
		return document.layers[objName];
	}
}

/*
function isAcc(s)
{  
	return /^[A-Za-z]{1}[A-Za-z0-9\-]+$/.exec(s);
}
*/

function in_array(needle, array) {
	for (s = 0; s <array.length; s++) {
		if (array[s] == needle) return true;
	}
	return false;
}


function hasChecked(formId, radioName){
	var former = G(formId);
	for(z=0; z<former.length;z++){
		if((former[z].type == 'radio' || former[z].type == 'checkbox') && former[z].name == radioName){
			if(former[z].checked) return true;
		}
	}
	return false;
}

function outputFlash(url, width, height){
	var flash = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="img/swflash.cab" width="'+ width +'" height="'+ height +'"><param name="movie" value="' + url + '"><param name="quality" value="high"><param name="wmode" value="transparent"><embed src="' + url + '" wmode="transparent" quality="high" type="application/x-shockwave-flash" width="'+ width +'" height="'+ height +'"></embed></object>';
	document.write(flash);
}

function generateCode(scodename){
	var codehtml = '<div id="loadingcode">loading...</div><img src="showcode.php?whatfor='+ scodename +'&ts={ts}" title="点击刷新" align="absmiddle" style="display:none">';
	if($('#' + scodename)){
		$('#' + scodename).parent()
		.append('<div class="scodediv" />')
		.children('#' + scodename)
		.focus(function(){
			var b = $(this).parent().children('.scodediv');
			if(b.children('img').length == 0){
				b.html(codehtml.replace('{ts}', new Date().getTime()))
				.children('img')
				.load(function(){
					$(this).prev().remove();
					$(this).fadeIn('normal');
				})
				.parent()
				.click(function(){
					$(this).html(codehtml.replace('{ts}', new Date().getTime()))
							.children('img')
							.load(function(){
								$(this).prev().remove();
								$(this).fadeIn('normal');
					});
					$('#' + scodename).val('').focus();
				})
				.children('img')
				.load(function(){
					 $(this).fadeIn('normal');
				});
			}
		});
	}
}

/************* init *****************/

function initAll(){
	/**** page init ****/
	//disable Mouse key 2, I dont like it and it's complete useless.
	//document.onmousedown="if (event.button==2) return false"; 
	//document.oncontextmenu=new Function("return false"); 
	document.onkeypress = keyCatcher;
	
	/**** form init ****/
	tooltipAutoCloseTime = 6000;	//tooltip 自动消失的时间 单位：秒
	tooltipGoRight = 50;			//tooltip 显示偏移. 单位：像素
	tooltipWidth = 277;				//tooltip 宽度. 单位：像素
	tooltipHeight = 35;				//tooltip 高度. 单位：像素
	scrolltop = 200;				//滚动屏幕时，顶部高度. 单位：像素
	formRebuilder();
	
	//解决IE6背景闪烁
	var m = document.uniqueID && document.compatMode && !window.XMLHttpRequest && document.execCommand;
    try{
        if(!!m){
            m("BackgroundImageCache", false, true);
        }
    }catch(e){};
	
	// 检查用户是否存在(for go signin, or game active)
	$('div.checkname')
		.html('<a href="javascript:void(0);">检查帐号是否可用?</a>')
		.children('a')
		.click(function(){
			tempVar2 = $(this).parent();
			tempVar = tempVar2.attr('id').split('_');
			var val = $('#' + tempVar[1]).val();
			if(val == undefined || val == ''){
				$('#' + tempVar[1]).focus();
				return false;
			}
			
			if(!checkNameInited){
				if (window.attachEvent){
					$('#' + tempVar[1])[0].attachEvent('onpropertychange', initOnchange);
				}
				if (window.addEventListener) {
					$('#' + tempVar[1])[0].addEventListener('input', initOnchange, false);
				}
				checkNameInited = true;
			}
			
			if($('#' + tempVar[1]).data('error') == 0 && (tempVar2.data('checking') == undefined || tempVar2.data('checking') < 1)){
				tempVar2.data('checking', 1)
					.append('<img src="/images/loading_g2.gif" width="16" height="16" />')
					.children('strong').remove();
				var qs = 'ajax=1&act=' + tempVar[0] + '&' + tempVar[1] + '=' + val;
				$.ajax({
				   type: "GET",
				   url: "index.php",
				   data: qs,
				   dataType: "html",
				   cache: false,
				   error: function(){
						showTooltip($('#' + tempVar[1])[0], Lang.sysError);
						inputStyleChanger($('#' + tempVar[1])[0], 'text', 'err');
						tempVar2.data('checking', 0);
				   },
				   success: function(data){
					   if(data == 'ok'){
							$('.checkname a').hide().parent().children('img').replaceWith('<strong class="checkok">恭喜! 此名称可注册</strong>');
							closeTooltip();
					   }else{
							showTooltip($('#' + tempVar[1])[0], Lang.notAvril);
							inputStyleChanger($('#' + tempVar[1])[0], 'text', 'err');
							$('.checkname img').remove();
					   }
					   tempVar2.data('checking', -1);
				   }
				 });
			}//else if($('#' + tempVar[1]).data('error') != 0){
				//$('#' + tempVar[1]).focus();
			//}
		});
	$('#leftmenu dt')
		.addClass('origin')
		.hover(function(){$(this).addClass('lihover').removeClass('origin');},
				function(){$(this).addClass('origin').removeClass('lihover');})
		.click(function(){
			if(!$(this).next().is(':visible')){
				$('#leftmenu dd:visible').slideUp(200);
				$(this).next().slideDown('fast');
			}
		});
	if($('#pagetitle').length){
		$('#leftmenu a').each(function(){
			if($(this).text() == $('#pagetitle').text()){
				$('dl#leftmenu').show();
				$(this).css('color', '#ce0000').closest('dd').prev().click();
				return false;
			}
		});
	}else if(getAct() == 'main'){
		$('dl#leftmenu').show();
		$('li#default a').css('color', '#ce0000').closest('dd').prev().click();
	}
	$('img.mopic')
		.hover(function(){$(this).attr('src', $(this).attr('src').replace('.jpg', 'b.jpg'));},
				function(){$(this).attr('src', $(this).attr('src').replace('b.jpg', '.jpg'));});
	$('div.Pages').each(function(){
		if($(this).attr('id') == undefined) return;
		$(this).find('a').click(gotoPage);
	});
	$('.choosepage').click(function(){
		var input = $(this).prev();
		if(input.is('input')){
			if(input.val().length > 0){
				gotoPage(input.get(0));
			}else{
				input.focus();
			}
		}
	});
	if($('div.winopener').length){
		$('div.winopener a').click(function(){
			var v=$(this).text();
			var u=$(this).attr('href');
			var source=$('#sourceid_input');
			var w = $(this).attr('winwidth') ? $(this).attr('winwidth') : 550;
			var h = $(this).attr('winheight') ? $(this).attr('winheight') : 375;
			var top = (window.screen.availHeight-30-h)/2;
			var left = (window.screen.availWidth-10-w)/2;
			source.next().find('li').each(function(){
				if($(this).text() == v){
					source.click();
					$(this).click();
				}
			});
			window.open(u, 'gopopwin', 'height=' + h + ',width=' + w + ',top=' + top + ',left=' + left + ',toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
			return false;
		});
	}
	$('#helper')/*.hover(function(){ showTooltip(this, 'GO点数错误? 点击刷新', true, -270);},
						function(){
							//closeTooltip();
						})*/
				.click(function(){
					$(this).attr('src', '/images/loading3.gif');
					var qs = 'ajax=1&act=ajax-refreshgopoint';
					$.ajax({
					   type: "GET",
					   url: "index.php",
					   data: qs,
					   cache: false,
					   dataType: "html",
					   error: function(){
							showTooltip($('#helper')[0], '查询时发生错误，请重试', true, -270);
							$('#helper').attr('src', '/images/helper1.gif');
							//inputStyleChanger($('#' + tempVar[1])[0], 'text', 'err');
							//tempVar2.data('checking', 0);
					   },
					   success: function(data){
							if(!isNaN(data)){
								$('#thepoints').text(data).css('color', '#0000ff');
								showTooltip($('#helper')[0], 'GO点数刷新成功，这是您现有的点数', true, -270);
							}else{
								showTooltip($('#helper')[0], '查询出错，请重试', true, -270);
							}
							$('#helper').attr('src', '/images/helper1.gif');
					   }
					 });
				});
				
	$(function(){
		var x = 10;
		var y = 20;
		$("a.tooltip").mouseover(function(e){
			this.myTitle = this.title;
			this.title = "";	
			var imgTitle = this.myTitle? "<br/>" + this.myTitle : "";
			var tooltip = "<div id='tooltip'><img src='"+ this.href +"' alt='产品预览图'/>"+imgTitle+"<\/div>"; //创建 div 元素
			$("body").append(tooltip);	//把它追加到文档中						 
			$("#tooltip")
				.css({
					"top": (e.pageY+y) + "px",
					"left":  (e.pageX+x)  + "px"
				}).show("fast");	  //设置x坐标和y坐标，并且显示
		}).mouseout(function(){
			this.title = this.myTitle;	
			$("#tooltip").remove();	 //移除 
		}).mousemove(function(e){
			$("#tooltip")
				.css({
					"top": (e.pageY+y) + "px",
					"left":  (e.pageX+x)  + "px"
				});
		});
	});
	
	$(function(){
		var x = 10;
		var y = 20;
		$("a.tooltip2").mouseover(function(e){
			this.myTitle = this.title;
			this.title = "";	
			var imgTitle = this.myTitle? "<br/>" + this.myTitle : "";
			/* 預覽圖指定了640X480 是因為有的圖太大了 */
			var tooltip2 = "<div id='tooltip2'><img src='"+ this.href +"' alt='产品预览图' width='640' herght='480'/>"+imgTitle+"<\/div>"; //创建 div 元素
			$("body").append(tooltip2);	//把它追加到文档中						 
			$("#tooltip2")
				.css({
					"top": (e.pageY+y) + "px",
					"left":  (e.pageX+x)  + "px"
				}).show("fast");	  //设置x坐标和y坐标，并且显示
		}).mouseout(function(){
			this.title = this.myTitle;	
			$("#tooltip2").remove();	 //移除 
		}).mousemove(function(e){
			$("#tooltip2")
				.css({
					"top": (e.pageY+y) + "px",
					"left":  (e.pageX+x)  + "px"
				});
		});
	});

	$("document").ready(function(){ 
	 
		//第六个表格的删除按钮事件绑定   
		$("#tbody6 .del").click(function() {   
			$(this).parents(".repeat").remove();   
		});   
		
		//第六个表格的添加按钮事件绑定   
		$("#add6").click(function(){  
			//所有的数据行有一个.repeat的Class，得到数据行的大小
			var vcount=$("#tbody6 tr").filter(".repeat").size();
			$("#tbody6>.template")   
				//连同事件一起复制   
				.clone(true)   
				//去除模板标记   
				.removeClass("template")   
				//修改内部元素   
				//.find(".content")   
					//.text("新增行")   
					//.end()  
				.find("#divg_m_type").attr("id","divg_m_type"+vcount).end()
				.find("#g_m_type")
					.attr("tabindex", vcount*4+9)
					.attr("id","g_m_type"+vcount)//這裡end了，下面就修改不了了哦，不管是否重新 .find("#g_m_type")
					.attr("name","g_m_type"+vcount).end()
				.find("#g_m_type_input")
					.attr("tabindex", vcount*4+9)
					.attr("id","g_m_type"+vcount+"_input").end()
				.find("#g_m_type_container").attr("id","g_m_type"+vcount+"_container").end()
				
				.find("#g_m_id")
					.attr("tabindex", vcount*4+10)
					.attr("id","g_m_id"+vcount)
					.attr("name","g_m_id"+vcount).end()
				.find("#g_m_id_input")
					.attr("tabindex", vcount*4+10)
					.attr("id","g_m_id_input"+vcount).end()
				.find("#g_m_id_container").attr("id","g_m_id_container"+vcount).end()
				
				.find("#g_m_price")
					.attr("tabindex", vcount*4+11)
					.attr("id","g_m_type"+vcount).end()
				.find("#g_m_value")
					.attr("tabindex", vcount*4+12)
					.attr("id","g_m_value"+vcount).end()
			    .find(".del").text("*删除*").end()        
				//插入表格   
				.appendTo($("#tbody6"))   
		});   
	}); 
	
	//屏蔽回車提交表單，IE没用。。。
	/*
	$(function() {  
        $("input").keypress(function (e) {  
            var keyCode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;  
            if (keyCode == 13) {  
                for (var i = 0; i < this.form.elements.length; i++) {  
                    if (this == this.form.elements[i]) break;  
                }  
                i = (i + 1) % this.form.elements.length;  
                this.form.elements[i].focus();  
                return false;  
            } else {  
                return true;  
            }  
		});  
    });
	*/
	$(function(){
    	//禁用Enter键表单自动提交  
        document.onkeydown = function(event) {  
            var target, code, tag;  
            if (!event) {  
                event = window.event; //针对ie浏览器  
                target = event.srcElement;  
                code = event.keyCode;  
                if (code == 13) {  
                    tag = target.tagName;  
                    if (tag == "TEXTAREA") { return true; }  
                    else { return false; }  
                }  
            }  
            else {  
                target = event.target; //针对遵循w3c标准的浏览器，如Firefox  
                code = event.keyCode;  
                if (code == 13) {  
                    tag = target.tagName;  
                    if (tag == "INPUT") { return false; }  
                    else { return true; }   
                }  
            }  
        };	
	});
	 
	init = true;
}

function gotoPage(){
	if(arguments.length == 1 && arguments[0].type != undefined && arguments[0].type == 'text'){
		var o = arguments[0];
		var page = $(o).val();
		var form = $(o).closest('.Pages');
	}else{
		var page = $(this).attr('page');
		var form = $(this).closest('.Pages');
	}
	if(isNaN(page) || !parseInt(page)) return ;
	if(form.attr('id') == undefined) return;
	var formId = form.attr('id').split('_');
	if(formId.length == 2 && $('#' + formId[1]) && $('#' + formId[1] + ' #' + formId[1] + 'page')){
		$('#' + formId[1] + ' #' + formId[1] + 'page').val(page);
		$('#' + formId[1]).trigger("submit");
	}
	return false;
}

var initOnchange = function(e) {
	var data = $('.checkname').data('checking');
	if(data != -1) return;
	if(!$.browser.msie || e.propertyName == 'value')
		$('.checkname a').css('display', '').next().remove();
}

$(function(){
	initAll();
});








function UpdateTotal()//更新总金额
{
	var vTotalMoney=0;//总金额的初始值为0;               
	var vTable=$("#tbody");//得到表格的jquery对象
	var vTotal = $("#total"); //得到总金额对象
	var vtxtAfters=vTable.find("#sub");//得到所有的费用对象;
	vtxtAfters.each(
		function(i)
		{
			var vTempValue=$(this).html();	
			vTempValue = vTempValue.replace(/,/g,"");
			if(vTempValue=="")
			{
				vTempValue=0;
			} 
			//这了用accAdd就不行。。。
			vTotalMoney=vTotalMoney + parseFloat(vTempValue);//這個parseFloat，好像會出好多小數點後面的0
		}
	)//遍历结束
	vTotal.html(formatCurrency(vTotalMoney));  
}

//为product qunantity绑定blur更新total事件
function quantityBlur(attr_id){
	$("#"+attr_id).blur(function(e){
		var rmb_input = $(this).parent().parent().next().children().children();
		var sub_td = rmb_input.parent().parent().next();
		var new_price = rmb_input.val().replace(/,/g,"");
		sub_td.html( accMul(parseFloat(new_price), parseFloat(/* 这里面居然不能用this或是$(this)不知道为什么。。。*/$("#"+attr_id).val())) );	
		UpdateTotal();//更新總和
	})
}

//为product price绑定blur更新total事件
function priceBlur(attr_id){
	$("#"+attr_id).blur(function(e){
		//这里每一次都要先执行第一行的blur事件，再执行当前行的blur事件，暂时还不知道是为什么
		var qut_input = $(this).parent().parent().prev().children().children();
		var sub_td = $(this).parent().parent().next();
		//居然 $(this) 和 $("#"+attr_id) 代表的内容是不同的。。。。（以上兩個問題都是由於將JS代碼放在<tr></tr>中才造成的吧，現在正常了。。。）
		$("#"+attr_id).val(formatCurrency($("#"+attr_id).val()));
		var new_price = $(this).val().replace(/,/g,""); 
		sub_td.html( accMul(parseFloat(new_price), parseFloat(qut_input.val())) );	
		UpdateTotal();//更新總和
	})
}

function SearchPid(i)
{
	//Search功能實現*********start*******
	$("#q_pid"+i).focus(function(e){
		var pid_input = $(this);//这里是关键，clone后都能有效！！
		//用了blur就無法選擇搜索到的項，因為失去焦點就什麼都清空了，但是不用autocomplete又會越來越多。。。
		//現使用回車觸發
		pid_input.keydown(function(e){
			if( e.which==13){						
				//zjn add
				//setTimeout(autocomplete.remove(), 500);
				autocomplete.remove();
			}
		})
		//关闭浏览器提供给输入框的自动完成
		pid_input.attr('autocomplete', 'off');
		//创建自动完成的下拉列表，用于显示服务器返回的数据,插入在搜索按钮的后面，等显示的时候再调整位置
		var autocomplete = $('<div class="autocomplete"></div>').hide().insertAfter(pid_input);//這裡的插入this下也是關鍵哦！！
		//清空下拉列表的内容并且隐藏下拉列表区
		var clear = function() {
			autocomplete.empty().hide();
		};
		//注册事件，当输入框失去焦点的时候清空下拉列表并隐藏
		pid_input.blur(function() {
			setTimeout(clear, 300);
		});
		//下拉列表中高亮的项目的索引，当显示下拉列表项的时候，移动鼠标或者键盘的上下键就会移动高亮的项目，想百度搜索那样
		var selectedItem = null;
		//timeout的ID
		var timeoutid = null;
		//设置下拉项的高亮背景
		var setSelectedItem = function(item) {
			//更新索引变量
			selectedItem = item;
			//按上下键是循环显示的，小于0就置成最大的值，大于最大值就置成0
			if (selectedItem < 0) {
				selectedItem = autocomplete.find('li').length - 1;
			}
			else if (selectedItem > autocomplete.find('li').length - 1) {
				selectedItem = 0;
			}
			//首先移除其他列表项的高亮背景，然后再高亮当前索引的背景
			autocomplete.find('li').removeClass('highlight').eq(selectedItem).addClass('highlight');
		};
		var ajax_request = function() {
			//ajax服务端通信
			var qs = 'ajax=1&act=ajax-search_pid&search_text='+pid_input.val();
			$.ajax({
				type: "GET",
				//async: false,//取消異步，因為老是多次請求（沒用）
				url: "index.php",
				//服务器的地址
				data: qs,
				//参数
				dataType: "json",
				//返回数据类型
				success: function(data) {
					if (data.length) {
						//遍历data，添加到自动完成区
						$.each(data,
						function(index, term) {
							//创建li标签,添加到下拉列表中
							//要這樣在IE下才兼容！！！！！！！！！！
							$('<li>'+term+'</li>')./*text(term).*/appendTo(autocomplete).addClass('clickable').hover(function() {
								//下拉列表每一项的事件，鼠标移进去的操作
								$(this).siblings().removeClass('highlight');
								$(this).addClass('highlight');
								selectedItem = index;
							},
							function() {
								//下拉列表每一项的事件，鼠标离开的操作
								$(this).removeClass('highlight');
								//当鼠标离开时索引置-1，当作标记
								selectedItem = -1;
							}).click(function() {
								//鼠标单击下拉列表的这一项的话，就将这一项的值添加到输入框中
								pid_input.val(term);
								//zjn add 為了使用戶還要blur一次，這樣數據才能更新
								pid_input.focus();
								//清空并隐藏下拉列表
								//autocomplete.empty().hide();
								//zjn add!!!
								autocomplete.remove();
							})
						}); //事件注册完毕
						//设置下拉列表的位置，然后显示下拉列表
						var ypos = pid_input.position().top + 29;
						var xpos = pid_input.position().left;
						//pid_input.css('width') 獲取到的是175px，所以parseInt後才能加數字
						autocomplete.css('width', parseInt(pid_input.css('width'))+25);
						autocomplete.css({
							//'position': 'relative',
							'position': 'absolute',//之前這裡忘了改了，太鬱悶了。。。
							'left': xpos + "px",
							'top': ypos + "px"
						});
						setSelectedItem(0);
						//显示下拉列表
						autocomplete.show();
					}
				}
			});
		};
		//对输入框进行事件注册
		pid_input.keyup(function(event) {
			//字母数字，退格，空格
			if (event.keyCode > 40 || event.keyCode == 8 || event.keyCode == 32) {
				//首先删除下拉列表中的信息
				autocomplete.empty().hide();
		
				clearTimeout(timeoutid);
				timeoutid = setTimeout(ajax_request, 600);
			}
			else if (event.keyCode == 38) {
				//上
				//selectedItem = -1 代表鼠标离开
				if (selectedItem == -1) {
					setSelectedItem(autocomplete.find('li').length - 1);
				}
				else {
					//索引减1
					setSelectedItem(selectedItem - 1);
				}
				//上下移动也加了把选中的赋值给输入框，这是参考baidu的做法，这样实现了enter后选中和搜索同时进行了
				pid_input.val(autocomplete.find('li').eq(selectedItem).text());
				event.preventDefault();
			}
			else if (event.keyCode == 40) {
				//下
				//selectedItem = -1 代表鼠标离开
				if (selectedItem == -1) {
					setSelectedItem(0);
				}
				else {
					//索引加1
					setSelectedItem(selectedItem + 1);
				}
				pid_input.val(autocomplete.find('li').eq(selectedItem).text());
				event.preventDefault();
			}
		})
		//之前想去掉enter选定product ID的设定，因为与前面的enter时间有点冲突。。。
		//现在改为shift按下事件
		.keydown(function(event) {
			//shift键按下事件
			//if (event.shiftKey == true) {
			//因为kevin提出不便，参考baidu的上下键就能赋值给输入框，这样就能使用enter，同时search了	
			if (e.which==13) {	
				//列表为空或者鼠标离开导致当前没有索引值
				if (autocomplete.find('li').length == 0 || selectedItem == -1) {
					return;
				}
				pid_input.val(autocomplete.find('li').eq(selectedItem).text());
				autocomplete.empty().hide();
				event.preventDefault();
			}
		})
		
		.keydown(function(event) {
			//esc键
			if (event.keyCode == 27) {
				autocomplete.empty().hide();
				event.preventDefault();
			}
		});
		//注册窗口大小改变的事件，重新调整下拉列表的位置
		$(window).resize(function() {
			var ypos = pid_input.position().top + 29;
			var xpos = pid_input.position().left;
			autocomplete.css('width', parseInt(pid_input.css('width'))+25);
			autocomplete.css({
				//'position': 'relative',
				'position': 'absolute',//改用绝对的IE反而行了。。。
				'left': xpos + "px",
				'top': ypos + "px"
			});
		});	
	});
	//Search功能實現*********end*******		
}

//查詢Product ID 以往的開價記錄
function searchPriceHistory(i, sign){
	$("#q_pid"+i).keydown(function(e){
		var pid_input = $(this);//这里是关键，clone后都能有效！！
		var pid_var = pid_input.val();
		//shift键按下事件
		if (e.shiftKey == true) {
			if(pid_var != ''){
				qs = 'ajax='+sign+'&act=ajax-search_price_history&pid='+pid_var;
				goto_url = 'model/com/'+sign+'_pid_history.php?pid='+pid_var;
				$.ajax({
					type: "GET",
					url: "index.php",
					data: qs,
					cache: false,
					dataType: "html",
					error: function(){
						alert('系统错误，查询pid失败');
					},
					success: function(data){
						if(data.indexOf('no-') < 0){
							//打開一個新頁面，顯示pid報價的歷史信息
							window.open (goto_url,'lux','height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no') 
						}else{
							alert('No Result！');
						}
					}
				})	
			}
		}
	})
	
	//代码重复，能不能简化呢。。。
	//为了方便键盘操作的习惯，enter以后光标会移到description，所以加了这个框的按下shift事件
	$("#q_p_description"+i).keydown(function(e){
		var pid_input = $(this).parent().parent().prev().children().children();;//这里是关键，clone后都能有效！！
		var pid_var = pid_input.val();
		//shift键按下事件
		if (e.shiftKey == true) {
			if(pid_var != ''){
				qs = 'ajax='+sign+'&act=ajax-search_price_history&pid='+pid_var;
				goto_url = 'model/com/'+sign+'_pid_history.php?pid='+pid_var;
				$.ajax({
					type: "GET",
					url: "index.php",
					data: qs,
					cache: false,
					dataType: "html",
					error: function(){
						alert('系统错误，查询pid失败');
					},
					success: function(data){
						if(data.indexOf('no-') < 0){
							//打開一個新頁面，顯示pid報價的歷史信息
							window.open (goto_url,'lux','height=400,width=800,top=0,left=0,toolbar=no,menubar=no,scrollbars=yes, resizable=yes,location=no,status=no') 
						}else{
							alert('No Result！');
						}
					}
				})	
			}
		}
	})
}

/* add的时候编号主键的是否重复的ajax判断 */
function judgeXid(xid){
	$("#"+xid).blur(function(){
		xidText = $("#"+xid).val()
		if(xidText != ''){
			var qs = 'ajax=1&act=ajax-judge_xid&field='+xid+'&value='+xidText;
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
					}else if(data.indexOf('no') >= 0){
						alert('此ID已存在！请换一个');
					}
				}
			})
		}
	})
}

/**
 * 搜索 Product，原来放在外面，现在放在函数里，方便调用
 *
 * @param num: 为了方便处理不同的表单的Input product的tabindex
 *        i: 记录当前是product ID 的id属性号
 * @return 
 * @type 
 */
function searchProduct(num, i){
	
	//從url來判斷当前的是那个页面
	var local_url = location.href;
	//for search price
	var sign = '';
	//for judge xid
	var xid = '';
	if(local_url.indexOf('quotation') > 0){
		sign = 'quotation';
		xid = 'q_qid';
	}else if(local_url.indexOf('proforma') > 0){
		sign = 'proforma';
		xid = 'pi_pvid';
	}else if(local_url.indexOf('purchase') > 0){
		sign = 'purchase';
		xid = 'pc_pcid';
	}else if(local_url.indexOf('invoice') > 0){
		sign = 'invoice';
		xid = 'i_vid';
	}

	//数据库id唯一，所以事先判断是否已存在，以防表都填好后，提交却失败，又要重新填写
	//add時才判斷，modify的都是已有的ID
	if(local_url.indexOf('com-add') > 0){
		judgeXid(xid);
	}
	
	//輸入框Search
	SearchPid(i);
	
	//查詢Product ID 以往的開價記錄
	searchPriceHistory(i, sign);
	
	//放外面是為了modify的時候可以用
	quantityBlur('q_p_quantity'+i);
	priceBlur('q_p_price'+i);
	
	//輸入框的失去焦点事件綁定，想綁定回車，但是IE下不行，一回車就連表單都提交了
	//以禁用了IE的enter提交表单，所以改回绑定enter事件
	$("#q_pid"+i).keydown(function(e){ 
		if( e.which==13){
	
			//Product ID輸入框
			var pid_input = $(this);//这里是关键，clone后都能有效！！	
	
			//Description輸入框
			var des_input = pid_input.parent().parent().next().children().children();
			//Quantity輸入框
			var qut_input = des_input.parent().parent().next().children().children();
			//Cost RMB輸入框
			var rmb_input = qut_input.parent().parent().next().children().children();
			
			// enter觸發能註冊blur事件，是為了add的時候用
			quantityBlur(qut_input.attr('id'));
			priceBlur(rmb_input.attr('id'));
			/*
			//Cost RMB输入框绑定失去焦点事件，为计算subtotal	
			rmb_input.blur(function(e){
				rmb_input.val(formatCurrency(rmb_input.val()))
				var new_price = rmb_input.val().replace(/,/g,""); 
				sub_td.html( accMul(parseFloat(new_price), parseFloat(qut_input.val())) );	
				UpdateTotal();//更新總和
			})
			//Quantity输入框绑定失去焦点事件，为计算subtotal				
			qut_input.blur(function(e){
				var new_price = rmb_input.val().replace(/,/g,"");
				sub_td.html( accMul(parseFloat(new_price), parseFloat(qut_input.val())) );	
				UpdateTotal();//更新總和
			})
			*/
			//Product Remark輸入框
			//var pmk_input = qut_input.parent().parent().next().children().children();			
	
			//photos隱藏輸入框
			var pt_input = rmb_input.parent().next();
			//ccode隱藏輸入框
			var cc_input = pt_input.next();
			//scode隱藏輸入框
			var sc_input = cc_input.next();
			
			//subtotal 显示处
			var sub_td = rmb_input.parent().parent().next();
			//Photo位置
			var photo_td = sub_td.next();
			//DEL 位置
			var del_div = photo_td.next().children();
			
			//取得输入框中的pid
			var pid_text = pid_input.val();
			//所有的数据行有一个.repeat的Class，得到数据行的大小
			var vcount=$("#tbody tr").filter(".repeat").size();
			
			//如过不允许修改product ID, 只能删除的话，就没有修改前面的product ID，tabindex混乱的问题了
			//当modify时，修改之前的product ID，而不是新增，则是再写入的tabindex值与原来保持一致
			/*
			if(i == '' || i >= vcount){
				var pid_tabindex_new = num+4*vcount;
				var pid_tabindex = num+4*(vcount-1)
				var des_tabindex = (num+1)+4*(vcount-1);
				var qut_tabindex = (num+2)+4*(vcount-1);
				var rmb_tabindex = (num+3)+4*(vcount-1);
			}else{
				var pid_tabindex_new = num+4*i;
				var pid_tabindex = num+4*(i-1)
				var des_tabindex = (num+1)+4*(i-1);
				var qut_tabindex = (num+2)+4*(i-1);
				var rmb_tabindex = (num+3)+4*(i-1);
			}
			*/
			var pid_tabindex_new = num+4*vcount;
			var pid_tabindex = num+4*(vcount-1)
			var des_tabindex = (num+1)+4*(vcount-1);
			var qut_tabindex = (num+2)+4*(vcount-1);
			var rmb_tabindex = (num+3)+4*(vcount-1);
			
			if( pid_text != ''){
				var qs = 'ajax=1&act=ajax-choose_product&value='+pid_text;
				$.ajax({
					type: "GET",
					url: "index.php",
					data: qs,
					cache: false,
					dataType: "html",
					error: function(){
						alert('系统错误，查询product失败');
					},
					success: function(data){
						if(data != 'no'){
							//先複製框，再在原來的框中插入值
							$("#tbody>.template")   
								//连同事件一起复制   
								.clone(true)   
								//去除模板标记   
								.removeClass("template")   
								//給id附新的值
								.find("#q_pid").attr("id","q_pid"+vcount).attr("name","q_pid"+vcount).attr("tabindex", pid_tabindex_new).end()
								.find("#q_p_price").attr("id","q_p_price"+vcount).attr("name","q_p_price"+vcount).addClass("disabled").attr("disabled", "disabled").end()
								.find("#q_p_quantity").attr("id","q_p_quantity"+vcount).attr("name","q_p_quantity"+vcount).addClass("disabled").attr("disabled", "disabled").end()
								//.find("#q_p_remark").attr("id","q_p_remark"+vcount).attr("name","q_p_remark"+vcount).addClass("disabled").attr("disabled", "disabled").end()
								.find("#q_p_description").attr("id","q_p_description"+vcount).attr("name","q_p_description"+vcount).addClass("disabled").attr("disabled", "disabled").end()
								.find("#q_p_photos").attr("id","q_p_photos"+vcount).attr("name","q_p_photos"+vcount).addClass("disabled").attr("disabled", "disabled").end()
								.find("#q_p_ccode").attr("id","q_p_ccode"+vcount).attr("name","q_p_ccode"+vcount).addClass("disabled").attr("disabled", "disabled").end()
								.find("#q_p_scode").attr("id","q_p_scode"+vcount).attr("name","q_p_scode"+vcount).addClass("disabled").attr("disabled", "disabled").end()
								
								//去掉autocomplete
								.find(".autocomplete").remove().end()
								
								//清空clone過來的輸入框中的值   
								.find("input").val("").removeClass("textfocus").end() 
								.find("textarea").val("").end()
								.find("img").remove().end() 
								.find("#sub").html('').end()
								//按鈕換成圖標了
								//.find(".del").html("<input class='defautButton' name='' type='button' value='Del' />").end()     
								.find(".del").html('<img src="../../sys/images/del-icon.png" />').end()
								//插入表格   
								.appendTo($("#tbody"))
							
							var data_array = data.split("|");
							//加tabindex是为了能使用tab键在按tabindex的顺序转到下一个，这里由于remarks和submit的tabindex值在pid_input之上，所以，会先经过remarks和submit，才到第一个pid_input。。。
							
							//当enter入了一个product ID后，就不能修改product ID了，设置成readonly，要修改就只能删除了这一行
							if(pid_input.attr("id") != 'q_pid'){
								pid_input.attr("readonly", "readonly").addClass("readonly").end();
							}
							
							pid_input.attr("tabindex", pid_tabindex).end();
							rmb_input.removeClass("disabled").removeAttr("disabled").attr("tabindex", rmb_tabindex).val(data_array[0]).end();
							qut_input.removeClass("disabled").removeAttr("disabled").attr("tabindex", qut_tabindex).val( (qut_input.val()==0 || qut_input.val() == '')?1:qut_input.val()).end();//为0或空值的时候改为1
							//pmk_input.removeClass("disabled").removeAttr("disabled");
							des_input.removeClass("disabled").removeAttr("disabled").attr("tabindex", des_tabindex).val(data_array[1]).end();
							//光标移到des的文字的最后面
							var t=des_input.val(); 
							des_input.val("").focus().val(t);
							
							pt_input.removeClass("disabled").removeAttr("disabled").val(data_array[3]).end();
							cc_input.removeClass("disabled").removeAttr("disabled").val(data_array[4]).end();
							sc_input.removeClass("disabled").removeAttr("disabled").val(data_array[5]).end();
							
							photo_td.html(data_array[2]);
							//第一个加了del真的很麻烦，如果第一个被删掉了，以后就不能copy出新的行了，所以，如果第一个行写错了，可以把第二行的数据复制到第一行，然后删了第二行。。。
							//del_div.html("<input class='defautButton' name='' type='button' value='Del' />").end();
							sub_td.html( accMul(parseFloat(rmb_input.val()), parseFloat(qut_input.val())) );
							//更新總和
							UpdateTotal();
						}else{
							//屏蔽掉是因為 search 選擇了一個id後，就會彈出這個，它把之前輸入的不完整id做判斷，而非選擇後的
							//綁定enter事件後就可以用這個來提示了
							//为了方便，enter直接选定product ID，所以又把这个提示去掉了
							//alert('请输入正确的Product ID');
							pid_input.val("")
							des_input.val("")
							qut_input.val("")
							rmb_input.val("")
							pid_input.html("")
							sub_td.html("")
						}
					}
				});
			}
		}
	});
	
	//為選擇product的Del删除按钮事件绑定   
	$("#tbody .del").click(function() {   
		$(this).parents(".repeat").remove(); 
		UpdateTotal();
	});
	//為選擇product的Del All删除按钮事件绑定   
	$("#tbody .del_all").click(function() {
		$(".template")
			.find("input").val("").end()
			.find("textarea").val("").end()
			.find("img").remove().end()
			.find("#q_p_price").addClass("disabled").attr("disabled", "disabled").end()
			.find("#q_p_quantity").addClass("disabled").attr("disabled", "disabled").end()
			//.find("#q_p_remark").addClass("disabled").attr("disabled", "disabled").end()
			.find("#q_p_description").addClass("disabled").attr("disabled", "disabled").end()
			.find("#q_p_photos").addClass("disabled").attr("disabled", "disabled").end()
			.find("#q_p_ccode").addClass("disabled").attr("disabled", "disabled").end()
			.find("#q_p_scode").addClass("disabled").attr("disabled", "disabled").end()
		$(".repeat").not(".template").remove();
		$(".autocomplete").remove();//這個是搜索的時候留下的
		$("#sub").html('');
		UpdateTotal();   
	});     
		
}

//为消除js计算的bug，就是浮点数相加或相乘有时会出现很多位浮点的bug
function accMul(arg1,arg2)
{
	//return formatCurrency(arg1*arg2)
//最近没有出现多位尾数的情况了，先去掉，以后出现了多位尾数再用吧（还是不能直接相加或相乘，因为居然出现了不能加出total的情况，所以还是用网上的方法吧）
  var m=0,s1=arg1.toString(),s2=arg2.toString();
  try{m+=s1.split(".")[1].length}catch(e){}
  try{m+=s2.split(".")[1].length}catch(e){}
  return formatCurrency(Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m))

}

function accAdd(arg1,arg2){
	//return formatCurrency(arg1+arg2)
	
  var r1,r2,m;
  try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
  try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
  m=Math.pow(10,Math.max(r1,r2))
  return formatCurrency((arg1*m+arg2*m)/m)
}

/**
 * 将数值四舍五入(保留2位小数)后格式化成金额形式
 *
 * @param num 数值(Number或者String)
 * @return 金额格式的字符串,如'1,234,567.45'
 * @type String
 */
function formatCurrency(num) {
    num = num.toString().replace(/\$|\,/g,'');
    if(isNaN(num))
    	num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
    	cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
    	num = num.substring(0,num.length-(4*i+3))+','+num.substring(num.length-(4*i+3));
    return (((sign)?'':'-') + num + '.' + cents);
}


/* 選擇customer 的ajax */
function selectCustomer(pre){
	$("#"+pre+"cid_container li").click(function(){
		selectText = $("#"+pre+"cid_container li").parent().parent().prev().val()
		attentionSelect = $("#"+pre+"attention");
		var qs = 'ajax=customer&act=ajax-search_contact_name&value='+selectText;
		$.ajax({
			type: "GET",
			url: "index.php",
			data: qs,
			cache: false,
			dataType: "html",
			error: function(){
				alert('系统错误，查询customer失败');
			},
			success: function(data){
				if(data.indexOf('no-') < 0){
					//先移除所有select的option
					attentionSelect.removeOption(/./);
					var data_array = data.split("|");
					//myOption = '{';
					for(i in data_array){
						//最後一個參數，使默認每個選項都不選中
						attentionSelect.addOption(/*parseInt(i)+1*/data_array[i], data_array[i], false);
						//myOption += (i == data_array.length - 1)?'"'+(parseInt(i)+1)+'":"'+data_array[i]+'"}':'"'+parseInt(i)+1+'":"'+data_array[i]+'",';
						//下面的加上<li>是能加上選項，但是不能夠選取
						//attentionUl.append('<li id="'+pre+'attention_input__'+data_array[i]+'">'+data_array[i]+'</li>');
						//attentionOptionUl.append('<option value="'+data_array[i]+'">'+data_array[i]+'</option>');	
					}
					//不能用字符串myOption來代替 如：{"foo":"bar","bar":"baz"} 
					//attentionSelect.addOption(myOption, false);
					//這一步是將select的option轉為<ul><li>的形式！！！
					attentionSelect.not('.special').selectbox();
					
					$("#"+pre+"attention_container li").click(function(){
						attentionSelectText = $("#"+pre+"attention_container li").parent().parent().prev().val()
						var qs2 = 'ajax=customer&act=ajax-search_contact_info&value='+attentionSelectText;
						$.ajax({
							type: "GET",
							url: "index.php",
							data: qs2,
							cache: false,
							dataType: "html",
							error: function(){
								alert('系统错误，查询customer失败(2)');
							},
							success: function(data){
								if(data.indexOf('no-') < 0){
									var data_array = data.split("|");
									$("#"+pre+"tel").val(data_array[0])
									$("#"+pre+"fax").val(data_array[1])
									$("#"+pre+"address").val(data_array[2])
								}else{
									//没有contact的customer ID的，都显示一下错误提示，太烦了，所以去掉了。。。
									//alert('无此Customer的contact信息！');							
									$("#"+pre+"tel").val("")
									$("#"+pre+"fax").val("")
									$("#"+pre+"address").val("")
								}
							}
						})
					})
				}else{
					//移除所有select的option
					attentionSelect.removeOption(/./);
					attentionSelect.not('.special').selectbox();
				}
			}
		})
	})
}

/* 選擇supplier 的ajax */
function selectSupplier(pre){
	$("#"+pre+"sid_container li").click(function(){
		selectText = $("#"+pre+"sid_container li").parent().parent().prev().val()
		attentionSelect = $("#"+pre+"attention");
		var qs = 'ajax=supplier&act=ajax-search_contact_name&value='+selectText;
		$.ajax({
			type: "GET",
			url: "index.php",
			data: qs,
			cache: false,
			dataType: "html",
			error: function(){
				alert('系统错误，查询supplier失败');
			},
			success: function(data){
				if(data.indexOf('no-') < 0){
					//先移除所有select的option
					attentionSelect.removeOption(/./);
					var data_array = data.split("|");
					//myOption = '{';
					for(i in data_array){
						//最後一個參數，使默認每個選項都不選中
						attentionSelect.addOption(/*parseInt(i)+1*/data_array[i], data_array[i], false);
					}
					//這一步是將select的option轉為<ul><li>的形式！！！
					attentionSelect.not('.special').selectbox();
					
					$("#"+pre+"attention_container li").click(function(){
						attentionSelectText = $("#"+pre+"attention_container li").parent().parent().prev().val()
						var qs2 = 'ajax=supplier&act=ajax-search_contact_info&value='+attentionSelectText;
						$.ajax({
							type: "GET",
							url: "index.php",
							data: qs2,
							cache: false,
							dataType: "html",
							error: function(){
								alert('系统错误，查询supplier失败(2)');
							},
							success: function(data){
								if(data.indexOf('no-') < 0){
									var data_array = data.split("|");
									$("#"+pre+"tel").val(data_array[0])
									$("#"+pre+"fax").val(data_array[1])
									$("#"+pre+"address").val(data_array[2])
								}else{
									$("#"+pre+"tel").val("")
									$("#"+pre+"fax").val("")
									$("#"+pre+"address").val("")
								}
							}
						})
					})
				}else{
					//移除所有select的option
					attentionSelect.removeOption(/./);
					attentionSelect.not('.special').selectbox();
				}
			}
		})
	})	
}


