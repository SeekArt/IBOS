
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
	</div>
	<div>
		<form action="" class="form-horizontal">
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
				<div class="xac mtg">
					<div class="dib">
						<i class="o-newest-image"></i>
					</div>
					<div class="dib mll">
						<p class="fsl xcm mbs"><?php echo $lang['Upgrade congratulation']; ?><span class="xcbu"><?php echo $lang['Upgrade newest']; ?></span>!</p>
						<p class="mb xal xcg"><?php echo $lang['Upgrade fashionable']; ?>~</p>
						<p class="mb xal"><?php echo $lang['Upgrade you can']; ?>：</p>
						<button type="button" class="btn pull-left" id="checkAgain"><?php echo $lang['Upgrade check again']; ?></button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	$('#checkAgain').on('click', function() {
		window.location.href = "<?php echo $this->createUrl( 'upgrade/index' ); ?>";
	});
</script>