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
 * @version $Id: Setting.php 4965 2015-04-07 03:22:57Z tanghang $
 */

namespace application\modules\main\model;

use application\core\model\Model;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\String;

class Setting extends Model {

    public function init() {
        $this->cacheLife = 0;
        parent::init();
    }
	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{setting}}';
	}

    public function afterSave() {
        CacheUtil::update('Setting');
        CacheUtil::load('Setting');
        parent::afterSave();
    }
	/**
	 * 根据skey获取对应的设置值
	 * @param string $sKey
	 * @return string
	 * @author Ring
	 */
	public function fetchSettingValueByKey( $sKey ) {
		$record = $this->fetch( "skey='$sKey'" );
		if ( !empty( $record ) ) {
			return $record['svalue'];
		}
		return null;
	}

	/**
	 * 查找多个skey对应的value,返回key=>value关联数组
	 * @param string $sKeys 逗号分隔的key
	 * @return array
	 */
    public function fetchSettingValueByKeys($sKeys, $autoUnserialize = false, $scope = array()) {
		$return = array();
		$record = $this->fetchAll( "FIND_IN_SET(skey,'{$sKeys}')" );
		if ( !empty( $record ) ) {
			foreach ( $record as $value ) {
                if ($autoUnserialize) {
                    if (!empty($scope)) {
                        if (in_array($value['skey'], $scope)) {
                            $return[$value['skey']] = String::utf8Unserialize($value['svalue']);
                        } else {
                            $return[$value['skey']] = $value['svalue'];
			}
                    } else {
                        $return[$value['skey']] = String::utf8Unserialize($value['svalue']);
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
		$records = $this->findAll();
		foreach ( $records as $record ) {
			$value = $record->attributes;
            $isSerialized = ($value['svalue'] == serialize(false) || String::utf8Unserialize($value['svalue']) !== false);
            $setting[$value['skey']] = $isSerialized ? String::utf8Unserialize($value['svalue']) : $value['svalue'];
		}
		return $setting;
	}
	/**
	 * 设置iboscloud的值里的isopen
	 * @param string $isOpen 1或者0
	 */
	public function SetIbosCloudIsOpen( $isOpen ) {
		$ibosCloud = $this->fetchSettingValueByKey( 'iboscloud' );
        $ibosCloudArr = String::utf8Unserialize($ibosCloud);
		$ibosCloudArr['isopen'] = $isOpen;
		$str = serialize( $ibosCloudArr );
		$this->updateSettingValueByKey( 'iboscloud', $str );
	}

    public function getIbosCloudIsOpen() {
        $ibosCloud = $this->fetchSettingValueByKey('iboscloud');
        $ibosCloudArray = String::utf8Unserialize($ibosCloud);
        return $ibosCloudArray['isopen'] ? true : false;
    }
}
