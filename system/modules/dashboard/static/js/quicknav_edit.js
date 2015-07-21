/**
 * 后台快捷导航设置
 * @version $Id$
 */

$(function() {
	// 初始化选色器
	$("#qn_icon_preview").colorPicker({
		style: "quicknav-icon-picker",
		data: ["#E47E61", "#F09816", "#D29A63", "#7BBF00", "#3497DB", "#8EABCD", "#AD85CC", "#82939E", "#58585C"],
		position: {my: "center top+10", at: "center bottom"},
		reset: false,
		onPick: function(hex) {
			$("#qn_icon_preview").css("background-color", hex);
			$("#qn_icon_color").val(hex);
		}
	});

	var renderPreivew = function() {
		var name = $("#qn_name").val();
		$("#qn_icon_preview").html(name.substr(0, 2));
		$("#qn_icon_text").val(name.substr(0, 2));
	};
	// 定时刷新预览
	setInterval(function() {
		if ($("#qn_img_value").val() === "") {
			renderPreivew();
		}
	}, 500);

	// 初始化上传控件
	Ibos.upload.image({
		// Backend Settings
		upload_url: Ibos.app.url("dashboard/quicknav/uploadicon"),
		post_params: {},
		file_post_name: "Filedata",
		file_queue_limit: "1",
		button_placeholder_id: "qn_upload",
		custom_settings: {
			targetId: "qn_img_preview",
			inputId: "qn_img_value",
			success: function(data, res) {
				if (res.isSuccess) {
					// 上传成功后，隐藏自动生成的图标效果
					// 显示重置按钮
					$("#qn_icon_preview").hide();
					$("#reset_qn_upload").show();
				} else {
					Ui.tip(res.msg, 'warning');
				}
			}
		}
	});

	// 清空上传图像
	$("#reset_qn_upload").on("click", function() {
		// 重置后，清空图片值， 显示自动生成图标效果， 隐藏重置按钮
		$("#qn_img_value").val("");
		$("#qn_icon_preview").show();
		$("#qn_img_preview").empty();
		$(this).hide();
	});
});

