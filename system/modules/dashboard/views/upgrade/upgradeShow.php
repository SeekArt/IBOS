<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css?<?= FORMHASH ?>">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
    </div>
    <div>
        <form action="" class="form-horizontal">
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
                <div class="main-content">
                    <i class="o-prompt-image"></i>
                </div>
                <?php $listCount = count($data['upgradeDesc']); ?>
                <?php foreach ($data['upgradeDesc'] as $key => $ver) : ?>
                    <div class="prompt-content upgrade-box <?php if ($key !== 0) : ?>unactive<?php endif; ?>">
                        <?php if ($listCount > 1) : ?><i class="o-page-ctrl"></i><?php endif; ?>
                        <p class="version-title mbm"><?php echo $lang['Discover new version']; ?> :</p>
                        <p class="version-num xcbu mbm"><?php echo $ver['version']; ?></p>
                        <p class="mbm"><?php echo $lang['Upgrade content']; ?></p>
                        <div class="upgrade-content scroll">
                            <?php echo $ver['desc']; ?><br/>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="mth mb posr upgrade-box">
                    <label class="checkbox">
                        <span class="mlm label-tip"><?php echo $lang['Upgrade remind']; ?>（<span class="xcbu upgrade-remind-hover">如何备份？</span>）</span>
                        <input type="checkbox" class="upgrade-remind" value="1">
                    </label>
                    <div class="prompt-content upgrade-remind-tip">
                        <p class="version-title mbm"><?php echo $lang['To backup']; ?></p>
                        <div class="upgrade-content scroll">
                            <?php echo $lang['Backup list']; ?><br/>
                        </div>
                    </div>
                </div>
                <div class="xac">
                    <button type="button" class="btn btn-primary btn-upgrade mbh" data-url="<?php echo $data['link']; ?>" disabled><?php echo $lang['Upgrade Now']; ?></button>
                    <br/>
                    <a href="http://doc.ibos.com.cn/article/detail/id/87" target="_blank"><?php echo $lang['Upgrade manual']; ?></a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    (function() {
        var DomMap = {
                $btn: $('.btn-upgrade'),
                $remindTip: $('.upgrade-remind-tip'),
                $remindHover: $('.upgrade-remind-hover')
            },
            pageCtrl = function(ishide, $context) {
                ishide ? $context.removeClass('unactive') : $context.addClass('unactive');
            };

        $('.o-page-ctrl').on('click', function() {
            var $context = $(this).parent('.prompt-content');
                isHide = $context.hasClass('unactive');

            pageCtrl(isHide, $context);
        });

        $('.upgrade-remind').on('change', function() {
            var checked = this.checked;

            DomMap.$btn.prop('disabled', !checked);
        });

        $('.upgrade-remind-hover').on('mouseover', function() {
                DomMap.$remindTip.addClass('active')
                    .position({
                        my: 'right bottom',
                        at: 'right top-10',
                        of: DomMap.$remindHover
                    });
            })
            .on('mouseout', function() {
                DomMap.$remindTip.removeClass('active');
            });

        DomMap.$btn.on('click', function () {
            var $el = $(this);

            // 关闭系统
            var closeSysUrl = "<?php echo $this->createUrl('index/switchstatus'); ?>";
            $.get(closeSysUrl, {val: '1'}, function(res) {
                window.location.href = $el.attr('data-url');
            });
        });
    })();
</script>