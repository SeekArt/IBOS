<?php

/**
 * user表的数据层操作文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * user表的数据层操作类
 * 
 * @package application.modules.user.model
 * @version $Id: User.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\user\model;

use application\core\model\Model;
use application\core\utils as util;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\dashboard\model\Syscache;
use application\modules\department\model\DepartmentRelated;
use application\modules\position\model\PositionRelated;
use application\modules\role\model\RoleRelated;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class User extends Model {

    const USER_STATUS_ABANDONED = 2;
    const USER_STATUS_LOCKED = 1;
    const USER_STATUS_NORMAL = 0;
    public function init() {
        $this->cacheLife = 0;
        parent::init();
    }
	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{user}}';
	}

    public function afterSave() {
        CacheUtil::update('Users');
        CacheUtil::load('Users');
        parent::afterSave();
    }
	/**
	 * 检查用户名是否存在
	 * @param string $name
	 * @return boolean
	 */
	public function userNameExists( $name ) {
		$user = $this->fetch( 'username = :name', array( ':name' => $name ) );
		if ( !empty( $user ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 根据用户真实姓名查找用户信息
	 * @param string $name
	 * @return array
	 */
	public function fetchByRealname( $name ) {
		$user = $this->fetch( 'realname = :name', array( ':name' => $name ) );
		$users = UserUtil::loadUser();
		if ( !empty( $user ) ) {
			if ( isset( $users[$user['uid']] ) ) {
				$user = $users[$user['uid']];
			} else {
				$user = UserUtil::wrapUserInfo( $user );
			}
		}
		return $user;
	}

	/**
	 * 检查唯一字段
	 * @param 需要插入的用户 $data
	 * @param 唯一字段的配置 $uniqueConfig，格式：key对应数据表里的字段，value对应这个字段的解释
	 */
	public function checkUnique( $data, $uniqueConfig = array( 'mobile' => '手机号', 'username' => '用户名', ) ) {
		$arr1 = $arr2 = array();
		foreach ( $uniqueConfig as $k => $v ) {
			if ( !empty( $data[$k] ) ) {
				$arr1[] = $k . '=:' . $k;
				$arr2[':' . $k] = $data[$k];
			}
		}

		$str = implode( ' or ', $arr1 );
		if ( isset( $data['uid'] ) ) {
			$con = ' and uid != :uid';
			$arr2[':uid'] = $data['uid'];
			$str = '(' . $str . ')' . $con;
		}
		$res = $this->fetch( $str, $arr2 );
		if ( !empty( $res ) ) {
			//todo:更好的显示方式
			Env::iExit( '请检查：' . implode( ',', $uniqueConfig ) . '中是否有重复的用户' );
		}
	}

	/**
	 * 
	 * @param type $realnames
	 * @return type
	 */
	public function fetchAllByRealnames( $realnames ) {
		$usersData = array();
		if ( !empty( $realnames ) ) {
			$users = UserUtil::loadUser();
			$criteria = array(
				'select' => '*',
				'condition' => sprintf( "realname IN (%s)", util\String::iImplode( $realnames ) )
			);
			$list = $this->fetchAll( $criteria );
			foreach ( $list as $user ) {
				if ( isset( $users[$user['uid']] ) ) {
					$usersData[$user['uid']] = $users[$user['uid']];
				} else {
					$usersData[$user['uid']] = UserUtil::wrapUserInfo( $user );
				}
			}
		}
		return $usersData;
	}

	/**
	 * 根据UID查找用户真实姓名
	 * @param integer $uid
	 * @return string
	 */
	public function fetchRealnameByUid( $uid ) {
		static $users = array();
		if ( !isset( $users[$uid] ) ) {
			$user = $this->fetchByUid( $uid );
			if ( isset( $user['realname'] ) ) {
				$users[$uid] = $user['realname'];
			} else {
				return '';
			}
		}
		return $users[$uid];
	}

	/**
	 * 查找用户真实姓名，返回$glue分隔的字符串格式
	 * @param mixed $uids 用户ID数组或=逗号分隔ID串
	 * @param string $glue 分隔符
	 * @return string
	 */
	public function fetchRealnamesByUids( $uids, $glue = ',' ) {
		$uid = is_array( $uids ) ? $uids : explode( ',', util\String::filterStr( $uids ) );
		$names = array();
		foreach ( $uid as $id ) {
			if ( !empty( $id ) ) {
				$names[] = $this->fetchRealnameByUid( $id );
			}
		}
		return implode( $glue, $names );
	}

	/**
	 * 根据用户id查找一条用户数据
	 * @param integer $uid
	 * @return array
	 */
    public function fetchByUid($uid, $force = false) {
		$users = UserUtil::loadUser();
        if ($force || !isset($users[$uid])) {
			$object = $this->findByPk( $uid );
			if ( is_object( $object ) ) {
				$user = $object->attributes;
				$users[$uid] = UserUtil::wrapUserInfo( $user );
				$this->makeCache( $users );
			} else {
				return array();
			}
		}
		return $users[$uid];
	}

    public function makeCache($userArr) {
        //将新数据缓存起来
        // 先判断准备缓存的用户是否处于禁用状态
        // 处于禁用状态的而用户不添加到缓存
        $users = array();
        foreach ($userArr as $uid => $user) {
            if (isset($user['status']) && ($user['status'] == '1' || $user['status'] == '0')) {
                $users[$uid] = $user;
            }
        }
        if (!empty($users)) {
            Syscache::model()->modifyCache('users', $users);
		util\Cache::load( 'users' );
        }
    }

	/**
	 * 查找部门内符合条件的人
	 */
	public function fetchAllFitDeptUser( $dept ) {
		$list = util\IBOS::app()->db->createCommand()
				->select( 'u.uid' )
				->from( '{{user}} u' )
				->leftJoin( '{{department_related}} dr', 'u.uid = dr.uid' )
				->where( "u.status IN (1,0) AND ((FIND_IN_SET(u.deptid,'{$dept}') OR FIND_IN_SET(dr.deptid,'{$dept}')))" )
				->queryAll();
		foreach ( $list as &$user ) {
			$user = $this->fetchByUid( $user['uid'] );
		}
		return $list;
	}

	/**
	 * 没设部门主管的情况下查找其他有权限的人
	 */
	public function fetchAllOtherManager( $dept ) {
		$list = util\IBOS::app()->db->createCommand()
				->select( 'u.uid' )
				->from( '{{user}} u' )
				->leftJoin( '{{department_related}} dr', 'u.uid = dr.uid' )
				->where( "u.status IN (1,0) AND ((FIND_IN_SET(u.deptid,'{$dept}') OR FIND_IN_SET(dr.deptid,'{$dept}')))" )
				->queryAll();
		return $list;
	}

	/**
	 * 根据用户id数组查找多条用户数据
	 * @param array $uids
	 * @return array
	 */
	public function fetchAllByUids( $uids ) {
		$users = UserUtil::loadUser();
		//先从缓存里取出存在的数据
		$record = array_intersect_key( $users, array_flip( $uids ) );
		//如果不完全匹配，则从数据库中查找。
		if ( empty( $record ) || count( $uids ) != count( $record ) ) {
			if ( is_array( $record ) && !empty( $record ) ) {
				$uids = array_diff( $uids, array_keys( $record ) );
			}
			if ( !empty( $uids ) ) {
				$records = $this->findAllByPk( array_merge( $uids ) );
				if ( !empty( $records ) ) {
					foreach ( $records as $rec ) {
						$user = $rec->attributes;
						$record[$user['uid']] = $users[$user['uid']] = UserUtil::wrapUserInfo( $user );
					}
					$this->makeCache( $users );
				}
			}
		}
		return $record;
	}

	/**
	 * 根据岗位id获取所有uid
	 * @param integer $posid
	 * @param boolean $returnDisabled 是否禁用用户一起返回
	 * @return string
	 * @author Ring
	 * @refactor banyan
	 */
    public function fetchUidByPosId($positionid, $returnDisabled = true, $related = false) {
        static $positionidArray = array();
        if (!isset($positionidArray[$positionid])) :
            $condition = " `u`.`positionid`= '{$positionid}' ";
            $query = IBOS::app()->db->createCommand();
            if (true === $related):
                $query = $query->leftJoin(PositionRelated::model()->tableName() . ' prpr'//自行百度prpr，我真的没有想歪
                        , " `prpr`.`uid` = `u`.`uid` ");
                $condition = array(
                    'OR',
                    $condition,
                    " `prpr`.`positionid`= '{$positionid}' ",
                );
            endif;
            if (true === $returnDisabled):
                $condition = array(
                    'AND',
                    $condition,
                    " `u`.`status` != '" . self::USER_STATUS_ABANDONED . "'",
                );
            endif;
            $uidArray = $query->selectDistinct('u.uid')
                    ->from($this->tableName() . ' u')
                    ->where($condition)
                    ->queryColumn();
            $positionidArray[$positionid] = $uidArray;
        endif;
        return $positionidArray;
	}

	/**
	 * 根据角色id获取所有uid
	 * @param integer $roleId
	 * @param boolean $returnDisabled 是否禁用用户一起返回
     * @param boolean $related 是否返回辅助角色
	 * @return array
	 */
    public function fetchUidByRoleId($roleid, $returnDisabled = true, $related = false) {
        if (!isset($RoleidArray[$roleid])) {
            $condition = " `u`.`roleid` = '{$roleid}'";
            $query = IBOS::app()->db->createCommand();
            if (true === $related):
                $query = $query->leftJoin(RoleRelated::model()->tableName() . ' rr'
                        , " `rr`.`uid` = `u`.`uid` ");
                $condition = array(
                    'OR',
                    $condition,
                    " `rr`.`roleid` = '{$roleid}' ",
                );
            endif;
            if (true === $returnDisabled):
                $condition = array(
                    'AND',
                    $condition,
                    " `u`.`status` != '" . self::USER_STATUS_ABANDONED . "'",
                );
            endif;
            $uidArray = $query->selectDistinct('u.uid')
                    ->from($this->tableName() . ' u')
                    ->where($condition)
                    ->queryColumn();
            $RoleidArray[$roleid] = $uidArray;
			}
        return $RoleidArray[$roleid];
		}

    public function fetchAllUidByRoleids($roleid, $returnDisabled = true, $related = false) {
        $condition = " FIND_IN_SET( `u`.`roleid`, '{$roleid}' ) ";
        $query = IBOS::app()->db->createCommand();
        if (true === $related):
            $query = $query->leftJoin(RoleRelated::model()->tableName() . ' rr'
                    , " `rr`.`uid` = `u`.`uid` ");
            $condition = array(
                'OR',
                $condition,
                " FIND_IN_SET( `rr`.`roleid`, '{$roleid}' ) ",
            );
        endif;
        if (true === $returnDisabled):
            $condition = array(
                'AND',
                $condition,
                " `u`.`status` != '" . self::USER_STATUS_ABANDONED . "'",
            );
        endif;
        $uidArray = $query->selectDistinct('u.uid')
                ->from($this->tableName() . ' u')
                ->where($condition)
                ->queryColumn();
        return $uidArray;
	}
	/**
	 * 获取所有的uid（暂时crm用到）
	 * @param boolean $returnDisabled 是否禁用用户一起返回
	 * @return array
	 */
    public function fetchUidA($returnDisabled = true) {
        $condition = $returnDisabled ? 1 : " `status` != '" . self::USER_STATUS_ABANDONED . "'";
        $uidA = $this->getDbConnection()->createCommand()
                ->select('uid')
                ->from($this->tableName())
                ->where($condition)
                ->queryColumn();
        return $uidA;
	}

	/**
	 * 根据多个岗位id获取所有uid
	 * @param mix $positionIds
	 * @param boolean $returnDisabled 是否禁用用户一起返回
	 * @return array
	 */
    public function fetchAllUidByPositionIds($positionids, $returnDisabled = true, $related = false) {
        $positionString = is_array($positionids) ? implode(',', $positionids) : $positionids;
        $condition = " FIND_IN_SET(`u`.`positionid`, '{$positionString}') ";
        $query = IBOS::app()->db->createCommand();
        if (true === $related):
            $query = $query->leftJoin(PositionRelated::model()->tableName() . ' prpr'//自行百度prpr，我真的没有想歪
                    , " `prpr`.`uid` = `u`.`uid` ");
            $condition = array(
                'OR',
                $condition,
                " FIND_IN_SET(`prpr`.`positionid`, '{$positionString}') ",
            );
        endif;
        if (true === $returnDisabled):
            $condition = array(
                'AND',
                $condition,
                " `u`.`status` != '" . self::USER_STATUS_ABANDONED . "'",
            );
        endif;
        $uidArray = $query->selectDistinct('u.uid')
                ->from($this->tableName() . ' u')
                ->where($condition)
                ->queryColumn();
        return $uidArray;
	}

	/**
	 * 根据部门id获取所有uid
	 * @param integer $deptid
	 * @param boolean $returnDisabled 是否禁用用户一起返回
     * @param boolean $related 是否返回辅助部门的用户
	 * @return array
	 */
    public function fetchAllUidByDeptid($deptid, $returnDisabled = true, $related = false) {
        static $deptidArray = array();
        if (!isset($deptidArray[$deptid])) {
            $condition = " `u`.`deptid` = '{$deptid}'";
            $query = IBOS::app()->db->createCommand();
            if (true === $related):
                $query = $query->leftJoin(DepartmentRelated::model()->tableName() . ' dr'
                        , " `dr`.`uid` = `u`.`uid` ");
                $condition = array(
                    'OR',
                    $condition,
                    " `dr`.`deptid` = '{$deptid}'",
                );
            endif;
            if (true === $returnDisabled):
                $condition = array(
                    'AND',
                    $condition,
                    " `u`.`status` != '" . self::USER_STATUS_ABANDONED . "'",
                );
            endif;
            $uidArray = $query->selectDistinct('u.uid')
                    ->from($this->tableName() . ' u')
                    ->where($condition)
                    ->queryColumn();
            $deptidArray[$deptid] = $uidArray;
		}
        return $deptidArray[$deptid];
	}

	/**
	 * 根据多个部门id获取所有uid
	 * @param mix $deptIds
	 * @param boolean $returnDisabled 是否禁用用户一起返回
	 * @return array
	 */
    public function fetchAllUidByDeptids($deptids, $returnDisabled = true, $related = false) {
        $deptidString = is_array($deptids) ? implode(',', $deptids) : $deptids;
        $condition = " FIND_IN_SET(`u`.`deptid`, '{$deptidString}') ";
        $query = IBOS::app()->db->createCommand();
        if (true === $related):
            $query = $query->leftJoin(DepartmentRelated::model()->tableName() . ' dr'
                    , " `dr`.`uid` = `u`.`uid` ");
            $condition = array(
                'OR',
                $condition,
                " FIND_IN_SET(`dr`.`deptid`, '{$deptidString}') ",
            );
        endif;
        if (true === $returnDisabled):
            $condition = array(
                'AND',
                $condition,
                " `u`.`status` != '" . self::USER_STATUS_ABANDONED . "'",
            );
        endif;
        $uidArray = $query->selectDistinct('u.uid')
                ->from($this->tableName() . ' u')
                ->where($condition)
                ->queryColumn();
        return $uidArray;
	}

	/**
	 * 查找所有用户UID以积分高低排序
	 * @return array
	 */
	public function fetchAllCredit() {
		$condition = array(
			'select' => 'uid',
			'condition' => 'status != 2',
			'order' => 'credits DESC'
		);
		$ids = $this->fetchAll( $condition );
		$result = array();
		if ( !empty( $ids ) ) {
			$result = util\Convert::getSubByKey( $ids, 'uid' );
		}
		return $result;
	}

	/**
	 * 根据部门ID,类型查找数据
	 * @param string $type 查询类型
	 * @param integer $limit 
	 * @param integer $offset 
	 * @return array
	 */
	public function fetchAllByDeptIdType( $deptId, $type, $limit, $offset ) {
		$condition = array(
			'condition' => $this->getConditionByDeptIdType( $deptId, $type ),
			'order' => 'createtime DESC',
			'limit' => $limit,
			'offset' => $offset
		);
		return $this->fetchAll( $condition );
	}

	/**
	 * 批量更新用户信息
	 * @param mixed $uids 用户ID字符串或数组
	 * @param array $attributes 要更新的值
	 * @return integer
	 */
	public function updateByUids( $uids, $attributes = array() ) {
		$uids = is_array( $uids ) ? $uids : explode( ',', $uids );
		$condition = "FIND_IN_SET(uid,'" . implode( ',', $uids ) . "')";
		$counter = $this->updateAll( $attributes, $condition );
		$users = UserUtil::loadUser();
		//重建缓存
		$records = $this->findAllByPk( $uids );
		if ( !empty( $records ) ) {
			foreach ( $records as $rec ) {
				$user = $rec->attributes;
				$users[$user['uid']] = UserUtil::wrapUserInfo( $user );
			}
			$this->makeCache( $users );
		}
		return $counter;
	}

	/**
	 * 按UID更新用户信息
	 * @param type $uid
	 * @param type $attributes
	 * @return type 
	 */
	public function updateByUid( $uid, $attributes ) {
		$counter = $this->updateByPk( $uid, $attributes );
		$users = UserUtil::loadUser();
		if ( !isset( $users[$uid] ) ) {
			$this->fetchByUid( $uid );
			return $counter;
		}
		$newUser = array_merge( $users[$uid], $attributes );
		$users[$uid] = UserUtil::wrapUserInfo( $newUser );
		//重建缓存
		$this->makeCache( $users );
		return $counter;
	}

	/**
	 * 根据部门ID,类型统计人数
	 * @param string $type 查询类型
	 * @return integer
	 */
	public function countByDeptIdType( $deptId, $type ) {
		return $this->count( array( 'condition' => $this->getConditionByDeptIdType( $deptId, $type ) ) );
	}

	/**
	 * 根据类型获取条件语句
	 * @param string $type 查询类型
	 * @return string SQL where 字段
	 */
	public function getConditionByDeptIdType( $deptId, $type ) {
		$condition = $deptId ? "`deptid` = {$deptId} AND " : '';
		switch ( $type ) {
			case 'enabled':
				$condition .= '`status` = 0';
				break;
			case 'lock':
				$condition .= '`status` = 1';
				break;
			case 'disabled' :
				$condition .= '`status` = 2';
				break;
			default:
				$condition .= '1';
				break;
		}
		return $condition;
	}

	/**
	 * 通过uid取得该用户所有下属id
	 * @param integer $uid 
	 * @return array
	 */
	public function fetchSubUidByUid( $uid ) {
		$subUid = $this->fetchAll( array(
			'select' => 'uid',
			'condition' => 'upuid=:upuid AND status != 2',
			'params' => array( ':upuid' => $uid )
				) );
		$uidArr = util\Convert::getSubByKey( $subUid, 'uid' );
		return $uidArr;
	}

	/**
	 * 通过uid取得该用户所有下属(日程模块和日志模块用到)
	 * @param integer $uid 
	 * @return array
	 */
	public function fetchSubByPk( $uid, $limitCondition = '' ) {
		$records = $this->fetchAll( array(
			'select' => 'uid, username, deptid, upuid, realname',
			'condition' => 'upuid=:upuid AND status != 2' . $limitCondition,
			'params' => array( ':upuid' => $uid )
				) );
		$userArr = array();
		foreach ( $records as $user ) {
			$userArr[] = $user;
		}
		return $userArr;
	}

	/**
	 * 获得某种状态的所有用户id数组
	 * @param integer $status 状态（0：启用 1：锁定 2：禁用）
	 * @return type
	 */
	public function fetchAllUidsByStatus( $status ) {
		$records = $this->fetchAll( array(
			'select' => 'uid',
			'condition' => 'status = :status',
			'params' => array( 'status' => $status )
				) );
		return util\Convert::getSubByKey( $records, 'uid' );
	}

	/**
	 * 处理一组uid，将禁用的uid去除掉
	 * @param mix $uids uid一维数组或者逗号隔开的字符串
	 * @return array
	 */
	public function removeDisableUids( $uids ) {
		$uids = is_array( $uids ) ? implode( ',', $uids ) : $uids;
		$records = $this->fetchAll( array(
			'select' => 'uid',
			'condition' => sprintf( "FIND_IN_SET(`uid`, '%s') AND status != %d", $uids, 2 )
				) );
		return util\Convert::getSubByKey( $records, 'uid' );
	}

	/**
	 * 根据用户id获取头像
	 * @param integer $uid 用户id
	 * @param string $size 大小标识，b大，m中，s小
	 * @return string
	 */
	public function fetchAvatarByUid( $uid, $size = 'm' ) {
		$user = $this->fetchByUid( $uid );
        if (empty($user)) {
            return '';
        }
		if ( $size == 'b' ) {
			// 大头像
			$avatar = $user['avatar_big'];
		} elseif ( $size == 's' ) {
			// 小头像
			$avatar = $user['avatar_small'];
		} else {
			// 中头像
			$avatar = $user['avatar_middle'];
		}
		return $avatar;
	}

	/**
	 * 根据uids获取手机号码
	 * @param mixed $uids
	 * @return string 分号隔开的电话号码
	 */
	public function fetchMobilesByUids( $uids ) {
		$mobiles = array();
		$uids = is_array( $uids ) ? $uids : explode( ',', $uids );
		$users = $this->fetchAllByUids( $uids );
		foreach ( $users as $user ) {
			if ( !empty( $user['mobile'] ) ) {
				$mobiles[] = $user['mobile'];
			}
		}
		return implode( ';', $mobiles );
	}

	/**
	 * 根据岗位ID统计用户数（忽略辅助岗位）
	 * @param integer $positionid
	 * @return integer
	 */
	public function countNumsByPositionId( $positionid ) {
		return User::model()->count( 'positionid = :positionid AND status != 2', array( ':positionid' => $positionid ) );
	}

	/**
	 * 根据角色ID统计用户数（忽略辅助角色）
	 * @param integer $roleId
	 * @return integer
	 */
	public function countNumsByRoleId( $roleId ) {
		return User::model()->count( 'roleid = :roleid AND status != 2', array( ':roleid' => $roleId ) );
	}

	/**
	 * 根据某个条件更新用户信息
	 * @param mixed $uids 用户ID字符串或数组
	 * @param array $attributes 要更新字段值
	 * @return integer
	 * @author Sam 2015-08-21 <gzxgs@ibos.com.cn>
	 */
	public function updateByConditions( $uids, $attributes = array(), $condition = "" ) {
		$uids = is_array( $uids ) ? $uids : explode( ',', $uids );
		if ( !empty( $condition ) ) {
			$condition = "FIND_IN_SET(uid,'" . implode( ',', $uids ) . "') AND " . $condition;
		} else {
			$condition = "FIND_IN_SET(uid,'" . implode( ',', $uids ) . "')";
		}
		$this->updateAll( $attributes, $condition );
//		$users = UserUtil::loadUser();
//		//重新建立缓存
//		$records = $this->findAllByPk( $uids );
//		if ( !empty( $records ) ) {
//			foreach ( $records as $rec ) {
//				$user = $rec->attributes;
//				$users[$user['uid']] = UserUtil::wrapUserInfo( $user );
//			}
//			$this->makeCache( $users );
//		}
//		return $counter;
	}

	public function checkIsExistByMobile( $mobile ) {
		$result = $this->fetch( 'mobile  = :mobile', array( ':mobile' => $mobile ) );
		return !empty( $result ) ? true : false;
	}

    /**
     * 根据用户状态批量获取用户信息
     * @param  integer|array $status 用户状态 0,1,2 可以是数组也可以是整型
     * @return array         用户信息数组
     */
    public function fetchAllByStatus($status) {
        if (is_array($status)) {
            $status = implode(',', $status);
        }
        $result = $this->fetchAll(array(
            'condition' => "FIND_IN_SET( status, ':status' )",
            'params' => array(':status' => $status),
        ));
        return !empty($result) ? TRUE : FALSE;
    }
}
