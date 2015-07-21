/**
 * 微博主页
 * 2014-01-06
 * @author inaki
 */


$(function() {
	var $doc = $(document);
	// 发布类型
	var publishPicType = false;
	// 发布范围，全公司
	var publishRange = 0;
	var CUSTOM = 3;
	// 微博最大字数
	var WBNUMS = Ibos.app.g('wbnums') || 200;
	// 发布框
	$('[data-node-type="textarea"]').on("countchange", function(evt, data) {
		var isSendable = 0 < data.count && data.count <= WBNUMS,
				$btn = $(this).closest("[data-node-type='publishWrap']").find('[data-node-type="publishBtn"]');
		$btn.toggleClass("btn-warning", isSendable).prop("disabled", !isSendable);
	}).charCount({style: "wb-word-limit", max: WBNUMS})

	Ibos.evt.add({
		// 发布
		"publish": function(param, elem) {
			var $elem = $(elem),
					$wrap = $elem.closest("[data-node-type='publishWrap']"),
					$txt = $wrap.find('[data-node-type="textarea"]'),
					interval = Ibos.app.g("submitInterval") || 0,
					txt = $txt.val();

			if (!$elem.data("disabledSubmit")) {
				if ($.trim(txt) !== "") {
					// 替换特殊字符
					param.body = U.entity.escape(txt);
					param.view = publishRange;
					//  当可见范围为指定人员时
					if (publishRange == CUSTOM) {
						param.viewid = $wrap.find('[data-node-type="publishRange"]').val();
						if (param.viewid == '') {
							Ui.tip('@WB.SPECIFY_FEED_USER', 'warning');
							return false;
						}
					}
					$elem.button("loading");
					// 当有附件时
					if (publishPicType) {
						var attachid = $wrap.find('[data-node-type="picId"]').val();
						if(attachid) {
							param.type = 'postimage';
							param.attachid = attachid;
						} else {
							param.type = "post";
						}
					} else {
						param.type = 'post';
					}
					param.formhash = Ibos.app.g('formHash');
					// 发布请求
					Wb.publish(param, function(res) {
						if (res.isSuccess) {
							$('.no-data-tip').remove();
							$txt.val("").trigger("focus");
							$elem.button("reset");
							// 图片类型发布后，移除图片并关闭图片面板
							if (publishPicType) {
								Ibos.evt.fire("removePic");
								$('[data-action="pic"]').trigger("click");
							}
							// Tip
							Ui.tip("@WB.PUBLISH_SUCCESS");
							Ui.showCreditPrompt();
							Wb.insertFeedBefore(res.data);
							// 发布成功后，在一定的时间间隔后，才可再次发布
							$elem.data("disabledSubmit", true);
							setTimeout(function() {
								$elem.removeData("disabledSubmit");
							}, interval);
						}
					});
				}
			}
		},
		// 发布范围选择
		"publishTo": function(param, elem) {
			var $elem = $(elem), $btn = $elem.closest("ul").prev(), btnTextNode = $btn[0].childNodes[0];
			btnTextNode.nodeValue = param.text;
			Ui.selectOne($elem.parent());
			// 指定人可见
			var $publishRange = $elem.closest("[data-node-type='publishWrap']").find("[data-node-type='publishToRange']");
			if (param.type == CUSTOM) {
				// 出现人员选择框
				$publishRange.show().find('[data-node-type="publishRange"]').userSelect({
					data: Ibos.data.get()
				});
			} else {
				$publishRange.hide();
			}
			publishRange = param.type;
		},
		// 图片内容
		"pic": function(param, elem) {
			var $elem = $(elem), $picBox = $elem.closest("[data-node-type='publishWrap']").find("[data-node-type='picBox']");
			$elem.toggleClass("active");
			publishPicType = !publishPicType;
			$picBox.toggle();
		},
		// 右栏快速关注
		"quickFollow": function(param, elem) {
			var $elem = $(elem), $followBox = $elem.closest("[data-node-type='quickFollowBox']");
			$elem.button("loading");
			// @Debug: 测试数据
			// 这里一个请求，处理了关注和返回新推荐用户数据
			// 如果后台实现不方便可分成两次请求
			$.get("system/modules/weibo/views/t_newpush.php", param, function(res) {
				// 此处关注成功后，如果有其他的推荐用户，会替代节点
				if (res.isSuccess) {
					$elem.button("reset").html("已关注");
					if (res.data) {
						$followBox.replaceWith(res.data);
					} else {
						$followBox.remove();
					}
				}
			}, "json");
		}
	});

	// 右侧栏定位
	(function() {
		var $sb = $("#wb_sidebar"), $mn = $("#wb_home_mainer");
		$sb.affixTo($mn);
	})();


	// 图片上传
	(function() {
		var $picBox = $('[data-node-type="picBox"]');
		var _uploadStart = function(file) {
			// 移除上张图片预览
			$picBox.find(".wb-upload-preview").remove();
			// showModal
			$('<div class="wb-upload-modal" data-node-type="uploadModal"></div>')
					.appendTo($picBox).fadeTo(1000, 0.5);
			// showProgress
			$('<div class="wb-upload-progress" data-node-type="uploadProgress"><div data-node-type="uploadProgressbar"></div> <span>上传中 <em data-node-type="uploadPercent">0%</em></span></div>')
					.appendTo($picBox)
					.find('[data-node-type="progressbar"]').progressbar();
		};
		var _uploadProgress = function(file, uploaded, total) {
			var $progressbar = $picBox.find('[data-node-type="uploadProgressbar"]'),
					$percent = $picBox.find('[data-node-type="uploadPercent"]'),
					percent = Math.floor(uploaded / total * 100);
			// updateProgress
			$progressbar.progressbar("val", percent);
			$percent.html(percent + "%");
		};
		var _uploadSuccess = function(file, res) {
			var $tip = $picBox.find('.wb-upload-success-tip'), resData = $.parseJSON(res);
			// hideProgress
			$picBox.find('[data-node-type="uploadProgress"]').remove();
			// tip
			$tip.fadeIn();
			// hideTip, hideModal
			$tip.fadeOut();
			$picBox.find('[data-node-type="uploadModal"]').fadeOut(function() {
				$(this).remove();
			});
			// showPreview
			var html = '<div class="wb-upload-preview"><img src="<%=attachUrl%>" title="<%=attachName%>" alt="<%=attachName%>">' +
					'<div class="wb-reupload-modal"></div>' +
					'<div class="wb-upload-pic"><i class="pic-holder active"></i> <p>重新上传</p></div>' +
					'<div class="wb-reupload-bar"> <div class="wb-reupload-bar-bg rdb"></div> <span>"<%=attachName%>"</span> <a href="javascript:;" data-action="removePic" class="cbtn o-trash"></a> </div>' +
					'</div>';

			$picBox.addClass("wb-upload-success");
			$.tmpl(html, {
				attachUrl: resData.url,
				attachName: resData.name
			}).hide().appendTo($picBox).fadeIn(1000);
			// setValue
			$picBox.find('[data-node-type="picId"]').val(resData.aid);
		};

		Ibos.evt.add({
			"removePic": function() {
				$picBox.find(".wb-upload-preview").remove();
				$picBox.removeClass("wb-upload-success").find('[data-node-type="picId"]').val("");
			}
		});

		Ibos.upload.image({
			button_placeholder_id: "wb_imgupload",
			post_params: {module: 'weibo'},
			file_upload_limit: 0,
			file_queue_limit: 1,
			button_image_url: "",
			button_width: 620,
			button_height: 348,
			button_action:SWFUpload.BUTTON_ACTION.SELECT_FILE,
			file_queued_handler: null,
			upload_start_handler: _uploadStart,
			upload_progress_handler: _uploadProgress,
			upload_success_handler: _uploadSuccess
		});
	})();
});

