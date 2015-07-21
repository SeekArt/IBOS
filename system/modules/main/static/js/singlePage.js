var SinglePage = {
	op: {
		// 获取模板内容
		getTemplate: function(tplName, callback) {
			if (tplName) {
				$.post(Ibos.app.url('dashboard/page/getcontent'), {fileUrl: tplName}, callback, "json");
			}
		}
	},
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
	// 选择公文模板 
	selectTemplate: function(tpl, pageid) {
		if (tpl) {
			// 取消模板时，询问是否清空编辑器
			if (tpl == "") {
				Ui.confirm("是否清空编辑器内容?", function() {
					ue.ready(function() {
						ue.setContent("");
					});
				});
			} else {
				var setTemplate = function(){
					window.location.href = Ibos.app.url( "main/page/edit", {tpl: tpl, pageid: pageid, op: 'switchTpl'} );
				}
				if (ue.getContent() !== "") {
					Ui.confirm("切换模版将清空内容，确定切换吗?", setTemplate);
				} else {
					setTemplate();
				}
			}
		}
	},
	closeWindow: function(){
		setInterval(function(){
			window.opener = null; 
			window.open('','_self',''); 
			window.close(); 
		}, 1000);
	}
}


$(function() {
	Ibos.evt.add({
		// 预览
		"preview": function() {
			var tpl = $("#moduel-type").children("li.active").find("a").attr("data-value"),
				url = Ibos.app.url("main/page/preview", {tpl: tpl}),
				content = ue.getContent();
			SinglePage.openPostWindow(content, url);
		},
		"close": function(){
			SinglePage.closeWindow();
		},
		"save": function(){
			var content = UE.getEditor("editor").getContent(),
				saveUrl = Ibos.app.url("main/page/save", {saveSubmit: 1}),
				tpl = $("#moduel-type").children("li.active").find("a").attr("data-value"),
				name = $("#navname").val(),
				navid = $("#navid").val(),
				pageid = $("#pageid").val();
			$.post(saveUrl, { tpl: tpl, content: content, navid: navid, pageid: pageid }, function(res){
				if(res.isSuccess){
					Ui.tip("@SAVE_SUCEESS");
					window.location.href = Ibos.app.url( "main/page/edit", {  pageid: res.pageid, name: name } );
				} else {
					Ui.tip(res.msg, 'warning');
				}
			}, 'json');
		}
	});

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

});