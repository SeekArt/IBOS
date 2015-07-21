<link href="<?php echo $this->getAssetUrl(); ?>/css/approval.css" type="text/css" rel="stylesheet" />
<div class="ct sp-ct">
	<div class="clearfix">
		<h1 class="mt">修改审批流程</h1>
	</div>
	<div>
		<form id="process_setting_form" action="<?php echo $this->createUrl( 'approval/edit' ); ?>" method="post" class="form-horizontal process-setting-form">
			<div class="ctb ps-type-title">
				<h2 class="st">审批流程</h2>
			</div>
			<div class="control-group">
				<label class="control-label">审批流程名称</label>
				<div class="controls">
					<input name="name" type="text" id="approval_name" value="<?php echo $approval['name']; ?>">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">审核等级</label>
				<div class="controls">
					<select id="approve_level_select" name="level">
						<option value="1" <?php if ( $approval['level'] == 1 ): ?>selected<?php endif; ?>>一级审核</option>
						<option value="2" <?php if ( $approval['level'] == 2 ): ?>selected<?php endif; ?>>二级审核</option>
						<option value="3" <?php if ( $approval['level'] == 3 ): ?>selected<?php endif; ?>>三级审核</option>
						<option value="4" <?php if ( $approval['level'] == 4 ): ?>selected<?php endif; ?>>四级审核</option>
						<option value="5" <?php if ( $approval['level'] == 5 ): ?>selected<?php endif; ?>>五级审核</option>
					</select>
				</div>
			</div>
			<div class="pf-select-area" id="pf_select_area">
				<div class="control-group">
					<label class="control-label">一级审核人员</label>
					<div class="controls">
						<input type="text" class="approval-select" name="level1" id="level_one_auditor" value="<?php echo $approval['level1']; ?>">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">二级审核人员</label>
					<div class="controls">
						<input type="text" class="approval-select" name="level2" id="level_two_auditor" value="<?php echo $approval['level2']; ?>">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">三级审核人员</label>
					<div class="controls">
						<input type="text" class="approval-select" name="level3" id="level_three_auditor" value="<?php echo $approval['level3']; ?>">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">四级审核人员</label>
					<div class="controls">
						<input type="text" class="approval-select" name="level4" id="level_four_auditor" value="<?php echo $approval['level4']; ?>">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">五级审核人员</label>
					<div class="controls">
						<input type="text" class="approval-select" name="level5" id="level_five_auditor" value="<?php echo $approval['level5']; ?>">
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">免审核人员</label>
				<div class="controls">
					<input type="text" name="free" id="exempt_auditor" value="<?php echo $approval['free']; ?>">
				</div>
			</div>
			<div class="control-group mbl">
				<lebel class="control-label">描述</lebel>
				<div class="controls">
					<textarea name="desc" id="" cols="30" rows="5"><?php echo $approval['desc']; ?></textarea>
				</div>
			</div>
			<div class="control-group">
				<lebel class="control-label"></lebel>
				<div class="controls">
					<button type="submit" id="process_submit" class="btn btn-large btn-submit btn-primary">提交</button>
					<input type="hidden" name="id" value="<?php echo $approval['id']; ?>">
					<input type="hidden" name="approvalSubmit" value="1">
				</div>
			</div>
		</form>
	</div>
</div>
<script src="<?php echo $this->getAssetUrl(); ?>/js/approval.js"></script>
<script>
	$(function(){
		var $select = $(".pf-select-area .control-group");
		var val = $("#approve_level_select").find('option:selected').val();
		process.switchDisplay($select, val);
	});
</script>