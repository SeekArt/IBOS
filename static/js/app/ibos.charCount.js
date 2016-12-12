// Charcount
(function($) {

	$.fn.charCount = function(options) {
		var opts = $.extend({}, $.fn.charCount.defaults, options);

		var _render = function($d, count) {
			if (count > opts.max) {
				$d.addClass("warning").html($.template(opts.warningTemplate, {
					count: opts.countdown ? count - opts.max : count,
					maxcount: opts.max
				}));
			} else {
				$d.removeClass("warning").html($.template(opts.template, {
					count: opts.countdown ? opts.max - count : count,
					maxcount: opts.max
				}));
			}
		}

		return this.each(function() {
			var $me = $(this), $display = Dom.getElem(opts.display, true);
			// $display 预期是一个Jq节点
			if (!$display || !$display.length) {
				$display = $("<span class='" + opts.style + "'></span>").insertAfter($me);
			}
			$me.data("opts", opts);
			// 在IE下还是有会部分无法兼容的情况
			// $me.on("input propertychange mouseup", function(){

			// 	$me.trigger("countchange", { count: _calc($display, this.value)});

			// })
			// $me.triggerHandler("input");
			// $me.triggerHandler("propertychange");

			var lastCount,
					countTimer,
					pressing = false,
					triggerChange = function() {
				var count = U.getCharLength($me.val());
				if (lastCount !== count) {
					_render($display, count);
					$me.trigger("countchange", {
						count: count,
						remnant: opts.max - count
					});
					lastCount = count;
				}
			};
			$me.on({
				"focus": function() {
					countTimer = setInterval(triggerChange, 200);
				},
				"blur": function() {
					triggerChange();
					clearInterval(countTimer);
				},
				"keydown": function() {
					if (!pressing) {
						triggerChange();
						pressing = true;
					}
				},
				"keyup": function() {
					triggerChange();
					pressing = false;
				}
			});
			$display.on("click", function(){
				$me.focus();
			});

			$me.on("addEmotion", function() {
				triggerChange();
				$me.focus();
			});

			triggerChange();
		});
	};
	$.fn.charCount.defaults = {
		// 最大字符数
		max: 140,
		// 是否倒计数
		countdown: true,
		// 正常情况时模板
		template: "还可输入 <strong><%=count%></strong> 字",
		// 超过最大数时模板
		warningTemplate: "已经超过限定字数 <strong><%=count%></strong> 字",
		// 使用样式类
		style: 'charcount',
		display: null
	};

})(jQuery);