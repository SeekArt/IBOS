/**
 * 滑动条，在JqueryUi的滑动条的基础上，添加了可配置的tip提示及简易标尺
 * 具体使用可参考JqUi
 * 需要JqueryUi的slider及Bootstrap的tooltip;
 * 为嘛不使用jqueryUi的tooltip....　
 * @method ibosSlider
 * @todo 由于jqueryUi的插件支持重初始化，此处需考虑重初始化的情况
 *       
 * @param {Object|String} [option] 配置|调用方法，调用方法时第二个参数开始为该方法的参数
 *     @param {Boolean}       [option.tip]  启用提示
 *     @param {Boolean}       [option.scale]  启用标尺
 *     @param {Jquery|Eelement|Selector}        [option.target] 用于放置值的input
 * @param {Function}      [option.tipFormat]  tip文本的格式化函数，传入当前值，要求返回字符串，默认添加"%"
 * @return {Jquery}       Jq对象
 */
$.fn.ibosSlider = function(option){
	// 获取格式化后的tip
	var _getTip = function(value){
		if(!option.tipFormat || typeof option.tipFormat !== "function") {
			return value;
		} else {
			return option.tipFormat.call(null, value);
		}
	}
	var $target,
		defaultValues;

	if(typeof option === "object"){

		if(option.target) {
			$target = option.target === "next" ? this.next() : $(option.target);
			defaultValues = $target.val();
	
			if(!option.value && !option.values && defaultValues) {
				if(option.range === true) {
					option.values = defaultValues.split(",")
				} else {
					option.value = defaultValues;
				}
			}
			if($target && $target.length) {
				this.on("slidechange", function(evt, data){
					$target.val(option.range === true ? data.values.join(",") : data.value);
				})
			}
		}

		// 判断是否存在tooltip方法
		if(option.tip && $.fn.tooltip) {
			// 默认的tipFormat
			option.tipFormat = option.tipFormat || function(value){
				return value + "%";
			}
			// 创建滑动条时，初始化tooltip
			$(this).on("slidecreate", function(){
				var instance = $.data(this, "uiSlider"),
					opt = $(this).slider("option");

				if(option.range === true) {
					instance.handles.each(function(index, h){
						$(h).tooltip({ title: _getTip(opt.values[index]), animation: false });
					})
				} else {
					instance.handle.tooltip({ title: _getTip(opt.value), animation: false })
				}
			// 滑动时，改变tooltip的title值
			}).on("slide", function(evt, data){
				$.attr(data.handle, "data-original-title", _getTip(data.value));
				$(data.handle).tooltip("show");
			})
			.on("slidechange", function(evt, data){
				$.attr(data.handle, "data-original-title", _getTip(data.value));
			})
		}

		if(option.scale) {
			option.scale = +option.scale;
			$(this).on("slidecreate", function(){
				var $elem = $(this),
					option = $elem.slider("option");

				var $wrap = $('<div class="ui-slider-scale"></div>');
				var scaleStep = (option.max - option.min)/ option.scale;
				
				for(var i = 0; i < option.scale + 1; i++) {
					$wrap.append('<span class="ui-slider-scale-step" style="left: ' + 100/option.scale * i + '%">' + (i * scaleStep + option.min) + '</span>');
				}

				$elem.append($wrap).addClass("ui-slider-hasscale");
			})
		}
	}

	return this.slider.apply(this, arguments);
};