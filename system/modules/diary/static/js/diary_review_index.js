/**
 * 评阅页面
 */

var RevIndex = {
	op : {
		/**
		 * 星标状态的切换
		 * @method toggleAsterisk
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
		 */
		toggleAsterisk : function(param){
			var url = Ibos.app.url("diary/attention/edit");
			return $.post(url, param, $.noop);
		},
		/**
		 * 提醒下属写日志
		 * @method remindUnderling
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
		 */
		remindUnderling : function(param){
			var url = Ibos.app.url("diary/review/edit");
			return $.post(url, param, $.noop);
		}
	},
	loadPopoverContent: function(url, param, $el) {
		var popover = $el.data("popover"),
			content = $el.data("content");
		// @Todo: 若需即时状态，此处或不做缓存
		// 若内容未初始化，则发起AJAX，成功后返回内容，并缓存记录
		if (!content) {
			$.get(url, param, function(res) {
				popover.options.content = res;// 此时应是AJAX返回的内容
				$el.data("content", res); // 缓存内容
				popover.setContent(); // 刷新popover中的内容
				popover.$tip.addClass("fade in bottom"); // 修正使用setContent后不显示箭头的bug
				popover.show(); // 再一次调用show，目的是重新定位
				
			}, "html");
		} else {
			// 若内容已初始化，则直接显示
			popover.options.content = content;
		}
	},
	/**
	 * 查看点评，阅读人员
	 * @method seeCommentReader
	 * @return {[type]} [description]
	 */
	seeCommentReader : function(){
		// 查看点评，阅读人员
		var $daList = $("#da_list");

		var $daComment = $daList.find("[data-node-type='showComment']"),
			$daReader = $daList.find("[data-node-type='showReader']"),
			popoverSetting = {
				content: "Loading...",
				placement: "bottom",
				html: true
			},
			popoverUrl = Ibos.app.url('diary/default/index');

		// 阅读ajax
		$daReader.popover(popoverSetting).on("show", function() {
			RevIndex.loadPopoverContent(popoverUrl, { 
				diaryid: $.attr(this, "data-id"),
			 	op: 'getreaderlist'
			}, $(this));
		});
		//点评ajax
		$daComment.popover(popoverSetting).on("show", function() {
			RevIndex.loadPopoverContent(popoverUrl, { 
				diaryid: $.attr(this, "data-id"),
			 	op: 'getcommentlist'
			}, $(this));
		});
	}
};


$(function(){
	//查看点评，阅读人员
	RevIndex.seeCommentReader();

	// 新手引导
	setTimeout(function(){
		Ibos.guide("dia_rev_index", function(){
			var guideData = [];
			if($("#mng_list .mng-item-user").length) {
				guideData.push({
					element: "#mng_list .mng-item-user", 
					intro: U.lang("DA.INTRO.SHOW_UNDERLINGS")
				});
			}
			if($(".o-da-bell").length) {
				guideData.push({
					element: ".o-da-bell", 
					intro: U.lang("DA.INTRO.REMIND_UNDERLINGS")
				});
			}

			if($(".da-list-item").length){
				guideData.push({
					element: ".da-list-item .avatar-circle", 
					intro: U.lang("DA.INTRO.MARK_UNDERLINGS")
				});
			}
			return guideData;
		});
	}, 1000);

	// 时间选择
	$('#da_date_btn').datepicker().on("changeDate", function(evt) {
		window.location.href = Ibos.app.url('diary/review/index', { date: $.data(this, "date" )});
	});

	Ibos.evt.add({
		// 星标，取消星标
		"toggleAsterisk": function(param, elem){
			var $elem = $(elem),
				isAtt = $elem.hasClass("o-da-asterisk");

			// 取消星标 unattention | 星标 	attention
			astParam = { op: (isAtt ? 'unattention' : 'attention'), auid: param.id };

			RevIndex.op.toggleAsterisk(astParam).done(function(res){
				if(res.isSuccess) {
					Ui.tip(res.info);
					$elem.removeClass("o-da-"+ (isAtt ? "asterisk" : "unasterisk") ).addClass("o-da-"+ (isAtt ? "unasterisk" : "asterisk") );
                    $("a[data-node-type='udstar'][data-id='"+ param.id + "']").attr("class", isAtt ? "o-udstar pull-right" : "o-gudstar pull-right");
				}
			});
		},

		// 提醒下属写日志
		"remindUnderling": function(param, elem){
			var $elem = $(elem),
				uids = "",
				date = Ibos.app.g("reviewDate");
			$elem.parent().siblings().each(function(){
				uids += (uids ? "," : "") + $.attr(this, "data-uid");
			});
			param = { op: 'remind', uids: uids, date: date };

			RevIndex.op.remindUnderling(param).done(function(res){
				Ui.tip(res.msg, (res.isSuccess ? "" : "wraning") );
			});
			$elem.parent().html('<a href="javascript:" class=""><i class="o-da-reminded"></i><span class="avatar-desc"><strong>'+ U.lang("DA.HAVE_REMINDED") +'</strong></span></a>');
		}
	});
});
