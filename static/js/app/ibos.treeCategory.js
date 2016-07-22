/**
 * 树目录，主要用于侧栏分类
 * 数据层操作
 * @class TreeCategory
 * @constructor
 * @param {ZTree} ztree     $.fn.zTree的一个实例
 * @param {Object} settings 配置
 */
var TreeCategory = function(ztree, settings){
	this.tree = ztree;
	this.settings = settings || {};
}
// 添加分类节点
TreeCategory.prototype.add = function(data, setting){
	setting = setting || {};
	data = data || {};
	data.cateop = "add";
	var _this = this;
	var url = setting.url || this.settings.url;
	$.post(url, data, function(res){
		if(res.isSuccess || res.IsSuccess) {
			// 使用返回的信息构建新的树节点
			var treeNode = $.extend({
				name: U.entity.unescape(data.name),
				pid: data.pid,
				enable: true
			}, res)

			// 获取父树节点
			var pTreeNode = _this.tree.getNodeByParam("id", treeNode.pid);
			_this.tree.addNodes(pTreeNode, treeNode);
			// 传入回调函数时，执行
			setting.success && setting.success(treeNode, _this.tree)	
		} else {
			setting.error && setting.error(res)
		}
	}, "json");
}

// 更新分类节点
TreeCategory.prototype.update = function(cid, data, setting){
	setting = setting || {};
	data = data || {};
	data.cateop = "update";
	data.catid = cid;
	var _this = this,
		url = setting.url || this.settings.url;
	// 获取当前修改中的树节点
	var treeNode = this.tree.getNodeByParam("id", cid);
	// 判断节点不存在的情况和没有发生修改的情况
	if(treeNode) {
		$.post(url, data, function(res){
			if(res.isSuccess || res.IsSuccess) {
				// treeNode.pId = data.pid;
				// 更新树节点数据
				$.extend(treeNode, res);
				treeNode.name = U.entity.unescape(data.name);
				_this.tree.updateNode(treeNode);
				// 若父树节点也发生修改，则对应作出处理
				if(treeNode.pId != data.pid) {
					var parentTreeNode = _this.tree.getNodeByParam("id", data.pid);
					treeNode.pid = data.pid;
					_this.tree.moveNode(parentTreeNode, treeNode, "parent");
				}
				setting.success && setting.success(treeNode, _this.tree)	
			} else {
				setting.error && setting.error(res)
			}
		}, "json");
	}
}

// 上移树节点
TreeCategory.prototype.moveup = function(cid, setting){
	setting = setting || {};
	var url = setting.url || this.settings.url;
	var _this = this,
		treeNode = this.tree.getNodeByParam("id", cid),
		prevTreeNode;
	if(treeNode) {
		prevTreeNode = treeNode.getPreNode();
		// 当存在前一树节点时，上移才有意义
		if(prevTreeNode){
			$.post(url, { cateop: "move", type: "moveup", catid: cid, pid: treeNode.pId ? treeNode.pId : 0 }, function(res){
				if(res.isSuccess || res.IsSuccess) {
					_this.tree.moveNode(prevTreeNode, treeNode, 'prev');
					setting.success && setting.success(res, treeNode, _this.tree);	
				} else {
					setting.error && setting.error(res)	;
				}
			}, "json");
		}
	}
}

// 下移树节点
TreeCategory.prototype.movedown = function(cid, setting){
	setting = setting || {};
	var url = setting.url || this.settings.url;
	var _this = this,
		treeNode = this.tree.getNodeByParam("id", cid),
		nextTreeNode;
	if(treeNode) {
		nextTreeNode = treeNode.getNextNode();
		// 当存在前一树节点时，上移才有意义
		if(nextTreeNode){
			$.post(url, { cateop: "move", type: "movedown", catid: cid, pid: treeNode.pId ? treeNode.pId : 0 }, function(res){
				if(res.isSuccess || res.IsSuccess) {
					_this.tree.moveNode(nextTreeNode, treeNode, 'next');
					setting.success && setting.success(res, treeNode, _this.tree)
				} else {
					setting.error && setting.error(res)	
				}
			}, "json");
		}
	}
}

// 移除树节点
TreeCategory.prototype.remove = function(cid, setting){
	setting = setting || {};
	var _this = this,
		url = setting.url || this.settings.url,
		treeNode = this.tree.getNodeByParam("id", cid);
	if(treeNode) {
		$.post(url, { cateop: "delete", catid: cid }, function(res){
			if(res.isSuccess || res.IsSuccess) {
				_this.tree.removeNode(treeNode);
				setting.success && setting.success(res, treeNode, _this.tree);
			} else {
				setting.error && setting.error(res)	
			}
		}, "json");
	}
}



/**
 * 侧栏分类操作
 * @class SideTreeCategory
 * @constructor
 * @todo  改为TreeCategory的子类会不会比较好？
 * @param {ZTree} ztree     zTree的一个实例
 * @param {Object} settings 基本配置
 */
var SideTreeCategory = function(ztree, settings){
	this.tree = ztree;
	this.settings = $.extend({}, SideTreeCategory.defaults, settings);
	this.treeCategory = new TreeCategory(ztree, settings);
}

/**
 * 根据树节点创建目录
 * @method _createCateOptions
 * @private
 * @param  {String} selected 默认选中的目录
 * @return {String}          html字符串
 */
SideTreeCategory.prototype._createCateOptions = function(selected, excludeCatid) {
	var treeNodes = this.tree.getNodes();
	var _createSpace = function(level) {
		var space = "";
		level = level || 0;
		for(var i = 0; i < level; i++){
			space += "&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		return space;
	}

	var _join = function (nodes, tpl) {
		tpl = tpl || "";
		for(var i = 0; i < nodes.length; i++) {
			if(!excludeCatid || excludeCatid != nodes[i].id) {
				tpl += '<option value="' + nodes[i].id + '"' + (nodes[i].id == selected ? "selected" : "") +'>' + 
					_createSpace(nodes[i].level) + U.entity.escape(nodes[i].name) + 
					'</option>';
				if(nodes[i].children && nodes[i].children.length) {
					tpl = _join(nodes[i].children, tpl);
				}
			}
		}
		return tpl;
	}

	return _join(treeNodes, "");
}

/**
 * 增加分类
 * @method add
 * @param {Object} node    默认作为父节点的树节点
 * @param {Object} setting 配置项，此处setting是作为TreeCategory add方法的参数存在的
 * @return
 */
SideTreeCategory.prototype.add = function(node, setting){
	var _this = this,
		tpl = setting.tpl || this.settings.tpl || "",
		data = {},
		dialog;

	data = $.extend({}, node);
	data.name = "";
	data.pid = node.id;	
	// 创建父目录下拉选框
	data.optionHtml = this._createCateOptions(data.pid);

	Ui.closeDialog("d_tree_menu")
	
	dialog = Ui.dialog({
		id: "d_tree_menu",
		title: U.lang('TREEMENU.ADD_CATELOG'),
		content: $.template(tpl, data),
		ok: function(){
			var content = this.DOM.content,
				name = content.find('[name="name"]').val(),
				pid = content.find('[name="pid"]').val(),
				aid = content.find('[name="aid"]').val();

			if($.trim(name) !== "") {
				_this.treeCategory.add({ "name": name, "pid": pid, "aid": aid }, setting)
			} else {
				// Ui.tip("", "warning")
			}
		}
	});
}

SideTreeCategory.prototype.update = function(node, setting) {
	var _this = this,
		tpl = setting.tpl || this.settings.tpl || "",
		data = {},
		dialog;

	// 创建父目录下拉选框
	data = $.extend({
		pid: "",
		name: ""
	}, node);
	data.optionHtml = this._createCateOptions(data.pid, data.id);

	Ui.closeDialog("d_tree_menu")
	
	dialog = Ui.dialog({
		id: "d_tree_menu",
		title: U.lang('TREEMENU.EDIT_CATELOG'),
		content: $.template(tpl, data),
		ok: function(){
			var content = this.DOM.content,
				name = content.find('[name="name"]').val(),
				pid = content.find('[name="pid"]').val(),
				aid = content.find('[name="aid"]').val();

			if($.trim(name) !== "") {
				_this.treeCategory.update(node.id, {
					name: name,
					pid: pid,
					aid: aid
				}, setting)
			} else {
				// Ui.tip("", "warning")
			}
		}
	});
}

SideTreeCategory.prototype.moveup = function(node, setting) {
	node && this.treeCategory.moveup(node.id, setting);
}

SideTreeCategory.prototype.movedown = function(node, setting) {
	node && this.treeCategory.movedown(node.id, setting);
}

SideTreeCategory.prototype.remove = function(node, setting) {
	node && this.treeCategory.remove(node.id, setting);
}



/**
 * 分类树菜单类
 * @class  TreeCategoryMenu
 * @constructor
 * @param {ZTree} ztree     ztree实例
 * @param {Object} settings 配置
 *     @param {Array} menu  菜单数组，根据此数据生成对应菜单
 */
var TreeCategoryMenu = function(ztree, settings){
	var _this = this;
	this.tree = ztree;
	// 当配置了菜单项（数组）时
	if(settings.menu && settings.menu.length){
		var $ctrl = this.$ctrl = $('<span class="tree-ctrl"></span>').appendTo(document.body),
			$menu = this.createMenu(settings.menu),
			$tree;
		// 初始化为菜单对象
		this.menu = new Ui.PopMenu($ctrl, $menu, {
			showDelay: 0,
			hideDelay: 200,
			position: {
				at: "right top",
				my: "left+8 top-6"
			}
		});

		$tree = this.tree.setting.treeObj;

		$tree.on({
			"mouseenter": function(){

				var $treeNode = $(this).closest("li"),
					treeNode = _this.tree.getNodeByTId($treeNode[0].id);
				$ctrl.show().appendTo(this);
				_this.menu.hide();
				_this.menu.treeNode = treeNode;
			},
			"mouseleave": function(){
				$ctrl.hide();
			}
		}, "[treenode_a]");
		
	}
}
TreeCategoryMenu.prototype = {
	constructor: TreeCategoryMenu,
	/**
	 * 创建功能菜单
	 * @createMenu
	 * @private 
	 * @param  {Array} menu  菜单配置数组
	 * @return {Jquery}      Jq实例
	 */
	createMenu: function(menu) {
		var _this = this,
			$menu = $('<ul class="dropdown-menu"></ul>'),
			$item;
		for(var i = 0; i < menu.length; i++) {
			$item = $('<li><a href="javascript:;">' + (menu[i].text || '') + '</a></li>')
			.appendTo($menu);

			// 使用闭包保存循环变量
			(function(h){
				$item.on("click", function(){
					h && h(_this.menu.treeNode, _this);
				})
			})(menu[i].handler)
		}
		return $menu.appendTo(document.body);
	}
}
