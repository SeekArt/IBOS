$(function() {
	var maxExtNum = Ibos.app.g("maxExtNum"),
		$extCreditTable = $("#point_setup_table"),
		$extCreditTbody = $("#point_setup_tbody");

	var _validateMaxNum = function() {
		return $extCreditTbody.find("tr").length < maxExtNum;
	}

	Ibos.evt.add({
		"addCreditRule": function(){
			var $row, $date = new Date;
			if (_validateMaxNum()) {
				$row = $.tmpl("ext_credit_tpl", {
					cid : $date.getTime()
				});
				// 模块初始化完成后，初始化switch控件
				$row.find("[data-toggle='switch']").iSwitch();
				$extCreditTbody.append($row);
			} else {
				Ui.tip(U.lang("DB.CREDIT_RULE_NUM_OVER"), "warning");
			}
		},
		"removeCreditRule": function(param, elem){
			var delId = $(elem).attr('data-id'), removeIdObj = $('#removeId'), removeIdSplit;
			removeId = removeIdObj.val();
			removeIdSplit = removeId.split(',');
			removeIdSplit.push(delId);
			removeIdObj.val(removeIdSplit.join());
			$('#credit_' + delId).remove();
		},
		"resetCreditRule": function(){
			Ui.confirm(Ibos.l("CREDIT.SURE_RESET_CREDIT_SETTING"), function() {
				// @Todo: 这里其实没有做
				window.location.reload();
			})
		}
	})
});