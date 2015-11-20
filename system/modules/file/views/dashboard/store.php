<form action="<?php echo $this->createUrl( 'dashboard/store' ); ?>" class="form-horizontal" method="post">
	<div class="ct">
		<div class="clearfix">
			<h1 class="mt"><?php echo $lang['Folder']; ?></h1>
			<ul class="mn">
				<li>
					<a href="<?php echo $this->createUrl( 'dashboard/index' ); ?>"><?php echo $lang['Folder setting']; ?></a>
				</li>
				<li>
					<span><?php echo $lang['Store setting']; ?></span>
				</li>
				<li>
					<a href="<?php echo $this->createUrl( 'dashboard/trash' ); ?>"><?php echo $lang['Trash']; ?></a>
				</li>
			</ul>
		</div>
		<!-- 存储设置 -->
		<div class="ctb">
			<h2 class="st"><?php echo $lang['Store setting']; ?></h2>
			<div class="alert trick-tip">
				<div class="trick-tip-title">
					<i></i>
					<strong><?php echo $lang['Tips']; ?></strong>
				</div>
				<div class="trick-tip-content">
					<?php if ( !$iboscloudopen ): ?>
						<?php echo $lang['Open iboscloud tips']; ?>
					<?php else: ?>
						<?php echo $lang['Open filecloud tips']; ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="ctbw">
				<div class="control-group ">
					<label class="control-label"><?php echo $lang['Whether open cloud plate']; ?></label>
					<div class="controls mbs">
						<input type="checkbox" name="filecloudopen" value="1" data-toggle="switch" <?php if ( $filecloudopen == 1 ): ?>checked<?php endif; ?> />
					</div>
					<div class="controls tcm fss ">
						<?php echo $lang['Filecloud tips']; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="control-group">
		<label for="" class="control-label"></label>
		<div class="controls">
			<button type="submit" class="btn btn-primary btn-large btn-submit"> <?php echo $lang['Submit']; ?> </button>
		</div>
	</div>
	<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
</form>
