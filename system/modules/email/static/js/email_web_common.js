$(function(){		
	$.formValidator.initConfig({formID: "add_form"});
	$("#mal").formValidator().regexValidator({
		dataType: "enum",
		regExp: "email",
		onError: Ibos.l("RULE.EMAIL_INVALID_FORMAT")
	});
	var urlVali = {
		dataType: "enum",
		regExp: "url",
		onError: Ibos.l("EM.SERVER_URL_VALIDATE")
	};
	$("#mal_pop_server, #mal_smtp_server").formValidator().regexValidator(urlVali);
	var portVali = {
		dataType: "enum",
		regExp: "num1",
		onError: Ibos.l("EM.PORT_VALIDATE")
	};
	$("#mal_pop_port, #mal_smtp_port").formValidator().regexValidator(portVali);
});