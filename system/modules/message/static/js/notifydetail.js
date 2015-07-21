
$(function(){
	var Ntd = {
		remove: function(ids) {
			$.get(Ibos.app.url("message/notify/delete"), {id: ids}, function(res){
				if(res.IsSuccess) {
					$.each(ids.split(","), function(n, i) {
						var $item = $("#timeline_"+i);
						$item.fadeOut(function() {
							Msg.removeTimelineItem($item)
						});
					});
					Ui.tip(U.lang("DELETE_SUCCESS"))
				} else {
					Ui.tip(U.lang("DELETE_FAILED"), "danger")
				}
			})
		}
	}

	Ibos.evt.add({
		'removeDetailNotice': function(param){
			Ui.confirm(U.lang("MSG.NOTIFY_REMOVE_CONFIRM"), function(){
				Ntd.remove(param.id)
			})
		},

		'removeDetailNotices': function(){
			var ids = U.getCheckedValue("remind");
			if (ids) {
				Ui.confirm(U.lang("MSG.NOTIFY_REMOVE_CONFIRM"), function(){
					Ntd.remove(ids)
				})
			} else {
				Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), "warning");
			}
		}
	});

	// 批量删除模式
	var multipleMode = Msg.multipleMode($("#remind_list"));
	
	$("#start_multiple_btn").click(multipleMode.start);
	$("#stop_multiple_btn").click(multipleMode.stop);
});