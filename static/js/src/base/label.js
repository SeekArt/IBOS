/* checkbox radio初始化 */
(function(window, $) {
	/**
	 * checkbox和radio的美化
	 * @class  Label
	 * @param  {Jquery} $el 目标元素
	 * @return {Object}     Label实例
	 */
	var Label = function($el) {
		var type = $el.attr("type");
		if(!type || (type !== "radio" && type !== "checkbox")) {
			throw new Error('初始化类型必须为"checkbox"或"radio"');
		}
		this.$el = $el;
		this.type = type;
		this.name = $el.attr("name");
		Label.items.push(this);
		this._initLabel();
		this._bindEvent();
	}
	/**
	 * 已初始化项的集合
	 * @type {Array}
	 */
	Label.items = [];
	Label.get = function(filter){
		var ret = [];
		for(var i = 0; i < this.items.lenght; i++) {
			if(filter && filter.call(this, this.items[i])) {
				ret.push(this.items[i]);
			}
		}
		return ret;
	}

	Label.prototype = {
		constructor: Label,
		/**
		 * 初始化checkbox和radio的容器
		 * @method _initLabel
		 * @private 
		 * @chainable
		 * @return {Object} 当前调用对象
		 */
		_initLabel: function() {
			var type = this.type,
				//向上查找css类名和type相同的节点
				$label = this.$el.parents('label.' + type).first();
			//如果不存在目标或该目标元素类型不为'label', 则创建;
			if(!$label.length){
				$label = $('<label>').addClass(type);
				$label.append(this.$el);
			}
			//加入作为样式表现的html
			$label.prepend('<span class="icon"></span><span class="icon-to-fade"></span>');
			this.$label = $label;
			this.refresh();
			return this;
		},
		_refresh: function(){
			this.$el.is(':checked') ? this.$label.addClass('checked') : this.$label.removeClass('checked');
			this.$el.is(':disabled') ? this.$label.addClass('disabled') : this.$label.removeClass('disabled');
		},

		refresh: function(){
			if(this.type === "radio") {
				var items = this.constructor.items;
				for(var i = 0, len = items.length; i < len; i++) {
					if(items[i].name === this.name && items[i].type === this.type) {
						items[i]._refresh();
					}
				}
			} else {
				this._refresh();
			}
		},

		/**
		 * 事件绑定
		 * @method _bindEvent
		 * @private
		 * @chainable
		 * @return {Object} 当前调用对象
		 */
		_bindEvent: function(){
			var that = this;
			this.$el.on('change', function(){
				that.refresh();
			})
		},
		check: function(){
			this.$el.prop('checked', true);
			this.refresh()
		},
		uncheck: function(){
			this.$el.prop('checked', false);
			this.refresh()
		},
		disable: function(){
			this.$el.prop('disabled', true);
			this.$label.addClass('disabled');
		},
		enable: function(){
			this.$el.prop('disabled', false);
			this.$label.removeClass('disabled');
		},
		toggle: function(){
			if(this.$el.prop('checked')) {
				this.uncheck();
			} else {
				this.check();
			}
		},
		toggleDisabled: function(){
			if(this.$el.prop('disabled')) {
				this.enable();
			} else {
				this.disable();
			}
		}
	}

	$.fn.label = function(option){
		var data;
		return this.each(function(){
			data = $(this).data('label');
			if(!data){
				$(this).data('label', data = new Label($(this)));
			}
			if(typeof option === 'string'){
				data[option].call(data);
			}
		})
	}
	$.fn.label.Constructor = Label;

	$(function(){
		$('.checkbox input, .radio input').label();
	})
})(window, window.jQuery);