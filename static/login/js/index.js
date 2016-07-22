/**
 *! 协同云首页
 * @author 		ibos
 */
$(function(){
	function Slide(container, imgs) {
		this.$container = $(container);
		this.imgs = imgs || [];
		this.preload(0);
	}

	Slide.prototype = {
		constructor: Slide,

		getImage: function(index) {
			return this.$container.find('[src="' + this.imgs[index] + '"]');
		},

		preload: function(index) {
			var _this = this;

			if(index < this.imgs.length) {
				if(this.getImage().length) {
					this.preload(index + 1);
				} else {
					this.loadImage(this.imgs[index])
					.done(function(){
						_this.preload(index + 1);
					});
				}
			}
		},

		loadImage: function (url, load, error){
			var img = new Image(),
				deferred = $.Deferred(),
				loaded = false;

			img.onload = function(){
				img.onload = img.onerr = null;
				!loaded && deferred.resolve(img);
				loaded = true;
			}
			// 加载错误
			img.onerror = function () {
				img.onload = img.onerror = null;
				deferred.reject(img);
			};
			img.src = url;
			if(img.complete){
				loaded = true;
				deferred.resolve(img);
			}
			return deferred;
		},

		slideTo: function(index) {
			var _this = this;
			var $container = this.$container;
			var $img = this.getImage(index);

			// 如果在图片加载完成前，就先切换到其他图片时，中断之前图片加载完成时的回调
			this.deferred && this.deferred.reject();

			if($img.length) {
				$img.fadeIn().siblings().fadeOut();
			} else {
				this.deferred = this.loadImage(this.imgs[index])
				.done(function(img) {
					$(img).hide().appendTo($container);
					_this.slideTo(index);
				});
			}
		}
	}

	var previewSlide = {
		images: [
			'./image/preview/email.png',
			'./image/preview/calendar.png',
			'./image/preview/contact.png',
			'./image/preview/article.png',
			'./image/preview/workflow.png',
			'./image/preview/index.png'
		],

		index: 0,

		$container: $('#mod_list'),

		sliderClass: '.mod-slider',

		switchTo: function(index) {
			var _this = this;
			var $slider = this.$container.find(this.sliderClass);

			$slider.stop().animate({
				left: this.$container.find('li').eq(index).position().left
			}, function() {
				_this.slide.slideTo(index);
				_this.index = index;
			});
		},

		start: function() {
			var _this = this;

			this.slide = new Slide('.device-preview', this.images);
			this.slide.slideTo(0);
			this.autoSlide();

			this.$container.on({
				'mouseenter': function() {
					_this.stopAutoSlide();
					_this.switchTo($(this).index());
				},
				'mouseleave': function() {
					_this.autoSlide();
				}
			}, 'li:not(' + this.sliderClass + ')');
		},

		autoSlide: function() {
			var _this = this;
			this.autoSlideTimer = setInterval(function() {
				_this.switchTo(_this.index + 1 >= _this.images.length ? 0 : _this.index + 1);
			}, 3000);
		},

		stopAutoSlide: function() {
			clearInterval(this.autoSlideTimer);
		}
	}

	previewSlide.start();
});