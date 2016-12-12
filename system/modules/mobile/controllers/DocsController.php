<?php

/**
 * 移动端新闻控制器文件
 *
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 移动端新闻控制器文件
 *
 * @package application.modules.mobile.controllers
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @version $Id$
 */

namespace application\modules\mobile\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\dashboard\model\Approval;
use application\modules\mobile\utils\Mobile;
use application\modules\officialdoc\core\Officialdoc as ICOfficialdoc;
use application\modules\officialdoc\model\Officialdoc;
use application\modules\officialdoc\model\OfficialdocCategory;
use application\modules\officialdoc\model\OfficialdocReader;
use application\modules\officialdoc\model\OfficialdocVersion;
use application\modules\officialdoc\model\RcType;
use application\modules\officialdoc\utils\Officialdoc as OfficialdocUtil;
use application\modules\user\model\User;
use CDbCriteria;
use CPagination;

class DocsController extends BaseController
{

    private $_condition = '';

    /**
     * 默认页,获取主页面各项数据统计
     * @return void
     */
    public function actionIndex()
    {
        $type = Env::getRequest('type');
        $catid = Env::getRequest('catid');

        $childCatIds = '';
        if (!empty($catid)) {
            if ($catid == "-1") {
                $type = "nosign";
            } elseif ($catid == "-2") {
                $type = "sign";
            } else {
                $childCatIds = OfficialdocCategory::model()->fetchCatidByPid($catid, true);
            }
        }
        if (Env::getRequest('search')) {
            $this->search();
        }

        $uid = Ibos::app()->user->uid;
        $this->_condition = OfficialdocUtil::joinListCondition($type, $uid, $childCatIds, $this->_condition);
        $datas = Officialdoc::model()->fetchAllAndPage($this->_condition);
        if (isset($datas["datas"])) {
            foreach ($datas["datas"] as $k => $v) {
                $datas["datas"][$k]["content"] = mb_substr(strip_tags($v["content"]), 0, 15, 'utf-8');
            }
        }
        $officialDocList = ICOfficialdoc::getListDatas($datas['datas']);
        // 判断是否审核人
        $aids = OfficialdocCategory::model()->fetchAids();
        $isApprover = in_array($uid, Approval::model()->fetchApprovalUidsByIds($aids));
        if ($catid == 0) {
            $category = OfficialdocCategory::model()->fetchAll("pid = 0");
        } else {
            $category = OfficialdocCategory::model()->fetchAll("pid = {$catid}");
        }
        $pages = array(
            'pageCount' => $datas['pages']->getPageCount(),
            'page' => $datas['pages']->getCurrentPage(),
            'pageSize' => $datas['pages']->getPageSize()
        );
        $params = array(
            'pages' => $pages,
            'datas' => $officialDocList,
            'isApprover' => $isApprover,
            'category' => $category
        );
        $this->ajaxReturn($params, Mobile::dataType());
    }

    public function actionCategory()
    {
        $category = OfficialdocCategory::model()->fetchAll();
        // 组合一个空的二维数组，使得下标从1开始
        $tmp = array(array());
        $data = array_merge($tmp, $category);
        unset($data[0]);
        $params = StringUtil::getTree($data, "<li class='\$selected'><a href='#docs' onclick='docs.loadList(\$catid)'>\$spacer<i class='ao-file'></i>\$name</a></li>");
        $this->ajaxReturn($params, Mobile::dataType());
    }

    public function actionShow()
    {
        $uid = Ibos::app()->user->uid;
        $docid = Env::getRequest('id');
        $version = Env::getRequest('version');
        if (empty($docid)) {
            $this->ajaxReturn("", Mobile::dataType());
        }
        $officialDocEntity = new ICOfficialdoc($docid);
        $officialDoc = $officialDocEntity->getAttributes();
        if ($version) {  //如果是查看历史版本，合并历史版本数据
            $versionData = OfficialdocVersion::model()->fetchByAttributes(array('docid' => $docid, 'version' => $version));
            $officialDoc = array_merge($officialDoc, $versionData);
        }
        if (!empty($officialDoc)) {
            //如果这篇文章状态是待审核时：如果当前读者是作者本人，可以查看，否者，提示该文章未通过审核
            if (!OfficialdocUtil::checkReadScope($uid, $officialDoc)) {
                $this->error(Ibos::lang('You do not have permission to read the officialdoc'), $this->createUrl('default/index'));
            }
            $data = ICOfficialdoc::getShowData($officialDoc);
            $row = OfficialdocReader::model()->find(sprintf("`docid` = %d AND `uid` = %d", $docid, $uid));
            if (empty($row)) {
                OfficialdocReader::model()->addReader($docid, $uid);
            }
            Officialdoc::model()->updateClickCount($docid, $data['clickcount']);
            //如果通知内容大于多少个字符，分页显示
            $page = Env::getRequest('page');
            $criteria = new CDbCriteria();
            $pages = new CPagination(OfficialdocUtil::getCharacterLength($data['content']));
            $pages->pageSize = 2000;
            $pages->applyLimit($criteria);
            $tmpContent = OfficialdocUtil::subHtml($data['content'], $pages->getCurrentPage() * $pages->getPageSize(), ($pages->getCurrentPage() + 1) * $pages->getPageSize());
            $data['content'] = $tmpContent;
            if (!empty($page) && $page != 1) {
                $data['content'] = '<div><div style="border-bottom:4px solid #e26f50;margin-top:60px;"></div><div style="border-top:1px solid #e26f50;margin-top:4px;"><div><p style="text-align:center;"></p><div id="original-content" style="min-height:400px;font:16px/2 ' . 'fangsong' . ',' . 'simsun' . ';color:#666;"><table cellspacing="0" cellpadding="0" width="95%" align="center"><tbody><tr><td class="p1"><span><p>' . $tmpContent . '</p>';
                $data['content'] = OfficialdocUtil::subHtml($data['content'], 0, $pages->pageSize * 2);
            }
            $params = array(
                'data' => $data,
                'pages' => $pages,
                'dashboardConfig' => Ibos::app()->setting->get('setting/docconfig'),
            );
            if ($data['rcid']) {
                $params['rcType'] = RcType::model()->fetchByPk($data['rcid']);
            }
            if (!empty($data['attachmentid'])) {
                $params['attach'] = Attach::getAttach($data['attachmentid']);
            }
        } else {
            $params = "";
        }
        $this->ajaxReturn($params, Mobile::dataType());
    }

    /**
     * 搜索
     * @return void
     */
    private function search()
    {
//		$type = Env::getRequest( 'type' );
//		$conditionCookie = MainUtil::getCookie( 'condition' );
//		if ( empty( $conditionCookie ) ) {
//			MainUtil::setCookie( 'condition', $this->_condition, 10 * 60 );
//		}
//		if ( $type == 'normal_search' ) {
        $keyword = Env::getRequest('search');
        $this->_condition = " subject LIKE '%$keyword%' ";
//			MainUtil::setCookie( 'keyword', $search, 10 * 60 );
//		} else {
//			$this->_condition = $conditionCookie;
//		}
        //把搜索条件存进cookie,当搜索出现分页时,搜索条件从cookie取
//		if ( $this->_condition != MainUtil::getCookie( 'condition' ) ) {
//			MainUtil::setCookie( 'condition', $this->_condition, 10 * 60 );
//		}
    }

    /**
     * 手机端处理单篇通知签收
     */
    public function actionSign()
    {
        $docid = Env::getRequest('docid', 'G');
        if (empty($docid)) {
            $isSuccess = false;
            $msg = '参数错误';
        } else {
            $uid = Ibos::app()->user->uid;
            $counter = OfficialdocReader::model()->updateSignByDocid($docid, $uid, true);
            if (!empty($counter)) {
                $isSuccess = true;
                $msg = '成功';
            } else {
                $isSuccess = false;
                $msg = '通知参数错误！';
            }
        }
        $this->ajaxReturn(array('isSuccess' => $isSuccess, 'msg' => $msg), Mobile::dataType());
    }

    /**
     * 手机端处理单篇通知查看
     */
    public function actionView()
    {
        $docid = Env::getRequest('docid', 'G');
        if (empty($docid)) {
            $isSuccess = false;
            $msg = '参数错误';
        } else {
            $uid = Ibos::app()->user->uid;
            OfficialdocReader::model()->addReader($docid, $uid);
            $isSuccess = true;
            $msg = '成功';
        }
        $this->ajaxReturn(array('isSuccess' => $isSuccess, 'msg' => $msg), Mobile::dataType());
    }

}
