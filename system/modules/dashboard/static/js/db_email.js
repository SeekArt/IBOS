$(function(){
	//check
	(function(){
		if( $("#email_test_send")[0] != undefined ){
			$.formValidator.initConfig({formID: "email_test_send", errorFocus: true});

			$("#testfrom").formValidator()
			.regexValidator({
				regExp: "email",
				dataType: "enum",
				onError: Ibos.l("RULE.EMAIL_INVALID_FORMAT")
			});

			$("#testto").formValidator()
			.regexValidator({
				regExp: "notempty",
				dataType: "enum",
				onError: Ibos.l("RULE.NOT_NULL")
			});
		}
	})();

	//setup
	(function(){
		new P.Tab($("#sent_method"), "label");
		// 新增项
		$("#add_socket_item,#add_smtp_item").on("click", function() {
			var id = $(this).data('id'), d = new Date;
			if (id === 'socket') {
				var socketTemp = $.template("mail_socket_template", {id: d.getTime()});
				//将模板文本生成节点，并对其中的复选框初始化，然后插入表格
				$(socketTemp).find("input[type='checkbox']").label().end().appendTo($("#socket_setup_tbody"));
			} else if (id === 'smtp') {
				var smtpTemp = $.template("mail_smtp_template", {id: d.getTime()});
				$(smtpTemp).appendTo($("#smtp_setup_tbody"));
			}
		});
		// 删除项
		$('#mail_setup_box').on("click", ".o-trash", function() {
			$(this).parents("tr").first().remove();
		});
	})();
})