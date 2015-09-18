$.fn.datetimepicker.dates['zh-CN'] = {
		days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
		daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
		daysMin:  ["日", "一", "二", "三", "四", "五", "六", "日"],
		months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
		monthsShort: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"],
		// monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
		today: "今日",
		selectMonth: "选择月份",
		selectYear: "选择年份"
};
$.fn.datepicker = function(options){
	var opt = {},
		argu = arguments;
	if(typeof options !== "string") {
		opt = $.extend({
			language: "zh-CN",
			format: "yyyy-mm-dd",
			orientation: "left",
			pickTime: false,
			autoNext: true
		}, options);
	}

	return this.each(function(){
		var $elem = $(this),
			$cp,
			$tecp,
			$targetElem;

		if(!$elem.data("datetimepicker")) {
			$cp = $elem.find(".datepicker-btn");

			$elem.datetimepicker($.extend({
				component: $cp.length ? $cp : false
			}, opt));
			
			$targetElem = Dom.getElem(opt.target, true);
	
			// 当 target jquery对象存在时，创建日期范围组，即会对可选范围做动态限制
			if($targetElem && $targetElem.length) {
				$tecp = $targetElem.find(".datepicker-btn");
				$targetElem.datetimepicker($.extend({
					// 初始化可选时间范围
					startDate: new Date($elem.find(">input").val()),
					component: $tecp.length ? $tecp : false
				}, opt));

				if($targetElem.val()) {
					$elem.datetimepicker("setEndDate", $targetElem.data("datetimepicker").getDate());
				} 
				if($elem.val()) {
					$targetElem.datetimepicker("setStartDate", $elem.data("datetimepicker").getDate());
				}

				// 时间变更时，改变可选时间范围
				$elem.on("changeDate", function(evt){				
					$targetElem.datetimepicker("setStartDate", evt.date);
				});

				$targetElem.on("changeDate", function(evt){				
					$elem.datetimepicker("setEndDate", evt.date);
				});

				// 选择完开始日期后，自动打开结束日期选择器
				if(opt.autoNext){
					var initDate;
					$elem.on("show", function(evt){
						initDate = $(this).data("date");
					})
					$elem.on("hide", function(evt){
						if($(this).data("date") !== initDate){
							$targetElem.datetimepicker("show");
						}
					})
				}
			}
		}
			 
		if(typeof options === "string") {
			$.fn.datetimepicker.apply($(this), argu)
		}
	})
}