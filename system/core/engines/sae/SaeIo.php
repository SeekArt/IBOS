<?php

/**
 * IBOS SAE IO文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * SAE IO父类,提供IO接口给子类扩展
 *
 * @package ext.enginedriver.sae
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: io.php 3557 2014-06-04 07:54:57Z zhangrong $
 */

namespace application\core\engines\sae;

use application\core\engines\Io;

class SaeIo extends Io
{

    /**
     * 新浪云的上传接口
     * @param array $attach 上传文件域
     * @param string $module 对应的模块
     * @return \SAEUpload
     */
    public function upload($attach, $module)
    {
        return new SaeUpload($attach, $module);
    }

    /**
     * SAE文件处理接口
     * @return object
     */
    public function file()
    {
        return SaeFile::getInstance();
    }

}
