<?php

use application\core\utils\Env;

?>
<!-- load css -->
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/diary.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/lightbox/css/lightbox.css?<?php echo VERHASH; ?>"/>

<!-- Header -->

<!-- Mainer -->
<div class="wrap">
    <div class="mc clearfix">
        <!-- Sidebar -->
        <?php echo $this->getSidebar(); ?>
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
                        <a href="<?php echo $this->createUrl('attention/index', array('date' => 'yesterday')) ?>"
                           class="btn <?php $date = Env::getRequest('date');
                           if ($date == 'yesterday' || !isset($date)): ?>active<?php endif; ?>"><?php echo $lang['Yesterday']; ?></a>
                        <a href="<?php echo $this->createUrl('attention/index', array('date' => 'today')) ?>"
                           class="btn <?php $date = Env::getRequest('date');
                           if ($date == 'today'): ?>active<?php endif; ?>"><?php echo $lang['Today']; ?></a>
                    </div>
                    <div class="btn-group pull-right">
                        <a href="<?php echo $this->createUrl('attention/index', array('date' => $prevAndNextDate['prev'])); ?>"
                           class="btn">
                            <i class="glyphicon-chevron-left"></i>
                        </a>
                        <a <?php if (TIMESTAMP > $prevAndNextDate['nextTime']): ?>
                            href="<?php echo $this->createUrl('attention/index', array('date' => $prevAndNextDate['next'])); ?>" class="btn"
                        <?php else: ?>
                            href="javascript:;" class="btn disabled"
                        <?php endif; ?>>
                            <i class="glyphicon-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div class="page-list-mainer">
                    <?php if (count($data) > 0): ?>
                        <ul class="da-list" id="da_list">
                            <?php foreach ($data as $diary) { ?>
                                <li class="da-list-item">
                                    <div class="da-summary">
                                        <div class="avatar-box">
                                            <a href="<?php echo $this->createUrl('attention/index', array('op' => 'personal', 'uid' => $diary['uid'])) ?>"
                                               class="avatar-circle">
                                                <img class="mbm" src="<?php echo $diary['user']['avatar_middle']; ?>"
                                                     alt="">
                                            </a>
                                            <span class="avatar-desc"><strong><?php echo $diary['realname']; ?></strong></span>
                                            <?php if ($attentionSwitch): ?>
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
                                                    <a href="<?php echo $this->createUrl('attention/show', array('diaryid' => $diary['diaryid'])) ?>"
                                                       target="_blank" class="o-more cbtn"
                                                       title="<?php echo $lang['More']; ?>"></a>
                                                </div>
                                                <span class="fss"><?php echo $diary['addtime']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="da-detail" data-id="<?php echo $diary['diaryid']; ?>"
                                         style="display:none;"></div>
                                    <div class="da-mark-down-wrap">
                                        <a href="javascript:;" class="da-mark-down" data-action="showDiaryDetail"
                                           data-param='{"id": "<?php echo $diary['diaryid']; ?>", "fromController": "<?php echo $this->id; ?>"}'></a>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                        <div class="page-list-footer">
                            <div class="pull-right">
                                <?php $this->widget('application\core\widgets\Page', array('pages' => $pagination)); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-data-tip"></div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Mainer content -->
        </div>
    </div>
</div>
<!-- Footer -->

<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/diary.js?<?php echo VERHASH; ?>'></script>
<script src="<?php echo STATICURL; ?>/js/lib/lightbox/js/lightbox.js?<?php echo VERHASH; ?>"></script>
<script>
    $(function () {
        // 时间选择
        $('#da_date_btn').datepicker().on("changeDate", function (evt) {
            location.href = Ibos.app.url("diary/attention/index", {date: $.data(this, "date")});
        });
    });
</script>
