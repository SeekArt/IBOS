<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\dashboard\controllers\BaseController;
use application\modules\main\model\Setting;

class OptimizeController extends BaseController {

	const DEFAULT_SEARCH_MODULE = 'email,diary,article'; // 默认支持的全文搜索模块

	/**
	 * 性能优化 - 内存设置
	 * @return void
	 */

	public function actionCache() {
		if ( LOCAL ) {
			$operation = Env::getRequest( 'op' );
			if ( $operation == 'clear' ) {
				IBOS::app()->cache->clear();
				$this->success( IBOS::lang( 'Operation succeed', 'message' ) );
			}
			$options = IBOS::app()->cache->options;
			$cacheExtension = IBOS::app()->cache->getExtension();
			$cacheType = $options['type'];
			$caches = array();
			foreach ( $cacheExtension as $cacheName => $enable ) {
				$index = ucfirst( $cacheName );
				$caches[$index]['extension'] = (boolean) $enable;
				$caches[$index]['op'] = ( strcasecmp( $cacheType, $cacheName ) === 0);
			}
			$data = array( 'list' => $caches );
			$this->render( 'cache', $data );
		} else {
			echo IBOS::lang( 'Not compatible service', 'message' );
		}
	}

	/**
	 * 性能优化 - 全文搜索
	 * @return void
	 */
	public function actionSearch() {
		if ( LOCAL ) {
			// 先取出所有相关记录
			$sphinxFields = 'sphinxon,sphinxmsgindex,sphinxsubindex,sphinxmaxquerytime,sphinxlimit,sphinxrank';
			$sphinx = Setting::model()->fetchSettingValueByKeys( $sphinxFields );
			// -----------------
			$formSubmit = Env::submitCheck( 'searchSubmit' );
			if ( $formSubmit ) {
				$operation = $_POST['operation'];
				// 接收数据
				$data = array(
					'sphinxon' => isset( $_POST['sphinxon'][$operation] ) ? 1 : 0,
					'sphinxsubindex' => $_POST['sphinxsubindex'][$operation],
					'sphinxmsgindex' => $_POST['sphinxmsgindex'][$operation],
					'sphinxmaxquerytime' => $_POST['sphinxmaxquerytime'][$operation],
					'sphinxlimit' => $_POST['sphinxlimit'][$operation],
					'sphinxrank' => $_POST['sphinxrank'][$operation],
				);
				// 更新相应的$operation选项
				foreach ( $sphinx as $sKey => $sValue ) {
					$value = unserialize( $sValue );
					$value[$operation] = $data[$sKey];
					Setting::model()->updateSettingValueByKey( $sKey, $value );
				}
				Cache::update( array( 'setting' ) );
				$this->success( IBOS::lang( 'Save succeed', 'message' ) );
			} else {
				$operation = Env::getRequest( 'op' );
				$moduleList = explode( ',', self::DEFAULT_SEARCH_MODULE );
				if ( !in_array( $operation, $moduleList ) ) {
					$operation = $moduleList[0];
				}
				$data['operation'] = $operation;
				$data['moduleList'] = $moduleList;
				foreach ( $sphinx as $sKey => $sValue ) {
					$data[$sKey] = unserialize( $sValue );
				}
				$this->render( 'search', $data );
			}
		} else {
			echo IBOS::lang( 'Not compatible service', 'message' );
		}
	}

	/**
	 * 性能优化 - Sphinx控制
	 * @return void 
	 */
	public function actionSphinx() {
		if ( LOCAL ) {
			$formSubmit = Env::submitCheck( 'sphinxSubmit' );
			if ( $formSubmit ) {
				$sphinxHost = $_POST['sphinxhost'];
				$sphinxPort = $_POST['sphinxport'];
				Setting::model()->updateSettingValueByKey( 'sphinxhost', $sphinxHost );
				Setting::model()->updateSettingValueByKey( 'sphinxport', $sphinxPort );
				Cache::update( array( 'setting' ) );
				$this->success( IBOS::lang( 'Save succeed', 'message' ) );
			} else {
				$record = Setting::model()->fetchSettingValueByKeys( 'sphinxhost,sphinxport' );
				$sphinxPort = Setting::model()->fetchSettingValueByKey( 'sphinxport' );
				$data = array( 'sphinxHost' => $record['sphinxhost'], 'sphinxPort' => $record['sphinxport'] );
				$this->render( 'sphinx', $data );
			}
		} else {
			echo IBOS::lang( 'Not compatible service', 'message' );
		}
	}

}
