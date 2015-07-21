/**
 * 消息中心--评论--首页
 * Message/Comment/Index
 * @author 		inaki
 * @version 	$Id$
 */
$(function() {
	var $msgCommentList = $("#msg_comment_list");

	function removeComments(ids) {
		$.get(Ibos.app.url('message/comment/del'), {
			cid: ids
		}, function(data) {
			if (data.isSuccess) {
				$.each(ids.split(','), function(n, i) {
					$('#comment_' + i).fadeOut(function() {
						$('#comment_' + i).remove();
					});
				});
				Ui.tip("@DELETE_SUCCESS");
			} else {
				Ui.tip("@DELETE_FAILED", 'danger');
			}
		}, 'json');
	}

	Ibos.evt.add({
		// 删除
		"removeComment": function(param, elem) {
			removeComments(param.id)
		},
		// 批量删除
		"removeComments": function(param, elem) {
			var ids = U.getCheckedValue("comment");
			if (ids) {
				removeComments(ids);
			} else {
				Ui.tip("@SELECT_AT_LEAST_ONE_ITEM", "warning");
				return false;
			}
		}
	});

	// 批量删除模式
	var multipleMode = Msg.multipleMode($msgCommentList);

	$("#start_multiple_btn").click(multipleMode.start);
	$("#stop_multiple_btn").click(multipleMode.stop);
});