/**
 * 招聘管理-背景调查-列表
 * @author 		inaki
 * @version 	$Id$
 */

$(function() {
	$("#fullname").ibosSelect({ width: "50%" });
	//高级搜索
	$("#mn_search").search(null, function(){
		Ibos.openAdvancedSearchDialog({
			content: document.getElementById("mn_search_advance"),
			init: function(){
				$("#date_time").datepicker({ target: $("#date_time2") });
			},
			ok: function() {
				this.DOM.content.find("form").submit();
			}
		});
	});
});