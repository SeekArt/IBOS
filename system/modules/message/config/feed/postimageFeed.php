<?php
use application\core\utils\StringUtil;
use application\modules\weibo\utils\Common;

?>
<feed module='weibo' type='postimage' info='发图片微博'>
    <title>
        <![CDATA[<?php echo $actor; ?>]]>
    </title>
    <body>
    <![CDATA[
    <div class="wb-info-picword clearfix">
        <?php if (isset($attachInfo)): ?>
            <?php foreach ($attachInfo as $att): ?>
                <div class="wb-info-pic">
                    <a href="<?php echo $att['attach_url'] ?>" data-lightbox="preview"
                       title="<?php echo $att['attach_name'] ?>">
                        <img src="<?php echo $att['attach_middle']; ?>" alt="<?php echo $att['attach_name'] ?>">
                    </a>
                    <?php if (Common::isResize($att['attach_middle'])): ?>
                        <div class="wb-info-pic-tip">
                            <div class="wb-info-pic-tipbg"></div>
                            <a href="<?php echo $att['attach_url'] ?>" data-lightbox="previewDesc"
                               title="<?php echo $att['attach_name'] ?>">点击查看完整图片</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (isset($title)): ?>
            <div class="mbs"><?php echo $title; ?></div><?php endif; ?>
        <p>
            <?php echo StringUtil::purify($body); ?>
        </p>
    </div>
    ]]>
    </body>
    <feedAttr comment="true" repost="true" like="false" favor="true" delete="true"/>
</feed>