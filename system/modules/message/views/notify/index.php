<?php 

use application\core\utils\Convert;
use application\core\utils\Ibos;

?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/message.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
	<!-- Sidebar goes here-->
	<?php echo $this->getSidebar( array( 'lang' => $lang ) ); ?>
	<!-- Mainer right -->
	<div class="mcr">
		<div class="page-list" id="remind_list">
			<div class="page-list-header">
				<div class="pull-left">
					<label class="checkbox btn"><input type="checkbox" data-name="remind"></label>
					<button type="button" class="btn" data-action="removeNotices"><?php echo $lang['Delete']; ?></button>
					<?php if ( $unreadCount > 0 ): ?><button type="button" class="btn" data-action="markNoticeRead"><?php echo $lang['Set read']; ?></button><?php endif; ?>
					<button type="button" onclick="location.href = '<?php echo Ibos::app()->urlManager->createUrl( 'user/home/personal', array( 'op' => 'remind' , 'uid' => Ibos::app()->user->uid) ); ?>'" class="btn"><?php echo $lang['Remind set']; ?></button>
				</div>
				<!-- 	<div class="pull-right" style="margin-top: 10px;" title="启用桌面通知功能，目前只支持chrome和safari浏览器">
					<label class="checkbox">
						<input type="checkbox" id="enable_desktop_notify">
						使用桌面通知
					</label>
				</div> -->
			</div>
			<div class="page-list-mainer">
				<?php if ( $unreadCount > 0 ): ?>
					<div class="band band-primary">
						<?php echo $lang['View']; ?> <strong><?php echo $unreadCount; ?></strong> <?php echo $lang['New notify'] ?>
						<a href="javascript:;"  data-action="markAllRead" class="anchor ilsep"><?php echo $lang['Set all read']; ?></a>
					</div>
				<?php endif; ?>
				<?php if ( !empty( $list ) ): ?>
					<ul class="main-list main-list-hover msg-pm-list">
						<?php foreach ( $list as $module => $data ): ?>
							<?php $isNew = array_key_exists( 'newlist', $data );?>
							<li class="main-list-item" id="remind_<?php echo $module; ?>">
								<div class="avatar-box pull-left posr" >
									<img class="mbm" width="64" src="<?php echo Ibos::app()->assetManager->getAssetsUrl($module).'/image/icon.png';?>" alt="">
									<?php if ( $isNew ): ?><span class="bubble"><?php echo count( $data['newlist'] ); ?></span><?php endif; ?>
								</div>
								<div class="main-list-item-body">
									<?php if ( $isNew ): ?>
										<div class="msg-box mb">
											<span class="msg-box-arrow"><i></i></span>
											<div class="msg-box-body">
												<div><strong><?php echo $modules[$module]['name']; ?>：</strong></div>
												<div>
													<ul class="clist">
														<?php foreach ( $data['newlist'] as $k=>$newMsg ): ?>
																		<?php if ( $k == 0 ) {
																			$time = Convert::formatDate( $newMsg['ctime'], 'u' );
																		} ?>
															<li>
																<?php if( empty( $newMsg['url'] ) ): ?>
																	<?php echo $newMsg['title']; ?>
																<?php else: ?>
																	<a href="<?php echo $this->createUrl('notify/jump', array( 'id' => $newMsg['id'], 'url' => $newMsg['url'] ) ); ?>"><?php echo $newMsg['title']; ?></a>
																<?php endif; ?>
															</li>
														<?php endforeach; ?>
													</ul>
												</div>
											</div>
										</div>
									<?php else: ?>
										<?php $time =  Convert::formatDate($data['latest']['ctime'],'u'); ?>
										<div class="mb">
											<p>
												<strong><?php echo $modules[$module]['name']; ?>：</strong>
												<?php if( empty( $data['latest']['url'] ) ): ?>
													<?php echo $data['latest']['title']; ?>
												<?php else: ?>
													<a href="<?php echo $data['latest']['url']; ?>"><?php echo $data['latest']['title']; ?></a>
												<?php endif; ?>
											</p>
										</div>
									<?php endif; ?>
									<div>
										<label class="checkbox checkbox-inline mbz pull-left">
											<input type="checkbox" name="remind" value="<?php echo $module; ?>">
											<span class="tcm fss"><?php echo $time; ?></span>
										</label>
										<div class="pull-right">
											<a href="<?php echo $this->createUrl( 'notify/detail', array( 'module' => $module ) ); ?>" title="<?php echo $lang['Detail']; ?>" class="cbtn o-more"></a>
											<a href="javascript:;" title="<?php echo $lang['Delete']; ?>" class="cbtn o-trash mls" data-action="removeNotice" data-param='{"id": "<?php echo $module; ?>"}'></a>
										</div>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
				<div class="no-data-tip"></div>
				<?php endif; ?>
			</div>
			<div class="page-list-footer">
				<?php $this->widget( 'application\core\widgets\Page', array( 'pages' => $pages ) ); ?>
			</div>
		</div>
		<!-- Mainer content -->
	</div>
</div>
<script src='<?php echo $assetUrl; ?>/js/message.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/message_notify_index.js?<?php echo VERHASH; ?>'></script>
