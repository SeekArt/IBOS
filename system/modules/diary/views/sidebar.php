<?php

use application\core\utils\Ibos;
use application\core\utils\Module;
use application\modules\statistics\core\StatConst;
use application\modules\statistics\utils\StatCommon;

?>
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li class="active">
                <a href="<?php echo $this->createUrl('default/index'); ?>">
                    <i class="o-da-personal"></i>
                    <?php echo Ibos::lang('Personal'); ?>
                </a>
                <div class="da-calendar">
                    <div class="da-calendar-month clearfix">
                        <a href="javascript:;" class="da-calendar-prev" data-action="toPrevMonth"></a>
                        <a href="javascript:;" id="ym"><?php echo $currentDateInfo['year']; ?>
                            / <?php echo $currentDateInfo['monthStr']; ?><?php echo Ibos::lang('Month'); ?></a>
                        <a href="javascript:;" class="da-calendar-next" data-action="toNextMonth"></a>
                    </div>
                    <div class="da-calendar-week">
                        <table class="da-calendar-table">
                            <tbody>
                            <tr>
                                <th><?php echo Ibos::lang('Day'); ?></th>
                                <th><?php echo Ibos::lang('One'); ?></th>
                                <th><?php echo Ibos::lang('Two'); ?></th>
                                <th><?php echo Ibos::lang('Three'); ?></th>
                                <th><?php echo Ibos::lang('Four'); ?></th>
                                <th><?php echo Ibos::lang('Five'); ?></th>
                                <th><?php echo Ibos::lang('Six'); ?></th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="da-calendar-date">
                        <table class="da-calendar-table">
                            <tbody id="da_calendar_tbody">
                            <tr>
                                <?php foreach ($calendar as $key => $value): ?>
                                <td data-action="toOneDay" data-id="<?php echo $value['diaryid']; ?>"
                                    class="<?php echo $value['className']; ?>"><?php echo $value['day']; ?></td>
                                <?php if (($key + 1) % 7 == 0): ?>
                            </tr>
                            <tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tr>
                            </tbody>
                        </table>
                        <!-- <input type="hidden" name="currentYear" id="currentYear" value="<?php echo $currentDateInfo['year']; ?>"> -->
                        <!-- <input type="hidden" name="currentMonth" id="currentMonth" value="<?php echo $currentDateInfo['month']; ?>"> -->
                    </div>
                    <div class="da-calendar-footer">
                        <span>
                            <i class="da-square-log"></i>
                            <?php echo Ibos::lang('Have a diary'); ?>
                        </span>
                        <span>
                            <i class="da-square-comment"></i>
                            <?php echo Ibos::lang('Have comments'); ?>
                        </span>
                        <span>
                            <i class="da-square-current"></i>
                            <?php echo Ibos::lang('Has been selected'); ?>
                        </span>
                    </div>
                </div>
            </li>

            <li>
                <a href="<?php echo $this->createUrl('review/index'); ?>">
                    <?php if ($this->getUnreviews() != ''): ?>
                        <span class="badge pull-right"><?php echo $this->getUnreviews(); ?></span>
                    <?php endif ?>
                    <i class="o-da-appraise"></i>
                    <?php echo Ibos::lang('Review it'); ?>
                </a>
            </li>

            <?php if ($this->issetShare()): ?>
                <li>
                    <a href="<?php echo $this->createUrl('share/index'); ?>">
                        <i class="o-da-concerned"></i>
                        <?php echo Ibos::lang('Share diary'); ?>
                    </a>
                </li>
            <?php endif; ?>
            <?php if ($this->issetAttention()): ?>
                <li>
                    <a href="<?php echo $this->createUrl('attention/index'); ?>">
                        <i class="o-da-shared"></i>
                        <?php echo Ibos::lang('Attention diary'); ?>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Module::getIsEnabled('statistics') && isset($statModule['diary'])): ?>
                <?php echo $this->widget(StatCommon::getWidgetName('diary', StatConst::SIDEBAR_WIDGET), array('hasSub' => $this->checkIsHasSub()), true); ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
<script type="text/ibos-template" id="sidebar_template">
    <td data-action="toOneDay" data-id="<%=diaryid%>" class="<%=className%>"><%=day%></td>
</script>
<script>
    Ibos.app.setPageParam({
        'currentYear': <?php echo $currentDateInfo['year']; ?>,
        'currentMonth': <?php echo $currentDateInfo['month']; ?>
    })
</script>
<script>
    (function () {
        //取得前一个月的日历
        function processData(data, month) {
            var WEEKDAY = 7;
            var htmlStr = '<tr>';
            for (var i = 0; i < data.length; i++) {
                var currentData = {
                    diaryid: data[i].diaryid,
                    className: data[i].className,
                    day: data[i].day
                };
                var temp = $.template('sidebar_template', currentData);
                // 当索引值 除以星期数的余数为0时，换一行
                if ((i + 1) % WEEKDAY === 0) {
                    temp += '</tr><tr>';
                }
                htmlStr += temp;
            }
            $('#da_calendar_tbody').html(htmlStr);
            var yearNumber = month.substring(0, 4);
            var monthNumber = month.substring(4);
            var monthName = [
                U.lang("CNUM.ONE"),
                U.lang("CNUM.TWO"),
                U.lang("CNUM.THREE"),
                U.lang("CNUM.FOUR"),
                U.lang("CNUM.FIVE"),
                U.lang("CNUM.SIX"),
                U.lang("CNUM.SEVEN"),
                U.lang("CNUM.EIGHT"),
                U.lang("CNUM.NINE"),
                U.lang("CNUM.TEN"),
                U.lang("CNUM.ELEVEN"),
                U.lang("CNUM.TWELVE")
            ];
            var monthStr = monthName[monthNumber - 1] + U.lang('TIME.MONTH');
            $('#ym').html(yearNumber + ' / ' + monthStr);
            Ibos.app.setPageParam({
                "currentYear": parseInt(yearNumber, 10),
                "currentMonth": parseInt(monthNumber, 10)
            });
        }

        var _render = function (dir) {
            var $cont = $("#da_calendar_tbody"),
                month = Ibos.app.g('currentMonth'),
                year = Ibos.app.g('currentYear'),
                ym;

            if (dir === "prev") {
                ym = (month === 1) ? ('' + (year - 1) + 12) : ('' + year + (month - 1))
            } else if (dir === "next") {
                ym = (month === 12) ? ('' + (year + 1) + 1) : ('' + year + (month + 1))
            }
            $cont.waiting(null, 'small')
            $.get(Ibos.app.url('diary/default/index', {'op': 'getAjaxSidebar'}), {ym: ym}, function (data) {
                processData(data, ym);
                $cont.waiting(false);
            });
        }

        Ibos.evt.add({
            // 取得前一个月的日历
            "toPrevMonth": function () {
                _render("prev");
            },
            // 取得后一个月的日历
            "toNextMonth": function () {
                _render("next");
            },
            //点击某一天的动作
            "toOneDay": function (param, elem) {
                var $elem = $(elem),
                    diaryid = $elem.attr('data-id'),
                    className = $elem.attr('class');
                if (className !== 'old' && className !== 'new') {
                    var currentMonth = Ibos.app.g('currentMonth');
                    var currentYear = Ibos.app.g('currentYear');
                    if (diaryid && diaryid !== "") {
                        window.location = Ibos.app.url('diary/default/show', {
                            diaryid: diaryid,
                            currentDay: $elem.html()
                        });
                    } else {
                        var date = currentYear + '-' + currentMonth + '-' + $elem.html();
                        window.location = Ibos.app.url('diary/default/add', {
                            diaryDate: date,
                            currentDay: $elem.html()
                        });
                    }
                }
            }
        })

    })();
</script>
