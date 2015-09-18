var Nt = {
	op : {
        /**
         * 移除提醒
         * @method deleteNotify
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
		deleteNotify : function(param){
			var url = Ibos.app.url('message/notify/delete');
			return $.get(url, param, $.noop, "json");
		},
        /**
         * 标记阅读
         * @method markRead
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
		markRead : function(param){
			var url = Ibos.app.url('message/notify/setisread');
			return $.get(url, param, $.noop, "json");
		},
        /**
         * 标记所有阅读
         * @method markAllRead
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
		markAllRead : function(param){
			var url = Ibos.app.url('message/notify/setallread');
			return $.get(url, param, $.noop, "json");
		}
	},
	/**
	 * 移除提醒
	 * @method remove
	 * @param  {String} ids 传入要删除的IDs
	 */
	remove: function(ids){
		var param = {op: 'module', id: ids};
		Nt.op.deleteNotify(param).done(function(res) {
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
		});
	},
	/**
	 * 标记已读
	 * @method markRead
	 * @param  {String} ids 传入要删除的IDs
	 */
	markRead: function(ids){
		var param = {'module': ids};
		Nt.op.markRead(param).done(function(res) {
			if (res.IsSuccess) {
				$.each(ids.split(','), function(n, i) {
					$('#remind_' + i + ' span.bubble').remove();
				});
				Ui.tip(U.lang('OPERATION_SUCCESS'));
			} else {
				Ui.tip(U.lang('OPERATION_FAILED', 'danger'));
			}
		});
	}
};
$(function(){
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
			Nt.op.markRead(null).done(function(res){
				if(res.IsSuccess){
					$('span.bubble').hide();
					$(elem).parent().hide();
					Ibosapp.dropnotify.getCount();
					Ui.tip(U.lang('OPERATION_SUCCESS'), 'success');
				} else {
					Ui.tip(U.lang('OPERATION_FAILED', 'danger'));
				}
			});
		},

		// 批量删除
		"removeNotices": function() {
			var ids = U.getCheckedValue("remind");
			if (ids) {
				Ui.confirm(U.lang("MSG.NOTIFY_REMOVE_CONFIRM"), function(){
					Nt.remove(ids);
				});
			} else {
				Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
			}
		},

		"removeNotice": function(param) {
			Ui.confirm(U.lang("MSG.NOTIFY_REMOVE_CONFIRM"), function(){
				Nt.remove(param.id);
			});
		},
	});
});