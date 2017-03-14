/**
 * 伪选择框
 * @Todo: 待完善改进后，用于替换 pseudoSelect 作为全局通用
 */

(function(){
	var SelectModel = function(data, settings){
		this.data = data && $.isArray(data) ? data : [];
	}

	SelectModel.prototype = {
		constractor: SelectModel,

		// add: function(optionData, settings){
		// 	optionData = $.extend({
		// 		value: "",
		// 		text: ""
		// 	}, optionData);

		// 	this.data.push(optionData);
		// 	$(this).trigger("selectadd", { data: optionData });
		// },
		
		// remove: function(value){
		// 	for(var i = 0; i < this.data.length; i++){
		// 		if(this.data[i].value == value){
		// 			this.removeByIndex(i);
		// 		}
		// 	}
		// },

		// removeByIndex: function(index){
		// 	var removeData;
		// 	if(this.data[index]) {
		// 		removeData = this.data.splice(index, 1);
		// 		$(this).trigger("selectremove", { index: index, data: removeData});
		// 	}
		// },
		
		getAll: function(){
			return this.data;
		},

		get: function(value){
			return $.grep(this.data, function(d){
				return d.value == value;
			});
		}
	}

	var SelectView = function(model, elements) {
		this.$container = $(elements.container);
		if(!this.$container.length) {
			$.error("SelectView: 找不到 elements.container");
		}

		// 初始化html结构
		var _dt = '<button type="button" class="btn dropdown-toggle" data-toggle="dropdown">' +
				'<i class="caret"></i>' +
			'</button>' +
			'<ul class="dropdown-menu"></ul>';

		this.$container.addClass("btn-group").html(_dt);
		this.$toggle = $('[data-toggle="dropdown"]', this.$container);
		this.$menu = $('ul', this.$container);
		this.model = model;

		this.renderList();
		// $(this.model).on({
		// 	"selectadd": $.noop,
		// 	"selectremove": $.noop
		// })
		// 绑定事件，click 事件相当于 select 的 change事件
		var _this = this;
		this.$container.on("click", "li > a", function(){
			_this.select($.attr(this, "data-value"));
		})
	}
	SelectView.prototype = {
		constractor: SelectView,
		// 构建 menu list
		renderList: function(){
			var allOptions = this.model.getAll(),
				tpl = "",
				itemTpl = '<li data-value="<%= value %>"><a href="javascript:;" data-value="<%= value %>"><%= text %></a></li>',
				selectedValue;

			if(allOptions.length){
				for(var i = 0; i < allOptions.length; i++) {
					tpl += $.template(itemTpl, $.extend({
						value: "",
						text: ""
					}, allOptions[i]));

					if(allOptions[i].selected) {
						selectedValue = allOptions[i].value;
					}
				}
			};

			this.$menu.html(tpl);
			if(selectedValue) {
				this.select(selectedValue);
			} else {
				this.select(allOptions[0].value);
			}
		},

		select: function(value) {
			var data = this.model.get(value)[0];
			if(data) {

				this.$menu.find("li").removeClass("active")
				.filter("[data-value='" + value + "']").addClass("active");

				this.$toggle.html(data.text + ' <i class="caret"></i>');

				this.selected = value;

				$(this).trigger("selectchange", { value: value });
			}
		}
	}


	$.fn.pSelect = function(options){
		return this.each(function(){
			var $elem = $(this);
			if(!$elem.is("select")) {
				$.error("pSelect: 初始化元素必须为 select 对象");
			}

			options = options || {};

			var data = $("option", $elem).map(function(i, o){
				return {
					value: o.value,
					text: o.text,
					selected: o.selected
				}
			}).get();

			var $ct = $("<div></div>").insertBefore($elem);
			
			$elem.hide();
			if(options.width) {
				$ct.css("width", options.width);
			}

			var sv = new SelectView(new SelectModel(data), { container: $ct });
			
			$(sv).on("selectchange", function(evt, evtData){
				$elem.val(evtData.value).trigger("change");
			});

			$elem.data("pSelect", sv);

			return $elem;
		});
	}

})();