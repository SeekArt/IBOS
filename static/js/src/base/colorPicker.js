// $.fn.colorPicker
/* 选色器 */
(function(window, $) {
	var lang = {
		TITLE: "请选择颜色",
		RESET: "重置颜色"
	}
	$.fn.colorPicker = function(conf) {
		// Config for plug
		var defaultData = ["#FFFFFF", "#E26F50", "#EE8C0C", "#FDE163", "#91CE31", "#3497DB", "#82939E", "#B2C0D1"];
		var config = $.extend({
			id: 'jquery-colour-picker', // id of colour-picker container
			horizontal: false, // 是否垂直排列色板
			// title: lang.TITLE, // Default dialogue title
			speed: 200, // Speed of dialogue-animation
			position: { my: "left top", at: "left top" },
			data: defaultData,
			style: "",
			reset: true,
			onPick: null // 选择时触发的回调
		}, conf);

		// Add the colour-picker dialogue if not added
		var colourPicker = $('#' + config.id);

		if (!colourPicker.length) {
			colourPicker = $('<div id="' + config.id + '" class="jquery-colour-picker ' + config.style + '"></div>').appendTo(document.body).hide();

			// Remove the colour-picker if you click outside it (on body)
			$(document.body).click(function(event) {
				if (!($(event.target).is('#' + config.id) || $(event.target).parents('#' + config.id).length)) {
					colourPicker.slideUp(config.speed);
				}
			});
		}

		if (config.horizontal) {
			colourPicker.addClass("horizontal");
			//垂直模式时，去掉title
			config.title = "";
		}

		// For every select passed to the plug-in
		return this.each(function() {
			if($.data(this, 'colorPicker')){
				return false;
			}
			// input element
			var loc = '',
				hex, title,
				createItemTemp = function(hex, title) {
					return '<li><a href="#" title="' + title + '" rel="' + hex + '" style="background: ' + hex + ';">' + title + '</a></li>'
				};

			// 当由data属性提供数据
			if (config.data) {
				for (var i = 0, len = config.data.length; i < len; i++) {
					//当data项是颜色值
					if (typeof config.data[i] === 'string') {
						loc += createItemTemp(config.data[i], config.data[i]);
						//当data项是键值对
					} else {
						loc += createItemTemp(config.data[i].hex, config.data[i].title);
					}
				}
				// 创建清除按钮
				if(config.reset){
					loc += createItemTemp("", lang.RESET);
				}

				// 为select元素时，从option中获取数据
			} else {
				//@Debug:
				throw new Error('数据不存在')
			}

			// When you click the ctrl
			var ctrl = config.ctrl && config.ctrl.length ? config.ctrl : $(this);
			ctrl.click(function() {
				// Show the colour-picker next to the ctrl and fill it with the colours in the select that used to be there
				var heading = config.title ? '<h2>' + config.title + '</h2>' : '';
				var pos = $.extend({ of: ctrl }, config.position);
				colourPicker.html(heading + '<ul>' + loc + '</ul>').css({
					position: 'absolute'
				})
				.slideDown(config.speed)
				.position(pos);

				return false;
			});

			// When you click a colour in the colour-picker
			colourPicker.on('click', 'a', function() {
				// The hex is stored in the link's rel-attribute
				var hex = $(this).attr('rel'),
					title = $(this).text();
				config.onPick && config.onPick(hex, title)
				// Hide the colour-picker and return false
				colourPicker.slideUp(config.speed);
				return false;
			});

			$.data(this, "colorPicker", true);
		});
	}

})(window, window.jQuery);

