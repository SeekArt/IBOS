/**
 * 后台登录
 * Dashboard/Login
 * @author inaki
 * @version $Id$
 */

(function(){
	var resizeBackground = function(img){
		var $win = $(window),
			winWidth = $win.width(),
			winHeight = $win.height(),
			imgWidth, imgHeight,
			resize = function(){
				imgWidth = img.width;
				imgHeight = img.height;
				// 适配高度
				if(imgWidth / imgHeight >= winWidth / winHeight){
					img.style.height = winHeight + 'px';
					img.style.width = 'auto';

				// 适配宽度
				} else {
					img.style.width = winWidth + 'px';
					// 高度自适应
					img.style.height = 'auto';
				}
				$(img).fadeIn();
			};
		if(img.complete){
			resize();
		}else{
			img.onload = resize;
		}
	}

	setTimeout(function() {
		if ($('#login_user').val() == '') {
			$("#login_user").focus();
		} else {
			$('#login_pass').focus();
		}
	}, 0);

	// 此处不能使用 "==="， Ie8 下最顶层window !== window.top
	if (window != window.top) {
		var requertUrl = window.location.href;
		var loginUrl = $("#loginForm").attr('action');
		if (requertUrl.indexOf(loginUrl) > -1) {
			// 如果子框架访问的是后台登陆页，顶层框架页面跳转到后台登陆页
			window.top.location.href = requertUrl;
		}
	}

	var img = document.getElementById("bg");
	window.onload =	window.onresize = function(){
		resizeBackground(img);
	}
	var btn = document.getElementById('submit-btn');
	$(btn).on('click',function(){
		$(this).button('loading');
	});
})();