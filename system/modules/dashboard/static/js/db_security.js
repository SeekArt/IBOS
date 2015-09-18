$(function(){
	//ip
	(function() {
		var ipRecTbody = $("#ip_rec_tbody"),
				addOneItem = function(tpl) {
					ipRecTbody.append(tpl);
				};
		// 增加项
		$("#add_ip_rec").on("click", function() {
			var d = new Date(), ipRecTpl = $.template('ip_rec_template', {id: d.getTime()});
			addOneItem(ipRecTpl);
		});
		// 删除项
		$('#ip_rec_table').on("click", ".o-trash", function() {
			$(this).parents("tr").first().remove();
		});
		// 删除选中
		$('[data-act="del"]').on('click', function() {
			var id = '';
			$('[data-check="ip"]:checked').each(function() {
				id += this.value + ',';
			});
			if (id !== '') {
				$('#form_act').val('del');
				$('#sys_security_form').submit();
			} else {
				$.jGrowl(Ibos.l("DATABASE.AT_LEAST_ONE_RECORD"), {theme: 'error'});
				return false;
			}
		});
		// 清空
		$('[data-act="clear"]').on('click', function() {
			$('#form_act').val('clear');
			$('#sys_security_form').submit();
		});
	})();


	//log
	(function() {
	    //日期选择器
	    $("#date_start").datepicker({
	        format: 'mm-dd',
	        target: $("#date_end")
	    });

	    var level = Ibos.app.g("level");
	    var url = Ibos.app.url("dashboard/security/log", {"level" : level});
	    $('#query_act').on('click', function() {
	        var start = $('#start_time').val(), end = $('#end_time').val(), scope = $('#time_scope').val();
	        if (scope == '') {
	            $('#time_scope').blink();
	            return false;
	        }
	        url += '&timescope=' + scope + '&start=' + start + '&end=' + end;
	        window.location.href = url;

	    });
	    $('#actions').on('change', function() {
	        url += '&filteract=' + this.value;
	        window.location.href = url;
	    });
	})();


	//setup
	$("#psw_strength").ibosSlider({
		min: 5,
		max: 32,
		scale: 9,
		range: 'min',
		tip: true,
		tipFormat: function(value) {
			return value + '位'
		},
		target: '#minlength'
	});
})