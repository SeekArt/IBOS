/**
 * MBox
 * @version $Id$
 */

var MBox = function(name, settings) {
	if(!name){
		$.error("(MBox): 未定义模块标识名");
	}
	if(this.constructor.indexOf(name) !== -1){
		$.error("(MBox): 该模块标识名已存在")
	}
	this.name = name;
	this.settings = $.extend({}, MBox.defaults, settings);
	this.constructor.instances.push(this);
	this._init();
}
MBox.defaults = {
	title: "",
	style: "",
	content: "",
	url: "",
	param: null,
	tab: [],
	autoLoad: false,
	autoLoadSpeed: 5000,
	loadOnce: true
	// onload
	// onclose
	// oncontent
}

MBox.instances = [];
// 检查标识名是否已存在
MBox.indexOf = function(name){
	for(var i = 0, len = this.instances.length; i < len; i++){
		if(this.instances[i].name === name){
			return i;
		}
	}
	return -1;
};
// 删除实例
MBox.remove = function(name){
	var index = this.indexOf(name);
	if(index !== -1){
		this.instances.splice(index, 1);
	}
}
// 获取实例
MBox.get = function(name){
	var index = this.indexOf(name);
	if(index !== -1){
		return this.instances[index];
	}
}
MBox.prototype = {
	constructor: MBox,
	_init: function(){
		this.$container = this._createContainer();
		this._bindEvent();
		this.active = "";
	},

	_createContainer: function(){
		var settings = this.settings,
			$ct = $('<div class="mbox ' + settings.style + '" data-name="' + settings.name + '">');

		this.$header = this._createHeader();
		this.$body = this._createBody();

		return $ct.append(this.$header, this.$body);
	},

	_createHeader: function(){
		var that = this,
			$hd = $('<div class="mbox-header"></div>'),
			$title,
			$close;

		// 创建标题
		if(this.settings.title !== ""){
			$title = $('<h4>' + this.settings.title + '</h4>');
			$hd.append($title);
		}

		// 如果配置了tab，则创建tab导航项
		if(this.settings.tab.length > 0){
			this.$tabs = this._createTab(this.settings.tab);
		} else {
			this.$tabs = this._createTab([{}]);
		}
		// 当只有一个tab项时，隐藏$tabs节点
		if(this.settings.tab.length <= 1){
			this.$tabs.hide();
		}
		$hd.append(this.$tabs);
		// 创建删除按钮
		$close = $('<a href="javascript:;" class="o-close-simple"></a>');
		$close.click(function(){
			that.remove();
		}).appendTo($hd);

		return $hd;
	},

	_createBody: function(){
		var $bd = $('<div class="mbox-body"></div>');

		// 如果配置了tab，创建tab内容
		if(this.settings.tab.length > 0){
			this.$tabContents = this._createTabContent(this.settings.tab);
		} else{
			this.$tabContents = this._createTabContent([{}]);
		}
		$bd.append(this.$tabContents);

		return $bd;
	},

	_createTab: function(data){
		var $tabs,
			tpl,
			name,
			icon,
			title;

		$tabs = $('<ul class="nav nav-skid"></ul>');

		for(var i = 0, len = data.length; i < len; i++){

			// 若tab项内没配置name, icon, title等属性，则直接使用全局配置中的
			name = data[i].name || this.settings.name || "";
			title = data[i].title || this.settings.title || "";
			icon = data[i].icon || "";

			if(name){
				tpl = '<li data-tab-name="' + name + '"> <a href="javascript:;">' +
					'<span class="' + icon + '"></span>' + title + '</a></li>';
				$tabs.append(tpl)
			}
		}
		
		return $tabs;
	},

	_createTabContent: function(data){
		var $tabContents = $('<div></div>'),
			name;

		for(var i = 0, len = data.length; i < len; i++){

			name = data[i].name || this.settings.name || "";
			if(name){
				$tabContents.append('<div data-tab-name="' + name + '"></div>')
			}
		}

		return $tabContents;
	},

	_bindEvent: function(){
		var that = this;
		this.$tabs.on("click", "li", function(){
			var tabName = $.attr(this, "data-tab-name");
			that.tab(tabName);
		})
	},

	_getTabSettings: function(name/*index*/){
		var tab = this.settings.tab,
			len = tab.length,
			setting = {};


		if(len){
			// 若未传参，则直接获取当前活动状态的tab组的配置
			if(typeof name === "undefined"){
				name = this.active;
			}
			// 按索引取配置项
			if(typeof name === "number"){
				setting = tab[name]
			} else if(typeof name === "string") {
			// 按标识名取
				for(var i = 0; i < len; i++){
					if(tab[i].name === name){
						setting = tab[i];
						break;
					}
				}
			}
		}
		return setting;
	},
	// 使用此方法会取得当前活动的tab组对象，$tab指向tab项的
	// $tabContent指向tab内容
	_getTab: function(name/*index*/){
		var $tabItems = this.$tabs.find("li"),
			$tabContentItems = this.$tabContents.find("div"),
			$tab,
			$tabContent;

		// 若未传参，则直接获取当前活动状态的tab组
		if(typeof name === "undefined"){
			name = this.active;
		}

		if(typeof name === "number"){

			$tab = $tabItems.eq(name);
			$tabContent = $tabContentItems.eq(name);

		} else if(typeof name === "string"){

			$tab = $tabItems.filter('[data-tab-name="' + name + '"]');
			$tabContent = $tabContentItems.filter('[data-tab-name="' + name + '"]');

		}
		return {
			$tab: $tab,
			$tabContent: $tabContent
		};

	},

	// 获取属性值时，tab项的配置，再查找全局配置，没有时返回undefined
	// name指定要获取哪个tab项的配置，不传入时，默认当前活动的tab项
	_getSetting: function(prop, name){
		var val = this._getTabSettings(name||this.active)[prop];
		return typeof val !== "undefined" ? val : this.settings[prop];
	},
	// 设置内容，当有format参数时，content要经过format的初始化
	// name指定要获取哪个tab项的配置，不传入时，默认当前活动的tab项
	setContent: function(content, name){
		content = (typeof content !== "undefined") ? content : this._getSetting("content", name);

		var format = this._getSetting("format", name),
			tab = this._getTab(name);

		// 定义了format时，用format格式化传入的内容，返回字符串
		if($.isFunction(format)){
			content = format.call(this, content, this.active)
		}

		tab.$tabContent.html(content);
		this._trigger(this._getSetting("oncontentchange"), tab.$tabContent);
		
	},

	tab: function(name/*index*/){
		var that = this,
			loadOnce,
			onload,
			activeTab;
		// 记录当前活动状态的tab的标识符
		this.active = name;
		activeTab = this._getTab(name);
		// 如果使用序号，则将其转化为标识符
		if(typeof this.active === "number"){
			this.active = activeTab.$tab.attr("data-tab-name");
		}

		// 切换Tab
		if(activeTab.$tab){
			activeTab.$tab.addClass("active").siblings().removeClass("active");
		}
		if(activeTab.$tabContent){
			activeTab.$tabContent.show().siblings().hide();
		}


		loadOnce = this._getSetting("loadOnce");
		onload = this._getSetting("onload");

		clearInterval(this._autoLoadTimer);

		var _triggerOnload = function(res){
			that._trigger(onload, res, this.active, activeTab.$tabContent, activeTab.$tab)
		}

		// loadOnce为true时，作缓存，并不再每次Tab都触发读取
		// 与自动读取不相斥
		if(loadOnce){
			if(!activeTab.$tab.data("load")){
				this._load(_triggerOnload);
				activeTab.$tab.data("load", true);
			}
		}else{
			this._load(_triggerOnload);
		}

	},

	_load: function(callback){
		var that = this,	
			setting = this._getTabSettings(),
			autoLoad,
			autoLoadSpeed,
			_autoload;

		autoLoad = this._getSetting("autoLoad");
		autoLoadSpeed =  this._getSetting("autoLoadSpeed");


		if(setting.url){
			that.load(setting.url, setting.param, function(res){
				that._trigger(callback, res)
			});
		}

		if(autoLoad && autoLoadSpeed) {
			_autoload = function(){
				// 定时自动刷新内容
				that._autoLoadTimer = setTimeout(function(){

					that.load(setting.url, setting.param, function(res){
						that._trigger(callback, res);
						_autoload();
					})

				}, autoLoadSpeed)
			}
			_autoload();
		}
	},

	load: function(url, param, callback){
		var that = this,
			content;

		url = url || this._getSetting("url");

		// 未定义url时使用默认内容
		if(!url){
			content = this._getSetting("content");
			this.setContent(content);
		}else{
			this.$body.stopWaiting().waitingC();
		
			param = param || this._getSetting("param");
			callback = callback || this._getSetting("callback");
			$.ajax({
				url: url,
				type: 'get',
				data: param,
				dataType: 'json',
				cache: false,
				success: function(res){
					that.$body.stopWaiting();
					// 当有返回数据时，将返回数据作为content，否则使用默认content
					if(!res){
						that.setContent(content);
					} else{
						that.setContent(res);
					}
					that._trigger(callback, res);
				}
			});
		}
	},

	remove: function(){
		var that = this;
		// 从Dom删除
		this.$container.fadeOut(100, function(){
			that.$container.remove();
			// 从实例类数组中删除
			that.constructor.remove(that.name)
			// 清除自动刷新定时器
			clearTimeout(that._autoLoadTimer);
			that._trigger(that.settings.onremove, that.name);
		});
	},

	appendTo: function(target){

		this.$container.hide().appendTo(target).fadeIn(100);
		// 插入文档后自动选中第一个tab项
		this.tab(0);

		return this.$container;
	},

	_trigger: function(callback/*args*/){

		var args = Array.prototype.slice.call(arguments, 1);

		if($.isFunction(callback)){
			callback.apply(this, args);
		}

	}
}
