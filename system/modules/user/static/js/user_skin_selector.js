/**
 * 用户-banner选择功能
 * @version 	$Id$
 */
$(function() {
	var PageList = function(container, options) {
		this.$container = $(container);
		this.opts = $.extend(true, {}, PageList.defaults, options);
		this.currentPage = 0;
		this.pageTo(this.opts.startAt);
	};

	PageList.defaults = {
		startAt: 1,
		pageSize: 10
	};

	$.extend(PageList.prototype, {
		render: function(datas) {
			var that = this,
				tmpl;

			if (datas && datas.length) {

				tmpl = $.map(datas, function(d, i) {
					return $.template(that.opts.tpl, d);
				}).join(" ");

				this.$container.html(tmpl);
			}
		},

		pageTo: function(page) {
			if (page < 1) {
				page = 1;
			}
			var opts = this.opts;

			// 如果是静态数据
			if (opts.data && opts.data.length) {
				// 计算出总页数
				var total = Math.ceil(opts.data.length / opts.pageSize);
				// 当设置页数大于总页数时，跳至最后一页
				if (page > total) {
					page = total;
				}

				this.render(this.opts.data.slice((page - 1) * opts.pageSize, page * opts.pageSize));
				this.currentPage = page;

				this.$container.trigger("pagechange", {
					page: this.currentPage,
					total: total
				});
			}
			// 如果动态请求数据
		},

		prev: function() {
			this.pageTo(this.currentPage - 1);
		},

		next: function() {
			this.pageTo(this.currentPage + 1);
		}
	});

	var SkinList = function(container, options) {
		var that = this;
		this._super.call(this, container, options);
		this.$container.on("click", "> li:not(.active)", function() {
			that.selectOne($(this));
		});
	};

	Ibos.core.inherits(SkinList, PageList);

	$.extend(SkinList.prototype, {
		selectOne: function($item) {
			$item.siblings(".active").removeClass("active");
			$item.addClass("active");
			this.$container.trigger("select", {
				$item: $item
			});
		},

		getSelected: function() {
			return this.$container.children(".active");
		},

		getDataLength: function() {
			return this.opts.data.length;
		},

		getDataById: function(id) {
			return $.grep(this.opts.data, function(d) {
				return d.id == id;
			})[0];
		},

		removeDataById: function(id) {
			var index = -1;
			$.each(this.opts.data, function(i, d) {
				if (d.id == id) {
					index = i;
					return false;
				}
			});

			if (index !== -1) {
				this.opts.data.splice(index, 1);
				this.pageTo(this.currentPage);
			}
		}
	});

	var skinDialogInited = false;

	function openSkinDialog(conf) {
		var dialog = Ui.dialog(
			$.extend({
				id: "d_skin_bg",
				title: Ibos.l("USER.INDIVIDUALITY_SETTING"),
				ok: false,
				padding: 0
			}, conf)
		);

		if (!skinDialogInited) {

			Ibos.statics.loads([
				Ibos.app.getStaticUrl("/js/lib/SWFUpload/swfupload.packaged.js"),
				Ibos.app.getStaticUrl("/js/lib/SWFUpload/handlers.js")
			])
			.done(function() {
				dialog.content(document.getElementById("skin_bg"));
				//初始化自定义模版上传功能
				Ibos.upload.image({
					upload_url: Ibos.app.url("user/skin/uploadBg", {
						"uid": Ibos.app.g("uid"),
						"hash": Ibos.app.g("upload").hash
					}),

					file_size_limit: "2MB", //设置图片最大上传值

					button_placeholder_id: "skin_bg_choose",
					button_width: "550",
					button_height: "170",
					button_image_url: "",

					custom_settings: {
						progressId: "skin_choose_area",

						success: function(file, data) {
							if (data.isSuccess) {
								skinUploadSuccess(file, data);
							} else {
								Ui.tip(data.msg, 'danger');
								return false;
							}
						}
					}
				});
				skinDialogInited = true;
			});
		} else {
			dialog.content(document.getElementById("skin_bg"));
		}

		return dialog;
	};


	//点击换肤,进行自定义和模板选择
	$("#skin_choose").click(openSkinDialog);

	// 皮肤列表翻页，判断上下页按钮的可用状态
	// 由于选中项被重置，同时隐藏删除按钮
	$("#choose_list").on("pagechange", function(evt, data) {
		$("#pre_bg_page").prop("disabled", data.page <= 1);
		$("#next_bg_page").prop("disabled", data.page >= data.total || data.total <= 1);
		$(".sk-delete-btn").hide();
	})
	// 选中皮肤时，显示删除按钮
	.on("select", function() {
		$(".sk-delete-btn").show();
	})

	var skinList = new SkinList("#choose_list", {
		tpl: "skin_template",
		pageSize: 4,
		data: Ibos.app.g("allBg")
	});

	// 切换至上页皮肤
	$("#pre_bg_page").on("click", $.proxy(skinList.prev, skinList));
	// 切换至下页皮肤
	$("#next_bg_page").on("click", $.proxy(skinList.next, skinList));


	//模板选择栏时,保存按钮
	$("#module_save").on('click', function() {
		var $selected = skinList.getSelected(),
			skinData;

		if ($selected.length) {
			skinData = skinList.getDataById($selected.attr("data-id"));

			$.post(Ibos.app.url("user/skin/cropBg"), {
				bgSubmit: 1,
				noCrop: 1,
				src: skinData.imgUrl,
				uid: Ibos.app.g("uid"),
				selectCommon: 1
			}, function(res) {
				//保存成功后刷新当前页面
				if (res.isSuccess) {
					Ui.getDialog("d_skin_bg").close();
					window.location.reload();
				}
			});
		} else {
			Ui.tip("@USER.SELECT_ONE_MODEL_PICTURE", "danger");
		}
	});

	// 删除选定模板
	$("#sk_delete_btn").on("click", function() {
		var skinCount = skinList.getDataLength();
		var selectedId = skinList.getSelected().attr("data-id");
		// 当有选中的皮肤时
		if (selectedId) {
			//当删除选定模版后只剩一张模版图片时，不能删除
			if (skinCount > 1) {

				Ui.confirm(Ibos.l("USER.DELETE_CHOOSE_MODEL"), function() {
					$.post(Ibos.app.url("user/skin/delbg"), {
						id: selectedId
					}, function(res) {
						if (res.isSuccess) {
							//视图层上删除节点
							skinList.removeDataById(selectedId);
							Ui.tip("@OPERATION_SUCCESS");
						}
					}, "json");

				});
			} else {
				Ui.tip("@USER.IS_LAST_MODEL", "warning");
			}
		}
	});

	// 提交裁剪好的图片
	$('#save_skin').on("click", function() {
		var bgData = getBgData();

		if (bgData.src === "") {
			Ui.tip("@USER.UPLOAD_PICTURE", 'danger');
			return false;
		} else {
			//判断是否将自定义上传图片设置为模板
			var commonSet = $("#sk_setting_model").is(":checked") ? 1 : 0;

			bgData.bgSubmit = "1";
			bgData.commonSet = commonSet;

			$.post(Ibos.app.url("user/skin/cropBg"), bgData, function(res) {
				if (res.isSuccess) {
					Ui.getDialog('d_skin_bg').close();
					Ui.tip("@OPERATION_SUCCESS", "success");
					window.location.reload();
				}
			}, 'json');
		}
	});

	// 取消按钮
	$("#module_close, #custom_close").on('click', function() {
		Ui.getDialog('d_skin_bg').close();
	});

	// 上传自定义皮肤成功
	function skinUploadSuccess(file, data) {
		U.loadImage(data.file, function(img) {
			var defaultWidth = img.width,
				defaultHeight = img.height;

			Ibos.statics.load({
				type: "css",
				url: Ibos.app.getAssetUrl("user", "/css/jquery.Jcrop.min.css")
			});

			Ibos.statics.load(Ibos.app.getAssetUrl("user", "/js/jquery.Jcrop.min.js"))
				.done(function() {
					$(".skin-choose-area").removeClass("active");
					var $preview = $('#preview_hidden');
					$preview.show();
					//赋值到对应控件
					$('#sk_img_src').val(data.file);
					//上传图片成功后，将重新上传按钮隐藏
					$("#skin_reupload_img").show();

					$(img).show().appendTo($preview)
					//裁剪插件
					.Jcrop({
						bgColor: '#333', //选区背景色
						bgFade: true, //选区背景渐显
						fadeTime: 1000, //背景渐显时间
						allowSelect: false, //是否可以选区，
						allowResize: true, //是否可以调整选区大小
						minSize: [170, 550], //可选最小大小
						boxWidth: 550, //画布宽度
						boxHeight: 170, //画布高度
						setSelect: [0, 0, 550, 170], //初始化时位置
						aspectRatio: 3.33,
						onSelect: function(c) { //选择时动态赋值，该值是最终传给程序的参数！
							$('#sk_x').val(c.x); //需裁剪的左上角X轴坐标
							$('#sk_y').val(c.y); //需裁剪的左上角Y轴坐标
							$('#sk_w').val(c.w); //需裁剪的宽度
							$('#sk_h').val(c.h); //需裁剪的高度
						}
					});


					var img_height = 0;
					var img_width = 0;
					var real_height = img.height;
					var real_width = img.width;

					if (real_height > real_width && real_height > 170) {
						var persent = real_height / 170;
						real_height = 170;
						real_width = real_width / persent;
					} else if (real_width > real_height && real_width > 550) {
						var persent = real_width / 550;
						real_width = 550;
						real_height = real_height / persent;
					}
					if (real_height < 170) {
						img_height = (170 - real_height) / 2;
					}
					if (real_width < 550) {
						img_width = (550 - real_width) / 2;
					}

					$preview.css({
						width: (550 - img_width),
						height: (170 - img_height),
						paddingTop: img_height,
						paddingLeft: img_width
					});
				});
		});
	}

	//重新上传,清空裁剪参数
	$('#skin_reupload_img').click(resetSkinUpload);

	// 重置自定义皮肤上传
	function resetSkinUpload() {
		// 隐藏重新上传按钮
		$("#skin_reupload_img").hide();
		$(".skin-choose-area").addClass("active");
		$("#preview_hidden").empty().hide().css({
			'padding-top': 0,
			'padding-left': 0,
			'width': '9999px',
			'height': '9999px'
		});
		$("#sk_img_src").val("");
	}

	function getBgData() {
		var params = {
			x: $("#sk_x").val(),
			y: $("#sk_y").val(),
			w: $("#sk_w").val(),
			h: $("#sk_h").val(),
			src: $("#sk_img_src").val(),
			uid: $("input[name='uid']").val()
		};
		return params;
	}
});