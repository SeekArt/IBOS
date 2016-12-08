<?php

/**
 * ApprovalStep表的数据层操作文件
 *
 * @author gzhyj <gzhyj@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *  ApprovalStep表的数据层操作
 *
 * @package application.modules.dashboard.model
 * @version $Id: ApprovalStep.php 575 2014-04-24 16:42:03Z gzhyj $
 * @author gzhyj <gzhyj@ibos.com.cn>
 */

namespace application\modules\dashboard\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use application\core\utils\Module;
use application\modules\article\model\ArticleApproval;
use application\modules\article\model\ArticleBack;
use application\modules\article\model\ArticleCategory;
use application\modules\user\model\User;

class ApprovalStep extends Model
{

    // 审批流程状态
    const TYPE_SUCCESS_NUM = 1;     // 审核通过
    const TYPE_FAILED_NUM = 2;      // 审核退回/失败
    const TYPE_NOT_APPROVAL = 3;    // 未审核


    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{approval_step}}';
    }


    public function relations()
    {
        return array(
            'approval' => array(self::BELONGS_TO, 'Approval', 'aid'),
        );
    }

    /**
     * 获取所有审核人里面有某个用户的步骤数据
     * @param  integer $uid 用户 ID
     * @return array        步骤数据
     */
    public function getAllApprovalidByUid($uid)
    {
        $stepArr = $this->findAll(sprintf("FIND_IN_SET( '%s', `uids` )", $uid));
        foreach ($stepArr as $step) {
            if (!isset($result[$step->step])) {
                $reuslt[$step->step] = explode(',', $step->uids);
            } else {
                $result[$step->step] = array_merge($result[$step->step], explode(',', $step->uids));
            }
        }
        return isset($result) ? $result : array();
    }

    /**
     * 获取审批流程某一步的审批人员列表字符串
     * @param  integer $aid 审批流程 ID
     * @param  integer $step 步骤
     * @return string        审批人员字符串
     */
    public function getApprovalerStr($aid, $step)
    {
        $approvalStep = $this->fetch(sprintf("`aid` = %d AND `step` = %d", $aid, $step));
        return !empty($approvalStep) ? $approvalStep['uids'] : '';
    }

    /**
     * 获取审核流程步骤需要的数据
     *
     * @return array 审批流程步骤需要的数据
     */
    public function getPreApprovalStepData()
    {
        return array(
            "allApprovals" => Approval::model()->fetchAllSortByPk('id'),// 所有审批流程
            "allCategorys" => ArticleCategory::model()->fetchAllSortByPk('catid'), // 所有新闻分类
            "artApprovals" => ArticleApproval::model()->fetchAllGroupByArtId(), // 已走审批的新闻
            "backArtIds" => ArticleBack::model()->fetchAllBackArtId(),
        );
    }

    // 获取新闻审核流程步骤数据
    public function getApprovalStepData(&$art, $preData)
    {
        if (is_array($preData)) {
            extract($preData);
        }

        // 当前新闻是否已被退回。1 是，0 否
        $art['back'] = in_array($art['articleid'], $backArtIds) ? 1 : 0;
        $art['approval'] = $art['approvalStep'] = array();
        $catid = (int)$art['catid'];
        $articleid = (int)$art['articleid'];

        // 如果该新闻的分类需要审核，则获取该审核流程的数据
        if (!empty($allCategorys[$catid]['aid'])) { // 审批流程不为空
            $aid = $allCategorys[$catid]['aid'];
            if (!empty($allApprovals[$aid])) {
                $art['approval'] = $allApprovals[$aid];
            }
        }

        // 如果该新闻有审核流程数据
        if (!empty($art['approval'])) {
            // 当前新闻所有审核步骤数据
            $allArtApprovals = Ibos::app()->db->createCommand()
                ->select('id, relateid as articleid, uid, time, step')
                ->from('{{approval_record}}')
                ->where('relateid = :relateid', array(':relateid' => $articleid))
                ->order('id asc')
                ->queryAll();

            $art['approvalName'] = !empty($art['approval']) ? $art['approval']['name'] : ''; // 审批流程名称
            $art['artApproval'] = isset($artApprovals[$art['articleid']]) ? $artApprovals[$art['articleid']] : array(); // 某篇新闻的审批步骤记录
            $art['stepNum'] = count($art['artApproval']); // 共审核了几步
            $step = array();
            foreach ($art['artApproval'] as $artApproval) {
                $step[$artApproval['step']] = User::model()->fetchRealnameByUid($artApproval['uid']); // 步骤=>审核人名称 格式
            }

            // 获取审核流程数据
            for ($i = 1; $i <= $art['approval']['level']; $i++) {
                if ($i <= $art['stepNum']) { // 如果已走审批步骤，找审批的人的名称， 否则找应该审核的人
                    $art['approval'][$i]['approvaler'] = isset($step[$i]) ? $step[$i] : '未知'; // 容错
                } else {
                    $approvalUids = self::getApprovalerStr($art['approval']['id'], $i);
                    $art['approval'][$i]['approvaler'] = User::model()->fetchRealnamesByUids($approvalUids, '、');
                }

                // 添加审核步骤数据
                // 当前新闻审核步骤审核人列表
                $approvaler = $art['approval'][$i]['approvaler'];
                if (isset($allArtApprovals[$i - 1])) {
                    // 当前审核流程数据
                    $approvalStepData = $allArtApprovals[$i - 1];

                    $art["approvalStep"][$i - 1] = array(
                        // 审核步骤名称，预留
                        "name" => "",
                        // 审核通过或审核退回的时间，使用时间戳格式
                        "addtime" => $approvalStepData["time"],
                        // 审核步骤id
                        "id" => $approvalStepData["id"],
                        // 审核步骤
                        "step" => $approvalStepData["step"] + 1,
                        // 审核人，多个用户使用顿号隔开
                        "approver" => $approvaler,
                        // 退回理由。如果审核通过，则为空。
                        "reason" => "",
                        // 审核状态，1成功、2失败、3未审核
                        "status" => self::TYPE_SUCCESS_NUM,
                    );

                    // 该新闻被退回并且当前是最后一个步骤
                    if ((1 === (int)$art["back"]) && (count($allArtApprovals) === $i)) {
                        $back = ArticleBack::model()->find("articleid = :articleid", array(":articleid" => $articleid));
                        $art["approvalStep"][$i - 1]["reason"] = $back->reason;
                        // 失败
                        $art["approvalStep"][$i - 1]["status"] = self::TYPE_FAILED_NUM;
                    }

                    // 该新闻未被退回并且当前是最后一个步骤
                    if ((0 === (int)$art["back"]) && (count($allArtApprovals) === $i)) {
                        // 未审核
                        $art["approvalStep"][$i - 1]["status"] = self::TYPE_NOT_APPROVAL;
                    }
                }
            }

            $approvalLevel = (int)$art["approval"]["level"];
            $currLevel = count($art["approvalStep"]);
            // 获取剩下的审核数据
            for (; $currLevel < $approvalLevel; $currLevel++) {
                $approver = @$art["approval"][$currLevel + 1]["approvaler"];
                if (empty($approver)) {
                    $approver = "未知";
                }
                $art["approvalStep"][] = array(
                    "name" => "",
                    "addtime" => 0,
                    "id" => 0,
                    "step" => $currLevel + 1,
                    "approver" => $approver,
                    "reason" => "",
                    "status" => self::TYPE_NOT_APPROVAL,
                );
            }
        }
    }

    /**
     * 根据审核的流程ID得到每个步骤的审核uid，以步骤step为键，uids为值
     * $param integer $aid 审核ID
     */
    public function getStepUids($aid)
    {
        $record = $this->fetchAll(array(
            'condition' => "aid={$aid}",
            'order' => 'step ASC'
        ));
        $list = array();
        foreach ($record as $value) {
            $list[$value['step']] = $value['uids'];
        }
        return $list;
    }
}
