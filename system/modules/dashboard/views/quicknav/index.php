<?php 
use application\core\utils\IBOS;
?>
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Quicknav setting']; ?></h1>
	</div>
	<div>
		<form action="javascript:;" class="form-horizontal">
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Quicknav setting']; ?></h2>
				<div>
					<table id="quicknav_table" class="table table-bordered table-striped table-operate">
						<thead>
							<tr>
								<th width="80"><?php echo $lang['Icon']; ?></th>
								<th><?php echo $lang['The application name']; ?></th>
								<th><?php echo $lang['The link address']; ?></th>
								<th width="80"><?php echo $lang['Enabled']; ?></th>
								<th width="80"><?php echo $lang['New window open']; ?></th>
								<th width="80"><?php echo $lang['Operation']; ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $menus as $menu ): ?>
								<tr>
									<td>
										<img src="<?php echo $menu['icon']; ?>" alt="<?php $menu['name']; ?>" width="64" height="64">
									</td>
                                    <td><?php echo $menu['name']; ?></td>
									<td><?php echo $menu['url']; ?></td>
									<td>
										<input type="checkbox" name="enable[]" data-id="<?php echo $menu['id']; ?>" class="enabled-status" value="1" data-toggle="switch" <?php if ( $menu['disabled'] == '0' ): ?>checked<?php endif; ?>>
									</td>
									<td>
										<input type="checkbox" name="newwindow[]" data-id="<?php echo $menu['id']; ?>" class="newwindow-status" value="1" data-toggle="switch" <?php if ( $menu['openway'] == '0' ): ?>checked<?php endif; ?>>
									</td>
									<td>
										<?php if ( $menu['iscustom'] ): ?>
											<a href="<?php echo $this->createUrl( 'quicknav/edit', array( 'id' => $menu['id'] ) ); ?>" class="cbtn o-edit" title="<?php echo IBOS::lang('edit'); ?>"></a>
											<a href="javascript:;" class="cbtn o-trash" title="<?php echo IBOS::lang('delete'); ?>" data-action="removeQuicknav" data-param='{"id": <?php echo $menu['id']; ?>}'></a>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot>

							<tr>
								<td colspan="6">
									<a href="<?php echo $this->createUrl( 'quicknav/add' ); ?>" class="operate-group">
										<i class="cbtn o-plus"></i>
										<?php echo $lang['Add quicknav']; ?> 
									</a>
								</td>
							</tr>

						</tfoot>
					</table>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	$(function() {

		// 启用、禁用快捷导航
		var table = $("#quicknav_table");
		table.find(".enabled-status").on("change", function() {
			var enabled = this.checked, url = Ibos.app.url('dashboard/quicknav/edit', {'op': 'changeenabled'}), status = {type: '', id: $(this).attr('data-id')};
			if (enabled) {
				status.type = 'enabled';
			} else {
				status.type = 'disabled';
			}
			$.post(url, status, function(res) {
				if (res.isSuccess) {
					Ui.tip(res.msg, 'success');
				} else {
					Ui.tip(res.msg, 'warning');
				}
			}, 'json');
		});

		// 是否开启新窗口打开
		table.find(".newwindow-status").on("change", function() {
			var enabled = this.checked, url = Ibos.app.url('dashboard/quicknav/edit', {'op': 'changeopenway'}), status = {type: '', id: $(this).attr('data-id')};
			if (enabled) {
				status.type = 'enabled';
			} else {
				status.type = 'disabled';
			}
			$.post(url, status, function(res) {
				if (res.isSuccess) {
					Ui.tip(res.msg, 'success');
				} else {
					Ui.tip(res.msg, 'warning');
				}
			}, 'json');
		});

		// 删除快捷导航
		Ibos.evt.add({
			"removeQuicknav": function(param, elem) {
				Ui.confirm(U.lang("DB.REMOVE_QUICKNAV_CONFIRM"), function() {
					$.post(Ibos.app.url('dashboard/quicknav/del'), param, function(res) {
						if (res.isSuccess) {
							$(elem).closest("tr").remove();
							Ui.tip("@OPERATION_SUCCESS")
						}
					}, "json");
				})
			}
		})


	});
</script>