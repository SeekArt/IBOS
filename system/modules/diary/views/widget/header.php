<?php

use application\core\utils\Env;
use application\core\utils\Ibos;

?>
<div class="clearfix">
    <ul class="time-type-select" id="time_type_select">
        <?php $uid = Env::getRequest('uid'); ?>
        <li <?php if ($time['timestr'] == 'thisweek'): ?> class="active"<?php endif; ?>>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => 'thisweek', 'module' => $module, 'uid' => $uid)); ?>"><?php echo $lang['This week']; ?></a>
        </li>
        <li <?php if ($time['timestr'] == 'lastweek'): ?> class="active"<?php endif; ?>>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => 'lastweek', 'module' => $module, 'uid' => $uid)); ?>"><?php echo $lang['Last week']; ?></a>
        </li>
        <li <?php if ($time['timestr'] == 'thismonth'): ?> class="active"<?php endif; ?>>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => 'thismonth', 'module' => $module, 'uid' => $uid)); ?>"><?php echo $lang['This month']; ?></a>
        </li>
        <li <?php if ($time['timestr'] == 'lastmonth'): ?> class="active"<?php endif; ?>>
            <a href="<?php echo Ibos::app()->createUrl($timeRoute, array('time' => 'lastmonth', 'module' => $module, 'uid' => $uid)); ?>"><?php echo $lang['Last month']; ?></a>
        </li>
    </ul>
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
<script>
    Ibos.app.setPageParam({
        curUid: '<?php echo Env::getRequest('uid'); ?>'
    });
</script>
<script type="text/javascript">

    //时间类型选择点击效果
    $("#time_type_select li").on("click", function () {
        $(this).addClass("active").siblings().removeClass("active");
    });

    //初始化时间范围选择
    $("#start_time").datepicker({target: $("#end_time")});
    //选择开始时间操作,发送开始时间和结束时间到后台
    //选择结束时间操作,发送开始时间和结束时间到后台
    $("#start_time, #end_time").on("hide", function () {
        var startTime = $("#range_start").val(),
            endTime = $("#range_end").val();
        if (startTime && endTime) {
            window.location.href = Ibos.app.url('<?php echo $timeRoute; ?>', {
                start: startTime,
                end: endTime,
                module: '<?php echo $module; ?>',
                uid: Ibos.app.g('curUid')
            });
        }
    })
</script>