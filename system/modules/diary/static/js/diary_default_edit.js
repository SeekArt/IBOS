var DiaryEdit = {
    /**
     * 初始化日志编辑页面
     * @method initDiaryAddPage
     */
    initDiaryEditPage: function() {
        // 编辑器初始化
        var ue = UE.getEditor('editor', {
            initialFrameWidth: 700,
            autoHeightEnabled: true,
            toolbars: UEDITOR_CONFIG.mode.simple
        });
        // 焦点设置
        ue.ready(function() {
            ue.addListener("contentchange", function() {
                $("#diary_form").trigger("formchange");
            });
            $('[data-node-type="oldPlanInput"]').eq(0).focus();
        });

        // 初始化时添加计划外
        var unplannedData = Ibos.app.g('unplannedData');
        $.each(unplannedData, function(index, data) {
            DiaryCommon.diaryRecord.addRow({
                subject: data.content,
                schedule: data.schedule
            });
        });
        DiaryCommon.diaryRecord.addRow();


        // 初始化时添加工作计划
        var planData = Ibos.app.g('planData');
        $.each(planData, function(index, data) {
            DiaryCommon.diaryPlan.addRow({
                subject: data.content,
                range: data.timeremind
            });
        });
        DiaryCommon.diaryPlan.addRow({
            subject: "",
            range: ""
        });
    }
};

$(function() {
    //初始化日志编辑页面
    DiaryEdit.initDiaryEditPage();

    $(".da-detail-table img").each(function(index, elem) {
        $(elem).wrap("<a data-lightbox='diary' href='" + elem.src + "' title='" + (elem.title || elem.alt) + "'></a>");
    });

    Ibos.evt.add({
        // 工作计划添加一行
        "addPlan": function() {
            DiaryCommon.diaryPlan.focus(DiaryCommon.diaryPlan.addRow({
                subject: "",
                range: ""
            }));
        },
        // 工作日志添加一行
        "addRecord": function() {
            DiaryCommon.diaryRecord.focus(DiaryCommon.diaryRecord.addRow());
        }
    });
});