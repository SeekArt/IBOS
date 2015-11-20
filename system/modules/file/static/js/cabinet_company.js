/**
 * 公司网盘
 * @author inaki 
 * @version $Id$
 */

(function(){
 	var Cb = Ibos.Cabinet;

	// 公司网盘文件、文件夹集合类
	Cb.CompanyFileCollection = Cb.FileCollection.extend({
		url: Ibos.app.url("file/company/getcate"),

		// 是否允许上传、新建文件
		isFileCreatable: function(){
			// 公司网盘根目录
			var isCompanyRoot = Ibos.app.g("cabinetType") == "company" && this.pid == 0;
			return !isCompanyRoot && this.response.pDir.access >= Cb.ACCESS.WRITABLE;
		},

		// 是否允许新建文件夹
		isFolderCreatable: function(){
			var isCompanyRoot = Ibos.app.g("cabinetType") == "company" && this.pid == 0;
			return (!isCompanyRoot && this.response.pDir.access >= Cb.ACCESS.WRITABLE)|| Ibos.app.g("isAdministrator") == 1;
		}
	});


	// 公司网盘文件、文件夹视图类
	Cb.CompanyFileView = Cb.FileView.extend({
		// 权限设置
		access: function(){
			var data = this.model.toJSON();

			Cb.showAccessDialog({ fid: data.fid }, function() {
				var rScope = $("#rScope").val(),
					wScope = $("#wScope").val();

				$.post(Ibos.app.url("file/company/ajaxEnt", {"op": "setAccess" }), {
					"fid": data.fid,
					"rScope": rScope, 
					"wScope": wScope
				}, function(res) {
					if (res.isSuccess) {
						Backbone.history.loadUrl();
						Ui.tip(res.msg, "success");
					} else {
						Ui.tip(res.msg, "warning");
					}
				}, 'json');
			});
		},

		getDownloadUrl: function(){
			return Ibos.app.url("file/company/ajaxent", { op: "download" });
		},

		getRenameUrl: function(){
			return Ibos.app.url("file/company/ajaxEnt", {'op': 'rename'});
		},

		getRemoveUrl: function(){
			return Ibos.app.url("file/company/del", { "op": "recycle" });
		}
	});	

	
	// 公司网盘文件、文件夹列表视图类
	Cb.CompanyFileListView = Cb.FileListView.extend({

		// 实例化一个 FileView, 添加一个文件节点
		addOne: function(file){
			var fileView = new Cb.CompanyFileView({ model: file });

			this.$el.append(fileView.render().el);

			fileView.on("viewingallery", this.viewInGallery);
		},

		getCutUrl: function(){
			return Ibos.app.url("file/company/ajaxEnt", {'op': 'cut'});
		},

		getCopyUrl: function(){
			return Ibos.app.url("file/company/ajaxEnt", {'op': 'copy'});
		},

		getRemoveUrl: function(){
			return Ibos.app.url("file/company/del", { "op": "recycle" });
		},

		getDownloadUrl: function(){
			return Ibos.app.url("file/company/ajaxent", { "op": "download" });
		}
	});

	// 公司网盘文件夹工具条视图类
	Cb.CompanyFolderToolbar = Cb.FolderToolbar.extend({
		getUploadUrl: function(){
			return Ibos.app.url('file/company/add', {
					op: "upload",
				pid: this.collection.pid || Ibos.app.g("pid") || "0",
				uid: Ibos.app.g("uid"),
				hash: Ibos.app.g("upload").hash
			})
		},

		getNewFolderUrl: function(){
			return Ibos.app.url("file/company/add", {'op': 'mkDir'});
		}
	});

	Cb.CompanyCabinetView = Cb.CabinetView.extend({
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
						name: Ibos.l("CABINET.COMPANY_CABINET")
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

	var fileCollection = new Cb.CompanyFileCollection();
	var fileListView = new Cb.CompanyFileListView({
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

	new Cb.CompanyFolderToolbar({
		collection: fileCollection
	});

	new Cb.CompanyCabinetView({ collection: fileCollection });

	// 路由类
	var CompanyRouter = Backbone.Router.extend({
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

	// 初始化路由功能
	new CompanyRouter();

	Backbone.history.start();
})();