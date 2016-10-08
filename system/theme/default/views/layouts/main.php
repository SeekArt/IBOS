<?php

use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\core\utils\Url;
use application\modules\user\utils\User;
?>
<!doctype html>
<!--[if lt IE 9]>
<html lang="en" class="ie8">
<![endif]-->
<!--[if gt IE 8]>
<html lang="en">
    <![endif]-->
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="renderer" content="webkit"/>
	<meta charset=<?php echo CHARSET; ?> />
	<title><?php echo Ibos::app()->setting->get( 'title' ); ?></title>
	<link rel="shortcut icon" href="<?php echo STATICURL; ?>/image/favicon.ico?<?php echo VERHASH; ?>">
	<link rel="apple-touch-icon-precomposed" href="<?php echo STATICURL; ?>/image/common/ios_icon.png">
	<meta name="generator" content="IBOS <?php echo VERSION; ?>" />
	<meta name="author" content="IBOS Team" />
	<meta name="copyright" content="2013 IBOS Inc." />
	<!-- IE 8 以下跳转至浏览器升级页 -->
	<!--[if lt IE 8]>
		<script>
			window.location.href = "<?php echo Ibos::app()->urlManager->createUrl( "main/default/unsupportedBrowser" ); ?>"
		</script>
	<![endif]-->
	<!-- load css -->
	<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/base.css?<?php echo VERHASH; ?>" />
	<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/common.css?<?php echo VERHASH; ?>" />
	<?php $skin = Ibos::app()->setting->get( 'setting/skin' ); ?>
	<?php if ( !empty( $skin ) ): ?><link rel="stylesheet" href="<?php echo STATICURL; ?>/css/skin/<?php echo $skin; ?>.css?<?php echo VERHASH; ?>" /><?php endif; ?>
	<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH; ?>" />
	<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH; ?>" />
	<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/zTree/css/ibos/ibos.css?<?php echo VERHASH; ?>" />
	<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/Select2/select2.css?<?php echo VERHASH; ?>" />
	<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/call.css?<?php echo VERHASH; ?>" />
	<!-- IE8 fixed -->
	<!--[if lt IE 9]>
		<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/iefix.css?<?php echo VERHASH; ?>" />
	<![endif]-->
	<!-- load css end -->
	<!-- JS全局变量-->
	<script>
<?php include PATH_ROOT . '/data/jsconfig.php'; ?>
	</script>
	<!-- 语言包 -->
	<script src='<?php echo STATICURL; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
	<!-- 核心库类 -->
	<script src='<?php echo STATICURL; ?>/js/src/core.js?<?php echo VERHASH; ?>'></script>

</head>
<body class="ibbody">
	<div class="ibcontainer">
		<!-- Header -->
		<div class="header" id="header">
			<div class="wrap">
				<div class="logo">
					<?php $unit = Ibos::app()->setting->get( 'setting/unit' ); ?>
					<a href="<?php echo Ibos::app()->setting->get( 'siteurl' ); ?>"><img src="<?php
						if ( !empty( $unit['logourl'] ) ): echo $unit['logourl'];
						else:
							?><?php echo STATICURL; ?>/image/logo.png<?php endif; ?>?<?php echo VERHASH; ?>" alt="IBOS" height="40"></a>
				</div>
				<!-- Nav -->
				<?php $navs = Ibos::app()->setting->get( 'cache/nav' ); ?>
				<?php if ( $navs ): ?>
					<div class="nvw" id="nvw">
						<ul class="nv nl" id="nv">
							<?php foreach ( $navs as $index => $nav ): ?>
								<?php
								if ( $nav['disabled'] ) {
									continue;
								}
								$nav['url'] = Url::getUrl( $nav['url'] );
								?>
								<li data-target="#sub_nav_<?php echo $index; ?>" data-id="<?php echo $index; ?>">
									<a href="<?php echo $nav['url']; ?>" target="<?php echo $nav['targetnew'] ? '_blank' : '_self'; ?>"><?php echo $nav['name']; ?></a>
									<?php if ( 1 == 2 ): ?><i class="o-new-nav-tip"></i> <?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div id="subnv_wrap">
						<?php foreach ( $navs as $index => $nav ): ?>
							<?php if ( !empty( $nav['child'] ) ): ?>
								<ul class="subnv" id="sub_nav_<?php echo $index; ?>" data-pid="<?php echo $index; ?>">
									<?php foreach ( $nav['child'] as $subNav ): ?>
										<?php
										if ( $subNav['disabled'] ) {
											continue;
										}
										$hasPurv = User::checkNavPurv( $subNav );
										$subNav['url'] = Url::getUrl( $subNav['url'] );
										?>
										<?php if ( $hasPurv ): ?>
											<li>
												<a target="<?php echo $subNav['targetnew'] ? '_blank' : '_self'; ?>" href="<?php echo $subNav['url']; ?>" data-id="<?php echo $subNav['id']; ?>">
													<?php echo $subNav['name']; ?>
													<?php if ( 1 == 2 ): ?><i class="o-new-nav-tip"></i><?php endif; ?>
												</a>
											</li>
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
							<?php echo StringUtil::cutStr( Ibos::app()->user->realname, 6 ); ?>
							<i class="caret caret-small"></i>
						</a>
					</div>
					<a href="<?php echo Ibos::app()->createUrl( 'message/mention/index' ); ?>" class="cbtn o-message" id="user_fun_ctrl">
						<?php echo Ibos::lang( 'Message', 'default' ); ?>
					</a>
				</div>
				<div class="posr">
					<div id="message_container" class="reminder" style="display: none;">
						<a href="javascript:void(0)" onclick="Ibosapp.dropnotify.hide()" class="o-close-small"></a>
						<ul class="reminder-list" >
							<li rel="new_folower_count" ><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'weibo/personal/follower' ); ?>" class="anchor">查看粉丝</a></li>
							<li rel="unread_comment" ><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/comment/index' ); ?>" class="anchor">查看消息</a></li>
							<li rel="unread_message"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/pm/index' ); ?>" class="anchor">查看消息</a></li>
							<li rel="unread_atme"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/mention/index' ); ?>" class="anchor">查看消息</a></li>
							<li rel="unread_notify"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/index' ); ?>" class="anchor">查看消息</a></li>
							<li rel="user"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=user' ); ?>" class="anchor">查看消息</a></li>
							<li rel="diary"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=diary' ); ?>" class="anchor">查看详情</a></li>
							<li rel="report"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=report' ); ?>" class="anchor">查看详情</a></li>
							<li rel="calendar"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=calendar' ); ?>" class="anchor">查看详情</a></li>
							<li rel="workflow"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=workflow' ); ?>" class="anchor">查看详情</a></li>
							<li rel="article"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=article' ); ?>" class="anchor">查看详情</a></li>
							<li rel="officialdoc"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=officialdoc' ); ?>" class="anchor">查看详情</a></li>
							<li rel="email"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=email' ); ?>" class="anchor">查看详情</a></li>
							<li rel="assignment"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=assignment' ); ?>" class="anchor">查看详情</a></li>
							<li rel="message"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=message' ); ?>" class="anchor">查看详情</a></li>
							<li rel="unread_group_atme"><span></span>，<a href="" class="anchor">查看消息</a></li>
							<li rel="unread_group_comment"><span></span>，<a href="" class="anchor">查看消息</a></li>
							<li rel="meeting"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=meeting' ); ?>" class="anchor">查看消息</a></li>
							<li rel="car"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=car' ); ?>" class="anchor">查看消息</a></li>
							<li rel="assets"><span></span>，<a href="<?php echo Ibos::app()->urlManager->createUrl( 'message/notify/detail&module=assets' ); ?>" class="anchor">查看消息</a></li>
						</ul>
					</div>
				</div>
			</div>
			<!-- 用户登录状态卡 -->
			<div class="uil-card" id="user_login_card" style="display:none;">
				<div class="uil-card-header">
					<div class="media">
						<a href="<?php echo Ibos::app()->user->space_url; ?>" class="pull-left avatar-circle">
							<img src="<?php echo Ibos::app()->user->avatar_middle; ?>">
						</a>
						<div class="media-body">
							<h5 class="media-heading"><strong><?php echo Ibos::app()->user->realname; ?></strong></h5>
							<p class="fss"><?php echo trim( Ibos::app()->user->deptname . ':' . Ibos::app()->user->posname, ':' ); ?></p>
						</div>
					</div>
				</div>
				<div class="uil-card-body">
					<div class="mbm">
						<span class="exp-val"><em><?php echo Ibos::app()->user->credits; ?></em>/<?php echo Ibos::app()->user->next_group_credit; ?></span>
						<span><i class="lv lv<?php echo Ibos::app()->user->level; ?>"></i> <?php echo Ibos::app()->user->group_title; ?></span>
					</div>
					<div class="progress" title="Progress-bar">
						<div class="progress-bar <?php if ( Ibos::app()->user->upgrade_percent > 90 ): ?>progress-bar-danger<?php else: ?>progress-bar-success<?php endif; ?>" style="width: <?php echo Ibos::app()->user->upgrade_percent; ?>%;"></div>
					</div>
					<div class="btn-group btn-group-justified">
						<a href="<?php echo Ibos::app()->user->space_url; ?>" class="btn"><i class="om-user"></i>个人</a>
						<?php if ( Ibos::app()->user->isadministrator || Ibos::app()->user->roleType ): ?><a class="btn" target="_blank" href="<?php echo Ibos::app()->urlManager->createUrl( 'dashboard/default/index' ); ?>" ><i class="om-key"></i><?php echo Ibos::lang( 'Control', 'default' ); ?></a><?php endif; ?>
						<a href="<?php echo Ibos::app()->urlManager->createUrl( 'user/default/logout', array( 'formhash' => FORMHASH ) ); ?>" class="btn">
							<i class="om-shutdown"></i><?php echo Ibos::lang( 'Quit', 'default' ); ?>
						</a>
					</div>
				</div>
			</div>
			<div class="fun-card" id="user_fun_card" style="display:none;">
				<div class="fill-ss">
					<ul class="list-inline fun-opt-list">
						<li>
							<a class="fun-opt-box" href="javascript:;" data-action="calling">
								<i class="on-fun-call"></i>
								<span class="fss db fun-opt-title">打电话</span>
							</a>
						</li>
						<li>
							<a class="fun-opt-box" href="<?php echo Ibos::app()->urlManager->createUrl( 'email/content/add' ); ?>">
								<i class="on-fun-email"></i>
								<span class="fss db fun-opt-title">发邮件</span>
							</a>
						</li>
						<li>
							<a class="fun-opt-box" href="javascript:;" data-action="sendPrivateLetter">
								<i class="on-fun-private-letter"></i>
								<span class="fss db fun-opt-title">发私信</span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<!-- Header end -->
		<!-- load script end -->
		<div class="wrap" id="mainer">
			<?php if ( !in_array( Ibos::app()->setting->get( 'module' ), array( 'user', 'weibo', 'app', 'main' ) ) ): ?>
				<div class="mtw">
					<h2 class="mt pull-left"><?php echo Ibos::app()->setting->get( 'pageTitle' ); ?></h2>
					<span class="pull-right"><?php echo Ibos::app()->setting->get( 'lunar' ); ?></span>
				</div>
			<?php endif; ?>

			<script src='<?php echo STATICURL; ?>/js/src/base.js?<?php echo VERHASH; ?>'></script>
			<!-- @Todo: 放到 mainer 加载之后 -->
			<script src='<?php echo STATICURL; ?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH; ?>'></script>
			<script src='<?php echo STATICURL; ?>/js/lib/zTree/jquery.ztree.all.min.js?<?php echo VERHASH; ?>'></script>
			<script src='<?php echo STATICURL; ?>/js/lib/Select2/select2.js?<?php echo VERHASH; ?>'></script>

			<script src='<?php echo STATICURL; ?>/js/src/common.js?<?php echo VERHASH; ?>'></script>

			<script src='<?php echo STATICURL; ?>/js/src/application.js?<?php echo VERHASH; ?>'></script>
			<!--数据包-->

			<?php echo File::getOrgJs(); ?>
			<script src='<?php echo STATICURL; ?>/js/app/ibos.userData.js?<?php echo VERHASH; ?>'></script>
			<script src='<?php echo STATICURL; ?>/js/app/ibos.userSelect.js?<?php echo VERHASH; ?>'></script>
			<!-- Mainer -->
			<?php echo $content; ?>
			<!-- Mainer end -->
		</div>
		<!-- Footer -->
		<div class="footer wrap" id="footer">
			<!-- Breadcrumb -->
			<div class="brc clearfix">
				<a href="<?php echo Ibos::app()->setting->get( 'siteurl' ); ?>" title="">
					<i class="o-logo"></i>
				</a>
				<?php $breadCrumbs = Ibos::app()->setting->get( 'breadCrumbs' ); ?>
				<?php foreach ( $breadCrumbs as $key => $value ): ?>
					<a href="<?php echo isset( $value['url'] ) ? $value['url'] : 'javascript:;' ?>"><?php echo $value['name']; ?></a>
				<?php endforeach; ?>
			</div>
			<!-- Quick link -->
			<div class="copyright">
				<div class="quick-link">
					<a target="_blank" href="http://doc.ibos.com.cn/"><?php echo Ibos::lang( 'Ibos help', 'default' ); ?></a>
					<span class="ilsep">|</span>
					<a target="_blank" href="http://kf.ibos.com.cn"><?php echo Ibos::lang( 'Ibos feedback', 'default' ); ?></a>
					<?php if ( ENGINE !== 'SAAS' ): ?>
						<span class="ilsep">|</span>
						<a href="javascript:;" data-action="showCert"><?php echo Ibos::lang( 'Certificate of authorization', 'default' ); ?></a>
					<?php endif; ?>
					<span class="ilsep">|</span>
					<a target="_blank" href="http://doc.ibos.com.cn/article/detail/id/256"><?php echo Ibos::lang( 'Chrome frame', 'default' ); ?></a>
					<span class="ilsep">|</span>
					<a href="javascript:;" data-action="appDownload">客户端下载</a>

					<?php
					$qr = Ibos::app()->setting->get( 'setting/qrcode' );
					$qrcode = isset( $qr ) ? $qr : '';
					if ( !empty( $qrcode ) ) :
						?>
						<span class="ilsep">|</span>
						<a href="javascript:;" data-action="followWx" class="im-link">
							<i class="o-olw-crcode"></i>关注微信号
						</a>
					<?php endif; ?>
				</div>
				Powered by <strong>IBOS <?php echo VERSION; ?> <?php echo VERSION_DATE; ?></strong>
				<?php if ( YII_DEBUG ): ?>
					Processed in <code><?php echo Ibos::app()->performance->endClockAndGet(); ?></code> second(s).
					<code><?php echo Ibos::app()->performance->getDbstats(); ?></code> queries.
				<?php endif; ?>
			</div>
		</div>
	</div>
</body>
</html>