<?php 
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\user\model\User;
use application\modules\vote\components\Vote;
?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->
<!-- Mainer -->
<div class="mc clearfix">
	<!-- Sidebar -->
	<?php echo $this->getSidebar( $this->catid ); ?>
	<!-- Sidebar -->

	<!-- Mainer right -->
	<div class="mcr">
		<form action="" class="form-horizontal">
			<div class="ct ctview ctview-art">
				<!-- 文章 -->
				<div class="art">
					<div class="art-container">
						<a href="javascript:" title="<?php echo $lang['Close']; ?>" class="art-close" onclick="window.location.href = document.referrer;"></a>
						<h1 class="art-title"><?php echo $data['subject']; ?></h1>
						<div class="art-ct mb">
							<?php if ( $data['type'] == 0 ): ?>
								<?php echo $data['content']; ?>
							<?php elseif ( $data['type'] == 1 ): ?>
								<div id="gallery" class="ad-gallery">
									<div class="ad-image-wrapper"></div>
									<!-- <div class="ad-controls"></div> -->
									<div class="ad-nav">
										<div class="ad-thumbs">
											<ul class="ad-thumb-list">
												<?php foreach ( $pictureData as $key => $picture ): ?>
													<li>
														<a href="<?php echo File::fileName( $picture['filepath'] ); ?>">
															<img src="<?php echo File::fileName( $picture['filepath'] ); ?>" alt="<?php echo $picture['filename']; ?>" />
															<!-- 此处输出索引和总张数 -->
															<span><em><?php echo $key + 1; ?>/<?php echo count( $pictureData ); ?></em></span>
														</a>
													</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
								</div>

							<?php elseif ( $data['type'] == 2 ): ?>
								<script>window.location = '<?php header( 'location:' . $data['url'] ); ?>';</script>
							<?php endif; ?>
						</div>
						<?php if ( isset( $attach ) ): ?>
							<div class="fill noprint">
								<h3 class="ctbt">
									<i class="o-paperclip"></i>
									<strong>附件</strong>（<?php echo count( $attach ); ?>个）
								</h3>
								<ul class="attl">
									<?php foreach ( $attach as $fileInfo ): ?>
										<li>
											<i class="atti">
												<img src="<?php echo $fileInfo['iconsmall']; ?>" alt="<?php echo $fileInfo['filename']; ?>">
											</i>
											<div class="attc">
												<div class="mbm">
													<?php echo $fileInfo['filename']; ?>
													<span class="tcm">(<?php echo $fileInfo['filesize']; ?>)</span>
												</div>
												<span class="fss">
													<a href="<?php echo $fileInfo['downurl']; ?>" class="anchor">下载</a>&nbsp;&nbsp;
													<!--<a href="#" class="anchor">转存到文件柜</a>-->
												</span>
											</div>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
						<!-- 是否有投票 -->
						<div class="noprint">
							<?php if ( $this->getVoteInstalled() && $data['votestatus'] ): ?>
								<?php echo Vote::getView( 'articleView' ); ?>
							<?php endif; ?>
						</div>
						<!-- <div class="noprint">
							<a href="javascript:;" title="<?php echo $lang['Print']; ?>" class="o-print cbtn" data-action="printArticle"></a> -->
						<!-- 转发 -->
						<!-- <?php if ( $isInstallEmail ): ?>
											<a href="javascript:;" title="<?php echo $lang['Forward']; ?>" class="o-forward-mail cbtn" data-action="forwardArticleByMail"></a>
						<?php endif; ?>
												</div> -->
					</div>
					<?php if ( $data['status'] == 2 ): ?>
						<div class="clearfix fill-nn art-funbar">
							<?php if ( isset( $isApprovaler ) && $isApprovaler ): ?>
								<div class="pull-left">
									<button type="button" class="btn btn-large btn-primary" data-action="verifyArticle">审核通过</button>
									<button type="button" class="btn btn-large" data-action="backArticle">退回</button>
								</div>
							<?php endif; ?>
							<?php if ( !empty( $data['approval'] ) ): ?>
								<div class="pull-right">
									<div class="approval-flow">
										<span class="flow-name xwb"><?php echo $data['approvalName']; ?></span>
										<i class="o-art-description" data-toggle="tooltip" data-original-title="审批规则"></i>
										<div class="dib mls">
											<ul class="list-inline flow-ul">
												<?php for ( $i = 1; $i <= $data['approval']['level']; $i++ ): ?>
													<li>
														<?php if ( $i > 1 ): ?>
															<span class="<?php if ( $data['stepNum'] >= $i ): ?>o-allow-line<?php else: ?>o-noallow-line<?php endif; ?>"></span>
														<?php endif; ?>
														<span data-toggle="tooltip" data-original-title="审核人:<?php echo $data['approval'][$i]['approvaler']; ?>" class="<?php if ( $data['stepNum'] >= $i ): ?>o-allow-circle<?php else: ?>o-noallow-circle<?php endif; ?>"><?php echo $i; ?></span>
													</li>
												<?php endfor; ?>
											</ul>
										</div>
									</div>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<div class="art-halving-line"></div>
					<div class="art-desc mb ">
						<ul class="art-desc-list">
							<li>
								<strong><?php echo $lang['News']; ?></strong>
								<div class="art-desc-body">
									<?php echo User::model()->fetchRealNameByUid( $data['author'] ); ?>
									<span class="ilsep"><?php echo $lang['Posted on']; ?></span>
									<?php echo $data['addtime']; ?>
									<span class="ilsep">|</span>
									<?php echo $lang['Approver']; ?>：<?php echo $data['approver']; ?>
									<?php if ( !empty( $data['uptime'] ) ): ?>
										<span class="ilsep">|</span>
										<?php echo $lang['Update on']; ?>：<?php echo $data['uptime']; ?>
									<?php endif; ?>
									<span class="ilsep">|</span>
									<?php echo $lang['Category']; ?>：<?php echo $data['categoryName']; ?>
								</div>
							</li>
							<li>
								<strong><?php echo $lang['Scope']; ?></strong>
								<div class="art-desc-body">
									<?php if ( !empty( $data['departmentNames'] ) ): ?>
										<i class="os-department"></i><?php echo $data['departmentNames']; ?>&nbsp;
									<?php endif; ?>
									<?php if ( !empty( $data['positionNames'] ) ): ?>
										<i class="os-position"></i><?php echo $data['positionNames']; ?>&nbsp;
									<?php endif; ?>
									<?php if ( !empty( $data['uidNames'] ) ): ?>
										<i class="os-user"></i><?php echo $data['uidNames']; ?></div>
								<?php endif; ?>
							</li>
						</ul>
					</div>
					<?php if ( $data['status'] != 2 ): ?>
						<div class="noprint">
							<ul class="nav nav-skid fill-zn art-related-nav" id="art_related_nav">
								<?php if ( $data['commentstatus'] && $dashboardConfig['articlecommentenable'] ) { ?>
									<li class="active">
										<a href="#comment" data-toggle="tab">
											<i class="o-art-comment">
												<!-- <em id="commentCount" class="o-bubble"></em> -->
											</i>
											<?php echo $lang['Comment']; ?>
										</a>
									</li>
								<?php } ?>
								<li>
									<a href="#isread" id="isread_tab" data-toggle="tab">
										<i class="o-art-isread"></i>
										<?php echo $lang['Now the situation']; ?>
									</a>
								</li>
							</ul>
							<div class="tab-content">
								<!-- 是否使用AJAX Tab -->
								<!-- 评论 -->
								<?php if ( $data['commentstatus'] && $dashboardConfig['articlecommentenable'] ): ?>
									<div id="comment" class="comment fill-zn tab-pane active">
										<?php
											$sourceUrl = IBOS::app()->urlManager->createUrl( 'article/default/index', array( 'op' => 'show', 'articleid' => $data['articleid'] ) );
											$this->widget( 'application\modules\article\core\ArticleComment', array(
												'module' => 'article',
												'table' => 'article',
												'attributes' => array(
													'rowid' => $data['articleid'],
													'moduleuid' => IBOS::app()->user->uid,
													'touid' => $data['author'],
													'module_rowid' => $data['articleid'],
													'module_table' => 'article',
													'url' => $sourceUrl,
													'detail' => IBOS::lang( 'Comment my article', '', array( '{url}' => $sourceUrl, '{title}' => String::cutStr( $data['subject'], 50 ) ) )
											) ) );
										?>
									</div>
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
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<input type="hidden" name="articleid" id="articleid" value="<?php echo $data['articleid']; ?>">
			<input type="hidden" name="relatedid" id="relatedid" value="<?php echo $data['articleid']; ?>">
		</form>
	</div>
	<div id="rollback_reason" style="display:none;">
		<form action="javascript:;" method="post" id="rollback_form">
			<textarea rows="8" cols="60" id="rollback_textarea" name="reason" placeholder="退回理由...."></textarea>
		</form>
	</div>
</div>

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
			<?php echo IBOS::lang( 'Temporarily no' ); ?>
		<% } %>
	</div>
</script>



<script>
	Ibos.app.setPageParam({
		"articleId": <?php echo $data['articleid']; ?>,
		"articleType": <?php echo $data['type']; ?>,
		"commentEnable": <?php echo $dashboardConfig['articlecommentenable']; ?>,
		"commentStatus": <?php echo $data['commentstatus']; ?>
	})
</script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/src/emotion.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article_default_show.js?<?php echo VERHASH; ?>'></script>
