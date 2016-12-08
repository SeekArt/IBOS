<?php

use application\core\utils\Convert;
use application\modules\file\model\FileTrash;

/**
 * 清空15天前的回收站文件
 */
$pastTime = TIMESTAMP - 15 * 86400;
$trashFiles = FileTrash::model()->fetchAll("`deltime` < {$pastTime}");
$fids = Convert::getSubByKey($trashFiles, 'fid');
if (!empty($fids)) {
    FileTrash::model()->fully($fids);
}