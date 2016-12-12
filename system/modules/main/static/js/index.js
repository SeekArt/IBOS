/**
 * index.js
 * 前台首页
 * IBOS
 * @author		inaki
 * @version		$Id$
 */

/**
 * name 标识符
 * title 标题
 * style 样式类
 * content 内容
 * url  ajax地址
 * param  ajax参数 
 * tab  选项卡
 * autoRefresh 自动刷新内容
 * refreshInterval 
 * onload
 * onclose
 * oncontentchange
 */

var MainIndex = {
	op : {
		/**
		 * 特殊模块快捷方式入口
		 * @method moduleEntry
		 * @param  {String} url   传入url地址
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		moduleEntry : function(url, param){
			return $.post(url, param, $.nnop);
		},
		/**
		 * 恢复默认菜单设置
		 * @method  restoreDefaultMenu
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		restoreDefaultMenu : function(param){
			var url = Ibos.app.url('main/default/restoremenu');
			return $.post(url, param, $.noop, 'json');
		},
		/**
		 * 设置为默认菜单
		 * @method  setDefaultMent
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		setDefaultMent : function(param){
			var url = Ibos.app.url('main/default/commonmenu');
			return $.post(url, param, $.noop, 'json');
		},
		/**
		 * 保存常用目录
		 * @method  saveCommonMenu
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		saveCommonMenu : function(param){
			var url = Ibos.app.url('main/default/personalmenu');
			return $.post(url, param, $.noop, 'json');
		}
	}
};


/**
 * 查询数组中的字符或数字
 * @method nameIndexOf
 * @param  {String|Number} name 传入要查询的参数
 * @param  {Array} 	       arr  传入数组
 * @return {Number}      		返回参数的位置
 */
function nameIndexOf(name, arr){
	for(var i = 0, len = arr.length; i < len; i++){
		if(arr[i].name === name){
			return i;
		}
	}
	return -1;
}

/**
 * 用户自定义配置，可定义模块是否显示及显示顺序
 * 配置存储操作
 * @method moduleStorage
 * @param  {Array} defaults 传入配置参数
 * @return {Object}         返回相应的方法
 */
var moduleStorage = function(defaults){
	var userModule, // 从缓存得到
		storageName = "index_modules",
		/**
		 * 获取本地存储
		 * @method _get
		 * @return {Array} 返回index_modules的值
		 */
		_get = function(){
			if(Ibos.local.get){
				return Ibos.local.get(storageName)||$.extend([], defaults);
			}else{
				return $.parseJSON(U.getCookie(storageName))||$.extend([], defaults);
			}
		},
		/**
		 * 保存本地存储
		 * @method _save
		 * @return {undefined} 
		 */
		_save = function(){
			if(Ibos.local.set){
				return Ibos.local.set(storageName, userModule);
			}else{
				return U.setCookie(storageName, $.toJSON(userModule));
			}
		},
		/**
		 * 清除本地存储index_modules的值
		 * @method _clear
		 * @return {undefined} 
		 */
		_clear = function(){
			if(Ibos.local.remove){
				return Ibos.local.remove(storageName);
			}else{
				return U.setCookie(storageName, '', -1);
			}
		};

	userModule = _get();

	return {
		/**
		 * 查询位置
		 * @method indexOf
		 * @param  {String|Number} name 查询的值
		 * @return {Number}      		返回所在位置
		 */
		indexOf: function(name){
			if(typeof name !== "undefined"){
				for(var i = 0, len = userModule.length; i < len; i++){
					if(name === userModule[i].name){
						return i;
					}
				}
				return -1;
			}
		},
		/**
		 * 传入标识名时，用于获取该标识名对应的配置，否则获取整个配置数组
		 * @method get
		 * @param  {String} 	 name 传入所查询的属性
		 * @return {String|Null}      返回所查询的值
		 */
		get: function(name){
			if(typeof name !== "undefined"){
				var index = this.indexOf(name);
				return (index !== -1) ? userModule[index] : null;
			}else{
				return userModule;
			}
		},
		/**
		 * 添加
		 * @method add
		 * @param  {Object} data 传入JSON格式数据
		 */
		add: function(data){
			if(data.name && typeof data.name === "string"){
				// 已存在相同标识符时，添加失败	
				if(this.indexOf(data.name) !== -1){
					$.error("(us.add): 已存在相同的标识符");
					// return false;
				}
				userModule.push(data);
			}
			_save();
		},
		/**
		 * 删除
		 * @method remove
		 * @param  {String}  name 要删除的属性
		 * @return {Boolean}      不存在是返回false
		 */
		remove: function(name){
			var index;
			if(!name){
				return false;
			}
			index = this.indexOf(name);

			(index !== -1) && userModule.splice(index, 1);
			_save();
		},
		/**
		 * 移动
		 * @method  move
		 * @param  {String}  name     要移动的属性
		 * @param  {Number}  newIndex 移动的位置
		 * @return {Boolean}          无参数时返回false
		 */
		move: function(name, newIndex){
			var index;
			if(!name || typeof newIndex === "undefined"){
				return false;
			}
			index = this.indexOf(name);
			if(index !== -1){
				userModule.splice(newIndex, 0, userModule.splice(index, 1)[0]);
			}
			_save();
		},
		/**
		 * 设置
		 * @method set
		 * @param {String} data 传入设置的值
		 */
		set: function(data){
			userModule = data;
			_save();
		},
		/**
		 * 相当于重置
		 * @method  clear
		 */
		clear: function(){
			_clear();
			userModule = _get();
		}
	};
};

/**
 * 模块管理器
 * @method moduleManager
 * @param  {Object} 		$ctrl   传入jquery节点对象
 * @param  {Array}  		data    传入模块管理的数据
 * @param  {Object} 		options 传入JSON格式数据
 * @return {Object|Boolean}         返回相应的方法或者传参错误返回false
 */
var moduleManager = function($ctrl, data, options){
	if(!$ctrl || !$ctrl.length || !$.isArray(data)){
		return false;
	}
	options = options || {};

	var _create = function(modData){
		return $.tmpl("tpl_manager", { data: modData });
	};
	var _trigger = function(callback/*args*/){
		var args = Array.prototype.slice.call(arguments, 1);
		if($.isFunction(callback)){
			callback.apply(null, args);
		}
	};


	// 创建管理器
	var $manager = _create(data);
	// 默认隐藏，初化checkbox样式
	$manager.hide().insertAfter($ctrl).find(".checkbox input").label();

	$manager.on("change", "input[type='checkbox']", function(){
		var checked = $.prop(this, "checked"),
			name = this.value;

		_trigger(options.onchange, name, checked);

	}).on("click", "[data-act='reset']", function(){
		_trigger(options.onreset);
	});

	return {
		/**
		 * 显示模块管理器弹窗
		 * @method show
		 */
		show: function(){
			Ui.dialog({
				id: "mod_manager",
				title: false,
				content: $manager[0],
				padding: 0,
				lock: true,
				skin: "in-dialog"
			});
		},
		/**
		 * 隐藏模块管理器弹窗
		 * @method hide
		 */
		hide: function(){
			Ui.closeDialog("mod_manager");
		},
		/**
		 * 单选按钮选中是添加样式
		 * @method check
		 * @param  {String} name 传入输入框的值
		 */
		check: function(name){
			$manager.find("input[value='" + name + "']").label("check")
			.parent().parent().addClass("active");
		},
		/**
		 * 按需按钮取消时去除样式
		 * @method unCheck
		 * @param  {String} name 传入输入框的值
		 */
		unCheck: function(name){
			$manager.find("input[value='" + name + "']").label("uncheck")
			.parent().parent().removeClass("active");
		}
	};

};


var indexModule = {};

/**
 * 首页模块管理加载
 * @method load
 * @param  {String}   url        数据访问地址
 * @param  {Object}   param      传入JSON格式数据
 * @param  {Function} [callback] 回调函数
 */
indexModule.load = function(url, param, callback){
	var _setTabContent = function(mod, data){
		for(var tabName in data){
			if(data.hasOwnProperty(tabName)){
				mod.setContent(data[tabName], tabName);
			}
		}
	};

	var _delegate = function(data){
		if(data){
			// 循环模块数据
			for(var modName in data){

				if(data.hasOwnProperty(modName)){
					var mod = MBox.get(modName);
					var modData = data[modName];

					if(mod && modData){
						// 写入模块内tab项数据
						_setTabContent(mod, modData);
						($.isFunction(callback)) && callback(modName, mod.$container);
					}
				}
			}
		}
	};
	
	$.ajax({
		url: url,
		data: param,
		cache: false,
		dataType: 'json',
		success: function(res){
			_delegate(res);
		}
	});
};

/**
 * 模块面板
 * @method modulePanel
 * @param  {Object} 		$wrap 传入jquery节点对象
 * @param  {String} 		data  传入数据
 * @return {Object|Boolean}       返回相应的方法或者参数错误时返回false
 */
var modulePanel = function($wrap, data){
	if(!$wrap || !$wrap.length){
		return false;
	}

	return {
		/**
		 * name用于获取默认设置,options用于扩展设置
		 * @method add
		 * @param {String}   name     	传入添加的属性 
		 * @param {Object}   options  	传入JSON格式数据
		 * @param {Function} [callback] 回调函数
		 */
		add: function(name, options, callback){
			if(nameIndexOf(name, data) === "-1"){
				$.error("(addModule): " + Ibos.l("MAIN.MODULE_NOT_FOUND", { modname: name}));
			}

			// 生成MBox实例并插入到容器中
			var mod = new MBox(name, options);
			mod.appendTo($wrap);
			
			if($.isFunction(callback)){
				callback(name, mod.$container);
			}
		},
		/**
		 * name用于获取默认设置,options用于扩展设置
		 * @method remove
		 * @param {String}   name     	传入删除的属性 
		 * @param {Function} [callback] 回调函数
		 */
		remove: function(name, callback){
			if(nameIndexOf(name, data) === "-1"){
				$.error("(removeModule): " + Ibos.l("MAIN.MODULE_NOT_FOUND", { modname: name}));
			}
			//移除模块时，从本地缓存中删除
			var mod = MBox.get(name);
			mod && mod.remove();
			if($.isFunction(callback)){
				callback(name);
			}
		}
	};
};

/**
 * 常用菜单消息数目提醒
 * @Method menuBubble
 * @param  {Object} $ctx 传入Jquery节点对象
 * @return {Object}      返回相应的方法
 */
var menuBubble = function($ctx){
	if(!$ctx || !$ctx.length){
		$ctx = $(document.body);
	}

	/**
	 * 设置消息数目提醒
	 * @method set
	 * @param {String} 		  name  设置菜单的名称
	 * @param {String|Number} count 传入消息数目
	 */
	var _set = function(name, count){
		count = parseInt(count, 10);
		var ctx = $ctx.find("[data-bubble='" + name.split("/")[1] + "']");
		if(count){
			ctx.text(count).show();					
		}else{
			ctx.empty().hide();					
		}
	};

	return {
		/**
		 * 设置消息数目提醒
		 * @method set
		 * @param {String} 		  name  设置菜单的名称
		 * @param {String|Number} count 传入消息数目
		 */
		set: function(name, count){

			var type = typeof name;
			// 当为对象时，假设为键值对
			if(type === "object"){
				for(var prop in name){
					if(name.hasOwnProperty(prop)){
						_set(prop, name[prop]);
					}
				}
			} else if(type === "string"){
				_set(name, count);
			}
		},
		/**
		 * 加载
		 * @method set
		 * @param {String}   url   		传入发送地址
		 * @param {Object}   count 		传入消息数目
		 * @param {Function} [callback] 回调函数
		 */
		load: function(url, param, callback){
			var that = this;
			$.ajax({
				url: url,
				data: param,
				dataType: 'json',
				cache: true,
				success: function(res){
					res && that.set(res);	

					if($.isFunction(callback)){
						callback(res);
					}
				}
			});
		}
	};
};

var In = {
	startIntro: function(){
		// 新手引导
		setTimeout(function(){
			Ibos.guide("index", function() {
				var guideData = [];

				if($("#module_panel .mbox").length){
					guideData.push({ 
						element: "#module_panel .mbox[data-name]", 
						intro: U.lang("MAIN.INTRO.MOD_DRAG"),
						position: "right"
					});
				}
				return guideData;
			});
		}, 1000);
	}
};

// 特殊模块快捷方式入口
var moduleEntry = (function(){
	var _entry = {
		workflow: function(id, title){
			if(!id) return false;
			title = title || "";
			var url = Ibos.app.url("workflow/new/add", { flowid: id });

			Ui.ajaxDialog(url, {
				id: "d_wf_entry",
				title: title,
				ok: function(){
					var formData = this.DOM.content.find("form").serializeArray();

					MainIndex.op.moduleEntry(url, formData).done(function(res){
						if(res.isSuccess){
							window.location.href = res.jumpUrl;
						}
					});

					return false;
				},
				cancel: true
			});
		}
	};

	return function (mod) {
		var args;
		if(mod && mod in _entry) {
			args = Array.prototype.slice.call(arguments, 1);
			return _entry[mod].apply(_entry, args);
		} else {
			Ui.tip( Ibos.l("MAIN.MODULE_ENTRY_NOT_FOUND"), "warning");
		}
	};
})();

$(function(){
	// 初始化常用菜单设置弹窗拖拽功能
	var initMenuSort = function(){
		// 常用菜单设置
		$(".in-outmenu-list").sortable({
			helper: "clone",
			connectWith: ".in-inmenu-list li",
			cursor: "move",
			tolerance: "pointer"
		});

		$(".in-inmenu-list li").sortable({
			helper: "clone",
			cursor: "move",
			connectWith: ".in-inmenu-list li, .in-outmenu-list",
			over: function(evt){ $(evt.target).addClass("hover"); },
			out: function(evt){ $(evt.target).removeClass("hover"); },
			receive: function(evt, data){
				var $item = $(evt.target).find(".in-menu-item");
				if($item.length){
					$item.each(function(i, elem){
						data.item.siblings().appendTo(data.sender);
					});
				}
			}
		});
	};


	Ibos.evt.add({
		//	打开管理器
		"openManager": function(){
			manager.show();
		},
		// 关闭管理器
		"closeManager": function(){
			manager.hide();
		},
		// 返回顶部
		"totop": function(){
			Ui.scrollToTop();
		},
		// 点击添加至常用菜单
		"addToCommonMenu": function(param, elem){
			var $item = $(elem).closest(".in-menu-item");
			$(".in-inmenu-list li").each(function(){
				if(!$(this).children().length){
					$(this).append($item);
					return false;
				}
			});
		},
		// 从常用菜单中移除
		"removeFromCommonMenu": function(param, elem){
			var $item = $(elem).closest(".in-menu-item");
			$item.appendTo(".in-outmenu-list");
		},
		// 恢复默认菜单设置
		"restoreDefaultMenu": function(param, elem){
			param = {restoreMenu: '1'};
			MainIndex.op.restoreDefaultMenu(param).done(function(res){
				(res.isSuccess)&& window.location.reload();

			});
		},
		// 设置为默认菜单
		"setDefaultMent": function(param, elem){
			var mod = [];
			$(".in-inmenu-list .in-menu-item").each(function(){
				var data = $(this).data();
				mod.push(data.mod);
			});
			param = {commonMenu: '1', mod: mod};

			MainIndex.op.setDefaultMent(param).done(function(res){
				if(res.isSuccess){
					Ui.tip(U.lang("OPERATION_SUCCESS", 'success'));
				}
			});
			
		},
		// 设置常用菜单
		setupMenu: function(){
			//取消常用菜单提示的操作
			var hasNew = $("#menu_new_tip").length;
			if(hasNew){
				var param = {guide: 1},
					url = "";
				$.post(url, param, function(res){
					isSuccess && $("#menu_new_tip").hide();
				});
			}

			var showDialog = function(){
				Ui.dialog({
					id: "in_mu_dialog",
					title: false,
					content: document.getElementById("in_mu"),
					padding: 0,
					top: 120,
					skin: "in-dialog",
					lock: true,
					init: function(){
						// 缓存当前菜单配置
						initMenuSort();
						$(this).data("inmenu", this.DOM.content.find(".in-inmenu-list").html());
						$(this).data("outmenu", this.DOM.content.find(".in-outmenu-list").html());
					},
					close: function(){
						// 取消时还原至一开始的菜单配置
						this.DOM.content.find(".in-inmenu-list").html($(this).data("inmenu"));
						this.DOM.content.find(".in-outmenu-list").html($(this).data("outmenu"));
					}
				});
			};
			if($(document).scrollTop() == 0) {
				showDialog();
			} else {
				Ui.scrollToTop(showDialog);
			}
		},
		// 保存常用目录
		saveCommonMenu: function(){
			var html = "",
				mod = [];
			$(".in-inmenu-list .in-menu-item").each(function(){
				var data = $(this).data();
				html += $.template("mu_item_tpl", data);
				mod.push(data.mod);
			});
			
			$(".cm-menu-list").html(html);
			var param = {personalMenu: '1', mod: mod};
			
			MainIndex.op.saveCommonMenu(param).done(function(res){
				var dialog = Ui.getDialog("in_mu_dialog");
				$(dialog).removeData("inmenu outmenu");
				dialog.close();
			});	
		}
	});
});




