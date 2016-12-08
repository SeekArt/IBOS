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
            <a href="<?php echo $this->createUrl('home/personal', array('op' => 'profile', 'uid' => $this->getUid())); ?>"><?php echo $lang['My profile']; ?></a>
        </li>
        <?php if ($this->getIsMe()): ?>
            <li class="active">
                <a href="<?php echo $this->createUrl('home/personal', array('op' => 'avatar', 'uid' => $this->getUid())); ?>"><?php echo $lang['Upload avatar']; ?></a>
            </li>
            <li>
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
        <div class="data-title">
            <i class="o-upload"></i><span class="fsl vam"><?php echo $lang['Upload avatar']; ?></span>
        </div>
        <div class="fill-nn clearfix">
            <form action="<?php echo $this->createUrl('info/cropimg'); ?>" method="post" id="pic" class="update-pic cf">
                <div class="mb">
                    <div class="upload-area" id="upload_area">
                        <span id="user_pic"></span>
                        <div class="file-tips">
                            <div class="tcm avater-upload-tip">
                                <div class="mb xac">
                                    <i class="o-big-upload-tip"></i>
                                    <p class="upload-text-tip">上传头像</p>
                                </div>
                                <?php echo $lang['Avatar tip']; ?>
                            </div>
                        </div>
                        <div class="preview" id="preview-hidden"></div>
                    </div>
                    <div class="preview-area">
                        <input type="hidden" id="x" name="x"/>
                        <input type="hidden" id="y" name="y"/>
                        <input type="hidden" id="w" name="w"/>
                        <input type="hidden" id="h" name="h"/>
                        <input type="hidden" id='img_src' name='src'/>
                        <input type="hidden" name="formhash" value='<?php echo FORMHASH; ?>'/>
                        <input type="hidden" name="userSubmit" value="1"/>
                        <input type="hidden" name="op" value="<?php echo $op; ?>"/>
                        <input type="hidden" name="uid" value="<?php echo $this->getUid(); ?>"/>
                        <div class="tcrop">
                            <?php echo $lang['Avatar review']; ?></div>
                        <div class="crop crop180">
                            <img id="crop-preview-180" src="<?php echo Ibos::app()->user->avatar_big ?>"/>
                        </div>
                        <p class="tcm fss size-tip">180*180<?php echo $lang['Pixel']; ?></p>
                        <div class="crop crop60">
                            <img id="crop-preview-60" src="<?php echo Ibos::app()->user->avatar_middle ?>"/>
                        </div>
                        <p class="tcm fss size-tip">60*60<?php echo $lang['Pixel']; ?></p>
                        <div class="crop crop30">
                            <img id="crop-preview-30" src="<?php echo Ibos::app()->user->avatar_small ?>"/>
                        </div>
                        <p class="tcm fss size-tip">30*30<?php echo $lang['Pixel']; ?></p>
                    </div>
                </div>
                <div class="clearfix upload-btnbar" id="upload_btnbar">
                    <a class="save-pic btn btn-primary btn-large pull-right"
                       href="javascript:;"><?php echo $lang['Save']; ?></a>
                    <a class="reupload-img btn btn-large pull-left"
                       href="javascript:$('#upload_btnbar').css('display','none'); void(0);"><?php echo $lang['Reupload']; ?></a>
                </div>
            </form>
        </div>
    </div>
    <!-- 右栏 完善情况 -->
    <?php $this->widget('application\modules\user\components\UserProfileTracker', array('user' => $user)) ?>
</div>
<div id="ava_progress" class="hide"></div>
<script src='<?php echo $assetUrl; ?>/js/user.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/user_home_avatar.js?<?php echo VERHASH; ?>'></script>
