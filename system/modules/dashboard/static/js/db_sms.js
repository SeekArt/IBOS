//access
/**
 * 交叉选择器方法集
 * 使用：手机短信管理
 * @class crossSelect
 */
var crossSelect = {
	/**
	 * 左侧选择
	 * @property	leftSelect 
	 * @type		{Jquery}
	 */
	leftSelect: null,
	/**
	 * 右侧选择
	 * @property	rightSelect 
	 * @type		{Jquery}
	 */
	rightSelect: null,
	/**
	 * 移动到左侧选择器
	 * @method moveToLeft
	 * @param {Jquery} jelem 要移动的节点
	 */
	moveToLeft: function(jelem){
		this.leftSelect.append(jelem);
	},
	/**
	 * 移动到右侧选择器
	 * @method moveToLeft
	 * @param {Jquery} jelem 要移动的节点
	 */
	moveToRight: function(jelem){
		this.rightSelect.append(jelem);
	},
	/**
	 * 将左侧选中的移到到右侧
	 * @method leftToRight
	 */
	leftToRight: function(){
		var selected = this.leftSelect.find(":selected");
		this.moveToRight(selected);
	},
	/**
	 * 将右侧选中的移到到左侧
	 * @method rightToLeft
	 */
	rightToLeft: function(){
		var selected = this.rightSelect.find(":selected");
		this.moveToLeft(selected);
	},
	/**
	 * 左侧全选
	 * @method leftSelectAll
	 */
	leftSelectAll: function(){
		this.leftSelect.find("option").prop("selected", true);
	},
	/**
	 * 右侧全选
	 * @method leftSelectAll
	 */
	rightSelectAll: function(){
		this.rightSelect.find("option").prop("selected", true);
	}
};

function beforeSubmit() {
	var enabled = new Array();
	$('#select_left').find('option').each(function(i, n) {
		enabled.push($(n).val());
	});
	$('#enabled_module').val(enabled.join(','));
}
(function() {
	crossSelect.leftSelect = $("#select_left");
	crossSelect.rightSelect = $("#select_right");
	$("#toLeftBtn").on("click", function() {
		crossSelect.rightToLeft();
	});
	$("#toRightBtn").on("click", function() {
		crossSelect.leftToRight()
	});
	$("#select_all_left").on("click", function() {
		crossSelect.leftSelectAll()
	});
	$("#select_all_right").on("click", function() {
		crossSelect.rightSelectAll();
	});
	$("#select_left").on('dblclick', 'option', function() {
		crossSelect.moveToRight(this);
	});
	$("#select_right").on('dblclick', 'option', function() {
		crossSelect.moveToLeft(this);
	});
})();


//setup
(function() {
	$("#sms_enable").on("change", function() {
		$("#sms_setup").toggle();
	});
})();


//manager
(function() {
	$("#date_start").datepicker({target: $("#date_end")});
	$('#sender').userSelect({
		data: Ibos.data.get("user"),
		type: "user",
		maximumSelectionSize: 1
	});
	function removeRows(ids) {
		var arr = ids.split(',');
		for (var i = 0, len = arr.length; i < len; i++) {
			$('#list_tr_' + arr[i]).remove();
		}
	}
	$('#exportsms').on('click', function() {
		var val = U.getCheckedValue('sms[]');
		if ($.trim(val) !== '') {
			/*var url = '<?php echo $this->createUrl( 'sms/export' ); ?>';
			url += '&id=' + val;*/
			var url = Ibos.app.url("dashboard/sms/export", { "id": val});
			window.location.href = url;
		} else {
			Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
		}
	});
	$('#delsms').on('click', function() {
		var val = U.getCheckedValue('sms[]');
		if ($.trim(val) !== '') {
			Ui.confirm(Ibos.l("SMS.SMS_DEL_CONFIRM"), function() {
				// var url = '<?php echo $this->createUrl( 'sms/del' ); ?>';
				var url = Ibos.app.url("dashboard/sms/del");
				$.get(url, {id: val}, function(data) {
					if (data.isSuccess) {
						removeRows(val);
						Ui.tip(U.lang("DELETE_SUCCESS"));
					} else {
						Ui.tip(U.lang("DELETE_FAILED"), 'danger');
					}
				}, 'json');
			});
		} else {
			Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
		}
	});
	var smsSearchDialog = Dom.byId('d_sms_search'),
			smsSearchDialogOptions = {
				title: Ibos.l("SMS.SMS_ADVANVED_SEARCH"),
				content: smsSearchDialog,
				width: 500,
				ok: function() {
					$('#type').val('');
					$('#select_type').find('a').each(function() {
						var id = $(this).data('id');
						if ($(this).hasClass('active')) {
							$('#type').val($('#type').val() + id);
						}
					});
					// var url = '<?php echo $this->createUrl( 'sms/manager', array( 'type' => 'search' ) ); ?>';
					var url = Ibos.app.url("dashboard/sms/manager", {"type" : "search"});
					var param = $('#d_sms_search_form').serializeArray();
					window.location.href = url + '&' + $.param(param);
				},
				cancel: true
			};
	$("#sms_search").search(true, function() {
		$.artDialog(smsSearchDialogOptions);
	});
})();


