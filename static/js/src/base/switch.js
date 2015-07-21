//$.fn.switch
//开关初始化
(function(){
	/**
	 * 初始化开关的类
	 * @class  Switch
	 * @param  {Element|Jquery} element 要初始化的元素，必须为input:checkbox元素
	 * @param  {Key-Value}      options [配置，目前未定义]
	 * @return {Object}         switch实例对象
	 */
	var Switch = function(element, options){
		this.$el = $(element);
		this.options = options;
		this.init();
	}
	Switch.prototype = {
		constructor: Switch,
		/**
		 * 初始化函数
		 * @method init
		 * @private
		 */
		init: function(){
			var $el = this.$el,
				cls = "toggle",
				isChecked = $el.prop('checked'),
				isDisabled = $el.prop('disabled');
			!isChecked && (cls += " toggle-off");
			isDisabled && (cls += " toggle-disabled");
			// $el.remove();
			this.toggle = $el.wrap('<label class="'+ cls +'"></label>').parent();
			// 将input的title属性赋予容器label
			this.toggle.attr('title', $el.attr('title'));
			if(!isDisabled){
				this._bindEvent();
			}
		},
		/**
		 * 事件绑定
		 * @method bindEvent
		 * @private
		 * @chainable
		 * @return {Object}        当前调用对象
		 */
		_bindEvent: function(){
			var that = this;
			this.$el.off("change.switch").on("change.switch", function(){
				if(!this.checked) {
					that.toggle.addClass("toggle-off");
				}else{
					that.toggle.removeClass("toggle-off");
				}
			})
			return this;
		},
		/**
		 * 事件解绑
		 * @method unbindEvent
		 * @private
		 * @chainable
		 * @return {Object}        当前调用对象
		 */
		_unbindEvent: function(){
			this.$el.off("change.switch");
			return this;
		},
		/**
		 * 打开开关
		 * @method turnOn
		 * @chainable
		 * @param  {Function} call 回调函数
		 * @return {Object}        当前调用对象
		 */
		turnOn: function(call){
			this.$el.prop("checked", true).trigger("change");
		},
		/**
		 * 关闭开关
		 * @method turnOff
		 * @chainable
		 * @param  {Function} call 回调函数
		 * @return {Object}        当前调用对象
		 */
		turnOff: function(){
			this.$el.prop("checked", false).trigger("change");
		},
		/**
		 * 禁用开关
		 * @method setDisabled
		 * @chainable
		 * @param  {Function} call 回调函数
		 * @return {Object}        当前调用对象
		 */
		setDisabled: function(call){
			this.toggle.addClass("toggle-disabled");
			this.$el.prop("disabled", true);
			this._unbindEvent()
			return this;
		},
		/**
		 * 启用开关
		 * @method setDisabled
		 * @chainable
		 * @param  {Function} call 回调函数
		 * @return {Object}        当前调用对象
		 */
		setEnabled: function(call){
			this.toggle.removeClass("toggle-disabled");
			this.$el.prop("disabled", false);
			this._bindEvent()
			return this;
		}
	}
	/**
	 * @class $.fn
	 */
	/**
	 * 初始化开关，类Switch的入口
	 * @method $.fn.iSwitch
	 * @param  {String|Object} option Switch方法名或配置（配置目前不可用
	 * @param  {Any}           [any]  传入Switch方法的参数，类型，长度不限
	 * @return {Jquery}        jq数组
	 */
	$.fn.iSwitch = function(option/*,...*/){
		var argu = Array.prototype.slice.call(arguments, 1);
		return this.each(function(){
			var data = $(this).data("switch");
			if(!data||!(data instanceof Switch)){
				$(this).data("switch", data = new Switch($(this), option));
			}
			if(typeof option === "string"){
				data[option] && data[option].apply(data, argu);
			}
		})
	}
	//全局调用
	$(function(){
		$('[data-toggle="switch"]').iSwitch();
	})
})();
