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
	toggleReply: function($ctrl, $textarea) {
		var selector = $ctrl.attr("data-target"),
			actData = $ctrl.attr("data-act-data"),
			param = U.getUrlParam(actData);

		$ctrl.toggleClass("focus");
		$(selector).toggleClass("msg-box-open");

		if (param["reply_name"]) {
			$textarea.val("回复 @" + param["reply_name"] + " ：").trigger("change");
		} else {
			$textarea.val("").trigger("change");
		}
	},
	submitReply: function(url, param, callback) {
		$.post(url, param, function(result) {
			// 重置input
			$.isFunction(callback) && callback(result);
		},'json');
	},
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

	multipleMode: function($list){
		var cls = "msg-multiple";
		return {
			start: function(){
				$list.addClass(cls);
			},
			stop: function(){
				$list.removeClass(cls);
			}
		}
	}
}

// 统计剩余字数
// @deprecated 此方法由 $.fn.charcount 方法替换
var WordCounter = function($el, $display, options) {
	var data;
	if (data = $el.data("wordCounter")) {
		return data;
	}
	$el.data("wordCounter", this);
	this.$el = $el;
	this.$display = $display;
	this.options = $.extend({}, WordCounter.defaults, options);
	this.countTimer = 0;
	this._init();
}
WordCounter.defaults = {
	max: 140,
	speed: 200,
	errorCls: "xcr"
}
WordCounter.prototype = {
	constructor: WordCounter,
	_init: function() {
		this.refreshCount();
		this._bindEvent();
		if (!this.$display || !this.$display.length) {
			this.$display = this.$el.next();
		}
		this.remindCharLength = this.options.max;
	},
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

		})
	},
	refreshCount: function() {
		var value = this.$el.val(),
				// 剩余合法字数
				remindCharLength = this.options.max - U.getCharLength(value);

		this.$display.html(remindCharLength);

		if (remindCharLength < 0) {
			this.$display.addClass(this.options.errorCls);
			this.$el.css("background-color", "#FCC")
		} else {
			this.$display.removeClass(this.options.errorCls);
			this.$el.css("background-color", "")
		}
		this.remindCharLength = remindCharLength;
	},
	isError: function() {
		return this.remindCharLength < 0;
	},
	isEmpty: function() {
		return this.remindCharLength === this.options.max;
	}
}


