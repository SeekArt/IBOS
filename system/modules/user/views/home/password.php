<?php

use application\core\utils\Ibos;

?>
<div class="mc mcf clearfix">
    <?php echo $this->getHeader($lang); ?>
    <div>
        <div>
            <ul class="nav nav-tabs nav-tabs-large nav-justified nav-special">
                <li>
                    <a href="<?php echo $this->createUrl('home/index', array('uid' => $this->getUid())); ?>"><?php echo $lang['Home page']; ?></a>
                </li>
                <?php if ($this->getIsWeiboEnabled()): ?>
                    <li><a
                        href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/index', array('uid' => $this->getUid())); ?>"><?php echo $lang['Weibo']; ?></a>
                    </li><?php endif; ?>
                <?php if ($this->getIsMe()): ?>
                    <li>
                        <a href="<?php echo $this->createUrl('home/credit', array('uid' => $this->getUid())); ?>"><?php echo $lang['Credit']; ?></a>
                    </li>
                <?php endif; ?>
                <li class="active"><a
                        href="<?php echo $this->createUrl('home/personal', array('uid' => $this->getUid())); ?>"><?php echo $lang['Profile']; ?></a>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="pc-header clearfix">
    <ul class="nav nav-skid">
        <li>
            <a href="<?php echo $this->createUrl('home/personal', array('op' => 'profile', 'uid' => $this->getUid())); ?>">
                <?php echo $lang['My profile']; ?>
            </a>
        </li>
        <?php if ($this->getIsMe()): ?>
            <li>
                <a href="<?php echo $this->createUrl('home/personal', array('op' => 'avatar', 'uid' => $this->getUid())); ?>"><?php echo $lang['Upload avatar']; ?></a>
            </li>
            <li class="active">
                <a href="<?php echo $this->createUrl('home/personal', array('op' => 'password', 'uid' => $this->getUid())); ?>"><?php echo $lang['Change password']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('home/personal', array('op' => 'remind', 'uid' => $this->getUid())); ?>"><?php echo $lang['Remind setup']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('home/personal', array('op' => 'history', 'uid' => $this->getUid())); ?>"><?php echo $lang['Login history']; ?></a>
            </li>
        <?php endif; ?>
    </ul>
</div>
<div>
    <div class="pc-container clearfix dib left-sidebar">
        <div>
            <form action="<?php echo $this->createUrl('home/personal'); ?>" method="post"
                  class="form-horizontal form-narrow" id="password_form">
                <div class="data-title mb">
                    <i class="o-change-password"></i><span
                        class="fsl vam"><?php echo $lang['Change password']; ?></span>
                </div>
                <?php $host = $_SERVER['HTTP_HOST']; ?>
                <?php if ($host == 'demo.ibos.com.cn' && in_array($this->getUid(), array(2, 3, 4))) : ?>
                    <div class="control-group">
                        【<?php echo Ibos::app()->user->realname ?>】为测试的演示账号，不允许修改密码
                    </div>
                    <div class="control-group">
                        如需使用本功能，请联系IBOS官网客服获取正式的测试账号
                    </div>
                <?php else : ?>
                    <div class="control-group">
                        <label class="control-label">
                            <?php echo $lang['Original password']; ?><span class="xcr">*</span>
                        </label>
                        <div class="controls">
                            <input type="text" name="originalpass" class="span8" id="raw_password"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">
                            <?php echo $lang['New password']; ?></label>
                        <div class="controls">
                            <input type="password" name="newpass" class="span8" id="new_password"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">
                            <?php echo $lang['Confirm new password']; ?></label>
                        <div class="controls">
                            <input type="password" name="newpass_confirm" class="span8" id="sure_password"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
                            <input type="hidden" name="op" value="password"/>
                            <input type="hidden" name="uid" value="<?php echo $this->getUid(); ?>"/>
                            <input type="submit" name="userSubmit" value="<?php echo $lang['Save']; ?>"
                                   class="btn btn-primary btn-large btn-great"/>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <?php $this->widget('application\modules\user\components\UserProfileTracker', array('user' => $user)) ?>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/user.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/user_home_password.js?<?php echo VERHASH; ?>'></script>
</script>