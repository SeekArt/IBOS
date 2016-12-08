<?php
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/message.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar goes here-->
    <?php echo $this->getSidebar(array('lang' => $lang)); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <div class="mc-header">
            <ul class="mnv nl clearfix">
                <li <?php if ($type == 'receive'): ?> class="active"<?php endif; ?>>
                    <a href="<?php echo $this->createUrl('comment/index', array('type' => 'receive')); ?>">
                        <i class="o-msg-received"></i>
                        <?php echo $lang['Receive']; ?>
                    </a>
                </li>
                <li <?php if ($type == 'sent'): ?> class="active"<?php endif; ?>>
                    <a href="<?php echo $this->createUrl('comment/index', array('type' => 'sent')); ?>">
                        <i class="o-msg-sent"></i>
                        <?php echo $lang['Sent']; ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="page-list" id="msg_comment_list">
            <div class="page-list-header">
                <?php if (Ibos::app()->user->isadministrator): ?>
                    <div class="pull-left msg-toolbar">
                        <button type="button" class="btn"
                                id="start_multiple_btn"><?php echo $lang['Batch delete']; ?></button>
                    </div>
                    <div class="pull-left msg-toolbar-multiple">
                        <label class="checkbox btn"><input type="checkbox" data-name="comment"></label>
                        <button type="button" class="btn btn-danger"
                                data-action="removeComments"><?php echo $lang['Delete']; ?></button>
                        <button type="button" class="btn" id="stop_multiple_btn"><?php echo $lang['Cancel']; ?></button>
                    </div>
                <?php endif; ?>
                <!--<div class="search search-enter pull-right span3">
                    <input type="text" placeholder="Search" data-toggle="search" id="mal_search">
                    <a href="javascript:;">search</a>
                </div>-->
            </div>
            <div class="page-list-mainer">
                <?php if (!empty($list)): ?>
                    <ul class="main-list msg-list msg-comment-list" id="">
                        <?php foreach ($list as $comment): ?>
                            <li class="main-list-item" id="comment_<?php echo $comment['cid']; ?>">
                                <div class="avatar-box pull-left">
                                    <a href="<?php echo $comment['user_info']['space_url']; ?>" class="avatar-circle">
                                        <img class="mbm" src="<?php echo $comment['user_info']['avatar_middle']; ?>"/>
                                    </a>
                                    <span
                                        class="avatar-desc"><strong><?php echo $comment['user_info']['realname']; ?></strong></span>
                                </div>
                                <div class="main-list-item-body">
                                    <div class="msg-box" id="msgbox_<?php echo $comment['cid']; ?>">
                                        <span class="msg-box-arrow"><i></i></span>
                                        <div class="msg-box-body">
                                            <p class="xcm mbm text-break">
                                                <a href="<?php echo $comment['url'];?>"><?php echo $comment['content']; ?></a>
                                            </p>
                                            <p class="tcm mb">
                                                <?php echo StringUtil::replaceExpression(!empty($comment['replyInfo']) ? $comment['replyInfo'] : $comment['detail']); ?>
                                            </p>
                                            <div>
                                                <label class="checkbox checkbox-inline mbz">
                                                    <input type="checkbox" name="comment"
                                                           value="<?php echo $comment['cid']; ?>">
                                                </label>
                                                <span
                                                    class="tcm fss"><?php echo Convert::formatDate($comment['ctime'], 'u'); ?></span>
                                                <div class="pull-right">
                                                    <?php if ($comment['isCommentDel']): ?><a href="javascript:;"
                                                                                              data-action="removeComment"
                                                                                              data-param='{"id": "<?php echo $comment['cid']; ?>"}'
                                                                                              data-target="#comment_<?php echo $comment['cid']; ?>"><?php echo $lang['Delete']; ?></a><?php endif; ?>
                                                    <!--<a href="javascript:;" data-act-data="id=<?php echo $comment['cid']; ?>&reply_name=<?php echo $comment['user_info']['realname']; ?>" data-act="reply" data-target="#msgbox_<?php echo $comment['cid']; ?>"><?php echo $lang['Reply']; ?></a>-->
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-data-tip"></div>
                <?php endif; ?>
            </div>
            <div class="page-list-footer">
                <?php $this->widget('application\core\widgets\Page', array('pages' => $pages)); ?>
            </div>
        </div>
        <!-- Mainer content -->
    </div>
</div>

<script src='<?php echo $assetUrl; ?>/js/message.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/message_comment_index.js?<?php echo VERHASH; ?>'></script>
