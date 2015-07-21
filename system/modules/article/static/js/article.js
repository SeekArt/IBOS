/**
 * article.js
 * 信息中心模块通用JS
 * IBOS
 * Article
 * @author		inaki
 * @version		$Id$
 */

var Article = Article || {};

// 数据交互
Article.op = {
	/**
	 * 删除新闻
	 * @method removeArticles
	 * @param  {String}   ids      单一的新闻id, 或以“,”分隔的多个id
	 * @param  {Function} [callback] 请求响应后的回调函数
	 * @return 
	 */
	removeArticles: function(ids, callback) {
		if (!ids) {
			return false;
		}

		Ui.confirm(U.lang("ART.SURE_DEL_ARTICLE"), function() {
			$.post(Ibos.app.url("article/default/del"), { articleids: ids }, callback);
		});
	},
	/**
	 * 获取新闻阅读人员
	 * @method getArticleReaders
	 * @param    {String}  id          新闻id
	 * @param    {Function} [callback] 回调函数
	 * @return   {Object}              阅读人员数据
	 */
	getArticleReaders: function(id, callback) {
		if (!id) {
			return false;
		}
		$.post(Ibos.app.url("article/default/index", {"op": "getReader"}), {articleid: id}, callback)
	}
}


$(function() {
	Article.pic = {
		$picIds: $("#picids"),
		_itemPrefix: "pic_item_",
		_values: [],
		_getItem: function(id) {
			return $("#" + this._itemPrefix + id);
		},
		getValues: function(){
			return this.$picIds.val().split(",");
		},
		setValues: function(vals){
			return this.$picIds.val(vals.join(","));
		},

		initPicItem: function(item, data) {
			var $item = $(item),
					$checkbox = $('<label class="checkbox"><input type="checkbox" name="pic" value="' + data.aid + '"></label>'),
					$img = $('<img class="pull-left" width="100" src="' + data.url + '" />');

			$item.find("i").replaceWith($img);
			$item.prepend($checkbox).find(".o-trash").attr("data-id", data.aid);

			$checkbox.find('input[type="checkbox"]').label();

			$item.attr("id", this._itemPrefix + data.aid);
		},

		removeSelect: function(ids) {
			var vals = this.getValues();

			if(!vals || !vals.length) {
				return;
			}

			if(!$.isArray(ids)) {
				ids = [ids];
			}

			for (var i = 0; i < ids.length; i++) {
				var index = $.inArray(ids[i], vals);
				if(index !== -1) {
					this._getItem(vals[index]).remove();
					vals.splice(index, 1);
				}
			};

			this.setValues(vals);
		},

		moveUp: function(id) {
			var vals = this.getValues(),
				$item = this._getItem(id),
				index = $.inArray(id, vals),
				temp;
			if (index === -1) {
				return false;
			}
			// 当已为最上一项时， 移动到最后面
			if (index === 0) {
				$item.appendTo($item.parent());
				vals.push(vals.shift())
			} else {
				// 交换节点位置
				$item.insertBefore($item.prev());
				// 交换数组中的位置
				temp = vals[index];
				vals[index] = vals[index - 1];
				vals[index - 1] = temp;
			}

			this.setValues(vals);
		},
		moveDown: function(id) {
			var vals = this.getValues(),
				$item = this._getItem(id),
				index = $.inArray(id, vals),
				temp;

			if (index === -1) {
				return false;
			}
			// 当已为最下一项时， 移动到最前面
			if (index === vals.length - 1) {
				$item.prependTo($item.parent());
				vals.unshift(vals.pop())
			} else {
				// 交换节点位置
				$item.insertAfter($item.next());
				// 交换数组中的位置
				temp = vals[index];
				vals[index] = vals[index + 1];
				vals[index + 1] = temp;
			}

			this.setValues(vals);
		}
	}


	// 初始化侧栏分类
	var $tree = $("#tree");

	// 左侧分类树初始化
	$tree.waiting(null, "mini");
	$.get(Ibos.app.url("article/category/index"), function(data) {
		var treeSettings = {
			data: {
				simpleData: {
					enable: true
				}
			},
			view: {
				showLine: false,
				selectedMulti: false,
				showIcon: false
			}
		};

		var selectedNode;
		var treeObj = $.fn.zTree.init($tree, treeSettings, data);
		var sideTreeCategory = new SideTreeCategory(treeObj, {tpl: "tpl_category_edit"});

		$tree.waiting(false);

		var treeMenu = [
			{
				name: "add",
				text: '<i class="o-menu-add"></i> ' + U.lang("NEW"),
				handler: function(treeNode, categoryMenu) {
					var aid = $("#approval_id").val();
					sideTreeCategory.add(treeNode, {
						url: Ibos.app.url('article/category/add'),
						success: function(node, tree) {
							var tNode = tree.getNodeByParam("id", node.id);
							tNode.aid = node.aid;
							tNode.catid = node.id;
							tree.updateNode(tNode);
							Ui.tip(U.lang('TREEMENU.ADD_CATELOG_SUCCESS'))
						}
					}, {aid: aid});
					categoryMenu.menu.hide();
				}
			},
			{
				name: "update",
				text: '<i class="o-menu-edit"></i> ' + U.lang("EDIT"),
				handler: function(treeNode, categoryMenu) {
					sideTreeCategory.update(treeNode, {
						url: Ibos.app.url('article/category/edit'),
						success: function(node, tree) {
							Ui.tip(U.lang('TREEMENU.EDIT_CATELOG_SUCCESS'));
						}
					});
					categoryMenu.menu.hide();
				}
			},
			{
				name: "moveup",
				text: '<i class="o-menu-up"></i> ' + U.lang("MOVEUP"),
				handler: function(treeNode, categoryMenu) {
					sideTreeCategory.moveup(treeNode, {
						url: Ibos.app.url('article/category/edit', {op: 'move'}),
						success: function() {
							Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_SUCCESS'));
						},
						error: function() {
							Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_FAILED'), 'danger');
						}
					});
					categoryMenu.menu.hide();
				}
			},
			{
				name: "movedown",
				text: '<i class="o-menu-down"></i> ' + U.lang("MOVEDOWN"),
				handler: function(treeNode, categoryMenu) {
					sideTreeCategory.movedown(treeNode, {
						url: Ibos.app.url('article/category/edit', {op: 'move'}),
						success: function() {
							Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_SUCCESS'));
						},
						error: function() {
							Ui.tip(U.lang('TREEMENU.MOVE_CATELOG_FAILED'), 'danger');
						}
					});
					categoryMenu.menu.hide();
				}
			},
			{
				name: "remove",
				text: '<i class="o-menu-trash"></i> ' + U.lang("DELETE"),
				handler: function(treeNode, categoryMenu) {
					var tree = categoryMenu.tree,
							topTreeNode = tree.getNodesByParam("pid", "0");
					// 当只有一个顶级节点且当前要删除的是该节点时，不可删除 
					if (topTreeNode.length <= 1 && topTreeNode[0].id === treeNode.id) {
						Ui.tip(U.lang("ART.LEAVE_AT_LEAST_A_CATEGORY"), "warning");
						return false;
					}
					Ui.confirm(U.lang("ART.SURE_DEL_CATEGORY"), function() {
						categoryMenu.$ctrl.hide().appendTo(document.body);
						sideTreeCategory.remove(treeNode, {
							url: Ibos.app.url('article/category/del'),
							success: function() {
								Ui.tip(U.lang('TREEMENU.DEL_CATELOG_SUCCESS'));
							},
							error: function(res) {
								Ui.tip(res.msg, "danger");
							}
						});
						categoryMenu.menu.hide();
					})
				}
			}
		]
		var cate = new TreeCategoryMenu(treeObj, {
			menu: treeMenu
		});

		// 选中当前所在分类
		if (Ibos.app.g("catId") && Ibos.app.g("catId") > 0) {
			var sbTree = $.fn.zTree.getZTreeObj("tree"),
					selectedNode = sbTree.getNodeByParam("id", Ibos.app.g("catId"), null);
			if (selectedNode) {
				sbTree.selectNode(selectedNode);
			}
		}
	}, 'json');
});
