	$(document).ready(function() {
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

		
		//日期计数器
		var dateTally = $("#tally");
		var dateCount = Math.floor((Ibos.app.g("nowTime") - Ibos.app.g("installTime")) / (3600 * 24));
		dateCount = dateCount < 10 ? "00" + dateCount : (dateCount < 100 ? "0" + dateCount : dateCount);
		dateTally.tallyCounter({
			count: dateCount,
			speed: 100,
			imgPath: Ibos.app.g("assetUrl") + '/image/counter/'
		});
		//系统开关
		var systemSwitch = $("#system_switch");
		systemSwitch.on("change", function() {
			var enabled = this.checked, val = 1, url = Ibos.app.url("dashboard/index/switchstatus");
			if (enabled) {
				val = 0;
			}
			$.post(url, {val: val}, function(data) {
				if (data.IsSuccess) {
					$("#switch_status").parent().toggleClass("card-flip");
					Ui.tip(U.lang("OPERATION_SUCCESS"));
				} else {
					Ui.tip(U.lang("DB.SHUTDOWN_SYSTEM_FAILED"), "danger");
				}
			}, 'json');
		});

		// ajax请求安全提示
		$("#securityTips").html("<img src='" + Ibos.app.getStaticUrl("/image/common/loading.gif") + "' />");
		$.ajax({
			type: "get",
			url: Ibos.app.url("dashboard/index/getsecurity"),
			dataType: 'html',
			timeout: 15000, // 超时15秒
			success: function(data) {
				$("#securityTips").html(data);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$("#securityTips").html(U.lang("DB.LOAD_SECURITY_INFO_FAILED"));
			}
		});

		var dialogs = {
			inputAuthCode: function() {
				Ui.dialog({
					id: "d_input_auth_code",
					title: U.lang("DB.LICENSE_KEY"),
					content: document.getElementById("input_auth_code_dialog"),
					ok: function() {
						var content = this.DOM.content,
							$licenseKey = $("#license_key");
						$licenseKey.val( $.trim($licenseKey.val()) );
						if ($licenseKey.val() === "") {
							alert(U.lang("DB.ENTER_LICENSEKEY"));
							return false;
						}
						content.find("form").submit();
					},
					width: 400,
					cancel: true
				})
			}
		}

		//皮肤选择
		$("#bgstyle_select_list").on("change", "input[type='radio']", function(){
			var $this = $(this),
				type = $this.val(),
				param = {type: type},
				url = Ibos.app.url("dashboard/background/skin");
			$.post(url, param, function(res){
				if(res.isSuccess){
					Ui.tip(Ibos.l("OPERATION_SUCCESS"));
					//加载对应的css文件
				}else{
					Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
				}
			});
		});

		$(document).on("click", '[data-click="inputAuthCode"]', function() {
			dialogs.inputAuthCode();
		})
		.on("click", '[data-click="showAuthInfo"]', function() {
			$("#show_auth_info_dialog").show().position({my: "center center", of: window});
		})
		.on("click", '[data-click="hideAuthInfo"]', function() {
			$("#show_auth_info_dialog").hide();
		})

	});
