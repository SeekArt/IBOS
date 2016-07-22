/**
 * Officialdoc/officialdoc/add
 * @version $Id$
 */

$(function() {
	// 表单验证
	$.formValidator.initConfig({formID: "officialdoc_form"});
	$("#subject").formValidator()
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: Ibos.l("RULE.SUBJECT_CANNOT_BE_EMPTY")
	});

	//选人框
	$("#publishScope, #ccScope").userSelect({
		data: Ibos.data.get()
	});

	// 初始化编辑器
	// 操作栏扩展分页按钮
	UEDITOR_CONFIG.mode.simple[0].push('pagebreak');
	var ue = UE.getEditor('officialdoc_add_editor', {
		initialFrameWidth: 738,
		minFrameWidth: 738,
		toolbars: UEDITOR_CONFIG.mode.simple
	});

	ue.ready(function() {
		(new Ibos.EditorCache(ue, null, "officialdoc_add_editor")).restore();
	});

	//默认模板设置
	$("#rc_type").on("change", function() {
		Official.selectTemplate(ue, this.value);
	});

	$("#articleCategory").on("change", function() {
		var uid = Ibos.app.g("uid"),
				catid = this.value,
				url = Ibos.app.url("officialdoc/officialdoc/add", {op: "checkIsAllowPublish"});
		$.get(url, {catid: catid, uid: uid}, function(res) {
			var label = $("#article_status label"),
				check = label.eq(0),
				publish = label.eq(1);
			check.toggle(res.checkIsPublish);
			res.checkIsPublish ? check.find('input').prop('checked', true) : check.find('input').prop('checked', false);
			publish.toggle(res.isSuccess);
			res.isSuccess ? publish.find('input').prop('checked', true) : publish.find('input').prop('checked', false);
		}, 'json');
	});

	$("#officialdoc_form").submit(function() {
		if($.data(this, "submiting")) {
			return false;
		}
		if($.formValidator.pageIsValid()) {
			$.data(this, "submiting", true);
		}
	});

	//上传
	Ibos.upload.attach({
		post_params: { module: 'officialdoc' },
		custom_settings: {
			containerId: "file_target",
			inputId: "attachmentid"
		}
	});

	// 新手引导
	setTimeout(function() {
		Ibos.guide("doc_add", [
			{
				element: "#purview_intro",
				intro: Ibos.l("DOC.INTRO.DOC_ADD.PURVIEW")
			}, {
				element: "#rc_type",
				intro: Ibos.l("DOC.INTRO.DOC_ADD.SELECT_TPL")
			}
		]);
	}, 1000);


	Ibos.evt.add({
		// 预览
		"preview": function() {
			var content = ue.getContent();
			Official.openPostWindow(content);
		}
	});
});