<?php

use application\core\utils\Env;
use application\core\utils\Org;
use application\modules\main\utils\Main;

?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/report.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/lightbox/css/lightbox.css?<?php echo VERHASH; ?>"/>

<!-- Mainer -->
<div class="wrap">
    <div class="mc clearfix">
        <!-- Sidebar -->
        <?php
        $getUid = Env::getRequest('uid');
        $getUser = Env::getRequest('user');
        ?>
        <?php echo $this->getSidebar($getUid, $getUser); ?>
        <!-- Mainer right -->
        <div class="mcr">
            <div class="page-list">
                <div class="page-list-header">
                    <form
                        action="<?php echo $this->createUrl('review/index', array('param' => 'search', 'user' => $getUser)); ?>"
                        method="post">
                        <div class="search search-config pull-right span3">
                            <input type="text" name="keyword" placeholder="Search" id="mn_search" nofocus value="<?php
                            if (Env::getRequest('param') == 'search') {
                                echo Main::getCookie('keyword');
                            };
                            ?>">
                            <a href="javascript:;">search</a>
                            <input type="hidden" name="type" value="normal_search">
                        </div>
                    </form>
                </div>
                <div class="page-list-mainer">
                    <ul class="rp-list" id="rp_list">
                        <?php if (count($reportList) > 0): ?>
                            <?php foreach ($reportList as $report): ?>
                                <li class="rp-list-item">

                                    <div class="rp-summary">
                                        <div class="avatar-box">
                                            <a href="<?php echo $this->createUrl('review/index', array('op' => 'personal', 'uid' => $report['uid'])) ?>"
                                               class="avatar-circle">
                                                <img class="mbm"
                                                     src="<?php echo Org::getDataStatic($report['uid'], 'avatar', 'middle') ?>"
                                                     alt="">
                                            </a>
                                            <span
                                                class="avatar-desc"><strong><?php echo $report['user']['realname']; ?></strong></span>
                                        </div>
                                        <div class="rps-content">
                                            <div class="clearfix ovh">
                                                <a href="javascript:;" data-action="showReportDetail"
                                                   data-param='{"id": "<?php echo $report['repid']; ?>", "fromController": "<?php echo $this->id; ?>"}'>
                                                    <h4 class="pull-left">
                                                        <?php echo $report['user']['realname']; ?>
                                                        &nbsp;<?php echo $report['cutSubject']; ?>
                                                    </h4>
                                                    <span
                                                        class="pull-left ml"><?php echo $report['user']['deptname']; ?></span>
                                                </a>
                                            </div>
                                            <p class="xcm mb text-break">
                                                <?php echo $report['content']; ?>
                                            </p>
                                            <div class="rp-list-item-desc">
                                                <div class="pull-right">
                                                    <a href="<?php echo $this->createUrl('review/show', array('repid' => $report['repid'])); ?>"
                                                       target="_blank" class="o-more cbtn"
                                                       title="<?php echo $lang['More']; ?>"></a>
                                                </div>
                                                <span class="fss"><?php echo $report['addtime']; ?></span>
                                                <span class="fss ilsep">|</span>
                                                <a href="javascript:;" class="fss" data-node-type="loadCommentUser"
                                                   data-id="<?php echo $report['repid']; ?>"><?php echo $lang['Comment']; ?>
                                                    <em><?php echo $report['commentcount']; ?></em></a>
                                                <span class="fss ilsep">|</span>
                                                <a href="javascript:;" class="fss" data-node-type="loadReader"
                                                   data-id="<?php echo $report['repid']; ?>"><?php echo $lang['Reading']; ?>
                                                    <em><?php echo $report['readercount']; ?></em></a>
                                                <?php if ($report['stamp'] > 0): ?>
                                                    &nbsp;&nbsp;<img width="60" height="24"
                                                                     id="report_stamp_<?php echo $report['repid']; ?>"
                                                                     src="<?php echo $report['stampPath']; ?>"/>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 此处展开详细页 -->
                                    <div class="rp-detail" style="display:none;"></div>

                                    <div class="rp-mark-down-wrap">
                                        <a href="javascript:;" class="rp-mark-down" data-action="showReportDetail"
                                           data-param='{"id": "<?php echo $report['repid']; ?>", "fromController": "<?php echo $this->id; ?>"}'></a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data-tip"></div>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="page-list-footer">
                    <?php $this->widget('application\core\widgets\Page', array('pages' => $pagination)); ?>
                </div>
            </div>
            <!-- Mainer content -->
        </div>
    </div>
</div>

<!-- 高级搜索弹出框 -->
<div id="mn_search_advance" style="width: 400px; display:none;">
    <form id="mn_search_advance_form" method="post"
          action="<?php echo $this->createUrl('review/index', array('param' => 'search', 'user' => $getUser)); ?>"
          class="form-horizontal form-compact">
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Keyword'] ?>：</label>
            <div class="controls">
                <input type="text" name="search[keyword]">
            </div>
        </div>
        <div class="control-group">
            <label for="" class="control-label"><?php echo $lang['Start time'] ?></label>
            <div class="controls">
                <div class="datepicker" id="date_start">
                    <a href="javascript:;" class="datepicker-btn"></a>
                    <input type="text" class="datepicker-input" name="search[starttime]">
                </div>
            </div>
        </div>
        <div class="control-group">
            <label for="" class="control-label"><?php echo $lang['End time'] ?></label>
            <div class="controls">
                <div class="datepicker" id="date_end">
                    <a href="javascript:;" class="datepicker-btn"></a>
                    <input type="text" class="datepicker-input" name="search[endtime]">
                </div>
            </div>
        </div>
        <input type="hidden" name="type" value="advanced_search">
    </form>
</div>

<script>
    Ibos.app.setPageParam({
        currentSubUid: "<?php echo(Env::getRequest('uid') ? Env::getRequest('uid') : 0); ?>",
        stampEnable: '<?php echo $dashboardConfig["stampenable"] ?>',
        stamps: <?php echo $this->getStamp(); ?>,
        stampPath: '',
        autoReview: '<?php echo $dashboardConfig["autoreview"] ?>'
    })
</script>

<script src="<?php echo STATICURL; ?>/js/lib/lightbox/js/lightbox.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo $assetUrl; ?>/js/report.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/report_review.js?<?php echo VERHASH; ?>'></script>
<script>
    $(function () {
        //高级搜索
        $("#mn_search").search(null, function () {
            Ibos.openAdvancedSearchDialog({
                content: document.getElementById("mn_search_advance"),
                init: function () {
                    // 初始化日期选择
                    $("#date_start").datepicker({target: $("#date_end")});
                },
                ok: function () {
                    this.DOM.content.find("form").submit();
                }
            });
        });
    });
</script>
