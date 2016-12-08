<?php
use application\core\utils\Convert;

?>
<li class="cmt-item">
    <div class="avatar-box">
        <a href="<?php echo $userInfo['space_url']; ?>" class="avatar-small pull-left">
            <img src="<?php echo $userInfo['avatar_middle']; ?>" class="avatar-small" width="30" height="30">
        </a>
    </div>
    <div class="cmt-body fss">
        <p class="xcm">
            <a href="<?php echo $userInfo['space_url']; ?>" class="anchor"><?php echo $userInfo['realname']; ?>：</a>
            <?php echo $content; ?>
            <span class="tcm ilsep">(<?php echo Convert::formatDate($ctime, 'u'); ?>)</span>
        </p>
        <div class="xar">
            <a href="javascript:;" data-act="reply"
               data-param='{"name":"<?php echo $userInfo['realname']; ?>","touid":"<?php echo $userInfo['uid']; ?>"}'><?php echo $lang['Reply']; ?></a>
            <?php if ($isCommentDel): ?>
                <a href="javascript:;" class="mls" data-act="delreply"
                   data-param='{"cid":"<?php echo $cid; ?>"}'><?php echo $lang['Delete']; ?></a>
            <?php endif; ?>
        </div>
    </div>
</li>