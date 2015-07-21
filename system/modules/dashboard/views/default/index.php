<?php

use application\core\utils\IBOS;
?>
<!doctype html>
<!-- <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"> -->
<html lang="en">
	<head>
		<meta charset="<?php echo CHARSET; ?>">
		<title><?php echo $lang['Home page']; ?></title>
		<!-- load css -->
		<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/base.css?<?php echo VERHASH; ?>">
		<!-- IE8 fixed -->
		<!--[if lt IE 9]>
			<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/iefix.css?<?php echo VERHASH; ?>">
		<![endif]-->
		<!-- private css -->
		<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/index.css?<?php echo VERHASH; ?>">
		<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH; ?>">
	</head>
	<body>
		<script>
			var adjustSidebarWidth = function() {
				document.body.className = (window.innerWidth || document.documentElement.clientWidth) > 1150 ? "db-widen" : "";
			}
			adjustSidebarWidth();
			window.onresize = adjustSidebarWidth;
		</script>
		<!-- <div style="height: 100%"> -->
		<div class="db-map" id="db_map" style="display:none;">
			<ul class="dbm-main-list">

				<!-- 首页 -->
				<li class="dbm-main-item clearfix">
					<div class="dbm-main-item-name"><?php echo $lang['Home page']; ?></div>
					<ul class="dbm-sub-list">
						<li>
							<a href="<?php echo $index['index']; ?>" target="main" title=""><?php echo $lang['Management center home page']; ?></a>
						</li>
						<!--<li>
							<a href="<?php echo $index['status']; ?>" target="main" title=""><?php echo $lang['System state']; ?></a>
						</li>-->
					</ul>
				</li>

				<!-- 全局 -->
				<li class="dbm-main-item clearfix">
					<div class="dbm-main-item-name"><?php echo $lang['Global']; ?></div>
					<ul class="dbm-sub-list">
						<li>
							<a href="<?php echo $global['unit']; ?>" target="main" title="<?php echo $lang['Unit management']; ?>">
								<?php echo $lang['Unit management']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['credit']; ?>" target="main" title="<?php echo $lang['Integral set']; ?>">
								<?php echo $lang['Integral set']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['userGroup']; ?>" target="main" title="<?php echo $lang['User group']; ?>">
								<?php echo $lang['User group']; ?>
							</a>
						</li>
						<?php if ( LOCAL ): ?>
							<li>
								<a href="<?php echo $global['optimize']; ?>" target="main" title="<?php echo $lang['Performance optimization']; ?>">
									<?php echo $lang['Performance optimization']; ?>
								</a>
							</li>
						<?php endif; ?>
						<li>
							<a href="<?php echo $global['date']; ?>" target="main" title="<?php echo $lang['Date setup']; ?>">
								<?php echo $lang['Date setup']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['upload']; ?>" target="main" title="<?php echo $lang['Upload setting']; ?>">
								<?php echo $lang['Upload setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['sms']; ?>" target="main" title="<?php echo $lang['Sms setting']; ?>">
								<?php echo $lang['Sms setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['im']; ?>" target="main" title="<?php echo $lang['Instant messaging binding']; ?>">
								<?php echo $lang['Instant messaging binding']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['sysCode']; ?>" target="main" title="<?php echo $lang['System code setting']; ?>">
								<?php echo $lang['System code setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['email']; ?>" target="main" title="<?php echo $lang['Email setting']; ?>">
								<?php echo $lang['Email setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['security']; ?>" target="main" title="<?php echo $lang['Security setting']; ?>">
								<?php echo $lang['Security setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['sysStamp']; ?>" target="main" tile="<?php echo $lang['System stamp']; ?>">
								<?php echo $lang['System stamp']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['approval']; ?>" target="main" tile="<?php echo $lang['Approval process']; ?>">
								<?php echo $lang['Approval process']; ?>
							</a>
						</li>
                        <li>
							<a href="<?php echo $global['notify']; ?>" target="main" tile="<?php echo $lang['Notify setup']; ?>">
								<?php echo $lang['Notify setup']; ?>
							</a>
						</li>
					</ul>
				</li>

				<!-- 界面 -->
				<li class="dbm-main-item">
					<div class="dbm-main-item-name"><?php echo $lang['Interface']; ?></div>
					<ul class="dbm-sub-list">
						<li>
							<a href="<?php echo $interface['nav']; ?>" target="main" title="<?php echo $lang['Navigation setting']; ?>">
								<?php echo $lang['Navigation setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $interface['quicknav']; ?>" target="main" title="<?php echo $lang['Quicknav setting']; ?>">
								<?php echo $lang['Quicknav setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $interface['login']; ?>" target="main" title="<?php echo $lang['Login page setting']; ?>">
								<?php echo $lang['Login page setting']; ?>
							</a>
						</li>
					</ul>
				</li>

				<!-- 模块 -->
				<li class="dbm-main-item">
					<div class="dbm-main-item-name"><?php echo $lang['Module']; ?></div>
					<ul class="dbm-sub-list">
						<li>
							<a href="<?php echo $module['module']; ?>" target="main" title="<?php echo $lang['Module manager']; ?>">
								<?php echo $lang['Module manager']; ?>
							</a>
						</li>
						<?php foreach ( $moduleMenu as $id => $menu ): ?>
							<li>
								<a href="<?php echo IBOS::app()->urlManager->createUrl( $menu['m'] . '/' . $menu['c'] . '/' . $menu['a'] ); ?>" target="main" title="<?php echo $menu['name']; ?>">
									<?php echo $menu['name']; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>

				<!-- 管理 -->
				<li class="dbm-main-item">
					<div class="dbm-main-item-name"><?php echo $lang['Manage']; ?></div>
					<ul class="dbm-sub-list">
						<li>
							<a href="<?php echo $manager['update']; ?>" target="main" title="<?php echo $lang['Update cache']; ?>">
								<?php echo $lang['Update cache']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $manager['announcement']; ?>" target="main" title="<?php echo $lang['System announcement']; ?>">
								<?php echo $lang['System announcement']; ?>
							</a>
						</li>
						<?php if ( LOCAL ): ?>
							<li>
								<a href="<?php echo $manager['database']; ?>" target="main" title="<?php echo $lang['Database']; ?>">
									<?php echo $lang['Database']; ?>
								</a>
							</li>
						<?php endif; ?>
						<!--<li>
							<a href="<?php echo $manager['split']; ?>" target="main" title="<?php echo $lang['Table archive']; ?>">
						<?php echo $lang['Table archive']; ?>
							</a>
						</li>-->
						<li>
							<a href="<?php echo $manager['cron']; ?>" target="main" title="<?php echo $lang['Scheduled task']; ?>">
								<?php echo $lang['Scheduled task']; ?>
							</a>
						</li>
						<!--<li>
							<a href="<?php echo $manager['fileperms']; ?>" target="main" title="<?php echo $lang['Check file permissions']; ?>">
						<?php echo $lang['Check file permissions']; ?>
							</a>
						</li>-->
						<?php if ( LOCAL ): ?>
							<li>
								<a href="<?php echo $manager['upgrade']; ?>" target="main" title="<?php echo $lang['Online upgrade']; ?>">
									<?php echo $lang['Online upgrade']; ?>
								</a>
							</li>
						<?php endif; ?>
					</ul>
				</li>

				<!-- 服务 -->
				<li class="dbm-main-item clearfix">
					<div class="dbm-main-item-name"><?php echo $lang['Service'] ?></div>
					<ul class="dbm-sub-list">
						<li>
							<a href="<?php echo $service['service']; ?>" target="main" title="IBOS <?php echo $lang['Shop']; ?>">Ibos <?php echo $lang['Shop']; ?></a>
						</li>
					</ul>
				</li>
			</ul>
		</div>

		<div class="header">
			<div class="logo" id="logo">
				<h2 class="logo-bg">IBOS</h2>
				<a href="javascript:;" id="db_map_ctrl" class="cbtn db-map-ctrl"></a>
			</div>
			<div class="hdbar clearfix" id="bar">
				<form method="post" autocomplete="off" target="main" action="<?php echo $this->createUrl( 'default/search' ); ?>">
					<div class="dbsearch">
						<input type="text" name="keyword" placeholder="<?php echo $lang['Search']; ?>" x-webkit-speech="" speech="" class="input-small">
						<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
					</div>
				</form>
				<div class="user-info pull-right">
					<span class="user-name">
						<a href="<?php echo IBOS::app()->user->space_url; ?>" target="_blank"><img width="30" height="30" class="radius msep" src="<?php echo IBOS::app()->user->avatar_middle; ?>" title="<?php echo IBOS::app()->user->realname; ?>"></a>
						<strong><?php echo IBOS::app()->user->realname; ?></strong>
					</span>
					<a href="<?php echo IBOS::app()->urlManager->createUrl( '/' ); ?>" target="_blank" class="msep cbtn o-homepage" title="<?php echo IBOS::lang( 'Return to home page' ); ?>"></a>
					<a href="<?php echo $this->createUrl( 'default/logout', array( 'formhash' => FORMHASH ) ); ?>" class="cbtn o-logout" title="<?php echo $lang['Logout']; ?>"></a>
				</div>
			</div>
		</div>

		<div class="mainer" id="mainer">
			<div class="aside" id="aside">
				<div class="main-nav">
					<ul id="main_nav">
						<li class="active">
							<a href="<?php echo $index['index']; ?>" target="main" data-href="#db_index_list" id="db_index" class="db-index"><?php echo $lang['Home page']; ?></a>
						</li>
						<li>
							<a href="<?php echo $co['Cobinding']; ?>" target="main" data-href="#db_binding_list" id="db_binding" class="db-binding"><?php echo $lang['binding']; ?></a>
						</li>
						<li>
							<a href="<?php echo $global['unit']; ?>" target="main" data-href="#db_global_list" id="db_global" class="db-global"><?php echo $lang['Global']; ?></a>
						</li>
						<li>
							<a href="<?php echo $organization['user']; ?>" target="main" data-href="#db_user_list" id="db_global" class="db-user"><?php echo $lang['User']; ?></a>
						</li>
						<li>
							<a href="<?php echo $interface['nav']; ?>" target="main" data-href="#db_interface_list" id="db_interface" class="db-interface"><?php echo $lang['Interface']; ?></a>
						</li>
						<li>
							<a href="<?php echo $module['module']; ?>" target="main" data-href="#db_module_list" id="db_module" class="db-module"><?php echo $lang['Module']; ?></a>
						</li>
						<li>
							<a href="<?php echo $manager['update']; ?>" target="main" data-href="#db_manage_list" id="db_manage" class="db-manage"><?php echo $lang['Manage']; ?></a>
						</li>
						<li>
							<a href="<?php echo $service['service']; ?>"  target="main" data-href="#db_services_list" id="db_services" class="db-services"><?php echo $lang['Service']; ?></a>
						</li>
					</ul>
				</div>
				<div class="sub-nav" id="sub_nav" style="overflow: auto">
					<!-- 首页 -->
					<ul id="db_index_list">
						<li class="active">
							<a href="<?php echo $index['index']; ?>" target="main" title="<?php echo $lang['Management center home page']; ?>">
								<?php echo $lang['Management center home page']; ?>
							</a>
						</li>
						<!--<li>
							<a href="<?php echo $index['status']; ?>" target="main" title="<?php echo $lang['System state']; ?>">
						<?php echo $lang['System state']; ?>
							</a>
						</li>-->
					</ul>
					<!-- 绑定 -->
					<ul id="db_binding_list" style="display:none;">
						<li class="active">
							<a href="<?php echo $co['Cobinding']; ?>" target="main" title="<?php echo $lang['Co binding']; ?>">
								<?php echo $lang['Co binding'] ?>
							</a>
						</li>
						<li class="active">
							<a href="<?php echo $weixin['wxBinding']; ?>" target="main" title="<?php echo $lang['Weixin binding']; ?>">
								<?php echo $lang['Weixin binding'] ?>
							</a>
						</li>
					</ul>
					<!-- 全局 -->
					<ul id="db_global_list" style="display:none;">
						<li class="active">
							<a href="<?php echo $global['unit']; ?>" target="main" title="<?php echo $lang['Unit management']; ?>">
								<?php echo $lang['Unit management']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['credit']; ?>" target="main" title="<?php echo $lang['Integral set']; ?>">
								<?php echo $lang['Integral set']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['userGroup']; ?>" target="main" title="<?php echo $lang['User group']; ?>">
								<?php echo $lang['User group']; ?>
							</a>
						</li>
						<?php if ( LOCAL ): ?>
							<li>
								<a href="<?php echo $global['optimize']; ?>" target="main" title="<?php echo $lang['Performance optimization']; ?>">
									<?php echo $lang['Performance optimization']; ?>
								</a>
							</li>
						<?php endif; ?>
						<li>
							<a href="<?php echo $global['date']; ?>" target="main" title="<?php echo $lang['Date setup']; ?>">
								<?php echo $lang['Date setup']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['upload']; ?>" target="main" title="<?php echo $lang['Upload setting']; ?>">
								<?php echo $lang['Upload setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['sms']; ?>" target="main" title="<?php echo $lang['Sms setting']; ?>">
								<?php echo $lang['Sms setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['im']; ?>" target="main" title="<?php echo $lang['Instant messaging binding']; ?>">
								<?php echo $lang['Instant messaging binding']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['sysCode']; ?>" target="main" title="<?php echo $lang['System code setting']; ?>">
								<?php echo $lang['System code setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['email']; ?>" target="main" title="<?php echo $lang['Email setting']; ?>">
								<?php echo $lang['Email setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['security']; ?>" target="main" title="<?php echo $lang['Security setting']; ?>">
								<?php echo $lang['Security setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['sysStamp']; ?>" target="main" tile="<?php echo $lang['System stamp']; ?>">
								<?php echo $lang['System stamp']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $global['approval']; ?>" target="main" tile="<?php echo $lang['Approval process']; ?>">
								<?php echo $lang['Approval process']; ?>
							</a>
						</li>
                        <li>
							<a href="<?php echo $global['notify']; ?>" target="main" tile="<?php echo $lang['Notify setup']; ?>">
								<?php echo $lang['Notify setup']; ?>
							</a>
						</li>
					</ul>
					<!-- 用户 -->
					<ul id="db_user_list" style="display:none;">
						<li class="active">
							<a href="<?php echo $organization['user']; ?>" target="main" title="<?php echo $lang['Department personnel management']; ?>">
								<?php echo $lang['Department personnel management']; ?>
							</a>
						</li>
						<li class="active">
							<a href="<?php echo $organization['role']; ?>" target="main" title="<?php echo $lang['Role management']; ?>">
								<?php echo $lang['Role management']; ?>
							</a>
						</li>
						<li class="active">
							<a href="<?php echo $organization['position']; ?>" target="main" title="<?php echo $lang['Position management']; ?>">
								<?php echo $lang['Position management']; ?>
							</a>
						</li>
					</ul>
					<!-- 界面 -->
					<ul id="db_interface_list" style="display:none;">
						<li class="active">
							<a href="<?php echo $interface['nav']; ?>" target="main" title="<?php echo $lang['Navigation setting']; ?>">
								<?php echo $lang['Navigation setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $interface['quicknav']; ?>" target="main" title="<?php echo $lang['Quicknav setting']; ?>">
								<?php echo $lang['Quicknav setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $interface['login']; ?>" target="main" title="<?php echo $lang['Login page setting']; ?>">
								<?php echo $lang['Login page setting']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $interface['background']; ?>" target="main" title="<?php echo $lang['System background setting'] ?>">
								<?php echo $lang['System background setting'] ?>
							</a>
						</li>
					</ul>
					<!-- 模块 -->
					<ul id="db_module_list" style="display:none;">
						<li class="active">
							<a href="<?php echo $module['module']; ?>" target="main" title="<?php echo $lang['Module center']; ?>"><?php echo $lang['Module center']; ?></a>
						</li>
						<?php if ( !empty( $moduleMenu ) ): ?>
							<li class="active">
								<a href="javascript:void(0);" target="main" title="<?php echo $lang['Module manager']; ?>"><?php echo $lang['Module manager']; ?></a>
								<ul class="sub-sec-nav">
									<?php foreach ( $moduleMenu as $id => $menu ): ?>
										<li>
											<a href="<?php echo IBOS::app()->urlManager->createUrl( $menu['m'] . '/' . $menu['c'] . '/' . $menu['a'] ); ?>" target="main" title="<?php echo $menu['name']; ?>">
												<?php echo $menu['name']; ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</li>
						<?php endif; ?>
						<li>
							<a href="<?php echo $module['permissions']; ?>" target="main" title="<?php echo $lang['Permissions setup']; ?>">
								<?php echo $lang['Permissions setup']; ?>
							</a>
						</li>
					</ul>
					<!-- 管理 -->
					<ul id="db_manage_list" style="display:none;">
						<li class="active">
							<a href="<?php echo $manager['update']; ?>" target="main" title="<?php echo $lang['Update cache']; ?>">
								<?php echo $lang['Update cache']; ?>
							</a>
						</li>
						<li>
							<a href="<?php echo $manager['announcement']; ?>" target="main" title="<?php echo $lang['System announcement']; ?>">
								<?php echo $lang['System announcement']; ?>
							</a>
						</li>
						<?php if ( LOCAL ): ?>
							<li>
								<a href="<?php echo $manager['database']; ?>" target="main" title="<?php echo $lang['Database']; ?>">
									<?php echo $lang['Database']; ?>
								</a>
							</li>
						<?php endif; ?>
						<!--功能还没做，暂时屏蔽-->
						<!--<li>
							<a href="<?php echo $manager['split']; ?>" target="main" title="<?php echo $lang['Table archive']; ?>">
						<?php echo $lang['Table archive']; ?>
							</a>
						</li>-->
						<li>
							<a href="<?php echo $manager['cron']; ?>" target="main" title="<?php echo $lang['Scheduled task']; ?>">
								<?php echo $lang['Scheduled task']; ?>
							</a>
						</li>
						<!--功能还没做好，暂时屏蔽-->
						<!--
						<li>
							<a href="<?php echo $manager['fileperms']; ?>" target="main" title="<?php echo $lang['Check file permissions']; ?>">
						<?php echo $lang['Check file permissions']; ?>
							</a>
						</li>
						-->
						<?php if ( LOCAL ): ?>
							<li>
								<a href="<?php echo $manager['upgrade']; ?>" target="main" title="<?php echo $lang['Online upgrade']; ?>">
									<?php echo $lang['Online upgrade']; ?>
								</a>
							</li>
						<?php endif; ?>
					</ul>
					<!-- 服务 -->
					<ul id="db_services_list" style="display:none;">
						<li class="active">
							<a href="<?php echo $service['service']; ?>" target="main" title="IBOS <?php echo $lang['Service']; ?>"><?php echo $lang['Service']; ?></a>
						</li>
					</ul>
				</div>
			</div>
			<div class="mc" id="mc">
				<iframe src="<?php echo $def; ?>" width="100%" height="100%" frameborder="0" name="main" id="main"></iframe>
			</div>
		</div>

		<!-- </div> -->
		<!-- load js -->
		<script src="<?php echo STATICURL; ?>/js/src/core.js?<?php echo VERHASH; ?>"></script>
		<script src="<?php echo STATICURL; ?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH; ?>"></script>
		<script src="<?php echo STATICURL; ?>/js/src/base.js?<?php echo VERHASH; ?>"></script>
		<script src="<?php echo STATICURL; ?>/js/src/common.js?<?php echo VERHASH; ?>"></script>
		<script src="<?php echo $assetUrl; ?>/js/frame.js?<?php echo VERHASH; ?>"></script>
		<script>
			$(document).on("click", "a[target='main']", function() {
				var title = '<?php echo $lang['Admin control']; ?> -' + $(this).html();
				document.title = title;
			})
		</script>
	</body>
</html>