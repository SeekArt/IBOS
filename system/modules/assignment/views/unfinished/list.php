<?php

use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\assignment\utils\Assignment as AssignmentUtil;
?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/assignment.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar start -->
	<?php echo $this->getSidebar(); ?>
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
            <!-- 发布框 -->
            <form action="javascript:;">
                <div class="am-publish-box shut">
                    <!-- 简易模式 -->
                    <div class="fill">
                        <div class="am-publish clearfix">
                            <div class="am-placeholder" id="am_publish_charcount"></div>
                            <input type="text" id="am_publish_input" class="am-publish-input" name="subject">
                            <div class="amp-bar">
                                <div class="amp-bar-charge">
                                    <input type="text" id="am_bar_charge" value="<?php echo StringUtil::wrapId( IBOS::app()->user->uid ); ?>">
                                </div>
                                <div class="amp-bar-endtime">
                                    <div class="input-group datepicker" id="am_bar_endtime">
                                        <span class="input-group-addon"><?php echo $lang['To']; ?></span>
                                        <a href="javascript:;" class="datepicker-btn"></a>
                                        <input type="text" class="datepicker-input" id="am_bar_endtime_input" placeholder="<?php echo $lang['When to end']; ?>">
                                    </div>
                                </div>
                                <button type="button" class="o-am-plus" title="<?php echo $lang['Release']; ?>" data-action="addTask"></button>
                            </div>
                        </div>
                        <a href="javascript:;" class="am-publish-toggle"></a>
                    </div>
                    <!-- 高级模式 -->
                    <div class="am-publish-dt">
                        <div class="row mb">
                            <div class="span4">
                                <input type="text" name="chargeuid" id="am_charge" value="<?php echo StringUtil::wrapId( IBOS::app()->user->uid ); ?>">
                            </div>
                            <div class="span4">
                                <div class="input-group datepicker pull-left" id="am_starttime">
                                    <span class="input-group-addon"><?php echo $lang['From']; ?></span>
                                    <a href="javascript:;" class="datepicker-btn"></a>
                                    <input type="text" class="datepicker-input" name="starttime" id="am_starttime_input" placeholder="<?php echo $lang['When to start']; ?>">
                                </div>
                            </div>
                            <div class="span4">
                                <div class="input-group datepicker pull-left" id="am_endtime">
                                    <span class="input-group-addon"><?php echo $lang['To']; ?></span>
                                    <a href="javascript:;" class="datepicker-btn"></a>
                                    <input type="text" class="datepicker-input" name="endtime" id="am_endtime_input" placeholder="<?php echo $lang['When to end']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row mb">
                            <div class="span12">
                                <input type="text" name="participantuid" id="am_participant">
                            </div>
                        </div>
                        <div class="mb">
                            <textarea name="description" rows="4" id="am_description" placeholder="<?php echo $lang['Description']; ?>"></textarea>
                        </div>
                        <div class="posr clearfix">
                            <div class="am-att-upload">
                                <span id="am_att_upload"></span>
                            </div>
                            <button class="btn btn-icon">
                                <i class="o-paperclip"></i>
                            </button>
                            <div class="pull-right">
                                <span id="am_description_charcount" class="am-desc-charcount"></span>
                                <button type="button" class="btn btn-primary" data-action="addTask"><?php echo $lang['Release']; ?></button>
                            </div>
                        </div>
                        <div id="am_att_list"></div>
                        <input type="hidden" name="attachmentid" id="attachmentid">
                        <a href="javascript:;" class="am-publish-toggle"></a>
                    </div>
                </div>
            </form>
            <!-- 列表分类 -->
            <div data-node-type="taskView">
                <!-- 我负责的任务 -->
                <div class="am-block mb" id="am_my_charge">
                    <div class="am-block-t">
                        <div class="am-pill"><i class="o-ol-am-user"></i> <?php echo $lang['My responsible assignment']; ?></div>
                    </div>
                    <div class="am-block-b">
						<?php if ( !empty( $chargeData ) ): ?>
							<table class="table table-hover am-op-table" data-node-type="taskTable">
								<?php foreach ( $chargeData as $k => $charge ): ?>
									<tr data-id="<?php echo $charge['assignmentid']; ?>">
										<td width="22">
											<?php if ( $charge['status'] == 4 ): ?>
												<a href="javascript:;" class="am-checkbox disabled" data-id="<?php echo $charge['assignmentid']; ?>"></a>
											<?php else: ?>
												<a href="javascript:;" class="am-checkbox" data-id="<?php echo $charge['assignmentid']; ?>" title="<?php echo $lang['Finish'] ?>"></a>
											<?php endif ?>
										</td>
										<td width="36">
											<span class="avatar-circle avatar-circle-small">
                                                <img src="<?php echo $charge['designee']['avatar_small']; ?>">
											</span>
										</td>
										<td>
											<a href="<?php echo $this->createUrl( 'default/show', array( 'assignmentId' => $charge['assignmentid'] ) ) ?>" target="_blank" class="xcm">
												<?php echo $charge['subject']; ?>
											</a>
											<div class="fss">
												<?php echo $lang['The originator']; ?> <?php echo $charge['designee']['realname']; ?> 
												<?php echo $charge['st'] ?> —— <?php echo $charge['et'] ?>
												<?php if ( TIMESTAMP > $charge['endtime'] ): ?>
													<i class="om-am-warning mls" title="<?php echo $lang['Expired']; ?>"></i>
												<?php elseif ( $charge['remindtime'] > 0 ): ?>
													<i class="om-am-clock mls" title="<?php echo $lang['Has been set to remind']; ?>"></i>
												<?php endif; ?>
											</div>
										</td>
										<td width="110">
											<span class="pull-right am-tag am-tag-<?php echo AssignmentUtil::getCssClassByStatus( $charge['status'] ) ?>">
												<?php if ( $charge['status'] == 0 ): ?>
													<?php echo $lang['Unreaded']; ?>
												<?php elseif ( $charge['status'] == 1 ): ?>
													<?php echo $lang['Ongoing']; ?>
												<?php elseif ( $charge['status'] == 4 ): ?>
													<?php echo $lang['Has been cancelled']; ?>
												<?php endif; ?>
											</span>
											<div class="am-item-op">
												<!-- 未读和进行中时可以设置提醒 -->
												<?php if ( $charge['status'] == 0 || $charge['status'] == 1 ): ?>
													<a href="javascript:;" class="co-clock" data-action="openRemindDialog" data-param='{"id": <?php echo $charge['assignmentid']; ?> }' title="<?php echo $lang['Remind'] ?>"></a>
												<?php endif; ?>
												<?php if ( $charge['designeeuid'] == $charge['chargeuid'] ): ?>
													<!-- 取消任务后不能再编辑 -->
													<?php if ( $charge['status'] != 4 ): ?>
														<a href="javascript:;" class="o-edit mlm" data-action="openTaskEditDialog" data-param='{"id": <?php echo $charge['assignmentid']; ?> }' title="<?php echo $lang['Edit']; ?>"></a>
													<?php endif ?>
													<a href="javascript:;" class="o-trash mlm" data-action="removeTask" data-param='{"id": <?php echo $charge['assignmentid']; ?> }' title="<?php echo $lang['Delete']; ?>"></a>
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
                <!-- 我指派的任务 -->
                <div class="am-block mb" id="am_my_designee">
                    <div class="am-block-t">
                        <div class="am-pill"><i class="o-ol-am-appoint"></i> <?php echo $lang['My assignments']; ?></div>
                    </div>
                    <div class="am-block-b">
						<?php if ( !empty( $designeeData ) ): ?>
							<table class="table table-hover am-op-table" data-node-type="taskTable">
								<?php foreach ( $designeeData as $k => $designee ): ?>
									<tr data-id="<?php echo $designee['assignmentid']; ?>">
										<td width="22">
											<?php if ( $designee['status'] == 0 || $designee['status'] == 4 ): ?>
												<a href="javascript:;" class="am-checkbox disabled" data-id="<?php echo $designee['assignmentid']; ?>"></a>
											<?php else: ?>
												<a href="javascript:;" class="am-checkbox" data-id="<?php echo $designee['assignmentid']; ?>" title="<?php echo $lang['Finish'] ?>"></a>
											<?php endif ?>
										</td>
										<td width="36">
											<span class="avatar-circle avatar-circle-small">
                                                <img src="<?php echo $designee['designee']['avatar_small']; ?>">
											</span>
										</td>
										<td>
											<a href="<?php echo $this->createUrl( 'default/show', array( 'assignmentId' => $designee['assignmentid'] ) ) ?>" class="xcm">
												<?php echo $designee['subject']; ?>
											</a>
											<div class="fss">
                                                <?php echo $lang['Arrange to']; ?> <?php echo $designee['designee']['realname']; ?>
                                                <?php echo $designee['st'] ?> —— <?php echo $designee['et'] ?>
												<?php if ( TIMESTAMP > $designee['endtime'] ): ?>
													<i class="om-am-warning mls" title="<?php echo $lang['Expired']; ?>"></i>
												<?php elseif ( $designee['remindtime'] > 0 ): ?>
													<i class="om-am-clock mls" title="<?php echo $lang['Has been set to remind']; ?>"></i>
												<?php endif; ?>
											</div>
										</td>
										<td width="110">
											<span class="pull-right am-tag am-tag-<?php echo AssignmentUtil::getCssClassByStatus( $designee['status'] ) ?>">
												<?php if ( $designee['status'] == 0 ): ?>
													<?php echo $lang['Unreaded']; ?>
												<?php elseif ( $designee['status'] == 1 ): ?>
													<?php echo $lang['Ongoing']; ?>
												<?php elseif ( $designee['status'] == 4 ): ?>
													<?php echo $lang['Has been cancelled']; ?>
												<?php endif; ?>
											</span>
											<div class="am-item-op">
												<!-- 未读和进行中时可以设置提醒 -->
												<?php if ( $designee['status'] == 0 || $designee['status'] == 1 ): ?>
													<a href="javascript:;" class="co-clock" data-action="openRemindDialog" data-param='{"id": <?php echo $designee['assignmentid']; ?> }' title="<?php echo $lang['Remind']; ?>"></a>
												<?php endif; ?>
												<!-- 取消任务后不能再编辑 -->
												<?php if ( $designee['status'] != 4 ): ?>
													<a href="javascript:;" class="o-edit mlm" data-action="openTaskEditDialog" data-param='{"id": <?php echo $designee['assignmentid']; ?> }' title="<?php echo $lang['Edit']; ?>"></a>
												<?php endif; ?>
												<a href="javascript:;" class="o-trash mlm" data-action="removeTask" data-param='{"id": <?php echo $designee['assignmentid']; ?> }' title="<?php echo $lang['Delete']; ?>"></a>
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
                <!-- 我参与的任务 -->
                <div class="am-block mb" id="am_my_participant">
                    <div class="am-block-t">
                        <div class="am-pill"><i class="o-ol-am-watch"></i> <?php echo $lang['I participate in the task']; ?></div>
                    </div>
                    <div class="am-block-b">
						<?php if ( !empty( $participantData ) ): ?>
							<table class="table table-hover" data-node-type="taskTable">
								<?php foreach ( $participantData as $k => $participant ): ?>
									<tr data-id="<?php echo $participant['assignmentid']; ?>">
										<td width="22"></td>
										<td width="36">
											<span class="avatar-circle avatar-circle-small">
												<img src="<?php echo $participant['charge']['avatar_small']; ?>">
											</span>
										</td>
										<td>
											<a href="<?php echo $this->createUrl( 'default/show', array( 'assignmentId' => $participant['assignmentid'] ) ) ?>" class="xcm">
												<?php echo $participant['subject']; ?>
											</a>
											<div class="fss">
												<?php echo $lang['The head']; ?> <?php echo $participant['charge']['realname']; ?> 
												<?php echo $participant['st'] ?> —— <?php echo $participant['et'] ?>
												<?php if ( TIMESTAMP > $participant['endtime'] ): ?>
													<i class="om-am-warning mls" title="<?php echo $lang['Expired']; ?>"></i>
												<?php elseif ( $participant['remindtime'] > 0 ): ?>
													<i class="om-am-clock mls" title="<?php echo $lang['Has been set to remind']; ?>"></i>
												<?php endif; ?>
											</div>
										</td>
										<td width="110">
											<span class="pull-right am-tag am-tag-<?php echo AssignmentUtil::getCssClassByStatus( $participant['status'] ) ?>">
												<?php if ( $participant['status'] == 0 ): ?>
													<?php echo $lang['Unreaded']; ?>
												<?php elseif ( $participant['status'] == 1 ): ?>
													<?php echo $lang['Ongoing']; ?>
												<?php elseif ( $participant['status'] == 4 ): ?>
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
		<?php if ( count( $chargeData ) > 10 ): ?>
			<a href="javascript:;" class="o-am-user" data-action="toCharge" title="<?php echo $lang['My responsible assignment']; ?>"></a>
		<?php endif; ?>
		<?php if ( count( $designeeData ) > 10 ): ?>
			<a href="javascript:;" class="o-am-appoint" data-action="toDesignee" title="<?php echo $lang['My assignments']; ?>"></a>
		<?php endif; ?>
		<?php if ( count( $participantData ) > 10 ): ?>
			<a href="javascript:;" class="o-am-watch" data-action="toParticipant" title="<?php echo $lang['I participate in the task']; ?>"></a>
		<?php endif; ?>
        <a href="javascript:;" class="o-am-top" data-action="totop" title="<?php echo $lang['To top']; ?>"></a>
    </div>
</div>
<script type="text/template" id="tpl_task">
    <tr data-id="<%=id%>">
    <td width="22">
    <a href="javascript:;" class="am-checkbox <%= (typeof chargeself == 'undefined') ? 'disabled' : '' %>" data-id="<%=id%>" title="<?php echo $lang['Finish'] ?>"></a>
    </td>
    <td width="36">
    <span class="avatar-circle avatar-circle-small">
    <img src="<%= charge.avatar_small %>">
    </span>
    </td>
    <td>
    <a href="<%= Ibos.app.url('assignment/default/show', { 'assignmentId': id }) %>" class="xcm">
    <%= subject %>
    </a>
    <div class="fss">
    <%= U.lang('ASM.ARRANGE_TO') %> <%= charge.realname %> 
    <%= time %>
    </div>
    </td>
    <td width="110">
    <span class="pull-right am-tag am-tag-unread">
    <%= U.lang('ASM.UNREAD') %>
    </span>
    <div class="am-item-op">
    <a href="javascript:;" class="co-clock" data-action="openRemindDialog" data-param='{"id": <%= id %> }' title="<?php echo $lang['Remind'] ?>"></a>
    <% if(typeof chargeself === 'undefined') { %> 
    <a href="javascript:;" class="o-edit mlm" data-action="openTaskEditDialog" data-param='{"id": <%= id %> }' title="<?php echo $lang['Edit'] ?>"></a>
    <a href="javascript:;" class="o-trash mlm" data-action="removeTask" data-param='{"id": <%= id %> }' title="<?php echo $lang['Delete'] ?>"></a>
    <% } %>
    </div>
    </td>
    </tr>
</script>

<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/app/ibos.charCount.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script> 
<script src="<?php echo $assetUrl; ?>/js/assignment.js?<?php echo VERHASH; ?>"></script> 
<script src="<?php echo $assetUrl; ?>/js/assignment_unfinished_list.js?<?php echo VERHASH; ?>"></script>
