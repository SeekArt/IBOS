<?php

/**
 * 投票模块------ 投票插件管理器类组件文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 投票模块------ 投票插件管理器类
 * @package application.modules.comment.components
 * @version $Id: ICVotePlugManager.php 50 2013-06-5 8:47:50Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\vote\components;

use application\core\components\PlugManager;
use application\modules\article\core\ArticleVote;

class VotePlugManager extends PlugManager
{

    /**
     * 取得文章投票对象
     */
    public static function getArticleVote()
    {
        return new ArticleVote();
    }
}
