/**
 * 招聘管理-面试记录-列表
 * @author 		inaki
 * @version 	$Id$
 */

$(function() {
	$("#fullname").ibosSelect({
		width: "100%"
	});

	//高级搜索
	$("#mn_search").search(null, function() {
		Ibos.openAdvancedSearchDialog({
			content: document.getElementById("mn_search_advance"),
			ok: function() {
				this.DOM.content.find("form").submit();
			}
		});
	});

	// 时间选择器
	$('#interview_time').datepicker();

	// 联系人选择框
	$("#user_interview, #user_interview_search").userSelect({
		type: "user",
		maximumSelectionSize: "1",
		data: Ibos.data.get("user")
	});

});