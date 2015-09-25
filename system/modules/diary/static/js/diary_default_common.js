// 新增页和编辑页的一些共有交互

var DiaryCommon = {
    op : {
        /**
         * 设置默认共享人
         * @method sharSetting
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        shareSetting : function(param){
            var url = Ibos.app.url('diary/default/edit');
                param = $.extend({}, param, {op : "setShare"});
            return $.post(url, param, $.noop);
        }
    },
    /**
     * 工作计划表格操作 - 增删
     * @method diaryPlan
     */
    diaryPlan : (function(){
        var $daPlan = $("#da_plan"),
            $daPlanRowspanCell = $("#da_plan_rowspan");

        var diaryPlanInstance = new Diary.orderTable($daPlan, "tpl_da_plan");
        return {
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
                $row = diaryPlanInstance[ dir === "prev" ? "getPrevRow" : "getNextRow" ](id);
                if($row && $row.is(".da-detail-row")) {
                    diaryPlanInstance.focus($row);
                    return true;
                }
            },
            focus: diaryPlanInstance.focus
        };
    })(),
    /**
     * 工作日志表格操作 - 增删
     * @method diaryRecord
     */
    diaryRecord : (function(){
        var $daRowspanCell = $("#schedule_plan"),
            $daComplete = $("#da_complete");

        var diaryRecordInstance = new Diary.orderTable($daComplete, "tpl_diary_record" );
        return {
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
                        $el.next().val(value);
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
                $row = diaryRecordInstance[ dir === "prev" ? "getPrevRow" : "getNextRow" ](id);
                if($row && $row.is(".da-detail-row")) {
                    diaryRecordInstance.focus($row);
                    return true;
                }
            },
            focus: diaryRecordInstance.focus
        };
    })(),  
    /**
     * 工作日志事件操作
     * @method diaryRecord
     */
    eventHandler : function(){
        var $daComplete = $("#da_complete"),
            $daPlan = $("#da_plan");

        // 工作日志移除一行
        $daComplete.on("click", ".o-trash", function() {
            DiaryCommon.diaryRecord.removeRow($.attr(this, "data-id"));
        });

        // 工作日志添加移除快捷键
        $daComplete.on("keydown", "input", function(evt) {
            var rowId = $.attr(this, "data-id");
            Diary.keyHandler(evt, {
                "enter": function(evt){
                    if($.trim(evt.target.value) !== "") {
                        DiaryCommon.diaryRecord.focus(DiaryCommon.diaryRecord.addRow());
                    }
                    evt.preventDefault();
                },
                "backspace": function(evt){
                    if($.trim(evt.target.value) === "" ) {
                        DiaryCommon.diaryRecord.removeRow(rowId);
                        evt.preventDefault();
                    }
                },
                "tab": function(evt){
                    var canFocus;
                    canFocus = DiaryCommon.diaryRecord.focusRow(rowId, evt.shiftKey ? "prev": "next");
                    canFocus && evt.preventDefault();
                }
            });
        });

        // 工作计划移除一行
        $daPlan.on("click", ".o-trash", function() {
            DiaryCommon.diaryPlan.removeRow($.attr(this, "data-id"));
        });

        // 工作计划添加移除快捷键
        $daPlan.on("keydown", "input", function(evt) {
            var rowId = $.attr(this, "data-id");
            Diary.keyHandler(evt, {
                "enter": function(evt){
                    if($.trim(evt.target.value) !== "") {
                        DiaryCommon.diaryPlan.focus(DiaryCommon.diaryPlan.addRow());
                    }
                    evt.preventDefault();
                },
                "delete": function(evt){
                    if($.trim(evt.target.value) === "" ) {
                        DiaryCommon.diaryPlan.removeRow(rowId);
                        evt.preventDefault();
                    }
                },
                "tab": function(evt){
                    var canFocus;
                    canFocus = DiaryCommon.diaryPlan.focusRow(rowId, evt.shiftKey ? "prev": "next");
                    canFocus && evt.preventDefault();
                }
            });
        });
    },
    /**
     * 初始化页面
     * @method initPage
     */
    initPage : function(){
        // 工作日志进度条初始化
        $("[data-node-type='starProgress']").each(function(){
            $(this).studyplay_star({
                prefix: $.attr(this, "data-id")
            }, function(value, $elem){
                $elem.next().val(value);
            });
        });

        // 上传事件初始化
        Ibos.upload.attach({
            post_params: { module:'diary' },
            custom_settings: {
                containerId: "file_target",
                inputId: "attachmentid"
            }
        });

        //表单改动离开页面提示
        Ibos.checkFormChange("#diary_form");
    }
};






$(function(){
    //日志中事件的操作
    DiaryCommon.eventHandler();

    //初始化页面
	DiaryCommon.initPage();

	// 计划日期更改
	$("#da_plan_date").datepicker({
	    component: $("#da_plan_date_btn")
	}).on("changeDate", function(evt) {
	    Diary.changePlanDate(evt.date);
	});


    // 共享人员
    (function() {
        var $daShared = $("#da_shared");
        // 共享人员选人框
        $daShared.userSelect({
            data: Ibos.data.get("user"),
            type: "user"
        });

        //设置默认共享人
        $('#da_share_set').click(function(){
            var deftoid = $daShared.val(),
                param = { deftoid: deftoid };
            DiaryCommon.op.shareSetting(param).done(function(res){
                Ui.tip( ( res.isSuccess ? "@OPERATION_SUCCESS" : ("@OPERATION_FAILED", "error") ) );
            });
        });
    })();

    // 提交验证
    $("#diary_form").on("submit", function(evt){
        if($.data(this, "submiting")) {
            return false;
        }

        var $form = $(this),
            $olds = $form.find('[data-node-type="oldPlan"]'), // 原计划
            $oldInputs = $form.find('[data-node-type="oldPlanInput"]'), // 计划外
            oldLength = $olds.length, 
            newLength = 0,
            $newInputs;

        var getinputedControlLength = function($inputs){
            return $inputs.filter(function(index, elem){
                return $.trim(elem.value) !== "";
            }).length;
        };

        // 加上计划外的条数
        oldLength += getinputedControlLength($oldInputs);

        if(!oldLength) {
            Ui.tip("请至少填写一条工作记录", "warning");
            return false;
        }

        $newInputs = $form.find('[data-node-type="newPlanInput"]'); // 计划
        newLength = getinputedControlLength($newInputs);
        if(!newLength) {
            Ui.tip("请至少填写一条工作计划", "warning");
            return false;
        }

        $.data(this, "submiting", true);
    });
	
	Ibos.evt.add({
		// 前后切换计划日期
		"changePlanDate": function(param){
			var $dp = $("#da_plan_date"),
				dpIns = $dp.data("datetimepicker"),
				oldDate = dpIns.getDate(),
                newDate = Ibos.date.calc(oldDate, param.dir === "prev" ? -1 : 1);

            Diary.changePlanDate(newDate);
            dpIns.setDate(newDate);
		},

        // 添加提醒
        "addRemind": function(param, elem){
            var settings = Ibos.app.g('scaleplateSettings'),
                $planRow = $(elem).closest('[data-node-type="planRow"]'),
                $scaleplateBar = $('<div class="da-plan-vernier"></div>').appendTo($planRow),
                $belt = $('<div class="da-belt"></div>').appendTo($planRow),
                beltIns;
            // 创建标尺
            Diary.createVernier($scaleplateBar, settings);

            // 创建拖拽范围
            beltIns = new Belt($belt, { cell: settings.cell, values: [0, 1] });
            $planRow.data("belt", beltIns);

            // 切换操作栏
            $planRow.addClass("da-remind-editing");
        },

        // 取消提醒
        "cancelRemind": function(param, elem){
            var $planRow = $(elem).closest('[data-node-type="planRow"]'),
                beltIns = $planRow.data("belt");

            // 移除标尺
            $planRow.removeClass("da-remind-editing").removeData("belt")
            .find(".da-plan-vernier").remove();
            beltIns.destory();
        },

        // 保存提醒配置
        "saveRemind": function(param, elem){
            var $planRow = $(elem).closest('[data-node-type="planRow"]'),
                $planOpr = $planRow.find('[data-node-type="planOperate"]'),
                beltIns = $planRow.data("belt"),
                settings = Ibos.app.g('scaleplateSettings'),
                remindValues = $.map(beltIns.values, function(v, i){
                    return v * settings.step + settings.min;
                });

            $planRow.removeClass("da-remind-editing").addClass("da-reminded");

            // 生成提醒视图
            var remindViewTpl = '<div class="da-remind-bar"><i class="o-clock"></i> <%=startTime%>-<%=endTime%> <a href="javascript:;" class="o-close-small" data-action="removeRemind"></a></div>';
            $.tmpl(remindViewTpl, {
                startTime: Ibos.date.numberToTime(remindValues[0]),
                endTime: Ibos.date.numberToTime(remindValues[1])
            }).prependTo($planOpr);

            // 赋值至隐藏域
            $planRow.find('[data-node-type="remindInput"]').val(remindValues.join(","));

            // 移除标尺
            $planRow.removeData("belt").find(".da-plan-vernier").remove();
            beltIns.destory();
        },

        // 移除提醒
        "removeRemind": function(param, elem){
            var $planRow = $(elem).closest('[data-node-type="planRow"]');

            $planRow.removeClass("da-reminded");

            $(elem).closest(".da-remind-bar").remove();
            $planRow.find('[data-node-type="remindInput"]').val("");
        }
	});
});