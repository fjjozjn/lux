navHover = function() {
	if($('#navmenu-h').length){
		var lis = $('#navmenu-h li');
		for (var i=0; i<lis.length; i++) {
			lis[i].onmouseover=function() {
				this.className+=" iehover";
			}
			lis[i].onmouseout=function() {
                //20130730 自定义的延时函数，因为导航栏反应太快有点不方便
                //几分钟后发现鼠标一旦移除只是延迟消失，但是鼠标马上移回去还是会消失，没用，所以又去掉了
                //my_sleep(100);
				this.className=this.className.replace(new RegExp(" iehover\\b"), "");
			}
		}
	}
}
//no jquery
//if (window.attachEvent) window.attachEvent("onload", navHover);

//jquery
$(function(){ navHover(); });