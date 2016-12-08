<?php

use application\core\utils\Ibos;

?>
<link href="<?php echo $assetUrl; ?>/css/initialize_guide.css" rel="stylesheet" type="text/css">
<link href="<?php echo $assetUrl; ?>/css/animate.css" rel="stylesheet" type="text/css">
<div id='initialize_guide'>
    <div class="main">
        <form id="ins_form">
            <div class="main-content">
                <div class="set-password posr mark show">
                    <div class="sp-content-top">
                        <div class="top-bg"></div>
                        <div class="top-area">
                            <div class="tip-info clearfix mb">
                                <div class="pull-left">
                                    <?php echo $lang['Welcome tip']; ?>
                                </div>
                                <div class="pull-right">
                                    <i class="o-step-first"></i>
                                    <i class="o-step-tip mlm"></i>
                                    <i class="o-step-tip mlm"></i>
                                </div>
                            </div>
                            <div class="degree-of-complete">
                                <div class="mbs">
                                    <span id="percent_nub_one" class="percent-nub">0</span>
                                    <span>%，</span>
                                    <span><?php echo $lang['Set new password']; ?></span>
                                    <span class="badge">+20%</span>
                                </div>
                                <div class="progress progress-striped">
                                    <div class="progress-bar" id="progress_one" role="progressbar" aria-valuenow="40"
                                         aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sp-content-body animated flipInY">
                        <div class="middle-area">
                            <div class="area-top posr">
                                <i class="o-tip-lock" id="tip_lock"></i>
                                <i class="o-success-lock" id="success_lock"></i>
                            </div>
                            <div class="area-content">
                                <p class="mb tcm fss xac"><?php echo $lang['Inter password tip']; ?></p>
                                <input type="text" class="mb password" id="raw_password" name="originalpass"
                                       placeholder="<?php echo $lang['Original password']; ?>" tabindex="1" nofocus>
                                <input type="password" class="mb password" id="new_password" name="newpass"
                                       placeholder="<?php echo $lang['New password']; ?>" tabindex="2" nofocus>
                                <input type="password" class="password" id="sure_password" name="newpass_confirm"
                                       placeholder="<?php echo $lang['Confirm password']; ?>" tabindex="3" nofocus>
                            </div>
                        </div>
                    </div>
                    <a class="dib next-write-step closs-init" href="javascript:;" id="never_write_again" tabindex="5">
                        <div class="posr">
                            <div class="ps-bg"></div>
                            <div class="previous-step-content">
                                <i class="o-next-write"></i>
                                <span><?php echo $lang['Never reloading']; ?></span>
                            </div>
                        </div>
                    </a>
                    <a class="dib next-step" href="javascript:;" id="next_step_one" tabindex="4">
                        <div class="posr">
                            <div class="ns-bg"></div>
                            <div class="next-step-content">
                                <i class="o-next-step"></i>
                                <span><?php echo $lang['Next step']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="write-information posr mark hidden">
                    <div class="sp-content-top">
                        <div class="top-bg"></div>
                        <div class="top-area">
                            <div class="tip-info clearfix mb">
                                <div class="pull-left">
                                    <?php echo $lang['Profile tip']; ?>
                                </div>
                                <div class="pull-right">
                                    <i class="o-step-tip"></i>
                                    <i class="o-step-second mlm"></i>
                                    <i class="o-step-tip mlm"></i>
                                </div>
                            </div>
                            <div class="degree-of-complete">
                                <div class="mbs">
                                    <span id="percent_nub_two" class="percent-nub">0</span>
                                    <span>%，</span>
                                    <span class="percent-tip"
                                          id="tip_step"><?php echo $lang['Upload true picture']; ?></span>
                                    <span class="badge" id="add_nub">+30%</span>
                                </div>
                                <div class="progress progress-striped">
                                    <div class="progress-bar" role="progressbar" id="progress_two" aria-valuenow="40"
                                         aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                    </div>
                                </div>
                                <input type="hidden" id="data_progress" data-progress="0">
                            </div>
                        </div>
                    </div>
                    <div class="portrait-tip" id="portrait_tip">
                        <div class="posr">
                            <i class="o-portrait-tip"></i>
                            <i class="o-tip-line"></i>
                        </div>
                    </div>
                    <div class="sp-content-body animated flipInY">
                        <div class="middle-area">
                            <div class="wi-area-top posr">
                                <div class="card-rope">
                                    <i class="o-rope"></i>
                                </div>
                                <label class="portrait-block" id="portrait_block">
                                    <div class="upload-trigger">
                                        <span id="upload_img" class="upload-img"></span>
                                    </div>
                                    <input type="hidden" id='img_src' name='src'/>
                                    <input type="hidden" id="uid" name="uid"
                                           value="<?php echo Ibos::app()->user->uid; ?>"/>
                                    <div class="img-upload-imgwrap" id="portrait_img_wrap">
                                        <img class="portrait-img" id="portrait_img"
                                             src="<?php echo $assetUrl; ?>/image/bg.jpg">
                                    </div>
                                    <div class="tip-tier" id="tip_tier">
                                        <div class="tip-bg"></div>
                                        <div class="tip-content"><?php echo $lang['Upload again']; ?></div>
                                    </div>
                                </label>
                                <i class="o-portrait"></i>
                            </div>
                            <div class="area-content">
                                <p class="mb xac" id="realname"></p>
                                <input type="text" class="mb" id="mobile" name="mobile"
                                       placeholder="<?php echo $lang['Mobile']; ?>" nofocus>
                                <input type="text" class="mb" id="email" name="email"
                                       placeholder="<?php echo $lang['Email']; ?>" nofocus>
                                <div class="datepicker" id="date_time" style="position:relative; z-index:99999;">
                                    <a href="javascript:;" class="datepicker-btn"></a>
                                    <input type="text" class="datepicker-input" id="birthday"
                                           placeholder="<?php echo $lang['Birthday']; ?>" nofocus>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a class="dib previous-step" href="javascript:;">
                        <div class="posr">
                            <div class="ps-bg"></div>
                            <div class="previous-step-content">
                                <i class="o-previous-step"></i>
                                <span><?php echo $lang['Pre step']; ?></span>
                            </div>
                        </div>
                    </a>
                    <a class="dib next-step" href="javascript:;" id="next_step_two">
                        <div class="posr">
                            <div class="ns-bg"></div>
                            <div class="next-step-content">
                                <i class="o-next-step"></i>
                                <span><?php echo $lang['Next step']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="settings-finish posr mark hidden">
                    <div class="sf-content-top">
                        <div class="top-bg"></div>
                        <div class="top-area">
                            <div class="tip-info clearfix mb">
                                <div class="pull-left">
                                    <?php echo $lang['Init complete tip']; ?>
                                </div>
                                <div class="pull-right">
                                    <i class="o-step-tip"></i>
                                    <i class="o-step-tip mlm"></i>
                                    <i class="o-step-third mlm"></i>
                                </div>
                            </div>
                            <div class="degree-of-complete">
                                <div class="mbs">
                                    <?php echo $lang['Init not complete tip']; ?>
                                </div>
                                <div class="progress progress-striped">
                                    <div class="progress-bar" role="progressbar" id="progress_three" aria-valuenow="40"
                                         aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sf-content-body animated flipInY">
                        <div class="middle-area">
                            <div class="area-top posr">
                                <i class="o-settings-finish"></i>
                            </div>
                            <div class="sf-area-content">
                                <div class="mbs posr">
                                    <span class="sf-divider"></span>
                                    <span class="step-tips"><?php echo $lang['Now you can']; ?></span>
                                </div>
                                <a href="<?php echo Ibos::app()->urlManager->createUrl('user/home/personal'); ?>"
                                   class="perfect-data fss">
                                    <i class="o-data"></i>
                                    <p><?php echo $lang['Continue to improve data']; ?></p>
                                </a>
                                <a href="<?php echo Ibos::app()->urlManager->createUrl('weibo/home/index'); ?>"
                                   class="greet-others fss" id="greet_others">
                                    <i class="o-smile"></i>
                                    <p><?php echo $lang['And say hello']; ?></p>
                                </a>
                                <a href="<?php echo Ibos::app()->urlManager->createUrl('user/home/index'); ?>"
                                   class="go-myhome fss" id="go_myhome">
                                    <i class="o-home"></i>
                                    <p><?php echo $lang['Into my home page']; ?></p>
                                </a>
                                <a href="javascript:;" class="look-mywork fss closs-init">
                                    <i class="o-work"></i>
                                    <p><?php echo $lang['Look at my work']; ?></p>
                                </a>
                                <a href="javascript:;" class="btn btn-warning btn-experience closs-init">
                                    <i class="o-next-step"></i>
                                    <span><?php echo $lang['Experience the new office']; ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <a class="dib previous-step" href="javascript:;">
                        <div class="posr">
                            <div class="ps-bg"></div>
                            <div class="previous-step-content">
                                <i class="o-previous-step"></i>
                                <span><?php echo $lang['Pre step']; ?></span>
                            </div>
                        </div>
                    </a>
                    <a class="dib finish-step closs-init" href="javascript:;">
                        <div class="posr">
                            <div class="ns-bg"></div>
                            <div class="next-step-content">
                                <i class="o-has-finish"></i>
                                <span><?php echo $lang['Completed']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="ava_progress" class="hide"></div>
<script>
    Ibos.app.s({
        avatarUploadUrl: "<?php echo Ibos::app()->urlManager->createUrl('user/info/uploadAvatar', array('uid' => $uid, 'hash' => $swfConfig['hash'])); ?>",
        passwordMinLength: '<?php echo $account['minlength']; ?>',
        passwordMaxLength: 32,
        passwordMixed: "<?php echo $account['mixed']; ?>",
        passwordRegex: "<?php echo $preg ?>"
    });
</script>
