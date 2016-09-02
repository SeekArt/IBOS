<?php

use application\core\utils\Ibos;
use application\modules\dashboard\utils\Wx;
?>
<link href="<?php echo $this->getAssetUrl(); ?>/css/weixin.css" type="text/css" rel="stylesheet">
<div>
	<div class="ct">
		<div class="clearfix">
			<h1 class="mt"><?php echo $lang['Wechat corp']; ?></h1>
	        <ul class="mn">
	            <li>
	                <span><?php echo $lang['Wechat binding'] ?></span>
	            </li>
	            <li>
	                <a href="<?php echo $this->createUrl( 'wxsync/index' ) ?>"><?php echo $lang['Department and user sync'] ?></a>
	            </li>
	        </ul>
		</div>
		<div>
			<!-- 企业信息 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Binding wechat'] ?></h2>
				<div class="co-banding-wrap">
					<div class="box-shadow ibosoa-info clearfix">
						<div class="company-logo mbs pull-left">
							<img src="<?php echo !empty( $logo ) ? $logo : 'static/image/logo.png'; ?>" alt="<?php echo $shortname; ?>">
							<div class="ibos-logo">
								<i class="o-binding-ibos"></i>
							</div>
						</div>
						<div class="company-info pull-left">
							<p class="lhl t"><?php echo $fullname; ?></p>
							<p class="lhl">系统URL : <?php echo $domain; ?></p>
							<p class="lhl ellipsis" title="AES KEY : 9Pcz3VcUe6kh-GEAgU3vL99rHUk5F7C-libcteUhQkYC72D8qf">AES KEY : <?php echo $aeskey; ?></p>
						</div>
					</div>
					<div class="wx-binding-check">
						<p class="fsb">系统URL连接验证</p>
						<div>
							<input type="text" class="span12" name="sysurl" value="<?php echo $domain; ?>" readonly disabled/>
						</div>
						<div class="wx-check-result <?php echo $access ? 'xcgn' : 'xcr'; ?>">
							<i class="<?php echo $access ? 'o-tip-success' : 'o-tip-failure'; ?>"></i>
							<span><?php echo $access ? '验证成功！' : '验证失败！请检查系统URL是否可被微信服务器访问。'; ?></span>
						</div>
					</div>
					<div class="wx-btn-group">
						<a class="wx-back-btn" href="<?php echo $this->createUrl('wxbinding/logout'); ?>">
							<i class="o-back-arrow mrm"></i>
							<span>退出当前帐号</span>
						</a>
						<button class="btn wx-suite-install <?php echo $access ? 'btn-primary' : 'disabled'; ?>" data-action="installApply" data-param='{ "url": "<?php echo $url; ?>"}' <?php echo $access ? '' : 'disabled'; ?>>安装套件应用</button>
					</div>
				</div>
			</div>
			<?php if ( $isBinding ) : ?>
				<div class="wx-auth-menu-wrap">
					<p class="wx-auth-menu-has">已授权<span class="wx-auth-menu-has-num"><?php echo count( $app ); ?></span>个应用</p>
					<ul class="wx-auth-menu-list clearfix">
						<?php foreach ($app as $row) : ?>
							<li class="wx-auth-menu-unit">
								<div class="wx-unit-box">
									<img class="wx-unit-icon" src="http://www.ibos.com.cn/Wxapi/image/<?php echo $row['appid']; ?>"/>
									<span class="wx-unit-name"><?php echo $row['name']; ?></span>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript" src="<?php echo $this->getAssetUrl(); ?>/js/iboscologin.js"></script>
<script src="<?php echo $assetUrl; ?>/js/syncdata.js"></script>