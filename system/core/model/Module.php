<?php

/**
 * 模块model文件.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 */
/**
 * 模块表的模型处理类
 * @package application.core.model
 * @version $Id: Module.php 2877 2014-03-24 06:10:38Z zhangrong $
 */

namespace application\core\model;

use application\core\utils\Cache;
use application\core\utils\Ibos;

class Module extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{module}}';
    }

	/**
	 * 获得模块名称
	 * @staticvar null $modules 所有模块缓存
	 * @param string $moduleName 模块标识
	 * @return string 
	 */
	public function fetchNameByModule( $moduleName ) {
		static $modules = null;
		if ( !$modules ) {
			$modules = $this->fetchAllEnabledModule();
		}
		$module = isset( $modules[$moduleName] ) ? $modules[$moduleName] : $this->fetchByAttributes( array( 'module' => $moduleName ) );
		return (is_array( $module ) && isset( $module['name']) ) ? $module['name'] : '';
	}

    /**
     * 查找所有非系统模块
     * @return type
     */
    public function fetchAllNotCoreModule() {
        $modules = $this->fetchAllSortByPk( 'module', array(
            'condition' => "`iscore` = 0 AND `disabled` = 0",
            'order' => '`sort` ASC',
                )
        );
        return $modules;
    }

    /**
     * 查找所有非系统与非辅助模块
     * @return array
     */
    public function fetchAllClientModule() {
        $modules = $this->fetchAllSortByPk( 'module', array(
            'condition' => "`iscore` = 0 AND `disabled` = 0 AND `category` != ''",
            'order' => '`sort` ASC',
                )
        );
        return $modules;
    }

    /**
     * 获取所有可用的模块
     * @return array
     */
    public function fetchAllEnabledModule() {
        $module = Cache::get( 'module' );
        if ( $module == false ) {
            $criteria = array(
                'condition' => '`disabled` = 0',
                'order' => '`sort` ASC'
            );
            $module = $this->fetchAllSortByPk( 'module', $criteria );
            Cache::set( 'module', $module );
        }
        return $module;
    }

	public function findAllEnabledModuleArray() {
		return Ibos::app()->db->createCommand()
						->select()
						->from( $this->tableName() )
						->where( " `disabled` = '0' " )
						->order( " sort ASC " )
						->queryAll();
	}
}
