<?php

/**
 * 酷办公同步用户以及组织架构控制器
 * CosyncController.class.file
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2015 IBOS Inc
 * @package application.modules.dashboard.controllers
 * @author Sam <gzxgs@ibos.com.cn>
 */

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\String;
use application\modules\dashboard\model\Cache;
use application\modules\dashboard\utils\CoSync;
use application\modules\department\model\Department as DepartmentModel;
use application\modules\main\model\Setting;
use application\modules\message\core\co\CoApi;
use application\modules\message\core\co\CodeApi;
use application\modules\position\model\Position;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;

class CosyncController extends CoController {
    // protected $aeskey;
    // protected $oaUrl;

    /**
     * 酷办公绑定需要提供的绑定类型
     * 在 IBOS 默认为 ibos
     * @var string
     */
    protected $coBindType;

    /**
     * 需要绑定的酷办公企业 ID
     * @var integer
     */
    protected $corpid;

    public function init() {
        parent::init();
        $coinfo = String::utf8Unserialize(Setting::model()->fetchSettingValueByKey('coinfo'));
        $this->coBindType = 'ibos';
        if (isset($coinfo['corpid'])):
            $this->corpid = $coinfo['corpid'];
        else:
            //这个判断是为了保证发布的6427版本之前的版本已经绑定酷办公的不会出现错误
            //如果是旧的绑定流程，coinfo里面就没有corpid，这里强制把cobinding标识改成未绑定状态
            Setting::model()->updateSettingValueByKey('cobinding', 0);
            return $this->redirect('cobinding/index');
        endif;
    }

    /**
     * 同步首页视图
     */
    public function actionIndex() {
        // 判断是后台访问还是安装访问
        // 后台访问比前台需要的数据多一点
        $isInstall = $this->verifyIsInstall();
        if (!empty($isInstall)) {
            $data = array(
                'co' => $isInstall['co'],
                'ibos' => $isInstall['ibos'],
            );
        }
        $data['isInstall'] = Env::getRequest('isInstall') != 1 ? 0 : 1;
        // 是否开启了自动同步并需要进行
        $isAutoSync = $this->isAutoSync();
        $data['pageInit'] = 'index';
        if ($isAutoSync) {
            $data['pageInit'] = 'sync';
        } else {
            $data['pageInit'] = 'index';
        }
        // 是否开启了自动同步
        $autoSync = String::utf8Unserialize(Setting::model()->fetchSettingValueByKey('autosync'));
        $data['autoSync'] = $autoSync['status'];
        $this->render('index', $data);
    }

    /**
     * 同步操作 ajax 调用
     */
    public function actionSync() {
        $autoSyncStatus = Env::getRequest('autoSync');
        $op = Env::getRequest('op');
        $isInstall = Env::getRequest('isInstall');
        $opList = array('init', 'buildRelation', 'syncIbosUser', 'syncCoUser');
        if (!in_array($op, $opList)) {
            // 先是不带 op 参数访问 sync 视图，将同步进行中的页面内容显示出来
            // 再根据 op 参数去进行对应的同步步骤 && 修改相应的显示内容
            if ($op === NULL) {
                // 判断是后台访问还是安装访问
                // 后台访问比前台需要的数据多一点
                $data = $this->verifyIsInstall();
                $data['isInstall'] = $isInstall;
                // $this->render( 'sync', $data );
                $this->ajaxReturn(array(
                    'status' => 1,
                    'data' => $data,
                    'op' => 'init',
                    'progress' => '0%',
                ));
            } else {
                $this->ajaxReturn(array(
                    'status' => 2,
                    'message' => '请求的参数有误',
                ));
            }
        }
        set_time_limit(120);
        // // 记录同步成功数据的数组
        $successInfo = Cache::model()->fetchArrayByPk('successinfo');
        // 初始化同步需要的相关数据
        $ibosCreateList = Cache::model()->fetchArrayByPk('iboscreatelist');
        $ibosRemoveList = Cache::model()->fetchArrayByPk('ibosremovelist');
        $coCreateList = Cache::model()->fetchArrayByPk('cocreatelist');
        $coRemoveList = Cache::model()->fetchArrayByPk('coremovelist');
        $removeIdenticalRes = $this->removeIdenticalByMobile($ibosCreateList, $coCreateList);
        $ibosCreateList = $removeIdenticalRes['userList_1'];
        $coCreateList = $removeIdenticalRes['userList_2'];
        $relationList = $removeIdenticalRes['identicalList'];
        switch ($op) {
            case 'init':
                // 开启/关闭 自动同步
                // $changeAutoSyncStatusRes = $this->changeAutoSyncStatus( $autoSyncStatus !== NULL ? $autoSyncStatus : 0 );
                // if ( $changeAutoSyncStatusRes === FALSE ) {
                // 	$this->ajaxReturn( array(
                // 		'status'	=> 2,
                // 		'message'	=> '开启/关闭 自动同步失败！',
                // 	) );
                // }
                // 准备同步结果数据记录字段
                if (Cache::model()->fetchArrayByPk('successinfo') === FALSE) {
                    Cache::model()->add(array('cachekey' => 'successinfo', 'cachevalue' => serialize(array())));
                } else {
                    Cache::model()->updateByPk('successinfo', array('cachevalue' => serialize(array())));
                }
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '初始化数据成功，请稍后...',
                    'op' => 'syncIbosUser',
                    'progress' => '20%',
                ));
                break;
            case 'syncIbosUser':
                // 根据酷办公的用户变动，对应修改 IBOS 的用户保持同步关系
                $ibosCreateRes = CoSync::createUserAndBindRelation($ibosCreateList);
                $ibosRemoveRes = CoSync::removeUserAndBindRelation($ibosRemoveList);
                // IBOS 新增 & 删除 绑定关系后，需要调用酷办公对应接口，让酷办公也做相应的绑定关系增删
                $this->coCreateRelation($ibosCreateRes);
                $this->coRemoveRelation($ibosRemoveRes);
                // 保存 IBOS 创建/启用、禁用 成功的数据
                $successInfo['ibosCreateNum'] = count($ibosCreateRes);
                $successInfo['ibosRemoveNum'] = count($ibosRemoveRes);
                Cache::model()->updateByPk('successinfo', array('cachevalue' => serialize($successInfo)));
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '开始同步用户，请稍后...',
                    'op' => 'syncCoUser',
                    'progress' => '45%',
                ));
                break;
            case 'syncCoUser':
                // 根据 IBOS 的用户变动，调用酷办公 新增 & 移除 用户接口
                // 并根据接口返回数据 新增 & 删除 对应的绑定关系记录
                $coCreateRes = $this->createCoUser($coCreateList);
                $coRemoveRes = $this->removeCoUser($coRemoveList);
                // 保存酷办公 创建/加入、移除 成功的数据
                $successInfo['coCreateNum'] = count($coCreateRes);
                $successInfo['coRemoveNum'] = count($coRemoveRes);
                Cache::model()->updateByPk('successinfo', array('cachevalue' => serialize($successInfo)));
                $this->ajaxReturn(array(
                    'status' => 1,
                    'message' => '开始建立绑定关系，请稍后...',
                    'op' => 'buildRelation',
                    'progress' => '70%',
                ));
                break;
            case 'buildRelation':
                // 实际上 IBOS 酷办公 用户创建、移除/禁用时已经对那部分用户进行了绑定
                // IBOS 与酷办公企业都已经存在的用户，直接添加绑定关系
                // 先添加 IBOS 绑定关系，再添加酷办公绑定关系
                // 根据调用 removeIdenticalByMobile 时的数组顺序 uid_1 是酷办公用户 uid，uid_2 是 IBOS 用户 uid
                $successInfo['addRelationNum'] = 0;
                if (!empty($relationList)) {
                    foreach ($relationList as $relation) {
                        $successInfo['addRelationNum'] = $this->addBindRelation($relation['uid_2'], $relation['uid_1']['guid']);
                        $coRelation[] = array('uid' => $relation['uid_1']['uid'], 'bindvalue' => $relation['uid_2']);
                    }
                    $this->coCreateRelation($coRelation);
                }
                // 统计同步结果，并返回
                $relationCount = UserBinding::model()->count("`app` = 'co'");
                $successInfo['syncCountNum'] = $relationCount;
                $autosync = String::utf8Unserialize(Setting::model()->fetchSettingValueByKey('autosync'));
                $autosync['lastsynctime'] = time();
                Setting::model()->updateSettingValueByKey('autosync', serialize($autosync));
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '同步成功！',
                    'data' => $successInfo,
                    'progress' => '100%',
                ));
                break;
            default :
                break;
        }
    }

    /**
     * 获取同步差异数据
     * @return ajax
     */
    public function actionGetSyncList() {
        // 根据酷办公需要的格式，获取 IBOS 的用户列表
        $userList = $this->getUserList();
        // 获取用户同步数据列表
        $syncList = $this->getSyncList($userList);
        // 准备 Cache 表的相关 key 值记录
        $this->readySync();
        // 将需要同步的用户数据列表存放在 Cache 表
        $syncList['third']['delete'] = $this->removeAdminUidFromIbosRemoveList($syncList['third']['delete']);
        Cache::model()->updateByPk('iboscreatelist', array('cachevalue' => serialize($syncList['third']['add'])));
        Cache::model()->updateByPk('ibosremovelist', array('cachevalue' => serialize($syncList['third']['delete'])));
        Cache::model()->updateByPk('cocreatelist', array('cachevalue' => serialize($syncList['co']['add'])));
        Cache::model()->updateByPk('coremovelist', array('cachevalue' => serialize($syncList['co']['delete'])));
        $unit = String::utf8Unserialize(Setting::model()->fetchSettingValueByKey('unit'));
        $coinfo = String::utf8Unserialize(Setting::model()->fetchSettingValueByKey('coinfo'));
        $data = array(
            'co' => array(
                'coAddNum' => count($syncList['third']['add']),
                'coAddList' => $syncList['third']['add'],
                'coDelNum' => count($syncList['third']['delete']),
                'coDelList' => $syncList['third']['delete'],
                'count' => $syncList['third']['count'],
            ),
            'ibos' => array(
                'ibosAddNum' => count($syncList['co']['add']),
                'ibosAddList' => $syncList['co']['add'],
                'ibosDelNum' => count($syncList['co']['delete']),
                'ibosDelList' => $syncList['co']['delete'],
                'count' => $syncList['co']['count'],
            ),
        );
        $this->ajaxReturn(array(
            'status' => TRUE,
            'data' => $data,
        ));
    }

    /**
     * 根据参数获取对应准备同步的用户分类信息列表
     * ibosAddList 	IBOS 新增/启用 用户信息列表
     * ibosDelList 	IBOS 禁用 用户信息列表
     * coAddList 	酷办公 新增/加入 用户信息列表
     * coDelList 	酷办公 移除 用户信息列表
     * @return ajax
     */
    public function actionGetUserListInfo() {
        $op = Env::getRequest('op');
        $opList = array('ibosAddList', 'ibosDelList', 'coAddList', 'coDelList');
        if (!in_array($op, $opList)) {
            $this->ajaxReturn(array(
                'isSuccess' => FALSE,
                'message' => '请求的参数不正确',
            ));
        }
        switch ($op) {
            case 'ibosAddList':
                $coCreateList = Cache::model()->fetchArrayByPk('cocreatelist');
                foreach ($coCreateList as $key => $user) {
                    $dataList[] = array(
                        'realname' => $user['realname'],
                        'deptname' => DepartmentModel::model()->fetchDeptNameByUid($user['uid']),
                        'posname' => Position::model()->fetchPosNameByUid($user['uid']),
                    );
                }
                break;
            case 'ibosDelList':
                $coRemoveList = Cache::model()->fetchArrayByPk('coremovelist');
                foreach ($coRemoveList as $key => $uid) {
                    $dataList[] = array(
                        'realname' => User::model()->fetchRealnamesByUids(array($uid)),
                        'deptname' => DepartmentModel::model()->fetchDeptNameByUid($uid),
                        'posname' => Position::model()->fetchPosNameByUid($uid),
                    );
                }
                break;
            case 'coAddList':
                $dataList = Cache::model()->fetchArrayByPk('iboscreatelist');
                break;
            case 'coDelList':
                $dataList = Cache::model()->fetchArrayByPk('ibosremovelist');
                break;
        }
        $this->ajaxReturn(array(
            'isSuccess' => TRUE,
            'data' => $dataList,
        ));
    }

    /**
     * 检查是否需要自动同步
     * @return boolen
     */
    public function isAutoSync() {
        if (Setting::model()->fetchSettingValueByKey('autosync') === NULL) {
            Setting::model()->add(array('skey' => 'autosync', 'svalue' => serialize(array('status' => 1, 'lastsynctime' => time()))));
        }
        $autosync = String::utf8Unserialize(Setting::model()->fetchSettingValueByKey('autosync'));
        // 开启了自动同步 && 上一次同步时间小于今天 0 点
        if ($autosync['status'] == 1 && $autosync['lastsynctime'] < strtotime(date('Y-m-d', time()))) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 开启关闭自动同步
     */
    public function actionAutoSync() {
        $status = Env::getRequest('autoSync');
        $autosync = String::utf8Unserialize(Setting::model()->fetchSettingValueByKey('autosync'));
        $autosync['status'] = intval($status);
        if (Setting::model()->updateSettingValueByKey('autosync', serialize($autosync))) {
            $this->ajaxReturn(array(
                'status' => TRUE,
                'message' => ( $status === '1' ? '开启' : '关闭' ) . '自动同步成功',
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => FALSE,
                'message' => ( $status === '1' ? '开启' : '关闭' ) . '自动同步失败',
            ));
        }
    }

    // public function actoinSyncinvite() {
    // }

    /**
     * 按照酷办公需要的数据格式获取 IBOS 用户信息列表
     * @return [type] [description]
     */
    protected function getUserList() {
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
    protected function getSyncList($userList) {
        $post = array(
            'type' => $this->coBindType,
            'corpid' => $this->corpid,
            'userlist' => $userList,
        );
        $getSyncListRes = CoApi::getInstance()->getDiffUsers($post);
        if ($getSyncListRes['errorcode'] == CodeApi::SUCCESS) {
            return $getSyncListRes['data'];
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => FALSE,
                'msg' => $getSyncListRes,
            ));
        }
    }

    /**
     * 检查 Cache 表中是否有对应 key 记录
     * 没有就添加
     * iboscreatelist 需要 IBOS 新增的用户
     * ibosremovelist 需要 IBOS 移除的用户
     * cocreatelist 需要酷办公新增的用户
     * coremovelist 需要酷办公移除的用户
     */
    protected function readySync() {
        // 准备同步结果数据记录字段
        if (Cache::model()->fetchArrayByPk('successinfo') === FALSE) {
            Cache::model()->add(array('cachekey' => 'successinfo', 'cachevalue' => serialize(array())));
        }
        // 需要 IBOS 新增的用户
        if (Cache::model()->fetchArrayByPk('iboscreatelist') === FALSE) {
            Cache::model()->add(array('cachekey' => 'iboscreatelist', 'cachevalue' => serialize(array())));
        }
        // 需要 IBOS 移除的用户
        if (Cache::model()->fetchArrayByPk('ibosremovelist') === FALSE) {
            Cache::model()->add(array('cachekey' => 'ibosremovelist', 'cachevalue' => serialize(array())));
        }
        // 需要酷办公新增的用户
        if (Cache::model()->fetchArrayByPk('cocreatelist') === FALSE) {
            Cache::model()->add(array('cachekey' => 'cocreatelist', 'cachevalue' => serialize(array())));
        }
        // 需要酷办公移除的用户
        if (Cache::model()->fetchArrayByPk('coremovelist') === FALSE) {
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
    protected function removeIdenticalByMobile($userList_1, $userList_2) {
        $identical = array();
        if (!is_array($userList_1) || !is_array($userList_2)) {
            $this->ajaxReturn(array(
                'status' => 5,
                'message' => '请尝试手动点击开始同步',
            ));
        }
        foreach ($userList_1 as $key_1 => $user_1) {
            foreach ($userList_2 as $key_2 => $user_2) {
                if ($user_1['mobile'] === $user_2['mobile']) {
                    $identical[] = array('uid_1' => array( 'uid' => $user_1['uid'], 'guid' => $user_1['guid'] ), 'uid_2' => $user_2['uid']);
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
    protected function coCreateRelation($relationCreateList) {
        $post = array(
            'type' => $this->coBindType,
            'corpid' => $this->corpid,
            'data' => $relationCreateList,
        );
        $createCoRelationRes = CoApi::getInstance()->createRelationByList($post);
        if ($createCoRelationRes['errorcode'] != CodeApi::SUCCESS) {
            $this->ajaxReturn(array(
                'isSuccess' => FALSE,
                'messag' => $createCoRelationRes['message'],
            ));
        }
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
    protected function coRemoveRelation($relationRemoveList) {
        $post = array(
            'type' => $this->coBindType,
            'corpid' => $this->corpid,
            'data' => $relationRemoveList,
        );
        $removeCoRelationRes = CoApi::getInstance()->removeRelationByList($post);
        if ($removeCoRelationRes['errorcode'] != CodeApi::SUCCESS) {
            $this->ajaxReturn(array(
                'isSuccess' => FALSE,
                'messag' => $removeCoRelationRes['message'],
            ));
        }
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
    protected function createCoUser($userCreateList) {
        $post = array(
            'type' => $this->coBindType,
            'corpid' => $this->corpid,
            'data' => $userCreateList,
        );
        $createCoUserRes = CoApi::getInstance()->createCoUserByList($post);
        // 调用接口成功，根据返回数据添加相应的绑定记录
        if ($createCoUserRes['errorcode'] == CodeApi::SUCCESS) {
            foreach ($createCoUserRes['data'] as $relation) {
                $this->addBindRelation($relation['uid'], $relation['bindvalue']);
            }
            return $createCoUserRes['data'];
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => FALSE,
                'message' => $createCoUserRes['message'],
            ));
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
    protected function removeCoUser($userRemoveList) {
        $post = array(
            'type' => $this->coBindType,
            'corpid' => $this->corpid,
            'data' => $userRemoveList,
        );
        $removeCoUserRes = CoApi::getInstance()->removeCoUserByList($post);
        // 调用接口成功，根据返回数据删除对应的绑定记录
        if ($removeCoUserRes['errorcode'] == CodeApi::SUCCESS) {
            foreach ($removeCoUserRes['data'] as $relation) {
                UserBinding::model()->deleteAll(sprintf("`uid` = %d AND `app` = 'co'", $relation['uid']));
            }
            return $removeCoUserRes['data'];
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => FALSE,
                'message' => 14 . $removeCoUserRes['message'],
            ));
        }
    }

    /**
     * 添加绑定关系
     * @param integer $uid       IBOS uid
     * @param integer $bindvalue 酷办公 uid
     * @return integer 成功数
     */
    protected function addBindRelation($uid, $bindvalue) {
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

    /**
     * 判断是安装时进行的同步操作还是后台绑定
     * 后台绑定时的同步，需要加上绑定企业双方的一些基本数据，用于资料展示
     * @return array 后台时会返回企业的 logo 等资料数组。安装时不需要，返回空数组
     */
    protected function verifyIsInstall() {
        $isInstance = Env::getRequest('isInstance');
        if ($isInstance === NULL) {
            $coinfo = String::utf8Unserialize(Setting::model()->fetchSettingValueByKey('coinfo'));
            $unit = String::utf8Unserialize(Setting::model()->fetchSettingValueByKey('unit'));
            $result['ibos'] = array(
                'corplogo' => $unit['logourl'],
                'corpshortname' => $unit['shortname'],
                'systemurl' => $unit['systemurl'],
            );
            $result['co'] = array(
                'corplogo' => $coinfo['corplogo'],
                'corpshortname' => $coinfo['corpshortname'],
                'corpid' => $coinfo['corpid'],
            );
        } else {
            $result = array();
        }
        return $result;
    }

    /**
     * 需求改动，移除该功能
     * 根据用户信息数组返回用户手机号列表字符串 "mobile1,mobile2,mobile3"
     * 用于调用酷办公用户邀请接口
     * @param  array $userList 用户信息数组
     * @return string
     */
    // protected function sendSyncInvite($userList) {
    // 	$mobileList = array();
    // 	foreach ($userList as $user) {
    // 		$mobileList[] = $user['mobile'];
    // 	}
    // 	$post = array(
    // 		'mobile' => $mobileList,
    // 		'type' => 'invite',
    // 	);
    // 	$coinfo = String::utf8Unserialize(Setting::model()->fetchSettingValueByKey('coinfo'));
    // 	$sendInviteRes = CoApi::getInstance()->sendInvite($coinfo['accesstoken'], $post);
    // 	if ($sendInviteRes['code'] != CodeApi::SUCCESS) {
    // 		$this->ajaxReturn(array(
    // 			'status' => FALSE,
    // 			'message' => '发送邀请失败',
    // 		));
    // 	}
    // }

    /**
     * 过滤酷办公移除用户列表中的 IBOS 超级管理员用户
     * @param  array $ibosRemoveList 从差异化分析接口返回的酷办公移除用户列表
     * @return array                 过滤后的用户列表
     */
    protected function removeAdminUidFromIBOSRemoveList($ibosRemoveList) {
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
     * 开启/关闭 自动同步操作
     * @param  integer $autoSync 状态值
     * @return boolen
     */
    // protected function changeAutoSyncStatus( $autoSyncStatus ) {
    // 	if ( Setting::model()->fetchSettingValueByKey( 'autosync' ) === NULL ) {
    // 		Setting::model()->add( array( 'skey' => 'autosync', 'svalue' => serialize( array() ) ) );
    // 	}
    // 	$autoSync = array( 'status' => $autoSyncStatus, 'lastsynctime' => strtotime( date( 'Y-m-d', time() ) ) );
    // 	return Setting::model()->updateSettingValueByKey( 'autosync', serialize( $autoSync ) );
    // }
}
