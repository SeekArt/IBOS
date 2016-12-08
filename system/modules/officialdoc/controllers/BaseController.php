<?php

/**
 * 通知模块基类控制器------ 基类控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 通知模块------ 基类控制器，继承Controller
 * @package application.modules.officialDoc.controllers
 * @version $Id: BaseController.php 639 2013-06-20 09:42:12Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\controllers;

use application\core\controllers\Controller;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Approval;
use application\modules\officialdoc\core\OfficialdocCategory as ICOfficialdocCategory;
use application\modules\officialdoc\model\Officialdoc;

class BaseController extends Controller
{

    /**
     * 默认最小的历史版本号
     */
    const ARTICLE_VERSION_DEFAULT = 1.0;

    /**
     * 每增加一个历史版本时版本号的增量
     */
    const ARTICLE_VERSION_CREATE = 0.1;

    /**
     * 默认最小的历史版本号
     * @return float
     */
    protected function getDefaultVersion()
    {
        return self::ARTICLE_VERSION_DEFAULT;
    }

    /**
     * 得到侧栏视图渲染结果
     * @return string
     */
    protected function getSidebar($catid = 0)
    {

        $sidebarAlias = 'application.modules.officialdoc.views.sidebar';
        $approvals = Approval::model()->fetchAllApproval();
        $params = array(
            'approvals' => $approvals,
            'categoryData' => $this->getCategoryOption(),
            'catid' => $catid
        );
        $noSignCount = Officialdoc::model()->countNoSignByUid(Ibos::app()->user->uid);
        $params['noSignCount'] = $noSignCount;
        return $this->renderPartial($sidebarAlias, $params, true);
    }

    /**
     * 获得下拉框选择选项列、生成分类树所需数据
     * @return array
     */
    protected function getCategoryOption()
    {
        $category = new ICOfficialdocCategory('application\modules\officialdoc\model\OfficialdocCategory');
        $categoryData = $category->getAjaxCategory($category->getData(array('order' => 'sort ASC')));
        return StringUtil::getTree($categoryData, "<option value='\$catid' \$selected>\$spacer\$name</option>");
    }

    /**
     * 获取通知最新版本
     * @param int $docid
     * @return int 返回版本号
     */
    protected function getNewestVerByDocid($docid)
    {
        $doc = Officialdoc::model()->fetchByPk($docid);
        if (!empty($doc)) {
            return $doc['version'];
        } else {
            return 1;
        }
    }

}
