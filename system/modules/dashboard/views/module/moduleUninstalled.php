<?php 
use application\core\utils\IBOS;
?>
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Module']; ?></h1>
		<ul class="mn">
			<li>
				<a href="<?php echo $this->createUrl( 'module/manager', array( 'op' => 'installed' ) ); ?>">
					<?php echo $lang['Installed']; ?>
				</a>
			</li>
			<li>
				<span><?php echo $lang['Uninstalled']; ?></span>
			</li>
		</ul>
	</div>
	<div>
		<form action="" class="form-horizontal">
			<!-- 未安装功能 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Uninstalled'] . $lang['Module']; ?></h2>
				<div>
					<table class="table table-bordered table-striped table-operate">
						<thead>
							<tr>
								<th width="80"><?php echo $lang['Icon']; ?></th>
								<th><?php echo $lang['Name']; ?></th>
								<th><?php echo $lang['Module desc']; ?></th>
								<th width="100"><?php echo $lang['Operation']; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $modules ) ): ?>
								<tr>
									<td colspan="4"><?php echo $lang['No not installed modules']; ?></td>
								</tr>
							<?php else: ?>
								<?php foreach ( $modules as $id => $module ) : ?>
									<tr>
										<td>
											<?php if ( $module['icon'] ): ?>
												<img src="<?php echo IBOS::app()->assetManager->getAssetsUrl( $id ) . '/image/icon.png'; ?>">
											<?php else: ?>

											<?php endif; ?>
										</td>
										<td><?php echo $module['name']; ?></td>
										<td><?php echo $module['description']; ?></td>
										<td>
											<a href="<?php echo $this->createUrl( 'module/install', array( 'module' => $id ) ); ?>" class="btn btn-small" title="<?php echo $lang['Install']; ?>"><?php echo $lang['Install']; ?></a>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
</div>