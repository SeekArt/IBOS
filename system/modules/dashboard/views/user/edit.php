<?php

use application\core\utils\StringUtil;
?>
<div class="ct">
	<div class="clearfix">
		<h1 class="mt">部门人员管理</h1>
	</div>
	<div>
		<!-- 部门信息 start -->
		<div class="ctb">
			<h2 class="st">编辑用户</h2>
			<div class="">
				<form action="<?php echo $this->createUrl( 'user/edit', array( 'userSubmit' => 1 ) ); ?>" method="post" class="user-info-form form-horizontal" id="user_form">
					<div class="control-group">
						<label class="control-label">
							<span><?php echo $lang['Mobile']; ?></span>
							<span class="xcr">*</span>
						</label>
						<div class="controls">
							<input type="text" name="mobile" id="mobile" placeholder="请输入手机号码" value="<?php echo $user['mobile']; ?>" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<span><?php echo $lang['Password']; ?></span>
							<span class="xcr">*</span>
						</label>
						<div class="controls">
							<input type="text" name="password" placeholder="<?php echo $lang['Empty then does not change']; ?>" id="password" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<span><?php echo $lang['Real name']; ?></span>
							<span class="xcr">*</span>
						</label>
						<div class="controls">
							<input type="text" name="realname" placeholder="请输入员工真实姓名" id="realname" value="<?php echo $user['realname']; ?>" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<span><?php echo $lang['Gender']; ?></span>
							<span class="xcr">*</span>
						</label>
						<div class="controls">
							<label class="radio radio-inline">
								<input type="radio" name="gender" value="1" <?php if ( $user['gender'] == '1' ): ?>checked<?php endif; ?> /><?php echo $lang['Male']; ?>
							</label>
							<label class="radio radio-inline">
								<input type="radio" name="gender" value="0" <?php if ( $user['gender'] == '0' ): ?>checked<?php endif; ?> /><?php echo $lang['Female']; ?>
							</label>
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label">
							<span><?php echo $lang['Email']; ?></span>
						</label>
						<div class="controls">
							<input type="text" name="email" id="email" placeholder="请输入邮箱号码" value="<?php echo $user['email']; ?>" />
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Weixin']; ?></label>
						<div class="controls">
							<input type="text" name="weixin" placeholder="请输入微信号码" id="weixin" value="<?php echo $user['weixin']; ?>">
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Jobnumber']; ?></label>
						<div class="controls">
							<input type="text" name="jobnumber" placeholder="请输入工号" id="jobnumber" value="<?php echo $user['jobnumber']; ?>">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<span><?php echo $lang['User name']; ?></span>
						</label>
						<div class="controls">
							<input type="text" name="username" placeholder="请输入用户名" id="username" value="<?php echo $user['username']; ?>" />
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Direct leader']; ?></label>
						<div class="controls">
							<div class="clearfix">
								<div class="pull-left info-list-wrap">
									<input type="text" name="upuid" placeholder="选择一个直属领导" id="user_supervisor" value="<?php echo StringUtil::wrapId( $user['upuid'] ); ?>">
								</div>
								<div class="pull-left mls">
									<a href="javascript:;" class="btn toggle-btn" data-target="#sub_subordinate_wrap">直属下属</a>
								</div>
							</div>
						</div>
					</div>
					<div class="control-group" style="display:none;" id="sub_subordinate_wrap">
						<label for="" class="control-label">直属下属</label>
						<div class="controls">
							<input type="text" name="subordinate" placeholder="可以选择多个下属人员" id="sub_subordinate" value="<?php echo StringUtil::wrapId( $user["subordinate"], 'u' ); ?>">
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Department']; ?></label>
						<div class="controls">
							<div class="clearfix">
								<div class="pull-left info-list-wrap">
									<input type="text" name="deptid" placeholder="选择一个主要部门" id="user_department" value="<?php echo $user['deptid'] == 0 ? 'c_0' : StringUtil::wrapId( $user['deptid'], 'd' ); ?>">
								</div>
								<div class="pull-left mls">
									<a href="javascript:;" class="btn toggle-btn" data-target="#auxiliary_department_wrap">辅助部门</a>
								</div>
							</div>
						</div>
					</div>
					<div class="control-group" <?php if(empty($user['auxiliarydept'])): ?>style="display:none;"<?php endif;?> id="auxiliary_department_wrap">
						<label for="" class="control-label"><?php echo $lang['Ancillary department']; ?></label>
						<div class="controls">
							<input type="text" name="auxiliarydept" placeholder="可以选择多个辅助部门" id="auxiliary_department" value="<?php echo StringUtil::wrapId( $user["auxiliarydept"], 'd' ); ?>">
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Position']; ?></label>
						<div class="controls">
							<div class="clearfix">
								<div class="pull-left info-list-wrap">
									<input type="text" name="positionid" placeholder="选择一个担任岗位" id="user_position" value="<?php echo StringUtil::wrapId( $user['positionid'], 'p' ); ?>">
								</div>
								<div class="pull-left mls">
									<a href="javascript:;" class="btn toggle-btn" data-target="#auxiliary_position_wrap">辅助岗位</a>
								</div>
							</div>
						</div>
					</div>
					<div class="control-group" <?php if(empty($user['auxiliarypos'])): ?>style="display:none;"<?php endif;?> id="auxiliary_position_wrap">
						<label for="" class="control-label"><?php echo $lang['Ancillary position']; ?></label>
						<div class="controls">
							<input type="text" name="auxiliarypos" placeholder="可以选择多个辅助岗位" id="auxiliary_position" value="<?php echo StringUtil::wrapId( $user['auxiliarypos'], 'p' ); ?>">
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label">角色</label>
						<div class="controls">
							<div class="clearfix">
								<div class="pull-left info-list-wrap">
									<input type="hidden" id="role_select" name="roleid" value="<?php echo $user['roleid']; ?>" placeholder="请选择角色"/>
								</div>
								<div class="pull-left mls">
									<a href="javascript:;" class="btn toggle-btn" data-target="#auxiliary_role_wrap">辅助角色</a>
								</div>
							</div>
						</div>
					</div>
					<div class="control-group" <?php if(empty($user['auxiliaryrole'])): ?>style="display:none;"<?php endif;?> id="auxiliary_role_wrap">
						<label for="" class="control-label">辅助角色</label>
						<div class="controls">
							<input type="hidden" id="auxiliary_role_select" name="auxiliaryrole" placeholder="可以选择多个辅助角色" value="<?php echo implode(',', $user['auxiliaryrole']); ?>"/>
						</div>
					</div>
					<div class="control-group">
						<label for="" class="control-label"><?php echo $lang['Account status']; ?></label>
						<div class="controls">
							<label class="radio radio-inline">
								<input type="radio" value="0" <?php if ( $user['status'] == 0 ): ?>checked<?php endif; ?> name="status">
								<?php echo $lang['Enabled']; ?>
							</label>
							<label class="radio radio-inline">
								<input type="radio" value="1" <?php if ( $user['status'] == 1 ): ?>checked<?php endif; ?> name="status">
								<?php echo $lang['Lock']; ?>
							</label>
							<label class="radio radio-inline">
								<input type="radio" value="2" <?php if ( $user['status'] == 2 ): ?>checked<?php endif; ?> name="status">
								<?php echo $lang['Disabled']; ?>
							</label>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"></label>
						<div class="controls">
							<button type="submit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
						</div>
					</div>
					<input type="hidden" name="uid" value="<?php echo $user['uid']; ?>" />
				</form>
			</div>
		</div>
	</div>
</div>
<script>
	Ibos.app.setPageParam({
		'minPasswordLength': "<?php echo $passwordLength ?>",
		'maxPasswordLength': 32,
		'passwordRegex': "<?php echo $preg ?>"
	});

	// 角色初选择框始化
	$("#role_select").ibosSelect({
		data: [<?php foreach($roles as $role):?>{text:"<?php echo $role['rolename']; ?>", id: <?php echo $role['roleid']; ?>},<?php endforeach;?>],
		width: '100%',
		multiple: false,
		placeholder: "请选择角色"
	});

	// 辅助角色初始化
	$("#auxiliary_role_select").ibosSelect({
		data: [<?php foreach($roles as $role):?>{text:"<?php echo $role['rolename']; ?>", id: <?php echo $role['roleid']; ?>},<?php endforeach;?>],
		width: '100%',
		multiple: true,
		placeholder: "可以选择多个辅助角色"
	});
</script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/organization.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_user_edit.js?<?php echo VERHASH; ?>'></script>
