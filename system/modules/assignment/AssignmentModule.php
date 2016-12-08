<?php

/**
 * 任务指派模块------ module类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */
/**
 * 任务指派模块------  module类文
 * @package application.modules.assignment
 * @version $Id: AssignmentModule.php 665 2013-06-24 01:03:57Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\assignment;

use application\core\modules\Module;
use application\core\utils\Env;

class AssignmentModule extends Module
{

    protected function preinit()
    {
        /*if ( !$this->checkModule( 'assignment' ) ) {
            Env::iExit( '你当前使用的模块为商业模块，请获取正确授权后使用' );
        }*/
    }

}
