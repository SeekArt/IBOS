<?php

use application\core\utils\IBOS;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/officialdoc.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">

<!-- Mainer -->
<div class="mc clearfix">
	<!-- Sidebar -->
	<?php echo $this->getSidebar( $this->catId ); ?>
	<!-- Sidebar end -->

	<!-- Mainer right -->
	<div class="mcr">
		<form id="officialdoc_form" action="<?php echo $this->createUrl( 'officialdoc/edit', array( 'op' => 'update' ) ); ?>" method="post" class="form-horizontal">
			<div class="ct ctform">
				<!-- Row 1 -->
				<div class="row">
					<div class="span12">
						<div class="control-group">
							<label for=""><?php echo IBOS::lang( 'News title' ); ?></label>
							<input type="text" name="subject" id="subject" value="<?php echo $data['subject']; ?>">
						</div>
					</div>
				</div>
				<!-- Row 2 -->
				<div class="row">
					<div class="span8">
						<div class="control-group">
							<label for="">公文号</label>
							<input type="text" name="docNo" id="docNo" value="<?php echo $data['docno']; ?>">
						</div>
					</div>
					<div class="span4">
						<div class="control-group">
							<label for=""><?php echo IBOS::lang( 'Appertaining category' ); ?></label>
							<select name="catid"  id="articleCategory">
								<?php echo $categoryOption; ?>
							</select>
							<script>$('#articleCategory').val(<?php echo $data['catid']; ?>);</script>
						</div>
					</div>
				</div>
				<!-- Row 3 -->
				<div class="row">
					<div class="span8">
						<div class="control-group">
							<label for=""><?php echo IBOS::lang( 'Publishing permissions' ); ?></label>
							<input type="text" name="publishScope" value="<?php echo $data['publishScope']; ?>" id="publishScope">
							<div id="publishScope_box"></div>
						</div>
					</div>
					<div class="span4">
						<div class="control-group">
							<label><?php echo IBOS::lang( 'Cc' ); ?></label>
							<input type="text" name="ccScope" value="<?php echo $data['ccScope']; ?>" id="ccScope">
							<div id="ccScope_box"></div>
						</div>
					</div>
				</div>
				<!-- Row 4 -->
				<div class="row">
					<div class="span4">
						<div class="control-group">
							<div>
								<div class="btn-group btn-group-justified" data-toggle="buttons-radio" id="article_status">
										<label class="btn <?php if( $aitVerify == 0 && $data['status'] != 3): ?>active<?php endif; ?>" <?php if($aitVerify != 0): ?>style="display:none"<?php endif;?>>
                                            <input type="radio" name="status" value="2" <?php if( $aitVerify == 0 ): ?>checked<?php endif; ?>>
                                            <?php echo IBOS::lang( 'Wait verify'); ?>
                                        </label>
										<label class="btn <?php if( $aitVerify == 1 && $data['status'] != 3): ?>active<?php endif; ?>" <?php if($aitVerify != 1): ?>style="display:none"<?php endif;?>>
                                            <input type="radio" name="status" value="1" <?php if( $aitVerify == 1 ): ?>checked<?php endif; ?>>
                                            <?php echo IBOS::lang( 'Publish'); ?>
                                        </label>
                                        <label class="btn <?php if( $data['status'] == 3 ): ?>active<?php endif; ?>">
                                            <input type="radio" name="status" value="3" <?php if( $data['status'] == 3 ): ?>checked<?php endif; ?>>
                                            <?php echo IBOS::lang( 'Draft'); ?>
                                        </label>
								</div>
							</div>
						</div>
					</div>
					<div class="span4">
						<div class="control-group">
							<div class="stand">
								<div class="pull-right">
									<input type="checkbox" value="1" id="commentStatus"
									<?php if ( !$dashboard['doccommentenable'] ): ?>
											   disabled title="<?php echo IBOS::lang( 'Comments module is not installed or enabled' ); ?>"
										   <?php elseif ( $data['commentstatus'] ): ?>
											   checked
										   <?php endif; ?>
										   name="commentstatus" data-toggle="switch" class="visi-hidden">
								</div>
								<?php echo IBOS::lang( 'Comment' ); ?>
							</div>
						</div>
					</div>
					<div class="span4">
						<select name="rcid" id="rc_type">
							<option value="0">选择套红模板</option>
							<?php foreach ( $RCData as $reType ): ?>
								<option value="<?php echo $reType['rcid']; ?>"><?php echo $reType['name']; ?></option>
							<?php endforeach; ?>
						</select>
						<script>$('#rc_type').val(<?php echo $data['rcid']; ?>);</script>
					</div>
				</div>
				<!-- Row 4 -->
				<div>
					<!-- 文章内容 -->
					<div class="mb">
						<div class="tab-content nav-content bdrb">
							<div id="type_article" class="tab-pane active">
								<div class="bdbs">
									<script id="editor" name="content" type="text/plain"><?php echo $data['content']; ?></script>
								</div>
								<div class="att">
									<div class="attb">
										<span id="upload_btn"></span>
										<button type="button" class="btn btn-icon vat" data-action="selectFile" data-param='{"target": "#file_target", "input": "#attachmentid"}'>
										    <i class="o-folder-close"></i>
										</button>
										<input type="hidden" name="attachmentid" id="attachmentid" value="<?php echo $data['attachmentid']; ?>">
										<span><?php echo $lang['File size limit'] ?><?php echo $uploadConfig['max'] / 1024; ?>MB</span>
									</div>
									<div class="attl" id="file_target">
										<?php if ( isset( $attach ) ): ?>
											<?php foreach ( $attach as $value ): ?>
												<div class="attl-item" data-node-type="attachItem">
													<a href="javascript:;" title="删除附件" class="cbtn o-trash" data-node-type="attachRemoveBtn" data-id="<?php echo $value['aid']; ?>" ></a>
													<i class="atti"><img width="32" height="32" src="<?php echo $value['iconsmall']; ?>" alt="<?php echo $value['filename']; ?>" title="<?php echo $value['filename']; ?>"></i>
													<div class="attc"><?php echo $value['filename']; ?></div>
													<span class="fss mlm">
														<a href="<?php echo $value['downurl']; ?>" class="anchor">下载</a>&nbsp;&nbsp;
			                                            <?php if (isset($value['officereadurl'])): ?>
			                                                <a href="javascript:;" data-action="viewOfficeFile" data-param='{"href": "<?php echo $value['officereadurl']; ?>"}' title="<?php echo $lang['View']; ?>">
			                                                    <?php echo $lang['View']; ?>
			                                                </a>
			                                            <?php endif; ?>&nbsp;&nbsp;
			                                            <?php if (isset($value['officeediturl'])): ?>
			                                                <a href="javascript:;" data-action="editOfficeFile" data-param='{"href": "<?php echo $value['officeediturl']; ?>"}' title="<?php echo $lang['Edit']; ?>">
			                                                    <?php echo $lang['Edit']; ?>
			                                                </a>
			                                            <?php endif; ?>
													</span>
												</div>
											<?php endforeach; ?>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- 修改理由下拉框 -->
				<div id="alter_reason" style="display:none;">
					<select id="reason" class="alter-reason-select">
						<option value="改正错误">改正错误</option>
						<option value="补充必要的信息">补充必要的信息</option>
						<option value="改进标点或格式">改进标点或格式</option>
					</select>
				</div>
				<!-- Row 5 Button -->
				<div id="submit_bar" class="clearfix">
					<button type="button" onclick="history.go(-1);" class="btn btn-large btn-submit pull-left"><?php echo IBOS::lang( 'Return' ); ?></button>
					<div class="pull-right">
						<button type="button" id="prewiew_submit" data-action="preview" class="btn btn-large btn-submit"><?php echo IBOS::lang( 'Preview' ); ?></button>
						<button type="submit" class="btn btn-large btn-submit btn-primary" id="edit_submit"><?php echo IBOS::lang( 'Submit' ); ?></button>
					</div>
				</div>
			</div>
			<input type="hidden" name="docid" value="<?php echo $data['docid']; ?>">
			<input type="hidden" name="version" value="<?php echo $data['version']; ?>">
			<input type="hidden" name="relatedmodule" value="officialdoc" />
			<input type="hidden" name="relatedid" value="<?php echo $data['docid']; ?>">
			<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>">
			<input type="hidden" name="reason" value="">
		</form>
	</div>
</div>

<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>

<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH; ?>'></script>

<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>

<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js"></script>
<script src="<?php echo $assetUrl; ?>/js/officialdoc.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/doc_officialdoc_edit.js?<?php echo VERHASH; ?>"></script>
