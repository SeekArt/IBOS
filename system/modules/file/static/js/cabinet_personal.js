/**
 * 个人网盘
 * @author inaki 
 * @version $Id$
 */

  (function(){
  	var Cb = Ibos.Cabinet;

 	// 个人网盘文件、文件夹集合类
 	Cb.PersonalFileCollection = Cb.FileCollection.extend({
 		url: Ibos.app.url("file/personal/getcate"),
 	});


 	// 个人网盘文件、文件夹视图类
 	Cb.PersonalFileView = Cb.FileView.extend({
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
 		}
 	});	

 	
 	// 个人网盘文件、文件夹列表视图类
 	Cb.PersonalFileListView = Cb.FileListView.extend({

 		// 实例化一个 FileView, 添加一个文件节点
 		addOne: function(file){
 			var fileView = new Cb.PersonalFileView({ model: file });

 			this.$el.append(fileView.render().el);

 			fileView.on("viewingallery", this.viewInGallery);
 		},

 		getCutUrl: function(){
 			return Ibos.app.url("file/personal/ajaxEnt", {'op': 'cut'});
 		},

 		getCopyUrl: function(){
 			return Ibos.app.url("file/personal/ajaxEnt", {'op': 'copy'});
 		},

 		getRemoveUrl: function(){
 			return Ibos.app.url("file/personal/del", { "op": "recycle" });
 		},

 		getDownloadUrl: function(){
 			return Ibos.app.url("file/personal/ajaxent", { "op": "download" });
 		}
 	});

 	// 个人网盘文件夹工具条视图类
 	Cb.PersonalFolderToolbar = Cb.FolderToolbar.extend({
 		getUploadUrl: function(){
 			return Ibos.app.url('file/personal/add', {
 					op: "upload",
 				pid: this.collection.pid || Ibos.app.g("pid") || "0",
 				uid: Ibos.app.g("uid"),
                PHPSESSID:Ibos.app.g("PHPSESSID"),
 				hash: Ibos.app.g("upload").hash
 			})
 		},

 		getNewFolderUrl: function(){
 			return Ibos.app.url("file/personal/add", {'op': 'mkDir'});
 		}
 	});

	Cb.PersonalCabinetView = Cb.CabinetView.extend({
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
 						name: Ibos.l("CABINET.PERSONAL_CABINET")
 					}]
 				})
 			});

 			this.collection.on("reset", function() {
 				var fc = this;
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

 	var fileCollection = new Cb.PersonalFileCollection();
 	var fileListView = new Cb.PersonalFileListView({
 		collection: fileCollection
 	});
 	// 右键菜单
	fileListView.$el.on("contextmenu", function(evt){
		var ctm = new Cb.ContextMenu({ collection: fileCollection });
		ctm.show({
			at: "left top",
			my: "left+" + evt.pageX + " top+" + evt.pageY,
			of: document.body
		});
		evt.preventDefault();
	});

 	new Cb.FileToolbar({
 		collection: fileCollection
 	});
 	
 	new Cb.PersonalFolderToolbar({
 		collection: fileCollection
 	});

 	new Cb.PersonalCabinetView({ collection: fileCollection });

 	// 初始化路由功能
 	var PersonalRouter = Cb.Router = Backbone.Router.extend({
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

 	new PersonalRouter({ collection: fileCollection });

 	Backbone.history.start();
 })();

