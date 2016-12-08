<?php
use application\core\utils\Convert;

?>
<div class="cmt-item">
    <div class="avatar-box">
        <a href="<?php echo $user_info['space_url']; ?>" class="avatar-circle">
            <img src="<?php echo $user_info['avatar_middle']; ?>" width="60" height="60">
        </a>
    </div>
    <div class="cmt-body">
        <p class="mbs xcm">
            <strong class="xcn"><?php echo $user_info['realname']; ?>：</strong>
            <?php echo $body; ?>
        </p>
        <div class="mbs fss">
            <span><?php echo Convert::formatDate($ctime, 'u'); ?></span>
            <a href="javascript:;" data-act="getreply"
               data-param="name=<?php echo $user_info['realname']; ?>&module=message&table=feed&rowid=<?php echo $feedid; ?>"
               class="pull-right"><?php echo $lang['Reply']; ?></a>
        </div>
        <div class="well well-small well-lightblue" style="display: none;">
            <textarea class="mbs"><?php echo $lang['Reply']; ?> <?php echo '@' . $user_info['realname']; ?> ：</textarea>
            <div class="clearfix">
                <button type="button" tocid="0" touid="0" class="btn btn-primary btn-small pull-right"
                        data-act="addreply"
                        data-param="rowid=<?php echo $feedid; ?>&table=feed&module=message"><?php echo $lang['Comment']; ?></button>
            </div>
            <ul class="cmt-sub"></ul>
        </div>
    </div>
</div>
