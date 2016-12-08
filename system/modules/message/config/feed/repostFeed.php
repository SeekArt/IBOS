<?php

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

?>
<feed app='public' type='repost' info='转发微博'>
    <title>
        <![CDATA[<?php echo $actor; ?>]]>
    </title>
    <body>
    <![CDATA[
    <div style="margin: 10px 0;"><?php echo $body; ?></div>
    <div class="wb-rep">
        <div class="wb-at-someone">
            <a href="<?php echo $sourceInfo['source_user_info']['space_url']; ?>"
               class="wb-source">@<?php echo $sourceInfo['source_user_info']['realname'] ?></a>
        </div>
        <?php if ($sourceInfo['isdel'] == 0 && $sourceInfo['source_user_info'] != false): ?>
            <!--转载的内容 S-->
            <div class="wb-info-picword clearfix">
                <?php if ($sourceInfo['has_attach']): ?>
                    <?php foreach ($sourceInfo['attach'] as $att): ?>
                        <div class="wb-info-pic">
                            <a href="<?php echo $att['attach_url'] ?>" data-lightbox="preview"
                               title="<?php echo $att['attach_name'] ?>">
                                <img src="<?php echo $att['attach_middle']; ?>" alt="<?php echo $att['attach_name'] ?>">
                            </a>
                            <div class="wb-info-pic-tip">
                                <div class="wb-info-pic-tipbg"></div>
                                <a href="<?php echo $att['attach_url'] ?>" data-lightbox="preview"
                                   title="<?php echo $att['attach_name'] ?>">点击查看完整图片</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <p>
                    <?php echo StringUtil::purify($sourceInfo['source_content']); ?>
                </p>
            </div>
            <div class="wb-info-ads clearfix">
                <div class="wb-info-from pull-left">
                    <span><?php echo $sourceInfo['ctime']; ?></span>
                    <span>&nbsp;<?php echo Env::getFromClient($sourceInfo['from'], $sourceInfo['module']); ?>
                        &nbsp;</span>
                </div>
                <div class="wb-handle pull-right">
                    <a href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/feed', array('uid' => $sourceInfo['uid'], 'feedid' => $sourceInfo['feedid'])); ?>"
                       target="_blank">
                        <i class="o-wbi-frow"></i>
                        转发( <?php echo $sourceInfo['repostcount']; ?> )
                    </a>
                    <a href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/feed', array('uid' => $sourceInfo['uid'], 'feedid' => $sourceInfo['feedid'])); ?>"
                       target="_blank">
                        <i class="o-wbi-mess"></i>
                        评论( <?php echo $sourceInfo['commentcount']; ?> )
                    </a>
                </div>
            </div>
        <?php else: ?>
            内容已被删除
        <?php endif; ?>
    </div>
    ]]>
    </body>
    <feedAttr comment="true" repost="true" like="true" favor="true" delete="true"/>
</feed>
<!--微博盒子 E-->