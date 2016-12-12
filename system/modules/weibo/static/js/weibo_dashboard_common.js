var WbDash = {
	/**
	 * 删除一行
	 * @method removeItem
	 * @param  {Object} $item 传入Jquery节点对象
	 */
	removeItem: function($item) {
		$item.fadeOut(function() {
			$(this).remove();
		});
	},
	/**
	 * 删除多行
	 * @method removeItemsById
	 * @param  {String} ids 传入要删除的ids
	 */
	removeItemsById: function(ids) {
		var that = this, idArr = ("" + ids).split(",");
		$.each(idArr, function(index, id) {
			that.removeItem($("[data-row='" + id + "']"));
		});
	},
	/**
	 * 删除的数据交互
	 * @method removeAccess
	 * @param  {String}   ids      	 传入删除的ids
	 * @param  {String}   url      	 传入访问地址
	 * @param  {Object}   param    	 传入JSON格式数据
	 * @param  {Ang}      msg      	 为真出现confirm删除确定,为假不显示
	 * @param  {Function} [callback] 回调函数
	 */
	removeAccess: function(ids, url, param, msg, callback) {
		var that = this, 
			send = function(u, p, c) {
				p.formhash = Ibos.app.g('formHash');
				$.post(u, p, function(res) {
					if (res.isSuccess) {
						that.removeItemsById(param.ids);
						Ui.tip(Ibos.l("OPERATION_SUCCESS"));
						c && c(res);
					}else{
						Ui.tip(Ibos.l("OPERATION_FAILED"));
					}
				}, 'json');
			};
		// 需要至少选中一项
		if (ids) {
			param.ids = ids;
			// 当传入了msg参数时，会出现confirm删除确认
			if (msg) {
				Ui.confirm(msg, function() {
					send(url, param, callback);
				});
			} else {
				send(url, param, callback);
			}
		} else {
			Ui.tip(Ibos.l("SELECT_AT_LEAST_ONE_ITEM"), "warning");
		}
	}
};
$(function() {
	// 传入地址
	var dashboardUrl = {
		wb : Ibos.app.url('weibo/dashboard/manage'),
		cm : Ibos.app.url('weibo/dashboard/comment')
	};

	Ibos.evt.add({
		// 删除选中微博
		"removeWbs": function(param, elem) {
			var wbIds = U.getCheckedValue("weibo");
			var tips = param.op == 'delFeed' ? Ibos.l("WB.REMOVE_SELECT_FEEDS_CONFIRM") : Ibos.l("WB.DELETE_SELECT_FEEDS_CONFIRM");
			WbDash.removeAccess(wbIds, dashboardUrl.wb, param, tips);
		},
		// 删除一条微博
		"removeWb": function(param, elem) {
			WbDash.removeAccess(param.id, dashboardUrl.wb, param, Ibos.l("WB.REMOVE_SELECT_FEEDS_CONFIRM"));
		},
		// 恢复一条微博
		"recoverWb": function(param, elem) {
			WbDash.removeAccess(param.id, dashboardUrl.wb, param, Ibos.l("WB.RECOVER_SELECT_FEED_CONFIRM"));
		},
		// 删除选中评论
		"removeCms": function(param, elem) {
			var cmIds = U.getCheckedValue("comment");
			var tips = param.op == 'delComment' ? Ibos.l("WB.REMOVE_SELECT_COMMENTS_CONFIRM") : Ibos.l("WB.DELETE_SELECT_COMMENTS_CONFIRM");
			WbDash.removeAccess(cmIds, dashboardUrl.cm, param, tips);
		},
		// 删除一条评论
		"removeCm": function(param, elem) {
			WbDash.removeAccess(param.id, dashboardUrl.cm, param, Ibos.l("WB.REMOVE_SELECT_COMMENTS_CONFIRM"));
		},
		// 恢复一条评论
		"recoverCm": function(param, elem) {
			WbDash.removeAccess(param.id, dashboardUrl.cm, param, Ibos.l("WB.RECOVER_SELECT_COMMENTS_CONFIRM"));
		},
		// 删除选中话题
		"removeTps": function(param, elem) {
			var tpIds = U.getCheckedValue("topic");
			WbDash.removeAccess(tpIds, "#topic", param, Ibos.l("WB.REMOVE_SELECT_TOPICS_CONFIRM"));
		},
		// 删除一条话题
		"removeTp": function(param, elem) {
			WbDash.removeAccess(param.id, "#topic", param);
		}
	});
});


