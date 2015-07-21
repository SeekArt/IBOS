<!doctype html>
<html lang="en">
	<head>
		<meta charset="<?php echo CHARSET; ?>">
		<title><?php echo $msgTitle; ?></title>
		<!-- load css -->
		<style>
			/* Status Tip */
			.status-tip i,.status-tip a.tip-url {background-image: url(<?php echo STATICURL; ?>/image/status_tip.png);background-repeat: no-repeat;}
			.status-tip{margin: 0 auto;width: 576px;text-align: center;}
			.status-tip i{margin: 0 auto;display: block;width: 80px;height: 80px;}
			.status-tip p{margin-bottom: 0;line-height: 40px;font-size: 20px;}
			.status-tip a.tip-url{display: block;height: 100px;width: 100%;background-position: 0 -80px;text-indent: -9999px;}
			.status-tip-error i{ background-position: -80px 0; }
			.status-tip-info i{ background-position: -160px 0; }
			.status-tip-lock .status-tip i{ background-position: -240px 0; }
			.ct { margin-top:80px;}
		</style>
		<link rel="stylesheet" href="<?php echo STATICURL ?>/css/base.css?<?php echo VERHASH; ?>">
	</head>
	<body>
		<div class="ct">
			<div class="status-tip status-tip-<?php echo $messageType; ?>">
				<i></i>
				<p><?php echo $message ?></p>
				<p><span id="wait" class='badge badge-info'><?php echo isset( $timeout ) && $autoJump ? $timeout : ''; ?></span></p>
				<?php if ( $autoJump ): ?>
					<a id="href" href="<?php echo $jumpUrl ?>" class="tip-url"></a>
					<script>
						(function() {
							var wait = document.getElementById('wait'),
								href = document.getElementById('href').href;

							var interval = setInterval(function() {
								var time = --wait.innerHTML;
								if (time === 0) {
									location.href = href;
									clearInterval(interval);
								}
							}, 1000);
						})();
					</script>
				<?php else: ?>
					<?php foreach ( $jumpLinksOptions as $linkName => $url ): ?>
						<a class="tip-url" href="<?php echo $url; ?>"><?php echo $linkName; ?></a>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( isset( $script ) ): ?>
			<script>
	<?php echo $script; ?>
			</script>
		<?php endif; ?>
	</body>
</html>