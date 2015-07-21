// PrivilegeLevel
/**
 * 权限等级
 * @todo  抽离核心逻辑，不要依赖html和css, 修改使之更具通用性
 */
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
				this.$element.trigger("change")
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

