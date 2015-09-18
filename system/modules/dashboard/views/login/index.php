<?php 
use application\core\utils\File;
?>
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Login page setting']; ?></h1>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'login/index' ); ?>" method="post" class="form-horizontal">
			<!-- 登录页设置 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Login page setting']; ?></h2>
				<div class="alert trick-tip">
					<div class="trick-tip-title">
						<i></i>
						<strong><?php echo $lang['Skills prompt']; ?></strong>
					</div>
					<div class="trick-tip-content">
						<?php echo $lang['Login tip']; ?>
					</div>
				</div>
				<div>
					<ul class="grid-list pic-list clearfix" id="pic_list">
						<?php if ( !empty( $list ) ): ?>
							<?php foreach ( $list as $bg ): ?>
								<li>
									<img <?php if ( !empty( $bg['image'] ) ): ?>src="<?php echo File::fileName( 'data/login/' . $bg['image'] ); ?>"<?php endif; ?> />
									<input type="hidden" name="bgs[<?php echo $bg['id']; ?>][image]" value="<?php echo $bg['image']; ?>" />
									<div class="pic-item-body">
										<div class="pic-upload-bg"></div>
										<div class="pic-upload-btn">
											<i class="o-img-default"></i>
											<p><?php echo $lang['Change background']; ?></p>
										</div>
										<div class="pic-upload-holder" id="pic_upload_<?php echo $bg['id']; ?>_wrap">
											<span id="pic_upload_<?php echo $bg['id']; ?>"></span>
										</div>
									</div>
									<div class="pic-item-operate">
										<div class="pull-left">
											<input type="checkbox" name="bgs[<?php echo $bg['id']; ?>][disabled]" value="0" data-toggle="switch" class="visi-hidden" <?php if ( $bg['disabled'] == '0' ) : ?>checked<?php endif; ?>>
										</div>
										<?php if ( $bg['system'] == '0' ) : ?>
											<div class="pull-right">
												<a href="javascript:;" data-target="pic_upload_<?php echo $bg['id']; ?>" data-id="<?php echo $bg['id']; ?>" class="cbtn o-trash"></a>
											</div>
										<?php endif; ?>
									</div>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
						<li class="pic-item-add">
							<a href="javascript:;" class="upload-add" id="pic_upload_add">
								<i class="upload-add-icon"></i>
								<p class="upload-add-desc"><?php echo $lang['Add new login background']; ?></p>
							</a>
						</li>
					</ul>
				</div>
				<div>
					<input type="hidden" name="removeId" id="removeId" />
					<button type="submit" name="loginSubmit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/template" id="pic_upload_tpl">
			<li class="pic-item-new">
				<img src="" alt="">
				<input type="hidden" name="newbgs[<%=picid%>][image]" />
				<div class="pic-item-body">
					<div class="pic-upload-bg"></div>
					<div class="pic-upload-btn">
						<i class="o-img-default-large"></i>
						<p><?php echo $lang['Upload background image']; ?></p>
					</div>
					<div class="pic-upload-holder" id="pic_upload_<%=picid%>_wrap">
						<span id="pic_upload_<%=picid%>"></span>
					</div>
				</div>
				<div class="pic-item-operate">
					<div class="pull-left">
						<input type="checkbox" name="newbgs[<%=picid%>][disabled]" value="0" data-toggle="switch" class="visi-hidden" checked>
					</div>
					<div class="pull-right">
						<a href="javascript:;" class="cbtn o-trash" data-target="pic_upload_<%=picid%>"></a>
					</div>
				</div>
			</li>
</script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/db_login.js"></script>