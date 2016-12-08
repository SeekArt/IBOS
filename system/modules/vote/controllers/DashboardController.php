<?php

/**
 * 投票模块后台控制器------ 后台控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 投票模块------ 后台控制器，继承DashboardBaseController
 * @package application.modules.comment.controllers
 * @version $Id: DashboardController.php 8695 2016-10-22 02:28:41Z gzhyj $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\vote\controllers;

use application\core\utils\Cache;
use application\core\utils\Ibos;
use application\modules\dashboard\controllers\BaseController;
use application\modules\main\model\Setting;

class DashboardController extends BaseController
{

    public function getAssetUrl($module = '')
    {
        $module = 'dashboard';
        return Ibos::app()->assetManager->getAssetsUrl($module);
    }

    /**
     * 默认显示页
     * @return void
     */
    public function actionIndex()
    {
        $votethumbwh = Ibos::app()->setting->get('setting/votethumbwh');
        list($width, $height) = explode(',', $votethumbwh);
        $config = array(
            'votethumbenable' => Ibos::app()->setting->get('setting/votethumbenable'),
            'votethumbwidth' => $width,
            'votethumbheight' => $height
        );
        $this->render('index', $config);
    }

    /**
     * 编辑
     * @return void
     */
    public function actionEdit()
    {
        $votethumbenable = 0;
        if (isset($_POST['votethumbenable'])) {
            $votethumbenable = $_POST['votethumbenable'];
        }
        $width = empty($_POST['votethumbwidth']) ? 0 : $_POST['votethumbwidth'];
        $height = empty($_POST['votethumbheight']) ? 0 : $_POST['votethumbheight'];
        $votethumbewh = $width . ',' . $height;
        Setting::model()->modify('votethumbenable', array('svalue' => $votethumbenable));
        Setting::model()->modify('votethumbwh', array('svalue' => $votethumbewh));
        Cache::update('setting');
        $this->success(Ibos::lang('Update succeed', 'message'), $this->createUrl('dashboard/index'));
    }

}
