<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
    </div>
    <div>
        <form action="" class="form-horizontal">
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
                <div class="main-content" id="">
                    <div>
                        <i class="o-continue-image"></i>
                    </div>
                    <div id="continue_info">
                        <p class="version-title mbl mtl"><?php echo $lang['Last upgrade']; ?>&nbsp;<span
                                class="xcbu"><?php echo $data['stepName']; ?></span><?php echo $lang['Need to continue']; ?>
                        </p>
                        <button type="button" class="btn btn-primary btn-upgrade" id="continue_upgrade"
                                data-url="<?php echo $data['url']; ?>"><?php echo $lang['Upgrade continue']; ?></button>
                        <a class="mls"
                           href="<?php echo $this->createUrl('upgrade/index', array('op' => 'checking', 'rechecking' => 1)); ?>"><?php echo $lang['Rechecking']; ?></a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        // 有步骤缓存，继续升级
        $('#continue_upgrade').on('click', function () {
            var upgradeUrl = $(this).attr('data-url');
            window.location.href = upgradeUrl;
        });
    })();
</script>