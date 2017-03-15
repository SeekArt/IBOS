<?php

use application\core\utils\Ibos;
use application\core\utils\Org;
use application\core\utils\StringUtil;

?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/diary.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
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
                        <li class="po-da-clock">
                            <?php echo $lang['Submit time']; ?>：<?php echo $diary['addtime']; ?>
                        </li>
                    </ul>
                </div>
                <div class="usi-terse">
                    <a href="" class="avatar-box">
                        <span class="avatar-circle">
                            <img class="mbm" src="<?php echo Org::getDataStatic($diary['uid'], 'avatar', 'middle') ?>"
                                 alt="">
                        </span>
                    </a>
                    <span class="usi-terse-user"><?php echo $diary['realname']; ?></span>
                    <span class="usi-terse-group"><?php echo $diary['departmentName']; ?></span>
                </div>
            </div>
        </div>
        <div class="page-list">
            <div class="page-list-header">
                <div class="mini-date pull-left">
                    <strong><?php echo $diary['diarytime']['day']; ?></strong>
                    <div class="mini-date-body">
                        <p><?php echo $diary['diarytime']['weekday']; ?></p>
                        <p><?php echo $diary['diarytime']['year']; ?>-<?php echo $diary['diarytime']['month']; ?></p>
                    </div>
                </div>
                <?php if ($this->id == 'default' && $diary['editIsLock'] == 0): ?>
                    <div class="btn-toolbar pull-left ml">
                        <a href="<?php echo $this->createUrl('default/edit', array('diaryid' => $diary['diaryid'])); ?>"
                           class="btn"><?php echo $lang['Edit']; ?></a>
                        <a href="javascript:" data-param='{"id": "<?php echo $diary['diaryid']; ?>"}' class="btn"
                           data-action="removeDiary"><?php echo $lang['Delete']; ?></a>
                    </div>
                <?php endif; ?>
                <div class="btn-group pull-right">
                    <a <?php if (!empty($prevAndNextPK['prevPK'])): ?>
                        href="<?php echo $this->createUrl($this->id . '/show', array('diaryid' => $prevAndNextPK['prevPK'])); ?>" class="btn"
                    <?php else: ?>
                        href="javascript:;" class="btn disabled"
                    <?php endif; ?>>
                        <i class="glyphicon-chevron-left"></i>
                    </a>
                    <a <?php if (!empty($prevAndNextPK['nextPK'])): ?>
                        href="<?php echo $this->createUrl($this->id . '/show', array('diaryid' => $prevAndNextPK['nextPK'])); ?>" class="btn"
                    <?php else: ?>
                        href="javascript:;" class="btn disabled"
                    <?php endif; ?>>
                        <i class="glyphicon-chevron-right"></i>
                    </a>
                </div>
            </div>
            <div class="page-list-mainer posr">

                <table class="da-detail-table">
                    <tbody>
                    <div class="da-stamp">
                        <?php if ($diary['stamp'] > 0): ?><img id="stamp_<?php echo $diary['diaryid']; ?>"
                                                               src="<?php echo $stampUrl; ?>" width="150px"
                                                               height="90px" /><?php endif; ?>
                    </div>
                    <!-- 原计划 -->
                    <?php if (count($data['originalPlanList']) > 0) { ?>
                        <?php foreach ($data['originalPlanList'] as $key => $originalPlan) { ?>
                            <tr class="da-detail-row">
                                <?php if ($key == 0) { ?>
                                    <th rowspan="<?php echo count($data['originalPlanList']); ?>" width="68"
                                        class="sep"><?php echo $lang['Original plan']; ?></th>
                                <?php } ?>
                                <td class="sep" width="3"></td>
                                <td>
                                    <div class="fill">
                                        <div class="bamboo-pgb pull-right">
                                            <span
                                                class="pull-left fss xcn"><?php echo ($originalPlan['schedule'] * 10) . "%" ?></span>
                                            <span data-toggle="bamboo-pgb"
                                                  data-value="<?php echo $originalPlan['schedule']; ?>"></span>
                                        </div>
                                        <span class="da-detail-num"><?php echo $key + 1; ?>
                                            .</span> <?php echo $originalPlan['content']; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>

                    <?php if (count($data['outsidePlanList']) > 0): ?>
                        <!-- 计划外 -->
                        <?php foreach ($data['outsidePlanList'] as $key2 => $outsidePlan): ?>
                            <tr class="da-detail-row">
                                <?php if ($key2 == 0): ?>
                                    <th rowspan="<?php echo count($data['outsidePlanList']); ?>" width="68"
                                        class="sep"><?php echo $lang['Unplanned']; ?></th>
                                <?php endif; ?>
                                <td class="sep" width="3"></td>
                                <td>
                                    <div class="fill">
                                        <div class="bamboo-pgb pull-right">
                                            <span
                                                class="pull-left fss xcn"><?php echo ($outsidePlan['schedule'] * 10) . "%" ?></span>
                                            <span data-toggle="bamboo-pgb"
                                                  data-value="<?php echo $outsidePlan['schedule']; ?>"></span>
                                        </div>
                                        <span
                                            class="da-detail-num"><?php echo count($data['originalPlanList']) + $key2 + 1; ?>
                                            .</span> <?php echo $outsidePlan['content']; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- 工作总结 -->
                    <tr>
                        <th class="sep" width="68"><?php echo $lang['Work']; ?><br/><?php echo $lang['Summary']; ?></th>
                        <td class="sep" width="3"></td>
                        <td>
                            <div class="fill editor-content text-break" style="width: 660px; min-height: 180px">
                                <p class="summary"><?php echo $diary['content']; ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <!-- 附件 -->
                    <?php if (isset($attach)): ?>
                        <tr>
                            <th class="sep" width="68"><?php echo $lang['Attachment']; ?>
                                <br/>(<?php echo count($attach); ?>个)
                            </th>
                            <td class="sep" width="3"></td>
                            <td>
                                <?php foreach ($attach as $key => $value): ?>
                                    <div class="cti">
                                        <i class="atti">
                                            <img src="<?php echo $value['iconsmall']; ?>"
                                                 alt="You are always gonna be my love">
                                        </i>
                                        <div class="attc">
                                            <div>
                                                <?php echo $value['filename']; ?><span
                                                    class="tcm">(<?php echo $value['filesize']; ?>)</span>
                                            </div>
											<span class="fss">
												<a href="<?php echo $value['downurl']; ?>"
                                                   target="_blank"><?php echo $lang['Download']; ?></a>&nbsp;&nbsp;
                                                <?php if (isset($value['officereadurl'])): ?>
                                                    <a href="javascript:;" data-action="viewOfficeFile"
                                                       data-param='{"href": "<?php echo $value['officereadurl']; ?>"}'
                                                       title="<?php echo $lang['Read']; ?>">
                                                        <?php echo $lang['Read']; ?>
                                                    </a>
                                                <?php endif; ?>
                                                <!-- 转存到文件柜，等实现文件柜功能再开启 -->
                                                <!--<a href="#">转存到文件柜</a>-->
											</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <!-- 计划的日期 -->
                <div>
                    <div class="mini-date fill-ss">
                        <strong><?php echo $diary['nextDiarytime']['day']; ?></strong>
                        <div class="mini-date-body">
                            <p><?php echo $diary['nextDiarytime']['weekday']; ?></p>
                            <p><?php echo $diary['nextDiarytime']['year']; ?>
                                -<?php echo $diary['nextDiarytime']['month']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="posr">
                    <table class="da-detail-table">
                        <tbody>
                        <!-- 计划 -->
                        <?php foreach ($data['tomorrowPlanList'] as $key3 => $tomorrowPlan): ?>
                            <tr class="da-detail-row">
                                <?php if ($key3 == 0): ?>
                                    <th class="sep" width="68"
                                        rowspan="<?php echo count($data['tomorrowPlanList']); ?>"><?php echo $lang['Work']; ?>
                                        <br/><?php echo $lang['Plan']; ?></th>
                                <?php endif; ?>
                                <td class="sep" width="3"></td>
                                <td>
                                    <div class="fill posr">
                                        <span class="da-detail-num"><?php echo $key3 + 1; ?>
                                            .</span> <?php echo $tomorrowPlan['content']; ?>
                                        <?php if (!empty($tomorrowPlan['timeremind']) && $this->id == 'default'): ?>
                                            <div class="da-remind-bar pull-right">
                                                <i class="o-clock"></i> <?php echo $tomorrowPlan['timeremind'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="cti bdbs">
                    <h4><?php echo $lang['Review']; ?></h4>
                    <div id="comment" class="comment">
                        <?php
                        $sourceUrl = Ibos::app()->urlManager->createUrl('diary/default/show', array('diaryid' => $diary['diaryid']));
                        $this->widget('application\modules\diary\widgets\DiaryComment', array(
                            'module' => 'diary',
                            'table' => 'diary',
                            'attributes' => array(
                                'rowid' => $diary['diaryid'],
                                'moduleuid' => Ibos::app()->user->uid,
                                'touid' => $diary['uid'],
                                'module_rowid' => $diary['diaryid'],
                                'module_table' => 'diary',
                                'allowComment' => $allowComment,
                                'showStamp' => 0,
                                'url' => $sourceUrl,
                                'detail' => Ibos::lang('Comment my diray', '', array('{url}' => $sourceUrl, '{title}' => StringUtil::cutStr(str_replace(PHP_EOL, '', strip_tags($diary['content'])), 50)))
                            )));
                        ?>
                    </div>
                </div>
                <?php if (!empty($readers)): ?>
                    <div class="cti">
                        <h4><?php echo $lang['Read'] . $lang['Personnel']; ?></h4>
                        <div>
                            <div class="da-reviews-count">
                                <?php echo $lang['View']; ?>
                                <strong><?php echo count($readers); ?></strong>
                                <?php echo $lang['People']; ?>
                            </div>
                            <div class="da-reviews-avatar">
                                <?php foreach ($readers as $reader): ?>
                                    <a href="<?php echo Ibos::app()->createUrl('user/home/index', array('uid' => $reader['uid'])); ?>">
                                        <img src="<?php echo Org::getDataStatic($reader['uid'], 'avatar', 'small') ?>"
                                             title="<?php echo $reader['realname']; ?>" class="img-rounded"/>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="hidden" id="relatedid" name="relatedid" value="<?php echo $diary['diaryid']; ?>">
                <input type="hidden" id="relatedmodule" name="relatedmodule" value="<?php echo 'diary'; ?>">
            </div>
        </div>
        <!-- Mainer content -->
    </div>
</div>
<!-- Footer -->
<script src='<?php echo STATICURL; ?>/js/src/belt.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/diary.js?<?php echo VERHASH; ?>'></script>
<script src="<?php echo STATICURL; ?>/js/lib/lightbox/js/lightbox.js?<?php echo VERHASH; ?>"></script>
<script>
    $(function () {
        $("[data-toggle='bamboo-pgb']").each(function () {
            $(this).studyplay_star({
                Enabled: false
            });
        });
    });
</script>
