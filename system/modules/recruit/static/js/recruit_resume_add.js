/**
 * 招聘管理-简历-新建/编辑简历
 * @author 		inaki
 * @version 	$Id$
 */

$(function() {
	//日期选择器
	$("#date_time").datepicker();

	// 根据后台配置的字段规则初始化表单验证
	var initCustomValidator = function(formId, fieldRule, fieldRuleRegex){
		$.formValidator.initConfig({ formID: formId, errorFocus: true});

		for(var field in fieldRule) {
			// 作为判断是否用户自定义规则的字段
			var isCustomRegex = true;
			// 当前字段的验证规则
			var currentFieldRule = fieldRule[field];
			// 当字段默认显示且要求验证时，才视为真正需要验证的字段
			var requirement = currentFieldRule['visi'] == '1' && currentFieldRule['fieldrule'] != 'notrequirement';

			if(requirement) {
				for (var index in fieldRuleRegex) {
					//判断是系统的验证规则还是自定义的正则表达式
					if (fieldRuleRegex[index]['type'] == currentFieldRule['fieldrule']) {
						$('#' + field).formValidator()
						.regexValidator({
							regExp: currentFieldRule['fieldrule'],
							dataType: "enum",
							onError: fieldRuleRegex[index]['desc']
						});
						isCustomRegex = false; //把初始化规则变为非自定义(即系统验证规则)
						break; //跳出循环
					}
				}
				//用户自定义的正则表达式
				if (isCustomRegex === true) {
					$('#' + field).formValidator()
					.regexValidator({
						regExp: currentFieldRule['fieldrule'],
						dataType: "string", //如果是"string",则regExp必须是正则表达式
						onError: Ibos.l('REC.PLEASE_ENTER_A_CORRECT_FORMAT')
					});
				}
			}
		}
	};

	initCustomValidator("resume_form", $.parseJSON(Ibos.app.g("resumeFieldRule")), $.parseJSON(Ibos.app.g("resumeFieldRuleRegex")));

	// 头像上传
	Ibos.upload.image({
		post_params: {
			module: 'recruit',
			type: 'recruit'
		},
		button_placeholder_id: "avatar_upload_btn",
		button_width: "116",
		button_image_url: "",

		custom_settings: {
			inputId: "avatarid",
			progressId: "rsm_avt_wrap",
			//成功上传图片后执行的方法
			success: function(file, data) {
				$('#pic_frame').attr('src', data.url);
			}
		}
	});


	//附件上传
	Ibos.upload.attach({
		post_params: {
			module: 'recruit'
		},
		custom_settings: {
			containerId: "file_target",
			inputId: "attachmentid"
		}
	});

	// 岗位选择         
	$("#recruittargetposition").userSelect({
		type: "position",
		maximumSelectionSize: "1",
		data: Ibos.data.get("position")
	});
});