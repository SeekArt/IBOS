$(function() {
	function  syncData(url, deptCount, userCount, i) {
		$.get(url, function(res) {
			var tpl = res.tpl;
			if (tpl == "success") {
				var template = $.template("result_success_tpl", {data: res});
				$("#wrap_body").html(template);
				$("#sync_data_btn").button('reset').addClass("btn-primary");
				$("#wrap_footer").slideDown();
				$("#change_setting_btn").show();
			} else if (tpl == "half") {
				var template = $.template("result_half_tpl", {data: res});
				$("#wrap_body").html(template);
				$("#sync_data_btn").button('reset').addClass("btn-primary");
				$("#wrap_footer").slideDown();
				$("#change_setting_btn").show();
			} else if (tpl == "error") {
				var template = $.template("result_error_tpl", {data: res});
				$("#wrap_body").html(template);
				$("#sync_data_btn").button('reset').addClass("btn-primary");
				$("#wrap_footer").slideDown();
				$("#change_setting_btn").show();
			} else if (tpl == "sending") {
				var template = $.template("result_sending_tpl", {data: res});
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
				syncData(res.url, deptCount, userCount, i);
			}

		}, "json");
	}

	Ibos.evt.add({
		"syncData": function(elem, param) {
			var $this = $(this),
					status = $("#send_email").prop("checked") ? 1 : 0, //同步成功后，是否发送邮件
					param = {status: status, 'op': 'init'},
			url = Ibos.app.url('dashboard/wxsync/sync', param);

			$.get(url, function(res) {
				var deptCount = res.deptCount,
						userCount = res.userCount,
						i = 0;
				$("#wrap_footer").slideUp();
				$("#change_setting_btn").hide();
				$this.button('loading').removeClass("btn-primary");
				var param = {'op': 'dept'},
				url = Ibos.app.url('dashboard/wxsync/sync', param);
				syncData(url, deptCount, userCount, i);
			}, "json");


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
							var isPass = $.formValidator.pageIsValid();
							if (isPass) {
								var corpid = $("#CorpID").val(),
										corpsecret = $("#CorpSecre").val(),
										qrcode = $("#QRCode").val(),
										param = {corpid: corpid, corpsecret: corpsecret, qrcode: qrcode},
								url = Ibos.app.url('dashboard/wxbinding/update', {"updatesubmit": 1});
								$("#setting_from_wrap").waiting(null, 'normal', true);
								$.post(url, param, function(res) {
									if (res.isSuccess) {
										$("#setting_from_wrap").waiting(false);
										Ui.tip(res.msg);
										Ui.getDialog("d_setting").close();
									} else {
										Ui.tip(res.msg, "danger");
										$("#setting_from_wrap").waiting(false);
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