/**
 *! 协同云申请页
 * @author 		ibos
 */
$(function() {
	function showValiTip(controlId, msg) {
		var elem = document.getElementById(controlId + '_tip');
		if(elem) {
			elem.innerHTML = msg;
			elem.style.display = "block";
		}
	}
	function hideValiTip(controlId) {
		var elem = document.getElementById(controlId + '_tip');
		if(elem) {
			elem.style.display = "none";
		}
	}

	var valiControls = {
		'companyname': function($elem){
			return $.trim($elem.val()) === '' ? '请填写公司名称！' : true;
		},

		'contact': function($elem){
			return $.trim($elem.val()) === '' ? '请填写联系人！' : true;
		},

		'tel': function($elem){
			var val = $elem.val();
			var mobileReg = /^1\d{10}$/;
			var telReg = /^(([0\+]\d{2,3}-)?(0\d{2,3})-)?(\d{7,8})(-(\d{3,}))?$/;
			return $.trim(val) === '' ?
				'请填写联系电话！' : 
				!mobileReg.test(val) && !telReg.test(val) ?
				'联系电话格式不正确！' :
				true;
		},

		'email': function($elem){
			var val = $elem.val();
			var emailReg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
			return $.trim(val) === '' ?
				'请填写联系邮箱！' : 
				!emailReg.test(val) ?
				'联系邮箱格式不正确！' :
				true;
		},

		'adminaccount': function($elem){
			var val = $elem.val();
			return $.trim(val) === '' ? '请填写管理员账号！' :  true;
		},

		'adminpassword': function($elem){
			var val = $elem.val();
			return $.trim(val) === '' ? '请填写管理员密码！' : true;
		}
	};

	function validateItem(elemId) {
		var $elem;

		if(valiControls[elemId]) {
			$elem = $("#" + elemId);
			var msg = valiControls[elemId]($elem);
			if(msg !== true) {
				showValiTip(elemId, msg);
				return false;
			} else {
				hideValiTip(elemId);
				return true;
			}
		}

		return false;
	}

	function validateForm() {
		var ret = true;
		$.each(valiControls, function(key, v) {
			if(validateItem(key) !== true) {
				// $('#' + key).focus();
				ret = false;
			}
		});
		return ret;
	}

	$('#apply_form').on('blur', 'input', function() {
		validateItem(this.id);
	})
	.on('submit', function() {
		return validateForm();
	})
});