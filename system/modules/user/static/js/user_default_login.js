/**
 * 用户中心--登录页
 * User
 * @author 		inaki
 * @version 	$Id$
 */
var Login = {
	//切换找回密码表格
	togglePanel : {
		/**
		 * 初始化表格
		 * @method init
		 */
		init : function(){
			this.lgPanel = $("#login_panel");
			this.pswPanel = $("#get_password_panel");

			Ui.focusForm(this.lgPanel);

			this._bind();
		},
		/**
		 * 登录面板和密码的切换
		 * @method _toggle
		 */
		_toggle : function(){
			this.lgPanel.toggle();
			this.pswPanel.toggle();
		},
		/**
		 * 事件操作
		 * @method _bind
		 */
		_bind : function(){
			var that = this;

			$("#to_get_password").on("click", function(){
				var userName;
				that._toggle();
				Ui.focusForm(that.pswPanel);
				// 同步登陆框用户名 至 找回密码面板 用户名
				userName = that.lgPanel.find("[name='username']").val();
				that.pswPanel.find("[name='username']").val(userName);
			}).tooltip();


			$("#to_login").on("click", function(){
				that._toggle();
				Ui.focusForm(that.lgPanel);
			});
		}
	},

	//公告内容过多时自动滚动
	autoScroll : function(){
		// 公告内容过多时自动滚动
		var $anc = $("#lg_anc_ct"),
			ANC_MAX_HEIGHT = 40, // 公告内容最大高度
			mgt = 0, // margin-top 公告内容当前上边距值
			ancHeight = $anc.outerHeight(),
			scrollSpeed = 2000, // 毫秒
			timer;

		var autoScroll = function() {
			timer = setInterval(function(){
				mgt -= 20;
				if (-mgt >= ancHeight) {
					mgt = ANC_MAX_HEIGHT;
					$anc.css({"margin-top": mgt});
				} else {
					$anc.animate({"margin-top": mgt});
				}
			}, scrollSpeed);
		};

		if (ancHeight > ANC_MAX_HEIGHT){
			autoScroll();
			$anc.hover(function(){
				clearInterval(timer);
			}, autoScroll);
		}
	},
	// 账号和密码验证，提示
	formValidator : function(){
		$.formValidator.initConfig({formID: "login_form", errorFocus: true});

		// 账号验证
		$("#account").formValidator({
			onFocus: function(){
				Ibosapp.formValidate.setGroupState("#account");
				return Ibos.l("V.INPUT_ACCOUNT");
			},
			onCorrect: function(){
				Ibosapp.formValidate.setGroupState("#account", "correct");
			},
			relativeID: "account_wrap"
		})
		.regexValidator({
			regExp: "notempty",
			dataType: "enum",
			onError: function(){
				Ibosapp.formValidate.setGroupState("#account", "error");
				return Ibos.l("V.INPUT_ACCOUNT");
			}
		});

		// 密码验证
		$("#password").formValidator({
			onFocus: function(){
				Ibosapp.formValidate.setGroupState("#password");
				return Ibos.l("V.INPUT_POSSWORD");
			},
			onCorrect: function(){
				Ibosapp.formValidate.setGroupState("#password", "correct");
			}
		})
		.regexValidator({
			regExp: "notempty",
			dataType: "enum",
			onError: function(){
				Ibosapp.formValidate.setGroupState("#password", "error");
				return Ibos.l("V.INPUT_POSSWORD");
			}
		});

		// 根据 cookie 还原“自动登录” 的勾选状态
		if (U.getCookie("lastautologin") == 1){
			$("[name='autologin']").label("check");
		}

		// 记住 “自动登录” 的勾选状态
		$("#login_form").on("submit", function(){
			if($.formValidator.pageIsValid()){
				U.setCookie("lastautologin", +$("[name='autologin']").prop('checked'));
			}
		});
	}
};
$(function(){
	// 切换找回密码表格
	Login.togglePanel.init();

	// 公告内容过多时自动滚动
	Login.autoScroll();

	// 账号和密码验证，提示
	Login.formValidator();
	
	$("#lg_help").tooltip();

	// 登录背景
	var imgArr = Ibos.app.g("loginBg");

	// LoadImage and set Image fullscreen
	var bgNode = document.getElementById("bg"),
		bgWrap = bgNode.parentNode,
		index = Math.ceil(imgArr.length * Math.random()) - 1;

	$(document.body).waiting(null, "normal");

	U.loadImage(imgArr[index], function(img){
		var imgRatio = img.width / img.height;
		img.style.width = "100%";
		img.style.height = "100%";
		var setWrapSize = function(width, height){
			bgWrap.style.width = width + "px";
			bgWrap.style.height = height + "px";
		};
		var resize = function(ratio){
			var $doc = $(document),
				docWidth = $doc.width(),
				docHeight = $doc.height(),
				ImgTotalWidth,
				ImgTotalHeight;

			setWrapSize(docWidth, $(window).height());

			if (docWidth / docHeight > ratio){
				ImgTotalHeight = docWidth / ratio;
				// 适配宽度
				bgNode.style.width = docWidth + 'px';
				// 按图片比例放大高度，保持图片不变形
				bgNode.style.height = ImgTotalHeight + 'px';
				// 图片垂直居中
				bgNode.style.marginTop = (docHeight - ImgTotalHeight) / 2 + 'px';
				bgNode.style.marginLeft = 'auto';
			} else {
				ImgTotalWidth = docHeight * ratio;
				// 适配高度
				bgNode.style.height = docHeight + 'px';
				// bgNode.style.height = docHeight + 'px';
				// 按图片比例放大高度，保持图片不变形
				bgNode.style.width = ImgTotalWidth + 'px';
				// 图片水平居中
				bgNode.style.marginLeft = (docWidth - ImgTotalWidth) / 2 + 'px';
				bgNode.style.marginTop = 'auto';
			}
			$(document.body).stopWaiting();
			$(bgNode).fadeIn();
		};

		resize(imgRatio);
		window.onresize = function(){
			setWrapSize(0, 0);
			resize(imgRatio);
		};
		bgNode.appendChild(img);
	});

	
	Ibos.evt.add({
		// 清除痕迹
		"clearCookie": function(){
			var result = window.confirm(Ibos.l("LOGIN.CLEAR_COOKIE_CONFIRM"));
			if (result) {
				U.clearCookie();
				Ui.tip(Ibos.l("LOGIN.CLEARED_COOKIE"));
			}
		},
		// 切换登录方式
		"switchLoginType": function(param, elem){
			var $this = $(this),
					$curLi = $this.closest("li"),
					$lis = $this.closest("ul").find("li"),
					index = $curLi.index(),
					$conDiv = $("#login_type_content>div");
			$lis.removeClass("active");
			$curLi.addClass("active");
			$conDiv.hide();
			$conDiv.eq(index).show();
			$.formValidator.resetTipState();
		},
		// 使用微信企业号二维码登陆
		"wxLogin": function(param, elem){
			var url = Ibos.app.url("user/default/wxcode"),
			dialog = Ui.ajaxDialog(url, {
				title: false,
				lock: true,
				padding: 0,
				ok: false,
				width: "300px"
			});
		}
	});
});

