<?php 
use application\core\utils\File;
?>
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Stamp manage']; ?></h1>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'sysstamp/index' ); ?>" class="form-horizontal" method="post">
			<div class="ctb" id="stamp_setup">
				<h2 class="st"><?php echo $lang['Stamp manage']; ?></h2>
				<div>
					<ul class="grid-list stamp-list clearfix" id="stamp_list">
						<?php if ( !empty( $list ) ): ?>
							<?php foreach ( $list as $key => $stamp ): ?>
								<li>
									<div class="stamp-item-hd">
										<div class="row">
											<div class="span4">
												<input type="text" name="stamps[<?php echo $stamp['id']; ?>][sort]" value="<?php echo $stamp['sort']; ?>" class="input-small" />
											</div>
											<div class="span8">
												<input type="text" name="stamps[<?php echo $stamp['id']; ?>][code]" value="<?php echo $stamp['code']; ?>" class="input-small" />
											</div>
										</div>
									</div>
									<div class="stamp-img">
										<div class="stamp-img-uploadbg"></div>
										<div class="stamp-img-upload" id="stamp_upload_<?php echo $stamp['id']; ?>_wrap">
											<span id="stamp_upload_<?php echo $stamp['id']; ?>"></span>
										</div>
										<img <?php if ( !empty( $stamp['stamp'] ) ): ?>src="<?php echo File::fileName( $stampUrl . $stamp['stamp'] ); ?>"<?php endif; ?> alt="<?php echo $stamp['code']; ?>" />
										<input type="hidden" name="stamps[<?php echo $stamp['id']; ?>][stamp]" value="<?php echo $stamp['stamp']; ?>" />
									</div>
									<div class="stamp-icon">
										<div class="stamp-icon-uploadbg"></div>
										<div class="stamp-icon-upload" id="stamp_icon_upload_<?php echo $stamp['id']; ?>_wrap">
											<span id="stamp_icon_upload_<?php echo $stamp['id']; ?>"></span>
										</div>
										<img <?php if ( !empty( $stamp['icon'] ) ): ?>src="<?php echo File::fileName( $stampUrl . $stamp['icon'] ); ?>"<?php endif; ?> alt="<?php echo $stamp['code']; ?>" />
										<input type="hidden" name="stamps[<?php echo $stamp['id']; ?>][icon]" value="<?php echo $stamp['icon']; ?>" />
									</div>
									<?php if ( $stamp['system'] == 0 ): ?>
										<div class="stamp-item-ft">
											<div class="pull-right">
												<a href="javascript:void(0);" data-type="old" data-id="<?php echo $stamp['id']; ?>" data-act="del" class="cbtn o-trash" title="<?php echo $lang['Del']; ?>"></a>
											</div>
										</div>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
						<li class="stamp-item-add">
							<a href="javascript:;" class="upload-add" id="upload_add">
								<i class="upload-add-icon"></i>
								<p class="upload-add-desc"><?php echo $lang['Add stamp']; ?></p>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<!-- <div id="upload_btn"></div> -->
			<div>
				<input type="hidden" name="removeId" id="removeId" />
				<button type="submit" name="stampSubmit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
			</div>
		</form>
	</div>
</div>
<!-- “添加图章”模板 -->
<script type="text/template" id="upload_add_tpl">
	<li>
	<div class="stamp-item-hd">
	<div class="row">
	<div class="span4">
	<input type="text" name="newstamps[<%=id%>][sort]" value="<%= sort+1 %>" class="input-small" />
	</div>
	<div class="span8">
	<input type="text" name="newstamps[<%=id%>][code]" value="" class="input-small">
	</div>
	</div>
	</div>
	<div class="stamp-img stamp-img-new">
	<div class="stamp-img-uploadbg"></div>
	<div class="stamp-img-upload" id="stamp_upload_<%=id%>_wrap">
	<span id="stamp_upload_<%=id%>"></span>
	</div>
	<img src="" alt="">
	<input type="hidden" name="newstamps[<%=id%>][stamp]" value="" />
	</div>
	<div class="stamp-icon stamp-icon-new">
	<div class="stamp-icon-uploadbg"></div>
	<div class="stamp-icon-upload" id="stamp_icon_upload_<%=id%>_wrap">
	<span id="stamp_icon_upload_<%=id%>"></span>
	</div>
	<img src="" alt="">
	<input type="hidden" name="newstamps[<%=id%>][icon]" value="" />
	</div>
	<div class="stamp-item-ft">
	<div class="pull-right">
	<a href="javascript:;" data-id="<%=id%>" data-act="del" class="cbtn o-trash" title="<?php echo $lang['Del']; ?>"></a>
	</div>
	</div>
	</li>
</script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>"></script>
<script>
Ibos.app.s({"maxSort" : <?php echo $maxSort; ?>});
</script>
<script src="<?php echo $assetUrl; ?>/js/db_sysstamp.js"></script>