/**
 * 我共享的
 * @author inaki
 * @version $Id$
 */

  (function(){
  	var Cb = Ibos.Cabinet;

 	// "我分享的"文件、文件夹集合类
 	Cb.MyshareFileCollection = Cb.FileCollection.extend({
 		url: Ibos.app.url("file/myshare/getcate"),
 	});


 	// "我分享的"文件、文件夹视图类
 	Cb.MyshareFileView = Cb.FileView.extend({
 		initialize: function(){
			var fileView = this;

			this.model.on("change", this.render, this);
			this.model.on("fileselect", this.select, this);
			this.model.on("fileunselect", this.unselect, this);

			this.model.on("open", this.open, this);
			this.model.on("edit", this.edit, this);
			this.model.on("download", this.download, this);

			this.model.on("rename", this.rename, this);
			this.model.on("remove", this.remove, this);
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

 		getRenameUrl: function(){
 			return Ibos.app.url("file/personal/ajaxEnt", {'op': 'rename'});
 		},

 		getRemoveUrl: function(){
 			return Ibos.app.url("file/personal/del", { "op": "recycle" });
 		},

 		getMarkUrl: function(){
 			return Ibos.app.url("file/personal/ajaxEnt", {'op': 'mark'});
 		},
 	});


 	// "我分享的"文件、文件夹列表视图类
 	Cb.MyshareFileListView = Cb.FileListView.extend({

 		// 实例化一个 FileView, 添加一个文件节点
 		addOne: function(file){
 			var fileView = new Cb.MyshareFileView({ model: file });

 			this.$el.append(fileView.render().el);

 			fileView.on("viewingallery", this.viewInGallery);
 		},

 		getRemoveUrl: function(){
 			return Ibos.app.url("file/personal/del", { "op": "recycle" });
 		},

 		getDownloadUrl: function(){
 			return Ibos.app.url("file/personal/ajaxent", { "op": "download" });
 		}
 	});

	Cb.MyshareCabinetView = Cb.CabinetView.extend({
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
		 				name: Ibos.l("CABINET.MYSHARE")
		 			}]
		 		})
			});

 			this.collection.on("reset", function() {
		 		// 初始化面包屑
		 		var crumbs = _.map(this.response.breadCrumbs, function(bcb) {
		 			return {
						name: bcb.name,
						path: "#pid/" + bcb.fid
					}
		 		});

				breadCrumb.model.setCrumbs(crumbs);
 			});
		}
	});
  })();


 (function() {
 	var Cb = Ibos.Cabinet;

 	var fileCollection = new Cb.MyshareFileCollection();
 	var fileListView = new Cb.MyshareFileListView({
 		collection: fileCollection,
 		menu: ""
 	});

 	new Cb.FileToolbar({ collection: fileCollection });

	new Cb.MyshareCabinetView({ collection: fileCollection });

 	// 初始化路由功能
 	var MyshareRouter = Cb.Router = Backbone.Router.extend({
 		routes: {
 			"": "getCate",
 			"pid/:id": "getCate"
 		},

 		getCate: function(id) {
 			id = id || "0";
 			// 公司网盘根目录
 			fileCollection.andFetch({
 				reset: true,
 				data: {
 					pid: id
 				}
 			});
 		}
 	});

 	new MyshareRouter({ collection: fileCollection });

 	Backbone.history.start();
 })();
