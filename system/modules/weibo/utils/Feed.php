<?php

namespace application\modules\weibo\utils;

use application\core\utils\StringUtil;
use application\modules\department\model\Department;
use application\modules\message\model\Feed as FeedModel;
use application\modules\user\model\User;
use application\modules\weibo\core as WbCore;

class Feed
{

    /**
     * 推送模块动态
     * @param integer $uid 动态作者
     * @param string $module 产生动态的模块
     * @param string $table 产生动态的资源表
     * @param integer $rowid 资源表ID
     * @param array $data 推送动态所需的数据数组
     * @param string $type 动态类型 （只支持post与postimage,不支持转发）
     * @return integer 推送成功与否
     */
    public static function pushFeed($uid, $module, $table, $rowid, $data, $type = 'post')
    {
        // 权限
        if (empty($data['userid']) &&
            empty($data['deptid']) &&
            empty($data['positionid']) &&
            empty($data['roleid'])
        ) {
            $data['view'] = 0;
        } else {
            $data['view'] = 3;
        }
        if (FeedModel::model()->put($uid, $module, $type, $data, $rowid, $table)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获得查看范围的查询语句
     * @param integer $uid 查看人的用户ID
     * @param string $tableprefix 可能出现的表前缀，在联表查询时可能会用到
     * @return string 针对uid的微博查看范围查询语句
     */
    public static function getViewCondition($uid, $tableprefix = '')
    {
        $user = User::model()->fetchByUid($uid);
        $alldowndeptid = Department::model()->fetchChildIdByDeptids($user['alldeptid']);
        $deptids = StringUtil::filterStr($user['alldeptid'] . ',' . $alldowndeptid);
        $custom = sprintf("(FIND_IN_SET('%d',{$tableprefix}userid) OR FIND_IN_SET('{$deptids}',{$tableprefix}deptid) OR FIND_IN_SET('%s',{$tableprefix}positionid) OR FIND_IN_SET('%s',{$tableprefix}roleid)) ", $uid, $user['allposid'], $user['allroleid']);
        $condition = "({$tableprefix}view = 0 OR ({$tableprefix}view = 1 AND {$tableprefix}uid = {$uid}) OR FIND_IN_SET('{$deptids}',{$tableprefix}deptid) OR {$tableprefix}deptid = {$user['deptid']} OR {$tableprefix}deptid = 'alldept' OR {$tableprefix}roleid = {$user['roleid']} OR {$custom})";
        return $condition;
    }

    /**
     * 获得指定feed与指定$uid之间是否有可见关系
     * @param integer $feedid 动态ID
     * @param integer $uid 用户ID
     * @return boolean true为有权限，false为无权限
     */
    public static function hasView($feedid, $uid)
    {
        $feed = FeedModel::model()->get($feedid);
        $feedUser = User::model()->fetchByUid($feed['uid']);
        $user = User::model()->fetchByUid($uid);
        if ($feed && $feed['view'] !== WbCore\WbConst::SELF_VIEW_SCOPE) {
            $alldowndeptid = Department::model()->fetchChildIdByDeptids($feedUser['alldeptid']);
            $allupdeptid = Department::model()->queryDept($feedUser['alldeptid']);
            $fuDeptIds = StringUtil::filterStr($feedUser['alldeptid'] . ',' . $alldowndeptid);
            $deptIds = StringUtil::filterStr($user['alldeptid'] . ',' . $allupdeptid);
            if ($feed['view'] == WbCore\WbConst::ALL_VIEW_SCOPE) {
                return true;
            } else if ($feed['view'] == WbCore\WbConst::SELFDEPT_VIEW_SCOPE) {
                if (StringUtil::findIn($fuDeptIds, $deptIds)) {
                    return true;
                }
            } else {
                if (StringUtil::findIn($feed['userid'], $uid)) {
                    return true;
                }
                if (StringUtil::findIn($feed['positionid'], $user['allposid'])) {
                    return true;
                }
                if (StringUtil::findIn($fuDeptIds, $deptIds)) {
                    return true;
                }
            }
        }
        return false;
    }

}
