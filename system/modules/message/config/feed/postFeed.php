<?php
use application\core\utils\StringUtil;
?>
<feed module='message' type='post' info='原创微博'>
    <title comment="feed标题">
        <![CDATA[<?php echo $actor; ?>]]>
    </title>
    <body comment="feed详细内容/引用的内容">
    <![CDATA[
    <?php if (isset($title)): ?>
        <div class="mbs"><?php echo $title; ?></div><?php endif; ?>

    <?php echo StringUtil::purify($body); ?>
    ]]>
    </body>
    <feedAttr comment="true" repost="true" like="true" favor="true" delete="true"/>
</feed>