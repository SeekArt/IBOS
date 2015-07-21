

$(function(){
    // 编辑器初始化
    var ue = UE.getEditor('editor', {
        initialFrameWidth: 700,
        autoHeightEnabled:true,
        toolbars: UEDITOR_CONFIG.mode.simple
    });
    // 焦点设置
    ue.ready(function(){
        ue.addListener("contentchange", function(){
            $("#diary_form").trigger("formchange");
        });
        $('[data-node-type="oldPlanInput"]').eq(0).focus();
    });
    
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
            data = data || {};
            data.schedule = typeof data.schedule === "undefined" ? 10 : parseInt(data.schedule);
            return diaryRecordInstance.add(data, function($row){
                // 初始化进度条
                var $star = $row.find("[data-node-type='starProgress']");
                $star.studyplay_star({
                    CurrentStar: data.schedule,
                    prefix: $star.attr("data-id")
                }, function(value, $el) {
                    // 此处确保初始化目标的下一个节点为其对应input控件
                    $el.next().val(value)
                });

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

    // 初始化时添加计划外
    var unplannedData = Ibos.app.g('unplannedData')
    $.each(unplannedData, function(index, data){
        diaryRecord.addRow({
            subject: data.content,
            schedule: data.schedule
        });
    })
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

    // 初始化时添加工作计划
    var planData = Ibos.app.g('planData');
    $.each(planData, function(index, data){
        diaryPlan.addRow({
            subject: data.content,
			range: data.timeremind
        });
    })
    diaryPlan.addRow({
        subject: "",
        range: ""
    });

    Ibos.evt.add({
        // 工作计划添加一行
        "addPlan": function(){
            diaryPlan.focus(diaryPlan.addRow({
                subject: "",
                range: ""
            }));
        },
       // 工作日志添加一行
        "addRecord": function(){
            diaryRecord.focus(diaryRecord.addRow());
        }

    })
});