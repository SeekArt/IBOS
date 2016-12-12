<?php

/**
 *
 * IO抽象父类文件
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * IO抽象父类,提供本地及云引擎之间的IO读写接口
 * @package application.core.engines
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\engines;

abstract class Io
{

    /**
     * 上传接口
     */
    abstract function upload($fileArea, $module);

    /**
     * 文件及文件夹处理接口
     */
    abstract function file();
}
