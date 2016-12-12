/**
 * officialdoc.js
 * 信息中心模块通用JS
 * @version		$Id$
 */

var Official = {
	// 数据操作
	op: {
		/**
		 * 删除一篇或多篇通知
		 * @method removeDocs
		 * @param  {String} ids 传入删除的IDs
		 * @return {Object}     返回deffered对象
		 */
		removeDocs: function(ids) {
			if (!ids) {
				return false;
			}
			var url = Ibos.app.url("officialdoc/officialdoc/del"),
				param = {docids: ids};
			return $.post(url, param, $.noop, "json");
		},
		/**
		 * 获取签收人员
		 * @method getSign
		 * @param  {String} id 传入删除的ID
		 * @return {Object}    返回deffered对象
		 */
		getSign: function(id) {
			if (!id) {
				return false;
			}
			var url = Ibos.app.url("officialdoc/officialdoc/index"),
				param = {op: "getSign", docid : id};
			return $.post(url, param, $.noop, "json");
		},
		/**
		 * 获取未签收人员
		 * @method getNoSign
		 * @param  {String} id 传入删除的ID
		 * @return {Object}    返回deffered对象
		 */
		getNoSign: function(id) {
			if (!id) {
				return false;
			}
			var url = Ibos.app.url("officialdoc/officialdoc/index"),
				param = {op: "getUnSign", docid : id};
			return $.post(url, param, $.noop, "json");
		},
		/**
		 * 获取历史版本
		 * @method getVersion
		 * @param  {String} id 传入删除的ID
		 * @return {Object}    返回deffered对象
		 */
		getVersion: function(id) {
			if (!id) {
				return false;
			}
			var url = Ibos.app.url("officialdoc/officialdoc/index"),
				param = {op: "getVersion", docid : id};
			return $.post(url, param, $.noop, "json");
		},
		/**
		 * 签收通知
		 * @method sign
		 * @param  {String} id 传入删除的ID
		 * @return {Object}    返回deffered对象
		 */
		sign: function(id) {
			if (id) {
				var url = Ibos.app.url("officialdoc/officialdoc/show"),
					param = {op: "sign", docid : id};
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 获取模板内容
		 * @method getTemplate
		 * @param  {String} id 传入删除的ID
		 * @return {Object}    返回deffered对象
		 */
		getTemplate: function(tplId) {
			if (tplId) {
				var url = Ibos.app.url("officialdoc/officialdoc/index"),
					param = {op: "getRcType", typeid : tplId};
				return $.post(url, param, $.noop, "json");
			}
		},
		/**
		 * 获取左侧分类树
		 * @method getTree
		 * @return {Object}    返回deffered对象
		 */
		getTree: function(){
			var url = Ibos.app.url("officialdoc/category/index");
			return $.get(url, $.noop, 'json');
		}

	},
	/**
	 * 选择通知模板
	 * @method selectTemplate
	 * @param  {Object} ue    编辑器对象
	 * @param  {String} tplId 传入模块的id
	 */
	selectTemplate: function(ue, tplId) {
		if (tplId) {
			// 取消模板时，询问是否清空编辑器
			if (tplId == "0") {
				Ui.confirm(Ibos.l("DOC.CANCEL_TEMPLATE_TIP"), function() {
					ue.ready(function() {
						ue.setContent("");
					});
				});
			} else {
				ue.ready(function() {
					var setTemplate = function() {
						Official.op.getTemplate(tplId).done(function(res){
							ue.setContent(res.escape_content);
						});
					};
					if (ue.getContent() !== "") {
						Ui.confirm(Ibos.l("DOC.USE_TEMPLATE_TIP"), setTemplate);
					} else {
						setTemplate();
					}
				});
			}
		}
	},
	/**
	 * 打开发送窗口
	 * @method openPostWindow
	 * @param  {String} data 传入input的值
	 * @param  {String} name 传入form的打开方式
	 */
	openPostWindow: function(data, name) {
		var tempForm = document.createElement("form");

		tempForm.id = "tempForm1";
		tempForm.method = "post";
		tempForm.action = Ibos.app.url("officialdoc/officialdoc/index", {'op': 'prewiew'});
		tempForm.target = name;

		var hideInput = document.createElement("input");

		hideInput.type = "hidden";
		hideInput.name = "content";
		hideInput.value = data;
		tempForm.appendChild(hideInput);

		//监听事件的方法 打开页面window.open(name);
		if( tempForm.addEventListener ){
			tempForm.addEventListener("onsubmit", function() {
				window.open(name);
			});	
		}else{
			tempForm.attachEvent("onsubmit", function() {
				window.open(name);
			});	
		}
		
		document.body.appendChild(tempForm);

		tempForm.submit();
		document.body.removeChild(tempForm);
	},
	/**
	 * 获取图片的id
	 * [getImgsID description]
	 * @param  {ObJect} $elem 传入jquery节点对象
	 * @return {String}       传出图片的IDs
	 */
	getImgsID: function($elem) {
		var ids = $elem.map(function() {
			return $(this).attr("data-id");
		}).get();
		return ids;
	},
	/**
	 * 初始化左侧分类树初始化
	 * [initTree description]
	 * @return {[type]} [description]
	 */
	initTree : function(){
		var $tree = $("#tree");

		$tree.waiting(null, "mini");
		Official.op.getTree().done(function(data) {
			$.map(data, function(item) {
	            item.name = U.entity.unescape(item.name);
	        });
			var treeSettings = {
				data: {
					simpleData: {
						enable: true
					}
				},
				view: {
					showLine: false,
					showIcon: false,
					selectedMulti: false
				},
				callback: {
	                onClick: function(event, treeId, treeNode) {
	                    var param = U.getUrlParam(),
	                        catid = treeNode.catid;

	                        
                        Ibos.local.set("catid", catid);
	                    // 路由判断是否列表页
	                    if (/index/.test(param.r)) {
	                        try {
	                            OfficialIndex.tableConfig.catid = catid;
	                            OfficialIndex.tableConfig.ajaxSearch();
	                        } catch (e) {}
	                    } else {
	                        window.location.href = Ibos.app.url('officialdoc/officialdoc/index');
	                    }
	                }
	            }
			};

			var selectedNode;
			var treeObj = $.fn.zTree.init($tree, treeSettings, data);
			var sideTreeCategory = new SideTreeCategory(treeObj, {tpl: "tpl_category_edit"});
			$tree.waiting(false);

			var treeMenu = [
				{
					name: "add",
					text: '<i class="o-menu-add"></i> ' + Ibos.l("NEW"),
					handler: function(treeNode, categoryMenu) {
						var aid = $("#approval_id").val();
						sideTreeCategory.add(treeNode, {
							url: Ibos.app.url('officialdoc/category/add'),
							success: function(node, tree) {
								var tNode = tree.getNodeByParam("id", node.id);
								tNode.aid = node.aid;
								tNode.catid = node.id;
								tree.updateNode(tNode);
								Ui.tip(Ibos.l('TREEMENU.ADD_CATELOG_SUCCESS'));
							},
							error: function(res) {
								Ui.tip(res.msg, 'warning');
                        		return false;
							}
						}, {aid: aid});
						categoryMenu.menu.hide();
					}
				},
				{
					name: "update",
					text: '<i class="o-menu-edit"></i> ' + Ibos.l("EDIT"),
					handler: function(treeNode, categoryMenu) {
						sideTreeCategory.update(treeNode, {
							url: Ibos.app.url('officialdoc/category/edit'),
							success: function(node, tree) {
								Ui.tip(Ibos.l('TREEMENU.EDIT_CATELOG_SUCCESS'));
							},
							error: function(res) {
								Ui.tip(res.msg, 'warning');
                        		return false;
							}
						});
						categoryMenu.menu.hide();
					}
				},
				{
					name: "moveup",
					text: '<i class="o-menu-up"></i> ' + Ibos.l("MOVEUP"),
					handler: function(treeNode, categoryMenu) {
						sideTreeCategory.moveup(treeNode, {
							url: Ibos.app.url('officialdoc/category/edit', {op: 'move'}),
							success: function() {
								Ui.tip(Ibos.l('TREEMENU.MOVE_CATELOG_SUCCESS'));
							},
							error: function() {
								Ui.tip(Ibos.l('TREEMENU.MOVE_CATELOG_FAILED'), 'danger');
							}
						});
						categoryMenu.menu.hide();
					}
				},
				{
					name: "movedown",
					text: '<i class="o-menu-down"></i> ' + Ibos.l("MOVEDOWN"),
					handler: function(treeNode, categoryMenu) {
						sideTreeCategory.movedown(treeNode, {
							url: Ibos.app.url('officialdoc/category/edit', {op: 'move'}),
							success: function() {
								Ui.tip(Ibos.l('TREEMENU.MOVE_CATELOG_SUCCESS'));
							},
							error: function() {
								Ui.tip(Ibos.l('TREEMENU.MOVE_CATELOG_FAILED'), 'danger');
							}
						});
						categoryMenu.menu.hide();
					}
				},
				{
					name: "remove",
					text: '<i class="o-menu-trash"></i> ' + Ibos.l("DELETE"),
					handler: function(treeNode, categoryMenu) {
						categoryMenu.$ctrl.hide().appendTo(document.body);
						sideTreeCategory.remove(treeNode, {
							url: Ibos.app.url('officialdoc/category/del'),
							success: function() {
								Ui.tip(Ibos.l('TREEMENU.DEL_CATELOG_SUCCESS'));
							},
							error: function(res) {
								Ui.tip(res.msg, "danger");
							}
						});
						categoryMenu.menu.hide();
					}
				}
			];
			var cate = new TreeCategoryMenu(treeObj, {
				menu: treeMenu
			});

			// 选中当前所在分类
			if (Ibos.app.g("catId") && Ibos.app.g("catId") > 0) {
				var sbTree = $.fn.zTree.getZTreeObj("tree"),
						selectedNode = sbTree.getNodeByParam("id", Ibos.app.g("catId"), null);
				(selectedNode) && sbTree.selectNode(selectedNode);
			}
		});
	}
};


// 初始化侧栏目录
$(function() {	
	// 初始化左侧分类树初始化
	Official.initTree();
});

	
