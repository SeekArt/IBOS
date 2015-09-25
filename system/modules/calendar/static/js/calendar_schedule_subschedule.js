/**
 * 日程-下属日程
 * @author 		inaki
 * @version 	$Id$
 */

$(function(){
	//拿到活动的uid，展开侧边栏
	var supUid = Ibos.app.g("supUid");
	if(supUid !== 0){
		var $sub = $('.g-sub[data-uid='+supUid+']');
		$sub.trigger("click");
	    $sub.parent().addClass('active');
	}
	
	// 新手引导
	// 保证至少有一个下属
	setTimeout(function(){
		Ibos.guide("cal_sch_sub", function(){
			$(".mng-item.sub").eq(0).addClass("introing");

			return $(".mng-item.sub").length ? [
				{ 
					element: ".mng-item.sub > div.pull-right",
					intro: U.lang("CAL.INTRO.VIEW_UNDERLINGS")
				}
			] : [];
		}, 
		function(){
			$(".mng-item.sub").eq(0).removeClass("introing");
		});
	}, 1000);
});