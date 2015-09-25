// 任务列表
var TodoList = function($list, options) {
	this.$list = $list;
	this.options = $.extend({}, TodoList.defaults, options);
	this.globalData = [];
	this._init();
};
// 默认参数
TodoList.defaults = {
	scrollSensitivity: 20,
	scrollSpeed: 20,
	disabled: false
};

TodoList.prototype = {
	constructor: TodoList,
	/**
	 * 初始化事件
	 * @method _init
	 */
	_init: function() {
		this._bindEvent();
	},
	/**
	 * 事件绑定
	 * @method _bindEvent
	 */
	_bindEvent: function() {

		var that = this,
			keyboard = {
				ENTER: 13,
				UP: 38,
				DOWN: 40,
				BACKSPACE: 8
			};

		var _focusPrev = function($item) {
				$prev = $item.prev();
				if (!$prev || !$prev.length) {
					$prev = $item.parent().prev().find(".todo-item").last();
				}
				return that._focusItem($prev);
			},
			_focusNext = function($item) {
				$next = $item.next();
				if (!$next || !$next.length) {
					$next = $item.parent().next().find(".todo-item").first();
				}
				return that._focusItem($next);
			};
		if(!this.options.disabled){
			// 完成
			this.$list.on("click", ".o-todo-complete", function() {
				var id = that._getItemIdByChild($(this));
				that.setUncomplete(id);
			})
			// 取消完成
			.on("click", ".o-todo-uncomplete", function() {
				var id = that._getItemIdByChild($(this));
				that.setComplete(id);
			})
			// 标记
			.on("click", ".o-todo-mark", function() {
				var id = that._getItemIdByChild($(this));
				that.setUnmark(id);
			})
			// 未标记
			.on("click", ".o-todo-unmark", function() {
				var id = that._getItemIdByChild($(this));
				that.setMark(id);
			})
			// 删除
			.on("click", ".o-trash", function() {
				if(that._trigger("beforeRemove") === false) {
					return;
				}
				var id = that._getItemIdByChild($(this));
				that.removeItem(id);
			})
			// 内容编辑
			.on({
				"focus": function() {
					var $ct = $(this),
						content = $ct.text();
					$ct.data("content", content);
					// Event.edit;
					that._trigger("edit");
				},
				"blur": function() {
					var $ct = $(this),
						id = that._getItemIdByChild($ct),
						oldContent = $ct.data("content"),
						content = $ct.text();
					if( content === "" ){
						that.removeItem(id);
						return false;
					}
					if (oldContent !== content) {
						that.setData(id, {
							text: content
					});
					// Event.save
					that._trigger("save", id, content);
					}
				}
			}, ".todo-item-content")
			// 添加子项
			.on("click", ".o-plus", function() {
				var id = that._getItemIdByChild($(this)),
					data = {
						pid: id
					},
					childContent = $.trim( $(this).closest(".todo-entry").find(".todo-item-content:last").html() );
				if( childContent !== "" ){
					that.addItem(data);
				}
			})
			// 快捷键
			.on("keydown", ".todo-item-content", function(evt) {
				var $content = $(this),
					$item = that._getItemByChild($content),
					id, pid,
					data;

				switch (evt.which) {
					// Enter
					case keyboard.ENTER:
						pid = $item.attr("data-pid");
						data = {
							text: ""
						};
						typeof pid !== "undefined" && (data.pid = pid);

						that._focusItem(that.addItem(data));
						evt.preventDefault();
						break;
					// Up
					case keyboard.UP:
						_focusPrev($item);
						break;
					// Down
					case keyboard.DOWN:
						_focusNext($item);
						break;
					// Backspace
					case keyboard.BACKSPACE:
						id = $item.attr("data-id");
						if ($content.text() === "") {
							if (!_focusNext($item)) {
								_focusPrev($item);
							}
							that.removeItem(id);
							evt.preventDefault();
						}
						break;
				}

			});

			this._bindDragStartEvent();
		}
	},
	/**
	 * 获取入口节点
	 * @method _getEntryByChild
	 * @param  {Object} $el 传入jquery节点对象
	 * @return {Object}     返回jquery节点对象
	 */
	_getEntryByChild: function($el) {
		return $el.parents(".todo-entry").eq(0);
	},
	/**
	 * 获取项目节点
	 * @method _getItemByChild
	 * @param  {Object} $el 传入jquery节点对象
	 * @return {Object}     返回jquery节点对象
	 */
	_getItemByChild: function($el) {
		return $el.parents(".todo-item").eq(0);
	},
	/**
	 * 获取项目节点的id
	 * @method _getItemByChild
	 * @param  {Object} $el 传入jquery节点对象
	 * @return {String}     返回项目节点的id
	 */
	_getItemIdByChild: function($el) {
		return this._getItemByChild($el).attr("data-id");
	},
	/**
	 * 绑定拖拽开始事件
	 * @method _bindDragStartEvent
	 */
	_bindDragStartEvent: function() {
		var that = this,
			options = this.options;
		this.$list.on("mousedown.todo.dragstart", ".o-todo-drag", function(evt) {
			// 鼠标左键触发事件
			if (evt.which === 1) {
				var $item = that._getEntryByChild($(this)),
					id = $item.attr("data-id");

				// Event.start
				that._trigger("start", id);
				evt.currentId = id;

				// 指向当前活动对象
				that.$current = $item;
				// 禁用文本选择
				$(document.body).noSelect();

				// 插入占位符
				that._setPlaceholderOn();
				// 悬浮作用对象，设置为绝对定位
				that._setHelperOn();
				// 记录当前滚动条位置
				evt.scrollTop = $(document).scrollTop();
				evt.scrollLeft = $(document).scrollLeft();

				that._bindDragingEvent(evt);
				that._bindDragStopEvent();
				evt.preventDefault();
			}
		});
	},
	/**
	 * 绑定拖拽中事件
	 * @method _bindDragStartEvent
	 * @param {Object} evt 事件对象
	 */
	_bindDragingEvent: function(evt) {
		var that = this,
			$doc = $(document),
			// $entrys = that.$list.find(".todo-entry"),
			$otherEntrys = that.$current.siblings(".todo-entry"),
			startX = evt.clientX,
			startY = evt.clientY,
			currentItemPos = this.$current.position(),
			offY = startY, // 用于分辨鼠标移动方向
			currentId = evt.currentId,
			targetId;

		$doc.on("mousemove.todo.draging", function(e) {
			// Event.sort
			// console.log("todo-list: ", "sort");
			that._trigger("sort");

			e.scrollTop = $doc.scrollTop();
			e.scrollLeft = $doc.scrollLeft();

			var endX = e.clientX,
				endY = e.clientY,
				scrollX = e.scrollLeft - evt.scrollLeft,
				scrollY = e.scrollTop - evt.scrollTop,
				shiftX = endX - startX + currentItemPos.left + scrollX,
				shiftY = endY - startY + currentItemPos.top + scrollY;

			// 参考层定位
			that.$current.css({
				left: shiftX,
				top: shiftY
			});

			// 当鼠标到达边缘时，页面自动上下滚动
			if (e.pageY < e.scrollTop + that.options.scrollSensitivity) {
				$doc.scrollTop(e.scrollTop - that.options.scrollSpeed);
			}
			else if (e.pageY > e.scrollTop + $(window).innerHeight() - that.options.scrollSensitivity) {
				$doc.scrollTop(e.scrollTop + that.options.scrollSpeed);
			}
			// ----
			// 向下移动
			if (endY > offY) {
				for (var i = 0, len = $otherEntrys.length; i < len; i++) {
					var $entry = $otherEntrys.eq(i),
						$next = $entry.next(),
						enTop = $entry.position().top,
						enHeight = $entry.outerHeight();
					// 与其它条目作位置比较
					if (shiftY >= enTop + enHeight / 2 && shiftY <= enTop + enHeight) {
						// 避免 插入位置 与 当前位置 相同时的 重复插入	
						if (!$next.is(that.$placeholder)) {
							targetId = $entry.attr("data-id");
							that.$placeholder.insertAfter($entry);
							// Event.change
							// console.log("todo-list: ", "change");
							that._change = {
								currentId: currentId,
								targetId: targetId,
								type: "down"
							};
							that._trigger("change", that._change);
						}
					}
				}
			// 向上移动
			} else {
				for (var i = 0, len = $otherEntrys.length; i < len; i++) {
					var $entry = $otherEntrys.eq(i),
						$prev = $entry.prev(),
						enTop = $entry.position().top,
						enHeight = $entry.outerHeight();

					// 与其它条目作位置比较
					if (shiftY >= enTop && shiftY <= enTop + enHeight / 2) {
						// 避免 插入位置 与 当前位置 相同时的 重复插入	
						if (!$prev.is(that.$placeholder)) {
							targetId = $entry.attr("data-id");
							that.$placeholder.insertBefore($entry);
							// Event.change
							// console.log("todo-list: ", "change");
							that._change = {
								currentId: currentId,
								targetId: targetId,
								type: "up"
							};
							that._trigger("change", that._change);
						}
					}
				}
			}
			offY = endY;
			// 阻止默认事件，避免ie8下拖动锚点
			e.preventDefault();
		});
	},
	/**
	 * 解绑拖拽中事件
	 * @method _bindDragStartEvent
	 */
	_unbindDragingEvent: function() {
		$(document).off(".todo.draging");
	},
	/**
	 * 绑定拖拽结束事件
	 * @method _bindDragStartEvent
	 */
	_bindDragStopEvent: function() {
		var that = this;
		$(document).on("mouseup.todo.dragstop", function(e) {
			// 启用文本选择
			$(document.body).noSelect(false);
			// 解绑事件
			that._unbindDragingEvent();
			that._unbindDragEndEvent();
			// 移除点位符
			that._setPlaceholderOff();
			// 关闭悬浮
			that._setHelperOff();
			// 清除当前活动对象指向
			that.$current = null;
			// 如果顺序没发生，则恢复原位 
			// Event.stop
			// console.log("todo-list: ", "stop");
			that._trigger("stop", that._change);
			delete that._change;
		});
	},
	/**
	 * 解绑拖拽事件
	 * @method _bindDragStartEvent
	 */
	_unbindDragEndEvent: function() {
		$(document).off(".todo.dragstop");
	},
	/**
	 * 设置帮助开
	 * @method _setHelperOn
	 */
	_setHelperOn: function() {
		var hpPos = this.$current.position(),
			hpWidth = this.$current.width(),
			hpHeight = this.$current.height(),
			hpStyle = {
				"position": "absolute",
				"top": hpPos.top,
				"left": hpPos.left,
				"width": hpWidth,
				"z-index": "1000"
			};
		this.$current.addClass("todo-helper").css(hpStyle);
	},
	/**
	 * 设置帮助关
	 * @method _setHelperOn
	 */
	_setHelperOff: function() {
		this.$current && this.$current.removeClass("todo-helper").attr("style", "");
	},
	/**
	 * 设置占位符开
	 * @method _setPlaceholderOn
	 */
	_setPlaceholderOn: function() {
		var $ph = $("<div class='todo-placeholder'></div>"),
			phHeight = this.$current.outerHeight();
		$ph.css({
			height: phHeight,
			visibility: "hidden"
		}).insertAfter(this.$current);
		this.$placeholder = $ph;
	},
	/**
	 * 设置占位符关
	 * @method _setPlaceholderOff
	 */
	_setPlaceholderOff: function() {
		this.$placeholder.replaceWith(this.$current);
		this.$placeholder = null;
	},
	/**
	 * 添加子项
	 * @method _addSubItem
	 * @param  {Object} data 传入JSON格式数据
	 * @return {Object}      模板数据
	 */
	_addSubItem: function(data) {
		var isComplete = data.complete === "1" ? true : false,
			date = typeof data.date === "undefined" ? "" : data.date,
			text = typeof data.text === "undefined" ? "" : data.text,
			tpl = '<div class="todo-item todo-sub-item ' + (isComplete ? "todo-complete" : "") + '" data-id="' + data.id + '" data-pid="' + data.pid + '" >' +
					'<div class="todo-item-left"></div>' +
					'<div class="todo-item-center">' +
						'<a href="javascript:;" class="pull-left ' + (isComplete ? "o-todo-complete" : "o-todo-uncomplete") + '"></a> ' +
						'<div class="todo-item-content"' + (this.options.disabled ? '' : 'contentEditable') + '>' +
							text +
						'</div>' +
					'</div>' +
					'<div class="todo-item-right">' +
						( this.options.disabled ? '' :
						'<div class="todo-operate">' +
							' <a href="javascript:;" title="' + U.lang("DELETE") + '" class="cbtn o-trash"></a>' +
						'</div>') +
					'</div>' +
				'</div>',
			$item = $(tpl),
			$wrap;
		// 循环判断其对应父项是否存在
		for (var i = 0, len = this.globalData.length; i < len; i++) {
			// 当新建子项的pid指向的父项已存在时,插入父项所在容器
			if (data.pid === this.globalData[i].id) {
				$wrap = this.globalData[i].item.parent();
				if ($wrap && $wrap.length) {
					$item.appendTo($wrap);
					this._focusItem($item);
					data.item = $item;
					this.addData(data);
				}
			}
		}
		return $item;
	},
	/**
	 * 添加项目
	 * @method _addItem
	 * @param  {Object} data 传入JSON格式数据
	 * @return {Object}      模板数据
	 */
	_addItem: function(data) {
		var that = this,
			markCls = data.mark === "1" ? "o-todo-mark" : "o-todo-unmark",
			isCompelte = data.complete === "1" ? true : false,
			date = typeof data.date === "undefined" ? "" : data.date,
			text = typeof data.text === "undefined" ? "" : data.text,
			tpl = '<div class="todo-item todo-item-movable ' + (isCompelte ? "todo-complete" : "") + '" data-id="' + data.id + '">' +
					'<div class="todo-item-left">' +
						' <a href="javascript:;" class="o-todo-drag"></a>' +
						' <a href="javascript:;" class="' + (isCompelte ? "o-todo-complete" : "o-todo-uncomplete") + '"></a>' +
						' <a href="javascript:;" class="' + markCls + '"></a>' +
					'</div>' +
					'<div class="todo-item-center">' +
						'<div class="todo-item-content"' + (this.options.disabled ? '' : 'contentEditable') + '>' +
							text +
						'</div>' +
					'</div>' +
					'<div class="todo-item-right">' +
						' <div class="date form_datetime">' +
							' <input disabled class="todo-date" value="' + date + '" />' +
						' </div>' +
						' <div class="todo-operate">' +
							( this.options.disabled ? '' :
							' <a href="javascript:;" title="' + U.lang('CAL.ADD_SUB_ITEM') + '" class="cbtn o-plus"></a>' +
							' <a href="javascript:;" title="' + U.lang('CAL.DEADLINE') + '" class="cbtn o-date mls"></a>' +
							' <a href="javascript:;" title="' + U.lang('DELETE') + '" class="cbtn o-trash mls"></a>')  +
						' </div>' +
					'</div>' +
				'</div>',
			$item = $(tpl);
		// 时间选择器
		if(!this.options.disabled){
			var $date = $item.find(".todo-date").parent(),
				$picker = $item.find(".o-date"),
				focusCls = "todo-focus";
			$date.datepicker({
				component: $picker
			}).on({
				"show": function() {
					$item.addClass(focusCls);
				},
				"hide": function() {
					that._trigger("date", data.id, $(this).data("datetimepicker").getLocalDate());
					$item.removeClass(focusCls);
				}
			});
		}

		$item.prependTo(this.$list).wrap("<div class='todo-entry" +  (this.options.disabled ? " todo-disabled" : "") + "' data-id='" + data.id + "'></div>");
		data.item = $item;
		this.addData(data);
		return $item;
	},
	/**
	 * 焦点项目
	 * @method _focusItem
	 * @param  {Object} $item 传入jquery节点对象
	 * @return {Boolean}      true
	 */
	_focusItem: function($item) {
		// 假设传入参数为ID时
		var $content;
		if (typeof $item === "string" || typeof $item === "number") {
			$item = this.getItem($item);
		}
		$content = $item.find(".todo-item-content");
		if ($content && $content.length) {
			$content.focus();
			return true;
		}
	},
	/**
	 * 添加项目
	 * @method addItem
	 * @param {Object}  data     传入JSON格式数据
	 * @param {Boolean} hasSaved 是否保存
	 */
	addItem: function(data, hasSaved) {
		var $item,
			isChildren;
		if (!data || typeof data !== "object") {
			throw new Error("{TodoList.addItem}: "+ U.lang("CAL.DATATYPE_NOT_CORRECT"));
		}
		if (typeof data.id === "undefined") {
			data.id = U.uniqid() || "todo_item_" + $.now();
		}

		isChildren = typeof data.pid !== "undefined" ? true : false;
		$item = this[ isChildren ? "_addSubItem" : "_addItem" ](data);

		// Event.add
		(!hasSaved) && this._trigger("add", data);
		return $item;
	},
	/**
	 * 仅用于初始化
	 * @method set
	 * @param {Object} data 传入JSON格式数据
	 */
	set: function(data) {
		var sub = [],
			i,
			len;
		// 先插入所有父级项，避免子项先于父项出现时的错误
		for (i = 0, len = data.length; i < len; i++) {
			if (typeof data[i].pid !== "undefined") {
				sub.push(data[i]);
			} else {
				this.addItem(data[i], true);
			}
		}
		// 再插入子级项
		if (sub.length) {
			for (i = 0, len = sub.length; i < len; i++) {
				this.addItem(sub[i], true);
			}
		}
	},
	/**
	 * 获取项目
	 * @method getItem
	 * @param {String} id 项目id
	 */
	getItem: function(id) {
		var data = this.getData(id);
		return data ? data.item : null;
	},
	/**
	 * 删除项目
	 * @method removeItem
	 * @param {String} id 项目id
	 */
	removeItem: function(id) {
		var data = this.getData(id),
			delIds = [],
			i, len;
		if (data) {
			// 子项
			if (typeof data.pid !== "undefined") {
				data.item.remove();
				// 父项，同时会删除子项
			} else {
				data.item.parent()
				.find(".date").datetimepicker("destroy")
				.end().remove();
				// 循环将要删除的子项数据的id存入数组
				// 不能直接对数组进行删除，会导致循环出错
				for (i = 0, len = this.globalData.length; i < len; i++) {
					if (this.globalData[i].pid == id) {
						delIds.push(this.globalData[i].id);
					}
				}
				// 循环要删除数据的id
				for (i = 0, len = delIds.length; i < len; i++) {
					this.removeData(delIds[i]);
				}
			}
			this.removeData(id);

			// Event.remove
			// console.log("todo-list: ", "remove");
			this._trigger("remove", id);
		}
	},
	/**
	 * 添加数据
	 * @method addData
	 * @param {Obejct} data 传入JSON格式数据
	 */
	addData: function(data) {
		this.globalData.push(data);
	},
	/**
	 * 获取数据
	 * @method getData
	 * @param {String} id 数据id
	 */
	getData: function(id) {
		var result = null;
		for (var i = 0, len = this.globalData.length; i < len; i++) {
			if (id == this.globalData[i].id) {
				result = this.globalData[i];
				break;
			}
		}
		return result;
	},
	/**
	 * 删除数据
	 * @method removeData
	 * @param {String} id 数据id
	 */
	removeData: function(id) {
		for (var i = 0, len = this.globalData.length; i < len; i++) {
			if (id == this.globalData[i].id) {
				this.globalData.splice(i, 1);
				break;
			}
		}
	},
	/**
	 * 设置数据
	 * @method setData
	 * @param {String} id   数据id
	 * @param {Obejct} data 传入JSON格式数据
	 */
	setData: function(id, data) {
		var result = this.getData(id);
		result && $.extend(result, data);
	},
	/**
	 * 设置完成
	 * @method setComplete
	 * @param {String} id   项目id
	 */
	setComplete: function(id) {
		var that = this,
			$item = this.getItem(id);

		$item.addClass("todo-complete").find(".o-todo-uncomplete")
			.removeClass("o-todo-uncomplete").addClass("o-todo-complete");

		that.setData(id, {
			complete: "1"
		});
		this._trigger("complete", id, true);
	},
	/**
	 * 设置未完成
	 * @method setUncomplete
	 * @param {String} id   项目id
	 */
	setUncomplete: function(id) {
		var that = this,
			$item = this.getItem(id);

		$item.removeClass("todo-complete").find(".o-todo-complete")
			.removeClass("o-todo-complete").addClass("o-todo-uncomplete");

		that.setData(id, {
			complete: "0"
		});
		this._trigger("complete", id, false);

	},
	/**
	 * 设置标记
	 * @method setMark
	 * @param {String} id   项目id
	 */
	setMark: function(id) {
		var $item = this.getItem(id);

		$item.find(".o-todo-unmark").removeClass("o-todo-unmark").addClass("o-todo-mark");
		this.setData(id, {
			mark: "1"
		});
		this._trigger("mark", id, true);
	},
	/**
	 * 设置未标记
	 * @method setUnmark
	 * @param {String} id   项目id
	 */
	setUnmark: function(id) {
		var $item = this.getItem(id);
		$item.find(".o-todo-mark").removeClass("o-todo-mark").addClass("o-todo-unmark");
		this.setData(id, {
			mark: "0"
		});
		this._trigger("mark", id, false);
	},
	/**
	 * 触发
	 * @method setUnmark
	 * @param {String} name  项目名称
	 */
	_trigger: function(name ) {
		var argu;
		if (this.options[name] && $.isFunction(this.options[name])) {
			argu = Array.prototype.slice.call(arguments, 1);
			return this.options[name].apply(this, argu);
		}
	},
	/**
	 * 加载数据
	 * @method load
	 * @param  {String} url 传入url地址
	 */
	load: function(url) {
		var that = this;
		if (typeof url === "string") {
			$.ajax({
				url: url,
				type: "get",
				dataType: "json",
				success: function(data) {
					that._addItems(data);
				}
			});
		}
	}
};
