<?php

use application\core\utils\Ibos;

?>

<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/user.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $moduleAssetUrl; ?>/css/wbpublic.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo $moduleAssetUrl; ?>/css/wbstyle.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.css?<?php echo VERHASH; ?>"/>
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/lightbox/css/lightbox.css?<?php echo VERHASH; ?>"/>
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
        <!--微博模块 左  S-->
        <div class="wbc-left pull-left">
            <div class="wbc-ll">
                <!--顶部tab S-->
                <div class="wb-per-hd clearfix mpanel">
                    <div class="search pull-right span3">
                        <input type="text" name="feedkey" placeholder="搜索" nofocus id="mn_search"/>
                        <a href="javascript:;"></a>
                    </div>
                    <ul class="nav nav-skid">
                        <!-- 个人页的全部相当于公司页的关注  -->
                        <li <?php if ($type == 'all'): ?> class="active"<?php endif; ?>>
                            <a href="<?php echo $this->createUrl('personal/index', array('type' => 'all', 'uid' => $this->getUid())) ?>">全部</a>
                        </li>
                        <!--<li>
                            <a href="javascript:;" data-action="feedList" data-param='{ "type": "praise" }' data-node-type="navpraise">表扬</a>
                        </li>
                        <li>
                            <a href="javascript:;" data-action="feedList" data-param='{ "type": "fresh" }' data-node-type="navfresh">迎新汇</a>
                        </li>-->
                        <?php if (!empty($movements)): ?>
                            <li class="posr<?php if ($type == 'movement'): ?> active<?php endif; ?>">
                                <a href="javascript:;" data-toggle="dropdown" data-toggle-role="select">
                                    <?php echo $lang['Module movements']; ?>
                                    <span class="wbi-arr-b"></span>
                                </a>
                                <ul class="dropdown-menu" data-node-type="feedExtraList">
                                    <li <?php if ($type == 'movement' && $feedtype == 'all'): ?>class="active"<?php endif; ?>>
                                        <a href="<?php echo $this->createUrl('personal/index', array('type' => 'movement', 'feedtype' => 'all', 'uid' => $this->getUid())) ?>"><?php echo $lang['All movements']; ?></a>
                                    </li>
                                    <?php foreach ($enableMovementModule as $key => $module): ?>
                                        <?php if (isset($movements[$module['module']]) && $movements[$module['module']] == 1): ?>
                                            <li <?php if ($feedtype == $module['module']): ?>class="active"<?php endif; ?>>
                                                <a href="<?php echo $this->createUrl('personal/index', array('type' => 'movement', 'feedtype' => $module['module'], 'uid' => $this->getUid())) ?>"><?php echo $module['name']; ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="wb-info-box wbindex" id="wb_main" data-node-type="feedList">
                    <?php if (!empty($html)): ?>
                        <?php echo $html; ?>
                        <!-- 加载更多 -->
                        <div class="wb-ifsort-more" data-node-type="loadMoreFeed">
                            <i class="o-wbtype-more"></i>
                            <div data-node-type="loadMoreFeedTip" style="display: none;">
                                <!--分类标志-->
                                <!-- 如果没有更多时，不显示这个节点 -->
                                <a href="javascript:;" class="wb-see-new disabled">
                                    <i class="loading-mini"></i>
                                    &nbsp;读取中...
                                </a>
                            </div>
                            <div data-node-type="page"><?php
                                if (isset($_GET['page'])):echo $pageData;
                                endif;
                                ?></div>
                        </div>
                    <?php else: ?>
                        <div class="no-data-tip"></div>
                        <div class="wb-ifsort-more" data-node-type="loadMoreFeed">
                            <i class="o-wbtype-more"></i>
                            <div data-node-type="page"><?php
                                if (isset($_GET['page'])):echo $pageData;
                                endif;
                                ?></div>
                        </div>
                    <?php endif; ?>
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
<script>
    var params = {
        uid: '<?php echo $this->getUid(); ?>',
        wbnums: '<?php echo Ibos::app()->setting->get('setting/wbnums'); ?>',
        firstId: '<?php echo $firstId; ?>',
        loadId: '<?php echo $lastId; ?>',
        maxId: '<?php echo $firstId; ?>',
        loadmore: '<?php echo $loadMore; ?>',
        loadnew: '<?php echo $loadNew; ?>',
        type: '<?php echo $type; ?>',
        feedtype: '<?php echo $feedtype; ?>',
        feedkey: '<?php echo $feedkey; ?>',
        inHome: 0,
        submitInterval: <?php echo intval(Ibos::app()->setting->get('setting/wbpostfrequency')) * 1000; ?>
    };
    Ibos.app.setPageParam(params);
    $("#mn_search").search(function (val) {
        window.location.href = Ibos.app.url('weibo/personal/index', {
            feedkey: val,
            uid: Ibos.app.g('uid'),
            type: Ibos.app.g("type"),
            feedtype: Ibos.app.g("feedtype")
        });
    });
</script>
<script src="<?php echo STATICURL; ?>/js/src/emotion.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/lightbox/js/lightbox.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/app/ibos.charCount.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $moduleAssetUrl; ?>/js/lang/zh-cn.js"></script>
<script src="<?php echo $moduleAssetUrl; ?>/js/weibo.js"></script>
<script src="<?php echo $moduleAssetUrl; ?>/js/weibo_personal_index.js"></script>

