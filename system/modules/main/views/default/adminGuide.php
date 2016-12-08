<link href="<?php echo $assetUrl; ?>/css/animate.css" rel="stylesheet" type="text/css">
<link href="<?php echo $assetUrl; ?>/css/initialize_guide.css" rel="stylesheet" type="text/css">
<div id='initialize_guide'>
    <div class="main">
        <form id="ad_form">
            <div class="main-content">
                <!-- 公司信息 -->
                <div class="as-initialize posr show mark">
                    <div class="content-top">
                        <div class="top-bg"></div>
                        <div class="top-area">
                            <div class="tip-info clearfix mb">
                                <div class="pull-left">
                                    <?php echo $lang['Welcome init tip']; ?>
                                </div>
                                <div class="pull-right">
                                    <i class="o-step-first"></i>
                                    <i class="o-step-tip mlm"></i>
                                    <i class="o-step-tip mlm"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="as-content-body animated flipInY">
                        <div class="middle-area">
                            <div class="as-area-top xac">
                                <?php echo $lang['Company init tip']; ?>
                            </div>
                            <div class="normal-content">
                                <input type="text" class="mb" placeholder="<?php echo $lang['Company fullname']; ?>"
                                       id="full_name" value="<?php echo $fullname; ?>" nofocus>
                                <div class="clearfix mb xal">
                                    <input type="text" class="span4 pull-left"
                                           placeholder="<?php echo $lang['Company shotname']; ?>" id="in_short"
                                           value="<?php echo $shortname; ?>" nofocus>
                                    <input type="text" class="span6 pull-right URL-input"
                                           placeholder="<?php echo $lang['System url']; ?>" id="url"
                                           value="<?php echo $pageUrl; ?>" nofocus>
                                </div>
                                <div class="clearfix department-setting">
                                    <div class="pull-left">
                                        <?php echo $lang['Department description']; ?>
                                    </div>
                                    <div class="pull-right">
                                        <?php echo $lang['Department content']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a class="dib as-previous-step closs-init" href="javascript:;" id="never_write_again">
                        <div class="posr">
                            <div class="ps-bg"></div>
                            <div class="previous-step-content"><i class="o-next-write"></i>
                                <span><?php echo $lang['Never reloading']; ?></span>
                            </div>
                        </div>
                    </a>
                    <a class="dib as-next-step" href="javascript:;" id="next_step_one">
                        <div class="posr">
                            <div class="ns-bg"></div>
                            <div class="next-step-content"><i class="o-next-step"></i>
                                <span><?php echo $lang['Next step']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- 设置账号 -->
                <div class="set-account posr hidden mark">
                    <div class="content-top">
                        <div class="top-bg"></div>
                        <div class="top-area">
                            <div class="tip-info clearfix mb">
                                <div class="pull-left">
                                    <?php echo $lang['Add new colleague']; ?>
                                </div>
                                <div class="pull-right">
                                    <i class="o-step-tip"></i>
                                    <i class="o-step-second mlm"></i>
                                    <i class="o-step-tip mlm"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="as-content-body animated flipInY" id="body_mark">
                        <div class="middle-area">
                            <div class="as-area-top xac">
                                <?php echo $lang['Set up colleague data']; ?>
                            </div>
                            <div class="normal-content">
                                <div class="" style="margin-bottom:10px; height:274px; overflow:hidden;">
                                    <div class="success-add-page" id="success_add_page" style="margin-top:-228px;">
                                        <div class="success-add-tip" id="success_add_tip">
                                            <i class="o-success-tip mbs"></i>
                                            <p class="xcgn fsl">添加成功</p>
                                        </div>
                                    </div>
                                    <div class="posr account-nub">
                                        <span class="sf-divider"></span>
										<span class="account-tips">
											<?php echo $lang['Add colleague tip']; ?>
										</span>
                                    </div>
                                    <div class="add-workmate" id="add_workmate">
                                        <div class="clearfix mb">
                                            <input type="text" class="pull-left normal-width personal" name="username"
                                                   placeholder="<?php echo $lang['Account']; ?>" id="username" nofocus>
                                            <input type="text" class="pull-right normal-width personal" name="password"
                                                   placeholder="<?php echo $lang['Password']; ?>" id="password" nofocus>
                                        </div>
                                        <div class="clearfix mb">
                                            <input type="text" class="pull-left normal-width personal" name="realname"
                                                   placeholder="<?php echo $lang['Realname']; ?>" id="realname" nofocus>
                                            <input type="text" class="pull-right normal-width personal" name="mobile"
                                                   placeholder="<?php echo $lang['Mobile']; ?>" id="mobile" nofocus>
                                        </div>
                                        <div class="clearfix mb">
                                            <!-- 部门，填完公司资料后，后台利用缓存ajax返回 -->
                                            <select class="normal-width pull-left personal" id="department"
                                                    name="department"></select>
                                            <!-- 岗位，填完公司资料后，后台利用缓存ajax返回 -->
                                            <select class="normal-width pull-right personal" id="position"
                                                    name="position"></select>
                                        </div>
                                        <input type="text" class="mb personal"
                                               placeholder="<?php echo $lang['Email url']; ?>" id="email" name="email"
                                               nofocus>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-warning btn-add-account" id="continue_add"
                                        style="display:none;">
                                    <i class="o-add-account"></i>
                                    <span class="fsn"><?php echo $lang['Add continue']; ?></span>
                                </button>
                                <button type="button" href="javascript:;" class="btn btn-warning btn-add-account"
                                        id="add_account">
                                    <i class="o-has-finish"></i>
                                    <span class="fsn">确认添加</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- <a class="dib as-previous-step" href="javascript:;">
						<div class="posr">
							<div class="ps-bg"></div>
							<div class="previous-step-content">
								<i class="o-previous-step"></i>
								<span><?php echo $lang['Pre step']; ?></span>
							</div>
						</div>
					</a> -->
                    <a class="dib as-next-step" href="javascript:;" id="next_step_two">
                        <div class="posr">
                            <div class="ns-bg"></div>
                            <div class="next-step-content">
                                <i class="o-next-step"></i>
                                <span><?php echo $lang['Next step']; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- 设置完成 -->
                <div class="base-settings posr mark hidden">
                    <div class="content-top">
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
                        </div>
                    </div>
                    <div class="sf-content-body animated flipInY">
                        <div class="middle-area">
                            <div class="area-top posr">
                                <i class="o-settings-finish"></i>
                            </div>
                            <div class="as-area-content">
                                <div class="mb posr">
                                    <span class="sf-divider"></span>
                                    <span class="step-tips"><?php echo $lang['Now you can']; ?></span>
                                </div>
                                <a href="<?php echo Yii::app()->urlManager->createUrl('dashboard/default/index', array('refer' => '/?r=dashboard/user/index')); ?>"
                                   class="perfect-framework fss">
                                    <i class="o-framework"></i>
                                    <p><?php echo $lang['Perfect organization']; ?></p>
                                </a>
                                <a href="<?php echo Yii::app()->urlManager->createUrl('dashboard/default/index'); ?>"
                                   class="backstage-set fss">
                                    <i class="o-backstage-set"></i>
                                    <p><?php echo $lang['Into dashboard']; ?></p>
                                </a>
                                <a href="javascript:;" class="tell-others fss">
                                    <i class="o-others"></i>
                                    <p><?php echo $lang['Tell more friend']; ?></p>
                                </a>
                                <a href="javascript:;" class="btn btn-warning btn-experience closs-init">
                                    <i class="o-next-step"></i>
                                    <span><?php echo $lang['Experience the new office']; ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- <a class="dib as-previous-step" href="javascript:;" id="previous_step_third">
						<div class="posr">
							<div class="ps-bg"></div>
							<div class="previous-step-content">
								<i class="o-previous-step"></i>
								<span><?php echo $lang['Pre step']; ?></span>
							</div>
						</div>
					</a> -->
                    <a class="dib as-finish-step closs-init" href="javascript:;">
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
<script>
    Ibos.app.s({
        passwordMinLength: '<?php echo $account['minlength']; ?>',
        passwordMaxLength: 32,
        passwordMixed: "<?php echo $account['mixed']; ?>",
        passwordRegex: "<?php echo $preg ?>"
    });
</script>

