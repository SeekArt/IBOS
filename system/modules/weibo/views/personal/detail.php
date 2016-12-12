<?php
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/user.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $moduleAssetUrl; ?>/css/wbpublic.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo $moduleAssetUrl; ?>/css/wbstyle.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/lightbox/css/lightbox.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>"/>
<div class="wrap">
    <!--顶部 S-->
    <div class="mc mcf clearfix">
        <!--用户信息 S-->
        <?php echo $this->getHeader($lang); ?>
        <!--用户信息 E-->
        <!--导航 S-->
        <div>
            <ul class="nav nav-tabs nav-tabs-large nav-justified">
                <li>
                    <a href="<?php echo Ibos::app()->urlManager->createUrl('user/home/index', array('uid' => $this->getUid())); ?>"><?php echo $lang['Home page']; ?></a>
                </li>
                <li class="active">
                    <a href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/index', array('uid' => $this->getUid())); ?>"><?php echo $lang['Weibo']; ?></a>
                </li>
                <?php if ($this->getIsMe()): ?>
                    <li>
                        <a href="<?php echo Ibos::app()->urlManager->createUrl('user/home/credit'); ?>"><?php echo $lang['Credit']; ?></a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="<?php echo Ibos::app()->urlManager->createUrl('user/home/personal', array('uid' => $this->getUid())); ?>"><?php echo $lang['Profile']; ?></a>
                </li>
            </ul>
        </div>
        <!--导航 E-->
    </div>
    <!--顶部 E-->

    <!--微博模块 S-->
    <div class="wb-pers clearfix">
        <?php
        $this->renderPartial('sidebar', array(
            'isMe' => $this->getIsMe(),
            'colleagues' => $colleagues,
            'bothfollow' => isset($bothfollow) ? $bothfollow : null,
            'secondfollow' => isset($secondfollow) ? $secondfollow : null,
        ));
        ?>
        <?php $viewDesc = array(1 => '仅自己可见', 2 => '所在部门可见', 3 => '指定人可见'); ?>
        <!--微博模块 左  S-->
        <div class="wbc-left pull-left">
            <div class="wbc-ll">
                <!--顶部tab E-->
                <div id="wb_main" data-node-type="feedList">
                    <div class="wb-ifview-box mpanel" data-node-type="feedBox" data-feed-id="<?php echo $fd['uid']; ?>">
                        <?php if ($fd['uid'] == Ibos::app()->user->uid || Ibos::app()->user->isadministrator): ?>
                            <div class="wb-trash-wrap">
                                <a href="javascript:;" class="o-wbf-trash"
                                   data-param='{"redirectToUid":<?php echo $fd['uid']; ?>,"feedid": <?php echo $fd['feedid']; ?>}'
                                   data-action="removeFeed"></a>
                            </div>
                        <?php endif; ?>
                        <div class="wb-ifview-top rdt bdbs">
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
                                    <span><?php echo Convert::formatDate($fd['ctime'], 'n月d日H:i'); ?></span>
                                    <span>&nbsp;<?php echo $fd['from']; ?>&nbsp;</span>
                                    <?php if (!empty($fd['view'])): ?>
                                        <a href="javascript:;" class="o-wbi-lock mls"
                                           data-action="openAllowedUserDialog"
                                           data-param='{"feedid": <?php echo $fd['feedid']; ?>}' data-toggle="tooltip"
                                           title="<?php echo $viewDesc[$fd['view']]; ?>"></a>
                                    <?php endif; ?>
                                </div>
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
                                    <a href="javascript:;" data-action="openFeedComment"
                                       data-param='{"module":"weibo","table":"feed","rowid":"<?php echo $fd['feedid']; ?>","moduleuid":"<?php echo $fd['uid'] ?>"}'>
                                        <i class="o-wbi-frow"></i>
                                        评论( <?php echo $fd['commentcount']; ?> )
                                    </a>
                                </div>
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
                                    'showlist' => 1,
                                    'url' => $sourceUrl,
                                    'detail' => Ibos::lang('Comment my weibo', '', array('{url}' => $sourceUrl, '{title}' => StringUtil::cutStr(preg_replace("/[\s]{2,}/", "", StringUtil::filterCleanHtml($fd['body'])), 50)))
                                )));
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!--微博模块 左  E-->
    </div>
    <!--微博模块 E-->
</div>
<!-- 赞过的人 -->
<div class="wb-digg-box popover fade bottom in" id="menu_digg_box" data-node-type="feedDiggBox" style="display: none; ">
    <div class="arrow"></div>
    <div class="popover-content" data-node-type="feedDiggContent">
        <ul class="list-inline" data-node-type="feedDiggList">
        </ul>
    </div>
</div>
<script src="<?php echo STATICURL; ?>/js/src/emotion.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/lightbox/js/lightbox.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/app/ibos.charCount.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $moduleAssetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $moduleAssetUrl; ?>/js/weibo.js"></script>
<script src="<?php echo $moduleAssetUrl; ?>/js/weibo_personal_index.js"></script>

