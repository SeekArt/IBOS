<?php 
use application\core\utils\Convert;
use application\modules\user\model\User;
?>

<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Sms setting']; ?></h1>
		<ul class="mn">
			<!--<li>
				<a href="<?php echo $this->createUrl( 'sms/setup' ); ?>"><?php echo $lang['Sms setup']; ?></a>
			</li>-->
			<li>
				<span><?php echo $lang['Sms sent manager']; ?></span>
			</li>
			<!--<li>
				<a href="<?php echo $this->createUrl( 'sms/access' ); ?>"><?php echo $lang['Sms access']; ?></a>
			</li>-->
		</ul>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'sms/manager', array( 'type' => 'search' ) ); ?>" method="post" id="sms_search_form">
			<!-- 短信发送管理 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Sms sent manager']; ?></h2>
				<div class="page-list">
					<div class="page-list-header">
						<div class="row">	
							<div class="span9">
								<button type="button" id="exportsms" class="btn"><?php echo $lang['Export']; ?></button>
								<button type="button" id="delsms" class="btn"><?php echo $lang['Delete selected sms']; ?></button>
								<?php if ( $search ): ?>
									<?php echo $lang['Match conditions']; ?>：<?php echo $count; ?>,<a href="<?php echo $this->createUrl( 'sms/manager' ); ?>"><?php echo $lang['Return list']; ?></a>
								<?php endif; ?>
							</div>
							<div class="span3">
								<div class="search search-config">
									<input type="text" name="keyword" placeholder="Search" id="sms_search">
									<input type="submit" name="submit" class="hide" >
									<a href="javascript:;">search</a>
								</div>
							</div>
						</div>
					</div>
					<div class="page-list-mainer">
						<table class="table table-striped" id="sms_manage_table">
							<thead>
								<tr>
									<th width="20">
										<label class="checkbox" for="checkbox_0"><span class="icon"></span><span class="icon-to-fade"></span>
											<input type="checkbox" value="" data-name="sms[]" id="checkbox_0">
										</label>
									</th>
									<th width="80"><?php echo $lang['User']; ?></th>
									<th width="80"><?php echo $lang['To user']; ?></th>
									<th><?php echo $lang['Content']; ?></th>
									<th width="120"><?php echo $lang['Result']; ?></th>
									<th width="120"><?php echo $lang['Send time']; ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $list as $data ): ?>
									<?php
									$toUser = User::model()->fetchByUid( $data['touid'] );
									if ( $data['uid'] ) {
										$user = User::model()->fetchByUid( $data['uid'] );
										$userName = $user['realname'];
									} else {
										$userName = $lang['System'];
									}
									?>
									<tr id="list_tr_<?php echo $data['id']; ?>">
										<td>
											<label class="checkbox" for="checkbox_<?php echo $data['id']; ?>"><span class="icon"></span><span class="icon-to-fade"></span>
												<input type="checkbox" value="<?php echo $data['id']; ?>" id="checkbox_<?php echo $data['id']; ?>" name="sms[]">
											</label>
										</td>
										<td><?php echo $userName; ?></td>
										<td><?php echo $toUser['realname']; ?></td>
										<td><?php echo $data['content']; ?></td>
										<td><?php echo $data['return'] ? $lang['Sent succeed'] : $lang['Sent failure']; ?></td>
										<td><?php echo Convert::formatDate( $data['ctime'], 'u' ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<div class="page-list-footer"><?php $this->widget( 'application\core\widgets\Page', array( 'pages' => $pages ) ); ?></div>
				</div>
			</div>
		</form>
	</div>
</div>
<div id="d_sms_search" style="display:none;">
	<form action="" id="d_sms_search_form" class="form-horizontal form-narrow">
		<div class="control-group">
			<label for="" class="control-label"><?php echo $lang['Message delivery status']; ?></label>
			<div class="controls">
				<div id="select_type" class="btn-group btn-group-justified" data-toggle="buttons-checkbox">
					<a href="#" data-id="1" class="btn"><?php echo $lang['Sent succeed'] ?></a>
					<a href="#" data-id="0" class="btn"><?php echo $lang['Sent failure'] ?></a>
					<a href="#" data-id="" class="btn"><?php echo $lang['All of it']; ?></a>
				</div>
				<input type="hidden" name="searchtype" id="type" />
			</div>
		</div>
		<div class="control-group">
			<label for="" class="control-label"><?php echo $lang['Sent time scope']; ?></label>
			<div class="controls">
				<div class="row">
					<div class="span6">
						<div class="datepicker" id="date_start">
							<a href="javascript:;" class="datepicker-btn"></a>
							<input type="text" class="datepicker-input" name="begin">
						</div>
					</div>
					<div class="span6">
						<div class="datepicker" id="date_end">
							<a href="javascript:;" class="datepicker-btn"></a>
							<input type="text" class="datepicker-input" name="end">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang['Sender']; ?></label>
			<div class="controls">
				<input type="text" name="sender" id="sender" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang['Recipient phone number']; ?></label>
			<div class="controls">
				<input type="text" name="recnumber">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label"><?php echo $lang['Content']; ?></label>
			<div class="controls">
				<textarea name="content" rows="3"></textarea>
			</div>
		</div>
	</form>
</div>
<script>
	(function() {
		$("#date_start").datepicker({target: $("#date_end")});
		$('#sender').userSelect({
			data: Ibos.data.get("user"),
			type: "user",
			maximumSelectionSize: 1
		});
		function removeRows(ids) {
			var arr = ids.split(',');
			for (var i = 0, len = arr.length; i < len; i++) {
				$('#list_tr_' + arr[i]).remove();
			}
		}
		$('#exportsms').on('click', function() {
			var val = U.getCheckedValue('sms[]');
			if ($.trim(val) !== '') {
				var url = '<?php echo $this->createUrl( 'sms/export' ); ?>';
				url += '&id=' + val;
				window.location.href = url;
			} else {
				Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
			}
		});
		$('#delsms').on('click', function() {
			var val = U.getCheckedValue('sms[]');
			if ($.trim(val) !== '') {
				Ui.confirm('<?php echo $lang['SMS del confirm']; ?>', function() {
					var url = '<?php echo $this->createUrl( 'sms/del' ); ?>';
					$.get(url, {id: val}, function(data) {
						if (data.isSuccess) {
							removeRows(val);
							Ui.tip(U.lang("DELETE_SUCCESS"));
						} else {
							Ui.tip(U.lang("DELETE_FAILED"), 'danger');
						}
					}, 'json');
				});
			} else {
				Ui.tip(U.lang("SELECT_AT_LEAST_ONE_ITEM"), 'danger');
			}
		});
		var smsSearchDialog = Dom.byId('d_sms_search'),
				smsSearchDialogOptions = {
					title: "<?php echo $lang['SMS advanced search']; ?>",
					content: smsSearchDialog,
					width: 500,
					ok: function() {
						$('#type').val('');
						$('#select_type').find('a').each(function() {
							var id = $(this).data('id');
							if ($(this).hasClass('active')) {
								$('#type').val($('#type').val() + id);
							}
						});
						var url = '<?php echo $this->createUrl( 'sms/manager', array( 'type' => 'search' ) ); ?>';
						var param = $('#d_sms_search_form').serializeArray();
						window.location.href = url + '&' + $.param(param);
					},
					cancel: true
				};
		$("#sms_search").search(true, function() {
			$.artDialog(smsSearchDialogOptions);
		});
	})();
</script>
