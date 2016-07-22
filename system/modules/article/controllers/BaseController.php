<?php

/**
 * 文章模块------ 基类控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 文章模块------ 信息中心基类控制器，继承Controller
 * @package application.modules.article.controllers
 * @version $Id: BaseController.php 7023 2016-05-10 08:01:05Z Aeolus $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\article\controllers;

use application\core\controllers\Controller;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\core\utils\StringUtil;
use application\modules\article\core\ArticleCategory;
use application\modules\dashboard\model\Approval;

class BaseController extends Controller {

    /**
     * 默认信息类型：文章
     */
    const ARTICLE_TYPE_DEFAULT = 0;

    /**
     * 信息类型：图片
     */
    const ARTICLE_TYPE_PICTURE = 1;

    /**
     * 信息类型：超链接
     */
    const ARTICLE_TYPE_LINK = 2;

    /**
     * 分类id
     * @var integer
     */
    protected $catid = 0;

    /**
     * 条件
     * @var string
     */
    protected $condition = '';

    /**
     * 得到侧栏视图渲染结果
     * @return string
     */
    public function getSidebar( $catid = 0 ) {
        $sidebarAlias = 'application.modules.article.views.sidebar';
        $approvals = Approval::model()->fetchAllApproval();
        $params = array(
            'approvals' => $approvals,
            'categoryData' => $this->getCategoryOption(),
            'catid' => $catid
        );
        return $this->renderPartial( $sidebarAlias, $params, true );
    }

    /**
     * 是否有安装投票模块
     * @return boolean
     */
    protected function getVoteInstalled() {
        return Module::getIsEnabled( 'vote' );
    }

    /**
     * 是否安装邮件模块
     * @return boolean
     */
    protected function getEmailInstalled() {
        $isInstallEmail = Module::getIsEnabled( 'email' );
        return $isInstallEmail;
    }

    /**
     * 获得下拉框选择选项列、生成分类树所需数据
     * @return array
     */
    protected function getCategoryOption() {
        $category = new ArticleCategory( 'application\modules\article\model\ArticleCategory' );
        $categoryData = $category->getAjaxCategory( $category->getData( array( 'order' => 'sort ASC' ) ) );
        return StringUtil::getTree( $categoryData, "<option value='\$catid' \$selected>\$spacer\$name</option>" );
    }

    /**
     * 取得后台配置数据
     * @return array $result
     */
    public function getDashboardConfig() {
        $result = array();
        $fields = array(
            'articleapprover', 'articlecommentenable', 'articlevoteenable', 'articlemessageenable'
        );
        foreach ( $fields as $field ) {
            $result[$field] = IBOS::app()->setting->get( 'setting/' . $field );
        }
        return $result;
    }

}
