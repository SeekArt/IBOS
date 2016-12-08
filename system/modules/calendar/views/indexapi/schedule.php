<?php

use application\core\utils\Ibos;

?>

    <link rel="stylesheet" href="<?php echo $assetUrl . '/css/index_calendar.css'; ?>">
    <!-- IE 8 Hack 加入空script标签延迟html加载，为了让空值图片能正常显示 -->
    <script></script>

<?php if (!empty($schedules)): ?>
    <table class="table table-underline">
        <tbody>
        <?php foreach ($schedules as $k => $schedule): ?>
            <tr id="cal_list_<?php echo $k; ?>" class="cal-list-first">
                <td style="padding:0; width:5px; <?php if (!empty($schedule['category'])): ?>background: #<?php echo $schedule['category'] ?><?php endif; ?>"></td>
                <?php if ($k > 0 && date('Y-m-d', $schedule['starttime']) == date('Y-m-d', $schedules[$k - 1]['starttime'])): ?>
                    <td width="90"
                        <?php if ($k + 1 < count($schedules) && date('Y-m-d', $schedule['starttime']) == date('Y-m-d', $schedules[$k + 1]['starttime'])): ?>style="border-bottom:none"<?php endif; ?>></td>
                <?php else: ?>
                    <td width="90"
                        <?php if ($k + 1 < count($schedules) && date('Y-m-d', $schedule['starttime']) == date('Y-m-d', $schedules[$k + 1]['starttime'])): ?>style="border-bottom:none"<?php endif; ?>>
                        <div class="mini-date">
                            <strong><?php echo $schedule['dateAndWeekDay']['day']; ?></strong>
                            <div class="mini-date-body">
                                <p><?php echo $schedule['dateAndWeekDay']['weekday']; ?></p>
                                <p><?php echo $schedule['dateAndWeekDay']['year']; ?>
                                    -<?php echo $schedule['dateAndWeekDay']['month']; ?></p>
                            </div>
                        </div>
                    </td>
                <?php endif; ?>
                <td width="90">
                    <?php if ($schedule['isalldayevent']): ?>
                        <span class="tcm">全天</span>
                    <?php else: ?>
                        <span class="tcm"><?php echo date('H:i', $schedule['starttime']); ?>
                            -<?php echo date('H:i', $schedule['endtime']); ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <p class="xcm cal-item-title"
                       title="<?php echo $schedule['subject']; ?>"><?php echo $schedule['cutSubject']; ?></p>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="in-cal-empty">
        <a href="<?php echo Ibos::app()->createUrl('calendar/schedule/index') ?>" class="in-cal-add"
           target="_blank"></a>
    </div>
<?php endif; ?>