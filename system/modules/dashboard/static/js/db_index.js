	$(document).ready(function() {
		//日期计数器
		var dateTally = $("#tally");
		var dateCount = Math.floor((Ibos.app.g("nowTime") - Ibos.app.g("installTime")) / (3600 * 24));
		dateCount = dateCount < 10 ? "00" + dateCount : (dateCount < 100 ? "0" + dateCount : dateCount);
		dateTally.tallyCounter({
			count: dateCount,
			speed: 100,
			imgPath: Ibos.app.g("assetUrl") + '/image/counter/'
		});
		//系统开关
		var systemSwitch = $("#system_switch");
		systemSwitch.on("change", function() {
			var enabled = this.checked, val = 1, url = Ibos.app.url("dashboard/index/switchstatus");
			if (enabled) {
				val = 0;
			}
			$.post(url, {val: val}, function(data) {
				if (data.IsSuccess) {
					$("#switch_status").parent().toggleClass("card-flip");
					Ui.tip(U.lang("OPERATION_SUCCESS"));
				} else {
					Ui.tip(U.lang("DB.SHUTDOWN_SYSTEM_FAILED"), "danger");
				}
			}, 'json');
		});

		// ajax请求安全提示
		$("#securityTips").html("<img src='" + Ibos.app.getStaticUrl("/image/common/loading.gif") + "' />");
		$.ajax({
			type: "get",
			url: Ibos.app.url("dashboard/index/getsecurity"),
			dataType: 'html',
			timeout: 15000, // 超时15秒
			success: function(data) {
				$("#securityTips").html(data);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$("#securityTips").html(U.lang("DB.LOAD_SECURITY_INFO_FAILED"));
			}
		});

		var dialogs = {
			inputAuthCode: function() {
				Ui.dialog({
					id: "d_input_auth_code",
					title: U.lang("DB.LICENSE_KEY"),
					content: document.getElementById("input_auth_code_dialog"),
					ok: function() {
						var content = this.DOM.content;
						if ($.trim($("#license_key").val()) === "") {
							alert(U.lang("DB.ENTER_LICENSEKEY"));
							return false;
						}
						content.find("form").submit();
					},
					width: 400,
					cancel: true
				})
			}
		}

		//皮肤选择
		$("#bgstyle_select_list").on("change", "input[type='radio']", function(){
			var $this = $(this),
				type = $this.val(),
				param = {type: type},
				url = "dashboard/background/skin";
			$.post(Ibos.app.url(url), param, function(res){
				if(res.isSuccess){
					Ui.tip(Ibos.l("OPERATION_SUCCESS"));
					//加载对应的css文件
				}else{
					Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
				}
			});
		});

		$(document).on("click", '[data-click="inputAuthCode"]', function() {
			dialogs.inputAuthCode();
		})
		.on("click", '[data-click="showAuthInfo"]', function() {
			$("#show_auth_info_dialog").show().position({my: "center center", of: window});
		})
		.on("click", '[data-click="hideAuthInfo"]', function() {
			$("#show_auth_info_dialog").hide();
		})

	});
