<?php

use application\core\utils\Convert;
use application\core\utils\Module;
use application\modules\message\model\Comment;
use application\modules\vote\model\Vote;
use application\modules\vote\model\VoteItem;
use application\modules\vote\model\VoteItemCount;

//清除评论和回复
$articleComments = Comment::model()->fetchAllByAttributes(array('module' => 'article'));
$cidArr = Convert::getSubByKey($articleComments, 'cid');
if (!empty($articleComments)) {
    $cidStr = implode(',', $cidArr);
    Comment::model()->deleteAll("rowid IN({$cidStr})");
    Comment::model()->deleteAllByAttributes(array('module' => 'article'));
}
//清除投票内容
$isInstallVote = Module::getIsEnabled('vote');
if ($isInstallVote) {
    $articleVotes = Vote::model()->fetchAllByAttributes(array('relatedmodule' => 'article'));
    $voteidArr = Convert::getSubByKey($articleVotes, 'voteid');
    $voteidStr = implode(',', $voteidArr);
    $articleVoteItems = VoteItem::model()->fetchAll("FIND_IN_SET(voteid, '{$voteidStr}')");
    $itemidArr = Convert::getSubByKey($articleVoteItems, 'itemid');
    $itemidStr = implode(',', $itemidArr);
    VoteItemCount::model()->deleteAll("FIND_IN_SET(itemid, '{$itemidStr}')");
    VoteItem::model()->deleteAll("FIND_IN_SET(itemid, '{$itemidStr}')");
    Vote::model()->deleteAllByAttributes(array('relatedmodule' => 'article'));
}

