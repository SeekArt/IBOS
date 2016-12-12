<?php

use application\core\utils\Ibos;
use application\modules\dashboard\model\Stamp;
use application\modules\main\utils\Main;

?>
<link rel="stylesheet" href="<?php echo $assetUrl . '/css/index_diary.css'; ?>">
<!-- IE 8 Hack 加入空script标签延迟html加载，为了让空值图片能正常显示 -->
<script></script>

<?php if ($tab == 'diaryPersonal'): ?>
    <div class="in-diary-left">
        <div class="in-dcal">
            <table class="in-dcal-table" id="dcal_table">
                <thead>
                <tr>
                    <th><span><?php echo $lang['Day']; ?></span></th>
                    <th><span><?php echo $lang['One']; ?></span></th>
                    <th><span><?php echo $lang['Two']; ?></span></th>
                    <th><span><?php echo $lang['Three']; ?></span></th>
                    <th><span><?php echo $lang['Four']; ?></span></th>
                    <th><span><?php echo $lang['Five']; ?></span></th>
                    <th><span><?php echo $lang['Six']; ?></span></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <?php foreach ($calendar as $key => $value): ?>
                    <?php if ($value['className'] == 'log'): ?>
                        <?php $value['className'] = 'has' ?>
                    <?php elseif ($value['className'] == 'log comment'): ?>
                        <?php $value['className'] = 'has comment' ?>
                    <?php elseif ($value['className'] == 'old'): ?>
                        <?php $value['className'] = 'prev' ?>
                    <?php elseif ($value['className'] == 'current'): ?>
                        <?php $value['className'] = 'has today' ?>
                    <?php elseif ($value['className'] == 'new'): ?>
                        <?php $value['className'] = 'next' ?>
                    <?php endif; ?>
                    <td data-id="<?php echo $value['diaryid']; ?>" style="cursor:pointer;"><a
                            data-id="<?php echo $value['diaryid']; ?>" href="javascript:"
                            class="<?php echo $value['className']; ?>"><?php echo $value['day']; ?></a></td>
                    <?php if (($key + 1) % 7 == 0): ?>
                </tr>
                <tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="in-diary-right">
        <div class="tray in-diary-tray xcbu mb">
            <div class="fss xwb"><?php echo $dateWeekDay['year']; ?>年<?php echo $dateWeekDay['month']; ?>月</div>
            <div class="date"><?php echo $dateWeekDay['day']; ?></div>
            <div class="xwb"><?php echo $dateWeekDay['weekday']; ?></div>
        </div>

        <div class="bdbs fill" style="height:45px;">
            <?php if (!empty($diary)): ?>
                <!-- 当已经点评时 -->
                <?php if (isset($stampUrl)): ?>
                    <img src="<?php echo $stampUrl; ?>" alt="" width="135" height="81" class="in-diary-stamp">
                <?php else: ?>
                    <div class="mb xac">
                        <?php echo $lang['You have write log']; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="xac">
                    <a href="<?php echo Ibos::app()->urlManager->createUrl('diary/default/add'); ?>"
                       class="btn btn-warning">
                        <i class="o-new"></i>
                        <?php echo $lang['Write log']; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="xac fill-hh">
            <!-- if 有前一篇日志时 -->
            <?php if (!empty($preDiary)): ?>
                <i class="o-diary-clock"></i>
                <?php echo date('m-d', $preDiary['diarytime']); ?>
                <!-- 已评阅时，显示评阅 -->
                <?php if (isset($preStampIcon)): ?>
                    <img src="<?php echo $preStampIcon; ?>" alt="" width="60" height="24">
                <?php endif; ?>
            <?php else: ?>
                <?php echo $lang['You have not write log yesterday']; ?>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="in-diary-left">
        <!-- 内容 -->
        <?php if (count($noReviewRecordList) > 0 || count($reviewRecordList) > 0): ?>
            <div class="show">
                <table class="table table-underline">
                    <tbody>
                    <?php foreach ($noReviewRecordList as $review): ?>
                        <tr>
                            <td width="40">
                                <div class="avatar-box" data-toggle="usercard"
                                     data-param="uid=<?php echo $review['uid']; ?>">
										<span class="avatar-circle avatar-circle-small">
											<img src="<?php echo $review['user']['avatar_middle']; ?>">
										</span>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo Ibos::app()->urlManager->createUrl('diary/review/show', array('diaryid' => $review['diaryid'])); ?>"><?php echo $review['user']['realname'] . ' &nbsp; ' . $review['diarytime']; ?></a>
                            </td>
                            <td width="60">

                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach ($reviewRecordList as $review): ?>
                        <tr>
                            <td width="40">
                                <div class="avatar-box" data-toggle="usercard"
                                     data-param="uid=<?php echo $review['uid']; ?>">
										<span class="avatar-circle avatar-circle-small">
											<img src="<?php echo $review['user']['avatar_middle']; ?>">
										</span>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo Ibos::app()->urlManager->createUrl('diary/review/show', array('diaryid' => $review['diaryid'])); ?>"><?php echo $review['user']['realname'] . ' &nbsp; ' . $review['diarytime']; ?></a>
                            </td>
                            <td width="60">
                                <?php if ($review['stamp'] != 0): ?>
                                    <?php $iconUrl = Stamp::model()->fetchIconById($review['stamp']); ?>
                                    <img src="<?php echo $iconUrl ?>" alt="">
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($reviewRecordList) + count($noReviewRecordList) > 4): ?>
                    <div class="mbox-base">
                        <div class="fill-hn xac">
                            <a href="<?php echo Ibos::app()->urlManager->createUrl('diary/review/index'); ?>"
                               class="link-more"> <i class="cbtn o-more"></i>
                                <span class="ilsep"><?php echo $lang['See more logs']; ?></span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- 空值 -->
            <div class="in-diary-appraise-empty 
				 <?php if (Main::getCookie('reminded_' . strtotime(date('Y-m-d')))): ?>o-da-reminded<?php endif; ?>">
                <?php if (!Main::getCookie('reminded_' . strtotime(date('Y-m-d')))): ?>
                    <a href="javascript:" class="da-bell" id="remind_underling">
                        <i class="o-da-bell"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="in-diary-right">
        <div class="tray in-diary-tray xcbu mb">
            <div class="fss xwb"><?php echo $dateWeekDay['year']; ?>年<?php echo $dateWeekDay['month']; ?>月</div>
            <div class="date"><?php echo $dateWeekDay['day']; ?></div>
            <div class="xwb"><?php echo $dateWeekDay['weekday']; ?></div>
        </div>
        <div class="fill-hh">
            <div class="mbs">
                <div class="mbm">
                    <strong class="pull-right fss">
                        <?php echo $reviewInfo['reviewedCount']; ?> / <?php echo $reviewInfo['count']; ?>
                    </strong>
                    <span><?php echo $lang['Situation of review']; ?></span>
                </div>
                <div>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php
                        if ($reviewInfo['count']) {
                            echo $reviewInfo['reviewedCount'] / $reviewInfo['count'] * 100;
                        } else {
                            echo 0;
                        }
                        ?>%;"></div>
                    </div>
                </div>
            </div>
            <div>
                <div class="mbs"><?php echo $lang['They have no record today']; ?></div>
                <ul class="list-inline">
                    <?php foreach ($noRecordUserList as $user): ?>
                        <li>
                            <a href="<?php echo Ibos::app()->urlManager->createUrl('diary/review/index'); ?>">
                                <img src="<?php echo $user['avatar_middle']; ?>" title="<?php echo $user['realname'] ?>"
                                     alt="" class="avatar-small">
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    Ibos.app.setPageParam({
        currentMonth: '<?php echo $dateWeekDay['month']; ?>',
        currentYear: '<?php echo $dateWeekDay['year']; ?>',
        subUids: '<?php echo $subUids; ?>',
        date: '<?php echo date("Y-m-d"); ?>'
    });

    (function () {
        var personalDiary = {
            show: function (diaryid, currentDay) {
                window.location = Ibos.app.url('diary/default/show', {
                    diaryid: diaryid,
                    currentDay: currentDay
                })
            },
            add: function (date, currentDay) {
                window.location = Ibos.app.url('diary/default/add', {
                    diaryDate: date,
                    currentDay: currentDay
                })
            }
        }

        //点击某一天的动作
        $('#dcal_table').on('click', 'a', function () {
            var diaryid = $.attr(this, 'data-id'),
                className = $.attr(this, 'class'),
                currentDay = this.innerHTML,
                currentMonth, currentYear;

            if (className !== 'prev' && className !== 'next') {
                // 当存在diaryId时，说明已经写了日志，此时行为为查看日志
                if (diaryid && diaryid !== "") {
                    personalDiary.show(diaryid, currentDay);
                    // 否则为新建日志
                } else {
                    currentMonth = Ibos.app.g("currentMonth");
                    currentYear = Ibos.app.g("currentYear");
                    personalDiary.add([currentYear, currentMonth, currentDay].join("-"), currentDay);
                }
            }
        });
        // 提醒未写日志的下属
        $("#remind_underling").on("click", function () {
            var $elem = $(this);
            $.post(Ibos.app.url('diary/review/edit', {op: 'remind'}), {
                uids: Ibos.app.g('subUids'),
                date: Ibos.app.g('date')
            }, function (res) {
                if (res.isSuccess === true) {
                    Ui.tip(res.msg);
                    $elem.hide().parent().addClass("o-da-reminded");
                }
            });
        })
    })();
</script>