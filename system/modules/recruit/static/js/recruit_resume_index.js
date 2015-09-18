/**
 * 招聘-简历-列表
 * @author 		inaki
 * @version 	$Id$
 */
$(function() {
	// 导入简历
	$('#imp_rsm').on("click", function() {
		Ui.dialog({
			title: Ibos.l("REC.IMPORT_RESUME"),
			content: Dom.byId("d_recruit_import"),
			ok: function() {
				$('#recruit_import_form').submit();
			},
			okVal: Ibos.l("IMPORT"),
			cancel: true
		});
	});

	// 粘贴栏获得焦点时改变方法二（粘贴简历）选中状态
	$('#import_content').on('focus', function() {
		$("[name='importType'][value='2']").label("check");
	});

	//高级搜索
	$("#mn_search").search(null, function() {
		Ibos.openAdvancedSearchDialog({
			content: document.getElementById("mn_search_advance"),
			ok: function() {
				$("#mn_search_advance_form").submit();
			}
		});
	});

	// 岗位选择 		
	$("#user_position").userSelect({
		type: "position",
		maximumSelectionSize: "1",
		data: Ibos.data.get("position")
	});
});