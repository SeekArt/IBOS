
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Update cache']; ?></h1>
	</div>
	<div>
		<div class="ctb">
			<?php if ( $doUpdate ): ?>
				<div style="width:300px;">
					<?php echo $typedesc; ?> ....
					<div id="progress_bar" class="progress progress-striped active" title="Progress-bar">
						<div class="progress-bar" style="width: 100%;"></div>
					</div>
				</div>
				<input type="hidden" id="next_move" value="<?php echo $next; ?>" />
			<?php else: ?>
				<h2 class="st"><?php echo $lang['Update cache']; ?></h2>
				<div class="alert trick-tip">
					<div class="trick-tip-title">
						<i></i>
						<strong><?php echo $lang['Skills prompt']; ?></strong>
					</div>
					<div class="trick-tip-content">
						<?php echo $lang['Update cache tip']; ?>
					</div>
				</div>
				<form class="form-horizontal" method="post" action="<?php echo $this->createUrl( 'update/index' ); ?>">
					<div>
						<div class="control-group">
							<div class="controls" id="update_type">
								<label class="checkbox">
									<input type="checkbox" value="data" checked name="updatetype[]" /><?php echo $lang['Data cache']; ?>
								</label>
								<label class="checkbox">
									<input type="checkbox" value="static" name="updatetype[]" /><?php echo $lang['Static cache']; ?>
								</label>
								<label class="checkbox">
									<input type="checkbox" value="module" name="updatetype[]"><?php echo $lang['Module setting']; ?>
								</label>
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
								<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
								<button type="submit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Sure']; ?></button>
							</div>
						</div>
					</div>
				</form>
			<?php endif; ?>
		</div>
	</div>
</div>
<script>
<?php if ( $doUpdate ): ?>
		$.get('<?php echo $this->createUrl( 'update/index', array( 'op' => $op ) ); ?>', function(data) {
			if (data.isSuccess) {
				window.location.href = $('#next_move').val();
			}
		}, 'json');
<?php endif; ?>
</script>