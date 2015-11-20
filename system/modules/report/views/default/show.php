<?php

use application\core\utils\File;
use application\core\utils\Ibos;
use application\modules\dashboard\model\Stamp;

?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/report.css?<?php echo VERHASH; ?>">

<!-- Mainer -->
<div class="wrap">
	<div class="mc clearfix">
		<!-- Sidebar -->
		<?php echo $this->getSidebar(); ?>
		<!-- Mainer right -->
		<div class="mcr">
			<div class="mc-header">
				<div class="mc-header-info clearfix">
					<div class="mc-overview pull-right">
						<ul class="mc-overview-list">
							<li class="po-rp-clock">
								<?php echo $lang['Submit time']; ?>：<?php echo date('Y-m-d H:i', $report['addtime']); ?>
							</li>
						</ul>
					</div>
					<div class="usi-terse">
						<a href="" class="avatar-box">
							<span class="avatar-circle">
								<img class="mbm" src="static.php?type=avatar&uid=<?php echo $report['uid']; ?>&size=middle&engine=<?php echo ENGINE; ?>" alt="">
							</span>
						</a>
						<span class="usi-terse-user"><?php echo $realname; ?></span>
						<span class="usi-terse-group"><?php echo $departmentName; ?></span>
					</div>
				</div>
			</div>
			<div class="page-list">
				<div class="page-list-header">
					<div class="btn-toolbar pull-left">
						<a href="<?php echo $this->createUrl('default/edit' , array('repid'=>$report['repid'])); ?>" class="btn"><?php echo $lang['Edit']; ?></a>
						<a href="javascript:;" class="btn" data-param='{"id": "<?php echo $report['repid']; ?>"}' data-action="removeReport"><?php echo $lang['Delete']; ?></a>
					</div>
					<div class="btn-group pull-right">
						<a <?php if ( !empty( $preAndNextRep['preRep'] ) ): ?>
                                href="<?php echo $this->createUrl( 'default/show', array( 'repid' => $preAndNextRep['preRep']['repid'] ) ); ?>" class="btn" title="<?php echo $preAndNextRep['preRep']['subject']; ?>"
                            <?php else: ?>
                                href="javascript:;" class="btn disabled"
                            <?php endif; ?>>
							<i class="glyphicon-chevron-left"></i>
						</a>
						<a <?php if ( !empty( $preAndNextRep['nextRep'] ) ): ?>
                                href="<?php echo $this->createUrl(  'default/show', array( 'repid' => $preAndNextRep['nextRep']['repid'] ) ); ?>" class="btn" title="<?php echo $preAndNextRep['nextRep']['subject']; ?>"
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
							<tr>
								<td colspan="3">
									<div class="mini-date fill-ss">
										<h4><?php echo $report['subject']; ?></h4>
									</div>
								</td>
							</tr>
							<div class="rp-stamp">
								<?php if ( $report['stamp'] > 0 ): ?><img id="stamp_<?php echo $report['repid']; ?>" src="<?php echo File::fileName( Stamp::STAMP_PATH . $stampUrl ); ?>" width="150px" height="90px" /><?php endif; ?>
							</div>
							<!-- 原计划 -->
							<?php if( !empty( $orgPlanList ) ): ?>
								<?php foreach( $orgPlanList as $k1 => $orgPlan ): ?>
									<tr>
										<?php if( $k1 == 0 ): ?>
											<th rowspan="<?php echo count($orgPlanList); ?>" width="68" class="sep"><?php echo $lang['Original plan'] ?></th>
										<?php endif; ?>
										<td width="3" class="sep"></td>
										<td>
											<div class="fill">
												<div class="bamboo-pgb pull-right">
													<span class="pull-left xcn fss"><?php echo $orgPlan['process'] * 10?>%</span>
													<span data-toggle="bamboo-pgb"></span>
													<input type="hidden" name="" value="<?php echo $orgPlan['process']; ?>">
												</div>
												<span class="rp-detail-num"><?php echo $k1 + 1; ?>.</span> <?php echo $orgPlan['content']; ?>
												<div class="rp-exec-status">
													<?php echo $lang['Implementation'] ?>：<?php echo $orgPlan['exedetail']; ?>
												</div>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
							<!-- 计划外 -->
							<?php if(!empty($outSidePlanList)): ?>
								<?php foreach( $outSidePlanList as $k2 => $outSidePlan ): ?>
									<tr>
										<?php if( $k2 == 0 ): ?>
											<th rowspan="<?php echo count($outSidePlanList); ?>" class="sep" width="68"><?php echo $lang['Outside plan'] ?></th>
										<?php endif; ?>
										<td class="sep" width="3"></td>
										<td>
											<div class="fill">
												<div class="bamboo-pgb pull-right">
													<span class="pull-left xcn fss"><?php echo $outSidePlan['process'] * 10?>%</span>
													<span data-toggle="bamboo-pgb"></span>
													<input type="hidden" name="" value="<?php echo $outSidePlan['process']; ?>">
												</div>
												<span class="rp-detail-num"><?php echo count( $orgPlanList ) + $k2 + 1; ?>.</span> <?php echo $outSidePlan['content'] ?>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
							<!-- 工作总结 -->
							<tr>
								<th class="sep" width="68"><?php echo $lang['Work']; ?><br /><?php echo $lang['Summary']; ?></th>
								<td class="sep" width="3"></td>
								<td>
									<div class="fill" style="width: 660px;">
										<p class="summary">
											<?php echo $report['content']; ?>
										</p>
									</div>
								</td>
							</tr>
							<!-- 附件 -->
							<?php if( !empty( $attachs ) ): ?>
								<?php foreach ( $attachs as $k3 => $attach ): ?>
									<tr>
										<?php if( $k3 == 0 ): ?>
										<th class="sep" width="68" rowspan="<?php echo count( $attachs ); ?>"><?php echo $lang['Attachement']; ?><br />(<?php echo count($attachs); ?>个)</th>
										<?php endif; ?>
										<td class="sep" width="3"></td>
										<td>
											<div class="cti">
												<i class="atti">
													<img src="<?php echo $attach['iconsmall']; ?>" alt="<?php echo $lang['Attachement']; ?>">
												</i>
												<div class="attc">
													<div>
														<?php echo $attach['filename']; ?><span class="tcm">(<?php echo $attach['filesize']; ?>)</span>
													</div>
													<span class="fss">
														<a href="<?php echo $attach['downurl']; ?>"><?php echo $lang['Download']; ?></a>&nbsp;&nbsp;
					                                    <?php if (isset($attach['officereadurl'])): ?>
					                                        <a href="javascript:;" data-action="viewOfficeFile" data-param='{"href": "<?php echo $attach['officereadurl']; ?>"}' title="<?php echo $lang['View']; ?>">
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
							<?php if( !empty( $nextPlanList ) ): ?>
								<tr>
									<td colspan="3">
										<div class="mini-date fill-ss">
											<h4><?php echo $nextSubject; ?></h4>
										</div>
									</td>
								</tr>
								<!-- 计划 -->
								<?php foreach( $nextPlanList as $k4 => $nextPlan ): ?>
									<tr>
										<?php if( $k4 == 0 ): ?>
											<th rowspan="<?php echo count( $nextPlanList ); ?>" class="sep" width="68"><?php echo $lang['Work']; ?><br /><?php echo $lang['Plan']; ?></th>
										<?php endif; ?>
										<td class="sep" width="3"></td>
										<td>
											<div class="fill">
												<span class="rp-detail-num"><?php echo $k4 + 1; ?>.</span> <?php echo $nextPlan['content']; ?>
												<?php if ( $isInstallCalendar && !empty( $nextPlan['reminddate'] )): ?>
													<div class="da-remind-bar pull-right">
														<i class="o-clock"></i> <?php echo date('Y-m-d', $nextPlan['reminddate']); ?>
													</div>
												<?php endif; ?>
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
						<div id="report_comment" data-url="<?php echo IBOS::app()->urlManager->createUrl( 'message/comment/getcomment' ); ?>">
							<?php
								$this->widget( 'application\modules\report\widgets\ReportComment', array(
									'module' => 'report',
									'table' => 'report',
									'attributes' => array(
										'rowid' => $report['repid'],
										'moduleuid' => IBOS::app()->user->uid,
										'touid' => $report['uid'],
										'module_rowid' => $report['repid'],
										'module_table' => 'report',
										'api' => 'reviewSubordinate',
										'allowComment' => 0,
										'showStamp' => 0,
										'url' => IBOS::app()->urlManager->createUrl( 'report/default/show', array( 'repid' => $report['repid'] ) )
								) ) );
							?>
                        </div>
					</div>
					<!--阅读人员-->
					<?php if ( !empty( $readers ) ): ?>
					<div class="cti">
						<h4 class="rp-review-reader"><?php echo $lang['Reading'] . $lang['Staff']; ?></h4>
						<div class="rp-reviews-count">
							<?php echo $lang['View']; ?>
							<strong><?php echo count( $readers ); ?></strong>
							<?php echo $lang['People']; ?>
						</div>
						<div class="rp-reviews-avatar">
						<?php foreach ( $readers as $reader ): ?>
							<a href="<?php echo IBOS::app()->createUrl('user/home/index',array('uid'=>$reader['uid'])); ?>">
								<img src="static.php?type=avatar&uid=<?php echo $reader['uid']; ?>&size=small&engine=<?php echo ENGINE; ?>" title="<?php echo $reader['realname']; ?>" class="img-rounded"/>
							</a>
						<?php endforeach; ?>
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

<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/report.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/report_default_index.js?<?php echo VERHASH; ?>'></script>
<script>
	(function(){
		// 进度条初始化;
		$("[data-toggle='bamboo-pgb']").each(function(){
			var $elem = $(this);
            $elem.studyplay_star({
                Enabled: false,
                CurrentStar: +$elem.next().val()
            });
        });
	})();

</script>
