$(function(){
	var PmIndex = {
		/**
		 * 检查私信提交
		 * @returns {Boolean}
		 */
		checkPmForm: function() {
			if ($.trim($('#to_uid').val()) === '') {
				Ui.tip(U.lang('RECEIVER_CANNOT_BE_EMPTY'), 'danger');
				return false;
			}
			if ($.trim($('#to_message').val()) === '') {
				Ui.tip(U.lang('CONTENT_CANNOT_BE_EMPTY'), 'danger');
				return false;
			}
			return true;
		},

		/**
		 * 发送私信
		 * @returns {json}	 
		 */
		sendPm: function() {
			$.post(Ibos.app.url('message/pm/post'), $('#pm_form').serializeArray(), function(res) {
				if (res.IsSuccess) {
					document.getElementById('pm_form').reset();
					$("#to_uid").userSelect("removeValue");
					Ui.tip(res.data);
				} else {
					Ui.tip(res.data, 'danger');
				}
			}, 'json');
		},

		/**
		 * 删除
		 * @param {type} ids
		 * @returns {undefined}
		 */
		delPm: function(ids) {
			Ui.confirm('你确认删除选中私信吗？该操作不可恢复', function(){
				$.get(Ibos.app.url('message/pm/delete'), {id: ids}, function(data) {
					if (data.IsSuccess) {
						$.each(ids.split(','), function(n, i) {
							$('#pm_' + i).fadeOut(function() {
								$(this).remove();
							});
						});
						Ui.tip(U.lang('DELETE_SUCCESS'));
					} else {
						Ui.tip(U.lang('DELETE_FAILED'), 'danger');
					}
				}, 'json');
			})
		},

		readPm: function(ids) {
			$.get(Ibos.app.url('message/pm/setisread'), {id: ids}, function(data) {
				if (data.IsSuccess) {
					$.each(ids.split(','), function(n, i) {
						$('#pm_' + i + ' span.bubble').remove();
					});
					Ui.tip(U.lang('OPERATION_SUCCESS'));
				} else {
					Ui.tip(U.lang('OPERATION_FAILED'), 'danger');
				}
			}, 'json');
		}
	};

	Ibos.evt.add({
		'markPmsRead': function(){
			// 标识已读
			var ids = U.getCheckedValue("pm");
			if (ids) {
				PmIndex.readPm(ids);
			} else {
				Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
			}
		}, 
		// 标识所有已读
		"markAllPmRead": function(){
			$.get(Ibos.app.url('message/pm/setallread'), function(data) {
				if (data.IsSuccess) {
					$('#pm_list span.bubble').remove();
					$('.band-primary').remove();
					Ui.tip(U.lang("SAVE_SUCCESS"));
				} else {
					Ui.tip(U.lang("OPERATION_FAILED"), 'danger');
				}
			}, 'json');
		},
		"removePm": function(param, elem, evt){
			PmIndex.delPm(param.id);
			evt.stopPropagation();
		},

		// 批量删除
		"removePms": function(){
			// 标识已读
			var ids = U.getCheckedValue("pm");
			if (ids) {
				PmIndex.delPm(ids);
			} else {
				Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
			}
		},

		"sendPm": function(){
			Ui.dialog({
				title: U.lang("SEND_PM"),
				content: document.getElementById('pm_message'),
				id: 'pmbox',
				padding: 0,
				lock: true,
				okVal: U.lang("SEND"),
				ok: function(){
					if (PmIndex.checkPmForm()) {
						PmIndex.sendPm();
						return true;
					}
					return false;
				}
			});
		}
	})

	// 跳转私信详情
	$(document).on("click", ".main-list-item", function(evt) {
		location.href = Ibos.app.url( 'message/pm/detail')+ '&' + $.attr(this, "data-url");
	})
	.on("click", ".main-list-item .checkbox, .main-list-item a", function(evt){
		evt.stopPropagation();
	});

	// 用户数据，过滤掉自己
	var userData = Ibos.data.get('user', function(userData) {
		return userData.id !== Ibos.app.g('dataUid');
	});
	$("#to_uid").userSelect({
		box: $("#to_uid_box"),
		type: "user",
		maximumSelectionSize: "1",
		data: userData
	});
	// Ctrl + enter 发送，
	$("#to_message").on("keydown", function(evt) {
		if (evt.ctrlKey && evt.which === 13) {
			if (PmIndex.checkPmForm()) {
				PmIndex.sendPm();
				$(this).blur();
			}
		}
	});
});