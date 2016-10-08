<?php

/**
 * 招聘模块------招聘模块基本控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------招聘模块基本控制器类，继承ICController
 * @package application.modules.recruit.components
 * @version $Id: BaseController.php 6533 2016-03-07 08:18:31Z gzhyj $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\controllers;

use application\core\controllers\Controller;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\modules\main\utils\Main;
use application\modules\recruit\model\ResumeDetail;
use application\modules\recruit\utils\Recruit as RecruitUtil;
use CJSON;

class BaseController extends Controller {

    /**
     * 查询的条件
     * @var string 
     */
    protected $condition = '';

    /**
     * 取得侧栏视图
     * @return void
     */
    protected function getSidebar() {
        $sidebarAlias = 'application.modules.recruit.views.resume.sidebar';
        $params = array(
            'statModule' => Ibos::app()->setting->get('setting/statmodules'),
        );
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    /**
     * 取得模块后台配置
     * @return array
     */
    public function getDashboardConfig() {
        //取得所有配置
        $config = Ibos::app()->setting->get('setting/recruitconfig');
        $result = array();
        foreach ($config as $configName => $configValue) {
            list($visi, $fieldRule) = explode(',', $configValue);
            $result[$configName]['visi'] = $visi;
            $result[$configName]['fieldrule'] = $fieldRule;
        }
        return $result;
    }

    /**
     *  检查是否已安装邮件模块
     * @return boolean
     */
    protected function checkIsInstallEmail() {
        $isInstallEmail = Module::getIsEnabled('email');
        return $isInstallEmail;
    }

    /**
     * 通过查询取得简历id 和 realname
     * @return void
     */
    public function actionGetRealname() {
        if (Ibos::app()->request->isAjaxRequest) {
            $keyword = Env::getRequest('keyword');
            $records = ResumeDetail::model()->fetchPKAndRealnameByKeyword($keyword);
            parent::ajaxReturn($records);
        }
    }

    /**
     * 搜索
     * @return void
     */
    public function actionSearch() {
        $type = Env::getRequest('type');

        $conditionCookie = Main::getCookie('condition');
        if (empty($conditionCookie)) {
            Main::setCookie('condition', $this->condition, 10 * 60);
        }

        if ($type == 'advanced_search') {
            $search = $_POST['search'];
            //添加转义
            @$search['realname'] = \CHtml::encode($search['realname']);
            $methodName = 'join' . ucfirst($this->id) . 'SearchCondition';
            $this->condition = RecruitUtil::$methodName($search, $this->condition);
        } else if ($type == 'normal_search') {
            //添加单引号转义
            $keyword = \CHtml::encode($_POST['keyword']);
            $this->condition = " rd.realname LIKE '%$keyword%' ";
        } else {
            $this->condition = $conditionCookie;
        }
        //把搜索条件存进cookie,当搜索出现分页时,搜索条件从cookie取
        if ($this->condition != Main::getCookie('condition')) {
            Main::setCookie('condition', $this->condition, 10 * 60);
        }
        $this->actionIndex();
    }

    /**
     * 异步检查输入的用户名是否存在于已有简历中
     * @return json
     */
    public function actionCheckRealname() {
        $fullname = Env::getRequest('fullname');
        $fullnameToUnicode = str_replace('%', '\\', $fullname);
        $fullnameToUtf8 = CJSON::unicodeToUTF8($fullnameToUnicode);
        $realnames = ResumeDetail::model()->fetchAllRealnames();
        $isExist['statu'] = in_array($fullnameToUtf8, $realnames) ? true : false;
        $this->ajaxReturn($isExist);
    }

}
