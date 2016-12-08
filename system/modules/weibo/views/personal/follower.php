<?php
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

?>
<!-- private css -->
<link rel="stylesheet"
      href="<?php echo Ibos::app()->assetManager->getAssetsUrl('user'); ?>/css/user.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $moduleAssetUrl; ?>/css/wbpublic.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo $moduleAssetUrl; ?>/css/wbstyle.css?<?php echo VERHASH; ?>"/>
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
    <div class="wb-fans-box">
        <div class="wb-per-hd mpanel">
            <ul class="nav nav-skid">
                <li>
                    <a href="<?php echo $this->createUrl('personal/following', array('uid' => $this->getUid())); ?>"><?php if ($this->getIsMe()): ?>我<?php else: ?>TA<?php endif; ?>
                        的关注（<?php echo $count['following']; ?>）</a>
                </li>
                <li class="active">
                    <a class="active"
                       href="<?php echo $this->createUrl('personal/follower', array('uid' => $this->getUid())); ?>"><?php if ($this->getIsMe()): ?>我<?php else: ?>TA<?php endif; ?>
                        的粉丝（<?php echo $count['follower']; ?>）</a>
                </li>
            </ul>
        </div>
        <div class="wb-fans-list">
            <?php if (!empty($count)): ?>
                <ul class="clearfix" data-node-type="followList">
                    <?php foreach ($list as $uid => $followState) : ?>
                        <li>
                            <div class="wb-fans-card">
                                <div class="wbc-box rdt bdbs">
                                    <div class="mbs">
                                        <a data-toggle="usercard"
                                           data-param="uid=<?php echo $followState['user']['uid']; ?>"
                                           href="<?php echo $followState['user']['space_url']; ?>"
                                           class="avatar-circle">
                                            <img src="<?php echo $followState['user']['avatar_middle']; ?>"
                                                 alt="<?php echo $followState['user']['realname']; ?>"/>
                                        </a>
                                    </div>
                                    <div class="wb-fans-name">
                                        <strong><?php echo $followState['user']['realname']; ?></strong><?php if (!empty($followState['user']['posname'])): ?>
                                            <span>&nbsp;·&nbsp;</span>
                                            <span><?php echo $followState['user']['posname']; ?></span><?php endif; ?>
                                    </div>
                                    <div class="wb-fans-from">
                                        <span
                                            title="<?php echo $followState['user']['bio']; ?>"><?php if (!empty($followState['user']['bio'])): ?><?php echo StringUtil::cutStr($followState['user']['bio'], 18); ?><?php else: ?>这家伙很懒,什么都没有写<?php endif; ?></span>
                                    </div>
                                </div>
                                <div class="rdb wbc-box2">
									<span class="wb-cb followedboth">
										<?php if (!$followState['following']): ?>
                                            <a href="javascript:;" class="btn btn-small btn-warning"
                                               data-action="follow"
                                               data-param='{"fid": <?php echo $followState['user']['uid']; ?>}'
                                               data-loading-text="关注中...">
                                                <i class="om-plus"></i>
                                                关注
                                            </a>
                                        <?php elseif ($followState['following'] && $followState['follower']): ?>
                                            <a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn"
                                               data-action="unfollow"
                                               data-param='{"fid": <?php echo $followState['user']['uid']; ?>}'
                                               data-loading-text="取消中...">
                                                <i class="om-geoc"></i>
                                                互相关注
                                            </a>
                                        <?php elseif ($followState['following']): ?>
                                            <a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn"
                                               data-action="unfollow"
                                               data-param='{"fid": <?php echo $followState['user']['uid']; ?>}'
                                               data-loading-text="取消中...">
                                                <i class="om-gcheck"></i>
                                                已关注
                                            </a>
                                        <?php endif; ?>
									</span>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                暂时没有关注<?php if ($this->getIsMe()): ?>我<?php else: ?>TA<?php endif; ?>的人
            <?php endif; ?>
        </div>
        <?php if ($count['follower'] > $limit): ?>
            <a href="javascript:;" class="wb-see-new" data-node-type="loadMoreFollowBtn" data-offset="25"
               data-action="loadMoreFollow" data-param='{"type": "follower","uid":<?php echo $this->getUid(); ?>}'>
                <i class="glyphicon-chevron-down"></i>
                查看更多
            </a>
        <?php endif; ?>
    </div>
</div>
<script src="<?php echo $moduleAssetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $moduleAssetUrl; ?>/js/weibo.js"></script>
<script src="<?php echo $moduleAssetUrl; ?>/js/weibo_personal_follow.js"></script>