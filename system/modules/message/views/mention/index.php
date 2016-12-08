<?php

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
        <div class="page-list">
            <div class="page-list-header">
            </div>
            <div class="page-list-mainer">
                <?php if (!empty($list)): ?>
                    <ul class="main-list msg-list" id="atme_list">
                        <?php foreach ($list as $key => $at) : ?>
                            <li class="main-list-item">
                                <div class="avatar-box pull-left">
                                    <?php if ($at['source_table'] == 'comment'): ?>
                                        <a href="<?php echo $at['comment_user_info']['space_url']; ?>"
                                           class="avatar-circle">
                                            <img class="mbm"
                                                 src="<?php echo $at['comment_user_info']['avatar_middle']; ?>" alt="">
                                        </a>
                                        <span
                                            class="avatar-desc"><strong><?php echo $at['comment_user_info']['realname']; ?></strong></span>
                                    <?php else : ?>
                                        <a href="<?php echo $at['source_user_info']['space_url']; ?>"
                                           class="avatar-circle">
                                            <img class="mbm"
                                                 src="<?php echo $at['source_user_info']['avatar_middle']; ?>" alt="">
                                        </a>
                                        <span
                                            class="avatar-desc"><strong><?php echo $at['source_user_info']['realname']; ?></strong></span>
                                    <?php endif; ?>
                                </div>
                                <div class="main-list-item-body">
                                    <div class="msg-box" id="msgbox_<?php echo $key; ?>">
                                        <span class="msg-box-arrow"><i></i></span>
                                        <div class="msg-box-body">
                                            <p class="xcm mbm">
                                                <?php echo StringUtil::parseHtml($at['source_content']); ?>
                                            </p>
                                            <p class="tcm mb">
                                                <?php echo StringUtil::replaceExpression($at['detail']); ?>
                                            </p>
                                            <div>
                                                <span
                                                    class="tcm fss"><?php if ($at['source_table'] == 'comment'): ?><?php echo date('n月j日H:i', $at['ctime']); ?><?php else: ?><?php echo $at['ctime']; ?><?php endif; ?></span>
                                                <!--<div class="pull-right">	
													<a href="javascript:;" data-act="reply" data-act-data="id=<?php echo $key; ?>&reply_name=<?php echo $at['source_user_info']['realname']; ?>" data-target="#msgbox_<?php echo $key; ?>"><?php echo $lang['Reply']; ?></a>
												</div>-->
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
                <?php
                $this->widget('application\core\widgets\Page', array('pages' => $pages));
                ?>
            </div>
        </div>
        <!-- Mainer content -->
    </div>
</div>

<script src='<?php echo $assetUrl; ?>/js/message.js?<?php echo VERHASH; ?>'></script>
