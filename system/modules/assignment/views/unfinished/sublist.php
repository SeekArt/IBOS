<?php
use application\modules\assignment\utils\Assignment as AssignmentUtil;

?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/assignment.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar start -->
    <?php echo $this->getSubSidebar(); ?>
    <!-- Siderbar end -->
    <div class="mcr">
        <div class="mc-header">
            <div class="mc-header-info clearfix">
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
        <div>
            <!-- 列表分类 -->
            <div>
                <div class="fill-sn"></div>
                <!-- TA负责的任务 -->
                <div class="am-block mb" id="am_my_charge">
                    <div class="am-block-t">
                        <div class="am-pill"><i
                                class="o-ol-am-user"></i> <?php echo $lang['His responsible assignment']; ?></div>
                    </div>
                    <div class="am-block-b">
                        <?php if (!empty($chargeData)): ?>
                            <table class="table table-hover am-op-table" data-node-type="taskTable">
                                <?php foreach ($chargeData as $k => $charge): ?>
                                    <tr data-id="<?php echo $charge['assignmentid']; ?>">
                                        <td width="36">
											<span class="avatar-circle avatar-circle-small">
												<img src="<?php echo $charge['designee']['avatar_small']; ?>">
											</span>
                                        </td>
                                        <td>
                                            <a href="<?php echo $this->createUrl('default/show', array('assignmentId' => $charge['assignmentid'])) ?>"
                                               class="xcm">
                                                <?php echo $charge['subject']; ?>
                                            </a>
                                            <div class="fss">
                                                <?php echo $lang['The originator']; ?> <?php echo $charge['designee']['realname']; ?>
                                                <?php echo $charge['st'] ?> —— <?php echo $charge['et'] ?>
                                                <?php if (TIMESTAMP > $charge['endtime']): ?>
                                                    <i class="om-am-warning mls"
                                                       title="<?php echo $lang['Expired']; ?>"></i>
                                                <?php elseif ($charge['remindtime'] > 0): ?>
                                                    <i class="om-am-clock mls"
                                                       title="<?php echo $lang['Has been set to remind']; ?>"></i>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td width="110">
											<span
                                                class="pull-right am-tag am-tag-<?php echo AssignmentUtil::getCssClassByStatus($charge['status']) ?>">
												<?php if ($charge['status'] == 0): ?>
                                                    <?php echo $lang['Unreaded']; ?>
                                                <?php elseif ($charge['status'] == 1): ?>
                                                    <?php echo $lang['Ongoing']; ?>
                                                <?php elseif ($charge['status'] == 4): ?>
                                                    <?php echo $lang['Has been cancelled']; ?>
                                                <?php endif; ?>
											</span>
                                            <div class="am-item-op">
                                                <?php if ($charge['status'] == 0 || $charge['status'] == 1): ?>
                                                    <a href="javascript:;" class="co-clock"
                                                       data-action="openRemindDialog"
                                                       data-param='{"id": <?php echo $charge['assignmentid']; ?> }'
                                                       title="<?php echo $lang['Remind'] ?>"></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <div class="am-charge-empty"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- TA指派的任务 -->
                <div class="am-block mb" id="am_my_designee">
                    <div class="am-block-t">
                        <div class="am-pill"><i class="o-ol-am-appoint"></i> <?php echo $lang['His assignments']; ?>
                        </div>
                    </div>
                    <div class="am-block-b">
                        <?php if (!empty($designeeData)): ?>
                            <table class="table table-hover am-op-table" data-node-type="taskTable">
                                <?php foreach ($designeeData as $k => $designee): ?>
                                    <tr data-id="<?php echo $designee['assignmentid']; ?>">
                                        <td width="36">
											<span class="avatar-circle avatar-circle-small">
												<img src="<?php echo $designee['charge']['avatar_small']; ?>">
											</span>
                                        </td>
                                        <td>
                                            <a href="<?php echo $this->createUrl('default/show', array('assignmentId' => $designee['assignmentid'])) ?>"
                                               class="xcm">
                                                <?php echo $designee['subject']; ?>
                                            </a>
                                            <div class="fss">
                                                <?php echo $lang['Arrange to']; ?> <?php echo $designee['charge']['realname']; ?>
                                                <?php echo $designee['st'] ?> —— <?php echo $designee['et'] ?>
                                                <?php if (TIMESTAMP > $designee['endtime']): ?>
                                                    <i class="om-am-warning mls"
                                                       title="<?php echo $lang['Expired']; ?>"></i>
                                                <?php elseif ($designee['remindtime'] > 0): ?>
                                                    <i class="om-am-clock mls"
                                                       title="<?php echo $lang['Has been set to remind']; ?>"></i>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td width="110">
											<span
                                                class="pull-right am-tag am-tag-<?php echo AssignmentUtil::getCssClassByStatus($designee['status']) ?>">
												<?php if ($designee['status'] == 0): ?>
                                                    <?php echo $lang['Unreaded']; ?>
                                                <?php elseif ($designee['status'] == 1): ?>
                                                    <?php echo $lang['Ongoing']; ?>
                                                <?php elseif ($designee['status'] == 4): ?>
                                                    <?php echo $lang['Has been cancelled']; ?>
                                                <?php endif; ?>
											</span>
                                            <div class="am-item-op">
                                                <?php if ($designee['status'] == 0 || $designee['status'] == 1): ?>
                                                    <a href="javascript:;" class="co-clock"
                                                       data-action="openRemindDialog"
                                                       data-param='{"id": <?php echo $designee['assignmentid']; ?> }'
                                                       title="<?php echo $lang['Remind'] ?>"></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <div class="am-designee-empty"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- TA参与的任务 -->
                <div class="am-block mb" id="am_my_participant">
                    <div class="am-block-t">
                        <div class="am-pill"><i
                                class="o-ol-am-watch"></i> <?php echo $lang['His participate in the task']; ?></div>
                    </div>
                    <div class="am-block-b">
                        <?php if (!empty($participantData)): ?>
                            <table class="table table-hover" data-node-type="taskTable">
                                <?php foreach ($participantData as $k => $participant): ?>
                                    <tr data-id="<?php echo $participant['assignmentid']; ?>">
                                        <td width="36">
											<span class="avatar-circle avatar-circle-small">
												<img src="<?php echo $participant['charge']['avatar_small']; ?>">
											</span>
                                        </td>
                                        <td>
                                            <a href="<?php echo $this->createUrl('default/show', array('assignmentId' => $participant['assignmentid'])) ?>"
                                               class="xcm">
                                                <?php echo $participant['subject']; ?>
                                            </a>
                                            <div class="fss">
                                                <?php echo $lang['The head']; ?> <?php echo $participant['charge']['realname']; ?>
                                                <?php echo $participant['st'] ?> —— <?php echo $participant['et'] ?>
                                                <?php if (TIMESTAMP > $participant['endtime']): ?>
                                                    <i class="om-am-warning mls"
                                                       title="<?php echo $lang['Expired']; ?>"></i>
                                                <?php elseif ($participant['remindtime'] > 0): ?>
                                                    <i class="om-am-clock mls"
                                                       title="<?php echo $lang['Has been set to remind']; ?>"></i>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td width="110">
											<span
                                                class="pull-right am-tag am-tag-<?php echo AssignmentUtil::getCssClassByStatus($participant['status']) ?>">
												<?php if ($participant['status'] == 0): ?>
                                                    <?php echo $lang['Unreaded']; ?>
                                                <?php elseif ($participant['status'] == 1): ?>
                                                    <?php echo $lang['Ongoing']; ?>
                                                <?php elseif ($participant['status'] == 4): ?>
                                                    <?php echo $lang['Has been cancelled']; ?>
                                                <?php endif; ?>
											</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <div class="am-participant-empty"></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="am-toolbar">
        <?php if (count($chargeData) > 10): ?>
            <a href="javascript:;" class="o-am-user" data-action="toCharge"
               title="<?php echo $lang['His responsible assignment']; ?>"></a>
        <?php endif; ?>
        <?php if (count($designeeData) > 10): ?>
            <a href="javascript:;" class="o-am-appoint" data-action="toDesignee"
               title="<?php echo $lang['His assignments']; ?>"></a>
        <?php endif; ?>
        <?php if (count($participantData) > 10): ?>
            <a href="javascript:;" class="o-am-watch" data-action="toParticipant"
               title="<?php echo $lang['His participate in the task']; ?>"></a>
        <?php endif; ?>
        <a href="javascript:;" class="o-am-top" data-action="totop" title="<?php echo $lang['To top']; ?>"></a>
    </div>
</div>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/app/ibos.charCount.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/assignment.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/assignment_unfinished_list.js?<?php echo VERHASH; ?>"></script>