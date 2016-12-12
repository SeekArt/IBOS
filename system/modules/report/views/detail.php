<?php

use application\core\utils\Ibos;
use application\core\utils\Org;
use application\core\utils\StringUtil;
use application\modules\user\model\User;

?>
<div class="rp-stamp">
    <span id="preview_stamp_<?php echo $report['repid']; ?>">
        <?php if (!empty($stampUrl)): ?>
            <img style="border:none" id="stamp_<?php echo $report['repid']; ?>" src="<?php echo $stampUrl; ?>"
                 width="150px" height="90px"/>
        <?php endif; ?>
    </span>
</div>
<table class="rp-detail-table">
    <tbody>
    <tr>
        <td colspan="3">
            <div class="rp-detail-header curp clearfix" data-action="hideReportDetail">
                <div class="mini-date fill-ss pull-left">
                    <h4>
                        <?php
                        if ($fromController == 'review') {
                            echo User::model()->fetchRealnameByUid($report['uid']);
                        }
                        ?>
                        <?php echo $report['subject']; ?>
                    </h4>
                </div>
                <div class="rp-detail-time pull-right">
                    <i class="o-rp-clock"></i>
                    <span><?php echo $lang['Submitted'] . $report['addtime']; ?></span>
                </div>
            </div>
        </td>
    </tr>
    <!--原计划-->
    <?php if (count($orgPlanList) > 0): ?>
        <?php foreach ($orgPlanList as $k1 => $orgPlan): ?>
            <tr>
                <?php if ($k1 == 0): ?>
                    <th rowspan="<?php echo count($orgPlanList) ?>" width="68"
                        class="sep"><?php echo $lang['Original plan']; ?></th>
                <?php endif; ?>
                <td width="3" class="sep"></td>
                <td>
                    <div class="fill">
                        <div class="bamboo-pgb pull-right">
                            <span class="pull-left fss xcn"><?php echo $orgPlan['process'] * 10 ?>%</span>
                            <span data-toggle="bamboo-pgb"></span>
                            <input type="hidden" name="" value="<?php echo $orgPlan['process']; ?>">
                        </div>
                        <span class="rp-detail-num"><?php echo $k1 + 1 ?>.</span> <?php echo $orgPlan['content']; ?>
                        <?php if (!empty($orgPlan['exedetail'])): ?>
                            <div class="rp-exec-status">
                                <?php echo $lang['Implementation']; ?>：<?php echo $orgPlan['exedetail']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    <!--计划外-->
    <?php if (count($outSidePlanList) > 0): ?>
        <?php foreach ($outSidePlanList as $k2 => $outSidePlan): ?>
            <tr>
                <?php if ($k2 == 0): ?>
                    <th rowspan="<?php echo count($outSidePlanList); ?>" class="sep"
                        width="68"><?php echo $lang['Outside plan']; ?></th>
                <?php endif; ?>
                <td class="sep" width="3"></td>
                <td>
                    <div class="fill">
                        <div class="bamboo-pgb pull-right">
                            <span class="pull-left fss xcn"><?php echo $outSidePlan['process'] * 10 ?>%</span>
                            <span data-toggle="bamboo-pgb" data-value="<?php $outSidePlan['process'] ?>"></span>
                            <input type="hidden" name="" value="<?php echo $outSidePlan['process']; ?>">
                        </div>
                        <span class="rp-detail-num"><?php echo count($orgPlanList) + $k2 + 1 ?>
                            .</span> <?php echo $outSidePlan['content']; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    <!--工作总结-->
    <tr>
        <th class="sep" width="68"><?php echo $lang['Work']; ?><br/><?php echo $lang['Summary']; ?></th>
        <td class="sep" width="3"></td>
        <td>
            <div class="fill editor-content text-break" style="min-height: 180px; width: 620px">
                <p class="summary">
                    <?php echo $report['content']; ?>
                </p>
            </div>
        </td>
    </tr>
    <!--附件-->
    <?php if (!empty($attachs)): ?>
        <tr>
            <th class="sep" width="68"><?php echo $lang['Attachement']; ?>
                <br/><?php echo count($attachs); ?><?php echo $lang['Individual']; ?></th>
            <td class="sep" width="3"></td>
            <td>
                <?php foreach ($attachs as $attache): ?>
                    <div class="cti">
                        <i class="atti">
                            <img src="<?php echo $attache['iconsmall']; ?>" alt="<?php echo $lang['Attachement']; ?>">
                        </i>
                        <div class="attc">
                            <div>
                                <?php echo $attache['filename']; ?>
                                <span class="tcm">(<?php echo $attache['filesize']; ?>)</span>
                            </div>
                                <span class="fss">
                                    <a href="<?php echo $attache['downurl']; ?>"
                                       target="_blank"><?php echo $lang['Download']; ?></a>&nbsp;&nbsp;
                                    <?php if (isset($attache['officereadurl'])): ?>
                                        <a href="javascript:;" data-action="viewOfficeFile"
                                           data-param='{"href": "<?php echo $attache['officereadurl']; ?>"}'
                                           title="<?php echo $lang['View']; ?>">
                                            <?php echo $lang['View']; ?>
                                        </a>
                                    <?php endif; ?>
                                </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </td>
        </tr>
    <?php endif; ?>
    <!--下次计划-->
    <?php if (count($nextPlanList) > 0): ?>
        <?php foreach ($nextPlanList as $k3 => $nextPlan): ?>
            <tr>
                <?php if ($k3 == 0): ?>
                    <th rowspan="<?php echo count($nextPlanList); ?>" class="sep"
                        width="68"><?php echo $lang['Work']; ?><br/><?php echo $lang['Plan']; ?></th>
                <?php endif; ?>
                <td class="sep" width="3"></td>
                <td>
                    <div class="fill">
                        <span class="rp-detail-num"><?php echo $k3 + 1; ?>.</span> <?php echo $nextPlan['content']; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    </tbody>
</table>
<div class="cti bdbs">
    <!--点评-->
    <h4><?php echo $lang['Comment']; ?></h4>
    <div id="load_comment_<?php echo $report['repid']; ?>" data-id="<?php echo $report['repid']; ?>"
         data-url="<?php echo Ibos::app()->urlManager->createUrl('message/comment/getcomment'); ?>">
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
                'allowComment' => $allowComment,
                'showStamp' => $fromController == 'review' && $this->issetStamp(),
                'url' => $sourceUrl,
                'detail' => Ibos::lang('Comment my report', '', array('{url}' => $sourceUrl, '{title}' => StringUtil::cutStr($report['subject'], 50)))
            )));
        ?>
    </div>
</div>


<div class="cti">
    <!--阅读人员-->
    <?php if (!empty($readers)): ?>
        <h4><?php echo $lang['Reading']; ?><?php echo $lang['Staff']; ?></h4>
        <div class="rp-reviews-count">
            <?php echo $lang['View']; ?>
            <strong><?php echo count($readers); ?></strong>
            <?php echo $lang['People']; ?>
        </div>
        <div class="rp-reviews-avatar">
            <?php foreach ($readers as $reader): ?>
                <a href="<?php echo Ibos::app()->createUrl('user/home/index', array('uid' => $reader['uid'])); ?>">
                    <img src="<?php echo Org::getDataStatic($reader['uid'], 'avatar', 'small') ?>"
                         title="<?php echo $reader['realname']; ?>" class="img-rounded"/>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="rp-detail-ft">
    <a href="javascript:;" class="rp-mark-up" data-action="hideReportDetail"></a>
</div>
