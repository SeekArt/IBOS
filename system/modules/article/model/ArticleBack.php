<?php

/**
 * 信息中心模块------ article_back
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * article_back 新闻退回记录表的数据层操作类，继承ICModel
 * @package application.modules.article.model
 * @version $Id: ArticleBack.php 3479 2014-05-28 03:29:56Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\article\model;

use application\core\model\Model;
use application\core\utils\Convert;

class ArticleBack extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{article_back}}';
    }

    /**
     * 添加一条退回记录
     * @param integer $artId 退回的新闻id
     * @param integer $uid 操作者uid
     * @param string $reason 退回理由
     * @param integer $time 退回时间
     * @return integer
     */
    public function addBack($artId, $uid, $reason, $time = TIMESTAMP)
    {
        return $this->add(array(
            'articleid' => $artId,
            'uid' => $uid,
            'reason' => $reason,
            'time' => $time
        ));
    }

    /**
     * 获得所有退回的新闻id数组
     * @return array
     */
    public function fetchAllBackArtId()
    {
        $record = $this->fetchAll();
        return Convert::getSubByKey($record, 'articleid');
    }

    /*
     * 得到一条退回的新闻数据
     * @param integer $articleid 新闻ID
     */
    public function getBackByArticleId($articleid)
    {
        $record = $this->fetch("articleid = :articleid", array(':articleid' => $articleid));
        return $record;
    }
}
