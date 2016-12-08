<div id="dialog_cal_setup">
    <form class="form-horizontal form-compact">
        <div class="control-group">
            <label class="control-label">时间段</label>
            <div class="controls">
                <div type="text" id="cal_interval_slider"></div>
                <div id="cal_interval_preview" class="xwb xco"></div>
                <input type="hidden" id="cal_interval_value" name="calviewinterval">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">隐藏日期</label>
            <div class="controls">
                <label class="checkbox checkbox-inline">
                    <input type="checkbox" name="calviewhiddenday" value="1"
                           <?php if (in_array(1, $hiddenDays)): ?>checked<?php endif; ?>>
                    一
                </label>
                <label class="checkbox checkbox-inline">
                    <input type="checkbox" name="calviewhiddenday" value="2"
                           <?php if (in_array(2, $hiddenDays)): ?>checked<?php endif; ?>>
                    二
                </label>
                <label class="checkbox checkbox-inline">
                    <input type="checkbox" name="calviewhiddenday" value="3"
                           <?php if (in_array(3, $hiddenDays)): ?>checked<?php endif; ?>>
                    三
                </label>
                <label class="checkbox checkbox-inline">
                    <input type="checkbox" name="calviewhiddenday" value="4"
                           <?php if (in_array(4, $hiddenDays)): ?>checked<?php endif; ?>>
                    四
                </label>
                <label class="checkbox checkbox-inline">
                    <input type="checkbox" name="calviewhiddenday" value="5"
                           <?php if (in_array(5, $hiddenDays)): ?>checked<?php endif; ?>>
                    五
                </label>
                <label class="checkbox checkbox-inline">
                    <input type="checkbox" name="calviewhiddenday" value="6"
                           <?php if (in_array(6, $hiddenDays)): ?>checked<?php endif; ?>>
                    六
                </label>
                <label class="checkbox checkbox-inline">
                    <input type="checkbox" name="calviewhiddenday" value="0"
                           <?php if (in_array(0, $hiddenDays)): ?>checked<?php endif; ?>>
                    日
                </label>
            </div>
        </div>
        <div class="control-group" id="share_edit_content">
            <label><?php echo $lang['Edit permission setting for schedule']; ?>
                <small class="xcr"><?php echo $lang['Editor own permission of read and edit']; ?></small>
            </label>
            <div class="span12">
                <input type="text" name="edituid" id="share_edit_limited"
                       value="<?php echo $sharingPersonnel['editSharing']; ?>">
            </div>
        </div>
        <div class="control-group" id="share_view_content">
            <label><?php echo $lang['Read permission setting for schedule']; ?></label>
            <div class="span12">
                <input type="text" name="viewuid" id="share_view_limited"
                       value="<?php echo $sharingPersonnel['viewSharing']; ?>">
            </div>
        </div>
    </form>
</div>
<script>
    Ibos.app.s({
        calViewInterval: [<?php echo $workTime['startTime'] . ', ' . $workTime['endTime']; ?>]
    });

    (function () {
        var refreshView = function (values) {
            var start = Ibos.date.numberToTime(values[0]),
                end = Ibos.date.numberToTime(values[1]);
            $("#cal_interval_preview").html(start + " - " + end);
            $("#cal_interval_value").val(values.join(","))
        }

        var calViewDays = Ibos.app.g("calViewHiddenDays");
        $("#dialog_cal_setup .checkbox input").label();

        $("#cal_interval_slider")
            .on("slide", function (jsEvt, data) {
                refreshView(data.values);
            })
            .ibosSlider({
                range: true,
                min: 0,
                max: 24,
                step: .5,
                values: Ibos.app.g("calViewInterval"),
                scale: 6
            });
        refreshView(Ibos.app.g("calViewInterval"));
    })();

    (function () {
        var $viewShare = $("#share_view_limited"),
            $editShare = $("#share_edit_limited");

        // 共享人员选人框
        $viewShare.userSelect({
            data: Ibos.data.get("user"),
            type: "user"
        });

        $editShare.userSelect({
            data: Ibos.data.get("user"),
            type: "user",
        });
    })();
</script>