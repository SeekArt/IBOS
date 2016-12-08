<?php

use application\core\utils\Ibos;

?>
    <div style="width:493px; margin:0 auto;padding-bottom:15px;border-bottom:1px solid #f6f6f6">
        <span
            style="width:330px; font-size:14px; font-weight:bold; color:#1180c6;"><?php echo Ibos::lang('Active share member', 'weibo.default'); ?></span>
        <span
            style="font-size:12px; color:#9097a9;"><?php echo Ibos::lang('Active share member tip', 'weibo.default'); ?></span>
    </div>
<?php foreach ($recentFeeds as $feed): ?>
    <div style="width:493px; margin:0 auto;border-bottom:1px dashed #d7d4d4">
        <p style="font-size:14px;font-weight:bold; color:#50545f;"><?php echo $feed['user_info']['realname']; ?><span
                style="font-size:12px;color:#50545f; font-weight:normal">(<?php echo $feed['user_info']['deptname']; ?>
                /<?php echo $feed['user_info']['posname']; ?>)</span></p>
        <p style="font-size:14px; color:#818797;"><?php echo strip_tags($feed['content']); ?></p>
    </div>
<?php endforeach; ?>