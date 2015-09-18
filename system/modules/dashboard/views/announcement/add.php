
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['System announcement']; ?></h1>
		<ul class="mn">
			<li>
				<a href="<?php echo $this->createUrl( 'announcement/setup' ); ?>"><?php echo $lang['Manage']; ?></a>
			</li>
			<li>
				<span><?php echo $lang['Add']; ?></span>
			</li>
		</ul>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'announcement/add' ); ?>" id="sys_announcement_form" method="post" class="form-horizontal">
			<!-- 添加系统公告 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Add'] . $lang['System announcement']; ?></h2>
				<div class="ctbw">
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Subject']; ?></label>
						<div class="controls">
							<div id="anc_title" class="imi-input mbs" contentEditable></div>
							<div id="anc_title_editor"></div>
							<input type="hidden" id="subject" name="subject" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Start time']; ?></label>
						<div class="controls">
							<div class="datepicker" id="date_start">
								<a href="javascript:;" class="datepicker-btn"></a>
								<input type="text" name="starttime" class="datepicker-input" value="<?php echo date( 'Y-m-d H:i' ); ?>">
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['End time']; ?></label>
						<div class="controls">
							<div class="datepicker" id="date_end">
								<a href="javascript:;" class="datepicker-btn"></a>
								<input type="text" name="endtime" class="datepicker-input">
							</div>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Announcement type']; ?></label>
						<div class="controls">
							<label class="radio">
								<input type="radio" name="type" value="0" checked />
								<?php echo $lang['Announcement text']; ?>
							</label>
							<label class="radio">
								<input type="radio" name="type" value="1" />
								<?php echo $lang['Announcement link']; ?>
							</label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Content']; ?></label>
						<div class="controls">
							<textarea name="message" id="an_content" rows="5" data-toggle="popover" data-trigger="focus"></textarea>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<button name="announcementSubmit" type="submit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
		</form>
	</div>
</div>
<script src="<?php echo $this->getAssetUrl(); ?>/js/db_announcement.js"></script>