<?php
use application\core\utils\Ibos;

?>
<link rel="stylesheet" href="<?php echo $assetUrl . '/css/file_cabinets.css'; ?>">
<style>
    .fc-list-cell .list-thumb li,
    .fc-list-cell .list-thumb .file-disk-ent {
        margin: 0;
        padding: 10px 5px 10px 5px;
        height: 125px;
    }
</style>
<div class="fc-list-cell">
    <div class="list-thumb clearfix">
        <a href="<?php echo Ibos::app()->urlManager->createUrl('file/company/index') ?>" class="file-disk-ent">
            <i class="nd-type o-disk-company" title="公司网盘"></i>
            <div class="nd-name">公司网盘</div>
        </a>
        <a href="<?php echo Ibos::app()->urlManager->createUrl('file/personal/index') ?>" class="file-disk-ent">
            <i class="nd-type o-disk-personal" title="个人网盘"></i>
            <div class="nd-name">个人网盘</div>
        </a>
        <a href="<?php echo Ibos::app()->urlManager->createUrl('file/myshare/index') ?> " class="file-disk-ent">
            <i class="nd-type o-disk-share" title="我分享的"></i>
            <div class="nd-name">我分享的</div>
        </a>
        <a href="<?php echo Ibos::app()->urlManager->createUrl('file/fromshare/index') ?>" class="file-disk-ent">
            <i class="nd-type o-disk-receive" title="我收到的"></i>
            <div class="nd-name">我收到的</div>
        </a>
    </div>
</div>