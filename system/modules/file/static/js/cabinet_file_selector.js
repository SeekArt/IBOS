/**
 * 文件选择器
 * @version $Id$
 */

(function(){
	var FileModel,
		FileCollection,
		FileView,
		FileListView,
		BreadcrumbView;

	Ibos.openFileSelector = function(ok){
		var selected = [];

		var inSelected = function(data) {
			for(var i = 0; i < selected.length; i++) {
				if(selected[i].fid === data.fid) {
					return i;
				}
			}
			return -1;
		}

		var dialog = Ui.dialog({
			id: "file_selector",
			title: "",
			lock: true,
			padding: 0,
			width: 602,
			height: 560,
			skin: "fc-selector-dialog",
			content: '<div class="fc-selector"><div id="fc_breadcrumb" class="fc-nav"></div><div class="fc-list-cell"><ul class="list-thumb scroll clearfix" id="fc_select_list"></ul></div></div>',
			ok: function(){
				ok && ok.call(this, [].concat(selected));
			}
		});


		var staticUrl = Ibos.app.getStaticUrl(),
			fileAssetUrl = Ibos.app.getAssetUrl("file");

		// 读取文件选择器相关样式
		Ibos.statics.load({
			url: fileAssetUrl + "/css/file_cabinets.css",
			type: "css"
		});

		$.when(
			Ibos.statics.load(staticUrl + "/js/lib/underscore/underscore.js"),
			Ibos.statics.load(fileAssetUrl + "/js/lang/zh-cn.js")
		).done(function(){
			function loadSource() {
				$.when(
					Ibos.statics.load({ url: fileAssetUrl + "/templates/file_item.html", type: "html" }),
					Ibos.statics.load({ url: fileAssetUrl + "/templates/file_breadcrumb.html", type: "html" }),
					Ibos.statics.load(fileAssetUrl + "/js/cabinet.js")
				).done(function(a1, a2, a3){
					var Cb = Ibos.Cabinet;

					FileModel = Cb.FileModel.extend({
						select: function(){
							if(this.get("type") == 0) {
								FileModel.__super__.select.call(this);
							}
						},

						unselect: function(){
							if(this.get("type") == 0) {
								FileModel.__super__.unselect.call(this);
							}
						}
					});


					// 个人网盘文件、文件夹集合类
					FileCollection = Cb.FileCollection.extend({
						url: Ibos.app.url("file/personal/getcate&getfull=1"),

						model: FileModel,

						initialize: function(){
							this.on("open", this.open);
						},

						open: function(fid){
							this.fetch({
								reset: true,
								data: { pid: fid }
							})
						}
					});

					// 个人网盘文件、文件夹视图类
					FileView = Cb.FileView.extend({
						template: _.template(a1),

						render: function(){
							this.$el.html(this.template(this.model.toJSON()));
							return this;
						},

						initialize: function(){
							this.model.on("change", this.render, this);
							this.model.on("fileselect", this.select, this);
							this.model.on("fileunselect", this.unselect, this);

							this.model.on("open", this.open, this);
						},

						// 勾选
						select: function(){	
							this.$el.addClass("selected");
						},

						// 取消勾选
						unselect: function(){
							this.$el.removeClass("selected");
						},

						events: {
							"mousedown": function(evt){
								this.mousedownClient = { x: evt.clientX, y: evt.clientY };

								// 选中状态时，阻止 mousedown 事件冒泡以阻止选区功能
								// 此时拖动文件作为剪切功能处理
								if(this.model.selected) {
									evt.stopPropagation();
								};
							},
							// 选中
							"click": function(evt){
								if(this.isClickEvent(evt)) {
									if(this.model.get("type") == 0) {
										this.model.toggleSelect();
									} else {
										this.model.trigger("open", this.model.get("fid"));
									}
								}
							}
						},
					});	

					
					// 个人网盘文件、文件夹列表视图类
					FileListView = Cb.FileListView.extend({
						el: "#fc_select_list",

						// 实例化一个 FileView, 添加一个文件节点
						addOne: function(file){
							var fileView = new FileView({ model: file });
							this.$el.append(fileView.render().el);
						},
					});

					BreadcrumbView = Cb.BreadcrumbView.extend({
						template: _.template(a2)
					});

					initFileSelector();
					Ibos.openFileSelector.depsLoaded = true;
				});
			}

			if(typeof Backbone == undefined) {
				loadSource();
			} else {
				Ibos.statics.load(staticUrl +"/js/lib/backbone/backbone.js").done(loadSource);
			}
		});

		function initFileSelector(){
			dialog.title(Ibos.l("CABINET.SELECT_FILE_FROM_CABINET"));

			var fileCollection = new FileCollection();
			var fileListView = new FileListView({
				collection: fileCollection
			});

			var breadCrumb = new BreadcrumbView({
				events: {
					"click a": function(evt){
						fileCollection.open($.attr(evt.target, "data-fid"));
					},
				},
				model: new Ibos.Cabinet.BreadcrumbModel({
					prefix: [{
						fid: "0",
						name: Ibos.l("CABINET.PERSONAL_CABINET")
					}]
				})
			});

			fileCollection.on("reset", function() {
				var fc = this;
				// 初始化面包屑
				var crumbs = _.map(this.response.breadCrumbs, function(bcb) {
					return {
						name: bcb.name,
						fid: bcb.fid
					}
				});
				breadCrumb.model.setCrumbs(crumbs);

				// 还原已选中文件的选中状态
				this.each(function(model){
					if(inSelected(model.toJSON()) !== -1){
						model.select();
					}
				});
			});

			// 选择、取消选择时同步选中文件数据
			fileCollection.on("fileselect", function(model){
				if(inSelected(model.toJSON()) === -1) {
					selected.push(model.toJSON());
				}
			});
			fileCollection.on("fileunselect", function(model){
				var index = inSelected(model.toJSON());
				if(index !== -1) {
					selected.splice(index, 1);
				}
			});

			// 默认打开个人网盘根目录
			fileCollection.open('0');
		}

		return dialog;
	}
})();
