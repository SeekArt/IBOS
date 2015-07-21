/**
 * 岗位角色--编辑
 * Organization
 * @author 		inaki
 * @version 	$Id$
 */

$(function(){
	// 表单验证
	$.formValidator.initConfig({ formID:"position_edit_form", errorFocus:true });

	// 角色名称
	$("#role_name").formValidator()
	.regexValidator({
		regExp:"notempty",
		dataType:"enum",
		onError: Ibos.l("ORG.ROLE_NAME_CANNOT_BE_EMPTY")
	});
	
	// 权限选择处理
	$("#limit_setup").bindEvents({
		// 选中功能
		"change [data-node='funcCheckbox']": function(){
			$(this).closest("label").toggleClass("active", this.checked);
		},
		// 选中模块 
		"change [data-node='modCheckbox']": function(evt){
			var id = $.attr(this, "data-id");
			Organization.auth.selectMod(id, $.prop(this, "checked"));
		},
		// 选中分类
		"click [data-node='cateCheckbox']": function(evt){
			var id = $.attr(this, "data-id"),
				checked = $.attr(this, "data-checked") === "1";
			Organization.auth.selectCate(id,  !checked);
			$.attr(this, "data-checked", checked ? "0" : "1");
		}
	});

	// 岗位成员列表
	Organization.memberList.init(Ibos.app.g("members"));
});