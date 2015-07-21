// JavaScript Document

var dbInit = {
	op: {
		// 登录酷办公
		"loginIbosco": function(param) {
			var url = "?op=coLogin";
			return $.post(url, param, $.noop, "json");
		},
		// 注册酷办公账号
		"registerAccount": function(param) {
			var url = "?op=coRegisterAccount";
			return $.post(url, param, $.noop, "json");
		},
		// 重发验证码
		"afreshCode": function(param) {
			var url = "?op=coAfreshCode";
			return $.post(url, param, $.noop, "json");
		},
		// 发送验证码，验证验证码是否正确
		"verifyCode": function(param) {
			var url = "?op=coVerifyCode";
			return $.post(url, param, $.noop, "json");
		},
		// 设置酷办公密码
		"setPassword": function(param) {
			var url = "?op=coRegister";
			return $.post(url, param, $.noop, "json");
		},
		// 验证企业代码是否唯一
		"verifyCorpCode": function(param) {
			var url = "?op=coVerifyCorpCode";
			return $.post(url, param, $.noop, "json");
		}
	},
	"afterLogin": function(res){
		var mobile = res.mobile,
			role = res.isadmin, // 返回0为非管理员，1为管理员
			shortname = res.shortname,
			$result = $("#relation_content"),
			$cophone = $("#cophone");
		$("#mobile").val("");
		$("#password").val("");
		Ui.dialog.get("relation_dialog").close();
		//额外的数据
		$("#extraData").val(res.extraData);

		// 登录酷办公后, 显示用户账号,且状态改为只读
		$("#administrator_account").val(mobile).removeAttr("data-type").hide();
		$("#result_account").text(mobile).show();

		// 将账号和企业代码提示隐藏
		$("#administrator_account_tip").hide();
		$("#qy_code_tip").hide();

		// 如果是管理员则正常操作
		/*
		 * 以下赋值了的有：
		 * code,
		 * 所以登录注册在赋值不会有问题
		 */
		if (res.corpcode) {
			if (role) {
				var realname = res.realname,
					code = res.corpcode,
					$realname = $("#realname");

				// 登录以后头部显示用户酷办公账号
				$realname.text(realname);
				$cophone.text(mobile);
				$result.animate({top: "-60px"}, 500);

				// 设置酷办公用户角色，用户退出时还原视图
				$("[data-action='exitAccount']").attr("data-role", role);
				// 如果是管理员, 企业代码显示出来,且状态改为只读
				$("#qy_code").val(code).removeAttr("data-type").hide();
				$("#qy_code_result").text(code).show();
				// 如果是管理员, 企业简称显示出来
				$("#short_name").val(shortname);
			} else {
				// 非管理员则提示是否退出原有企业
				Ui.confirm("你不是“ " + shortname + " ”的超级管理员, 绑定后将退出原有企业, 是否确定绑定？", function() {
					$cophone.text(mobile);
					$result.animate({top: "-60px"}, 500);
				});
			}
		} else {
			$cophone.text(mobile);
			$result.animate({top: "-60px"}, 500);
		}
	},
	// 进入倒计时
	"countdownTime": function($elem){
		$elem.button('loading');
		var wait = document.getElementById('counting'),
			time = --wait.innerHTML,
			interval = setInterval(function() {
				var time = --wait.innerHTML;
				if (time === 0) {
					$elem.button('reset');
					clearInterval(interval);
				}
			}, 1000);
	}
};


//正则表达式规则集合(可扩展)
var rNoEmpty = /\S+/;//不为空
var reg = {
	username: rNoEmpty,
	DBpassword: rNoEmpty,
	account: /^1\d{10}$/,
	ADpassword: /^.{5,32}$/, //6到32位数字或者字母组成
	shortname: rNoEmpty,
	qycode: /^[a-zA-Z0-9]{4,20}$/,
	mobile: /^1\d{10}$/
};

// 对表单中每项进行验证	
var validate = {
	// 对数据库用户名进行验证
	username: function(id) {
		var value = $("#" + id).val();
		if (!reg.username.test(value)) {
			$("#" + id + "_tip").show();
			return false;
		}
		return true;
	},
	// 对数据库密码进行验证
	DBpassword: function(id) {
		var value = $("#" + id).val();
		if (!reg.DBpassword.test(value)) {
			$("#" + id + "_tip").show();
			return false;
		}
		return true;
	},
	// 对管理员账号进行验证
	account: function(id) {
		var value = $("#" + id).val(),
			$tip = $("#" + id + "_tip");
		if(value){
			if (!reg.account.test(value)) {
				$tip.text("账号格式不正确").show();
				return false;
			}
		}else{
			$tip.text("账号不能为空！").show();
		}
		return true;
	},
	// 对管理员密码进行验证
	ADpassword: function(id) {
		var value = $("#" + id).val();
		if (!reg.ADpassword.test(value)) {
			$("#" + id + "_tip").show();
			return false;
		}
		return true;
	},
	// 对企业简称进行验证
	shortname: function(id) {
		var val = $("#" + id).val();
		if (!reg.shortname.test(val)) {
			$("#" + id + "_tip").show();
			return false;
		}
		return true;
	},
	// 对企业代码进行验证
	"qycode": function(id) {
		var val = $("#" + id).val(),
				$tip = $("#" + id + "_tip"),
				ajaxverify = +$("#" + id + "_verify").val();
		if (!reg.qycode.test(val)) {
			$tip.text("企业代码格式不正确！").show();
			return false;
		} else {
			if (!ajaxverify) {
				$tip.text("企业代码已存在！").show();
				return false;
			}
		}
		return true;
	}
};

$(function() {
	//创建数据页面,点击显示更多后,隐藏部分信息显示
	$("#table_info").on("click", ".show-info", function() {
		$(".hidden-info").slideDown(100, function() {
			$("#database_server").focus();
		});
		$(this).slideUp(100);
	});

	/*
	 1.勾选自定义模块,立即安装按钮文字变为"下一步",同时表单提交至"下一步"
	 2.取消勾选自定义模块后,下一步按钮文字变为"立即安装",同时表单提交至"立即安装"
	 */
	$("#user_defined").on("change", function() {
		var value = $("#user_defined").prop("checked"),
			text = value ? "下一步" : "立即安装";
		$("#btn_install").text(text);
	});

	//对数据库账号在获取焦点和失去焦点时进行验证操作
	$("#database_name").on({
		"blur": function() {
			var $elem = $(this);
			validate.username(this.id);
		},
		"focus": function() {
			$("#" + this.id + "_tip").hide();
		}
	});

	//对数据库密码在获取焦点和失去焦点时进行验证操作
	$("#database_password").on({
		"blur": function() {
			validate.DBpassword(this.id);
		},
		"focus": function() {
			$("#" + this.id + "_tip").hide();
		}
	});

	//对管理员账号在获取焦点和失去焦点时进行验证操作
	$("#administrator_account").on({
		"blur": function() {
			validate.account(this.id);
		},
		"focus": function() {
			$("#" + this.id + "_tip").hide();
		}
	});

	// 对管理员密码在获取焦点和失去焦点时进行验证操作
	$("#administrator_password").on({
		"blur": function() {
			validate.ADpassword(this.id);
		},
		"focus": function() {
			$("#" + this.id + "_tip").hide();
		}
	});

	// 企业简称进行验证
	$("#short_name").on({
		"blur": function() {
			validate.shortname(this.id);
		},
		"focus": function() {
			$("#" + this.id + "_tip").hide();
		}
	});

	// 企业代码进行验证
	$("#qy_code").on({
		"blur": function() {
			var id = this.id,
				val = $("#" + id).val(),
				param = {code: val},
				$tip = $("#" + id + "_tip"),
				$ajaxverify = $("#" + id + "_verify");
			// 先验证企业代码
			dbInit.op.verifyCorpCode(param).done(function(res) {
				if (res.isSuccess) {
					var isAvailable = res.available;
					if (!isAvailable) {
						$tip.text("企业代码已存在！").show();
						$ajaxverify.val("0");
					} else {
						$ajaxverify.val("1");
						$tip.hide();
					}
				} else {
					Ui.tip(res.msg, "danger");
				}
			});
			validate.qycode(id);
		},
		"focus": function() {
			$("#" + this.id + "_tip").hide();
		}
	});

	//点击立即安装时,对表单进行验证
	$("#user_form").submit(function() {
		var elems = $(this).get(0).elements;
		for (var i = 0; i < elems.length; i++) {
			var elem = elems[i],
				type = elem.getAttribute("data-type"),
				id = elem.id;
			if (validate[type] && !validate[type](id)) {
				// 重置站点数据
				U.clearCookie();
				Ibos.local.clear();
				$(elem).trigger("focus.submit").blink();
				return false;
			}
		}
	});

	var $rwrap = $("#relation_wrap"),
		$afreshCodeBtn = $("#afreshCode");

	Ibos.evt.add({
		"hideRelation": function(param, elem) {
			$(".relation-info-wrap").slideUp();
		},
		// 打开绑定酷办公窗口
		"activeRelation": function(param, elem) {
			var dialog = Ui.dialog({
				title: false,
				id: "relation_dialog",
				content: document.getElementById("relation_dialog"),
				lock: true,
				ok: false,
				close: function() {
					// 当关闭绑定窗口时，回到登录页面
					var $wrap = $("#relation_opt_wrap");
					$wrap.css("top", 0);
				}
			});
		},
		// 立即注册功能
		"activeRegistered": function(param, elem){
			var dialog = Ui.dialog({
				title: false,
				id: "relation_dialog",
				content: document.getElementById("relation_dialog"),
				lock: true,
				ok: false,
				close: function() {
					// 当关闭绑定窗口时，回到登录页面
					var $wrap = $("#relation_opt_wrap");
					$wrap.css("top", 0);
				},
				init: function(){
					var $wrap = $("#relation_opt_wrap");
					$wrap.css("top", "-320px");
				}
			});
		},
		// 登录酷办公账号
		"loginIbosco": function(param, elem) {
			var $this = $(this),
				$mobile = $("#mobile"),
				mobile = $mobile.val(),
				$password = $("#password"),
				password = $password.val(),
				status = reg.mobile.test(mobile);
			if(!status){
				Ui.tip("手机号格式不正确！", "warning");
				$mobile.blink().focus();
				return false;
			}
			if(!password){
				Ui.tip("密码不能为空！", "warning");
				$password.blink().focus();
			}		
			var param = {
				mobile: mobile,
				password: password
			};
			$this.button('loading');
			dbInit.op.loginIbosco(param).done(function(res) {
				if (res.isSuccess) {
					$this.button('reset');
					if (res.status) {
						dbInit.afterLogin(res);
					} else {
						Ui.tip(res.msg, "danger");
					}
				} else {
					$this.button('reset');
					Ui.tip(res.msg, "danger");
				}
			});
		},
		// 退出绑定
		"exitAccount": function(param, elem) {
			var role = $(this).data("role"),
				$cophone = $("#cophone"),
				cophone = $cophone.text(),
				param = {cophone: cophone};
			Ui.confirm("确定退出酷办公？", function() {
				var $content = $("#relation_content"),
					$resCount = $("#result_account"),
					$adCount = $("#administrator_account"),
					$name = $("#short_name"),
					$code = $("#qy_code"),
					$resCode = $("#qy_code_result"),
					$extraData = $("#extraData");
				// 切换为登录酷办公的状态
				$cophone.text("");
				$content.animate({top: 0}, 500);

				// 将设置账号转换为输入状态
				$resCount.text("").hide();
				$adCount.attr("data-type", "account").val("").show();
				$extraData.val("");
				// 若为管理员
				if (role) {
					// 将企业代码转换为可写状态
					$resCode.text("").hide();
					$code.attr("data-type", "qycode").val("").show();
					// 将企业简称转换为可写状态
					$name.val("");
				}
			});
		},
		// 切换注册页和登录页
		"toggleShow": function(param, elem) {
			var $wrap = $("#relation_opt_wrap"),
					top = param.target == "register" ? "-320px" : "0";
			$wrap.animate({top: top}, 500);
		},
		// 注册账号
		"registerAccount": function(param, elem) {
			var $this = $(this),
				$mobile = $("#register_mobile"),
				mobile = $mobile.val(),
				status = reg.mobile.test(mobile);
			if(!status){
				Ui.tip("手机号格式不正确！", "warning");
				$mobile.blink().focus();
				return false;
			}
			var param = {mobile: mobile};
			$this.button('loading');
			dbInit.op.registerAccount(param).done(function(res) {
				if (res.isSuccess) {
					if (res.status) {
						$this.button('reset');

						// 发送手机号至后台后，切换到发送验证码页面
						var $wrap = $("#relation_opt_wrap");
						$wrap.animate({top: "-650px"}, 500);

						// 进入倒计时
						dbInit.countdownTime($afreshCodeBtn);

						$mobile.val("");
						$("#send_mobile").text(mobile);
						$("#reg_mobile").val(mobile);
					} else {
						$this.button('reset');
						Ui.tip("发送验证码失败", "danger");
					}
				} else {
					$this.button('reset');
					Ui.tip(res.msg, "danger");
				}
			});
		},
		// 重新发送验证码
		"afreshCode": function(param, elem) {
			var $this = $(this),
				mobile = $("#reg_mobile").val(),
				param = {_csrf: Ibos.app.g('csrftoken'), mobile: mobile};
			$this.button('loading');
			dbInit.op.afreshCode(param).done(function(res) {
				if (res.isSuccess) {
					if (res.status) {
						var wait = document.getElementById('counting'),
							time = --wait.innerHTML,
							interval = setInterval(function() {
								var time = --wait.innerHTML;
								if (time === 0) {
									$this.button('reset');
									clearInterval(interval);
								}
							}, 1000);
					} else {
						Ui.tip("发送验证码失败", "danger");
					}
				} else {
					$this.button('reset');
					Ui.tip(res.msg, "danger");
				}
			});
		},
		// 发送验证码至后台验证
		"verifyCode": function(param, elem) {
			var $code = $("#code_input"),
				code = $code.val(),
				mobile = $("#reg_mobile").val(),
				param = {code: code, mobile: mobile};
			if(!code){
				Ui.tip("请输入验证码！", "warning");
				$code.blink().focus();
				return false;
			}
			dbInit.op.verifyCode(param).done(function(res) {
				if (res.isSuccess) {
					if (res.status) {
						// 当验证码通过后,进入设置密码
						var $wrap = $("#relation_opt_wrap");
						$wrap.animate({top: "-968px"}, 500);
						$code.val("");
					} else {
						// 当验证码验证不通过时, 应该提示输入正确的验证码
						Ui.tip("请输入正确的验证码", "warning");
					}
				} else {
					Ui.tip("验证失败", "danger");
				}
			});
		},
		// 设置密码
		"setPassword": function(param, elem) {
			var $pwd = $("#set_password"),
				pwd = $pwd.val(),
				mobile = $("#reg_mobile").val(),
				$ressetPwd = $("#reset_password"),
				ressetPwd = $ressetPwd.val();
			if(!pwd){
				$pwd.blink().focus();
				Ui.tip("请输入密码！", "warning");
				return false;
			}
			if(!ressetPwd){
				$ressetPwd.blink().focus();
				Ui.tip("请输入确认密码！", "warning");
				return false;
			}
			if(pwd !== ressetPwd){
				$pwd.blink().focus();
				Ui.tip("两次输入密码不一致！", "warning");
				return false;
			}
			var param = {password: pwd, mobile: mobile};
			dbInit.op.setPassword(param).done(function(res) {
				if (res.isSuccess) {
					dbInit.afterLogin(res);
					Ui.tip("注册成功");
					var $result = $("#relation_content"),
						$wrap = $("#relation_opt_wrap");
					// 登录成功后，关闭设置密码窗口
					$wrap.css("top", 0);
					$pwd.val("");
					$ressetPwd.val("");

					// 切换显示登录信息
					$("#cophone").text(mobile);
					$result.animate({top: "-60px"}, 500);
				} else {
					Ui.tip(res.msg, "danger");
				}
			});
		}
	});
});