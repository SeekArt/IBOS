/**
 * 初始化一个可拖动新建的滑动块区域。
 * @class Belt
 * @param {Jquery} $elem   容器
 * @param {Object} [options] 配置项
 * @param {Number} [options.cell=10] 总单元格数
 * @param {Boolean} [options.drawable=true] 是否允许拖动新建
 * @param {Boolean} [options.draggable=true] 是否允许拖拽移动
 * @param {Boolean} [options.retractable=true] 是否允许拖动两侧以调整大小
 * @param {Boolean} [options.removable=true] 是否允许双击移除
 * @todo 可配置max，min范围
 *       可配置是否允许新建多个块
 *       动态启用禁用各种操作
 */
var Belt = function($elem, options) {
	this.$elem = $elem;
	var opts = this.options = $.extend({}, Belt.defaults, options);
	// 值
	this.values = [0, 0];
	// 步长
	this.step = $elem.width()/opts.cell;

	if(!/relative|absolute|fixed/.test(this.$elem.css("position"))){
		this.$elem.css("position", "relative");
	}
	// 创建节点
	this.$bar = $("<div class='belt-slider'></div>").appendTo(this.$elem);
	var that = this
		$adjustorLeft = $("<div class='belt-adjustor-left'>||</div>").appendTo(this.$bar),//.hide(),
		$adjustorRight = $("<div class='belt-adjustor-right'>||</div>").appendTo(this.$bar),//.hide(),
		cursor = {
			drawable: "crosshair",
			retractable: "e-resize",
			draggable: "move"
		}

	if(opts.drawable) {
		this.$elem.css("cursor", cursor['drawable']);
	}
	if(opts.retractable) {
		$adjustorLeft.add($adjustorRight).show().css("cursor", cursor['retractable'])
	}
	if(opts.draggable){
		this.$bar.css("cursor", cursor['draggable']);
	}

	if(opts.values && $.isArray(opts.values)){
		this.range(opts.values[0], opts.values[1]);
	}

	// 事件委派， 各种拖拽事件
	this.$elem.on("mousedown.ver", function(evt){
		var $target = $(evt.target);
		var sPos = {
			x: evt.clientX,
			y: evt.clientY
		}
		that.$elem.trigger("belt.rangestart", {value: that.values});

		// 禁止文本选中
		$(document.body).noSelect();

		if($target.hasClass("belt-adjustor-left")) {
			that.options.retractable && that._starDrag(sPos, that._retractingLeft);
		} else if($target.hasClass("belt-adjustor-right")) {
			that.options.retractable && that._starDrag(sPos, that._retractingRight);
		} else if($target.hasClass("belt-slider")) {
			that.options.draggable && that._starDrag(sPos, that._dragging);
		} else {
			that.options.drawable && that._starDrag(sPos, that._drawing);
		}
	
		$(document).on("mouseup.ver", function(evt){
			// 恢复文本选中
			$(document.body).noSelect(false);
			$(document).off("mousemove.ver");
			$(document).off("mouseup.ver");
			that.$elem.trigger("belt.rangestop", {value: that.values});
		});
	});

	this.$bar.on("dblclick", function(){
		that.options.removable && that.reset();
	})

};

Belt.defaults = {
	cell: 60, 
	drawable: true, // 允许拖动新建
	draggable: true, // 允许拖拽
	retractable: true, // 允许伸缩
	removable: false // 允许移除
};
Belt.prototype = {
	constructor: Belt,
	_starDrag: function(sPos, callback){
		var that = this,
			ePos;
		$(document).on("mousemove.ver", function(evt){
			// setTimeout(function(){
				ePos = {
					x: evt.clientX,
					y: evt.clientY
				}
				callback && callback.call(that, sPos, ePos)
			// }, 64)
		});
	},

	_drawing: function(sPos, ePos){
		// console.time('draw')
		var base = this.$elem.offset(),
			sVal,
			eVal;

		base.width = this.$elem.outerWidth();
		base.height = this.$elem.outerHeight();

		var sVal, eVal;
		// 鼠标 x轴 坐标需要减去容器本身定位左距离offsetLeft
		if(sPos.x < ePos.x) {
			// 使用Math.round对值四舍五入，当用户在cell中拖拽过半距离时，判定为选中
			sVal = Math.round((sPos.x - base.left)/this.step);
			eVal = Math.round((ePos.x - base.left)/this.step);
		} else {
			sVal = Math.round((ePos.x - base.left)/this.step);
			eVal = Math.round((sPos.x - base.left)/this.step);
		}
		// console.log(sVal, eVal)
		// 渲染视图
		this.range(sVal, eVal);
		// console.timeEnd('draw')
	},
	_dragging: function(sPos, ePos){
		// 移动的步数
		var stepCount;
		
		// 向右移动
		if(sPos.x < ePos.x){
			// 已达到最大值时，跳过计算
			if(this.values[1] >= this.options.cell) {
				return false;
			}
			stepCount = Math.floor((ePos.x - sPos.x)/this.step);

			// 达到最大值时，直接移动到最大值
			if(this.values[1] + stepCount >= this.options.cell) {
				// 修正开始位置x坐标
				sPos.x += (this.options.cell - this.values[1]) * this.step;
				this.values[0] += this.options.cell - this.values[1];
				this.values[1] = this.options.cell;
			// 直接按步数计算
			} else {
				sPos.x += stepCount * this.step;
				this.values[0] += stepCount;
				this.values[1] += stepCount;
			}

		// 向左移动
		} else {
			// 已达到最小值时，跳过计算
			if(this.values[0] <= 0) {
				return false;
			}

			stepCount = Math.floor((sPos.x - ePos.x)/this.step);

			// 达到最小值时，直接移动到最小值
			if(this.values[0] - stepCount <= 0) {
				sPos.x -= (this.values[0] - 0) * this.step;
				this.values[1] -= this.values[0];
				this.values[0] = 0;
			} else {
				sPos.x -= stepCount * this.step;
				this.values[0] -= stepCount;
				this.values[1] -= stepCount;
			}
		}
		this.range(this.values[0], this.values[1])
	},

	_retractingLeft: function(sPos, ePos){
		var stepCount;
		if(sPos.x < ePos.x){
			
			// 向右收缩
			stepCount = Math.floor((ePos.x - sPos.x)/this.step);

			// 超到上限值时，将值设上限值
			if(this.values[0] + stepCount >= this.values[1] - 1) {
				sPos.x += this.step * (this.values[1] - 1 - this.values[0]);
				this.values[0] = this.values[1] - 1;
			} else {
				sPos.x += this.step * stepCount;
				this.values[0] += stepCount;
			}

		} else {
			// 达到最小值时，不再执行
			if(this.values[1] <= 0) {
				return false;
			}

			// 向左拉伸
			stepCount = Math.floor((sPos.x - ePos.x)/this.step);

			// 低于最小值时，设为最小值
			if(this.values[0] - stepCount <= 0) {
				sPos.x -= this.values[0] * this.step;
				this.values[0] = 0;
			} else {
				sPos.x -= this.step * stepCount;
				this.values[0] -= stepCount;
			}
		}
		this.range(this.values[0], this.values[1]);
	},

	_retractingRight: function(sPos, ePos){
		var stepCount;
		// 向右拉伸
		if(sPos.x < ePos.x){
			// 已达到最大值，不再计算
			if(this.values[1] >=  this.options.cell) {
				return false;
			}

			stepCount = Math.floor((ePos.x - sPos.x)/this.step);
			
			// 超过最大值时，设为最大值
			if(this.values[1] + stepCount >= this.options.cell) {
				sPos.x += (this.options.cell - this.values[1]) * this.step;
				this.values[1] = this.options.cell;
			} else {
				sPos.x += this.step * stepCount;
				this.values[1] += stepCount;
			}
		} else {
			// 达到下限值时，不再执行
			// 向左收缩
			stepCount = Math.floor((sPos.x - ePos.x)/this.step);

			// 低于下限值时，将值设为下限值....下限呢，下限在哪！！
			if(this.values[1] - stepCount <= this.values[0] + 1) {
				sPos.x -= this.step * (this.values[1] - 1 - this.values[0]);
				this.values[1] = this.values[0] + 1
			} else {
				sPos.x -= this.step * stepCount;
				this.values[1] -= stepCount; 
			}
			
		}
		this.range(this.values[0], this.values[1]);
	},

	// 验证值，将值转为数字格式，超过最大值时返回最大值，低于最小值时返回最小值
	_validValue: function(value){
		value = value ? parseInt(value, 10) : 0;
		return value < 0 ? 0 : value > this.options.cell ? this.options.cell : value;
	},

	range: function(startValue, endValue) {
		// 值按正向排序,
		this.values = [this._validValue(startValue), this._validValue(endValue)].sort(function(a, b){
			return a > b;
		});

		this.$bar.css("left", this.values[0] * this.step);
		this.$bar.outerWidth((this.values[1] - this.values[0]) * this.step);
		
		this.$elem.trigger("belt.ranging", {values: this.values});
	},
	/**
	 * 正向移动一个单位
	 * @return {[type]} [description]
	 */
	increase: function(){
		if(this.values[1] < this.options.cell && this.values[0] !== this.values[1]){
			this.range(this.values[0] + 1, this.values[1] + 1);
		}
	},

	decrease: function(){
		if(this.values[0] > 0 && this.values[0] !== this.values[1]) {
			this.range(this.values[0] - 1, this.values[1] - 1);
		}
	},

	/**
	 * 伸展范围
	 * @method expand
	 * @param  {Boolean} [positive=true] 方向，为true时正向伸展，否则反向伸展
	 * @return {[type]}          [description]
	 */
	expand: function(positive) {
		positive = typeof positive === "undefined" ? true : !!positive;
		if(positive) {
			this.range(this.values[0], this.values[1] + 1);
		} else {
			this.range(this.values[0] - 1, this.values[1]);
		}
	},

	/**
	 * 收缩范围
	 * @method shrink
	 * @param  {Boolean} [positive=true] 方向，为true时正向收缩，否则反向收缩
	 * @return {[type]}          [description]
	 */
	shrink: function(positive) {
		positive = typeof positive === "undefined" ? true : !!positive;
		if(positive) {
			this.range(this.values[0], this.values[1] - 1);
		} else {
			this.range(this.values[0] + 1, this.values[1]);
		}	
	},

	getValue: function(){
		return this.values
	},

	reset: function(){
		this.range(0, 0);
		this.$elem.trigger("belt.reset", {value: this.values});
	},

	destory: function(){
		$(document).off(".ver");
		this.$elem.off(".ver");
		this.$elem.remove();
		this.$elem = null;
		this.$bar.remove();
		this.$bar = null;
		this.values = null;
	}
}