<?php

use application\core\utils\Env;
use application\modules\main\utils\Main;

?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/report.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/lightbox/css/lightbox.css?<?php echo VERHASH; ?>"/>

<!-- Mainer -->
<div class="wrap">
    <div class="mc clearfix">
        <!-- Sidebar -->
        <?php
        $getUid = Env::getRequest('uid');
        $getUser = Env::getRequest('user');
        ?>
        <?php $this->getSidebar($getUid, $getUser); ?>
        <!-- Mainer right -->
        <div class="mcr">
            <div class="mc-header">
                <div class="mc-header-info clearfix">
                    <div class="mc-overview pull-right">
                        <ul class="mc-overview-list">
                            <li class="po-rp-diary">
                                <?php echo $lang['Summary and plan']; ?>
                                <em><?php echo $reportCount ?></em><?php echo $lang['Piece'] ?>
                            </li>
                            <li class="po-rp-comment">
                                <?php echo $lang['Comment'] ?>
                                <em><?php echo $commentCount ?></em><?php echo $lang['Piece'] ?>
                            </li>
                        </ul>
                    </div>
                    <div class="usi-terse">
                        <a href="javascript:;" class="avatar-box">
							<span class="avatar-circle">
								<img class="mbm" src="<?php echo $user['avatar_middle']; ?>" alt="">
							</span>
                        </a>
                        <span class="usi-terse-user"><?php echo $user['realname']; ?></span>
                        <span class="usi-terse-group"><?php echo $user['deptname']; ?></span>
                    </div>
                </div>
            </div>
            <div class="page-list">
                <div class="page-list-header">
                    <form
                        action="<?php echo $this->createUrl('review/index', array('op' => 'personal', 'param' => 'search', 'uid' => $getUid)); ?>"
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
                <?php if (count($reportList) > 0): ?>
                <div class="page-list-mainer">
                    <ul class="rp-list" id="rp_list">
                        <?php foreach ($reportList as $k => $report): ?>
                            <li class="rp-list-item">
                                <div class="rp-summary">
                                    <a href="<?php echo $this->createUrl('review/show', array('repid' => $report['repid'])); ?>"
                                       class="rp-weekly">
											<span class="rp-weekly-start">
												<em><?php echo date('d', $report['enddate']); ?></em>
                                                <?php echo date('m', $report['enddate']); ?><?php echo $lang['Month'] ?>
											</span>
											<span class="rp-weekly-end">
												<em><?php echo date('d', $report['begindate']); ?></em>
                                                <?php echo date('m', $report['begindate']); ?><?php echo $lang['Month'] ?>
											</span>
                                    </a>
                                    <div class="rps-content">
                                        <h4><a href="javascript:;" data-action="showReportDetail"
                                               data-param='{"id": "<?php echo $report['repid']; ?>", "fromController": "<?php echo $this->id; ?>"}'
                                               title="<?php echo $report['subject']; ?>"><?php echo $report['cutSubject']; ?></a>
                                        </h4>
                                        <p class="xcm mb">
                                            <?php echo $report['content']; ?>
                                        </p>
                                        <div class="rp-list-item-desc">
                                            <div class="pull-right">
                                                <a href="<?php echo $this->createUrl('review/show', array('repid' => $report['repid'])) ?>"
                                                   target="_blank" class="o-more cbtn"
                                                   title="<?php echo $lang['More']; ?>"></a>
                                            </div>
                                            <span class="fss"><?php echo $report['addtime']; ?></span>
                                            <span class="fss ilsep">|</span>
                                            <a href="javascript:;" class="j-comment fss"
                                               data-node-type="loadCommentUser"
                                               data-id="<?php echo $report['repid']; ?>">点评
                                                <em><?php echo $report['commentcount']; ?></em></a>
                                            <span class="fss ilsep">|</span>
                                            <a href="javascript:;" class="j-reader fss" data-node-type="loadReader"
                                               data-id="<?php echo $report['repid']; ?>">阅读
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
          action="<?php echo $this->createUrl('review/index', array('op' => 'personal', 'param' => 'search', 'uid' => $getUid)); ?>"
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
        supUid: <?php
        if (isset($supUid)) {
            echo $supUid;
        } else {
            echo 0;
        }
        ?>,
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
    $(document).ready(function () {
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

        // 若浏览的是下属的下属，需要展开
        var supUid = Ibos.app.g('supUid');
        if (supUid !== 0) {
            var $sub = $('.g-sub[data-uid=' + supUid + ']');
            $sub.click();
            $sub.parent().addClass('active');
        }
    });
</script>
