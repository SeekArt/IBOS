<?php

use application\modules\user\model\User;

?>

    <div style="width:493px; margin:0 auto;">
        <span
            style=" color:#1180c6;font-size:14px;line-height:30px;font-weight:bold"><?php echo $body['subject']; ?></span>
    </div>
    <div style="width:493px; margin:0 auto;">
        <span
            style=" color:#818797; font-size:12px; line-height:30px"><?php echo User::model()->fetchRealnameByUid($body['fromid']); ?>
            发送于 <?php echo date('Y-m-d H:i', $body['sendtime']); ?></span>
    </div>
    <div style="width:493px; margin:0 auto;">
        <span
            style=" color:#818797; font-size:12px; line-height:30px">收件人：<?php echo User::model()->fetchRealnamesByUids($body['toids']); ?>
            　　抄送：<?php echo User::model()->fetchRealnamesByUids($body['copytoids']); ?></span>
    </div>
<?php echo $body['content']; ?>