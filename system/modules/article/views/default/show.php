<?php

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\model\User;
use application\modules\vote\components\Vote;
use application\modules\article\controllers\comment;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->
<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar($this->catid); ?>
    <!-- Sidebar -->

    <!-- Mainer right -->
    <?php $articleId = Env::getRequest('articleid'); ?>
    <div class="mcr">
        <form action="" class="form-horizontal">
            <div class="ct ctview ctview-art">
                <!-- 文章 -->
                <div class="art"></div>
            </div>
            <input type="hidden" name="articleid" id="articleid" value="<?php echo $articleId; ?>">
            <input type="hidden" name="relatedid" id="relatedid" value="<?php echo $articleId; ?>">
        </form>
    </div>
</div>

<script type="text/template" id="tpl_art_content">
	<div class="art-container">
		<% if (tableType == 'reback_to') { %>
			<div class="da-stamp">
			    <span><img src="data/stamp/011.png" width="150px" height="90px"></span>
			</div>
		<% } %>
        <a href="javascript:" title="<?php echo $lang['Close']; ?>" class="art-close" onclick="window.location.href=document.referrer;"></a>
        <h1 class="art-title ellipsis"><%= subject %></h1>
        <div class="art-ct mb message-content editor-content text-break">
        	<% if (type == 0) { %>
        		<%= content %>
        	<% } else if (type == 1) { %>
        		<div id="gallery" class="ad-gallery">
                    <div class="ad-image-wrapper"></div>
                    <div class="ad-nav">
                        <div class="ad-thumbs">
                            <ul class="ad-thumb-list">
                            	<% for (var j = 0, jlen = pictureData.length; j < jlen; j += 1) { %>
                                    <% var picItem = pictureData[j]; %>
                                    <li>
                                        <a href="<%= picItem.filepath %>">
                                            <img src="<%= picItem.filepath %>" alt="<%= picItem.filename %>"/>
                                            <span><em><%= (j + 1) + '/' + jlen %></em></span>
                                        </a>
                                    </li>
                                <% } %>
                            </ul>
                        </div>
                    </div>
                </div>
        	<% } else if (type == 2) { %>
        		<% window.location.href = url; %>
        	<% } %>
        </div>
        <% var len, item; %>
        <% if (len = attach.length) { %>
            <div class="fill noprint">
                <h3 class="ctbt">
                    <i class="o-paperclip"></i>
                    <strong>附件</strong>（<%= len %>个）
                </h3>
                <ul class="attl">
                	<% for (var i = 0; i < len; i += 1 ) { %>
                		<% item = attach[i]; %>
                        <li>
                            <i class="atti">
                                <img src="<%= item['iconsmall'] %>" alt="<%= item['filename'] %>">
                            </i>
                            <div class="attc">
                                <div class="mbm">
                                    <%= item['filename'] %>
                                    <span class="tcm">(<%= item['filesize'] %>)</span>
                                </div>
                                <span class="fss">
									<a href="<%= item['downurl'] %>" target="_blank" class="anchor">下载</a>&nbsp;&nbsp;
                                    <% if (item['officereadurl']) { %>
                                        <a href="javascript:;" data-action="viewOfficeFile" data-param='{"href": "<%= item['officereadurl'] %>"}' title="<?php echo $lang['View']; ?>">
											<?php echo $lang['View']; ?>
										</a>
                                    <% } %>
								</span>
                            </div>
                        </li>
                    <% } %>
                </ul>
            </div>
        <% } %>
        <!-- 是否有投票 -->
        <div class="noprint">
            <?php if ($this->getVoteInstalled()): ?>
            	<% if (votestatus) { %>
                    <div class="art-show-vote" id="vote_content"></div>
                <% } %>
            <?php endif; ?>
        </div>
    </div>
    <div class="art-halving-line"></div>
    <div class="art-desc mb ">
        <ul class="art-desc-list">
            <li>
                <strong><?php echo $lang['News']; ?></strong>
                <div class="art-desc-body">
                    <%= author %>
                    <span class="ilsep"><?php echo $lang['Posted on']; ?></span>
                    <%= addtime %>
                    <span class="ilsep">|</span>
                    <?php echo $lang['Approver']; ?>：<%= approver %>
                    <% if (uptime) { %>
                        <span class="ilsep">|</span>
                        <?php echo $lang['Update on']; ?>：<%= uptime %>
                    <% } %>
                    <span class="ilsep">|</span>
                    <?php echo $lang['Category']; ?>：<%= categoryName %>
                </div>
            </li>
            <li>
                <strong><?php echo $lang['Scope']; ?></strong>
                <div class="art-desc-body">
                    <% if (departmentNames) { %>
                        <i class="os-department"></i><%= departmentNames %>&nbsp;
                    <% } %>
                    <% if (positionNames) { %>
                        <i class="os-position"></i><%= positionNames %>&nbsp;
                    <% } %>
                    <% if (roleNames) { %>
                        <i class="os-role"></i><%= roleNames %>&nbsp;
                    <% } %>
                    <% if (uidNames) { %>
                    <i class="os-user"></i><%= uidNames %>
                	<% } %>
                </div>
            </li>
        </ul>
    </div>
    <div class="noprint">
        <ul class="nav nav-skid fill-zn art-related-nav" id="art_related_nav">
        	<% if (status == 1) { %>
	            <?php if ($config['articlecommentenable']) { ?>
	            	<!-- 新闻发布后可评论 -->
	            	<% if (commentstatus == 1) { %>
		                <li>
		                    <a href="#comment" id="comment_tab" data-toggle="tab">
		                        <i class="o-art-comment"></i>
		                        <?php echo $lang['Comment']; ?>
		                    </a>
		                </li>
	                <% } %>
	            <?php } ?>
	            <li>
	                <a href="#isread" id="isread_tab" data-toggle="tab">
	                    <i class="o-art-isread"></i>
	                    <?php echo $lang['Now the situation']; ?>
	                </a>
	            </li>
            <% } %>
            <li>
                <a href="#verifylog" id="verifylog_tab" data-toggle="tab">
                    <i class="o-art-verifylog"></i>
                    <?php echo $lang['Verify log']; ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <!-- 是否使用AJAX Tab -->
            <!-- 评论 -->
            <% if (status == 1) { %>
	            <?php if ($config['articlecommentenable']): ?>
	            	<% if (commentstatus) { %>
		                <div id="comment" class="comment fill-zn tab-pane active"></div>
	                <% } %>
	            <?php endif; ?>
	            <!-- 查阅情况 -->
	            <div id="isread" class="tab-pane">
	                <table class="table table-striped">
	                    <tbody>
		                    <tr>
		                        <td colspan="2"><img src='<?php echo STATICURL ?>/image/common/loading.gif'/></td>
		                    </tr>
	                    </tbody>
	                </table>
	            </div>
            <% } %>
            <!--流转日志-->
            <div id="verifylog" class="tab-pane"></div>
        </div>
    </div>
    <% if (status != 1) { %>
        <div id="submit_bar" class="fill-nn clearfix">
            <button type="button" class="btn btn-large btn-submit pull-left"
                    onclick="history.back();"><?php echo Ibos::lang('Return'); ?></button>
            <div class="pull-right">
                <% if (tableType == 'reback_to') { %>
                    <button type="button" data-action="removeArticle" data-id="<%= articleid %>"
                            class="btn btn-large btn-submit btn-danger"><?php echo Ibos::lang('Delete'); ?></button>
                    <button type="button" data-action="editArticle" data-id="<%= articleid %>"
                        class="btn btn-large btn-submit btn-primary"><?php echo Ibos::lang('Edit'); ?></button>
                <% } %>
                <% if (tableType == 'approval') { %>
                    <button type="button" data-action="pushBack" data-id="<%= articleid %>"
                        class="btn btn-large btn-submit"><?php echo Ibos::lang('Getback'); ?></button>
                    <button type="button" data-action="remindApprover" data-id="<%= articleid %>"
                            class="btn btn-large btn-submit btn-primary"><?php echo Ibos::lang('Remind'); ?></button>
                <% } %>
                <% if (tableType == 'wait') { %>
                    <button type="button" data-action="reback" data-id="<%= articleid %>"
                        class="btn btn-large btn-submit"><?php echo Ibos::lang('Back'); ?></button>
                    <button type="button" data-action="passArticle" data-id="<%= articleid %>"
                            class="btn btn-large btn-submit btn-primary"><?php echo Ibos::lang('Pass'); ?></button>
                <% } %>
                <% if (tableType == 'passed') { %>
                    <button type="button" data-action="getBack" data-id="<%= articleid %>"
                            class="btn btn-large btn-submit btn-primary"><?php echo Ibos::lang('Getback'); ?></button>
                <% } %>
            </div>
        </div>
    <% } %>
</script>

<!-- Template: 阅读人员表格 -->
<script type="text/template" id="tpl_reader_table">
    <div class="art-reader-table" id="art_reader_table">
        <% if(readerData){ %>
	        <% for(var depName in readerData) { %>
		        <h5 class="art-reader-dep"><%= depName %></h5>
		        <% var userData = readerData[depName]; %>
		        <ul class="art-reader-list clearfix">
		            <% for(var i = 0; i < userData.length; i++){ %>
		            <li>
		                <a href="<%= userData[i]['space_url'] %>" class="avatar-circle avatar-circle-small">
		                    <img src="<%= userData[i]['avatar_small']%>"/>
		                </a>
		                <%= userData[i]['realname'] %>
		            </li>
		            <% } %>
		        </ul>
	        <% } %>
	    <% } else { %>
	        <?php echo Ibos::lang('Temporarily no'); ?>
        <% } %>
    </div>
</script>

<script type="text/template" id="tpl_verify_log">
	<div class="art-verify-log">
		<div class="art-log-content">
			<% for (var i = 0, len = datas.length; i < len; i += 1) { %>
				<% var data = datas[i]; %>
				<div class="art-log-item <%= (data.status == '发起' || data.status == '通过') ? 'art-act' : data.status == '退回' ? 'art-warn' : '' %>">
					<i class="o-art-log-step"><%= i + 1 %></i>
					<div class="dib xcn span10">
						<p><%= data.author %>&nbsp;&nbsp;&nbsp;<span class="xcg"><%= data.time %></span></p>
						<p class="ellipsis" title="<%= data.reason %>"><span class="<%= data.status == '发起' ? 'xcm' : data.status == '通过' ? 'xcbu' : data.status == '退回' ? 'xcr' : 'xcg' %>"><%= data.status %></span>&nbsp;&nbsp;<%= data.reason %></p>
					</div>
				</div>
			<% } %>
		</div>
	</div>
</script>

<script>
    Ibos.app.setPageParam({
        'articleId': <?php echo $articleId; ?>,
        'commentEnable': <?php echo $config['articlecommentenable']; ?>,
        'voteEnable': <?php echo $config['articlevoteenable']; ?>
    })
</script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/src/emotion.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/art_show.js?<?php echo VERHASH; ?>'></script>
