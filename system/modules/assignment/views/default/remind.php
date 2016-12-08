<form action="javascript:;" style="width: 300px;">
    <div class="btn-toolbar mb">
        <div class="btn-group">
            <button class="btn dropdown-toggle am-select-btn" id="task_rm_date_picker" style="width: 130px;">
                <i class="caret"></i>
                <?php echo $reminddate; ?>
            </button>
            <input type="hidden" name="reminddate" id="task_rm_date" value="<?php echo $reminddate; ?>">
        </div>
        <div class="btn-group ml">
            <button class="btn am-select-btn" id="task_rm_time_picker" style="width: 100px;">
                <i class="caret"></i>
                <?php echo $remindtime; ?>
            </button>
        </div>
        <input type="hidden" name="remindtime" id="task_rm_time" value="<?php echo $remindtime; ?>">
        <span class="pull-left" style="padding: 10px;"><?php echo $lang['Remind me']; ?></span>
    </div>
    <div>
        <input type="text" placeholder="<?php echo $lang['Remind content']; ?>" name="remindcontent"
               value="<?php echo $content; ?>">
    </div>
</form>
<script>
    new DropdownDatepicker("#task_rm_date_picker");
    $("#task_rm_date_picker").on("changeDate", function (evt, data) {
        var formatedDate = Ibos.date.format(data.date)
        $(this).html((data.date ? formatedDate : U.lang("ASM.NO_LONGER")) + "<i class='caret'></i>");
        $("#task_rm_date").val(data.date ? formatedDate : "");
    });

    $("#task_rm_time_picker").datepicker({
        pickTime: true,
        pickDate: false,
        pickSeconds: false,
        format: "hh:ii"
    }).on("show", function () {
        var widget = $(this).data("datetimepicker").widget;
        widget.position({
            of: this,
            at: "left bottom",
            my: "left top+5"
        })
    }).on("changeDate", function (evt) {
        var formatedDate = Ibos.date.format(evt.localDate, "hh:ii")
        $(this).html(formatedDate + "<i class='caret'></i>");
        $("#task_rm_time").val(formatedDate);
    })
</script>