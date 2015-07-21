/**
 * index.js
 * 前台首页
 * IBOS
 * @author		inaki
 * @version		$Id: index.js 4192 2014-09-23 00:33:10Z gzljj $
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

function nameIndexOf(name, arr){
	for(var i = 0, len = arr.length; i < len; i++){
		if(arr[i].name === name){
			return i;
		}
	}
	return -1;
}

// 用户自定义配置，可定义模块是否显示及显示顺序
// 配置存储操作
var moduleStorage = function(defaults){
	var userModule, // 从缓存得到
		storageName = "index_modules",
		_get = function(){
			if(Ibos.local.get){
				return Ibos.local.get(storageName)||$.extend([], defaults);
			}else{
				return $.parseJSON(U.getCookie(storageName))||$.extend([], defaults);
			}
		},
		_save = function(){
			if(Ibos.local.set){
				return Ibos.local.set(storageName, userModule);
			}else{
				return U.setCookie(storageName, $.toJSON(userModule));
			}
		},
		_clear = function(){
			if(Ibos.local.remove){
				return Ibos.local.remove(storageName);
			}else{
				return U.setCookie(storageName, '', -1);
			}
		}

	userModule = _get();

	return {
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
		// 传入标识名时，用于获取该标识名对应的配置，否则获取整个配置数组
		get: function(name){
			if(typeof name !== "undefined"){
				var index = this.indexOf(name);
				if(index !== -1){
					return userModule[index]
				}else{
					return null;
				}
			}else{
				return userModule;
			}
		},
		add: function(data){
			if(data.name && typeof data.name === "string"){
				// 已存在相同标识符时，添加失败	
				if(this.indexOf(data.name) !== -1){
					$.error("(us.add): 已存在相同的标识符")
					// return false;
				}
				userModule.push(data)
			}
			_save();
		},
		remove: function(name){
			var index;
			if(!name){
				return false;
			}
			index = this.indexOf(name);
			if(index !== -1){
				userModule.splice(index, 1);
			}
			_save();
		},
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
		set: function(data){
			userModule = data;
			_save();
		},
		// 相当于重置
		clear: function(){
			_clear();
			userModule = _get();
		}
	}
}

// 模块管理器
var moduleManager = function($ctrl, data, options){
	if(!$ctrl || !$ctrl.length || !$.isArray(data)){
		return false;
	}
	options = options || {};

	var _create = function(modData){
		return $.tmpl("tpl_manager", { data: modData })
	}
	var _trigger = function(callback/*args*/){
		var args = Array.prototype.slice.call(arguments, 1);
		if($.isFunction(callback)){
			callback.apply(null, args);
		}
	}


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
	})

	return {
		show: function(){
			Ui.dialog({
				id: "mod_manager",
				title: false,
				content: $manager[0],
				padding: 0,
				lock: true,
				skin: "in-dialog"
			})
			// menu.show();
		},
		hide: function(){
			Ui.closeDialog("mod_manager");
		},
		check: function(name){
			$manager.find("input[value='" + name + "']").label("check")
			.parent().parent().addClass("active");
		},
		unCheck: function(name){
			$manager.find("input[value='" + name + "']").label("uncheck")
			.parent().parent().removeClass("active");
		}
	}

}


var indexModule = {};

indexModule.load = function(url, param, callback){
	var _setTabContent = function(mod, data){
		for(var tabName in data){
			if(data.hasOwnProperty(tabName)){
				mod.setContent(data[tabName], tabName);
			}
		}
	}

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
						if($.isFunction(callback)){
							callback(modName, mod.$container);
						}
					}
				}
			}
		}
	}
	
	$.ajax({
		url: url,
		data: param,
		cache: false,
		dataType: 'json',
		success: function(res){
			_delegate(res);
		}
	})
}


var modulePanel = function($wrap, data){
	if(!$wrap || !$wrap.length){
		return false;
	}

	return {
		// name用于获取默认设置,options用于扩展设置
		add: function(name, options, callback){
			if(nameIndexOf(name, data) === "-1"){
				$.error("(addModule): " + Ibos.l("MAIN.MODULE_NOT_FOUND", { modname: name}));
			}

			// 生成MBox实例并插入到容器中
			
			var mod = new MBox(name, options);
			mod.appendTo($wrap);
			
			if($.isFunction(callback)){
				callback(name, mod.$container)
			}
		},
		remove: function(name, callback){
			if(nameIndexOf(name, data) === "-1"){
				$.error("(removeModule): " + Ibos.l("MAIN.MODULE_NOT_FOUND", { modname: name}));
			}
			//移除模块时，从本地缓存中删除
			var mod = MBox.get(name);
			mod && mod.remove();
			if($.isFunction(callback)){
				callback(name)
			}
		}
	}
}

// 常用菜单消息数目提醒 
var menuBubble = function($ctx){
	if(!$ctx || !$ctx.length){
		$ctx = $(document.body);
	}

	var _set = function(name, count){
		count = parseInt(count, 10);
		if(count){
			$ctx.find("[data-bubble='" + name + "']").text(count).show();					
		}else{
			$ctx.find("[data-bubble='" + name + "']").empty().hide();					
		}
	}

	return {
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
			})
		}
	}
}

Ibos.evt.add({
	"openManager": function(){
		manager.show()
	},
	"closeManager": function(){
		manager.hide();
	},
	"totop": function(){
		Ui.scrollToTop();
	}
})

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

				// guideData.push({
				// 	element: "#manager_ctrl",
				// 	intro: U.lang("MAIN.INTRO.MOD_SETTING"),
				// 	position: "left"
				// })

				return guideData;
			})
		}, 1000);
	}
}

// In.startIntro();

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
					})
				}
			}
		});
	}

	Ibos.evt.add({
		// 点击添加至常用菜单
		"addToCommonMenu": function(param, elem){
			var $item = $(elem).closest(".in-menu-item");
			$(".in-inmenu-list li").each(function(){
				if(!$(this).children().length){
					$(this).append($item);
					return false;
				}
			})
		},
		// 从常用菜单中移除
		"removeFromCommonMenu": function(param, elem){
			var $item = $(elem).closest(".in-menu-item");
			$item.appendTo(".in-outmenu-list")
		},
		// 恢复默认菜单设置
		"restoreDefaultMenu": function(param, elem){
			$.post(Ibos.app.url('main/default/restoremenu'), {restoreMenu: '1'}, function(res){
				if(res.isSuccess){
					window.location.reload();
				}
			}, 'json');
		},
		// 设置为默认菜单
		"setDefaultMent": function(param, elem){
			var mod = [];
			$(".in-inmenu-list .in-menu-item").each(function(){
				var data = $(this).data();
				mod.push(data.mod);
			});
			$.post(Ibos.app.url('main/default/commonmenu'), {commonMenu: '1', mod: mod}, function(res){
				if(res.isSuccess){
					Ui.tip(U.lang("OPERATION_SUCCESS", 'success'));
				}
			}, 'json');
			
		},
		// 设置常用菜单
		setupMenu: function(){
			//取消常用菜单提示的操作
			var hasNew = $("#menu_new_tip").length;
			if(hasNew){
				var param = {guide: 1},
					url = "";
				$.post(url, param, function(res){
					if(isSuccess){
						$("#menu_new_tip").hide();
					}
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
			}
			if($(document).scrollTop() == 0) {
				showDialog();
			} else {
				Ui.scrollToTop(showDialog);
			}
		},

		saveCommonMenu: function(){
			var html = "",
				mod = [];
			$(".in-inmenu-list .in-menu-item").each(function(){
				var data = $(this).data();
				html += $.template("mu_item_tpl", data);
				mod.push(data.mod);
			});
			
			$(".cm-menu-list").html(html);

			$.post(Ibos.app.url('main/default/personalmenu'), {personalMenu: '1', mod: mod}, function(res){
				var dialog = Ui.getDialog("in_mu_dialog");
				$(dialog).removeData("inmenu outmenu");
				dialog.close();
			}, 'json');	
		}
	})
})


// 特殊模块快捷方式入口
var moduleEntry = (function(){
	var _entry = {
		workflow: function(id, title){
			if(!id) return false;
			title = title || ""
			var url = Ibos.app.url("workflow/new/add", { flowid: id });

			Ui.ajaxDialog(url, {
				id: "d_wf_entry",
				title: title,
				ok: function(){
					var formData = this.DOM.content.find("form").serializeArray();
					$.post(url, formData, function(res){
						if(res.isSuccess){
							window.location.href = res.jumpUrl
						}
					});
					return false;
				},
				cancel: true
			})
		}
	};

	return function (mod) {
		var args;
		if(mod && mod in _entry) {
			args = Array.prototype.slice.call(arguments, 1);
			return _entry[mod].apply(_entry, args);
		} else {
			Ui.tip("未找到模块入口", "warning")
		}
	} 
})();



