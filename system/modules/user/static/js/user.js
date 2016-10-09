/**
 * center.js
 * 个人中心
 * IBOS
 * @author		inaki
 * @version		$Id$
 */

/**
 * 动态进度条，使进度条有从0开始读取的效果
 * @param {Jquery} $elem   容器节点
 * @param {Number} value   初始值
 * @param {Object} options 配置项
 */
var Progress = function($elem, value, options) {
	this.$elem = $elem;
	this.value = this._reviseValue(value);
	this.options = $.extend({}, Progress.defaults, options);
	this.style = "";
	this._init();
};
// 默认参数
Progress.defaults = {
	roll: true,
	speed: 20,
	active: false
};
Progress.prototype = {
	constractor: Progress,
	/**
	 * 初始化进度条
	 * @method _init 
	 */
	_init: function() {
		this.$elem.addClass("progress");
		this.$progress = this.$elem.find(".progress-bar");
		if (this.$progress.length === 0) {
			this.$progress = $("<div class='progress-bar'></div>").appendTo(this.$elem);
		}
		this.setStyle(this.options.style);
		this.setActive(this.options.active);
		if (!isNaN(this.value)) {
			this._setValue();
		}
	},
	/**
	 * 修正值的大小，值必须在0到100之间
	 * @method _reviseValue 
	 * @param  {Number} value 传入值
	 * @return {Number}       返回修正值
	 */
	_reviseValue: function(value) {
		value = parseInt(value, 10);
		// NaN
		value = value < 0 ? 0 : value > 100 ? 100 : value;
		return value;
	},
	/**
	 * 设置样式
	 * @method setStyle
	 * @param  {String} style 传入要设置的样式名
	 */
	setStyle: function(style) {
		var styles = ["danger", "info", "warning", "success"],
				styleStr = "",
				pre = "progress-bar-";

		if (this.style !== style) {
			this.style = style;
			for (var i = styles.length; i--; ) {
				styleStr += pre + styles[i] + " ";
			}
			this.$progress.removeClass(styleStr);

			if ($.inArray(style, styles) !== -1) {
				this.$progress.addClass(pre + style);
			}
		}
	},
	/**
	 * 设置活动状态
	 * @method setActive
	 * @param  {Boolean} toStriped 传入活动状态
	 */
	setActive: function(toStriped) {
		this.$elem.toggleClass("progress-striped", toStriped);
		this.$elem.toggleClass("active", toStriped);
	},
	/**
	 * 设置进度值(内部使用)
	 * @method _setValue
	 */
	_setValue: function() {
		if (!isNaN(this.value)) {
			// 动态进度条
			if (this.options.roll) {
				var that = this,
						interval = this.options.speed,
						current = 0,
						transTemp,
						timer;
				// 由于css3的transition会与setInterval计算冲突，transitionEnd回调不兼容，所以先去掉该属性
				transTemp = this.$progress.css("transition");
				this.$progress.css("transition", "none");

				that.$elem.trigger("rollstart");

				timer = setInterval(function() {
					that.$progress.css("width", current + "%");
					that.$elem.trigger("rolling", {
						value: current
					});
					if (current >= that.value) {
						clearInterval(timer);
						that.$elem.trigger("rollend");
						that.$progress.css("transition", transTemp);
					}
					current++;
				}, interval);
			} else {
				this.$progress.css("width", this.value + "%");
			}
		}

	},
	/**
	 * 设置进度值
	 * @method setValue
	 * @param {Number} value 传入进度值
	 */
	setValue: function(value) {
		this.value = this._reviseValue(value);
		this._setValue();
	}
};

var userCenter = {
	op: {
		/**
		 * 绑定酷办公
		 * @method bindIbosco
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       传出deffered对象
		 */
		"bindIbosco": function(param){
			var url = Ibos.app.url("user/home/bindco");
			return $.post(url, param, $.noop, "json");
		},
		/**
		 * 绑定微信企业号
		 * @method bindIbosco
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       传出deffered对象
		 */
		"bindIboswxqy": function(param){
			var url = Ibos.app.url("user/home/bindwxqy");
			return $.post(url, param, $.noop, "json");
		},
		/**
		 * 解绑酷办公
		 * @method relieveIbosco
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       传出deffered对象
		 */ 
		relieveIbosco: function(param){
			var url = Ibos.app.url("user/home/unbindco");
			return $.post(url, param, $.noop, "json");
		},
		/**
		 * 解绑微信企业
		 * @method relieveIboswxqy
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       传出deffered对象
		 */ 
		relieveIboswxqy: function(param){
			var url = Ibos.app.url("user/home/unbindwxqy");
			return $.post(url, param, $.noop, "json");
		},
		/**
		 * 验证信息
		 * @method checkVerify
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       传出deffered对象
		 */
		checkVerify : function(param){
			var url = Ibos.app.url("user/home/checkVerify");
			return $.get(url, param, $.noop, 'json');
		}
	}
};

$(function() {
	Ibos.evt.add({
		//  绑定手机、邮箱
		"bind": function(param, elem) {
			var dialog = Ui.dialog({
				id: 'bind_box',
				title: Ibos.l("USER.BIND_OPERATION"),
				width: '500px',
				cancel: true,
				ok: function() {
					var $verify = $('#inputVerify'),
						verify = $verify.val(),
						checkData;
					if ($.trim(verify) === '') {
						$verify.blink().focus();
						return false;
					}
					if( param.type == "wxqy" ){
						checkData = {
							userid: verify
						};
					}else{
						checkData = {
							uid: Ibos.app.g("currentUid"),
							data: encodeURI(verify),
							op: param.type
						};
					}
					userCenter.op[param.type === 'wxqy' ? "bindIboswxqy": "checkVerify"](checkData).done(function(res) {
						if (res.isSuccess) {
							Ui.tip('@OPERATION_SUCCESS');
							dialog.close();
							window.location.reload();
						} else {
							Ui.tip('@OPERATION_FAILED', 'danger');
							return false;
						}
					});
					return false;
				}
			});
			// 加载对话框内容
			$.ajax({
				url: Ibos.app.url("user/home/bind", {
					uid: Ibos.app.g("currentUid")
				}),
				data: {
					op: param.type
				},
				success: function(res) {
					dialog.content(res);
				},
				cache: false
			});
		},
		// 绑定酷办公账号
		"bindIbosco": function(param, elem) {
			var url = Ibos.app.url('user/home/show', {uid: Ibos.app.g("currentUid")}),
			dialog = Ui.ajaxDialog(url, {
				title: Ibos.l("USER.BIND_OPERATION"),
				id: "ibosco_bind",
				width: '500px',
				cancel: true,
				ok: function() {
					var _dialog = this,
						$account = $("#account"),
						$password = $("#password"),
						account = $account.val(),
						password = $password.val();
					// 验证账号不为空
					if($.trim(account) === ""){
						$account.blink().focus();
						return false;
					}
					// 验证密码不为空
					if($.trim(password) === ""){
						$password.blink().focus();
						return false;
					}

					var param = {account: account, password: password};
					userCenter.op.bindIbosco(param).done(function(res){
						if (res.isSuccess) {
							_dialog.close();
							window.location.reload();
						}
						Ui.tip(res.msg, res.isSuccess ?　"" : "danger");
					});
					return false;
				}
			});
		},
		// 解绑酷办公账号
		"relieveIbosco": function(param, elem) {
			var confirm = Ui.confirm(Ibos.l("USER.SUER_UNBIND_IBOSCO"), function() {
				var param = {uid: Ibos.app.g("currentUid")};
				
				userCenter.op.relieveIbosco(param).done(function(res){
					if (res.isSuccess) {
						Ui.tip(res.msg);
						window.location.reload();
					} else {
						Ui.tip(res.msg, "danger");
						return false;
					}
				});
			});
		},
		// 解绑微信企业账号
		relieveIboswxqy: function(){
			 Ui.confirm(Ibos.l("USER.SUER_UNBIND_WXYQ"), function() {
				userCenter.op.relieveIboswxqy().done(function(res){
					if (res.isSuccess) {
						Ui.tip(res.msg);
						window.location.reload();
					} else {
						Ui.tip(res.msg, "danger");
						return false;
					}
				});
			});

		}
	});
});
