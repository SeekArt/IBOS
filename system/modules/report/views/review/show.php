<?php

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Org;
use application\core\utils\StringUtil;

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
        <?php echo $this->getSidebar($getUid, $getUser); ?>
        <!-- Mainer right -->
        <div class="mcr">
            <div class="mc-header">
                <div class="mc-header-info clearfix">
                    <div class="mc-overview pull-right">
                        <ul class="mc-overview-list">
                            <li class="po-rp-clock">
                                <?php echo $lang['Submit time'] ?>：<?php echo date('Y-m-d H:i', $report['addtime']); ?>
                            </li>
                        </ul>
                    </div>
                    <div class="usi-terse">
                        <a href="" class="avatar-box">
                            <span class="avatar-circle">
                                <img class="mbm"
                                     src="<?php echo Org::getDataStatic($report['uid'], 'avatar', 'middle') ?>" alt="">
                            </span>
                        </a>
                        <span class="usi-terse-user"><?php echo $realname; ?></span>
                        <span class="usi-terse-group"><?php echo $departmentName; ?></span>
                    </div>
                </div>
            </div>
            <div class="page-list">
                <div class="page-list-header">
                    <div class="btn-group pull-right">
                        <a <?php if (!empty($preAndNextRep['preRep'])): ?>
                            href="<?php echo $this->createUrl('review/show', array('repid' => $preAndNextRep['preRep']['repid'])); ?>" class="btn" title="<?php echo $preAndNextRep['preRep']['subject']; ?>"
                        <?php else: ?>
                            href="javascript:;" class="btn disabled"
                        <?php endif; ?>>
                            <i class="glyphicon-chevron-left"></i>
                        </a>
                        <a <?php if (!empty($preAndNextRep['nextRep'])): ?>
                            href="<?php echo $this->createUrl('review/show', array('repid' => $preAndNextRep['nextRep']['repid'])); ?>" class="btn" title="<?php echo $preAndNextRep['nextRep']['subject']; ?>"
                        <?php else: ?>
                            href="javascript:;" class="btn disabled"
                        <?php endif; ?>>
                            <i class="glyphicon-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div class="page-list-mainer posr">
                    <table class="rp-detail-table" id="rp_detail_table">
                        <tbody>
                        <div class="rp-stamp">
                            <span id="preview_stamp">
								<?php if ($report['stamp'] > 0): ?><img id="stamp_<?php echo $report['repid']; ?>"
                                                                        src="<?php echo $stampUrl; ?>" width="150px"
                                                                        height="90px" /><?php endif; ?>
                            </span>
                        </div>
                        <tr>
                            <td colspan="3">
                                <div class="mini-date fill-ss">
                                    <h4><?php echo $report['subject']; ?></h4>
                                </div>
                            </td>
                        </tr>

                        <!-- 原计划 -->
                        <?php if (!empty($orgPlanList)): ?>
                            <?php foreach ($orgPlanList as $k1 => $orgPlan): ?>
                                <tr>
                                    <?php if ($k1 == 0): ?>
                                        <th rowspan="<?php echo count($orgPlanList); ?>" width="68"
                                            class="sep"><?php echo $lang['Original plan']; ?></th>
                                    <?php endif; ?>
                                    <td width="3" class="sep"></td>
                                    <td>
                                        <div class="fill">
                                            <div class="bamboo-pgb pull-right">
                                                <span class="pull-left xcn fss"><?php echo $orgPlan['process'] * 10; ?>
                                                    %</span>
                                                <span data-toggle="bamboo-pgb"></span>
                                                <input type="hidden" value="<?php echo $orgPlan['process']; ?>">
                                            </div>
                                            <span class="rp-detail-num"><?php echo $k1 + 1; ?>
                                                .</span> <?php echo $orgPlan['content']; ?>
                                            <div class="rp-exec-status">
                                                <?php echo $lang['Implementation']; ?>
                                                ：<?php echo $orgPlan['exedetail']; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <!-- 计划外 -->
                        <?php if (!empty($outSidePlanList)): ?>
                            <?php foreach ($outSidePlanList as $k2 => $outSidePlan): ?>
                                <tr>
                                    <?php if ($k2 == 0): ?>
                                        <th rowspan="<?php echo count($outSidePlanList); ?>" class="sep"
                                            width="68"><?php echo $lang['Outside plan']; ?></th>
                                    <?php endif; ?>
                                    <td class="sep"></td>
                                    <td>
                                        <div class="fill">
                                            <div class="bamboo-pgb pull-right">
                                                <span
                                                    class="pull-left xcn fss"><?php echo $outSidePlan['process'] * 10; ?>
                                                    %</span>
                                                <span data-toggle="bamboo-pgb"></span>
                                                <input type="hidden" name=""
                                                       value="<?php echo $outSidePlan['process']; ?>">
                                            </div>
                                            <span class="rp-detail-num"><?php echo count($orgPlanList) + $k2 + 1; ?>
                                                .</span> <?php echo $outSidePlan['content'] ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <!-- 工作总结 -->
                        <tr>
                            <th class="sep" width="68"><?php echo $lang['Work']; ?><br/><?php echo $lang['Summary']; ?>
                            </th>
                            <td class="sep" width="3"></td>
                            <td>
                                <div class="fill editor-content text-break">
                                    <p class="summary">
                                        <?php echo $report['content']; ?>
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <!-- 附件 -->
                        <?php if (!empty($attachs)): ?>
                            <?php foreach ($attachs as $k3 => $attach): ?>
                                <tr>
                                    <?php if ($k3 == 0): ?>
                                        <th class="sep" width="68"
                                            rowspan="<?php echo count($attachs); ?>"><?php echo $lang['Attachement']; ?>
                                            <br/>(<?php echo count($attachs); ?>个)
                                        </th>
                                    <?php endif; ?>
                                    <td class="sep" width="3"></td>
                                    <td>
                                        <div class="cti">
                                            <i class="atti">
                                                <img src="<?php echo $attach['iconsmall']; ?>"
                                                     alt="<?php echo $lang['Attachement']; ?>">
                                            </i>
                                            <div class="attc">
                                                <div>
                                                    <?php echo $attach['filename']; ?><span
                                                        class="tcm">(<?php echo $attach['filesize']; ?>)</span>
                                                </div>
												<span class="fss">
													<a href="<?php echo $attach['downurl']; ?>"
                                                       target="_blank"><?php echo $lang['Download']; ?></a>&nbsp;&nbsp;
                                                    <?php if (isset($attach['officereadurl'])): ?>
                                                        <a href="javascript:;" data-action="viewOfficeFile"
                                                           data-param='{"href": "<?php echo $attach['officereadurl']; ?>"}'
                                                           title="<?php echo $lang['View']; ?>">
                                                            <?php echo $lang['View']; ?>
                                                        </a>
                                                    <?php endif; ?>
                                                    <!-- 转存到文件柜，等实现文件柜功能再开启 -->
                                                    <!--<a href="#">转存到文件柜</a>-->
												</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($nextPlanList)): ?>
                            <tr>
                                <td colspan="3">
                                    <div class="mini-date fill-ss">
                                        <h4><?php echo $nextSubject; ?></h4>
                                    </div>
                                </td>
                            </tr>
                            <!-- 计划 -->
                            <?php foreach ($nextPlanList as $k4 => $nextPlan): ?>
                                <tr>
                                    <?php if ($k4 == 0): ?>
                                        <th rowspan="<?php echo count($nextPlanList); ?>" class="sep"
                                            width="68"><?php echo $lang['Work']; ?><br/><?php echo $lang['Plan']; ?>
                                        </th>
                                    <?php endif; ?>
                                    <td class="sep" width="3"></td>
                                    <td>
                                        <div class="fill">
                                            <span class="rp-detail-num"><?php echo $k4 + 1; ?>
                                                .</span> <?php echo $nextPlan['content']; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <!--点评-->
                    <div class="cti bdbs">
                        <h4><?php echo $lang['Comment']; ?></h4>
                        <div id="report_comment"
                             data-url="<?php echo Yii::app()->urlManager->createUrl('message/comment/getcomment'); ?>">
                            <?php
                            $sourceUrl = Ibos::app()->urlManager->createUrl('report/default/show', array('repid' => $report['repid']));
                            $this->widget('application\modules\report\widgets\ReportComment', array(
                                'module' => 'report',
                                'table' => 'report',
                                'attributes' => array(
                                    'rowid' => $report['repid'],
                                    'moduleuid' => Ibos::app()->user->uid,
                                    'touid' => $report['uid'],
                                    'module_rowid' => $report['repid'],
                                    'module_table' => 'report',
                                    'api' => 'reviewSubordinate',
                                    'allowComment' => 1,
                                    'showStamp' => $this->issetStamp(),
                                    'url' => $sourceUrl,
                                    'detail' => Ibos::lang('Comment my report', '', array('{url}' => $sourceUrl, '{title}' => StringUtil::cutStr($report['subject'], 50)))
                                )));
                            ?>
                        </div>
                    </div>
                    <!--阅读人员-->
                    <?php if (!empty($readers)): ?>
                        <div class="cti">
                            <h4><?php echo $lang['Reading']; ?><?php echo $lang['Staff']; ?></h4>
                            <div>
                                <div class="rp-reviews-count">
                                    <?php echo $lang['View']; ?>
                                    <strong><?php echo count($readers); ?></strong>
                                    <?php echo $lang['People']; ?>
                                </div>
                                <div class="rp-reviews-avatar">
                                    <?php foreach ($readers as $reader): ?>
                                        <a href="<?php echo Ibos::app()->createUrl('user/home/index', array('uid' => $reader['uid'])); ?>">
                                            <img
                                                src="<?php echo Org::getDataStatic($reader['uid'], 'avatar', 'small') ?>"
                                                title="<?php echo $reader['realname']; ?>" class="img-rounded"/>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="hidden" id="relatedid" name="relatedid" value="<?php echo $report['repid']; ?>">
                    <input type="hidden" id="relatedmodule" name="relatedmodule" value="<?php echo 'report'; ?>">
                </div>
            </div>
            <!-- Mainer content -->
        </div>
    </div>
</div>
<script>
    Ibos.app.setPageParam({
        currentSubUid: "<?php echo(Env::getRequest('uid') ? Env::getRequest('uid') : 0); ?>",
        reportId: <?php echo $report['repid']; ?>,
        stamps: <?php echo $this->getStamp(); ?>,
        stampPath: '',
    })
</script>
<script src="<?php echo STATICURL; ?>/js/lib/lightbox/js/lightbox.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo $assetUrl; ?>/js/report.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/report_review.js?<?php echo VERHASH; ?>'></script>
<script>
    $(function () {
        // 等评论模块读取完成后再初始化
        $(document).on("reportcommentload", function () {
            // 评论
            var $comment = $('#report_comment');
            // 图章
            var $stampBtn = $comment.find("[data-toggle='stampPicker']");

            Ibosapp.stampPicker($stampBtn, Ibos.app.g('stamps'), 1);

            $stampBtn.on("stampChange", function (evt, data) {
                var $commentBtn = $comment.find("[data-act='addcomment']");

                var stamp = '<img src="' + Ibos.app.g('stampPath') + data.stamp + '" width="150" height="90" />',
                    smallStamp = '<img src="' + data.path + '" width="60px" height="24px" />',
                    $parentRow = $stampBtn.parents("div").eq(0);

                $("#preview_stamp").html(stamp);

                $parentRow.find(".preview_stamp_small").html(smallStamp);

                $.extend($commentBtn.data("param"), {"stamp": data.value});
            });
        });

        // 进度条初始化;
        $("[data-toggle='bamboo-pgb']").each(function () {
            var $elem = $(this);
            $elem.studyplay_star({
                Enabled: false,
                CurrentStar: +$elem.next().val()
            });
        });

        //给日志内容图片<img>创建一个父级<a>以创建预览大图
        $(".rp-detail-table img").each(function (index, elem) {
            $(elem).wrap("<a data-lightbox='report' href='" + elem.src + "' title='" + (elem.title || elem.alt) + "'></a>");
        });
    });

</script>
