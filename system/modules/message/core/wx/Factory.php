<?php

/**
 * WxFactory class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信处理器工厂类，负责统一创建微信处理器实例
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx;

use application\core\utils\Ibos;
use CApplicationComponent;
use CException;

class Factory extends CApplicationComponent
{

    /**
     * 创建处理器
     * @param string $handleType
     * @param array $config
     * @param array $properties
     * @return \application\modules\message\core\wx\className
     */
    public function createHandle($handleType, $properties = array())
    {
        $className = 'application\modules\message\core\wx\\' . $handleType;
        $instance = new $className();
        $this->chkInstance($instance);
        foreach ($properties as $name => $value) {
            $instance->$name = $value;
        }
        return $instance;
    }

    /**
     * 检查适配器来源是否正确
     * @param object $handle
     * @throws CException
     */
    private function chkInstance($handle)
    {
        if (!$handle instanceof CApplicationComponent) {
            throw new CException(Ibos::t('error', 'Class "{class}" is illegal.', array('{class}' => get_class($handle))));
        }
    }

}
