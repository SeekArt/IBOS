<?php

use application\core\utils\Env;
use application\core\utils\Org;
use application\modules\main\utils\Main;

?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/diary.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/lightbox/css/lightbox.css?<?php echo VERHASH; ?>"/>
<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar(); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <div class="mc-header">
            <div class="mc-header-info clearfix">
                <div class="mc-overview pull-right">
                    <ul class="mc-overview-list">
                        <li class="po-da-diary">
                            <?php echo $lang['Diary']; ?>
                            <em><?php echo $diaryCount ?></em><?php echo $lang['Article']; ?>
                        </li>
                        <li class="po-da-comment">
                            <?php echo $lang['Review']; ?>
                            <em><?php echo $commentCount; ?></em><?php echo $lang['Article']; ?>
                        </li>
                    </ul>
                </div>
                <div class="usi-terse">
                    <a href="javascript:;" class="avatar-box">
                        <span class="avatar-circle">
                            <img class="mbm" src="<?php echo Org::getDataStatic($user['uid'], 'avatar', 'middle') ?>"
                                 alt="">
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
                    <!-- 添加日志判断 -->
                    <?php if (!$diaryIsAdd): ?><a href="<?php echo $this->createUrl('default/add'); ?>"
                                                  class="btn btn-primary"><?php echo $lang['Written work diary']; ?></a><?php endif; ?>
                    <button class="btn" data-action="removeDiarys"><?php echo $lang['Delete']; ?></button>
                </div>
                <form action="<?php echo $this->createUrl('default/index', array('param' => 'search')); ?>"
                      method="post">
                    <div class="search search-config pull-right span3">
                        <input type="text" name="keyword" placeholder="搜索" id="mn_search" nofocus value="<?php
                        if (Env::getRequest('param') == 'search') {
                            echo Main::getCookie('keyword');
                        };
                        ?>">
                        <a href="javascript:;">search</a>
                        <input type="hidden" name="type" value="normal_search">
                    </div>
                </form>
            </div>
            <div class="band band-primary">
                <?php if ($diaryIsAdd): ?>
                    <?php echo $lang['Today has already submitted the log']; ?>. <a
                        href="<?php echo $this->createUrl('default/show', array('diarydate' => date('Y-m-d'))); ?>"
                        class="anchor">查看工作日志</a>
                <?php else: ?>
                    <?php echo $lang['Today has not submitted the log']; ?>
                <?php endif; ?>
            </div>

            <?php if (count($data) > 0): ?>
                <div class="page-list-mainer">
                    <ul class="da-list" id="da_list">
                        <?php foreach ($data as $key => $diary) { ?>
                            <li class="da-list-item">
                                <!-- 简述 -->
                                <div class="da-summary clearfix">
                                    <a href="<?php echo $this->createUrl('default/show', array('diaryid' => $diary['diaryid'])); ?>"
                                       class="datebox">
                                        <span><?php echo $diary['diarytime']['month']; ?><?php echo $lang['Month']; ?></span>
                                        <span class="datebox-body">
                                            <strong><?php echo $diary['diarytime']['day']; ?></strong>
                                            <span><?php echo $diary['diarytime']['weekday']; ?></span>
                                        </span>
                                    </a>
                                    <div class="das-content">
                                        <p class="xcm mb text-break">
                                            <a href="javascript:;" data-action="showDiaryDetail"
                                               data-param='{"id": "<?php echo $diary['diaryid']; ?>", "fromController": "<?php echo $this->id; ?>", "isShowDiarytime": "1"}'><?php echo $diary['content']; ?></a>
                                        </p>
                                        <div class="da-list-item-desc">
                                            <div class="pull-right">
                                                <a href="<?php echo $this->createUrl('default/show', array('diaryid' => $diary['diaryid'])) ?>"
                                                   target="_blank" class="o-more cbtn"
                                                   title="<?php echo $lang['More']; ?>"></a>
                                                <!-- 日志评阅后是否被锁定 模块配置判断-->
                                                <?php if (($diary['editIsLock'] == 0 && $dashboardConfig['reviewlock'] == 0) || ($diary['editIsLock'] == 0 && $dashboardConfig['reviewlock'] == 1 && $diary['isreview'] == 0)): ?>
                                                    <a href="<?php echo $this->createUrl('default/edit', array('diaryid' => $diary['diaryid'])) ?>"
                                                       class="o-edit cbtn mls" title="<?php echo $lang['Edit']; ?>"></a>
                                                    <a href="javascript:;" data-action="removeDiary"
                                                       data-param='{"id": "<?php echo $diary['diaryid']; ?>"}'
                                                       class="o-trash cbtn mls"
                                                       title="<?php echo $lang['Delete']; ?>"></a>
                                                <?php else: ?>
                                                    <a href="javascript:;" class="bo-locked cbtn mls"
                                                       data-toggle="tooltip" title="<?php echo $lang['Locked']; ?>"></a>
                                                <?php endif; ?>
                                            </div>
                                            <!-- 锁定时不出现复选框-->
                                            <?php if (($diary['editIsLock'] == 0 && $dashboardConfig['reviewlock'] == 0) || ($diary['editIsLock'] == 0 && $dashboardConfig['reviewlock'] == 1 && $diary['isreview'] == 0)): ?>
                                                <label class="checkbox checkbox-inline">
                                                    <input type="checkbox" name="diaryids"
                                                           value="<?php echo $diary['diaryid']; ?>">
                                                </label>
                                            <?php endif; ?>
                                            <span class="fss"><?php echo $diary['addtime']; ?></span>
                                            <span class="fss ilsep">|</span>
                                            <a href="javascript:;" class="fss" data-node-type="loadCommentUser"
                                               data-id="<?php echo $diary['diaryid']; ?>"><?php echo $lang['Review']; ?>
                                                <em><?php echo $diary['commentcount']; ?></em></a>
                                            <span class="fss ilsep">|</span>
                                            <a href="javascript:;" class="fss" data-node-type="loadReader"
                                               data-id="<?php echo $diary['diaryid']; ?>"><?php echo $lang['Read']; ?>
                                                <em><?php echo $diary['readercount']; ?></em></a>
                                            <?php if ($diary['stamp'] > 0): ?>
                                                &nbsp;&nbsp;<img width="60" height="24"
                                                                 src="<?php echo $diary['stampPath']; ?>"/>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- 详情， 通过ajax加载过来 -->
                                <div class="da-detail" style="display:none;"></div>
                                <div class="da-mark-down-wrap">
                                    <a href="javascript:;" class="da-mark-down" data-action="showDiaryDetail"
                                       data-param='{"id": "<?php echo $diary['diaryid']; ?>", "fromController": "<?php echo $this->id; ?>", "isShowDiarytime": "1"}'></a>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="page-list-footer">
                    <div class="pull-right">
                        <?php $this->widget('application\core\widgets\Page', array('pages' => $pagination)); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-data-tip"></div>
            <?php endif; ?>
        </div>
        <!-- Mainer content -->
    </div>
</div>

<!-- Footer -->

<!-- 高级搜索弹出框 -->
<div id="mn_search_advance" style="width: 400px; display:none;">
    <form id="mn_search_advance_form" method="post"
          action="<?php echo $this->createUrl('default/index', array('param' => 'search')); ?>"
          class="form-horizontal form-compact">
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Keyword']; ?>：</label>
            <div class="controls">
                <input type="text" name="search[keyword]">
            </div>
        </div>
        <div class="control-group">
            <label for="" class="control-label"><?php echo $lang['Startime']; ?></label>
            <div class="controls">
                <div class="datepicker" id="date_start">
                    <a href="javascript:;" class="datepicker-btn"></a>
                    <input type="text" class="datepicker-input" name="search[starttime]">
                </div>
            </div>
        </div>
        <div class="control-group">
            <label for="" class="control-label"><?php echo $lang['Endtime']; ?></label>
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
<script src="<?php echo STATICURL; ?>/js/lib/lightbox/js/lightbox.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo $assetUrl; ?>/js/diary.js?<?php echo VERHASH; ?>'></script>
<script>
    $(document).ready(function () {

        //高级搜索
        $("#mn_search").search(null, function () {
            Ui.dialog({
                id: "d_advance_search",
                title: U.lang("ADVANCED_SETTING"),
                content: document.getElementById("mn_search_advance"),
                cancel: true,
                init: function () {
                    var form = this.DOM.content.find("form")[0];
                    form && form.reset();
                    // 初始化日期选择
                    $("#date_start").datepicker({target: $("#date_end")});
                },
                ok: function () {
                    this.DOM.content.find("form").submit();
                },
            })
        })

        $("[data-toggle='tooltip']").tooltip();
    });
</script>
