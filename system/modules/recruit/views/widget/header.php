<?php

use application\core\utils\Ibos;

?>
<div class="clearfix">
    <ul class="time-type-select" id="time_type_select">
        <li <?php if ($time['timestr'] == 'thisweek'): ?> class="active"<?php endif; ?>>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => 'thisweek', 'module' => $module, 'type' => $type)); ?>"><?php echo $lang['This week']; ?></a>
        </li>
        <li <?php if ($time['timestr'] == 'lastweek'): ?> class="active"<?php endif; ?>>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => 'lastweek', 'module' => $module, 'type' => $type)); ?>"><?php echo $lang['Last week']; ?></a>
        </li>
        <li <?php if ($time['timestr'] == 'thismonth'): ?> class="active"<?php endif; ?>>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => 'thismonth', 'module' => $module, 'type' => $type)); ?>"><?php echo $lang['This month']; ?></a>
        </li>
        <li <?php if ($time['timestr'] == 'lastmonth'): ?> class="active"<?php endif; ?>>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => 'lastmonth', 'module' => $module, 'type' => $type)); ?>"><?php echo $lang['Last month']; ?></a>
        </li>
    </ul>
    <div class="span3">
        <div class="datepicker input-group" id="start_time">
            <span class="input-group-addon">从</span>
            <a href="javascript:;" class="datepicker-btn"></a>
            <input type="text" class="datepicker-input" id="range_start"
                   value="<?php echo date('Y-m-d', $time['start']); ?>">
        </div>
    </div>
    <div class="span3">
        <div class="datepicker input-group" id="end_time">
            <span class="input-group-addon">至</span>
            <a href="javascript:;" class="datepicker-btn"></a>
            <input type="text" class="datepicker-input" id="range_end"
                   value="<?php echo date('Y-m-d', $time['end']); ?>">
        </div>
    </div>
    <!-- 当按钮不可用时,<a>标签的href属性为href="javascript:void(0);",可用时为对应的地址值 -->
    <div class="btn-group" id="range_select">
        <?php if ($time['timestr'] == 'custom'): ?>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('start' => date('Y-m-d', $time['start']), 'end' => date('Y-m-d', $time['end']), 'module' => $module, 'type' => 'day')); ?>"
               type="button" id="day_choose"
               class="btn time-choose-btn <?php if ($type == 'day'): ?>active<?php endif; ?>">日</a>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('start' => date('Y-m-d', $time['start']), 'end' => date('Y-m-d', $time['end']), 'module' => $module, 'type' => 'week')); ?>"
               type="button" id="week_choose"
               class="btn time-choose-btn <?php if ($type == 'week'): ?>active<?php endif; ?>">周</a>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('start' => date('Y-m-d', $time['start']), 'end' => date('Y-m-d', $time['end']), 'module' => $module, 'type' => 'month')); ?>"
               type="button" id="month_choose"
               class="btn time-choose-btn <?php if ($type == 'month'): ?>active<?php endif; ?>">月</a>
        <?php else: ?>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => $time['timestr'], 'module' => $module, 'type' => 'day')); ?>"
               type="button" id="day_choose"
               class="btn time-choose-btn <?php if ($type == 'day'): ?>active<?php endif; ?>">日</a>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => $time['timestr'], 'module' => $module, 'type' => 'week')); ?>"
               type="button" id="week_choose"
               class="btn time-choose-btn <?php if ($type == 'week'): ?>active<?php endif; ?>">周</a>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => $time['timestr'], 'module' => $module, 'type' => 'month')); ?>"
               type="button" id="month_choose"
               class="btn time-choose-btn <?php if ($type == 'month'): ?>active<?php endif; ?>">月</a>
        <?php endif; ?>
    </div>
</div>
<script type="text/javascript">
    //时间类型选择点击效果
    $("#time_type_select li").on("click", function () {
        $(this).addClass("active").siblings().removeClass("active");
    });

    //初始化时间范围选择
    $("#start_time").datepicker({target: $("#end_time")});
    $("#start_time, #end_time").on("hide", function () {
        var startTime = $("#range_start").val(),
            endTime = $("#range_end").val();
        if (startTime && endTime) {
            window.location.href = Ibos.app.url('<?php echo $timeRoute; ?>', {
                start: startTime,
                end: endTime,
                module: '<?php echo $module; ?>'
            });
        }
    })

    //时间范围,"日""周""月"三种类型点击选择时样式切换
    $("#range_select .btn").on("click", function () {
        $(this).addClass("active").siblings().removeClass("active");
    });


</script>