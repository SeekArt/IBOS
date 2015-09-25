var Ntd = {
	op : {
        /**
         * 删除通知
         * @method remove
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
		remove : function(param){
			var url = Ibos.app.url("message/notify/delete");
			return $.get(url, param, $.noop);
		}
	},
    /**
     * 删除通知
     * @method remove
     * @param  {String} ids 传入删除通知的IDs
     */
	remove: function(ids) {
		var param = {id : ids};
		Ntd.op.remove(param).done(function(res){
			if(res.IsSuccess) {
				$.each(ids.split(","), function(n, i) {
					var $item = $("#timeline_"+i);
					$item.fadeOut(function() {
						Msg.removeTimelineItem($item);
					});
				});
				Ui.tip(Ibos.l("DELETE_SUCCESS"));
				window.location.reload();
			} else {
				Ui.tip(Ibos.l("DELETE_FAILED"), "danger");
			}
		});
	}
};

$(function(){
	// 批量删除模式
	var multipleMode = Msg.multipleMode($("#remind_list"));
	
	$("#start_multiple_btn").click(multipleMode.start);
	$("#stop_multiple_btn").click(multipleMode.stop);

	Ibos.evt.add({
		// 删除单个详情通知
		'removeDetailNotice': function(param){
			Ui.confirm(Ibos.l("MSG.NOTIFY_REMOVE_CONFIRM"), function(){
				Ntd.remove(param.id);
			});
		},
		// 批量删除详情通知
		'removeDetailNotices': function(){
			var ids = U.getCheckedValue("remind");
			if (ids) {
				Ui.confirm(Ibos.l("MSG.NOTIFY_REMOVE_CONFIRM"), function(){
					Ntd.remove(ids);
				});
			} else {
				Ui.tip(Ibos.l("SELECT_AT_LEAST_ONE_ITEM"), "warning");
			}
		}
	});
});