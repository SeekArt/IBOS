<?php

use application\core\utils\Cache;
use application\core\utils\Convert;
use application\modules\message\model\Comment;

Cache::set('notifyNode', null);

//清除评论和回复
$reportComments = Comment::model()->fetchAllByAttributes(array('module' => 'report'));
$cidArr = Convert::getSubByKey($reportComments, 'cid');
if (!empty($reportComments)) {
    $cidStr = implode(',', $cidArr);
    Comment::model()->deleteAll("rowid IN({$cidStr})");
    Comment::model()->deleteAllByAttributes(array('module' => 'report'));
}