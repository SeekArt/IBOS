<?php

use application\core\utils\Ibos;
use application\core\utils\Org;
use application\core\utils\StringUtil;
use application\modules\user\model\User;

?>
<div class="da-stamp">
    <span id="preview_stamp_<?php echo $diary['diaryid']; ?>">
        <?php if (!empty($stampUrl)): ?>
            <img style="border:none" id="stamp_<?php echo $diary['diaryid']; ?>" src="<?php echo $stampUrl; ?>"
                 width="150px" height="90px"/>
        <?php endif; ?>
    </span>
</div>
<table class="da-detail-table">
    <tbody>
    <tr>
        <td colspan="3">
            <div class="da-detail-header curp clearfix" data-action="hideDiaryDetail">
                <?php if ($isShowDiarytime): ?>
                    <div class="mini-date fill-ss pull-left">
                        <strong><?php echo $diary['diarytime']['day']; ?></strong>
                        <div class="mini-date-body">
                            <p><?php echo $diary['diarytime']['weekday']; ?></p>
                            <p><?php echo $diary['diarytime']['year']; ?>
                                -<?php echo $diary['diarytime']['month']; ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="da-detail-usi pull-left">
                        <a href="javascript:;" class="avatar-circle">
                            <img class="mbm" src="<?php echo Org::getDataStatic($diary['uid'], 'avatar', 'middle') ?>"
                                 alt="">
                        </a>
                        <span><?php echo User::model()->fetchRealnameByUid($diary['uid']) . $lang['Log']; ?></span>
                    </div>
                <?php endif; ?>
                <div class="da-detail-time pull-right">
                    <i class="o-da-clock"></i>
                    <span><?php echo $lang['Submitted'] . $diary['addtime']; ?></span>
                </div>
            </div>
        </td>
    </tr>
    <?php if (count($data['originalPlanList']) > 0): ?>
        <?php foreach ($data['originalPlanList'] as $key2 => $diaryRecord): ?>
            <tr>
                <?php if ($key2 == 0): ?>
                    <th rowspan="<?php echo count($data['originalPlanList']) ?>" width="68"
                        class="sep"><?php echo $lang['Original plan']; ?></th>
                <?php endif; ?>
                <td width="3" class="sep"></td>
                <td>
                    <div class="fill">
                        <div class="bamboo-pgb pull-right">
                            <span class="pull-left xcn fss"><?php echo ($diaryRecord['schedule'] * 10) . "%" ?></span>
                            <span data-toggle="bamboo-pgb" data-value="<?php echo $diaryRecord['schedule']; ?>"></span>
                            <input type="hidden" name="" value="<?php echo $diaryRecord['schedule']; ?>">
                        </div>
                        <span class="da-detail-num"><?php echo $key2 + 1 ?>
                            .</span><?php echo $diaryRecord['content']; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (count($data['outsidePlanList']) > 0): ?>
        <?php foreach ($data['outsidePlanList'] as $key3 => $outsidePlan): ?>
            <tr>
                <?php if ($key3 == 0): ?>
                    <th rowspan="<?php echo count($data['outsidePlanList']); ?>" width="68"
                        class="sep"><?php echo $lang['Unplanned']; ?></th>
                <?php endif; ?>
                <td class="sep" width="3"></td>
                <td>
                    <div class="fill">
                        <div class="bamboo-pgb pull-right">
                            <span class="pull-left xcn fss"><?php echo ($outsidePlan['schedule'] * 10) . "%" ?></span>
                            <span data-toggle="bamboo-pgb" data-value="<?php echo $outsidePlan['schedule']; ?>"></span>
                            <input type="hidden" name="" value="<?php echo $outsidePlan['schedule']; ?>">
                        </div>
                        <span class="da-detail-num"><?php echo $key3 + count($data['originalPlanList']) + 1 ?>
                            .</span><?php echo $outsidePlan['content']; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    <tr>
        <th class="sep" width="68"><?php echo $lang['Work']; ?><br/><?php echo $lang['Summary']; ?></th>
        <td class="sep" width="3"></td>
        <td class="summary-td">
            <div class="fill editor-content text-break" style="width: 620px; min-height: 180px">
                <p class="summary"><?php echo $diary['content']; ?></p>
            </div>
        </td>
    </tr>
    <?php if (!empty($attachs)): ?>
        <tr>
            <th class="sep" width="68"><?php echo $lang['Attachment']; ?>
                <br/><?php echo '(' . count($attachs) . '个)'; ?></th>
            <td class="sep" width="3"></td>
            <td>
                <?php foreach ($attachs as $attache): ?>
                    <div class="cti">
                        <i class="atti">
                            <img src="<?php echo $attache['iconsmall']; ?>">
                        </i>
                        <div class="attc">
                            <div>
                                <?php echo $attache['filename']; ?>
                                <span class="tcm"><?php echo '(' . $attache['filesize'] . ')'; ?></span>
                            </div>
                                <span class="fss">
                                    <a href="<?php echo $attache['downurl']; ?>"
                                       target="_blank"><?php echo $lang['Download']; ?></a>&nbsp;&nbsp;
                                    <?php if (isset($attache['officereadurl'])): ?>
                                        <a href="javascript:;" data-action="viewOfficeFile"
                                           data-param='{"href": "<?php echo $attache['officereadurl']; ?>"}'
                                           title="<?php echo $lang['Read']; ?>">
                                            <?php echo $lang['Read']; ?>
                                        </a>
                                    <?php endif; ?>
                                </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </td>
        </tr>
    <?php endif; ?>
    <?php if (count($data['tomorrowPlanList']) > 0): ?>
        <tr>
            <td colspan="3">
                <div class="mini-date fill-ss pull-left">
                    <strong><?php echo $diary['nextdiarytime']['day']; ?></strong>
                    <div class="mini-date-body">
                        <p><?php echo $diary['nextdiarytime']['weekday']; ?></p>
                        <p><?php echo $diary['nextdiarytime']['year']; ?>
                            -<?php echo $diary['nextdiarytime']['month']; ?></p>
                    </div>
                </div>
            </td>
        </tr>
        <?php foreach ($data['tomorrowPlanList'] as $key4 => $tomorrowPlan): ?>
            <tr>
                <?php if ($key4 == 0): ?>
                    <th rowspan="<?php echo count($data['tomorrowPlanList']); ?>" width="68"
                        class="sep"><?php echo $lang['Work']; ?><br/><?php echo $lang['Plan']; ?></th>
                <?php endif; ?>
                <td class="sep" width="3"></td>
                <td>
                    <div class="fill">
                        <span class="da-detail-num"><?php echo $key4 + 1; ?>
                            .</span><?php echo $tomorrowPlan['content']; ?>
                        <?php if (!empty($tomorrowPlan['timeremind']) && $this->id == 'default'): ?>
                            <div class="da-remind-bar pull-right">
                                <i class="o-clock"></i> <?php echo $tomorrowPlan['timeremind'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
<div class="cti bdbs">
    <!-- 点评 -->
    <h4><?php echo $lang['Review']; ?></h4>
    <div id="load_comment_<?php echo $diary['diaryid']; ?>" data-id="<?php echo $diary['diaryid']; ?>">
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
                'module_table' => "diary",
                'api' => "reviewSubordinate",
                'allowComment' => $allowComment,
                'showStamp' => $fromController == 'review' && $this->issetStamp(),
                'url' => $sourceUrl,
                'detail' => Ibos::lang('Comment my diray', '', array('{url}' => $sourceUrl, '{title}' => StringUtil::cutStr(str_replace(PHP_EOL, '', strip_tags($diary['content'])), 50)))
            )));
        ?>
    </div>
</div>
<div class="cti">
    <!-- 阅读人员 -->
    <?php if (!empty($readers)): ?>
        <h4><?php echo $lang['Read']; ?><?php echo $lang['Personnel']; ?></h4>
        <div>
            <div class="da-reviews-count"><?php echo $lang['View']; ?>
                <strong><?php echo count($readers); ?></strong>
                <?php echo $lang['People']; ?>
            </div>
            <div class="da-reviews-avatar">
                <?php foreach ($readers as $reader): ?>
                    <a href="<?php echo Ibos::app()->createUrl('user/home/index', array('uid' => $reader['uid'])); ?>"><img
                            src="<?php echo Org::getDataStatic($reader['uid'], 'avatar', 'small') ?>"
                            title="<?php echo $reader['realname']; ?>" class="img-rounded"/></a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="da-detail-ft">
    <a href="javascript:;" class="da-mark-up" data-action="hideDiaryDetail"></a>
</div>
