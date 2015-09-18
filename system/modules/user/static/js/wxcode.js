(function(){
	// renderQrCode(Ibos.app.s('loginQrcode'))
	var Comet = {
		renderQrCode: (function() {
			var render;
			try {
			    document.createElement('canvas').getContext('2d');
			    render = 'canvas';
			} catch(e) {
			    render = 'table';
			}

			return function(text) {
			    if(!text) return;
			    $('#login_qrcode').empty().qrcode({
			        text: text,
			        width: 160,
			        height: 160,
			        render: render
			    });
			};
		})(),

		cancel: false,
		"connect": function (opts) {
			var defaults = {
				type: "POST",
				url: "",
				dataType: "json",
				timeout: 30000,
				data: {}
			};

			return $.ajax($.extend(true, {}, defaults, opts));
		},
		// 扫描二维码
		"scanCode": function () {
			var _this = this,
				$wrap = $("#login_tip_wrap");
			return this.connect({
				url: 'static.php?type=checklogincode&code=' + Ibos.app.g('loginQrcode')
			})
			.done(function (res, textStatus) {
				if (res.isSuccess) {
					_this.sureLogin(res.code);
					$wrap.removeClass("tcm").addClass("xcm").text(Ibos.l("USER.SCAN_QR_CODE_SUCCESSED"));
				} else {
					//扫描失败, 重新返回二维码图片地址
					Ibos.app.s('loginQrcode', res.code);
					$wrap.removeClass("tcm").addClass("xcm").text(Ibos.l("USER.SCAN_TIMEOUT"));
					_this.renderQrCode(res.code);
					_this.scanCode();
				}
			})
			.fail(function (XMLHttpRequest, textStatus, errorThrown) {
				!_this.cancel && _this.scanCode();
			});
		},
		// 微信页面确定登录
		"sureLogin": function (code) {
			var _this = this;
			return this.connect({
				url: 'static.php?type=checklogin&code=' + code
			}).done(function (res, textStatus) {
				if (res.isSuccess) {
					window.location.reload();
				} else {
					_this.scanCode();
				}
			})
			.fail(function (XMLHttpRequest, textStatus, errorThrown) {
				!_this.cancel && _this.scanCode();
			});
		}
	};

	// 如果绑定了微信，进入页面后开始二维码变化
	if(Ibos.app.g('wxbinding')) {
		// 进入页面后，发起ajax请求
		var promise = Comet.scanCode();
		window.onbeforeunload = function () {
			Comet.cancel = true;
			promise.abort();
		};
		
		Comet.renderQrCode(Ibos.app.g('loginQrcode'));
	}
})();