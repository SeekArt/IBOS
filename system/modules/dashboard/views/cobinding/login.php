<link href="<?php echo $this->getAssetUrl(); ?>/css/ibosco.css" type="text/css" rel="stylesheet">
<div class="<?php if ($isInstall) : ?>binding-install-wrap<?php endif; ?>">
    <div class="ct">
        <?php if (!$isInstall) : ?>
            <div class="clearfix">
                <h1 class="mt">绑定酷办公，体验真正的移动办公！</h1>
            </div>
        <?php endif; ?>
        <div>
            <!-- 企业信息 start -->
            <div <?php if (!$isInstall) : ?>class="ctb"<?php endif; ?>>
                <?php if (!$isInstall) : ?>
                    <h2 class="st">酷办公绑定</h2>
                <?php endif; ?>
                <div class="co-banding-wrap">
                    <?php if ($op === "isBinding") : ?>
                        <div class="co-binding-content clearfix">
                            <div class="co-info-box pull-left co-info-ibos">
                                <div class="co-binding-logo">
                                    <img src="<?php if ($ibos['corplogo']) {
                                        echo $ibos['corplogo'];
                                    } else {
                                        echo $this->getAssetUrl() . '/image/corp_logo.png';
                                    } ?>"/>
                                    <i class="o-binding-ibos"></i>
                                </div>
                                <div class="xac">
                                    <p class="xwb mts"><?php echo $ibos['corpshortname']; ?></p>
                                    <p><?php echo $ibos['systemurl']; ?></p>
                                </div>
                            </div>
                            <div class="co-binding-state">
                                <div class="co-binding-icon">
                                    <i class="o-binding-success"></i>
                                    <span>已绑定</span>
                                </div>
                            </div>
                            <div class="co-info-box pull-right co-info-co">
                                <div class="co-binding-logo">
                                    <img src="<?php if ($co['corplogo']) {
                                        echo $co['corplogo'];
                                    } else {
                                        echo $this->getAssetUrl() . '/image/corp_logo.png';
                                    } ?>"/>
                                    <i class="o-binding-co"></i>
                                </div>
                                <div class="xac">
                                    <p class="xwb mts"><?php echo $co['corpshortname']; ?></p>
                                    <p>企业ID:<?php echo $co['corpid']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="co-binding-login">
                            <p>登录酷办公管理</p>
                            <div class="span11 ml">
                                <input type="text" class="mb" value="<?php echo $mobile; ?>" id="bind_mobile"
                                       placeholder="请输入帐号" autocomplete="off"
                                       <?php if ($readonly) : ?>readonly<?php endif; ?>/>
                                <input type="password" class="mb" id="bind_password" placeholder="请输入密码"/>
                                <button type="button" class="btn btn-primary span12 mb" data-action="loginCorp">登录
                                </button>
                                <div>
                                    <a href="http://www.ibos.cn/forgotpass" class="pull-left">忘记密码?</a>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="co-binding-info">
                            <h1 class="binding-title">绑定酷办公</h1>
                            <p>酷办公是IBOS的移动客户端，登录或注册即可绑定体验！</p>
                            <div>
                                <i class="o-unbinding-intro"></i>
                            </div>
                            <div class="co-binding-tip">
                                <p class="co-binding-tips">绑定流程</p>
                                <i class="o-binding-tip"></i>
                            </div>
                            <div class="co-binding-info-box">
                                <button type="button" class="btn btn-primary binding-btn-wrap mls"
                                        data-action="bindLoginCo">登录酷办公绑定
                                </button>
                                <button type="button" class="btn binding-btn-wrap mls" data-action="bindIbosCo">
                                    我还没注册酷办公
                                </button>
                            </div>
                            <?php if ($isInstall) : ?>
                                <p class="xcn">如果现在暂不绑定，以后也可以登录后台找到酷办公绑定流程</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ibosco_register_dialog" style="display:none;">
    <div style="width:300px">
        <form class="form-horizontal" id="ibosco_register_form">
            <div class="user-reg-info" id="user_reg_verify">
                <div class="clearfix mb">
                    <div class="span11 ml12 xac">
                        <h3 class="xwb">欢迎注册酷办公</h3>
                    </div>
                </div>
                <div class="clearfix mb">
                    <div class="pull-left span11 ml12">
                        <input type="text" id="inputMobile" placeholder="手机号" autocomplete="off">
                    </div>
                </div>
                <div class="clearfix mb">
                    <div class="pull-left span11 ml12">
                        <div class="input-group">
                            <input type="text" id="inputMobileVerify" placeholder="短信验证码" autocomplete="off">
                            <span class="input-group-addon pl pr" data-action="getMobileVerifyCode"
                                  data-loading-text="重新发送（<span id='mobile_counting'>60</span>）">获取验证码</span>
                        </div>
                        <div class="xcr">
                            <span id='send_mobile_status'></span>
                        </div>
                    </div>
                </div>
                <div class="clearfix mb">
                    <div class="pull-left span11 ml12">
                        <button type="button" class="btn btn-warning span12" id="next_reg_state"
                                data-evtname="nextState">注册
                        </button>
                    </div>
                </div>
                <div class="clearfix mb">
                    <div class="pull-left span11 ml12">
                        <p class="pull-left">已有帐号？<a href="javascript:;" class="xcbu" data-action="bindLoginCo">立即登录</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="user-reg-info" id="user_reg_info" style="display:none;">
                <div class="clearfix mb">
                    <div class="span11 ml12 xac">
                        <h3 class="xwb">完善个人信息</h3>
                    </div>
                </div>
                <div class="clearfix mb">
                    <div class="pull-left span11 ml12">
                        <input type="text" id="user_name" placeholder="真实姓名" autocomplete="off"/>
                    </div>
                </div>
                <div class="clearfix mb">
                    <div class="pull-left span11 ml12">
                        <input type="password" id="user_password" placeholder="设置登录密码" autocomplete="off"/>
                    </div>
                </div>
                <div class="clearfix mb">
                    <div class="pull-left span11 ml12">
                        <input type="text" id="user_invite" placeholder="邀请人手机号，选填" autocomplete="off"/>
                    </div>
                </div>
                <div class="clearfix mb">
                    <div class="pull-left span11 ml12">
                        <button type="button" class="btn btn-primary span12" id="reg_and_bind"
                                data-evtname="registerCo">完成
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="ibosco_login_dialog" style="display:none;">
    <div style="width:300px">
        <form class="form-horizontal" id="ibosco_login_form">
            <div class="clearfix mb">
                <div class="span11 ml12 xac">
                    <h3 class="xwb">欢迎登录酷办公</h3>
                </div>
            </div>
            <div class="clearfix mb">
                <div class="pull-left span11 ml12">
                    <input type="text" id="mobile" placeholder="手机号"/>
                </div>
            </div>
            <div class="clearfix mb">
                <div class="pull-left span11 ml12">
                    <input type="password" id="password" placeholder="密码">
                </div>
            </div>
            <div class="clearfix mb">
                <div class="pull-left span11 ml12">
                    <button type="button" class="btn btn-primary span12" id="login_and_bind" data-evtname="loginCo">登录
                    </button>
                </div>
            </div>
            <div class="clearfix mb">
                <div class="pull-left span11 ml12">
                    <a href="javascript:;" onclick="javascript:window.open('http://www.kubangong.com/forgotpass');"
                       class="pull-left">忘记密码</a>
                    <a href="javascript:;" class="pull-right" data-action="bindIbosCo">免费注册</a>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    Ibos.app.s("isInstall", "<?php echo $isInstall; ?>")
</script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript" src="<?php echo $this->getAssetUrl(); ?>/js/iboscologin.js"></script>
<script src="<?php echo $assetUrl; ?>/js/db_cobinding_banding.js"></script>