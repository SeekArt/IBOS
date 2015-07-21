// JavaScript Document
//步骤选择
var flag = 0,
	state = true,
	guideUrl = Ibos.app.url('main/default/guide'),
	valiGroup = {
		COMPANY: "2",
		USER: "3"
	};

var chooseStep = {
	//上一步
	previous: function(elem) {
		var $parent = $(elem).closest(".mark");
		$parent.removeClass("show").addClass("hidden")
			.prev().removeClass("hidden").addClass("show");
	},
	//下一步
	next: function(elem) {
		var $parent = $(elem).closest(".mark");
		$parent.removeClass("show").addClass("hidden");
		$parent.next().removeClass("hidden").addClass("show");
	}
};

var administrator = {
	ajax: function(url, param, success, error) {
		$.post(url, param, function(res) {
			if (res.isSuccess) {
				if ($.isFunction(success)) {
					success.call(null, res);
				}
			} else {
				$.isFunction(error) && error.call(null, res)
			}
		}, 'json');
	}
};
//提交数据后,将输入框内容重置为空,显示提示填写内容
var view = {
	restore: function(elem) {
		var value = $(elem);
		var lenght = value.length;
		for (var i = 0; i < lenght; i++) {
			$(value[i]).val("").removeClass("input-correct input-error");
		}
	}
};

/*var validatAccount = function() {
	var isValid = $.formValidator.isOneValid('username') && $.formValidator.isOneValid('password');
	if(isValid){
		$('#add_account').removeAttr('disabled');
	}else{
		$('#add_account').attr("disabled","disabled");
	}
} */

$(function() {
	// 通用AJAX验证配置
	var ajaxValidateSettings = {
		type: 'GET',
		dataType: "json",
		async: true,
		url: Ibos.app.url("dashboard/user/IsRegistered"),
		success: function(res) {
			//数据是否可用？可用则返回true，否则返回false
			return !!res.isSuccess;
		}
	};
	$.formValidator.initConfig({
		formID: "ad_form",
		errorFocus: true
	});
	$.formValidator.initConfig({
		formID: "ad_form",
		validatorGroup: valiGroup.COMPANY,
		errorFocus: true
	});
	$.formValidator.initConfig({
		formID: "ad_form",
		validatorGroup: valiGroup.USER,
		errorFocus: true
	});

	$("#username").formValidator({
		validatorGroup: valiGroup.USER
	})
	.inputValidator({
		min: 4,
		max: 20,
		onError: U.lang("V.USERNAME_VALIDATE")
	})
	//验证用户名是否已被注册
	.ajaxValidator($.extend(ajaxValidateSettings, {
		onError: U.lang("V.USERNAME_EXISTED")
	}));

	//密码验证
	$("#password").formValidator({
		validatorGroup: valiGroup.USER
	})
	.inputValidator({
		min: Ibos.app.g("passwordMinLength"),
		max: Ibos.app.g("passwordMaxLength"),
		onError: U.lang("V.PASSWORD_PREG", {
			min: Ibos.app.g("passwordMinLength"),
			max: Ibos.app.g("passwordMaxLength"),
			mixed: Ibos.app.g("passwordMixed") == '1' ?  U.lang("RULE.CONTAIN_NUM_AND_LETTER") : ''
		})
	})
	.regexValidator({
		regExp: Ibos.app.g('passwordRegex'),
		dataType:"string",
		onError: Ibos.app.g('passwordMixed') ? 
				U.lang("V.PASSWORD_PREG", { 
					min: Ibos.app.g("passwordMinLength"),
					max: Ibos.app.g("passwordMaxLength"), 
					mixed: U.lang("RULE.CONTAIN_NUM_AND_LETTER")
				}) 
				: U.lang("RULE.PASSWORD")
	});

	//电话号码验证
	$("#mobile").formValidator({
		empty: true,
		validatorGroup: valiGroup.USER
	})
	.regexValidator({
		regExp: "mobile",
		dataType: "enum",
		onError: U.lang("MOBILE_VALIDATE")
	})
	//验证手机是否已被注册
	.ajaxValidator($.extend(ajaxValidateSettings, {
		onError: U.lang("V.MOBILE_EXISTED"),
	}));

	//邮件地址验证
	$("#email").formValidator({
		empty: true,
		validatorGroup: valiGroup.USER
	})
	.regexValidator({
		regExp: "email",
		dataType: "enum",
		onError: U.lang("EMAIL_ADDRESS_VALIDATE")
	})
	//验证邮箱是否已被注册
	.ajaxValidator($.extend(ajaxValidateSettings, {
		onError: U.lang("V.EMAIL_EXISTED"),
	}));

	//真实姓名验证
	$("#realname").formValidator({
		empty: true,
		validatorGroup: valiGroup.USER
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: U.lang("RULE.REALNAME_CANNOT_BE_EMPTY")
	});

	//公司全称验证
	$("#full_name").formValidator({
		validatorGroup: valiGroup.COMPANY
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: U.lang("RULE.INVALID_FORMAT")
	});

	//公司简称验证
	$("#in_short").formValidator({
		validatorGroup: valiGroup.COMPANY
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: U.lang("RULE.INVALID_FORMAT")
	});

	//系统URL验证
	$("#url").formValidator({
		validatorGroup: valiGroup.COMPANY
	})
	.regexValidator({
		regExp: ["url", "local"],
		dataType: "enum",
		onError: U.lang("RULE.URL_INVALID_FORMAT")
	});

	//填写内容验证
	$("#write_content").formValidator({
		validatorGroup: valiGroup.COMPANY
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: U.lang("RULE.INVALID_FORMAT")
	});

	//点击上一步
	$(".as-previous-step").on("click", function() {
		var $elem = $(this);
		chooseStep.previous($elem);
	});


	/*var accountValidat = setInterval(function() {
		var isValid = $.formValidator.isOneValid('username') && $.formValidator.isOneValid('password');
		if(isValid){
			$('#add_account').removeAttr('disabled');
		}else{
			$('#add_account').attr("disabled","disabled");
		}
	}, 500);*/

	//点击下一步 ///////重复了
	//	$(".as-next-step").on("click", function() {
	//		var $elem = $(this);
	//		chooseStep.next($elem);
	//	});

	// 隐藏初始化引导界面
	$(".closs-init").on("click", function() {
		//取消遮罩
		Ui.modal.hide();
		$("#initialize_guide").css("display", "none");
		In.startIntro();
	});
	// 下次再填，提交给后台保存cookie，本次登陆不再提醒引导
	$("#later_write_step").on("click", function() {
		$.post(Ibos.app.url('main/default/guide'), {
			op: 'guideNextTime'
		});
		//		In.startIntro();
	});

	//第一步骤中点击下一步时的操作	
	$("#next_step_one").on("click", function() {
		var isValid = $.formValidator.pageIsValid(valiGroup.COMPANY);
		var param = {
			op: 'companyInit',
			fullname: $("#full_name").val(),
			shortname: $("#in_short").val(),
			systemurl: $("#url").val(),
			depts: $("#write_content").val()
		};
		if (isValid) {
			$(".main").waiting(null, 'normal', true);
			administrator.ajax(guideUrl, param, function(res) {
				if (res.isSuccess) {
					$(".main").waiting(false);
					// 获取返回的部门和岗位缓存数据，插入到部门和岗位select中
					var positions = res.positions,
						posStr = '';
					// 先删除原有的节点(因为用户添加完一次点击上一次的话，已经添加好的节点会保存)
					$("#department").children().remove();
					$("#position").children().remove();
					for (var j in positions) {
						posStr += '<option value="' + positions[j]['positionid'] + '">' + positions[j]['posname'] + '</option>';
					}
					$("#department").append(res.depts);
					$("#position").append(posStr);
					chooseStep.next("#next_step_one");
				} else {
					Ui.tip(res.msg, 'danger');
					return false;
				}
			});
		}
	});

	//第二步骤中点击下一步时的操作	
	$("#next_step_two").on("click", function() {
		chooseStep.next("#next_step_two");
		$.formValidator.resetTipState(valiGroup.USER)
	});

	//点击继续添加操作
	$("#add_account").on("click", function() {
		var param = {
			op: 'addUser',
			username: $("#username").val(),
			password: $("#password").val(),
			realname: $("#realname").val(),
			mobile: $("#mobile").val(),
			email: $("#email").val(),
			deptid: $("#department").val(),
			positionid: $("#position").val()
		};
		//获取需要进行验证项的结果
		var isValid = $.formValidator.pageIsValid(valiGroup.USER);
		//满足验证条件后才进行提交数据操作
		if (isValid && state) {
			//第一次点击后,将状态值设置false，当第二次点击时(isValid && state)返回false，无法再次发送请求,只有ajax返回true后才能执行下一次添加
			state = false;
			//将添加按钮设置为禁用状态样式
			//$("#add_account").addClass('disabled');
			$('#add_account').attr("disabled", "disabled");

			administrator.ajax(guideUrl, param, function(res) {
				//当ajax执行完毕后,点击按钮恢复可点击状态

				$('#add_account').removeAttr('disabled');
				//将表单重置
				view.restore(".personal");
				//改变已添加账号计数
				$("#add_number").text(flag += 1);
				//返回状态true，可进行下一次添加操作
				state = true;

				//添加成功时切换到成功添加提示页面
				$("#success_add_page").animate({
					marginTop: '0'
				}, 200);
				$("#add_account").css("display", "none");
				$("#continue_add").css("display", "block");

			}, function(res) {
				Ui.tip(res.msg, 'danger');
				return false;
			});
		}
	});

	//点击继续添加时，切换到添加表单页面
	$("#continue_add").on("click", function() {
		$("#success_add_page").animate({
			marginTop: '-228px'
		}, 200);
		$("#add_account").css("display", "block");
		$(this).css("display", "none");
	});
});