<?php

/**
 * setting表的数据层操作文件。
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * setting表的数据层操作类。
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.main.model
 * @version $Id: Setting.php 6882 2016-04-18 06:39:04Z tanghang $
 */

namespace application\modules\main\model;

use application\core\model\Model;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\IBOS;
use application\core\utils\String;

class Setting extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{setting}}';
    }

    /**
     * 根据skey获取对应的设置值
     * @param string $sKey
     * @return string
     * @author Ring
     */
    public function fetchSettingValueByKey( $sKey ) {
        $value = IBOS::app()->db->createCommand()
                ->select( 'svalue' )
                ->from( $this->tableName() )
                ->where( " `skey` = '{$sKey}' " )
                ->queryScalar();
        return $value;
    }

    /**
     * 查找多个skey对应的value,返回key=>value关联数组
     * @param string $sKeys 逗号分隔的key
     * @return array
     */
    public function fetchSettingValueByKeys( $sKeys, $autoUnserialize = false, $scope = array() ) {
        $return = array();
        $record = $this->fetchAll( "FIND_IN_SET(skey,'{$sKeys}')" );
        if ( !empty( $record ) ) {
            foreach ( $record as $value ) {
                if ( $autoUnserialize ) {
                    if ( !empty( $scope ) ) {
                        if ( in_array( $value['skey'], $scope ) ) {
                            $return[$value['skey']] = String::utf8Unserialize( $value['svalue'] );
                        } else {
                            $return[$value['skey']] = $value['svalue'];
                        }
                    } else {
                        $return[$value['skey']] = String::utf8Unserialize( $value['svalue'] );
                    }
                } else {
                    $return[$value['skey']] = $value['svalue'];
                }
            }
        }
        return $return;
    }

    /**
     * 根据skey和svalue更新对应数据
     * @param string $sKey
     * @param mixed $sValue
     * @return boolean
     * @author Ring
     */
    public function updateSettingValueByKey( $sKey, $sValue ) {
        $sValue = is_array( $sValue ) ? serialize( $sValue ) : $sValue;
        $updateResult = $this->modify( $sKey, array( 'svalue' => $sValue ) );
        return (bool) $updateResult;
    }

    /**
     * 获取全部设置
     * @return array
     */
    public function fetchAllSetting() {
        $setting = array();
        $records = IBOS::app()->db->createCommand()
                ->select( '*' )
                ->from( $this->tableName() )
                ->queryAll();
        foreach ( $records as $record ) {
            $isSerialized = ($record['svalue'] == serialize( false ) || String::utf8Unserialize( $record['svalue'] ) !== false);
            $setting[$record['skey']] = $isSerialized ? String::utf8Unserialize( $record['svalue'] ) : $record['svalue'];
        }
        return $setting;
    }

    /**
     * 设置iboscloud的值里的isopen
     * @param string $isOpen 1或者0
     */
    public function SetIbosCloudIsOpen( $isOpen ) {
        $ibosCloud = $this->fetchSettingValueByKey( 'iboscloud' );
        $ibosCloudArr = String::utf8Unserialize( $ibosCloud );
        $ibosCloudArr['isopen'] = $isOpen;
        $str = serialize( $ibosCloudArr );
        $this->updateSettingValueByKey( 'iboscloud', $str );
    }

    public function getIbosCloudIsOpen() {
        $ibosCloud = $this->fetchSettingValueByKey( 'iboscloud' );
        $ibosCloudArray = String::utf8Unserialize( $ibosCloud );
        return $ibosCloudArray['isopen'] ? true : false;
    }

    /**
     * 检查setting里的值是否存在
     * @param mixed $settingX skey值，数组或者逗号字符串
     * @return 返回不存在的skey值
     */
    public function checkSettingExist( $settingX ) {
        $settingString = is_array( $settingX ) ? implode( ',', $settingX ) : $settingX;
        $value = IBOS::app()->db->createCommand()
                ->select( 'skey' )
                ->from( $this->tableName() )
                ->where( " FIND_IN_SET( `skey`, '{$settingString}' )" )
                ->queryColumn();
        return array_diff( explode( ',', $settingString ), $value );
    }

    /**
     * 删除skey的值为指定的行
     * @param mixed $settingX skey数组或者逗号字符串
     * @return integer 影响的行数
     */
    public function deleteSettingValue( $settingX ) {
        $settingString = is_array( $settingX ) ? implode( ',', $settingX ) : $settingX;
        $count = $this->deleteAll( " FIND_IN_SET( `skey`,'{$settingString}' )" );
        return $count;
    }

}
