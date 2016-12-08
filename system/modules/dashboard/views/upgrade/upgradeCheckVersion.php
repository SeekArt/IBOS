<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
    </div>
    <div>
        <form action="" class="form-horizontal">
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
                <div class="main-content mtg">
                    <div>
                        <i class="o-chicking-image"></i>
                    </div>
                    <p class="version-title mbl mtl"><?php echo $lang['Upgrade checking']; ?></p>
                    <div class="progress progress-striped active">
                        <div class="progress-bar" role="progressbar" style="width: 100%">
                            <span class="sr-only"></span>
                        </div>
                    </div>
                    <span><?php echo $lang['Upgrade not jump']; ?></span><a class="click-link"
                                                                            href="<?php echo $this->createUrl('upgrade/index', array('op' => 'showupgrade')); ?>"><?php echo $lang['Upgrade this']; ?></a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        var upgrade = {
            // 默认请求路由
            baseUrl: Ibos.app.url('dashboard/upgrade/index', {op: 'checking'}),
            /**
             * 检查升级
             * @returns {mixed}
             */
            check: function () {
                // 开始检查升级
                window.location.href = this.baseUrl;
            }
        };
        // 加载页面就开始请求检查更新
        upgrade.check();

    })();
</script>