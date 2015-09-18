$(function() {
	// 启用、禁用快捷导航
	var table = $("#quicknav_table");
	table.find(".enabled-status").on("change", function() {
		var enabled = this.checked, url = Ibos.app.url('dashboard/quicknav/edit', {'op': 'changeenabled'}), status = {type: '', id: $(this).attr('data-id')};
		if (enabled) {
			status.type = 'enabled';
		} else {
			status.type = 'disabled';
		}
		$.post(url, status, function(res) {
			if (res.isSuccess) {
				Ui.tip(res.msg, 'success');
			} else {
				Ui.tip(res.msg, 'warning');
			}
		}, 'json');
	});

	// 是否开启新窗口打开
	table.find(".newwindow-status").on("change", function() {
		var enabled = this.checked, url = Ibos.app.url('dashboard/quicknav/edit', {'op': 'changeopenway'}), status = {type: '', id: $(this).attr('data-id')};
		if (enabled) {
			status.type = 'enabled';
		} else {
			status.type = 'disabled';
		}
		$.post(url, status, function(res) {
			if (res.isSuccess) {
				Ui.tip(res.msg, 'success');
			} else {
				Ui.tip(res.msg, 'warning');
			}
		}, 'json');
	});

	// 删除快捷导航
	Ibos.evt.add({
		"removeQuicknav": function(param, elem) {
			Ui.confirm(U.lang("DB.REMOVE_QUICKNAV_CONFIRM"), function() {
				$.post(Ibos.app.url('dashboard/quicknav/del'), param, function(res) {
					if (res.isSuccess) {
						$(elem).closest("tr").remove();
						Ui.tip("@OPERATION_SUCCESS")
					}
				}, "json");
			})
		}
	})
});