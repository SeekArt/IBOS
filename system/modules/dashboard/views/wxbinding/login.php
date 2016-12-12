<?php

use application\core\utils\Ibos;
use application\modules\dashboard\utils\Wx;

?>
<link href="<?php echo $this->getAssetUrl(); ?>/css/weixin.css" type="text/css" rel="stylesheet">
<div>
    <div class="ct">
        <div class="clearfix">
            <h1 class="mt"><?php echo $lang['Binding wechat and install now']; ?></h1>
        </div>
        <div>
            <!-- 企业信息 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Wechat binding'] ?></h2>
                <!--已绑定-->
                <?php if ($isBinding) : ?>
                    <div class="box-shadow bind-info-wrap">
                        <div class="clearfix">
                            <div class="box-shadow ibos-qy">
                                <div class="aes-key" data-toggle="tooltip" data-html="true"
                                     title="<div class='aes-key-tip'><p class='xwb'>AES KEY：</p><p><?php //echo $aeskey; ?></p></div>">
                                    AES KEY
                                </div>
                                <div class="company-logo mbs">
                                    <img src="<?php echo !empty($logo) ? $logo : 'static/image/logo.png'; ?>"
                                         alt="<?php echo $shortname; ?>">
                                    <div class="ibos-logo">
                                        <i class="o-binding-ibos"></i>
                                    </div>
                                </div>
                                <p class="lhl t"><?php echo $fullname; ?></p>
                                <p class="lhl"><?php echo $domain; ?></p>
                            </div>
                            <div class="box-shadow weixin-qy">
                                <div class="company-logo mbs">
                                    <img src="<?php echo $wxlogo; ?>" alt="<?php echo $wxname; ?>">
                                    <div class="weixin-logo">
                                        <i class="o-binding-weixin"></i>
                                    </div>
                                </div>
                                <p class="lhl"><?php echo $wxname; ?></p>
                                <p class="lhl">CorpID : <?php echo $wxcorpid; ?></p>
                            </div>
                            <div class="co-binding-state" data-toggle="tooltip" title="解绑需要到微信企业号后台取消套件托管">
                                <div class="co-binding-icon">
                                    <i class="o-binding-success"></i>
                                    <span></span>绑定成功
                                </div>
                                <div class="co-unbinding-icon"
                                     onclick="window.open('http://doc.ibos.com.cn/article/detail/id/329' ,'_blank');">
                                    <i class="o-unbinding-success"></i>
                                    <span></span>解除绑定
                                </div>
                            </div>
                        </div>
                        <div class="co-binding-login">
                            <p>登录酷办公管理</p>
                            <div class="span11 ml">
                                <input type="text" class="mb" value="<?php echo $mobile; ?>" id="bind_mobile"
                                       placeholder="请输入帐号" autocomplete="off" readonly disabled/>
                                <input type="password" class="mb" id="bind_password" placeholder="请输入密码"/>
                                <button type="button" class="btn btn-primary span12 mb" data-action="loginCorp">登录
                                </button>
                                <div>
                                    <a href="http://www.ibos.com.cn/forgotpass" target="_blank"
                                       class="pull-left">忘记密码?</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--未绑定-->
                <?php else : ?>
                    <div class="co-banding-wrap">
                        <div class="co-binding-info">
                            <div class="box-shadow ibosoa-info clearfix">
                                <div class="company-logo mbs pull-left">
                                    <img src="<?php echo !empty($logo) ? $logo : 'static/image/logo.png'; ?>"
                                         alt="<?php echo $shortname; ?>">
                                    <div class="ibos-logo">
                                        <i class="o-binding-ibos"></i>
                                    </div>
                                </div>
                                <div class="company-info pull-left">
                                    <p class="lhl t"><?php echo $fullname; ?></p>
                                    <p class="lhl">系统URL : <?php echo $domain; ?></p>
                                    <p class="lhl ellipsis"
                                       title="AES KEY : 9Pcz3VcUe6kh-GEAgU3vL99rHUk5F7C-libcteUhQkYC72D8qf">AES KEY
                                        : <?php echo $aeskey; ?></p>
                                </div>
                            </div>
                            <div>
                                <i class="o-unbinding-wxintro"></i>
                            </div>
                            <div class="co-binding-tip">
                                <p class="co-binding-tips">绑定流程</p>
                                <i class="o-binding-wxtip"></i>
                            </div>
                            <div class="co-binding-info-box">
                                <button type="button" class="btn btn-primary binding-btn-wrap mls"
                                        data-action="bindLoginCo">登录酷办公
                                </button>
                                <button type="button" class="btn binding-btn-wrap mls" data-action="bindIbosCo">注册酷办公
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
    Ibos.app.s('page', 'wxbind');
</script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript" src="<?php echo $this->getAssetUrl(); ?>/js/iboscologin.js"></script>
<script src="<?php echo $assetUrl; ?>/js/syncdata.js"></script>