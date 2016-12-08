<?php

use application\core\utils\Env;
use application\core\utils\Org;
use application\modules\dashboard\model\Stamp;
use application\modules\main\utils\Main;

?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/diary.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/lightbox/css/lightbox.css?<?php echo VERHASH; ?>"/>
<!-- Header -->
<!-- Mainer -->

<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->widget('application\modules\diary\widgets\DiaryReviewSidebar', array(), true); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <div class="page-list">
            <div class="page-list-header">
                <div class="mini-date pull-left">
                    <strong><?php echo $dateWeekDay['day']; ?></strong>
                    <div class="mini-date-body">
                        <p><?php echo $dateWeekDay['weekday']; ?></p>
                        <p><?php echo $dateWeekDay['year']; ?>-<?php echo $dateWeekDay['month']; ?></p>
                    </div>
                </div>
                <div class="btn-group pull-left ml">
                    <button type="button" class="btn btn-icon" id="da_date_btn">
                        <i class="o-ex-calendar vat"></i>
                    </button>
                </div>
                <div class="btn-group pull-left mls">
                    <a href="<?php echo $this->createUrl('review/index', array('date' => 'yesterday')) ?>" class="btn
					<?php
                    $curDate = Env::getRequest('date');
                    if ($curDate == 'yesterday' || !isset($curDate)):
                        ?>active<?php endif; ?>"><?php echo $lang['Yesterday']; ?></a>
                    <a href="<?php echo $this->createUrl('review/index', array('date' => 'today')) ?>" class="btn
					   <?php if (Env::getRequest('date') == 'today'): ?>active<?php endif; ?>"><?php echo $lang['Today']; ?></a>

                </div>
                <div class="btn-group pull-right">
                    <a href="<?php echo $this->createUrl('review/index', array('date' => $prevAndNextDate['prev'], 'uid' => $subUids)); ?>"
                       class="btn">
                        <i class="glyphicon-chevron-left"></i>
                    </a>
                    <a <?php if (TIMESTAMP > $prevAndNextDate['nextTime']): ?>
                        href="<?php echo $this->createUrl('review/index', array('date' => $prevAndNextDate['next'], 'uid' => $subUids)); ?>" class="btn"
                    <?php else: ?>
                        href="javascript:" class="btn disabled"
                    <?php endif; ?>>
                        <i class="glyphicon-chevron-right"></i>
                    </a>
                </div>
            </div>
            <div class="page-list-mainer">
                <ul class="da-list" id="da_list">
                    <?php if (count($data) > 0): ?>
                        <?php foreach ($data as $diary): ?>
                            <li class="da-list-item">
                                <div class="da-summary">
                                    <div class="avatar-box">
                                        <a href="<?php echo $this->createUrl('review/personal', array('uid' => $diary['uid'])) ?>"
                                           class="avatar-circle">
                                            <img class="mbm"
                                                 src="<?php echo Org::getDataStatic($diary['uid'], 'avatar', 'middle') ?>"
                                                 alt="">
                                        </a>
                                        <span
                                            class="avatar-desc"><strong><?php echo $diary['realname']; ?></strong></span>
                                        <!--是否开启关注-->
                                        <?php if ($dashboardConfig['attention']): ?>
                                            <a href="javascript:;" data-action="toggleAsterisk"
                                               <?php if ($diary['isattention']): ?>class="o-da-asterisk"
                                               <?php else: ?>class="o-da-unasterisk"<?php endif; ?>
                                               data-param='{"id": "<?php echo $diary['uid']; ?>"}'></a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="das-content">
                                        <p class="xcm mb">
                                            <a href="javascript:;" data-action="showDiaryDetail"
                                               data-param='{"id": "<?php echo $diary['diaryid']; ?>", "fromController": "<?php echo $this->id; ?>"}'><?php echo $diary['content']; ?></a>
                                        </p>
                                        <div class="da-list-item-desc">
                                            <div class="pull-right">
                                                <a href="<?php echo $this->createUrl('review/show', array('diaryid' => $diary['diaryid'])) ?>"
                                                   target="_blank" class="o-more cbtn"
                                                   title="<?php echo $lang['More']; ?>"></a>
                                            </div>
                                            <span class="fss"><?php echo $diary['addtime']; ?></span>
                                            <span class="fss ilsep">|</span>
                                            <a href="javascript:;" data-node-type="showComment" class="fss"
                                               data-id="<?php echo $diary['diaryid']; ?>"><?php echo $lang['Review']; ?>
                                                <em><?php echo $diary['commentcount']; ?></em></a>
                                            <span class="fss ilsep">|</span>
                                            <a href="javascript:;" data-node-type="showReader" class="fss"
                                               data-id="<?php echo $diary['diaryid']; ?>"><?php echo $lang['Read']; ?>
                                                <em><?php echo $diary['readercount']; ?></em></a>
                                            <?php if ($diary['stamp'] > 0): ?>
                                                <?php $iconUrl = Stamp::model()->fetchIconById($diary['stamp']); ?>
                                                &nbsp;&nbsp;<img width="60" height="24"
                                                                 id="diary_stamp_<?php echo $diary['diaryid']; ?>"
                                                                 src="<?php echo $iconUrl; ?>"/>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="da-detail" style="display:none;"
                                     data-id="<?php echo $diary['diaryid']; ?>"></div>
                                <div class="da-mark-down-wrap">
                                    <a href="javascript:;" class="da-mark-down" data-action="showDiaryDetail"
                                       data-param='{"id": "<?php echo $diary['diaryid']; ?>", "fromController": "<?php echo $this->id; ?>"}'></a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data-tip"></div>
                    <?php endif; ?>
                </ul>
                <?php if (count($noRecordUserList) > 0): ?>
                    <div class="da-remind">
                        <h3 class="mb"><?php echo $lang['They have no record today']; ?></h3>
                        <ul class="da-remind-list">
                            <?php foreach ($noRecordUserList as $user): ?>
                                <li data-uid="<?php echo $user['uid']; ?>">
                                    <a href="<?php echo $this->createUrl('review/personal', array('uid' => $user['uid'])); ?>"
                                       target="_blank" class="avatar-box">
										<span class="avatar-circle">
											<img class="mbm"
                                                 src="<?php echo Org::getDataStatic($user['uid'], 'avatar', 'middle') ?>"
                                                 alt="">
										</span>
                                        <span
                                            class="avatar-desc"><strong><?php echo $user['realname']; ?></strong></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li class="da-bell">
                                <?php if (Main::getCookie('reminded_' . strtotime($date))): ?>
                                    <a href="javascript:" class="">
                                        <i class="o-da-reminded"></i>
                                        <span
                                            class="avatar-desc"><strong><?php echo $lang['Reminded']; ?></strong></span>
                                    </a>
                                <?php else: ?>
                                    <a href="javascript:" class="avatar-box" data-action="remindUnderling">
                                        <i class="o-da-bell"></i>
                                        <span
                                            class="avatar-desc"><strong><?php echo $lang['Remind to write log']; ?></strong></span>
                                    </a>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
                <div class="page-list-footer">
                    <div class="pull-right">
                        <?php $this->widget('application\core\widgets\Page', array('pages' => $pagination)); ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mainer content -->
    </div>
</div>
<!-- Footer -->
<script>
    Ibos.app.setPageParam({
        reviewDate: '<?php echo $date; ?>',
        issetStamp: '<?php echo $this->issetStamp(); ?>',
        stampEnable: '<?php echo $dashboardConfig["stampenable"] ?>',
        stamps: <?php echo $this->getStamp(); ?>,
        stampPath: '',
        autoReview: '<?php echo $dashboardConfig["autoreview"] ?>'
    })
</script>
<script src="<?php echo STATICURL; ?>/js/lib/lightbox/js/lightbox.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/diary.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/diary_review_index.js?<?php echo VERHASH; ?>'></script>


