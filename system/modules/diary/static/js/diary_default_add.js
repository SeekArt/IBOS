/**
 * 工作日志--新建
 * Diary
 * @author 		inaki
 * @version 	$Id$
 */
var DiaryAdd = DiaryAdd || {};

DiaryAdd.op = {
	/**
	 * 来自日程的计划外日志
	 * @method addFromCalendar 
     * @param  {Object} param 传入JSON格式数据
     * @return {Object}       返回deffered对象
	 */
	addFromCalendar : function(param){
		var url = Ibos.app.url('diary/default/add');
			param = $.extend({}, param, {op : 'planFromSchedule'}); 
		return $.get(url, param, $.noop);
	}
}; 
/**
 * 初始化日志添加页面
 * @method initDiaryAddPage
 */
DiaryAdd.initDiaryAddPage = function(){
	// 初始化时添加一条计划外
	DiaryCommon.diaryRecord.addRow();

	// 初始化时添加两条工作计划
	DiaryCommon.diaryPlan.addRow();

	// 使用时间戳作id, 延时避免产生相同ID
	setTimeout(function(){
		DiaryCommon.diaryPlan.addRow();
	}, 100);

	// 编辑器初始化
	var ue = UE.getEditor('diary_add_editor', {
	    initialFrameWidth: 700,
	    autoHeightEnabled:true,
	    toolbars: UEDITOR_CONFIG.mode.simple
	});
	ue.ready(function(){
		ue.addListener("contentchange", function(){
			$("#diary_form").trigger("formchange");
		});
		// 焦点设置
        $('[data-node-type="oldPlanInput"]').eq(0).focus();
	

        (new Ibos.EditorCache(ue, null, "diary_add_editor")).restore();
	});

	// 新手引导
	setTimeout(function(){
		Ibos.guide("dia_def_add", function() {
	        var guideData = [];

	        if(document.getElementById("from_calendar_btn")){
	            guideData.push({
	                element: "#from_calendar_btn",
	                intro: Ibos.l("DA.INTRO.ADD_FROM_SCHE")
	            });
	        }

	        if(document.getElementById("share_intro")) {
	            guideData.push({
	                element: "#share_intro",
	                intro: Ibos.l("DA.INTRO.ADD_SHARE"),
	                position: "top"
	            });
	        }

	        return guideData;
	    });

	}, 1000);
};

$(function(){
	//初始化日志添加页面
	DiaryAdd.initDiaryAddPage();

    Ibos.evt.add({
        // 工作计划添加一行
        "addPlan": function(){
            DiaryCommon.diaryPlan.focus( DiaryCommon.diaryPlan.addRow() );
        },
        // 工作日志添加一行
        "addRecord": function(){
            DiaryCommon.diaryRecord.focus( DiaryCommon.diaryRecord.addRow() );
        },
        // 来自日程的计划外日志
        "addFromCalendar": function(param, elem) {
    		var fromCalendarParam = { todayDate: Ibos.app.g('todayDate') };
            DiaryAdd.op.addFromCalendar(fromCalendarParam).done(function(res) {
                if (res.length === 0) { //如果没有日程，给个提示
                	Ui.tip("@DA.YOU_DO_NOT_SCHEDULE_TODAY", "warning");
                    return false;
                }
                //否则有日程，添加到计划外
                for (var i = 0; i < res.length; i++) {
                    DiaryCommon.diaryRecord.addRow(res[i]);
                }
            });
        }
    });
});