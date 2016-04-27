<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization_role.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/dataTable/css/jquery.dataTables_ibos.min.css?<?php echo VERHASH; ?>">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt">岗位管理</h1>
	</div>
	<div>
		<!-- 部门信息 start -->
		<div class="ctb">
			<h2 class="st">岗位管理</h2>
			<div class="mc clearfix">
				<div class="aside">
					<div class="fill-ss">
						<a href="javascript:;" class="btn btn-warning add-dept-btn" data-action="addType">添加分类</a>
					</div>
					<div class="ztree-wrap">
						<ul id="ptree" class="ztree position-ztree">
						</ul>
					</div>
				</div>
				<div class="mcr">
					<div class="page-list">
						<div class="page-list-header">
							<div class="btn-toolbar pull-left">
								<div class="btn-group">
									<button type="button" data-action="addPosition" class="btn btn-primary" id="add_position"><?php echo $lang['Add position']; ?></button>
								</div>
							</div>
							<div class="btn-group pull-left mlm">
								<button type="button" data-action="removePositions" class="btn"><?php echo $lang['Delete']; ?></button>
							</div>
							<form method="post" action="javascript:;">
								<div class="search pull-right span4">
									<input type="text" name="keyword" placeholder="<?php echo $lang['Position search tip']; ?>" id="mn_search" nofocus />
									<a href="javascript:;">search</a>
								</div>
								<input type="hidden" name="search" value="1" />
								<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
							</form>
						</div>
						<div class="page-list-mainer">
							<table class="table table-striped table-hover org-positon-table" id="org_position_table">
								<thead>
									<tr>
										<th width="20">
											<label class="checkbox">
												<input type="checkbox" data-name="positionid">
											</label>
										</th>
										<th><?php echo $lang['Position name']; ?></th>
										<th><?php echo $lang['Position category']; ?></th>
										<th width="100"><?php echo $lang['Position users']; ?></th>
										<th width="100"><?php echo $lang['Operation']; ?></th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="add_type_dialog" style="display:none;">
	<div class="fill-nn">
		<form action="javascript:;" class="form-horizontal form-compact" style="width: 300px;">
			<div class="control-group">
				<label class="control-label">分类名称</label>
				<div class="controls">
					<input type="text" class="input-small" id="tpye_name"></div>
			</div>
			<div class="control-group">
				<label class="control-label">父目录</label>
				<div class="controls">
					<select class="input-small" id="dep_pid">
						<option value="0" id="dep_pid_first">无</option>
						<?php echo $category; ?>
					</select>
				</div>
			</div>
		</form>
	</div>
</div>
<div id="edit_type_dialog" style="display:none;">
	<div class="fill-nn">
		<form action="javascript:;" class="form-horizontal form-compact" style="width: 300px;">
			<div class="control-group">
				<label class="control-label">分类名称</label>
				<div class="controls">
					<input type="text" class="input-small" id="edit_tpye_name"></div>
			</div>
			<div class="control-group">
				<label class="control-label">父目录</label>
				<div class="controls">
					<select class="input-small" id="pid_select">
						<option value="0" id="pid_select_first">无</option>
						<?php echo $category; ?>
					</select>
				</div>
			</div>
		</form>
	</div>
</div>
<script src="<?php echo STATICURL; ?>/js/lib/dataTable/js/jquery.dataTables.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/organization.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_position_index.js?<?php echo VERHASH; ?>'></script>

