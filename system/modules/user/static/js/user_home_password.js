/**
 * 个人中心--修改密码
 * @author 		inaki
 * @version 	$Id$
 */

$(function(){
	// 通用AJAX验证配置
	var ajaxValidateSettings = {
		type: 'GET',
		dataType: "json",
		async: true,
		url: Ibos.app.url('main/default/guide', { op: 'modifyPassword', checkOrgPass: '1' }),
		success: function(res) {
			//数据是否可用？可用则返回true，否则返回false
			return !!res.isSuccess;
		}
	};
	var passwordSettings = Ibos.app.g("password");

	$.formValidator.initConfig({
		formID: "password_form"
	});
	$.formValidator.initConfig({
		formID: "password_form",
		validatorGroup: "2",
		errorFocus: true
	});
	var passwordValidSetting = {
		min: passwordSettings.minLength,
		max: passwordSettings.maxLength,
		onError: Ibos.l("V.PASSWORD_LENGTH_RULE", {
			min: passwordSettings.minLength,
			max: passwordSettings.maxLength
		})
	};

	//原密码验证
	$("#raw_password").formValidator({
		validatorGroup: "2"
	}).ajaxValidator($.extend(ajaxValidateSettings, {
		onError: Ibos.l("V.ORIGINAL_PASSWORD_INPUT_INVALID")
	}));

	//新密码验证
	$("#new_password").formValidator({
		validatorGroup: "2"
	})
	.inputValidator(passwordValidSetting)
	.regexValidator({
		regExp: passwordSettings.regex,
		dataType:"string",
		onError: Ibos.l("RULE.CONTAIN_NUM_AND_LETTER")
	});

	//确认密码验证
	$("#sure_password").formValidator({
		validatorGroup: "2"
	})
	.inputValidator(passwordValidSetting).compareValidator({
		desID: "new_password",
		onError: Ibos.l("TWICE_INPUT_INCONFORMITY"),
		validateType: "compareValidator"
	});
});