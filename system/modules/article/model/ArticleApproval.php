<?php

/**
 * 新闻模块------ article_approval表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

/**
 * 新闻模块 审批步骤记录------  article_approval表的数据层操作类，继承ICModel
 * @package application.modules.article.model
 * @version $Id: ArticleApproval.php 2669 2014-04-26 08:58:29Z gzhzh $
 * @author gzhzh <gzhzh@ibos.com.cn>
 */

namespace application\modules\article\model;

use application\core\model\Model;
use application\core\utils\Ibos;
use application\modules\user\model\User;

class ArticleApproval extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{article_approval}}';
    }

    /**
     * 已改为只要是其中一个审核人或作者都能看到未审核完的文章
     * 获取某个uid的未审核新闻
     * @return array
     */
//	public function fetchUnAuditedArtidByUid( $uid ) {
//		$artIdArr = array();
//		$artApprovals = $this->fetchAll();
//		foreach ( $artApprovals as $artApproval ) {
//			$art = Article::model()->fetchByPk( $artApproval['articleid'] );
//			if ( !empty( $art['catid'] ) ) {
//				$category = ArticleCategory::model()->fetchByPk( $art['catid'] );
//				if ( !empty( $category['aid'] ) ) {
//					$approval = Approval::model()->fetchByPk( $category['aid'] );
//					if ( ($artApproval['step'] + 1) <= $approval['level'] ) { // 还没审核完，查找下一步审核的uid
//						$levelName = Approval::model()->getLevelNameByStep( $artApproval['step'] + 1 );
//						if ( in_array( $uid, explode( ',', $approval[$levelName] ) ) ) { // uid在下一步审核人中，该公文属于该uid的未审核公文
//							$artIdArr[] = $artApproval['articleid'];
//						}
//					}
//				}
//			}
//		}
//		return $artIdArr;
//	}

    /**
     * 记录签收步骤
     * @param integer $artId 新闻id
     * @param integer $uid 签收人uid
     * @param string $time 签收时间
     * @param integer $status 签收状态  0表示退回，1表示通过，2表示结束
     */
    public function recordStep($artId, $uid, $time = 0, $status = 1)
    {
        $artApproval = $this->fetchLastStep($artId);
        if (empty($artApproval)) { // 第0步表示新的未审核公文
            $step = 0;
        } else {
            $step = $artApproval['step'] + 1;
        }
        return $this->add(array(
            'articleid' => $artId,
            'uid' => $uid,
            'step' => $step,
            'time' => $time,
            'status' => $status,
        ));
    }

    /**
     * 获取某篇新闻最后一条审批步骤
     * @param integer $artId
     * @return array
     */
    public function fetchLastStep($artId)
    {
        $record = $this->fetch(array(
            'condition' => "articleid={$artId}",
            'order' => 'step DESC'
        ));
        return $record;
    }

    /**
     * 根据新闻ids删除审核记录
     * @param mix $artIds
     */
    public function deleteByArtIds($artIds)
    {
        $artIds = is_array($artIds) ? implode(',', $artIds) : $artIds;
        return $this->deleteAll("FIND_IN_SET(articleid,'{$artIds}')");
    }

    /**
     * 查询已走审核步骤的新闻，并按新闻id分组
     * @return array
     */
    public function fetchAllGroupByArtId()
    {
        $result = array();
        $records = $this->fetchAll("step > 0");
        if (!empty($records)) {
            foreach ($records as $record) {
                $artId = $record['articleid'];
                $result[$artId][] = $record;
            }
        }
        return $result;
    }

    /*
     * 根据新闻ID得到审核流程日志
     * @param integer $articleid 新闻ID
     * @return array 每条日志包括用户真实名，状态，原因，审核时间（或者发布时间）
     */
    public function getVerifyFlowLog($articleid)
    {
        $log = array();
        $lists = $this->fetchAll(array(
            'condition' => "articleid={$articleid}",
            'order' => 'step,time ASC'
        ));
        $article = Article::model()->fetch(array('condition' => "articleid={$articleid}"));
        for ($i = 0; $i < count($lists); $i++) {
            if ($lists[$i]['status'] == 3) {//发布者
                $log[] = array(
                    'author' => $article['relaname'],
                    'status' => Ibos::lang('Publish'),
                    'reason' => '',
                    'time' => date('Y-m-d h:i:s', $article['addtime']),
                );
            } elseif ($lists[$i]['status'] = 1 || $lists[$i]['status'] = 2) {//通过者,包括通过和结束
                $userRelaName = User::model()->fetchRealnameByUid($lists[$i]['uid']);
                $log[] = array(
                    'author' => $userRelaName,
                    'status' => Ibos::lang('Pass'),
                    'reason' => '',
                    'time' => date('Y-m-d h:i:s', $lists[$i]['time']),
                );
            } elseif ($lists[$i]['status'] = 0) {//退回者
                $record = ArticleBack::model()->getBackByArticleId($articleid);
                $userRelaName = User::model()->fetchRealnameByUid($lists[$i]['uid']);
                $log[] = array(
                    'author' => $userRelaName,
                    'status' => Ibos::lang('Back'),
                    'reason' => $record['reason'],
                    'time' => date('Y-m-d h:i:s', $record['time']),
                );
            }
        }
        return $log;
    }

}
