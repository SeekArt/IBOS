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
	public function fetchSettingValueByKeys( $sKeys, $autoUnserialize = false ) {
		$return = array();
		$record = $this->fetchAll( "FIND_IN_SET(skey,'{$sKeys}')" );
		if ( !empty( $record ) ) {
			foreach ( $record as $value ) {
				$return[$value['skey']] = $autoUnserialize ? (array) unserialize( $value['svalue'] ) : $value['svalue'];
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
			$isSerialized = ($value['svalue'] == serialize( false ) || @unserialize( $value['svalue'] ) !== false);
			$setting[$value['skey']] = $isSerialized ? unserialize( $value['svalue'] ) : $value['svalue'];
		}
		return $setting;
	}
	/**
	 * 设置iboscloud的值里的isopen
	 * @param string $isOpen 1或者0
	 */
	public function SetIbosCloudIsOpen( $isOpen ) {
		$ibosCloud = $this->fetchSettingValueByKey( 'iboscloud' );
		$ibosCloudArr = unserialize( $ibosCloud );
		$ibosCloudArr['isopen'] = $isOpen;
		$str = serialize( $ibosCloudArr );
		$this->updateSettingValueByKey( 'iboscloud', $str );
	}

}
