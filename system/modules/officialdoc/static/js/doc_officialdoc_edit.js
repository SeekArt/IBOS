/**
 * Officialdoc/officialdoc/edit
 * @version $Id$
 */
var OfficialEdit = {
	op : {
		/**
		 * 获取文本类别
		 * @method articleCategory
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
		 */
		articleCategory : function(param){
			var url = Ibos.app.url("officialdoc/officialdoc/add");
			param = $.extend({}, param, {op: "checkIsAllowPublish"});
			return $.get(url, param, $.noop, 'json');
		}
	},
	/**
	 * 初始化编辑页面
	 * @method initEditPage
	 */
	initEditPage : function(){
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

		//上传
		Ibos.upload.attach({
			post_params: {module: 'officialdoc'},
			custom_settings: {
				containerId: "file_target",
				inputId: "attachmentid"
			}
		});
	}
};


$(function() {
	// 初始化编辑页面
	OfficialEdit.initEditPage();

	//修改内容后，点击提交时，选择修改理由
	$("#officialdoc_form").submit(function() {
		var status = $(this).attr("data-status");
		if(status == undefined){
			status = false;
		}
		if(!status){
			Ui.dialog({
				id: "alter_reason",
				title: false,
				content: document.getElementById("alter_reason"),
				cancel: true,
				ok: function(){
					$("#officialdoc_form").attr("data-status","true");
					$("input[name='reason']").val($("#reason").val());
					$("#officialdoc_form").submit();
				}
			});
		}
		return status;
	});

	$("#articleCategory").on("change", function() {
		var uid = Ibos.app.g("uid"),
				catid = this.value,
				param = {catid: catid, uid: uid};
		OfficialEdit.op.articleCategory(param).done(function(res) {
			var label = $("#article_status label");
			label.eq(0).toggle(res.checkIsPublish).end().eq(+res.checkIsPublish).trigger("click");
			label.eq(1).toggle(res.isSuccess).end().eq(+res.isSuccess).trigger("click");
		});
	});
	

	// 初始化编辑器
	// 操作栏扩展分页按钮
	UEDITOR_CONFIG.mode.simple[0].push('pagebreak');
	var ue = UE.getEditor('editor', {
		initialFrameWidth: 738,
		minFrameWidth: 738,
		toolbars: UEDITOR_CONFIG.mode.simple
	});

	//默认模板设置
	$("#rc_type").on("change", function() {
		Official.selectTemplate(ue, this.value);
	});

	Ibos.evt.add({
		// 预览
		"preview": function() {
			var content = ue.getContent();
			Official.openPostWindow(content);
		}
	});
});
