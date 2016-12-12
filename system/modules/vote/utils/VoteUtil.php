<?php

/**
 * 投票模块------投票工具类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 投票模块------投票工具类
 * @package application.modules.vote.utils
 * @version $Id: VoteUtil.php 140 2013-05-29 16:55:50Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\vote\utils;

use application\core\utils\ArrayUtil;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\PHPExcel;
use application\core\utils\StringUtil;
use application\extensions\ChineseNumericHelper;
use application\modules\article\utils\Article;
use application\modules\department\utils\Department;
use application\modules\user\model\Reader;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\modules\vote\model\Vote;
use application\modules\vote\model\VoteItem;
use application\modules\vote\model\VoteItemCount;
use application\modules\vote\model\VoteTopic;
use application\modules\vote\VoteModule;

class VoteUtil
{

    /**
     * 对投票源数据进行相应页面显示处理
     * @param array $data 处理前投票数据
     * @return array 处理后投票数据
     */
    public static function processVoteData($data)
    {
        //如果有投票项记录，设置各自投票项票数和总票数所占的百分比
        if (!empty($data)) {
            $data['voteItemList'] = self::getPercentage($data['voteItemList']);
            //得到投票剩余结束时间
            $data['vote']['remainTime'] = self::getRemainTime($data['vote']['starttime'], $data['vote']['endtime']);
        }
        return $data;
    }

    /**
     * 判断用户是否投过票
     * @param string $relatedModule 关联模块名称
     * @param integer $relatedId 关联模块id
     * @param integer $uid 访问当前投票用户UID,如果不填，则默认Ibos::app()->user->uid
     * @return boolean true为已投，false为没投过票
     */
    public static function checkVote($relatedModule, $relatedId, $uid = 0)
    {
        $result = false;
        $uid = empty($uid) ? Ibos::app()->user->uid : $uid;
        $condition = 'relatedmodule=:relatedmodule AND relatedid=:relatedid';
        $params = array(
            ':relatedmodule' => $relatedModule,
            ':relatedid' => $relatedId
        );
        $vote = Vote::model()->fetch($condition, $params);
        if (!empty($vote)) {
            //取出所有投票项
            $voteid = $vote['voteid'];
            $voteItemList = VoteItem::model()->fetchAll("voteid=:voteid", array(':voteid' => $voteid));
            //判断所有投票项下是否有用户记录
            foreach ($voteItemList as $voteItem) {
                $itemid = $voteItem['itemid'];
                //取出所有投票用户信息
                $itemCountList = VoteItemCount::model()->fetchAll("itemid=:itemid", array(':itemid' => $itemid));
                if (!empty($itemCountList) && count($itemCountList) > 0) {
                    foreach ($itemCountList as $itemCount) {
                        if ($itemCount['uid'] == $uid) {
                            $result = true;
                            break;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 设置各自投票项票数和总票数所占的百分比以及颜色样式
     * @param array $voteItemList 未经过处理的投票项集合
     * @return array $voteItemList 处理后的投票项集合
     */
    private static function getPercentage($voteItemList)
    {
        //得到所有票数
        $numberCount = 0;
        foreach ($voteItemList as $index => $voteItem) {
            $voteItemList[$index]['picpath'] = File::fileName($voteItem['picpath']);
            $numberCount += $voteItem['number'];
        }
        //计算各自投票项所占百分比
        $length = count($voteItemList);
        if ($numberCount == 0) {
            //如果票数为0，说明没人投票，设置所有投票项百分比为0%
            for ($i = 0; $i < $length; $i++) {
                $voteItemList[$i]['percentage'] = '0%';
                $voteItemList[$i]['color_style'] = '';
            }
        } else {
            //如果票数不为0，计算各自的百分比
            $percentageCount = 0;
            $count = 0;
            $colors = array('#91CE31', '#EE8C0C', '#E26F50', '#3497DB');
            $colorLength = count($colors);
            for ($i = 0; $i < $length; $i++) {
                $percentage = round($voteItemList[$i]['number'] / $numberCount * 100);
                $voteItemList[$i]['percentage'] = $percentage;
                $percentageCount = $percentageCount + $voteItemList[$i]['percentage'];
                //设置投票项的颜色样式
                $voteItemList[$i]['color_style'] = $colors[$count];
                $count++;
                if ($count >= $colorLength) {
                    $count = 0;
                }
            }
            if ($percentageCount != 100) {
                $voteItemList[0]['percentage'] = $voteItemList[0]['percentage'] + 1;
            }
            for ($i = 0; $i < $length; $i++) {
                $voteItemList[$i]['percentage'] = $voteItemList[$i]['percentage'] . '%';
            }
        }
        return $voteItemList;
    }

    /**
     * 设置投票结束时间
     * @param integer $startTime 投票开始时间戳
     * @param integer $dayNumber 天数
     * @return integer 结束投票时间戳
     */
    public static function setEndTime($startTime, $dayNumber)
    {
        return $startTime + $dayNumber * 24 * 60 * 60;
    }

    /**
     * 取得剩余结束时间
     * @code array('day'=>5,'hour'=>17,'minute'=>43,'second'=>31)
     * @param integer $startTime 开始时间戳
     * @param integer $endTime 结束时间戳
     * @return mixed 有剩余时间则返回数组如上示例数组,0代表无结束时间，-1代表已经过了结束时间
     */
    public static function getRemainTime($startTime, $endTime)
    {
        $remainTime = $endTime - time();
        if ($endTime == 0) {
            return 0;
        } else if ($endTime > $startTime && $remainTime > 0) {
            $minuteCount = floor($remainTime / 60);
            $dayNumber = floor($minuteCount / (60 * 24));
            $remainHour = floor(($minuteCount - $dayNumber * 24 * 60) / 60);
            $remainMinute = floor(($minuteCount - $dayNumber * 24 * 60) % 60);
            $remainSecond = round(($remainTime / 60 - $minuteCount) * 60);
            $remainTime = array(
                'day' => $dayNumber,
                'hour' => $remainHour,
                'minute' => $remainMinute,
                'second' => $remainSecond
            );
            return $remainTime;
        } else if ($endTime > $startTime && $remainTime <= 0) {
            return -1;
        }

        return false;
    }

    public static function processDateTime($dateTime)
    {
        $resultTime = 0;
        if ($dateTime == 'One week') {
            $resultTime = time() + 7 * 24 * 60 * 60;
        } else if ($dateTime == 'One month') {
            $resultTime = time() + 30 * 24 * 60 * 60;
        } else if ($dateTime == 'half of a year') {
            $resultTime = time() + 6 * 30 * 24 * 60 * 60;
        } else if ($dateTime == 'One year') {
            $resultTime = time() + 365 * 24 * 60 * 60;
        }
        return $resultTime;
    }

    /**
     * 取得投票结束时间
     * @param integer $endtime
     * @param integer $selectEndIime
     * @return int|string
     */
    public static function getEndTime($endtime, $selectEndIime)
    {
        $result = '';
        $selectEndTime = trim($selectEndIime);
        if (isset($endtime) && $selectEndTime == 'Custom') {
            $result = strtotime($endtime) + 24 * 60 * 60 - 1;
        } else if ($selectEndTime !== 'Custom') {
            $result = self::processDateTime($selectEndTime);
        }
        return $result;
    }

    /**
     * 添加一条投票记录
     *
     * @param array $postData 投票数据
     * @param string $moduleName 关联模块名
     * @param integer $moduleId 关联模块表 id
     * @return int 新增投票 id
     * @throws \Exception
     */
    public static function addVote($postData, $moduleName, $moduleId)
    {
        // 提交参数检查
        RequestValidator::getInstance()->initAddVoteForVoteModule($postData);
        RequestValidator::getInstance()->initAddVote($postData);

        $voteId = self::add($postData, $moduleName, $moduleId);

        return $voteId;
    }


    /**
     * 获取投票选择范围内用户 uid 数组
     *
     * @param integer $voteId
     * @return array
     */
    public static function fetchScopeUidArr($voteId)
    {
        $vote = self::fetchVoteByPk($voteId);

        $scopeStr = StringUtil::joinSelectBoxValue($vote['deptid'], $vote['positionid'], $vote['scopeuid'], $vote['roleid']);
        $scopeArr = StringUtil::handleSelectBoxData($scopeStr);
        $uidArr = Article::getScopeUidArr($scopeArr);

        return $uidArr;
    }


    /**
     * 修改投票记录
     * @param array $postData
     * @param string $moduleName
     * @param integer $moduleId
     * @return int
     * @throws \Exception
     */
    public static function updateVote($postData, $moduleName, $moduleId)
    {
        RequestValidator::getInstance()->initUpdateVote($postData);

        $voteId = $postData['voteid'];
        // 删除已有的投票数据
        $delStatus = self::delOneVote($voteId);
        if ($delStatus === false) {
            // 投票数据删除失败
            throw new \InvalidArgumentException(Ibos::lang('the vote not exists'));
        }
        // 添加投票数据
        $voteId = self::add($postData, $moduleName, $moduleId);
        return $voteId;
    }


    /**
     * 删除投票记录
     *
     * @param array $postData
     * @return bool
     */
    public static function delVotes($postData)
    {
        RequestValidator::getInstance()->initDelVotes($postData);
        $flag = true;

        foreach ($postData['voteid'] as $voteId) {
            $delStatus = self::delOneVote($voteId);
            $flag = $flag && $delStatus;
        }

        return $flag;
    }


    public static function delOneVote($voteId)
    {
        $voteId = (int)$voteId;

        // 开始删除投票数据
        $voteDelStatus = Vote::model()->delByVoteId($voteId);
        $voteTopicDelStatus = VoteTopic::model()->delAllByVoteId($voteId);
        $voteItemDelStatus = VoteItem::model()->delAll($voteId);
        $voteItemCountDelStatus = VoteItemCount::model()->delAll($voteId);

        return $voteDelStatus && $voteTopicDelStatus && $voteItemDelStatus && $voteItemCountDelStatus;
    }


    /**
     * @param $voteId
     * @return mixed
     */
    public static function fetchVoteByPk($voteId)
    {
        $vote = Vote::model()->fetchVoteByPk($voteId);

        if (empty($vote)) {
            throw new \InvalidArgumentException(Ibos::lang('the vote not exists'));
        }

        return $vote;
    }

    /**
     * 更新投票截止时间
     *
     * @param array $postData
     * @return bool
     * @throws \Exception
     */
    public static function updateEndTime(array $postData)
    {
        RequestValidator::getInstance()->initUpdateEndTime($postData);

        $voteId = $postData['voteid'];
        $endTime = strtotime($postData['endtime']);


        // 更新投票截至时间
        // 检查截至时间是否正确
        if ($endTime < TIMESTAMP) {
            throw new \InvalidArgumentException(Ibos::lang('endtime value invalid'));
        }
        $updateStatus = Vote::model()->updateAttributes($voteId, array('endtime' => $endTime, 'updatetime' => TIMESTAMP));

        if ($updateStatus === false) {
            throw new \Exception(Ibos::lang('update vote attributes failed'));
        }

        return true;
    }


    /**
     * 获取投票表单需要的数据
     *
     * @param null $voteId
     * @return array
     */
    public static function fetchVoteFormData($voteId)
    {
        $vote = self::fetchVoteByPk($voteId);
        $vote['publishScope'] = StringUtil::joinSelectBoxValue($vote['deptid'], $vote['positionid'], $vote['scopeuid'], $vote['roleid']);

        return array('vote' => $vote);
    }

    /**
     * 返回投票详细内容
     *
     * @param integer $voteId
     * @return array
     */
    public static function showVote($voteId)
    {
        $uid = Ibos::app()->user->uid;

        // 获取投票信息和投票话题信息
        list($vote, $topics) = Vote::model()->fetchVoteDetail($voteId);

        // 处理投票信息和投票话题信息
        list($vote, $topics) = self::handleShowVote($vote, $topics);

        // 添加阅读记录
        Reader::model()->addRecordIsNotExists(VoteModule::MODULE_NAME, $voteId, $uid);

        return array(
            'vote' => $vote,
            'topics' => $topics,
        );
    }

    /**
     * 返回用户的参与情况
     *
     * @param integer $voteId 投票 id
     * @return array
     */
    public static function showVoteUsers($voteId)
    {
        // 获取参与人员情况
        list($joinedUidArr, $unJoinedUidArr) = VoteUserUtil::getInstance()->fetchGroupUidArr($voteId);

        return array(
            'users' => array(
                'joined' => $joinedUidArr,
                'unjoined' => $unJoinedUidArr,
            ),
        );
    }

    /**
     * 处理投票详细内容，添加需要的字段
     *
     * @param array $vote
     * @param array $topics
     * @return array
     */
    private static function handleShowVote($vote, $topics)
    {
        $uid = Ibos::app()->user->uid;
        $voteId = $vote['voteid'];


        // 添加投票发起人真实姓名
        $user = User::model()->fetchByPk($vote['uid']);
        $vote['realname'] = $user['realname'];
        // 添加投票状态
        $endTime = $vote['endtime'];
        $vote['status'] = self::calcVoteStatus($endTime);
        $vote['statusstr'] = self::getVoteStatusStr($endTime);
        // 添加是否有管理权限
        $vote['canmanage'] = VoteRoleUtil::canManage();
        // 添加是否有发起调查的权限
        $vote['canpublish'] = VoteRoleUtil::canPublish();
        // 添加是否有导出投票结果的权限
        $vote['canexport'] = VoteRoleUtil::canExport($voteId);


        // 添加是否有查看投票结果的权限
        $canViewVoteResult = VoteRoleUtil::canViewVoteResult($voteId, $uid);
        $vote['canviewvoteresult'] = $canViewVoteResult;
        if (!$canViewVoteResult) {
            // 没有查看投票结果的权限，隐藏投票结果数据
            $topics = self::hideVoteResult($topics);
        } else {
            // 计算百分比
            foreach ($topics as &$topic) {
                $numbers = ArrayUtil::getColumn($topic['items'], 'number');
                $voteSum = array_sum($numbers);
                foreach ($topic['items'] as &$item) {
                    if ($voteSum != 0) {
                        $item['votepercent'] = number_format(($item['number'] / $voteSum) * 100, 2);
                    } else {
                        $item['votepercent'] = 0;
                    }
                    $item['votepercent'] .= '%';
                }
            }
        }

        // 添加是否可以投票的权限
        $vote['canvote'] = VoteRoleUtil::canVote($voteId, $uid);

        // 当前用户是否已经投票
        $vote['isvote'] = VoteItemCount::model()->isVote($voteId, $uid);

        // 阅读范围信息
        $vote['readscopes'] = Department::getScopes($vote['deptid'], $vote['positionid'], $vote['roleid'], $vote['scopeuid']);

        // 投票参加情况 - 参与人数、未参与人数、总人数
        $vote['joinedusernum'] = VoteItemCount::model()->fetchJoinedUserNum($voteId);
        $vote['unjoinedusernum'] = VoteItemCount::model()->fetchUnJoinedUserNum($voteId);
        $vote['allusernum'] = $vote['joinedusernum'] + $vote['unjoinedusernum'];

        return array($vote, $topics);
    }


    /**
     * 获取调查投票列表
     *
     * @param integer $type
     * @param string $search
     * @param integer $start
     * @param integer $length
     * @return static[]
     */
    public static function fetchList($type, $search = null, $start = null, $length = null)
    {
        if (empty($type)) {
            $type = Vote::LIST_VOTE_ALL;
        }
        if (empty($start)) {
            $start = 0;
        }
        if (empty($length)) {
            $length = Vote::DEFAULT_PAGE_SIZE;
        }
        if (empty($search)) {
            $search = array();
        }
        return Vote::model()->fetchList($type, $search, $start, $length);
    }

    /**
     * 处理调查投票列表数据
     *
     * @param array $list
     * @return array
     */
    public static function handleList($list)
    {
        $handleList = array();
        $uid = Ibos::app()->user->uid;
        $moduleName = VoteModule::MODULE_NAME;

        foreach ($list as $item) {
            $endTime = $item['endtime'];
            $voteId = $item['voteid'];
            $status = self::calcVoteStatus($endTime);
            $statusStr = self::getVoteStatusStr($endTime);
            $userNum = VoteItemCount::model()->fetchVoteUserNum($voteId);
            $isSponsor = ($uid === $item['uid']);
            $currentUser = array_values(UserUtil::safeWrapUserInfo($item['uid']));
            $currentUser = $currentUser[0];

            $handleList[] = array(
                'voteid' => $voteId,
                'subject' => $item['subject'],
                'endtime' => $endTime,
                'endtimestr' => date('Y-m-d H:i', $endTime),
                'sponsor' => $currentUser['realname'],
                'sponsorid' => $item['uid'],
                'status' => $status,
                'statusstr' => $statusStr,
                'usernum' => $userNum,
                'issponsor' => $isSponsor,
                'isread' => Reader::model()->isRead($moduleName, $voteId, $uid),
            );
        }

        return $handleList;
    }

    /**
     * 导出投票数据
     *
     * @param integer $voteId 投票id
     * @return bool
     */
    public static function exportVoteData($voteId)
    {
        $vote = VoteUtil::fetchVoteByPk($voteId);

        // 如果投票无标题，则导出文件名为当前时间戳
        $filename = empty($vote['subject']) ? TIMESTAMP : $vote['subject'];
        $filename = StringUtil::subString($filename, 0, 10);
        $filename .= '.xls';

        // 获取 excel 标题数据
        $voteTopics = VoteTopic::model()->fetchTopics($voteId);
        $voteTopicCount = count($voteTopics);

        $headers = array('姓名');
        for ($i = 0; $i < $voteTopicCount; $i++) {
            $topic = $voteTopics[$i];
            $headers[] = sprintf('题目%s（%d）', ChineseNumericHelper::numeric2Chinese($i + 1), $topic['itemnum']);
        }

        // 获取 excel 内容
        $body = array();
        $voteResult = VoteFormUtil::getInstance()->listVoteResult($voteId);
        foreach ($voteResult as $uid => $userVote) {
            $realName = $userVote[0]['realname'];
            $topics = array();
            $row = array($realName);
            foreach ($userVote as $item) {
                $topics[$item['topicid']][] = $item;
            }
            foreach ($topics as $topic) {
                $allSelect = ArrayUtil::getColumn($topic, 'select');
                $allSelect = implode(',', $allSelect);
                array_push($row, $allSelect);
            }
            $body[] = $row;

        }

        PHPExcel::exportToExcel($filename, $headers, $body);
        return true;
    }


    /**
     * 隐藏投票结果
     *
     * @param $topics
     * @return mixed
     * @internal param $topicItems
     */
    private static function hideVoteResult($topics)
    {
        foreach ($topics as &$topic) {
            $topicItems = $topic['items'];

            foreach ($topicItems as &$item) {
                if (isset($item['number'])) {
                    unset($item['number']);
                }
            }

            $topic['items'] = $topicItems;
        }

        return $topics;
    }


    /**
     * 计算投票状态：进行中 / 已结束
     *
     * @param integer $endTime
     * @return int
     */
    public static function calcVoteStatus($endTime)
    {
        if ($endTime > TIMESTAMP) {
            return Vote::STATUS_RUNNING;
        }

        return Vote::STATUS_END;
    }

    /**
     * 计算投票状态，并返回对应的文本内容
     *
     * @param $endTime
     * @return string
     */
    private static function getVoteStatusStr($endTime)
    {
        $status = self::calcVoteStatus($endTime);
        if ($status == Vote::STATUS_RUNNING) {
            return Ibos::lang('vote running');
        }

        return Ibos::lang('vote end');
    }


    /**
     * 处理投票数据
     *
     * @param $postData
     * @param $moduleName
     * @param $moduleId
     * @return mixed
     * @throws \CException
     */
    private static function handleVoteData($postData, $moduleName, $moduleId)
    {
        $uid = Ibos::app()->user->uid;

        $postData['subject'] = isset($postData['subject']) ? $postData['subject'] : '';
        $postData['content'] = isset($postData['content']) ? $postData['content'] : '';

        $publishScope = $postData['publishscope'];
        if (empty($publishScope)) {
            $publishScope = Env::getRequest('publishScope');
        }

        $publishScope = StringUtil::handleSelectBoxData($publishScope, false);
        $attributes = Vote::model()->create();
        $attributes['subject'] = StringUtil::filterCleanHtml($postData['subject']);
        $attributes['content'] = StringUtil::purify($postData['content']);
        $attributes['starttime'] = TIMESTAMP;
        $attributes['endtime'] = strtotime($postData['endtime']);
        $attributes['isvisible'] = (int)$postData['isvisible'];
        $attributes['uid'] = (int)$uid;
        $attributes['deadlinetype'] = 0;
        $attributes['relatedmodule'] = $moduleName;
        $attributes['relatedid'] = (int)$moduleId;
        $attributes['deptid'] = $publishScope['deptid'];
        $attributes['positionid'] = $publishScope['positionid'];
        $attributes['roleid'] = $publishScope['roleid'];
        $attributes['scopeuid'] = $publishScope['uid'];
        $attributes['addtime'] = TIMESTAMP;
        $attributes['updatetime'] = TIMESTAMP;

        return $attributes;
    }

    /**
     * 添加一条投票（无验证）
     *
     * @param array $postData
     * @param string $moduleName
     * @param integer $moduleId
     * @return integer
     * @throws \Exception
     */
    public static function add($postData, $moduleName, $moduleId)
    {
        $voteData = self::handleVoteData($postData, $moduleName, $moduleId);
        $transaction = Ibos::app()->db->beginTransaction();

        // 开始添加投票
        try {
            $voteId = Vote::model()->addRecord($voteData);

            // 更新 vote 模块的 moduleid
            if ($moduleName == VoteModule::MODULE_NAME) {
                Vote::model()->updateModuleId($voteId, $voteId);
            }

            $topics = $postData['topics'];
            foreach ($topics as $topic) {
                // 获取投票项
                $voteItems = array_filter($topic, function ($item) {
                    // 真正的 item 类型为 array
                    if (is_array($item)) {
                        return true;
                    }
                    return false;
                });
                // 添加投票话题
                $topicType = $topic['topic_type'];
                $topicSubject = $topic['subject'];
                $topicMaxSelectNum = $topic['maxselectnum'];
                $voteTopicId = VoteTopic::model()->addRecord($voteId, $topicType, $topicSubject, $topicMaxSelectNum, count($voteItems));
                // 添加投票项
                foreach ($voteItems as $item) {
                    $itemContent = $item['content'];
                    $picPath = @$item['picpath'];
                    $topicType = $topic['topic_type'];

                    // 图片选项：忽略 picpath 和 content 均为空的选项
                    if ($topicType == Vote::CONTENT_TYPE_PIC && empty($picPath) && empty($itemContent)) {
                        continue;
                    }

                    // 文字选项：忽略 content 为空的选项
                    if ($topicType == Vote::CONTENT_TYPE_TEXT && empty($itemContent)) {
                        continue;
                    }

                    // 文字选项内容不能为空
                    if (empty($itemContent) && $topicType == Vote::CONTENT_TYPE_TEXT) {
                        throw new \InvalidArgumentException(Ibos::lang('text type topic item must have content param'));
                    }
                    // 图片选项 picpath 的值不能为空
                    if (empty($picPath) && $topicType == Vote::CONTENT_TYPE_PIC) {
                        throw new \InvalidArgumentException(Ibos::lang('pic type topic item must have picpath param'));
                    }
                    // 如果是文字选项，picpath 的值为空
                    if ($topicType == Vote::CONTENT_TYPE_TEXT) {
                        $picPath = '';
                    }
                    VoteItem::model()->addRecord($voteId, $voteTopicId, $itemContent, 0, $picPath);
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }

        return $voteId;
    }

}
