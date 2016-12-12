<?php

use application\core\utils\Ibos;
use application\modules\dashboard\utils\Wx;

?>
<link href="<?php echo $this->getAssetUrl(); ?>/css/weixin.css" type="text/css" rel="stylesheet">
<div>
    <div class="ct">
        <div class="clearfix">
            <h1 class="mt"><?php echo $lang['Binding wechat and enjoy it']; ?></h1>
        </div>
        <div>
            <!-- 企业信息 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Wechat binding'] ?></h2>
                <div class="co-banding-wrap">
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
                    <div class="wx-binding-check">
                        <div class="wx-check-result">
                            <i class="mbs <?php echo $access ? 'o-result-tip' : 'o-failure-tip'; ?>"></i>
                            <?php if ($access) : ?>
                                <p class="xcgn mbm">系统URL验证成功！</p>
                                <p>接下来你可以使用该域名授权并安装套件应用</p>
                            <?php else : ?>
                                <p class="xcr mbm">系统URL验证失败！</p>
                                <p>请检查系统URL是否可被微信服务器访问，请填写正确URL验证并授权</p>
                            <?php endif; ?>
                        </div>
                        <?php if ($access) : ?>
                            <div>
                                <input type="hidden" class="dib span5" name="sysurl" value="<?php echo $domain; ?>"/>
                            </div>
                        <?php else : ?>
                            <div>
                                <input type="text" class="dib span5" name="sysurl" value="<?php echo $domain; ?>"/>
                                <button class="btn btn-primary mlm" data-action="bindWXCheck">验证</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="wx-btn-group">
                        <a class="wx-back-btn" href="<?php echo $this->createUrl('wxbinding/logout'); ?>">
                            <i class="o-back-arrow mrm"></i>
                            <span>退出当前帐号</span>
                        </a>
                        <button class="btn fsm wx-suite-install <?php echo $access ? 'btn-primary' : 'disabled'; ?>"
                                data-action="installApply" <?php echo $access ? '' : 'disabled'; ?>>安装套件应用
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript" src="<?php echo $this->getAssetUrl(); ?>/js/iboscologin.js"></script>
<script src="<?php echo $assetUrl; ?>/js/syncdata.js"></script>