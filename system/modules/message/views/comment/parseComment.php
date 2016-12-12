<?php
use application\core\utils\Convert;

?>
<div class="cmt-item" id="comment_<?php echo $cid; ?>">
    <div class="avatar-box">
        <a href="<?php echo $userInfo['space_url']; ?>" class="avatar-circle">
            <img src="<?php echo $userInfo['avatar_middle']; ?>" width="60" height="60">
        </a>
    </div>
    <div class="cmt-body">
        <p class="mbs xcm">
            <strong class="xcn"><?php echo $userInfo['realname']; ?>：</strong>
            <?php echo $content; ?>
        </p>
        <div class="mbs fss">
            <span><?php echo Convert::formatDate($ctime, 'u'); ?></span>
            <div class="pull-right">
                <a href="javascript:;" data-act="getreply"
                   data-param='{"type":"reply","name":"<?php echo $userInfo['realname']; ?>","module":"message","table":"comment","rowid":"<?php echo $cid; ?>"}'><?php echo $lang['Reply']; ?>
                    (0)</a>
                <a href="javascript:;" class="mls" data-act="delcomment" data-param='{"cid":"<?php echo $cid; ?>"}'
                   class="mls"><?php echo $lang['Delete']; ?></a>
            </div>
        </div>
        <div class="well well-small well-lightblue" style="display: none;">
            <textarea class="mbs reply"><?php echo $lang['Reply']; ?> <?php echo '@' . $userInfo['realname']; ?>
                ：</textarea>
            <div class="clearfix">
                <button type="button" data-tocid="<?php echo $cid; ?>" data-touid="<?php echo $userInfo['uid']; ?>"
                        class="btn btn-primary btn-small pull-right" data-act="addreply"
                        data-loading-text="<?php echo $lang['Reply ing']; ?>..."
                        data-param='{"type":"reply","rowid":"<?php echo $cid; ?>","table":"comment","module":"message","moduleuid":"<?php echo $uid; ?>"}'><?php echo $lang['Reply']; ?></button>
            </div>
            <ul class="cmt-sub"></ul>
        </div>
        <div>
            <?php if (isset($attach)): ?>
                <?php foreach ($attach as $key => $value): ?>
                    <div class="media mbs">
                        <img src="<?php echo $value['iconsmall']; ?>" alt="<?php echo $value['filename']; ?>"
                             class="pull-left">
                        <div class="media-body">
                            <div class="media-heading">
                                <?php echo $value['filename']; ?> <span class="tcm">(<?php echo $value['filesize']; ?>
                                    )</span>
                            </div>
                            <div class="fss">
                                <a href="<?php echo $value['downurl']; ?>"><?php echo $lang['Download']; ?></a>
                                <?php if (isset($value['officereadurl'])): ?>
                                    <a href="javascript:;" class="mls" data-action="viewOfficeFile"
                                       data-param='{"href": "<?php echo $value['officereadurl']; ?>"}'
                                       title="<?php echo $lang['Read']; ?>">
                                        <?php echo $lang['Read']; ?>
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
