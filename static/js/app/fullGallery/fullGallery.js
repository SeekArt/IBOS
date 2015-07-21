/**
 * 基于 ADGallery 的全屏画廊
 * 
 */

(function(){
	// 由于 adCallery 本身只支持横向预览图
	// 这里需要呈现有纵向预览图，要重写一部分函数
	$.extend($.fn.adGallery.Constructor.prototype, {
		thumbs_wrapper_height: 0,

		findImages: function() {
			var context = this;
			this.images = [];
			var thumbs_loaded = 0;
			var thumbs = this.thumbs_wrapper.find('a');
			var thumb_count = thumbs.length;
			if (this.settings.thumb_opacity < 1) {
				thumbs.find('img').css('opacity', this.settings.thumb_opacity);
			};
			thumbs.each(
				function(i) {
					var link = $(this);
					link.data("ad-i", i);
					var image_src = link.attr('href');
					var thumb = link.find('img');
					context.whenImageLoaded(thumb[0], function() {
						var height = thumb[0].parentNode.parentNode.offsetHeight;
						if (thumb[0].height == 0) {
							// If the browser tells us that the image is loaded, but the width
							// is still 0 for some reason, we default to 100px width.
							// It's not very nice, but it's better than 0.
							height = 100;
						};
						context.thumbs_wrapper_height += height;
						thumbs_loaded++;
					});
					context._initLink(link);
					context.images[i] = context._createImageData(link, image_src);
				}
			);
			// Wait until all thumbs are loaded, and then set the width of the ul
			var inter = setInterval(
				function() {
					if (thumb_count == thumbs_loaded) {
						context._setThumbListHeight(context.thumbs_wrapper_height);
						clearInterval(inter);
					};
				},
				100
			);
		},

		_setThumbListHeight: function(wrapper_height) {
			wrapper_height -= 100;
			var list = this.nav.find('.ad-thumb-list');
			list.css('height', wrapper_height + 'px');

			if (list.height() < this.nav.height()) {
				list.height(this.nav.height());
			};
		},

		initBackAndForward: function() {
			var context = this;
			this.scroll_forward = $('<div class="ad-forward"></div>');
			this.scroll_back = $('<div class="ad-back"></div>');
			this.nav.append(this.scroll_forward);
			this.nav.prepend(this.scroll_back);
			var has_scrolled = 0;
			var thumbs_scroll_interval = false;
			$(this.scroll_back).add(this.scroll_forward).click(
				function() {
					// We don't want to jump the whole width, since an image
					// might be cut at the edge
					var height = context.nav.height() - 50;
					if (context.settings.scroll_jump > 0) {
						var height = context.settings.scroll_jump;
					};
					if ($(this).is('.ad-forward')) {
						var top = context.thumbs_wrapper.scrollTop() + height;
					} else {
						var top = context.thumbs_wrapper.scrollTop() - height;
					};
					if (context.settings.slideshow.stop_on_scroll) {
						context.slideshow.stop();
					};
					context.thumbs_wrapper.animate({
						scrollTop: top + 'px'
					});
					return false;
				}
			).css('opacity', 0.6).hover(
				function() {
					var direction = 'up';
					if ($(this).is('.ad-forward')) {
						direction = 'down';
					};
					thumbs_scroll_interval = setInterval(
						function() {
							has_scrolled++;
							// Don't want to stop the slideshow just because we scrolled a pixel or two
							if (has_scrolled > 30 && context.settings.slideshow.stop_on_scroll) {
								context.slideshow.stop();
							};
							var top = context.thumbs_wrapper.scrollTop() + 1;
							if (direction == 'up') {
								top = context.thumbs_wrapper.scrollTop() - 1;
							};
							context.thumbs_wrapper.scrollTop(top);
							// console.log(top)
						},
						10
					);
					$(this).css('opacity', 1);
				},
				function() {
					has_scrolled = 0;
					clearInterval(thumbs_scroll_interval);
					$(this).css('opacity', 0.6);
				}
			);
		},

		initKeyEvents: function() {
			var context = this;
			$(document).keydown(
				function(e) {
					if (e.keyCode == 39 || e.keyCode == 40) {
						// right arrow
						context.nextImage();
						context.slideshow.stop();
						e.preventDefault();
					} else if (e.keyCode == 37 || e.keyCode == 38) {
						// left arrow
						context.prevImage();
						context.slideshow.stop();
						e.preventDefault();
					};
				}
			);
		},

		highLightThumb: function(thumb) {
			this.thumbs_wrapper.find('.ad-active').removeClass('ad-active');
			thumb.addClass('ad-active');
			if (this.settings.thumb_opacity < 1) {
				this.thumbs_wrapper.find('a:not(.ad-active) img').fadeTo(300, this.settings.thumb_opacity);
				thumb.find('img').fadeTo(300, 1);
			};
			var top = thumb[0].parentNode.offsetTop;
			top -= (this.nav.height() / 2) - (thumb[0].offsetHeight / 2);
			this.thumbs_wrapper.animate({
				scrollTop: top + 'px'
			});
		},
		/**
		preloadImage: function(index, callback) {
		  if(this.images[index]) {
		    var image = this.images[index];
		    if(!this.images[index].preloaded) {
		      var img = $(new Image());
		      img.attr('src', image.image);
		      if(!this.isImageLoaded(img[0])) {
		        this.preloads.append(img);
		        var context = this;

		        img.load(
		          function() {
		            image.preloaded = true;
		            image.size = { width: this.width||this.clientWidth, height: this.height };
		            context.fireCallback(callback);
		          }
		        ).error(
		          function() {
		            image.error = true;
		            image.preloaded = false;
		            image.size = false;
		            // 图片发生错误时，将部分信息重置，以免卡住无法切换
		            context.in_transition = false;
		            context.loading(false);
		          }
		        );
		      } else {
		        image.preloaded = true;
		        image.size = { width: img[0].width, height: img[0].height };
		        this.fireCallback(callback);
		      };
		    } else {
		      this.fireCallback(callback);
		    };
		  };
		},
		*/
		// 这两个方法也需要重写，但目前没使用到这部分功能，先不管
		// addImage
		// removeImage
	});
	
	/**
	 * 全屏画廊，单例
	 * @class FullGallery
	 * @constructor
	 * @param {[type]} datas [description]
	 */
	var FullGallery = function(datas, glOpts) {
		var inst = FullGallery.instance;
		// 若已存在实例，则只重新初始化数据，返回原实例
		if(inst) {
			// 先显示，否则可能造成图像宽高计算出错
			inst.show();
			inst._createGallery(datas, glOpts);
			return inst;
		} else {
			this._createModal();

			this._createContainer();

			this._createGallery(datas, glOpts);
			// 把实例存至类的静态属性
			FullGallery.instance = this;
		}
	};

	FullGallery.prototype = {
		constructor: FullGallery,

		// 创建模态层
		_createModal: function(){
			this.$modal = $("<div class='ad-full-modal'></div>").appendTo(document.body);
		},

		// 创建容器
		_createContainer: function(){
			var _this = this;
			var containerHtml = '<div class="ad-full">' + 
				'<div class="ad-full-hd">' + 
					'<strong></strong>' +
					'<i></i>' +
					'<a href="javascript:;" class="ad-full-close"></a>' +
				'</div>' +
			'</div>'


			this.$container = $(containerHtml);
			this.$header = this.$container.find(".ad-full-hd");

			// 为关闭按钮绑定事件
			this.$header.find(".ad-full-close").on("click", function(){
				_this.hide();
			});

			this.$container.appendTo(document.body);

			this.show();
		},

		// 创建画廊容器
		_createGallery: function(datas, glOpts){
			var galleryHtml = '<div class="ad-gallery">' +
				'<div class="ad-image-wrapper"></div>' +
				'<div class="ad-controls"></div>' +
				'<div class="ad-nav">' +
					'<div class="ad-thumbs">' +
						'<ul class="ad-thumb-list"></ul>' +
					'</div>' +
				'</div>' +
			'</div>';

			this.$gallery = $(galleryHtml);
			this.$imageWrapper = this.$gallery.find(".ad-image-wrapper");

			this.$nav = this.$gallery.find(".ad-nav");
			this.$thumbs = this.$gallery.find(".ad-thumbs");
			this.$thumbList = this.$gallery.find(".ad-thumb-list");

			this._initThumbList(datas);

			// 检测页面里是否已存在该容器，存在时替换，不存在时直接生成新节点
			var $oldGallery = this.$container.find(".ad-gallery");
			if($oldGallery.length) {
				this.$gallery.replaceAll($oldGallery);
			} else {
				this.$gallery.appendTo(this.$container);
			}

			// 适应屏幕高度
			var galleryHeight = $(window).height() - this.$gallery.position().top - 20;
			this.$imageWrapper.css("height", galleryHeight);
			this.$thumbs.css("height", galleryHeight - 60);

			this._initGallery(glOpts);
		},

		// 根据数据生成图像节点列表
		_initThumbList: function(datas){
			var _itemTpl = '<li>' +
				'<a href="<%= image.url %>" id="<%= image.id %>">' +
					'<img src="<%= image.thumburl %>" title="<%= image.title %>" alt="<%= image.desc %>" longDesc="<%= image.longDesc %>">' +
				'</a>' +
			'</li>';

			var itemHtml = "",
				i = 0,
				len = datas.length;

			if(len){
				for(; i < len; i++) {
					itemHtml += $.template(_itemTpl, { image: datas[i] });
				}
				this.$thumbList.html(itemHtml);
			}
		},

		// 初始化画廊
		_initGallery: function(glOpts){
			var _this = this,
				$desc = _this.$header.find("strong"),
				$info = _this.$header.find("i");

			var galleries = this.$gallery.adGallery($.extend(true, {
				loader_image: Ibos.app.getStaticUrl("/image/loading.gif"),
				effect: 'slide-vert',
				cycle: false,
				update_window_hash: false,
				slideshow: { enable: false },
				hooks: {
					displayDescription: function(image) {
						$desc.html(image.title)
					}
				},
				callbacks: {
					init: function(){
						var context = this;
						// 添加滚轮查看上下图的功能
						this.image_wrapper.on("mousewheel", function(evt){
							setTimeout(function(){
								if(evt.deltaY === -1) {
									context.nextImage();
		            				context.slideshow.stop();
								} else if(evt.deltaY === 1) {
									context.prevImage();
		            				context.slideshow.stop();
								}
							}, 100);
							evt.preventDefault();
						});
					},
					afterImageVisible: function(){ 
						$info.html(this.current_index + 1 + "/" + this.images.length)
					}
				}
			}, glOpts));

			this.gallery = galleries[0];
		},

		hide: function(){
			this.$modal.hide();
			this.$container.hide();
			document.body.style.overflow = "";
			$(document).off("keydown.fullgallery");
		},

		show: function(){
			var _this = this;

			this.$modal.show();
			this.$container.show().css("top", $(window).scrollTop());
			
			document.body.style.overflow = "hidden";
			
			$(document).on("keydown.fullgallery", function(evt){
				// 按 Esc 键隐藏
				if(evt.keyCode === 27) {
					_this.hide();
					evt.preventDefault();
				}
			})
		}
	};

	window.FullGallery = FullGallery;
})();