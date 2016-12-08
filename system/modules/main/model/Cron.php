<?php

/**
 * cron表对应数据层操作
 *
 * @package application.app.main.model
 * @version $Id$
 */

namespace application\modules\main\model;

use application\core\model\Model;

class Cron extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{cron}}';
    }

    /**
     * 查找下一条可执行的定时任务
     * @param integer $timestamp 用于对比的时间戳
     * @return array
     */
    public function fetchByNextRun($timestamp = TIMESTAMP)
    {
        $timestamp = intval($timestamp);
        return $this->fetch("`available` > 0 AND `nextrun`<={$timestamp} ORDER BY nextrun");
    }

    /**
     * 按照下一次执行时间排序的下一条定时任务
     * @return array
     */
    public function fetchByNextCron()
    {
        return $this->fetch("`available` > 0 ORDER BY nextrun");
    }

}
