/**
 * 文件柜核心 js
 * @author inaki
 * @version $Id$
 */
 // 选区功能
(function(){
 	var Region = function(elem, opts) {
 		this.$elem = $(elem);
 		this.opts = $.extend({}, Region.defaults, opts);
 		this.selectedIndexes = [];
 		this.posInfo = []; // [{x, x2, y, y2}]
 		this.scroll = { left: 0, top: 0 };
 		this._bindEvents();
 		this.enabled = true;
 	};

 	Region.defaults = {
 		selector: "> li"
 		// cancel: ".selected"
 	};

 	Region.prototype = {
 		constructor: Region,

 		_bindEvents: function(){
 			this.$elem.on("mousedown.region", $.proxy(this._mousedown, this));
 		},

 		_unbindEvents: function(){
 			this.$elem.off("mousedown.region");
 		},

 		_mousedown: function(evt){
 			// 非鼠标左键时返回
 			if(evt.which !== 1) {
 				return;
 			}

 			// 禁用文本选择
 			$(document.body).noSelect();
 			this._bindMouseMoveEvent(evt);
 			$(document).on("mouseup.region", $.proxy(this._mouseup, this));

 			if(!this.$region) {
 				this.$region = $("<div class='region'></div>").appendTo(document.body);
 			}
 			this.scroll = {
 				left: this.$elem.scrollLeft(),
 				top: this.$elem.scrollTop()
 			};
 			this.fetchPosInfo();
 		},

 		_mouseup: function(){
 			// 还原文本选择
 			$(document.body).noSelect(true);

 			// 解绑相关事件
 			$(document).off("mouseup.region");
 			this._unbindMouseMoveEvent();

 			// 移除选区层
 			if(this.$region) {
 				this.$region.remove();
 				this.$region = null;
 			}
 			this.scroll = {
 				left: 0,
 				top: 0
 			};

 			// 清除延时执行定时器
 			clearTimeout(this.timer);

 			// 发布选区结束事件
 			$(this).trigger("drawend");
 		},

 		_bindMouseMoveEvent: function(evt, _firstDraw) {
 			var _this = this,
 				region = { x: evt.pageX, y: evt.pageY },
 				_firstDraw = typeof _firstDraw !== "undefined" ? _firstDraw : true;

 			$(document).on("mousemove.region", function(moveEvt){
 				region.x2 = moveEvt.pageX;
 				region.y2 = moveEvt.pageY;

 				if(!_this._isValidRegion(region)) {
 					return false;
 				}
 				// 开始 draw 时，发布drawstart事件;
 				if(_firstDraw) {
 					_this.selectedIndexes = [];
 					$(_this).trigger("drawstart");
 					_firstDraw = false;
 				}

 				_this.draw(region);

 				// 定时执行，性能优化
 				_this._unbindMouseMoveEvent();
 				_this.timer = setTimeout($.proxy(_this._bindMouseMoveEvent, _this, evt, _firstDraw), 20);

 				moveEvt.stopPropagation();
 				moveEvt.preventDefault();
 			});
 		},

 		_unbindMouseMoveEvent: function(){
 			$(document).off("mousemove.region");
 		},

 		// 绘制选区层
 		draw: function(region) {
 			region = this.reviseRegion(region);
 			var $doc = $(document),
 				docOffset = {
 					width: $doc.width(),
 					height: $doc.height()
 				},
 				offset = {
 					left: region.x,
 					top: region.y,
 					width: region.x2 - region.x,
 					height: region.y2 - region.y
 				};

 			// 限制选区范围在文档内, 1px 修正
 			if(offset.left + offset.width > docOffset.width) {
 				offset.width = docOffset.width - offset.left - 1;
 			}
 			if(offset.top + offset.height > docOffset.height) {
 				offset.height = docOffset.height - offset.top - 1;
 			}

 			// 此处考虑到有 border 的存在，宽高不能直接使用 css 方法设置，而应该用 outerWidth 和 outerHeight
 			// 这里之所以将 left, top 减1，将宽高加2。目的是为了让鼠标在 draw 层上拖动，
 			// 避免在图片和链接上拖动时触发浏览器原本的辅助功能
 			this.$region.css({
 				left: offset.left - 1,
 				top: offset.top - 1
 			})
 			.outerWidth(offset.width + 2)
 			.outerHeight(offset.height + 2);

 			this.selectedIndexes = this.getItemIndexesInRegion(region);
 			$(this).trigger("drawing");
 		},

 		// 获取所有作用节点的位置信息
 		fetchPosInfo: function(){
 			var that = this,
 				$item = this.$elem.find(this.opts.selector);

 			this.posInfo = $item.map(function(i, elem){
 				var $elem = $(elem),
 					offset = $elem.offset();

 				return {
 					x: offset.left,
 					x2: offset.left + $elem.outerWidth(),
 					y: offset.top,
 					y2: offset.top + $elem.outerHeight()
 				};
 			}).get();

 			return this.posInfo;
 		},

 		// 校正区域位置信息，保持x2 >= x, y2 >= y
 		reviseRegion: function(region){
 			return {
 				x: Math.min(region.x, region.x2),
 				x2: Math.max(region.x, region.x2),
 				y: Math.min(region.y, region.y2),
 				y2: Math.max(region.y, region.y2)
 			}
 		},

 		// 判断选区是否有效
 		// 默认选区区域大于 10px 开始有效
 		_isValidRegion: function(region){
 			region = this.reviseRegion(region);
 			return (region.x2- region.x) >= 10 || (region.y2 - region.y >= 10)
 		},

 		// 判断一个节点是否在选区内 { x, x2, y, y2 }
 		// 分两种情况：
 		// 1. 选区边界在节点内
 		// 2. 选区包含节点
 		_isInRegion: function(info, region){
 			var st = this.$elem.scrollTop(),
 				sl = this.$elem.scrollLeft();

 			region = {
 				x: region.x,
 				y: region.y,
 				x2: region.x2,
 				y2: region.y2
 			}

 			if(sl > this.scroll.left) {
 				region.x2 = region.x2 + sl - this.scroll.left;
 			} else {
 				region.x = region.x + sl - this.scroll.left;
 			}

 			if(st > this.scroll.top) {
 				region.y2 = region.y2 + st - this.scroll.top;
 			} else {
 				region.y = region.y + st - this.scroll.top;
 			}

 			if((
 					// 选区包含节点
 					(region.x <= info.x && region.x2 >= info.x2) ||
 					// 选区左边在节点内
 					(region.x > info.x && region.x < info.x2) ||
 					// 选区右边在节点内
 					(region.x2 > info.x && region.x2 < info.x2)
 				) && (
 					// 选区包含节点
 					(region.y <= info.y && region.y2 >= info.y2) ||
 					// 选区上边在节点内
 					(region.y > info.y && region.y < info.y2) ||
 					// 选区下边在节点内
 					(region.y2 > info.y && region.y2 < info.y2)
 			)) {
 				return true;
 			}

 			return false;
 		},

 		/**
 		 * 获取在选区内的节点下标
 		 * @method getItemIndexesInRegion
 		 * @param  {Object} region 选区位置信息
 		 * @return {Array}         选区内的节点下标数组
 		 */
 		getItemIndexesInRegion: function(region) {
 			var indexes = [];

 			for(var i = 0, len = this.posInfo.length; i < len; i++) {
 				if(this._isInRegion(this.posInfo[i], region)) {
 					indexes.push(i);
 				}
 			};

 			return indexes;
 		},

 		/**
 		 * 获取在选区内的节点
 		 * @method getItemsInREgion
 		 * @param  {Object} region 选区位置信息
 		 * @return {Array}         选区内的节点数组
 		 */
 		getItemsInREgion: function(region) {
 			var _this = this;
 			return $.map(this.getItemIndexesInRegion(region), function(i, itemIndex){
 				return _this.$elem.find(_this.opts.selector).eq(itemIndex);
 			});
 		},

 		disable: function(){
 			if(this.enabled){
 				this._unbindEvents();
 				this.enabled = false;
 			}
 		},

 		enable: function(){
 			if(!this.enabled){
 				this._bindEvents();
 				this.enabled = true;
 			}
 		}
 	}

 	Ibos.Region = Region;
})();


(function () {
	var Cb = Ibos.Cabinet = {
		// 节点类型
		TYPE: {
			FLIE: 0,
			FOLDER: 1
		},

		// 权限
		ACCESS: {
			READABLE: 1,
			WRITABLE: 2
		},

		// 打开新建文件夹对话框
		showAddFolderDialog: function(param, ok){
			param = param || {};
			Ui.closeDialog("d_folder_add");
			return Ui.dialog({
				id: "d_folder_add",
				title: Ibos.l("CABINET.ADD_FOLDER"),
				content: "<input type='text' id='fc_folder_name' value='" + (param.name || "") + "' style='width: 420px'/>",
				width: 450,
				cancel: true,
				padding: "20px 15px",
				lock: true,
				init: function(){
					$("#fc_folder_name").focus();
				},
				ok: function(){
					var $input = $("#fc_folder_name"),
						name = $input.val();

					if($.trim(name) == "") {
						Ui.tip("@CABINET.INPUT_FOLDER_NAME", "warning");
						$input.focus()
						return false;
					}

					ok && ok(name);
				}
			});
		},

		// 打开重命名对话框
		showRenameDialog: function(param, ok){
			param = param || {};
			var name = param.name || "", postfix = "";

			Ui.closeDialog("d_file_rename");

			var dialog = Ui.dialog({
				id: "d_file_rename",
				title: Ibos.l("RENAME"),
				width: 450,
				padding: "20px 15px",
				cancel: true,
				lock: true,
				ok: function(){
					var $input = $("#fc_rename"),
						prefix = $input.val();

					if($.trim(prefix) == "") {
						Ui.tip(param.type == 0 ? "@CABINET.INPUT_FILE_NAME" : "@CABINET.INPUT_FOLDER_NAME", "warning");
						$input.focus();
						return false;
					}
					ok && ok(prefix + postfix);
				}
			});

			// 文件类型时，处理后缀名
			var tpl = "";
			if(param.type == 0) {
				var splitIndex = name.lastIndexOf(".");
				postfix = name.slice(splitIndex);
				name = name.slice(0, splitIndex);
			}

			if(postfix) {
				tpl = "<div class='input-group' style='width: 420px'> <input type='text' id='fc_rename' value='" + name + "'/> <span class='input-group-addon'><em class='ilsep'>" + postfix + "</em></span> </div>";
			} else {
				tpl = "<input type='text' id='fc_rename' value='" + name + "' style='width: 420px'/>";
			}

			dialog.content(tpl);
			$("#fc_rename").select();

			return dialog;
		},

		// 打开共享对话框
		showShareDialog: function(param, ok){
			param = param || {};
			Ui.closeDialog("d_file_share");

			var dialog = Ui.dialog({
				id: "d_file_share",
				title: Ibos.l("CABINET.SHARE_TO"),
				width: 450,
				padding: "20px 15px",
				cancel: true,
				lock: true,
				zIndex: 2000,
				ok: function(){
					ok && ok($("#fc_share_to").val());
				}
			});

			$.get(Ibos.app.url("file/myshare/share&op=getShareData", { fids: param.fids }), function(res){
				if(res.isSuccess) {
					dialog.content(res.html);
				} else {
					Ui.tip(res.msg, "danger");
				}
			}, "json")

			return dialog;
		},

		// 打开权限设置对话框
		showAccessDialog: function(param, ok){
			param = param || {};
			Ui.closeDialog("d_file_access");

			 Ui.ajaxDialog(Ibos.app.url("file/company/ajaxEnt", { "op": "getAccessView", "fid": param.fid }), {
				id: "d_file_access",
				title: Ibos.l("CABINET.SETUP_ACCESS"),
				width: 550,
				padding: "15px 20px",
				cancel: true,
				ok: ok
			});
		},

		// 在图片预览器中打开
		viewInGallery: function(images, index){ // thumburl、url、title、desc
			index = index || 0;
			// 读取初始化需要的文件
			var _loadFiles = function(callback){
				if(typeof FullGallery !== "undefined") {
					callback && callback();
				} else {
					U.loadCss(Ibos.app.getStaticUrl("/js/lib/gallery/jquery.gallery.css"));
					U.loadCss(Ibos.app.getStaticUrl("/js/app/fullGallery/fullGallery.css"));

					var galleryJsPath = Ibos.app.getStaticUrl("/js/lib/gallery/jquery.gallery.js"),
						mousewheelJsPath = Ibos.app.getStaticUrl("/js/lib/jquery.mousewheel.js"),
						fullGalleryJsPath = Ibos.app.getStaticUrl("/js/app/fullGallery/fullGallery.js");

					$.when($.getScript(galleryJsPath), $.getScript(mousewheelJsPath))
					.done(function(){
						$.getScript(fullGalleryJsPath, callback);
					});
				}
			};


			_loadFiles(function(){
				new FullGallery(images, { start_at_index: index })
			});

		}
	};

	// 文件柜面包屑模型类
	Cb.BreadcrumbModel = Backbone.Model.extend({
		defaults: {
			prefix: [],
			breadcrumbs: []
		},

		// 添加一节面包屑
		setCrumbs: function(crumbs) {
			var breadcrumbs = [].concat(this.get("prefix"));

			if(crumbs){
				if(!_.isArray(crumbs)) {
					crumbs = [crumbs];
				}
				breadcrumbs = breadcrumbs.concat(crumbs);
			}

			this.set("breadcrumbs", breadcrumbs);
		}
	});

	// 文件柜面包屑视图类
	Cb.BreadcrumbView = Backbone.View.extend({
		el: "#fc_breadcrumb",

		template: document.getElementById("tpl_file_breadcrumb") ?
			_.template(document.getElementById("tpl_file_breadcrumb").text) :
			$.noop,

		initialize: function(){
			this.listenTo(this.model, "change:breadcrumbs", this.render);
		},

		render: function(){
			return this.$el.html(this.template(this.model.toJSON()));
		}
	});


	/**
	 * 菜单基本视图类
	 * @class Ibos.Cabinet.Menu
	 * @constructor
	 * @extends {Backbone.View}
	 * @param  {Object} [] 配置
	 * @return {Ibos.Cabinet.Menu}
	 */
	Cb.Menu = Backbone.View.extend({
		tagName: "ul",

		className: "dropdown-menu fc-menu",

		attributes: { style: "position: absolute" },

		show: function(position){
			var view = this;

			this.render();

			this.$el.show().appendTo(document.body).position(position);

			// 点击非菜单区域时关闭菜单
			$(document.body).on("mousedown.filecontextmenu", function(evt){
				if(view.$el[0] !== evt.target && !view.$el[0].contains(evt.target)){
					view.hide();
				}
			});
		},

		hide: function(){
			$(document.body).off("mousedown.filecontextmenu");
			this.$el.remove();
		}
	});


	Cb.FileMenu = Cb.Menu.extend({
		template: document.getElementById("tpl_file_menu") ?
			_.template(document.getElementById("tpl_file_menu").text) :
			$.noop,

		events: {
			"click li:not(.disabled) [file-act]": "hide",
			"click li:not(.disabled) [file-act='open']": "open",
			"click li:not(.disabled) [file-act='edit']": "edit",
			"click li:not(.disabled) [file-act='download']": "download",
			"click li:not(.disabled) [file-act='access']": "access",
			"click li:not(.disabled) [file-act='copy']": "copy",
			"click li:not(.disabled) [file-act='cut']": "cut",
			"click li:not(.disabled) [file-act='rename']": "rename",
			"click li:not(.disabled) [file-act='remove']": "remove"
		},

		render: function(){
			var model = this.model,
                _this = this,
                officetype = ["doc", "docx", "xls", "xlsx", "ppt", "pptx"],
                isOffice = $.inArray(model.get("filetype"), officetype) != -1;


			this.$el.html(this.template({
				access: model.get("access"),
				isAdministrator: Ibos.app.g("isAdministrator") == 1,
				filetype: model.get("filetype"),
                isOffice: isOffice
			}));
		},

		open: function(){ this.model.trigger("open"); },

		download: function(){ this.model.trigger("download"); },

		edit: function(){ this.model.trigger("edit");},

		access: function(){ this.model.trigger("access"); },

		copy: function(){ this.model.trigger("filecopy", this.model); },

		cut: function(){ this.model.trigger("filecut", this.model); },

		rename: function(){ this.model.trigger("rename"); },

		remove: function(){ this.model.trigger("remove"); }
	});


	/**
	 * 右键菜单视图类
	 * @class Ibos.Cabinet.ContextMenu
	 * @constructor
	 * @extends {Ibos.Cabinet.Menu}
	 * @param  {Object} [] 配置
	 * @return {Ibos.Cabinet.ContextMenu}
	 */
	Cb.ContextMenu = Cb.Menu.extend({
		template: document.getElementById("tpl_context_menu") ?
			_.template(document.getElementById("tpl_context_menu").text) :
			$.noop,

		events: {
			"click li:not(.disabled) [file-act]": "hide",
			"click li:not(.disabled) [file-act='upload']": "upload",
			"click li:not(.disabled) [file-act='newFolder']": "newFolder",
			"click li:not(.disabled) [file-act='copy']": "copy",
			"click li:not(.disabled) [file-act='cut']": "cut",
			"click li:not(.disabled) [file-act='paste']": "paste"
		},

		render: function(){
			var collection = this.collection;

			this.$el.html(this.template({
				fileCreatable: collection.isFileCreatable(),
				folderCreatable: collection.isFolderCreatable(),
				selectedFileCount: collection.getSelected().length,
				clipboardFileCount: collection.getClipboardFileCount(),
				access: collection.response.pDir.access
			}));
		},

		copy: function(){
			this.collection.copySelected();
		},

		cut: function(){
			this.collection.cutSelected();
		},

		paste: function(){
			this.collection.paste();
		},

		upload: function(){
			this.collection.upload();
		},

		newFolder: function(){
			this.collection.newFolder();
		}
	});


	// 文件、文件夹模型类
	Cb.FileModel = Backbone.Model.extend({
		defaults: {
			access: 2,
			type: 1,
			thumb: "",
			name: "",
			iconBig: "",
			addtime: 0,
			formattedaddtime: "",
			size: "0",
			formattedsize: "0 Bytes",
			mark: 0,
			isShared: 0
		},

		initialize: function(){
			// 标识当前是否勾选
			this.selected = false;
		},

		select: function(){
			if(!this.selected) {
				this.selected = true;
				this.trigger("fileselect", this);
			}
		},

		unselect: function(){
			if(this.selected) {
				this.selected = false;
				this.trigger("fileunselect", this);
			}
		},

		toggleSelect: function(){
			if(this.selected) {
				this.unselect();
			} else {
				this.select();
			}
		},
	});


	// 文件、文件夹集合类
	Cb.FileCollection = Backbone.Collection.extend({
		model: Cb.FileModel,

		clipboard: null, // 剪贴板，用于处理复制、剪切、粘贴 { op, pid, fids }

		url: "",

		initialize: function(){
			this.on({
				"filecopy": function(model){
					this.copy([model]);
				},
				"filecut": function(model){
					this.cut([model]);
				}
			})
		},

		// response 解析函数
		parse: function(response) {
			this.response = response;
			this.pid = response.pid;
			return response.data;
		},

		// 根据 fid 属性获取模型
		getModelByFid: function(fid){
			if(_.isArray(fid)) {
				return this.filter(function(model){
					return _.indexOf(fid, model.get("fid")) !== -1;
				});
			} else {
				return this.filter(function(model){
					return model.get("fid") == fid;
				});
			}
		},

		// 获取勾选的文件模型
		getSelected: function(){
			return this.filter(function(model){
				return model.selected;
			});
		},

		// 获取勾选文件 ID
		getSelectedFids: function(){
			var fids = [];
			this.each(function(model){
				if(model.selected) {
					fids.push(model.get("fid"));
				}
			})
			return fids;
		},

		// 获取勾选文件的索引值
		getSelectedIndexes: function(){
			var indexes = [];
			this.each(function(model, index){
				if(model.selected) {
					indexes.push(index);
				}
			});
			return indexes;
		},

		// 全部勾选
		selectAll: function(){
			this.each(function(model){
				model.select();
			});
		},

		// 全部取消勾选
		unselectAll: function(){
			this.each(function(model){
				model.unselect();
			});
		},

		// 合并每一次的获取条件，迭代搜索
		andFetch: function(options, reset){
			if(reset){
				this._queryData = null;
			} else {
				this._queryData = _.extend({}, this._queryData, options.data);
				options.data = this._queryData;
			}
			this.fetch(options);
		},

		// 保存片段到剪贴板
		saveClip: function(op, models){
			this.clipboard = {
				op: op, // 操作类型 copy、cut
				pid: this.pid, // 源文件夹 id
				files: _.invoke(models, "toJSON") // 文件、文件夹数据
			};
			return this.clipboard;
		},

		// 清空剪贴板
		clearClipboard: function(){
			this.clipboard = null;
		},

		// 获取剪贴板里的文件数
		getClipboardFileCount: function(){
			var count = 0;
			if(this.clipboard) {
				count = this.clipboard.files.length;
			}
			return count;
		},

		// 复制
		copy: function(models){
			if(models && models.length) {
				this.saveClip("copy", models);
				_.invoke(this.models, "trigger", "removecutstate");
			}
			return this.clipboard;
		},

		// 复制当前选中项
		copySelected: function(){
			return this.copy(this.getSelected());
		},

		// 剪切
		cut: function(models){
			if(models && models.length) {
				this.saveClip("cut", models);
				// 未被剪切的项恢复正常
				_.invoke(_.difference(this.models, models), "trigger", "removecutstate");
				// 剪切项半透明
				_.invoke(models, "trigger", "addcutstate");
			}
			return this.clipboard;
		},

		// 剪切当前选中项
		cutSelected: function(){
			return this.cut(this.getSelected());
		},

		paste: function(pid){
			this.trigger("paste", pid);
		},

		// 是否允许上传、新建文件
		isFileCreatable: function(){
			return true;
		},

		// 是否允许新建文件夹
		isFolderCreatable: function(){
			return true;
		},

		upload: function(){
			this.trigger("upload");
		},

		newFolder: function(){
			this.trigger("newFolder");
		},

		removeSelectedFiles: function(){
			this.trigger("removeSelectedFiles", this.getSelected());
		},

		downloadSelectedFiles: function(){
			this.trigger("downloadSelectedFiles", this.getSelected());
		},

		shareSelectedFiles: function(shares){
			this.trigger("shareFiles", this.getSelectedFids().join(","), shares);
		}
	});


	// 文件、文件夹视图类
	Cb.FileView = Backbone.View.extend({
		tagName: "li",

		template: document.getElementById("tpl_file_item") ?
			_.template(document.getElementById("tpl_file_item").text) :
			$.noop,

		events: {
			"mousedown": function(evt){
				this.mousedownClient = { x: evt.clientX, y: evt.clientY };

				// 选中状态时，阻止 mousedown 事件冒泡以阻止选区功能
				// 此时拖动文件作为剪切功能处理
				if(this.model.selected && evt.which == 1) {
					evt.stopPropagation();
				};
			},
			// 选中
			"click": function(evt){
				if(this.isClickEvent(evt)) {
					this.model.toggleSelect();
				}
			},
			// 打开
			"dblclick": function(evt) {
				if(this.isClickEvent(evt)) {
					this.open(evt);
				}
			},

			"click .file-name": function(evt){
				if(this.isClickEvent(evt)) {
					this.open(evt);
					evt.stopPropagation();
				}
			},

			"click .o-folder-open": function(evt){
				if(this.isClickEvent(evt)) {
					this.open(evt);
					evt.stopPropagation();
				}
			},

			"click .oc-checkbox": function(evt){
				if(this.isClickEvent(evt)) {
					this.model.toggleSelect();
					evt.stopPropagation();
				}
			},

			// 分享
			"click .o-folder-share": function(evt){
				if(this.isClickEvent(evt)) {
					this.share();
					evt.stopPropagation();
				}
			},

			// 下载
			"click .o-folder-down": function(evt){
				if(this.isClickEvent(evt)) {
					this.download();
					evt.stopPropagation();
				}
			},

			// 菜单
			"click .o-folder-dropdown": function(evt){
				if(this.isClickEvent(evt)) {
					var menu = new Cb.FileMenu({ model: this.model });
					menu.show({
						at: "left bottom",
						my: "left top",
						of: evt.target
					});
					evt.stopPropagation();
				}
			},

			// 星标
			"click .o-fc-emptystar": function(evt){
				this.mark(true);
				evt.stopPropagation();
			},

			// 取消星标
			"click .o-fc-goldstar": function(evt){
				this.mark(false);
				evt.stopPropagation();
			}
		},

		// 由于结合选区，所以这里如果 mousedown 跟 mouseup 鼠标位置差距太大，则判断选区事件而为非 click 事件
		isClickEvent: function(evt){
			return Math.abs(evt.clientX - this.mousedownClient.x) <= 10 &&
				Math.abs(evt.clientY - this.mousedownClient.y) <= 10;
		},

		initialize: function(){
			var fileView = this,
				fileModel = this.model,
				fileCollection = fileModel.collection;

			fileModel.on("change", this.render, this);
			fileModel.on("fileselect", this.select, this);
			fileModel.on("fileunselect", this.unselect, this);

			fileModel.on("open", this.open, this);
			fileModel.on("edit", this.edit, this);
			fileModel.on("download", this.download, this);
			fileModel.on("access", this.access, this);

			fileModel.on("rename", this.rename, this);
			fileModel.on("remove", this.remove, this);

			fileModel.on("addcutstate", this.addCutState, this);
			fileModel.on("removecutstate", this.removeCutState, this);

			// 初始化拖动功能，默认禁用，选中后启用
			this.$el.draggable({
				disabled: true,
				scope: "file",
				helper: "clone",
				// containment : "document",
				cursorAt: { left: 40, top: 20 }
			});

			// 如果是文件夹，则可以成为拖动放置的目标
			// 当然也需要有写入的权限
			if(fileModel.get("type") == Cb.TYPE.FOLDER && fileModel.get("access") >= Cb.ACCESS.WRITABLE) {
				this.droppable = true;
				this.$el.droppable({
					scope: "file",
					hoverClass: "hover"
				})
			};

			// 开始文件拖动时，视图反馈
			this.$el.on("dragstart dropout", function(evt, ui){
				ui &&
				ui.helper.html(Ibos.l("CABINET.MOVE_TIP", {
					count: fileCollection.getSelected().length,
					folderName: ""
				}));
			});

			this.$el.on("dropover", function(evt, ui){
				ui &&
				ui.helper.html(Ibos.l("CABINET.MOVE_TIP", {
					count: fileCollection.getSelected().length,
					folderName: Ibos.string.ellipsis(fileModel.get("name"), 10) || ""
				}));
			})

			// 文件拖动完成
			this.$el.on("drop", function(){
				fileCollection.cutSelected();
				fileCollection.paste(fileModel.get("fid"));
			});
		},

		render: function() {
			var data = this.model.toJSON();

			// 是否支持在线查看
			data.openable = !!this.getDefualtViewer(data.filetype) || data.type == 1;

			this.$el.html(this.template(data));
			return this;
		},

		// 勾选
		select: function(){
			this.$el.addClass("selected");
			// 选中的文件节点可以拖动，但不能作为拖动目标
			this.$el.draggable("enable");
			this.droppable && this.$el.droppable("disable");
		},

		// 取消勾选
		unselect: function(){
			this.$el.removeClass("selected");
			// 未选中的文件节点不可以拖动，但能作为拖动目标
			this.$el.draggable("disable");
			this.droppable && this.$el.droppable("enable");
		},

		// 打开文件、文件夹
		open: function(){
			var data = this.model.toJSON();

			// 如果是文件夹则直接进入
			if(data.type == Cb.TYPE.FOLDER) {
				window.location.href = "#pid/" + this.model.get("fid");
			// 文件, 则判断是否支持在线预览
			} else {
				var defaultViewer = this.getDefualtViewer(data.filetype);
				// 如果支持在线预览
				if(defaultViewer) {
					this.viewWith(defaultViewer);
				}
			}
		},

		// 定义各种查看器
		// handler 的 this 指向 View 实例
		viewers: [
			// 图片查看器，画廊形式
			{
				name: "gallery",
				text: Ibos.l("CABINET.IMAGE_VIEWER"),
				filetypes: ["jpg", "jpeg", "png", "gif"],
				defaults: ["jpg", "jpeg", "png", "gif"],
				handler: function(model){
					this.trigger("viewingallery", model);
				}
			},
			// Microsoft 文档查看器
			{
				name: "Microsoft Doc viewer",
				text: Ibos.l("CABINET.MICROSOFT_DOC_VIEWER"),
				filetypes: ["doc", "docx", "xls", "xlsx", "ppt", "pptx"],
				defaults: ["doc", "docx", "xls", "xlsx", "ppt", "pptx"],
				handler: function(model){
					Ui.openFrame(model.get("officereadurl"), {
						title: false,
						width: 800,
						height: 600,
						lock: true
					});
				}
			},
			{
				name: "Txt Viewer",
				text: Ibos.l("CABINET.TXT_VIEWER"),
				filetypes: ["txt"],
				defaults: ["txt"],
				handler: function(model){
					var dialog = Ui.dialog({
						title: false,
						lock: true,
						width: 600,
						skin: "fc-txt-dialog"
					});

					$.get(model.get("fileurl"), function(res){
						dialog.content("<pre style='background: transparent; border: 0 none;'>" + res + "</pre>");
					});
				}
			}
		],

		getViewer: function(viewerName) {
			return _.findWhere(this.viewers, { name: viewerName })
		},

		// 根据文件类型拿到默认查看器
		getDefualtViewer: function(filetype) {
			return _.filter(this.viewers, function(viewer){
				return _.indexOf(viewer.defaults, filetype) !== -1;
			})[0];
		},

		// 根据文件类型拿到其他查看器
		getOtherViewers: function(filetype) {
			return _.filter(this.viewers, function(viewer){
				return _.indexOf(viewer.filetypes, filetype) !== -1 &&
					_.indexOf(viewer.defaults, filetype) === -1;
			});
		},

		viewWith: function(viewer){
			if(_.isString(viewer)) {
				viewer = this.getViewer(viewer);
			}
			viewer && viewer.handler.call(this, this.model);
		},

		share: function(){
			var model = this.model;

			Cb.showShareDialog({ fids: model.get("fid") }, function(shares){
				model.trigger("shareFiles", model.get("fid"), shares);
			});
		},

		getDownloadUrl: function(){
			return "";
		},

		// 下载
		download: function(){
			window.location.href =  this.getDownloadUrl() + "&fids=" + this.model.get("fid");
		},

        //编辑
        edit: function(){
            var data = this.model.toJSON();
            window.open(data.officeediturl, "_blank");
        },

		// 权限设置
		access: $.noop,

		getRenameUrl: function(){
			return "";
		},

		// 重命名
		rename: function(){
			var fileView = this,
				data = this.model.toJSON();

			Cb.showRenameDialog(data, function(newName) {
				if (newName != data.name) {
					$.post(fileView.getRenameUrl(), { fid: data.fid, name: newName }, function(res) {
						if (res.isSuccess) {
							fileView.model.set("name", newName);
							Ui.tip(res.msg, "success");
						} else {
							Ui.tip(res.msg, "warning");
						}
					}, 'json');
				}
			});
		},

		getRemoveUrl: function(){
			return "";
		},

		// 删除
		remove: function(){
			var view = this,
				fid = this.model.get("fid");

			Ui.confirm(Ibos.l("CABINET.REMOVE_TO_RECYCLEBIN_CONFIRM"), function() {
				$.post(view.getRemoveUrl(), { "fids": fid}, function(res) {
					if (res.isSuccess) {
						Backbone.history.loadUrl();
					} else {
						Ui.tip(res.msg, "warning");
					}
				}, 'json');
			});
		},

		// 附加剪切状态
		addCutState: function(){
			this.$el.addClass("fc-cut-state");
		},

		// 移除剪切状态
		removeCutState: function(){
			this.$el.removeClass("fc-cut-state");
		},

		getMarkUrl: function(){
			return "";
		},

		mark: function(mark){
			var view = this;

			$.post(view.getMarkUrl(), { "fid": this.model.get("fid"), mark: mark ? "1" : "0" }, function(res) {
				if (res.isSuccess) {
					view.model.set("mark", mark ? "1" : "0")
				} else {
					Ui.tip(res.msg, "warning");
				}
			}, 'json');
		}
	});


	// 文件、文件夹列表视图类
	Cb.FileListView = Backbone.View.extend({
		el: "#fc_list",

		initialize: function(){
			var flView = this,
				collection = this.collection;

			this.listenTo(collection, "add", this.addOne);
			this.listenTo(collection, "reset", this.addAll);

			this.listenTo(collection, "paste", this.paste);
			this.listenTo(collection, "removeSelectedFiles", this.removeSelectedFiles);
			this.listenTo(collection, "downloadSelectedFiles", this.downloadSelectedFiles);
			this.listenTo(collection, "shareFiles", this.shareFiles);

			// 过渡状态显隐
			this.listenTo(collection, "request", function(){
				flView.loading(true);
			});
			this.listenTo(collection, "sync", function(){
				flView.loading(false);
			});

			this.initRegion();
		},

		// 初始化选区功能
		initRegion: function(){
			var collection = this.collection;
			this.fileRegion = new Ibos.Region(this.$el);

			$(this.fileRegion).on({
				"drawing": function(evt){
					var selectedIndexes = this.selectedIndexes;

					if(collection.getSelectedIndexes().join(",") !== selectedIndexes.join(",")) {
						collection.each(function(model, index){
							if(_.indexOf(selectedIndexes, index) !== -1) {
								model.select();
							} else {
								model.unselect();
							}
						});
					}
				}
			});
		},

		events: {},

		loading: function(state){
			if(state) {
				this.$el.waiting(null, "normal", true);
			} else {
				this.$el.waiting(false);
			}
		},

		// 实例化一个 FileView, 添加一个文件节点
		addOne: function(file){
			var fileView = new Cb.FileView({ model: file });

			this.$el.append(fileView.render().el);

			fileView.on("viewingallery", this.viewInGallery);
		},

		// 添加所有模型对应的文件节点
		addAll: function() {
			var collection = this.collection,
				response = collection.response;

			this.$el.empty();

			if(typeof response.isSuccess !== "undefined" && !response.isSuccess) {
				Ui.tip(response.msg, "danger");
			} else {
				this.collection.each(this.addOne, this);
			}
		},

		getCutUrl: function(){
			return "";
		},

		getCopyUrl: function(){
			return "";
		},

		getRemoveUrl: function(){
			return "";
		},

		// 粘贴
		paste: function(pid){
			var view = this,
				collection = this.collection,
				clipboard = collection.clipboard,
				files = clipboard.files,
				fileParts,
				op = clipboard.op,
				_success;

			pid = pid || collection.pid;

			// 复制、剪切文件夹到自己或子文件夹
			if(_.filter(files, function(file){
				return pid == file.fid || (collection.response.pDir.idpath && collection.response.pDir.idpath.indexOf("/" + file.fid + "/") !== -1);
			}).length) {
				Ui.tip("@CABINET.CANNOT_COPY_OR_CUT_TO_SELF", "warning");
				return false;
			}

			// 移动（剪切）的文件中包含没有写入权限的项
			if(clipboard.op == "cut") {
				// 将要粘贴的文件分为有写入权限跟没写入权限两组
				fileParts = _.partition(clipboard.files, function(file){
					return file.access >= Cb.ACCESS.WRITABLE;
				});

				if(fileParts[1].length) {
					Ui.confirm(
						Ibos.l("CABINET.MOVE_FILE_ACCESS_CONFIRM", {
							fileName: Ibos.string.ellipsis(_.pluck(fileParts[1], "name").join(", "), 10),
							count: fileParts[1].length
						}),
						function(){
							_paste(fileParts[0], pid, op);
						}
					);
					return false;
				}
			}

			_paste(files, pid, op);

			function _paste(files, pid, op) {
				// 如果剪贴板非空
				if(files){
					_success = function(res){
						if (res.isSuccess) {
							Backbone.history.loadUrl();
						} else {
							Ui.tip(res.msg, "warning");
						}
					};

					// 从复制粘贴
					if(op === "copy") {

						$.post(view.getCopyUrl(), {
							sourceFids: _.pluck(files, "fid").join(","),
							targetFid: pid
						}, _success, 'json');

					// 从剪切粘贴，成功后清空剪切板
					} else if(op === "cut") {

						$.post(view.getCutUrl(), {
							sourceFids: _.pluck(files, "fid").join(","),
							targetFid: pid
						}, function(res){
							_success(res);
							collection.clearClipboard();
						}, 'json');

					}
				}
			}
		},

		// 批量删除
		removeSelectedFiles: function(selected){
			var view = this;

			Ui.confirm(Ibos.l("CABINET.REMOVE_TO_RECYCLEBIN_CONFIRM"), function() {
				$.post(view.getRemoveUrl(), { "fids": _.map(selected, function(model){
					return model.get("fid");
				}).join(",") }, function(res) {
					if (res.isSuccess) {
						Backbone.history.loadUrl();
					} else {
						Ui.tip(res.msg, "warning");
					}
				}, 'json');
			});
		},

		// 批量下载
		downloadSelectedFiles: function(selected){
			window.location.href =  this.getDownloadUrl() + "&fids=" + _.map(selected, function(model){
				return model.get("fid");
			}).join(",");
		},

		// 共享
		shareFiles: function(fids, shares){
			$.post(Ibos.app.url("file/myshare/share", { op: "share" }), {
				fids: fids,
				shares: shares
			}, function(res){
				if (res.isSuccess) {
					Backbone.history.loadUrl();
				} else {
					Ui.tip(res.msg, "danger");
				}
			}, "json");
		},

		viewInGallery: function(){
			var view = this,
				images = [],
				types = ["jpg", "jpeg", "png", "gif"];

			var index;

			view.model.collection.each(function(model){
				var data = model.toJSON();
				if(_.indexOf(types, data.filetype) !== -1 ) {
					images.push({
						thumburl: data.thumb,
						url: data.fileurl,
						title: data.name
					})
				}
				if(model === view.model) {
					index = images.length - 1;
				}
			});

			Cb.viewInGallery(images, index);
		}
	});


	// 文件工具条视图类
	Cb.FileToolbar = Backbone.View.extend({
		el: "#fc_file_toolbar",

		template: document.getElementById("tpl_file_toolbar") ?
			_.template(document.getElementById("tpl_file_toolbar").text) :
			$.noop,


		events: {
			"click [file-act='download']": "download",
			"click [file-act='rename']": "rename",
			"click [file-act='share']": "share",
			"click [file-act='remove']": "remove"
		},

		initialize: function(){
			this.collection.on("reset", this.render, this);
			this.collection.on("fileselect fileunselect", this.render, this);
		},

		render: function(){
			var selected = _.map(this.collection.getSelected(), function(model){
				return model.toJSON();
			});
			// 合计总大小
			var totalSize = 0,
			// 最小权限值
				minAccess = Cb.ACCESS.WRITABLE;


			if(selected.length) {
				for(var i = 0; i < selected.length; i++) {
					totalSize += +selected[i].size;
					minAccess = Math.min(selected[i].access, minAccess);
				};

				this.$el.html(this.template({
					length: selected.length,

					multiple: selected.length > 1,

					totalSize: U.formatFileSize(totalSize, 2),

					formattedaddtime: selected[0].formattedaddtime,

					name: Ibos.string.ellipsis(selected[0].name, 20, 10),

					access: minAccess,

					cabinetType: Ibos.app.g("cabinetType"),

					isFolder: selected[0].type == Cb.TYPE.FOLDER
				})).show();
			} else {
				this.$el.empty().hide();
			}
		},

		// 批量下载
		download: function(){
			this.collection.downloadSelectedFiles();
		},

		// 批量删除
		remove: function(){
			this.collection.removeSelectedFiles();
		},

		// 共享
		share: function(){
			var collection = this.collection;

			Cb.showShareDialog({ fids: collection.getSelectedFids().join() }, function(shares){
				collection.shareSelectedFiles(shares);
			});
		},

		// 重命名
		rename: function(){
			this.collection.getSelected()[0].trigger("rename");
		}
	});


	// 文件夹工具条视图类
	Cb.FolderToolbar = Backbone.View.extend({
		el: "#fc_folder_toolbar",

		template: document.getElementById("tpl_folder_toolbar") ?
			_.template(document.getElementById("tpl_folder_toolbar").text) :
			$.noop,


		initialize: function(){
			this.collection.on("reset", this.render, this);
			this.collection.on("upload", this.upload, this);
			this.collection.on("newFolder", this.newFolder, this);
		},

		events: {
			"click #upload_file_btn": "upload",
			"click #add_file_btn": "newFolder"
		},

		render: function(){
			this.$el.html(this.template({
				fileCreatable: this.collection.isFileCreatable(),
				folderCreatable: this.collection.isFolderCreatable()
			}))
		},

		getUploadUrl: function(){
			return "";
		},

		getNewFolderUrl: function(){
			return "";
		},

		// 新建文件夹
		newFolder: function(){
			var view = this,
				collection = this.collection;

			Cb.showAddFolderDialog({}, function(name){
				$.post(view.getNewFolderUrl(), { pid: collection.pid, name: name }, function(res) {
					if (res.isSuccess) {
						window.location.href = "#pid/" + res.fid;
					} else {
						Ui.tip(res.msg, "danger");
					}
				}, 'json');
			});
		},

		// 上传文件
		upload: function(){
			var collection = this.collection;

			var dialog = Ibos.uploadDialog({
				upload_url: this.getUploadUrl(),
				file_post_name: "Filedata",
				file_size_limit: Ibos.app.g("uploadMaxSize"),
				file_types: Ibos.app.g("uploadTypes"),
				file_types_description: Ibos.app.g("uploadTypesDesc"),
				post_params: {module: "file"},

				custom_settings: {
					success: function(file, res) {
						if(typeof res.isSuccess !== "undefined" && !res.isSuccess){
							Ui.tip(res.msg, "danger");
							return false;
						}
						Backbone.history.loadUrl();
					}
				}
			});

			// 动态设置上传地址
			if(Ibos.dialogUploadInstance) {
				Ibos.dialogUploadInstance.setUploadURL(this.getUploadUrl());
			}
		}
	});

	// 文件柜外围容器
	Cb.CabinetView = Backbone.View.extend({
		el: ".page-list",

		events: {
			// 全选、取消全靠
			"change #fc_checkall": function(evt){
				this.collection[evt.target.checked ? "selectAll" : "unselectAll"]();
			}
		},

		initialize: function(){
			var collection = this.collection;

			// 全选框状态
			collection.on("fileselect fileunselect", function() {
				$("#fc_checkall").label(this.length === this.getSelected().length ? "check" : "uncheck");
			});

			collection.on("reset", function(){
				var page = this.response.page;

				// 初始化页码
				var _settings = {
					items_per_page: page.limit,
					current_page: page.curPage,
					num_display_entries: 5,
					prev_text: false,
					next_text: false,
					renderer: "ibosRenderer",
					allow_jump: true,
					load_first_page: false,
					callback: function(page, elem) {
						collection.andFetch({
							reset: true,
							data: {
								page: page + 1
							}
						});
					}
				};

				$("#fc_pagination").pagination(page.count, _settings);
			})

			// 搜索功能
			$("#fc_search").search(function(value) {
				collection.andFetch({
					reset: true,
					data: {
						normal_search: 1,
						search: 1,
						keyword: value,
						page: 0
					}
				});
			});

			// 过滤功能
			$("#fc_filter").pSelect().on("change", function() {
				var val = $(this).val();

				$(this).data("pSelect").$container.toggleClass("highlight", val != "all");

				collection.andFetch({
					reset: true,
					data: {
						type: val,
						page: 0
					}
				});
			});

			// 排序功能
			$("#fc_order").pSelect().on("change", function() {
				var val = $(this).val();

				$(this).data("pSelect").$container.toggleClass("highlight", val != "0");

				collection.andFetch({
					reset: true,
					data: {
						orderIndex: val,
						page: 0
					}
				});
			});
		}
	});
})();
