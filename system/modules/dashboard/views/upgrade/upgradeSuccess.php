<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css">
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
        var url = "<?php echo $this->createUrl('upgrade/updateCache');?>";
        $("#updateCache").text("正在更新数据缓存");
        $.get(url, {op: "cache"}, function (data) {
            updateCache(url, data);
        })
        function updateCache(url, data) {
            if (data.isSuccess) {
                if (data.isContinue) {
                    $("#updateCache").text(data.msg);
                    $.get(url, {op: data.op}, function (res) {
                        updateCache(url, res);
                    })
                } else {
                    $("#updateCache").text(data.msg);
                }
            }
        }

        $('#back_home').on('click', function () {
            window.parent.location.href = "<?php echo $this->createUrl('default/index'); ?>";
        });

    })
</script>