<?php

use application\core\utils\IBOS;
use application\core\utils\Module;
?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/wbpublic.css?<?php echo VERHASH; ?>" />
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/wbstyle.css?<?php echo VERHASH; ?>" />
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>" />
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.css?<?php echo VERHASH; ?>" />
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/lightbox/css/lightbox.css?<?php echo VERHASH; ?>" />

<div class="mtw">
    <div class="mtw-portal-nav-wrap">
        <ul class="portal-nav clearfix">
            <li>
                <a href="<?php echo IBOS::app()->urlManager->createUrl( 'main/default/index' ); ?>">
                    <i class="o-portal-office"></i>
                    办公门户
                </a>
            </li>
            <li class="active">
                <a href="<?php echo IBOS::app()->urlManager->createUrl( 'weibo/home/index' ); ?>">
                    <i class="o-portal-personal"></i>
                    个人门户
                </a>
            </li>
            <?php if ( Module::getIsEnabled( 'app' ) ): ?>
                <li >
                    <a href="<?php echo IBOS::app()->urlManager->createUrl( 'app/default/index' ); ?>">
                        <i class="o-portal-app"></i>
                        常用工具
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <span class="pull-right"><?php echo IBOS::app()->setting->get( 'lunar' ); ?></span>
</div>

<div class="wrap">
    <div class="wb-topic clearfix">
        <div class="wbc-left pull-left" id="wb_home_mainer">
            <!--微博盒子 S  -->
            <div class="wb-publish mpanel" data-node-type="publishWrap">
                <div id="wb_pub">
                    <!--tab切换-->
                    <div class="wb-pub-tit"></div>
                    <!--tab切换内容盒子-->
                    <div class="wb-pub-box">
                        <textarea rows="4" class="wb-pub-textarea" data-node-type="textarea" id="wb_pub_textarea"></textarea>
                    </div>
                </div>
                <!--发布按钮和功能按钮-->
                <div class="wb-pub-other clearfix">
                    <div class="wb-pub-menu">
                        <a href="javascript:;" class="o-wb-face" data-action="face" id="wb_face"></a>
                        <a href="javascript:;" class="o-wb-pic" data-action="pic"></a>
                        <!-- <a href="javascript:;" class="o-wb-topic" data-action="topic"></a> -->
                        <!-- <a href="javascript:;" class="o-wb-medal" data-action="medal"></a> -->
                    </div>
                    <div class="pull-right">
                        <span class="posr">
                            <a href="javascript:;" data-toggle="dropdown" data-node-type="select">
                                <?php echo $lang['Feed view scope company']; ?>
                                <i class="wbi-arr-b"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="active"><a href="javascript:;" data-action="publishTo" data-param='{"type": 0, "text": "<?php echo $lang['Feed view scope company']; ?>"}'><?php echo $lang['Feed view scope company']; ?></a></li>
                                <li><a href="javascript:;" data-action="publishTo" data-param='{"type": 1, "text": "<?php echo $lang['Feed view scope self'] ?>"}'><?php echo $lang['Feed view scope self'] ?></a></li>
                                <li><a href="javascript:;" data-action="publishTo" data-param='{"type": 2, "text": "<?php echo $lang['Feed view scope dept'] ?>"}'><?php echo $lang['Feed view scope dept'] ?></a></li>
                                <li><a href="javascript:;" data-action="publishTo" data-param='{"type": 3, "text": "<?php echo $lang['Feed view scope specifiy']; ?>"}'><?php echo $lang['Feed view scope specifiy']; ?></a></li>
                            </ul>
                        </span>
                        <button class="btn mls" disabled="true" data-action="publish" data-node-type="publishBtn" data-loading-text="<?php echo $lang['Publish ing']; ?>"><?php echo $lang['Publish']; ?></button>
                    </div>
                </div>
                <!--上传图片-->
                <div class="wb-upload-img" data-node-type="picBox">
                    <!-- Flash占位 -->
                    <div class="wb-upload-btn">
                        <span id="wb_imgupload"></span>
                    </div>
                    <!-- 上传引导 -->
                    <div class="wb-upload-holder">
                        <div class="wb-upload-pic">
                            <i class="pic-holder"></i>
                            <p><strong><?php echo $lang['Upload one photo']; ?></strong></p>
                        </div>
                    </div>
                    <!-- 成功提示 -->
                    <div class="wb-upload-success-tip">
                        <i class="cbtn o-ok active"></i>
                        <?php echo $lang['Upload success']; ?>
                    </div>
                    <input type="hidden" name="picid" data-node-type="picId">
                </div>
                <!-- 指定人可见选择范围 -->
                <div data-node-type="publishToRange" class="wb-pub-range" style="display: none;">
                    <input type="hidden" data-node-type="publishRange">
                </div>
            </div>
            <!--微博盒子 E-->
            <div>
                <!--顶部tab S-->
                <div class="wb-per-hd clearfix mpanel">
                    <div class="search pull-right">
                        <input type="text" name="feedkey" placeholder="搜索" nofocus id="mn_search" />
                        <a href="javascript:;"></a>
                    </div>
                    <ul class="nav nav-skid">
                        <li<?php if ( $type == 'all' ): ?> class="active"<?php endif; ?>>
                            <a href="<?php echo $this->createUrl( 'home/index', array( 'type' => 'all' ) ) ?>"><?php echo $lang['All']; ?></a>
                        </li>
                        <li<?php if ( $type == 'following' ): ?> class="active"<?php endif; ?>>
                            <a href="<?php echo $this->createUrl( 'home/index', array( 'type' => 'following' ) ) ?>"><?php echo $lang['Follow']; ?></a>
                        </li>
                        <!--<li>
                            <a href="javascript:;" data-action="feedList" data-param='{ "type": "praise" }' data-node-type="navpraise">表扬</a>
                        </li>
                        <li>
                            <a href="javascript:;" data-action="feedList" data-param='{ "type": "fresh" }' data-node-type="navfresh">迎新汇</a>
                        </li>-->
                        <?php if ( !empty( $movements ) ): ?>
                            <li class="posr<?php if ( $type == 'movement' ): ?> active<?php endif; ?>">
                                <a href="javascript:;" data-toggle="dropdown" data-toggle-role="select">
                                    <?php echo $lang['Module movements']; ?>
                                    <span class="wbi-arr-b"></span>
                                </a>
                                <ul class="dropdown-menu" data-node-type="feedExtraList">
                                    <li <?php if ( $type == 'movement' && $feedtype == 'all' ): ?>class="active"<?php endif; ?>>
                                        <a href="<?php echo $this->createUrl( 'home/index', array( 'type' => 'movement', 'feedtype' => 'all' ) ) ?>"><?php echo $lang['All movements']; ?></a>
                                    </li>
                                    <?php foreach ( $enableMovementModule as $key => $module ): ?>
                                        <?php if ( isset( $movements[$module['module']] ) && $movements[$module['module']] == 1 ): ?>
                                            <li <?php if ( $feedtype == $module['module'] ): ?>class="active"<?php endif; ?>>
                                                <a href="<?php echo $this->createUrl( 'home/index', array( 'type' => 'movement', 'feedtype' => $module['module'] ) ) ?>"><?php echo $module['name']; ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!--顶部tab E-->
                <div class="wb-info-box wbindex" id="wb_main" data-node-type="feedList">
                    <?php if ( !empty( $html ) ): ?>
                        <?php echo $html; ?>
                        <!-- 加载更多 -->
                        <div class="wb-ifsort-more" data-node-type="loadMoreFeed">
                            <i class="o-wbtype-more"></i>
                            <div data-node-type="loadMoreFeedTip" style="display: none;">
                                <!--分类标志-->
                                <!-- 如果没有更多时，不显示这个节点 -->
                                <a href="javascript:;" class="wb-see-new disabled" >
                                    <i class="loading-mini"></i>
                                    &nbsp;<?php echo $lang['Loading ing']; ?>
                                </a>
                            </div>
                            <div data-node-type="page"><?php
                                if ( isset( $_GET['page'] ) ):echo $pageData;
                                endif;
                                ?></div>
                        </div>
                    <?php else: ?>
                        <div class="no-data-tip"></div>
                        <div class="wb-ifsort-more" data-node-type="loadMoreFeed">
                            <i class="o-wbtype-more"></i>
                            <div data-node-type="page"><?php
                                if ( isset( $_GET['page'] ) ):echo $pageData;
                                endif
                                ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- 右侧栏 -->
        <div class="wbc-right pull-right" >
            <div id="wb_sidebar">
                <!-- 个人信息栏 -->
                <div class="mpanel">
                    <div class="wb-tpc-user bdbs">
                        <div class="wb-tpcu-banner rdt">
                            <img src="<?php echo IBOS::app()->user->bg_small; ?>" alt="<?php echo IBOS::app()->user->realname; ?>" />
                        </div>
                        <div class="wb-tpcu-usi">
                            <div class="wb-tpcu-pic">
                                <a href="<?php echo IBOS::app()->user->space_url; ?>" class="avatar-circle">
                                    <img src="<?php echo IBOS::app()->user->avatar_big; ?>" alt="<?php echo IBOS::app()->user->realname; ?>" />
                                </a>
                            </div>
                            <div class="wb-tpcu-name"> <strong><?php echo IBOS::app()->user->realname; ?></strong>
                                &nbsp; <strong>·</strong>
                                &nbsp;
                                <small><?php echo IBOS::app()->user->posname; ?></small>
                            </div>
                            <div class="wb-tpcu-num ">
                                <ul>
                                    <li>
                                        <a href="<?php echo $this->createUrl( 'personal/following' ); ?>"> <strong class="xcbu"><?php echo isset( $userData['following_count'] ) ? $userData['following_count'] : 0; ?></strong>
                                            <p><?php echo $lang['Follow']; ?></p>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo $this->createUrl( 'personal/follower' ); ?>">
                                            <strong class="xcbu"><?php echo isset( $userData['follower_count'] ) ? $userData['follower_count'] : 0; ?></strong>
                                            <p><?php echo $lang['Fans']; ?></p>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo $this->createUrl( 'personal/index' ); ?>">
                                            <strong class="xcbu"><?php echo isset( $userData['feed_count'] ) ? $userData['feed_count'] : 0; ?></strong>
                                            <p><?php echo $lang['Weibo']; ?></p>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="wb-exp rdb">
                        <!-- Status 1 start-->
                        <div class="mbs">
                            <span> 
                                <i class="wb-lv lv<?php echo IBOS::app()->user->level; ?>"></i>
                                &nbsp;
                                <strong><?php echo IBOS::app()->user->group_title; ?></strong>
                            </span>
                            <span class="exp-val"> <em><?php echo IBOS::app()->user->credits; ?></em>
                                /<?php echo IBOS::app()->user->next_group_credit; ?>
                            </span>
                        </div>
                        <div class="progress mbs" id="exp_info">
                            <div class="progress-bar " style="width: <?php echo IBOS::app()->user->upgrade_percent; ?>%;"></div>
                        </div>
                    </div>
                </div>
                <!-- @Todo: 认识新同事, 后期完善 -->
                <!--<div class="mpanel wbb-mt20" id="user_recommend">
                    <div class="wbc-box rdt bdbs"> <i class="o-wbr-leaf"></i> <strong class="wbc-tit">认识新同事</strong>
                    </div>
                    <div class="wb-new-friend">
                        <ul>
                            <li class="clearfix" data-node-type="quickFollowBox">
                                <div class="clearfix">
                                    <button type="button" class="btn btn-small pull-right wbb-mt10" data-action="quickFollow" data-loading-text="关注中...">
                                        <i class="om-gplus"></i>
                                        关注
                                    </button>
                                    <div class="pull-left clearfix">
                                        <div class="pull-left wbb-pr10">
                                            <a href="#" class="wb-pub-opic">
                                                <img src="<?php echo $assetUrl; ?>/image/defaultAva.gif" alt="" />
                                            </a>
                                        </div>
                                        <div class="pull-right wbb-pt10 wb-nf-info" >
                                            <strong>小胖</strong>
                                            <p>博思协创 打酱油份子</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="wb-nf-notes">
                                    我是博思协创部门的新同事，很高兴认识大家，请多关照
                                    <a href="#" class="xcbu">@por</a>
                                    <div class="wb-nf-arr"><i></i></div>
                                </div>
                            </li>
                            <li class="clearfix" data-node-type="quickFollowBox">
                                <div class="clearfix">
                                    <button type="button" class="btn btn-small pull-right wbb-mt10" data-action="quickFollow" data-loading-text="关注中...">
                                        <i class="om-gplus"></i>
                                        关注
                                    </button>
                                    <div class="pull-left clearfix">
                                        <div class="pull-left wbb-pr10">
                                            <a href="#" class="wb-pub-opic">
                                                <img src="<?php echo $assetUrl; ?>/image/defaultAva.gif" alt="" />
                                            </a>
                                        </div>
                                        <div class="pull-right wbb-pt10 wb-nf-info" >
                                            <strong>por</strong>
                                            <p>博思协创 打酱油份子</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="wb-nf-notes">
                                    你们同在博思协创部门
                                    <div class="wb-nf-arr"><i></i></div>
                                </div>
                            </li>
                            <li class="clearfix" data-node-type="quickFollowBox">
                                <div class="clearfix">
                                    <button type="button" class="btn btn-small pull-right wbb-mt10" data-action="quickFollow" data-loading-text="关注中...">
                                        <i class="om-gplus"></i>
                                        关注
                                    </button>
                                    <div class="pull-left clearfix">
                                        <div class="pull-left wbb-pr10">
                                            <a href="#" class="wb-pub-opic">
                                                <img src="<?php echo $assetUrl; ?>/image/test/userAva1.jpg" alt="" />
                                            </a>
                                        </div>
                                        <div class="pull-right wbb-pt10 wb-nf-info" >
                                            <strong>por</strong>
                                            <p>博思协创 打酱油份子</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="wb-nf-none">
                                    Weiss还没打过招呼，
                                    <a href="#">邀请她来玩微博吧~</a>
                                    <div class="wb-nf-arr"><i></i></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>-->
                <!-- @Todo: 话题, 后期完善 -->		
                <!--<div class="mpanel wbb-mt20">
                    <div class="wbc-box rdt bdbs">
                        <i class="o-wbr-topic"></i>
                        <strong class="wbc-tit">话题</strong>
                    </div>
                    <div class="wb-mod-tpc rdb">
                        <div class="wb-modt-pic">
                            <img src="<?php echo $assetUrl; ?>/image/test/wb-mod-tpcpic.jpg" alt="" />
                            <div class="wb-modt-num">
                                <a href="#">
                                    <i class="wbi-tpc-wmes"></i>
                                    123
                                </a>
                            </div>
                            <div class="wb-modt-ava">
                                <div class="wb-modt-avabg">
                                    <a href="#" class="avatar-circle">
                                        <img src="<?php echo $assetUrl; ?>/image/defaultAva.gif" alt=""></a>
                                    <a href="#" class="wb-modt-tit">#三星手机字库门#</a>
                                </div>
                            </div>
                        </div>

                        <div class="wbc-box2 rdb">
                            <div class="wbc-modt-view">
                                <p>
                                    三星Note和S系列手机存在设计缺陷，“猝死”问题早已蔓延全球，售后服务却存在双重标准。英国保修期免费维修或换新机；
                                    <a href="topic.html" class="xcbu">参与讨论</a>
                                </p>
                            </div>
                            <div class="wbb-pt10">
                                <ul class="wb-mytopic-list">
                                    <li>
                                        <a href="topic.html">
                                            <span>8</span>
                                            #IBOS#设计创新讨论
                                            <i class="o-wbi-mic"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="topic.html">
                                            <span>99</span>
                                            #带着微博去旅行#
                                            <i class="o-wbi-mic"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="topic.html">
                                            <span>43</span>
                                            #2013年会#
                                        </a>
                                    </li>
                                    <li>
                                        <a href="topic.html">
                                            <span>121</span>
                                            #IBOS2.0发布#
                                        </a>
                                    </li>
                                    <li>
                                        <a href="topic.html">
                                            <span>5523</span>
                                            #测试什么的最讨厌了#
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="wb-modt-oper wbb-pt10 clearfix">
                                <a class="pull-left fss" href="?r=weibo/topic/mytopic">
                                    <i class="o-user"></i>
                                    我参与的话题（6）
                                </a>
                                <a class="pull-right fss" href="?r=weibo/topic/ranking">
                                    <i class="o-wbi-more"></i>
                                    更多热门话题
                                </a>
                            </div>
                        </div>
                    </div>
                </div>-->

                <!-- 活跃成员 -->
                <?php if ( !empty( $activeUser ) ): ?>
                    <div class="mpanel wbb-mt20">
                        <div class="wbc-box rdt bdbs wbb-mt20">
                            <i class="o-wbr-user"></i>
                            <span class="wbc-tit"><?php echo $lang['Active members']; ?></span>
                        </div>
                        <div class="wb-tpc-joinbd">
                            <table class="table table-striped table-hover table-condensed ">
                                <tbody>
                                    <?php foreach ( $activeUser as $k => $au ): ?>
                                        <?php if ( $k == 0 ): ?>
                                            <tr>
                                                <td>
                                                    <a data-toggle="usercard" data-param="uid=<?php echo $au['uid']; ?>" href="<?php echo $au['user']['space_url']; ?>" class="wb-pub-opic">
                                                        <img src="<?php echo $au['user']['avatar_middle']; ?>" alt="<?php echo $au['user']['realname']; ?>">
                                                        <span class="top-flag">1</span>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a target="_blank" href="<?php echo $au['user']['space_url']; ?>"><strong><?php echo $au['user']['realname']; ?></strong></a>
                                                    <p class="tcm"><?php echo $au['user']['posname']; ?></p>
                                                </td>
                                                <td class="xco xar xwb"><?php echo $au['value']; ?></td>
                                            </tr>
                                        <?php else: ?>
                                            <tr>
                                                <td>
                                                    <em class="xcr"><?php echo $k + 1; ?></em>
                                                    <a data-toggle="usercard" data-param="uid=<?php echo $au['uid']; ?>" href="<?php echo $au['user']['space_url']; ?>" class="avatar-circle avatar-circle-small">
                                                        <img src="<?php echo $au['user']['avatar_small']; ?>" alt="<?php echo $au['user']['realname']; ?>">
                                                    </a>
                                                </td>
                                                <td>
                                                    <a target="_blank" href="<?php echo $au['user']['space_url']; ?>"><?php echo $au['user']['realname']; ?></a>
                                                </td>
                                                <td class="xar xwb"><?php echo $au['value']; ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div><!-- end sidebar-->
        </div>
    </div>
</div>

<!-- 赞过的人 -->
<div class="wb-digg-box popover fade bottom in" id="menu_digg_box" data-node-type="feedDiggBox" style="display: none; ">
    <div class="arrow"></div>
    <div class="popover-content" data-node-type="feedDiggContent">
        <ul class="list-inline" data-node-type="feedDiggList">
        </ul>
    </div>
</div>

<!-- 图片上传后显示内容 -->
<script type="text/template" id="showPreview_tpl">
    <div class="wb-upload-preview">
    <img src="<%= attachUrl %>" title="<%= attachName %>" alt="<%= attachName %>">
    <div class="wb-reupload-modal"></div>
    <div class="wb-upload-pic"><i class="pic-holder active"></i> <p>重新上传</p></div>
    <div class="wb-reupload-bar"> 
    <div class="wb-reupload-bar-bg rdb"></div> 
    <span>"<%= attachName %>"</span> 
    <a href="javascript:;" data-action="removePic" class="cbtn o-trash"></a> 
    </div>
    </div>
</script>
<script>
    var params = {
        wbnums: '<?php echo IBOS::app()->setting->get( 'setting/wbnums' ); ?>',
        firstId: '<?php echo $firstId; ?>',
        loadId: '<?php echo $lastId; ?>',
        maxId: '<?php echo $firstId; ?>',
        loadmore: '<?php echo $loadMore; ?>',
        loadnew: '<?php echo $loadNew; ?>',
        type: '<?php echo $type; ?>',
        feedtype: '<?php echo $feedtype; ?>',
        feedkey: '<?php echo $feedkey; ?>',
        inHome: 1,
        submitInterval: <?php echo intval( IBOS::app()->setting->get( 'setting/wbpostfrequency' ) ) * 1000; ?>
    };
    Ibos.app.setPageParam(params);
</script>
<script src="<?php echo STATICURL; ?>/js/lib/atwho/jquery.atwho.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/lightbox/js/lightbox.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/app/ibos.charCount.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/src/emotion.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/weibo.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/weibo_home_index.js?<?php echo VERHASH; ?>"></script>