var SinglePage = {
	op: {
		/**
		 * 获取模板内容
		 * [getTemplate description]
		 * @param  {String}   tplName  传入模板名字
		 * @return {Object}            返回deffered对象
		 */
		getTemplate: function(tplName) {
			if (tplName) {
				var url = Ibos.app.url('dashboard/page/getcontent'),
					param = {fileUrl: tplName};
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 保存
		 * @method save
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		save : function(param){
			var url = Ibos.app.url("main/page/save");
			param = $.extend({}, param, {saveSubmit : 1});
			return $.post(url, param, $.noop, 'json');
		}
	},
	/**
	 * 打开发送窗口
	 * @method openPostWindow
	 * @param  {String} data 传入数据
	 * @param  {String} url  传入发送的地址
	 */
	openPostWindow: function(data, url) {
		var tempForm = document.createElement("form");

		tempForm.id = "tempForm1";
		tempForm.method = "post";
		tempForm.action = url;
		tempForm.target = "_blank";

		var hideInput = document.createElement("input");

		hideInput.type = "hidden";
		hideInput.name = "content";
		hideInput.value = data;
		tempForm.appendChild(hideInput);

		document.body.appendChild(tempForm);

		tempForm.submit();
		document.body.removeChild(tempForm);
	},
	/**
	 * 选择通知模板
	 * @method selectTemplate
	 * @param  {String} tpl    传入模板
	 * @param  {String} pageid 页面ID
	 */
	selectTemplate: function(tpl, pageid) {
		if (tpl) {
			// 取消模板时，询问是否清空编辑器
			if (tpl == "") {
				Ui.confirm(Ibos.l("MAIN.CLEAR_EDIT_CONTENT"), function() {
					ue.ready(function() {
						ue.setContent("");
					});
				});
			} else {
				var setTemplate = function(){
					window.location.href = Ibos.app.url( "main/page/edit", {tpl: tpl, pageid: pageid, op: 'switchTpl'} );
				};
				if (ue.getContent() !== "") {
					Ui.confirm(Ibos.l("MAIN.TOGGLE_TPL_CONTENT_EMPTY"), setTemplate);
				} else {
					setTemplate();
				}
			}
		}
	},
	/**
	 * 关闭弹窗
	 * @method closeWindow
	 */
	closeWindow: function(){
		setInterval(function(){
			window.opener = null; 
			window.open('','_self',''); 
			window.close(); 
		}, 1000);
	}
};


$(function() {
	// 编辑器初始化
	// UEDITOR_CONFIG.mode.simple[0].push('source');
	ue = UE.getEditor('editor', {
		initialFrameWidth: 'auto',
		initialFrameHeight: 600,
		// autoHeightEnabled: true,
		// toolbars: UEDITOR_CONFIG.mode.simple,
		iframeCssUrl: Ibos.app.g("assetUrl") + "/css/template.css",
		autoFloatEnabled: false
	});

	//默认模板设置
	 $("#moduel-type li").on("click", function() {
	 	var tpl = $(this).children().attr("data-type"),
	 		name = $("#navname").val(),
			pageid = $("#pageid").val();
	 	SinglePage.selectTemplate(tpl, pageid);
	 });


	Ibos.evt.add({
		// 预览
		"preview": function() {
			var tpl = $("#moduel-type").children("li.active").find("a").attr("data-value"),
				url = Ibos.app.url("main/page/preview", {tpl: tpl}),
				content = ue.getContent();
			SinglePage.openPostWindow(content, url);
		},
		// 关闭
		"close": function(){
			SinglePage.closeWindow();
		},
		// 保存
		"save": function(){
			var content = UE.getEditor("editor").getContent(),
				tpl = $("#moduel-type").children("li.active").find("a").attr("data-value"),
				name = $("#navname").val(),
				navid = $("#navid").val(),
				pageid = $("#pageid").val(),
				param = { tpl: tpl, content: content, navid: navid, pageid: pageid };

			SinglePage.op.save(param).done(function(res){
				if(res.isSuccess){
					Ui.tip("@SAVE_SUCEESS");
					window.location.href = Ibos.app.url( "main/page/edit", {  pageid: res.pageid, name: name } );
				} else {
					Ui.tip(res.msg, 'warning');
				}
			});
		}
	});
});