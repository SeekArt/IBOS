<?php

/**
 * 分类树组件类
 * @package application.core.components
 * @version $Id: category.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\components;

use application\core\utils\Ibos;
use CException;
use CMap;

class Category
{

    /**
     * 分类表
     * @var string
     */
    protected $_category = '';

    /**
     * 相关联的模块表
     * @var string
     */
    protected $_related = '';

    /**
     * 配置数组
     * @var array
     * <pre>
     * array(
     *    'index' => 'catid',
     *    'parent' => 'pid',
     *    'name' => 'name',
     *    'sort' => 'sort',
     * );
     * </pre>
     */
    protected $_setting = array();

    /**
     * 默认分类表字段值
     * @var array
     */
    protected $_default = array(
        'index' => 'catid',
        'parent' => 'pid',
        'name' => 'name',
        'sort' => 'sort'
    );

    public function __construct($category, $related = '', $setting = array())
    {
        $this->setCategory($category);
        $this->setRelated($related);
        $this->setSetting($setting);
        $this->init();
    }

    /**
     * getter方法，针对setting值快速获取
     * @param string $name
     * @return mixed
     * @throws CException
     */
    public function __get($name)
    {
        if (isset($this->_setting[strtolower($name)])) {
            return $this->_setting[$name];
        }
        throw new CException(Ibos::t('yii', 'Property "{class}.{property}" is not defined.', array('{class}' => get_class($this), '{property}' => $name)));
    }

    public function __isset($name)
    {
        if (isset($this->_setting[strtolower($name)])) {
            return $this->_setting[$name] !== null;
        }
        return false;
    }

    /**
     * 设置分类表对象
     * @param string $category 分类表Model名
     * @throws CException
     */
    public function setCategory($category)
    {
        $this->_category = null;
        if (is_object($category)) {
            $this->_category = $category;
        } else if (class_exists($category)) {
            $this->_category = new $category();
        } else {
            throw new CException(Ibos::lang('Cannot find class', 'error', array('{class}' => $category)));
        }
    }

    /**
     * 设置分类关联表对象
     * @param string $related 关联表Model名
     * @return void
     */
    public function setRelated($related = '')
    {
        $this->_related = null;
        if (!empty($related)) {
            $this->_related = class_exists($related) ? new $related() : null;
        }
    }

    /**
     * 设置setting
     * @param array $setting
     */
    public function setSetting($setting = array())
    {
        $this->_setting = CMap::mergeArray($this->_default, $setting);
    }

    /**
     * 添加分类
     * @param integer $pid 父id
     * @param string $name 分类名
     * @return boolean
     */
    public function add($pid, $name)
    {
        $sort = $this->sort;
        $parent = $this->parent;
        $catName = $this->name;
        // 查询出最大的sort
        $cond = array('select' => $sort, 'condition' => "pid={$pid}", 'order' => "`{$sort}` DESC");
        $sortRecord = $this->_category->fetch($cond);
        if (empty($sortRecord)) {
            $sortId = 0;
        } else {
            $sortId = $sortRecord['sort'];
        }
        // 排序号默认在最大的基础上加1，方便上移下移操作
        $newSortId = $sortId + 1;
        $status = $this->_category->add(
            array(
                $sort => $newSortId,
                $parent => $pid,
                $catName => $name
            )
            , true);
        $this->afterAdd();
        return $status;
    }

    /**
     * 删除分类
     * @param integer $catid
     * @return boolean
     */
    public function delete($catid)
    {
        $clear = false;
        $ids = $this->fetchAllSubId($catid);
        $idStr = implode(',', array_unique(explode(',', trim($ids, ','))));
        if (empty($idStr)) {
            $idStr = $catid;
        } else {
            $idStr .= ',' . $catid;
        }
        // 有关联表，获取关联表里有无关联分类id
        if (!is_null($this->_related)) {
            $count = $this->_related->count("`{$this->index}` IN ($idStr)");
            !$count && $clear = true;
        } else {
            $clear = true;
        }
        if ($clear) {
            $status = $this->_category->deleteAll("FIND_IN_SET({$this->index},'$idStr')");
            $this->afterDelete();
            return $status;
        } else {
            return false;
        }
    }

    /**
     * 编辑分类
     * @param integer $catid 要修改的分类id
     * @param integer $pid 选择的父id
     * @param string $name 更改之后的名字
     * @return string
     */
    public function edit($catid, $pid, $name)
    {
        //assert( '!empty( $catid ) && !empty( $name )' );
        $status = $this->_category->modify($catid, array($this->parent => $pid, $this->name => $name));
        $this->afterEdit();
        return $status;
    }

    /**
     * 分类移动。当分类移至顶点或底部时，移动会失败
     * @param string $action 移动动作，moveup or movedown
     * @param integer $catid 分类id
     * @param integer $pid 父id
     * @return boolean
     */
    public function move($action, $catid, $pid)
    {
        $sort = $this->sort;
        $parent = $this->parent;
        $index = $this->index;
        $sortRecord = $this->_category->fetch(array('select' => $sort, 'condition' => "{$index} = '{$catid}'"));
        if (empty($sortRecord)) {
            $sortId = 0;
        } else {
            $sortId = $sortRecord['sort'];
        }
        // 插入为避免序号相同，所以排序仅交换相邻的sort
        if ($action == 'moveup') {
            $where = " `{$parent}` = {$pid} AND {$sort} < {$sortId} ORDER BY `{$sort}` DESC";
        } else if ($action == 'movedown') {
            $where = " {$parent} = {$pid} AND {$sort} > {$sortId} ORDER BY `{$parent}` ASC";
        } else {
            $where = " 1 ";
        }
        // 获取将倒换位置的数据行相关字段数据值
        $record = $this->_category->fetch(array('select' => "{$index},{$sort}", 'condition' => $where));
        if (!empty($record)) {
            $nextCatid = $record[$index];
            $nextSort = $record[$sort];
            // 交换SORT
            $this->_category->modify($nextCatid, array($sort => $sortId));
            $this->_category->modify($catid, array($sort => $nextSort));
            $this->afterEdit();
            return true;
        }
        return false;
    }

    /**
     * 获取分类数据
     * @return array
     */
    public function getData($condition = '')
    {
        $result = array();
        $sort = $this->sort;
        $index = $this->index;
        $data = $this->_category->fetchAll(array('condition' => $condition, 'order' => "{$sort} ASC"));
        foreach ($data as $row) {
            $catid = $row[$index];
            $result[$catid] = $row;
        }
        return $result;
    }

    /**
     * 获取生成分类树所需要的数据,返回页面“zTree”js组件使用
     * @param array $datas 分类数据 默认为array()
     * @return array zTree所需对象数组
     */
    public function getAjaxCategory($data = array())
    {
        $return = array();
        $counter = 0;
        foreach ($data as $row) {
            $tmp = array();
            //-- 打开第一个分类处理 --
            if ($counter == 0 && $row[$this->parent] == '0') {
                $tmp['open'] = 1;
                $counter++;
            }
            $tmp['id'] = $row[$this->index];
            $tmp['pId'] = $row[$this->parent];
            $tmp['name'] = $row[$this->name];
            $return[] = array_merge($tmp, $row);
        }
        return $return;
    }

    /**
     * 扩展接口：初始化方法，留给子类扩展
     */
    protected function init()
    {
        return true;
    }

    /**
     * 扩展接口：编辑后回调方法，留给子类扩展
     */
    protected function afterEdit()
    {

    }

    /**
     * 扩展接口：增加后回调方法，留给子类扩展
     */
    protected function afterAdd()
    {

    }

    /**
     * 扩展接口：删除后调用方法，留给子类扩展
     */
    protected function afterDelete()
    {

    }

    /**
     * 获取分类的所有父级的分类id，返回一个字符串
     * @param integer $catid 分类id
     * @return string
     */
    protected function fetchAllParentId($catid)
    {
        $condition = "`{$this->index}` = {$catid}";
        $field = $this->parent;
        $idStr = '';
        $count = $this->_category->count($condition);
        if ($count > 0) {
            $record = $this->_category->fetchAll(array('select' => $field, 'condition' => $condition));
            if (!empty($record)) {
                foreach ($record as $row) {
                    $idStr .= $row[$field] . "," . $this->fetchAllParentId($row[$field]);
                }
            }
        }
        return $idStr;
    }

    /**
     * 获取分类的所有子级的分类id，返回一个字符串
     * @param integer $catid 分类id
     * @return string
     */
    protected function fetchAllSubId($catid)
    {
        $condition = "`{$this->parent}` = {$catid}";
        $field = $this->index;
        $idStr = '';
        $count = $this->_category->count($condition);
        if ($count > 0) {
            $record = $this->_category->fetchAll(array('select' => $field, 'condition' => $condition));
            if (!empty($record)) {
                foreach ($record as $row) {
                    $idStr .= $row[$field] . "," . $this->fetchAllSubId($row[$field]);
                }
            }
        }
        return $idStr;
    }

}
