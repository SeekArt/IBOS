<?php

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\user\model\BgTemplate;
?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/user.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/avatar.css?<?php echo VERHASH; ?>">
<div class="pc-banner">
    <img src="<?php echo Ibos::app()->user->bg_big ?>" />
    <?php if ( $this->getIsMe() ): ?><a href="javascript:;" id="skin_choose" title="<?php echo $lang['Custom banner']; ?>"></a><?php endif; ?>
</div>
<div class="pc-usi">
    <div class="pc-usi-bg"></div>
    <?php if ( $this->getIsMe() ): ?>
        <a href="<?php echo Ibos::app()->createUrl( 'user/home/personal', array( 'op' => 'avatar', 'uid' => $user['uid'] ) ); ?>" class="pc-usi-avatar posr">
            <img src="<?php echo Ibos::app()->user->avatar_big ?>" alt="<?php echo $user['realname']; ?>" width="180" height="180" />
            <div class="pc-img-shade">
                <div class="pc-bg"></div>
                <div class="pc-upload-tip"><?php echo $lang['Edit avatar']; ?></div>
            </div>
        </a>
    <?php else: ?>
        <span class="pc-usi-avatar posr">
            <img src="<?php echo $user['avatar_big']; ?>" alt="<?php echo $user['realname']; ?>" width="180" height="180" />
        </span>
    <?php endif; ?>
    <?php if ( Ibos::app()->user->uid !== $user['uid'] ): ?>
        <a href="javascript:Ibos.showPmDialog('<?php echo StringUtil::wrapId( $user['uid'], 'u' ); ?>');void(0);" class="private-letter" title="<?php echo $lang['Send message']; ?>">
            <i class="o-private-letter <?php echo $onlineIcon; ?>"></i>
        </a>
    <?php endif; ?>
    <div class="pc-usi-name">
        <?php if ( $user['gender'] == '1' ): ?>
            <i class="om-male"></i>
        <?php else: ?>
            <i class="om-female"></i>
        <?php endif; ?>
        <strong><?php echo $user['realname']; ?></strong>
        <?php if ( !empty( $user['deptname'] ) ): ?><span><?php echo $user['deptname']; ?></span><?php endif; ?>
        <?php if ( !empty( $user['posname'] ) ): ?><span><?php echo $user['posname']; ?></span><?php endif; ?>
    </div>
    <div class="pc-usi-sign clearfix">
        <div class="pull-left">
            <?php if ( $this->getIsMe() ): ?>
                <a href="<?php echo Ibos::app()->createUrl( 'user/home/personal', array( 'uid' => $user['uid'] ) ); ?>" class="btn btn-small"><?php echo $lang['Edit profile']; ?></a>
            <?php else: ?>
                <!-- 关注的几种状态 -->
                <?php if ( !$states['following'] ): ?>
                    <a href="javascript:;" class="btn btn-small btn-warning" data-action="follow" data-param='{"fid": <?php echo $user['uid']; ?>}' data-loading-text="关注中...">
                        <i class="om-plus"></i>
                        <?php echo $lang['Focus']; ?> <!--关注-->
                    </a>
                <?php elseif ( $states['following'] && $states['follower'] ): ?>
                    <a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn" data-action="unfollow" data-param='{"fid": <?php echo $user['uid']; ?>}' data-loading-text="取消中...">
                        <i class="om-geoc"></i>
                        <?php echo $lang['Focus on each other']; ?> <!--互相关注-->
                    </a>
                <?php elseif ( $states['following'] ): ?>
                    <a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn" data-action="unfollow" data-param='{"fid": <?php echo $user['uid']; ?>}' data-loading-text="取消中...">
                        <i class="om-gcheck"></i>
                        <?php echo $lang['Has been focused']; ?> <!-- 已关注 -->
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <span><?php echo $user['bio'] ?></span>
        </div>
        <!-- 个人信息关注，粉丝，微博信息 -->
        <div class="pull-right">
            <ul class="list-inline pc-info-list">
                <li class="ml">
                    <a href="<?php echo Ibos::app()->urlManager->createUrl( 'weibo/personal/following', array( 'uid' => $user['uid'] ) ); ?>">
                        <strong class="xcbu fsl"><?php echo isset( $userData['following_count'] ) ? $userData['following_count'] : 0; ?></strong>
                        <p><?php echo $lang['Focus']; ?></p>
                    </a>
                </li>
                <li class="ml">
                    <a href="<?php echo Ibos::app()->urlManager->createUrl( 'weibo/personal/follower', array( 'uid' => $user['uid'] ) ); ?>">
                        <strong class="xcbu fsl"><?php echo isset( $userData['follower_count'] ) ? $userData['follower_count'] : 0; ?></strong>
                        <p><?php echo $lang['Fans']; ?></p>
                    </a>
                </li>
                <li class="ml">
                    <a href="<?php echo Ibos::app()->urlManager->createUrl( 'weibo/personal/index', array( 'uid' => $user['uid'] ) ); ?>">
                        <strong class="xcbu fsl"><?php echo isset( $userData['weibo_count'] ) ? $userData['weibo_count'] : 0; ?></strong>
                        <p><?php echo $lang['Weibo']; ?></p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- 选择皮肤弹出框内容 -->
<div class="skin-bg" id="skin_bg" style="display:none;">
    <ul class="nav nav-skid skin-nav-skid">
        <li class="active">
            <a href="#skin_select" data-toggle="tab">选择模板</a>
        </li>
        <li>
            <a href="#skin_custom" data-toggle="tab">自定义</a>
        </li>
    </ul>
    <div class="skin-type-choose tab-content">
        <div id="skin_select" class="bg-choose mark tab-pane active">
            <div class="template-bg">
                <ul class="list-inline choose-list" id="choose_list"></ul>
            </div>
            <div class="model-page-choose" id="model_page_choose">
                <div id="perv_next" class="pager btn-group">
                    <button type="button" class="btn btn-small" id="pre_bg_page">
                        <i class="glyphicon-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-small"  id="next_bg_page">
                        <i class="glyphicon-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="sk-divider mbs"></div>

            <div class="clearfix">
                <?php if ( Ibos::app()->user->uid == 1 ): ?>
                    <div class="pull-left delete-module">
                        <a href="javascript:;" class="sk-delete-btn" id="sk_delete_btn">
                            <i class="o-trash"></i>
                            <span class="dib mlm">删除模板</span>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="pull-right">
                    <button type="button" class="btn" id="module_close">取消</button>
                    <button type="button" class="btn btn-primary mlm" id="module_save">保存</button>
                </div>
            </div>
        </div>
        <div id="skin_custom" class="bg-choose mark tab-pane model-skin">
            <div class="user-defined-bg mark">
                <form action="<?php echo Ibos::app()->urlManager->createUrl( 'user/skin/cropBg' ); ?>" method="post" id="skin" class="update-pic cf">
                    <div class="skin-choose-area mb active" id="skin_choose_area">
                        <input type="file" id="skin_bg_choose">
                        <input type="hidden" id="sk_x" name="x" />
                        <input type="hidden" id="sk_y" name="y" />
                        <input type="hidden" id="sk_w" name="w" />
                        <input type="hidden" id="sk_h" name="h" />
                        <input type="hidden" id='sk_img_src' name='src' />
                        <input type="hidden" name="formhash" value='<?php echo FORMHASH; ?>' />
                        <input type="hidden" name="uid" value="<?php echo Ibos::app()->user->uid; ?>" />
                        <input type="hidden" name="bgSubmit" value="1" />
                        <div class="file-tips">
                            <div class="mb xac">
                                <i class="o-plus"></i>
                                <span class="upload-text-tip">上传图片</span>
                            </div>
                            <div class="tcm upload-tip">
                                <p>支持jpg、gif、png图片文件，且文件小于2MB，</p>
                                <p>尺寸不小于1000x300。</p>
                            </div>
                        </div>
                        <div class="preview" id="preview_hidden" style="width: 9999px; height: 9999px;"></div>
                    </div>
                    <div class="sk-divider"></div>
                    <div class="clearfix">
                        <div class="pull-left upload-btn">
                            <a href="javascript:;" class="skin-reupload-img" id="skin_reupload_img" style="display:none;">
                                <i class="o-upload-btn"></i>
                                <span>重新上传</span>
                            </a>
                        </div>
                        <div class="pull-right">
                            <?php if ( Ibos::app()->user->isadministrator == 1 ): ?>
                                <label class="checkbox dib sk-setting-model">
                                    <input type="checkbox" name="commonSet" value="同时设为公用模板" id="sk_setting_model" />同时设为公用模板
                                </label>
                            <?php endif; ?>
                            <a class="btn" href="javascript:;" id="custom_close">取消</a>
                            <a class="btn btn-primary mlm save-skin" id="save_skin" href="javascript:;">保存</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="ava_progress" class="hide"></div>
<!-- 皮肤背景模板 -->
<script type="text/ibos-template" id="skin_template">
    <li data-id="<%=id%>">
    <a class="model-img posr" href="javascript:;">
    <img src="<%=imgUrl%>">
    <i class="o-select-tip"></i>
    </a>
    </li>
</script>
<script>
    Ibos.app.setPageParam({
        allBg: <?php echo CJSON::encode( BgTemplate::model()->fetchAllBg() ); ?>
    });
</script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/user_skin_selector.js?<?php echo VERHASH; ?>"></script>
