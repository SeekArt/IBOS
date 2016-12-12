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
 * @version $Id: User.php 8774 2016-10-26 01:53:44Z gzcsh $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\user\model;

use application\core\model\Model;
use application\core\utils as util;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\department\model\DepartmentRelated;
use application\modules\department\model\Department;
use application\modules\position\model\PositionRelated;
use application\modules\role\model\RoleRelated;
use application\modules\user\utils\User as UserUtil;

class User extends Model
{

    const USER_STATUS_ABANDONED = 2;
    const USER_STATUS_LOCKED = 1;
    const USER_STATUS_NORMAL = 0;

    /**
     * @param string $className
     * @return User
     */
    public static function model($className = __CLASS__)
    {
        static $model = null;

        if (empty($model)) {
            $model = parent::model($className);
        }

        return $model;
    }

    public function tableName()
    {
        return '{{user}}';
    }

    //以下大量函数被重新改写，原因是AR在处理复杂的业务时效率不高，应该用DAO代替
    //AR使用场景只是为了提高代码编写效率用的
    //参见：
    //作者本人对于AR的效率回复：http://www.yiiframework.com/forum/index.php/topic/16597-yii%E7%9A%84ar%E7%9C%9F%E7%9A%84%E8%83%BD%E7%94%A8%E4%B9%88%EF%BC%9F/
    public $select = '*';
    public $limit = null;
    public $offset = null;
    public $leftJoin = null;
    public $pre = '';

    /**
     * 用以设置select值，如果需要前缀，也直接加，如：' `u`.`uid`,`u`.`username` '
     * @param string $select
     */
    public function setSelect($select)
    {
        $this->select = $select;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    public function setLeftJoin($table, $join)
    {
        $this->leftJoin = array($table, $join);
    }

    public function setPre($pre)
    {
        $this->pre = $pre;
    }

    /**
     * 用以重置DAO的一些参数，目前只有一个select
     */
    public function afterQuery()
    {
        $this->select = '*';
        $this->limit = null;
        $this->offset = null;
        $this->leftJoin = null;
        $this->pre = '';
    }

    //--------------------------builder condition------------------builder的条件
    //约定：
    //用下划线命名的函数表示用来快速格式化一些SQL语句的函数
    //X后缀表示可以支持数组或者逗号分割的字符串（简称逗号字符串）
    //返回值是yii可能支持的一些SQL格式，比如条件的话，可以支持数组和字符串
    //尽可能条件尽可能具体，不要太复杂
    /**
     * 用以生成uid的FIND_IN_SET语句，结果可能是FIND_IN_SET( `uid`,'1,2,3' )
     * 如果需要生成FIND_IN_SET( '1,2,3', `uid` )，建议写一个find_in_set_uid函数
     * @param mixed $uidX 支持uid的数组和逗号字符串
     * @param string $pre 表简称，一般直接拿除去前缀的表的首字母，比如ibos_user_profile这里定义成up
     * @return string 格式化后的condition语句。后面也是一样的，yii支持数组，所以这样的格式化函数也可以返回数组，只是这个是字符串
     */
    public function uid_find_in_set($uidX, $pre = '')
    {
        $preString = empty($pre) ? $pre : '`' . $pre . '`.';
        $uidString = is_array($uidX) ? implode(',', $uidX) : $uidX;
        return " FIND_IN_SET( {$preString}`uid`, '{$uidString}') ";
    }

    /**
     * 用户状态非禁用的条件
     * @param string $pre 表简称
     * @return string
     */
    public function status_not_disabled($pre = '')
    {
        $preString = empty($pre) ? $pre : '`' . $pre . '`.';
        $abandoned = self::USER_STATUS_ABANDONED;
        return " {$preString}`status` != '{$abandoned}' ";
    }

    /**
     * uid等于？
     * @param integer $uid uid
     * @param string $pre 表简写
     * @return string
     */
    public function uid_eq($uid, $pre = '')
    {
        $preString = empty($pre) ? $pre : '`' . $pre . '`.';
        return "{$preString}`uid` = '{$uid}' ";
    }

    //--------------------------builder condition end-------------
    //--------------------------new function-----------------------新一批的函数，旧的函数需要被优化的
    //一些重写的函数，约定
    //indexBy表示返回的数组的键是By后面的值，比如findUserIndexByUid表示查询出来的二维user数组，键是uid
    //第一个参数尽量是By后面的字段，比如上面就是uid，而且尽可能数组和字符串都支持
    //如果这个字段默认值是null，表示有可能查询表里的所有的该字段的值
    //
    /**
     * 获取user表的数据，并且以uid作为数组的键
     * @param mixed $uidX uid数组或者字符串
     * @param boolean $returnDisabled 是否返回禁用用户，默认不
     * @param mixed $extraCondition 额外的条件
     * @return array
     */
    public function findUserIndexByUid($uidX = null, $returnDisabled = false, $extraCondition = '')
    {
        if (empty($uidX) && null !== $uidX) {
            return array();
        }
        $userArray = $this->findUserByUid($uidX, $returnDisabled, $extraCondition);
        $return = array();
        if (!empty($userArray)) {
            foreach ($userArray as $user) {
                $return[$user['uid']] = $user;
            }
        }
        return $return;
    }

    /**
     * 获取user表的数据
     * @param mixed $uidX uid数组或者字符串
     * @param boolean $returnDisabled 是否返回禁用用户，默认false，只返回非禁用用户
     * @param mixed $extraCondition 额外的条件
     * @return array
     */
    public function findUserByUid($uidX = null, $returnDisabled = false, $extraCondition = '')
    {
        if (empty($uidX) && null !== $uidX) {
            return array();
        }
        $query = Ibos::app()->db->createCommand()
            ->select($this->select)
            ->from($this->tableName());
        $conditionArray = array(
            !empty($uidX) ? $this->uid_find_in_set($uidX, $this->pre) : 1,
            false === $returnDisabled ? $this->status_not_disabled($this->pre) : 1,
            !empty($extraCondition) ? $extraCondition : 1,
        );
        $condition = array_filter($conditionArray, function ($cond) {
            if ($cond != 1) {
                return true;
            }
        });
        $query->where(implode(' AND ', $condition));
        null !== $this->limit && $query->limit($this->limit);
        null !== $this->offset && $query->offset($this->offset);
        null !== $this->leftJoin && $query->leftJoin($this->leftJoin[0], $this->leftJoin[1]);
        $userArray = $query->queryAll();
        $this->afterQuery();
        return $userArray;
    }

    /**
     * 根据批量uid获取批量真实姓名，uid为数组的键
     * @param mixed $uidX
     * @return array
     */
    public function findRealnameIndexByUid($uidX)
    {
        $realName = Ibos::app()->db->createCommand()
            ->select('uid,realname')
            ->from($this->tableName())
            ->where($this->uid_find_in_set($uidX))
            ->queryAll();
        $return = array();
        if (!empty($realName)) {
            foreach ($realName as $name) {
                $return[$name['uid']] = $name['realname'];
            }
        }
        return $return;
    }

    /**
     * 获取所有的uid
     * @param boolean $returnDisabled 是否禁用用户一起返回
     * @return array
     */
    public function fetchUidA($returnDisabled = false)
    {
        $condition = $returnDisabled ? 1 : $this->status_not_disabled();
        $uidA = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where($condition)
            ->queryColumn();
        return $uidA;
    }

    /**
     * 根据批量uid获取批量真实姓名和电话，uid为数组的键
     * @param mixed $uidX
     * @return array
     */
    public function findThreeByUid($uidX)
    {
        $return = Ibos::app()->db->createCommand()
            ->select('uid,realname,mobile,deptid')
            ->from($this->tableName())
            ->where($this->uid_find_in_set($uidX))
            ->queryAll();
        return $return;
    }

    /**
     * 处理一组uid，将禁用的uid去除掉
     * @param mix $uidX uid一维数组或者逗号隔开的字符串
     * @return array
     */
    public function findNotDisabledUid($uidX)
    {
        $uidArray = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where(array(
                'AND',
                $this->uid_find_in_set($uidX),
                $this->status_not_disabled(),
            ))
            ->queryColumn();
        return $uidArray;
    }

    /**
     * 获得某种状态的所有用户数组
     * @param integer $status 状态（0：启用 1：锁定 2：禁用）
     * @return array
     */
    public function fetchAllUidsByStatus($status)
    {
        $uidArray = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where(" `status` = '{$status}' ")
            ->queryColumn();
        return $uidArray;
    }

    /**
     * 获取某个uid的所有部门
     * @param integer $uid uid
     * @param boolean $onlyRelated 是否只返回关联数据，默认false，返回包括主部门
     * @return array
     */
    public function findAllDeptidByUid($uid, $onlyRelated = false)
    {
        $main = null;
        if (false === $onlyRelated) {
            $main = Ibos::app()->db->createCommand()
                ->select('deptid')
                ->from($this->tableName())
                ->where($this->uid_eq($uid))
                ->queryScalar();
        }
        $related = Ibos::app()->db->createCommand()
            ->select('deptid')
            ->from(DepartmentRelated::model()->tableName())
            ->where($this->uid_eq($uid))
            ->queryColumn();

        return array_unique(array_merge($related, array($main)));
    }

    /**
     * 获取某个uid的所有岗位
     * @param integer $uid uid
     * @param boolean $onlyRelated 是否只返回关联数据，默认false，返回包括主岗位
     * @return array
     */
    public function findAllPositionidByUid($uid, $onlyRelated = false)
    {
        $main = null;
        if (false === $onlyRelated) {
            $main = Ibos::app()->db->createCommand()
                ->select('positionid')
                ->from($this->tableName())
                ->where($this->uid_eq($uid))
                ->queryScalar();
        }
        $related = Ibos::app()->db->createCommand()
            ->select('positionid')
            ->from(PositionRelated::model()->tableName())
            ->where($this->uid_eq($uid))
            ->queryColumn();

        return array_unique(array_merge($related, array($main)));
    }

    /**
     * 获取某个uid的所有角色
     * @param integer $uid uid
     * @param boolean $onlyRelated 是否只返回关联数据，默认false，返回包括主角色
     * @return array
     */
    public function findAllRoleidByUid($uid, $onlyRelated = false)
    {
        $main = null;
        if (false === $onlyRelated) {
            $main = Ibos::app()->db->createCommand()
                ->select('roleid')
                ->from($this->tableName())
                ->where($this->uid_eq($uid))
                ->queryScalar();
        }
        $related = Ibos::app()->db->createCommand()
            ->select('roleid')
            ->from(RoleRelated::model()->tableName())
            ->where($this->uid_eq($uid))
            ->queryColumn();

        return array_unique(array_merge($related, array($main)));
    }

    /**
     * 根据批量uid获取部门id，uid作为键
     * @param mixed $uidX uid数组或者逗号字符串，默认null，表示返回所有uid的
     * @return array 返回关联和主要部门id
     */
    public function findDeptidIndexByUid($uidX = null)
    {
        $condition = 1;
        if (null !== $uidX) {
            $condition = $this->uid_find_in_set($uidX);
        }
        $deptRelated = Ibos::app()->db->createCommand()
            ->select('uid,deptid')
            ->from(DepartmentRelated::model()->tableName())
            ->where($condition)
            ->queryAll();
        $deptMain = Ibos::app()->db->createCommand()
            ->select('uid,deptid')
            ->from($this->tableName())
            ->where($condition)
            ->queryAll();
        $return = $relatedArray = array();
        foreach ($deptRelated as $related) {
            $relatedArray[$related['uid']][] = $related['deptid'];
        }
        foreach ($deptMain as $main) {
            $return[$main['uid']] = array(
                'main' => $main['deptid'],
                'related' => !empty($relatedArray[$main['uid']]) ? $relatedArray[$main['uid']] : array(),
            );
        }
        return $return;
    }

    /**
     * 根据批量uid获取岗位id，uid作为键
     * @param mixed $uidX uid数组或者逗号字符串，默认null，表示返回所有uid的
     * @return array 返回关联和主要岗位id
     */
    public function findPositionidIndexByUid($uidX = null)
    {
        $condition = 1;
        if (null !== $uidX) {
            $condition = $this->uid_find_in_set($uidX);
        }
        $PositionRelated = Ibos::app()->db->createCommand()
            ->select('uid,positionid')
            ->from(PositionRelated::model()->tableName())
            ->where($condition)
            ->queryAll();
        $positionMain = Ibos::app()->db->createCommand()
            ->select('uid,positionid')
            ->from($this->tableName())
            ->where($condition)
            ->queryAll();
        $return = $relatedArray = array();
        foreach ($PositionRelated as $related) {
            $relatedArray[$related['uid']][] = $related['positionid'];
        }
        foreach ($positionMain as $main) {
            $return[$main['uid']] = array(
                'main' => $main['positionid'],
                'related' => !empty($relatedArray[$main['uid']]) ? $relatedArray[$main['uid']] : array(),
            );
        }
        return $return;
    }

    /**
     * 根据批量uid获取角色id，uid作为键
     * @param mixed $uidX uid数组或者逗号字符串，默认null，表示返回所有uid的
     * @return array 返回关联和主要角色id
     */
    public function findRoleidIndexByUid($uidX = null)
    {
        $condition = 1;
        if (null !== $uidX) {
            $condition = $this->uid_find_in_set($uidX);
        }
        $roleRelated = Ibos::app()->db->createCommand()
            ->select('uid,roleid')
            ->from(RoleRelated::model()->tableName())
            ->where($condition)
            ->queryAll();
        $roleMain = Ibos::app()->db->createCommand()
            ->select('uid,roleid')
            ->from($this->tableName())
            ->where($condition)
            ->queryAll();
        $return = $relatedArray = array();
        foreach ($roleRelated as $related) {
            $relatedArray[$related['uid']][] = $related['roleid'];
        }
        foreach ($roleMain as $main) {
            $return[$main['uid']] = array(
                'main' => $main['roleid'],
                'related' => !empty($relatedArray[$main['uid']]) ? $relatedArray[$main['uid']] : array(),
            );
        }
        return $return;
    }

    /**
     * 通过uid取得该用户所有下属id
     * @param integer $uid
     * @return array
     */
    public function fetchSubUidByUid($uid)
    {
        $uidArray = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where(array(
                'AND',
                $this->status_not_disabled(),
                " `upuid` ='{$uid}' "
            ))
            ->queryColumn();
        return $uidArray;
    }

    /**
     * 根据用户真实姓名查找用户信息，如果有重复，则只拿第一个查出来的
     * @param string $name 真实姓名
     * @return array 用户数据
     */
    public function findByRealname($name)
    {
        $user = Ibos::app()->db->createCommand()
            ->select()
            ->from($this->tableName())
            ->where(" `realname` = ':name' ", array(':name' => $name))
            ->queryScalar();
        return $user;
    }

    /**
     * 根据用户的手机号码查找用户信息
     *
     * @param string $mobile 手机号码
     * @return array 用户数据
     */
    public function findByMobile($mobile)
    {
        $user = Ibos::app()->db->createCommand()
            ->from($this->tableName())
            ->where('mobile = :mobile', array(':mobile' => $mobile))
            ->queryRow();
        return $user;
    }

    /**
     * 根据多个真实姓名获取uid数组
     * @param mixed $realnameX 真实姓名的数组或者逗号字符串
     * @return array
     */
    public function findUidByRealnameX($realnameX)
    {
        $realnameString = is_array($realnameX) ? implode(',', $realnameX) : $realnameX;
        $uidArray = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where(" FIND_IN_SET( `realname`, '{$realnameString}') ")
            ->queryColumn();
        return $uidArray;
    }

    /**
     * 根据用户id查找一条用户数据，该函数将获取用户的详细信息
     * @param integer $uid
     * @return array
     */
    public function fetchByUid($uid, $returnDisabled = true, $force = false)
    {
        $user = UserUtil::wrapUserInfo($uid, $returnDisabled, $force);
        return !empty($user[$uid]) ? $user[$uid] : array();
    }

    /**
     * 根据用户id数组查找多条用户数据，该函数将获取用户的详细信息。uid作为键
     * @param mixed $uidX uid的数组或者逗号字符串
     * @param bool $returnDisabled
     * @return array
     */
    public function fetchAllByUids($uidX = null, $returnDisabled = true)
    {
        if (null !== $uidX && empty($uidX)) {
            return false;
        } else {
            return UserUtil::wrapUserInfo($uidX, $returnDisabled);
        }
    }

    /**
     * 根据UID查找用户真实姓名
     * @param integer $uid
     * @return string
     */
    public function fetchRealnameByUid($uid)
    {
        $realname = Ibos::app()->db->createCommand()
            ->select('realname')
            ->from($this->tableName())
            ->where($this->uid_eq($uid))
            ->queryScalar();
        return $realname;
    }

    /**
     * 查找用户真实姓名，返回$glue分隔的字符串格式
     * @param mixed $uidX 用户ID数组或=逗号分隔ID串
     * @param string $glue 分隔符
     * @return string
     */
    public function fetchRealnamesByUids($uidX, $glue = ',')
    {
        $realnameArray = Ibos::app()->db->createCommand()
            ->select('realname')
            ->from($this->tableName())
            ->where($this->uid_find_in_set($uidX))
            ->queryColumn();
        return implode($glue, $realnameArray);
    }

    /**
     * 检查能否禁用或者锁定一批用户，如果禁用了之后一个管理员都没有，就禁止禁用这些uid
     * @param type $uidX
     */
    public function checkCanDisabled($uidX)
    {
        $uidArray = is_array($uidX) ? $uidX : explode(',', $uidX);
        $adminArray = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where(" isadministrator = 1 ")
            ->queryColumn();
        $isEmpty = array_diff($adminArray, $uidArray);
        return !empty($isEmpty);
    }

    //--------------------------new function end------------------
    /**
     * 检查用户名是否存在
     * @param string $name
     * @return boolean
     */
    public function userNameExists($name)
    {
        $user = $this->fetch('username = :name', array(':name' => $name));
        if (!empty($user)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查唯一字段
     * @param 需要插入的用户 $data
     * @param 唯一字段的配置 $uniqueConfig ，格式：key对应数据表里的字段，value对应这个字段的解释
     */
    public function checkUnique($data, $uniqueConfig = array('mobile' => '手机号', 'username' => '用户名',))
    {
        $arr1 = $arr2 = array();
        foreach ($uniqueConfig as $k => $v) {
            if (!empty($data[$k])) {
                $arr1[] = $k . '=:' . $k;
                $arr2[':' . $k] = $data[$k];
            }
        }

        $str = implode(' or ', $arr1);
        if (isset($data['uid'])) {
            $con = ' and uid != :uid';
            $arr2[':uid'] = $data['uid'];
            $str = '(' . $str . ')' . $con;
        }
        $res = $this->fetch($str, $arr2);
        if (!empty($res)) {
            //todo:更好的显示方式
            Env::iExit('请检查：' . implode(',', $uniqueConfig) . '中是否有重复的用户');
        }
    }

    /**
     * 查找部门内符合条件的人
     */
    public function fetchAllFitDeptUser($dept)
    {
        $uidArray = util\Ibos::app()->db->createCommand()
            ->select('u.uid')
            ->from('{{user}} u')
            ->leftJoin('{{department_related}} dr', 'u.uid = dr.uid')
            ->where(array(
                'AND',
                $this->status_not_disabled('u'),
                array(
                    'OR',
                    Department::model()->deptid_find_in_set($dept, 'u'),
                    Department::model()->deptid_find_in_set($dept, 'dr'),
                ),
            ))
            ->queryColumn();
        return $this->fetchAllByUids($uidArray);
    }

    /**
     * 没设部门主管的情况下查找其他有权限的人
     */
    public function fetchAllOtherManager($dept)
    {
        $list = util\Ibos::app()->db->createCommand()
            ->select('u.uid')
            ->from('{{user}} u')
            ->leftJoin('{{department_related}} dr', 'u.uid = dr.uid')
            ->where($this->status_not_disabled('u')
                . " AND ((FIND_IN_SET(u.deptid,'{$dept}') OR FIND_IN_SET(dr.deptid,'{$dept}')))")
            ->queryAll();
        return $list;
    }

    /**
     * 根据岗位id获取所有uid
     * @param integer $posid
     * @param boolean $returnDisabled 是否禁用用户一起返回
     * @return string
     * @author Ring
     * @refactor banyan
     */
    public function fetchUidByPosId($positionid, $returnDisabled = true, $related = false)
    {
        static $positionidArray = array();
        if (!isset($positionidArray[$positionid])) :
            $condition = " `u`.`positionid`= '{$positionid}' ";
            $query = Ibos::app()->db->createCommand();
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
                    $this->status_not_disabled('u'),
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
     * @param integer $roleid
     * @param boolean $returnDisabled 是否禁用用户一起返回
     * @param boolean $related 是否返回辅助角色
     * @return array
     */
    public function fetchUidByRoleId($roleid, $returnDisabled = true, $related = false)
    {
        if (!isset($RoleidArray[$roleid])) {
            $condition = " `u`.`roleid` = '{$roleid}'";
            $query = Ibos::app()->db->createCommand();
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
                    $this->status_not_disabled('u'),
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

    /**
     * 根据角色 ID 列表获取对应的用户 ID 数组
     * @param  string|array $roleids 角色列表
     * @param  boolean $returnDisabled 是否去除被禁用的用户 ID
     * @param  boolean $related 是否关联用户角色关系表数据
     * @return
     */
    public function fetchAllUidByRoleids($roleids, $returnDisabled = true, $related = false)
    {
        $roleids = is_array($roleids) ? implode(',', $roleids) : $roleids;
        $condition = " FIND_IN_SET( `u`.`roleid`, '{$roleids}' ) ";
        $query = Ibos::app()->db->createCommand();
        if (true === $related):
            $query = $query->leftJoin(RoleRelated::model()->tableName() . ' rr'
                , " `rr`.`uid` = `u`.`uid` ");
            $condition = array(
                'OR',
                $condition,
                " FIND_IN_SET( `rr`.`roleid`, '{$roleids}' ) ",
            );
        endif;
        if (false === $returnDisabled):
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
     * 根据多个岗位id获取所有uid
     * @param mix $positionids
     * @param boolean $returnDisabled 是否禁用用户一起返回
     * @param boolean $related 是否返回辅助岗位
     * @return array
     */
    public function fetchAllUidByPositionIds($positionids, $returnDisabled = true, $related = false)
    {
        $positionString = is_array($positionids) ? implode(',', $positionids) : $positionids;
        $condition = " FIND_IN_SET(`u`.`positionid`, '{$positionString}') ";
        $query = Ibos::app()->db->createCommand();
        if (true === $related):
            $query = $query->leftJoin(PositionRelated::model()->tableName() . ' prpr'//自行百度prpr，我真的没有想歪
                , " `prpr`.`uid` = `u`.`uid` ");
            $condition = array(
                'OR',
                $condition,
                " FIND_IN_SET(`prpr`.`positionid`, '{$positionString}') ",
            );
        endif;
        if (false === $returnDisabled):
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
     *
     * @param integer $deptId
     * @param boolean $returnDisabled 是否禁用用户一起返回
     * @param boolean $related 是否返回辅助部门的用户
     * @return array
     */
    public function fetchAllUidByDeptid($deptId, $returnDisabled = true, $related = false)
    {
        $allUidDeptIdArr = $this->fetchAllUidDeptId($returnDisabled, $related);

        // 按部门 id 分组
        $groupArr = $this->handleForGroup($allUidDeptIdArr);

        if (isset($groupArr[$deptId])) {
            return $groupArr[$deptId];
        }

        return array();
    }

    /**
     * 按部门 id 将用户 uid 分组
     * 备注：deptid 是 user 表中的 deptid，deptid2 是 department_realted 表中的 deptid
     *
     * @param array $uidDeptIdArr 用户部门关联数组，Example: array(array('uid' => 1, 'deptid' => 2, 'deptid2' => null))
     * @return array
     */
    public function handleForGroup($uidDeptIdArr)
    {
        // 按部门 id 分组
        $groupArr = array();
        foreach ($uidDeptIdArr as $loopUidDeptId) {
            $loopDeptId = $loopUidDeptId['deptid'];
            $loopDeptId2 = $loopUidDeptId['deptid2'];
            $loopUid = $loopUidDeptId['uid'];

            if (!is_null($loopDeptId)) {
                $groupArr[$loopDeptId][] = $loopUid;
            }

            if (!is_null($loopDeptId2)) {
                $groupArr[$loopDeptId2][] = $loopUid;
            }
        }

        // 过滤重复数据
        foreach ($groupArr as &$group) {
            $group = array_unique($group);
        }

        return $groupArr;
    }

    /**
     * 获取所有没有部门的用户（不包括辅助部门）
     *
     * @param bool $returnDisabled 是否返回禁用用户，true是、false否
     * @param bool $returnLocked 是否返回锁定用户，true是、false否
     * @return array|\CDbDataReader
     */
    public function fetchAllUidWithoutDeptId($returnDisabled = true, $returnLocked = true)
    {
        $criteria = new \CDbCriteria();

        $criteria = $criteria->addCondition('deptid = 0');

        if ($returnDisabled === false) {
            $criteria = $criteria->addCondition(sprintf('status != %d', self::USER_STATUS_ABANDONED));
        }
        if ($returnLocked === false) {
            $criteria = $criteria->addCondition(sprintf('status != %d', self::USER_STATUS_LOCKED));
        }
        // 没有部门的用户 uid 数组
        $hasNotDeptUidArr = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where($criteria->condition)
            ->queryColumn();

        return $hasNotDeptUidArr;
    }

    /**
     * 获取所有用户和部门关系的对应表
     *
     * @param boolean $returnDisabled 是否禁用用户一起返回
     * @param boolean $related 是否返回辅助部门的用户
     * @return array
     */
    public function fetchAllUidDeptId($returnDisabled = true, $related = false, $returnLocked = true)
    {
        $criteria = new \CDbCriteria();
        $query = Ibos::app()->db->createCommand();
        if (true === $related) {
            $query = $query->leftJoin(DepartmentRelated::model()->tableName() . ' dr'
                , " `dr`.`uid` = `u`.`uid` ");
            $query->select('u.status, u.uid, u.deptid, dr.deptid as deptid2');
        } else {
            $query->select('u.status, u.uid, u.deptid, u.deptid as deptid2');
        }
        if ($returnDisabled === false) {
            $criteria = $criteria->addCondition(sprintf('u.status != %d', self::USER_STATUS_ABANDONED));
        }
        if ($returnLocked === false) {
            $criteria = $criteria->addCondition(sprintf('u.status != %d', self::USER_STATUS_LOCKED));
        }

        $uidDeptIdArr = $query->from($this->tableName() . ' u')
            ->where($criteria->condition)
            ->queryAll();


        return $uidDeptIdArr;
    }

    /**
     * 根据多个部门id获取所有uid
     * @param mix $deptIds
     * @param boolean $returnDisabled 是否禁用用户一起返回
     * @return array
     */
    public function fetchAllUidByDeptids($deptids, $returnDisabled = true, $related = false)
    {
        $deptIdArr = !is_array($deptids) ? explode(',', $deptids) : $deptids;
        $condition = util\StringUtil::generateInCondition('`u`.`deptid`', $deptIdArr);
        $query = Ibos::app()->db->createCommand();
        if (true === $related):
            $query = $query->leftJoin(DepartmentRelated::model()->tableName() . ' dr'
                , " `dr`.`uid` = `u`.`uid` ");
            $condition2 = util\StringUtil::generateInCondition('`dr`.`deptid`', $deptIdArr);
            $condition = array(
                'OR',
                $condition,
                $condition2,
            );
        endif;
        if (false === $returnDisabled):
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
    public function fetchAllCredit()
    {
        $uid = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where(" `status` != 2 ")
            ->order(' credits DESC ')
            ->queryColumn();
        return !empty($uid) ? $uid : array();
    }

    /**
     * 根据部门ID,类型查找数据
     * @param string $type 查询类型
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    public function fetchAllByDeptIdType($deptId, $type, $limit, $offset)
    {
        $condition = array(
            'condition' => $this->getConditionByDeptIdType($deptId, $type),
            'order' => 'createtime DESC',
            'limit' => $limit,
            'offset' => $offset
        );
        return $this->fetchAll($condition);
    }

    /**
     * 批量更新用户信息
     * @param mixed $uids 用户ID字符串或数组
     * @param array $attributes 要更新的值
     * @return integer
     */
    public function updateByUids($uidX, $attributes = array())
    {
        $counter = $this->updateAll($attributes, $this->uid_find_in_set($uidX));
        return $counter;
    }

    /**
     * 按UID更新用户信息
     * @param type $uid
     * @param type $attributes
     * @return type
     */
    public function updateByUid($uid, $attributes)
    {
        $counter = $this->updateByPk($uid, $attributes);
        return $counter;
    }

    /**
     * 根据部门ID,类型统计人数
     * @param string $type 查询类型
     * @return integer
     */
    public function countByDeptIdType($deptId, $type)
    {
        return $this->count(array('condition' => $this->getConditionByDeptIdType($deptId, $type)));
    }

    /**
     * 根据类型获取条件语句
     * @param string $type 查询类型
     * @return string SQL where 字段
     */
    public function getConditionByDeptIdType($deptId, $type)
    {
        $condition = $deptId ? "`deptid` = {$deptId} AND " : '';
        switch ($type) {
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
     * 通过uid取得该用户所有下属(日程模块和日志模块用到)
     * @param integer $uid
     * @return array
     */
    public function fetchSubByPk($uid, $limitCondition = '')
    {
        $records = $this->fetchAll(array(
            'select' => 'uid, username, deptid, upuid, realname',
            'condition' => 'upuid=:upuid AND status != 2' . $limitCondition,
            'params' => array(':upuid' => $uid)
        ));
        $userArr = array();
        foreach ($records as $user) {
            $userArr[] = $user;
        }
        return $userArr;
    }

    /**
     * 根据用户id获取头像
     * @param integer $uid 用户id
     * @param string $size 大小标识，b大，m中，s小
     * @return string
     */
    public function fetchAvatarByUid($uid, $size = 'm')
    {
        $user = $this->fetchByUid($uid);
        if (empty($user)) {
            return '';
        }
        if ($size == 'b') {
            // 大头像
            $avatar = $user['avatar_big'];
        } elseif ($size == 's') {
            // 小头像
            $avatar = $user['avatar_small'];
        } else {
            // 中头像
            $avatar = $user['avatar_middle'];
        }
        return $avatar;
    }

    /**
     * 根据岗位ID统计用户数（忽略辅助岗位）
     * @param integer $positionid
     * @return integer
     */
    public function countNumsByPositionId($positionid)
    {
        return User::model()->count('positionid = :positionid AND status != 2', array(':positionid' => $positionid));
    }

    /**
     * 根据角色ID统计用户数（忽略辅助角色）
     * @param integer $roleId
     * @return integer
     */
    public function countNumsByRoleId($roleId)
    {
        return User::model()->count('roleid = :roleid AND status != 2', array(':roleid' => $roleId));
    }

    /**
     * 计算用户总数（默认不包含被禁用和被锁定用户）
     *
     * @param boolean $containsDisabled 是否包含禁用用户，true 是、false 否
     * @param boolean $containsLocked 是否包含锁定用户，true 是、false 否
     * @return int
     */
    public function countNums($containsDisabled = false, $containsLocked = false)
    {
        $criteria = new \CDbCriteria();

        if ($containsDisabled === false) {
            $criteria = $criteria->addCondition(sprintf('status != %d', self::USER_STATUS_ABANDONED));
        }
        if ($containsLocked === false) {
            $criteria = $criteria->addCondition(sprintf('status != %d', self::USER_STATUS_LOCKED));
        }
        return $this->count($criteria->condition);
    }


    /**
     * 根据某个条件更新用户信息
     * @param mixed $uidX 用户ID字符串或数组
     * @param array $attributes 要更新字段值
     * @return integer
     * @author Sam 2015-08-21 <gzxgs@ibos.com.cn>
     */
    public function updateByConditions($uidX, $attributes = array(), $condition = "")
    {
        $cond = array(
            'AND',
            $this->uid_find_in_set($uidX),
            empty($condition) ? 1 : $condition,
        );
        return $this->updateAll($attributes, $cond);
    }

    public function checkIsExistByMobile($mobile)
    {
        $result = $this->fetch('mobile  = :mobile', array(':mobile' => $mobile));
        return !empty($result) ? true : false;
    }

    /**
     * 根据用户状态批量获取用户信息
     * @param  integer|array $status 用户状态 0,1,2 可以是数组也可以是整型
     * @return array         用户信息数组
     */
    public function fetchAllByStatus($status)
    {
        if (is_array($status)) {
            $status = implode(',', $status);
        }
        $result = $this->fetchAll(array(
            'condition' => "FIND_IN_SET( status, ':status' )",
            'params' => array(':status' => $status),
        ));
        return !empty($result) ? true : false;
    }

    /**
     * 查询未跟酷办公关联的用户id
     * @param  integer $limit
     * @return array  用户uid数组
     */
    public function fetchUnbind($limit = null)
    {
        $query = util\Ibos::app()->db->createCommand()
            ->select('u.uid')
            ->from('{{user}} u')
            ->where('u.status !=' . self::USER_STATUS_ABANDONED)
            ->andWhere($this->uidNotInBind());
        if ($limit) {
            $query = $query->limit($limit);
        }
        $list = $query->queryColumn();

        return $list;
    }

    /**
     * 查询跟酷办公关联但已禁用的用户id
     */
    public function fetchDeletebind()
    {
        $list = util\Ibos::app()->db->createCommand()
            ->select('u.uid')
            ->from('{{user}} u')
            ->leftJoin('{{user_binding}} b', 'u.uid = b.uid')
            ->where("u.status =" . self::USER_STATUS_ABANDONED . " and app='co' ")->queryColumn();
        return $list;
    }

    /**
     * 查询未跟酷办公关联的用户总数
     * @return array  用户uid数组
     */
    public function CountUnbind()
    {
        $list = util\Ibos::app()->db->createCommand()
            ->select('count(uid)')
            ->from('{{user}}')
            ->where("status != " . self::USER_STATUS_ABANDONED . " and uid not in (select uid from {{user_binding}} where app='co' )")->queryColumn();
        return $list;
    }

    /**
     * 查询跟酷办公关联但已禁用的用户id总数
     */
    public function CountDelete()
    {
        $list = util\Ibos::app()->db->createCommand()
            ->select('count(u.uid)')
            ->from('{{user}} u')
            ->leftJoin('{{user_binding}} b', 'u.uid = b.uid')
            ->where("u.status =" . self::USER_STATUS_ABANDONED . " and app='co' ")->queryColumn();
        return $list;
    }

    /**
     * 按条件查询未跟酷办公关联的用户id
     * @param $start
     * @param $length
     * @return array 用户uid数组
     */
    public function fetchPartUnbind($start, $length)
    {
        $list = Ibos::app()->db->createCommand()
            ->select('u.uid')
            ->from('{{user}} u')
            ->where('u.status !=' . self::USER_STATUS_ABANDONED)
            ->andWhere($this->uidNotInBind())
            ->limit($length)
            ->offset($start)
            ->queryColumn();;
        return $list;
    }

    /**
     * 通过真实姓名和手机拿取uid
     * @param $realName
     * @param $phone
     * @return bool
     */
    public function fetchUidByRealNameAndPhone($realName, $phone)
    {
        $result = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where('realname = :name AND mobile = :phone', array(':name' => $realName, ':phone' => $phone))
            ->queryScalar();
        return (!empty($result)) ? $result : '';
    }

    /**
     * 通过真实姓名和手机拿取uid
     * @param $realName
     * @param $phone
     * @return bool
     */
    public function fetchDeptIDAndPositionIdByRealNameAndPhone($realName, $phone)
    {
        $result = Ibos::app()->db->createCommand()
            ->select('uid')
            ->from($this->tableName())
            ->where('realname = :name AND mobile = :phone', array(':name' => $realName, ':phone' => $phone))
            ->queryScalar();
        return (!empty($result)) ? $result : '';
    }

    /**
     * 获取部门id 职位id
     * @param $uid
     * @return array
     */
    public function fetchDeptIdAndMore($uid)
    {
        $result = self::fetch(array(
            'select' => array('deptid', 'positionid', 'roleid'),
            'condition' => 'uid=:uid',
            'params' => array(':uid' => $uid)
        ));
        return $result;
    }

    /**
     * 拿取绑定表里没有的且没有禁用的
     * @param $msgPlatform
     * @param $limit
     * @param $offset
     * @return mixed
     */
    public function fetchNotBind($msgPlatform, $limit, $offset)
    {
        $result = Ibos::app()->db->createCommand()
            ->select(implode(',', $this->getSelectUser()))
            ->from('{{user}} u')
            ->leftJoin('{{user_profile}} up', ' `u`.`uid` = `up`.`uid` ')
            ->where($this->uidNotInBind($msgPlatform))
            ->andWhere('u.status !=' . self::USER_STATUS_ABANDONED)
            ->limit($limit)
            ->offset($offset)
            ->queryAll();
        return $result;
    }

    /**
     * 拿取绑定表里但状态是禁用的
     * @param $msgPlatform
     * @param $limit
     * @param $offset
     * @return mixed
     */
    public function fetchHasBindButDel($msgPlatform, $limit, $offset)
    {
        $result = Ibos::app()->db->createCommand()
            ->select(implode(',', $this->getSelectUser()))
            ->from('{{user}} u')
            ->leftJoin('{{user_profile}} up', ' `u`.`uid` = `up`.`uid` ')
            ->where($this->uidInBind($msgPlatform))
            ->andWhere('u.status =' . self::USER_STATUS_ABANDONED)
            ->limit($limit)
            ->offset($offset)
            ->queryAll();
        return $result;
    }

    /**
     * 根据用户 id 数组获取用户 uid 和 deptid 信息
     *
     * @param array $uidArr
     * @return array
     */
    public function fetchAllDeptId(array $uidArr)
    {
        $condition = util\StringUtil::generateInCondition('`uid`', $uidArr);

        return Ibos::app()->db->createCommand()
            ->select('uid, deptid')
            ->from($this->tableName())
            ->where($condition)
            ->queryAll();
    }

    /**
     * 构造查询字段
     */
    private function getSelectUser()
    {
        return array(
            'u.deptid',
            'u.uid',
            'u.mobile',
            'u.password',
            'u.salt',
            'u.email',
            'u.realname',
            'u.username nickname',
            'u.weixin wechat',
            'up.qq',
            'u.gender',
            'up.birthday',
            'up.address',
            'u.status',
        );
    }

    /**
     * uid在绑定表中
     * @param $msgPlatform
     * @return string
     */
    private function uidInBind($msgPlatform)
    {
        return "u.uid IN ( SELECT `uid` FROM {{user_binding}} WHERE"
        . " `app` = '{$msgPlatform}' )";
    }

    /**
     * uid不在绑定表中
     * @param $msgPlatform
     * @return string
     */
    private function uidNotInBind($msgPlatform = 'co')
    {
        return "u.uid NOT IN ( SELECT `uid` FROM {{user_binding}} WHERE"
        . " `app` = '{$msgPlatform}' )";
    }
}
