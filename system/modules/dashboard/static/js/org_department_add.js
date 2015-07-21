$(function(){
	$("#dep_manager, #superior_manager, #superior_branched_manager").userSelect({
		data: Ibos.data.get("user"),
		type: "user",
		maximumSelectionSize: "1"
	});

	$.formValidator.initConfig({ formID:"add_dept_form", errorFocus:true });

	$("#dept_name").formValidator()
	.regexValidator({
		regExp:"notempty",
		dataType:"enum",
		onError: Ibos.l("ORG.DEPARTMENT_NAME_CANNOT_BE_EMPTY")
	});
});