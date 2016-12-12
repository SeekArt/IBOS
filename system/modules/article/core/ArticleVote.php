<?php

/**
 * 文章模块------ 文章投票类组件文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 文章模块------ 文章投票类
 * @package application.modules.comment.components
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\article\core;

use application\core\utils\Ibos;
use application\modules\vote\components\Vote;
use application\modules\vote\model\Vote as VoteModel;
use application\modules\vote\model\VoteItem;

class ArticleVote extends Vote
{

    /**
     * 添加投票和投票项
     * @param array $voteData 投票
     * @param array $voteItemList 投票项
     * @return void
     */
    public function addVote($voteData, $voteItemList)
    {
        $vote = new VoteModel();
        foreach ($vote->attributes as $field => $value) {
            if (isset($voteData[$field])) {
                $vote->$field = $voteData[$field];
            }
        }
        $vote->save();
        $voteid = Ibos::app()->db->getLastInsertID();
        for ($i = 0; $i < count($voteItemList); $i++) {
            $voteItem = new VoteItem();
            $voteItem->voteid = $voteid;
            $voteItem->content = $voteItemList[$i];
            $voteItem->number = 0;
            $voteItem->type = $voteData['voteItemType'];
            $voteItem->save();
        }
    }

}
