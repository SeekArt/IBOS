<?php

use application\core\utils as util;
use application\modules\dashboard\model\Stamp;

?>
<link rel="stylesheet" href="<?php echo util\Ibos::app()->assetManager->getAssetsUrl( 'diary' ); ?>/css/dbdiary.css?<?php echo VERHASH; ?>">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Work diary']; ?></h1>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'dashboard/update' ); ?>" class="form-horizontal" method="post">
			<!-- 工作日志设置 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Work diary setting']; ?></h2>
				<div class="ctbw">
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Locks log']; ?></label>
						<div class="controls">
							<div class="row">
								<div class="span6">
									<div class="input-group" >
										<input type="text" name="lockday" value="<?php echo $config['lockday']; ?>">
										<span class="input-group-addon"><?php echo $lang['days']; ?></span>
									</div>
								</div>
								<div class="span6" style="padding-top: 6px;">
									<?php echo $lang['Previous log can not be modified']; ?>
								</div>
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Review log lock']; ?></label>
						<div class="controls">
							<input type="checkbox" name="reviewlock" value='1' id="" data-toggle="switch" class="visi-hidden" <?php if ( $config['reviewlock'] ): ?>checked<?php endif; ?>>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Sharing log server']; ?></label>
						<div class="controls">
							<input type="checkbox" name="sharepersonnel" value='1' id="" data-toggle="switch" class="visi-hidden" <?php if ( $config['sharepersonnel'] ): ?>checked<?php endif; ?>>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['To share logs to give comments']; ?></label>
						<div class="controls">
							<input type="checkbox" name="sharecomment" value='1' id="" data-toggle="switch" class="visi-hidden" <?php if ( $config['sharecomment'] ): ?>checked<?php endif; ?>>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Focus on logging']; ?></label>
						<div class="controls">
							<input type="checkbox" name="attention" value='1' id="" data-toggle="switch" class="visi-hidden" <?php if ( $config['attention'] ): ?>checked<?php endif; ?>>
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['A key to remind the default content']; ?></label>
						<div class="controls">
							<input type="text" name="remindcontent" value="<?php echo $config['remindcontent']; ?>">
							<div class="help-inline"><?php echo $lang['The staff did not write the log that day will receive this message'] ?></div>
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Stamp function']; ?></label>
						<div class="controls">
							<input type="checkbox" id="stamp_switch" name="stampenable" value='1' id="" data-toggle="switch" class="visi-hidden" <?php if ( $config['stampenable'] ): ?>checked<?php endif; ?>>
						</div>
					</div>
				</div>

				<div id="stamp_setup_box">
					<div class="ctbw">
						<div class="control-group">
							<div class="controls">
								<select name="pointsystem" id="point_sys" class="span6">
									<option value="10" <?php if ( $config['pointsystem'] == 10 ): ?>selected<?php endif; ?>><?php echo $lang['Ten point scale']; ?></option>
									<option value="5" <?php if ( $config['pointsystem'] == 5 ): ?>selected<?php endif; ?>><?php echo $lang['Five point scale']; ?></option>
									<option value="3" <?php if ( $config['pointsystem'] == 3 ): ?>selected<?php endif; ?>><?php echo $lang['Three point scale']; ?></option>
								</select>
							</div>
						</div>
					</div>
					<div>
						<div class="control-group">
							<div class="controls">
								<table id="stamp_slot_table" class="table table-bordered table-striped ps-table pull-left" style="width: 300px;">
									<thead>
										<tr>
											<th><?php echo $lang['Score']; ?></th>
											<th><?php echo $lang['Stamp']; ?></th>
											<th>
												<label class="checkbox pull-right" title="<?php echo $lang['Autoreview tip']; ?>"><input type="checkbox" name="autoreview" id="auto_apprise" value="1"><?php echo $lang['Auto comment']; ?></label>
											</th>
										</tr>
									</thead>
									<tbody>
										<?php if ( count( $stamps ) > 0 ): ?>
											<?php foreach ( $stamps as $score => $stampId ): ?>
												<!--大于上面所选的积分制的条数就隐藏-->
												<?php if ( $score > $config['pointsystem'] || $stampId == 0 ): ?>
													<tr <?php if ( $score > $config['pointsystem'] ): ?>style="display: none;"<?php endif; ?>>
														<td><?php echo $score; ?></td>
														<td>
															<div class="stamp-slot" data-node-type="stampSlot"></div>
															<input type="hidden" name="stampdetails[<?php echo $score; ?>]">
														</td>
														<td>
															<label class="radio pull-right" style="display: none;"><input type="radio" name="apprise" value="<?php echo $score; ?>"></label>
														</td>
													</tr>
												<?php else: ?>
													<tr>
														<td><?php echo $score; ?></td>
														<td>
															<div class="stamp-slot" data-node-type="stampSlot">
																<div class="stamp-item ui-draggable" data-node-type="stampItem" data-stamp-id="<?php echo $stampId; ?>">
																	<img src="<?php echo util\File::fileName(  Stamp::model()->fetchIconById( $stampId ) ); ?>" alt="" width="60" height="24">
																</div>
															</div>
															<input type="hidden" name="stampdetails[<?php echo $score; ?>]" value="<?php echo $stampId; ?>">
														</td>
														<td>
															<label class="radio pull-right" style="display: none;"><input type="radio" name="apprise" value="<?php echo $score; ?>" <?php if ( $config['autoreviewstamp'] == $stampId ): ?>checked<?php endif; ?>></label>
														</td>
													</tr>
												<?php endif; ?>
											<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
								<table class="table table-bordered ps-table pull-left ml" style="width: 172px;">
									<thead>
										<tr>
											<th colspan="2"><?php echo $lang['Drag the stamp']; ?></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<div id="stamp_item_box" class="stamp-item-box">
													<?php foreach ( $diffStampIds as $key => $diffStampId ): ?>
														<div class="stamp-item" data-node-type="stampItem" data-stamp-id="<?php echo $diffStampId; ?>">
															<img src="<?php echo util\File::fileName(  Stamp::model()->fetchIconById( $diffStampId ) ); ?>" alt=""  width="60" height="24">
														</div>    
													<?php endforeach; ?>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<!-- end-->

				<div class="control-group">
					<div class="controls">
						<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
						<button class="btn btn-primary btn-large btn-submit" type="submit"><?php echo $lang['Submit']; ?></button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<script src="<?php echo util\Ibos::app()->assetManager->getAssetsUrl( 'diary' ); ?>/js/diary_dashboard_index.js?<?php echo VERHASH; ?>"></script>
<script>
	Ibos.app.setPageParam({
		AUTO_REVIEW: "<?php echo $config['autoreview']; ?>"
	});
</script>