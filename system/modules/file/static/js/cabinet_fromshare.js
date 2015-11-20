/**
 * 我收到的
 * @author inaki
 * @version $Id$
 */

  (function(){
  	var Cb = Ibos.Cabinet;

 	// "我收到的"文件、文件夹集合类
 	Cb.FromshareFileCollection = Cb.FileCollection.extend({
 		url: Ibos.app.url("file/fromshare/getcate"),
 	});


 	// "我收到的"文件、文件夹视图类
 	Cb.FromshareFileView = Cb.FileView.extend({
 		initialize: function(){
			this.model.on("change", this.render, this);
			this.model.on("fileselect", this.select, this);
			this.model.on("fileunselect", this.unselect, this);

			this.model.on("open", this.open, this);
			this.model.on("edit", this.edit, this);
			this.model.on("download", this.download, this);
		},

		render: function(){
			var data = $.extend({
				user: {},
				isnew: false
			}, this.model.toJSON());

			data.isUDir = data.fromuid && !data.fid;
			// 支持在线查看或为文件夹
			data.openable = !!this.getDefualtViewer(data.filetype) || data.type == 1;

			this.$el.html(this.template(data));

			return this;
		},

		// 勾选
		select: function(){
			this.$el.addClass("selected");
		},

		// 取消勾选
		unselect: function(){
			this.$el.removeClass("selected");
		},

 		getDownloadUrl: function(){
 			return Ibos.app.url("file/personal/ajaxent", { op: "download" });
 		},

 		// 打开文件、文件夹
		open: function(){
			var data = this.model.toJSON();

			// 在人员列表时
			if(data.fromuid && !data.fid){
				window.location.href = "#from/" + this.model.get("fromuid");
			} else {
				// 如果是文件夹则直接进入
				if(data.type == Cb.TYPE.FOLDER) {
					window.location.href = "#pid/" + this.model.get("fid");
				// 文件, 则判断是否支持在线预览
				} else {
					var defaultViewer = this.getDefualtViewer(data.filetype);
					// 如果支持在线预览
					if(defaultViewer) {
						this.viewWith(defaultViewer);
					// 不支持在线预览则勾选
					} else {
						this.model.toggleSelect();
					}
				}
			}
		},
 	});


 	// "我收到的"文件、文件夹列表视图类
 	Cb.FromshareFileListView = Cb.FileListView.extend({
 		initialize: function(){
 			var collection = this.collection;

			this.constructor.__super__.initialize.call(this);
			this.listenTo(collection, "reset", function(){
				// 根据面包屑长度判断是否在根目录
				// "我收到的" 根目录没有选择功能
				if(!collection.response.breadCrumbs.length) {
					this.fileRegion.disable();
				} else {
					this.fileRegion.enable();
				}
			});
 		},

 		// 实例化一个 FileView, 添加一个文件节点
 		addOne: function(file){
 			var fileView = new Cb.FromshareFileView({ model: file });

 			this.$el.append(fileView.render().el);

 			fileView.on("viewingallery", this.viewInGallery);
 		},

 		getCopyUrl: function(){
 			return Ibos.app.url("file/personal/ajaxEnt", {'op': 'copy'});
 		},

 		getDownloadUrl: function(){
 			return Ibos.app.url("file/personal/ajaxent", { "op": "download" });
 		}
 	});

 	Cb.FromshareFileToolBar = Cb.FileToolbar.extend({
 		render: function(){
 			var selected = _.map(this.collection.getSelected(), function(model){
 				return model.toJSON();
 			});

 			// 合计总大小
 			var totalSize = 0,
 			// 最小权限值
 				minAccess = Cb.ACCESS.WRITABLE;

 			if(selected.length) {
 				var isUDir = selected[0].fromuid && !selected[0].fid;

 				if(isUDir) {
 					this.$el.empty().hide();
 					return this;
 				}

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
 	});

	Cb.FromshareCabinetView = Cb.CabinetView.extend({
		initialize: function(){
			this.constructor.__super__.initialize.call(this);
			// 面包屑实例
			var breadCrumb = new Cb.BreadcrumbView({
				model: new Cb.BreadcrumbModel({
					prefix: [{
						path: Ibos.app.url("file/default/index"),
						name: Ibos.l("CABINET.CABINET")
					}, {
						path: "#",
						name: Ibos.l("CABINET.FROMSHARE")
					}]
				})
			});

 			this.collection.on("reset", function() {
		 		// 初始化面包屑
		 		var crumbs = _.map(this.response.breadCrumbs, function(bcb) {
		 			return bcb.fid ? {
		 				name: bcb.name,
						path: "#pid/" + bcb.fid
		 			} : {
		 				name: bcb.realname,
		 				path: "#from/" + bcb.uid
		 			}
		 		});

				breadCrumb.model.setCrumbs(crumbs);
 			});
		}
	});
  })();


 (function() {
 	var Cb = Ibos.Cabinet;

 	var fileCollection = new Cb.FromshareFileCollection();
 	var fileListView = new Cb.FromshareFileListView({
 		collection: fileCollection
 	});

 	new Cb.FromshareFileToolBar({ collection: fileCollection });

 	new Cb.FromshareCabinetView({ collection: fileCollection });

 	fileCollection.on("reset", function(){
 		// 以面包屑长度判断是否在根目录
		var isRootDir = !this.response.breadCrumbs.length;

		// 首页不支持选择、筛选、排序
		$("#fc_order").prev().toggle(!isRootDir);
		$("#fc_filter").prev().toggle(!isRootDir);
		$(".fc-toolbar .checkbox").toggle(!isRootDir);

		$("#fc_search").attr("placeholder", isRootDir ? Ibos.l("CABINET.SEARCH_USER_NAME") : Ibos.l("CABINET.SEARCH_FILE_NAME"));
 	})

 	// 初始化路由功能
 	var FromshareRouter = Cb.Router = Backbone.Router.extend({
 		routes: {
 			"": "getCate",
 			"pid/:id": "getCate",
 			"from/:uid": "getUCate"
 		},

 		getCate: function(id) {
 			id = id || "0";
 			// 公司网盘根目录
 			fileCollection.andFetch({
 				reset: true,
 				data: {
 					pid: id,
 					fromuid: null
 				}
 			});
 		},

 		getUCate: function(uid){
 			// 公司网盘根目录
 			fileCollection.andFetch({
 				reset: true,
 				data: {
 					fromuid: uid,
 					pid: 0
 				}
 			});
 		}
 	});

 	new FromshareRouter({ collection: fileCollection });

 	Backbone.history.start();
 })();
