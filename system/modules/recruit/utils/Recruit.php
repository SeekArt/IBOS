<?php

/**
 * 招聘模块------ 工具类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------  工具类
 * @package application.modules.recruit.utils
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\utils;

use application\core\utils\StringUtil;

class Recruit
{

    /**
     * 组合条件查询语句
     * @param string $type 查询类型
     * @param string $condition 查询条件
     * @return string 连接后的语句
     */
    public static function joinTypeCondition($type, $condition)
    {
        $statusCondition = '';
        if ($type == 'arrange') {
            $statusCondition = 'r.status=4';
        } else if ($type == 'audition') {
            $statusCondition = 'r.status=1';
        } else if ($type == 'hire') {
            $statusCondition = 'r.status=2';
        } else if ($type == 'eliminate') {
            $statusCondition = 'r.status=5';
        } else if ($type == 'flag') {
            $statusCondition = 'r.flag=1';
        }
        return $condition . $statusCondition;
    }

    /**
     * 组合搜索条件
     * @param array $search
     * @param string $condition
     */
    public static function joinResumeSearchCondition(array $search, $condition)
    {
        $searchCondition = '';

        $realname = $search['realname'];
        $positionid = implode(',', StringUtil::getId($search['positionid']));
        $gender = $search['gender'];
        $ageRange = $search['ageRange'];
        $education = $search['education'];
        $workyears = $search['workyears'];
        if (!empty($realname)) {
            $searchCondition .= " rd.realname LIKE '%$realname%' AND ";
        }
        if (!empty($positionid)) {
            $searchCondition .= " rd.positionid = {$positionid} AND ";
        }
        if ($gender != -1) {
            $searchCondition .= " rd.gender='$gender' AND ";
        }
        if ($ageRange != -1) {
            $ageArr = explode('-', $ageRange);
            list($minAge, $maxAge) = $ageArr;
            $maxTime = strtotime(date('Y') - $minAge);
            $minTime = strtotime(date('Y') - $maxAge);
            $searchCondition .= " rd.birthday>='$minTime' AND rd.birthday<='$maxTime' AND ";
        }
        if ($education != -1) {
            $searchCondition .= " rd.education='$education' AND ";
        }
        if ($workyears != -1) {
            $searchCondition .= " rd.workyears='$workyears' AND ";
        }
        $searchCondition = empty($searchCondition) ? '' : substr($searchCondition, 0, -4);
        return $condition . $searchCondition;
    }

    /**
     * 链接contact控制器搜索条件
     * @param array $search
     * @param string $condition
     * @return string
     */
    public static function joinContactSearchCondition(array $search, $condition)
    {
        $searchCondition = '';

        $realname = $search['realname'];
        $input = implode(',', StringUtil::getId($search['input']));
        $inputtime = $search['inputtime'];
        $contact = $search['contact'];
        $purpose = $search['purpose'];

        if (!empty($realname)) {
            $searchCondition .= " rd.realname LIKE '%$realname%' AND ";
        }
        if (!empty($input)) {
            $searchCondition .= " rc.input='$input' AND ";
        }
        if ($inputtime != -1) {
            $maxTime = TIMESTAMP;
            $minTime = TIMESTAMP - $inputtime * 24 * 60 * 60;
            $searchCondition .= " rc.inputtime>='$minTime' AND rc.inputtime<='$maxTime' AND ";
        }
        if ($contact != -1) {
            $searchCondition .= " rc.contact='$contact' AND ";
        }
        if ($purpose != -1) {
            $searchCondition .= " rc.purpose='$purpose' AND ";
        }
        $searchCondition = empty($searchCondition) ? '' : substr($searchCondition, 0, -4);
        return $condition . $searchCondition;
    }

    /**
     * 链接Interview控制器搜索条件
     * @param array $search
     * @param string $condition
     * @return string
     */
    public static function joinInterviewSearchCondition(array $search, $condition)
    {
        $searchCondition = '';

        $realname = $search['realname'];
        $interviewtime = $search['interviewtime'];
        $interviewer = implode(',', StringUtil::getId($search['interviewer']));
        $type = $search['type'];

        if (!empty($realname)) {
            $searchCondition .= " rd.realname LIKE '%$realname%' AND ";
        }
        if ($interviewtime != -1) {
            $maxTime = TIMESTAMP;
            $minTime = TIMESTAMP - $interviewtime * 24 * 60 * 60;
            $searchCondition .= " ri.interviewtime>='$minTime' AND ri.interviewtime<='$maxTime' AND ";
        }
        if (!empty($interviewer)) {
            $searchCondition .= " ri.interviewer='$interviewer' AND ";
        }
        if ($type != -1) {
            $searchCondition .= " ri.type='$type' AND ";
        }
        $searchCondition = empty($searchCondition) ? '' : substr($searchCondition, 0, -4);
        return $condition . $searchCondition;
    }

    /**
     * 链接Bgchecks控制器搜索条件
     * @param array $search
     * @param string $condition
     * @return string
     */
    public static function joinBgchecksSearchCondition(array $search, $condition)
    {
        $searchCondition = '';

        $realname = $search['realname'];
        $company = $search['company'];
        $position = $search['position'];
        $entrytime = $search['entrytime'];
        $quittime = $search['quittime'];

        if (!empty($realname)) {
            $searchCondition .= " rd.realname LIKE '%$realname%' AND ";
        }
        if (!empty($company)) {
            $searchCondition .= "rb.company LIKE '%$company%' AND ";
        }
        if (!empty($position)) {
            $searchCondition .= "rb.position LIKE '%$position%' AND ";
        }
        if (!empty($entrytime)) {
            $entrytime = strtotime($entrytime);
            $searchCondition .= " rb.entrytime>='$entrytime' AND ";
        }
        if (!empty($quittime)) {
            $quittime = strtotime($quittime);
            $searchCondition .= " rb.quittime>='$quittime' AND ";
        }
        $searchCondition = empty($searchCondition) ? '' : substr($searchCondition, 0, -4);
        return $condition . $searchCondition;
    }

    /**
     * 编码转换
     * @param string $str 要转码的字符
     * @param string $in_charset 输入字符集
     * @param string $out_charset 输出字符集
     */
    public static function diconv($str, $in_charset, $out_charset = CHARSET)
    {
        $in_charset = strtoupper($in_charset);
        $out_charset = strtoupper($out_charset);
        if (empty($str) || $in_charset == $out_charset || is_null($out_charset)) {
            return $str;
        }
        $out = '';
        if (function_exists('iconv')) {
            $out = iconv($in_charset, $out_charset . '//IGNORE', $str);
        } elseif (function_exists('mb_convert_encoding')) {
            $out = mb_convert_encoding($str, $out_charset, $in_charset);
        }
        return $out;
    }

    /**
     * 把对象转为数组
     * @param type $e
     * @return type
     */
    public static function objectToArray($e)
    {
        $e = (array)$e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource')
                return;
            if (gettype($v) == 'object' || gettype($v) == 'array')
                $e[$k] = (array)self::objectToArray($v);
        }
        return $e;
    }

}
