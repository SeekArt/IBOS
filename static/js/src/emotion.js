(function($) {
	var imgurl = Ibos.app.getStaticUrl('/image/expression/');
	var target;
	$.fn.ibosEmotion = function(options) {
		var defaults = {
			target: $(this).parent('form').find('textarea'),
		};
		options = $.extend({}, defaults, options);
		var $elem = $(this);
		var $emotions = $('#emotions');
		var cat_current;
		var cat_page;
		//表情数组
		var emotions = new Array();
		//类别数组
		var categorys = new Array();
		
		var isShow = false;
		var isInit = false;

		//点击浏览器的任何区域，隐藏表情框
		$('body').on('click',hideEmotionBox);
		//阻止点击表情时的冒泡


		if (!$emotions[0]) {
			$emotions = $('<div id="emotions"></div>').appendTo(document.body);
		}
		$emotions.on('click',function(evt) {
			evt.stopPropagation();
		});

		$(this).click(function(evt) {
			target = options.target;
			if (!isInit) {
				$.ajax({
					url: Ibos.app.url('message/feed/getexp'),
					dataType: 'json',
					error: function(request) {
						$emotions.html('<div>加载失败</div>');
					},
					success: function(res) {
						var data = res.data;
						$emotions.html('<div style="float:right"><a href="javascript:void(0);" id="emotions_prev">&laquo;</a><a href="javascript:void(0);" id="emotions_next">&raquo;</a></div><div class="categorys"></div><div class="container"></div><div class="page"></div>');
						if(data){
							for (var i in data) {
								if (data[i].category == '') {
									data[i].category = '默认';
								}
								if (emotions[data[i].category] == undefined) {
									emotions[data[i].category] = new Array();
									categorys.push(data[i].category);
								}
								emotions[data[i].category].push({name: data[i].phrase, icon: imgurl + data[i].icon});
							}
							showCategorys();
							showEmotions();
							isInit = true;
						}else{
							$emotions.html('<div>抱歉！没有可选择的表情!>_<!</div>');
						}
					}
				});
			}

			if (!isShow) {
				$emotions.show().position({
					of: $(this),
					my: "left top+5",
					at: "left bottom",
					collision: "none flip"
				});
				isShow = true;
				$elem.addClass("active");
			} else {
				hideEmotionBox();
			}
			evt.stopPropagation();			
		});

		$('#emotions_prev').on('click',function() {
			showCategorys(cat_page - 1);
		});
		$('#emotions_next').on('click',function() {
			showCategorys(cat_page + 1);
		});

		//表情类型导航栏的显示方法
		function showCategorys() {
			var page = arguments[0] ? arguments[0] : 0;
			var $categorysNav,$categorys;
			if (page < 0 || page >= categorys.length / 5) {
				return;
			}
			$categorys = $('#emotions .categorys');
			$categorys.html('');
			cat_page = page;
			for (var i = page * 5; i < (page + 1) * 5 && i < categorys.length; ++i) {
				$categorys.append($('<a href="javascript:void(0);">' + categorys[i] + '</a>'));
			}

			$categorysNav = $('#emotions .categorys a');
			//点击选择表情集合不同类型的tab切换
			$categorysNav.on('click',function() {
				showEmotions($(this).text());
			});

			//渲染当前表情类型的样式
			$categorysNav.each(function() {
				if ($(this).text() == cat_current) {
					$(this).addClass('current');
				}
			});
		}

		//具体类型表情集合的显示方法
		function showEmotions() {
			//初始时，arguments[0]是undf，所以为'默认'。当有参数传入时，category为对应类型
			var category = arguments[0] ? arguments[0] : '默认';

			//初始化当前页数,通过获取showEmotions('类型','点击页数的值')第二个参数的值转化而来
			var page = arguments[1] ? arguments[1] - 1 : 0;
			var	$container = $('#emotions .container'),
				$page = $('#emotions .page');

			$container.html('');
			$page.html('');

			//记录当前表情类型
			cat_current = category;

			//循环输出对应影类型对应页数下的表情集合
			for (var i = page * 72; i < (page + 1) * 72 && i < emotions[category].length; ++i) {
				$container.append($('<a href="javascript:void(0);" title="' + emotions[category][i].name + '"><img src="' + emotions[category][i].icon + '" alt="' + emotions[category][i].name + '" width="22" height="22" /></a>'));
			}
			//点击表情，将对应表情的代表字符串插入输入框中
			$('.container a', $emotions).on("click", function() {
				var title = $(this).attr('title');
				target.insertText(title);
				target.trigger('addEmotion', { mc: target, title: title });
				hideEmotionBox();
			});

			//当点击翻页"1"和"2"后，通过对比i和page+1(page为上次点击的页数，"1"为0,"2"为1)确定已点击和未点击页数的样式，并将所有页数选择显示出来
			for (var i = 1; i < emotions[category].length / 72 + 1; ++i) {
				$page.append($('<a href="javascript:void(0);"' + (i == page + 1 ? ' class="current"' : '') + '>' + i + '</a>'));
			}
			//点击底部翻页时，将当前表情类型(category)和当前页数($(this).text())传递给显示表情集合的方法(showEmotions())
			$('.page a', $emotions).click(function() {
				showEmotions(category, $(this).text());
			});

			//点击底部翻页后，重新渲染各个表情类型选择按钮的样式
			$('.categorys a.current', $emotions).removeClass('current');
			$('.categorys a', $emotions).each(function() {
				if ($(this).text() == category) {
					$(this).addClass('current');
				}
			});
		}

		function hideEmotionBox(){
			isShow = false;
			$emotions.hide();
			$elem.removeClass("active");
		}
	}
})(jQuery);