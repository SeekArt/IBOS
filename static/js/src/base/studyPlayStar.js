/**
 * 评级插件
 * @param  {Key-value}   [options]  配置
 * @param  {Number}      [options.MaxStar=5]       最大星级
 * @param  {Number}      [options.StarWidth=30]    每级所占宽度，与上面参数共同构成总宽度
 * @param  {Number}      [options.CurrentStar=0]   当级星级
 * @param  {Boolean}     [options.Enabled=true]    是否可用，当为false时，不能选择星级
 * @param  {Number}     [options.Half=0]          当Half为true时，可以选择每级的1/2处
 * @param  {Number}     [options.prefix=0]
 * @param  {Function}    callback                  回调
 * @return {Jquery}            jq对象
 */
$.fn.studyplay_star = function(options, callback) {
	//默认设置
	var settings = {
		MaxStar: 11,
		StarWidth: 10,
		CurrentStar: 0,
		Enabled: true,
		Half: 0,
		prefix: 0,
		mark: 0
	};

	var container = jQuery(this),
		_value =  container.data("value");
	if(_value) {
		settings.CurrentStar = _value; 
	}

	if (options) {
		jQuery.extend(settings, options);
	};
	container.css({
		"position": "relative",
		"float": "right"
	})
	.html('<ul class="studyplay_starBg"></ul>')
	.find('.studyplay_starBg').width(settings.MaxStar * settings.StarWidth)
	.html('<li class="studyplay_starovering" style="width:' + (settings.CurrentStar + 1) * settings.StarWidth + 'px; z-index:0;" id="studyplay_current"></li>');
	
	if (settings.Enabled) {
		var ListArray = "";
		if (settings.Half == 0) {
			for (k = 1; k < settings.MaxStar + 1; k++) {
				ListArray += '<li class="studyplay_starON" style="width:' + settings.StarWidth * k + 'px;z-index:' + (settings.MaxStar - k + 1) + ';"></li>';
			}
		}
		if (settings.Half == 1) {
			for (k = 1; k < settings.MaxStar * 2 + 1; k++) {
				ListArray += '<li class="studyplay_starON" style="width:' + settings.StarWidth * k / 2 + 'px;z-index:' + (settings.MaxStar - k + 1) + ';"></li>';
			}
		}
		container.find('.studyplay_starBg').append(ListArray);

		container.find('.studyplay_starON').hover(function() {
				var studyplay_count = settings.MaxStar - $(this).css("z-index");
				$(this).siblings('.studyplay_starovering').hide();
				$('#processbar_info_' + settings.prefix).html('&nbsp;' + studyplay_count * 10 + '%');
				$(this).removeClass('studyplay_starON').addClass("studyplay_starovering");
				$("#studyplay_current" + settings.prefix).hide();
				container.trigger("star.enter", { count: studyplay_count, current: $(this) })
			},
			function() {
				var studyplay_count = settings.MaxStar - $(this).css("z-index");
				$(this).siblings('.studyplay_starovering').show();
				$('#processbar_info_' + settings.prefix).html('&nbsp;' + $('#processinput_' + settings.prefix).val() * 10 + '%');
				$(this).removeClass('studyplay_starovering').addClass("studyplay_starON");
				$("#studyplay_current" + settings.prefix).show();
				container.trigger("star.leave", { count: studyplay_count, current: $(this) })		
			})
			.click(function() {
				var studyplay_count = settings.MaxStar - $(this).css("z-index");
				$(this).siblings('.studyplay_starovering').width((studyplay_count + 1) * settings.StarWidth)
				if (settings.Half == 0)
					$("#studyplay_current" + settings.prefix).width('&nbsp;' + studyplay_count * settings.StarWidth)
				$(this).siblings('.studyplay_starovering').width('&nbsp;' + (studyplay_count + 1) * settings.StarWidth)
				if (settings.Half == 1)
					$("#studyplay_current" + settings.prefix).width((studyplay_count + 1) * settings.StarWidth / 2)
					//回调函数
				if (typeof callback == 'function') {
					if (settings.Half == 0)
						callback(studyplay_count, container);
					if (settings.Half == 1)
						callback(studyplay_count / 2, container);
					return;
				}
			})
	}
};