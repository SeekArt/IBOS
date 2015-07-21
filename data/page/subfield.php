<?php

use application\core\utils\IBOS;
use application\core\utils\String;
use application\core\utils\Url;
use application\modules\user\utils\User as UserUtil;

?>
<!doctype <html>
</html>
<html lang="en">
	<head>
        <meta charset=<?php echo CHARSET; ?> />
        <title><?php echo IBOS::app()->setting->get( 'title' ); ?></title>
		<link rel="shortcut icon" href="<?php echo STATICURL; ?>/image/favicon.ico?<?php echo VERHASH; ?>">
		<link rel="apple-touch-icon-precomposed" href="<?php echo STATICURL; ?>/image/common/ios_icon.png">
        <meta name="generator" content="IBOS <?php echo VERSION; ?>" />
        <meta name="author" content="IBOS Team" />
        <meta name="copyright" content="2013 IBOS Inc." />
		<!-- IE 8 以下跳转至浏览器升级页 -->
		<!--[if lt IE 8]>
			<script>
				window.location.href = "<?php echo IBOS::app()->urlManager->createUrl( "main/default/unsupportedBrowser" ); ?>"
			</script>
		<![endif]-->
		
	</head>
	<body class="ibbody">
		<div class="ibcontainer">
			<!-- Header -->
			<div class="header" id="header">
				<div class="wrap">
					<div class="logo">
						<?php $unit = IBOS::app()->setting->get( 'setting/unit' ); ?>
						<a href="<?php echo IBOS::app()->setting->get( 'siteurl' ); ?>"><img src="<?php if( !empty( $unit['logourl'] ) ): echo $unit['logourl']; else: ?><?php echo STATICURL; ?>/image/logo.png<?php endif; ?>?<?php echo VERHASH; ?>" alt="IBOS"></a>
					</div>
					<!-- Nav -->
					<?php $navs = IBOS::app()->setting->get( 'cache/nav' ); ?>
					<?php if ( $navs ): ?>
						<div class="nvw">
							<ul class="nv nl" id="nv">
								<?php foreach ( $navs as $index => $nav ): ?>
									<?php
									if ( $nav['disabled'] ) {
										continue;
									}
									$nav['url'] = Url::getUrl($nav['url']);
									?>
									<li data-target="#sub_nav_<?php echo $index; ?>">
										<a href="<?php echo $nav['url']; ?>" target="<?php echo $nav['targetnew'] ? '_blank' : '_self'; ?>"><?php echo $nav['name']; ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div>
							<?php foreach ( $navs as $index => $nav ): ?>
								<?php if ( !empty( $nav['child'] ) ): ?>
									<ul class="subnv" id="sub_nav_<?php echo $index; ?>">
										<?php foreach ( $nav['child'] as $subNav ): ?>
											<?php
											if ( $subNav['disabled'] ) {
												continue;
											}
											$hasPurv = UserUtil::checkNavPurv( $subNav );
											$subNav['url'] = Url::getUrl($subNav['url']);
											?>
											<?php if ($hasPurv): ?>
												<li><a target="<?php echo $subNav['targetnew'] ? '_blank' : '_self'; ?>" href="<?php echo $subNav['url']; ?>"><?php echo $subNav['name']; ?></a></li>
											<?php else: ?>
												<li class="disabled"><a href="javascript:;" title="暂无权限访问"><?php echo $subNav['name']; ?></a></li>
											<?php endif; ?>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<!-- Nav end -->
					<div class="usi">
						<div class="btn-group">
							<a href="javascript:;" data-toggle="dropdown" id="user_login_ctrl">
								<?php echo String::cutStr(IBOS::app()->user->realname, 6); ?>
								<i class="caret caret-small"></i>
							</a>
						</div>
						<a href="<?php echo IBOS::app()->createUrl( 'message/mention/index' ); ?>" class="cbtn o-message"><?php echo IBOS::lang( 'Message', 'default' ); ?></a>
					</div>
					<div class="posr">
						<div id="message_container" class="reminder" style="display: none;">
							<a href="javascript:void(0)" onclick="Ibosapp.dropnotify.hide()" class="o-close-small"></a>
							<ul class="reminder-list" >
								<li rel="new_folower_count" ><span></span>，<a href="<?php echo IBOS::app()->urlManager->createUrl( 'weibo/personal/follower' ); ?>" class="anchor">查看粉丝</a></li>
								<li rel="unread_comment" ><span></span>，<a href="<?php echo IBOS::app()->urlManager->createUrl( 'message/comment/index' ); ?>" class="anchor">查看消息</a></li>
								<li rel="unread_message"><span></span>，<a href="<?php echo IBOS::app()->urlManager->createUrl( 'message/pm/index' ); ?>" class="anchor">查看消息</a></li>
								<li rel="unread_atme"><span></span>，<a href="<?php echo IBOS::app()->urlManager->createUrl( 'message/mention/index' ); ?>" class="anchor">查看消息</a></li>
								<li rel="unread_notify"><span></span>，<a href="<?php echo IBOS::app()->urlManager->createUrl( 'message/notify/index' ); ?>" class="anchor">查看消息</a></li>
								<li rel="unread_group_atme"><span></span>，<a href="" class="anchor">查看消息</a></li>
								<li rel="unread_group_comment"><span></span>，<a href="" class="anchor">查看消息</a></li> 
							</ul>
						</div>
					</div>
				</div>
				<!-- 用户登录状态卡 -->
				<div class="uil-card" id="user_login_card" style="display:none;">
					<div class="uil-card-header">
						<div class="media">
							<a href="<?php echo IBOS::app()->user->space_url; ?>" class="pull-left avatar-circle">
								<img src="<?php echo IBOS::app()->user->avatar_middle; ?>">
							</a>
							<div class="media-body">
								<h5 class="media-heading"><strong><?php echo IBOS::app()->user->realname; ?></strong></h5>
								<p class="fss"><?php echo trim( IBOS::app()->user->deptname . ':' . IBOS::app()->user->posname, ':' ); ?></p>
							</div>
						</div>
					</div>
					<div class="uil-card-body">
						<div class="mbm">
							<span class="exp-val"><em><?php echo IBOS::app()->user->credits; ?></em>/<?php echo IBOS::app()->user->next_group_credit; ?></span>
							<span><i class="lv lv<?php echo IBOS::app()->user->level; ?>"></i> <?php echo IBOS::app()->user->group_title; ?></span>
						</div>
						<div class="progress" title="Progress-bar">
							<div class="progress-bar <?php if ( IBOS::app()->user->upgrade_percent > 90 ): ?>progress-bar-danger<?php else: ?>progress-bar-success<?php endif; ?>" style="width: <?php echo IBOS::app()->user->upgrade_percent; ?>%;"></div>
						</div>
						<div class="btn-group btn-group-justified">
							<a href="<?php echo IBOS::app()->user->space_url; ?>" class="btn"><i class="om-user"></i>个人中心</a>
							<?php if ( IBOS::app()->user->isadministrator ): ?><a class="btn" target="_blank" href="<?php echo IBOS::app()->urlManager->createUrl( 'dashboard/' ); ?>" ><i class="om-key"></i><?php echo IBOS::lang( 'Control center', 'default' ); ?></a><?php endif; ?>
							<a href="<?php echo IBOS::app()->urlManager->createUrl( 'user/default/logout', array( 'formhash' => FORMHASH ) ); ?>" class="btn">
								<i class="om-shutdown"></i><?php echo IBOS::lang( 'Quit', 'default' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<!-- Header end -->
			<!-- load script end -->
			<div>
			<div class="wrap" id="mainer">
				<div class="mtw">
					<h2 class="mt pull-left"><?php echo $pageTitle; ?></h2>
					<span class="pull-right"><?php echo IBOS::app()->setting->get( 'lunar' ); ?></span>
				</div>
				
				<!-- Mainer -->
				<!-- 这里就是内容模板 -->
				<div class="mpc clearfix" id="page_content">
					<div class="page-edit">
						<div class="wrap">
							<div class="mc clearfix">
								<!--sidebar-->
								<div class="aside">
									<div class="sbbf sbbf sbbl">		
										<ul class="nav nav-stacked nav-strip">
											<li class="active">
												<a href="">这是菜单</a>
											</li>
											<li>
												<a href="">这是菜单</a>
											</li>
										</ul>
									</div>
								</div>	
								<div class="mcr">
									<div>
										<h1 style="font-size: 32px;text-align: center; color: #58585C;">这是标题</h1>
										<div style="padding:20px;">这是内容</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
				<!-- 这里就是内容模板 -->

				<!-- Mainer end -->
			</div>
			<!-- Footer -->
			<div class="footer wrap" id="footer">
				<!-- Breadcrumb -->
				<div class="brc clearfix">
					<a href="<?php echo IBOS::app()->setting->get( 'siteurl' ); ?>" title="">
						<i class="o-logo"></i>
					</a>
					<?php foreach ( $breadCrumbs as $key => $value ): ?>
						<a href="<?php echo isset( $value['url'] ) ? $value['url'] : 'javascript:;' ?>"><?php echo $value['name']; ?></a>
					<?php endforeach; ?>
				</div>
				<!-- Quick link -->
				<div class="copyright">
					<div class="quick-link">
						<a target="_blank" href="http://doc.ibos.com.cn/"><?php echo IBOS::lang( 'Ibos help', 'default' ); ?></a>
						<span class="ilsep">|</span>
						<a target="_blank" href="http://bbs.ibos.com.cn"><?php echo IBOS::lang( 'Ibos feedback', 'default' ); ?></a>
						<span class="ilsep">|</span>
						<a target="_blank" href="<?php echo IBOS::app()->urlManager->createUrl( 'dashboard/' ); ?>" ><?php echo IBOS::lang( 'Control center', 'default' ); ?></a>
						<span class="ilsep">|</span>
						<a href="javascript:;" data-action="showCert"><?php echo IBOS::lang( 'Certificate of authorization', 'default' ); ?></a>
						<span class="ilsep">|</span>
						<a target="_blank" href="http://www.ibos.com.cn/file/99"><?php echo IBOS::lang( 'Chrome frame', 'default' ); ?></a>
					</div>
					Powered by <strong>IBOS <?php echo VERSION; ?> <?php echo VERSION_DATE; ?></strong>
					<?php if ( YII_DEBUG ): ?>
						Processed in <code><?php echo IBOS::app()->performance->endClockAndGet(); ?></code> second(s).
						<code><?php echo IBOS::app()->performance->getDbstats(); ?></code> queries. 
					<?php endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>
