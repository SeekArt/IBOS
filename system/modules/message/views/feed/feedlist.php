<?php

use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

?>
<?php $viewDesc = array(1 => '仅自己可见', 2 => '所在部门可见', 3 => '指定人可见'); ?>
<?php foreach ($list as $fd): ?>
    <div class="wb-ifview-box mpanel" data-node-type="feedBox" data-feed-id="<?php echo $fd['feedid'] ?>">
        <?php if ($fd['uid'] == Ibos::app()->user->uid || Ibos::app()->user->isadministrator): ?>
            <div class="wb-trash-wrap">
                <a href="javascript:;" class="o-wbf-trash" data-param='{"feedid": <?php echo $fd['feedid']; ?>}'
                   data-action="removeFeed"></a>
            </div>
        <?php endif; ?>
        <div class="wb-ifview-top rdt <?php if ($fd['module'] == 'weibo'): ?>bdbs<?php else: ?>rdb<?php endif; ?> ">
            <!--分类标志-->
            <div class="wb-ifsort-icon">
                <?php
                $typeStyle = 'normal';
                switch ($fd['type']) {
                    case 'post':
                    case 'postimage':
                    case 'repost':
                        if ($fd['module'] !== 'weibo') {
                            $typeStyle = $fd['module'];
                        } else {
                            $typeStyle = 'normal';
                        }
                        break;
                    case 'wc': // 迎新汇
                        $typeStyle = 'leaf';
                        break;
                    case 'praise':
                        $typeStyle = 'praise';
                        break;
                    default:
                        $typeStyle = 'normal';
                        break;
                }
                ?>
                <a href="javascript:;" class="o-wbtype-<?php echo $typeStyle; ?>"></a>
            </div>
            <!--用户头像-->
            <div class="wb-ifview-user">
                <div class="wb-ifview-pic">
                    <a href="<?php echo $fd['user_info']['space_url']; ?>" target="_blank" class="wb-pub-opic"
                       data-toggle="usercard" data-param="uid=<?php echo $fd['user_info']['uid']; ?>">
                        <img src="<?php echo $fd['user_info']['avatar_middle'] ?>"
                             alt="<?php echo $fd['user_info']['realname']; ?>">
                        <i class="wbi-plus"></i>
                    </a>
                </div>
                <a target="_blank" href="<?php echo $fd['user_info']['space_url']; ?>"><strong
                        class="wb-ifview-username xcbu"><?php echo $fd['user_info']['realname']; ?></strong></a>
                <?php if (isset($fd['actdesc'])): ?><span><?php echo $fd['actdesc']; ?></span><?php endif; ?>
            </div>
            <!--图文-->
            <div class="wb-info-picword clearfix">
                <?php echo stripslashes($fd['body']); ?>
            </div>
            <!--地图定位-->
            <!--<div>
                <i class="glyphicon-map-marker"></i>
                <span>暨南大学科技产业大厦</span>
                <span>&nbsp;-&nbsp;</span>
                <a href="#" class="wb-source">查看地图</a>
            </div>-->
            <!--来源信息-->
            <div class="wb-info-ads clearfix">
                <div class="wb-info-from pull-left">
                    <a href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/feed', array('uid' => $fd['uid'], 'feedid' => $fd['feedid'])); ?>"><?php echo Convert::formatDate($fd['ctime'], 'n月d日H:i'); ?></a>
                    <span>&nbsp;<?php echo $fd['from']; ?>&nbsp;</span>
                    <?php if (!empty($fd['view'])): ?>
                        <a href="javascript:;" class="o-wbi-lock mls" data-action="openAllowedUserDialog"
                           data-param='{"feedid": <?php echo $fd['feedid']; ?>}' data-toggle="tooltip"
                           title="<?php echo $viewDesc[$fd['view']]; ?>"></a>
                    <?php endif; ?>
                </div>
                <?php if ($fd['module'] == 'weibo'): ?>
                    <div class="wb-handle pull-right">
                        <a href="javascript:;" data-param='{"feedid":<?php echo $fd['feedid']; ?>}'
                           data-action="feedDigg" data-node-type="feedDiggBtn">
                            <?php if (isset($diggArr[$fd['feedid']])): ?>
                                <i class="o-wbi-good active"></i>
                                已赞( <?php echo $fd['diggcount']; ?> )
                            <?php else: ?>
                                <i class="o-wbi-good"></i>
                                赞( <?php echo $fd['diggcount']; ?> )
                            <?php endif; ?>
                        </a>
                        <?php if (empty($fd['view'])): ?>
                            <?php $sid = !empty($fd['rowid']) ? $fd['rowid'] : $fd['feedid']; ?>
                            <a href="javascript:;"
                               data-param='{"module":"<?php echo $fd['module']; ?>","curtable":"feed", "feedtype":"<?php echo $fd['type']; ?>", "sid":<?php echo $sid; ?>,"curid":<?php echo $fd['feedid']; ?> ,"stable":"<?php echo $fd['table']; ?>","isrepost":<?php echo $fd['isrepost'] ?>}'
                               data-action="openFeedForward">
                                <i class="o-wbi-mess"></i>
                                转发( <?php echo $fd['repostcount']; ?> )
                            </a>
                        <?php endif; ?>
                        <a href="javascript:;"
                           data-param='{"module":"weibo","table":"feed","rowid":"<?php echo $fd['feedid']; ?>","moduleuid":"<?php echo $fd['uid'] ?>"}'
                           data-action="openFeedComment">
                            <i class="o-wbi-frow"></i>
                            评论( <?php echo $fd['commentcount']; ?> )
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($fd['module'] == 'weibo'): ?>
            <?php
            $sourceUrl = Ibos::app()->urlManager->createUrl('weibo/personal/feed', array('feedid' => $fd['feedid']));
            $this->widget('application\modules\weibo\core\WeiboComment', array(
                'module' => $fd['module'],
                'table' => 'feed',
                'attributes' => array(
                    'rowid' => $fd['feedid'],
                    'moduleuid' => $fd['uid'],
                    'module_rowid' => $fd['rowid'],
                    'module_table' => $fd['table'],
                    'tocid' => 0,
                    'touid' => $fd['uid'],
                    'url' => $sourceUrl,
                    'detail' => Ibos::lang('Comment my weibo', '', array('{url}' => $sourceUrl, '{title}' => StringUtil::cutStr(preg_replace("/[\s]{2,}/", "", StringUtil::filterCleanHtml($fd['body'])), 50)))
                )));
            ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>