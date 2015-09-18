$(function(){
	$.formValidator.initConfig({formID: "position_add_form", errorFocus: true});

	// 角色名称
	$("#order_id").formValidator()
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: "排序序号不能为空"
	});

	$("#pos_name").formValidator()
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: "岗位名称不能为空"
	});	
})
