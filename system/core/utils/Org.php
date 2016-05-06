<?php

/**
 * 组织架构模块函数库
 *
 * @package application.app.user.utils
 * @version $Id: org.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\main\utils\Main as MainUtil;
use application\modules\position\utils\Position as PositionUtil;
use application\modules\role\utils\Role as RoleUtil;
use application\modules\user\utils\User as UserUtil;

class Org {

    /**
     * 更新组织架构js调用接口
     * @staticvar boolean $execute 执行标识，确保一个进程只执行一次更新操作
     * @return boolean 执行成功标识
     */
    public static function update() {
        static $execute = false;
        if ( !$execute ) {
            self::createStaticJs();
            $execute = true;
        }
        return $execute;
    }

    public static function hookSyncUser( $uid, $pwd = '', $syncFlag = 1 ) {
        $type = '';
        $imCfg = array();
        foreach ( IBOS::app()->setting->get( 'setting/im' ) as $imType => $config ) {
            if ( $config['open'] == '1' ) {
                $type = $imType;
                $imCfg = $config;
                break;
            }
        }
        if ( !empty( $type ) && !empty( $imCfg ) && $imCfg['syncuser'] == '1' ) {
            MainUtil::setCookie( 'hooksyncuser', 1, 30 );
            MainUtil::setCookie( 'syncurl', IBOS::app()->createUrl( 'dashboard/organizationApi/syncUser', array( 'type' => $type, 'uid' => $uid, 'pwd' => $pwd, 'flag' => $syncFlag ) ), 30 );
        }
    }

    /**
     * 生成组织架构静态文件JS
     * @return void
     */
    private static function createStaticJs() {
        //更新最新缓存到全局
        Cache::load( array( 'department', 'position' ), true );
        $unit = IBOS::app()->setting->get( 'setting/unit' );
        $department = DepartmentUtil::loadDepartment();
        $position = PositionUtil::loadPosition();
        $positionCategory = PositionUtil::loadPositionCategory();
        $role = RoleUtil::loadRole();
        $companyData = self::initCompany( $unit );
        $deptData = self::initDept( $department );
        $userData = self::initUser();
        $posData = self::initPosition( $position );
        $posCatData = self::initPositionCategory( $positionCategory );
        $roleData = self::initRole( $role );
        $default = file_get_contents( PATH_ROOT . '/static/js/src/org.default.js' );
        if ( $default ) {
            $patterns = array(
                '/\{\{(company)\}\}/',
                '/\{\{(department)\}\}/',
                '/\{\{(position)\}\}/',
                '/\{\{(users)\}\}/',
                '/\{\{(positioncategory)\}\}/',
                '/\{\{(role)\}\}/',
            );
            $replacements = array(
                $companyData,
                $deptData,
                $posData,
                $userData,
                $posCatData,
                $roleData
            );
            $new = preg_replace( $patterns, $replacements, $default );
            File::createFile( PATH_ROOT . '/data/org.js', $new );
            // 更新VERHASH
            Cache::update( 'setting' );
        }
    }

    /**
     * 初始化岗位分类数据
     * @return string
     */
    private static function initPositionCategory( $categorys ) {
        $catList = '';
        if ( !empty( $categorys ) ) {
            foreach ( $categorys as $catId => $category ) {
                $catList .= "{id: 'f_{$catId}',"
                        . " text: '{$category['name']}',"
                        . " name: '{$category['name']}',"
                        . " type: 'positioncategory',"
                        . " pId: 'f_{$category['pid']}',"
                        . " open: 1,"
                        . " nocheck:true},\n";
            }
        }
        return $catList;
    }

    /**
     * 初始化公司数据
     * @param array $unit 单位信息
     * @return string
     */
    private static function initCompany( $unit ) {
        $comList = "{id: 'c_0',"
                . " text: '{$unit['fullname']}',"
                . " name: '{$unit['fullname']}',"
                . " iconSkin: 'department',"
                . " type: 'department',"
                . " enable: 1,"
                . " open: 1},\n";
        return $comList;
    }

    /**
     * 初始化部门静态文件
     * @param array $department 部门信息数组
     * @return string
     */
    private static function initDept( $department ) {
        $deptList = '';
        //针对情况1的解决办法：
        //判断是否是字符串，是的话反序列化
        if ( !is_array( $department ) ) {
            //反序列化失败返回false
            $department = StringUtil::utf8Unserialize( $department );
        }
        if ( !empty( $department ) && is_array( $department ) ) {
            foreach ( $department as $deptId => $dept ) {
                $deptList .= "{id: 'd_{$deptId}',"
                        . " text: '{$dept['deptname']}',"
                        . " name: '{$dept['deptname']}',"
                        . " iconSkin: 'department',"
                        . " type: 'department',"
                        . " pId: 'd_{$dept['pid']}',"
                        . " enable: 1,"
                        . " open: 1},\n";
            }
        } else {
            //do nothing
        }
        return $deptList;
    }

    /**
     * 初始用户静态文件
     * @param array $users 用户信息数组
     * @return string
     */
    private static function initUser() {
        $userArray = UserUtil::getOrgJsData();
        $userList = '';
        if ( !empty( $userArray['userArray'] ) ) :
            foreach ( $userArray['userArray'] as $user ) :
                $deptRelated = !empty( $userArray['deptRelated'][$user['uid']] ) ? $userArray['deptRelated'][$user['uid']] : array();
                $deptArray = array_merge( $deptRelated, array( $user['deptid'] ) );
                $deptStr = StringUtil::wrapId( $deptArray, 'd' );
                $positionRelated = !empty( $userArray['positionRelated'][$user['uid']] ) ? $userArray['positionRelated'][$user['uid']] : array();
                $positionArray = array_merge( $positionRelated, array( $user['positionid'] ) );
                $positionStr = StringUtil::wrapId( $positionArray, 'p' );
                $roleRelated = !empty( $userArray['roleRelated'][$user['uid']] ) ? $userArray['roleRelated'][$user['uid']] : array();
                $roleArray = array_merge( $roleRelated, array( $user['roleid'] ) );
                $roleStr = StringUtil::wrapId( $roleArray, 'r' );
                $space_url = "?r=user/home/index&uid=" . $user['uid'];
                // 头像
                $avatarArray = Org::getDataStatic( $user['uid'], 'avatar', 'small', true );
                $userList .= "{id: 'u_{$user['uid']}',
                text: '{$user['realname']}',
                name: '{$user['realname']}',
                phone: '{$user['mobile']}',
                iconSkin: 'user',
                type: 'user',
                enable: 1,
                imgUrl:'{$avatarArray['small']}',
                avatar_small:'{$avatarArray['small']}',
                avatar_middle:'{$avatarArray['middle']}',
                avatar_big:'{$avatarArray['big']}',
                spaceurl:'{$space_url}',
                department:'{$deptStr}',
                role:'{$roleStr}',
                position: '{$positionStr}'},\n";
            endforeach;
        endif;
        return $userList;
    }

    /**
     * 获取静态资源
     * @param string $uid
     * @param string $type
     * @param string $size
     * @return string
     */
    public static function getDataStatic( $uid, $type, $size = 'small', $returnArray = false ) {
        if ( $type == 'avatar' ) {
            $path = './data/avatar/';
        } else {
            $path = './data/home/';
        }
        $dir = (int) ($uid / 100);
        $staticFile = $dir . '/' . $uid . '_' . $type . '_' . $size . '.jpg';
        if ( strtolower( ENGINE ) == 'local' ) {
            $fileExists = file_exists( $path . $staticFile );
        } else {
            require_once PATH_ROOT . '/system/extensions/enginedriver/sae/SAEFile.php';
            $file = new SAEFile();
            $path = $file->fileName( trim( $path, './' ) );
            $fileExists = $file->fileExists( $path . $staticFile );
        }
        $string = $fileExists ? $path . $dir . '/' . $uid . '_' . $type : $path . 'no' . $type;
        if ( true === $returnArray ) {
            $returnArray = array(
                'small' => $string . '_small.jpg',
                'big' => $string . '_big.jpg',
                'middle' => $string . '_middle.jpg',
            );
            return $returnArray;
        } else {
            return $string . '_' . $size . '.jpg';
        }
    }

    /**
     * 初始化岗位信息数据
     * @param array $position 岗位信息数组
     * @return array
     */
    private static function initPosition( $position ) {
        $posList = '';
        if ( !is_array( $position ) ) {
            $position = StringUtil::utf8Unserialize( $position );
        }
        if ( !empty( $position ) && is_array( $position ) ) {
            foreach ( $position as $posId => $pos ) {
                $posList .= "{id: 'p_{$posId}',"
                        . " text: '{$pos['posname']}',"
                        . " name: '{$pos['posname']}', "
                        . " iconSkin: 'position', "
                        . " type: 'position', "
                        . " pId:'f_{$pos['catid']}', "
                        . " enable: 1},\n ";
            }
        }
        return $posList;
    }

    private static function initRole( $role ) {
        $roleList = '';
        $role = !is_array( $role ) ? StringUtil::utf8Unserialize( $role ) : $role;
        if ( !empty( $role ) && is_array( $role ) ):
            foreach ( $role as $roleid => $row ):
                $roleList .= "{id: 'r_{$roleid}',"
                        . " text: '{$row['rolename']}',"
                        . " name: '{$row['rolename']}', "
                        . " roletype: '{$row['roletype']}', "
                        . " iconSkin: 'role', "
                        . " type: 'role', "
                        . " enable: 1, "
                        . " open: 1},\n ";
            endforeach;
        endif;
        return $roleList;
    }

}
