<?php
use application\core\utils\Ibos;
use application\modules\user\model\User;
use application\modules\department\utils\Department as DepartmentUtil;
?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization_role.css?<?php echo VERHASH; ?>">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt">管理员管理</h1>
        <ul class="mn">
            <li>
                <a href="/?r=dashboard/roleadmin/index">普通管理员</a>
            </li>
            <li>
                <span>超级管理员</span>
            </li>
        </ul>
    </div>
    <div class="ctb">
		<h2 class="st">超级管理员</h2>
		<div class="alert trick-tip">
            <div class="trick-tip-title">
                <i></i>
                <strong>提示</strong>
            </div>
            <div class="trick-tip-content">
                <ul>
                    <li>超级管理员拥有全部后台权限，不能超过3名。</li>
                </ul>
            </div>
        </div>
        <form method="post" action="javascript:;">
        	<div>
        		<ul class="admin-list mb clearfix" id="admin_list">
        			<?php $count = 0; $uidStr = ""; ?>
        			<?php foreach ($userA as $user): ?>
        				<?php $count++; ?>
        				<?php
        					if ( $count == 1 ){
        						$uidStr = "u_".$user['uid'];
        					} else {
        						$uidStr = $uidStr.",u_".$user['uid'];
        					}
        				?>
						<li id="super_u_<?php echo $user['uid']?>" class="super-list">
							<img src="<?php echo $user['avatar_big']?>" />
							<div class="admin-item-body">
								<span class="fsl"><?php echo $user['realname']?></span>
								<?php if ( $user['uid'] !== Ibos::app()->user->uid ): ?>
									<div class="admin-edit-btn">
										<button class="btn ptm pbm" data-action="superDel" data-id="u_<?php echo $user['uid']?>">删除</button>
									</div>
								<?php endif;?>
							</div>
						</li>
					<?php endforeach; ?>
					<li class="admin-item-add" <?php if ( $count >= 3 ): ?>style="display:none;"<?php endif; ?>>
						<a href="javascript:;" class="rolesuper-add" id="org_super_add">
							<i class="upload-add-icon"></i>
						</a>
					</li>
				</ul>
        	</div>
		    <input type="hidden" name="uid" value="<?php echo $uidStr;?>"/>
		    <button type="submit" class="btn btn-primary btn-large btn-submit" id="submit">提交</button>
		</form>
    </div>
</div>
<div id="member_select_box"></div>
<script type="text/template" id="org_super_tpl">
	<li id="super_<%=id%>" class="super-list">
	<img src="<%=imgurl%>" />
	<div class="admin-item-body">
	<span class="fsl"><%=user%></span>
	<div class="admin-edit-btn">
	<button class="btn ptm pbm" data-action="superDel" data-id="<%=id%>">删除</button>
	</div>
	</div>
	</li>
</script>
<script>
	Ibos.app.s({
		"members": "<?php echo $uidStr;?>".split(","),
		"user": "u_<?php echo Ibos::app()->user->uid;?>"
	});
</script>
<script src='<?php echo $assetUrl; ?>/js/role_super.js?<?php echo VERHASH; ?>'></script>