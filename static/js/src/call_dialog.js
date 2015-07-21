/**
 * 在线通话
 * Glogal
 */
(function() {
	var Call = Ibos.Call;

	$('#other_phone_select').ibosSelect({
		tags: Ibos.data.get("user"),
		tokenSeparators: [",", " "],
		maximumSelectionSize: "1",
		placeholder: "您可以输入号码或者选人"
		// formatSelection: function(data){
		// 	return data.text + "  " + data.type
		// }
	});

	Call.selectMember("#select_other_btn", 
		$("#member_select_box"), 
		$('#other_phone_select')
	);
	Call.byPhoneDial("phone_number_list",
		$("#other_phone_select")
	);

	$('#others_phone_select').ibosSelect({
		tags: Ibos.data.get("user"),
		tokenSeparators: [",", " "],
		maximumSelectionSize: "1",
		placeholder: "您可以输入号码或者选人"
	});
	Call.selectMember("#select_others_btn", 
		$("#member_others_box"), 
		$('#others_phone_select')
	);

	// Call.selectMember("#select_my_btn", 
	// 	$("#member_my_box"), 
	// 	$('#my_phone_select')
	// );

	$('#meeting_phone_select').userSelect({
		data: Ibos.data.get("user"),
		type: "user"
	});

	$(".select2-input").focus(function(){
		var id = $(this).parents(".other-select-block").attr("data-id");
			$number = $("#target_phone_number");
		if(id == "my_phone_select" || id == "others_phone_select"){
			$number.val("");
		}
		Call.byPhoneDial("target_number_list",
			$("#" + id)
		);

		$("#del_number_btn").off("click.del").on("click.del", function(){
			Call.delInputNumber($number, $("#" + id));
		});
	});

	//拨号盘的收缩操作
	$("[data-toggle='dial']").on("click", function(){
		var $this = $(this);
		$(this).toggleClass('active');
		$("[data-content='dial']").slideToggle();
	});

	$("#other_phone_select").on("change", function(){
		var value = $(this).val();
		if(!value){
			$("#other_phone_number").val("");
		}
	});

	//通话类型的TAB切换
	$("#fun_header_nav li").on("click", function(){
		var $this = $(this),
			index = $this.index(),
			$li = $this.parent().children(),
			$contents = $("#fun_box_body>div"),
			$eqContent = $contents.eq(index),
			$dial = $eqContent.find("[data-content='dial']"),
			type = $eqContent.attr("data-type");			
		$li.removeClass('active');
		$this.addClass('active');
		// (type == "unidirec") ? $dial.slideDown() : $dial.slideUp();
		$contents.hide();
		$eqContent.show();
		Call.resetSelect($eqContent);
	});



	Ibos.evt.add({
		"call" : function(param, elem){
			var $this = $(elem),
				type = $this.attr("data-type"),
				$wrap = $this.parents(".call-content-wrap"),
				dialog = Ui.getDialog("opt_call");

			switch(type){
				// 单向通话功能
				case "unidirec":
					var phone = $("#other_phone_select").val(),
						validParam = { phone: phone },
						isPass = Call.formValidator(type, validParam);

					if(isPass){
						Call.connect([phone], type);
					}

					break;
				//双向通话功能
				case "bidirec":
					var myPhone = $('#my_phone_select').val(),
						otherPhone = $('#others_phone_select').val(),
						validParam = { myPhone: myPhone, otherPhone: otherPhone },
						isPass = Call.formValidator(type, validParam);

					if(isPass){
						Call.connect([myPhone, otherPhone], type);
					}
					break;

				//多人会议功能
				case "meeting":
					var inside = $("#meeting_phone_select").val(),
						outside = $("#outside_meeting_phone").val(),
						validParam = { inside: inside, outside: outside },
						isPass = Call.formValidator(type, validParam);

					if(isPass){
						Call.connect([inside, outside], type);
					}
					break;
			}
		},
		"delPhoneNum": function(param, elem){
			var $this = $(elem),
				$storeInput = $this.parents(".phone-dial-content").find('input'),
				selectVal = $this.parents(".phone-dial-list").attr("data-target"),
				$select = $("#" + selectVal);
			Call.delInputNumber($storeInput, $select);
		},
		"dialToggle": function(param, elem){
			var $this = $(elem),
				$wrap = $this.parents(".call-content-wrap"),
				$cltr = $wrap.find("[data-toggle='dial']"),
				$toggleCont = $wrap.find("[data-content='dial']");
			$cltr.toggleClass("active");
			$toggleCont.slideToggle();
		} 
	});

})();