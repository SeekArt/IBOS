<?php
/**
 * 部门缓存工具类
 *
 * @namespace application\modules\contact\utils
 * @filename DeptCacheUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/17 8:49
 */

namespace application\modules\contact\utils;

use application\core\utils\Ibos;
use application\modules\contact\extensions\Tree\lib\BlueM\Tree;
use application\modules\contact\extensions\Tree\lib\BlueM\Node;
use application\modules\department\utils\Department as DepartmentUtil;


/**
 * Class DeptCacheUtil
 *
 * @package application\modules\contact\utils
 */
class DeptCacheUtil extends AbstractCacheUtil
{
    /**
     * @var string 缓存名
     */
    protected $cacheName = 'DeptTree';

    /**
     * @param string $className
     * @return DeptCacheUtil
     */
    public static function getInstance($className = __CLASS__)
    {
        static $instance = null;

        if (empty($instance)) {
            $instance = parent::getInstance($className);
        }

        return $instance;
    }

    /**
     * @return Tree
     */
    public function getCache()
    {
        return parent::getCache();
    }

    /**
     * 创建缓存
     *
     * @return Tree|bool 返回缓存数据
     */
    public function buildCache()
    {
        $deptTreeCache = $this->getCache();

        if (empty($deptTreeCache)) {
            $deptTreeCache = $this->buildDeptTree();
            $this->setCache($deptTreeCache);
        }

        return $deptTreeCache;
    }


    /**
     * 获取当前部门下的直接子部门
     *
     * @param integer $deptId 部门 id
     * @return Node[] 直接子部门节点数组
     */
    public function fetchChildren($deptId)
    {
        $deptTree = $this->getCache();

        return $deptTree->getNodeById($deptId)->getChildren();
    }

    /**
     * 获取当前部门的直接父部门
     * 
     * @param integer $deptId 部门 id
     * @return Node|null
     */
    public function fetchParent($deptId)
    {
        $deptTree = $this->getCache();

        return $deptTree->getNodeById($deptId)->getParent();
    }

    /**
     * 获取当前部门下的直接子部门 id 数组
     *
     * @param integer $deptId 部门 id
     * @return array
     */
    public function fetchChildrenId($deptId)
    {
        $children = $this->fetchChildren($deptId);

        $childrenIdArr = array();
        foreach ($children as $loopChild) {
            $childrenIdArr[] = $loopChild->getId();
        }

        return $childrenIdArr;
    }

    /**
     * 获取当前部门下的所有子部门
     *
     * @param integer $deptId 部门 id
     * @return Node[] 所有子部门节点数组
     */
    public function fetchAllChildren($deptId)
    {
        $deptId = (int)$deptId;
        $deptTree = $this->getCache();
        $node = $deptTree->getNodeById($deptId);

        $isCacheAllChildren = $node->hasAllChildren();
        $allChildren = $node->getAllChildren();

        // 忽略根节点
        if (isset($allChildren[0]) &&$allChildren[0]->isRoot()) {
            unset($allChildren[0]);
        }

        // 没有缓存到该数据，更新缓存
        if (!$isCacheAllChildren) {
            $deptTree->setNodeById($deptId, $node);
            $this->updateLocalCache($deptTree);
        }

        return $allChildren;
    }

    /**
     * 获取当前部门的所有父部门
     *
     * @param integer $deptId 部门 id
     * @return Node[]
     */
    public function fetchAllParent($deptId)
    {
        $deptId = (int)$deptId;
        $deptTree = $this->getCache();
        $node = $deptTree->getNodeById($deptId);

        $isCacheAllParent = $node->hasAllParent();
        $allParent = $node->getAllParent();

        // 没有缓存，更新缓存
        if (!$isCacheAllParent) {
            $deptTree->setNodeById($deptId, $node);
            $this->updateLocalCache($deptTree);
        }

        return $allParent;
    }

    /**
     * 获取当前部门下的所有子部门 id 数组
     *
     * @param integer $deptId 部门 id
     * @return array
     */
    public function fetchAllChildrenId($deptId)
    {
        $allChildren = $this->fetchAllChildren($deptId);

        $allChildrenIdArr = array();
        foreach ($allChildren as $loopChild) {
            $allChildrenIdArr[] = $loopChild->getId();
        }

        return $allChildrenIdArr;
    }

    /**
     * 创建一棵部门树
     *
     * @return Tree
     */
    protected function buildDeptTree()
    {
        static $staticDeptTree = null;

        if (!empty($staticDeptTree)) {
            return $staticDeptTree;
        }

        $allDepartments = DepartmentUtil::loadDepartment();

        // 将部门数组中的键名 deptid 改为 id，pid 改为 parent。
        // Tree 类在创建树的时候，期望拿到这些数据。
        foreach ($allDepartments as &$loopDept) {
            $loopDept['id'] = $loopDept['deptid'];
            unset($loopDept['deptid']);

            $loopDept['parent'] = $loopDept['pid'];
            unset($loopDept['pid']);
        }
        $deptTree = TreeUtil::getInstance()->create($allDepartments);

        $staticDeptTree = $deptTree;

        return $deptTree;
    }
}
