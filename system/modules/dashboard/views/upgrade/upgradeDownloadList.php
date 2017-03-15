<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css?<?= FORMHASH ?>">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
    </div>
    <div>
        <form action="" class="form-horizontal">
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
                <div class="alert trick-tip">
                    <div class="trick-tip-title">
                        <i></i>
                        <strong><?php echo $lang['Skills prompt']; ?></strong>
                    </div>
                    <div class="trick-tip-content">
                        <?php echo $lang['Update file path']; ?>
                    </div>
                </div>
                <div class="">
                    <div class="brc mb clearfix">
                        <a class="active" href="javascript:;">
                            <span class="circle">1</span>
                            <span class="ml"><?php echo $lang['Upgrade get file'] ?></span>
                        </a>
                        <a href="javascript:;">
                            <span class="circle">2</span>
                            <span class="ml"><?php echo $lang['Upgrade download'] ?></span>
                        </a>
                        <a href="javascript:;">
                            <span class="circle">3</span>
                            <span class="ml"><?php echo $lang['Upgradeing'] ?></span>
                        </a>
                        <a href="javascript:;">
                            <span class="circle">4</span>
                            <span class="ml"><?php echo $lang['Upgrade complete'] ?></span>
                        </a>
                    </div>
                    <table class="table table-bordered table-striped table-operate">
                        <thead>
                        <tr>
                            <th><?php echo $lang['Upgrade preupdatelist']; ?></th>
                            <th><?php echo $lang['Upgrade filesize count'] . $data['count']; ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($data['list'] as $key => $upgradeFile): ?>
                            <tr>
                                <td><?php echo rawurldecode(basename($upgradeFile)); ?></td>
                                <td><?php echo $data['filesize'][$key]; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-large btn-primary" id="upgradeDownload" data-url="<?php echo $data['actionUrl']; ?>">下载更新</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    (function () {
        $('#upgradeDownload').on('click', function () {
            window.location.href = $(this).attr('data-url');
        });
    })();
</script>