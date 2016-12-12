<?php

use application\core\utils\Convert;
use application\modules\recruit\model\Resume;
use application\modules\recruit\model\ResumeStats;

defined("ONE_DATE_TIME") or define("ONE_DATE_TIME", 86400);
//$row = cron::model()->fetch(array(
//	'select' => '`lastrun`,`nextrun`',
//	'condition' => 'filename = :filename',
//	'params' => array(':filename'=>basename(__FILE__))
//));
// 今天的日期
$todayTime = strtotime(date('Y-m-d'));
// 获取上一次添加的统计时间
$stats = ResumeStats::model()->fetch(array(
    'select' => 'datetime',
    'order' => 'datetime DESC'
));
// 从现在的时间作参照，给往前的没添加统计的每一天添加统计数据（一般是一天，特殊是有全天都没有用户登录过系统，特殊情况补空值）
if ($todayTime - $stats['datetime'] >= ONE_DATE_TIME) {
    for ($i = $stats['datetime'] + ONE_DATE_TIME; $i < $todayTime; $i += ONE_DATE_TIME) {
        $newCount = Resume::model()->count(sprintf("`entrytime` BETWEEN %d AND %d", $i, $i + ONE_DATE_TIME));
        $resumes = Resume::model()->fetchAll(array(
            'select' => 'status',
            'condition' => sprintf("`statustime` = %d", $i)
        ));
        $status = Convert::getSubByKey($resumes, 'status');
        $ac = array_count_values($status);
        $data = array(
            'new' => $newCount, // 新增数
            'pending' => isset($ac['4']) ? $ac['4'] : 0, // 未安排数
            'interview' => isset($ac['1']) ? $ac['1'] : 0, // 面试数
            'employ' => isset($ac['2']) ? $ac['2'] : 0, // 录用数
            'eliminate' => isset($ac['5']) ? $ac['5'] : 0, // 淘汰数
            'datetime' => $i // 日期时间戳
        );
        ResumeStats::model()->add($data);
    }
}