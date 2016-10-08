<?php

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
?>
<link rel="stylesheet" href="<?php echo $assetUrl . '/css/index_report.css'; ?>">
<!-- IE 8 Hack 加入空script标签延迟html加载，为了让空值图片能正常显示 -->
<script></script>

<!--个人-->
<?php if ( $tab == 'reportPersonal' ): ?>
	<?php if ( !empty( $reports ) ): ?>
		<table class="table table-striped">
			<tbody>
				<?php foreach ( $reports as $report ): ?>
					<tr>
						<td width="80">
							<div>
								<a title="<?php echo $report['subject']; ?>" 
								   href="<?php echo Ibos::app()->urlManager->createUrl( 'report/default/show', array('repid' => $report['repid']) ); ?>" 
									><?php echo StringUtil::cutStr( $report['subject'], 40 ); ?></a>
							</div>
						</td>
						<td width="20" style="text-align:right;">
							<?php if ( !empty($report['iconUrl']) ): ?><img src="<?php echo $report['iconUrl'];  ?>" alt=""><?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="mbox-base">
			<div class="fill-hn xac">
				<a href="<?php echo Ibos::app()->urlManager->createUrl( 'report/default/index' ); ?>" class="link-more">
					<i class="cbtn o-more"></i>
					<span class="ilsep"><?php echo $lang['Show more report']; ?></span>
				</a>
			</div>
		</div>
	<?php else: ?>
		<div class="in-rp-personal-empty">
			<a href="<?php echo Ibos::app()->createUrl('report/default/add') ?>" class="in-rp-add" target="_blank"></a>
		</div>
	<?php endif; ?>
<!--评阅-->
<?php elseif ( $tab == 'reportAppraise' ): ?>
	<?php if ( !empty( $subReports ) ): ?>
		<table class="table table-striped">
			<tbody>
				<?php foreach ( $subReports as $subReport ): ?>
					<tr>
						<td width="40">
							<div class="avatar-box" data-toggle="usercard" data-param="uid=<?php echo $subReport['uid']; ?>">
                                <span class="avatar-circle avatar-circle-small">
									<a href="<?php echo Ibos::app()->urlManager->createUrl( 'user/home/index', array( 'uid'=>$subReport['uid'] ) ); ?>">
										<img src="<?php echo $subReport['userInfo']['avatar_middle']; ?>">
									</a>
                                </span>
                            </div>
						</td>
						<td align="left">
							<div>
								<a title="<?php echo $subReport['subject']; ?>" 
								   href="<?php echo Ibos::app()->urlManager->createUrl( 'report/review/show', array('repid' => $subReport['repid']) ); ?>" 
									><?php echo $subReport['userInfo']['realname'].' &nbsp; ' . StringUtil::cutStr( $subReport['subject'], 40 ); ?></a>
							</div>
						</td>
						<td width="60" style="text-align:right;">
							<?php if ( !empty($subReport['iconUrl']) ): ?><img src="<?php echo $subReport['iconUrl'];  ?>" alt=""><?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="mbox-base">
			<div class="fill-hn xac">
				<a href="<?php echo Ibos::app()->urlManager->createUrl( 'report/default/index' ); ?>" class="link-more">
					<i class="cbtn o-more"></i>
					<span class="ilsep"><?php echo $lang['Show more report']; ?></span>
				</a>
			</div>
		</div>
	<?php else: ?>
		<div class="in-rp-appraise-empty"></div>
	<?php endif; ?>
<?php endif; ?>
