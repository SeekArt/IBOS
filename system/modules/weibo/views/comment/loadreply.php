<?php

use application\core\utils\Convert;
use application\core\utils\Ibos;

?>
<?php foreach ($list as $reply): ?>
    <li class="cmt-item">
        <div class="avatar-box">
            <a href="<?php echo $reply['user_info']['space_url']; ?>" class="avatar-small pull-left">
                <img src="<?php echo $reply['user_info']['avatar_middle']; ?>" class="avatar-small" width="30"
                     height="30">
            </a>
        </div>
        <div class="cmt-body fss">
            <p class="xcm">
                <a href="<?php echo $reply['user_info']['space_url']; ?>"
                   class="anchor"><?php echo $reply['user_info']['realname']; ?>：</a>
                <?php echo $reply['content']; ?>
                <span class="tcm ilsep">(<?php echo Convert::formatDate($reply['ctime'], 'u'); ?>)</span>
            </p>
            <div class="xar">
                <a href="javascript:;" data-action="reply"
                   data-param='{"name":"<?php echo $reply['user_info']['realname']; ?>","touid":<?php echo $reply['user_info']['uid']; ?>,"tocid":<?php echo $reply['cid']; ?>}'>回复</a>
                <?php if ($reply['isCommentDel']): ?>
                    <a href="javascript:;" class="mls" data-action="delreply"
                       data-param='{"cid":<?php echo $reply['cid']; ?>}'>删除</a>
                <?php endif; ?>
            </div>
        </div>
    </li>
<?php endforeach; ?>
<?php if ($count > $limit): ?>
    <div>
        <?php if (!isset($showlist)): ?>
            <a target="_blank"
               href='<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/feed', array('feedid' => $rowid, 'uid' => $moduleuid)) ?>'
               class='fss anchor'><?php echo $lang['See more']; ?> >></a>
        <?php else: ?>
            <?php echo $this->widget('application\core\widgets\Page', array('pages' => $pages), true); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>