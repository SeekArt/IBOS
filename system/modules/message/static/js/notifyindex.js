
$(function(){
	var Nt = {
		// 移除提醒
		remove: function(ids){
			$.get(Ibos.app.url('message/notify/delete', {op: 'module'}), {id: ids}, function(res) {
				if (res.IsSuccess) {
					$.each(ids.split(','), function(n, i) {
						$('#remind_' + i).fadeOut(function() {
							$('#remind_' + i).remove();
						});
					});
					Ui.tip(U.lang('DELETE_SUCCESS'));
				} else {
					Ui.tip(U.lang('DELETE_FAILED'), 'danger');
				}
			}, 'json');
		},

		// 标记已读
		markRead: function(ids){
			$.get(Ibos.app.url('message/notify/setisread'), {'module': ids}, function(res) {
				if (res.IsSuccess) {
					$.each(ids.split(','), function(n, i) {
						$('#remind_' + i + ' span.bubble').remove();
					});
					Ui.tip(U.lang('OPERATION_SUCCESS'));
				} else {
					Ui.tip(U.lang('OPERATION_FAILED', 'danger'));
				}
			}, 'json');
		}
	};

	Ibos.evt.add({
		// 标记全部已读
		"markNoticeRead": function(){
			var ids = U.getCheckedValue("remind");
			if (ids) {
				Nt.markRead(ids);
				Ibosapp.dropnotify.getCount();
			} else {
				Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
			}
		},
		
		"markAllRead": function(param, elem){
			$.post(Ibos.app.url("message/notify/setallread"), function(res){
				if(res.IsSuccess){
					$('span.bubble').hide();
					$(elem).parent().hide();
					Ibosapp.dropnotify.getCount();
					Ui.tip(U.lang('OPERATION_SUCCESS'), 'success');
				} else {
					Ui.tip(U.lang('OPERATION_FAILED', 'danger'));
				}
			}, 'json');
		},

		// 批量删除
		"removeNotices": function() {
			var ids = U.getCheckedValue("remind");
			if (ids) {
				Ui.confirm(U.lang("MSG.NOTIFY_REMOVE_CONFIRM"), function(){
					Nt.remove(ids);
				})
			} else {
				Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
			}
		},

		"removeNotice": function(param) {
			Ui.confirm(U.lang("MSG.NOTIFY_REMOVE_CONFIRM"), function(){
				Nt.remove(param.id);
			})
		},
	});

	// 桌面通知设置
	$("#enable_desktop_notify").on("change", function(){
		var n =  window.webkitNotifications
		if(this.checked) {
			if(n && n.checkPermission() !== 0){
				n.requestPermission(function(){
					U.setCookie("allow_desktop_notify", "1", 7776000);
				});
			} else {
				U.setCookie("allow_desktop_notify", "1", 7776000);
			}
		} else {
			U.setCookie("allow_desktop_notify", "", 0);
		}
	})
	.prop("checked", U.getCookie("allow_desktop_notify") == "1")
	.label("refresh");
})