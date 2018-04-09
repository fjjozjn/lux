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
};
var tempVar, tempVar2;
var init = false;
var checkNameInited = false;

/********** 普通函数 ************/
var goto = function(url) { window.location.href = url; };
var strLength = function(str) {  return str.replace(/[^\x00-\xff]/g,"**").length};
var isId = function(s){	return (!isNaN(s) && /^[0-9]+$/.exec(s));};
var vChar = function(s){ return /^[A-Za-z0-9\-]+$/.exec(s);};
var trim =  function(str) {return str.replace(/(^\s*)|(\s*$)/g, ""); };
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
};

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
    }catch(e){}
	
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

			
			
			
			
			
	//從url來判斷当前的是那个页面
	/*
	var local_url = location.href;
	if(local_url.indexOf('modify') > 0){
		$("img").lazyload({
			placeholder : "../../sys/images/grey.gif",
			effect      : "fadeIn"
		});
	}
	*/
	
	(function() {
		var $backToTopTxt = '<img src="../images/gotop.gif" />', $backToTopEle = $('<div class="backToTop"></div>').appendTo($("body"))
			.html($backToTopTxt).attr("title", '返回顶部').click(function() {
				$("html, body").animate({ scrollTop: 0 }, 120);
		}), $backToTopFun = function() {
			var st = $(document).scrollTop(), winh = $(window).height();
			(st > 0)? $backToTopEle.show(): $backToTopEle.hide();    
			//IE6下的定位
			if (!window.XMLHttpRequest) {
				$backToTopEle.css("top", st + winh - 166);    
			}
		};
		$(window).bind("scroll", $backToTopFun);
		$(function() { $backToTopFun(); });
	})();

	
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
	
	//有Input Product 的页面禁用了enter提交表单，因enter键绑定了search product 。其他的页面则不禁用。
	var local_url = location.href;
	if(local_url.indexOf('quotation') > 0 || local_url.indexOf('proforma') > 0 || local_url.indexOf('invoice') > 0 || local_url.indexOf('purchase') > 0){
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
	}
	 
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
};

$(function(){
	initAll();
});














	/*
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
	*/
	
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

//点击如pdf等链接的判断
function pdfConfirm(){
	if(confirm('Please confirm the page saved?')){
		//confirm yes 自动提交表单
		return true;
	}else{
		//自动滚动屏幕到最底端，让用户save
		window.scroll(0,20000);
		return false;
	}
}

function UpdateTotal()//更新总金额
{
	var vTotalMoney=0;//总金额的初始值为0;
	var vTotal = $("#total"); //得到总金额对象
	//$("#sub").each(//有相同id的，这样取就只能取出第一个
	$("td[id^='sub']").each(
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
	);//遍历结束
	vTotal.html(formatCurrency(vTotalMoney));  
}

//为product qunantity绑定blur更新total事件
function quantityBlur(attr_id, sign){
	$("#"+attr_id).blur(function(e){
        //20141219 修正
        if(/*sign == 'proforma' || sign == 'invoice'*/sign == 'purchase'){
            var rmb_input = $(this).parent().parent().next().next().children().children();
        }else{
			if(local_url.indexOf('modifyproforma') > 0){
				var rmb_input = $(this).parent().parent().next().next().children().children();
			}else{
				var rmb_input = $(this).parent().parent().next().children().children();
			}
        }
		var sub_td = rmb_input.parent().parent().next();
		var new_price = rmb_input.val().replace(/,/g,"");
		sub_td.html( accMul(parseFloat(new_price), parseFloat(/* 这里面居然不能用this或是$(this)不知道为什么。。。*/$("#"+attr_id).val())) );	
		UpdateTotal();//更新總和
	})
}

//为product price绑定blur更新total事件
function priceBlur(attr_id, sign){
	$("#"+attr_id).blur(function(e){
		//这里每一次都要先执行第一行的blur事件，再执行当前行的blur事件，暂时还不知道是为什么
        //20141219 修正
        if(/*sign == 'proforma' || sign == 'invoice'*/sign == 'purchase'){
            var qut_input = $(this).parent().parent().prev().prev().children().children();
        }else{
        	if(local_url.indexOf('modifyproforma') > 0){
        		var qut_input = $(this).parent().parent().prev().prev().children().children();
        	}else{
        		var qut_input = $(this).parent().parent().prev().children().children();
        	}
        }
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
		});
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
							}).mouseover(function(){
							    //20130725 加当鼠标移动过也将pid填到框里，因为现在是blur事件，点击选择pid也会触发blur，如果这时候框里面还没填，就会ajax查询不到，又会清空了框里的内容
                                pid_input.val(term);
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
		//alt键按下事件
		if (e.keyCode == 18) {
			if(pid_var != ''){
				var qs = 'ajax='+sign+'&act=ajax-search_price_history&pid='+pid_var;
				var goto_url = 'model/com/'+sign+'_pid_history.php?pid='+pid_var;
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
							alert('No historical data!');
						}
					}
				})	
			}
		}
	});
	
	//代码重复，能不能简化呢。。。
	//为了方便键盘操作的习惯，enter以后光标会移到description，所以加了这个框的按下shift事件
	$("#q_p_description"+i).keydown(function(e){
		var pid_input = $(this).parent().parent().prev().children().children();//这里是关键，clone后都能有效！！
		var pid_var = pid_input.val();
		//alt键按下事件
		if (e.keyCode == 18) {
			if(pid_var != ''){
				var qs = 'ajax='+sign+'&act=ajax-search_price_history&pid='+pid_var;
				var goto_url = 'model/com/'+sign+'_pid_history.php?pid='+pid_var;
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
							alert('No historical data!');
						}
					}
				})	
			}
		}
	})
}

//去除头尾的空格
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g, "");
};
//去除头尾的tab
String.prototype.trim_tab = function() {
	return this.replace(/^\t+|\t+$/g, "");	
};

/* add的时候编号主键的是否重复的ajax判断 */
function judgeXid(xid){
	$("#"+xid).blur(function(){
		var xidText = $("#"+xid).val();
		xidText = xidText.trim();
		xidText = xidText.trim_tab();
		$("#"+xid).val(xidText);
		xidText = $("#"+xid).val();
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
						alert('The ID already exists. Please change another one!');
						$("#"+xid).val('');//id已存在则清空输入框
					}
				}
			})
		}
	})
}

/*
 * 搜索 Product，原来放在外面，现在放在函数里，方便调用
 *
 * @param num: 为了方便处理不同的表单的Input product的tabindex
 *        i: 记录当前是product ID 的id属性号
 * @return 
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
	}else if(local_url.indexOf('invoice') > 0 && local_url.indexOf('customs') < 0){
		sign = 'invoice';
		xid = 'i_vid';
	}else if(local_url.indexOf('customs') > 0){
		sign = 'customs_invoice';
		xid = 'ci_vid';		
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
	quantityBlur('q_p_quantity'+i, sign);
	priceBlur('q_p_price'+i, sign);
	
	//輸入框的失去焦点事件綁定，想綁定回車，但是IE下不行，一回車就連表單都提交了
	//以禁用了IE的enter提交表单，所以改回绑定enter事件
    //20130724 改为了blur事件
	//$("#q_pid"+i).keydown(function(e){
    $("#q_pid"+i).bind('blur', function(e){

        //if( e.which==13){

        //20130705 检测是否有选择customer，没有选择就找不出markup_ratio
        //这里用name是因为selectbox前面的div的id里也有_cid，而name只有select标签里有！！
        var customer_value = $("[name*='_cid']").val();
        if(customer_value == ''){
            alert('Please select a customer ! ');
            return false;
        }
        //检测是否有选择currency，PI等显示的是美元，没有选择就算不出item的价格
        var currency_value = $("[name*='_currency']").val();
        if(currency_value == ''){
            alert('Please select currency ! ');
            return false;
        }

        //Product ID輸入框
        var pid_input = $(this);//这里是关键，clone后都能有效！！
        //index_td
        var index_td = pid_input.parent().parent().prev();

        //20131020 add 的时候没有 delivery_num 因为要有pcid才行，add的时候没有pcid
        if(local_url.indexOf('modifypurchase') > 0){
            //add scode 20130325
            var scode_td = pid_input.parent().parent().next().children();
            //Description輸入框
            var des_input = scode_td.parent().next().children().children();
            //Quantity輸入框
            var qut_input = des_input.parent().parent().next().children().children();
            //20131020 add Delivery num 显示处
            var delivery_num_td = qut_input.parent().parent().next().children();
            //Cost RMB輸入框
            var rmb_input = delivery_num_td.parent().next().children().children();
        }else if(local_url.indexOf('addpurchase') > 0){
            //add scode 20130325
            var scode_td = pid_input.parent().parent().next().children();
            //Description輸入框
            var des_input = scode_td.parent().next().children().children();
            //Quantity輸入框
            var qut_input = des_input.parent().parent().next().children().children();
            //Cost RMB輸入框
            var rmb_input = qut_input.parent().parent().next().children().children();
        }else if(local_url.indexOf('modifyinvoice') > 0 || local_url.indexOf('modifyproforma') > 0){
            //Description輸入框
            var des_input = pid_input.parent().parent().next().children().children();
            //Quantity輸入框
            var qut_input = des_input.parent().parent().next().children().children();
            // 20131021 add packing list num 显示处
            var packinglist_num_td = qut_input.parent().parent().next().children();
            //Cost RMB輸入框
            var rmb_input = packinglist_num_td.parent().next().children().children();
        }else{
            //Description輸入框
            var des_input = pid_input.parent().parent().next().children().children();
            //Quantity輸入框
            var qut_input = des_input.parent().parent().next().children().children();
            //Cost RMB輸入框
            var rmb_input = qut_input.parent().parent().next().children().children();
        }



        // enter觸發能註冊blur事件，是為了add的時候用
        quantityBlur(qut_input.attr('id'), sign);
        priceBlur(rmb_input.attr('id'), sign);
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
        //HIS 位置
        var his_div = photo_td.next().children();
        //CLEAR 位置
        //20130725 去掉，因q_pid应unbind所有事件了，清除内容后也不能使用自动search product的功能了
        //var clear_div = photo_td.next().next().children();
        //DEL 位置
        var del_div = photo_td.next().next().children();
        //20160429 BOM
        var bom_div = del_div.parent().next();

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
        var pid_tabindex = num+4*(vcount-1);
        var des_tabindex = (num+1)+4*(vcount-1);
        var qut_tabindex = (num+2)+4*(vcount-1);
        var rmb_tabindex = (num+3)+4*(vcount-1);

        if( pid_text != ''){
            var qs;
            if(sign == 'purchase'){
                //20130327 purchase 的用searchProduct_purchase 这个方法了，不走这里面了
                //purchase 的返回product信息不同于其他，des_chi和cost，主要是要在界面显示scode
                //20130725 在purchase又用回searchProduct这个了，在这里面判断比维护两个方法要简单，如这次要加check item id是否重复就有比较大的改动
                //20131020 为在modify里显示delivery出货数
                if(local_url.indexOf('modifypurchase') > 0){
                    qs = 'ajax=1&act=ajax-choose_purchase_product&value='+pid_text+'&pcid='+$('#pc_pcid').val();
                }else{
                    qs = 'ajax=1&act=ajax-choose_purchase_product&value='+pid_text;
                }
            }else{
                //var qs = 'ajax=1&act=ajax-choose_product&value='+pid_text;
                //20120425 新增了能选择currency的功能，所以改用 choose_product_new
                //20130705 加了 customer_value 和 currency_value 两个参数
                //20131021 modifyinvoice 加 packing list num
                if(local_url.indexOf('modifyinvoice') > 0){
                    qs = 'ajax=1&act=ajax-choose_product_new&value='+pid_text+'&customer='+customer_value+'&currency='+currency_value+'&pvid='+$('#i_vid').val();
                }else{
                    qs = 'ajax=1&act=ajax-choose_product_new&value='+pid_text+'&customer='+customer_value+'&currency='+currency_value;
                }
            }

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
                    if(data.indexOf('!no-') < 0){//改为 !no- 是为了复杂一点，以防正确的放回值里面也会有出现no导致错误

                        //20130724 加检查在一个表单里item是否有重复输入
                        var already_exsit_num = 0;//already_exsit_num 为2时才是有重复的，因为有1项是自己
                        var vTable = $("#tbody");//得到表格的jquery对象
                        var vtxtAfters = vTable.find("[id^='q_pid']");
                        vtxtAfters.each(
                            function(){
                                var vTempValue = $(this).val();
                                if(vTempValue == pid_input.val())
                                {
                                    already_exsit_num++;
                                }
                            }
                        );//遍历结束

                        if(already_exsit_num == 2){
                            alert('Item '+pid_input.val()+' is already exsit !');
                            pid_input.val('');
                        }else{
                            //mod 20130710 改为用精确到毫秒的时间戳来区别表单项，不用vcount了，她们一直反映有很多问题，试试用了时间戳后会不会好点
                            var item_index = (new Date()).valueOf();

                            //先複製框，再在原來的框中插入值
                            $("#tbody>.template")
                                //连同事件一起复制
                                .clone(true)
                                //複製後的也是被hide起來的了，所以要show一下
                                .show()
                                //去除模板标记
                                .removeClass("template")
                                //給id附新的值
                                .find("#q_pid").attr("id","q_pid"+item_index).attr("name","q_pid"+item_index).attr("tabindex", pid_tabindex_new).removeClass("disabled").removeAttr("disabled").end()
                                .find("#q_p_price").attr("id","q_p_price"+item_index).attr("name","q_p_price"+item_index).end()
                                .find("#q_p_quantity").attr("id","q_p_quantity"+item_index).attr("name","q_p_quantity"+item_index).end()
                                //.find("#q_p_remark").attr("id","q_p_remark"+item_index).attr("name","q_p_remark"+item_index).addClass("disabled").attr("disabled", "disabled").end()
                                .find("#q_p_description").attr("id","q_p_description"+item_index).attr("name","q_p_description"+item_index).end()
                                .find("#q_p_photos").attr("id","q_p_photos"+item_index).attr("name","q_p_photos"+item_index).end()
                                .find("#q_p_ccode").attr("id","q_p_ccode"+item_index).attr("name","q_p_ccode"+item_index).end()
                                .find("#q_p_scode").attr("id","q_p_scode"+item_index).attr("name","q_p_scode"+item_index).end()

                                //去掉autocomplete
                                .find(".autocomplete").remove().end()

                                //清空clone過來的輸入框中的值
                                .find("input").val("").removeClass("textfocus").end()
                                .find("textarea").val("").end()
                                .find("img").remove().end()
                                .find("#sub").html('').end()

                                //加了product history點擊查看的按鈕
                                .find("#his").attr("id","his"+item_index)/*.html('<img src="../../sys/images/Actions-edit-copy-icon.png" />')*/.end()
                                //加了clear清除但前行内容但不删除当前行的按钮图标
                                //.find("#clear").attr("id", "clear"+item_index).end()
                                //按鈕換成圖標了
                                //.find(".del").html("<input class='defautButton' name='' type='button' value='Del' />").end()
                                //新出的那一行不要删除按钮
                                .find("#del").attr("id","del"+item_index)/*.html('<img src="../../sys/images/del-icon.png" />')*/.end()
                                //插入表格
                                .appendTo($("#tbody"));

                            var data_array = data.split("|");
                            //加tabindex是为了能使用tab键在按tabindex的顺序转到下一个，这里由于remarks和submit的tabindex值在pid_input之上，所以，会先经过remarks和submit，才到第一个pid_input。。。

                            //当enter入了一个product ID后，就不能修改product ID了，设置成readonly，要修改就只能删除了这一行
                            /*
                             if(pid_input.attr("id") != 'q_pid'){
                             pid_input.attr("readonly", "readonly").addClass("readonly").end();
                             }
                             */

                            index_td.attr('id', 'index');
                            //20130724 加上了unbind,unbind q_pid绑定的所有事件
                            pid_input.attr("tabindex", pid_tabindex).attr("readonly", "readonly").addClass("readonly").unbind().end();

                            if(local_url.indexOf('addpurchase') > 0){
                                //add scode 20130325
                                scode_td.html(data_array[5]).end();
                            }else if(local_url.indexOf('modifypurchase') > 0){
                                delivery_num_td.html(data_array[6]).end();
                            }else if(local_url.indexOf('modifyinvoice') > 0){
                                packinglist_num_td.html(data_array[6]).end();
                            }

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
                            his_div.html('<img src="../../sys/images/history-icon.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" title="History" />').end();
                            //clear_div.html('<img src="../../sys/images/clear.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" title="Clear" />').end();
                            del_div.html('<img src="../../sys/images/del-icon.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" title="Delete" />').end();
                            bom_div.html('<a href="?act=formdetail&gid="+pid_text target="_blank"></a>').end();
                            //更新總和
                            UpdateTotal();
                            generateIndex();
                        }
                    }else{
                        //alert("\""+data+"\"");
                        if(trim(data) == "!no-2"){
                            alert('Please select currency!');
                        }
                        //屏蔽掉是因為 search 選擇了一個id後，就會彈出這個，它把之前輸入的不完整id做判斷，而非選擇後的
                        //綁定enter事件後就可以用這個來提示了
                        //为了方便，enter直接选定product ID，所以又把这个提示去掉了
                        //alert('请输入正确的Product ID');
                        pid_input.val("");

                        if(sign == 'purchase'){
                            //add scode 20130325
                            scode_td.html("");
                        }

                        des_input.val("");
                        qut_input.val("");
                        rmb_input.val("");
                        pid_input.html("");
                        sub_td.html("");
                    }
                }
            })
        }
		//}
	});
	
	//为template的Del按钮绑定事件
	$("#tbody .template .del").click(function() { 
		var del_vcount=$("#tbody tr").filter(".repeat").size();
		if(del_vcount > 1){
			//将下一个repeat的class加上template，这样就会一直有template
			$(this).parents(".template").next().addClass("template");
			
			$(this).parents(".template").remove(); 
			UpdateTotal();
			generateIndex();
		} 
		//这里就不else输出信息了，下面有了，否则就要弹两次了
	});
	
	//為選擇product的Del删除按钮事件绑定   
	$("#tbody #del"+i).click(function() {   
		var del_vcount=$("#tbody tr").filter(".repeat").size();
		//减1是template的
		if(del_vcount-1 > 1){
			if(confirm("Are you sure you want to delete?")){
				$(this).parents(".repeat").remove(); 
				UpdateTotal();
				generateIndex();
				//如果有下一個的話
				/*
				var nextRepeat = $(this).parents(".repeat").next();
				while(nextRepeat){
					nextRepeat = nextRepeat.next();
					//alert('1');
				}
				*/
			}
		}else{
			alert("Don't delete the last one!");	
		} 
	});
	
	//為選擇product的clear删除当前行内容 按钮绑定事件
	$("#tbody #clear"+i).click(function() {   
		if(confirm("Are you sure you want to clear?")){
			/*var c_sub_td = $(this).parent().prev().prev().prev();
			var c_rmb_input = c_sub_td.prev();
			var c_pt_input = c_rmb_input.children().next();
			var c_cc_input = c_pt_input.next();
			var c_sc_input = c_cc_input.next();
			var c_qut_input = c_rmb_input.prev();
			var c_des_input = c_qut_input.prev();
			var c_pid_input = c_des_input.prev();
			c_pid_input.children().children().removeClass("readonly").removeAttr("readonly").val("");
			c_des_input.children().children().val("");
			c_qut_input.children().children().val("");
			c_rmb_input.children().children().val("");
			c_pt_input.val("");
			c_cc_input.val("");
			c_sc_input.val("");
			c_sub_td.html("0.00");
			UpdateTotal();
			generateIndex();*/

            //20130725 不使用clear功能了，因q_pid已经unbind所有事件了,暂时先这样处理，因为去掉图片很麻烦
            alert('Please use delete button !');
		}
	});
	
	//為選擇product的his查看按钮事件绑定   
	$("#tbody #his"+i).click(function() {
        //外面定义的pid_input这里面用不了
        if(local_url.indexOf('addpurchase') > 0){
            var his_pid_input = $(this).parent().prev().prev().prev().prev().prev().prev().prev().children().children();//这里是关键，clone后都能有效！！
        }else if(local_url.indexOf('modifypurchase') > 0){
            var his_pid_input = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().children().children();//这里是关键，clone后都能有效！！
        }else if(local_url.indexOf('modifyinvoice') > 0 || local_url.indexOf('modifyproforma') > 0){
            var his_pid_input = $(this).parent().prev().prev().prev().prev().prev().prev().prev().children().children();//这里是关键，clone后都能有效！！
        }else{
            var his_pid_input = $(this).parent().prev().prev().prev().prev().prev().prev().children().children();//这里是关键，clone后都能有效！！
        }

		var pid_var = his_pid_input.val();

		if(pid_var != ''){
			var qs = 'ajax='+sign+'&act=ajax-search_price_history&pid='+pid_var;
			var goto_url = 'model/com/'+sign+'_pid_history.php?pid='+pid_var;
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
						alert('No historical data!');
					}
				}
			})	
		}
	});
	
	//為選擇product的Del All删除按钮事件绑定 
	//kevin不要Del All按钮了
	/*
	$("#tbody .del_all").click(function() {
		$(".template")
			.find("input").val("").end()
			.find("textarea").val("").end()
			.find("img").not("#del").remove().end()
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
	*/
		
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
  m=Math.pow(10,Math.max(r1,r2));
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
    var sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    var cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
    	cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
    	num = num.substring(0,num.length-(4*i+3))+','+num.substring(num.length-(4*i+3));
    return (((sign)?'':'-') + num + '.' + cents).replace(/,/g, "");
}


/* 選擇customer 的ajax */
function selectCustomer(pre){
	$("#"+pre+"cid_container li").click(function(){
        //20150122
		//var selectText = $("#"+pre+"cid_container li").parent().parent().prev().val();
        var selectText = $("#"+pre+"cid").val();
		var attentionSelect = $("#"+pre+"attention");
		var qs = 'ajax=customer&act=ajax-search_contact_name&value='+escape(selectText);
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
					for(var i in data_array){
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
						var attentionSelectText = $("#"+pre+"attention_container li").parent().parent().prev().val();
						var qs2 = 'ajax=customer&act=ajax-search_contact_info&value0='+selectText+'&value='+escape(attentionSelectText);
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
									$("#"+pre+"tel").val(data_array[0]);
									$("#"+pre+"fax").val(data_array[1]);
									$("#"+pre+"address").val(data_array[2]);
								}else{
									//没有contact的customer ID的，都显示一下错误提示，太烦了，所以去掉了。。。
									//alert('无此Customer的contact信息！');							
									$("#"+pre+"tel").val("");
									$("#"+pre+"fax").val("");
									$("#"+pre+"address").val("");
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

/* 選擇customer 的ajax */
function selectFtyCustomer(pre){
    $("#"+pre+"cid_container li").click(function(){
        //20150122
        //var selectText = $("#"+pre+"cid_container li").parent().parent().prev().val();
        var selectText = $("#"+pre+"cid").val();
        var attentionSelect = $("#"+pre+"attention");
        var qs = 'ajax='+pre+'&act=ajax-search_contact_name&value='+escape(selectText);
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
                    for(var i in data_array){
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
                        var attentionSelectText = $("#"+pre+"attention_container li").parent().parent().prev().val();
                        var qs2 = 'ajax='+pre+'&act=ajax-search_contact_info&value0='+selectText+'&value='+escape(attentionSelectText);
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
                                    $("#"+pre+"tel").val(data_array[0]);
                                    $("#"+pre+"fax").val(data_array[1]);
                                    $("#"+pre+"address").val(data_array[2]);
                                }else{
                                    //没有contact的customer ID的，都显示一下错误提示，太烦了，所以去掉了。。。
                                    //alert('无此Customer的contact信息！');
                                    $("#"+pre+"tel").val("");
                                    $("#"+pre+"fax").val("");
                                    $("#"+pre+"address").val("");
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
        //20150122
		//var selectText = $("#"+pre+"sid_container li").parent().parent().prev().val();
        var selectText = $("#"+pre+"sid").val();
		var attentionSelect = $("#"+pre+"attention");
		var qs = 'ajax=supplier&act=ajax-search_contact_name&value='+escape(selectText);//ajax傳中文在除firefox以外的瀏覽器都亂碼
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
						attentionSelect.addOption(data_array[i], data_array[i], false);
					}
					//這一步是將select的option轉為<ul><li>的形式！！！
					attentionSelect.not('.special').selectbox();
					
					$("#"+pre+"attention_container li").click(function(){
						var attentionSelectText = $("#"+pre+"attention_container li").parent().parent().prev().val();
						var qs2 = 'ajax=supplier&act=ajax-search_contact_info&value0='+selectText+'&value='+escape(attentionSelectText);
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
									$("#"+pre+"tel").val(data_array[0]);
									$("#"+pre+"fax").val(data_array[1]);
									$("#"+pre+"address").val(data_array[2]);
								}else{
									$("#"+pre+"tel").val("");
									$("#"+pre+"fax").val("");
									$("#"+pre+"address").val("");
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


/* 選擇sample order po 的ajax */
function selectSampleOrder(pre){
    $("#"+pre+"sid_container li").click(function(){
        //20150122 sample order 要直接提交工厂名字
        var selectText = $("#"+pre+"sid_container li").parent().parent().prev().val();
        //var selectText = $("#"+pre+"sid").val();
        var attentionSelect = $("#"+pre+"attention");
        var qs = 'ajax=sample_order&act=ajax-search_contact_name&value='+escape(selectText);//ajax傳中文在除firefox以外的瀏覽器都亂碼
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
                        attentionSelect.addOption(data_array[i], data_array[i], false);
                    }
                    //這一步是將select的option轉為<ul><li>的形式！！！
                    attentionSelect.not('.special').selectbox();

                    $("#"+pre+"attention_container li").click(function(){
                        var attentionSelectText = $("#"+pre+"attention_container li").parent().parent().prev().val();
                        var qs2 = 'ajax=sample_order&act=ajax-search_contact_info&value0='+selectText+'&value='+escape(attentionSelectText);
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
                                    $("#"+pre+"tel").val(data_array[0]);
                                    $("#"+pre+"fax").val(data_array[1]);
                                    $("#"+pre+"address").val(data_array[2]);
                                }else{
                                    $("#"+pre+"tel").val("");
                                    $("#"+pre+"fax").val("");
                                    $("#"+pre+"address").val("");
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


/* add to ci 弹出设置percent框 */
function setCiPercent(vid){
	if(confirm('Please confirm the page saved?')){
		art.dialog({
			id: 'shake-demo',
			content: 'Invoice value down to :<br /><br /><input align=\'middle\' id=\'percent\' type=\'text\' value=\'50\' style=\'width:80px;\' /> %<br />',
			lock: true,
			fixed: true,
			ok: function () {
				var value = $('#percent').val();
				window.location.href='?act=com-modifycustomsinvoice&vid='+vid+'&percent='+value;
				return true;               
			},
			okValue: 'ok',
			cancel: function () {}
		});
	}else{
		//自动滚动屏幕到最底端，让用户save
		window.scroll(0,20000);
		return false;
	}	
}


/* pdf photo list  */
function photoPdfList(){
	if(confirm('Please confirm the page saved?')){
		art.dialog({
			id: 'shake-demo',
			title: 'PDF - Photo List',
			content: ':<br /><br /><select></select><br />',
			lock: true,
			fixed: true,
			ok: function () {
				var value = $('#percent').val();
				window.location.href='?act=com-modifycustomsinvoice&vid='+vid+'&percent='+value;
				return true;               
			},
			okValue: 'ok',
			cancel: function () {}
		});
	}else{
		//自动滚动屏幕到最底端，让用户save
		window.scroll(0,20000);
		return false;
	}		
}


/* 选择currency */
function currency(pre){
	$("#"+pre+"currency_container li").click(function(){
		var selectText = $("#"+pre+"currency_container li").parent().parent().prev().val()
		
		//所有pid的集合
		var pid_all = '';
		//第一个是隐藏的为空，最后一个是空的，所以要去除这两个
		var i = 1;
		var pid_set_length = $("input[name*='q_pid']").length;
		$("input[name*='q_pid']").each(function(){
			if(i == 1 || i == pid_set_length){
				i++;
				return true;	
			}
			(i == pid_set_length - 1)?(pid_all += $(this).val()):(pid_all += ($(this).val() + '|'));
			i++;
		});
		
		//所有quantity的集合
		var quantity_all = '';
		i = 1;
		var quantity_set_length = $("input[name*='q_p_quantity']").length;
		$("input[name*='q_p_quantity']").each(function(){
			if(i == 1 || i == quantity_set_length){
				i++;
				return true;	
			}		
			(i == quantity_set_length - 1)?(quantity_all += $(this).val()):(quantity_all += ($(this).val() + '|'));
			i++;
		});
		
		//alert(pid_all);		
		//alert(quantity_all);

		var qs = 'ajax=currency&act=ajax-currency&value='+selectText+'&pid_all='+pid_all+'&quantity_all='+quantity_all;
		$.ajax({
			type: "GET",
			url: "index.php",
			data: qs,
			cache: false,
			dataType: "html",
			error: function(){
				alert('系统错误导致选择currency出错');
			},
			success: function(data){
				if(data.indexOf('no-') < 0){
					var data_set = data.split(" ");
					var price_set = data_set[0].split("|");
					var total_set = data_set[1].split("|");
					
					var i = 1;
					var price_set_length = $("input[name*='q_p_price']").length;
					var index = 0;
					$("input[name*='q_p_price']").each(function(){
						if(i == 1 || i == price_set_length){
							i++;
							return true;	
						}	
						$(this).val(price_set[index]);
						$(this).parent().parent().next().html(total_set[index]);
						index++;	
						i++;
					});
					UpdateTotal();
				}
			}
		})
	})		
}


/**
 * 搜索 BOM 的物料类型
 *
 * @param 	obj type select 框BOM对象
 * @return 
 */
function searchMaterial(obj){
	//type select 框
	var g_m_type_select = $(obj);//这里是关键，clone后都能有效！！	
	//type select 的值
	var selectText = g_m_type_select.val()

	var g_m_id_name_select = g_m_type_select.parent().parent().next().children().children();
	g_m_id_name_select.empty();
	
	//所有的数据行有一个.repeat的Class，得到数据行的大小
	var vcount=$("#tbody tr").filter(".repeat").size();
	
	//加9是因为 tbody 之前的 tabindex 排到9了
	var g_m_type_tabindex_new = 9+5*vcount;
	var g_m_id_name_tabindex = 9+4*(vcount-1);
	var g_m_price_tabindex = 9+4*(vcount-1);
	var g_m_value_tabindex = 9+4*(vcount-1);
	var g_m_remark_tabindex = 9+4*(vcount-1);
	
	var html = '';
	
	var qs = 'ajax=1&act=ajax-choose_material_type&type='+escape(selectText);
	$.ajax({
		type: "GET",
		url: "index.php",
		data: qs,
		cache: false,
		dataType: "html",
		error: function(){
			alert('系统错误，查询 material 失败');
		},
		success: function(data){
			if(data.indexOf('no-') < 0){
				var data_array = data.split("|");
				html = '<option value="">- select -</option>';
				for(i=0; i<data_array.length; i++){
					html += "<option value='"+data_array[i]+"'>"+data_array[i]+"</option>";
				}
				g_m_id_name_select.removeAttr("disabled").append(html);
								
			}else{
				html = '<option value="">无记录</option>';
				g_m_id_name_select.removeAttr("disabled").append(html);
			}
		}
	});	
}

/**
 * 物料和工序的删除用这个
 *
 * @param 	obj del div的BOM对象
 * @return 
 */
function delBomItem(obj){
	if(confirm("Are you sure you want to delete?")){
		$(obj).parents(".repeat").remove(); 
	}
}


/**
 * 搜索 BOM 的物料ID+NAME
 *
 * @param 	obj type select 框BOM对象
 * @return 
 */
function searchMaterialDetail(obj){
	var g_m_id_name_select = $(obj);
	var g_m_type_select = g_m_id_name_select.parent().parent().prev().children().children();
	
	//color 显示位置
	var color_td = g_m_id_name_select.parent().parent().next();
	//unit 显示位置
	var unit_td = color_td.next();	
	
	//price 输入框
	var g_m_price_input = unit_td.next().children().children();
	//value 输入框
	var g_m_value_input = g_m_price_input.parent().parent().next().children().children();
	//add 损耗率 20130325
	var g_m_loss = g_m_value_input.parent().parent().next().children();
	//total 显示位置
	var total_td = g_m_loss.parent().next().children();
	//remark 输入框
	var g_m_remark_input = total_td.parent().next().children().children();
	//DEL 位置
	var del_div = g_m_remark_input.parent().parent().next().children();
	//hidden g_m_id
	var hidden_g_m_id = del_div.next();	
	
	//用时间戳来区别表单
	var timestamp = Date.parse(new Date())/1000;	
	
	var selectText = g_m_id_name_select.val();
	var selectText_array = selectText.split(":");
	//hidden 赋值为ID
	hidden_g_m_id.val(trim(selectText_array[0]));
	
	var qs = 'ajax=1&act=ajax-choose_material_id&value='+selectText_array[0];
	$.ajax({
		type: "GET",
		url: "index.php",
		data: qs,
		cache: false,
		dataType: "html",
		error: function(){
			alert('系统错误，查询 material 失败');
		},
		success: function(data){
			if(data.indexOf('no-') < 0){
				var data_array = data.split("|");
				color_td.html(data_array[0]);
				unit_td.html(data_array[2]);
				g_m_price_input.val(data_array[1]);
				g_m_loss.html(data_array[3]);
				
				//先複製框，再在原來的框中插入值
				$("#tbody>.template")   
					//连同事件一起复制   
					.clone(true)  
					//複製後的也是被hide起來的了，所以要show一下
					.show() 
					//去除模板标记   
					.removeClass("template")   
					//給id附新的值
					//find 后一定要有 end 不知道为什么？？
					
					.find("#g_m_type").removeClass("disabled").removeAttr("disabled").attr("id", "g_m_type"+timestamp).attr("name", "g_m_type"+timestamp).end()
					.find("#g_m_id_name").attr("id", "g_m_id_name"+timestamp).attr("name", "g_m_id_name"+timestamp).end()
					.find("#g_m_price").attr("id", "g_m_price"+timestamp).attr("name", "g_m_price"+timestamp).end()
					.find("#g_m_value").attr("id", "g_m_value"+timestamp).attr("name", "g_m_value"+timestamp).end()
					.find("#g_m_remark").attr("id", "g_m_remark"+timestamp).attr("name", "g_m_remark"+timestamp).end()
					//hidden
					.find("#g_m_id").attr("id", "g_m_id"+timestamp).attr("name", "g_m_id"+timestamp).end()

					//插入表格   
					.appendTo($("#tbody"));
				
				g_m_type_select.attr("disabled", "disabled");
				//用hidden input来代替这个select的提交了，所以可以把这个disabled掉了
				g_m_id_name_select.attr("disabled", "disabled");	
				
				hidden_g_m_id.removeClass("disabled").removeAttr("disabled");				
				g_m_price_input.removeClass("disabled").removeAttr("disabled");
				g_m_value_input.removeClass("disabled").removeAttr("disabled");
				g_m_remark_input.removeClass("disabled").removeAttr("disabled");
				del_div.html('<img src="../../sys/images/del-icon.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" title="Delete" />').end()	
			}
		}
	})	
}


/**
 * BOM 物料计算总价
 * onblur
 */
function materialPriceBlur(obj){
	var my = $(obj);
	var g_m_value_input = my.parent().parent().next().children().children();
	var loss_td = g_m_value_input.parent().parent().next().children();
	var total_td = loss_td.parent().next().children();
	total_td.html(formatCurrency( my.val() * g_m_value_input.val() * (1+loss_td.html()/100) ));
	updateMTotal();
}

/**
 * BOM 物料计算总价
 * onblur
 */
function materialValueBlur(obj){
	var my = $(obj);
	var g_m_price_input = my.parent().parent().prev().children().children();
	var loss_td = my.parent().parent().next().children();
	var total_td = loss_td.parent().next().children();
	total_td.html(formatCurrency(my.val() * g_m_price_input.val() * (1+loss_td.html()/100) ));
	updateMTotal();
}




//fty 用户填写出货单添加订单号(点击添加图标添加)
function addPurchase(){
	//$("#fty_id_container li").click(function(){	
	//$("#addpurchase").click(function(){
		//获取当前有几个purchase，以确定下一个的标识编号
		//var pcount = $("tbody[class*='delivery']").size();							 
									 
		var selectText = $("#fty_id_container li").parent().parent().prev().val();
		
		if(selectText != '- select -'){
			var judge = $('tbody #'+selectText).length;
			if(judge == 0){
				//先清空tbody下的内容，以防一直往里面添加
				//6.29要改为一个出货单要添加多个purchase了，所以就又把这个去掉了，这样就可以连续添加了
				//$('.delivery').empty();
				var qs = 'ajax=delivery&act=ajax-search_purchase&value='+selectText;
				$.ajax({
					type: "GET",
					url: "index.php",
					data: qs,
					cache: false,
					dataType: "html",
					error: function(){
						alert('系统错误，查询订单失败');
					},
					success: function(data){
						if(data.indexOf('!no-') < 0){
							var all_html = '';
							//var d_index = 0;
							//20120703 要获取当前<tr id="PO***">的个数，以确定下一个的编号是什么
							//d_index = $("tr[name*='PO']").length;//减1是因为编号是从0开始的
							//alert(d_index);
							var data_purchase_item = data.split(",");
							for(i in data_purchase_item){
								//item_index = (parseInt(d_index)+parseInt(i));
								//mod 20130127 改为用精确到毫秒的时间戳来区别表单项
								var item_index = (new Date()).valueOf()+i;
								var data_purchase_each = data_purchase_item[i].split("|");
								all_html += '<tr name="'+selectText+'" id="'+data_purchase_each[2]+'">'
									
								//箱数 tabindex 从加7开始，是因为提交按钮已经是6了
								all_html += '<td><div class="formfield"><input id="box_num'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+7)+'" strlen="1,10" maxlength="10" value="" restrict="number" required="1" name="box_num'+item_index+'" onblur="boxnumBlur(this)" ></div></td>';
									
								//内箱
                                //20130813 内箱改为非必填
								all_html += '<td><div class="formfield"><input id="inner_box_num'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+8)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="inner_box_num'+item_index+'"></div></td>';
									
								//订单号
								all_html +=	'<td><div class="formfield"><input id="po_id'+item_index+'" class="textinit textinitb" type="text" style="width:105px" tabindex="'+(10*i+9)+'" strlen="1,10" maxlength="10" value="'+data_purchase_each[0]+'" readonly="readonly" required="1" name="po_id'+item_index+'"></div></td>';
									
								//客户
								all_html +=	'<td>'+data_purchase_each[1]+'</td>';
									
								//款号
								all_html +=	'<td><div class="formfield"><input id="p_id'+item_index+'" class="textinit textinitb" type="text"  tabindex="'+(10*i+7)+'" strlen="1,30" maxlength="30" value="'+data_purchase_each[2]+'" readonly="readonly" required="1" name="p_id'+item_index+'"></div></td>';
									
								//客号
								all_html +=	'<td>'+data_purchase_each[3]+'</td>';
									
								//数量
								all_html +=	'<td><div class="formfield"><input id="quantity'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+10)+'" strlen="1,10" maxlength="10" onblur="quantity_blur(this)" value="'+data_purchase_each[4]+'" required="1" restrict="number" name="quantity'+item_index+'"></div></td>';
									
								//单价
								all_html +=	'<td>'+data_purchase_each[5]+'</td>';
									
								//金额
								all_html +=	'<td id="sub" align="right">'+formatCurrency(data_purchase_each[4]*data_purchase_each[5])+'</td>';

                                //20130813 给重量长宽高加上必填的属性，但是改为灰色时会将必填属性remove
								//重量
								all_html +=	'<td><div class="formfield"><input id="weight'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+11)+'" strlen="1,10" maxlength="10" onblur="weight_blur(this)" value="" restrict="number" name="weight'+item_index+'" required="1"></div></td>';
									
								//尺寸-长
								all_html +=	'<td><div class="formfield"><input id="size_l'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+12)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="size_l'+item_index+'" required="1"></div></td>';
									
								//尺寸-宽
								all_html +=	'<td><div class="formfield"><input id="size_w'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="size_w'+item_index+'" required="1"></div></td>';
									
								//尺寸-高
								all_html +=	'<td><div class="formfield"><input id="size_h'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+14)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="size_h'+item_index+'" required="1"></div></td>';
									
								//备注
								all_html +=	'<td><div class="formfield"><input id="remark'+item_index+'" class="textinit textinitb" type="text" style="width:100px" tabindex="'+(10*i+15)+'" strlen="1,200" maxlength="200" value="" name="remark'+item_index+'" required="1"></div></td>';
								//操作
								all_html +=	'<td><img id="'+data_purchase_each[2]+'" title="复制" style="opacity: 0.5;" onclick="copyProduct(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/copy_small.png"></td><td><img id="'+data_purchase_each[2]+'" title="删除" style="opacity: 0.5;" onclick="delProduct(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del2_small.png"></td>';
																																																																												
								all_html += '</tr>';
							}	
							//all_html += '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td>运费：</td><td><div class="formfield"><input id="express_cost" class="textinit textinitb" type="text" style="width:40px" tabindex="" strlen="1,10" maxlength="10" value="" required="1" restrict="number" name="express_cost"></div></td><td></td><td></td><td></td><td></td><td></td></tr>';
							//all_html += '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td>合计：</td><td id="total" align="right"></td><td></td><td></td><td></td><td></td><td></td></tr>';
							
							//all_html += '<tr><td colspan="13"></td><td><div id="divsubmitbutton" class="buttonfield"><input id="submitbutton" class="defautButton" type="submit" tabindex="" value="提交" name="submitbutton"></div></td></tr>';
							
							//多了这个空行不好算item_index，所以去掉
							//all_html += '<tr id="'+selectText+'"><td colspan="15">&nbsp;</td></tr>';
							
							$('.delivery').append(all_html);
							
							//删除已经添加的订单号选项
							//如果添加的时候删除了这个选项，那么删除的时候就不能用到了
							//$("#fty_id").removeOption(selectText);
							
							
							//能用到通用的，挺好。。
							UpdateTotal();
							UpdateTotalQuantity();
							
							//！！这个必须加上的，这个原来是页面一加载完，就在 form->end() 的 initAll() 里面初始化所有的表单的，但是现在使用的是jquery的事件触发生成表单，所以就不能自动初始化所有表单。
							//formRebuilder();
							
							
							//在加上 selectbox 就不能触发 $("#fty_id_container li").click 了，不知道为什么，先去掉			
							//$('select').not('.special').selectbox();				
							//$('body').append('<div id="mytt"></div>').children('#mytt')
								//.click(function(){$(this).hide(); clearTimeout(tooltipClock);})
								//.hover(function () {$(this).addClass("heavy" + $(this).data('reverse'));},
								//function () {$(this).removeClass("heavy" + $(this).data('reverse'));});
							
							$('form').submit(checkSubmit).attr('autocomplete', 'off');
							$(':input').each(function(){bindEventFor(this);});
							//$(':submit,:reset,:button').not('.smallbutton').addClass('defautButton');
							
							//！有这个选择时间的插件才能用，为什么要加这一句，同上
							$('#d_date').click(function(){WdatePicker();});
							
							//快递费输入框blur后，跟新total_all
							express_blur();
							
						}else{
							alert('查询订单号失败！');
						}
					}
				})
			}else{
				alert('此订单号已添加。请不要重复添加。');	
			}
		}else{
			alert('请先选择订单号。');
		}
	//})
}


//fty 用户填写出货单删除订单号(点击删除图标进行删除)
function delPurchase(){
	var selectText = $("#fty_id_container li").parent().parent().prev().val();
	if(selectText != '- select -'){
		var judge = $('tbody [name='+selectText+']').length;
		if(judge != 0){
			/* 方法一
			var vTable=$("#tbody");
			vTable.find('#'+selectText).each(function(){
				$(this).remove();
			})
			*/
			//方法二
			//改为name保持订单号了，因为ID用来放pid了，因为复制单个porduct信息的时候要用到ID
			$('tbody [name='+selectText+']').each(function(){
				$(this).remove();
			});
			UpdateTotal();
		}else{
			alert('订单号未添加，无需删除。');	
		}
	}else{
		alert('请先选择订单号。');
	}
}

//快递费输入框blur后，跟新total_all
function express_blur(){
	$("#express_cost").blur(function(e){
		UpdateTotal();//更新總和，因为后面每次加都是以total的数字加上express_cost，所以没加这句，每输入新的express_cost就是在原来的基础上加，结果越加越多
		var old_total_all = $("#total").html();
		var express_cost = $(this).val();
		if(express_cost == ''){
			express_cost = 0;
		}else{
			//虽然是自己输入的，但也要防着会按标准的有逗号的输入
			express_cost = express_cost.replace(',', '');
		}
		$("#total").html(formatCurrency(parseFloat(express_cost) + parseFloat(old_total_all.replace(',', ''))));
	})
}

//更新product数量总数
function UpdateTotalQuantity()
{
	var vTotalQuantity = 0;//总数量的初始值为0;               
	var vTable = $("#tbody");//得到表格的jquery对象
	var vTotal = $("#totalQ"); //得到总金额对象
	var vtxtAfters = vTable.find("[id^='quantity']");//得到所有的费用对象;
	vtxtAfters.each(
		function(i)
		{
			var vTempValue = $(this).val();	
			vTempValue = vTempValue.replace(/,/g,"");
			if(vTempValue == "")
			{
				vTempValue = 0;
			} 
			//这了用accAdd就不行。。。
			vTotalQuantity += parseFloat(vTempValue);//這個parseFloat，好像會出好多小數點後面的0
		}
	);//遍历结束
	vTotal.html(vTotalQuantity);  
}

//quantity 失去焦点，更新总数量和金额、总金额
function quantity_blur(obj){
	var quantity = $("#"+obj.id);
	var quantity_value = obj.value;
	var price = quantity.parent().parent().next();
	var total = price.next();
	total.html(formatCurrency(quantity_value * price.html()));
	
	UpdateTotalQuantity();//更新总数量
	UpdateTotal();//更新总金额
}

//删除出货单中的product
function delProduct(obj){
	//$("#"+obj.id).remove();
	// ！！！！$(obj) 这个是关键，从dom对象转为jquery对象
	$(obj).parent().parent().remove();
	
	UpdateTotalQuantity();//更新总数量
	UpdateTotal();//更新总金额
}

//复制出货单中的product
function copyProduct(obj){
	//var my = $("#"+obj.id);
	var my = $(obj).parent().parent();
	var pid = my.children().next().next().children().children().val();
	var timestamp = Date.parse(new Date())/1000;
	var myclone = my.clone(true);
	$("<tr name="+pid+" id="+obj.id+">" + myclone.html() + "</tr>").insertAfter(my);
	//alert(myclone.html());
	
	myclone.find("input").each(function(){
		var myid = this.id;
		//要先改name，再改id，如果先改id，改name的时候就找不到了
		$('#'+this.id).attr('name', myid + timestamp);
		$('#'+this.id).attr('id', myid + timestamp);
	});
	
	UpdateTotalQuantity();//更新总数量
	UpdateTotal();//更新总金额
}

//添加group到group textarea
function addGroup(){
	var selectText = $("#admin_lux_group_container li").parent().parent().prev().val();
	if(selectText != '- select -'){
		var group_textarea = $('#admin_lux_group_textarea');
		var group_textarea_value = group_textarea.val();
		if(group_textarea_value.indexOf(selectText) < 0){
			group_textarea.val(group_textarea_value+selectText+"\r\n");
		}else{
			alert('此Group已添加，请不要重复添加。');	
		}
	}else{
		alert('请先选择Group。');	
	}
	
}

//从group textarea 删除 group
function delGroup(){
	var selectText = $("#admin_lux_group_container li").parent().parent().prev().val();
	if(selectText != '- select -'){
		var group_textarea = $('#admin_lux_group_textarea');
		var group_textarea_value = group_textarea.val();
		if(group_textarea_value.indexOf(selectText) < 0){
			alert('此Group未添加，无需删除。');
		}else{
			group_textarea.val(group_textarea_value.replace(selectText+"\n", ''));
			group_textarea.val(group_textarea_value.replace(selectText, ''));
		}
	}else{
		alert('请先选择Group。');	
	}
}


/* 展会product的选择currency */
function p_currency(){
	$("#p_currency_container li").click(function(){
		var selectText = $("#p_currency_container li").parent().parent().prev().val();
		var pid = $("#p_pid").val();

		var qs = 'ajax=currency&act=ajax-currency&pid='+pid+'&value='+selectText;
		$.ajax({
			type: "GET",
			url: "index.php",
			data: qs,
			cache: false,
			dataType: "html",
			error: function(){
				alert('系统错误导致选择currency出错');
			},
			success: function(data){
				if(data.indexOf('no-') < 0){
					$("#p_price").val(data);
					$("#p_price").parent().prev().html('Price('+selectText+')');
				}else{
					alert('error');
				}
			}
		})
	})		
}


/*
 * 搜索 BOM 的工序
 *
 * @param 	i: 记录当前是工序 ID 的id属性号
 * @return 
 */
function searchTask(obj){
	
	var g_t_select = $(obj);
	
	//马上就disabled掉，防止网络卡了，用户能点多次
	g_t_select.attr("disabled", "disabled");
	
	var selectText = g_t_select.val();
	var g_t_price_input = g_t_select.parent().parent().next().children().children();
	var g_t_time_input = g_t_price_input.parent().parent().next().children().children();
	var total_div = g_t_time_input.parent().parent().next().children();
	var g_t_remark_input = total_div.parent().next().children().children();
	var del_div = g_t_remark_input.parent().parent().next().children();
	//hidden g_t_id
	var hidden_g_t_id = del_div.next();	
	hidden_g_t_id.val(selectText);
	
	//所有的数据行有一个.repeat的Class，得到数据行的大小
	var vcount=$("#tbody tr").filter(".repeat").size();
	//用时间戳来区别表单
	var timestamp = Date.parse(new Date())/1000;
	
	var qs = 'ajax=task&act=ajax-choose_task_id&value='+selectText;
	$.ajax({
		type: "GET",
		url: "index.php",
		data: qs,
		cache: false,
		dataType: "html",
		error: function(){
			alert('system error!');
		},
		success: function(data){
			if(data.indexOf('no-') < 0){
				/*var data_array = data.split("|");
				g_t_price_input.val(data_array[0]);
				g_t_time_input.val(data_array[1]);
				total_div.html(data_array[2]);*/
                g_t_price_input.val(data);
				
				//先複製框，再在原來的框中插入值
				$("#tbody1>.template")   
					//连同事件一起复制   
					.clone(true)  
					//複製後的也是被hide起來的了，所以要show一下
					.show() 
					//去除模板标记   
					.removeClass("template")   
					//給id附新的值
					//find 后一定要有 end 不知道为什么？？
					
					.find("#g_t_type_name").removeClass("disabled").removeAttr("disabled").attr("id", "g_t_type_name"+timestamp).attr("name", "g_t_type_name"+timestamp).end()
					.find("#g_t_price").attr("id", "g_t_price"+timestamp).attr("name", "g_t_price"+timestamp).end()
					.find("#g_t_time").attr("id", "g_t_time"+timestamp).attr("name", "g_t_time"+timestamp).end()
                    .find("#g_t_remark").attr("id", "g_t_remark"+timestamp).attr("name", "g_t_remark"+timestamp).end()
					//hidden
					.find("#g_t_id").attr("id", "g_t_id"+timestamp).attr("name", "g_t_id"+timestamp).end()

					//插入表格   
					.appendTo($("#tbody1"));
				
				//20120830 解除select绑定的change事件，这个以前都没有想到
				//在前面 disabled 了，这里就太晚了
				//g_t_select.attr("disabled", "disabled")//.unbind("change")	
				
				hidden_g_t_id.removeClass("disabled").removeAttr("disabled");
				g_t_price_input.removeClass("disabled").removeAttr("disabled");
				g_t_time_input.removeClass("disabled").removeAttr("disabled");
				g_t_remark_input.removeClass("disabled").removeAttr("disabled");
				del_div.html('<img src="../../sys/images/del-icon.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" title="Delete" />').end()	
			}
		}
	})		
}




/**
 * BOM 工序price失去焦点，计算总价
 * onblur
 */
function taskPriceBlur(obj){
	var my = $(obj);
	var g_t_time_input = my.parent().parent().next().children().children();
	var total_td = g_t_time_input.parent().parent().next().children();
	total_td.html(formatCurrency(my.val() * g_t_time_input.val()));
	updateTTotal();
}


/**
 * BOM 工序time失去焦点，计算总价
 * onblur
 */
function taskTimeBlur(obj){
	var my = $(obj);
	var g_t_price_input = my.parent().parent().prev().children().children();
	var total_td = my.parent().parent().next().children();
	total_td.html(formatCurrency(my.val() * g_t_price_input.val()));
	updateTTotal();
}




//更新 material 总价
function updateMTotal(){
	var m_total_num = 0;
	var m_total_div = $("#materialTotal");//显示的位置
	var m_total = $("#tbody").find("#m_total");
	m_total.each(
		function(){
			var vTempValue = $(this).html();
			if(vTempValue == "")
			{
				vTempValue = 0;
			}else{
				vTempValue = vTempValue.replace(/,/g,"");
			}
			//这了用accAdd就不行。。。
			m_total_num += parseFloat(vTempValue);//這個parseFloat，好像會出好多小數點後面的0			
		}
	);
	m_total_div.html(formatCurrency(m_total_num)); 
	$("#post_m_total").val(m_total_num);
	updateBomTotal();
}

//更新 task 总价
function updateTTotal(){
	var t_total_num = 0;
	var t_total_div = $("#taskTotal");//显示的位置
	var t_total = $("#tbody1").find("#t_total");
	t_total.each(
		function(){
			var vTempValue = $(this).html();
			if(vTempValue == "")
			{
				vTempValue = 0;
			}else{
				vTempValue = vTempValue.replace(/,/g,"");
			}
			//这了用accAdd就不行。。。
			t_total_num += parseFloat(vTempValue);//這個parseFloat，好像會出好多小數點後面的0			
		}
	);
	t_total_div.html(formatCurrency(t_total_num));
	$("#post_t_total").val(t_total_num);
	updateBomTotal(); 	
}

//更新 bom 总价

function updateBomTotal(){
	var m_total_value = $("#materialTotal").html();
	m_total_value = (m_total_value != '' && m_total_value != null)?m_total_value.replace(/,/g,""):0;
	var t_total_value = $("#taskTotal").html();
	t_total_value = (t_total_value !='' && t_total_value != null)?t_total_value.replace(/,/g,""):0;
	var p_plate_value = $("#p_plate").val();
	p_plate_value = (p_plate_value != '')?p_plate_value:0;
	var p_other_value = $("#p_other").val();
	p_other_value = (p_other_value !='')?p_other_value:0;
	var p_profit_value = $("#p_profit").val();
	p_profit_value = (p_profit_value != '')?p_profit_value:0;
	
	var total = (parseFloat(m_total_value) + parseFloat(t_total_value) + parseFloat(p_plate_value) + parseFloat(p_other_value)) * parseFloat(p_profit_value);
	$("#allTotal").html(formatCurrency(total));
	$("#post_total").val(total);
}


//settlement po_no change
function pcidChange(){
	$("#po_no_container li").click(function(){
		var selectText = $("#po_no_container li").parent().parent().prev().val();
		
		var qs = 'ajax=settlement&act=ajax-choose_settlement_pcid&value='+selectText;
		$.ajax({
			type: "GET",
			url: "index.php",
			data: qs,
			cache: false,
			dataType: "html",
			error: function(){
				alert('system error!');
			},
			success: function(data){	
				if(data.indexOf('no-') < 0){
					var data_array = data.split("|");
					$("#s_total").val(data_array[0]);
					$("#outstanding_value").val(data_array[1]);
				}else{
					alert('计算total出错');
				}
			}
		})
	})
}


//给product item 加序号
function generateIndex(){
	var index = 0;//隐藏的也有.repeat，所以从0开始
	$('.repeat').each(function(){	
		var index_td = $(this).find("#index");
		index_td.html(index++);
	})
}



//product item onmouseover 事件
function product_itme_mouseover(obj){
	$(obj).find("#index").addClass("showDragHandle");
}

//product item onmouseout 事件
function product_item_mouseout(obj){
	$(obj).find("#index").removeClass("showDragHandle");
}

//product item onmouseup 事件
function product_item_mouseup(obj){
	generateIndex();
}

//modifyproduct 中的bom链接，判断是否有bom记录，有才跳转到formdetail
function bomConfirm(obj){
	var my_val = $(obj).attr("id");
	var qs = 'ajax=1&act=ajax-judge_bom_id&&value='+my_val;
	$.ajax({
		type: "GET",
		url: "index.php",
		data: qs,
		cache: false,
		dataType: "html",
		error: function(){
			alert('系统错误，查询bom id失败');
		},
		success: function(data){
			if(data.indexOf('yes') >= 0){
				alert('不存在此 BOM ID');
			}else if(data.indexOf('no-') >= 0){
				//window.location.href 这个不能打开新窗口，但 window.open 可以
				window.open('?act=formdetail&gid='+my_val);				
			}
		}
	})
}

//重量输入框blur后，跟新total_weight
function weight_blur(){	
	UpdateTotalWeight();//更新总重量
}

//更新总重量
function UpdateTotalWeight(){
	var vTotalWeight = 0;//总重量的初始值为0;               
	var vTable = $("#tbody");//得到表格的jquery对象
	var vTotal = $("#totalW"); //得到总重量对象
	var vtxtAfters = vTable.find("[id^='weight']");//得到所有的重量对象;
	vtxtAfters.each(
		function()
		{
			var vTempValue = $(this).val();	
			vTempValue = vTempValue.replace(/,/g,"");
			if(vTempValue == "")
			{
				vTempValue = 0;
			} 
			//这了用accAdd就不行。。。
			vTotalWeight += parseFloat(vTempValue);//這個parseFloat，好像會出好多小數點後面的0
		}
	);//遍历结束
	vTotal.html(vTotalWeight);  		
}


//添加invoice no.到invoice group textarea
function addInvoice(){
	var selectText = $("#invoice_container li").parent().parent().prev().val();
	if(selectText != '- select -'){
		var group_textarea = $('#invoice_group_textarea');
		var group_textarea_value = group_textarea.val();
		if(group_textarea_value.indexOf(selectText) < 0){
			group_textarea.val(group_textarea_value+selectText+",");
		}else{
			alert('此 Invoice NO. 已添加，请不要重复添加。');	
		}
	}else{
		alert('Please choose Invoice NO.');	
	}
}

//从group textarea 删除 group
function delInvoice(){
	var selectText = $("#invoice_container li").parent().parent().prev().val();
	if(selectText != '- select -'){
		var group_textarea = $('#invoice_group_textarea');
		var group_textarea_value = group_textarea.val();
		if(group_textarea_value.indexOf(selectText) < 0){
			alert('此 Invoice NO. 未添加，无需删除。');
		}else{
			group_textarea.val(group_textarea_value.replace(selectText+",", ''));
		}
	}else{
		alert('Please choose Invoice NO.');	
	}
}


//sys 或 fty 用户填写QC表添加Factory PO(点击添加图标添加)
function changeQc(obj){
	var selectText = obj.selectedVal;
	if(selectText != '- select -'){	
		//每次选择清空之前内容
		$('.qc').empty();
		var judge = $('tbody #'+selectText).length;
		if(judge == 0){
			//先清空tbody下的内容，以防一直往里面添加
			//$('.delivery').empty();
			var qs = 'ajax=qc&act=ajax-qc_search_purchase&value='+selectText;
			$.ajax({
				type: "GET",
				url: "index.php",
				data: qs,
				cache: false,
				dataType: "html",
				error: function(){
					alert('系统错误，查询订单失败');
				},
				success: function(data){
					if(data.indexOf('!no-') < 0){
						var all_html = '';
						var d_index = 0;
						
						//20130403 把这个放for里面，害苦我了，每次new都把前面的冲掉了，难怪老提示未定义！！！
						var swfu = new Array();

						//20120703 要获取当前<tr id="PO***">的个数，以确定下一个的编号是什么
						d_index = $("tr[id*='PO']").length;//减1是因为编号是从0开始的
						var data_purchase_item = data.split(",");
						for(var i in data_purchase_item){
							
							var item_index = (parseInt(d_index)+parseInt(i));

							all_html = '';
							var data_purchase_each = data_purchase_item[i].split("|");
							all_html += '<tr class="formtitle" name="'+selectText+'" id="'+data_purchase_each[0]+'">';
								
							//款号 & 总件数
							all_html +=	'<td>'+data_purchase_each[0]+'<br />订单总数：'+data_purchase_each[2]+'<br />';
							//product图片
							if(data_purchase_each[1].length <= 0 || data_purchase_each[1].length == ''){
								all_html += '<img src="../images/nopic.gif" border="0" width="80" height="60" />';
							}else{
								all_html +=	'<a href="/sys/upload/lux/'+data_purchase_each[1]+'" target="_blank"><img src="/sys/upload/luxsmall/s_'+data_purchase_each[1]+'" /></a>';
							}
                            all_html += '</td>';
																							
							//总件数
							//all_html +=	'<td><div class="formfield"><input id="quantity'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+10)+'" strlen="1,10" maxlength="10" onblur="" value="'+data_purchase_each[2]+'" required="1" restrict="number" name="quantity'+item_index+'"></div></td>';
							
							//已检数
							//all_html +=	'<td><div class="formfield"><input id="num_all'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+11)+'" strlen="1,10" maxlength="10" onblur="" value="" restrict="number" name="num_all'+item_index+'"></div></td>';

							//问题
							all_html +=	'<td><div id="divremarka'+item_index+'" class="formfield"><textarea id="remarka'+item_index+'" class="tainit tainit"  tabindex="'+(10*i+12)+'" strlen="1,200" style="width:300px" rows="2" value="" name="remarka'+item_index+'"></textarea></div><div id="uploaded'+item_index+'"></div><div class="fieldset flash" id="fsUploadProgress'+item_index+'"><span class="legend">图片上传队列</span></div><div id="divMovieContainer"><span id="spanButtonPlaceHolder'+item_index+'"></span><input type="button" value="开始上传" id="upload_'+item_index+'" style="margin-left: 2px; font-size: 8pt; height: 29px;" /></div></td>';
							
							//例子里的 multiinstancedemo 还没调好，估计是某些路径不行
							//all_html += '<td><div id="divsize_l'+item_index+'" class="formfield"><textarea id="size_l'+item_index+'" class="tainit tainit"  tabindex="'+(10*i+12)+'" strlen="1,200" style="width:300px" rows="2" value="" name="size_l'+item_index+'"></textarea></div><div class="fieldset flash" id="fsUploadProgress'+item_index+'"><span class="legend">上传队列</span></div><div style="padding-left: 5px;"><span id="spanButtonPlaceholder'+item_index+'"></span><input id="btnCancel'+item_index+'" type="button" value="Cancel Uploads" onclick="cancelQueue(swfu['+item_index+']);" disabled="disabled" style="margin-left: 2px; height: 22px; font-size: 8pt;" /><br /></div></td>';
								
							//程度：数量
							all_html +=	'<td><div class="formfield">接受：<input id="num_pass'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="num_pass'+item_index+'"></div><div class="formfield">轻微：<input id="num_a'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="num_a'+item_index+'"></div><div class="formfield">中度：<input id="num_b'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="num_b'+item_index+'"></div><div class="formfield">严重：<input id="num_c'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="num_c'+item_index+'"></div><div class="formfield" style="text-align: left;">总检(件)：<span id="num_all"></span></div></td>';
								
							//结果/行动
							all_html +=	'<td><div id="divis_pass'+item_index+'" class="selectfield"><select id="is_pass'+item_index+'" style="width:100px" name="is_pass'+item_index+'" size="1"><option value="1">合格</option><option value="2">不合格</option><option value="3" selected>待验</option></select></div><div id="divremarkb'+item_index+'" class="formfield"><textarea id="remarkb'+item_index+'" class="tainit tainit" type="text" style="width:100px" rows="2" tabindex="'+(10*i+14)+'" strlen="1,200" maxlength="200" value=""  name="remarkb'+item_index+'"></textarea></div></td>';
								
							//工厂回应
							all_html +=	'<td><div id="divremarkc'+item_index+'" class="formfield"><textarea id="remarkc'+item_index+'" class="tainit tainit" type="text" style="width:100px" rows="2" tabindex="'+(10*i+15)+'" strlen="1,200" maxlength="200" value="" name="remarkc'+item_index+'"></textarea></div></td>';
							//操作
							//all_html +=	'<td><img id="'+data_purchase_each[2]+'" title="复制" style="opacity: 0.5;" onclick="copyProduct(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/copy_small.png"></td><td><img id="'+data_purchase_each[2]+'" title="删除" style="opacity: 0.5;" onclick="delProduct(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del2_small.png"></td>';

							all_html += '</tr>';
							$('.qc').append(all_html);

							//swfupload
							var settings = {
								flash_url : "/ui/swfupload/swfupload.swf",
								flash9_url : "/ui/swfupload/swfupload_fp9.swf",
								upload_url: "/sys/model/com/swfupload_upload.php",
								post_params: {"PHPSESSID" : "", "my_index" : item_index},
								file_size_limit : "20 MB",
								file_types : "*.jpg;*.gif;*.png",
								file_types_description : "Only allow format jpg|gif|png",
								file_upload_limit : 100,
								file_queue_limit : 0,
								//file_post_name : "Filedata",
								custom_settings : {
									progressTarget : "fsUploadProgress"+item_index,
									cancelButtonId : "btnCancel"+item_index
								},
								debug: true,

								// Button settings
								button_image_url: "/ui/swfupload/images/TestImageNoText_65x29.png",
								button_width: "65",
								button_height: "29",
								button_placeholder_id: "spanButtonPlaceHolder"+item_index,
								button_text: '<span class="theFont">浏览</span>',
								button_text_style: ".theFont { font-size: 16; }",
								button_text_left_padding: 12,
								button_text_top_padding: 3,

								// The event handler functions are defined in handlers.js
								swfupload_preload_handler : preLoad,
								swfupload_load_failed_handler : loadFailed,
								file_queued_handler : fileQueued,
								file_queue_error_handler : fileQueueError,
								upload_start_handler : uploadStart,
								upload_progress_handler : uploadProgress,
								upload_error_handler : uploadError,
								upload_success_handler : uploadSuccess


								/* 例子里的 multiinstancedemo 还没调好，估计是某些路径不行
								// Backend Settings
								upload_url: "/sys/model/com/swfupload_upload.php",
								post_params: {"PHPSESSID" : ""},

								// File Upload Settings
								file_size_limit : "102400",	// 100MB
								file_types : "*.*",
								file_types_description : "All Files",
								file_upload_limit : 10,
								file_queue_limit : 0,

								// Event Handler Settings (all my handlers are in the Handler.js file)
								swfupload_preload_handler : preLoad,
								swfupload_load_failed_handler : loadFailed,
								file_dialog_start_handler : fileDialogStart,
								file_queued_handler : fileQueued,
								file_queue_error_handler : fileQueueError,
								file_dialog_complete_handler : fileDialogComplete,
								upload_start_handler : uploadStart,
								upload_progress_handler : uploadProgress,
								upload_error_handler : uploadError,
								upload_success_handler : uploadSuccess,
								upload_complete_handler : uploadComplete,

								// Button Settings
								button_image_url : "/ui/swfupload/images/XPButtonUploadText_61x22.png",
								button_placeholder_id : "spanButtonPlaceholder1"+item_index,
								button_width: 61,
								button_height: 22,

								// Flash Settings
								flash_url : "/ui/swfupload/swfupload.swf",
								flash9_url : "/ui/swfupload/swfupload_fp9.swf",

								custom_settings : {
									progressTarget : "fsUploadProgress1"+item_index,
									cancelButtonId : "btnCancel1"+item_index
								},

								// Debug Settings
								debug: true
								*/
							};

							//alert(item_index);
							swfu[item_index] = new SWFUpload(settings);
							//eval("swfu"+item_index) = new SWFUpload(settings);

                            //20131121 ！！！插件设定new SWFUpload必须是已经有表单，所以要new的js要在表单append的后面，但是onclick事件不能加在表单里面，因为append之前还没有new SWFUpload，所以swfu还未定义，所以要在swfu定义以后再绑定click事件！！！这个问题又卡了我好久。。。。
                            $('#upload_'+item_index).click(function(){
                                //！！！点击上传的时候item_index的值已经固定为最后的值了，所以要重新获取当前上传块的id
                                var upload_id = $(this).attr('id');
                                var my = upload_id.split("_");
                                swfu[my[1]].startUpload();
                            })
						}
						//all_html += '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td>运费：</td><td><div class="formfield"><input id="express_cost" class="textinit textinitb" type="text" style="width:40px" tabindex="" strlen="1,10" maxlength="10" value="" required="1" restrict="number" name="express_cost"></div></td><td></td><td></td><td></td><td></td><td></td></tr>';
						//all_html += '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td>合计：</td><td id="total" align="right"></td><td></td><td></td><td></td><td></td><td></td></tr>';
						
						//all_html += '<tr><td colspan="13"></td><td><div id="divsubmitbutton" class="buttonfield"><input id="submitbutton" class="defautButton" type="submit" tabindex="" value="提交" name="submitbutton"></div></td></tr>';
						
						//多了这个空行不好算item_index，所以去掉
						//all_html += '<tr id="'+selectText+'"><td colspan="15">&nbsp;</td></tr>';
						
						//为了swfupload已经放到前面foreach里了
						//$('.qc').append(all_html);
												
						//删除已经添加的订单号选项
						//如果添加的时候删除了这个选项，那么删除的时候就不能用到了
						//$("#fty_id").removeOption(selectText);
						
						//！！这个必须加上的，这个原来是页面一加载完，就在 form->end() 的 initAll() 里面初始化所有的表单的，但是现在使用的是jquery的事件触发生成表单，所以就不能自动初始化所有表单。
						//formRebuilder();
						
						
						//在加上 selectbox 就不能触发 $("#fty_id_container li").click 了，不知道为什么，先去掉			
						//$('select').not('.special').selectbox();				
						//$('body').append('<div id="mytt"></div>').children('#mytt')
							//.click(function(){$(this).hide(); clearTimeout(tooltipClock);})
							//.hover(function () {$(this).addClass("heavy" + $(this).data('reverse'));},
							//function () {$(this).removeClass("heavy" + $(this).data('reverse'));});
						
						$('form').submit(checkSubmit).attr('autocomplete', 'off');
						$(':input').each(function(){bindEventFor(this);});
						//$(':submit,:reset,:button').not('.smallbutton').addClass('defautButton');
						
						//！有这个选择时间的插件才能用，为什么要加这一句，同上
						//$('#d_date').click(function(){WdatePicker();});
						
						//快递费输入框blur后，跟新total_all
						//express_blur();
						
					}else{
						alert('查询订单号失败！');
					}
				}
			})
		}else{
			alert('此订单号已添加。请不要重复添加。');	
		}
	}
}

//20131202
function changeQcNew(obj){
    //20140211 改为不能直接add qc report 而要从，qc schedule点过来带参数，才能添加，现在用上面的获取值不用下面的了
    var selectText = obj.val();
    //var selectText = obj.selectedVal;
    if(selectText != '- select -'){
        //每次选择清空之前内容
        $('.qc').empty();
        var judge = $('tbody #'+selectText).length;
        if(judge == 0){
            //先清空tbody下的内容，以防一直往里面添加
            //$('.delivery').empty();
            var qs = 'ajax=qc&act=ajax-qc_search_purchase&value='+selectText;
            $.ajax({
                type: "GET",
                url: "index.php",
                data: qs,
                cache: false,
                dataType: "html",
                error: function(){
                    alert('系统错误，查询订单失败');
                },
                success: function(data){
                    if(data.indexOf('!no-') < 0){
                        var all_html = '';

                        var data_purchase_item = data.split(",");
                        for(var i in data_purchase_item){

                            var item_index = parseInt(i);

                            all_html = '';
                            var data_purchase_each = data_purchase_item[i].split("|");
                            all_html += '<tr class="formtitle">';

                            //款号 & 总件数 （包括两个隐藏表单）
                            all_html +=	'<td><input type="hidden" value="'+trim(data_purchase_each[0])+'" name="pid'+item_index+'" />'+data_purchase_each[0]+'<br />订单总数：'+data_purchase_each[2]+'<br />';
                            //product图片
                            if(data_purchase_each[1].length <= 0 || data_purchase_each[1].length == ''){
                                all_html += '<img src="../images/nopic.gif" border="0" width="80" height="60" />';
                            }else{
                                all_html +=	'<a href="/sys/upload/lux/'+data_purchase_each[1]+'" target="_blank"><img src="/sys/upload/luxsmall/s_'+data_purchase_each[1]+'" /></a>';
                            }
                            all_html += '</td>';

                            //问题
                            all_html +=	'<td><div id="divremarka'+item_index+'" class="formfield"><textarea id="remarka'+item_index+'" class="tainit tainit" tabindex="'+(10*i+12)+'" strlen="1,500" maxlength="500" style="width:300px" rows="2" value="" name="remarka'+item_index+'"></textarea><img onclick="add_upload_field(this)" id="'+item_index+'" style="cursor: pointer; margin:0px 5px 25px;" src="../images/add_small.png" id="up_button'+item_index+'" /></div><div id="divupload_photo'+item_index+'" class="formfield"></div></td>';

                            //程度：数量
                            all_html +=	'<td><div class="formfield">接受：<input id="num_pass'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="num_pass'+item_index+'"></div><div class="formfield">轻微：<input id="num_a'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="num_a'+item_index+'"></div><div class="formfield">中度：<input id="num_b'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="num_b'+item_index+'"></div><div class="formfield">严重：<input id="num_c'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="num_c'+item_index+'"></div><div class="formfield" style="text-align: left;">总检(件)：<span id="num_all"></span></div></td>';

                            //结果/行动
                            all_html +=	'<td><div id="divis_pass'+item_index+'" class="selectfield" style="float:left;"><select id="is_pass'+item_index+'" style="width:100px" name="is_pass'+item_index+'" size="1"><option value="1">合格</option><option value="2">不合格</option><option value="3" selected>待验</option></select></div><br /><br /><div id="divremarkb'+item_index+'" class="formfield"><textarea id="remarkb'+item_index+'" class="tainit tainit" type="text" style="width:100px" rows="2" tabindex="'+(10*i+14)+'" strlen="1,500" maxlength="500" value="" name="remarkb'+item_index+'"></textarea></div></td>';

                            //工厂回应
                            all_html +=	'<td><div id="divremarkc'+item_index+'" class="formfield"><textarea id="remarkc'+item_index+'" class="disabled tainit tainit" type="text" style="width:100px" rows="2" tabindex="'+(10*i+15)+'" strlen="1,500" maxlength="500" value="" name="remarkc'+item_index+'" readonly="readonly"></textarea></div></td>';

                            all_html += '</tr>';
                            $('.qc').append(all_html);
                        }

                        $("#tbody").find("[id^='is_pass']").not('.special').selectbox();
                        $('form').submit(checkSubmit).attr('autocomplete', 'off');
                        $(':input').each(function(){bindEventFor(this);});
                        //$(':submit,:reset,:button').not('.smallbutton').addClass('defautButton');

                        //！有这个选择时间的插件才能用，为什么要加这一句，同上
                        //$('#d_date').click(function(){WdatePicker();});

                    }else{
                        alert('查询订单号失败 或 此FTY ID已添加过QC REPORT ！');
                    }
                }
            })
        }else{
            alert('此订单号已添加。请不要重复添加。');
        }
    }
}

//20131203
function add_upload_field(obj){
    var id = $(obj).attr('id');
    var timestamp = Date.parse(new Date())/1000;
    $('#divupload_photo'+id).append('<div><input type="file" name="photo_'+id+'[]" id="photo_'+id+'_'+timestamp+'" /><img style="cursor: pointer; width: 16px;" src="../images/del_small.png" onclick="del_upload_field(this)" required=1 /></div>');
}
function del_upload_field(obj){
    $(obj).parent().remove();
}


//出货单箱数输入框 blur 事件，检测其他行的箱数输入框是否有相同的输入数字，如果有，则将此行的重量等四格变灰不让输入
function boxnumBlur(obj){
	var box_num_id = $(obj).attr("id");
	var sign = box_num_id.substring(7);
	var box_num = $(obj).val();
	if(box_num != ''){
		var vTable = $("#tbody");//得到表格的jquery对象
		var vField = vTable.find("[id^='box_num']");//得到所有的 箱数 输入框对象
		var judge = 0;
		vField.each(
			function()
			{
				var vId = $(this).attr("id");
				if(vId != box_num_id){
					var vValue = $(this).val();	
					if(vValue == box_num)
					{
						judge = 1
					}
				}
			}
		);//遍历结束
		
		if(judge == 1){
            //20130813 加了必填属性，所以改为灰色后要remove必填属性
            //20130905 且清空里面填的数值
			$("#weight"+sign).attr("readonly", "readonly").addClass("readonly").removeAttr("required").val('');
			$("#size_l"+sign).attr("readonly", "readonly").addClass("readonly").removeAttr("required").val('');
			$("#size_w"+sign).attr("readonly", "readonly").addClass("readonly").removeAttr("required").val('');
			$("#size_h"+sign).attr("readonly", "readonly").addClass("readonly").removeAttr("required").val('');
		}else{
			$("#weight"+sign).removeAttr("readonly").removeClass("readonly");
			$("#size_l"+sign).removeAttr("readonly").removeClass("readonly");
			$("#size_w"+sign).removeAttr("readonly").removeClass("readonly");
			$("#size_h"+sign).removeAttr("readonly").removeClass("readonly");			
		}
	}else{
		$("#weight"+sign).removeAttr("readonly").removeClass("readonly");
		$("#size_l"+sign).removeAttr("readonly").removeClass("readonly");
		$("#size_w"+sign).removeAttr("readonly").removeClass("readonly");
		$("#size_h"+sign).removeAttr("readonly").removeClass("readonly");			
	}
}

function changeClientCompany(){
	var company = $('#client_company').val();
	var qs = 'ajax=client&act=ajax-search_client_address&value='+escape(company);//ajax傳中文在除firefox以外的瀏覽器都亂碼
	$.ajax({
		type: "GET",
		url: "index.php",
		data: qs,
		cache: false,
		dataType: "html",
		error: function(){
			alert('系统错误，查询客户地址失败！');
		},
		success: function(data){
			if(data.indexOf('no-') < 0){
				$('#client_address').val(trim(data));//会在前面多个换行，所以用trim去掉
			}
		}
	})
}

//20140323
function changeWarehouse(){
    var wh_name = $('#wh_id').val();
    var temp = wh_name.split("|");
    var qs = 'ajax=client&act=ajax-search_wh_address&value='+temp[1];
    $.ajax({
        type: "GET",
        url: "index.php",
        data: qs,
        cache: false,
        dataType: "html",
        error: function(){
            alert('系统错误，查询地址失败！');
        },
        success: function(data){
            if(data.indexOf('no-') < 0){
                $('#address').val(trim(data));//会在前面多个换行，所以用trim去掉
            }
        }
    })
}

//sys packing list item (点击添加图标从invoice添加)
function addPackingList(){		
	var selectText = $("#vid_container li").parent().parent().prev().val();		 
	if(selectText != '- select -'){
		var judge = $('tbody #'+selectText).length;
		if(judge == 0){
			var qs = 'ajax=invoice_to_packing_list&act=ajax-search_invoice_to_packing_list&value='+selectText;
			$.ajax({
				type: "GET",
				url: "index.php",
				data: qs,
				cache: false,
				dataType: "html",
				error: function(){
					alert('System error. Search invoice failure!');
				},
				success: function(data){
					if(data.indexOf('!no-') < 0){
						var all_html = '';
						var temp = data.split(" @ ");
						var invoice_info = temp[0].split("|");
						
						$('#ship_to').val(trim(invoice_info[0]));
						$('#tel').val(invoice_info[1]);
						$('#unit').val(invoice_info[2]);
						$('#reference').val(invoice_info[3]);
						
						var data_invoice_item = temp[1].split(",");
						
						//箱数总和（！这个还不能放在底下，因为copy后，item会在这个的后面，这样就没法计算item数了.放前面也不行，而且这个值提交的是旧的箱数总和，没有用，去掉了）
						//all_html += '<input type="hidden" value="'+$("#total_cart").html()+'" name="total_cart" />';
						
						for(var i in data_invoice_item){
							//mod 20130127 改为用精确到毫秒的时间戳来区别表单项
							var item_index = (new Date()).valueOf()+i;
							var data_invoice_each = data_invoice_item[i].split("|");
							all_html += '<tr name="'+selectText+'" id="'+selectText+'">'
								
							//Invoice NO.
							all_html += '<td>'+selectText+'</td>';	
								
							//箱数 tabindex都未完成！！
							all_html += '<td><div class="formfield"><input id="cartno'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+7)+'" strlen="1,10" maxlength="10" value="" restrict="number" required="1" name="cartno'+item_index+'" onblur="cart_blur(this)" ></div></td>';
								
							//客号
							all_html += '<td>'+data_invoice_each[1]+'</td>';
								
							//pid
							all_html +=	'<td>'+data_invoice_each[0]+'</td>';
								
							//数量
							all_html += '<td><div class="formfield"><input id="qty'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+7)+'" strlen="1,10" maxlength="10" restrict="number" required="1" name="qty'+item_index+'" onblur="UpdateTotalQty()" value="'+data_invoice_each[2]+'" ></div></td>';
							
							//重量
							all_html +=	'<td><div class="formfield"><input id="gross_weight'+item_index+'" class="textinit textinitb" type="text" style="width:80px" tabindex="'+(10*i+11)+'" strlen="1,10" maxlength="10" onblur="UpdateTotalWeight()" value="" restrict="number" name="gross_weight'+item_index+'"></div></td>';
								
							//MEASURMENT 计量
							all_html +=	'<td><div class="formfield"><input id="size_l'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+12)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="size_l'+item_index+'" onblur="blurSizeL(this)" ></div></td> <td><div class="formfield"><input id="size_w'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="size_w'+item_index+'" onblur="blurSizeW(this)" ></div></td> <td><div class="formfield"><input id="size_h'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+14)+'" strlen="1,10" maxlength="10" value="" restrict="number" name="size_h'+item_index+'" onblur="blurSizeH(this)" ></div></td>';
								
							//CBM 长*宽*高 单位米 用前面的计量值计算
							all_html +=	'<td id="cbm"></td>';
							//操作
							all_html +=	'<td><img id="'+data_invoice_each[0]+'" name="'+selectText+'" title="copy" style="opacity: 0.5;" onclick="copyItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/copy_small.png"></td><td><img id="'+data_invoice_each[0]+'" name="'+selectText+'" title="delete" style="opacity: 0.5;" onclick="delItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del2_small.png"></td>';
							
							//为了需要设置的隐藏表单
							all_html += '<input type="hidden" value="'+selectText+'" id="hidden_ref_no'+item_index+'" name="hidden_ref_no'+item_index+'" /><input type="hidden" value="'+data_invoice_each[0]+'" id="hidden_pid'+item_index+'" name="hidden_pid'+item_index+'" />';

							all_html += '</tr>';
						}	
						
						$('.packing_list').append(all_html);
						
						//UpdateTotal();
						UpdateTotalQty();
																		
						$('form').submit(checkSubmit).attr('autocomplete', 'off');
						$(':input').each(function(){bindEventFor(this);});
						
						//！有这个选择时间的插件才能用，为什么要加这一句，同上
						$('#d_date').click(function(){WdatePicker();});
						
					}else{
						alert('Search invoice failure!');
					}
				}
			})
		}else{
			alert('Re-post error!');	
		}
	}else{
		alert('Please choose invoice NO.');
	}
}

//sys packing list item (点击删除图标从invoice添加)
function delPackingList(){
	var selectText = $("#vid_container li").parent().parent().prev().val();
	if(selectText != '- select -'){
		var judge = $('tbody [name='+selectText+']').length;
		if(judge != 0){
			//改为name保存Invoice NO.了，因为ID用来放ITEM#了，因为复制单个ITEM信息的时候要用到ID
			$('tbody [name='+selectText+']').each(function(){
				$(this).remove();
			});
			//UpdateTotal();
		}else{
			alert('No need to delete!');	
		}
	}else{
		alert('Please choose invoice NO.(201303071035)');
	}
}

//删除出货单中的product
function delItem(obj){
	//$("#"+obj.id).remove();
	// ！！！！$(obj) 这个是关键，从dom对象转为jquery对象
	$(obj).parent().parent().remove();
	
	UpdateTotalQty();//更新总数量
	UpdateTotalWeight();//更新总重量
	UpdateTotalCbm();
}

//复制出货单中的product
function copyItem(obj){
	//var my = $("#"+obj.id);
	var my = $(obj).parent().parent();
	var pid = my.children().next().next().next().html();
	var timestamp = Date.parse(new Date())/1000;
	var myclone = my.clone(true);
	$("<tr id="+$(obj).attr('name')+" name="+$(obj).attr('name')+">" + myclone.html() + "</tr>").insertAfter(my);
	//alert(myclone.html());
	
	myclone.find("input").each(function(){
		var myid = this.id;
		//要先改name，再改id，如果先改id，改name的时候就找不到了
		$('#'+this.id).attr('name', myid + timestamp);
		$('#'+this.id).attr('id', myid + timestamp);
	});
	
	UpdateTotalQty();//更新总数量
	UpdateTotalWeight();//更新总重量
	UpdateTotalCbm();
}

//更新ITEM 重量总和
function UpdateTotalWeight()
{
	var vTotalWeight = 0;//总重量的初始值为0;               
	var vTable = $("#tbody");//得到表格的jquery对象
	var vTotal = $("#total_weight"); //得到总重量对象
	var vtxtAfters = vTable.find("[id^='gross_weight']");//得到所有的重量对象;
	vtxtAfters.each(
		function()
		{
			var vTempValue = $(this).val();	
			vTempValue = vTempValue.replace(/,/g,"");
			if(vTempValue == "")
			{
				vTempValue = 0;
			} 
			//这了用accAdd就不行。。。
			vTotalWeight += parseFloat(vTempValue);//這個parseFloat，好像會出好多小數點後面的0
		}
	);//遍历结束
	vTotal.html(vTotalWeight);  
}

//更新ITEM 数量总和
function UpdateTotalQty()
{
	var vTotalQty = 0;//总数量的初始值为0;               
	var vTable = $("#tbody");//得到表格的jquery对象
	var vTotal = $("#total_qty"); //得到总数量对象
	var vtxtAfters = vTable.find("[id^='qty']");//得到所有的qty对象;
	vtxtAfters.each(
		function()
		{
			var vTempValue = $(this).val();	
			vTempValue = vTempValue.replace(/,/g,"");
			if(vTempValue == "")
			{
				vTempValue = 0;
			} 
			//这了用accAdd就不行。。。
			vTotalQty += parseFloat(vTempValue);//這個parseFloat，好像會出好多小數點後面的0
		}
	);//遍历结束
	vTotal.html(vTotalQty);  
}

//因为accMul这个只能有两个参数相乘，所以这里就不用了
//!!!!!先把小数乘10000，变为整数后，再相乘（或相加），结果在除，大小不变就行，这样就不会出现N多位的浮点数
//缺点1：如果设定的10000还不能把小数变为整数就不行了
//缺点2： 小数后太多位了，还是会转为科学计数法显示
function blurSizeL(obj){
	var size_l_value = $(obj).val();
	var size_w_value = $(obj).parent().parent().next().children().children().val();
	var size_h_value = $(obj).parent().parent().next().next().children().children().val();
	var cbm = $(obj).parent().parent().next().next().next();
	var cbm_value = (size_l_value*10000)*(size_w_value*10000)*(size_h_value*10000)/1000000000000000000;
    cbm_value = cbm_value.toFixed(3);
	cbm.html(cbm_value);
	UpdateTotalCbm()
}
function blurSizeW(obj){
	var size_w_value = $(obj).val();
	var size_l_value = $(obj).parent().parent().prev().children().children().val();
	var size_h_value = $(obj).parent().parent().next().children().children().val();
	var cbm = $(obj).parent().parent().next().next();
	var cbm_value = (size_l_value*10000)*(size_w_value*10000)*(size_h_value*10000)/1000000000000000000;
    cbm_value = cbm_value.toFixed(3);
	cbm.html(cbm_value);
	UpdateTotalCbm()
}
function blurSizeH(obj){
	var size_h_value = $(obj).val();
	var size_l_value = $(obj).parent().parent().prev().prev().children().children().val();
	var size_w_value = $(obj).parent().parent().prev().children().children().val();
	var cbm = $(obj).parent().parent().next();
	var cbm_value = (size_l_value*10000)*(size_w_value*10000)*(size_h_value*10000)/1000000000000000000;
    cbm_value = cbm_value.toFixed(3);
	cbm.html(cbm_value);
	UpdateTotalCbm()
}

//更新CBM总和
function UpdateTotalCbm()
{
	var vTotalCbm = 0;//总数量的初始值为0;               
	var vTable = $("#tbody");//得到表格的jquery对象
	var vTotal = $("#total_cbm"); //得到cbm对象
	var vtxtAfters = vTable.find("[id^='cbm']");//得到所有的cbm对象;
	vtxtAfters.each(
		function()
		{
			var vTempValue = $(this).html();	
			vTempValue = vTempValue.replace(/,/g,"");
			if(vTempValue == "")
			{
				vTempValue = 0;
			} 
			//这了用accAdd避免小数点后多了很多位
			vTotalCbm = accAdd2(parseFloat(vTotalCbm), parseFloat(vTempValue));
		}
	);//遍历结束
	vTotal.html(vTotalCbm.toFixed(3));
}
function accAdd2(arg1,arg2){
  var r1,r2,m;
  try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
  try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
  m=Math.pow(10,Math.max(r1,r2));
  return (arg1*m+arg2*m)/m
}

function cart_blur(obj){
	var vTable = $("#tbody");//得到表格的jquery对象
	var vTotal = $("#total_cart"); //得到箱数对象
	var vtxtAfters = vTable.find("[id^='cart']");//得到所有的箱数对象
	var myarr = new Array();
	vtxtAfters.each(
		function()
		{
			var vTempValue = $(this).val();	
			vTempValue = vTempValue.replace(/,/g,"");
			if(vTempValue != ''){			
				if($.inArray(vTempValue, myarr) < 0){
					myarr.push(vTempValue)
				}
			}
		}
	);//遍历结束
	vTotal.html(myarr.length);	
}

//sys packing list item (点击添加图标从delivery添加)
function addPackingList2(){		
	var selectText = $("#did_container li").parent().parent().prev().val();		 
	if(selectText != '- select -'){
		var judge = $('tbody #'+selectText).length;
		if(judge == 0){
			var qs = 'ajax=delivery_to_packing_list&act=ajax-search_delivery_to_packing_list&value='+selectText;
			$.ajax({
				type: "GET",
				url: "index.php",
				data: qs,
				cache: false,
				dataType: "html",
				error: function(){
					alert('System error. Search delivery failure!');
				},
				success: function(data){
					if(data.indexOf('!no-') < 0){
						var all_html = '';
						var temp = data.split(" @ ");
						var delivery_info = temp[0].split("|");
						
						$('#ship_to').val(trim(delivery_info[0]));
						$('#tel').val(delivery_info[1]);
						$('#unit').val(delivery_info[2]);
						$('#reference').val(delivery_info[3]);
						
						var data_delivery_item = temp[1].split(",");
						
						//箱数总和（！这个还不能放在底下，因为copy后，item会在这个的后面，这样就没法计算item数了.放前面也不行，而且这个值提交的是旧的箱数总和，没有用，去掉了）
						//all_html += '<input type="hidden" value="'+$("#total_cart").html()+'" name="total_cart" />';
						
						for(var i in data_delivery_item){
							//mod 20130127 改为用精确到毫秒的时间戳来区别表单项
							var item_index = (new Date()).valueOf()+i;
							var data_delivery_each = data_delivery_item[i].split("|");
							all_html += '<tr name="'+selectText+'" id="'+selectText+'">';
								
							//Delivery NO.
							all_html += '<td>'+selectText+'</td>';	
								
							//箱数 tabindex都未完成！！
							all_html += '<td><div class="formfield"><input id="cartno'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+7)+'" strlen="1,10" maxlength="10" value="'+data_delivery_each[7]+'" restrict="number" required="1" name="cartno'+item_index+'" onblur="cart_blur(this)" ></div></td>';
								
							//客号
							all_html += '<td>'+data_delivery_each[1]+'</td>';
								
							//pid
							all_html +=	'<td>'+data_delivery_each[0]+'</td>';
								
							//数量
							all_html += '<td><div class="formfield"><input id="qty'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+7)+'" strlen="1,10" maxlength="10" restrict="number" required="1" name="qty'+item_index+'" onblur="UpdateTotalQty()" value="'+data_delivery_each[2]+'" ></div></td>';
							
							//重量
							all_html +=	'<td><div class="formfield"><input id="gross_weight'+item_index+'" class="textinit textinitb" type="text" style="width:80px" tabindex="'+(10*i+11)+'" strlen="1,10" maxlength="10" onblur="UpdateTotalWeight()" value="'+data_delivery_each[3]+'" restrict="number" name="gross_weight'+item_index+'"></div></td>';
								
							//MEASURMENT 计量
							all_html +=	'<td><div class="formfield"><input id="size_l'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+12)+'" strlen="1,10" maxlength="10" value="'+data_delivery_each[4]+'" restrict="number" name="size_l'+item_index+'" onblur="blurSizeL(this)" ></div></td> <td><div class="formfield"><input id="size_w'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+13)+'" strlen="1,10" maxlength="10" value="'+data_delivery_each[5]+'" restrict="number" name="size_w'+item_index+'" onblur="blurSizeW(this)" ></div></td> <td><div class="formfield"><input id="size_h'+item_index+'" class="textinit textinitb" type="text" style="width:40px" tabindex="'+(10*i+14)+'" strlen="1,10" maxlength="10" value="'+data_delivery_each[6]+'" restrict="number" name="size_h'+item_index+'" onblur="blurSizeH(this)" ></div></td>';
								
							//CBM 长*宽*高 单位米 用前面的计量值计算
							all_html +=	'<td id="cbm"></td>';
							//操作
							all_html +=	'<td><img id="'+data_delivery_each[0]+'" name="'+selectText+'" title="copy" style="opacity: 0.5;" onclick="copyItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/copy_small.png"></td><td><img id="'+data_delivery_each[0]+'" name="'+selectText+'" title="delete" style="opacity: 0.5;" onclick="delItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del2_small.png"></td>';
							
							//为了需要设置的隐藏表单
							all_html += '<input type="hidden" value="'+selectText+'" id="hidden_delivery_no'+item_index+'" name="hidden_delivery_no'+item_index+'" /><input type="hidden" value="'+data_delivery_each[0]+'" id="hidden_pid'+item_index+'" name="hidden_pid'+item_index+'" />';

							all_html += '</tr>';
						}	
						
						$('.packing_list').append(all_html);
						
						//UpdateTotal();
						UpdateTotalQty();
																		
						$('form').submit(checkSubmit).attr('autocomplete', 'off');
						$(':input').each(function(){bindEventFor(this);});
						
						//！有这个选择时间的插件才能用，为什么要加这一句，同上
						$('#d_date').click(function(){WdatePicker();});
						
					}else{
						alert('Search delivery failure!');
					}
				}
			})
		}else{
			alert('Re-post error!');	
		}
	}else{
		alert('Please choose delivery NO.');
	}
}

//sys packing list item (点击删除图标从delivery添加)
function delPackingList2(){
	var selectText = $("#did_container li").parent().parent().prev().val();
	if(selectText != '- select -'){
		var judge = $('tbody [name='+selectText+']').length;
		if(judge != 0){
			//改为name保存Delivery NO.了，因为ID用来放ITEM#了，因为复制单个ITEM信息的时候要用到ID
			$('tbody [name='+selectText+']').each(function(){
				$(this).remove();
			});
			//UpdateTotal();
		}else{
			alert('No need to delete!');	
		}
	}else{
		alert('Please choose delivery NO.(201303071035)');
	}
}


/*
 * 搜索 Product，原来放在外面，现在放在函数里，方便调用
 *
 * @param num: 为了方便处理不同的表单的Input product的tabindex
 *        i: 记录当前是product ID 的id属性号
 * @return 
 */
//add 20130325 only for purchase
/*function searchProduct_purchase(num, i){
	
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
	}else if(local_url.indexOf('invoice') > 0 && local_url.indexOf('customs') < 0){
		sign = 'invoice';
		xid = 'i_vid';
	}else if(local_url.indexOf('customs') > 0){
		sign = 'customs_invoice';
		xid = 'ci_vid';		
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
			//index_td
			var index_td = pid_input.parent().parent().prev();
			//add scode 20130325
			var scode_td = pid_input.parent().parent().next().children();
			//Description輸入框
			var des_input = scode_td.parent().next().children().children();
			//Quantity輸入框
			var qut_input = des_input.parent().parent().next().children().children();
			//Cost RMB輸入框
			var rmb_input = qut_input.parent().parent().next().children().children();
			
			// enter觸發能註冊blur事件，是為了add的時候用
			quantityBlur(qut_input.attr('id'));
			priceBlur(rmb_input.attr('id'));
			*//*
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
			*//*
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
			//HIS 位置
			var his_div = photo_td.next().children();
			//CLEAR 位置
			var clear_div = photo_td.next().next().children();
			//DEL 位置
			var del_div = photo_td.next().next().next().children();
			
			//取得输入框中的pid
			var pid_text = pid_input.val();
			//所有的数据行有一个.repeat的Class，得到数据行的大小
			var vcount=$("#tbody tr").filter(".repeat").size();
			
			//如过不允许修改product ID, 只能删除的话，就没有修改前面的product ID，tabindex混乱的问题了
			//当modify时，修改之前的product ID，而不是新增，则是再写入的tabindex值与原来保持一致
			*//*
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
			*//*
			
			var pid_tabindex_new = num+4*vcount;
			var pid_tabindex = num+4*(vcount-1);
			var des_tabindex = (num+1)+4*(vcount-1);
			var qut_tabindex = (num+2)+4*(vcount-1);
			var rmb_tabindex = (num+3)+4*(vcount-1);
			
			if( pid_text != ''){
                var qs;
				if(sign == 'purchase'){
					//purchase 的返回product信息不同于其他，des_chi和cost
					qs = 'ajax=1&act=ajax-choose_purchase_product&value='+pid_text;
				}else{
					//var qs = 'ajax=1&act=ajax-choose_product&value='+pid_text;
					//20120425 新增了能选择currency的功能，所以改用 choose_product_new
					qs = 'ajax=1&act=ajax-choose_product_new&value='+pid_text;
				}
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
						if(data.indexOf('!no-') < 0){//改为 !no- 是为了复杂一点，以防正确的放回值里面也会有出现no导致错误

                            //mod 20130710 改为用精确到毫秒的时间戳来区别表单项，不用vcount了，她们一直反映有很多问题，试试用了时间戳后会不会好点
                            var item_index = (new Date()).valueOf();

							//先複製框，再在原來的框中插入值
							$("#tbody>.template")   
								//连同事件一起复制   
								.clone(true)  
								//複製後的也是被hide起來的了，所以要show一下
								.show() 
								//去除模板标记   
								.removeClass("template")   
								//給id附新的值
								.find("#q_pid").attr("id","q_pid"+item_index).attr("name","q_pid"+item_index).attr("tabindex", pid_tabindex_new).removeClass("disabled").removeAttr("disabled").end()
								.find("#q_p_price").attr("id","q_p_price"+item_index).attr("name","q_p_price"+item_index).end()
								.find("#q_p_quantity").attr("id","q_p_quantity"+item_index).attr("name","q_p_quantity"+item_index).end()
								//.find("#q_p_remark").attr("id","q_p_remark"+item_index).attr("name","q_p_remark"+item_index).addClass("disabled").attr("disabled", "disabled").end()
								//add scode 20130325
								.find("#q_p_scode").attr("id","q_p_scode"+item_index).attr("name","q_p_scode"+item_index).end()
								
								.find("#q_p_description").attr("id","q_p_description"+item_index).attr("name","q_p_description"+item_index).end()
								.find("#q_p_photos").attr("id","q_p_photos"+item_index).attr("name","q_p_photos"+item_index).end()
								.find("#q_p_ccode").attr("id","q_p_ccode"+item_index).attr("name","q_p_ccode"+item_index).end()
								.find("#q_p_scode").attr("id","q_p_scode"+item_index).attr("name","q_p_scode"+item_index).end()
								
								//去掉autocomplete
								.find(".autocomplete").remove().end()
								
								//清空clone過來的輸入框中的值   
								.find("input").val("").removeClass("textfocus").end() 
								.find("textarea").val("").end()
								.find("img").remove().end() 
								.find("#sub").html('').end()
								
								//加了product history點擊查看的按鈕
								.find("#his").attr("id","his"+item_index)*//*.html('<img src="../../sys/images/Actions-edit-copy-icon.png" />')*//*.end()
								//加了clear清除但前行内容但不删除当前行的按钮图标
								.find("#clear").attr("id", "clear"+item_index).end()
								//按鈕換成圖標了
								//.find(".del").html("<input class='defautButton' name='' type='button' value='Del' />").end()  
								//新出的那一行不要删除按钮   
								.find("#del").attr("id","del"+item_index)*//*.html('<img src="../../sys/images/del-icon.png" />')*//*.end()
								//插入表格   
								.appendTo($("#tbody"));
							
							var data_array = data.split("|");
							//加tabindex是为了能使用tab键在按tabindex的顺序转到下一个，这里由于remarks和submit的tabindex值在pid_input之上，所以，会先经过remarks和submit，才到第一个pid_input。。。
							
							//当enter入了一个product ID后，就不能修改product ID了，设置成readonly，要修改就只能删除了这一行
							*//*
							if(pid_input.attr("id") != 'q_pid'){
								pid_input.attr("readonly", "readonly").addClass("readonly").end();
							}
							*//*
							
							index_td.attr('id', 'index');
							pid_input.attr("tabindex", pid_tabindex).attr("readonly", "readonly").addClass("readonly").end();
							//add scode 20130325
							scode_td.html(data_array[5]).end();
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
							his_div.html('<img src="../../sys/images/Actions-edit-copy-icon.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" title="History" />').end();
							clear_div.html('<img src="../../sys/images/clear.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" title="Clear" />').end();
							del_div.html('<img src="../../sys/images/del-icon.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" title="Delete" />').end();
							//更新總和
							UpdateTotal();
							generateIndex();
						}else{
							//alert("\""+data+"\""); 
							if(trim(data) == "!no-2"){
								alert('Please select currency!');	
							}
							//屏蔽掉是因為 search 選擇了一個id後，就會彈出這個，它把之前輸入的不完整id做判斷，而非選擇後的
							//綁定enter事件後就可以用這個來提示了
							//为了方便，enter直接选定product ID，所以又把这个提示去掉了
							//alert('请输入正确的Product ID');
							pid_input.val("");
							//add scode 20130325
							scode_td.html("");
							des_input.val("");
							qut_input.val("");
							rmb_input.val("");
							pid_input.html("");
							sub_td.html("");
						}
					}
				});
			}
		}
	});
	
	//为template的Del按钮绑定事件
	$("#tbody .template .del").click(function() { 
		var del_vcount=$("#tbody tr").filter(".repeat").size();
		if(del_vcount > 1){
			//将下一个repeat的class加上template，这样就会一直有template
			$(this).parents(".template").next().addClass("template");
			
			$(this).parents(".template").remove(); 
			UpdateTotal();
			generateIndex();
		} 
		//这里就不else输出信息了，下面有了，否则就要弹两次了
	});
	
	//為選擇product的Del删除按钮事件绑定   
	$("#tbody #del"+i).click(function() {   
		var del_vcount=$("#tbody tr").filter(".repeat").size();
		//减1是template的
		if(del_vcount-1 > 1){
			if(confirm("Are you sure you want to delete?")){
				$(this).parents(".repeat").remove(); 
				UpdateTotal();
				generateIndex();
				//如果有下一個的話
				*//*
				var nextRepeat = $(this).parents(".repeat").next();
				while(nextRepeat){
					nextRepeat = nextRepeat.next();
					//alert('1');
				}
				*//*
			}
		}else{
			alert("Don't delete the last one!");	
		} 
	});
	
	//為選擇product的clear删除当前行内容 按钮绑定事件
	$("#tbody #clear"+i).click(function() {   
		if(confirm("Are you sure you want to clear?")){
			var c_sub_td = $(this).parent().prev().prev().prev();
			var c_rmb_input = c_sub_td.prev();
			var c_pt_input = c_rmb_input.children().next();
			var c_cc_input = c_pt_input.next();
			var c_sc_input = c_cc_input.next();
			var c_qut_input = c_rmb_input.prev();
			var c_des_input = c_qut_input.prev();
			var c_scode_td = c_des_input.prev();
			var c_pid_input = c_scode_td.prev();
			c_pid_input.children().children().removeClass("readonly").removeAttr("readonly").val("");
			c_scode_td.children().html("");
			c_des_input.children().children().val("");
			c_qut_input.children().children().val("");
			c_rmb_input.children().children().val("");
			c_pt_input.val("");
			c_cc_input.val("");
			c_sc_input.val("");
			c_sub_td.html("0.00");
			UpdateTotal();
			generateIndex();
		}
	});
	
	//為選擇product的his查看按钮事件绑定   
	$("#tbody #his"+i).click(function() {   
		var his_pid_input = $(this).parent().prev().prev().prev().prev().prev().prev().prev().children().children();//这里是关键，clone后都能有效！！
		var pid_var = his_pid_input.val();

		if(pid_var != ''){
			var qs = 'ajax='+sign+'&act=ajax-search_price_history&pid='+pid_var;
			var goto_url = 'model/com/'+sign+'_pid_history.php?pid='+pid_var;
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
						alert('No historical data!');
					}
				}
			})	
		}
	});
	
	//為選擇product的Del All删除按钮事件绑定 
	//kevin不要Del All按钮了
	*//*
	$("#tbody .del_all").click(function() {
		$(".template")
			.find("input").val("").end()
			.find("textarea").val("").end()
			.find("img").not("#del").remove().end()
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
	*//*
		
}*/

//传参数名获取URL中的参数值
/*
function getQueryString(name)
{
    // 如果链接没有参数，或者链接中不存在我们要获取的参数，直接返回空
    if(location.href.indexOf("?")==-1 || location.href.indexOf(name+'=')==-1)
    {
        return '';
    }
 
    // 获取链接中参数部分
    var queryString = location.href.substring(location.href.indexOf("?")+1);
 
    // 分离参数对 ?key=value&key2=value2
    var parameters = queryString.split("&");
 
    var pos, paraName, paraValue;
    for(var i=0; i<parameters.length; i++)
    {
        // 获取等号位置
        pos = parameters[i].indexOf('=');
        if(pos == -1) { continue; }
 
        // 获取name 和 value
        paraName = parameters[i].substring(0, pos);
        paraValue = parameters[i].substring(pos + 1);
 
        // 如果查询的name等于当前name，就返回当前值，同时，将链接中的+号还原成空格
        if(paraName == name)
        {
            return unescape(paraValue.replace(/\+/g, " "));
        }
    }
    return '';
};

//传参数名获取URL中的参数值（正则）
function getQueryStringRegExp(name)
{
    var reg = new RegExp("(^|\\?|&)"+ name +"=([^&]*)(\\s|&|$)", "i");  
    if (reg.test(location.href)) return unescape(RegExp.$2.replace(/\+/g, " ")); return "";
};
*/

/*
function sttlement_amount_blur(obj){
	var amount = $(obj);
	var amount_value = amount.val();
	var outstanding_value = $('#outstanding_value').val();
	var po_no = $('#po_no').val();
	if(amount_value != ''){
		if(po_no != ''){
			var qs = 'ajax=settlement&act=ajax-blur_settlement_amount&po_no='+po_no+'&value='+amount_value;
			$.ajax({
				type: "GET",
				url: "index.php",
				data: qs,
				cache: false,
				dataType: "html",
				error: function(){
					alert('system error!');
				},
				success: function(data){	
					if(data.indexOf('no-') < 0){
						if(trim(data) == 'yes'){
							$("#outstanding_value").val(parseFloat($("#outstanding_value").val()) - parseFloat(amount_value));
						}else{
							$("#outstanding_value").val(parseFloat($("#outstanding_value").val()) + parseFloat(data) - parseFloat(amount_value));
						}
					}else{
						alert('计算Total与Outstanding Value出错 !');
					}
				}
			})
		}else{
			alert('PO# 不能为空 !');
			//清空amount让用户重填，这样blur才能生效
			amount.val('');
		}
	}
}
*/

//amount focus outstanding_value框加上旧的amount值
function sttlement_amount_focus(obj){
	var amount = $(obj);
	if(amount.val() != ''){
		$("#outstanding_value").val(parseFloat($('#outstanding_value').val()) + parseFloat(amount.val()));
	}
}

//amount blur outstanding_value框减去新的amount值
function sttlement_amount_blur(obj){
	var amount = $(obj);
	if(amount.val() != ''){
		$("#outstanding_value").val(parseFloat($('#outstanding_value').val()) - parseFloat(amount.val()));
	}
}

//Credit Note
function addCreditNoteItem(){
    //用精确到毫秒的时间戳来区别表单项
    var item_index = (new Date()).valueOf();

    //每次点击添加的内容
    var all_html = '';
    all_html += '<tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">';
    //description
    //20131101 把required都去掉了，不然删除item后提交不了
    all_html += '<td><div id="divdescription'+item_index+'" class="formfield"><textarea id="description'+item_index+'" class="tainit tainitb" strlen="1,1000" tabindex="3" rows="2" name="description'+item_index+'"></textarea><h6 class="required">*</h6></div>';
    //amount
    all_html +=	'<td valign="top"><div class="formfield"><input id="amount'+item_index+'" class="textinit textinitb" type="text" strlen="1,30" maxlength="30" value="" name="amount'+item_index+'"><h6 class="required">*</h6></div></td>';
    //按钮
    all_html += '<td align="left"><img title="添加" style="opacity: 0.5;" onclick="addCreditNoteItem()" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/add_small.png"></td><td align="left"><img title="删除" style="opacity: 0.5;" onclick="delCreditNoteItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del_small.png"></td>';
    all_html += '</tr>';

    $("#tbody").append(all_html);
}
function delCreditNoteItem(obj){
    $(obj).parent().parent().remove();
}


//Warehouse
function wh_pid_blur(obj){
    var q_pid_obj = $(obj);
    var description_chi_obj = q_pid_obj.parent().parent().next().children().children();
    var quantity_obj = description_chi_obj.parent().parent().next().children().children();
    //var cost_rmb_obj = quantity_obj.parent().parent().next().children().children();
    //var subtotal_obj = cost_rmb_obj.parent().parent().next();
    var arrival_date_obj = quantity_obj.parent().parent().next().children().children();
    var photo_obj = arrival_date_obj.parent().parent().next();
    var pt_input_obj = photo_obj.next().next().next().next().children();

    if(q_pid_obj.val() != ''){
        var qs = 'ajax=1&act=ajax-choose_purchase_product&warehouse&value='+q_pid_obj.val();
        $.ajax({
            type: "GET",
            url: "index.php",
            data: qs,
            cache: false,
            dataType: "html",
            error: function(){
                alert('System error, search product ID failure !');
            },
            success: function(data){
                if(data.indexOf('!no-') < 0){//改为 !no- 是为了复杂一点，以防正确的放回值里面也会有出现no导致错误
                    var data_array = data.split("|");

                    //cost_rmb_obj.val(data_array[0]).attr("readonly", "readonly").addClass("readonly");
                    description_chi_obj.val(data_array[1]).attr("readonly", "readonly").addClass("readonly");
                    photo_obj.html(data_array[2]);
                    pt_input_obj.val(data_array[3]);
                    //subtotal_obj.html(cost_rmb_obj.val()*quantity_obj.val());
                }else{
                    //20130614 选product也算是blur了，所以会显示这句。。。
                    //alert('Can not find any information from the Product ID !');
                    q_pid_obj.focus();
                    description_chi_obj.val("");
                    quantity_obj.val("");
                    //cost_rmb_obj.val("");
                    //subtotal_obj.html("");
                    //arrival_date 页面加载后就有设置了默认值为当天，所以在选择pid的时候，就会触发blur事件，先清空了原来设置的默认值，所以这里去掉了这个
                    //arrival_date_obj.val("");
                    photo_obj.html("");
                    pt_input_obj.val("");
                    //remark_obj.val("");
                }
            }
        });
    }else{
        alert('Please fill in the Product ID !');
    }
}
//创建补0函数
function p(s) {
    return s < 10 ? '0' + s: s;
}
function addWarehouseItem(obj){
    var pid_value = $(obj).parent().prev().prev().prev().prev().prev().prev().prev().children().children().val();
    if(pid_value != ''){
        var myDate = new Date();
        var today = myDate.getFullYear() + '-' + p(myDate.getMonth()+1) + '-' + p(myDate.getDate());
        //用精确到毫秒的时间戳来区别表单项
        var item_index = myDate.valueOf();

        //每次点击添加的内容
        var all_html = '';
        all_html += '<tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">';
        //q_pid
        all_html +=	'<td valign="top"><div class="formfield"><input id="q_pid'+item_index+'" class="textinit textinitb" type="text" style="width:150px" tabindex="" strlen="1,20" required="1" maxlength="20" onblur="wh_pid_blur(this)" name="q_pid'+item_index+'"></div></td>';
        //description chi
        all_html += '<td><div id="divdescription_chi'+item_index+'" class="formfield"><textarea id="description_chi'+item_index+'" class="tainit tainitb" strlen="1,1000" style="width:200px" tabindex="" rows="2" name="description_chi'+item_index+'"></textarea></div></td>';
        //quantity
        all_html +=	'<td valign="top"><div class="formfield"><input id="qty'+item_index+'" class="textinit textinitb" type="text" style="width:80px" tabindex="" strlen="1,20" restrict="number" required="1" maxlength="20" name="qty'+item_index+'" onblur="whQtyBlur(this)"></div></td>';
        //cost_rmb
        //all_html +=	'<td valign="top"><div class="formfield"><input id="cost_rmb'+item_index+'" class="textinit textinitb" type="text" style="width:80px" tabindex="" strlen="1,20" restrict="number" required="1" maxlength="20" name="cost_rmb'+item_index+'"></div></td>';
        //subtotal
        //all_html += '<td></td>';
        //arrival_date
        all_html += '<td><div class="formfield"><input id="arrival_date'+item_index+'" class="textinit textinitb" type="text" style="width:100px" tabindex="" restrict="date" name="arrival_date'+item_index+'" value="'+today+'"></div></td>';
        //photo
        all_html += '<td></td>';
        //remark
        all_html += '<td><div id="remark'+item_index+'" class="formfield"><textarea id="remark'+item_index+'" class="tainit tainitb" strlen="1,1000" style="width:200px" tabindex="" rows="2" name="remark'+item_index+'"></textarea></div></td>';
        //按钮
        all_html += '<td align="left"><img title="添加" style="opacity: 0.5;" onclick="addWarehouseItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/add_small.png"></td><td align="left"><img title="删除" style="opacity: 0.5;" onclick="delWarehouseItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del_small.png"></td>';
        //hidden pt_input
        all_html += '<td><input type="hidden" name="pt_input'+item_index+'" id="pt_input'+item_index+'" /></td>';
        all_html += '</tr>';
        all_html += '<script>$(function(){SearchPid('+item_index+');});</script>';

        $("#tbody").append(all_html);

        //！有这个选择时间的插件才能用，为什么要加这一句，同上
        $('#arrival_date'+item_index).click(function(){WdatePicker();});
    }else{
        //alert('Please fill in Product ID !');
    }
}
function delWarehouseItem(obj){
    $(obj).parent().parent().remove();
}
//为warehouse item qunantity 绑定blur更新 subtotal 事件
function whQtyBlur(obj){
/*    var qty_obj = $(obj);
    var cost_rmb_obj = qty_obj.parent().parent().next().children().children();
    var subtotal_obj = cost_rmb_obj.parent().parent().next();
    subtotal_obj.html(formatCurrency(qty_obj.val()*cost_rmb_obj.val()));*/
}


//Retail Sales Memo
function rsm_pid_blur(obj){

    var wh_id = $('#wh_id').val();
    //alert(wh_id);
    if(wh_id != ''){
        var q_pid_obj = $(obj);
        //var description_chi_obj = q_pid_obj.parent().parent().next().children().children();
        var payment_method_obj = q_pid_obj.parent().parent().next().children().children();
        var cost_rmb_obj = payment_method_obj.parent().parent().next().children().children();
        var quantity_obj = cost_rmb_obj.parent().parent().next().children().children();
        var stock_obj = quantity_obj.parent().parent().next();
        var subtotal_obj = stock_obj.next();
        var photo_obj = subtotal_obj.next();
        var remark_obj = photo_obj.next().children().children();
        var pt_input_obj = remark_obj.parent().parent().next().next().next().children();

        if(q_pid_obj.val() != ''){
            var qs = 'ajax=1&act=ajax-choose_purchase_product&retail&wh_id='+wh_id+'&value='+q_pid_obj.val();
            $.ajax({
                type: "GET",
                url: "index.php",
                data: qs,
                cache: false,
                dataType: "html",
                error: function(){
                    alert('System error, search product ID failure !');
                },
                success: function(data){
                    if(data.indexOf('!no-') < 0){//改为 !no- 是为了复杂一点，以防正确的放回值里面也会有出现no导致错误
                        var data_array = data.split("|");

                        //20130711 可自己填写price
                        //cost_rmb_obj.val(data_array[0]).attr("readonly", "readonly").addClass("readonly");
                        //cost_rmb_obj.val(data_array[0]);

                        //description_chi_obj.val(data_array[1]);
                        photo_obj.html(data_array[2]);
                        pt_input_obj.val(data_array[3]);
                        subtotal_obj.html(cost_rmb_obj.val()*quantity_obj.val());
                        stock_obj.html(data_array[6]);
                    }else{
                        //20130614 选product也算是blur了，所以会显示这句。。。
                        //alert('Can not find any information from the Product ID !');
                        q_pid_obj.focus();
                        //description_chi_obj.val("");
                        quantity_obj.val("");
                        cost_rmb_obj.val("");
                        subtotal_obj.html("");
                        //arrival_date 页面加载后就有设置了默认值为当天，所以在选择pid的时候，就会触发blur事件，先清空了原来设置的默认值，所以这里去掉了这个
                        //arrival_date_obj.val("");
                        photo_obj.html("");
                        pt_input_obj.val("");
                        //remark_obj.val("");
                        stock_obj.val("");
                    }
                }
            });
        }else{
            alert('Please fill in the Product ID !');
        }
    }else{
        alert('Please select a Shop !');
    }
}
function addRetailSalesMemoItem(obj){
    //20130708 加检测是否pid有内容，即这一行是否有被使用到，如果没有，点击加按钮没反映
    var pid_value = $(obj).parent().prev().prev().prev().prev().prev().prev().prev().prev().children().children().val();
    if(pid_value != ''){
        var myDate = new Date();
        //var today = myDate.getFullYear() + '-' + p(myDate.getMonth()+1) + '-' + p(myDate.getDate());
        //用精确到毫秒的时间戳来区别表单项
        var item_index = myDate.valueOf();

        //每次点击添加的内容
        var all_html = '';
        all_html += '<tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">';
        //q_pid
        all_html +=	'<td valign="top"><div class="formfield"><input id="q_pid'+item_index+'" class="textinit textinitb" type="text" style="width:150px" tabindex="" strlen="1,20" required="1" maxlength="20" onblur="rsm_pid_blur(this)" name="q_pid'+item_index+'"></div></td>';
        //description_chi
        //all_html += '<td><div id="divdescription_chi'+item_index+'" class="formfield"><textarea id="description_chi'+item_index+'" class="tainit tainitb" strlen="1,1000" style="width:180px" tabindex="" rows="2" name="description_chi'+item_index+'"></textarea></div></td>';
        //payment method
        all_html += '<td><div id="divpayment_method'+item_index+'" class="selectfield"><input id="payment_method'+item_index+'_input" class="selectbox" type="text" autocomplete="off" readonly="" tabindex="6"><div id="payment_method'+item_index+'_container" class="selectbox-wrapper" style="display: none; width: 204px; height: 80px;"><ul><li id="payment_method'+item_index+'_input__" class="selected">- select -</li><li id="payment_method'+item_index+'_input__Cash">Cash</li><li id="payment_method'+item_index+'_input__China UnionPay">China UnionPay</li><li id="payment_method'+item_index+'_input__Credit Card">Credit Card</li><li id="payment_method'+item_index+'_input__EPS">EPS</li></ul></div><select id="payment_method'+item_index+'" tabindex="6" required="1" name="payment_method'+item_index+'" size="1" style="display: none;"><option value="">- select -</option><option value="Cash">Cash</option><option value="China UnionPay">China UnionPay</option><option value="Credit Card">Credit Card</option><option value="EPS">EPS</option></select></div></td>';
        //price
        all_html +=	'<td valign="top"><div class="formfield"><input id="cost_rmb'+item_index+'" class="textinit textinitb" type="text" style="width:80px" tabindex="" strlen="1,20" restrict="number" required="1" maxlength="20" name="cost_rmb'+item_index+'" onblur="rsmPriceBlur(this)"></div></td>';
        //quantity
        all_html +=	'<td valign="top"><div class="formfield"><input id="qty'+item_index+'" class="textinit textinitb" type="text" style="width:80px" tabindex="" strlen="1,20" restrict="number" required="1" maxlength="20" name="qty'+item_index+'" onblur="rsmQtyBlur(this)"></div></td>';
        //stock
        all_html += '<td class="num_td"></td>';
        //subtotal
        all_html += '<td id="sub" class="num_td"></td>';
        //photo
        all_html += '<td></td>';
        //remark
        all_html += '<td><div id="remark'+item_index+'" class="formfield"><textarea id="remark'+item_index+'" class="tainit tainitb" strlen="1,1000" style="width:200px" tabindex="" rows="2" name="remark'+item_index+'"></textarea></div></td>';
        //按钮
        all_html += '<td align="left"><img title="添加" style="opacity: 0.5;" onclick="addRetailSalesMemoItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/add_small.png"></td><td align="left"><img title="删除" style="opacity: 0.5;" onclick="delRetailSalesMemoItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del_small.png"></td>';
        //hidden pt_input
        all_html += '<td><input type="hidden" name="pt_input'+item_index+'" id="pt_input'+item_index+'" /></td>';
        all_html += '</tr>';
        all_html += '<script>$(function(){SearchPid('+item_index+');});</script>';

        $("#tbody").append(all_html);

        $('select').not('.special').selectbox();
    }else{
        //alert('Please fill in Product ID !');
    }
}
function delRetailSalesMemoItem(obj){
    $(obj).parent().parent().remove();
}
//Retail Sales Memo item qunantity 绑定blur更新 subtotal 事件
function rsmQtyBlur(obj){
    var qty_obj = $(obj);
    var stock_obj = qty_obj.parent().parent().next();

    var qty_value = qty_obj.val();
    if(qty_value.length == 0) qty_value = 0;

    var stock_value = (stock_obj.html());
    if(stock_value.length == 0) stock_value = 0;

    if(parseInt(stock_value) >= parseInt(qty_value)){
        var cost_rmb_obj = qty_obj.parent().parent().prev().children().children();
        var subtotal_obj = stock_obj.next();
        subtotal_obj.html(formatCurrency(qty_obj.val()*cost_rmb_obj.val()));
    }else{
        alert('Quantity must less than Stock !');
        qty_obj.val('');
    }
    UpdateTotal();
}
function rsmPriceBlur(obj){
    var price_obj = $(obj);
    var qty_obj = price_obj.parent().parent().next().children().children();
    var subtotal_obj = qty_obj.parent().parent().next().next();
    subtotal_obj.html(formatCurrency(qty_obj.val()*price_obj.val()));
    UpdateTotal();
}

//20130730 自定义js程序延时函数
function my_sleep(numberMillis) {
    var now = new Date();
    var exitTime = now.getTime() + numberMillis;
    while (true) {
        now = new Date();
        if (now.getTime() > exitTime)    return;
    }
}


//PAYMENT NEW 20130801
function select_pi_or_cn(obj){
    var pi_or_cn_obj = $(obj);
    var pi_or_cn_no_obj = pi_or_cn_obj.parent().parent().next().children().children();
    var total_obj = pi_or_cn_no_obj.parent().parent().next().children().children();
    var outstanding_obj = total_obj.parent().parent().next().children().children();
    var received_obj = outstanding_obj.parent().parent().next().children().children();
    var balance_obj = received_obj.parent().parent().next().children().children();

    if(pi_or_cn_obj.val() != 'CUSTOMER BANK CHARGE'){
        var qs = 'ajax=1&act=ajax-get_pi_or_cn_no&value='+pi_or_cn_obj.val();
        $.ajax({
            type: "GET",
            url: "index.php",
            data: qs,
            cache: false,
            dataType: "html",
            error: function(){
                alert('System error, search PI or CN or CUSTOMER BANK CHARGE failure !');
            },
            success: function(data){
                if(data.indexOf('!no-') < 0){
                    var data_array = data.split("|");
                    pi_or_cn_no_obj.html('<option value="">- select -</option>');
                    for (var i=0; i<data_array.length; i++){
                        pi_or_cn_no_obj.append("<option value='"+data_array[i]+"'>"+data_array[i]+"</option>");
                    }
                    total_obj.val('');
                    outstanding_obj.val('');
                    received_obj.val('');
                    balance_obj.val('');
                }else{
                    alert('Error!');
                }
            }
        });
    }
}
//20130802
function select_pi_or_cn_no(obj){
    var pi_or_cn_no_obj = $(obj);
    var total_obj = pi_or_cn_no_obj.parent().parent().next().children().children();
    var outstanding_obj = total_obj.parent().parent().next().children().children();
    var qs = 'ajax=1&act=ajax-get_pi_or_cn_info&value='+pi_or_cn_no_obj.val();
    $.ajax({
        type: "GET",
        url: "index.php",
        data: qs,
        cache: false,
        dataType: "html",
        error: function(){
            alert('System error, search PI or CN info failure !');
        },
        success: function(data){
            if(data.indexOf('!no-') < 0){
                var data_array = data.split("|");
                total_obj.val(data_array[0]);
                outstanding_obj.val(data_array[1]);
            }else{
                alert('Error!');
            }
        }
    });
}
//20130802
function addPaymentItemNew(obj){
    var pi_or_cn_no = $(obj).parent().prev().prev().prev().prev().prev().children().children();
    var pi_or_cn_no_value = $(obj).parent().prev().prev().prev().prev().prev().children().children().val();
    var pi_or_cn = pi_or_cn_no.parent().parent().prev().children().children().val();
    if(pi_or_cn_no_value != '' || pi_or_cn == 'CUSTOMER BANK CHARGE'){
        var myDate = new Date();
        var today = myDate.getFullYear() + '-' + p(myDate.getMonth()+1) + '-' + p(myDate.getDate());
        //用精确到毫秒的时间戳来区别表单项
        var item_index = myDate.valueOf();

        //每次点击添加的内容
        //20131101 去掉required，因为删除了item后提交还会提示让填写被删除的项
        var all_html = '';
        all_html += '<tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">';
        // pi_or_cn
        all_html += '<td><div id="divpi_ro_cn'+item_index+'" class="selectfield"><select id="pi_ro_cn'+item_index+'" style="width:210px" tabindex="6" name="pi_ro_cn'+item_index+'" size="1" onchange="select_pi_or_cn(this)"><option value="">- select -</option><option value="PI">PI</option><option value="CN">CN</option><option value="CUSTOMER BANK CHARGE">CUSTOMER BANK CHARGE</option></select></div></td>';
        // pi_or_cn_no
        all_html += '<td><div id="divpi_or_cn_no'+item_index+'" class="selectfield"><select id="pi_or_cn_no'+item_index+'" style="width:150px" tabindex="6" name="pi_or_cn_no'+item_index+'" size="1" onchange="select_pi_or_cn_no(this)"><option value="">- select -</option></div></td>';
        //total
        all_html +=	'<td valign="top"><div class="formfield"><input id="total'+item_index+'" class="readonly textinit textinitb" type="text" style="width:100px" tabindex="" strlen="1,20" restrict="number" maxlength="20" name="total'+item_index+'" readonly="readonly"></div></td>';
        //outstanding
        all_html +=	'<td valign="top"><div class="formfield"><input id="outstanding'+item_index+'" class="readonly textinit textinitb" type="text" style="width:100px" tabindex="" strlen="1,20" restrict="number" maxlength="20" name="outstanding'+item_index+'" readonly="readonly"></div></td>';
        //received
        all_html +=	'<td valign="top"><div class="formfield"><input id="received'+item_index+'" class="textinit textinitb" type="text" style="width:100px" onblur="received_blur(this)" tabindex="" strlen="1,20" restrict="number" maxlength="20" name="received'+item_index+'"></div></td>';
        //balance
        all_html +=	'<td valign="top"><div class="formfield"><input id="balance'+item_index+'" class="readonly textinit textinitb" type="text" style="width:100px" tabindex="" strlen="1,20" restrict="number" maxlength="20" name="balance'+item_index+'" readonly="readonly"></div></td>';
        //按钮
        all_html += '<td align="left"><img title="添加" style="opacity: 0.5;" onclick="addPaymentItemNew(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/add_small.png"></td><td align="left"><img title="删除" style="opacity: 0.5;" onclick="delPaymentItemNew(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del_small.png"></td>';
        all_html += '</tr>';

        $("#tbody").append(all_html);

    }else{
        alert('Please select PI/CN # !');
    }
}
//20130805
function delPaymentItemNew(obj){
    $(obj).parent().parent().remove();
    payment_advice_total();
}
//20130812
function received_blur(obj){
    var received_obj = $(obj);
    received_obj.val( (received_obj.val()-0).toFixed(2) );
    var outstanding_obj = received_obj.parent().parent().prev().children().children();
    var balance_obj = received_obj.parent().parent().next().children().children();
    // toFixed(2) 保留两位小数，以防浮点计算产生太多小数位
    balance_obj.val( (outstanding_obj.val() - received_obj.val()).toFixed(2) );
    payment_advice_total();
}
//20130922
function changeExclusiveTo(obj){
    var selectText = obj.selectedVal;
    if(selectText != ''){
        $('#p_suggested_price').removeAttr('readonly').removeClass('readonly');
    }else{
        $('#p_suggested_price').val('').attr('readonly', 'readonly').addClass('readonly');
    }
}
//20130925
function payment_advice_total(){
    var remitting_amount_total = $('#remitting_amount').val();
    var vTable = $("#tbody");//得到表格的jquery对象
    var vTotal = $("#total");
    var vBalance = $('#balance');
    var vtxtAfters = vTable.find("[id^='received']");//得到所有的received对象
    var received_total = 0;
    vtxtAfters.each(
        function()
        {
            var vTempValue = $(this).val();
            //20140428 cn为减
            var type = $(this).parent().parent().prev().prev().prev().prev().children().children().val();
            vTempValue = vTempValue.replace(/,/g,"");
            if(vTempValue != ''){

                //20140520 可以填负号，所以都用加
                received_total = parseFloat(received_total) + parseFloat(vTempValue);
/*                if(type == 'CN' || type == 'CUSTOMER BANK CHARGE'){
                    received_total = parseFloat(received_total) - parseFloat(vTempValue);
                }else{
                    received_total = parseFloat(received_total) + parseFloat(vTempValue);
                }*/

            }
        }
    );//遍历结束
    vTotal.html(received_total.toFixed(2));
    vBalance.html((remitting_amount_total-received_total).toFixed(2));
}
//20141108
function addTransferItem(obj){
    var pid = $(obj).parent().prev().prev().prev().children().children().val();
    var t_qty = $(obj).parent().prev().children().children().val();
    if(pid != '' && t_qty != ''){
        var myDate = new Date();
        var today = myDate.getFullYear() + '-' + p(myDate.getMonth()+1) + '-' + p(myDate.getDate());
        //用精确到毫秒的时间戳来区别表单项
        var item_index = myDate.valueOf();

        //每次点击添加的内容
        //20131101 去掉required，因为删除了item后提交还会提示让填写被删除的项
        var all_html = '';
        all_html += '<tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">';
        //pid
        all_html +=	'<td valign="top"><div class="formfield"><input id="q_pid'+item_index+'" class="textinit textinitb" onblur="itf_pid_blur(this)" type="text" tabindex="" strlen="1,20" maxlength="20" name="q_pid'+item_index+'"></div></td>';
        //photo
        all_html += '<td></td>';
        //quantity
        all_html +=	'<td valign="top"><div class="formfield"><input id="q_p_quantity'+item_index+'" class="textinit textinitb" type="text" tabindex="" strlen="1,20" maxlength="20" name="q_p_quantity'+item_index+'"></div></td>';
        //按钮
        all_html += '<td align="left"><img title="添加" style="opacity: 0.5;" onclick="addTransferItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/add_small.png"></td><td align="left"><img title="删除" style="opacity: 0.5;" onclick="delTransferItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../fty/images/del_small.png"></td>';
        all_html += '<td></td>';
        all_html += '</tr>';
        all_html += '<script>$(function(){SearchWarehousePid('+item_index+');});</script>';

        $("#tbody").append(all_html);

    }else{
        alert('Please fill in the Product ID and Transfer Quantity');
    }
}
function delTransferItem(obj){
    $(obj).parent().parent().remove();
}
//20150512
function addPRVoucherItem(obj){
    var account_name = $(obj).parent().prev().prev().prev().prev().prev().prev().children().children().val();
    var dr = $(obj).parent().prev().prev().prev().children().children().val();
    var cr = $(obj).parent().prev().children().children().val();
    if(account_name != ''){
        if(dr != '' || cr != ''){
            var myDate = new Date();
            var today = myDate.getFullYear() + '-' + p(myDate.getMonth()+1) + '-' + p(myDate.getDate());
            //用精确到毫秒的时间戳来区别表单项
            var item_index = myDate.valueOf();

            //每次点击添加的内容
            //20131101 去掉required，因为删除了item后提交还会提示让填写被删除的项
            var all_html = '';
            all_html += '<tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">';
            //account Name
            all_html +=	'<td valign="top"><div class="formfield"><input id="account_name'+item_index+'" class="textinit textinitb" type="text" tabindex="" strlen="1,200" maxlength="200" name="account_name'+item_index+'"></div></td>';
            //description
            all_html +=	'<td valign="top"><div class="formfield"><textarea id="description'+item_index+'" class="tainit tainitb" strlen="1,500" style="width:200px" tabindex="" rows="2" name="description'+item_index+'"></textarea></div></td>';
            //dr_currency
            all_html +=	'<td valign="top"><div class="formfield"><input id="dr_currency'+item_index+'" class="textinit textinitb" type="text" tabindex="" strlen="1,10" maxlength="10" style="width:50px" name="dr_currency'+item_index+'"  value="HK$"></div></td>';
            //dr
            all_html +=	'<td valign="top"><div class="formfield"><input id="dr'+item_index+'" class="textinit textinitb" type="text" tabindex="" strlen="1,20" maxlength="20" restrict="number" style="width:100px" name="dr'+item_index+'"></div></td>';
            //cr_currency
            all_html +=	'<td valign="top"><div class="formfield"><input id="cr_currency'+item_index+'" class="textinit textinitb" type="text" tabindex="" strlen="1,10" maxlength="10" style="width:50px" name="cr_currency'+item_index+'"  value="HK$"></div></td>';
            //cr
            all_html +=	'<td valign="top"><div class="formfield"><input id="cr'+item_index+'" class="textinit textinitb" type="text" tabindex="" strlen="1,20" maxlength="20" style="width:100px" restrict="number" name="cr'+item_index+'"></div></td>';
            //按钮
            all_html += '<td align="left"><img title="添加" style="opacity: 0.5;" onclick="addPRVoucherItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../sys/images/add_small.png"></td><td align="left"><img title="删除" style="opacity: 0.5;" onclick="delPRVoucherItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../sys/images/del_small.png"></td>';
            all_html += '</tr>';

            $("#tbody").append(all_html);

        }else{
            alert('Please fill Dr or Cr');
        }
    }else{
        alert('Please fill in the Account Name');
    }
}
function delPRVoucherItem(obj){
    $(obj).parent().parent().remove();
}
//20150512
function addPCVoucherItem(obj){
    var account_name = $(obj).parent().prev().prev().prev().prev().prev().children().children().val();
    var cny = $(obj).parent().prev().prev().prev().children().children().val();
    var rate = $(obj).parent().prev().prev().children().children().val();
    var amount = $(obj).parent().prev().children().children().val();
    if(account_name != ''){
        if(cny != '' && rate != '' && amount != ''){
            var myDate = new Date();
            var today = myDate.getFullYear() + '-' + p(myDate.getMonth()+1) + '-' + p(myDate.getDate());
            //用精确到毫秒的时间戳来区别表单项
            var item_index = myDate.valueOf();

            //每次点击添加的内容
            //20131101 去掉required，因为删除了item后提交还会提示让填写被删除的项
            var all_html = '';
            all_html += '<tr class="repeat" valign="top" onmouseover="product_itme_mouseover(this)" onmouseout="product_item_mouseout(this)" onmouseup="product_item_mouseup(this)">';
            //Account Name
            all_html +=	'<td valign="top"><div class="formfield"><input id="account_name'+item_index+'" class="textinit textinitb" type="text" tabindex="" strlen="1,200" maxlength="200" name="account_name'+item_index+'"></div></td>';
            //Description
            all_html +=	'<td valign="top"><div class="formfield"><textarea id="description'+item_index+'" class="tainit tainitb" strlen="1,500" style="width:200px" tabindex="" rows="2" name="description'+item_index+'"></textarea></div></td>';
            //CNY
            all_html +=	'<td valign="top"><div class="formfield"><input id="cny'+item_index+'" class="textinit textinitb" type="text" tabindex="" strlen="1,20" maxlength="20" restrict="number" name="cny'+item_index+'" onblur="pcvCNYBlur(this)" ></div></td>';
            //Rate
            all_html +=	'<td valign="top"><div class="formfield"><input id="rate'+item_index+'" class="textinit textinitb" type="text" tabindex="" strlen="1,20" maxlength="20" restrict="number" name="rate'+item_index+'" value="1.00" onblur="pcvRateBlur(this)"></div></td>';
            //Amount
            all_html +=	'<td valign="top"><div class="formfield"><input id="amount'+item_index+'" class="textinit textinitb" type="text" tabindex="" strlen="1,20" maxlength="20" restrict="number" name="amount'+item_index+'"></div></td>';
            //按钮
            all_html += '<td align="left"><img title="添加" style="opacity: 0.5;" onclick="addPCVoucherItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../sys/images/add_small.png"></td><td align="left"><img title="删除" style="opacity: 0.5;" onclick="delPCVoucherItem(this)" onmouseover="$(this).css(\'opacity\',\'1\')" onmouseout="$(this).css(\'opacity\',\'0.5\')" src="../sys/images/del_small.png"></td>';
            all_html += '</tr>';

            $("#tbody").append(all_html);

        }else{
            alert('Please fill CNY, Rate and Amount');
        }
    }else{
        alert('Please fill in the Account Name');
    }
}
function delPCVoucherItem(obj){
    $(obj).parent().parent().remove();
}
function pcvCNYBlur(obj){
    var cny_obj = $(obj);
    var cny = cny_obj.val();
    cny_obj.val( formatCurrency(cny) );
    var rate_obj = $(obj).parent().parent().next().children().children();
    var rate = rate_obj.val();
    rate_obj.val( formatCurrency(rate) );
    $(obj).parent().parent().next().next().children().children().val( formatCurrency(cny * rate) );
}
function pcvRateBlur(obj){
    var rate_obj = $(obj);
    var rate = rate_obj.val();
    rate_obj.val( formatCurrency(rate) );
    var cny_obj = $(obj).parent().parent().prev().children().children();
    var cny = cny_obj.val();
    cny_obj.val( formatCurrency(cny) );
    $(obj).parent().parent().next().children().children().val( formatCurrency(cny * rate) );
}

//item transfer form
function itf_pid_blur(obj){

    //检测是否有选择warehouse
    var transfer_from = $("#transfer_from").val();
    if(transfer_from == ''){
        alert('Please select Transfer From ! ');
        return false;
    }

    var q_pid_obj = $(obj);
    var photo_obj = q_pid_obj.parent().parent().next();
    var qty_obj = photo_obj.next().children().children();

    if(q_pid_obj.val() != ''){
        var qs = 'ajax=1&act=ajax-choose_warehouse_item&value='+q_pid_obj.val()+'&wh='+transfer_from;
        $.ajax({
            type: "GET",
            url: "index.php",
            data: qs,
            cache: false,
            dataType: "html",
            error: function(){
                alert('System error, search product ID failure !');
            },
            success: function(data){
                if(data.indexOf('!no-') < 0){//改为 !no- 是为了复杂一点，以防正确的放回值里面也会有出现no导致错误
                    var data_array = data.split("|");

                    photo_obj.html(data_array[0]);
                    qty_obj.val(data_array[1]);
                }else{
                    //20130614 选product也算是blur了，所以会显示这句。。。
                    //alert('Can not find any information from the Product ID !');
                    q_pid_obj.focus();
                    photo_obj.html("");
                    qty_obj.val("");
                }
            }
        });
    }else{
        //alert('Please fill in the Product ID !');
    }
}
function SearchWarehousePid(i)
{
    //Search功能實現*********start*******
    $("#q_pid"+i).focus(function(e){

        //检测是否有选择warehouse
        var transfer_from = $("#transfer_from").val();
        if(transfer_from == ''){
            alert('Please select Transfer From ! ');
            return false;
        }

        var pid_input = $(this);//这里是关键，clone后都能有效！！
        //用了blur就無法選擇搜索到的項，因為失去焦點就什麼都清空了，但是不用autocomplete又會越來越多。。。
        //現使用回車觸發
        pid_input.keydown(function(e){
            if( e.which==13){
                //zjn add
                //setTimeout(autocomplete.remove(), 500);
                autocomplete.remove();
            }
        });
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
            var qs = 'ajax=1&act=ajax-search_warehouse_pid&search_text='+pid_input.val()+'&wh='+transfer_from;
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
                                    }).mouseover(function(){
                                        //20130725 加当鼠标移动过也将pid填到框里，因为现在是blur事件，点击选择pid也会触发blur，如果这时候框里面还没填，就会ajax查询不到，又会清空了框里的内容
                                        pid_input.val(term);
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

//fty payment_request使用
function searchFtyCustomer(obj){
    //type select 框
    var fpr_type_select = $(obj);//这里是关键，clone后都能有效！！
    //type select 的值
    var selectText = fpr_type_select.val();

    var fpr_fty_customer_select = fpr_type_select.parent().parent().next().children().children();
    fpr_fty_customer_select.empty();

    var html = '';

    var qs = 'ajax=1&act=ajax-search_fty_customer&type='+selectText;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: qs,
        cache: false,
        dataType: "json",//用json格式，select2直接可用
        error: function(){
            alert('系统错误，查询 fty customer 失败');
        },
        success: function(data){
            if(data.indexOf('no-') < 0){
                /*var data_array = data.split("|");
                html = '<option value="">- select -</option>';
                for(i=0; i<data_array.length; i++){
                    html += "<option value='"+data_array[i]+"'>"+data_array[i]+"</option>";
                }
                fpr_fty_customer_select.removeAttr("disabled").append(html);*/

                fpr_fty_customer_select.select2({
                    placeholder: '- select -',
                    data: data
                });
                fpr_fty_customer_select.prop("disabled", false);
            }else{
                html = '<option value="">无记录</option>';
                fpr_fty_customer_select.removeAttr("disabled").append(html);
            }
        }
    });
}

function searchFtyCustomerDetail(obj){
    var fpr_fty_customer_select = $(obj);
    var fpr_type_select = fpr_fty_customer_select.parent().parent().prev().children().children();

    //ap 输入框
    var fpr_fty_customer_ap_input = fpr_fty_customer_select.parent().parent().next().children().children();
    //pay_amount 输入框
    var fpr_pay_amount_input = fpr_fty_customer_ap_input.parent().parent().next().children().children();
    //remark 输入框
    var fpr_remark_input = fpr_pay_amount_input.parent().parent().next().children().children();
    //actual_pay_amount 位置
	var fpr_actual_pay_amount = fpr_remark_input.parent().parent().next();
    //DEL 位置
    var del_div = fpr_actual_pay_amount.next().children();
    //hidden fpr_type_value
    var hidden_fpr_type_value = del_div.next();
    //hidden fpr_fty_customer_value
    var hidden_fpr_fty_customer_value = hidden_fpr_type_value.next();

    //用时间戳来区别表单
    var timestamp = Date.parse(new Date())/1000;

    var typeText = fpr_type_select.val();
    hidden_fpr_type_value.val(typeText);
    var selectText = fpr_fty_customer_select.val();
    var selectText_array = selectText.split(":");
    hidden_fpr_fty_customer_value.val(trim(selectText));//库里保存完整的，cid:name格式

    var qs = 'ajax=1&act=ajax-search_fty_customer_detail&type='+typeText+'&value='+selectText_array[0];
    $.ajax({
        type: "GET",
        url: "index.php",
        data: qs,
        cache: false,
        dataType: "html",
        error: function(){
            alert('系统错误，查询 fty customer detail 失败');
        },
        success: function(data){
            if(data.indexOf('no-') < 0){
                fpr_fty_customer_ap_input.val(data);

                //先複製框，再在原來的框中插入值
                $("#tbody>.template")
                    //连同事件一起复制
                    .clone(true)
                    //複製後的也是被hide起來的了，所以要show一下
                    .show()
                    //去除模板标记
                    .removeClass("template")
                    //給id附新的值
                    //find 后一定要有 end 不知道为什么？？

                    .find("#fpr_type").removeClass("disabled").removeAttr("disabled").attr("id", "fpr_type"+timestamp).attr("name", "fpr_type"+timestamp).end()
                    .find("#fpr_fty_customer").attr("id", "fpr_fty_customer"+timestamp).attr("name", "fpr_fty_customer"+timestamp).end()
                    .find("#fpr_fty_customer_ap").attr("id", "fpr_fty_customer_ap"+timestamp).attr("name", "fpr_fty_customer_ap"+timestamp).end()
                    .find("#fpr_pay_amount").attr("id", "fpr_pay_amount"+timestamp).attr("name", "fpr_pay_amount"+timestamp).end()
                    .find("#fpr_remark").attr("id", "fpr_remark"+timestamp).attr("name", "fpr_remark"+timestamp).end()
                    //hidden
                    .find("#fpr_type_value").attr("id", "fpr_type_value"+timestamp).attr("name", "fpr_type_value"+timestamp).end()
                    .find("#fpr_fty_customer_value").attr("id", "fpr_fty_customer_value"+timestamp).attr("name", "fpr_fty_customer_value"+timestamp).end()

                    //插入表格
                    .appendTo($("#tbody"));

                fpr_type_select.attr("disabled", "disabled");
                fpr_fty_customer_select.attr("disabled", "disabled");
                fpr_fty_customer_ap_input.removeClass("disabled").removeAttr("disabled").attr("readonly", "readonly");
                hidden_fpr_type_value.removeClass("disabled").removeAttr("disabled");
                hidden_fpr_fty_customer_value.removeClass("disabled").removeAttr("disabled");
                fpr_pay_amount_input.removeClass("disabled").removeAttr("disabled");
                fpr_remark_input.removeClass("disabled").removeAttr("disabled");
                del_div.html('<img src="../../sys/images/del-icon.png" onmouseout="$(this).css(\'opacity\',\'0.5\')" onmouseover="$(this).css(\'opacity\',\'1\')" style="opacity: 0.5;" title="Delete" />').end()
            }
        }
    })
}