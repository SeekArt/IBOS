/**
 * 普通用户初始化引导
 * @version $Id$
 */
var guideUrl = Ibos.app.url('main/default/guide');

//设置对应项匹配成功后的百分比值
var percent = {
	password: 20,
	mobile: 20,
	email: 10,
	birthday: 10,
	avatar: 30
};

//初始化d对应项的匹配初始状态
var vali = {
	password: false,
	avatar: false,
	mobile: false,
	email: false,
	birthday: false
};

// 验证类型
var valiField = ['password', 'avatar', 'mobile', 'email', 'birthday'];


var valiGroup = {
	PASSWORD: "2"
};

//第二步骤中,完成某项后对应头部的下一步骤提示和对应完成增加值
var tip = {
	password: $.noop,
	/**
	 * 手机
	 * @method mobile
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	mobile: function(elem) {
		$(elem).text(U.lang("GUIDE.WRITE_PHONE_NUMBER"));
		$(elem).next().text("+10%");
	},
	/**
	 * 邮箱
	 * @method email
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	email: function(elem) {
		$(elem).text(U.lang("GUIDE.WRITE_EMAIL_ADDRESS"));
		$(elem).next().text("+10%");
	},
	/**
	 * 生日
	 * @method birthday
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	birthday: function(elem) {
		$(elem).text(U.lang("GUIDE.WRITE_PERSONAL_BIRTHDAY"));
		$(elem).next().text("+10%");
	},
	/**
	 * 头像
	 * @method avatar
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	avatar: function(elem) {
		$(elem).text(U.lang("GUIDE.UPLOAD_REAL_AVATAR"));
		$(elem).next().text("+30%");
	}
};

/**
 * 检查个人信息数据
 * @method  checkPersonalData
 */
function checkPersonalData(){
	var field;
	for (var i = 0; i < valiField.length; i++) {
		field = valiField[i];
		initialize.setProgress("#progress_two");
		if(!vali[field]){
			tip[field]("#tip_step");
			break;	
		}
	}
}

//各项设置值的匹配
var validate = {
	/**
	 * 手机匹配
	 * @method mobile
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	mobile: function(elem) {
		var reg = /\S+/;
		var elemval = $("#" + elem).val();
		var value = $.formValidator.isOneValid(elem);

		vali.mobile = value && reg.test(elemval);

		checkPersonalData();
	},
	/**
	 * 邮件匹配
	 * @method email
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	email: function(elem) {
		var reg = /\S+/;
		var elemval = $("#" + elem).val();
		var value = $.formValidator.isOneValid(elem);
		vali.email = value && reg.test(elemval);
		checkPersonalData();
	},
	/**
	 * 生日匹配
	 * @method birthday
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	birthday: function(elem) {
		var reg = /\S+/;
		var values = $("#" + elem).val();
		var value = $.formValidator.isOneValid(elem);
		vali.birthday = value && reg.test(values);
		checkPersonalData();
	}
};

//密码设置时,成功和失败情况下,进度条和提示锁的表现切换
var view = {
	/**
	 * 密码设置失败
	 * @method failure
	 * @param  {Number} progress 进度值
	 * @param  {Object} flock    失败的提示锁
	 * @param  {Object} slock    成功的提示锁
	 */
	failure: function(progress, flock, slock) {
		var failureState = $(flock);
		var successState = $(slock);
		initialize.setPdProgress(progress);
		failureState.show();
		successState.fadeOut(500, function() {
			failureState.css("z-index", "10");
			successState.css("z-index", "5");
		});
	},
	/**
	 * 密码设置成功
	 * @method success
	 * @param  {Number} progress 进度值
	 * @param  {Object} flock    失败的提示锁
	 * @param  {Object} slock    成功的提示锁
	 */
	success: function(progress, flock, slock) {
		var failureState = $(flock);
		var successState = $(slock);
		initialize.setPdProgress(progress);
		successState.show();
		failureState.fadeOut(500, function() {
			successState.css("z-index", "10");
			failureState.css("z-index", "5");
		});
	}
};

//当完成度到90%时,进度条颜色的改变
var progressState = {
	/**
	 * 正在进行的进度条样式改变
	 * @method going
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	going: function(elem) {
		$(elem).removeClass("progress-bar-success");
	},
	/**
	 * 结束后的进度条样式改变
	 * @method approachFinish
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	approachFinish: function(elem) {
		$(elem).addClass("progress-bar-success");
	}
};

var initialize = {
	/**
	 * 第二步骤循环匹配后的视图操作
	 * @method setProgress
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	setProgress: function(elem) {
		var p = 0;
		for (var i in vali) {
			vali[i] && (p += percent[i]);
		}
		$("#percent_nub_two").text(p);
		$("#progress_three").css("width", p + "%");
		$(elem).css("width", p + "%");
	},
	/**
	 * 第一步骤匹配后的视图操作
	 * @method setPdProgress
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	setPdProgress: function(elem) {
		var p = 0;
		if (vali.password) {
			p += percent.password;
		}
		$(elem).css("width", p + "%");
		$("#percent_nub_one").text(p);
	},
	/**
	 * 第一步骤的匹配操作
	 * @method matchTo
	 * @param  {Object} elem 传入Jquery节点对象
	 */
	matchTo: function() {
		var isValid = $.formValidator.isOneValid;
		var raw = isValid("raw_password");
		var newvalue = isValid("new_password");
		var sure = isValid("sure_password");
		if (raw && newvalue && sure) {
			vali.password = true;
			view.success("#progress_one", "#tip_lock", "#success_lock");
		} else {
			vali.password = false;
			view.failure("#progress_one", "#tip_lock", "#success_lock");
		}
		initialize.setProgress("#progress_two");
	},
	/**
	 * 判断值是否达到90%
	 * @method judge
	 * @param  {Number} p    进度值
	 * @param  {Object} elem 传入Jquery节点对象
	 * @return {[type]}      [description]
	 */
	judge: function(p, elem) {
		if (p >= 90) {
			//进度条颜色变绿色
			progressState.approachFinish(".progress-bar");
			//头部提示语的改变
			$(elem).text(U.lang("GUIDE.JUST_A_LITTLE_MORE")).next().text(U.lang("GUIDE.IMMEDIATELY_TO_FILL_OUT"));
		} else {
			progressState.going(".progress-bar");
			$(elem).text(U.lang("GUIDE.DATA_HAS_NOT_FILLED_OUT")).next().text(U.lang("GUIDE.CONTINUE_TO_IMPROVE"));
		}
	},
	/**
	 * ajax访问
	 * @method ajax
	 * @param  {String}   url       传入请求url地址
	 * @param  {Object}   param     传入JSON格式数据
	 * @param  {Function} [success] 成功后的回调函数
	 */
	ajax: function(url, param, success) {
		$.post(url, param, function(res) {
			if (res.isSuccess) {
				if (success && $.isFunction(success)) {
					success.call(null, res);
				}
			} else {
			}
		}, 'json');
	},
	/**
	 * 头像
	 * @Method avatar
	 * @param  {Object} uploadParam 传入JSON格式数据
	 */
	avatar: function(uploadParam) {
		var attachUpload = Ibos.upload.image($.extend({
			button_placeholder_id: "upload_img",
			file_size_limit: "2000", //设置图片最大上传值
			button_width: "130",
			button_height: "130",
			button_image_url: "",
			custom_settings: {
				//头像上传成功后的操作
				success: function(file, data) {
					if(data.IsSuccess){
						// 上传头像的路径
						$("#img_src").val(data.file);
						//将上传后的图片显示出来
						$("#portrait_img").css("display", "block").attr("src", data.data);
						//改变初始化的头像判断值
						vali.avatar = true;
						//改变进度条视图
						initialize.setProgress("#progress_two");
						//改变步骤提示语
						$("#tip_step").text(U.lang("GUIDE.WRITE_PHONE_NUMBER"));
						//当头像上传成功后,鼠标移入移除时,显示和隐藏头像的覆盖层
						$("#portrait_block").hover(function() {
							$("#tip_tier").toggle();
						});
					} else {
						Ui.tip(data.msg, 'danger');
						return false;
					}
					checkPersonalData();
				},

				progressId: "portrait_img_wrap"
			}
		}, uploadParam));
	}
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
		$parent.removeClass("show").addClass("hidden");
		$parent.next().removeClass("hidden").addClass("show");
	}
};

$(function() {
	//点击上一步
	$(".previous-step").on("click", function() {
		var $elem = $(this);
		chooseStep.previous($elem);
	});

	//原密码的change事件操作
	$("#raw_password").change(function() {
		initialize.matchTo();
	});

	//新密码的change事件操作
	$("#new_password").change(function() {
		initialize.matchTo();
	});
	//确认密码的change事件操作
	$("#sure_password").change(function() {
		initialize.matchTo();
	});

	//手机的change事件操作
	$("#mobile").change(function() {
		validate.mobile("mobile");
	});

	//邮件的change事件操作
	$("#email").change(function() {
		validate.email("email");
	});

	//生日的change事件操作
	$("#birthday").change(function() {
		validate.birthday("birthday");
	});

	//初始化日期选择
	$("#date_time").datepicker().on("show", function(){
		$(this).data('datetimepicker').widget.css("z-index", "2001");
	});

	// 通用AJAX验证配置
	var ajaxValidateSettings = {
		type: 'GET',
		dataType: "json",
		async: true,
		url: Ibos.app.url('main/default/guide', {op: 'modifyPassword', checkOrgPass: '1'}),
		success: function(res) {
			//数据是否可用？可用则返回true，否则返回false
			return !!res.isSuccess;
		}
	};

	$.formValidator.initConfig({
		formID: "ins_form"
	});
	$.formValidator.initConfig({
		formID: "ins_form",
		validatorGroup: valiGroup.PASSWORD,
		errorFocus: true
	});
	var passwordValidSetting = {
		min: Ibos.app.g("passwordMinLength"),
		max: Ibos.app.g("passwordMaxLength"),
		onError: U.lang("V.PASSWORD_PREG", {
			min: Ibos.app.g("passwordMinLength"),
			max: Ibos.app.g("passwordMaxLength"),
			mixed: Ibos.app.g("passwordMixed") == '1' ?  U.lang("RULE.CONTAIN_NUM_AND_LETTER") : ''
		})
	};

	//原密码验证
	$("#raw_password").formValidator({
		validatorGroup: valiGroup.PASSWORD
	})
	.ajaxValidator($.extend(ajaxValidateSettings, {
		onError: U.lang("V.ORIGINAL_PASSWORD_INPUT_INVALID")
	}));

	//新密码验证
	$("#new_password").formValidator({
		validatorGroup: valiGroup.PASSWORD
	})
	.inputValidator(passwordValidSetting)
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

	//确认密码验证
	$("#sure_password").formValidator({
		validatorGroup: valiGroup.PASSWORD
	})
	.inputValidator(passwordValidSetting).compareValidator({
		desID: "new_password",
		onError: U.lang("TWICE_INPUT_INCONFORMITY"),
		validateType: "compareValidator"
	});

	//电话验证
	$("#mobile").formValidator({
		empty: true
	})
	.regexValidator({
		regExp: "mobile",
		dataType: "enum",
		onError: U.lang("MOBILE_VALIDATE")
	});

	//邮件验证
	$("#email").formValidator({
		empty: true
	})
	.regexValidator({
		regExp: "email",
		dataType: "enum",
		onError: U.lang("EMAIL_ADDRESS_VALIDATE")
	});

	//生日验证
	$("#birthday").formValidator({
		empty: true
	})
	.regexValidator({
		regExp: "date",
		dataType: "enum",
		onError: U.lang("BIRTHDAY_CANNOT_BE_EMPTY")
	});

	//上传头像部分的鼠标移入移除效果切换
	$("#portrait_block").mouseover(function() {
		$("#portrait_tip").animate({
			width: 'show',
			opacity: 'show'
		}, 'normal', function() {
			$(".portrait-tip").show();
		});
	}).mouseout(function() {
		$('#portrait_tip').animate({
			width: 'hide',
			opacity: 'hide'
		},
		'normal', function() {
			$('.portrait-tip').hide();
		});
	});

	//隐藏初始化引导界面
	$(".closs-init").on("click", function() {
		Ui.modal.hide();
		$("#initialize_guide").css("display", "none");
		In.startIntro();
	});
	// 下次再填，提交给后台保存cookie，本次登陆不再提醒引导
	$("#later_write_step").on("click", function() {
		$.post(Ibos.app.url('main/default/guide'), {op: 'guideNextTime'});
		$.formValidator.resetTipState(valiGroup.PASSWORD);
	});

	//间隔0.5秒检测表单中对应项是否满足条件,并改变视图
	setInterval(function() {
		initialize.matchTo();
	}, 500);


	//第一步骤点击下一步时,提交第一步骤填写数据
	$("#next_step_one").on("click", function() {
		var isValid = $.formValidator.pageIsValid(valiGroup.PASSWORD);
		if (isValid) {
			var param = {
				op: 'modifyPassword',
				originalpass: $("#raw_password").val(),
				newpass: $("#new_password").val(),
				newpass_confirm: $("#sure_password").val()
			};
			$(".main").waiting(null, 'normal', true);
			initialize.ajax(guideUrl, param, function(res) {
				if (res.isSuccess) {
					$(".main").waiting(false);
					// 填充姓名、手机、邮箱
					$("#realname").text(res.realname);
					$("#mobile").val(res.mobile).change();
					$("#email").val(res.email).change();

					//点击下一步骤时,对第二步骤中的数据进行验证
					// validate.mobile("mobile");
					// validate.email("email");
					// validate.birthday("birthday");
					chooseStep.next("#next_step_one");
				} else {
					Ui.tip(res.msg, 'danger');
					return false;
				}
			});
		}
	});

	//第二步骤点击下一步时,提交第二步骤填写数据
	$("#next_step_two").on("click", function() {
		if($.formValidator.pageIsValid()) {
			var param = {
				op: 'modifyProfile',
				src : $("#img_src").val(),
				mobile: $("#mobile").val(),
				email: $("#email").val(),
				birthday: $("#birthday").val()
			};
			$(".main").waiting(null, 'normal', true);
			$.formValidator.resetTipState();
			initialize.ajax(guideUrl, param, function(res) {
				if (res.isSuccess) {
					if(res.isInstallWeibo){
						//如果安装微博,则显示"与大家打个招呼"
						$("#greet_others").css('display','inline-block');
					}else{
						//如果没有安装微博则显示"进入我的主页""
						$("#go_myhome").css('display','inline-block');
					}
					$(".main").waiting(false);
					chooseStep.next("#next_step_two");
				}
			});
		}
	});

	//间隔0.5秒检测完成度,当达到90%以上时,改变最后一步骤的语言提示和进度条颜色
	setInterval(function() {
		var p = 0;
		for (var i in vali) {
			vali[i] && (p += percent[i]);
		}
		initialize.judge(p, "#last_tip");
	}, 500);
	
	// 头像上传初始化
	initialize.avatar({
		upload_url: Ibos.app.g("avatarUploadUrl")
	});
});
