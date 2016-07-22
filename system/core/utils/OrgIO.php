<?php

namespace application\core\utils;

use application\modules\dashboard\model\Cache as CacheModel;
use application\modules\department\model\Department;
use application\modules\department\model\DepartmentRelated;
use application\modules\main\utils\Main;
use application\modules\user\model\User;
use application\modules\user\model\UserCount;
use application\modules\user\model\UserProfile;
use application\modules\user\model\UserStatus;

/**
 * 组织架构导入导出类
 *
 * @namespace application\core\utils
 * @filename OrgIO.php
 * @encoding UTF-8
 * @author mumu <2317216477@qq.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2015 IBOS Inc
 * @datetime 2015-12-30 9:55:35
 * @version $Id: OrgIO.php 7251 2016-05-26 13:30:32Z tanghang $
 */
class OrgIO {

    /**
     * 格式化即将要插入到user表里的数据
     * @param array $row 需要导入的用户数据，二维数组
     * @param array $config 举个栗子
     * 这里的二维数组有可能是两种格式：
     * [
     *      [
     *          'name'=>'user1',
     *          'pwd'=>'password1'
     *      ],
     *    ......
     * ]
     * 和
     * [
     *      [
     *          '0'=>'user1',
     *          '1'=>'password1',
     *      ],
     *    ......
     * ]
     * 这时候，$config 对应的就是
     * [
     *      'username'=>'name',
     *      'password'=>'pwd',
     * ]
     * 和
     * [
     *      'username'=>0,//或者'0'
     *      'password'=>1,
     * ]
     * @return array
     */
    public static function formatUserData( $row, $config ) {
        $mobile = $row[$config['mobile']];
        $password = $row[$config['password']];
        $realname = $row[$config['realname']];
        $gender = $row[$config['gender']];
        $email = $row[$config['email']];
        $wechat = $row[$config['wechat']];
        $jobnumer = $row[$config['jobnumer']];
        $username = $row[$config['username']];
        $salt = StringUtil::random( 6 );
        $origPass = !empty( $password ) ? $password : '123456'; //默认密码为123456
        $data = array(
            'salt' => $salt,
            'username' => !empty( $username ) ? trim( $username ) : '', // 用户名
            'password' => !empty( $origPass ) ? md5( md5( trim( $origPass ) ) . $salt ) : '', // 密码
            'realname' => !empty( $realname ) ? trim( $realname ) : '', // 真实姓名
            'gender' => !empty( $gender ) && trim( $gender ) == '女' ? 0 : 1, // 性别
            'mobile' => !empty( $mobile ) ? trim( $mobile ) : '', // 手机
            'email' => !empty( $email ) ? trim( $email ) : '', // 邮箱
            'weixin' => !empty( $wechat ) ? trim( $wechat ) : '', // 微信
            'jobnumber' => !empty( $jobnumer ) ? trim( $jobnumer ) : '', // 工号
        );
        return $data;
    }

    /**
     * 格式化即将要插入到userProfile表的用户数据
     * @param integer $uid 用户UID
     * @param array $row 需要导入的用户数据，二维数组
     * @param array $config @see self::formatUserData
     * @return array
     */
    public static function formatUserProfileData( $uid, $row, $config ) {
        $birthday = $row[$config['birthday']];
        $telephone = $row[$config['telephone']];
        $address = $row[$config['address']];
        $qq = $row[$config['qq']];
        $bio = $row[$config['bio']];
        $profileData = array(
            'uid' => $uid,
            'birthday' => !empty( $birthday ) ? strtotime( $birthday ) : 0,
            'telephone' => !empty( $telephone ) ? $telephone : '',
            'address' => !empty( $address ) ? $address : '',
            'qq' => !empty( $qq ) ? $qq : '',
            'bio' => !empty( $bio ) ? $bio : '',
        );
        return $profileData;
    }

    /**
     * 检查提交用户数据是否完整、或者有冲突
     * @param array $data 需要检查的数组
     * @param array $allUsers 检查的用户数组
     * @return array
     * err 为空时用户数据通过检查
     * 数据检查不通过分三种种情况：查重不通过、必填项未填、邮件格式不正确
     * 查重不通过时 uid 为对应重复用户的 uid
     * 另外两种情况 uid 为 0
     */
    protected static function checkUserData( $data, $allUsers = array() ) {
        if ( empty( $allUsers ) ) {
            $allUsers = User::model()->fetchAllSortByPk( 'uid' ); // 全部用户，包括锁定、禁用等
        }
        $convert = array(
            'username' => array(),
            'mobile' => array(),
            'email' => array(),
            'jobnumber' => array(),
        );
        foreach ( $allUsers as $user ) {
            !empty( $user['username'] ) && $convert['username'][] = $user['username']; // 已存在的用户名
            !empty( $user['mobile'] ) && $convert['mobile'][] = $user['mobile']; // 已存在的手机号
            !empty( $user['email'] ) && $convert['email'][] = $user['email']; // 已存在的邮箱
            !empty( $user['jobnumber'] ) && $convert['jobnumber'][] = $user['jobnumber']; // 已存在的工号
        }
        // 邮件格式匹配正则
        $emailPreg = "/^[\w\-\.]+@[\w\-]+(\.\w+)+$/";
        $err = '';
        if ( empty( $data['password'] ) || empty( $data['realname'] ) || empty( $data['mobile'] ) ) {
            $err = '手机、密码、真实姓名不能为空';
        } else if ( !empty( $data['username'] ) && in_array( $data['username'], $convert['username'] ) ) {
            $err = $data['username'] . '用户名已存在';
        } else if ( in_array( $data['mobile'], $convert['mobile'] ) ) {
            $err = $data['mobile'] . '手机号码已存在';
        } else if ( !empty( $data['email'] ) && in_array( $data['email'], $convert['email'] ) ) {
            $err = $data['email'] . '邮箱已存在';
        } else if ( !empty( $data['jobnumber'] ) && in_array( $data['jobnumber'], $convert['jobnumber'] ) ) {
            $err = $data['jobnumber'] . '工号已存在';
        } else if ( !empty( $data['email'] ) && !StringUtil::isEmail( $data['email'] ) ) {
            $err = $data['email'] . '邮件格式错误';
        }
        return $err;
    }

    /**
     * 导入用户数据到IBOS
     * @param array $data 需要导入的用户数据，二维数组
     * @param array $config @see self::formatUserData
     * @return array 返回导入失败的提示信息数组
     */
    public static function import( $data, $config ) {
        CacheModel::model()->deleteAll( "`cachekey` = 'userimportfail'" );
        set_time_limit( 0 ); //避免php脚本超时
        // $field = in_array( Env::getRequest( 'field' ), array( 0, 1, 2, 3, 4 ) ) ? Env::getRequest( 'field' ) : 0;
        // $op = in_array( Env::getRequest( 'op' ), array( 'update', 'ignore' ) ) ? Env::getRequest( 'op' ) : 'ignore';
        $err = array();
        $successCount = 0;
        $newUser = array();
        if ( !empty( $data ) && is_array( $data ) ) {
            $count = count( $data );
            Main::checkLicenseLimit( false, $count ); //检查授权人数
            $currentDeptA = self::findDeptAWithFormat(); //取出所有的部门

            $allUsers = User::model()->fetchAllSortByPk( 'uid' ); // 取出全部用户，包括锁定、禁用等, 等下做判定, 避免放在循环中影响效率, 注意,为了能匹配实时插入的数据,要在循环中增加新插入的用户
            foreach ( $data as $k => $row ) {
                $userData = self::formatUserData( $row, $config );
                $result = self::checkUserData( $userData, $allUsers );
                if ( !empty( $result ) ) {
                    $err[$k] = $userData;
                    $err[$k]['reason'] = $result;
                } else {
                    //-------先找出部门是否存在,不存在则创建-----
                    $deptidA = array();
                    $department = $row[$config['department']];
                    if ( !empty( $department ) ) {
                        $explodeDeptA = array_filter( explode( ',', $department ) );
                        foreach ( $explodeDeptA as $departS ) {
                            $deptidA[] = self::setDept( explode( '/', $departS ), $currentDeptA );
                        }
                        $userData['deptid'] = array_shift( $deptidA );
                    }
                    //插入用户
                    $newId = User::model()->add( $userData, true );
                    if ( $newId ) {
                        UserCount::model()->add( array( 'uid' => $newId ) );
                        $ip = IBOS::app()->setting->get( 'clientip' );
                        UserStatus::model()->add(
                                array(
                                    'uid' => $newId,
                                    'regip' => $ip,
                                    'lastip' => $ip
                                )
                        );
                        $profileData = self::formatUserProfileData( $newId, $row, $config );
                        //往user_profile添加相关数据，即使为空，要不然会报错
                        UserProfile::model()->add( $profileData );

                        //插入辅助部门关系
                        if ( !empty( $deptidA ) ) {
                            foreach ( $deptidA as $did ) {
                                DepartmentRelated::model()->add(
                                        array(
                                            'deptid' => $did,
                                            'uid' => $newId,
                                ) );
                            }
                        }

                        // 记录新加的用户,待后续处理
                        $origPass = !empty( $row[$config['password']] ) ? $row[$config['password']] : '123456'; //默认密码为123456
                        $newUser[$newId] = $origPass;
                        $successCount++;
                    }
                }
            }
            if ( $successCount > 0 ) {
                // 同步用户钩子
                foreach ( $newUser as $newId => $origPass ) {
                    Org::hookSyncUser( $newId, $origPass, 1 );
                }
                // 更新组织架构js调用接口
                Org::update();
            }
            if ( !empty( $err ) ) {
                CacheModel::model()->add( array( 'cachekey' => 'userimportfail', 'cachevalue' => serialize( $err ) ) );
            }
            return array(
                'isSuccess' => true,
                'successCount' => $successCount,
                'errorCount' => count( $err ),
                'url' => IBOS::app()->createUrl(
                        'dashboard/user/import', array(
                    'op' => 'downError',
                        )
                )
            );
        } else {
            return array(
                'isSuccess' => true,
                'successCount' => 0,
                'errorCount' => 0,
                'url' => '',
            );
        }
    }

    /**
     * 递归处理格式为"AAA/BBB"的部门，不存在则创建
     * @param array $explodeDeptA 如array('AAA','BBB')
     * @param array $currentDeptA 读取的当前部门情况，格式：
     * [
     * '0'=>[
     *          'deptid'=>'1',
     *          'deptname'=>'name',
     *          'pid'=>'0',//该数组键即是pid，值里的pid可有可无
     *      ],
     * ......
     * ]
     * @param integer $pid 父部门id
     * @return integer 最终的部门id，比如这里指的是BBB的部门id
     */
    private static function setDept( $explodeDeptA, &$currentDeptA, $pid = 0 ) {
        $deptid = 0;
        $dept = trim( array_shift( $explodeDeptA ) );
        if ( $dept !== NULL ) {
            if ( isset( $currentDeptA[$pid] ) ) {
                foreach ( $currentDeptA[$pid] as $d ) {
                    if ( $d['deptname'] == $dept ) {
                        $deptid = $d['deptid'];
                    }
                }
            }
            if ( $deptid === 0 ) {
                $deptid = Department::model()->add( array(
                    'deptname' => $dept,
                    'pid' => $pid,
                        ), true );
                if ( isset( $currentDeptA[$pid] ) ) {
                    $currentDeptA[$pid] = array_merge(
                            $currentDeptA[$pid], array(
                        array(
                            'deptid' => $deptid,
                            'deptname' => $dept,
                        ),
                            )
                    );
                } else {
                    $currentDeptA[$pid] = array(
                        array(
                            'deptid' => $deptid,
                            'deptname' => $dept,
                        ),
                    );
                }
            }
            if ( !empty( $explodeDeptA ) ) {
                $pid = $deptid;
                return self::setDept( $explodeDeptA, $currentDeptA, $pid );
            } else {
                return $deptid;
            }
        }
    }

    /**
     * 获取当前部门
     * @return array 格式@see self::setDept的参数$currentDeptA
     */
    private static function findDeptAWithFormat() {
        $return = array();
        $list = IBOS::app()->db->createCommand()
                ->select( 'deptid,deptname,pid' )
                ->from( Department::model()->tableName() )
                ->queryAll();
        if ( !empty( $list ) ) {
            foreach ( $list as $row ) {
                $return[$row['pid']][] = $row;
            }
        }
        return $return;
    }

}
