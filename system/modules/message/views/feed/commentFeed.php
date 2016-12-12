<feed app='ibos2' type='comment' info='评论'>
    <title comment="评论标题">
        <![CDATA[<?php echo $actorRealName; ?>]]>
    </title>
    <body comment="评论内容">
    <![CDATA[<?php echo $body; ?> ]]>
    </body>
    <feedAttr feedid="<?php echo $feedid; ?>"/>
</feed>