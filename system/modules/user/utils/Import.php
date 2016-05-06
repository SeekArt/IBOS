<?php

namespace application\modules\user\utils;

use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\department\model\Department as DepartmentModel;
use application\modules\department\model\DepartmentRelated as DepartmentRelatedModel;
use application\modules\main\utils\ImportParent;
use application\modules\user\model\User as UserModel;
use application\modules\user\model\UserCount as UserCountModel;
use application\modules\user\model\UserProfile as UserProfileModel;
use application\modules\user\model\UserStatus as UserStatusModel;

/**
 * Description
 *
 * @namespace application\modules\user\utils
 * @filename Import.php
 * @encoding UTF-8
 * @author mumu <2317216477@qq.com>
 * @link https://www.ibos.com.cn
 * @copyright Copyright &copy; 2012-2015 IBOS Inc
 * @datetime 2016-3-24 17:06:34
 * @version $Id: Import.php 6722 2016-03-31 01:30:57Z tanghang $
 */
class Import extends ImportParent {

    public $tplField = array(
        '手机号' => 'u.mobile',
        '密码' => 'u.password',
        '真实姓名' => 'u.realname',
        '性别' => 'u.gender',
        '邮箱' => 'u.email',
        '微信号' => 'u.weixin',
        '工号' => 'u.jobnumber',
        '用户名' => 'u.username',
        '生日' => 'up.birthday',
        '住宅电话' => 'up.telephone',
        '地址' => 'up.address',
        'QQ' => 'up.qq',
        '自我介绍' => 'up.bio',
        '岗位' => 'p.posname',
        '部门' => 'd.deptname',
        '角色' => 'r.rolename',
    );
    public $tableMap = array(
        'u' => '{{user}}',
        'up' => '{{user_profile}}',
        'p' => '{{position}}',
        'd' => '{{department}}',
        'r' => '{{role}}',
    );

    public function importUser() {
        return $this->importData( 'user' );
    }

    /**
     * 父类里importData里需要importDetail处理详细的数据
     * importData里的循环处理是通用的
     * @param integer $i 这个是父类中循环shift出数据时的一个顺序，用以全程给error的i赋值
     */
    public function importUserDetail( $i ) {
        $importUserRow = $this->import->importData['{{user}}'];
        if ( !StringUtil::isMobile( $importUserRow['mobile'] ) ) {
            $this->error[$i]['status'] = false;
            $this->error[$i]['text'] .= '手机号格式不正确;';
        }
        if ( !empty( $importUserRow['email'] ) && !StringUtil::isEmail( $importUserRow['email'] ) ) {
            $this->error[$i]['status'] = false;
            $this->error[$i]['text'] .='邮箱格式不正确;';
        }
        $importUserProfileRow = $this->import->importData['{{user_profile}}'];
        if ( !empty( $importUserRow['birthday'] ) && false === strtotime( $importUserProfileRow['birthday'] ) ) {
            $this->error[$i]['status'] = false;
            $this->error[$i]['text'] .= '生日的日期格式不正确;';
        }
        //------------------------------通用部分------------------------------
        $way = $this->import->check;
        $result = $this->refuseRepeat( $i, $way );
        $repeat = $result['repeat'];
        $row = $result['row'];
        if ( false === $this->error[$i]['status'] ) {
            return; //如果error状态为fasle，说明有不合格的，不再保存数据
        } else {
            $this->error[$i]['text'] .= '成功！';
        }
        //------------------------------    end    ------------------------------
        $updateUserData = array(
            'mobile' => $importUserRow['mobile'],
            'realname' => $importUserRow['realname'],
            'gender' => $importUserRow['gender'] == '男' ? 1 : 0,
            'email' => $importUserRow['email'],
            'weixin' => $importUserRow['weixin'],
            'jobnumber' => $importUserRow['jobnumber'],
            'username' => $importUserRow['username'],
        );
        $updateUserProfileData = array(
            'birthday' => strtotime( $importUserProfileRow['birthday'] ),
            'telephone' => $importUserProfileRow['telephone'],
            'address' => $importUserProfileRow['address'],
            'qq' => $importUserProfileRow['qq'],
            'bio' => $importUserProfileRow['bio'],
        );
        if ( $way == 'cover' && $repeat ) {//覆盖操作并且有重复值时的处理
            //user表处理
            $updateUserData['password'] = md5( md5( $importUserRow['password'] ) . $row['{{user}}']['salt'] );
            $uid = $row['{{user}}']['uid'];
            UserModel::model()->updateAll( $updateUserData, " `uid` = '{$uid}' " );
            UserProfileModel::model()->updateAll( $updateUserProfileData, " `uid` = '{$uid}' " );
        } else {//这里是nothing，或者为new和cover时，没有重复字段时的处理
            //user表处理
            $salt = StringUtil::random( 6 );
            $updateUserData['salt'] = $salt;
            $updateUserData['password'] = md5( md5( $importUserRow['password'] ) . $salt );
            $updateUserData['guid'] = StringUtil::createGuid();
            $updateUserData['createtime'] = TIMESTAMP;
            $uid = UserModel::model()->add( $updateUserData, true );
            if ( $way == 'cover' ) {
                UserProfileModel::model()->updateAll( $updateUserProfileData, " `uid` = '{$uid}' " );
            } else {
                //处理user的其他关联数据
                UserCountModel::model()->add( array( 'uid' => $uid ) );
                $ip = IBOS::app()->setting->get( 'clientip' );
                UserStatusModel::model()->add( array( 'uid' => $uid, 'regip' => $ip, 'lastip' => $ip ) );
                UserProfileModel::model()->add( array_merge( $updateUserProfileData, array( 'uid' => $uid ) ) );
            }
        }
        //部门的处理
        if ( !empty( $this->import->importData['{{department}}']['deptname'] ) ) {
            $departmentString = $this->import->importData['{{department}}']['deptname'];
            $allDepartment = IBOS::app()->db->createCommand()
                    ->select( 'deptid,deptname,pid' )
                    ->from( '{{department}}' )
                    ->queryAll();
            $departData = $this->findToCreateFolder( $allDepartment, $departmentString, 'deptid', 'pid', 'deptname' );
            $pid = $departData['pid'];
            if ( !empty( $departData['findArray'] ) ) {
                foreach ( $departData['findArray'] as $departname ) {
                    $pid = DepartmentModel::model()->add( array(
                        'pid' => $pid, 'deptname' => $departname
                            ), true );
                }
            }
            //循环结束后的pid其实是当前数据的部门id
            $deptid = IBOS::app()->db->createCommand()
                    ->select( 'deptid' )
                    ->from( '{{user}}' )
                    ->where( " `uid` = '{$uid}' " )
                    ->queryScalar();
            if ( empty( $deptid ) ) {
                UserModel::model()->updateAll( array( 'deptid' => $pid ), " `uid` = '{$uid}' " );
            } else {
                $deptid = IBOS::app()->db->createCommand()
                        ->select( 'deptid' )
                        ->from( '{{department_related}}' )
                        ->where( " `uid` = '{$uid}' AND 'deptid' = '{$pid}' " )
                        ->queryScalar();
                if ( empty( $deptid ) ) {
                    DepartmentRelatedModel::model()->add( array(
                        'deptid' => $deptid,
                        'uid' => $uid,
                    ) );
                }
            }
        }
    }

}
