$(function() {
	var WxSync = {
		op : {
			//获取绑定开关信息
			getBindOpt : function(param){
				var url = Ibos.app.url('dashboard/wxbinding/toggleSwitch');
				return $.post(url, param, $.noop);
			},
			//获取同步开始时的数据
			getSyncData : function(param){
				var url = Ibos.app.url('dashboard/wxsync/sync');
				return $.get(url, param, $.noop, "json");
			}
		},
		syncData : function(url, deptCount, userCount, i){
			$.get(url, function(res) {
				var tpl = res.tpl;
				if (tpl == "success" || tpl == "half" || tpl == "error" || tpl == "sending") {
					var template = $.template("result_"+ tpl +"_tpl", {data: res});
					$("#wrap_body").html(template);
					$("#sync_data_btn").button('reset').addClass("btn-primary");
					$("#wrap_footer").slideDown();
					$("#change_setting_btn").show();
				} else {
					var percentage = (i / (deptCount + userCount + 4)) * 100,
							res = $.extend({}, res, {percentage: percentage});
					var template = $.template("result_syncing_tpl", {data: res});
					$("#wrap_body").html(template);
					i++;
					WxSync.syncData(res.url, deptCount, userCount, i);
				}

			}, "json");
		}
	};

	// 绑定的开关操作
	$("#bind_opt_checkbox").on("change", function() {
		var isbind = $(this).prop("checked"),
			status = isbind ? 1 : 0,
			param = {status: status};
			
		WxSync.op.getBindOpt(param).done(function(res){
			if (res.isSuccess) {
				Ui.tip(res.msg);
				$("#sync_opt_wrap").toggle(isbind);
			} else {
				Ui.tip(res.msg, 'danger');
			}
		})
	});

	Ibos.evt.add({
		"syncData": function(elem, param) {
			var $this = $(this),
					status = $("#send_email").prop("checked") ? 1 : 0, //同步成功后，是否发送邮件
					param = {'status': status, 'op': 'init'};

			WxSync.op.getSyncData(param).done(function(res){
				var deptCount = res.deptCount,
						userCount = res.userCount,
						i = 0;
				$("#wrap_footer").slideUp();
				$("#change_setting_btn").hide();
				$this.button('loading').removeClass("btn-primary");
				var param = {'op': 'dept'},
				url = Ibos.app.url('dashboard/wxsync/sync', param);
				WxSync.syncData(url, deptCount, userCount, i);
			});
		},
		"changeSeting": function(elem, param) {
			var url = Ibos.app.url('dashboard/wxbinding/update');
			$.get(url, function(res) {
				if (res.isSuccess) {
					var data = res.data;
					$("#CorpID").val(data.corpid);
					$("#CorpSecre").val(data.corpsecret);
					$("#QRCode").val(data.qrcode);

					var dialog = Ui.dialog({
						title: "企业号绑定设置",
						id: "d_setting",
						width: 520,
						content: document.getElementById("sync_setting_dialog"),
						ok: function() {
							var isPass = $.formValidator.pageIsValid(),
								settingFromWrap = $("#setting_from_wrap");
							if (isPass) {
								var corpid = $("#CorpID").val(),
										corpsecret = $("#CorpSecre").val(),
										qrcode = $("#QRCode").val(),
										param = {corpid: corpid, corpsecret: corpsecret, qrcode: qrcode},
								url = Ibos.app.url('dashboard/wxbinding/update', {"updatesubmit": 1});
								settingFromWrap.waiting(null, 'normal', true);
								$.post(url, param, function(res) {
									if (res.isSuccess) {
										settingFromWrap.waiting(false);
										Ui.tip(res.msg);
										Ui.getDialog("d_setting").close();
									} else {
										Ui.tip(res.msg, "danger");
										settingFromWrap.waiting(false);
									}
								});
							}
							return false;
						},
						cancel: function() {
							$.formValidator.resetTipState();
						},
						close: function() {
							$.formValidator.resetTipState();
						}
					});
				}
			});
		}
	});
});