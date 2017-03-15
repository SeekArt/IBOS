<?php

/**
 * 自动同步计划任务
 */
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Cache;
use application\modules\dashboard\utils\CoSync;
use application\modules\main\model\Setting;
use application\modules\message\core\co\CoApi;
use application\modules\message\core\co\CodeApi;
use application\modules\user\model\UserBinding;
use application\modules\user\model\User;

$coinfo = StringUtil::utf8Unserialize(Setting::model()->fetchSettingValueByKey('coinfo'));
$coBindType = 'ibos';
if (!isset($coinfo['corpid'])) {
    Setting::model()->updateSettingValueByKey('cobinding', 0);
}
if (Setting::model()->fetchSettingValueByKey('cobinding') == 0) {
    return true;
} else {
    // 根据酷办公需要的格式，获取 IBOS 的用户列表
    // $userList = getUserList();
    // 获取用户同步数据列表
    $syncList = getSyncList($coinfo['corpid']);
    // 准备 Cache 表的相关 key 值记录
    readySync();
    // 将需要同步的用户数据列表存放在 Cache 表
    $syncList['third']['delete'] = removeAdminUidFromIbosRemoveList($syncList['third']['delete']);
    Cache::model()->updateByPk('iboscreatelist', array('cachevalue' => serialize($syncList['third']['add'])));
    Cache::model()->updateByPk('ibosremovelist', array('cachevalue' => serialize($syncList['third']['delete'])));
    $coids = User::model()->fetchUnbind(50);
    $removeids = User::model()->fetchDeletebind();
    $coCreateList = User::model()->findThreeByUid($coids);
    $coRemoveList = User::model()->findThreeByUid($removeids);
    // Cache::model()->updateByPk('cocreatelist', array('cachevalue' => serialize($syncList['co']['add'])));
    // Cache::model()->updateByPk('coremovelist', array('cachevalue' => serialize($syncList['co']['delete'])));
    // 初始化同步需要的相关数据
    $ibosCreateList = Cache::model()->fetchArrayByPk('iboscreatelist');
    $ibosRemoveList = Cache::model()->fetchArrayByPk('ibosremovelist');
    // $coCreateList = Cache::model()->fetchArrayByPk('cocreatelist');
    // $coRemoveList = Cache::model()->fetchArrayByPk('coremovelist');
    $removeIdenticalRes = removeIdenticalByMobile($ibosCreateList, $coCreateList);
    $ibosCreateList = $removeIdenticalRes['userList_1'];
    $coCreateList = $removeIdenticalRes['userList_2'];
    $relationList = $removeIdenticalRes['identicalList'];
    // 根据酷办公的用户变动，对应修改 IBOS 的用户保持同步关系
    $ibosCreateRes = CoSync::createUserAndBindRelation($ibosCreateList);
    $ibosRemoveRes = CoSync::removeUserAndBindRelation($ibosRemoveList);
    // IBOS 新增 & 删除 绑定关系后，需要调用酷办公对应接口，让酷办公也做相应的绑定关系增删
    coCreateRelation($ibosCreateRes, $coinfo['corpid']);
    // var_dump($ibosRemoveList, $ibosRemoveRes);
    coRemoveRelation($ibosRemoveRes, $coinfo['corpid']);
    // 根据 IBOS 的用户变动，调用酷办公 新增 & 移除 用户接口
    // 并根据接口返回数据 新增 & 删除 对应的绑定关系记录
    $coCreateRes = createCoUser($coCreateList, $coinfo['corpid']);
    $coRemoveRes = removeCoUser($coRemoveList, $coinfo['corpid']);
    // 先添加 IBOS 绑定关系，再添加酷办公绑定关系
    // 根据调用 removeIdenticalByMobile 时的数组顺序 uid_1 是酷办公用户 uid，uid_2 是 IBOS 用户 uid
    if (!empty($relationList)) {
        $coRelation = array();
        foreach ($relationList as $relation) {
            addBindRelation($relation['uid_2'], $relation['uid_1']['guid']);
            $coRelation[] = array('uid' => $relation['uid_1']['uid'], 'bindvalue' => $relation['uid_2']);
        }
        if (!empty($coRelation)) {
            coCreateRelation($coRelation);
        }
    }
    return true;
}

/**
 * 获取 IBOS 目前启用状态下的用户列表
 * @return arrya 用户列表
 */
function getUserList()
{
    $userList = User::model()->fetchAll('status = 0');
    foreach ($userList as $user) {
        $result[] = array(
            'uid' => $user['uid'],
            'realname' => $user['realname'],
            'mobile' => $user['mobile'],
        );
    }
    return isset($result) ? $result : array();
}

/**
 * 把 IBOS 用户列表交给酷办公处理，返回同步用户列表
 * @param  array $userList IBOS 用户列表
 * @return array           同步用户列表
 */
function getSyncList($corpid)
{
    // 查出Ibos新增和禁用的用户 
    $add = $delete = '';
    $result = array();
    $disabled = User::USER_STATUS_ABANDONED;
    $count = User::model()->find(" status != {$disabled} ")->count();
    $delete = User::model()->CountDelete();
    $add = User::model()->CountUnbind();
    //请求酷办公的用户数据
    $post = array(
        'type' => $coBindType,
        'corpid' => $corpid,
    );
    $getSync = CoApi::getInstance()->getCoUsers($post);
    if ($getSync['errorcode'] == CodeApi::SUCCESS) {
        $result['data'] = array(
            'co' => array(
                'add' => $add['0'],
                'delete' => $delete['0'],
                'count' => $count
            ),
            'third' => $getSync['data']['third']
        );
        return $result['data'];
    } else {
        die;
    }

    // $post = array(
    //     'type' => $coBindType,
    //     'corpid' => $corpid,
    //     'userlist' => $userList,
    // );
    // $getSyncListRes = CoApi::getInstance()->getDiffUsers($post);
    // if ($getSyncListRes['errorcode'] == CodeApi::SUCCESS) {
    //     return $getSyncListRes['data'];
    // } else {
    //     die;
    // }
}

/**
 * 过滤酷办公移除用户列表中的 IBOS 超级管理员用户
 * @param  array $ibosRemoveList 从差异化分析接口返回的酷办公移除用户列表
 * @return array                 过滤后的用户列表
 */
function removeAdminUidFromIbosRemoveList($ibosRemoveList)
{
    $bindvalue = UserBinding::model()->fetchBindValue(1, 'co');
    if (!empty($bindvalue)) {
        foreach ($ibosRemoveList as $key => $user) {
            if ($bindvalue == $user['uid']) {
                unset($ibosRemoveList[$key]);
            }
        }
    }
    return $ibosRemoveList;
}

/**
 * 检查 Cache 表中是否有对应 key 记录
 * 没有就添加
 * iboscreatelist 需要 IBOS 新增的用户
 * ibosremovelist 需要 IBOS 移除的用户
 * cocreatelist 需要酷办公新增的用户
 * coremovelist 需要酷办公移除的用户
 */
function readySync()
{
    // 准备同步结果数据记录字段
    if (Cache::model()->fetchArrayByPk('successinfo') === false) {
        Cache::model()->add(array('cachekey' => 'successinfo', 'cachevalue' => serialize(array())));
    }
    // 需要 IBOS 新增的用户
    if (Cache::model()->fetchArrayByPk('iboscreatelist') === false) {
        Cache::model()->add(array('cachekey' => 'iboscreatelist', 'cachevalue' => serialize(array())));
    }
    // 需要 IBOS 移除的用户
    if (Cache::model()->fetchArrayByPk('ibosremovelist') === false) {
        Cache::model()->add(array('cachekey' => 'ibosremovelist', 'cachevalue' => serialize(array())));
    }
    // 需要酷办公新增的用户
    if (Cache::model()->fetchArrayByPk('cocreatelist') === false) {
        Cache::model()->add(array('cachekey' => 'cocreatelist', 'cachevalue' => serialize(array())));
    }
    // 需要酷办公移除的用户
    if (Cache::model()->fetchArrayByPk('coremovelist') === false) {
        Cache::model()->add(array('cachekey' => 'coremovelist', 'cachevalue' => serialize(array())));
    }
}

/**
 * 比较两个用户数组中的手机号，相同的话从数组中去除，将手机号相同的两个数组中用户 uid 组成第三个数组
 * 第三个数组直接表示的是需要绑定的 uid 关系 ['uid_1' => $user_1['uid'], 'uid_2' => $user_2['uid']]
 * 返回去重后的两个用户数组 & uid 组成的第三个数组
 * @param  array $userList_1 包含 mobile 的用户数组
 * @param  array $userList_2 包含 mobile 的用户数组
 * @return array
 */
function removeIdenticalByMobile($userList_1, $userList_2)
{
    $identical = array();
    if (!is_array($userList_1) || !is_array($userList_2)) {
        return array();
    }
    foreach ($userList_1 as $key_1 => $user_1) {
        foreach ($userList_2 as $key_2 => $user_2) {
            if ($user_1['mobile'] === $user_2['mobile']) {
                $identical[] = array('uid_1' => array('uid' => $user_1['uid'], 'guid' => $user_1['guid']), 'uid_2' => $user_2['uid']);
                unset($userList_1[$key_1]);
                unset($userList_2[$key_2]);
                break;
            }
        }
    }
    return array(
        'userList_1' => $userList_1,
        'userList_2' => $userList_2,
        'identicalList' => $identical,
    );
}

/**
 * 调用酷办公接口
 * 根据 IBOS 提供的绑定关系列表，创建酷办公的绑定关系
 * array(
 *     array( 'uid' => [酷办公用户 uid], 'bindvalue' => [IBOS 用户 uid] ),
 *     array( 'uid' => [酷办公用户 uid], 'bindvalue' => [IBOS 用户 uid] ),
 *     array( 'uid' => [酷办公用户 uid], 'bindvalue' => [IBOS 用户 uid] ),
 *     ...
 * )
 * @param  array $relationCreateList IBOS 的绑定关系列表
 */
function coCreateRelation($relationCreateList, $corpid)
{
    $post = array(
        'type' => $coBindType,
        'corpid' => $corpid,
        'data' => $relationCreateList,
    );
    CoApi::getInstance()->createRelationByList($post);
}

/**
 * 调用酷办公接口
 * 根据 IBOS 提供的绑定关系列表，删除酷办公的绑定关系
 * array(
 *     array( 'uid' => [酷办公用户 uid], 'bindvalue' => [IBOS 用户 uid] ),
 *     array( 'uid' => [酷办公用户 uid], 'bindvalue' => [IBOS 用户 uid] ),
 *     array( 'uid' => [酷办公用户 uid], 'bindvalue' => [IBOS 用户 uid] ),
 *     ...
 * )
 * @param  array $relationRemoveList 需要酷办公删除的绑定关系列表
 * @return [type]                     [description]
 */
function coRemoveRelation($relationRemoveList, $corpid)
{
    $post = array(
        'type' => $coBindType,
        'corpid' => $corpid,
        'data' => $relationRemoveList,
    );
    CoApi::getInstance()->removeRelationByList($post);
}

/**
 * 调用酷办公创建用户接口，将 IBOS 新增的用户同步到酷办公
 * array(
 *     array(
 *         'uid' => [IBOS 用户 uid],
 *         'realname' => [IBOS 用户真实姓名],
 *         'mobile' => [IBOS 用户手机号]
 *     ),
 *     array(
 *         'uid' => [IBOS 用户 uid],
 *         'realname' => [IBOS 用户真实姓名],
 *         'mobile' => [IBOS 用户手机号]
 *     ),
 *     array(
 *         'uid' => [IBOS 用户 uid],
 *         'realname' => [IBOS 用户真实姓名],
 *         'mobile' => [IBOS 用户手机号]
 *     ),
 *     ...
 * )
 * @param  array $coreadysynclist IBOS 新增的用户列表
 * @return array
 */
function createCoUser($userCreateList, $corpid)
{
    $post = array(
        'type' => $coBindType,
        'corpid' => $corpid,
        'data' => $userCreateList,
    );
    $createCoUserRes = CoApi::getInstance()->createCoUserByList($post);
    // 调用接口成功，根据返回数据添加相应的绑定记录
    if ($createCoUserRes['errorcode'] == CodeApi::SUCCESS) {
        foreach ($createCoUserRes['data'] as $relation) {
            addBindRelation($relation['uid'], $relation['bindvalue']);
        }
        return $createCoUserRes['data'];
    }
}

/**
 * 调用酷办公移除用户接口，将 IBOS 移除的用户同步到酷办公
 * array(
 *     array(
 *         'uid' => [IBOS 用户 uid],
 *         'realname' => [IBOS 用户真实姓名],
 *         'mobile' => [IBOS 用户手机号]
 *     ),
 *     array(
 *         'uid' => [IBOS 用户 uid],
 *         'realname' => [IBOS 用户真实姓名],
 *         'mobile' => [IBOS 用户手机号]
 *     ),
 *     array(
 *         'uid' => [IBOS 用户 uid],
 *         'realname' => [IBOS 用户真实姓名],
 *         'mobile' => [IBOS 用户手机号]
 *     ),
 *     ...
 * )
 * @param  array $ibosRemoveList IBOS 移除的用户列表
 * @return array
 */
function removeCoUser($userRemoveList, $corpid)
{
    $post = array(
        'type' => $coBindType,
        'corpid' => $corpid,
        'data' => $userRemoveList,
    );
    $removeCoUserRes = CoApi::getInstance()->removeCoUserByList($post);
    // 调用接口成功，根据返回数据删除对应的绑定记录
    if ($removeCoUserRes['errorcode'] == CodeApi::SUCCESS) {
        foreach ($removeCoUserRes['data'] as $relation) {
            UserBinding::model()->deleteAll(sprintf("`uid` = %d AND `app` = 'co'", $relation['uid']));
        }
        return $removeCoUserRes['data'];
    }
}

/**
 * 添加绑定关系
 * @param integer $uid IBOS uid
 * @param integer $bindvalue 酷办公 uid
 * @return integer 成功数
 */
function addBindRelation($uid, $bindvalue)
{
    static $successNum = 0;
    $condition = "`uid` = :uid AND `app` = 'co'";
    $params = array(':uid' => $uid);
    $userBind = UserBinding::model()->fetch($condition, $params);
    if (!empty($userBind)) {
        UserBinding::model()->deleteAll(sprintf("`uid` = %d AND `app` = 'co'", $uid));
    }
    $addRes = UserBinding::model()->add(array('uid' => $uid, 'bindvalue' => $bindvalue, 'app' => 'co'));
    if ($addRes) {
        $successNum++;
    }
    return $successNum;
}
