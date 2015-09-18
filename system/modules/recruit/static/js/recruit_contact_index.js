/**
 * 招聘管理-联系记录-列表
 * @author 		inaki
 * @version 	$Id$
 */

$(function() {
	//高级搜索
	$("#mn_search").search(null, function() {
		Ibos.openAdvancedSearchDialog({
			content: document.getElementById("mn_search_advance"),
			init: function(){
				$("#date_start").datepicker({
					target: $("#date_end")
				});
			},
			ok: function() {
				this.DOM.content.find("form").submit();
			}
		});
	});

	// 联系记录姓名选择框
	$("#fullname").ibosSelect({
		width: "100%"
	});

	// 联系人选择框
	$("#user_contact, #user_contact_search").userSelect({
		type: "user",
		maximumSelectionSize: "1",
		data: Ibos.data.get("user")
	});
});