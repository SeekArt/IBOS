<?php

use application\core\utils\Ibos;
use application\core\utils\Attach;
use application\modules\vote\components\Vote;
use application\core\utils\Env;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/introjs/introjs.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar(); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <!-- Sidebar end -->
        <form id="article_form" action="javascript:;"
              method="post" class="form-horizontal" enctype="multipart/form-data">
            <div class="ct ctform">
                <!-- Row 1 -->
                <div class="row">
                    <div class="span12">
                        <div class="control-group">
                            <label for=""><?php echo $lang['News title']; ?></label>
                            <div>
                                <input id="subject" type="text" name="subject" class="span10" value="">
                                <i class="btn-top" data-action="toTop">顶</i>
                                <i class="btn-highlight" data-action="toHighLight">A</i>
                                <div class="top-input-mc">
                                   <input type="hidden" name="istop" value="0">
                                   <input type="hidden" name="topendtime" value="">
                                </div>
                                <div class="highlight-input-mc">
                                   <input type="hidden" name="ishighlight" value="0">
                                   <input type="hidden" name="highlightstyle" value="">
                                   <input type="hidden" name="highlightendtime" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Row 2 -->
                <div class="row">
                    <div class="span6">
                        <div class="control-group">
                            <label for=""><?php echo $lang['Appertaining category']; ?></label>
                            <select name="catid" id="articleCategory"></select>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <label for=""><?php echo $lang['Approval step']; ?></label>
                            <div class="posr art-cate-approval"></div>
                            <input type="hidden" name="status" value=""/>
                        </div>
                    </div>
                </div>
                <!-- Row 3 -->
                <div class="row">
                    <div class="span12">
                        <div class="control-group">
                            <label for=""><?php echo $lang['Publishing permissions']; ?></label>
                            <input type="text" name="publishscope" value="" id="publishscope">
                        </div>
                    </div>
                </div>
                <!-- Row 4 Tab -->
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
                            <!-- 文本新闻 -->
                            <div id="type_article" class="tab-pane active">
                                <div class="bdbs">
                                    <script id="article_editor" name="content" type="text/plain"></script>
                                </div>
                                <div class="att">
                                    <div class="attb">
                                        <span id="upload_btn"></span>
                                        <button type="button" class="btn btn-icon vat" data-action="selectFile"
                                                data-param='{"target": "#file_target", "input": "#attachmentid"}'>
                                            <i class="o-folder-close"></i>
                                        </button>
                                        <input type="hidden" id="attachmentid" name="attachmentid" value="">
                                        <?php $uploadConfig = Attach::getUploadConfig(); ?>
                                        <span><?php echo $lang['File size limit'] ?><?php echo $uploadConfig['max'] / 1024; ?>
                                            MB</span>
                                    </div>
                                    <div>
                                        <div class="attl" id="file_target"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- 图片新闻 -->
                            <div id="type_pic" class="tab-pane">
                                <div class="fill-nn">
                                    <div class="btn-group pull-right">
                                        <button type="button" id="pic_moveup" class="btn btn-fix"
                                                style="display: none;"><i class="glyphicon-arrow-up"></i></button>
                                        <button type="button" id="pic_movedown" class="btn btn-fix"
                                                style="display: none;"><i class="glyphicon-arrow-down"></i></button>
                                    </div>
                                    <label class="btn checkbox checkbox-inline"><input type="checkbox" data-name="pic"
                                                                                       id=""></label>
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
                            <!-- 超链接新闻 -->
                            <div id="type_url" class="tab-pane fill-nn">
                                <input type="text" id="article_link_url" name="url" value="" placeholder="输入链接地址">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Row 5 -->
                <div class="row">
                    <div class="span4">
                        <div class="control-group">
                            <div class="stand stand-label">
                                <div class="pull-right">
                                    <input type="checkbox" id="voteStatus" value="1"
                                        <?php if (!$this->getVoteInstalled() || !$config['articlevoteenable']): ?>
                                            disabled title="<?php echo $lang['Votes module is not installed or enabled']; ?>"
                                        <?php endif; ?>
                                           name="votestatus" data-toggle="switch">
                                </div>
                                <i class="o-vote"></i>
                                <?php echo Ibos::lang('Vote'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="control-group">
                            <div class="stand stand-label">
                                <!--判断开关初始状态 -->
                                <div class="pull-right">
                                    <input type="checkbox" value="1" id="commentStatus"
                                        <?php if (!$config['articlecommentenable']): ?>
                                            disabled title="<?php echo Ibos::lang('Comments module is not installed or enabled'); ?>"
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
                </div>
                <!-- Row 6 Tab -->
                <div class="art-show-vote"></div>
                <!-- Row 7 Button -->
                <div id="submit_bar" class="clearfix">
                    <button type="button" class="btn btn-large btn-submit pull-left"
                            onclick="history.back();"><?php echo Ibos::lang('Return'); ?></button>
                    <div class="pull-right">
                        <button type="button" data-action="preview"
                                class="btn btn-large btn-submit btn-preview"><?php echo Ibos::lang('Preview'); ?></button>
                        <button type="button" data-action="saveForm"
                                class="btn btn-large btn-submit btn-preview"><?php echo Ibos::lang('Save'); ?></button>
                        <button type="button" data-action="submitForm"
                                class="btn btn-large btn-submit btn-primary"><?php echo Ibos::lang('Submit'); ?></button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="relatedmodule" value="article"/>
            <input type="hidden" name="articleid" value="<?php echo Env::getRequest("articleid"); ?>"/>
        </form>
    </div>
    <!--预览页面-->
</div>
<script type="text/template" id="approval_step">
    <span class="lhf mr">新闻审批流程</span>
    <div class="o-art-approval-line-h">
        <% for (var i = 0, ilen = parseInt(level); i < ilen; i += 1) { %>
        <i class="o-cate-approval-step"></i>
        <% } %>
    </div>
    <div class="fill-nn art-cate-approval-box">
        <div class="mbs art-approval-flow-title">
            <div class="fsl xcn"><%= name %></div>
        </div>
        <div class="art-process-step">
            <div class="art-process-step-list">
                <div class="xwb mbs">审批流程</div>
                <div class="art-step-list mb">
                    <% for (var j = 0, jlen = step.approval.length; j < jlen; j += 1) { %>
                    <% var item = step.approval[j]; %>
                    <div class="art-step-content">
                        <div class="art-step-icon">
                            <i class="o-step-<%= item.id %>"></i>
                        </div>
                        <div class="art-related-person" title="<%= item.name %>"><%= item.name %></div>
                    </div>
                    <% } %>
                </div>
                <div class="art-escape-person mb">
                    <div class="art-step-icon">
                        <i class="o-step-escape"></i>
                    </div>
                    <div class="art-related-person" title="<%= step.free %>"><%= step.free ? step.free :
                        Ibos.l('ART.NOT_SET_APPROVER') %>
                    </div>
                </div>
            </div>
            <div class="approve-description">
                <span class="tcm">审核描述</span>
                <p title="<%= desc %>" class="description-content"><%= desc ? desc : Ibos.l('ART.NO_DATA') %></p>
            </div>
        </div>
    </div>
</script>
<script type="text/template" id="tmpl_file_container">
    <% for(var i = 0, len = fileArr.length; i < len; i +=1 ) { %>
    <% var file_unit = fileArr[i]; %>
    <div class="attl-item" data-node-type="attachItem">
        <a href="javascript:;" title="删除附件" class="cbtn o-trash" data-node-type="attachRemoveBtn"
           data-id="<%= file_unit['aid'] %>"></a>
        <i class="atti"><img width="44" height="44" src="<%= file_unit['iconsmall'] %>"
                             alt="<%= file_unit['filename'] %>" title="<%= file_unit['filename'] %>"></i>
        <div class="attc"><%= file_unit['filename'] %></div>
            <span class="fss mlm">
                <a href="<%= file_unit['downurl'] %>" target="_blank" class="anchor">下载</a>&nbsp;&nbsp;
                <% if ( file_unit['officereadurl']) { %>
                    <a href="javascript:;" data-action="viewOfficeFile" data-param='{"href": "<%= file_unit['
                       officereadurl'] %>"}' title="查看">
                        查看
                </a>
                <% } %>&nbsp;&nbsp;
                <% if ( file_unit['officeediturl']) { %>
                    <a href="javascript:;" data-action="editOfficeFile" data-param='{"href": "<%= file_unit['
                       officeediturl'] %>"}' title="编辑">
                        编辑
                </a>
                <% } %>
            </span>
    </div>
    <% } %>
</script>
<script type="text/template" id="tmpl_pics_container">
    <% for (var i = 0, len = picsArr.length; i < len; i += 1) { %>
        <% var picItem = picsArr[i]; %>
        <div class="attl-item" id="pic_item_<%= picItem['aid'] %>" data-node-type="attachItem">
            <label class="checkbox">
                <input type="checkbox" name="pic"
                       value="<%= picItem['aid'] %>">
            </label>
            <a href="javascript:;" title="删除附件" class="cbtn o-trash"
               data-id="<%= picItem['aid'] %>"
               data-node-type="attachRemoveBtn"></a>
            <img class="pull-left" width="100"
                 src="<%= picItem['filepath'] %>">
            <div class="attc"><%= picItem['filename'] %></div>
        </div>
    <% } %>
</script>
<script type="text/javascript">
    Ibos.app.s({
        'articleid': '<?php echo Env::getRequest("articleid"); ?>',
        'voteInstall': '<?php echo $this->getVoteInstalled() ? 1 : 0;?>',
        'articlevoteenable': '<?php echo $config["articlevoteenable"]; ?>'
    })
</script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/art_form.js?<?php echo VERHASH; ?>'></script>