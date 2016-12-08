<?php

/**
 * IBOS本地IO文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 本地IO,实现IO接口
 *
 * @package ext.enginedriver.local
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: LocalIO.php 3557 2014-06-04 07:54:57Z zhangrong $
 */

namespace application\core\engines\local;

use application\core\components\Upload;
use application\core\engines\Io;

class LocalIo extends Io
{

    /**
     * 本地IO上传接口
     * @param array $fileArea 文件上传域
     * @param string $module 对应的模块
     * @return \upload
     */
    public function upload($fileArea, $module)
    {
        return new Upload($fileArea, $module);
    }

    /**
     * 本地IO文件操作接口
     * @return object
     */
    public function file()
    {
        return LocalFile::getInstance();
    }

}
