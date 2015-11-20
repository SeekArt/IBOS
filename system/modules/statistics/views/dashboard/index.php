
<body>
	<form action="<?php echo $this->createUrl( 'dashboard/index' ); ?>" class="form-horizontal" method="post">
		<div class="ct">
			<div class="clearfix">
				<h1 class="mt"><?php echo $lang['Stats setup']; ?></h1>
			</div>
			<div>
				<!-- 微博设置 -->
				<div class="ctb">
					<h2 class="st"><?php echo $lang['Stats setup']; ?></h2>
					<div class="ctbw">
						<div class="control-group">
							<label class="control-label"><?php echo $lang['Enabled stat modules']; ?></label>
							<div class="controls">
								<div>
									<?php if ( !empty( $enabledModules ) ): ?>
										<?php foreach ( $enabledModules as $key => $module ): ?>
											<?php if ( $key % 3 == 0 ): ?></div><div><?php endif; ?>
											<label class="checkbox checkbox-inline">
												<input value="1" type="checkbox" <?php if ( isset( $statModules[$module['module']] ) && $statModules[$module['module']] == 1 ): ?>checked<?php endif; ?> name="statmodules[<?php echo $module['module']; ?>]">
												<?php echo $module['name']; ?>
											</label>
										<?php endforeach; ?>
									<?php else: ?>
										<?php echo $lang['Temporarily no use of the module']; ?>
									<?php endif; ?>
								</div>
							</div>
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