<?php 
use application\core\utils\Ibos;
use application\core\utils\Module;
?>

<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Module']; ?></h1>
		<ul class="mn">
			<li>
				<span><?php echo $lang['Installed']; ?></span>
			</li>
			<li>
				<a href="<?php echo $this->createUrl( 'module/manager', array( 'op' => 'uninstalled' ) ); ?>">
					<?php echo $lang['Uninstalled']; ?>
				</a>
			</li>
		</ul>
	</div>
	<div>
		<form action="javascript:;" class="form-horizontal">
			<!-- 已安装模块 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Installed'] . $lang['Module']; ?></h2>
				<div>
					<table id="installedTable" class="table table-bordered table-striped table-operate">
						<thead>
							<tr>
								<th width="80"><?php echo $lang['Icon']; ?></th>
								<th><?php echo $lang['Name']; ?></th>
								<th><?php echo $lang['Module desc']; ?></th>
								<th><?php echo $lang['Enable or not']; ?></th>
								<th width="100"><?php echo $lang['Operation']; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $modules as $id => $module ) : ?>
								<tr>
									<td>
										<?php $config = CJSON::decode( $module['config'], true ); ?>
										<?php if ( $config['param']['icon'] ): ?>
											<img src="<?php echo Ibos::app()->assetManager->getAssetsUrl( $module['module'] ) . '/image/icon.png'; ?>">
										<?php else: ?>

										<?php endif; ?>
									</td>
									<td><?php echo $module['name']; ?></td>
									<td><?php echo $module['description']; ?></td>
									<td>
										<?php if ( $module['iscore'] == 1 ): echo $lang['System built in']; ?>
										<?php elseif ( $module['iscore'] == 2 ): echo $lang['Core depend']; ?>
										<?php else: ?>
											<input type="checkbox" <?php if ( $module['disabled'] == 0 && Module::getIsEnabled( $module['module'] ) ): ?>checked<?php endif; ?> value="<?php echo $module['module']; ?>" data-toggle="switch" class="visi-hidden">
										<?php endif; ?>
									</td>
									<td>
										<?php if ( !empty( $module['managerUrl'] ) ) : ?>
											<a href="<?php echo $module['managerUrl']; ?>" class="cbtn o-conf" title="<?php echo $lang['Setting']; ?>"></a>
										<?php endif; ?>
										<?php if ( !$module['iscore'] ): ?>
											<a data-module-act="uninstall" module="<?php echo $module['module']; ?>" href="javascript:void(0);" class="cbtn o-trash" title="<?php echo $lang['Uninstall']; ?>"></a>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	// 禁用与启用模块
	(function() {
		var table = $("#installedTable");
		table.find("input[type=checkbox]").on("change", function() {
			var enabled = this.checked, url = '<?php echo $this->createUrl( 'module/status' ); ?>', status = {type: '', module: this.value};
			if (enabled) {
				status.type = 'enabled';
			} else {
				status.type = 'disabled';
			}
			$.post(url, status, function(data) {
				if (data.IsSuccess) {
					Ui.tip('<?php echo Ibos::lang( 'Operation succeed', 'message' ); ?>', 'success');
				} else {
					$.jGrowl('<?php echo Ibos::lang( 'Operation failure', 'message' ); ?>', 'danger');
				}
			}, 'json');
		});
	})();
	$(document).ready(function() {
		// 模块删除前确认动作
		$('[data-module-act="uninstall"]').on('click', function() {
			var module = $(this).attr('module'), url = '<?php echo $this->createUrl( 'module/uninstall' ); ?>', self = $(this);
			$.artDialog({
				title: "<?php echo Ibos::lang( 'Confirm action', 'message' ); ?>",
				content: '<?php echo $lang['Confirm uninstall module']; ?>',
				id: 'confirm_module_act',
				lock: true,
				ok: function() {
					$.post(url, {module: module}, function(data) {
						if (data.IsSuccess) {
							$.jGrowl('<?php echo Ibos::lang( 'Operation succeed', 'message' ); ?>', {theme: 'success'});
							self.parent().parent().remove();
						} else {
							$.jGrowl('<?php echo Ibos::lang( 'Operation failure', 'message' ); ?>', {theme: 'danger'});
						}
					}, 'json');
				}
			});
		});
	});
</script>