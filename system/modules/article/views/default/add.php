<?php

use application\core\utils\IBOS;
use application\modules\vote\components\Vote;
?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/introjs/introjs.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
	<!-- Sidebar -->
	<?php echo $this->getSidebar( $this->catid ); ?>
	<!-- Mainer right -->
	<div class="mcr">
	<!-- Sidebar end -->
		<form id="article_form" action="<?php echo $this->createUrl( 'default/add', array( 'op' => 'submit' ) ); ?>" method="post" class="form-horizontal" enctype="multipart/form-data">
			<div class="ct ctform">
				<!-- Row 1 -->
				<div class="row">
					<div class="span8">
						<div class="control-group">
							<label for=""><?php echo $lang['News title']; ?></label>
							<input id="subject" type="text" name="subject" value="">
						</div>
					</div>
					<div class="span4">
						<div class="control-group">
							<label for=""><?php echo $lang['Appertaining category']; ?></label>
							<select name="catid"  id="add_articleCategory">
								<?php echo $categoryOption; ?>
							</select>
						</div>
						<script>$('#add_articleCategory').val('<?php echo $this->catid; ?>')</script>
					</div>
				</div>
				<!-- Row 2 -->
				<div class="row">
					<div class="span12">
						<div class="control-group">
							<label for=""><?php echo $lang['Publishing permissions']; ?></label>
							<input type="text" name="publishScope" value="" id="publishScope">
						</div>
					</div>
				</div>
				<!-- Row 3 Tab -->
				<div class="mb">
					<div>
						<ul class="nav nav-tabs nav-tabs-large nav-justified" id="content_type">
							<input type="hidden" name="type" id="content_type_value" value="0">
							<li class="active">
								<a href="#type_article" data-toggle="tab" data-value="0">
									<i class="o-art-text"></i>
									<?php echo $lang['Article content']; ?>
								</a>
							</li>
							<li>
								<a href="#type_pic" data-toggle="tab" data-value="1">
									<i class="o-art-picm"></i>
									<?php echo $lang['Picture content']; ?>
								</a>
							</li>
							<li>
								<a href="#type_url" data-toggle="tab" data-value="2">
									<i class="o-art-link"></i>
									<?php echo $lang['Hyperlink address']; ?>
								</a>
							</li>
						</ul>
						<div class="tab-content nav-content bdrb">
							<!-- <?php echo $lang['Article content']; ?> -->
							<div id="type_article" class="tab-pane active">
								<div class="bdbs">
									<script id="article_add_editor" name="content" type="text/plain"></script>
								</div>
								<div class="att">
									<div class="attb">
										<span id="upload_btn"></span>
										<button type="button" class="btn btn-icon vat" data-action="selectFile" data-param='{"target": "#file_target", "input": "#attachmentid"}'>
											<i class="o-folder-close"></i>
										</button>
										<input type="hidden" id="attachmentid" name="attachmentid" value="">
										<span><?php echo $lang['File size limit'] ?><?php echo $uploadConfig['max']/1024; ?>MB</span>
									</div>
									<div>
										<div class="attl" id="file_target"></div>
									</div>
								</div>
							</div>
							<!-- <?php echo $lang['Picture content']; ?> -->
							<div id="type_pic" class="tab-pane">
								<div class="fill-nn">
									<div class="btn-group pull-right">
										<button type="button" id="pic_moveup" class="btn btn-fix" style="display: none;"><i class="glyphicon-arrow-up"></i></button>
										<button type="button" id="pic_movedown" class="btn btn-fix" style="display: none;"><i class="glyphicon-arrow-down"></i></button>
									</div>
									<label class="btn checkbox checkbox-inline"><input type="checkbox" data-name="pic" id=""></label>
									<span>
										<i id="pic_upload"></i>
									</span>
									<button type="button" class="btn btn-fix" id="pic_remove" style="display: none;">
										<i class="glyphicon-trash"></i>
									</button>
								</div>
								<div>
									<div id="pic_list" class="art-pic-list"></div>
								</div>
								<input type="hidden" name="picids" id="picids"/>
							</div>

							<!-- <?php echo IBOS::lang( 'Hyperlink address' ); ?> -->
							<div id="type_url" class="tab-pane fill-nn">
								<input type="text" id="article_link_url" name="url" value="" placeholder="输入链接地址">
							</div>
						</div>
					</div>
				</div>
				<!-- Row 4 -->
				<div class="row">
					<div class="span4">
						<div class="control-group">
							<label for="status"><?php echo $lang['Information Status']; ?></label>
							<div>
								<div class="btn-group btn-group-justified" data-toggle="buttons-radio" id="article_status">
									<?php if($aitVerify == 0): ?>
									<label class="btn active">
										<input type="radio" name="status" value="2" <?php if($aitVerify == 0): ?>checked<?php endif;?>>
										<?php echo IBOS::lang( 'Wait verify' ); ?>
									</label>
									<?php endif; ?>
									<label class="btn active" <?php if($aitVerify != 1): ?>style="display:none;"<?php endif; ?>>
										<input type="radio" name="status" value="1" <?php if($aitVerify == 1): ?>checked<?php endif;?>>
										<?php echo IBOS::lang( 'Publish' ); ?>
									</label>
									<label class="btn">
										<input type="radio" name="status" value="3">
										<?php echo IBOS::lang( 'Draft' ); ?>
									</label>
								</div>
							</div>
						</div>
					</div>
					<div class="span4">
						<div class="control-group">
							<div class="stand stand-label">
								<!--判断开关初始状态 -->
								<div class="pull-right">
									<input type="checkbox" value="1" id="commentStatus" 
									<?php if ( !$dashboardConfig['articlecommentenable'] ): ?>
											   disabled title="<?php echo IBOS::lang( 'Comments module is not installed or enabled' ); ?>"
										   <?php else: ?>
											   checked
										   <?php endif; ?>
										   name="commentstatus" data-toggle="switch">
								</div>
								<i class="o-comment"></i>
								<?php echo $lang['Comment']; ?>
							</div>
						</div>
					</div>
					<div class="span4">
						<div class="control-group">
							<div class="stand stand-label">
								<div class="pull-right">
									<input type="checkbox" id="voteStatus" value="1" 
									<?php if ( !$this->getVoteInstalled() || !$dashboardConfig['articlevoteenable'] ): ?>
											   disabled title="<?php echo $lang['Votes module is not installed or enabled']; ?>"
										   <?php endif; ?>
										   name="votestatus" data-toggle="switch">
								</div>
								<i class="o-vote"></i>
								<?php echo IBOS::lang( 'Vote' ); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- Row 5 Tab -->
				<?php if ( $this->getVoteInstalled() && $dashboardConfig['articlevoteenable'] ): ?>
					<?php echo Vote::getView( 'articleAdd' ); ?>
				<?php endif; ?>
				<!-- Row 6 Button -->	
				<div id="submit_bar" class="clearfix">
					<button type="button" class="btn btn-large btn-submit pull-left" onclick="history.back();"><?php echo IBOS::lang( 'Return' ); ?></button>
					<div class="pull-right">
						<button type="button" id="prewiew_submit" class="btn btn-large btn-submit btn-preview"><?php echo IBOS::lang( 'Preview' ); ?></button>
						<button type="submit" class="btn btn-large btn-submit btn-primary"><?php echo IBOS::lang( 'Submit' ); ?></button>
					</div>
				</div>
			</div>
			<input type="hidden" name="relatedmodule" value="article" />
		</form>	
	</div>
	<!--预览页面-->
</div>

<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article_default_add.js?<?php echo VERHASH; ?>'></script>


