<div class="clearfix">
    <div class="span3">
        <div class="datepicker input-group" id="start_time">
            <span class="input-group-addon">从</span>
            <a href="javascript:;" class="datepicker-btn"></a>
            <input type="text" value="<?php echo date('Y-m-d', $time['start']); ?>" class="datepicker-input"
                   id="range_start">
        </div>
    </div>
    <div class="span3">
        <div class="datepicker input-group" id="end_time">
            <span class="input-group-addon">至</span>
            <a href="javascript:;" class="datepicker-btn"></a>
            <input type="text" value="<?php echo date('Y-m-d', $time['end']); ?>" class="datepicker-input"
                   id="range_end">
        </div>
    </div>
</div>
<script type="text/javascript">

    //时间类型选择点击效果
    $("#time_type_select li").on("click", function () {
        $(this).addClass("active").siblings().removeClass("active");
    });

    //初始化时间范围选择
    $("#start_time").datepicker({
        target: $("#end_time")
    });
    $("#start_time, #end_time").on("hide", function () {
        var startTime = $("#range_start").val(),
            endTime = $("#range_end").val();
        if (startTime && endTime) {
            window.location.href = Ibos.app.url('<?php echo $timeRoute; ?>', {
                start: startTime,
                end: endTime,
                module: '<?php echo $module; ?>',
                typeid: <?php echo $this->getTypeid(); ?>});
        }
    })
</script>