// 权限级别的操作
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
			if (!this.disabled) {
				this.$element.val(value);
				this._setLevel(value);
				this.value = value;
				this.$element.trigger("valuechange", {value: value})
			}
		},
		_setLevel: function(value) {
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
			} else if (typeof options === "string") {
				data[options].apply(data, argu);
			}
		})
	}
})();

// 权限级别
(function(){
	var tip = {
		'0': U.lang("DB.POWERLESS"),
		'1': U.lang("DB.ME"),
		'2': U.lang("DB.AND_SUBORDINATE"),
		'4': U.lang("DB.CURRENT_BRANCH"),
		'8': U.lang("DB.ALL")
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
				$(this).closest("label").find('[data-node="funcCheckbox"]').prop("checked", true).trigger("change");
			});
		});
	});
})();
	
$("#limit_setup").bindEvents({
	// 选中功能
	"change [data-node='funcCheckbox']": function(evt){
		$(this).closest("label").toggleClass("active", this.checked);
	},
	// 选中模块 
	"change [data-node='modCheckbox']": function(evt){
		var id = $.attr(this, "data-id");
		Limit.auth.selectMod(id, $.prop(this, "checked"))
	},
	// 选中分类
	"click [data-node='cateCheckbox']": function(evt){
		var id = $.attr(this, "data-id"),
			checked = $.attr(this, "data-checked") === "1"
		Limit.auth.selectCate(id,  !checked);
		$.attr(this, "data-checked", checked ? "0" : "1")
	}
});

