<?php

use application\core\utils\Cache;
use application\core\utils\Convert;
use application\modules\message\model\Comment;

Cache::update(array('setting', 'nav'));

//清除评论和回复
$docComments = Comment::model()->fetchAllByAttributes(array('module' => 'officialdoc'));
$cidArr = Convert::getSubByKey($docComments, 'cid');
if (!empty($docComments)) {
    $cidStr = implode(',', $cidArr);
    Comment::model()->deleteAll("rowid IN({$cidStr})");
    Comment::model()->deleteAllByAttributes(array('module' => 'officialdoc'));
}