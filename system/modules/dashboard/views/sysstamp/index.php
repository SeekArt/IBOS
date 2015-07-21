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
											<span id="stamp_upload_<?php echo $stamp['id']; ?>">
										</div>
										<img <?php if ( !empty( $stamp['stamp'] ) ): ?>src="<?php echo File::fileName( $stampUrl . $stamp['stamp'] ); ?>"<?php endif; ?> alt="<?php echo $stamp['code']; ?>" />
										<input type="hidden" name="stamps[<?php echo $stamp['id']; ?>][stamp]" value="<?php echo $stamp['stamp']; ?>" />
									</div>
									<div class="stamp-icon">
										<div class="stamp-icon-uploadbg"></div>
										<div class="stamp-icon-upload" id="stamp_icon_upload_<?php echo $stamp['id']; ?>_wrap">
											<span id="stamp_icon_upload_<?php echo $stamp['id']; ?>">
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
	<input type="text" name="newstamps[<%=id%>][sort]" value="<%=sort%>" class="input-small" />
	</div>
	<div class="span8">
	<input type="text" name="newstamps[<%=id%>][code]" value="" class="input-small">
	</div>
	</div>
	</div>
	<div class="stamp-img stamp-img-new">
	<div class="stamp-img-uploadbg"></div>
	<div class="stamp-img-upload" id="stamp_upload_<%=id%>_wrap">
	<span id="stamp_upload_<%=id%>">
	</div>
	<img src="" alt="">
	<input type="hidden" name="newstamps[<%=id%>][stamp]" value="" />
	</div>
	<div class="stamp-icon stamp-icon-new">
	<div class="stamp-icon-uploadbg"></div>
	<div class="stamp-icon-upload" id="stamp_icon_upload_<%=id%>_wrap">
	<span id="stamp_icon_upload_<%=id%>">
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
	(function() {
		// 图章上传
		var stampUploadSettings = {
			upload_url: Ibos.app.url("dashboard/sysstamp/index", { op: "upload" }),
			file_post_name: 'stamp',
			button_image_url: "",
			button_width: 200,
			button_height: 120,
			custom_settings: {
				success: function(data, file) {
					var uploadBtnWrapId = this.button_placeholder_wrap_id,
						uploadBtnWrap = $("#" + uploadBtnWrapId),
						uploadWrap = uploadBtnWrap.parent();
					uploadWrap.removeClass("stamp-img-new").find("img").attr("src", file.url);
					$(uploadWrap.find('input[type=hidden]').get(0)).val(file.fakeUrl);
				}
			}
		};

		// Init
		var $stampList = $("#stamp_list"),
			stampUploadSelector = ".stamp-img-upload span",
			$stampUploadHolders = $stampList.find(stampUploadSelector);

		PicUpload.init($stampUploadHolders, stampUploadSettings);

		// 小图章上传
		var stampIconUploadSettings = {
			upload_url: Ibos.app.url("dashboard/sysstamp/index", { op: "upload" }),
			file_post_name: 'stamp',
			button_image_url: "",
			button_width: 200,
			button_height: 40,
			custom_settings: {
				success: function(data, file) {
					var uploadBtnWrapId = this.button_placeholder_wrap_id,
						uploadBtnWrap = $("#" + uploadBtnWrapId),
						uploadWrap = uploadBtnWrap.parent();
					uploadWrap.removeClass("stamp-icon-new").find("img").attr("src", file.url);
					$(uploadWrap.find('input[type=hidden]').get(0)).val(file.fakeUrl);
				}
			}
		}

		// Init
		var $stampIconList = $("#stamp_list"),
			stampIconUploadSelector = ".stamp-icon-upload span";
		$stampIconUploadHolders = $stampList.find(stampIconUploadSelector);
		PicUpload.init($stampIconUploadHolders, stampIconUploadSettings);
		var stampPrefix = "stamp_upload_",
				stampIconPrefix = "stamp_icon_upload_",
				swfUploadSet = SWFUpload.instances;
		// 删除图章
		$stampList.on("click", ".o-trash", function() {
			var $el = $(this),
					uploadId = $el.attr("data-id"),
					isOld = $el.attr("data-type"),
					hasRemoveStamp = false,
					removeIdObj = $('#removeId');
			// 销毁对应SWFUpload对象
			PicUpload.remove((stampPrefix + uploadId), function() {
				hasRemoveStamp = true;
			});
			PicUpload.remove((stampIconPrefix + uploadId), function() {
				if (hasRemoveStamp) {
					// 移除对应节点
					$el.parents("li").first().remove();
				}
			});
			if (isOld) {
				var removeId = removeIdObj.val(),
						removeIdSplit = removeId.split(',');
				removeIdSplit.push(uploadId);
				removeIdObj.val(removeIdSplit.join());
			}
		});

		// 添加一个图章
		var stampAdd = function(data, lastItem) {
			var uploadTpl = $.template("upload_add_tpl", data),
					uploadNode = $(uploadTpl),
					stampUploadHolder,
					stampIconUploadHolder;
			// 需先插入到文档再初始化
			uploadNode.insertBefore(lastItem);
			stampUploadHolder = uploadNode.find(stampUploadSelector);
			stampIconUploadHolder = uploadNode.find(stampIconUploadSelector);
			// 初始化其SWFUpload对象
			PicUpload.create(stampUploadHolder, stampUploadSettings);
			PicUpload.create(stampIconUploadHolder, stampIconUploadSettings);
		};
		var $uploadBtn = $("#upload_add"),
			$stampItemLast = $uploadBtn.parent(),
			stampGid = <?php echo $maxSort; ?>;
		$uploadBtn.on("click", function() {
			stampAdd({id: +new Date(), sort: stampGid++}, $stampItemLast);
		});
	})();
</script>
