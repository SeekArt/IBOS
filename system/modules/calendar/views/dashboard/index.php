
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Calendar module']; ?></h1>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'dashboard/update' ); ?>" method="post" class="form-horizontal">
			<!-- 日程设置 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Schedule setting']; ?></h2>
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Allow add schedule']; ?></label>
					<div class="controls">
						<input type="checkbox" name="calendaraddschedule" data-toggle="switch" value="1" <?php if ( $setting['calendaraddschedule'] == 1 ): ?>checked<?php endif; ?> />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Allow edit schedule']; ?></label>
					<div class="controls">
						<input type="checkbox" name="calendareditschedule" data-toggle="switch" value="1" <?php if ( $setting['calendareditschedule'] == 1 ): ?>checked<?php endif; ?> />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Default workingtime']; ?></label>
					<div class="controls ctbw">
						<div class="b-m">
							<div id="psw_strength" data-target="minlength"></div>
							<input type="hidden" id="workingtime" name="calendarworkingtime" value="<?php echo $setting['calendarworkingtime'] ?>" />
						</div>
					</div>
				</div>
			</div>
			<!-- 任务设置 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Task setting']; ?></h2>
				<div class="control-group">
					<label class="control-label"><?php echo $lang['Allow add and edit task']; ?></label>
					<div class="controls">
						<input type="checkbox" name="calendaredittask" data-toggle="switch" value="1" <?php if ( $setting['calendaredittask'] == 1 ): ?>checked<?php endif; ?> />
					</div>
				</div>
			</div>
			<!-- 提交按钮 -->
			<div class="control-group">
				<div class="controls">
					<button type="submit" name="calendarSubmit" class="btn btn-primary btn-large btn-submit">
						<?php echo $lang['Submit']; ?>
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	$("#psw_strength").ibosSlider({
		range: true,
		max: 24,
		step: .5,
		scale: 4,
		tip: true,
		tipFormat: function(value) {
			return Ibos.date.numberToTime(value);
		},
		target: "next"
	})
</script>