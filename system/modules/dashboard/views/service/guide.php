<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/services.css?<?php echo VERHASH; ?>">
<div class="interface-service-wrap">
    <div class="fill-nn service-title">
        <span class="fsl">如何开通云服务？</span>
    </div>
    <div class="service-steps-title clearfix">
        <div class="pull-left">
            <i class="o-steps-one"></i>
        </div>
        <div class="ml pull-left">
            <p class="fsm xwb xcm mbm">申请授权</p>
            <?php if ($isOpen): ?>
                <p>登陆或者注册IBOS官网账号即可通过授权</p>
            <?php else: ?>
                <p>登录IBOS官网申请授权，申请通过后登录【IBOS管理中心—首页】填写授权码，授权绑定成功！</p>
            <?php endif; ?>
        </div>
        <div class="pull-right toggle-apply-label">
            <label class="toggle-tip-label">
                <a href="javascript:;" id="toggle_apply_content">如何获得授权?</a>
                <i class="o-toggle-apply"></i>
            </label>
        </div>
    </div>
    <div class="service-steps-list" id="apply_step_content">
        <?php if ($isOpen): ?>
            <div class="bind-step-first">
                <?php if (empty($loginInfo)): ?>
                    <div class="bind-info-wrap">
                        <form action="#" method="post" id="bind_info_form">
                            <p class="xwb mbs" id="web_tip">请先填写IBOS官网账号信息</p>
                            <div class="mbs" id="user_info_wrap">   
								<span id="userinfo_item" class="userinfo-wrap xwb mbs">
									<div class="login-info-wrap clearfix">
                                        <div class="user-info-wrap pull-left" id="login_info_wrap">
                                            <input type="text" id="username" name="username" placeholder="用户名/邮箱"
                                                   class="span4 guide-user-name">
                                            <input type="password" id="password" name="password" placeholder="密码"
                                                   class="span4 guide-user-passwrod">
                                        </div>
                                        <div class="pull-left mls">
                                            <a href="javascript:;" class="btn btn-primary mls" id="login_submit"
                                               data-action="login">登录</a>
                                            <a href="javascript:;" class="btn btn-primary mls open-app-services"
                                               id="open_app_services">开通云服务</a>
                                        </div>
                                    </div>
								</span>
                            </div>

                            <div class="bind-tip-wrap clearfix">
                                <p class="pull-left" id="bing_tip">如果没有IBOS账号可输入邮箱注册</p>
                                <p class="xcr pull-right" id="opt_tip_wrap"></p>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <span class="xwb fsl dib user-info-wrap">
						<?php echo $loginInfo['username']; ?> ( <?php echo $loginInfo['email']; ?> )
					</span>
                    <a href="javascript:;" class="btn btn-primary dib ml" id="open_app_services">开通云服务</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <ul class="list-inline steps-tip-list mb">
                <li>
                    <i class="o-apply-tip mbs"></i>
                    <p class="xac">
                        <span class="tcm">第一步: 申请并通过授权</span>
                        <a target='_blank' href="<?php echo $website . '/product' ?>" class="quick-apply-link">立即申请</a>
                    </p>
                </li>
                <li>
                    <i class="o-next-tip"></i>
                </li>
                <li>
                    <i class="o-key-tip mbs"></i>
                    <p class="xac">
                        <span class="tcm">第二步: 管理中心绑定授权</span>
                    </p>
                </li>
                <li>
                    <i class="o-next-tip"></i>
                </li>
                <li>
                    <i class="o-success-tip mbs"></i>
                    <p class="xac">
                        <span class="tcm">第三步: 授权绑定成功</span>
                    </p>
                </li>
            </ul>
            <div class="xac">
                <a href="<?php echo $this->createUrl('index/index') ?>" class="btn btn-large btn-primary">已通过,
                    我要填写授权</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="service-steps-title clearfix">
        <div class="pull-left">
            <i class="o-steps-two"></i>
        </div>
        <div class="ml pull-left">
            <p class="fsm xwb xcm mbm">开通云服务</p>
            <p>用户根据实际需要开启、关闭云服务。IBOS云服务已涵盖邮件、短信、语音、移动端管理等各大服务类型。</p>
        </div>
    </div>
    <div class="service-item-wrap">
        <ul class="list-inline service-item-list">
            <li>
                <i class="o-item-email mbs"></i>
                <p class="xwb xac mbs">邮件发送</p>
                <p class="fss">提供强大的邮件服务，邮件高速送达</p>
            </li>
            <li>
                <i class="o-item-SMS mbs"></i>
                <p class="xwb xac mbs">短信发送</p>
                <p class="fss">支持用户发送手机短信，发送短信提醒</p>
            </li>
            <li>
                <i class="o-item-call mbs"></i>
                <p class="xwb xac mbs">语音呼叫</p>
                <p class="fss">支持基于互联网的语音通话服务</p>
            </li>
            <li>
                <i class="o-item-meeting mbs"></i>
                <p class="xwb xac mbs">语音会议</p>
                <p class="fss">持多人采用网络或电话接入参与会议</p>
            </li>
            <li>
                <i class="o-item-warning mbs"></i>
                <p class="xwb xac mbs">推送提醒</p>
                <p class="fss">提供移动端同步的消息提醒推送服务</p>
            </li>
            <li>
                <i class="o-item-mobile mbs"></i>
                <p class="xwb xac mbs">移动端网络设置</p>
                <p class="fss">提供移动端网络入口绑定服务</p>
            </li>
        </ul>
    </div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_service.js"></script>