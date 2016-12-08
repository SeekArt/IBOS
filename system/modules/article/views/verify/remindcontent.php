<?php

use application\core\utils\Ibos;

?>
    <div style="width:493px; margin:0 auto; text-align:center">
        <span
            style=" color:#1180c6;font-size:14px;line-height:30px;font-weight:bold"><?php echo $article['subject']; ?></span>
    </div>
    <div style="width:493px; margin:0 auto; text-align:center; padding-bottom:5px; border-bottom:1px dashed #e4e4e4;">
        <span
            style=" color:#818797; font-size:12px; line-height:30px"><?php echo $author; ?><?php echo Ibos::lang('Posted on',
                'article.default'); ?><?php echo date('Y-m-d H:i', $article['addtime']); ?></span>
    </div>
<?php echo strip_tags($article['content'], '<a><p>'); ?>