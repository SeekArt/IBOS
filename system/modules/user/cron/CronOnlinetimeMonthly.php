<?php
/**
 * 后台->管理->计划任务，清空本月在线时间
 */
use application\modules\user\model\OnlineTime;

OnlineTime::model()->updateThisMonth();
