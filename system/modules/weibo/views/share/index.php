<?php

use application\core\utils\StringUtil;

?>
<!-- 转发框 -->
<div id="dialog_feed_forward" data-node-type="feedForwardBox" style="width: 500px;">
    <div class="wb-rep mb">
        <?php if ($oldInfo['isdel'] == 1): ?>
            内容已被删除
        <?php else: ?>
            <div class="wb-at-someone">
                <a href="<?php echo $oldInfo['source_user_info']['space_url'] ?>"
                   class="wb-source"><?php echo $oldInfo['source_user_info']['realname'] ?>:</a>
            </div>
            <!--转载的内容 S-->
            <div class="wb-info-picword clearfix">
                <p>
                    <?php $sourceInfo = $oldInfo; ?>
                    <?php if (!empty($shareInfo['shareHtml'])): ?>
                        <?php echo $shareInfo['shareHtml']; ?>
                    <?php else: ?>
                        <?php echo StringUtil::parseHtml($sourceInfo['source_content']); ?>
                    <?php endif; ?>
                </p>
            </div>
            <!--转载的内容 E-->
        <?php endif; ?>
    </div>
    <!--微博盒子 S-->
    <div data-node-type="feedForwardComment">
        <form action="javascript:;">
            <div class="wb-pub-box wb-forword-pub">
                <textarea name="body" data-node-type="textarea" class="wb-pub-text" rows="4"
                          placeholder="我也说一句..."><?php echo $shareInfo['initHTML']; ?></textarea>
            </div>
            <!--发布按钮和功能按钮-->
            <div class="clearfix">
                <button type="button" class="btn btn-primary btn-small pull-right" data-action="feedForward"
                        data-node-type="feedForwardBtn">转发
                </button>
                <div class="pull-left">
                    <a href="javascript:;" class="o-wb-face" title="表情" data-node-type="forwardEmotion"></a>
                    <label class="checkbox checkbox-inline fss" style="top:3px;">
                        <input type="checkbox" name="comment" value="1"/>
                        同时评论给 <?php echo $oldInfo['source_user_info']['realname'] ?>
                    </label>
                </div>
            </div>
            <input type="hidden" name="comment_touid" value="<?php echo $oldInfo['source_user_info']['uid'] ?>"/>
            <input type="hidden" name="module" value="<?php echo $shareInfo['module']; ?>"/>
            <input type="hidden" name="sid" value="<?php echo $shareInfo['sid']; ?>"/>
            <input type="hidden" name="curid" value="<?php echo $shareInfo['curid']; ?>"/>
            <input type="hidden" name="curtable" value="<?php echo $shareInfo['curtable']; ?>"/>
            <input type="hidden" name="type" value="<?php echo $shareInfo['stable']; ?>"/>
            <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
        </form>
    </div>
    <!--微博盒子 E-->
</div>
