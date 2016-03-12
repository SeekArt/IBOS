// JavaScript Document
//步骤选择
var flag = 0,
	state = true,
	guideUrl = Ibos.app.url('main/default/guide'),
	valiGroup = {
		COMPANY: "2",
		USER: "3"
	};

// 步骤选择
var chooseStep = {
	/**
	 * 上一步
	 * @method  previous
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	previous: function(elem) {
		var $parent = $(elem).closest(".mark");
		$parent.removeClass("show").addClass("hidden")
			.prev().removeClass("hidden").addClass("show");
	},
	/**
	 * 下一步
	 * @method  next
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	next: function(elem) {
		var $parent = $(elem).closest(".mark");
		$parent.removeClass("show").addClass("hidden")
			.next().removeClass("hidden").addClass("show");
	}
};


var administrator = {
	/**
	 * ajax获取数据
	 * @method ajax
	 * @param  {String}   url     传入url地址
	 * @param  {Object}   param   传入JSON格式数据
	 * @param  {Function} success 成功后回调函数
	 * @param  {Function} error   失败后回调函数
	 */
	ajax: function(url, param, success, error) {
		$.post(url, param, function(res) {
			if (res.isSuccess) {
				if ($.isFunction(success)) {
					success.call(null, res);
				}
			} else {
				$.isFunction(error) && error.call(null, res);
			}
		}, 'json');
	}
};
//提交数据后,将输入框内容重置为空,显示提示填写内容
var view = {
	/**
	 * 重置
	 * @method restore
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	restore: function(elem) {
		var value = $(elem);
		var lenght = value.length;
		for (var i = 0; i < lenght; i++) {
			$(value[i]).val("").removeClass("input-correct input-error");
		}
	}
};

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
		onError: Ibos.l("V.USERNAME_VALIDATE")
	})
	//验证用户名是否已被注册
	.ajaxValidator($.extend(ajaxValidateSettings, {
		onError: Ibos.l("V.USERNAME_EXISTED")
	}));

	//密码验证
	var pwdMin = Ibos.app.g("passwordMinLength"),
		pwdMax = Ibos.app.g("passwordMaxLength");
	$("#password").formValidator({
		validatorGroup: valiGroup.USER
	})
	.inputValidator({
		min: pwdMin,
		max: pwdMax,
		onError: Ibos.l("V.PASSWORD_PREG", {
			min: pwdMin,
			max: pwdMax,
			mixed: Ibos.app.g("passwordMixed") == '1' ?  Ibos.l("RULE.CONTAIN_NUM_AND_LETTER") : ''
		})
	})
	.regexValidator({
		regExp: Ibos.app.g('passwordRegex'),
		dataType:"string",
		onError: Ibos.app.g('passwordMixed') ? 
				Ibos.l("V.PASSWORD_PREG", { 
					min: pwdMin,
					max: pwdMax, 
					mixed: Ibos.l("RULE.CONTAIN_NUM_AND_LETTER")
				}) 
				: Ibos.l("RULE.PASSWORD")
	});

	//电话号码验证
	$("#mobile").formValidator({
		empty: true,
		validatorGroup: valiGroup.USER
	})
	.regexValidator({
		regExp: "mobile",
		dataType: "enum",
		onError: Ibos.l("MOBILE_VALIDATE")
	})
	//验证手机是否已被注册
	.ajaxValidator($.extend(ajaxValidateSettings, {
		onError: Ibos.l("V.MOBILE_EXISTED"),
	}));

	//邮件地址验证
	$("#email").formValidator({
		empty: true,
		validatorGroup: valiGroup.USER
	})
	.regexValidator({
		regExp: "email",
		dataType: "enum",
		onError: Ibos.l("EMAIL_ADDRESS_VALIDATE")
	})
	//验证邮箱是否已被注册
	.ajaxValidator($.extend(ajaxValidateSettings, {
		onError: Ibos.l("V.EMAIL_EXISTED"),
	}));

	//真实姓名验证
	$("#realname").formValidator({
		empty: true,
		validatorGroup: valiGroup.USER
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: Ibos.l("RULE.REALNAME_CANNOT_BE_EMPTY")
	});

	//公司全称验证
	$("#full_name").formValidator({
		validatorGroup: valiGroup.COMPANY
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: Ibos.l("RULE.INVALID_FORMAT")
	});

	//公司简称验证
	$("#in_short").formValidator({
		validatorGroup: valiGroup.COMPANY
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: Ibos.l("RULE.INVALID_FORMAT")
	});

	//系统URL验证
	$("#url").formValidator({
		validatorGroup: valiGroup.COMPANY
	})
	.regexValidator({
		regExp: ["url", "local"],
		dataType: "enum",
		onError: Ibos.l("RULE.URL_INVALID_FORMAT")
	});

	//填写内容验证
	$("#write_content").formValidator({
		validatorGroup: valiGroup.COMPANY
	})
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: Ibos.l("RULE.INVALID_FORMAT")
	});

	//点击上一步
	$(".as-previous-step").on("click", function() {
		var $elem = $(this);
		chooseStep.previous($elem);
	});

	// 隐藏初始化引导界面
	$(".closs-init").on("click", function() {
		//取消遮罩
		Ui.modal.hide();
		$("#initialize_guide").hide();
		In.startIntro();
	});
	// 不再提醒引导
	$("#never_write_again").on("click", function() {
		$.post(Ibos.app.url('main/default/guide'), {
			op: 'neverGuideAgain'
		});
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
		$.formValidator.resetTipState(valiGroup.USER);
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
				$("#add_account").hide();
				$("#continue_add").show();

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
		$("#add_account").show();
		$(this).hide();
	});
});