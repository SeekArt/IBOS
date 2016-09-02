<?php

use application\core\utils\Env;
use application\core\utils\Org;
use application\modules\main\utils\Main;
?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/report.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="wrap">
    <div class="mc clearfix">
        <!-- Sidebar -->
        <?php $this->getSidebar(); ?>
        <!-- Mainer right -->
        <div class="mcr">
            <div class="mc-header">
                <div class="mc-header-info clearfix">
                    <div class="mc-overview pull-right">
                        <ul class="mc-overview-list">
                            <li class="po-rp-diary">
                                <?php echo $lang['Summary and plan']; ?><em><?php echo $reportCount ?></em><?php echo $lang['Piece']; ?>
                            </li>
                            <li class="po-rp-comment">
                                <?php echo $lang['Comment']; ?><em><?php echo $commentCount ?></em><?php echo $lang['Piece']; ?>
                            </li>
                        </ul>
                    </div>
                    <div class="usi-terse">
                        <a href="" class="avatar-box">
                            <span class="avatar-circle">
                                <img class="mbm" src="<?php echo Org::getDataStatic( $user['uid'], 'avatar', 'middle' ) ?>" alt="">
                            </span>
                        </a>
                        <span class="usi-terse-user"><?php echo $user['realname']; ?></span>
                        <span class="usi-terse-group"><?php echo $user['deptname']; ?></span>
                    </div>
                </div>
            </div>
            <div class="page-list">
                <div class="page-list-header">
                    <div class="btn-toolbar pull-left">
                        <a href="<?php echo $this->createUrl( 'default/add', array( 'typeid' => $typeid ) ); ?>" class="btn btn-primary"><?php echo $lang['Write report']; ?></a>
                        <button class="btn" data-action="removeReportsFromList"><?php echo $lang['Delete']; ?></button>
                    </div>
                    <form action="<?php echo $this->createUrl( 'default/index', array( 'param' => 'search' ) ); ?>" method="post">
                        <div class="search search-config pull-right span3">
                            <input type="text" name="keyword" placeholder="Search" id="mn_search" nofocus value="<?php
                            if ( Env::getRequest( 'param' ) == 'search' ) {
                                echo Main::getCookie( 'keyword' );
                            };
                            ?>">
                            <a href="javascript:;">search</a>
                            <input type="hidden" name="type" value="normal_search">
                        </div>
                    </form>
                </div>
                <?php if ( count( $reportList ) > 0 ): ?>
                    <div class="page-list-mainer">
                        <ul class="rp-list" id="rp_list">
                            <?php foreach ( $reportList as $k => $report ): ?>
                                <li class="rp-list-item">
                                    <div class="rp-summary">
                                        <a href="<?php echo $this->createUrl( 'default/show', array( 'repid' => $report['repid'] ) ); ?>" class="rp-weekly">
                                            <span class="rp-weekly-start">
                                                <em><?php echo date( 'd', $report['enddate'] ); ?></em>
                                                <?php echo date( 'm', $report['enddate'] ); ?><?php echo $lang['Month']; ?>
                                            </span>
                                            <span class="rp-weekly-end">
                                                <em><?php echo date( 'd', $report['begindate'] ); ?></em>
                                                <?php echo date( 'm', $report['begindate'] ); ?><?php echo $lang['Month']; ?>
                                            </span>
                                        </a>
                                        <div class="rps-content">
                                            <h4><a href="javascript:;" data-action="showReportDetail" data-param='{"id": "<?php echo $report['repid']; ?>", "fromController": "<?php echo $this->id; ?>"}' title="<?php echo $report['subject']; ?>"><?php echo $report['cutSubject']; ?></a></h4>
                                            <p class="xcm mb text-break">
                                                <?php echo $report['content']; ?>
                                            </p>
                                            <div class="rp-list-item-desc clearfix">
                                                <div class="pull-right">
                                                    <a href="<?php echo $this->createUrl( 'default/show', array( 'repid' => $report['repid'] ) ) ?>" target="_blank" class="o-more cbtn" title="<?php echo $lang['More']; ?>"></a>
                                                    <a href="<?php echo $this->createUrl( 'default/edit', array( 'repid' => $report['repid'] ) ) ?>" class="o-edit cbtn mls" title="<?php echo $lang['Edit']; ?>"></a>
                                                    <a href="javascript:;" data-action="removeReportFromList" data-param='{"id": "<?php echo $report['repid']; ?>"}' class="o-trash cbtn mls" title="<?php echo $lang['Delete']; ?>"></a>
                                                </div>
                                                <label class="checkbox checkbox-inline">
                                                    <input type="checkbox" name="report[]" value="<?php echo $report['repid']; ?>">
                                                </label>
                                                <span class="fss"><?php echo $report['addtime']; ?></span>
                                                <span class="fss ilsep">|</span>
                                                <a href="javascript:;" class="fss" data-node-type="loadCommentUser" data-id="<?php echo $report['repid']; ?>"><?php echo $lang['Comment']; ?> <em><?php echo $report['commentcount']; ?></em></a>
                                                <span class="fss ilsep">|</span>
                                                <a href="javascript:;" class="fss" data-node-type="loadReader" data-id="<?php echo $report['repid']; ?>"><?php echo $lang['Reading']; ?>  <em><?php echo $report['readercount']; ?></em></a>
                                                <?php if ( $report['stamp'] > 0 ): ?>
                                                    &nbsp;&nbsp;<img width="60" height="24" src="<?php echo $report['stampPath']; ?>" />
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 此处展开详细页 -->
                                    <div class="rp-detail" style="display:none;"></div>
                                    <div class="rp-mark-down-wrap">
                                        <a href="javascript:;" class="rp-mark-down" data-action="showReportDetail" data-param='{"id": "<?php echo $report['repid']; ?>", "fromController": "<?php echo $this->id; ?>"}'></a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data-tip"></div>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="page-list-footer">
                    <?php $this->widget( 'application\core\widgets\Page', array( 'pages' => $pagination ) ); ?>
                </div>
            </div>
            <!-- Mainer content -->
        </div>
    </div>
</div>
<!-- 高级搜索弹出框 -->
<div id="mn_search_advance" style="width: 400px; display:none;">
    <form  id="mn_search_advance_form" method="post" action="<?php echo $this->createUrl( 'default/index', array( 'param' => 'search' ) ); ?>" class="form-horizontal form-compact">
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Keyword']; ?>：</label>
            <div class="controls">
                <input type="text" name="search[keyword]">
            </div>
        </div>
        <div class="control-group">
            <label for="" class="control-label"><?php echo $lang['Start time']; ?></label>
            <div class="controls">
                <div class="datepicker" id="date_start">
                    <a href="javascript:;" class="datepicker-btn"></a>
                    <input type="text" class="datepicker-input" name="search[starttime]">
                </div>
            </div>
        </div>
        <div class="control-group">
            <label for="" class="control-label"><?php echo $lang['End time']; ?></label>
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

<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/report.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/report_default_index.js?<?php echo VERHASH; ?>'></script>
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
