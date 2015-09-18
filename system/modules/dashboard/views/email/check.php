<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Email setting']; ?></h1>
		<ul class="mn">
			<li>
				<a href="<?php echo $this->createUrl( 'email/setup' ); ?>"><?php echo $lang['Setup']; ?></a>
			</li>
			<li>
				<span><?php echo $lang['Check']; ?></span>
			</li>
		</ul>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'email/check' ); ?>" method='post' class="form-horizontal" id="email_test_send">
			<!-- 检测邮件发送设置 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Check email setup']; ?></h2>
				<div class="ctbw">
					<div class="control-group">
						<label for="" class="control-label">
							<?php echo $lang['Test sender']; ?>
						</label>
						<div class="controls">
							<input name='testfrom' id="testfrom" type="text">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<?php echo $lang['Test recipient']; ?>
						</label>
						<div class="controls">
							<textarea name="testto" id="testto" rows="3"></textarea>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"></label>
						<div class="controls">
							<button name='emailSubmit' class="btn btn-primary btn-large btn-submit" type="submit"><?php echo $lang['Submit']; ?></button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src="<?php echo $assetUrl; ?>/js/db_email.js"></script>