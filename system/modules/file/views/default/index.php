<?php 

use application\core\utils\Convert;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/file_cabinets.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="fmc">
	<div class="page-list">
		<div class="page-list-header">
			<div class="fc-dyna-bar">
				<span class="fc-dyna-tt">
					<i class="o-ol-trumpet"></i>
					<strong>最新动态：</strong>
				</span>
				<div class="btn-group pull-right">
					<button class="btn btn-small" id="prev_dyna_btn">
						<i class="o-ol-chevron-up"></i>
					</button>
					<button class="btn btn-small" id="next_dyna_btn">
						<i class="o-ol-chevron-down"></i>
					</button>
				</div>
				<ul class="fc-dyna-list"></ul>
			</div>
		</div>
		<div class="page-list-mainer">
			<div class="fc-topbar clearfix">
				<div class="fc-nav">
					<a href="<?php echo $this->createUrl( "company/index" ); ?>" class="current">文件柜</a>
				</div>
			</div>
			<div class="fc-list-cell">
				<div class="list-thumb clearfix">
					<a href="<?php echo $this->createUrl( "company/index" ); ?>" class="file-disk-ent">
						<i class="nd-type o-disk-company" title="公司网盘"></i>
						<div class="nd-name">公司网盘</div>
					</a>
					<a href="<?php echo $this->createUrl( "personal/index" ); ?>" class="file-disk-ent file-disk-info">
						<i class="nd-type o-disk-personal" title="个人网盘"></i>
						<div class="nd-name">个人网盘</div>
						<div class="nd-info">
							<div class="progress mbm">
								<div class="progress-bar <?php if ($usedSize / $userSize * 100 >= 80): ?>progress-bar-danger<?php endif; ?>" style="width: <?php echo $usedSize / $userSize * 100; ?>%;"></div>
							</div>
							<div class="nd-size">
								<span class="xcn"><?php echo Convert::sizeCount( $usedSize ); ?></span>
								/
								<span class="tcm"><?php echo Convert::sizeCount( $userSize ); ?></span>
							</div>
						</div>
					</a>
					<a href="<?php echo $this->createUrl( "myshare/index" ); ?>" class="file-disk-ent">
						<i class="nd-type o-disk-share" title="我分享的"></i>
						<div class="nd-name">我分享的</div>
					</a>
					<a href="<?php echo $this->createUrl( "fromshare/index" ); ?>" class="file-disk-ent">
						<?php if ( $hasNewShare ): ?>
							<span class="bubble">NEW</span>
						<?php endif; ?>
						<i class="nd-type o-disk-receive" title="我收到的"></i>
						<div class="nd-name">我收到的</div>
						<div class="nd-info">
							<div class="progress mbm">
								<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%;"></div>
							</div>
							<div class="nd-size">
								73GB / <span class="tcm">122GB</span>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<script src='<?php echo $this->getAssetUrl(); ?>/js/cabinet_disk.js?<?php echo VERHASH; ?>'></script>
