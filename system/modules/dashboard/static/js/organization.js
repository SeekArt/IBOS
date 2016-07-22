/**
 * organization.js
 * 组织架构模块通用JS
 * IBOS
 * @module		Global
 * @submodule   Organization
 * @author		inaki
 * @version		$Id$
 * @modified	2013-07-02 
 */

// PrivilegeLevel
(function() {
	var PrivilegeLevel = function($element, options) {
		this.$element = $element;
		this.options = $.extend({}, PrivilegeLevel.defaults, options);
		this.value = this.options.value || $element.val() || 0;
		// this.value = parseInt(value, 10);
		this.text = this.options.text || $element.attr("data-text") || "";
		this.disabled = this.$element.prop("disabled");
		this._init();
	}
	PrivilegeLevel.prototype = {
		constructor: PrivilegeLevel,
		_init: function() {
			this.$element.hide();
			this._build();
		},
		_build: function() {
			var $anchor = $("<a class='privilege-level' href='javascript:;'><i></i><p></p></a>");
			this.$anchor = $anchor.insertBefore(this.$element);
			this._setLevel(this.value);
			this.setText(this.text);
			this._bindEvent();
			if (this.disabled) {
				this.setDisabled();
			}
		},
		_bindEvent: function() {
			var that = this;
			this._unbindEvent();
			this.$anchor.on("click.level", function() {
				if (that.value == 8) {
					that.setValue(0);
				} else if (that.value == 0) {
					that.setValue(1);
				} else {
					that.setValue(that.value * 2);
				}
			})
		},
		_unbindEvent: function() {
			this.$anchor.off(".level");
		},
		setValue: function(value) {
			// @Debug
			// console && console.assert((typeof value === "number"), "(Level.setLevel): typeof value must be number");
			if (!this.disabled) {
				this.$element.val(value);
				this._setLevel(value);
				this.value = value;
				this.$element.trigger("valuechange", {value: value})
			}
		},
		_setLevel: function(value) {
			// @Debug
			// console && console.assert((typeof value === "number"), "(Level.setLevel): typeof value must be number")
			var cls = "";
			if (value) {
				cls += "level" + value
			}
			this.$anchor.find("i").attr("class", cls);
		},
		setText: function(text) {
			this.$anchor.find("p").html(text)
		},
		setDisabled: function() {
			this._unbindEvent();
			this.disabled = true;
			this.$element.prop("disabled", true);
			this.$anchor.addClass("disabled");
		},
		setEnabled: function() {
			this._bindEvent();
			this.disabled = false;
			this.$element.prop("disabled", false);
			this.$anchor.removeClass("disabled")
		}
	}
	$.fn.privilegeLevel = function(options) {
		var argu = Array.prototype.slice.call(arguments, 1);
		return this.each(function() {
			var $el = $(this),
					data = $el.data("privilegeLevel");
			if (!data) {
				$el.data("privilegeLevel", data = new PrivilegeLevel($el, options))
			}
			if (typeof options === "string") {
				data[options] && data[options].apply(data, argu);
			}
		})
	}
})();


var Organization = {
	auth: {
		selectMod: function(pid, status){
			status = status === false ? false : true;
			$("#limit_setup").find("[data-node='funcCheckbox'][data-pid='" + pid + "']")
			.prop("checked", status)
			.trigger("change");
		},

		selectCate: function(pid, status){
			status = status === false ? false : true;
			$("#limit_setup").find("[data-node='modCheckbox'][data-pid='" + pid + "']")
			.prop("checked", status)
			.trigger("change");
		}
	}
};

// 权限级别
(function(){

	var tip = {
		'0': Ibos.l("ORG.POWERLESS"),
		'1': Ibos.l("ORG.ME"),
		'2': Ibos.l("ORG.AND_SUBORDINATE"),
		'4': Ibos.l("ORG.CURRENT_BRANCH"),
		'8': Ibos.l("ORG.ALL")
	}
	$(function(){
		$("[data-toggle='privilegeLevel']").each(function(){
			var $elem = $(this),
				ins,
				title;

			$elem.privilegeLevel();
			ins = $.data(this, "privilegeLevel");
			title = tip[ins.value];

			ins.$anchor.tooltip({
				title: title,
				trigger: "hover"
			}).on("click", function(){
				var insTooltip = $.data(this, "tooltip");
				insTooltip.options.title = tip[$elem.val()];
				insTooltip.show();
				
				var aInput = $(this).siblings("input"),
					bChecked = true;
				if( (aInput[0].value == 0) && (aInput[1].value == 0) ){
					bChecked = false;
				}
				$(this).closest("ul").find('[data-node="funcCheckbox"]:last').prop("checked", bChecked).trigger("change");

			});
		});
	});
})();

// 岗位成员列表
Organization.memberList = (function(){
	// 根据ID从Ibos.data中获取相关信息，包括图像地址，所属部门及用户名
	var _getUserData = function(id){
		var userData,
			deptData,
			results;
		if(Ibos.data && typeof id !== "undefined"){
			userData = Ibos.data.getUser(id);
			results = {
				id: id,
				imgurl: userData.avatar || "./data/avatar/noavatar_middle.jpg",
				user: userData.text || "",
				department: userData.department || ""
			}
		}
		return results||{};
	}


	// 值管理
	var valueManager = function(values){
		// 必须为Array
		if(!$.isArray(values)){
			values = [];
		}
		var _add = function(id, callback){
			// 已存在Id时返回
			if ($.inArray(id, values) === -1) {
				values.push(id);
				if($.isFunction(callback)){
					callback(id);
				}
			}
		};
		var _remove = function(id, callback){
			// 已存在Id时返回
			var index = $.inArray(id, values);
			if (index !== -1) {
				values.splice(index, 1);
				if($.isFunction(callback)){
					callback(id);
				}
			}
		}

		return {
			add: function(ids, callback) {
				ids = $.isArray(ids) ? ids : [ids];
				for(var i = 0; i < ids.length; i++) {
					_add(ids[i], callback);
				}
			},
			remove: function(ids, callback){
				ids = $.isArray(ids) ? ids : [ids];
				for(var i = 0; i < ids.length; i++) {
					_remove(ids[i], callback);
				}
			},
			get: function(){ return values.join(",") }
		}
	};

	var init = function(values){
		var member = valueManager(values);
		var $list = $("#org_member_list"),
			$add = $("#org_member_add"),
			$box = $("#member_select_box"),
			$value = $("#member"),
			member_tpl = "org_member_tpl",
			$emptyTip = $("#no_data_tip"),
			$submit = $("#submit_put_wrap"),
			memberBox;

		// 改变视图，同步更新表单对应控件的值
		var addMember = function(id){
			var data = _getUserData(id);
			$.tmpl(member_tpl, data).prependTo($list);
			$value.val(member.get());
		}
		var removeMember = function(id){
			$("#member_" + id).remove();
			$value.val(member.get());
		}

		$box.selectBox({
			data: Ibos.data && Ibos.data.get(),
			type: "user",
			values: [].concat(values)
		}).hide();


		memberBox = $box.data("selectBox");
		$(memberBox).on("slbchange", function(evt, data){
			if (data.checked) {
				member.add(data.id, function(id){
					addMember(id);
					$emptyTip.hide();
				});
			} else {
				member.remove(data.id, function(id){
					removeMember(id);
					var liLength = $list.find("li").length;
					if(liLength){
						$emptyTip.hide();
					}else{
						$emptyTip.show();
					}
				});
			}
			$submit.toggle(!!memberBox.values.length);
		});

		$add.click(function() {
			$box.show().position({
				at: "left-200",
				my: "bottom+20",
				of: "#org_member_add"
			});
			// 打开选人框时即时刷新人员列表
			memberBox.refreshList();
		})

		// 移除成员
		$list.on("click", "[data-act='removeMember']", function(){
			var id = $.attr(this, "data-id");
			member.remove(id, function(id){
				removeMember(id);
				memberBox.removeValue(id);
			});
		})
	}

	return {
		init: init
	}
})();

