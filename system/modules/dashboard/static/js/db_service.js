$(function() {
	$("#toggle_apply_content").on("click", function() {
		$("#apply_step_content").slideToggle();
		$(this).siblings("i").toggleClass("down");
	});

	$("#login_submit").on("click", function() {
		var $this = $(this),
			username = $('#username').val(),
			password = $("#password").val(),
			url = Ibos.app.url('dashboard/service/login'),
			param = {username: username, password: password};
		$("#bind_info_form").waiting(null, 'normal', true);

		$.post(url, param, function(res) {
			if (res.isSuccess) {
				$("#bind_info_form").waiting(false);
				var info = res.username + "(" + res.email + ")";
				$("#login_info_wrap").html(info);
				$this.hide();
				$("#web_tip").html("");
				$("#bing_tip").html("");
				$("#open_app_services").show();
				Ui.tip('登陆成功');
			} else {
				$("#bind_info_form").waiting(false);
				Ui.tip(res.msg, "danger");
			}
		}, 'json');
		
	});
	
	$("#open_app_services").on("click", function() {
		var url = Ibos.app.url('dashboard/service/open');
		$.post(url, {}, function(res) {
			if (res.isSuccess) {
				$("#bind_info_form").waiting(false);
				Ui.tip('开通成功');
				setTimeout(function(){
					window.location.href = Ibos.app.url('dashboard/service/index');
				}, 500);
			} else {
				$("#bind_info_form").waiting(false);
				Ui.tip(res.msg, "danger");
			}
		}, 'json');
	});
});