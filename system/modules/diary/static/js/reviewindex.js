

var RevIndex = {
	// @Todo: 完善，将此函数移至diary.js
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
				
			}, "html")
		} else {
			// 若内容已初始化，则直接显示
			popover.options.content = content;
		}
	}
}

$(function(){
	Ibos.evt.add({
		// 星标，取消星标
		"toggleAsterisk": function(param, elem){
			var $elem = $(elem);
			// 取消星标
			if($elem.hasClass("o-da-asterisk")) {
				$.post(Ibos.app.url("diary/attention/edit", { 'op': 'unattention' }), { auid: param.id }, function(res){
					if(res.isSuccess) {
						Ui.tip(res.info);
						$elem.removeClass("o-da-asterisk").addClass("o-da-unasterisk")
					}
				})
			// 星标
			} else {
				$.post(Ibos.app.url("diary/attention/edit", { 'op': 'attention' }), { auid: param.id }, function(res){
					if(res.isSuccess) {
						Ui.tip(res.info);
						$elem.removeClass("o-da-unasterisk").addClass("o-da-asterisk")
					}
				})
			}
		},

		// 提醒下属写日志
		"remindUnderling": function(param, elem){
			var $elem = $(elem),
				uids = "",
				date = Ibos.app.g("reviewDate");
			$elem.parent().siblings().each(function(){
				uids += (uids ? "," : "") + $.attr(this, "data-uid");
			});

			$.post(Ibos.app.url("diary/review/edit", { op: 'remind' }), { uids: uids, date: date }, function(res){
				if(res.isSuccess){
					Ui.tip(res.msg);
				} else {
					Ui.tip(res.msg, 'warning');
				}
			})
			$elem.parent().html('<a href="javascript:" class=""><i class="o-da-reminded"></i><span class="avatar-desc"><strong>已提醒</strong></span></a>')
		}
	});

	// 查看点评，阅读人员
	var $daList = $("#da_list");

	var $daComment = $daList.find("[data-node-type='showComment']"),
		$daReader = $daList.find("[data-node-type='showReader']"),
		popoverSetting = {
			content: "Loading...",
			placement: "bottom",
			html: true
		}
	// 阅读ajax
	$daReader.popover(popoverSetting).on("show", function() {
		RevIndex.loadPopoverContent(Ibos.app.url('diary/default/index', { op: 'getreaderlist'}), { diaryid: $.attr(this, "data-id") }, $(this));
	});
	//点评ajax
	$daComment.popover(popoverSetting).on("show", function() {
		RevIndex.loadPopoverContent(Ibos.app.url('diary/default/index', { op: 'getcommentlist'}), { diaryid: $.attr(this, "data-id") }, $(this));
	});

	// 时间选择
	$('#da_date_btn').datepicker().on("changeDate", function(evt) {
		window.location.href = Ibos.app.url('diary/review/index', { date: $.data(this, "date" )});
	});

	// 新手引导
	setTimeout(function(){
		Ibos.guide("dia_rev_index", function(){
			var guideData = [];

			if($("#mng_list .mng-item-user").length) {
				guideData.push({
					element: "#mng_list .mng-item-user", 
					intro: Ibos.l("DA.INTRO.SHOW_UNDERLINGS")
				});
			};

			if($(".o-da-bell").length) {
				guideData.push({
					element: ".o-da-bell", 
					intro: Ibos.l("DA.INTRO.REMIND_UNDERLINGS")
				});
			}

			if($(".da-list-item").length){
				guideData.push({
					element: ".da-list-item .avatar-circle", 
					intro: Ibos.l("DA.INTRO.MARK_UNDERLINGS")
				});
			}
			
			return guideData;
		})
	}, 1000)
})