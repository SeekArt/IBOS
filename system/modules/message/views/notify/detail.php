<?php
use application\core\utils\Ibos;

?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/message.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar goes here-->
    <?php echo $this->getSidebar(array('lang' => $lang)); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <div class="page-list" id="remind_list">
            <div class="page-list-header">
                <div class="msg-toolbar">
                    <button type="button" onclick="location.href = '<?php echo $this->createUrl('notify/index'); ?>'"
                            class="btn"><?php echo $lang['Return']; ?></button>
                    <button type="button" class="btn"
                            id="start_multiple_btn"><?php echo $lang['Batch delete']; ?></button>
                </div>
                <div class="msg-toolbar msg-toolbar-multiple">
                    <label class="checkbox btn">
                        <input type="checkbox" data-name="remind">
                    </label>
                    <button type="button" class="btn btn-danger"
                            data-action="removeDetailNotices"><?php echo $lang['Delete']; ?></button>
                    <button type="button" class="btn" id="stop_multiple_btn"><?php echo $lang['Cancel']; ?></button>
                </div>
            </div>
            <?php if (!empty($list)): ?>
                <div class="msg-remind-detail">
                    <?php foreach ($list as $time => $msgs): ?>
                        <?php $month = substr($time, 4);
                        rsort($msgs); ?>
                        <div class="msg-remind-timeline">
                            <i class="o-timeline-point"></i>
                            <div class="msg-box msg-box-inverse bglb">
                                <span class="msg-box-arrow"><i></i></span>
                                <div class="msg-box-body"><?php echo $month; ?><?php echo $lang['Month']; ?></div>
                            </div>
                            <?php foreach ($msgs as $msg) : ?>
                                <div class="msg-box <?php if ($msg['isread'] === "0") : ?>msg-alert<?php endif; ?>"
                                     id="timeline_<?php echo $msg['id']; ?>">
                                    <i class="o-timeline-subpoint"></i>
                                    <span class="msg-box-arrow"><i></i></span>
                                    <div class="msg-box-body">
                                        <a href="<?php if (!empty($msg['url'])) {
                                            echo $this->createUrl('notify/jump', array('id' => $msg['id'], 'url' => $msg['url']));
                                        } ?>">
                                            <?php echo $msg['title']; ?>
                                        </a>
                                        <div>
                                            <label class="checkbox checkbox-inline mbz">
                                                <input type="checkbox" name="remind" value="<?php echo $msg['id']; ?>">
                                            </label>
                                            <span class="tcm"><?php echo date('m-d H:i', $msg['ctime']); ?></span>
                                            <div class="pull-right">
                                                <a href="javascript:;" class="msg-box-del"
                                                   data-action="removeDetailNotice"
                                                   data-param='{"id": "<?php echo $msg['id']; ?>"}'><?php echo $lang['Delete']; ?></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data-tip"></div>
            <?php endif; ?>
            <div class="page-list-footer">
                <?php $this->widget('application\core\widgets\Page', array('pages' => $pages)); ?>
            </div>
        </div>
        <!-- Mainer content -->
    </div>
</div>
<script src='<?php echo $assetUrl; ?>/js/message.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/message_notify_detail.js?<?php echo VERHASH; ?>'></script>