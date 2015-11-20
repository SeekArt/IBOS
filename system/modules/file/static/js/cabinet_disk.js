/**
 * 网盘列表页
 * @author inaki
 * @version $Id$
 */

$(function(){
	var Reel = function(elem, options){
		this.$elem = $(elem);
		this.options = $.extend(true, {}, this.constructor.defaults, options);
		this.index = 0;
		
		this.stepLength = this.options.stepLength;

		if(this.options.autoScroll.enable) {
			this.enableAutoScroll();	
		}

		if(this.options.autoScroll.hoverStop) {
			this.$elem.on({
				"mouseenter": $.proxy(this.disableAutoScroll, this),
				"mouseleave": $.proxy(this.enableAutoScroll, this)
			})
		}
	};

	Reel.defaults = {
		autoScroll: {
			enable: false,
			speed: 1000,
			hoverStop: false
		},
		// 滚动方向
		direction: "vertical",
		// 滚动步长
		stepLength: 0,
		// 滚动步长偏差校正
		deviation: 0
	},

	Reel.prototype = {
		constructor: Reel,

		// 计算滚动的步长，以第一个子节点的宽高作为基准
		_calcStepLength: function(){
			var $item = this.$elem.children().eq(0);
			if($item.length) {
				this.stepLength = this.options.direction === "vertical" ?
					$item.outerHeight() + this.options.deviation:
					$item.outerWidth() + this.options.deviation;
			}
			return this.stepLength;
		},

		// 获取步长
		getStepLength: function(){
			return this.stepLength || this._calcStepLength();
		},

		// 获取子节点数
		getItemLength: function(){
			return this.$elem.children().length;
		},

		prev: function(){
			var that = this,
				scrollType = this.options.direction === "vertical" ? "scrollTop" : "scrollLeft",
				prop;

			if(this.index > 0 && !this.$elem.is(":animated")) {
				prop = {};
				prop[scrollType] = this.$elem[scrollType]() - this.getStepLength();

				this.$elem.animate(prop, 200, function(){
					that.index--;
					that.$elem.trigger("reelchange", {
						index: that.index
					});
				});
			}
		},

		next: function(){
			var that = this,
				scrollType = this.options.direction === "vertical" ? "scrollTop" : "scrollLeft",
				prop;

			if(this.index < this.getItemLength() - 1 && !this.$elem.is(":animated")) {
				prop = {};
				prop[scrollType] = this.$elem[scrollType]() + this.getStepLength();

				this.$elem.animate(prop, 200, function(){
					that.index++;
					that.$elem.trigger("reelchange", {
						index: that.index
					});
				});
			}
		},

		enableAutoScroll: function(){
			var that = this;
			this.timer = setInterval(function(){
				that.next();
			}, this.options.autoScroll.speed);
		},

		disableAutoScroll: function(){
			clearInterval(this.timer);
		}
	}
	

	var fileDynamic = window.fd = {
		list: ".fc-dyna-list",

		offset: 0,

		remind: 1,

		loadLock: false,

		template: '<li> <a href="#" class="avatar-circle avatar-circle-small"><img src="<%= avatar %>" alt=""> </a> <%= content %> </li>',

		init: function(){
			var that = this;
			this.$list = $(this.list);
			this.reel = new Reel(this.$list, {
				// autoScroll: {
				// 	enable: true,
				// 	speed: 5000,
				// 	hoverStop: true
				// }
			});

			this.$list.on("reelchange", function(evt, data){
				if(data.index > that.$list.children().length - 10) {
					that.get();
				}
			});

			this.get();
		},

		get: function(callback){
			var that = this;
			if(!this.loadLock && that.remind > 0) {
				this.loadLock = true;

				$.get(Ibos.app.url("file/default/getDynamic"), { offset: this.offset }, function(res){
					that.addItem(res.datas);
					that.offset = res.offset;
					that.remind = res.remind;
					that.loadLock = false;
				}, "json");
			}
		},

		addItem: function(datas){
			var that = this;
			if(datas && datas.length) {
				$.each(datas, function(i, data){
					that.$list.append($.template(that.template, data));
				});
			}
		}
	}

	fileDynamic.init();

	$("#prev_dyna_btn").click($.proxy(fileDynamic.reel.prev, fileDynamic.reel));
	$("#next_dyna_btn").click($.proxy(fileDynamic.reel.next, fileDynamic.reel));
});
