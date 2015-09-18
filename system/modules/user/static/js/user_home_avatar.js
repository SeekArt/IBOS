/**
 * 用户--个人中心--修改头像
 * @author 		inaki
 * @version 	$Id$
 */

$(function() {
	Ibos.statics.loads([
		Ibos.app.getStaticUrl("/js/lib/SWFUpload/swfupload.packaged.js"),
		Ibos.app.getStaticUrl("/js/lib/SWFUpload/handlers.js")
	])
	.done(function() {
		Ibos.upload.image({
			upload_url: Ibos.app.url('user/info/uploadavatar', {
				"uid": Ibos.app.g("uid"),
				"hash": Ibos.app.g("upload").hash
			}),
			button_placeholder_id: "user_pic",
			file_size_limit: "2MB", //设置图片最大上传值
			button_width: "408",
			button_height: "408",
			button_image_url: "",
			custom_settings: {
				progressId: "upload_area",

				//头像上传成功后的操作
				success: function(file, data) {
					if (data.IsSuccess) {
						var preview = $('.upload-area').children('#preview-hidden');
						preview.show();

						//三个预览窗口赋值
						$('.crop').children('img').attr('src', data.file + '?random=' + Math.random());

						//隐藏表单赋值
						$('#img_src').val(data.file);

						U.loadImage(data.file + "?random=" + Math.random(), function(img) {
							$(img).attr("id", "cropbox").show().appendTo(preview);
							var img_height = 0;
							var img_width = 0;
							var real_height = img.height;
							var real_width = img.width;
							if (real_height > real_width && real_height > 408) {
								var persent = real_height / 408;
								real_height = 408;
								real_width = real_width / persent;
							} else if (real_width > real_height && real_width > 408) {
								var persent = real_width / 408;
								real_width = 408;
								real_height = real_height / persent;
							}
							if (real_height < 408) {
								img_height = (408 - real_height) / 2;
							}
							if (real_width < 408) {
								img_width = (408 - real_width) / 2;
							}
							preview.css({
								width: (408 - img_width) + 'px',
								height: (408 - img_height) + 'px'
							});
							preview.css({
								paddingTop: img_height + 'px',
								paddingLeft: img_width + 'px'
							});

							Ibos.statics.load({
								type: "css",
								url: Ibos.app.getAssetUrl("user", "/css/jquery.Jcrop.min.css")
							});

							Ibos.statics.load(Ibos.app.getAssetUrl("user", "/js/jquery.Jcrop.min.js"))
								.done(function() {
									//裁剪插件
									$(img).Jcrop({
										bgColor: '#333', //选区背景色
										bgFade: true, //选区背景渐显
										fadeTime: 1000, //背景渐显时间
										allowSelect: false, //是否可以选区，
										allowResize: true, //是否可以调整选区大小
										aspectRatio: 1, //约束比例
										minSize: [180, 180], //可选最小大小
										boxWidth: 408, //画布宽度
										boxHeight: 408, //画布高度
										onChange: showPreview, //改变时重置预览图
										onSelect: showPreview, //选择时重置预览图
										setSelect: [0, 0, 180, 180], //初始化时位置
										onSelect: function(c) { //选择时动态赋值，该值是最终传给程序的参数！
											$('#x').val(c.x); //需裁剪的左上角X轴坐标
											$('#y').val(c.y); //需裁剪的左上角Y轴坐标
											$('#w').val(c.w); //需裁剪的宽度
											$('#h').val(c.h); //需裁剪的高度
										}
									});
								});
						});


						//提交裁剪好的图片
						$('.save-pic').click(function() {
							if ($('#preview-hidden').html() === '') {
								Ui.tip("@USER.UPLOAD_PICTURE", 'danger');
								return false;
							} else {
								//由于GD库裁剪gif图片很慢，所以显示loading
								$("#upload_area").waiting(null, "normal");
								$('#pic').submit();
							}
						});
						//重新上传,清空裁剪参数
						var i = 0;
						$('.reupload-img').click(function() {
							$('#preview-hidden').empty().hide().css({
								'padding-top': 0,
								'padding-left': 0
							});
						});
						//当头像上传成功后,显示重新上传和保存按钮
						$("#upload_btnbar").css("display", "block");
					} else {
						Ui.tip(data.msg, 'danger');
						return false;
					}
				}
			}
		});

		//预览图
		function showPreview(coords) {
			var img_width = $('#cropbox').width();
			var img_height = $('#cropbox').height();

			var getOffset = function(width, height) {
				var rx = width / coords.w,
					ry = height /coords.h;

				return {
					width: Math.round(rx * img_width) + 'px',
					height: Math.round(ry * img_height) + 'px',
					marginLeft: '-' + Math.round(rx * coords.x) + 'px',
					marginTop: '-' + Math.round(ry * coords.y) + 'px'
				};
			};
			//根据包裹的容器宽高,设置被除数
			$('#crop-preview-180').css(getOffset(180, 180));
			$('#crop-preview-60').css(getOffset(60, 60));
			$('#crop-preview-30').css(getOffset(30, 30));
		}
	});
});