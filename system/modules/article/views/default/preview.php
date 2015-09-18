<?php

use application\core\utils\File;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->
<!-- Mainer -->
<div class="mc clearfix">
	<!-- Sidebar -->
    <div class="aside">
        <div class="sbbf">
            <ul class="nav nav-strip nav-stacked">
                <li class="active">
                    <a href="<?php echo $this->createUrl( 'default/index'); ?>">
                        <i class="o-art-doc"></i>
                        <?php echo $lang['Information center']; ?>
                    </a>
                    <ul id="tree" class="ztree posr">
                    </ul>
                </li>
            </ul>
        </div>
    </div>
	<!-- Sidebar -->

	<!-- Mainer right -->
	<div class="mcr">
		<form action="" class="form-horizontal">
			<div class="ct ctview ctview-art">
				<!-- 文章 -->
				<div class="art">
					<div class="art-container">
						<h1 class="art-title"><?php echo $subject; ?></h1>
						<div class="art-ct mb">
							<?php if($type == 1): ?>
								<div id="gallery" class="ad-gallery">
									<div class="ad-image-wrapper"></div>
									<!-- <div class="ad-controls"></div> -->
									<div class="ad-nav">
										<div class="ad-thumbs">
											<ul class="ad-thumb-list">
												<?php $attachDir = File::getAttachUrl() . '/'; ?>
												<?php foreach ( $pictureData as $key => $picture ): ?>
													<li>
														<a href="<?php echo  $attachDir . File::fileName( $picture['attachment'] ); ?>">
															<img src="<?php echo $attachDir . File::fileName( $picture['attachment'] ); ?>" alt="<?php echo $picture['filename']; ?>" />
															<!-- 此处输出索引和总张数 -->
															<span><em><?php echo $key + 1; ?>/<?php echo count( $pictureData ); ?></em></span>
														</a>
													</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
								</div>
							<?php else: ?>
								<?php echo $content; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
Ibos.app.setPageParam({
		"articleType": 1
	});
</script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/src/emotion.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article_default_show.js?<?php echo VERHASH; ?>'></script>