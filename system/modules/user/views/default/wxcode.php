<div class="wx-login-title">
    <span class="fsl">微信企业号登录</span>
</div>
<div class="wx-login-content xac">
    <div class="fill-sn attend-wx-wrap xal">
        <i class="on-scan-opt"></i>
        <span class="scan-tip">扫一扫关注微信号</span>
        <a href="javascript:;" data-action="followWx" class="scan-link xcbu">查看详情</a>
    </div>
    <div class="mbm wx-code-wrap">
        <div id="login_qrcode"></div>
    </div>
    <span class="fss tcm" id="login_tip_wrap">使用微信企业号的“安全小助手”扫描二维码登录IBOS</span>
</div>
<script>
    Ibos.app.s('loginQrcode', '<?php echo $randomcode; ?>');
    Ibos.app.s('wxbinding', '<?php echo $wxbinding; ?>');
</script>
<script src='<?php echo $assetUrl; ?>/js/wxcode.js?<?php echo VERHASH; ?>'></script>