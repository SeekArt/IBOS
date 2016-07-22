<?php
use application\core\utils\Convert;
use application\core\utils\StringUtil;
?>
<?php if ( !$loadmore ): ?>
	<!-- private css -->
	<link rel="stylesheet" href='<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.css?<?php echo VERHASH; ?>' />
	<!-- Comment start -->
	<div class="cmt am-cmt" id="am_cmt">
	<?php endif; ?>
	<?php if ( !empty( $comments ) ): ?>
		<?php foreach ( $comments as $comment ): ?>
			<div class="cmt-item" id="comment_<?php echo $comment['cid']; ?>">
				<div class="avatar-box">
					<a href="<?php echo $comment['user_info']['space_url']; ?>" class="avatar-circle">
						<img src="<?php echo $comment['user_info']['avatar_middle']; ?>">
					</a>
				</div>
				<div class="cmt-body">
					<p class="mbs xcm">
						<strong class="xcn"><?php echo $comment['user_info']['realname']; ?>：</strong>
						<?php echo StringUtil::parseHtml( $comment['content'] ); ?>
					</p>
					<div class="mbs fss">
						<span><?php echo Convert::formatDate( $comment['ctime'], 'u' ); ?></span>
						<div class="pull-right">
							<a href="javascript:;" data-act="getreply" data-param='{"type":"reply","module":"message","table":"comment","rowid":"<?php echo $comment['cid']; ?>","name":"<?php echo $comment['user_info']['realname']; ?>","type":"reply"}'><?php echo $lang['Reply'] ?>(<?php echo $comment['replys']; ?>)</a>
							<?php if ( $comment['isCommentDel'] && !$status): ?><a class='mls' href="javascript:;" data-act="delcomment" data-param='{"cid":"<?php echo $comment['cid']; ?>"}'><?php echo $lang['Delete']; ?></a><?php endif; ?>
						</div>
					</div>
					<div class="well well-small well-lightblue" style="display: none;">
						<textarea class="mbs reply"><?php echo $lang['Reply']; ?> <?php echo $comment['user_info']['realname']; ?>： </textarea>
						<div class="clearfix mbs">
							<button type="button" data-tocid="<?php echo $comment['cid']; ?>" data-touid="<?php echo $comment['uid']; ?>" class="btn btn-primary btn-small pull-right" data-act="addreply" data-loading-text="<?php echo $lang['Reply ing']; ?>..." data-param='{"type":"reply","rowid":"<?php echo $comment['cid']; ?>","table":"comment","module":"message","moduleuid":"<?php echo $comment['uid']; ?>","url":"<?php echo $url; ?>"}'><?php echo $lang['Reply']; ?></button>
						</div>
						<!-- 子评论列表 -->
						<ul class="cmt-sub"></ul>
					</div>
					<div>
						<?php if ( isset( $comment['attach'] ) ): ?>
							<?php foreach ( $comment['attach'] as $key => $value ): ?>
								<div class="media mbs">
									<img src="<?php echo $value['iconsmall']; ?>" alt="<?php echo $value['filename']; ?>" class="pull-left">
									<div class="media-body">
										<div class="media-heading">
											<?php echo $value['filename']; ?> <span class="tcm">(<?php echo $value['filesize']; ?>)</span>
										</div>
										<div class="fss">
											<a href="<?php echo $value['downurl']; ?>" target="_blank"><?php echo $lang['Download']; ?></a>
											<?php if ( isset( $value['officereadurl'] ) ): ?>
												<a href="javascript:;" class="mls" data-action="viewOfficeFile" data-param='{"href": "<?php echo $value['officereadurl']; ?>"}'  title="<?php echo $lang['Read']; ?>">
													<?php echo $lang['Read']; ?>
												</a>
											<?php endif; ?>
											<?php if ( isset( $value['officeediturl'] ) && $comment['isCommentDel'] ): ?>
												<a href="javascript:;" class="mls" data-action="editOfficeFile" data-param='{"href": "<?php echo $value['officeediturl']; ?>"}'  title="<?php echo $lang['Edit']; ?>">
													<?php echo $lang['Edit']; ?>
												</a>
											<?php endif; ?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="no-comment-tip"></div>
	<?php endif; ?>
	<?php if ( !$loadmore ): ?>
		<?php if ( $count > 10 ): ?>
			<div id="commentMoreFoot" style="padding: 10px;" data-node-type="moreCommentWrap">
				<button type="button" style="width: 100%;" class="btn" id="load_more_btn" data-act="loadmorecomment" data-node-type="moreComment" data-param='{"type":"comment","rowid":<?php echo $rowid; ?>,"table":"<?php echo $module_table; ?>","module":"<?php echo $module; ?>","moduleuid":"<?php echo $moduleuid; ?>","url":"<?php echo $url; ?>"}'><?php echo $lang['See more']; ?></button>
			</div>
		<?php endif; ?>
		<!-- 新增评论 -->
		<div class="cmt-item" id="newCommentBox" data-node-type="commentBox">
			<div class="cmt-body" style="margin-left: 0">
				<textarea rows="3" class="mbs comment-box"  id="commentBox" placeholder="<?php echo $lang['Say something...']; ?>" data-node-type="commentText"></textarea>
				<div class="mbs fss clearfix">
					<div class="pull-left">
						<span id="am_cm_upload"></span>
					</div>
					<input type="hidden" name="" id="am_cmt_attach_input">
					<button type="button" data-act="addcomment" data-param='{
						"type":"comment",
						"rowid":<?php echo $rowid; ?>,
						"table":"<?php echo $module_table; ?>",
						"module":"<?php echo $module; ?>",
						"moduleuid":"<?php echo $moduleuid; ?>",
						"touid":"<?php echo $touid; ?>",
						"url":"<?php echo $url; ?>",
						"detail":"<?php echo $detail; ?>"}' class="btn btn-primary pull-right" data-loading-text="<?php echo $lang['Posting']; ?>"><?php echo $lang['Post comment']; ?></button>
				</div>
			</div>
			<div id="am_cmt_attach"></div>
		</div>
	</div>
	<script src='<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.js?<?php echo VERHASH; ?>'></script>
	<script src='<?php echo $assetUrl; ?>/js/comment.js?<?php echo VERHASH; ?>'></script>
	<script>
		var commentCount = '<?php echo $count; ?>';
		$(function() {
			var timer;
			var _loadComment = function() {
				if (Ibos.data) {
					Comment.init($(".cmt"), {
						getReplyUrl: "<?php echo $getUrl; ?>",
						getCommentUrl: "<?php echo $getUrl; ?>",
						addUrl: "<?php echo $addUrl; ?>",
						delUrl: "<?php echo $delUrl; ?>",
						defCommentOffset: 10
					});
					clearTimeout(timer);
				}
			};
			timer = setTimeout(function() {
				_loadComment();
			}, 100);

			var _attachParam = function(){
				var $commentAddBtn = $('[data-act="addcomment"]');
				var cmtParam = Ibos.app.getEvtParams($commentAddBtn[0]);
				cmtParam.attachmentid = Dom.byId("am_cmt_attach_input").value;
				$commentAddBtn.attr("data-param", $.toJSON(cmtParam));
			}

			Ibos.upload.attach({
				post_params: { module: 'assignment' },
				button_placeholder_id: 'am_cm_upload',
				custom_settings: {
					containerId: 'am_cmt_attach',
					inputId: 'am_cmt_attach_input',
					success: _attachParam,
					remove: _attachParam
				}
			});

			// 评论成功后清空所有附件
			$("#am_cmt").on("commentAdd", function(evt, $elem){
				$("#am_cmt_attach [data-node-type='attachRemoveBtn']").trigger("click");
			});
		});
	</script>
<?php endif; ?>
