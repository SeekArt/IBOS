<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['User group setup']; ?></h1>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'usergroup/index' ); ?>" method="post" class="form-horizontal">
			<!-- 用户组 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['User group']; ?></h2>
				<table class="table table-bordered table-striped table-operate" id="user_group_table">
					<thead>
						<tr>
							<th>
								<?php echo $lang['Prefix']; ?> 
							</th>
							<th width="350">
								<?php echo $lang['Integration range']; ?>
							</th>
						</tr>
					</thead>
					<tbody id="user_group_body">
						<?php foreach ( $data as $value ): ?>
							<tr id="group_<?php echo $value['gid']; ?>">
								<td>
									<div class="row">
										<div class="span6">
											<input type="text" value="<?php echo $value['title'] ?>" name="groups[<?php echo $value['gid'] ?>][title]" class="input-small">
										</div>
										<?php if ( $value['system'] != 1 ): ?>
											<div class="span6">
												<a href="javascript:void(0);" data-id="<?php echo $value['gid']; ?>" data-act="remove" class="cbtn o-trash"></a>
											</div>
										<?php endif; ?>
									</div>
								</td>
								<td>
									<div class="row">
										<div class="span5">
											<input type="text" class="input-small" value="<?php echo $value['creditshigher']; ?>" name="groups[<?php echo $value['gid'] ?>][creditshigher]">
										</div>
										<div class="span1">~</div>
										<div class="span5">
											<input type="text" class="input-small disabled" value="<?php echo $value['creditslower']; ?>" name="groups[<?php echo $value['gid'] ?>][creditslower]" disabled />
										</div>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<tbody>
						<tr>
							<td colspan="4">
								<a href="javascript:void(0);" class="operate-group" data-act="add">
									<i class="cbtn o-plus"></i>
									<?php echo $lang['Add new user group']; ?> 
								</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div>
				<input type="hidden" name="removeId" id="removeId" />
				<button type="submit" name="userGroupSubmit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
			</div>
		</form>
	</div>
</div>
<script type="text/ibos-template" id="new_usergroup">
			<tr id="group_<%=id%>">
				<td>
					<div class="row">
						<div class="span6">
							<input type="text" name="newgroups[title][]" class="input-small">
						</div>
						<div class="span6">
							<a href="javascript:void(0);" data-id="<%=id%>" data-act="newRemove" class="cbtn o-trash"></a>
						</div>
					</div>
				</td>
				<td>
					<div class="row">
						<div class="span5">
							<input type="text" class="input-small" name="newgroups[creditshigher][]">
						</div>
					</div>
				</td>
			</tr>
</script>
<script>
	(function() {
		var insertNode = $('#user_group_body');
		// 用户组表格设置js
		$("#user_group_table").on("click", "a", function() {
			var self = $(this),
					act = self.attr('data-act');
			switch (act) {
				// 增加一条用户组记录
				case 'add' :
					var d = new Date(), newNode = $.template('new_usergroup', {id: d.getTime()});
					insertNode.append(newNode);
					break;
					// 删除掉已存在的记录，记录删除的id待提交处理
				case 'remove' :
					var delId = self.attr('data-id'), removeIdObj = $('#removeId');
					removeId = removeIdObj.val();
					removeIdSplit = removeId.split(',');
					removeIdSplit.push(delId);
					removeIdObj.val(removeIdSplit.join());
					$('#group_' + delId).remove();
					break;
					// 删除掉当前新增但还没保存的记录
				case 'newRemove' :
					var delId = self.attr('data-id');
					$('#group_' + delId).remove();
					break;
			}
		});
	})();
</script>