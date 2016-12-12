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
                        <a class="active" href="javascript:;">
                            <span class="circle">2</span>
                            <span class="ml"><?php echo $lang['Upgrade download'] ?></span>
                        </a>
                        <a href="javascript:;">
                            <span class="circle">3</span>
                            <span class="ml xcg"><?php echo $lang['Upgrade compare'] ?></span>
                        </a>
                        <a href="javascript:;">
                            <span class="circle">4</span>
                            <span class="ml xcg"><?php echo $lang['Upgradeing'] ?></span>
                        </a>
                        <a href="javascript:;">
                            <span class="circle">5</span>
                            <span class="ml xcg"><?php echo $lang['Upgrade complete'] ?></span>
                        </a>
                    </div>
                    <div class="alert trick-tip">
                        <div class="trick-tip-title">
                            <i></i>
                            <strong><?php echo $lang['Tips']; ?></strong>
                        </div>
                        <div class="trick-tip-content">
                            <ul>
                                <li><?php echo $lang['Upgrade download error tip']; ?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="main-content mtg">
                        <div>
                            <i class="o-chicking-image"></i>
                        </div>
                        <p class="version-title mbl mtl"
                           id="download_info"><?php echo $lang['Upgrade downloading']; ?></p>
                        <div class="progress progress-striped active">
                            <div class="progress-bar" role="progressbar" style="width: 100%">
                                <span class="sr-only"></span>
                            </div>
                        </div>
                        <?php echo $lang['Upgrade jump tip']; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        // 下载路径
        var downloadUrl = "<?php echo $downloadUrl; ?>",
            $downloadInfo = $('#download_info'),
            upgrade = {
                /**
                 * 第二步：下载文件
                 * @param {object} data 服务器返回的操作数据
                 * @returns {mixed}
                 */
                processingDownloadFile: function (data) {
                    $.get(data.url, {downloadStart: true}, function (res) {
                        if (res.step == '2') { // 没下载完，继续下载
                            if (res.data.IsSuccess) {
                                $downloadInfo.text(res.data.msg);
                                return upgrade.processingDownloadFile(res.data);
                            } else {
                                Ui.confirm(res.data.msg, function () {
                                    return upgrade.processingDownloadFile(res.data);
                                });
                            }
                        } else if (res.step == '3') { // 下载完成，跳到文件对比
                            window.location.href = res.data.url;
                        }
                    }, 'json');
                }
            };


        // 初始化加载页面就开始开始下载文件
        $.get(downloadUrl, {downloadStart: true}, function (data) {
            if (data.data.IsSuccess) {
                $downloadInfo.text(data.data.msg);
                upgrade.processingDownloadFile(data.data);
            } else {
                Ui.confirm(data.data.msg, function () {
                    upgrade.processingDownloadFile(data.data);
                });
            }
        });

    })();
</script>