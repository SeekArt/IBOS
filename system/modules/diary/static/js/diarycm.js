// 新增页和编辑页的一些共有交互

$(function(){
	//表单改动离开页面提示
	Ibos.checkFormChange("#diary_form");

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

	// 计划日期更改
	$("#da_plan_date").datepicker({
	    component: $("#da_plan_date_btn")
	}).on("changeDate", function(evt) {
	    Diary.changePlanDate(evt.date);
	});


	// 
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
            var remindViewTpl = '<div class="da-remind-bar"><i class="o-clock"></i> <%=startTime%>-<%=endTime%> <a href="javascript:;" class="o-close-small" data-action="removeRemind"></a></div>'
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
			var deftoid = $daShared.val();
			$.post(Ibos.app.url('diary/default/edit', {'op': 'setShare'}), { deftoid: deftoid }, function(res){
				if(res.isSuccess){
					Ui.tip("@OPERATION_SUCCESS");
				} else {
					Ui.tip("@OPERATION_FAILED", "error");
				}
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
    			return $.trim(elem.value) !== ""
    		}).length;
    	}

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
    })
})