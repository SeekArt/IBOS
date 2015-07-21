/**
 * 工作日志--新建
 * Diary
 * @author 		inaki
 * @version 	$Id$
 */
// @Todo: 重写新建日志相关的代码
$(function(){
	(function() {
	    // 工作日志表格
	    var $daRowspanCell = $("#schedule_plan"),
	        $daComplete = $("#da_complete");

	    var diaryRecordInstance = new Diary.orderTable($daComplete, "tpl_diary_record" );
	    var diaryRecord = {
	        $container: $daComplete,
	        reflowRowspan: function(){
	            $daRowspanCell.attr("rowspan", this.$container.find("tr").length);
	        },
	        addRow: function(data) {
	            var that = this;
	            return diaryRecordInstance.add(data, function($row){
	                // 初始化进度条
	                var $star = $row.find("[data-node-type='starProgress']");
	                $star.studyplay_star({
	                    CurrentStar: 10,
	                    prefix: $star.attr("data-id")
	                }, function(value, $el) {
	                    // 此处确保初始化目标的下一个节点为其对应input控件
	                    $el.next().val(value)
	                })

	                that.reflowRowspan();
	            });
	        },
	        removeRow: function(id){
	            diaryRecordInstance.remove(id);
	            this.reflowRowspan();
	        },
	        focusRow: function(id, dir){
	            var $row;
	            if(dir === "prev") {
	                $row = diaryRecordInstance.getPrevRow(id);
	            } else {
	                $row = diaryRecordInstance.getNextRow(id);
	            }
	            if($row && $row.is(".da-detail-row")) {
	                diaryRecordInstance.focus($row);
	                return true;
	            }
	        },
	        focus: diaryRecordInstance.focus
	    }

	    // 工作日志移除一行
	    $daComplete.on("click", ".o-trash", function() {
	        diaryRecord.removeRow($.attr(this, "data-id"))
	    });

	    // 工作日志添加移除快捷键
	    $daComplete.on("keydown", "input", function(evt) {
	        var rowId = $.attr(this, "data-id");
	        Diary.keyHandler(evt, {
	            "enter": function(evt){
	                if($.trim(evt.target.value) !== "") {
	                    diaryRecord.focus(diaryRecord.addRow());
	                }
	                evt.preventDefault();
	            },
	            "backspace": function(evt){
	                if($.trim(evt.target.value) === "" ) {
	                    diaryRecord.removeRow(rowId);
	                    evt.preventDefault();
	                }
	            },
	            "tab": function(evt){
	                var canFocus;
	                canFocus = diaryRecord.focusRow(rowId, evt.shiftKey ? "prev": "next");
	                canFocus && evt.preventDefault();
	            }
	        });

	    });

	    // 初始化时添加一条计划外
	    diaryRecord.addRow();


	    // 工作计划表格
	    var $daPlan = $("#da_plan"),
	        $daPlanRowspanCell = $("#da_plan_rowspan");

	    var diaryPlanInstance = new Diary.orderTable($daPlan, "tpl_da_plan");
	    var diaryPlan = {
	        $container: $daPlan,
	        reflowRowspan: function(){
	            $daPlanRowspanCell.attr("rowspan", this.$container.find("tr").length);
	        },
	        addRow: function(data) {
	            var that = this;

	            return diaryPlanInstance.add(data, function($row){
	                that.reflowRowspan();
	            });
	        },
	        removeRow: function(id){
	            diaryPlanInstance.remove(id);
	            this.reflowRowspan();
	        },
	        focusRow: function(id, dir){
	            var $row;
	            if(dir === "prev") {
	                $row = diaryPlanInstance.getPrevRow(id);
	            } else {
	                $row = diaryPlanInstance.getNextRow(id);
	            }
	            if($row && $row.is(".da-detail-row")) {
	                diaryPlanInstance.focus($row);
	                return true;
	            }
	        },
	        focus: diaryPlanInstance.focus
	    }

	    // 工作计划移除一行
	    $daPlan.on("click", ".o-trash", function() {
	        diaryPlan.removeRow($.attr(this, "data-id"));
	    });

	    // 工作计划添加移除快捷键
	    $daPlan.on("keydown", "input", function(evt) {
	        var rowId = $.attr(this, "data-id");
	        Diary.keyHandler(evt, {
	            "enter": function(evt){
	                if($.trim(evt.target.value) !== "") {
	                    diaryPlan.focus(diaryPlan.addRow());
	                }
	                evt.preventDefault();
	            },
	            "delete": function(evt){
	                if($.trim(evt.target.value) === "" ) {
	                    diaryPlan.removeRow(rowId);
	                    evt.preventDefault();
	                }
	            },
	            "tab": function(evt){
	                var canFocus;
	                canFocus = diaryPlan.focusRow(rowId, evt.shiftKey ? "prev": "next");
	                canFocus && evt.preventDefault();
	            }
	        })
	    });

	    // 初始化时添加两条工作计划
	    diaryPlan.addRow();
	    // 使用时间戳作id, 延时避免产生相同ID
	    setTimeout(function(){
	    	diaryPlan.addRow();
	    }, 100)

	    Ibos.evt.add({
	        // 工作计划添加一行
	        "addPlan": function(){
	            diaryPlan.focus(diaryPlan.addRow());
	        },
	        // 工作日志添加一行
	        "addRecord": function(){
	            diaryRecord.focus(diaryRecord.addRow());
	        },
	        // 来自日程的计划外日志
	        "addFromCalendar": function(param, elem) {
	            $.get(Ibos.app.url('diary/default/add', {'op': 'planFromSchedule'}), {
	                todayDate: Ibos.app.g('todayDate')
	            }, function(res) {
	                if (res.length === 0) { //如果没有日程，给个提示
	                	Ui.tip("@DA.YOU_DO_NOT_SCHEDULE_TODAY", "warning");
	                    return false;
	                }
	                //否则有日程，添加到计划外
	                for (var i = 0; i < res.length; i++) {
	                    diaryRecord.addRow(res[i])
	                };
	            });
	        }
	    })
	})();

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
});