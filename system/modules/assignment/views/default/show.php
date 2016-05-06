<?php

use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Stamp;
?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/assignment.css?<?php echo VERHASH; ?>">
<div class="am-details">
	<div class="am-details-hd">
		<div class="am-details-hd-bg"></div>
		<div class="am-details-hd-bgb"></div>
		<div class="am-details-hd-ct">
			<h2 class="am-details-subject"><?php echo $assignment['subject']; ?></h2>
			<p class="am-details-desc"><?php echo $assignment['description']; ?></p>
			<!-- 任务状态 -->
			<span data-node-type="taskStatusTag"></span>

			<?php if ( $isDesigneeuid || $isChargeuid ): ?>
				<div id="task_op_btn_wrap" class="pull-right"></div>
			<?php endif; ?>
			<div class="am-details-prop clearfix">
				<?php echo $assignment['st']; ?> <?php echo $lang['To']; ?> <?php echo $assignment['et']; ?>
				<?php if ( TIMESTAMP > $assignment['endtime'] ): ?><span class="ilsep fss"><i class="om-am-warning-w"></i> <?php echo $lang['Expired']; ?></span><?php endif; ?>
				<?php if ( $assignment['remindtime'] > 0 ): ?><span class="ilsep fss"><i class="om-am-clock-w"></i> <?php echo $lang['Has been set to remind']; ?></span><?php endif; ?>
				<?php if ( $isDesigneeuid || $isChargeuid ): ?>
					<span class="posr">
						<a href="javascript:;" class="o-am-setup mlm" data-toggle="dropdown"></a>
						<!-- 操作菜单 -->
						<ul class="dropdown-menu" id="task_op_menu">
							<?php if ( $isDesigneeuid ): ?>
								<li data-node="edit">
									<a href="javascript:;" data-action="openTaskEditDialog" data-param='{"id": <?php echo $assignment['assignmentid'] ?>}'><i class="o-menu-edit"></i> <?php echo $lang['Edit assignment']; ?></a>
								</li>
								<li data-node="remove">
									<a href="javascript:;" data-action="removeTaskInShow" data-param='{"id": <?php echo $assignment['assignmentid'] ?>}'><i class="o-menu-trash"></i> <?php echo $lang['Delete assignment']; ?></a>
								</li>
							<?php endif; ?>
							<li data-node="restart">
								<a href="javascript:;" data-action="restartTask" data-param='{"id": <?php echo $assignment['assignmentid'] ?>}'><i class="o-amm-loop"></i> <?php echo $lang['Restart assignment']; ?></a>
							</li>
							<li data-node="cancel">
								<a href="javascript:;" data-action="cancelTask" data-param='{"id": <?php echo $assignment['assignmentid'] ?>}'><i class="o-amm-chi"></i> <?php echo $lang['Cancel assignment']; ?></a>
							</li>
							<li class="divider" style="margin: 0;"></li>
							<li data-node="remind">
								<a href="javascript:;" data-action="openRemindDialog" data-param='{"id": <?php echo $assignment['assignmentid'] ?>}'><i class="o-amm-clock"></i> <?php echo $lang['Set remind']; ?></a>
							</li>
							<li data-node="delay">
								<a href="javascript:;" data-action="delayTask" data-param='{"id": <?php echo $assignment['assignmentid'] ?>}'><i class="o-amm-delay"></i> <?php echo $lang['Delay']; ?></a>
							</li>
						</ul>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="am-details-bd clearfix">
		<?php if ( isset( $assignment['stampUrl'] ) ): ?>
			<div id="am_stamp_holder">
				<!-- 输出已选中的图章  -->
				<img src="<?php echo File::fileName( Stamp::STAMP_PATH . $assignment['stampUrl'] ); ?>" width="150" height="90">
			</div>
		<?php endif; ?>
		<div class="am-details-lside">
			<div class="fill">
				<!-- 指派信息 -->
				<div class="posr clearfix mb">
					<div class="am-designee-user">
						<a href="<?php echo $designee['space_url']; ?>" target="_blank" class="avatar-circle">
							<img src="<?php echo $designee['avatar_middle']; ?>" alt="">
						</a>
						<div class="am-designee-user-bd">
							<strong><?php echo $designee['realname']; ?></strong>
							<p class="fss tcm"><?php echo $lang['The originator']; ?></p>
						</div>
					</div>
					<div class="am-flow">
						<div class="am-pill"><i class="o-ol-am-appoint"></i> <?php echo $lang['Assigned to']; ?></div>
					</div>
					<div class="am-charge-user">
						<a href="<?php echo $charge['space_url']; ?>" target="_blank" class="avatar-circle">
							<img src="<?php echo $charge['avatar_middle']; ?>" alt="">
						</a>
						<div class="am-charge-user-bd">
							<strong><?php echo $charge['realname']; ?></strong>
							<p class="fss tcm"><?php echo $lang['The head']; ?></p>
						</div>
					</div>
				</div>
				<!-- 附件 -->
				<div class="bdbs">
					<?php if ( isset( $assignment['attach'] ) ): ?>
						<?php foreach ( $assignment['attach'] as $key => $value ): ?>
							<div class="media mb">
								<img src="<?php echo $value['iconsmall']; ?>" alt="<?php echo $value['filename']; ?>" class="pull-left">
								<div class="media-body">
									<div class="media-heading">
										<?php echo $value['filename']; ?> <span class="tcm">(<?php echo $value['filesize']; ?>)</span>
									</div>
									<div class="fss">
										<a href="<?php echo $value['downurl']; ?>"><?php echo $lang['Download']; ?></a>
										<?php if ( isset( $value['officereadurl'] ) ): ?>
											<a href="javascript:;" data-action="viewOfficeFile" data-param='{"href": "<?php echo $value['officereadurl']; ?>"}' title="<?php echo $lang['Read']; ?>">
												<?php echo $lang['Read']; ?>
											</a>
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<!-- 参与人详情 -->
				<div>
					<?php if ( $participantCount > 0 ): ?>
						<br />
						<p class="mbs">
							<?php echo $lang['In addition to']; ?> <span class="fsl"><?php echo $participantCount; ?></span> <?php echo $lang['Participate in the number of']; ?><br />
						</p>
						<p class="fss"><?php echo $participant; ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="am-details-rside">
			<div class="fill">
				<div class="t">任务详情</div>
				<div>
					<?php
					$sourceUrl = IBOS::app()->urlManager->createUrl( 'assignment/default/show', array( 'assignmentId' => $assignment['assignmentid'] ) );
					$this->widget( 'application\modules\assignment\widgets\AssignmentComment', array(
						'module' => 'assignment',
						'table' => 'assignment',
						'attributes' => array(
							'rowid' => $assignment['assignmentid'],
							'moduleuid' => IBOS::app()->user->uid,
							'touid' => $assignment['designeeuid'],
							'module_rowid' => $assignment['assignmentid'],
							'module_table' => 'assignment',
							'allowComment' => 1,
							'showStamp' => 1,
							'url' => $sourceUrl,
							'detail' => IBOS::lang( 'Comment my assignment', '', array( '{url}' => $sourceUrl, '{title}' => StringUtil::cutStr( $assignment['subject'], 50 ) ) )
				) ) );
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Template: 延期申请、取消申请确认框 -->
<script type="text/template" id="tpl_apply_confirm">
	<div style="width: 370px;">
	<div class="media">
	<div class="pull-left avatar-circle">
	<img src="<%=data.imgUrl%>">
	</div>
	<div class="mbs">
	<p><strong><%=data.name%></strong>： <%= data.reason %></p>
	</div>
	<% if (data.startTime  && data.endTime ) { %>
	<div class="mb xco">
	<?php echo $lang['Delay time']; ?>：<%= data.startTime %> <?php echo $lang['To']; ?> <%= data.endTime %>
	</div>
	<% } %>
	</div>
	<div class="pull-right">
	<button class="btn" data-node-type="refuseBtn"><?php echo $lang['Refused']; ?></button>
	<button class="btn" data-node-type="agreeBtn"><?php echo $lang['Agree']; ?></button>
	</div>
	</div>
</script>
<!-- Template: 延期申请对话框 -->
<script type="text/template" id="tpl_delay_dialog">
	<form action="javascript:;">

	<textarea style="width: 300px; height: 100px;" name="reason" placeholder="<%= U.lang('ASM.INPUT_TASK_DELAY_REASON')%>" class="mb"></textarea>
	<div class="input-group datepicker mb" id="task_delay_starttime">
	<span class="input-group-addon"><?php echo $lang['Starttime']; ?></span>
	<a href="javascript:;" class="datepicker-btn"></a>
	<input type="text" name="starttime" class="datepicker-input" value="<?php echo date('Y-m-d H:i',$assignment['starttime']); ?>">
	</div>
	<div class="input-group datepicker mb" id="task_delay_endtime">
	<span class="input-group-addon"><?php echo $lang['Endtime']; ?></span>
	<a href="javascript:;" class="datepicker-btn"></a>
	<input type="text" name="endtime" class="datepicker-input" value="<?php echo date( 'Y-m-d H:i',$assignment['endtime']); ?> ">
	</div>
	</form>
</script>

<script>
	Ibos.app.s({
			isDesignee: <?php echo $isDesigneeuid ? 'true' : 'false'; ?>,
			isCharge: <?php echo $isChargeuid ? 'true' : 'false'; ?>,
			// 任务状态(0:未读,1:进行中,2:已完成,3:已评价,4:取消)
			taskStatus: <?php echo $assignment['status']; ?>,
			taskId: <?php echo $assignment['assignmentid'] ?>,
			stamps: <?php echo CJSON::encode( $this->getStamps() ) ?>,
			stampUrl: "<?php echo  File::fileName( Stamp::STAMP_PATH ); ?>",
			// 延期任务、取消任务申请
			apply:<?php echo $applyData; ?>,
			// 评论附件url
			commentAttachUrl: Ibos.app.url("assignment/default/show", {'op': 'updateCommentAttach'})
	});
</script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/assignment.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/assignment_default_show.js?<?php echo VERHASH; ?>"></script>
