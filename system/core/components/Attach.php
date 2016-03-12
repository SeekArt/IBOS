<?php

/**
 * 
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/ 
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 
 * @package application.core.components
 * @version $Id: Attach.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\components;

use application\core\utils\File;
use application\core\utils\IBOS;
use CException;

abstract class Attach {

    /**
     * 上传对象
     * @var object 
     */
    protected $upload;

    /**
     * 初始化上传域
     * @param string $fileArea
     */
    public function __construct( $fileArea = 'Filedata', $module = 'temp' ) {
        $file = $_FILES[$fileArea];
        if ( $file['error'] ) {
            throw new CException( IBOS::lang( 'File is too big', 'error' ) );
        } else {
            $upload = File::getUpload( $file, $module );
            $this->upload = $upload;
        }
    }

    abstract public function upload();
}
