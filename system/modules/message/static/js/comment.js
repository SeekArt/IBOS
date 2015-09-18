
var Comment = (function() {
	function CommentOp(baseUrl, params) {
		this.baseUrl = baseUrl;
		this.params = params;
	}

	$.extend(CommentOp.prototype, {
		/**
		 * 获取url地址
		 * @method getUrl
		 * @param  {String} op 传入操作事件
		 * @return {String}    返回url地址
		 */
		getUrl: function(op) {
			return this.baseUrl + "&op=" + op;
		},
		/**
		 * 深层次拷贝
		 * @method extendParams
		 * @param  {Object} params 传入JSON格式数据
		 * @return {Object}        返回JSON格式数据
		 */
		extendParams: function(params) {
			return $.extend(true, {}, this.params, params);
		},
		/**
		 * 获取某条评论下的回复
		 * @method getReply 
		 * @param  {Object} params   ajax 参数
		 * @param  {Object} settings 配置项
		 * @return {Object} jquery 	 返回deffered对象
		 */
		getReply: function(params, settings) {
			var url = settings.url || this.getUrl("getReply");
			params = this.extendParams(params);

			return $.post(url, params, $.noop, "json");
		},
		/**
		 * 添加某条评论下的回复
		 * @method addReply 
		 * @param  {Object} params   ajax 参数
		 * @param  {Object} settings 配置项
		 * @return {Object} jquery 	 返回deffered对象
		 */
		addReply: function(params, settings) {
			var url = settings.url || this.getUrl("addReply");
			params = this.extendParams(params);

			return $.post(url, params, $.noop, "json");
		},
		/**
		 * 删除某条评论下的回复
		 * @method delReply 
		 * @param  {Object} params   ajax 参数
		 * @param  {Object} settings 配置项
		 * @return {Object} jquery 	 返回deffered对象
		 */
		delReply: function(params, settings) {
			var url = settings.url || this.getUrl("delReply");
			params = this.extendParams(params);

			return $.get(url, params, $.noop, "json");
		},
		/**
		 * 获取评论
		 * @method getComment 
		 * @param  {Object} params   ajax 参数
		 * @param  {Object} settings 配置项
		 * @return {Object} jquery 	 返回deffered对象
		 */
		getComment: function(params, settings) {
			var url = settings.url || this.getUrl("getComment");
			params = this.extendParams(params);

			return $.post(url, params, $.noop, "json");
		},
		/**
		 * 添加评论
		 * @method addComment 
		 * @param  {Object} params   ajax 参数
		 * @param  {Object} settings 配置项
		 * @return {Object} jquery 	 返回deffered对象
		 */
		addComment: function(params, settings) {
			var url = settings.url || this.getUrl("addComment");
			params = this.extendParams(params);

			return $.post(url, params, $.noop, "json");
		}
	});
	

	function Comment($ctx, options){
		if (!$ctx || !$ctx.length) {
			return false;
		}
		var that = this;

		this.$ctx = $ctx;
		this.replyLock = 0;
		this.options = options || {};
		this.customParam = {};
		this.defCommentOffset = options.defCommentOffset ? options.defCommentOffset : 0;
		this.defCommentLimit = options.defCommentLimit ? options.defCommentLimit : 10;
		this.defReplyOffset = options.defReplyOffset ? options.defReplyOffset : 0;
		this.defReplyLimit = options.defReplyLimit ? options.defReplyLimit : 10;
		
		this.op = new CommentOp("", { formhash: Ibos.app.g("formHash") });
		this.bindEvents();
		// 初始化@功能 
		Ibos.atwho($ctx.find("textarea"), { url: Ibos.app.url('message/api/searchat') });

		this.$ctx.on("keydown", "textarea", function(evt) {
			var $textarea = $(this);
			if(evt.which === 13 && evt.ctrlKey) {
				that.getParentItem($textarea).find("[data-act='addcomment'], [data-act='addreply']").click();
			}
		});
	}

	$.extend(Comment.prototype, {	
		/**
		 * 事件绑定
		 * @method bindEvents
		 */
		bindEvents: function() {
			var that = this;

			this.$ctx.on("click", "[data-act]", function(evt) {
				var act = $.attr(this, "data-act");
				var param = $(this).data("param");
				
				param = param || {};

				that[act] && that[act].call(that, $(this), param, evt);
			});
		},
		/**
		 * 获取父级节点
		 * @method getParentItem
		 * @param  {Object} $elem 传入Jquery节点对象
		 * @return {Object}       传入Jquery节点对象
		 */
		getParentItem: function($elem) {
			return $elem.closest(".cmt-item");
		},

		/**
		 * 展开并获取评论下的回复列表
		 * @method getreply
		 * @param  {Jquery} $elem  触发节点
		 * @param  {Object} params 
		 * @return
		 */
		getreply: function($elem, params) {
			var that = this;
			var $textarea = this.getParentItem($elem).find("textarea");
			var $replyPanel = $textarea.parent();

			this._setDefaultAt($textarea, params.name);
			$replyPanel.toggle();

			// 未加载过相关回复时
			if(!$elem.hasClass("loaded")) {
				var $replyList = $replyPanel.find(".cmt-sub");
				// 临时设置高度用于显示 “读取中”状态
				$replyList.empty().height(60).waiting(null, "mini");

				this.op.getReply(params, {
					url: this.options.getReplyUrl
				})
				.done(function(res) {
					if(res.isSuccess) {
						$replyList.height("").waiting(false).replaceWith(res.data);
						$elem.addClass("loaded");
						that.defReplyOffset = that.defReplyLimit;
					}
				});
			}
		},
		
		/**
		 * 添加一条回复
		 * @method addreply
		 * @param  {Jquery} $elem  触发节点
		 * @param  {Object} params 
		 * @return
		 */
		addreply: function($elem, params) {
			if (this.replyLock === 1) {
				return;
			}

			var $textarea = this.getParentItem($elem).find("textarea"),
				$replyPanel = $textarea.parent();

			var touid = $elem.data("touid"),
				tocid = $elem.data("tocid"),
				content = $textarea.val();

			if ($.trim(content) === "") {
				$textarea.blink();
				return false;
			}

			$elem.button("loading");

			var $replyList = $replyPanel.find(".cmt-sub");
			$replyList.waiting(null, "mini");

			this.op.addReply($.extend({
				content: content,
				touid: touid,
				tocid: tocid
			}, params), {
				url: this.options.addUrl
			})
			.done(function(res) {
				if(res.isSuccess) {
					$replyList.prepend(res.data).waiting(false);
				} else {
					Ui.tip(res.msg, "danger");
				}

				$textarea.val("");
				$elem.button("reset");
			});

			// 计数器，预防狂刷回复
			this.replylock = 1;

			setTimeout(function() {
				this.replyLock = 0;
			}, 3000);
		},

		/**
		 * 删除一条回复
		 * @method delreply
		 * @param  {Jquery} $elem  触发节点
		 * @param  {Object} params 
		 * @return
		 */
		delreply: function($elem, params) {
			var that = this;

			this.op.delReply(params, { url: this.options.delUrl })
			.done(function(res) {
				if(res.isSuccess) {
					var $replyItem = that.getParentItem($elem);
					$replyItem.fadeOut(function() {
						$replyItem.remove();
					});
				} else {
					Ui.tip(res.msg, "danger");
				}

			});
		},
		/**
		 * 回复某用户的回复
		 * @method reply
		 * @param  {Jquery} $elem  触发节点
		 * @param  {Object} params 
		 * @return
		 */
		reply: function($elem, params) {
			var $commentItem = $elem.closest("div.cmt-item");
			var $textarea = $commentItem.find("textarea");
			var $replyBtn = $commentItem.find("[data-act='addreply']");

			$replyBtn.data('touid', params.touid)
				.data('tocid', params.tocid);

			this._setDefaultAt($textarea, params.name);
		},

		/**
		 * 读取更多回复
		 * @method loadmorereply
		 * @param  {Jquery} $elem  触发节点
		 * @param  {Object} params 
		 * @return
		 */
		loadmorereply: function($elem, params) {
			var that = this;

			params.limit = this.defReplyLimit;
			params.offset = this.defReplyOffset;
			params.loadmore = 1;
			params.type = 'reply';

			$elem.hide().parent().waiting();

			this.op.getReply(params, {
				url: this.options.getReplyUrl
			})
			.done(function(res) {
				if(res.isSuccess) {
					that.defReplyOffset = that.defReplyOffset + that.defReplyLimit;
					var $replyList = that.getParentItem($elem).find(".cmt-sub");
					$replyList.append(res.data);

					$elem.parent().waiting(false);
					$elem.toggle(parseInt(res.count, 10) > that.defReplyOffset);
				}
			});
		},

		/**
		 * 读取更多评论
		 * @method loadmorecomment
		 * @param  {Jquery} $elem  触发节点
		 * @param  {Object} params 
		 * @return
		 */
		loadmorecomment: function($elem, params) {
			var that = this;
			var $commentList = $elem.closest(".cmt");

			params.limit = this.defCommentLimit;
			params.offset = this.defCommentOffset;
			params.loadmore = 1;
			params.type = "comment";

			$elem.hide().parent().waiting();

			this.op.getComment(params, {
				url: this.options.getCommentUrl
			})
			.done(function(res) {
				if(res.isSuccess) {
					that.defCommentOffset = that.defCommentOffset + that.defCommentLimit;

					$elem.parent().waiting(false).before(res.data);
					$elem.toggle(parseInt(res.count, 10) > that.defCommentOffset);			
				} else {
					Ui.tip(res.msg, "danger");
				}
			});
		},

		/**
		 * 添加一条评论
		 * @method addcomment
		 * @param  {Jquery} $elem  触发节点
		 * @param  {Object} params 
		 */
		addcomment: function($elem, params) {
			var that = this;
			var $commentBox = $elem.closest("[data-node-type='commentBox']");
			var $textarea = $commentBox.find("textarea");
			var content = $textarea.val();

			if($.trim(content) == "") {
				$textarea.blink();
				return false;
			}

			$elem.button("loading");

			params = $.extend({
				content: content
			}, params, this.customParam);
		
			this.op.addComment(params, {
				url: this.options.addUrl
			})
			.done(function(res){
				if(res.isSuccess) {
					var $item = $(res.data).hide().prependTo(that.$ctx).fadeIn();
					Ibos.atwho($item.find("textarea"));

					Ui.scrollYTo($item.attr("id"), -60);
					that.$ctx.find(".no-comment-tip").remove();

					$textarea.val("");
					that.$ctx.trigger("commentAdd", $item);
				} else {
					Ui.tip(res.msg, 'danger');
				}
				$elem.button("reset");
			});
		},

		/**
		 * 删除一条评论
		 * @method delcomment
		 * @param  {Jquery} $elem  触发节点
		 * @param  {Object} params 
		 */
		delcomment: function($elem, params) {
			var that = this;
			artDialog.confirm(Ibos.l("CONFIRM_DEL_COMMENT"), function() {
				that.op.delReply(params, {
					url: that.options.delUrl
				})
				.done(function() {
					var $commentItem = that.getParentItem($elem);
					$commentItem.fadeOut(function() {
						$commentItem.remove();
						Ui.showCreditPrompt();
					});
				});
			});
		},

		/**
		 * 初始化文本框的值
		 * @param {Jquery} $input 文本框节点
		 * @param {String} name   要@的名字
		 */
		_setDefaultAt: function($input, name) {
			var val = L.REPLY + " @" + name + " ： ";
			$input.focus().val(val);
		}
	});

	return {
		init: function($ctx, options){
			return new Comment($ctx, options);
		}
	};
})();


