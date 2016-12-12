<?php

use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

?>
<?php if (!$loadmore): ?>
    <!-- Comment start -->
    <div class="cmt">
        <?php endif; ?>
        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="cmt-item" id="comment_<?php echo $comment['cid']; ?>">
                    <div class="avatar-box">
                        <a href="<?php echo $comment['user_info']['space_url']; ?>" class="avatar-circle">
                            <img src="<?php echo $comment['user_info']['avatar_middle']; ?>" width="60" height="60">
                        </a>
                    </div>
                    <div class="cmt-body">
                        <p class="mbs xcm">
                            <strong class="xcn"><?php echo $comment['user_info']['realname']; ?>：</strong>
                            <?php echo $comment['content']; ?>
                        </p>
                        <div class="mbs fss">
                            <span><?php echo Convert::formatDate($comment['ctime'], 'u'); ?></span>
                            <div class="pull-right">
                                <a href="javascript:;" data-act="getreply"
                                   data-param='{"type":"reply","module":"message","table":"comment","rowid":"<?php echo $comment['cid']; ?>","name":"<?php echo $comment['user_info']['realname']; ?>","type":"reply"}'><?php echo $lang['Reply'] ?>
                                    (<?php echo $comment['replys']; ?>)</a>
                                <?php if ($comment['isCommentDel']): ?><a class='mls' href="javascript:;"
                                                                          data-act="delcomment"
                                                                          data-param='{"cid":"<?php echo $comment['cid']; ?>"}'><?php echo $lang['Delete']; ?></a><?php endif; ?>
                            </div>
                        </div>
                        <div class="well well-small well-lightblue" style="display: none;">
                            <textarea
                                class="mbs reply"><?php echo $lang['Reply']; ?> <?php echo $comment['user_info']['realname']; ?>
                                ： </textarea>
                            <div class="clearfix mbs">
                                <button type="button" data-tocid="<?php echo $comment['cid']; ?>"
                                        data-touid="<?php echo $comment['uid']; ?>"
                                        class="btn btn-primary btn-small pull-right" data-act="addreply"
                                        data-loading-text="<?php echo $lang['Reply ing']; ?>..."
                                        data-param='{"type":"reply","rowid":"<?php echo $comment['cid']; ?>","table":"comment","module":"message","moduleuid":"<?php echo $comment['uid']; ?>","url":"<?php echo $url; ?>"}'><?php echo $lang['Reply']; ?></button>
                            </div>
                            <!-- 子评论列表 -->
                            <ul class="cmt-sub"></ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-comment-tip"></div>
        <?php endif; ?>
        <?php if (!$loadmore): ?>
        <?php if ($count > 10): ?>
            <div id="commentMoreFoot" style="padding: 10px;" data-node-type="moreCommentWrap">
                <button type="button" style="width: 100%;" class="btn" id="load_more_btn" data-act="loadmorecomment"
                        data-node-type="moreComment"
                        data-param='{"type":"comment","rowid":<?php echo $rowid; ?>,"table":"<?php echo $module_table; ?>","module":"<?php echo $module; ?>","moduleuid":"<?php echo $moduleuid; ?>","url":"<?php echo $url; ?>"}'><?php echo $lang['See more']; ?></button>
            </div>
        <?php endif; ?>
        <!-- 新增评论 -->
        <div class="cmt-item" id="newCommentBox" data-node-type="commentBox">
            <div class="avatar-box">
                <a href="<?php echo Ibos::app()->user->space_url; ?>" class="avatar-circle">
                    <img src="<?php echo Ibos::app()->user->avatar_middle; ?>" width="60" height="60">
                </a>
            </div>
            <div class="cmt-body">
                <textarea rows="3" class="mbs comment-box" id="commentBox"
                          placeholder="<?php echo $lang['Say something...']; ?>"
                          data-node-type="commentText"></textarea>
                <div class="mbs fss clearfix">
                    <a href="javascript:;" id="comment_emotion" title="<?php echo $lang['Expression']; ?>"
                       class="cbtn o-expression comment-btn" data-act="face" data-node-type="commentEmotion"></a>
                    <button type="button" data-act="addcomment"
                            data-param='{"type":"comment","rowid":<?php echo $rowid; ?>,"table":"<?php echo $module_table; ?>","module":"<?php echo $module; ?>","moduleuid":"<?php echo $moduleuid; ?>","touid":"<?php echo $touid; ?>","url":"<?php echo $url; ?>","detail":"<?php echo $detail; ?>"}'
                            class="btn btn-primary pull-right"
                            data-loading-text="<?php echo $lang['Posting']; ?>"><?php echo $lang['Post comment']; ?></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var commentCount = '<?php echo $count; ?>';
        $(function () {
            Ibos.statics.load({type: "css", url: Ibos.app.getStaticUrl("/js/lib/atwho/jquery.atwho.css")});

            $.when(
                Ibos.statics.load(Ibos.app.getStaticUrl("/js/lib/atwho/jquery.atwho.js")),
                Ibos.statics.load(Ibos.app.getAssetUrl("message", "/js/comment.js"))
            ).done(function () {
                var timer;
                var _loadComment = function () {
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
                timer = setTimeout(function () {
                    _loadComment();
                }, 100);
            });
        });
    </script>
<?php endif; ?>