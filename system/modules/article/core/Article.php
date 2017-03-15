<?php

/**
 * 文章模块------文章组件类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzzyb <gzwwb@ibos.com.cn>
 */
/**
 * 文章模块------文章组件类
 * @package application.modules.article.model
 * @version $Id: Article.php 8957 2016-11-07 06:17:06Z php_lwd $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\article\core;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\article\model\ArticleCategory;
use application\modules\article\model\ArticlePicture;
use application\modules\article\model\ArticleReader;
use application\modules\article\utils\Article as ArticleUtil;
use application\modules\dashboard\model\Approval;
use application\modules\dashboard\model\ApprovalRecord;
use application\modules\dashboard\model\ApprovalStep;
use application\modules\department\model\Department;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\position\utils\Position as PositionUtil;
use application\modules\role\utils\Role as RoleUtil;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class Article
{

    // 管理权限
    const NO_PERMISSION = 0; // 无权限
    const ONLY_SELF = 1; //本人
    const CONTAIN_SUB = 2; //本人及下属
    const SELF_BRANCH = 4; // 当前分支机构
    const All_PERMISSION = 8; // 全部

    /**
     * 对查看页数据进行相应处理，方便页面输出显示
     * @param array $data
     * @return array
     */

    public static function getShowData($data, $uid)
    {
        $data["subject"] = stripslashes($data["subject"]);

        $approver = explode(',', $data['approver']);
        $back = ApprovalRecord::model()->fetchLastStep($data['articleid']);
        $passedUid = ApprovalRecord::model()->getPassedUid($data['articleid']);
        if ($data['status'] == 2 && in_array($uid, $approver)) {//待我审核
            $data['tableType'] = 'wait';
        } elseif (isset($back) && !empty($back) && $back['status'] == 0 && $data['author'] == $uid) {//被退回
            $data['tableType'] = 'reback_to';
        } elseif ($data['author'] == $uid && $data['status'] == 2) {//审核中
            $data['tableType'] = 'approval';
        } elseif (count($passedUid) != 0) {//我已通过
            if (in_array($uid, $passedUid)) {
                $data['tableType'] = 'passed';
            }
        } else {
            $data['tableType'] = 'publish';
        }
        if (!isset($data['tableType'])) {
            $data['tableType'] = "";
        }

        if (!empty($data['author'])) {
            $data['authorDeptName'] = Department::model()->fetchDeptNameByUid($data['author']);
            $data['author'] = User::model()->fetchRealnameByUid($data['author']);
        }

        if ($data['approver'] != 0) {
            $data['approver'] = User::model()->fetchRealnameByUid($data['approver']);
        } else {
            $data['approver'] = Ibos::lang('None');
        }

        $data['addtime'] = Convert::formatDate($data['addtime'], 'u');
        $data['uptime'] = empty($data['uptime']) ? '' : Convert::formatDate($data['uptime'], 'u');
        $data['categoryName'] = ArticleCategory::model()->fetchCateNameByCatid($data['catid']);
        // 图片类型新闻
        if ($data['type'] == 1) {
            $data['pictureData'] = ArticlePicture::model()->fetchPictureByArticleId($data['articleid']);
            // 修改图片输出路径，对应saas和本地的不同
            foreach ($data['pictureData'] as $key => $pic) {
                $pic['filepath'] = File::imageName($pic['filepath']);
            }
        }
        if (empty($data['deptid']) && empty($data['positionid']) && empty($data['uid']) && empty($data['roleid'])) {
            $data['departmentNames'] = Ibos::lang('All');
            $data['positionNames'] = $data['uidNames'] = '';
        } else {
            if ($data['deptid'] == 'alldept') {
                $data['departmentNames'] = Ibos::lang('All');
                $data['positionNames'] = $data['uidNames'] = $data['roleNames'] = '';
            } else {
                //取得部门名称集以、号分隔
                $department = DepartmentUtil::loadDepartment();
                $data['departmentNames'] = ArticleUtil::joinStringByArray($data['deptid'], $department, 'deptname',
                    '、');
                //取得职位名称集以、号分隔
                $position = PositionUtil::loadPosition();
                $data['positionNames'] = ArticleUtil::joinStringByArray($data['positionid'], $position, 'posname', '、');
                // 取得角色名称集以、号分割
                $role = RoleUtil::loadRole();
                $data['roleNames'] = ArticleUtil::joinStringByArray($data['roleid'], $role, 'rolename', '、');

                //取得阅读范围人员名称集以、号分隔
                if (!empty($data['uid'])) {
                    $users = User::model()->fetchAllByUids(explode(",", $data['uid']));
                    $data['uidNames'] = ArticleUtil::joinStringByArray($data['uid'], $users, 'realname', '、');
                } else {
                    $data['uidNames'] = "";
                }
            }
        }
        return $data;
    }

    /**
     * 对列表原始数据进行处理，方便渲染视图显示
     * @param array $datas
     * @return array $listArr
     * @todo 有html代码
     */
    public static function getListData($datas, $uid)
    {
        $datas = self::handlePurv($datas);
        $listDatas = array();
        $checkTime = 3 * 86400;
        // 所有已读新闻id
        $readArtIds = ArticleReader::model()->fetchReadArtIdsByUid($uid);
        foreach ($datas as $data) {
            $data['subject'] = StringUtil::cutStr($data['subject'], 50);
            // 1:已读；-1:未读
            $data['readStatus'] = in_array($data['articleid'], $readArtIds) ? 1 : -1;
            //发布时间由时间戳=》年-月-日
            $data['publishtime'] = date('Y-m-d H:i:s', $data['addtime']);
            //如果新闻已经通过得到通过时间
            if ($data['status'] == 1 && $data['uptime'] != 0) {
                $data['opentime'] = date('Y-m-d H:i:s', $data['uptime']);
            } else {
                $data['opentime'] = date('Y-m-d H:i:s', $data['addtime']);
            }
            // 三天内为新文章
            if ($data['readStatus'] === -1 && $data['uptime'] > TIMESTAMP - $checkTime) {
                $data['readStatus'] = 2;
            }

            $data['author'] = User::model()->fetchRealnameByUid($data['author']);
            if (empty($data['uptime'])) {
                $data['uptime'] = $data['addtime'];
            }
            //得到新闻的分类名
            $category = ArticleCategory::model()->fetchByPk($data['catid']);
            $data['categoryname'] = $category['name'];

            $data['uptime'] = Convert::formatDate($data['uptime'], 'u');
            $keyword = Env::getRequest('keyword');
            if (!empty($keyword)) {
                // 搜索关键字变红
                $data['subject'] = preg_replace("|({$keyword})|i", "<span style='color:red'>\$1</span>",
                    $data['subject']);
            }
            if ($data['ishighlight'] == '1') {
                // 有高亮,形式为bolc,color,in,undefinline
                $highLightStyle = $data['highlightstyle'];
                $hiddenInput = "<input type='hidden' id='{$data['articleid']}_hlstyle' value='$highLightStyle'/>";
                $data['subject'] .= $hiddenInput;
                $highLightStyleArr = explode(',', $highLightStyle);
                // 字体颜色
                $color = $highLightStyleArr[0];
                // 字体加粗
                $isB = $highLightStyleArr[1];
                // 字体倾斜
                $isI = $highLightStyleArr[2];
                // 字体下划线
                $isU = $highLightStyleArr[3];
                $isB && $data['subject'] = "<b>{$data['subject']}</b>";
                $isU && $data['subject'] = "<u>{$data['subject']}</u>";
                $fontStyle = '';
                $color != '' && $fontStyle .= "color:{$color};";
                $isI && $fontStyle .= "font-style:italic;";
                $fontStyle != '' && $data['subject'] = "<font style='{$fontStyle}'>{$data['subject']}</font>";
            }
            $listDatas[] = $data;
        }
        return $listDatas;
    }

    /**
     * 处理未审核列表的审核流程数据
     * @param array $datas 要处理的未审核新闻
     * @return array
     */
    public static function handleApproval($datas)
    {
        $allApprovals = Approval::model()->fetchAllSortByPk('id'); // 所有审批流程
        $allCategorys = ArticleCategory::model()->fetchAllSortByPk('catid'); // 所有新闻分类
        $artApprovals = ApprovalRecord::model()->fetchAllGroupByArtId(); // 已走审批的新闻
        $backArtIds = ApprovalRecord::model()->fetchAllBackArtId();
        foreach ($datas as &$art) {
            $art['back'] = in_array($art['articleid'], $backArtIds) ? 1 : 0;
            $art['approval'] = $art['approvalStep'] = array();
            $catid = $art['catid'];
            if (!empty($allCategorys[$catid]['aid'])) { // 审批流程不为空
                $aid = $allCategorys[$catid]['aid'];
                if (!empty($allApprovals[$aid])) {
                    $art['approval'] = $allApprovals[$aid];
                }
            }
            if (!empty($art['approval'])) {
                $art['approvalName'] = !empty($art['approval']) ? $art['approval']['name'] : ''; // 审批流程名称
                $art['artApproval'] = isset($artApprovals[$art['articleid']]) ? $artApprovals[$art['articleid']] : array(); // 某篇新闻的审批步骤记录
                $art['stepNum'] = count($art['artApproval']); // 共审核了几步
                $step = array();
                foreach ($art['artApproval'] as $artApproval) {
                    $step[$artApproval['step']] = User::model()->fetchRealnameByUid($artApproval['uid']); // 步骤=>审核人名称 格式
                }
                for ($i = 1; $i <= $art['approval']['level']; $i++) {
                    if ($i <= $art['stepNum']) { // 如果已走审批步骤，找审批的人的名称， 否则找应该审核的人
                        $art['approval'][$i]['approvaler'] = isset($step[$i]) ? $step[$i] : '未知'; // 容错
                    } else {
                        $approvalUids = ApprovalStep::model()->getApprovalerStr($art['approval']['id'], $i);
                        $art['approval'][$i]['approvaler'] = User::model()->fetchRealnamesByUids($approvalUids, '、');
                    }
                }
            }
        }
        return $datas;
    }

    /**
     * 为每一篇文章加上阅读状态
     * @param array $data 源数据
     * @param integer $uid 用户Ｉｄ
     * @return array
     */
    public static function setReadStatus($data, $uid)
    {
        if (is_array($data) && count($data) > 0) {
            for ($i = 0; $i < count($data); $i++) {
                $articleid = $data[$i]['articleid'];
                if (ArticleReader::model()->checkIsRead($articleid, $uid)) {
                    $data[$i]['readStatus'] = 1;
                } else {
                    $data[$i]['readStatus'] = 0;
                }
            }
        }
        return $data;
    }

    /**
     * 表单数据验证
     * @param array $data
     * @return boolean
     */
    public static function formCheck($data)
    {
        if (empty($data['subject'])) {
            return false;
        }
        return true;
    }

    /**
     * 处理编辑、删除权限
     * @param array $list 文章数据
     * @return array
     */
    public static function handlePurv($list)
    {
        if (empty($list)) {
            return $list;
        }
        if (Ibos::app()->user->isadministrator) {
            $list = self::grantPermission($list, 1, self::getAllowType('edit'));
            return self::grantPermission($list, 1, self::getAllowType('del'));
        }
        $uid = Ibos::app()->user->uid;
        $user = User::model()->fetchByUid($uid);
        // 编辑、删除权限，取主岗位和辅助岗位权限最大值
        $editPurv = RoleUtil::getMaxPurv($uid, 'article/manager/edit');
        $delPurv = RoleUtil::getMaxPurv($uid, 'article/manager/del');
        $list = self::handlePermission($user, $list, $editPurv, 'edit');
        $list = self::handlePermission($user, $list, $delPurv, 'del');
        return $list;
    }

    /**
     * 获取允许类型字符串
     * @param string $type edit、del
     * @return string allowEdit、allowDel
     */
    private static function getAllowType($type)
    {
        return 'allow' . ucfirst($type);
    }

    /**
     * 处理是否有编辑/删除权限
     * @param array $user 登陆者用户数组
     * @param array $list 要处理的新闻数组
     * @param integer $purv 岗位对应权限数值
     * @param string $type 类型（编辑：edit，删除：del）
     * @return array
     */
    private static function handlePermission($user, $list, $purv, $type)
    {
        $uid = $user['uid'];
        $allowType = self::getAllowType($type);
        switch ($purv) {
            // 没权限
            case self::NO_PERMISSION:
                $list = self::grantPermission($list, 0, $allowType);
                break;
            // 只是自己
            case self::ONLY_SELF:
                foreach ($list as $k => $article) {
                    if ($article['author'] == $uid) {
                        $list[$k][$allowType] = 1;
                    } else {
                        $list[$k][$allowType] = 0;
                    }
                }
                break;
            // 自己和下属
            case self::CONTAIN_SUB:
                $subUids = UserUtil::getAllSubs($uid, '', true);
                array_push($subUids, $uid);
                $accordUid = array_unique($subUids);
                foreach ($list as $k => $article) {
                    if (in_array($article['author'], $accordUid)) {
                        $list[$k][$allowType] = 1;
                    } else {
                        $list[$k][$allowType] = 0;
                    }
                }
                break;
            // 所在分支
            case self::SELF_BRANCH:
                $branch = Department::model()->getBranchParent($user['deptid']);
                $childDeptIds = Department::model()->fetchChildIdByDeptids($branch['deptid'], true);
                $accordUid = User::model()->fetchAllUidByDeptids($childDeptIds, false);
                foreach ($list as $k => $article) {
                    if (in_array($article['author'], $accordUid)) {
                        $list[$k][$allowType] = 1;
                    } else {
                        $list[$k][$allowType] = 0;
                    }
                }
                break;
            // 所有人
            case self::All_PERMISSION:
                $list = self::grantPermission($list, 1, $allowType);
                break;
            default :
                $list = self::grantPermission($list, 0, $allowType);
                break;
        }
        return $list;
    }

    /**
     * 遍历给用户授予管理权限
     * @param array $users 用户数组
     * @param integer $permission 授与管理权限（0为无权限，1为有权限）
     * @param string $allowType 类型（allowEdit、allowDel）
     * @return array
     */
    private static function grantPermission($list, $permission, $allowType)
    {
        foreach ($list as $k => $article) {
            $list[$k][$allowType] = $permission;
        }
        return $list;
    }

}
