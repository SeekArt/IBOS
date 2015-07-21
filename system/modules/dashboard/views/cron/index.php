
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Scheduled task']; ?></h1>
	</div>
	<div>
		<form method="post" action="<?php echo $this->createUrl( 'cron/index' ); ?>" id="cron_form">
			<!-- 计划任务 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Scheduled task']; ?></h2>
				<div class="alert trick-tip">
					<div class="trick-tip-title">
						<i></i>
						<strong><?php echo $lang['Skills prompt']; ?></strong>
					</div>
					<div class="trick-tip-content">
						<?php echo $lang['Cron tips']; ?>
					</div>
				</div>
				<div class="page-list">
					<div class="page-list-header">
						<a href="javascript:;" id="del_btn" class="btn"><?php echo $lang['Cron delete task']; ?></a>
					</div>
					<div class="page-list-mainer">
						<table class="table table-striped table-operate">
							<thead>
								<tr>
									<th width="30">
										<label class="checkbox">
											<input type="checkbox" data-name="delete[]" id="">
										</label>
									</th>
									<th><?php echo $lang['Name']; ?></th>
									<th width="100"><?php echo $lang['Enable or not']; ?></th>
									<th><?php echo $lang['Type']; ?></th>
									<th><?php echo $lang['Time']; ?></th>
									<th><?php echo $lang['Cron last exec time']; ?></th>
									<th><?php echo $lang['Cron next exec time']; ?></th>
									<th width="120"><?php echo $lang['Operation']; ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $list as $cron ): ?>
									<tr>
										<td>
											<label class="checkbox">
												<input type="checkbox" name="delete[]" <?php if ( $cron['type'] == 'system' ): ?>disabled<?php endif; ?> value="<?php echo $cron['cronid']; ?>" >
											</label>
										</td>
										<td>
											<div>
												<input type="text" value="<?php echo $cron['name']; ?>" name="namenew[<?php echo $cron['cronid']; ?>]" class="input-small">
											</div>
											<div>
												<strong><?php echo $cron['filename']; ?></strong>
											</div>
										</td>
										<td>
											<input type="checkbox" name="availablenew[<?php echo $cron['cronid']; ?>]" value="1" data-toggle="switch" class="visi-hidden" <?php if ( $cron['available'] == '1' ): ?>checked<?php endif; ?> <?php if ( $cron['disabled'] ): ?>disabled<?php endif; ?>/>
										</td>
										<td><?php if ( $cron['type'] == 'user' ): ?><?php echo $lang['Custom']; ?><?php elseif ( $cron['type'] == 'system' ): ?><?php echo $lang['System built in']; ?><?php endif; ?></td>
										<td><?php echo $cron['time']; ?></td>
										<td><?php echo $cron['lastrun']; ?></td>
										<td><?php echo $cron['nextrun']; ?></td>
										<td>
											<a href="<?php echo $this->createUrl( 'cron/index', array( 'op' => 'edit', 'id' => $cron['cronid'] ) ); ?>" class="btn btn-mini"><?php echo $lang['Edit']; ?></a>
											<a href="<?php echo $this->createUrl( 'cron/index', array( 'op' => 'run', 'id' => $cron['cronid'] ) ); ?>" class="btn btn-mini"><?php echo $lang['Perform']; ?></a>
										</td>
									</tr>
								<?php endforeach; ?>
								<tr>
									<td>
										<?php echo $lang['New add'] ?>
									</td>
									<td>
										<input type="text" name="newname" class="input-small">
									</td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
			<input type="hidden" name="op" id="del_act" value="" />
			<button type="submit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
		</form>
	</div>
</div>
<script>
	$('#del_btn').on('click', function() {
		var val = U.getCheckedValue('delete[]');
		if ($.trim(val) !== '') {
			$('#del_act').val('delete');
			$('#cron_form').submit();
		} else {
			Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
		}
	});
</script>