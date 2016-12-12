<div class="wb-ifview-bom rdb" data-node-type="commentBox">
    <div class="posr">
        <textarea name="content" placeholder="我来说一句..." data-node-type="commentText"></textarea>
    </div>
    <!--发布按钮和功能按钮-->
    <div class="wb-pub-other clearfix">
        <div class="pull-left">
            <a href="javascript:;" class="o-wb-face" id="comment_emotion_<?php echo $rowid; ?>" title="表情"
               data-node-type="commentEmotion"></a>
            <?php if ($canrepost): ?>
                <label class="checkbox checkbox-inline fss">
                    <input type="checkbox" name="sharefeed" value="1"/>
                    同时转发到我的微博
                </label>
            <?php endif; ?>
            <?php if (isset($feedtype) && $feedtype == 'repost' && $cancomment == 1): ?>
                <label class="checkbox checkbox-inline fss">
                    <input type="checkbox" name="comment" value="1"/>
                    同时评论给原作者&nbsp;<?php echo $user_info['realname']; ?>
                </label>
            <?php endif; ?>
        </div>
        <button type="button" data-tocid="0" data-touid="<?php echo $touid; ?>"
                data-param='{"module": "weibo", "table": "feed", "rowid": <?php echo $rowid; ?>,
                        "module_table": "<?php echo $module_table; ?>", "moduleuid": "<?php echo $moduleuid; ?>",
                        "touid": "<?php echo $touid; ?>", "module_rowid": <?php echo $module_rowid; ?>,
                        "url": "<?php echo $url; ?>"}'
                class="btn btn-small pull-right" disabled data-node-type="commentBtn" data-action="comment"
                data-loading-text="评论中...">评论
        </button>
    </div>
    <div class="cmt">
        <ul class="cmt-sub">
            <?php if (!empty($showlist)): ?>
                <?php echo $list; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
