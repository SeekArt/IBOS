<?php ?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization_role.css?<?php echo VERHASH; ?>">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt">角色权限管理</h1>
	</div>
	<div>
		<!-- 部门信息 start -->
		<div class="ctb">
			<h2 class="st">角色管理</h2>
			<div class="com-list-wrap clearfix">
				<ul class="db-com-list">
					<?php foreach ( $data as $role ): ?>
						<li data-id="<?php echo $role['roleid']; ?>">
							<p class="xac fsl mb">
								<span><?php echo $role['rolename']; ?></span>
							</p>
							<div class="com-list-content mbs">
								<p class="list-content-title mbs">
									<span class="xwb">成员</span>
									<span class="xwb xcbu"><?php echo count( $role['users'] ); ?></span>
									<span class="xwb">位</span>
								</p>
								<div class="user-list-wrap">
									<ul class="list-inline sub-com-list">
										<?php $count = count( $role['users'] ); ?>
										<?php $i = 0; ?>
										<?php foreach ( $role['users'] as $user ): ?>
											<li>
												<div class="xac">
													<span class="user-avatar-wrap">
                                                        <img src="<?php echo isset( $user['avatar_small'] ) ? $user['avatar_small'] : '' ?>">
													</span>
													<div class="user-name-wrap">
                                                        <span class="fss"><?php echo isset( $user['realname'] ) ? $user['realname'] : '' ?></span>
													</div>
												</div>
											</li>
											<?php $i++; ?>
											<?php if ( $count > 5 && $i == 4 ): ?>
												<li>
													<div class="xac">
														<span class="user-avatar-wrap">
															<a href="<?php echo $this->createUrl( 'role/edit', array( 'op' => 'member', 'id' => $role['roleid'] ) ); ?>">
																<i class="cbtn o-more"></i>
															</a>
														</span>
														<div class="user-name-wrap">
															<span class="fss">更多</span>
														</div>
													</div>
												</li>
												<?php break; ?>
											<?php endif; ?>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
							<div class="clearfix">
								<div class="pull-right">
									<a href="<?php echo $this->createUrl( 'role/edit', array( 'id' => $role['roleid'] ) ); ?>" class="cbtn o-edit" title="编辑"></a>
									<a href="javascript:;" class="cbtn o-trash" title="删除" data-action="delRole"></a>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
					<li class="add-item-li">
						<a href="<?php echo $this->createUrl( 'role/add' ); ?>">
							<div class="add-item-wrap">
									<i class="o-add-role mbs"></i>
									<p class="fsl add-opt-tip">新建角色</p>
							</div>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<script src='<?php echo $assetUrl; ?>/js/organization_role.js?<?php echo VERHASH; ?>'></script>
