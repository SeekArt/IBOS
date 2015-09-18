var PmIndex = {
	op : {
        /**
         * 阅读私信
         * @method readPm
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
		readPm : function(param){
			var url = Ibos.app.url('message/pm/setisread');
			return $.get(url, param, $.noop, "json");
		},
        /**
         * 发送私信
         * @method sendPm
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
		sendPm : function(param){
			var url = Ibos.app.url('message/pm/post');
			return $.post(url, param, $.noop, "json");
		},
        /**
         * 删除私信
         * @method delPm
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
		delPm : function(param){
			var url = Ibos.app.url('message/pm/delete');
			return $.post(url, param, $.noop, "json");
		},
        /**
         * 标记所有私信
         * @method markAllPmRead
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
		markAllPmRead : function(param){
			var url = Ibos.app.url('message/pm/setallread');
			return $.get(url, param, $.noop, "json");
		}
	},
	/**
	 * 检查私信提交
	 * @method checkPmForm
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
	 * @method sendPm
	 */
	sendPm: function() {
		var param = $('#pm_form').serializeArray();
		PmIndex.on.sendPm(param).done(function(res) {
			if (res.IsSuccess) {
				document.getElementById('pm_form').reset();
				$("#to_uid").userSelect("removeValue");
			}
			Ui.tip(res.data, res.IsSuccess ? "" : "danger");
		});
	},

	/**
	 * 删除
	 * @method delPm
	 * @param {type} ids 传入删除私信的IDs
	 */
	delPm: function(ids) {
		Ui.confirm('你确认删除选中私信吗？该操作不可恢复', function(){
			var param = {id: ids};
			PmIndex.op.delPm(param).done(function(data) {
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
			});
		});
	},
    /**
     * 阅读私信
     * @method readPm
     * @param  {String} ids 传入阅读私信的IDs
     */
	readPm: function(ids) {
		var param = {id: ids};
		PmIndex.op.readPm(param).done(function(data) {
			if (data.IsSuccess) {
				$.each(ids.split(','), function(n, i) {
					$('#pm_' + i + ' span.bubble').remove();
				});
				Ui.tip(U.lang('OPERATION_SUCCESS'));
			} else {
				Ui.tip(U.lang('OPERATION_FAILED'), 'danger');
			}
		});
	}
};

$(function(){
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

	Ibos.evt.add({
		// 标识已读
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
			PmIndex.op.markAllPmRead(null).done(function(data) {
				if (data.IsSuccess) {
					$('#pm_list span.bubble').remove();
					$('.band-primary').remove();
					Ui.tip(U.lang("SAVE_SUCCESS"));
				} else {
					Ui.tip(U.lang("OPERATION_FAILED"), 'danger');
				}
			});
		},
		// 删除私信
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
		// 发送私信
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
	});
});