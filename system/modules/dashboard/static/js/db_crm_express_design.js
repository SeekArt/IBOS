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
	//快递单模板修改
	var container = $("#express_template");
	$("#add_print_item").on("click", function(){
		var item = $("#print_item"),
			value = item.val(),
			text;
		if(value != 0){
			text = item.find(":selected").text();
			new Dragbox({
				content: text,
				container: container
			})
		}
	})

	container.find(".drag-box").each(function(){
		new Dragbox({
			element: $(this),
			container: container
		})
	})
	
})()