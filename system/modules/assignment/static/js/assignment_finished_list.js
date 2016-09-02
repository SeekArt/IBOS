/**
 * Assignment/finished/list
 * 指派任务已完成列表
 * @version $Id$
 */

$(function(){
	// 时间范围选择
	$("#am_daterange_btn").daterangepicker({
		format: 'YYYY/MM/DD',
		opens: "left",
		applyClass: "btn-primary",
		locale: {
			applyLabel: U.lang("CONFIRM"),
			cancelLabel: U.lang("CANCEL"),
			fromLabel: U.lang("FROM"),
			toLabel: U.lang("TO"),
			weekLabel: 'W',
			customRangeLabel: 'Custom Range',
			daysOfWeek: U.lang("DATE.DAYSTR").split(""),
			monthNames: U.lang("DATE.MONTHSTR").split(","),
			firstDay: 0
		},
		ignoredOld: true
	}, function(start, end, label) {
		$("#am_daterange_input").val(start.format('YYYY/MM/DD') + ' - ' + end.format('YYYY/MM/DD'))[0].form.submit();
	});

	// 选择框初始化
	$("#mn_search").search(function(val, target) {
		target[0].form.submit();
	});
});