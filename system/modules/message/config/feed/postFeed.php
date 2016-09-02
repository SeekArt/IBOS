<feed module='message' type='post' info='原创微博'>
    <title comment="feed标题">
        <![CDATA[<?php echo $actor; ?>]]>
    </title>
    <body comment="feed详细内容/引用的内容">
    <![CDATA[
    <?php if ( isset( $title ) ): ?><div class="mbs"><?php echo $title; ?></div><?php endif; ?>
    <!--
        备注：
        这里已使用 CHtmlPurifier 过滤不安全的元素。
        具体请查看：system\modules\message\model\Feed.php LINE 810
    -->
    <?php echo  $body; ?>
    ]]>
    </body>
    <feedAttr comment="true" repost="true" like="true" favor="true" delete="true" />
</feed>