/**
 * message.js
 * 消息
 * IBOS
 * Message
 * @author		inaki
 * @version		$Id$
 */

// 消息模块命名空间
var Msg = {
	/**
	 * 切换回复
	 * @method toggleReply
	 * @param  {Object} $ctrl     传入Jquery节点对象
	 * @param  {Object} $textarea 传入textareaJquery节点对象
	 */
	toggleReply: function($ctrl, $textarea) {
		var selector = $ctrl.attr("data-target"),
			actData = $ctrl.attr("data-act-data"),
			param = U.getUrlParam(actData);

		$ctrl.toggleClass("focus");
		$(selector).toggleClass("msg-box-open");

		if (param["reply_name"]) {
			$textarea.val( Ibos.l("REPLY") +" @" + param["reply_name"] + " ：").trigger("change");
		} else {
			$textarea.val("").trigger("change");
		}
	},
	/**
	 * 发送回复
	 * @method submitReply
	 * @param  {String}   url        传入地址
	 * @param  {Object}   param      传入JSON格式数据
	 * @param  {Function} [callback] 回调函数
	 */
	submitReply: function(url, param, callback) {
		$.post(url, param, function(result) {
			// 重置input
			$.isFunction(callback) && callback(result);
		},'json');
	},
	/**
	 * 删除时间线的信息
	 * @method removeTimelineItem
	 * @param  {Object} $item 传入Jquery节点对象
	 */
	removeTimelineItem: function($item) {
		var msgBoxSelector = ".msg-box",
				// 邻节点中，有1个.msg-box节点表示日期时间，
				// 所以当.msg-box大于1时，才表明有邻级
				hasSiblings = $item.siblings(msgBoxSelector).length > 1;
		if (hasSiblings) {
			$item.remove();
		} else {
			$item.parent().remove();
		}
	},
	/**
	 * 批量模式
	 * @method multipleMode
	 * @param  {Object} $list 传入Jquery节点对象
	 */
	multipleMode: function($list){
		var cls = "msg-multiple";
		return {
			start: function(){
				$list.addClass(cls);
			},
			stop: function(){
				$list.removeClass(cls);
			}
		};
	}
};

// 统计剩余字数
// @deprecated 此方法由 $.fn.charcount 方法替换
var WordCounter = function($el, $display, options) {
	var data;
	if (data == $el.data("wordCounter")) {
		return data;
	}
	$el.data("wordCounter", this);
	this.$el = $el;
	this.$display = $display;
	this.options = $.extend({}, WordCounter.defaults, options);
	this.countTimer = 0;
	this._init();
};
// 默认参数
WordCounter.defaults = {
	max: 140,
	speed: 200,
	errorCls: "xcr"
};
WordCounter.prototype = {
	constructor: WordCounter,
	/**
	 * 初始化
	 * @method _init
	 */
	_init: function() {
		this.refreshCount();
		this._bindEvent();
		if (!this.$display || !this.$display.length) {
			this.$display = this.$el.next();
		}
		this.remindCharLength = this.options.max;
	},
	/**
	 * 事件绑定
	 * @method _bindEvent
	 */
	_bindEvent: function() {
		var that = this,
			timer,
			isKeydown = false,
			count = 0;
		this.$el.on({
			"focus": function(){
				that.countTimer = setInterval(function() {
					that.refreshCount();
				}, that.options.speed);
			},
			"blur": function(){
				that.refreshCount();
				clearInterval(that.countTimer);
			},
			"keyup": function() {
				that.refreshCount();
				isKeydown = false;
			},
			"keydown": function(evt) {
				if(!isKeydown){
					that.refreshCount();
					isKeydown = true;
				}
			},
			"change":  function(){
				that.refreshCount();
			}
		});
	},
	/**
	 * 刷新计数
	 * @method refreshCount
	 */
	refreshCount: function() {
		var value = this.$el.val(),
				// 剩余合法字数
				remindCharLength = this.options.max - U.getCharLength(value),
				isAtt = (remindCharLength < 0);

		this.$display.html(remindCharLength);

		this.$display[isAtt ? "addClass" : "removeClass"](this.options.errorCls);
		this.$el.css("background-color", (isAtt ? "#FCC" : "") );
		this.remindCharLength = remindCharLength;
	},
	/**
	 * 是否错误
	 * @method isError
	 * @return {Boolean} 返回Boolean
	 */
	isError: function() {
		return this.remindCharLength < 0;
	},
	/**
	 * 是否为空值
	 * @method isEmpty
	 * @return {Boolean} 返回Boolean
	 */
	isEmpty: function() {
		return this.remindCharLength === this.options.max;
	}
};


