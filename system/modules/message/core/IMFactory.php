<?php

/**
 * IMFactory class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 适配器工厂类，负责统一创建IM适配器实例
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core
 * @version $Id$
 */

namespace application\modules\message\core;

use application\core\utils\Ibos;
use CApplicationComponent;
use CException;

class IMFactory extends CApplicationComponent
{

    private $_error = array();

    /**
     *
     * @param string $className IM适配器的类名。这个参数也可以用path alias代替。(e.g. system.web.widgets.COutputCache)
     * @param array $properties 初始化适配器所需参数
     * @return ICChart
     */
    public function createAdapter($className, $config = array(), $properties = array())
    {
        $adapter = new $className($config);
        $this->chkInstance($adapter);
        if ($adapter->check()) {
            foreach ($properties as $name => $value) {
                $adapter->$name = $value;
            }
            return $adapter;
        } else {
            $this->setError($className, $adapter->getError());
            return false;
        }
    }

    /**
     *
     * @param type $className
     * @param type $error
     */
    protected function setError($className, $error = array())
    {
        $this->_error[$className] = $error;
    }

    /**
     *
     * @param type $className
     * @return type
     */
    public function getError($className)
    {
        if (isset($this->_error[$className])) {
            return $this->_error[$className];
        } else {
            return array();
        }
    }

    /**
     * 检查适配器来源是否正确
     * @param ICChart $chart
     * @throws CException
     */
    private function chkInstance($adapter)
    {
        if (!$adapter instanceof IM) {
            throw new CException(Ibos::t('error', 'Class "{class}" is illegal.', array('{class}' => get_class($adapter))));
        }
    }

}
