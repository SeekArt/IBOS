var Login = Login|| {};
Login = {
	op : {
		//验证用户身份信息
		verifyUserInfo : function(param){
			var url = "";
			return $.post(url, param, $.noop);
		}
	},
	// 表单验证
	formValidate: {
		pageError: function(msg, elem, errorList){
			var data;
			// 如果设置了errorFocus(即出错后focus到第一个出错的控件)
			// 则判断控件的类型，对一些特殊控件做出相应的处理
			if(this.errorFocus){
				// 如果是下拉控件、选人控件
				if( data = $.data(elem, "select2") ){
					data.focus();
				}
			}
		},
		setGroupState: function(input, state){
			var $group = $(input).closest(".input-group"),
				errorCls = "input-group-error",
				correctCls = "input-group-correct",
				hasfocus = "has-focus";

			switch(state) {
				case "correct": 
					$group.removeClass(errorCls).addClass(correctCls);
					break;
				case "error":
					$group.removeClass(correctCls).addClass(errorCls);
					break;
				case "hasfocus":
					$group.removeClass(correctCls).removeClass(errorCls).addClass(hasfocus);
					break;
				default:
					$group.removeClass(correctCls).removeClass(errorCls).removeClass(hasfocus);
			}
		}
	}
};
/**
 * 表单验证
 * @method formVerify
 */
Login.formVerify = function(){
	$.formValidator.initConfig({formID: "login_form", errorFocus: true});
	
	// 企业验证
	$("#companycode").formValidator({
		onFocus: function(){
			Login.formValidate.setGroupState("#companycode", "hasfocus");
			return "请输入企业代码";
		},
		onCorrect: function(){
			Login.formValidate.setGroupState("#companycode", "correct");
		}
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: function(){
			Login.formValidate.setGroupState("#companycode", "error");
			return "请输入企业代码";
		}
	});

	// 密码验证
	$("#phone").formValidator({
		onFocus: function(){
			Login.formValidate.setGroupState("#phone", "hasfocus");
			return "请输入手机号";
		},
		onCorrect: function(){
			Login.formValidate.setGroupState("#phone", "correct");
		}
	})
	.regexValidator({
		regExp: "mobile",
		dataType: "enum",
		onError: function(){
			Login.formValidate.setGroupState("#phone", "error");
			return "请输入手机号";
		}
	});

	// 密码验证
	$("#password").formValidator({
		onFocus: function(){
			Login.formValidate.setGroupState("#password", "hasfocus");
			return "请输入密码";
		},
		onCorrect: function(){
			Login.formValidate.setGroupState("#password", "correct");
		}
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: function(){
			Login.formValidate.setGroupState("#password", "error");
			return "请输入密码";
		}
	});

	$(window).resize(function(){
		$.formValidator.resetTipState();
	});
};
/**
 * 自动登录的操作
 * @method autologin
 */
Login.autoLogin = function(){
	// 根据 cookie 还原“自动登录” 的勾选状态
	if (U.getCookie("lastautologin") == 1){
		$("[name='autologin']").label("check");
	}
	
	$("#login_form").on("submit", function(){
		var qycode = document.loginForm.qycode.value;
		if($.formValidator.pageIsValid()){
			// 跳转子域名登录
			document.loginForm.action = 'http://' + qycode + '.saas.ibos.cn/?r=user/default/login';
			U.setCookie("corp_code", qycode);
			// 记住 “自动登录” 的勾选状态
			U.setCookie("lastautologin", +$("[name='autologin']").prop('checked'));
		}
	});
};
$(function(){
	//表单验证
	Login.formVerify();

	//自动登录的操作
	Login.autoLogin();
});