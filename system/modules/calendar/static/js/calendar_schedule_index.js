/**
 * 日程--个人日程
 * @author 		inaki
 * @version 	$Id$
 */

$(function(){
	setTimeout(function(){
		Ibos.guide("cal_sch_index", function(){
			var guideData = [
				{
					element: ".fc-slot2 .fc-widget-content",
					intro: Ibos.l("CAL.INTRO.CALENDAR_ADD"),
					position: "top"
				},
				{
					element: "tbody .fc-agenda-axis.fc-widget-header",
					intro: Ibos.l("CAL.INTRO.WORKTIME"),
					position: "right"
				},
				{
					element: ".fc-header-right",
					intro: Ibos.l("CAL.INTRO.VIEWTYPE"),
					position: "left"
				}
			];

			if($(".fc-event").length) {
				guideData.push({
					element: ".fc-event",
					intro: Ibos.l("CAL.INTRO.CALENDAR_DRAG")
				})
			}

			return guideData;
		});
	}, 1000);
});