$(function(){
	$.formValidator.initConfig({
		formID: "bind_info_form"
	});

	$("#corpid").formValidator()
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: "CorpID不能为空！"
	});

	$("#corpsecre").formValidator()
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: "CorpSecre不能为空！"
	});

	$("#address").formValidator()
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: "二维码不能为空！"
	});

	//启用应用操作
	$("#weixin_opt_table").on("change", "input[type=checkbox]", function(){
		var $this = $(this),
			$img = $this.closest("tr").find("img"),
			ischeck = $this.prop("checked"),
			status = ischeck ? 1 : 0,　
			module = $this.val(),
			param = {module: module, status: status},
			url = "";

		$.post(url, param, function(res){
			if(res.isSuccess){
				Ui.tip(Ibos.l("OPERATION_SUCCESS"));
				$img.attr("src", res.url);
			}else{
				Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
			}
		});
	});

	Ibos.evt.add({
		"bind": function(elem, param){
			var dialog = Ui.dialog({
				title: "企业号绑定设置",
				padding: 0,
				width: 600,
				content: document.getElementById("bind_info_dialog"),
				ok: function(){
					var isPass = $.formValidator.pageIsValid();
					if(isPass){
						$("#bind_info_form").submit();
					} 
					return false;
				},
				cancel: function(){
					$.formValidator.resetTipState();
				},
				close: function(){
					$.formValidator.resetTipState();
				}
			});
		},
		"delapp": function(elem, param){//删除对应应用
			var $this = $(this),
				$tr = $this.closest("tr"),
				type = $this.data("type"),
				tip = "删除该应用后将无法再发送消息也不能在微信中使用该应用" + 
					  "同时该应用的相关数据会被删除。同时需要在" + 
					  "<a href='' class='xwb xcbu'>微信企业平台</a> 删除," + 
					  "<a href='' class='xwb xcbu'>如图所示</a>";
			Ui.confirm(tip, function(){
				var param = {type: type},
					url = "";
				$.post(url, param, function(res){
					if(res.isSuccess){
						Ui.tip(Ibos.l("OPERATION_SUCCESS"));
						$tr.remove();
						var trsLength = $this.closest("table").find("tbody tr").length;
						if(trsLength == 0){
							$("#no_data_tr").show();
						}  
					}else{
						Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
					}
				});
			});
		},
		"openapp": function(elem, param){//开启对应应用
			var $this = $(this),
				$tr = $this.closest("tr"),
				module = $this.data("mudule"),
				param = {module: module},
				url = "";
			$.post(url, param, function(res){
				if(true){
					Ui.tip(Ibos.l("OPERATION_SUCCESS"));
					$tr.remove();
					var trsLength = $this.closest("table").find("tbody tr").length;
					if(trsLength == 0){
						$("#no_data_tr").show();
					} 
				}else{
					Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
				}
			});
		},
		"syncData": function(elem, param){
			var $this = $(this),
				status = $("#send_email").prop("checked") ? 1 : 0, //同步成功后，是否发送邮件
				param = {status: status},
				url = "";
			$this.button('loading').removeClass("btn-primary");
			$.post(url, param, function(res){
				if(res.isSuccess){
					// 数据形式
					// var data = {
					// 		status: 1, //同步状态，成功为1 
					// 		success: 20, //同步成功人数
					// 		failue: 20,  //同步失败人数
					// 		url: "ww.baidu.com" //下载错误信息地址 
					// };
					var	dataHtml = $.template("result_info_tpl", {data: res.data});
					$("#wrap_body").html(dataHtml);
					$this.button('reset').addClass("btn-primary");
					Ui.tip("成功同步数据！");
				}else{
					$this.button('reset').addClass("btn-primary");
					Ui.tip("同步操作失败！");
				}
			});
		},
		"changesSeting": function(elem, param){
			var dialog = Ui.dialog({
				title: "企业号绑定设置",
				width: 450,
				content: document.getElementById("sync_setting_dialog"),
				ok: function(){
					var isPass = $.formValidator.pageIsValid();
					if(isPass){
						$("#setting_from").submit();
					} 
					return false; 
				},
				cancel: function(){
					$.formValidator.resetTipState();
				},
				close: function(){
					$.formValidator.resetTipState();
				}
			});
		}
	});
});