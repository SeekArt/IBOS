
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Edit scheduled task']; ?> - <?php echo $cron['name']; ?></h1>
	</div>
	<div>
		<!-- 计划任务 start -->
		<div class="ctb">
			<div class="alert trick-tip">
				<div class="trick-tip-title">
					<i></i>
					<strong><?php echo $lang['Skills prompt']; ?></strong>
				</div>
				<div class="trick-tip-content">
					<?php echo $lang['Cron edit tips']; ?>
				</div>
			</div>
		</div>
		<form method="post" class="form-horizontal form-narrow" action="<?php echo $this->createUrl( 'cron/index', array( 'op' => 'edit' ) ); ?>">
			<div class="control-group">
				<label class="control-label"><?php echo $lang['Weekly']; ?></label>
				<div class="controls">
					<select name="weekdaynew" class="span2">
						<option value="-1">*</option>
						<?php for ( $i = 0; $i <= 6; $i++ ): ?>
							<option value="<?php echo $i; ?>" <?php if ( $cron['weekday'] == $i ): ?>selected<?php endif; ?>><?php echo $lang['Cron week day ' . $i] ?></option>
						<?php endfor; ?>
					</select>
					<p class="help-inline"><?php echo $lang['Cron week tip']; ?></p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang['Per mensem']; ?></label>
				<div class="controls">
					<select name="daynew" class="span2">
						<option value="-1">*</option>
						<?php for ( $i = 1; $i <= 31; $i++ ): ?>
							<option value="<?php echo $i; ?>" <?php if ( $cron['day'] == $i ): ?>selected<?php endif; ?>><?php echo $i . $lang['Cron day'] ?></option>
						<?php endfor; ?>
					</select>
					<p class="help-inline"><?php echo $lang['Cron month tip']; ?></p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang['Hours']; ?></label>
				<div class="controls">
					<select name="hournew" class="span2">
						<option value="-1">*</option>
						<?php for ( $i = 0; $i <= 23; $i++ ): ?>
							<option value="<?php echo $i; ?>" <?php if ( $cron['hour'] == $i ): ?>selected<?php endif; ?>><?php echo $i . $lang['Cron hour'] ?></option>
						<?php endfor; ?>
					</select>
					<p class="help-inline"><?php echo $lang['Cron hours tip'] ?></p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang['Minutes']; ?></label>
				<div class="controls">
					<input type="text" name="minutenew" class="span2" value="<?php echo $cron['minute']; ?>" />
					<p class="help-inline"><?php echo $lang['Cron minutes tip']; ?></p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo $lang['Task script']; ?></label>
				<div class="controls">
					<input type="text" name="filenamenew" value="<?php echo $cron['filename']; ?>" class="span2" />
					<p class="help-inline"><?php echo $lang['Cron task script tip']; ?></p>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
					<input type="hidden" name="id" value="<?php echo $cron['cronid']; ?>" />
					<input type="hidden" name="type" value="<?php echo $cron['type']; ?>" />
					<input type="hidden" name="module" value="<?php echo $cron['module']; ?>" />
					<button type="submit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
				</div>
			</div>
	</div>
</form>
</div>