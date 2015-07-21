<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['System background setting']?></h1>
	</div>
	<div class="ctb ctbp">
		<h2 class="st">系统背景设置</h2>
		<div class="alert trick-tip">
			<div class="trick-tip-title">
				<i></i>
				<strong><?php echo $lang['System background setting']?></strong>
			</div>
			<div class="trick-tip-content">
				<ul>
					<li>您可以对系统的背景、导航条进行定制，使其更突显企业LOGO。</li>
				</ul>
			</div>
		</div>
		<div>
			<ul class="grid-list pic-list clearfix" id="bgstyle_select_list">
				<li>
					<img src="<?php echo $this->getAssetUrl(); ?>/image/bg_black.png">
					<div class="pic-item-operate bg-item-operate">
						<div class="pull-left operate-wrap">
							<label class="radio">
								<input type="radio" name="bgstyle" <?php if ( $skin == 'black' || empty($skin)): ?>checked<?php endif; ?> value="black" />
								<span>酷炫黑（适合配搭浅色LOGO）</span>
							</label>
						</div>
					</div>
				</li>
				<li>
					<img src="<?php echo $this->getAssetUrl(); ?>/image/bg_white.png">
					<div class="pic-item-operate bg-item-operate">
						<div class="pull-left operate-wrap">
							<label class="radio">
								<input type="radio" name="bgstyle" <?php if ( $skin == 'white' ): ?>checked<?php endif; ?> value="white" />
								<span>闪耀白（适合配搭深色LOGO）</span>
							</label>
						</div>
					</div>
				</li>
			</ul>
		</div>
	</div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_index.js"></script>