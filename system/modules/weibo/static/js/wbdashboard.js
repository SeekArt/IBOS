

var WbDash = {
	removeItem: function($item) {
		$item.fadeOut(function() {
			$(this).remove();
		});
	},
	removeItemsById: function(ids) {
		var that = this, idArr = ("" + ids).split(",");
		$.each(idArr, function(index, id) {
			that.removeItem($("[data-row='" + id + "']"));
		});
	},
	removeAccess: function(ids, url, param, msg, callback) {
		var that = this, send = function(u, p, c) {
			p.formhash = Ibos.app.g('formHash');
			$.post(u, p, function(res) {
				if (res.isSuccess) {
					that.removeItemsById(param.ids);
					Ui.tip(Ibos.l("DELETE_SUCCESS"));
					c && c(res);
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
	Ibos.evt.add({
		// 删除选中微博
		"removeWbs": function(param, elem) {
			var wbIds = U.getCheckedValue("weibo");
			var tips = param.op == 'delFeed' ? Ibos.l("WB.REMOVE_SELECT_FEEDS_CONFIRM") : Ibos.l("WB.DELETE_SELECT_FEEDS_CONFIRM");
			WbDash.removeAccess(wbIds, Ibos.app.url('weibo/dashboard/manage'), param, tips);
		},
		// 删除一条微博
		"removeWb": function(param, elem) {
			WbDash.removeAccess(param.id, Ibos.app.url('weibo/dashboard/manage'), param, Ibos.l("WB.REMOVE_SELECT_FEEDS_CONFIRM"));
		},
		// 恢复一条微博
		"recoverWb": function(param, elem) {
			WbDash.removeAccess(param.id, Ibos.app.url('weibo/dashboard/manage'), param, Ibos.l("WB.RECOVER_SELECT_FEED_CONFIRM"));
		},
		// 删除选中评论
		"removeCms": function(param, elem) {
			var cmIds = U.getCheckedValue("comment");
			var tips = param.op == 'delComment' ? Ibos.l("WB.REMOVE_SELECT_COMMENTS_CONFIRM") : Ibos.l("WB.DELETE_SELECT_COMMENTS_CONFIRM");
			WbDash.removeAccess(cmIds, Ibos.app.url('weibo/dashboard/comment'), param, tips);
		},
		// 删除一条评论
		"removeCm": function(param, elem) {
			WbDash.removeAccess(param.id, Ibos.app.url('weibo/dashboard/comment'), param, Ibos.l("WB.REMOVE_SELECT_COMMENTS_CONFIRM"));
		},
		// 恢复一条评论
		"recoverCm": function(param, elem) {
			WbDash.removeAccess(param.id, Ibos.app.url('weibo/dashboard/comment'), param, Ibos.l("WB.RECOVER_SELECT_COMMENTS_CONFIRM"));
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


