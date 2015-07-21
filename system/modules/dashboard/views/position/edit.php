<?php ?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization_role.css?<?php echo VERHASH; ?>">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt">角色权限管理</h1>
	</div>
	<div>
		<!-- 部门信息 start -->
		<div class="ctb">
			<h2 class="st">角色管理</h2>
			<div>
				<div class="btn-group mb">
					<a href="javascript:;" class="btn active">岗位设置</a>
					<a href="<?php echo $this->createUrl( 'position/edit', array( 'op' => 'member', 'id' => $id ) ); ?>" class="btn">岗位成员管理</a>
				</div>
				<div class="">
					<form action="<?php echo $this->createUrl( 'position/edit' ); ?>" method="post" id="position_add_form" class="form-horizontal user-info-form">
						<div class="control-group">
							<label class="control-label">
								<span>排序序号</span>
							</label>
							<div class="controls">
								<input type="text" name="sort" placeholder="请输入排序号" id="order_id" value="<?php echo $pos['sort']; ?>" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">
								<span>岗位名称</span>
							</label>
							<div class="controls">
								<input type="text" name="posname" placeholder="请输岗位名称" id="pos_name" value="<?php echo $pos['posname']; ?>" />
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">
								<span>岗位分类</span>
							</label>
							<div class="controls">
								<select name="catid">
									<?php echo $category; ?>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">
							</label>
							<div class="controls">
								<button type="submit" class="btn btn-large btn-primary">提交</button>
							</div>
						</div>
						<input type="hidden" name="id" value="<?php echo $id; ?>" />
						<input type="hidden" name="posSubmit" value="1" />
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript">
	$.formValidator.initConfig({formID: "position_add_form", errorFocus: true});

	// 角色名称
	$("#order_id").formValidator()
			.regexValidator({
				regExp: "notempty",
				dataType: "enum",
				onError: "排序序号不能为空"
			});

	$("#pos_name").formValidator()
			.regexValidator({
				regExp: "notempty",
				dataType: "enum",
				onError: "岗位名称不能为空"
			});
</script>


