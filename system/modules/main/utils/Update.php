<?php

namespace application\modules\main\utils;

use application\core\model\Module as ModuleModel;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\core\utils\Org;
use application\modules\main\model\Setting;
use application\modules\role\utils\Role;
use application\modules\user\utils\User;
use CJSON;

/**
 * 更新数据类
 * 提供三种数据更新方法：
 * 1、更新数据
 * 2、更新静态文件
 * 3、更新模块配置
 * 都接收一个$position参数（其实应该是offset，只是为了名字不重复）
 * 返回ajaxReturn给前端循环
 * @namespace application\modules\main\utils
 * @filename Update.php
 * @encoding UTF-8
 * @author forsona <2317216477@qq.com>
 * @link https://github.com/forsona
 * @copyright Copyright &copy; 2012-2015 IBOS Inc
 * @datetime 2016-6-15 13:37:20
 * @version $Id$
 */
class Update
{

    public static function datas($position)
    {
        //是否需要更新用户cache
//		$settingStatus = Setting::model()->fetchSettingValueByKey( 'cacheuserstatus' );
//		if ( $settingStatus == '0' ) {
//			return array(
//				'isSuccess' => true,
//				'data' => array(
//					'process' => 'end',
//					'offset' => 0,
//					'total' => 0,
//				),
//				'msg' => '【数据缓存】：最近更新过用户数据，不需要更新'
//			);
//		} else {
        //暂时不设置这个状态值，不然可能一直都不更新
        $settingConfig = Setting::model()->fetchSettingValueByKey('cacheuserconfig');
        $settingConfigArray = CJSON::decode($settingConfig);
        //取较大的offset。毕竟前端过来的offset第一次都是0
        $offset = max($position, $settingConfigArray['offset']);
        //如果没有设置的话，一次更新1000个
        $limit = !empty($settingConfigArray['limit']) ? $settingConfigArray['limit'] : 1000;
        //如果是第一次，那么就先删除所有从uid开始的数据
        $uid = $settingConfigArray['uid'] ? $settingConfigArray['uid'] : 1;
        $total = 0;
        if ($position == '0') {
            Ibos::app()->db->createCommand()
                ->delete('{{cache_user_detail}}'
                    , " `uid` IN ( SELECT `uid` FROM {{user}}"
                    . " WHERE `status` = '0' AND"
                    . " `uid` >= {$uid} )");
            $count = Ibos::app()->db->createCommand()
                ->select('count(uid)')
                ->from('{{user}}')
                ->where(" `uid` NOT IN ( SELECT `uid` FROM {{cache_user_detail}} ) ")
                ->andWhere(" `status` = 0 ")
                ->queryScalar();
            $total = ceil($count / $limit);
        }

        $cacheOver = User::CacheUser(null, false, array(
            'limit' => $limit,
            'offset' => $offset,
        ));
        $setOffset = true === $cacheOver ? '0' : $offset;
        if (true === $cacheOver) {
            Ibos::app()->db->createCommand()
                ->update('{{setting}}', array(
                    'svalue' => '0',
                ), " `skey` = 'cacheuserstatus' ");
        }
        $maxUid = self::findMaxUidFromCache();
        $setUid = true === $cacheOver ? '1' : $maxUid + 1;
        Ibos::app()->db->createCommand()
            ->update('{{setting}}', array(
                'svalue' => CJSON::encode(array(
                    'limit' => $limit,
                    'offset' => $setOffset,
                    'uid' => $setUid,
                )),
            ), " `skey` = 'cacheuserconfig' ");
        return array(
            'isSuccess' => true,
            'msg' => $cacheOver ? '【数据缓存】：用户数据更新完成' : '【数据缓存】：正在更新用户数据',
            'data' => array(
                'process' => $cacheOver ? 'end' : 'continue',
                'offset' => $cacheOver ? 0 : $offset + $limit,
                'total' => $total,
            ),
        );
//		}
    }

    public static function statics($position)
    {
        $type = array('user', 'department', 'role', 'position', 'positioncategory');
        $typeName = array('用户', '部门', '角色', '岗位', '岗位分类');
        $total = 0;
        if ($position == '0') {
            LOCAL && Ibos::app()->assetManager->republicAll();
            $total = count($type);
        }

        if (isset($type[$position])) {
            Org::update(array($type[$position]));
            return array(
                'isSuccess' => true,
                'msg' => '【静态文件缓存】：【' . $typeName[$position] . '】静态文件完成',
                'data' => array(
                    'process' => 'continue',
                    'offset' => $position + 1,
                    'total' => $total,
                ),
            );
        } else {
            return array(
                'isSuccess' => true,
                'msg' => '【静态文件缓存】：所有静态文件更新完成',
                'data' => array(
                    'process' => 'end',
                    'offset' => 0,
                    'total' => 0,
                ),
            );
        }
    }

    public static function modules($position)
    {
        $allEnabledModuleArray = ModuleModel::model()->findAllEnabledModuleArray();
        $total = 0;
        if ($position == '0') {
            $total = count($allEnabledModuleArray);
        }
        if (isset($allEnabledModuleArray[$position])) {
            Module::updateConfig($allEnabledModuleArray[$position]['module']);
            return array(
                'isSuccess' => true,
                'msg' => '【模块配置文件】：【' . $allEnabledModuleArray[$position]['name'] . '】模块配置更新完成',
                'data' => array(
                    'process' => 'continue',
                    'offset' => $position + 1,
                    'total' => $total,
                ),
            );
        } else {
            Role::updateAuthItemByRoleid();
            return array(
                'isSuccess' => true,
                'msg' => '【模块配置文件】：所有模块配置更新完成',
                'data' => array(
                    'process' => 'end',
                    'offset' => 0,
                    'total' => 0,
                ),
            );
        }
    }

    public static function findMaxUidFromCache()
    {
        return Ibos::app()->db->createCommand()
            ->select('uid')
            ->from('{{cache_user_detail}}')
            ->limit(1)
            ->order('uid DESC')
            ->queryScalar();
    }

    /**
     * 重新设置更新缓存标识
     * @param boolean $updateFromMax 是否从最大的uid开始更新，如果true，则之前的数据不会被更新。默认false
     */
    public static function refreshCache($updateFromMax = false)
    {
        Ibos::app()->db->createCommand()
            ->update('{{setting}}', array(
                'svalue' => '1',
            ), " `skey` = 'cacheuserstatus' ");
        if (true === $updateFromMax) {
            $maxUid = self::findMaxUidFromCache();
            $settingConfig = Setting::model()->fetchSettingValueByKey('cacheuserconfig');
            $settingConfigArray = CJSON::decode($settingConfig);
            Ibos::app()->db->createCommand()
                ->update('{{setting}}', array(
                    'svalue' => CJSON::encode(array(
                        'limit' => $settingConfigArray['limit'],
                        'offset' => $settingConfigArray['offset'],
                        'uid' => $maxUid + 1,
                    )),
                ), " `skey` = 'cacheuserconfig' ");
        }
    }

}
