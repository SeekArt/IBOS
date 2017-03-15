<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css?<?= FORMHASH ?>">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
    </div>
    <div>
        <form action="" class="form-horizontal">
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
                <div class="xac mtg">
                    <div class="dib">
                        <i class="o-success-image"></i>
                    </div>
                    <div class="success-tip-info">
                        <div class="info-content">
                            <?php echo $data['msg']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(function () {
        var url = Ibos.app.url("dashboard/update/index"),
            updatetype = ['data', 'static', 'module'],
            total = 0, index = 0,
            $updateCache = $("#updateCache");

        $updateCache.text("正在更新数据缓存");
        
        function sync(op, offset) {
            $.post(url, {
                op: op,
                offset: offset
            }, function (res) {
                var data = res.data;
                if (res.isSuccess) {
                    if (data.total != 0) {
                        total = data.total;
                    }

                    $updateCache.text(res.msg);
                    if (data.process == "end") {
                        index += 1;
                        i = 0;
                    }

                    updatetype[index] ? sync(updatetype[index], data.offset) : (function() {
                        setTimeout(function() {
                            $updateCache.text('记得要定期检查更新哦～')
                            openSystem();
                        }, 500);
                    })();
                }
            }, "json");
        }

        function swicthSys(param) {
            var url = Ibos.app.url('dashboard/index/switchstatus');
            return $.post(url, param, $.noop, 'json');
        }

        sync(updatetype[index], 0);

        $('#back_home').on('click', function () {
            window.parent.location.href = "<?php echo $this->createUrl('default/index'); ?>";
        });

        function openSystem() {
            // 不知道为什么开启是0，问后端..
            swicthSys({ val: 0 }).done(function(res) {
                if (res.IsSuccess) {
                    Ui.tip(U.lang("DB.OPEN_SYSTEM_SUCCESSED"));
                    setTimeout(function() {
                        window.location.href = Ibos.app.url('dashboard/index/index');
                    }, 1000);
                } else {
                    Ui.tip(U.lang("DB.OPEN_SYSTEM_FAILED"), "danger");
                    return false;
                }
            });
        };
    })
</script>