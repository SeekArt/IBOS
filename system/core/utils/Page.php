<?php

/**
 * 分页工具类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 分页工具类,提供封装分页对象方法。
 *
 * @package application.core.utils
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

use CDbCriteria;
use CPagination;

class Page extends CPagination
{

    /**
     * 静态pagination实例
     * @var mixed
     */
    private static $instance;

    /**
     * 静态调用单例模式
     * @return mixed
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 创建page对象以供调用
     * @param integer $count 记录数
     * @param integer $pageSize 每页显示条数
     * @return mixed
     */
    public static function create($count, $pageSize = self::DEFAULT_PAGE_SIZE, $usingDb = true)
    {
        self::getInstance()->setPageSize($pageSize);
        self::getInstance()->setItemCount($count);
        if ($usingDb) {
            $criteria = new CDbCriteria(
                array('limit' => self::getInstance()->getLimit(), 'offset' => self::getInstance()->getOffset())
            );
            self::getInstance()->applyLimit($criteria);
        }
        return self::$instance;
    }

}
