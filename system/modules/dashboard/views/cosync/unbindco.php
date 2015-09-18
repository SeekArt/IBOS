<link href="<?php echo $this->getAssetUrl(); ?>/css/ibosco.css" type="text/css" rel="stylesheet">

<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/home.css?<?php echo VERHASH; ?>">
<link href="<?php echo $this->getAssetUrl(); ?>/css/weixin.css" type="text/css" rel="stylesheet" />

<div class="ct">
	<div class="clearfix">
		<h1 class="mt">酷办公</h1>
		<ul class="mn">
			<li>
				<a href="<?php echo $this->createUrl( 'cobinding/index' ) ?>">酷办公绑定</a>
			</li>
			<li>
				<span>部门及用户同步</span>
			</li>
		</ul>
	</div>
	<div class="xac unbinding-warning-wrap">
		<div class="dib">
			<i class="o-unbinding-warning"></i>
		</div>
		<div class="dib wraning-opt-tip">
			<p class="fsl xcm mb">哎呀，你还没有绑定酷办公哦！</p>
			<p class="mb xal">你还可以:</p>
			<p class="xal">
				<a href="javascript:;" id="callback_btn" class="btn"><?php echo $lang['Return binding page'] ?></a>
			</p>
		</div>
	</div>
</div>
<script>
	$(function() {
		$("#callback_btn").on("click", function() {
			var url = "<?php echo $this->createUrl( 'cobinding/index' ); ?>";
			window.location.href = url;
		});
	});
</script>


