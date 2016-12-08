<?php

use application\core\utils\Convert;
use application\core\utils\Cache;
use application\modules\message\model\Comment;

Cache::set('notifyNode', null);

//清除评论和回复
$diaryComments = Comment::model()->fetchAllByAttributes(array('module' => 'diary'));
$cidArr = Convert::getSubByKey($diaryComments, 'cid');
if (!empty($diaryComments)) {
    $cidStr = implode(',', $cidArr);
    Comment::model()->deleteAll("rowid IN({$cidStr})");
    Comment::model()->deleteAllByAttributes(array('module' => 'diary'));
}