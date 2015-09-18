/**
 * 积分计算器方法集
 * 使用: 积分设置
 * @class calculator
 */
var creditCalculator = function($context){
	var $display = $context.find("[data-component='display']"),
		$panel = $context.find("[data-component='panel']"),
		$keyboard = $context.find("[data-component='keyboard']");

	var _cache = [],
		// 用于保存最后插入的节点
		$item = null;
	// 有效类型
	var validateType = ["number", "operator", "bracket", "action", "entry"],
		validateOperator = {
			'divide': '/',
			'multiply': '*',
			'minus': '-',
			'plus': '+'
		},
		inputEnable = {
			bracket: true,
			number: true,
			entry: true,
			operator: true,
			action: true
		},
		validateEntrys = {};
	// 保存积分项
	$panel.find("[data-type='entry']").each(function(){
		var name = $.attr(this, "data-value"),
			text = $.text(this);
		validateEntrys[name] = text;
	});

	// 获取结果值
	var get = function(){
		return _cache.join("");
	}

	// 添加节点至屏幕					
	var _addItem = function(value, type){
		$item =  $('<span data-type="' + type + '" data-value="' + value + '" class="' + type + '">'  + value + '</span>');
		$display.append($item);
	}

	// 获取某一节点上存储的数据，包括type, value. 
	var _getData = function($elem){
		var data = {};
		if($elem && $elem.length){
			data.value = $elem.attr("data-value"),
			data.type = $elem.attr("data-type");
		}
		return data;
	}
	
	// 设置括号按键显示内容;
	var _setBracket = function(dir){
		var lastData = _getData($item),
			text,
			value;
		if(lastData){
			switch(lastData.type){
				// fall through
				case "number":
				case "entry":
					text = value = ")";
					break;

				case "operator":
					text = value = "(";
					break;

				// empty;
				case "bracket":
					break;

				default:
					text = "()";
					value = "(";
					break;
			}
		}
		if(text && value){
			$keyboard.find("[data-type='bracket']").attr("data-value", value).html(text);
		}
	}

	// 对某些类型的按键启用或禁用
	var _toggleKeyEnabled = function(types, enable){
		if(!$.isArray(types)){
			types = [types]
		}
		for(var i = 0, len = types.length; i < len; i++){
			var type = types[i]
			if(inputEnable[type] !== enable){
				inputEnable[type] = enable;
				$context.find("[data-type='" + type + "']").toggleClass("disabled", !enable);
			}
		}
	}
	// 设置按键状态
	var _setKeyStatus = function(type){
		switch(type){
			case "number":
				_toggleKeyEnabled(['entry'], false);
				_toggleKeyEnabled(['operator'], true);
				break;
			case "entry":
				_toggleKeyEnabled(['entry', 'number'], false);
				_toggleKeyEnabled(['operator'], true);
				break;
			case "operator":
			default:
				_toggleKeyEnabled(['entry', 'number'], true);
				_toggleKeyEnabled(['operator'], false);
				break;
		}
	};

	// 输入成功时回调 
	var _inputSuccess = function(value, type){
		$context.trigger("input", {
			inputType: type,
			inputValue: value
		}).trigger("change", {
			result: get(),
			template: $display.html()
		})
	};

	// 类型为action时的处理函数， “后退”和“清空”功能
	var _actionHandler = {
		back: function(){
			var _$temp,
				lastData;
			if($item && $item.length){
				// 移除最后一个节点，将$item指向最后一个节点的上一节点
				_$temp = $item.prev(); 
				$item.remove();
				$item = _$temp;
				// 改变按键可用状态
				lastData = _getData($item);
				_setKeyStatus(lastData.type);
				_setBracket();
				
				_cache.pop();
			}
		},
		clear: function(){
			// 清空节点
			$display.empty();
			// 重置$item指向
			$item = null;
			// 重置按键可用状态
			_setKeyStatus();
			_setBracket();

			_cache = [];
		}
	}

	var _inputHandler = {
		number: function(value){
			var lastData = _getData($item);
			value = parseInt(value, 10);
			// 输入数字时仅 0 到 9 之间的数字是有效的
			if(value <= 9 && value >= 0 ) {
				// 如果上一次输入为数字，则不新增节点，只是改变数字
				if(lastData.type === "number"){
					var oldValue = $item.html();
					$item.attr("data-value", oldValue + value).html(oldValue + value);
					_cache[_cache.length - 1] = oldValue + value;
				// 否则新增数字节点
				}else{
					// 数字不能以0开头
					if(value === 0){
						return false
					}
					_addItem(value, "number");
					_setKeyStatus("number");
					_setBracket();
					_cache.push(value);
				}
				_inputSuccess(value, "number");
			}
		},
		entry: function(value){
			// 只有当值在积分项中存在时，才会添加项
			if(validateEntrys[value]){
				_addItem(validateEntrys[value], "entry");
				_setKeyStatus("entry");
				_setBracket();
				_cache.push(value);
				_inputSuccess(value, "entry");
			}
		},
		operator: function(value){
			if(validateOperator.hasOwnProperty(value)){
				_addItem(validateOperator[value], "operator");
				_setKeyStatus("operator");
				_setBracket();
				_cache.push(validateOperator[value]);
				_inputSuccess(value, "operator");
			}
		},
		bracket: function(value){
			if(value === "(" || value === ")"){
				// 若上个输入为条目entry或数字number时，出现右括号，否则出现左括号
				_addItem(value, "bracket");
				_cache.push(value);
				_inputSuccess(value, "bracket");
			}
		}
	}

	var input = function(value, type){
		// 输入类型默认为数值
		if($.inArray(type, validateType) === -1){
			type = "number";
		}
		// 当输入类型没被禁用时
		if(inputEnable[type]){
			_inputHandler[type].call(null, value)
		}
	}

	function getEntryKey(entryName) {
		for(var i in validateEntrys) {
			if(validateEntrys[i] === entryName) {
				return i;
			}
		}
	}
	// 初始化函数, 根据已有节点的值
	var init = function($display){
		$display.find("[data-type]").each(function(){
			var value = $.attr(this, "data-value"),
				type = $.attr(this, "data-type");
			// input(value, type);
			$item = $(this);
			_setKeyStatus(type);
			_setBracket();
			if(type === 'entry') {
				_cache.push(getEntryKey(value));
			} else {
				_cache.push(value);
			}
		});
	}
	_setKeyStatus();
	init($display);

	$context.on("click", "[data-type]", function(){
		var type = $.attr(this, "data-type"),
			value = $.attr(this, "data-value");
		// 如果是“后退”或“清空”等行为
		if(type === "action"){
			_actionHandler[value] && _actionHandler[value].call(null);
			$context.trigger("change", {
				result: get(),
				template: $display.html()
			})
		// 否则，则判断为输入行为
		} else{
			input(value, type);
		}
	});

	$display.on("click", function(evt){
		evt.stopPropagation();
	});
	
	return {
		input: input,
		get: get
	}
}

$(function(){
	var $calc = $("#calculator");
	creditCalculator($calc);
	$calc.on("change", function(evt, data) {
		$("#calculator_input").val(data.result);
		$("#calculator_expression").val(data.template);
	});
	// 鼠标经过提示
	$("#calculator_screen").popover({
		title: U.lang("DB.TIP"),
		content: U.lang("DB.CREDIT_TIP"),
		trigger: "hover"
	});	
})

