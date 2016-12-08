<?php

use application\core\utils\Convert;

?>
<?php if (!$loadmore): ?>
    <ul class="cmt-sub">
<?php endif; ?>
<?php if (!empty($comments)): ?>
    <?php foreach ($comments as $comment): ?>
        <li class="cmt-item">
            <div class="avatar-box">
                <a href="<?php echo $comment['user_info']['space_url']; ?>" class="avatar-small pull-left">
                    <img src="<?php echo $comment['user_info']['avatar_middle']; ?>" class="avatar-small" width="30"
                         height="30">
                </a>
            </div>
            <div class="cmt-body fss">
                <p class="xcm">
                    <a href="<?php echo $comment['user_info']['space_url']; ?>"
                       class="anchor"><?php echo $comment['user_info']['realname']; ?>：</a>
                    <?php echo $comment['content']; ?>
                    <span class="tcm ilsep">(<?php echo Convert::formatDate($comment['ctime'], 'u'); ?>)</span>
                </p>
                <div class="xar">
                    <a href="javascript:;" data-act="reply"
                       data-param='{"name":"<?php echo $comment['user_info']['realname']; ?>","touid":"<?php echo $comment['user_info']['uid']; ?>","tocid":"<?php echo $comment['cid']; ?>"}'><?php echo $lang['Reply']; ?></a>
                    <?php if ($comment['isCommentDel']): ?>
                        <a href="javascript:;" class="mls" data-act="delreply"
                           data-param='{"cid":"<?php echo $comment['cid']; ?>"}'><?php echo $lang['Delete']; ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (!$loadmore): ?>
    </ul>
    <?php if ($count > $limit): ?>
        <div><a href='javascript:;' data-act='loadmorereply'
                data-param='{"module":"message","table":"comment","rowid":"<?php echo $rowid; ?>"}'
                class='fss anchor'><?php echo $lang['See more']; ?> >></a></div>
    <?php endif; ?>
<?php endif; ?>