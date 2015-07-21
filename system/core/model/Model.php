<?php

/**
 * 数据层操作的抽象基类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2014 IBOS Inc
 */
/**
 * 数据层操作的抽象基类,提供给所有Model封装过的基本操作
 * 
 * @package application.core.model
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\model;

use application\core\utils\Cache;
use application\core\utils\IBOS;
use application\core\utils\String;
use CActiveRecord;
use CException;

class Model extends CActiveRecord {

    /**
     * 是否允许缓存
     * @var mixed 
     */
    protected $allowCache;

    /**
     * 缓存生命周期
     * @var mixed 
     */
    protected $cacheLife = null;

    /**
     * 创建各个model实例后的执行方法，获取缓存设置
     * 如有需要子类可覆盖初始化方法init
     */
    public function init() {
        $cacheLife = $this->cacheLife !== null ? $this->cacheLife : null;
        if ( $cacheLife && Cache::check() ) {
            $this->cacheLife = $cacheLife;
            $this->allowCache = true;
        }
    }

    /**
     * 查询一条符合条件的数据，返回数组 不缓存
     * @param mixed $condition 条件字符串 || 数组 || criteria对象{@link CDbCriteria}
     * @param array $params 参数绑定到SQL语句
     * @return array 
     */
    public function fetch( $condition = '', $params = array() ) {
        $result = array();
        $record = $this->find( $condition, $params );
        if ( !empty( $record ) ) {
            $result = $record->attributes;
        }
        return $result;
    }

    /**
     * 如果缓存存在数据，则直接读取缓存。否则根据主键查找一条记录，返回数组格式
     * @return array
     */
    public function fetchByPk( $pk ) {
        //assert( '!empty($pk)' );
        $record = $this->fetchCache( $pk );
        if ( $record === false ) {
            $object = $this->findByPk( $pk );
            if ( is_object( $object ) ) {
                $record = $object->attributes;
                if ( $this->getIsAllowCache() ) {
                    Cache::set( $this->getCacheKey( $pk ), $record, $this->cacheLife );
                }
            } else {
                $record = null;
            }
        }
        return $record;
    }

    /**
     * 封装parent::findByAttributes
     * @param type $attributes
     * @param type $condition
     * @param type $params
     * @return array
     */
    public function fetchByAttributes( $attributes, $condition = '', $params = array() ) {
        $result = array();
        $record = $this->findByAttributes( $attributes, $condition, $params );
        if ( !empty( $record ) ) {
            $result = $record->attributes;
        }
        return $result;
    }

    /**
     * 查询所有数据，返回一个数组集合 不缓存 
     * @param mixed $condition 条件字符串 || 数组 || criteria对象{@link CDbCriteria}
     * @param array $params 参数绑定到SQL语句
     * @return array 
     */
    public function fetchAll( $condition = '', $params = array() ) {
        $result = array();
        $records = $this->findAll( $condition, $params );
        if ( !empty( $records ) ) {
            foreach ( $records as $record ) {
                $result[] = $record->attributes;
            }
        }
        return $result;
    }

    /**
     * 封装parent::findAllByAttributes
     * @param type $attributes
     * @param string $condition
     * @param array $params
     * @return type
     */
    public function fetchAllByAttributes( $attributes, $condition = '', $params = array() ) {
        $result = array();
        $records = $this->findAllByAttributes( $attributes, $condition, $params );
        if ( !empty( $records ) ) {
            foreach ( $records as $record ) {
                $result[] = $record->attributes;
            }
        }
        return $result;
    }

    /**
     * 顾名思义，返回已主键为索引的数组
     * @param string $pk
     * @param mixed $condition 条件字符串 || 数组 || criteria对象{@link CDbCriteria}
     * @param array $params 参数绑定到SQL语句
     * @return array
     */
    public function fetchAllSortByPk( $pk, $condition = '', $params = array() ) {
        $result = array();
        $records = $this->findAll( $condition, $params );
        if ( !empty( $records ) ) {
            foreach ( $records as $record ) {
                $row = $record->attributes;
                $result[$row[$pk]] = $row;
            }
        }
        return $result;
    }

    /**
     *  如果缓存存在数据，则直接读取缓存。否则根据主键查找指定pks记录，返回数组格式
     * @param array $pks
     * @return array
     */
    public function fetchAllByPk( $pks ) {
        $record = $this->fetchCaches( $pks );
        if ( $record === false || count( $pks ) != count( $record ) ) {
            if ( is_array( $record ) && !empty( $record ) ) {
                $pks = array_diff( $pks, array_keys( $record ) );
            }
            if ( $record === false ) {
                $record = array();
            }
            if ( !empty( $pks ) ) {
                $records = $this->findAllByPk( array_merge( $pks ) );
                if ( !empty( $records ) ) {
                    foreach ( $records as $rec ) {
                        $pk = $rec->getPrimaryKey();
                        $record[$pk] = $rec->attributes;
                        if ( $this->getIsAllowCache() ) {
                            Cache::set( $this->getCacheKey( $pk ), $rec->attributes, $this->cacheLife );
                        }
                    }
                }
            }
        }
        return $record;
    }

    /**
     * 增加一条记录。封装自AR::insert方法。
     * @param array $attributes 要插入的数据
     * @param boolean $returnNewId 是否返回插入的ID
     * @param boolean $replace 是否替换插入
     * @return mixed 返回插入的id或者插入成功与否
     */
    public function add( $attributes, $returnNewId = false, $replace = false ) {
        $attrs = $this->getAttributes();
        $schema = $this->getTableSchema();
        foreach ( $attrs as $attr => $val ) {
            if ( isset( $attributes[$attr] ) ) {
                $this->setAttribute( $attr, $attributes[$attr] );
            } else {
                $column = $schema->getColumn( $attr );
                if ( !is_null( $column ) ) {
                    if ( $column->isPrimaryKey ) {
                        continue;
                    }
                    $this->setAttribute( $attr, (string) $column->defaultValue );
                }
            }
        }
        if ( $replace ) {
            if ( $this->refresh() ) {
                $this->setIsNewRecord( false );
            } else {
                $this->setIsNewRecord( true );
            }
        } else {
            $this->setIsNewRecord( true );
        }
        $status = $this->save();
        $lastInsert = $this->getPrimaryKey();
        $this->setOldPrimaryKey( null );
        $this->setPrimaryKey( null );
        if ( $returnNewId ) {
            return $lastInsert;
        } else {
            return $status;
        }
    }

    /**
     * 根据主键id更新记录。封装AR updateByPk方法，使之调用beforeSave方法
     * @param mixed $pk 主键
     * @param array $attributes 更新的值
     * @return boolean 成功与否
     */
    public function modify( $pk, $attributes ) {
        if ( $this->beforeSave() ) {
            $result = $this->updateByPk( $pk, $attributes );
            return $result;
        }
    }

    /**
     * 删除单条记录。封装AR delete方法，使之调用beforeDelete方法
     * @param mixed $pk 主键
     * @return boolean 成功与否
     */
    public function remove( $pk ) {
        $this->setPrimaryKey( $pk );
        $result = $this->delete();
        return $result;
    }

    /**
     * 检测缓存是否可用的简单封装方法
     * @return boolean
     */
    public function getIsAllowCache() {
        return (bool) $this->allowCache;
    }

    /**
     * 获取指定数据表最大的主键id
     * @param String $pk 数据模板表主键 默认为id
     * @return integer
     */
    public function getMaxId( $pk = 'id' ) {
        $result = 0;
        $record = $this->find( array( 'select' => "COUNT({$pk}) as {$pk}" ) );
        if ( !empty( $record ) ) {
            $result = intval( $record->$pk );
        }
        return $result;
    }

    /**
     * 覆盖此方法实现各model的afterSave
     * @see parent::updateByPk
     */
    public function updateByPk( $pk, $attributes, $condition = '', $params = array() ) {
        if ( $this->getIsAllowCache() ) {
            $pk = is_array( $pk ) ? $pk : explode( ',', $pk );
            foreach ( $pk as $id ) {
                $key = $this->getCacheKey( $id );
                // 删除缓存，在取数据的时候再写入
                Cache::rm( $key );
            }
        }
        $counter = parent::updateByPk( $pk, $attributes, $condition, $params );
        $this->afterSave();
        return $counter;
    }

    /**
     * 覆盖此方法实现各model的afterSave
     * @see parent::updateAll
     */
    public function updateAll( $attributes, $condition = '', $params = array() ) {
        $counter = parent::updateAll( $attributes, $condition, $params );
        $this->afterSave();
        return $counter;
    }

    /**
     * 实现批量删除缓存
     * @param mixed $pk
     * @param mixed $condition
     * @param array $params
     */
    public function deleteByPk( $pk, $condition = '', $params = array() ) {
		$ids = is_array( $pk ) ? $pk : explode( ',', $pk );
        if ( $this->getIsAllowCache() ) {
            foreach ( $ids as $id ) {
                if ( !empty( $id ) ) {
                    Cache::rm( $this->getCacheKey( $id ) );
                }
            }
        }
        return parent::deleteByPk( $ids, $condition, $params );
    }

    /**
     * 创建数据对象 但不保存到数据库
     * @param mixed $data 创建数据
     * @param string $type 状态
     * @return mixed
     */
    public function create( $data = '' ) {
        // 如果没有传值默认取POST数据
        if ( empty( $data ) ) {
            $data = $_POST;
        } elseif ( is_object( $data ) ) {
            $data = get_object_vars( $data );
        }

        // 验证数据
        if ( empty( $data ) || !is_array( $data ) ) {
            throw new CException( IBOS::lang( 'Data type invalid', 'error' ) );
        }
        // 对比过滤表单数据
        $fields = $this->getAttributes();
        if ( isset( $fields ) ) {
            foreach ( $data as $key => $val ) {
                if ( !array_key_exists( $key, $fields ) ) {
                    unset( $data[$key] );
                }
            }
        }
        // 返回创建的数据以供其他调用
        return $data;
    }

    /**
     * 删除前调用方法，增加缓存处理
     * 子类覆盖时应调用此方法确保父类实现
     * @return boolean 方法调用成功
     */
    protected function beforeDelete() {
        if ( $this->getIsAllowCache() ) {
            $key = $this->getCacheKey();
            Cache::rm( $key );
        }
        return parent::beforeDelete();
    }

    /**
     * 获取数据缓存
     * @param mixed $pk 主键
     * @return mixed false : 无缓存数据,其他为缓存数据
     */
    protected function fetchCache( $pk ) {
        $resource = false;
        if ( $this->getIsAllowCache() ) {
            $resource = Cache::get( $this->getCacheKey( $pk ) );
        }
        return $resource;
    }

    /**
     * 批量获取数据缓存，特别处理以适应fetchAllByPk函数
     * @param array $pks
     * @return mixed array - the results.false - if the result not found
     */
    protected function fetchCaches( $pks ) {
        $return = array();
        if ( $this->getIsAllowCache() ) {
            foreach ( $pks as $pk ) {
                $data = Cache::get( $this->getCacheKey( $pk ) );
                if ( $data !== false ) {
                    $return[$pk] = $data;
                }
            }
        }
        return !empty( $return ) ? $return : false;
    }

    /**
     * 获得继承Model的类名
     * @return string 子类类名
     */
    protected function getModelClass() {
        $modelClass = get_class( $this );
        return $modelClass;
    }

    /**
     * 获取缓存键值
     * @param mixed $pk 主键
     * @return string 处理后的缓存key
     */
    protected function getCacheKey( $pk = '' ) {
        $modelClass = $this->getModelClass();
        if ( empty( $pk ) ) {
            $modelPk = $this->getPrimaryKey();
            if ( !$modelPk ) {
                throw new CException( IBOS::lang( 'Cache must have a primary key', 'error' ) );
            }
            $pk = $modelPk;
        }
        $key = strtolower( $modelClass ) . '_' . $pk;
        return $key;
    }

}
