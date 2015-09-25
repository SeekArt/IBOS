/**
 * 消息中心--评论--首页
 * Message/Comment/Index
 * @author 		inaki
 * @version 	$Id$
 */
var CommentIndex = {
	op : {
		/**
		 * 删除评论
		 * @method delComment
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		delComment : function(param){
			var url = Ibos.app.url('message/comment/del');
			return $.get(url, param, $.noop, "json");
		}
	},
	/**
	 * 删除评论
	 * @method removeComments
	 * @param  {String} ids 传入删除评论的IDs
	 */
	removeComments : function(ids){
		var param = { cid: ids };
		this.op.delComment(param).done(function(res){
			if (res.isSuccess) {
				$.each(ids.split(','), function(n, i) {
					$('#comment_' + i).fadeOut(function() {
						$('#comment_' + i).remove();
					});
				});
				Ui.tip("@DELETE_SUCCESS");
				window.location.reload();
			} else {
				Ui.tip("@DELETE_FAILED", 'danger');
			}
		});
	}
};
$(function() {
	var $msgCommentList = $("#msg_comment_list");

	// 批量删除模式
	var multipleMode = Msg.multipleMode($msgCommentList);

	$("#start_multiple_btn").click(multipleMode.start);
	$("#stop_multiple_btn").click(multipleMode.stop);

	Ibos.evt.add({
		// 删除
		"removeComment": function(param, elem) {
			CommentIndex.removeComments(param.id);
		},
		// 批量删除
		"removeComments": function(param, elem) {
			var ids = U.getCheckedValue("comment");
			if (ids) {
				CommentIndex.removeComments(ids);
			} else {
				Ui.tip("@SELECT_AT_LEAST_ONE_ITEM", "warning");
				return false;
			}
		}
	});
});