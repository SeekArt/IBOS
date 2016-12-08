<?php

use application\core\utils\Ibos;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/officialdoc.css">
<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar($this->catId); ?>
    <!-- Sidebar end -->

    <!-- Mainer right -->
    <div class="mcr">
        <form id="officialdoc_form" action="<?php echo $this->createUrl('officialdoc/add', array('op' => 'save')); ?>"
              method="post" class="form-horizontal">
            <div class="ct ctform">
                <!-- Row 1 -->
                <div class="row">
                    <div class="span12">
                        <div class="control-group">
                            <label for="">通知标题</label>
                            <input type="text" name="subject" id="subject">
                        </div>
                    </div>
                </div>
                <!-- Row 2 -->
                <div class="row">
                    <div class="span8">
                        <div class="control-group">
                            <label for="">通知号</label>
                            <input type="text" name="docNo" id="docNo">
                        </div>
                    </div>
                    <div class="span4">
                        <div class="control-group">
                            <label for=""><?php echo Ibos::lang('Appertaining category'); ?></label>
                            <select name="catid" id="articleCategory">
                                <?php echo $categoryOption; ?>
                            </select>
                            <script>$('#articleCategory').val(<?php echo $this->catId; ?>);</script>
                        </div>
                    </div>
                </div>

                <!-- Row 3 -->
                <div class="row" id="purview_intro">
                    <div class="span8">
                        <div class="control-group">
                            <label for=""><?php echo $lang['Publishing permissions']; ?></label>
                            <input type="text" name="publishScope" value="" id="publishScope">
                        </div>
                    </div>
                    <div class="span4">
                        <div class="control-group">
                            <label><?php echo Ibos::lang('Cc'); ?></label>
                            <input type="text" name="ccScope" value="" id="ccScope">
                        </div>
                    </div>
                </div>
                <!-- Row 4 -->
                <div class="row">
                    <div class="span4">
                        <div class="control-group">
                            <div>
                                <div class="btn-group btn-group-justified" data-toggle="buttons-radio"
                                     id="article_status">
                                    <label class="btn active"
                                           <?php if ($aitVerify != 0): ?>style="display:none"<?php endif; ?>>
                                        <input type="radio" name="status" value="2"
                                               <?php if ($aitVerify == 0): ?>checked<?php endif; ?>>
                                        <?php echo Ibos::lang('Wait verify'); ?>
                                    </label>
                                    <label class="btn active"
                                           <?php if ($aitVerify != 1): ?>style="display:none"<?php endif; ?>>
                                        <input type="radio" name="status" value="1"
                                               <?php if ($aitVerify == 1): ?>checked<?php endif; ?>>
                                        <?php echo Ibos::lang('Publish'); ?>
                                    </label>
                                    <label class="btn">
                                        <input type="radio" name="status" value="3">
                                        <?php echo Ibos::lang('Draft'); ?>
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
                                        <?php if (!$dashboardConfig['doccommentenable']): ?>
                                            disabled title="<?php echo Ibos::lang('Comments module is not installed or enabled'); ?>"
                                        <?php else: ?>
                                            checked
                                        <?php endif; ?>
                                           name="commentstatus" data-toggle="switch" class="visi-hidden">
                                </div>
                                <?php echo Ibos::lang('Comment'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="span4">
                        <select name="rcid" id="rc_type">
                            <option value="0">选择套红模板</option>
                            <?php foreach ($RCData as $tcType): ?>
                                <option value="<?php echo $tcType['rcid']; ?>"><?php echo $tcType['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <script>$('#rc_type').val(<?php echo isset($data) ? $data['rctype'] : ''; ?>);</script>
                    </div>
                </div>
                <!-- Row 5 Editor -->
                <!-- 文章内容 -->
                <div class="mb">
                    <div class="tab-content nav-content bdrb">
                        <div id="type_article" class="tab-pane active">
                            <div class="bdbs">
                                <script id="officialdoc_add_editor" name="content" type="text/plain">
								<?php // echo isset( $data ) ? $data['content'] : ''; ?>
								
                                
                                </script>
                            </div>
                            <!-- 附件上传-->
                            <div class="att">
                                <div class="attb">
                                    <span id="upload_btn"></span>
                                    <button type="button" class="btn btn-icon vat" data-action="selectFile"
                                            data-param='{"target": "#file_target", "input": "#attachmentid"}'>
                                        <i class="o-folder-close"></i>
                                    </button>
                                    <input type="hidden" id="attachmentid" name="attachmentid" value="">
                                    <span><?php echo $lang['File size limit'] ?><?php echo $uploadConfig['max'] / 1024; ?>
                                        MB</span>
                                </div>
                                <div>
                                    <div class="attl" id="file_target"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Row 6 Button -->
                <div id="submit_bar" class="clearfix">
                    <button type="button" class="btn btn-large btn-submit pull-left"
                            onclick="history.back();"><?php echo Ibos::lang('Return'); ?></button>
                    <div class="pull-right">
                        <button type="button" data-action="preview"
                                class="btn btn-large btn-submit"><?php echo Ibos::lang('Preview'); ?></button>
                        <button type="submit" id="article_submit"
                                class="btn btn-large btn-submit btn-primary"><?php echo Ibos::lang('Submit'); ?></button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="relatedmodule" value="officialdoc"/>
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
<script src="<?php echo $assetUrl; ?>/js/officialdoc.js"></script>
<script src="<?php echo $assetUrl; ?>/js/doc_officialdoc_add.js"></script>

