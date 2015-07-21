<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/quicknav.css?<?php echo VERHASH; ?>">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Quicknav setting']; ?></h1>
	</div>
	<div>
		<form action="<?php echo $this->createUrl('quicknav/edit'); ?>" method="post" class="form-horizontal">
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Custom navigation']; ?></h2>
				<!-- @Todo: 据说这里需要程序端做判断 -->
				<div class="alert trick-tip clearfix">
					<div class="trick-tip-title">
						<strong>技巧提示</strong>
					</div>
					<div class="trick-tip-content">
						<ul>
							<li>
								链接地址使用 <strong>javascript: moduleEntry("workflow", 表单ID, 标题);</strong> 的格式可以快速新建工作流表单
							</li>
						</ul>
					</div>
				</div>
				<div class="ctbw">
					<div class="control-group">
						<label class="control-label"><?php echo $lang['The application name']; ?></label>
						<div class="controls">
							<input type="text" name="name" value="<?php echo $menu['name']; ?>" id="qn_name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['The link address']; ?></label>
						<div class="controls">
							<input type="text" name="url" value="<?php echo $menu['url']; ?>">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label"><?php echo $lang['Effect of icon preview']; ?></label>
						<!-- if 自定义上传图标  -->
						<div class="controls">
							<div class="pull-left">
								<div id="qn_icon_preview" class="quicknav-icon-preview" style="display: none;"></div>
								<input type="hidden" id="qn_icon_color" name="quicknavcolor">
								<input type="hidden" id="qn_icon_text" name="fontvalue" value="">
								<div id="qn_img_preview" class="quicknav-img-preview">
									<img src="<?php echo $menu['icon']; ?>" title="<?php echo $menu['icon']; ?>" alt="<?php echo $menu['icon']; ?>">
								</div>
								<input type="hidden" id="qn_img_value" name="quicknavimg" value="<?php echo $menu['icon']; ?>">
							</div>
							<div class="pull-right">
								<span id="qn_upload"></span>
								<button type="button" id="reset_qn_upload" class="btn vat"><?php echo $lang['Reset']; ?></button>
							</div>
						</div>
						<!-- esle 默认图标 -->
						<!--
						<div class="controls">
							<div class="pull-left">
								<div id="qn_icon_preview" class="quicknav-icon-preview" style="background-color: #3497DB;"></div>
								<input type="hidden" name="quicknavcolor">
								<div id="qn_img_preview" class="quicknav-img-preview"></div>
								<input type="hidden" id="qn_img_value" name="quicknavimg">
							</div>
							<div class="pull-right">
								<span id="qn_upload"></span>
								<button id="reset_qn_upload" class="btn vat" style="display: none;">重置</button>
							</div>
						</div>
						-->
					</div>
					<div class="control-group">
						<label for="" class="control-label"></label>
						<div class="controls">
							<button type="submit" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
							<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
							<input type="hidden" name="id" value="<?php echo $menu['id']; ?>" />
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js"></script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js"></script>
<script src="<?php echo $assetUrl; ?>/js/quicknav_edit.js?<?php echo VERHASH; ?>"></script>
<script>
$(function() {
	var name = $("#qn_name").val();
	$("#qn_icon_preview").html(name.substr(0, 2));
	$("#qn_icon_text").val(name.substr(0, 2));
});	
</script>