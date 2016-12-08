<?php

/**
 * 文件柜模块------ 云盘工厂类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 云盘工厂类------ 用于生成云盘实例
 * @package application.modules.file.core
 * @version $Id: ICCloudOSSFactory.php 3297 2014-06-19 06:40:54Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\file\core;

use application\core\utils\Ibos;
use CApplicationComponent;
use CException;

class CloudOSSFactory extends CApplicationComponent
{

    public function createDisk($className, $config = array(), $properties = array())
    {
        $disk = new $className($config);
        $this->chkInstance($disk);
        foreach ($properties as $name => $value) {
            $disk->$name = $value;
        }
        return $disk;
    }

    /**
     * 检查适配器来源是否正确
     * @param CloudOSS $disk
     * @throws CException
     */
    private function chkInstance($disk)
    {
        if (!$disk instanceof CloudOSS) {
            throw new CException(Ibos::t('error', 'Class "{class}" is illegal.', array('{class}' => get_class($disk))));
        }
    }

}
