<!-- 未授权 -->
<?php
use application\core\utils\IBOS;
?>
<div id="license_unauthorized_cert">
    <form class="form-horizontal form-compact">
        <div>
            <p class="license-title">如果您还未购买产品</p>
            <span class="license-tip">请联系我们</span>
            <span class="license-tip ml">联系官网：<a href="http://www.ibos.com.cn" class="license-href" target="_blank">http://www.ibos.com.cn</a></span>
            <span class="license-tip ml30">营销QQ：4008381185</span>
        </div>
        <div>
            <p class="license-title mt30">如果您已经购买产品</p>
            <div class="auth-step">
                <i class="o-auth-first"></i>
                <span class="license-tip">打开<a href="/?r=dashboard/default/index" class="license-href" target="_blank">管理首页</a>，在授权信息处点击【立刻申请授权码】进入我的产品页</span>
            </div>
            <div class="auth-step ml40">
                <i class="o-auth-second"></i>
                <span class="license-tip">从我的产品后面的【申请授权码】按钮进入到授权码获取页</span>
            </div>
            <div class="auth-step ml40">
                <i class="o-auth-third"></i>
                <span class="license-tip">在获取页，提交资料便可获得授权码，返回首页输入即可开通</span>
            </div>
            <div class="mb">
                <i class="o-auth-guide"></i>
            </div>
        </div>
    </form>
</div>

<!--
<div class="license-cert unauthorized">
    <table>
        <tr>
            <th><?php echo $lang['License status']; ?></th>
            <td><?php echo $lang['Unauthorized']; ?></td>
        </tr>
        <tr>
            <th><?php echo $lang['Contact the official website']; ?></th>
            <td><a href="http://www.ibos.com.cn" target="_blank">http://www.ibos.com.cn</a></td>
        </tr>
        <tr>
            <th><?php echo $lang['QQ marketing']; ?></th>
            <td>4008381185</td>
        </tr>
        <tr>
            <th></th>
            <td><a href="<?php echo IBOS::app()->urlManager->createUrl( 'dashboard/default/index' ); ?>" target="_blank"><?php echo $lang['Application for authorization']; ?></a></td>
        </tr>
    </table>
</div>
-->