<?php

use application\core\utils\Env;
use application\core\utils\Ibos;

?>

<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
    </div>
    <div>
        <form action="" class="form-horizontal">
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
                <div class="">
                    <div class="brc mb clearfix">
                        <a class="finish-step" href="javascript:;">
                            <i class="o-finish-step"></i>
                            <span class="ml"><?php echo $lang['Upgrade get file'] ?></span>
                        </a>
                        <a class="finish-step" href="javascript:;">
                            <i class="o-finish-step"></i>
                            <span class="ml"><?php echo $lang['Upgrade download'] ?></span>
                        </a>
                        <a class="finish-step" href="javascript:;">
                            <i class="o-finish-step"></i>
                            <span class="ml xcg"><?php echo $lang['Upgrade compare'] ?></span>
                        </a>
                        <a class="active" href="javascript:;">
                            <span class="circle">4</span>
                            <span class="ml xcg"><?php echo $lang['Upgradeing'] ?></span>
                        </a>
                        <a href="javascript:;">
                            <span class="circle">5</span>
                            <span class="ml xcg"><?php echo $lang['Upgrade complete'] ?></span>
                        </a>
                    </div>
                    <div class="main-content mtg">
                        <div>
                            <i class="o-chicking-image"></i>
                        </div>
                        <p class="version-title mbl mtl" id="upgrade_info"><?php echo $lang['Updateing'] ?></p>
                        <div class="progress progress-striped active">
                            <div class="progress-bar" role="progressbar" style="width: 100%">
                                <span class="sr-only"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!--错误重新设置模板-->
<script type="text/ibos-template" id="update_confirm">
    <div>
        <div class="alert alert-error>"><%=msg%></div>
        <button type="button" data-target="<%=retryUrl%>" data-loading-text="<?php echo $lang['Action processing']; ?>"
                autocomplete="off" data-act="processStep" class="btn btn-large">重试
        </button>
        <button type="button" data-target="<%=ftpUrl%>" data-loading-text="<?php echo $lang['Action processing']; ?>"
                autocomplete="off" data-act="processStep" class="btn btn-large mls" id="ftp_setup_btn">设置ftp
        </button>
    </div>
</script>
<!--设置ftp-->
<script type="text/ibos-template" id="ftp_setup">
    <form id="sys_ftp_form" method="post" class="form-horizontal">
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Enabled ssl']; ?></label>
            <div class="controls">
                <input type="checkbox" name="ftp[ssl]" value="1" data-toggle="switch" class="visi-hidden"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Ftp host']; ?></label>
            <div class="controls">
                <input type="text" name="ftp[host]"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Ftp port']; ?></label>
            <div class="controls">
                <input type="text" name="ftp[port]" value="25"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Ftp user']; ?></label>
            <div class="controls">
                <input type="text" name="ftp[username]"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Ftp pass']; ?></label>
            <div class="controls">
                <input type="text" name="ftp[password]"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo $lang['Ftp pasv']; ?></label>
            <div class="controls">
                <input type="checkbox" name="ftp[pasv]" value="1" data-toggle="switch" class="visi-hidden"/>
            </div>
        </div>
    </form>
</script>
<script type="text/javascript">
    var _ib = _ib || [];
    _ib.push(['authkey', '<?php echo Ibos::app()->setting->get('config/security/authkey'); ?>']);
    _ib.push(['ip', '<?php echo Env::getClientIp(); ?>']);
    _ib.push(['from', '<?php echo $from; ?>']);
    _ib.push(['to', '<?php echo $to; ?>']);
    _ib.push(['fullname', '<?php echo Ibos::app()->setting->get('setting/unit/fullname'); ?>']);
    _ib.push(['type', 'upgrade']);
    (function () {
        var ib = document.createElement('script');
        ib.type = 'text/javascript';
        ib.async = true;
        ib.src = 'http://www.ibos.com.cn/Public/static/ib.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ib, s);
    })();
    Ibos.app.s({coverUrl: "<?php echo $coverUrl; ?>"})
</script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js"></script>
<script src="<?php echo $assetUrl; ?>/js/db_upgrade_cover.js"></script>
