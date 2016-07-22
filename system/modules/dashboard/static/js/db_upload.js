$(function() {
	var logoUpload = Ibos.upload.image({
		// Backend Settings
		upload_url: Ibos.app.url("dashboard/upload/index", {op: "upload"}),
		file_post_name: 'watermark',
		file_types: "*.jpg; *.jpeg; *.png",
		custom_settings: {
			//图片显示节点
			success: function(file, data) {
				Dom.byId("upload_img").src = data.url;
				Dom.byId("watermark_img").value = data.path;
			},
			progressId: 'upload_img_wrap'
		}
	});
	$("#thumbnail, #watermark_opacity, #photo_quality").each(function() {
		$(this).ibosSlider({
			range: 'min',
			scale: 5,
			tip: true,
			target: "next"
		});
	});

	new P.Tab($("#watermark_type"), "label");


	var watermarkEnable = $("#watermark_enable");
	watermarkEnable.attr("value", watermarkEnable.attr("checked") ? 1 : 0);

	// 水印开关
	watermarkEnable.on("change", function() {
		watermarkEnable.attr("value", watermarkEnable.attr("checked") ? 1 : 0);
		$("#watermark_setup").toggle();
	});

	// 图片水印位置选择
	$("#watermark_position").on("click", "label", function() {
		$(this).addClass("active").siblings().removeClass("active");
	});

	// 水印颜色选择
	var $watermarkColorCtrl = $("#watermark_color_ctrl"),
			$watermarkColorValue = $('#watermark_color_value');
	$watermarkColorValue.colorPicker({
		ctrl: $watermarkColorCtrl,
		mode: 'simple',
		onPick: function(hex, title) {
			$watermarkColorCtrl.css('background-color', hex);
			$watermarkColorValue.val(hex);
		}
	});
	// 水印预览
	$('[data-type="watermark-review"]').on('click', function() {
		var waterMarkType = $('input[name="watermarktype"]:checked').val(),
				$imgTarget = $('#watermark_img'),
				$textTarget = $('#watermark_text'),
				params = {
					trans: $('#watermarktrans').val(),
					quality: $('#watermarkquality').val(),
					type: '',
					val: '',
					pos: $('input[name="watermarkposition"]:checked').val(),
					textcolor: $('#watermark_color_value').val(),
					size: $('#watermark_text_size').val(),
					fontpath: $('#watermark_fontpath').val(),
					watermarkminheight: $('input[name="watermarkminheight"]').val(),
					watermarkminwidth: $('input[name="watermarkminwidth"]').val()
				};

		if (waterMarkType === 'image') {
			params.val = $imgTarget.val();
			if ($.trim(params.val) === '') {
				Ui.tip(Ibos.l("UPLOAD.UPLOAD_PICTURE_FIRST"), 'danger');
				return false;
			}
			params.type = 'image';
		} else if (waterMarkType === 'text') {
			params.val = $textTarget.val();
			if ($.trim(params.val) === '') {
				$textTarget.blink();
				return false;
			}
			params.type = 'text';
		}
		var dialog = Ui.dialog({
			id: 'review_box',
			title: Ibos.l("UPLOAD.UPLOAD_PICTURE_FIRST"),
			width: '500px',
			cancel: true
		});
		// 加载对话框内容
		$.ajax({
			url: Ibos.app.url("dashboard/upload/index", {op: "waterpreview"}),
			data: $.param(params),
			success: function(data) {
				dialog.content(data);
			},
			cache: false
		});
	});

	$('input[name="watermarkposition"]:checked').parent().addClass('active');
});