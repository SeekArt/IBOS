<?php

use application\core\utils\Cache;
use application\core\utils\Convert;
use application\modules\message\model\Comment;

Cache::set('notifyNode', null);

//清除评论和回复
$assignmentComments = Comment::model()->fetchAllByAttributes(array('module' => 'assignment'));
$cidArr = Convert::getSubByKey($assignmentComments, 'cid');
if (!empty($assignmentComments)) {
    $cidStr = implode(',', $cidArr);
    Comment::model()->deleteAll("rowid IN({$cidStr})");
    Comment::model()->deleteAllByAttributes(array('module' => 'assignment'));
}