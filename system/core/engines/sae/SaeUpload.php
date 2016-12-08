<?php

/**
 * SAE 上传处理文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * SAE上传处理类,实现IO upload接口扩展
 *
 * @package application.core.engines.sae
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: Upload.php 3557 2014-06-04 07:54:57Z zhangrong $
 */

namespace application\core\engines\sae;

use application\core\components\Upload;

class SaeUpload extends Upload
{

    public function save()
    {
        if ($this->getError() == 0) {
            $storage = new SaeFile();
            $attach = $this->getAttach();
            $arr = array('type' => $attach['type']);
            $rs = $storage->uploadFile($attach['target'], $attach['tmp_name'], $arr);
            $this->afterSave($attach);
            return $rs;
        } else {
            return false;
        }
    }

}
