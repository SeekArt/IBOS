$(function(){
	/*---- 初始化表单验证 */
	$.formValidator.initConfig({formID: "email_form", onError: Ibosapp.formValidate.pageError});
	$("#toids").formValidator({ 
		relativeID: "toids_row",
		onFocus: U.lang("EM.SELECT_RECEIVER")
	})
	.functionValidator({
		fun: function(txt, elem){
			// 内部收件人及外部收件人只要有一者即可允许发送
			var toWebEmail = document.getElementById("to_web_email");
			if($.trim(elem.value) === "") {
				if (toWebEmail && $.trim(toWebEmail.value) !== "") {
					return true;
				}
				return false;
			}
			return true;
		},
	 	onError: U.lang("EM.SELECT_RECEIVER")
	})
	.on("change", function(){ $(this).trigger("blur"); });

	$("#mal_title").formValidator()
	.regexValidator({
		dataType: "enum",
		regExp: "notempty",
		onError: U.lang("RULE.SUBJECT_CANNOT_BE_EMPTY")
	});


	/*---- 初始化百度编辑器 */
	var urlParam = U.getUrlParam();
	var ue = new UE.getEditor('editor', {
		initialFrameWidth: 738,
		minFrameWidth: 738,
		toolbars: UEDITOR_CONFIG.mode.simple,
		focus: urlParam.op == "reply" || urlParam.op == "replyAll"
	});

	/*---- 离开页面提示 */
	Ibos.checkFormChange("#email_form", U.lang("EM.MAIL_UNSAVE_WARNING"));
	ue.addListener("contentChange", function(){
		$("#email_form").trigger("formchange");
	});

	/*---- 初始化用户选择框*/
	$("#toids, #copytoids, #secrettoids").userSelect({
		type: "user",
		data: Ibos.data.get('user')
	});


	/*---- 附件上传 */
	var attachUpload = Ibos.upload.attach({
		post_params: {module: 'email'},
		custom_settings: {
			containerId: "file_target",
			inputId: "attachmentid"
		}
	});

	/*---- 邮件标题颜色 */
	var MAL_LEVEL_COLOR = {
		NORMAL: '',
		URGENCY: 'xcr',
		IMPORTANT: 'xcgn'
	},
	MAL_LEVEL_MAP = {
		'0': MAL_LEVEL_COLOR.NORMAL,
		'1': MAL_LEVEL_COLOR.IMPORTANT,
		'2': MAL_LEVEL_COLOR.URGENCY
	},
	$levelCtrl = $("#mal_level"), $levelMenu = $levelCtrl.next();

	$levelCtrl.on("select", function(evt, data) {
		$("#mal_title").attr('class', MAL_LEVEL_MAP[evt.selected]);
		$("#mal_level_val").val(evt.selected);
	});

	new P.PseudoSelect($levelCtrl, $levelMenu, {
		template: '<span><%=text%></span> <i class="caret"></i>'
	});

	/*---- 验证是否有外部收件箱 */
	$("#email_form").on("submit", function() {

		if($.data(this, "submiting")) {
			return false;
		}

		if ($("#to_web_email").val() != "" && $("#webid").val() == "") {
			Ui.tip("@EM.EMPTY_FROMWEBID_TIP", 'warning');
			return false;
		}


		if($.formValidator.pageIsValid()) {
			if( $.trim(ue.getContent()) == "" ){
				Ui.tip("请填写邮件内容", "danger");
				return false;
			}
			
			$.data(this, "submiting", true);	
		}
	});

	// 同步外部邮箱选项
	$(Email).on("addWebMailBox", function(evt, data) {
		if(data.res.isSuccess) {
			var $webBoxSelect = $("#webid");
			$webBoxSelect.append('<option selected value="' + data.res.webId + '">' + data.params.web.address + '</option>')
			.find('option[value=""]').remove();
		}
	});

	/*---- 初始化关联主线 */
	//判断是否安装了主线模块
	if(Ibos.app.g("mods").thread) {
		$("#thread_select").ibosSelect({ width: "100%" });
	}
});
