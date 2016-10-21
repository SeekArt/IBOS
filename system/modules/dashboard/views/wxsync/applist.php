<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/home.css?<?php echo VERHASH; ?>">
<link href="<?php echo $this->getAssetUrl(); ?>/css/weixin.css" type="text/css" rel="stylesheet" />
<div class="ct sp-ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Wechat app list center']; ?></h1>
	</div>
	<div>
		<div class="ps-type-title">
			<a href="<?php echo $this->createUrl('wxbinding/index'); ?>" class="db wx-back-btn mb">
				<i class="o-back-arrow"></i>
				<span class="wx-back-tip">返回企业号绑定</span>
			</a>
			<!-- 同步start -->
			<?php foreach ($list as $suite) : ?>
				<div class="box-shadow bind-info-wrap">
					<div class="wx-app-title">
						<img class="wx-app-logo" src="<?php echo $suite['logo']; ?>" />
						<div class="wx-app-text-box">
							<p class="wx-app-name"><?php echo $suite['name']; ?></p>
							<p class="wx-app-tip"><?php echo $suite['desc']; ?></p>
						</div>
						<button class="btn btn-primary wx-suite-btn" data-action="installApply" data-param='{ "url": "<?php echo $suite['url']; ?>" }'>安装应用</button>
					</div>
					<div class="wx-app-list">
						<ul class="wx-auth-menu-list clearfix">
							<?php foreach ( $suite['app'] as $app ) : ?>
								<li class="wx-auth-menu-unit <?php if ($app['exist']) : ?>active<?php endif; ?>">
									<div class="wx-unit-box">
										<img class="wx-unit-icon" src="<?php echo $app['img']; ?>" title="<?php echo $app['desc']; ?>"/>
										<p class="ellipsis"><?php echo $app['name']; ?></p>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function() {
		// 安装套件应用
        Ibos.evt.add({
        	"installApply": function(param, elem){
	            param.url && window.open(param.url);
	        }
        })
	})
</script>