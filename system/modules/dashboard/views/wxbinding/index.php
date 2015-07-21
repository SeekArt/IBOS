<?php

use application\core\utils\IBOS;
use application\modules\dashboard\utils\Wx;
?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/home.css?<?php echo VERHASH; ?>">
<link href="<?php echo $this->getAssetUrl(); ?>/css/weixin.css" type="text/css" rel="stylesheet" />
<div class="ct sp-ct">
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
	<div class="ctb ps-type-title">
		<h2 class="st"><?php echo $lang['Binding wechat'] ?></h2>
	</div>
	<div class="conpamy-info-wrap binding-info-wrap">
		<iframe src="<?php echo Wx::getInstance()->getBindingSrc(); ?>" name="<?php echo IBOS::app()->setting->get( 'siteurl' ) . '?r=dashboard'; ?>" class="setting-bind-iframe">
		<div class="fill-nn step-tip-content" id="bind_apply_content">

		</div>
		</iframe>
	</div>
</div>