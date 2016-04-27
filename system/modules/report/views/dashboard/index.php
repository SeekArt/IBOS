<?php 

use application\core\utils\File;
use application\core\utils\IBOS;
use application\modules\dashboard\model\Stamp;

?>
<link rel="stylesheet" href="<?php echo IBOS::app()->assetManager->getAssetsUrl( 'report' ); ?>/css/dbreport.css?<?php echo VERHASH; ?>">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Work report']; ?></h1>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'dashboard/update' ); ?>" class="form-horizontal" method="post">
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Work report setting']; ?></h2>
				<div class="ctbw">
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Stamp function']; ?></label>
						<div class="controls">
							<input type="checkbox" id="stamp_switch" name="stampenable" value='1' id="" data-toggle="switch" class="visi-hidden" <?php if ( $config['stampenable'] ): ?>checked<?php endif; ?>>
						</div>
					</div>
				</div>
				<!-- 图章设置 -->
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
												<label class="checkbox pull-right" title="<?php //echo $lang['Autoreview tip'];  ?>"><input type="checkbox" name="autoreview" id="auto_apprise" value="1"><?php echo $lang['Auto comment']; ?></label>
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
																	<img src="<?php echo File::fileName( 'data/stamp/' . Stamp::model()->fetchIconById( $stampId ) ); ?>" alt="" width="60" height="24">
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
															<img src="<?php echo File::fileName( 'data/stamp/' . Stamp::model()->fetchIconById( $diffStampId ) ); ?>" alt=""  width="60" height="24">
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
<script src="<?php echo IBOS::app()->assetManager->getAssetsUrl( 'report' ); ?>/js/report_dashboard_index.js?<?php echo VERHASH; ?>"></script>
<script>
	Ibos.app.setPageParam({
		AUTO_REVIEW: "<?php echo $config['autoreview']; ?>"
	});
</script>	