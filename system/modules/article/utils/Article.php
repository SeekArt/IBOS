<?php

/**
 * 信息中心模块------ 文章工具类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 信息中心模块------  文章工具类
 * @package application.modules.article.model
 * @version $Id: Article.php 8946 2016-11-05 05:44:50Z php_lwd $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\article\utils;

use application\core\utils\ArrayUtil;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\model\Article as ArticleModel;
use application\modules\article\model\ArticleReader;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\department\model\Department;
use application\modules\department\model\DepartmentRelated;
use application\modules\user\model\User;
use CHtml;


class Article
{

    //全部，这里包括已读和未读
    const  TYPE_ALL = 'all';
    //未读
    const TYPE_UNREAD = 'unread';
    //已读
    const TYPE_READ = 'read';
    //待审核
    const TYPE_APPROVAL = 'approval';
    //草稿
    const TYPE_DRAFT = 'draft';
    //已发布
    const TYPE_PUBLISH = "publish";
    //被退回
    const TYPE_REBACK_TO = "reback_to";
    //待我审核
    const TYPE_WAIT = "wait";
    //我已通过
    const TYPE_PASSED = "passed";
    //被我退回
    const TYPE_REBACK_FROM = "reback_from";

    //下面这几种情况主要是兼容h5的新闻接口，
    //还有一个草稿箱，已经在上面定义
    //未读
    const TYPE_NEW = 'new';
    //已读
    const TYPE_OLD = 'old';
    //待审核
    const TYPE_NOTALLOW = 'notallow';
    // 公开状态数字
    const TYPE_ALLOW_NUM = 1;
    // 待审核状态数字
    const TYPE_NOTALLOW_NUM = 2;
    // 草稿状态数字
    const TYPE_DRAFT_NUM = 3;

    /*
     * 列表组合数据主要是全部，已读，未读这三种状态下的条件
     */
    public static function getListCondition($type, $uid, $catid = 0, $keyword = "")
    {
        $user = User::model()->fetchByUid($uid);
        $upDeptid = Department::model()->queryDept($user['deptid']);
        $allDeptId = array_filter(array_unique(explode(',', $upDeptid . "," . $user['alldeptid'])));
        $deptConditionArray = array();
        if (count($allDeptId) > 0) {
            foreach ($allDeptId as $deptId) {
                $deptConditionArray[] = "FIND_IN_SET('$deptId',`deptid`)";
            }
            $deptCondition = implode(' OR ', $deptConditionArray);
        } else {
            $deptCondition = "FIND_IN_SET('',`deptid`)";
        }
        //主要考虑到辅助的问题
        $posCon = '';
        $pos = explode(',', $user['allposid']);
        for ($i = 0; $i < count($pos); $i++) {
            $posCon .= "FIND_IN_SET('{$pos[$i]}',`positionid`) OR ";
        }
        $roleCon = '';
        $role = explode(',', $user['allroleid']);
        for ($i = 0; $i < count($role); $i++) {
            $roleCon .= "FIND_IN_SET('{$role[$i]}',`roleid`) OR ";
        }
        $scopeCondition = "(`deptid` = 'alldept' OR "
            . "{$deptCondition} OR FIND_IN_SET('{$user['deptid']}',`deptid`) OR "
            . "FIND_IN_SET('{$user['positionid']}',`positionid`) OR "
            . $posCon
            . $roleCon
            . "FIND_IN_SET('{$uid}',uid) OR FIND_IN_SET('{$user['roleid']}',`roleid`)) AND `status` = 1 ";
        if (!empty($catid)) {
            $scopeCondition .= "AND `catid` IN ($catid)";
        }
        if ($type == self::TYPE_ALL) {
            $condition = $scopeCondition;
        } else {
            $typeWhere = self::joinReadAndUnReadCondition($type, $uid, $catid);
            $condition = $typeWhere . ' AND ' . $scopeCondition;
        }
        if (!empty($keyword)) {//这里是如果有搜索关键字，就说明是搜索功能
            $condition .= "AND  `subject` LIKE '%{$keyword}%' ";
        }
        return $condition;
    }

    /**
     * 获取类型条件和all。read和unread进行拼接
     * @param string $type
     * @param integer $uid
     * @param integer $catid
     * @return string
     */
    public static function joinReadAndUnReadCondition($type, $uid, $catid = 0)
    {
        $typeWhere = '';
        // 根据uid查询所有已读新闻articleid
        $articleidArr = ArticleReader::model()->fetchArticleidsByUid($uid);
        if ($type == self::TYPE_UNREAD || $type == self::TYPE_READ) {
            $articleidsStr = implode(',', $articleidArr);
            $articleids = empty($articleidsStr) ? '-1' : $articleidsStr;
            $flag = $type == self::TYPE_UNREAD ? 'NOT' : '';
            $typeWhere = "articleid " . $flag . " IN($articleids) AND `status` = 1";
        }
        return $typeWhere;
    }

    /*
     * 列表组合数据，主要是我的投稿对应的数据，有草稿箱，已发布，审核中，被退回这四种状态
     */
    public static function getPublishCondition($type, $uid, $catid = 0, $keyword = "")
    {
        if ($type == self::TYPE_DRAFT) {
            $condtition = "`status` = 3 AND `author` = {$uid} ";
        } elseif ($type == self::TYPE_PUBLISH) {
            $condtition = "`status` = 1 AND `author` = {$uid} ";
        } elseif ($type == self::TYPE_APPROVAL) {
            $condtition = "`status` = 2 AND `author` = {$uid} ";
        } elseif (self::TYPE_REBACK_TO) {
            $condtition = "`status` = 0 AND `author` = {$uid} ";
        }
        if (!empty($catid)) {
            $condtition .= "AND `catid` IN ($catid) ";
        }
        if (!empty($keyword)) {
            $condtition .= "AND `subject` LIKE '%{$keyword}%' ";
        }
        return $condtition;
    }

    //列表组合数据，主要是我的投稿里面的待我审核，我已通过，被我退回
    public static function getVerifyCondition($type, $uid, $catid = 0, $keyword = "")
    {
        if ($type == self::TYPE_WAIT) {
            $condition = "FIND_IN_SET('{$uid}',`approver`) AND `status` = 2 ";
        } elseif ($type == self::TYPE_PASSED) {
            $record = ApprovalRecord::model()->fetchAll("status IN (1,2) AND module = :module AND uid = :uid",
                array(':module' => 'article', ':uid' => $uid));
            $passId = ArrayUtil::getColumn($record, 'relateid');
            $passId = !empty($passId) ? array_unique($passId) : '';
            $passId = is_array($passId) ? implode(',', $passId) : 0;
            $condition = "`articleid` IN ($passId) ";
        } elseif ($type == self::TYPE_REBACK_FROM) {
            $record = ApprovalRecord::model()->fetchAll("status = :status AND module = :module AND uid = :uid",
                array(':status' => 0, ':module' => 'article', ':uid' => $uid));
            $backId = ArrayUtil::getColumn($record, 'relateid');
            $backId = !empty($backId) ? array_unique($backId) : '';
            $backId = is_array($backId) ? implode(',', $backId) : 0;
            $condition = "`articleid` IN ({$backId}) ";
        }
        if (!empty($catid)) {
            $condition .= "AND `catid` IN ($catid) ";
        }
        if (!empty($keyword)) {
            $condition .= "AND `subject` LIKE '%{$keyword}%' ";
        }
        return $condition;
    }

    /**
     * 判断信息中心的阅读权限
     * @param integer $uid 用户访问uid
     * @param array $data 文章数据
     * @return boolean
     */
    public static function checkReadScope($uid, $data)
    {
        if ($data['deptid'] == 'alldept') {
            return true;
        }
        if ($uid == $data['author']) {
            return true;
        }
        //如果都为空，返回true
        if (empty($data['deptid']) && empty($data['positionid']) && empty($data['uid'])) {
            return true;
        }
        if (empty($data['deptid']) && empty($data['positionid']) && empty($data['roleid'])) {
            return true;
        }
        //得到用户的部门id,如果该id存在于文章部门范围之内,返回true
        $user = User::model()->fetch(array(
            'select' => array('deptid', 'positionid'),
            'condition' => 'uid=:uid',
            'params' => array(':uid' => $uid)
        ));
        $departRelated = DepartmentRelated::model()->fetchAllDeptIdByUid($uid);
        //取得文章部门范围id以及他的子id
        $childDeptid = Department::model()->fetchChildIdByDeptids($data['deptid']);
        if (StringUtil::findIn($user['deptid'] . ',' . implode(',', $departRelated),
            $childDeptid . ',' . $data['deptid'])
        ) {
            return true;
        }
        //考虑辅助部门的时候
        if (static::isDepterment($data['deptid'], $user['deptid'])) {
            return true;
        }
        //取得文章岗位范围Id与用户岗位相比较
        if (StringUtil::findIn($data['positionid'], $user['positionid'])) {
            return true;
        }
        //考虑辅助岗位的时候
        if (static::isPosition($data['positionid'], $uid)) {
            return true;
        }
        if (StringUtil::findIn($data['uid'], $uid)) {
            return true;
        }
        if (StringUtil::findIn($data['roleid'], $user['roleid'])) {
            return true;
        }
        //辅助辅助角色的时候
        if (static::isRole($data['roleid'], $user['roleid'])) {
            return true;
        }
        return false;
    }

    /*
     * 判断文章的发布岗位是否在用户的辅助岗位上
     */
    public static function isPosition($pid = 0, $uid)
    {
        $positionId = Ibos::app()->db->createCommand()
            ->select('positionid')
            ->from('{{position_related}}')
            ->where('uid = :uid', array(':uid' => $uid))->queryAll();
        for ($i = 0; $i < count($positionId); $i++) {
            if ($positionId[$i]['positionid'] == $pid) {
                return true;
            }
        }
        return false;
    }

    /*
     * 判断文章的发布角色是否在用户的辅助角色上
     */
    public static function isRole($rid, $uid)
    {
        $ruleId = Ibos::app()->db->createCommand()
            ->select('roleid')
            ->from('{{role_related}}')
            ->where('uid = :uid', array(':uid' => $uid))->queryAll();
        for ($i = 0; $i < count($ruleId); $i++) {
            if ($ruleId[$i]['roleid'] == $rid) {
                return true;
            }
        }
        return false;
    }

    /*
     * 判断文章的发布部门是否在用户的辅助部门上
     */
    public static function isDepterment($did, $uid)
    {
        $deptermentId = Ibos::app()->db->createCommand()
            ->select('deptid')
            ->from('{{department_related}}')
            ->where('uid = :uid', array(':uid' => $uid))->queryAll();
        for ($i = 0; $i < count($deptermentId); $i++) {
            if ($deptermentId[$i]['deptid'] == $did) {
                return true;
            }
        }
        return false;
    }

    /**
     * 取得在发布范围内的uid数组
     * @param array $data
     * @return array
     */
    public static function getScopeUidArr($data)
    {
        $string = '';
        $all = false;
        if (!empty($data['deptid'])) {
            foreach (explode(',', $data['deptid']) as $deptid) {
                if ($deptid == 'alldept') {
                    $all = true;
                    $string = 'c_0';
                } else {
                    $string .= ',d_' . $deptid;
                }
            }
        }
        if (false === $all && !empty($data['positionid'])) {
            foreach (explode(',', $data['positionid']) as $positionid) {
                $string .= ',p_' . $positionid;
            }
        }
        if (false === $all && !empty($data['uid'])) {
            foreach (explode(',', $data['uid']) as $uid) {
                $string .= ',u_' . $uid;
            }
        }
        if (false === $all && !empty($data['roleid'])) {
            foreach (explode(',', $data['roleid']) as $roleid) {
                $string .= ',r_' . $roleid;
            }
        }
        $uidArray = StringUtil::getUidAByUDPX(trim($string, ','), true, false, true);
        return $uidArray;
    }

    /**
     * 取出源数据中$field的值，用$join分割合并成字符串
     * @param string $str 逗号分割的字符串
     * @param array $data 源数据
     * @param type $field 要取出的字段
     */
    public static function joinStringByArray($str, $data, $field, $join)
    {
        if (empty($str)) {
            return '';
        }
        $result = array();
        $strArr = explode(',', $str);
        foreach ($strArr as $value) {
            if (array_key_exists($value, $data)) {
                $result[] = $data[$value][$field];
            }
        }
        $resultStr = implode($join, $result);
        return $resultStr;
    }

    /**
     * 处理请求的高亮数据，过滤无用数据
     * $highLight['highlightstyle']='bold,color,italic,underline'
     */
    public static function processHighLightRequestData($data)
    {
        $highLight = array();
        $highLight['highlightstyle'] = '';
        if (!empty($data['endTime'])) {
            $highLight['highlightendtime'] = strtotime($data['endTime']) + 24 * 60 * 60 - 1;
        }
        if (empty($data['bold'])) {
            $data['bold'] = 0;
        }
        $highLight['highlightstyle'] = $highLight['highlightstyle'] . $data['bold'] . ',';
        if (empty($data['color'])) {
            $data['color'] = '';
        }
        $highLight['highlightstyle'] = $highLight['highlightstyle'] . $data['color'] . ',';
        if (empty($data['italic'])) {
            $data['italic'] = 0;
        }
        $highLight['highlightstyle'] = $highLight['highlightstyle'] . $data['italic'] . ',';
        if (empty($data['underline'])) {
            $data['underline'] = 0;
        }
        $highLight['highlightstyle'] = $highLight['highlightstyle'] . $data['underline'] . ',';
        $highLight['highlightstyle'] = substr($highLight['highlightstyle'], 0,
            strlen($highLight['highlightstyle']) - 1);
        if (!empty($highLight['highlightendtime']) || strlen($highLight['highlightstyle']) > 3) {
            $highLight['ishighlight'] = 1;
        } else {
            $highLight['ishighlight'] = 0;
        }
        return $highLight;
    }

    /**
     * 对分类列表进行处理，增加级别处理
     * @staticvar array $result
     * @param type $list
     * @param type $pid
     * @param type $level
     * @return type
     */
    public static function getCategoryList($list, $pid, $level)
    {
        static $result = array();
        foreach ($list as $category) {
            if ($category['pid'] == $pid) {
                $category['level'] = $level;
                $result[] = $category;
                array_merge($result, self::getCategoryList($list, $category['catid'], $level + 1));
            }
        }
        return $result;
    }

    //下面的这些方法都是为了兼容h5的新闻接口
    /**
     * 列表查询条件组合
     * @param string $type 全部、未读、已读、未审核、草稿 这几种类型
     * @param string $catid 分类id 包括当前分类及它的子类以','号分割的字符串
     * @param string $condition 其他的查询条件
     * @return array $condition 组合好的查询条件
     */
    public static function joinListCondition($type, $uid, $catid = 0, $condition = '')
    {
        $user = User::model()->fetchByUid($uid);;
        $upDeptid = Department::model()->queryDept($user['deptid']);
        $typeWhere = self::joinTypeCondition($type, $uid, $catid);
        if (!empty($condition)) {
            $condition .= " AND " . $typeWhere;
        } else {
            $condition = $typeWhere;
        }
        //加上阅读权限判断
        $allDeptId = array_filter(array_unique(explode(',', $upDeptid . "," . $user['alldeptid'])));
        $deptConditionArray = array();
        if (count($allDeptId) > 0) {
            foreach ($allDeptId as $deptId) {
                $deptConditionArray[] = "FIND_IN_SET( '$deptId',deptid)";
            }
            $deptCondition = implode(' OR ', $deptConditionArray);
        } else {
            $deptCondition = "FIND_IN_SET( '',deptid)";
        }
        //主要是考虑到如果有辅助部门和辅助岗位和辅助角色的时候需要有列表显示
        $posCon = '';
        $pos = explode(',', $user['allposid']);
        for ($i = 0; $i < count($pos); $i++) {
            $posCon .= "FIND_IN_SET('{$pos[$i]}',positionid) OR ";
        }
        $roleCon = '';
        $role = explode(',', $user['allroleid']);
        for ($i = 0; $i < count($role); $i++) {
            $roleCon .= "FIND_IN_SET('{$role[$i]}',roleid) OR ";
        }
        $scopeCondition = " ( ((deptid='alldept' OR "
            . "{$deptCondition} OR FIND_IN_SET('{$user['deptid']}',deptid) OR "
            . "FIND_IN_SET('{$user['positionid']}',positionid) OR "
            . $posCon
            . $roleCon
            . "FIND_IN_SET('{$uid}',uid) OR "
            . "FIND_IN_SET('{$user['roleid']}',roleid)) OR "
            //. "(`author`='{$uid}') OR (`approver`='{$uid}')) ";
            . "(`author`='{$uid}') OR (FIND_IN_SET('{$uid}', approver))) ";
        // 如果新闻当前状态为：未审核
        // 审核人可以看到所有属于他的所有未审核新闻
        if (self::TYPE_NOTALLOW === $type) {
            $scopeCondition .= " OR {$typeWhere} ";
        }
        $scopeCondition .= " ) ";
        $condition .= " AND " . $scopeCondition;
        if (!empty($catid)) {
            $condition .= " AND catid IN ($catid)";
        }

        // 只有对应步骤的审核人才能看到未审核新闻
        if (self::TYPE_NOTALLOW === $type) {
            $condition .= " AND (approver IN (0, {$uid}) OR (author = {$uid}) OR FIND_IN_SET('{$uid}', approver)) ";
        }
        return $condition;
    }

    /**
     * 获取类型条件
     * @param string $type
     * @param integer $uid
     * @param integer $catid
     * @return string
     */
    public static function joinTypeCondition($type, $uid, $catid = 0)
    {
        $typeWhere = '';
        // 根据uid查询所有已读新闻articleid
        $articleidArr = ArticleReader::model()->fetchArticleidsByUid($uid);
        if ($type == self::TYPE_NEW || $type == self::TYPE_OLD) {
            $articleidsStr = implode(',', $articleidArr);
            $articleids = empty($articleidsStr) ? '-1' : $articleidsStr;
            $flag = $type == self::TYPE_NEW ? 'NOT' : '';
            $typeWhere = " articleid " . $flag . " IN($articleids) AND status=1";
        } elseif ($type == self::TYPE_NOTALLOW) {
            $artIds = ArticleModel::model()->fetchUnApprovalArtIds($catid, $uid);
            $artIdStr = implode(',', $artIds);
            $typeWhere = "FIND_IN_SET(`articleid`, '{$artIdStr}')";
        } elseif ($type == self::TYPE_DRAFT) {
            $typeWhere = "status='3' AND author='$uid'";
        } else {
            $typeWhere = "status ='1' AND approver!=0";
        }
        return $typeWhere;
    }

    /**
     * 组合搜索条件
     * @param array $search 查询数据
     * @param string $condition 条件
     * @return string 新的查询条件
     */
    public static function joinSearchCondition(array $search, $condition)
    {
        $searchCondition = '';

        $keyword = $search['keyword'];
        // 添加对keyword的转义，防止SQL错误
        $keyword = CHtml::encode($search['keyword']);
        $starttime = $search['starttime'];
        $endtime = $search['endtime'];

        if (!empty($keyword)) {
            $searchCondition .= " subject LIKE '%$keyword%' AND ";
        }
        if (!empty($starttime)) {
            $starttime = strtotime($starttime);
            $searchCondition .= " addtime>=$starttime AND";
        }
        if (!empty($endtime)) {
            $endtime = strtotime($endtime) + 24 * 60 * 60;
            $searchCondition .= " addtime<=$endtime AND";
        }
        $newCondition = empty($searchCondition) ? '' : substr($searchCondition, 0, -4);
        return $condition . $newCondition;
    }

    /**
     * 提取用户所给字符串 $str 里的日期，将其转换为时间戳格式。（如果 $str 未匹配到日期，则返回当前时间）
     *
     * @param String $str 待转换的字符串
     * @return Int Unix 时间戳
     */
    public static function formatToTimestamp($str)
    {
        $datePattern = '/\d{4}-\d{1,2}-\d{1,2}/';
        preg_match($datePattern, $str, $matches);

        if (isset($matches[0])) {
            return strtotime($matches[0]);
        }
        return time();
    }
}
