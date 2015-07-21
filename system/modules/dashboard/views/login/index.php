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
<script>
(function() {
	//Init, Add, Create, Delete
	// 配置
	var picUploadSettings = {
		upload_url: "<?php echo $this->createUrl( 'login/index', array( 'op' => 'upload' ) ); ?>",
		button_width: 140,
		file_post_name: "bg",
		button_image_url: "",
		button_height: 120,
		custom_settings: {
			success: function(data, file) {
				var id = this.movieName,
						$holderWrap = $("#" + this.button_placeholder_wrap_id),
						$img = $holderWrap.parent().siblings("img"),
						$item = $img.parent();
				// 移除对应样式
				$item.removeClass("pic-item-new").find(".o-img-default-large").attr("class", "o-img-default");
				// 调整控件宽高
				$("#" + id).attr("width", 140).attr("height", 120);
				$img.attr("src", file.url);
				$($img.next('input[type=hidden]').get(0)).val(file.fakeUrl);
			}
		}
	};

	var picSelector = ".pic-upload-holder span", picList = $("#pic_list"), picUploadHolders = picList.find(picSelector);
	// Init
	PicUpload.init(picUploadHolders, picUploadSettings);
	// Add
	var picUploadAdd = function(data, lastItem) {
		var tpl = $.template("pic_upload_tpl", data), $node = $(tpl), $holder = $node.find(picSelector);
		$node.find("[data-toggle='switch']").iSwitch("");
		$node.insertBefore(lastItem);
		PicUpload.create($holder, $.extend({}, picUploadSettings, {
			button_width: 320,
			button_height: 170
		}));
	};
	$("#pic_upload_add").on("click", function() {
		var lastItem = $(this).parent(),
			// 用于模板替换的键值集
			data = {picid: +new Date()};
		picUploadAdd(data, lastItem);
	});
	//Remove
	picList.on("click", ".o-trash", function() {
		var $el = $(this), picId = $el.data("target"), id = $el.data('id'), removeIdObj = $('#removeId');
		if (id) {
			var removeId = removeIdObj.val(), removeIdSplit = removeId.split(',');
			removeIdSplit.push(id);
			removeIdObj.val(removeIdSplit.join());
		}
		PicUpload.remove(picId, function() {
			$el.parents("li").first().remove();
		});
	});
})();
</script>