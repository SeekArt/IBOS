/**
 * dashboard.js
 * 后台模块
 * @module Dashboard
 * @author inaki
 * @version $Id: dashboard.js 5453 2015-08-14 10:25:36Z gzzz $
 * @modified 2013-05-04
 */

/**
 * 表格处理方法集
 * @class customTable
 */

var customTable = {
	/**
	 * 开合对应tbody
	 * @method collapseline
	 * @param {Jquery} target 对应的tbody的对象
	 */
	collapseLine: function(target){
		var display = target.css("display");
		if(display === "none"){
			customTable.showLine(target);
		}else{
			customTable.hideLine(target);
		}
		return false;
	},
	/**
	 * 展开对应tbody
	 * @method showLine
	 * @param {Jquery} target 对应的tbody的对象
	 */
	showLine: function(target){
		if(target.css("display") === "none"){
			target.show().prev().addClass("active");
		}
	},
	/**
	 * 收起对应tbody
	 * @method hideLine
	 * @param {Jquery} target 对应的tbody的对象
	 */
	hideLine: function(target){
		if(target.css("display") !== "none"){
			target.hide().prev().removeClass("active");
		}
	},
	/**
	 * 新增一行
	 * @method addLine
	 * @param {Jquery} target 行插入的容器
	 * @param {String} [temp=""] Html模板，没有时为空行
	 * @param {String} [id] 行ID，同时作为表单name序号
	 * @param {String} [prefix] ID前缀
	 * @parma {Function} [callback] 回调
	 */
	addLine: function(target, temp, id, prefix, callback){
		var line;
		if(typeof temp === "string"){
			line = $("<tr>").addClass("msts-last");
			id && (temp = temp.replace(/@/g, id));
			line.html(temp||"");
		}else{
			line = temp;
		}
		id && line.attr("id", id);
		target.children().last().removeClass();
		target.append(line);
		callback && callback(line)
		//展开对应tbody
		customTable.showLine(target);
		return false;
	},
	/**
	 * 删减一行
	 * @method removeLine
	 * @param {Jquery} line 要删减的行对象
	 */
	removeLine: function(line){
		var target = line.parent(),
			//当前tbody内行数
			length = target.children().length;
		//当删除行为最后一行时
		if(line.index() === length -1){
			line.prev().addClass("msts-last");
		}
		line.remove();
		//若当前tbody已空，则折叠
		target.children().length == 0 && customTable.hideLine(target);
	},
	/**
	 * 重置行内输入框的值
	 * @method resetLine
	 * @param {Jquery} line 要重置的行对象
	 */
	resetLine: function(line){
		line.find("input[type='text'], textarea").val("");
		return false;
	}
};

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

/**
 * 虽然不想吐槽，但老实说，但这是一个没什么用的类
 */
(function(){
	/**
	 * 生成数字牌的类
	 * @class Tally
	 * @constructor
	 * @param {Element||Jquery} element		容器
	 * @param   {Key-Value}     options         配置
	 * @param {Number}			num			数值
	 * @param {Number}			speed		翻动速率
	 * @param {Function}		callback	回调函数
	 */
	var Tally = function(element, options){
		this.element = $(element);
		this.num = options.num;
		this.speed = options.speed||100;
		this.callback = options.callback;
		this.start = 0;
		this.imgPath = options.imgPath||"../../static/image/counter";
		this.init();
	};
	Tally.prototype = {
		/**
		 * 初始化函数
		 * @method init
		 * @private
		 */
		init: function(){
			!this.element.hasClass("tally-item") && this.element.addClass("tally-item");
			this.createItem();
			this.createBgItem();
			this.refresh(this.num, this.callback);
		},
		/**
		 * @method createItem
		 * @private
		 */
		createItem: function(){
			var upWrap, downWrap;
			this.imgUp = $("<img>").attr("src", this.imgPath+"/up/" + this.start +".png").css("visibility", "hidden");
			this.imgDown = $("<img>").attr("src", this.imgPath+"/down/" + this.start + ".png").css("visibility", "hidden");
			upWrap = $("<div>").addClass("tally-top").append(this.imgUp);
			downWrap = $("<div>").addClass("tally-bottom").append(this.imgDown);
			this.item = $("<div>").append(upWrap, downWrap).addClass("tally-item-front");
			this.element.append(this.item);
		},
		/**
		 * @method createBgItem
		 * @private
		 */
		createBgItem: function(){
			var upWrap, downWrap;
			this.imgUpBg = $("<img>").attr("src", this.imgPath+"/up/" + this.start +".png");
			this.imgDownBg = $("<img>").attr("src", this.imgPath+"/down/" + this.start +".png");
			upWrap = $("<div>").addClass("tally-top").append(this.imgUpBg);
			downWrap = $("<div>").addClass("tally-bottom").append(this.imgDownBg);
			this.itemBg = $("<div>").append(upWrap, downWrap).addClass("tally-item-back");
			this.element.append(this.itemBg);
		},
		/**
		 * 刷新已有Tally对象的数值
		 * @method refresh
		 * @param {Number}		num			新数值
		 * @param {Function}	callback	回调函数
		 */
		refresh: function(num, callback){
			this.refreshValue(this.imgUpBg, num);
			this.imgUp.css({
				"height": "23px",
				"visibility": "visible"
			}).stop().animate({height: "0"}, this.speed, $.proxy(function(){
				this.refreshValue(this.imgDown, num, "down");

				this.imgDown.css({
					"height": "0",
					"visibility": "visible"
				}).stop().animate({height: "22px"}, this.speed, $.proxy(function(){
					this.refreshValue(this.imgDownBg, num, "down");
					callback && callback();
				}, this));
				this.refreshValue(this.imgUp, num);
			}, this));
		},
		/**
		 * 刷新图片路径
		 * @method refreshValue
		 * @param {Jquery} elem			对应图片jquery对象
		 * @param {Number} num			新数值
		 * @param {String} [type="up"]	图片对应文件夹，值为"up"|"down"
		 * @private
		 */
		refreshValue: function(elem, num, type){
			type = type||"up";
			elem.attr("src", this.imgPath+"/" + type + "/"+ num +".png");
		}
	};
	/**
	 * @class $.fn
	 */
	/**
	 * 生成可翻动数字牌，具体效果请参照后台主页，使用类Tally进行初始化
	 * @method	$.fn.tally
	 * @uses	Tally
	 * @param   {Key-Value}     options         配置
	 * @param	{Number}		num				数值
	 * @param	{Number}		[speed=100]		翻动速率
	 * @param	{Function}		[callback]		翻动完成后的回调函数
	 * @return	{Jquery}						jQuery对象
	 */
	$.fn.tally = function(options){
		return this.each(function(){
			var that = $(this),
				thatTally = that.data("tally");
			//未初始化
			if(!thatTally){
				that.data("tally", new Tally(that, options));
			}else{
				//已初始化
				if(options.speed){
					thatTally.speed = options.speed;
				}
				options.num !== undefined && thatTally.refresh(options.num, options.callback)
			}
		});
	};
})();

//生成日期计数
(function(){
	/**
	 * 生成日期计数
	 * @class TallyCounter
	 * @constructor
	 * @param {Element||Jquery} element		容器节点对象
	 * @param   {Key-Value}     options         配置
	 * @param {String}			count		数值字符串
	 * @param {Number}			[speed=100]	翻动速率
	 */
	var TallyCounter = function(element, options){
		this.element = $(element);
		this.options = options;
		this.count = options.count;
		this.speed = options.speed;
		this.init();
	};
	TallyCounter.prototype = {
		/**
		 * @method init
		 * @private
		 */
		init: function(){
			this.countArray = String.prototype.split.call(this.count, "");
			this.build();
		},
		/**
		 * 更新子节点
		 * @method build
		 */
		build: function(){
			var i = 0, arr = this.countArray,
				length = arr.length, item;
			this.element.empty();
			for(; i < length; i++){
				item = $("<div>");
				this.element.append(item);
				item.data("start", 0);
				this.turn(item, arr[i]);
			}
		},
		/**
		 * 数值轮翻, 从0翻到指定数值
		 * @method turn
		 * @param {Jquery}	item	数值对应的jquery对象
		 * @param {num}		num		数值
		 */
		turn: function(item, num){
			var that = this,
				start = item.data("start");
			if(start <= num){
				item.tally({
					num: start,
					speed: that.speed,
					callback: function(){
						start++;
						item.data("start", start);
						that.turn(item, num);
					},
					imgPath: that.options.imgPath
				});
			}
		}
	}
	/**
	 * @class $.fn
	 */
	/**
	 * 生成日期计数器，具体效果请参照后台主页，使用类TallyCounter进行初始化
	 * @method	$.fn.tallyCounter
	 * @uses	TallyCounter
	 * @param	{Key-Value}     [options]       配置
	 * @param	{String}		count			数值字符串
	 * @param	{Number}		[speed=100]		翻动速率
	 * @return	{Jquery}						jQuery对象
	 */
	$.fn.tallyCounter = function(options){
		return this.each(function(){
			options.count = options.count||"0";
			var that = $(this),
				thatTallyCounter = that.data("tallyCounter");
			//未初始化
			if(!thatTallyCounter){
				that.data("tallyCounter", new TallyCounter(that, options));
			}else{
			//已初始化
				TallyCounter.call(thatTallyCounter, that, options);
			}

		});
	}
})();

var Dragbox;
(function(window, $){
	var defaults = {
		element:		null,
		resize:			true,
		drag:			true,
		container:		$(document.body),
		cls:			"drag-box",
		content:		"",
		onMoveStart:	null,
		onMove:			null,
		onMoveStop:		null,
		onResizeStart:  null,
		onResize:		null,
		onResizeEnd:	null,
		onRemove:		null
	}
	/**
	 * 创建或初始化可拖拽的对象
	 * @class Dragbox
	 * @constructor
	 * @param {Object} 		options 				配置键值对
	 * @param {Jquery} 	 	[element] 				要初始化的节点，没有时自动新建
	 * @param {Boolean} 	[resize=true] 			是否允许拉伸
	 * @param {Boolean} 	[drag=true] 			是否允许拖拽
	 * @param {Jquery} 		[container=$("body")] 	外容器，作为范围限制
	 * @param {String} 		[cls="drag-box"] 		拖拽块样式类
	 * @param {String} 		[content] 				拖拽块内容
	 * @param {Function} 	[onMoveStart] 			
	 * @param {Function} 	[onMove] 			
	 * @param {Function} 	[onMoveStop] 			
	 * @param {Function} 	[onResizeStart] 			
	 * @param {Function} 	[onResize] 			
	 * @param {Function} 	[onResizeEnd] 			
	 * @param {Function} 	[onRemove] 			
	 */
	Dragbox = function(options){
		this.options = $.extend({}, defaults, options);
		this.init();
	}
	/**
	 * Dragbox的全局ID
	 * @property 	gid
	 * @type 		{Number}
	 * @default 	0
	 */
	Dragbox.gid = 0;
	/**
	 * Dragbox的全局数组，用于存放dragbox对象
	 * @property 	group	 
	 * @type 		{Array}
	 */
	Dragbox.group = [];
	/**
	 * @method 	Dragbox.get
	 * @param 	{Number} id 根据全局ID获取dragbox对象
	 * @return 	{Object} dragbox对象
	 */	
	Dragbox.get = function(id){
		for(var i = 0; i < Dragbox.group.length; i++){
			if(Dragbox.group[i].gid == id){
				return Dragbox.group[i].context;
			}
		}
	}
	/**
	 * @method 	Dragbox.remove
	 * @param 	{Number} id 根据全局ID移除dragbox对象
	 */	
	Dragbox.remove = function(id){
		for(var i = 0; i < Dragbox.group.length; i++){
			if(Dragbox.group[i].gid == id){
				Dragbox.group.splice(i, i+1);
			}
		}
	}
	Dragbox.prototype = {
		/**
		 * 初始化函数
		 * @method init
		 * @private
		 */
		init: function(){
			this.options.element = $(this.options.element);
			this.options.container = $(this.options.container).css("position", "relative");
			Dragbox.gid++;
			this.gid = Dragbox.gid;
			Dragbox.group.push({
				gid: this.gid,
				context: this
			});
			this.initBox();
		},
		/**
		 * 初始化容器
		 * @method initBox
		 * @private
		 */
		initBox: function(){
			var box = this.createBox();
			this.options.container.append(box);
			this.bindEvent();
		},
		/**
		 * 创建容器
		 * @method createBox
		 * @private
		 */
		createBox: function(){
			var that = this,
				options = this.options;
			//当存在element属性时，容器指向element，否则新建容器
			this.boxNode = options.element.length ?
				options.element:
				$("<div>");
			this.boxNode.addClass(options.cls);

			this.contentNode = this.boxNode.find(".drag-content");
			//当不存在文本节点时，新建
			if(!this.contentNode.length){
				this.contentNode = $("<div>").addClass("drag-content");
				this.content(options.content);
				this.boxNode.append(this.contentNode);
			}
			//当允许定义大小
			if(options.resize){
				this.resizeNode = this.boxNode.find(".drag-resize");
				//当不存在拉伸节点时，新建
				if(!this.resizeNode.length){
					this.resizeNode = $("<div>").addClass("drag-resize");
					this.boxNode.append(this.resizeNode);
				}
			}
			//存储当前dragbox对象对应gid
			this.boxNode.data("id", this.gid);
			return this.boxNode;
		},
		/**
		 * 修改内容
		 * @method 	content
		 * @param 	{String} text 文本或格式的html;
		 */
		content: function(text){
			this.contentNode.html(text);
		},
		/**
		 * 绑定事件
		 * @method bindEvent
		 * @private
		 */
		bindEvent: function(){
			var that = this;
			if(this.options.drag){
				this.boxNode.on("mousedown", function(evt){
					that.startMove(evt);
				});
			}
			if(this.options.resize){
				this.resizeNode.on("mousedown", function(evt){
					that.startResize(evt);
					evt.stopPropagation();
				})
			}
			this.boxNode.on("dblclick", function(){
				that.remove();
			})
		},
		/**
		 * 移动开始及过程
		 * @method 	startMove
		 * @param 	{jEvent} evt  jquery事件对象
		 * @private
		 */
		startMove: function(evt){
			var that = this,
				currentPos = this.boxNode.position(),
				currentX = currentPos.left,
				currentY = currentPos.top,
				startPosX = evt.clientX,
				startPosY = evt.clientY,
				endPosX, endPosY,
				newPosX, newPosY,
				scrollTop;
			that.execCallback(that.options.onMoveStart);
			//禁止文本选择
			$("body").noSelect();
			$(document).on("mousemove.dragbox-move", function(endEvt){
				that.execCallback(that.options.onMove);
				scrollTop = $(document).scrollTop();
				endPosX = endEvt.clientX;
				endPosY = endEvt.clientY;
				newPosX = currentX + endPosX - startPosX;
				newPosY = currentY + endPosY - startPosY;
				that.boxNode.css("left", that.fixMoveValueX(newPosX));
				that.boxNode.css("top", that.fixMoveValueY(newPosY));
			});
			$(document).on("mouseup.dragbox-move", function(){
				that.execCallback(that.options.onMoveStop);
				that.stopMove();
			});
		},
		/**
		 * 移动结束
		 * @method stopMove
		 * @private
		 */
		stopMove: function(){
			//恢复文本选择
			$("body").noSelect(false);
			//解绑事件
			$(document).off(".dragbox-move");
		},
		/**
		 * 对象在X轴上移动时位置的修正
		 * @method 	fixMoveValueX
		 * @private
		 * @param 	{Number} value 	修正前的值
		 * @return 	{Number} 		修正后的值
		 */
		fixMoveValueX: function(value){
			var containerWidth = this.options.container.outerWidth(),
				boxWidth = this.boxNode.outerWidth(),
				gap = containerWidth - boxWidth;
			return value = value < 0 ? 0 : value > gap ? gap : value;
		},
		/**
		 * 对象在Y轴上移动时位置的修正
		 * @method 	fixMoveValueY
		 * @private
		 * @param 	{Number} value 	修正前的值
		 * @return 	{Number} 		修正后的值
		 */
		fixMoveValueY: function(value){
			var containerHeight = this.options.container.outerHeight(),
				boxHeight = this.boxNode.outerHeight(),
				gap = containerHeight - boxHeight;
			return value = value < 0 ? 0 : value > gap ? gap : value;
		},
		/**
		 * 拉伸开始
		 * @method 	startResize
		 * @private
		 * @param 	{jEvent} evt 	jquery事件对象
		 */
		startResize: function(evt){
			var that = this,
				startPosX = evt.clientX,
				startPosY = evt.clientY,
				endPosX, endPosY,
				currentWidth = that.boxNode.width(),
				currentHeight = that.boxNode.height(),
				newWidth, newHeight;
			that.execCallback(that.options.onResizeStart);
			//禁止文本选择
			$("body").noSelect();
			$(document).on("mousemove.dragbox-resize", function(endEvt){
				that.execCallback(that.options.onResize);
				endPosX = endEvt.clientX;
				endPosY = endEvt.clientY;
				newWidth = currentWidth + endPosX - startPosX;
				newHeight = currentHeight + endPosY - startPosY;
				that.boxNode.css("width", that.fixResizeWidth(newWidth));
				that.boxNode.css("height", that.fixResizeHeight(newHeight));
			});

			$(document).on("mouseup.dragbox-resize", function(){
				that.execCallback(that.options.onResizeStop);
				that.stopResize();
			})
		},
		/**
		 * 拉伸结束
		 * @method 	startResize
		 * @private
		 */
		stopResize: function(){
			//恢复文本选择
			$("body").noSelect(false);
			$(document).off(".dragbox-resize");
		},
		/**
		 * 拉伸时高度的修正，最小高度为30
		 * @method 	fixResizeHeight
		 * @private
		 * @param  	{Number} value 	修正前的值
		 * @return 	{Number}		修正后的值
		 */
		fixResizeHeight: function(value){
			var containerHeight = this.options.container.outerHeight(),
				boxPosY = this.boxNode.position().top,
				gap = containerHeight - boxPosY;
			return value = value < 30 ? 30: value > gap ? gap: value;
		},
		/**
		 * 拉伸时宽度的修正，最小宽度为30
		 * @method 	fixResizeWidth
		 * @private
		 * @param  	{Number} value 	修正前的值
		 * @return 	{Number}		修正后的值
		 */
		fixResizeWidth: function(value){
			var containerWidth = this.options.container.outerWidth(),
				boxPosX = this.boxNode.position().left,
				gap = containerWidth - boxPosX;
			return value = value < 30 ? 30: value > gap ? gap: value;
		},
		/**
		 * 移除对象
		 * @method remove
		 */
		remove: function(){
			//将对应节点从页面中移除
			this.boxNode.remove();
			//将对象从全局数组中移除
			Dragbox.remove(this.gid)
			this.execCallback(this.options.onRemove);
		},
		/**
		 * 判断并执行回调函数
		 * @method execCallback
		 * @private
		 * @param {Function} func 回调函数体
		 * @param {Any} 	 any  任意长度的参数，作为回调执行时的参数
		 */
		execCallback: function(func){
			if(!func){
				return false;
			}
			var props = Array.prototype.slice.call(arguments, 1, arguments.length);
			if(typeof func === "function"){
				func.apply(this, props)
			}
		}
	}
})(window, window.jQuery);

(function(){
	$.fn.loading = function(content, fix){
		content = content||"请稍等...";
		var context = this,
			loading = context.data("loading"),
			contentContainer;
		if(!loading || !loading.length){
			contentContainer = $("<div>");
			loading = $("<div>").attr("class", "loading").append($("<img>").attr("src", ""), contentContainer);
			loading.appendTo(context).hide();
			context.data("loading", loading);
		}else{
			contentContainer = loading.find("div");
		}
		contentContainer.html(content);
		var setPosition = function(){
			var loadingWidth = loading.outerWidth(),
				loadingHeight = loading.outerHeight(),
				contextWidth = fix ? $(window).width() : context.outerWidth(),
				contextHeight = fix ? $(window).height() : context.outerHeight();
			return {
				top: (contextHeight - loadingHeight)/2 + (fix ? $(document).scrollTop(): 0),
				left: (contextWidth - loadingWidth)/2
			}
		}
		context.css("position", "relative");
		loading.css("top", setPosition().top).css("left", setPosition().left).toggle();

	}
	$.loading = function(content){
		$("body").loading(content, true);
	}
})();

// 图章上传及背景图上传

var PicUpload = {
	create: function($el, settings){
		var that = this,
			uploadObj;
		// 找到SWFUpload控件的替换ID并加入settings中
		settings.button_placeholder_id = $el.attr("id");
		// 此属性用于缓存SWFUpload替代节点的父节点，以便后续调用
		// settings.custom_settings.button_placeholder_wrap_id = settings.button_placeholder_id + "_wrap";//$("#" + settings.button_placeholder_id + "_wrap");// $el.parent();//$el.parent(); 
		uploadObj = Ibos.upload.image(settings);
		uploadObj.button_placeholder_wrap_id = settings.button_placeholder_id + "_wrap";
	},
	init: function($els, settings){
		var that = this;
		$els.each(function(){
			var $el = $(this);
			that.create($el, settings);
		})
	},

	remove: function(id, callback){
		var swfUploadSet = SWFUpload.instances,
			isCurrent = false;
		for(var i in swfUploadSet){
			isCurrent = (swfUploadSet[i].settings.button_placeholder_id === id );
			if(isCurrent){
				swfUploadSet[i].destroy();	
				callback && callback();
				return;
			}
		}
	}
}


var Ibos = Ibos||{};
(function(window, Ibos){
	Ibos.pushValue = function(value, source){
		var result;
		if(source.length){
			result = source + "," +value;
		}else{
			result = value;
		}
		return result;
	}
	Ibos.popValue = function(value, source, global){
		global = (global == false) ? false : true;
		var arr = source.split(",");
		for(var i = 0; i < arr.length; i++){
			if(value === arr[i]){
				arr.splice(i, 1);
				if(!global){
					break;
				}
			}
		}
		return arr.join(",")
	}
	Ibos.checkExists = function(value, source){
		return (source.indexOf(value) > -1);
	}
})(window, Ibos);

// 
$(function(){
	var refer = U.getUrlParam().refer;
	if(refer !== ""){
		var $referElem = $('#sub_nav [href="' + unescape(refer) + '"]');
		var $subMenu = $referElem.closest("ul");
		var $nav = $('[data-href="#' + $subMenu.attr("id") + '"]');
		$nav.click();
		$referElem.click();
	}
})

