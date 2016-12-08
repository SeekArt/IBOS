<?php

use application\core\utils\Ibos;
use application\core\utils\Module;
use application\core\utils\StringUtil;

?>
<!--微博个人资料卡 S-->
<div class="uic-banner rdt">
    <img src="<?php echo $user['bg_small']; ?>" alt="<?php echo $user['realname']; ?>"/>
    <div class="uic-lv">
        <i class="lv lv<?php echo $user['level']; ?>"></i>
        <strong><?php echo $user['group_title']; ?></strong>
    </div>
    <div class="uic-operate">
        <?php if (Ibos::app()->user->uid !== $user['uid']): ?>
            <a href="javascript:Ibos.showCallingDialog(<?php echo $user['uid']; ?>);void(0);" title="打电话"
               class="co-tcall"></a>
        <?php endif; ?>
        <?php if (Module::getIsEnabled('email')): ?><a target="_blank"
                                                       href="<?php echo Ibos::app()->createUrl('email/content/add', array('toid' => $user['uid'])); ?>"
                                                       title="<?php echo $lang['Send email']; ?>"
                                                       class="co-temail"></a><?php endif; ?>
        <?php if (Ibos::app()->user->uid !== $user['uid']): ?>
            <a title="<?php echo $lang['Send message']; ?>"
               href="javascript:Ibos.showPmDialog('<?php echo StringUtil::wrapId($user['uid'], 'u'); ?>');void(0);"
               class="co-tpm">
                <i class="<?php echo $status; ?>"></i>
            </a>
        <?php endif; ?>
    </div>
    <div class="uic-usi-name">
        <?php if ($user['gender'] == '1'): ?>
            <i class="om-male"></i>
        <?php else: ?>
            <i class="om-female"></i>
        <?php endif; ?>
        <strong><?php echo $user['realname']; ?></strong>
        &nbsp;&nbsp;
        <small><?php echo trim($user['deptname'] . ':' . $user['posname'], ':'); ?></small>
    </div>
</div>
<div class="uic-usi rdb">
    <div class="uic-ava">
        <a href="<?php echo $user['space_url']; ?>" class="avatar-circle">
            <img src="<?php echo $user['avatar_big']; ?>" alt="<?php echo $user['realname']; ?>">
        </a>
        <?php if ($weibo && Ibos::app()->user->uid !== $user['uid']): ?>
            <div class="uic-btn">
                <?php if (!$states['following']): ?>
                    <a href="javascript:;" class="btn btn-small btn-warning" data-action="follow"
                       data-param='{"fid": <?php echo $user['uid']; ?>}' data-loading-text="关注中...">
                        <i class="om-plus"></i>
                        <?php echo $lang['Focus']; ?> <!--关注-->
                    </a>
                <?php elseif ($states['following'] && $states['follower']): ?>
                    <a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn" data-action="unfollow"
                       data-param='{"fid": <?php echo $user['uid']; ?>}' data-loading-text="取消中...">
                        <i class="om-geoc"></i>
                        <?php echo $lang['Focus on each other']; ?> <!--互相关注-->
                    </a>
                <?php elseif ($states['following']): ?>
                    <a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn" data-action="unfollow"
                       data-param='{"fid": <?php echo $user['uid']; ?>}' data-loading-text="取消中...">
                        <i class="om-gcheck"></i>
                        <?php echo $lang['Has been focused']; ?> <!-- 已关注 -->
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($weibo): ?>
        <div class="uic-fans">
            <ul>
                <li>
                    <a target="_blank"
                       href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/following', array('uid' => $user['uid'])); ?>"><?php echo $lang['Focus']; ?></a>
                    <span><?php echo isset($userData['following_count']) ? $userData['following_count'] : 0; ?></span>
                </li>
                <li>|</li>
                <li>
                    <a target="_blank"
                       href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/follower', array('uid' => $user['uid'])); ?>"><?php echo $lang['Fans']; ?></a>
                    <span><?php echo isset($userData['follower_count']) ? $userData['follower_count'] : 0; ?></span>
                </li>
                <li>|</li>
                <li>
                    <a target="_blank"
                       href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/index', array('uid' => $user['uid'])); ?>"><?php echo $lang['Weibo']; ?></a>
                    <span><?php echo isset($userData['weibo_count']) ? $userData['weibo_count'] : 0; ?></span>
                </li>
            </ul>
        </div>
    <?php endif; ?>
    <div class="uic-info">
        <ul>
            <li><?php echo $lang['Email']; ?>：<?php echo $user['email']; ?></li>
            <li><?php echo $lang['Cell phone']; ?>：<?php echo $user['mobile']; ?></li>
            <li><?php echo $lang['Birth day']; ?>
                ：<?php echo $user['birthday'] ? date('n月j日', $user['birthday']) : '未填写'; ?></li>
        </ul>
    </div>
</div>