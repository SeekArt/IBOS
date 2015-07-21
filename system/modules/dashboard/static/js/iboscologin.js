$.formValidator.initConfig({formID: "ibosco_login_form", errorFocus: true});

$("#mobile").formValidator()
.regexValidator({
	regExp: "mobile",
	dataType: "enum",
	onError: Ibos.l("RULE.MOBILE_INVALID_FORMAT")
});

$("#password").formValidator()
.regexValidator({
	regExp: "notempty",
	dataType: "enum",
	onError: Ibos.l("RULE.PASSWORD_CANNOT_BE_EMPTY")
});

Ibos.evt.add({
	"loginIbosCo": function(param, elem) {
		var isPass = $.formValidator.pageIsValid();
		if (isPass) {
			var mobile = $("#mobile").val(),
					password = $("#password").val(),
					param = {mobile: mobile, password: password},
			url = Ibos.app.url('dashboard/cobinding/loginco');
			$.post(url, param, function(res) {
				if (res.isSuccess) {
					// 跳转到信息页
					Ui.tip(Ibos.l("CO.LOGIN_SUCCESS"));
					window.location.href = Ibos.app.url('dashboard/cobinding/index');
				} else {
					Ui.tip(res.msg, "danger");
				}
			}, "json");
		}
	}
});