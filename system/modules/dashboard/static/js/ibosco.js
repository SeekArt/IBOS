$(function() {
	var IbosCo = {
		op: {
			// 解绑酷办公
			"unbindingIbosCo": function() {
				var url = Ibos.app.url('dashboard/cobinding/unbinding');
				return $.get(url, $.noop, "json");
			},
			// 酷办公解绑原有OA后，绑定现登录OA，并将现有OA与酷办公绑定
			"imUnbindingIbos": function() {
				var url = Ibos.app.url('dashboard/cobinding/imUnbindingIbos');
				return $.get(url, $.noop, "json");
			},
			// 统一酷办公和OA的企业代码
			"unifyCode": function(param) {
				var url = Ibos.app.url('dashboard/cobinding/unifyCode');
				return $.post(url, param, $.noop, "json");
			},
			// 退出企业
			"exitBusiness": function() {
				var url = Ibos.app.url('dashboard/cobinding/quitco');
				return $.get(url, $.noop, "json");
			},
			// 创建并绑定酷办公
			"createAndBindingIbosCo": function() {
				var url = Ibos.app.url('dashboard/cobinding/createAndBinding');
				return $.get(url, $.noop, "json");
			},
		},
		//同步时传送的信息
		syncData : function(url, deptCount, userCount, i){
			$.get(url, function(res) {
				var tpl = res.tpl;
				if (tpl == "success" || tpl == "half" || tpl == "error" || tpl == "sending") {
					var template = $.template("result_success_tpl", {data: res});
					$("#wrap_body").html(template);
					$("#sync_data_btn").addClass("btn-primary");
					$("#wrap_footer").slideDown();
				} else {
					var percentage = (i / (deptCount + userCount + 4)) * 100;
							res = $.extend({}, res, {percentage: percentage});
					var template = $.template("result_syncing_tpl", {data: res});
					$("#wrap_body").html(template);
					i++;
					IbosCo.syncData(res.url, deptCount, userCount, i);
				}
			}, "json");
		}
	};

	var $box = $("#rbox_box");

	Ibos.evt.add({
		// 解绑酷办操作
		"unbindingIbosCo": function(param, elem) {
			Ui.confirm(Ibos.l("CO.UNBINDING_IBOSCO_CONFIRM"), function() {
				IbosCo.op.unbindingIbosCo().done(function(res) {
					if (res.isSuccess) {
						Ui.tip(Ibos.l("CO.UNBINDING_SUCCESS"));
						// 解绑成功后返回到登录页
						window.location.href = Ibos.app.url('dashboard/cobinding/index');
					} else {
						Ui.tip(res.msg, "danger");
					}
				});
			});
		},
		// 绑定酷办公用户操作
		"bindingIbosCo": function(param, elem) {
			var accesstoken = elem.getAttribute('data-token');
			var url = "http://www.ibos.cn/dashboard/corp/oabinding?accesstoken=" + accesstoken,
					dialog = Ui.openFrame(url, {
						title: Ibos.l("CO.BINDING_USER"),
						id: "b_dialog",
						padding: 0,
						lock: true,
						width: "420px",
						height: "500px",
						close: function() {
							window.location.reload();
						}
					});

		},
		// 酷办公已绑定其他OA，现解绑酷办公关联OA
		// 1，解绑已登录酷办公超管账号所在企业关联的OA;
		// 2. 解绑后，将酷办公与现已登录的OA关联起来;
		// 3. OA绑定已登录的酷办公所在企业
		"imUnbindingIbos": function(param, elem) {
			Ui.confirm(Ibos.l("CO.SURE_UNBINDING_AND_LINK_NEW_ADRESS"), function() {
				$box.waiting(null, "mini", true);
				IbosCo.op.imUnbindingIbos().done(function(res) {
					if (res.isSuccess) {
						Ui.tip(Ibos.l("OPERATION_SUCCESS"));
						$box.waiting(false);
						// 跳转回当前页面显示绑定信息
						window.location.href = Ibos.app.url('dashboard/cobinding/index');
					} else {
						Ui.tip(res.msg, "danger");
						$box.waiting(false);
					}
				});
			});
		},
		// 统一酷办公和OA的企业代码
		"unifyCode": function(param, elem) {
			var code = $("input[name='code']:checked").val(),
					param = {code: code};
			$box.waiting(null, "mini", true);
			IbosCo.op.unifyCode(param).done(function(res) {
				if (res.isSuccess) {
					Ui.tip(Ibos.l("OPERATION_SUCCESS"));
					$box.waiting(false);
					// 跳转回当前页面显示绑定信息
					window.location.href = Ibos.app.url('dashboard/cobinding/index');
				} else {
					Ui.tip(res.msg, "danger");
					$box.waiting(false);
				}
			});
		},
		// 退出企业
		"exitBusiness": function(param, elem) {
			var name = Ibos.app.g("CoCompanyName");
			Ui.confirm(Ibos.l("CO.SURE_EXIT_COMPANY", {name: name}), function() {
				$box.waiting(null, "mini", true);
				IbosCo.op.exitBusiness().done(function(res) {
					if (res.isSuccess) {
						Ui.tip(Ibos.l("OPERATION_SUCCESS"));
						$box.waiting(false);
						// 跳转回当前页面显示提示创建新企业并绑定
						window.location.href = Ibos.app.url('dashboard/cobinding/index');
					} else {
						Ui.tip(res.msg, "danger");
						$box.waiting(false);
					}
				});
			});
		},
		// 创建并绑定酷办公
		// 1. 在酷办公创建企业
		// 2. 创建企业后绑定当前OA
		// 3. 当前OA绑定酷办公
		"createAndBindingIbosCo": function(param, elem) {
			var name = Ibos.app.g("IbosCompanyName");
			Ui.confirm(Ibos.l("CO.CREAT_AND_BINDING_COMPANY", {name: name}), function() {
				$box.waiting(null, "mini", true);
				IbosCo.op.createAndBindingIbosCo().done(function(res) {
					if (res.isSuccess) {
						Ui.tip(Ibos.l("OPERATION_SUCCESS"));
						$box.waiting(false);
						// 跳转回当前页面显示绑定信息
						window.location.href = Ibos.app.url('dashboard/cobinding/index');
						;
					} else {
						Ui.tip(res.msg, "danger");
						$box.waiting(false);
					}
				});
			});
		},
		//开始同步
		"startSyncData" : function(param, elem){
			var $this = $(this),
				sendinvite = $("#send_request").prop("checked") ? 1 : 0,
				datum = $("input[name='datum']:checked").val(),
				//TODO  传送的地址
				url = Ibos.app.url('dashboard/cosync/sync', {"op" : "init", "sendinvite" : sendinvite, "datum" : datum});
				
			$.get(url, function(res){
				var deptCount = res.deptCount,
					userCount = res.userCount,
					i = 0;

				$("#wrap_footer").slideUp();
				$this.removeClass("btn-primary");
				//TODO  传送的地址
				url = Ibos.app.url("dashboard/cosync/sync", {"op" : "dept"});
				IbosCo.syncData(url, deptCount, userCount, i);
			});
		},
	});
});