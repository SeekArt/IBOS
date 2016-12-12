/**
 * 微博模块主JS
 * 2014-01-06
 * @author inaki
 */


// Progressbar
(function() {
	var Progressbar = function(elem, options) {
		var style = "progress";
		this.$elem = $(elem);

		options.stripe && (style += " progress-striped");
		options.active && (style += " active");
		this.$bar = $('<div class="progress-bar"></div>');
		this.val(options.value);
		this.$elem.addClass(style).html(this.$bar);
	};

	Progressbar.prototype = {
		constructor: Progressbar,
		val: function(value) {
			if (typeof value === "undefined") {
				return this.value || 0;
			} else {
				value = +value;
				this.value = isNaN(value) ? this.value : value;
				if (!isNaN(this.value)) {
					this.$bar.css("width", this.value + "%");
				}
			}
		}
	};

	$.fn.progressbar = function(options) {
		var ret, argu = Array.prototype.slice.call(arguments, 1);
		this.each(function() {
			var $elem = $(this),
				data = $elem.data("progressbar");
			if (!data) {
				$elem.data("progressbar", data = new Progressbar(this, $.extend({}, $.fn.progressbar.defaults, options)));
			}
			if (typeof options === "string" && data[options]) {
				ret = data[options].apply(data, argu);
			}
		});
		return typeof ret !== "undefined" ? ret : this;
	};
	$.fn.progressbar.defaults = {
		stripe: true,
		active: true,
		value: 0
	};
	$.fn.progressbar.Constructor = Progressbar;
})();

var Wb = (function() {
	/**
	 * 读取有权成员列表、赞过的人列表
	 * @method loadUserList
	 * @param  {Object} elem  传入Jquery节点对象
	 * @param  {String} url   传入访问地址
	 * @param  {Object} param 传入JSON格式数据
	 */
	function loadUserList(elem, url, param) {
		var $elem = $(elem);
		// 避免重复读取
		if ($elem.data("isLoading")) {
			return false;
		}
		$elem.button({
			loadingText: "<i class='loading-small'></i> " + Ibos.l("WB.ISLOADING")
		}).button("loading").data("isLoading", true);
		$.get(url, param, function(res) {
			if (res.isSuccess) {
				$elem.prev().find("tbody").append(res.data);
				$elem.button('reset').removeData("isLoading");
			}
		}, "json");
	}
	/**
	 * 初始化评论框
	 * @method initCommentBox
	 * @param {Object} $txts 传入textarea的Jquery节点对象
	 */
	function initCommentBox($txts) {
		var charcountTpl = "<strong><%=count%></strong>/<%=maxcount%>";
		$txts.on("countchange", function(evt, data) {
			var $elem = $(this),
				$commentBox = $elem.closest('[data-node-type="commentBox"]'),
				$commentBtn = $commentBox.find('[data-node-type="commentBtn"]');

			var isSendable = 0 < data.count && data.count <= 140;
			$commentBtn.toggleClass("btn-warning", isSendable).prop("disabled", !isSendable);
		}).charCount({
			template: charcountTpl,
			warningTemplate: charcountTpl,
			countdown: false
		});
		// 初始化 @ 或功能
		Ibos.atwho($txts, {
			url: Ibos.app.url('message/api/searchat')
		});
	}
	/**
	 * 初始化新加载的微博
	 * @method initFeedBox
	 * @param  {Object} $box 传入Jquery节点对象
	 */
	function initFeedBox($box) {
		var $commentText;
		// 初始化复选框
		$box.find(".checkbox input").label();
		// 初始化评论框
		$commentText = $('[data-node-type="commentText"]', $box);
		if ($commentText.length) {
			initCommentBox($commentText);
		}
		// 初始化网页地址提示
		$('[data-node-type="wbUrl"]', $box).popover({
			html: true,
			trigger: "hover",
			placement: "top",
			content: function() {
				return '<span class="wb-url" target="_blank">' + this.href + '</span>';
			},
			container: $(document.body)
		});
	}
	/**
	 * 初始化表情
	 * @method initCommentEmotion
	 * @param  {[type]} $context 传入Jquery节点对象
	 */
	function initCommentEmotion($context) {
		//按钮[data-node-type="commentEmotion"]
		$('[data-node-type="commentEmotion"]', $context).each(function() {
			var $elem = $(this),
				$target = $elem.closest('[data-node-type="commentBox"]').find('[data-node-type="commentText"]');
			$elem.ibosEmotion({
				target: $target
			});
		});
	}

	/**
	 * 初始化表情
	 * @method initForwardEmotion
	 * @param  {Object} $context 传入Jquery节点对象
	 */
	function initForwardEmotion($context) {
		$('[data-node-type="forwardEmotion"]', $context).each(function() {
			var $elem = $(this),
				$target = $elem.closest('[data-node-type="feedForwardBox"]').find('[data-node-type="textarea"]');
			$elem.ibosEmotion({
				target: $target
			});
		});
	}

	var Wb = {
		loadCount: 0,
		loadNewUrl: Ibos.app.g('inHome') ? Ibos.app.url('weibo/home/loadnew') : Ibos.app.url('weibo/personal/loadnew'),
		loadMoreUrl: Ibos.app.g('inHome') ? Ibos.app.url('weibo/home/loadmore') : Ibos.app.url('weibo/personal/loadmore'),
		loadId: Ibos.app.g("loadId"),
		maxId: Ibos.app.g("maxId"),
		firstId: Ibos.app.g("firstId"),
		feedType: Ibos.app.g('feedtype'),
		type: Ibos.app.g('type'),
		MAX_LOAD_TIMES: 3, // 最大可加载次数
		/**
		 * 发布
		 * @method publish
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		publish: function(param) {
			var url = Ibos.app.url('message/feed/postfeed');
			return $.post(url, param, $.noop, "json");
		},
		/**
		 * 插入列表之前
		 * @method insertFeedBefore
		 * @param  {Object} html 传入Jquery节点
		 */
		insertFeedBefore: function(html) {
			var $feedList = $('[data-node-type="feedList"]'),
				$newBar = $feedList.children().eq(0),
				$node = $(html).hide();
			initFeedBox($node);
			initCommentEmotion($node);
			// 如果列表中有新微博提醒栏，则插入在提醒栏之后
			if ($newBar.is('[data-node-type="feedNewBar"]')) {
				$newBar.after($node);
				// 否则插入在列表最前面
			} else {
				$feedList.prepend($node);
			}
			$node.fadeIn(500);
		},
		/**
		 * 读取更多
		 * @method loadMoreFeed
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		loadMoreFeed: function(param) {
			return $.get(Wb.loadMoreUrl, param, $.noop, "json");
		},
		/**
		 * 加载最新
		 * @method loadNew
		 */
		loadNew: function() {
			var that = this;
			$.get(Wb.loadNewUrl, {
				maxId: Wb.maxId,
				"new": 1,
				type: Wb.type,
				feedtype: Wb.feedType,
				uid: Ibos.app.g('uid')
			}, function(res) {
				that.updateNewBar(res);
			}, 'json');
		},
		/**
		 * 新微博数目提示
		 * @method updateNewBar
		 * @param  {Object} data 传入JSON格式数据
		 */
		updateNewBar: function(data) {
			var $newBar = $("[data-node-type='feedNewBar']");
			if (!$newBar.length) {
				$newBar = $('<a href="javascript:;" class="wb-see-new" data-node-type="feedNewBar" data-action="updateFeed" style="display: none;"></a>')
					.prependTo($('[data-node-type="feedList"]'));
			}
			// 没有新微博时不显示
			if (data.status == -1 || data.status == 0) {
				$newBar.fadeOut(200);
			} else {
				$newBar.html(Ibos.l("WB.FEED_NEW_MSG", {
					count: data.count
				})).fadeIn(200).data("html", data.html).data("maxId", data.maxId);
			}
		},
		/**
		 * 加载新微博
		 * @method updateFeed
		 */
		updateFeed: function() {
			var $newBar = $("[data-node-type='feedNewBar']"),
				html = $newBar.data('html');
			Wb.maxId = $newBar.data("maxId");
			this.insertFeedBefore(html);
			$newBar.removeData("html maxId");
			$newBar.remove();
		},
		/**
		 * 打开转发框
		 * @method updateFeed
		 * @param  {Object} param 传入JSON格式数据
		 */
		openForwardDialod: function(param) {
			Ui.closeDialog("d_feed_forward");
			Ui.ajaxDialog(Ibos.app.url('weibo/share/index', param), {
				id: "d_feed_forward",
				title: Ibos.l("WB.FORWARD"),
				width: 500,
				init: function() {
					var $content = this.DOM.content,
						$fwText = $content.find("textarea"),
						$fwBtn = $fwText.closest("[data-node-type='feedForwardComment']").find("[data-node-type='feedForwardBtn']");
					// 初始化复选框
					$content.find(".checkbox input").label();
					// 初始化字数统计
					$fwText.on("countchange", function(evt, data) {
						var isSendable = 0 < data.count && data.count <= 140;
						$fwBtn.toggleClass("btn-primary", isSendable).prop("disabled", !isSendable);
					}).charCount();
					// 初始化 At 功能
					Ibos.atwho($fwText, {
						url: Ibos.app.url('message/api/searchat')
					});

					initForwardEmotion($content);

				}
			});
		},
		/**
		 * 转发
		 * @method feedForward
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		feedForward: function(param) {
			var url = Ibos.app.url('message/feed/sharefeed');
			return $.post(url, param, $.noop, "json");
		},
		/**
		 * 赞，取消赞
		 * @method feedDigg
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		feedDigg: function(param) {
			var url = Ibos.app.url('message/feed/setdigg');
			return $.get(url, param, $.noop, "json");
		},
		/**
		 * 删除一篇微博
		 * @method removeFeed
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		removeFeed: function(param) {
			var url = Ibos.app.url('message/feed/removefeed');
			param.formhash = Ibos.app.g('formHash');
			return $.post(url, param, $.noop, 'json');
		},
		/**
		 * 获取评论列表
		 * @method removeFeed
		 * @param  {Object}   param      传入JSON格式数据
		 * @param  {Function} [callback] 回调函数
		 */
		getCommentList: function($cmList, param) {
			param.formhash = Ibos.app.g('formHash');
			// 临时设置高度用于显示 “读取中”状态
			$cmList.empty().height(60).waiting(null, "mini");
			$.ajax({
				url: Ibos.app.url('weibo/comment/getcommentlist'),
				data: param,
				type: "post",
				dataType: "json",
				success: function(res) {
					if (res.isSuccess) {
						var $res = $(res.data);
						$cmList.height("").stopWaiting().html($res);
					}
				}
			});
		},
		/**
		 * 初始化文本框的值
		 * @param {Jquery} $input 文本框节点
		 * @param {String} name   要@的名字
		 */
		setDefaultAt: function($input, name) {
			var val = Ibos.l('REPLY') + " @" + name + " ： ";
			$input.focus().val($input.val() + " " + val);
		}
	};


	$(function() {
		var $doc = $(document);

		Ibos.evt.add({
			// 加载最新的微博
			"updateFeed": function() {
				Wb.updateFeed();
			},
			// 打开"转发微博"对话框
			"openFeedForward": function(param) {
				Wb.openForwardDialod(param);
			},
			// 转发微博
			"feedForward": function(param, elem) {
				var $form = $(elem.form),
					$forwardBox = $form.closest("[data-node-type='feedForwardBox']");
				$forwardBox.waiting(null, 'normal', true);
				Wb.feedForward($form.serializeArray()).done(function(res) {
					if (res.isSuccess) {
						$forwardBox.waiting(false);
						Ui.closeDialog("d_feed_forward");
						Ui.tip(Ibos.l("WB.FORWARD_SUCCESS"));
						Wb.insertFeedBefore(res.data);
					}
				});
			},
			// 展开评论框
			"openFeedComment": function(param, elem) {
				var $elem = $(elem),
					$feedBox = $elem.closest('[data-node-type="feedBox"]'),
					$commentText = $feedBox.find('[data-node-type="commentText"]'),
					$target = $feedBox.find('[data-node-type="commentBox"]');
				$commentText.trigger("focus");
				if (!$target.hasClass('loaded')) {
					// modify by banyan：修改为只执行一个进程只执行一次加载
					Wb.getCommentList($target.find(".cmt-sub"), param);
					$target.addClass("loaded");
				}
			},
			// 发布评论
			"comment": function(param, elem) {
				var $elem = $(elem),
					$target = $elem.closest('[data-node-type="commentBox"]'),
					$cmList = $target.find(".cmt-sub"),
					$commentText = $target.find('textarea'),
					interval = Ibos.app.g("submitInterval") || 0;

				if (!$elem.data("disabledSubmit")) {
					param.content = $commentText.val();
					param.tocid = $elem.data('tocid');
					param.touid = $elem.data('touid');
					param.formhash = Ibos.app.g('formHash');
					param.sharefeed = +$target.find('[name=sharefeed]').prop('checked');
					var $comment = $target.find('[name=comment]');
					if ($comment.length) {
						param.comment = +$comment.prop('checked');
					}
					$elem.button('loading');
					$.post(Ibos.app.url('weibo/comment/addcomment'), param, function(res) {
						if (res.isSuccess) {
							$elem.button('reset');
							$commentText.val("").trigger("focus");
							var $res = $(res.data);
							$cmList.prepend($res);
							Ui.tip(Ibos.l("COMMENT.SUCCESS"));
							$elem.data("disabledSubmit", true);
							setTimeout(function() {
								$elem.removeData("disabledSubmit");
							}, interval);
						} else {
							Ui.tip(res.msg, 'danger');
						}
					}, "json");
				} else {
					Ui.tip(Ibos.l('WB.PUBLISH_TIME_LIMIT', {
						time: interval / 1000
					}), 'warning');
				}
			},
			// 赞, 取消赞
			"feedDigg": function(param, elem) {
				var $elem = $(elem),
					$diggBox, $diggList;
				Wb.feedDigg(param).done(function(res) {
					var $items;
					if (res.isSuccess) {
						$diggBox = $("#menu_digg_box");
						$diggList = $diggBox.find('[data-node-type="feedDiggList"]');
						$items = $diggList.find("li");
						// 这里传回一个参数用于标识当前操作是赞还是取消赞
						if (res.digg) {
							// 节点数达到上限时，移除最后一个人员
							if ($items.length == 5) {
								$items.eq(3).remove();
							}
							// 把自己加入列表，由ajax返回html字符串
							if (!$items.filter('[data-uid="' + Ibos.app.g('uid') + '"]').length) {
								$diggList.prepend(res.data);
							}
							// 更新赞数及描述
							$elem.html('<i class="o-wbi-good active"></i>' + Ibos.l('WB.DIGGED') + '（' + res.count + '）');
						} else {
							$items.filter('[data-uid="' + Ibos.app.g('uid') + '"]').remove();
							// 若赞列表中没有条目，则隐藏弹出层
							if (!$diggList.find("li").length) {
								$diggBox.hide();
							}

							// 更新赞数
							$elem.html('<i class="o-wbi-good"></i>' + Ibos.l('WB.DIGG') + '（' + res.count + '）');
						}
					} else {
						Ui.tip(res.msg, 'warning');
					}
				});
			},
			// 从赞列表中移除自己
			"removeFeedDigg": function(param, elem) {
				Wb.feedDigg(param).done(function(res) {
					if (res.isSuccess && !res.digg) {
						var $feedBox = $('[data-node-type="feedBox"][data-feed-id="' + param.feedid + '"]'),
							$feedDiggBtn = $feedBox.find('[data-node-type="feedDiggBtn"]'),
							$diggItem = $(elem).parent(),
							$diggBox = $diggItem.closest('[data-node-type="feedDiggBox"]');
						// 更新赞状态
						$feedDiggBtn.html('<i class="o-wbi-good"></i>' + Ibos.l('WB.DIGG') + '（' + res.count + '）');

						// 若赞列表中没有条目，则隐藏弹出层
						if (!$diggItem.siblings().length) {
							$diggBox.hide();
						}
						$diggItem.remove();
					}
				});
			},
			// 查看赞人员列表
			"openDiggUserDialog": function(param, elem) {
				Ui.closeDialog('d_digg_user');
				Ui.ajaxDialog(Ibos.app.url('message/feed/alldigglist', param), {
					id: 'd_allowed_user',
					title: Ibos.l('WB.VIEWDIGGLIST'),
					width: 380,
					padding: 0
				});
			},
			// 查看允许人员列表
			"openAllowedUserDialog": function(param, elem) {
				Ui.closeDialog('d_allowed_user');
				Ui.ajaxDialog(Ibos.app.url('message/feed/allowedlist', param), {
					id: 'd_allowed_user',
					title: Ibos.l('WB.VIEWALLOWEDLIST'),
					width: 380,
					padding: 0
				});
			},
			// 查看更多赞的人员
			"loadMoreDiggUser": function(param, elem) {
				loadUserList(elem, Ibos.app.url('message/api/loadmoredigguser'), param);
			},
			// 删除一篇微博
			"removeFeed": function(param, elem) {
				var $elem = $(elem),
					$feedBox = $elem.closest('[data-node-type="feedBox"]');
				Ui.confirm(Ibos.l("WB.REMOVE_FEED_CONFIRM"), function() {
					Wb.removeFeed(param).done(function(res) {
						if (res.isSuccess) {
							$feedBox.fadeOut(500, function() {
								$feedBox.remove();
							});
							Ui.showCreditPrompt(); //提醒积分变动
							if (param.redirectToUid) {
								var url = Ibos.app.url('weibo/personal/index', {
									uid: param.redirectToUid
								});
								window.location.href = url;
							}
						} else {
							Ui.tip(res.msg, 'danger');
							return false;
						}
					});
				});
			},
			// 点击回复后的操作
			"reply": function(param, elem) {
				var $elem = $(elem),
					$feedBox = $elem.closest('[data-node-type="feedBox"]'),
					$commentText = $feedBox.find('[data-node-type="commentText"]'),
					$commentBtn = $feedBox.find('[data-node-type="commentBtn"]');
				$commentBtn.attr({
					'data-touid': param.touid,
					'data-tocid': param.tocid
				});
				Wb.setDefaultAt($commentText, param.name);
			},
			// 删除评论
			"delreply": function(param, elem) {
				$.get(Ibos.app.url('weibo/comment/delcomment'), param, function(res) {
					if (res.isSuccess) {
						var $parent = $(elem).parentsUntil('.cmt-sub');
						$parent.fadeOut(function() {
							$parent.remove();
						});
					}
				}, 'json');
			}
		});
		// 评论框展开
		$doc.on({
			"focus": function() {
				var $elem = $(this),
					$commentBox = $elem.closest('[data-node-type="commentBox"]');
				$commentBox.addClass("open");
			}
		}, '[data-node-type="commentText"]');

		// 点赞
		(function() {
			var $diggBox = $('#menu_digg_box'),
				$diggList = $diggBox.find('[data-node-type="feedDiggList"]'),
				timerId;
			var _hide = function() {
				clearTimeout(timerId);
				timerId = setTimeout(function() {
					$diggBox.hide();
				}, 500);
			};
			$(document).on({
				"mouseenter": function() {
					var param = Ibos.app.getEvtParams(this),
						$elem = $(this);
					clearTimeout(timerId);
					timerId = setTimeout(function() {
						$.get(Ibos.app.url('message/feed/simpledigglist'), param, function(res) {
							if (res.isSuccess) {
								// 赞过的人
								$diggList.html(res.data);
								$diggBox.show().position({
									of: $elem,
									at: "center bottom",
									my: "center top+10"
								});
								// 更新点赞数
								$elem.html($elem.html().replace(/\d+/, res.count));
							}
						}, "json");
					}, 500);
				},
				"mouseleave": _hide
			}, "[data-action='feedDigg']");

			$diggList.on({
				"mouseenter": function() {
					clearTimeout(timerId);
				},
				"mouseleave": _hide
			});
		})();

		// 初始化字数统计
		if ($.fn.charCount) {
			$(".wb-pub-text").charCount();
		}
		// 初始化tooltip
		$("[data-toggle='tooltip']").tooltip();
		// 初始化 @ 功能
		Ibos.atwho && Ibos.atwho($('[data-node-type="textarea"]'), {
			url: Ibos.app.url('message/api/searchat')
		});
		// 初始化所有微博状态
		initFeedBox($('[data-node-type="feedBox"]'));
		(function() {
			// 加载更多
			// 最多可以加载3次
			// 翻页后不出现
			var $feedList = $('[data-node-type="feedList"]'),
				$loadMoreFeed = $('[data-node-type="loadMoreFeed"]'),
				$loadMoreFeedTip = $loadMoreFeed.find("[data-node-type='loadMoreFeedTip']"),
				isLoading = false;
			var loadMore = function() {
				// 按钮改变为读取中状态
				$loadMoreFeedTip.show();
				isLoading = true;
				var param = {
					loadcount: Wb.loadCount,
					loadId: Wb.loadId,
					type: Wb.type,
					uid: Ibos.app.g('uid'),
					feedkey: Ibos.app.g('feedkey'),
					feedtype: Wb.feedType
				};
				Wb.loadMoreFeed(param).done(function(res) {
					var $temp;
					if (res.status == 1) {
						Wb.loadCount++;
						Wb.loadId = +res.loadId;
						// Wb.loadId -= 5;
						// 如果读取次数到了上限且有页码时，显示页码
						if (res.firstId != 0 && res.pageData) {
							$loadMoreFeedTip.hide();
							$("[data-node-type='page']").html(res.pageData);
						} else {
							$temp = $(res.data);
							// 初始化新加载的内容
							initFeedBox($temp);
							// 初始化表情
							initCommentEmotion($temp);
							$feedList.children().eq(-2).before($temp);

						}
						isLoading = false;
					} else if (res.status == 0) {
						$loadMoreFeedTip.find('a').html(res.msg);
					}
				});
			};
			if (Ibos.app.g("loadmore") == "1" && $loadMoreFeed.length) {
				$(window).on("scroll", function() {
					var scrollTop, winHeight, offTop;
					// 当前“加载更多”次数未达上限且还有未加载的微博时，允许继续加载
					if (Wb.loadId && Wb.loadCount < Wb.MAX_LOAD_TIMES && !isLoading) {
						scrollTop = $(document).scrollTop();
						winHeight = $(window).height();
						offTop = $loadMoreFeed.offset().top;
						// 当滚动到接近微博列表底部时，自动加载，提前400px加载
						if (scrollTop + winHeight + 400 > offTop) {
							loadMore();
						}
					}
				});
			}
		})();

		// 
		initCommentEmotion();

		// 读取最新微博
		if (Ibos.app.g("loadnew") == "1") {
			setInterval(function() {
				Wb.loadNew();
			}, Ibos.app.g('loadNewFrequency', 30000));
		}

	});
	return Wb;
})();