
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
	</div>
	<div>
		<form action="" class="form-horizontal">
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
				<div class="main-content mtg">
					<div>
						<i class="o-prompt-image"></i>
					</div>
					<div class="prompt-content">
						<p class="version-title mbs"><?php echo $lang['Discover new version']; ?>&nbsp;&nbsp;<span class="xcbu"><?php echo $list[0]['desc']; ?></span></p>
						<p class="mbs"><?php echo $lang['Upgrade content']; ?></p>
						<div class="upgrade-content scroll">
							<?php echo $list[0]['upgradeDesc']; ?>
						</div>
					</div>
					<button type="button" class="btn btn-primary btn-upgrade" id="upgradeNow" data-url="<?php echo $list[0]['link']; ?>"><?php echo $lang['Upgrade Now']; ?></button>
					<a class="mls" href="http://doc.ibos.com.cn/article/detail/id/87" target="_blank"><?php echo $lang['Upgrade manual']; ?></a>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	$('#upgradeNow').on('click', function() {
		var $el = $(this);
		Ui.confirm("<?php echo $lang['Close system tip']; ?>", function() {
			// 关闭系统
			var closeSysUrl = "<?php echo $this->createUrl( 'index/switchstatus' ); ?>";
			$.get(closeSysUrl, {val: '1'});
			window.location.href = $el.attr('data-url');
			// 取消的话就返回首页	
		}, function() {
			window.parent.location.href = "<?php echo $this->createUrl( 'default/index' ); ?>";
		});
	});
</script>