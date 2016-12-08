<?php

/**
 * 移动端日志控制器文件
 *
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 移动端日志控制器文件
 *
 * @package application.modules.mobile.controllers
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @version $Id: DiaryController.php 7972 2016-08-22 10:35:14Z gzzcs $
 */

namespace application\modules\mobile\controllers;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\Model;
use application\core\utils\StringUtil;
use application\modules\calendar\model\Calendars;
use application\modules\dashboard\model\Stamp;
use application\modules\diary\components\Diary as ICDiary;
use application\modules\diary\model\Diary;
use application\modules\diary\model\DiaryAttention;
use application\modules\diary\model\DiaryDirect;
use application\modules\diary\model\DiaryRecord;
use application\modules\diary\model\DiaryShare;
use application\modules\diary\model\DiaryStats;
use application\modules\diary\utils\Diary as DiaryUtil;
use application\modules\message\model\Comment;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;
use application\core\utils\Module as ModuleUtil;

class DiaryController extends BaseController
{

    /**
     * @var string 当前 controller 对应的模块
     * 备注：如果需要获取评论列表，则需要设置正确的模块名。
     */
    protected $_module = 'diary';

    /**
     * @var 日志后台配置
     */
    protected $dashboardconfig;

    public function init()
    {
        parent::init();
        $this->productDirectTable();
        $this->dashboardconfig = Ibos::app()->setting->get('setting/diaryconfig');
    }

    /**
     * 默认页,获取主页面各项数据统计,我的日志
     * @return void
     */
    public function actionIndex()
    {
        $pageSize = Env::getRequest('pagesize');
        $uid = Env::getRequest('uid');
        if (!$uid) {
            $uid = Ibos::app()->user->uid;
        }
        if (isset($pageSize) && !empty($pageSize)) {
            $datas = Diary::model()->fetchAllByPage("uid=" . $uid, $pageSize);
        } else {
            $datas = Diary::model()->fetchAllByPage("uid=" . $uid);
        }

        if (isset($datas["data"])) {
            $datas["data"] = $this->handleDiariesOutput($datas["data"]);
        }
        $return = array(
            "isSuccess" => true,
            "msg" => Ibos::lang('Call Success'),
        );
        $return['data'] = $datas['data'];
        if (isset($datas['pagination'])) {
            $datas = $this->handlePage($datas);
            $return['pages'] = $datas['pages'];
        }

        $return['dashboardConfig'] = $this->dashboardconfig;
        $this->ajaxReturn($return, Mobile::dataType());
    }

    /**
     * 处理日志列表输出时一些字段的格式化
     * @param $diaries
     * @return
     */
    protected function handleDiariesOutput($diaries)
    {
        $dashboardConfig = $this->dashboardconfig;
        //是否有锁定多少天前的日志
        $lockday = $dashboardConfig['lockday'] ? intval($dashboardConfig['lockday']) : 0;
        $reviewlock = $dashboardConfig['reviewlock'] ? intval($dashboardConfig['reviewlock']) : 0;
        foreach ($diaries as $k => $v) {
            // 清除标签
            $diaries[$k]['content'] = strip_tags($v['content']);

            // 阅读人数
            if (empty($v['readeruid'])) {
                $diaries[$k]['readercount'] = 0;
            } else {
                $diaries[$k]['readercount'] = count(explode(',',
                    trim($v['readeruid'], ',')));
            }

            // 图章
            if ($v['stamp'] != 0) {
                $stampData = Stamp::model()->fetchByPk($v['stamp']);
                $stampData['stamp'] = File::fileName($stampData['stamp']);
                $stampData['icon'] = File::fileName($stampData['icon']);
                $diaries[$k]['stampParams'] = $stampData;
            }

            // 是否已锁定
            $todayTime = (int)strtotime(date('Y-m-d',
                time()));  //今天的开始时间，即00:00
            $diaryTime = (int)$v['diarytime'];
            $diffDay = ($todayTime - $diaryTime) / (24 * 60 * 60);  //相差多少天
            if (($lockday > 0 && $diffDay > $lockday) || ($reviewlock > 0 && !empty($v['isreview']))) {
                $diaries[$k]['islock'] = 1;
            } else {
                $diaries[$k]['islock'] = 0;
            }

        }

        return $diaries;
    }


    /**
     * 处理分页
     * @param $datas
     * @return mixed
     */
    protected function handlePage($datas)
    {
        $datas['pages'] = array(
            'pageCount' => $datas['pagination']->getPageCount(),
            'page' => $datas['pagination']->getCurrentPage(),
            'pageSize' => $datas['pagination']->getPageSize()
        );

        return $datas;
    }
    /*public function actionReview() {
        $op = Env::getRequest( 'op' );
        $option = empty( $op ) ? 'default' : $op;
        $routes = array( 'default', 'show', 'showdiary', 'getsubordinates', 'personal', 'getStampIcon' );
        if ( !in_array( $option, $routes ) ) {
            $this->error( Ibos::lang( 'Can not find the path' ), $this->createUrl( 'default/index' ) );
        }
        $date = 'today';
        if ( array_key_exists( 'date', $_GET ) ) {
            $date = $_GET['date'];
        }
        if ( $date == 'today' ) {
            $time = strtotime( date( 'Y-m-d' ) );
            $date = date( 'Y-m-d' );
        } else if ( $date == 'yesterday' ) {
            $time = strtotime( date( 'Y-m-d' ) ) - 24 * 60 * 60;
            $date = date( 'Y-m-d', $time );
        } else {
            $time = strtotime( $date );
        }

        $uid = Ibos::app()->user->uid;
        $getSubUidArr = Env::getRequest( 'subUidArr' );  //是否有传递下属人员过来，用于前一天、后一天
        $user = Env::getRequest( 'user' );  //是否是点击某个部门
        if ( !empty( $getSubUidArr ) ) {
            $subUidArr = $getSubUidArr;
        } elseif ( !empty( $user ) ) {
            $subUidArr = array();
            foreach ( $user as $v ) {
                $subUidArr[] = $v['uid'];
            }
        } else {
            $subUidArr = User::model()->fetchSubUidByUid( $uid );
        }
        $params = array();
        if ( count( $subUidArr ) > 0 ) {
            $uids = implode( ',', $subUidArr );
            $condition = "uid IN($uids)" . " AND diarytime=$time";
            $paginationData = Diary::model()->fetchAllByPage( $condition, 100 );

            //得到该天没有工作日志的uid --取得该天有记录的uid，总下属uid-有记录的uid
            $recordUidArr = $noRecordUidArr = $noRecordUserList = array();
            foreach ( $paginationData['data'] as $diary ) {
                $recordUidArr[] = $diary['uid'];
            }
            if ( count( $recordUidArr ) > 0 ) {
                foreach ( $subUidArr as $subUid ) {
                    if ( !in_array( $subUid, $recordUidArr ) ) {
                        $noRecordUidArr[] = $subUid;
                    }
                }
            } else {
                $noRecordUidArr = $subUidArr;
            }
            if ( count( $noRecordUidArr ) > 0 ) {
                $noRecordUserList = User::model()->fetchAllByPk( $noRecordUidArr );
            }
            $params = array(
                'pagination' => $paginationData['pagination'],
                'pages' => array(
                    'pageCount' => $paginationData['pagination']->getPageCount(),
                    'page' => $paginationData['pagination']->getCurrentPage(),
                    'pageSize' => $paginationData['pagination']->getPageSize()
                ),
                'data' => ICDiary::processReviewListData( $uid, $paginationData['data'] ),
                'noRecordUserList' => $noRecordUserList
            );
        } else {
            $params = array(
                'pagination' => new CPagination( 0 ),
                'pages' => array(),
                'data' => array(),
                'noRecordUserList' => array()
            );
        }
        // 与个人日志列表统一数据格式
        $params['datas'] = $params['data'];

        $params['dateWeekDay'] = DiaryUtil::getDateAndWeekDay( $date );
        $params['dashboardConfig'] = Ibos::app()->setting->get( 'setting/diaryconfig' );
        $params['subUidArr'] = $subUidArr;
        //上一天和下一天
        $params['prevAndNextDate'] = array(
            'prev' => date( 'Y-m-d', (strtotime( $date ) - 24 * 60 * 60 ) ),
            'next' => date( 'Y-m-d', (strtotime( $date ) + 24 * 60 * 60 ) ),
            'prevTime' => strtotime( $date ) - 24 * 60 * 60,
            'nextTime' => strtotime( $date ) + 24 * 60 * 60
        );
        $this->ajaxReturn( $params, Mobile::dataType() );
    }*/

    /**
     * 列表页显示
     * @return void
     */
    /* public function actionAttention() {
     	//取得shareuid字段中包含作者的数据
     	$date = 'yesterday';
     	if ( array_key_exists( 'date', $_GET ) ) {
     		$date = $_GET['date'];
     	}
     	if ( $date == 'today' ) {
     		$time = strtotime( date( 'Y-m-d' ) );
     		$date = date( 'Y-m-d' );
     	} else if ( $date == 'yesterday' ) {
     		$time = strtotime( date( 'Y-m-d' ) ) - 24 * 60 * 60;
     		$date = date( 'Y-m-d', $time );
     	} else {
     		$time = strtotime( $date );
     		$date = date( 'Y-m-d', $time );
     	}

     	$uid = Ibos::app()->user->uid;
     	//关注了哪些人
     	$attentions = DiaryAttention::model()->fetchAllByAttributes( array( 'uid' => $uid ) );
     	$auidArr = Convert::getSubByKey( $attentions, 'auid' );
     	$hanAuidArr = $this->handleAuid( $uid, $auidArr );
     	$subUidStr = implode( ',', $hanAuidArr['subUid'] );
     	$auidStr = implode( ',', $hanAuidArr['aUid'] );
     	// 下属日志的条件和非下属日志条件
     	$condition = "(FIND_IN_SET(uid, '{$subUidStr}') OR (FIND_IN_SET('{$uid}', shareuid) AND FIND_IN_SET(uid, '{$auidStr}') ) ) AND diarytime=$time";
     	$paginationData = Diary::model()->fetchAllByPage( $condition, 100 );
     	$params = array(
     		'dateWeekDay' => DiaryUtil::getDateAndWeekDay( date( 'Y-m-d', strtotime( $date ) ) ),
     		'pagination' => $paginationData['pagination'],
     		'pages' => array(
     			'pageCount' => $paginationData['pagination']->getPageCount(),
     			'page' => $paginationData['pagination']->getCurrentPage(),
     			'pageSize' => $paginationData['pagination']->getPageSize()
     		),
     		'data' => ICDiary::processShareListData( $uid, $paginationData['data'] ),
     		'shareCommentSwitch' => 0,
     		'attentionSwitch' => 1
     	);

     	// 与个人日志列表统一数据格式
     	$params['datas'] = $params['data'];

     	//上一天和下一天
     	$params['prevAndNextDate'] = array(
     		'prev' => date( 'Y-m-d', (strtotime( $date ) - 24 * 60 * 60 ) ),
     		'next' => date( 'Y-m-d', (strtotime( $date ) + 24 * 60 * 60 ) ),
     		'prevTime' => strtotime( $date ) - 24 * 60 * 60,
     		'nextTime' => strtotime( $date ) + 24 * 60 * 60,
     	);
     	$this->ajaxReturn( $params, Mobile::dataType() );
     }
    */
    /**
     * 处理关注的uid中，下属uid和非下属uid分离开
     * @param integer $uid 登陆用户uid
     * @param mix $attentionUids 关注的uid
     * @return array
     */
    private function handleAuid($uid, $attentionUids)
    {
        $aUids = is_array($attentionUids) ? $attentionUids : implode(',',
            $attentionUids);
        $ret['subUid'] = array();
        $ret['aUid'] = array();
        if (!empty($aUids)) {
            foreach ($aUids as $aUid) {
                if (UserUtil::checkIsSub($uid, $aUid)) {
                    $ret['subUid'][] = $aUid;
                } else {
                    $ret['aUid'][] = $aUid;
                }
            }
        }

        return $ret;
    }

    /**
     *
     */
    public function actionCategory()
    {

        $this->ajaxReturn(array(), Mobile::dataType());
    }

    /**
     * 显示工作日志
     * @return array
     */
    public function actionShow()
    {
        $diaryid = Env::getRequest('id');
        $diaryDate = Env::getRequest('diarydate');
        if (empty($diaryid) && empty($diaryDate)) {
            $message = array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Lack of params')
            );
            $this->ajaxReturn($message, Mobile::dataType());
        }
        $uid = Ibos::app()->user->uid;
        if (!empty($diaryid)) {
            $diary = Diary::model()->fetchByPk($diaryid);
        } else {
            $diary = Diary::model()->fetch('diarytime=:diarytime AND uid=:uid',
                array(
                    ':diarytime' => strtotime($diaryDate),
                    ':uid' => $uid
                ));
        }
        if (empty($diary)) {
            $this->ajaxReturn(array(), Mobile::dataType());
        }
        // 权限判断
        if (!ICDiary::checkScope($uid, $diary)) {
            $this->error(Ibos::lang('You do not have permission to view the log'),
                $this->createUrl('index'));
        }
        $dashboardconfig = $this->dashboardconfig;
        //增加阅读记录
        Diary::model()->addReaderuidByPK($diary, $uid);
        //取得原计划和计划外内容,下一次计划内容
        $data = Diary::model()->fetchDiaryRecord($diary);
        $params = array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Call Success'),
            'diary' => ICDiary::processDefaultShowData($diary),
            'prevAndNextPK' => Diary::model()->fetchPrevAndNextPKByPK($diary['diaryid']),
            'data' => $data,
        );
        // 判断是否开了自动评阅
        if (!empty($dashboardconfig['stampenable'])) {
            if (!empty($dashboardconfig['autoreview'])) {
                $this->changeIsreview($diary,
                    $dashboardconfig['autoreviewstamp']);
            }
            $params['allStamps'] = Stamp::model()->fetchAll();
        }
        //附件
        if (!empty($diary['attachmentid'])) {
            $params['attach'] = Attach::getAttach($diary['attachmentid'], true,
                true, false, false, true);
            $params['count'] = 0;
        }
        //图章
        $stampBasePath = File::fileName(Stamp::STAMP_PATH);
        if (!empty($diary['stamp'])) {
            $stamp = Stamp::model()->fetchStampById($diary['stamp']);
            $params['stampUrl'] = $stamp;
        }
        $params['stampBasePath'] = $stampBasePath;

        $defaultOrder = "cid desc";  // 默认使用：cid asc
        //评论,二级评论
        $map = Comment::model()->getMapForGetCommentList($diaryid);
        $commentList = Comment::model()->getCommentList($map, $defaultOrder,
            -1);

        $params['dashboardConfig'] = $dashboardconfig;
        $params['list'] = $commentList;
        $params['isSup'] = UserUtil::checkIsSub($uid, $diary['uid']);
        $params['isShare'] = Diary::model()->checkUidIsShared($uid,
            $diary['diaryid']);
        $this->ajaxReturn($params, Mobile::dataType());
    }

    /**
     * 以前的评论列表
     * @param $diary
     * @return mixed
     */
    private function getCommentList($diary)
    {
        $limit = Env::getRequest('limit', 'P', 5);
        $offset = Env::getRequest('offset', 'P', 0);
        $arr = array(
            'module' => 'diary',
            'table' => 'diary',
            'attributes' => array(
                'rowid' => $diary['diaryid'],
                'moduleuid' => Ibos::app()->user->uid,
                'limit' => $limit,
                'offset' => $offset,
            ),
        );
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this,
            'application\modules\diary\widgets\DiaryComment', $arr);
        $list = $widget->getCommentList();

        return $list;
    }

    /**
     * 获取日志原计划数据
     * @return type
     */

    public function actionAdd()
    {
        // 以前的代码残余，jsonp与callback
        // $dataType = 'JSON';
        // $callback = Env::getRequest( 'callback' );
        // if ( isset( $callback ) ) {
        // 	$dataType = Mobile::dataType();
        // }
        $todayDate = date('Y-m-d');
        if (array_key_exists('diaryDate', $_GET)) {
            $todayDate = $_GET['diaryDate'];
            if (strtotime($todayDate) > strtotime(date('Y-m-d'))) {
                $message = array(
                    'isSuccess' => false,
                    'msg' => Ibos::lang('Future not allow')
                );
                $this->ajaxReturn($message, Mobile::dataType());
            }
        }
        $todayTime = strtotime($todayDate);
        $uid = Ibos::app()->user->uid;
        if (Diary::model()->checkDiaryisAdd($todayTime, $uid)) {
            $message = array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Has already submitted')
            );
            $this->ajaxReturn($message, Mobile::dataType());
        }
        //取得今日的工作计划
        $diaryRecordList = DiaryRecord::model()->fetchAllByPlantime($todayTime);
        $originalPlanList = $outsidePlanList = array();
        foreach ($diaryRecordList as $diaryRecord) {
            if ($diaryRecord['planflag'] == 1) {
                $originalPlanList[] = $diaryRecord;
            } else {
                $outsidePlanList[] = $diaryRecord;
            }
        }
        $dashboardConfig = Ibos::app()->setting->get('setting/diaryconfig');
        // 检测是否已安装日程模块，用于添加日志时“来自日程”的计划功能
        $isInstallCalendar = ModuleUtil::getIsInstall('calendar');
        $workStart = $this->getWorkStart($isInstallCalendar);
        $params = array(
            "isSuccess" => true,
            "msg" => Ibos::lang('Call Success'),
            'diary' => array(
                // 'diaryid' => 0,
                'uid' => $uid,
                'diarytime' => DiaryUtil::getDateAndWeekDay($todayDate),
                'nextDiarytime' => DiaryUtil::getDateAndWeekDay(date("Y-m-d",
                    strtotime("+1 day", $todayTime))),
                // 'content' => ''
            ),
            'data' => array(
                'originalPlanList' => $originalPlanList,
                'outsidePlanList' => $outsidePlanList,
            ),
            'dashboardConfig' => $dashboardConfig,
            'uploadConfig' => Attach::getUploadConfig(),
            'isInstallCalendar' => $isInstallCalendar,
            'workStart' => $workStart
        );
        //取得默认共享人员
        if ($dashboardConfig['sharepersonnel']) {
            $shareData = DiaryShare::model()->fetchByAttributes(array('uid' => $uid));
            $params['deftoid'] = isset($shareData['deftoid']) ? $shareData['deftoid'] : '';
        }
        $this->ajaxReturn($params, Mobile::dataType());
    }

    /**
     * 添加工作日志
     * @return void
     */
    function actionSave()
    {
        //判断是否是post类型请求
        if (!Ibos::app()->request->isPostRequest) {
            //不是post请求跳到工作日志页面
            $this->error(Ibos::lang('Wrong request'),
                $this->createUrl('index'));
        }
        $uid = Ibos::app()->user->uid;
        $originalPlan = $planOutside = '';
        if (array_key_exists('originalPlan', $_POST)) {
            $originalPlan = $_POST['originalPlan'];
        }
        if (array_key_exists('planOutside', $_POST)) {
            $planOutside = array_filter($_POST['planOutside'],
                create_function('$v', 'return !empty($v["content"]);'));
        }
        //如果原计划存在，修改原计划完成情况
        if (!empty($originalPlan)) {
            foreach ($originalPlan as $key => $value) {
                DiaryRecord::model()->modify($key, array('schedule' => $value));
            }
        }
        //保存最新计划
        $shareUidArr = isset($_POST['shareuid']) ? StringUtil::getId($_POST['shareuid']) : array();
        $diary = array(
            'uid' => $uid,
            'diarytime' => strtotime($_POST['todayDate']),
            'nextdiarytime' => strtotime($_POST['plantime']),
            'addtime' => TIMESTAMP,
            'content' => $_POST['diaryContent'],
            'shareuid' => implode(',', $shareUidArr),
            'readeruid' => '',
            'remark' => '',
            'attention' => ''
        );
        // 上传文件
        if (!empty($_POST['attachmentid'])) {
            Attach::updateAttach($_POST['attachmentid']);
            $diary['attachmentid'] = $_POST['attachmentid'];
        }
        $diaryId = Diary::model()->add($diary, true);
        //如果存在计划外，增加到该天的计划记录中
        if (!empty($planOutside)) {
            DiaryRecord::model()->addRecord($planOutside, $diaryId,
                strtotime($_POST['todayDate']), $uid, 'outside');
        }
        $plan = array_filter($_POST['plan'],
            create_function('$v', 'return !empty($v["content"]);'));
        DiaryRecord::model()->addRecord($plan, $diaryId,
            strtotime($_POST['plantime']), $uid, 'new');
        //更新积分
        UserUtil::updateCreditByAction('adddiary', $uid);
        $message = array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Add success')
        );
        $this->ajaxReturn($message, Mobile::dataType());
    }


    /**
     * 修改工作日志
     * @return void
     */
    public function actionUpdate()
    {
        //判断是否是post类型请求
        if (!Ibos::app()->request->isPostRequest) {
            //不是post请求跳到工作日志页面
            $this->error(Ibos::lang('Wrong request'),
                $this->createUrl('index'));
        }
        $uid = Ibos::app()->user->uid;
        $post = $_POST;
        $filterData = array('diaryContent', 'todayDate', 'plantime', 'shareuid');
        foreach ($filterData as $data) {
            if (isset($post[$data]) && !empty($post[$data])) {
                $post[$data] = StringUtil::filterCleanHtml($post[$data]);
            }
        }
        $shareUidArr = isset($post['shareuid']) ? StringUtil::getId($post['shareuid']) : array();
        $diaryId = (int)Env::getRequest('id');
        $diary = Diary::model()->fetchByPk($diaryId);
        // 权限判断
        if (!ICDiary::checkReadScope($uid, $diary)) {
            $this->error(Ibos::lang('You do not have permission to edit the log'),
                $this->createUrl('index'));
        }
        $diary = array(
            'uid' => $uid,
            'diarytime' => strtotime($post['todayDate']),
            'nextdiarytime' => strtotime($post['plantime']),
            'addtime' => TIMESTAMP,
            'content' => $post['diaryContent'],
            'shareuid' => implode(',', $shareUidArr),
            'readeruid' => '',
            'remark' => '',
            'attention' => ''
        );
        $isDiary = Diary::model()->modify($diaryId, $diary);
        //更新附件
        $attachmentid = trim($post['attachmentid'], ',');
        Attach::updateAttach($attachmentid);
        Diary::model()->modify($diaryId,
            array('attachmentid' => $attachmentid));

        $originalPlan = $planOutside = '';
        if (array_key_exists('originalPlan', $post)) {
            $originalPlan = $post['originalPlan'];
        }
        if (array_key_exists('planOutside', $post)) {
            $planOutside = array_filter($post['planOutside'],
                create_function('$v', 'return !empty($v["content"]);'));
        }
        if (!empty($originalPlan)) {
            foreach ($originalPlan as $key => $value) {
                DiaryRecord::model()->modify($key, array('schedule' => $value));
            }
        }
        DiaryRecord::model()->deleteAll("diaryid = {$diaryId}");
        if (!empty($planOutside)) {
            DiaryRecord::model()->addRecord($planOutside, $diaryId,
                strtotime($post['todayDate']), $uid, 'outside');
        }
        $plan = array_filter($post['plan'],
            create_function('$v', 'return !empty($v["content"]);'));
        if (!empty($plan)) {
            $isDiaryRecord = DiaryRecord::model()->addRecord($plan, $diaryId,
                strtotime($post['plantime']), $uid, 'new');
        }
        if ($isDiary && $isDiaryRecord) {
            $message = array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Edit success')
            );
        } else {
            $message = array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Edit fail')
            );
        }
        $this->ajaxReturn($message, Mobile::dataType());
    }

    /**
     * 删除工作日志,，目前只能一篇一篇地删，没有批量删除
     * @return void
     */
    public function actionDel()
    {
        $uid = Ibos::app()->user->uid;
        $diaryId = Env::getRequest('diaryids');
        $diary = Diary::model()->fetchByPk($diaryId);
        $this->checkTheDiary($uid, $diary);
        $diary = Diary::model()->deleteAll("diaryid = {$diaryId}");
        $diaryRecord = DiaryRecord::model()->deleteAll("diaryid = {$diaryId}");
        //删除附件
        $pk = array($diaryId);
        $aids = Diary::model()->fetchAllAidByPks($pk);
        if ($aids) {
            Attach::delAttach($aids);
        }
        if ($diary > 0 && $diaryRecord > 0) {
            $message = array(
                'isSuccess' => true,
                'msg' => Ibos::lang('Del success')
            );
        } else {
            $message = array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Del fail')
            );
        }
        $this->ajaxReturn($message, Mobile::dataType());
    }

    /**
     * 获取下属的日志列表
     */
    public function actionPersonal()
    {
        $uid = Ibos::app()->user->uid;
        $getUid = intval(Env::getRequest('uid'));
        // 上司的日志列表不能出现也不能看
        if (false === UserUtil::checkIsSub($uid, $getUid)) {
            $this->error(Ibos::lang('You do not have permission to view the log'),
                $this->createUrl('index'));
        }
        $condition = "uid = '{$getUid}'";
        $pageSize = Env::getRequest('pagesize');
        if (isset($pageSize) && !empty($pageSize)) {
            $diary = Diary::model()->fetchAllByPage($condition, $pageSize);
        } else {
            $diary = Diary::model()->fetchAllByPage($condition);
        }
        // 是否关注
        $attention = DiaryAttention::model()->fetchAllByAttributes(array(
            'uid' => $uid,
            'auid' => $getUid
        ));
        $isattention = empty($attention) ? 0 : 1;

        if (isset($diary)) {
            $return['data'] = $this->handleDiariesOutput($diary['data']);
        }
        if (isset($diary['pagination'])) {
            $datas = $this->handlePage($diary);
            $return['pages'] = $datas['pages'];
        }

        //图章标签
        $params = array(
            "isSuccess" => true,
            "msg" => Ibos::lang('Call Success'),
            'diary' => $return['data'],
            'pages' => $return['pages'],
            'isattention' => $isattention,
            'dashboardConfig' => Ibos::app()->setting->get('setting/diaryconfig')
        );
        $this->ajaxReturn($params, Mobile::dataType());
    }

    /**
     * 获取所有下属的日志列表
     */
    public function actionAllSubs()
    {
        $pageSize = Env::getRequest('pagesize');
        $diary = $attentionList = array();
        $uid = Ibos::app()->user->uid;
        $subUidArr = User::model()->fetchSubUidByUid($uid);
        // 如果没有下属
        if (empty($subUidArr)) {
            $params = array(
                "isSuccess" => true,
                "msg" => Ibos::lang('Call Success'),
                "data" => ''
            );
            $this->ajaxReturn($params, Mobile::dataType());
        }

        // 是否设置只看直属下属，没有就默认设置了
        $oldDirect = DiaryDirect::model()->fetchByAttributes(array('uid' => $uid));
        if (empty($oldDirect)) {
            $data = array(
                'uid' => $uid,
                'direct' => 1,
            );
            DiaryDirect::model()->add($data);
            $direct = '1';
        } else {
            $direct = $oldDirect['direct'];
        }
        // 获取全部下属
        if ('0' == $direct) {
            foreach ($subUidArr as $subUid) {
                $_subUidArr = User::model()->fetchSubUidByUid($subUid);
                $subUidArr = array_merge($subUidArr, $_subUidArr);
            }
        }

        if (count($subUidArr) > 0) {
            // 是否关注
            foreach ($subUidArr as $sub){
                $attention = DiaryAttention::model()->fetchAllByAttributes(array(
                    'uid' => $uid,
                    'auid' => $sub
                ));
                if (!empty($attention)) {
                    $attentionList[] = $sub;
                }
            }
            
            $subUids = implode(',', $subUidArr);
            $condition = "uid IN($subUids)";
            if (isset($pageSize) && !empty($pageSize)) {
                $diary = Diary::model()->fetchAllByPage($condition, $pageSize);
            } else {
                $diary = Diary::model()->fetchAllByPage($condition);
            }

        }

        if (isset($diary)) {
            $return['data'] = $this->handleDiariesOutput($diary['data']);
        }
        if (isset($diary['pagination'])) {
            $datas = $this->handlePage($diary);
            $return['pages'] = $datas['pages'];
        }
        $params = array(
            "isSuccess" => true,
            "msg" => Ibos::lang('Call Success'),
            'diary' => $return['data'],
            'pages' => $return['pages'],
            'attentionList' => $attentionList
        );
        $this->ajaxReturn($params, Mobile::dataType());
    }

    /**
     * 获取共享和关注的日志列表
     */
    public function actionOther()
    {
        $pageSize = Env::getRequest('pagesize');
        $params = $return = array();
        $uid = Ibos::app()->user->uid;
        $dashboardConfig = $this->dashboardconfig;
        //关注了哪些人
        $attentions = DiaryAttention::model()->fetchAllByAttributes(array('uid' => $uid));
        $auidArr = Convert::getSubByKey($attentions, 'auid');
        $auidStr = implode(',', $auidArr);
        if (empty($dashboardConfig['sharepersonnel']) && empty($dashboardConfig['attention'])) {
            $params = array(
                "isSuccess" => true,
                "msg" => Ibos::lang('Call Success'),
                'data' => '',
            );
            $this->ajaxReturn($params, Mobile::dataType());
        } else if (empty($dashboardConfig['sharepersonnel']) && '1' == $dashboardConfig['attention']) {
            $condition = "FIND_IN_SET(uid, '{$auidStr}')";
        } else if ('1' == $dashboardConfig['sharepersonnel'] && empty($dashboardConfig['attention'])) {
            $condition = "FIND_IN_SET('{$uid}', shareuid)";
        } else {
            $condition = "(FIND_IN_SET(uid, '{$auidStr}') OR (FIND_IN_SET('{$uid}', shareuid)))";
        }

        if (isset($pageSize) && !empty($pageSize)) {
            $datas = Diary::model()->fetchAllByPage($condition, $pageSize);
        } else {
            $datas = Diary::model()->fetchAllByPage($condition);
        }

        if (isset($datas['data'])) {
            $return['data'] = $this->handleDiariesOutput($datas['data']);
        }
        if (isset($datas['pagination'])) {
            $datas = $this->handlePage($datas);
            $return['pages'] = $datas['pages'];
        }
        $params = array(
            "isSuccess" => true,
            "msg" => Ibos::lang('Call Success'),
            'data' => $return['data'],
            'pages' => $return['pages'],
        );
        $this->ajaxReturn($params, Mobile::dataType());
    }

    /**
     * 设置关注工作日志
     * @return void
     */
    public function actionAttention()
    {
        $auid = Env::getRequest('auid');
        $uid = Ibos::app()->user->uid;
        DiaryAttention::model()->addAttention($uid, $auid);
        $message = array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Attention succeed')
        );
        $this->ajaxReturn($message, Mobile::dataType());
    }

    /**
     * 取消关注工作日志
     * @return void
     */
    public function actionUnattention()
    {
        $auid = Env::getRequest('auid');
        $uid = Ibos::app()->user->uid;
        DiaryAttention::model()->removeAttention($uid, $auid);
        $message = array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Unattention succeed')
        );
        $this->ajaxReturn($message, Mobile::dataType());
    }

    /**
     * 获取个人设置
     */
    public function actionSetting()
    {
        $params = array();
        $uid = Ibos::app()->user->uid;
        //关注了哪些人
        $attentions = DiaryAttention::model()->fetchAllByAttributes(array('uid' => $uid));
        $auidArr = Convert::getSubByKey($attentions, 'auid');
        $auidStr = implode(',', $auidArr);
        // 是否设置只看直属下属，没有就默认设置了
        $oldDirect = DiaryDirect::model()->fetchByAttributes(array('uid' => $uid));
        if (empty($oldDirect)) {
            $data = array(
                'uid' => $uid,
                'direct' => 1,
            );
            DiaryDirect::model()->add($data);
            $direct = '1';
        } else {
            $direct = $oldDirect['direct'];
        }
        $shareData = DiaryShare::model()->fetchByAttributes(array('uid' => $uid));

        // $params['defaultShareList'] = $shareData['shareInfo'];
        // 共享给哪些人
        $params = array(
            "isSuccess" => true,
            "msg" => Ibos::lang('Call Success'),
            'data' => array(
                'attentionList' => $auidStr,
                'direct' => $direct,
                'defaultShareList' => isset($shareData['deftoid']) ? $shareData['deftoid'] : '',
            ),
            'dashboardConfig' => Ibos::app()->setting->get('setting/diaryconfig')
        );
        $this->ajaxReturn($params, Mobile::dataType());
    }


    //  $_POST= array(
    // 	"attentionList" => "4,5",
    // 	"direct" => "0",
    // 	"defaultShareList" => "2,6"
    // );
    /**
     * 保存个人设置
     */
    public function actionSaveSetting()
    {
        // 先检验传参
        $checkParams = array(
            "attentionList",
            "defaultShareList",
        );
        foreach ($checkParams as $param) {
            $paramValue = Env::getRequest($param);
            if (!isset($paramValue)) {
                $msg = Ibos::lang("Lack of params") . ",请检查是否提供 {$param} 参数。";

                return $this->ajaxReturn(array(
                    "isSuccess" => false,
                    "msg" => $msg
                ), Mobile::dataType());
            }
            // 在这里讲设置 $type、$rowid 的值
            $$param = $paramValue;
        }

        $direct = Env::getRequest('direct');
        // direct要传0或1
        if (!isset($direct)) {
            $msg = Ibos::lang("Lack of params") . ",请检查是否提供 direct 参数。";

            return $this->ajaxReturn(array(
                "isSuccess" => false,
                "msg" => $msg
            ), Mobile::dataType());
        }
        $direct = intval($direct);
        if (0 !== $direct && 1 !== $direct) {
            $msg = Ibos::lang("Error param") . ",请检查direct参数";

            return $this->ajaxReturn(array(
                "isSuccess" => false,
                "msg" => $msg
            ), Mobile::dataType());
        }

        $uid = Ibos::app()->user->uid;
        $oldDirect = DiaryDirect::model()->fetchByAttributes(array('uid' => $uid));
        // 添加或者更新设置字段
        if (empty($oldDirect)) {
            $data = array(
                'uid' => $uid,
                'direct' => $direct,
            );
            DiaryDirect::model()->add($data);
        } else {
            DiaryDirect::model()->modify($oldDirect['id'],
                array('direct' => $direct));
        }

        // 处理默认分享人员列表
        // 如果传过来的默认分享为空，则删除之前的数据
        if (empty($defaultShareList)) {
            DiaryShare::model()->delDeftoidByUid($uid);
        } else {
            DiaryShare::model()->addOrUpdateDeftoidByUid($uid,
                StringUtil::getId($defaultShareList));
        }

        // 设置关注人员
        DiaryAttention::model()->delAttentionByUid($uid);
        if (!empty($attentionList)) {
            DiaryAttention::model()->addAttentionByUid($uid,
                StringUtil::getId($attentionList));
        }

        $message = array(
            'isSuccess' => true,
            'msg' => Ibos::lang('Set succeed')
        );
        $this->ajaxReturn($message, Mobile::dataType());
    }

    /**
     * 编辑日志页
     */
    public function actionEdit()
    {
        $uid = Ibos::app()->user->uid;
        $diaryid = (int)Env::getRequest('diaryid');
        $diary = Diary::model()->fetchByPk($diaryid);
        $this->checkTheDiary($uid, $diary);
        //取得原计划和计划外内容,下一次计划内容
        $data = Diary::model()->fetchDiaryRecord($diary);
        $dashboardConfig = $this->dashboardconfig;
        // 检测是否已安装日程模块，用于添加日志时“来自日程”的计划功能
        $isInstallCalendar = ModuleUtil::getIsInstall('calendar');
        $workStart = $this->getWorkStart($isInstallCalendar);
        $params = array(
            "isSuccess" => true,
            "msg" => Ibos::lang('Call Success'),
            'diary' => ICDiary::processDefaultShowData($diary),
            'prevAndNextPK' => Diary::model()->fetchPrevAndNextPKByPK($diaryid),
            'data' => $data,
            'dashboardConfig' => $dashboardConfig,
            'uploadConfig' => Attach::getUploadConfig(),
            'isInstallCalendar' => $isInstallCalendar,
            'workStart' => $workStart
        );
        //取得附件
        if (!empty($diary['attachmentid'])) {
            $params['attach'] = Attach::getAttach($diary['attachmentid']);
        }
        //取得默认共享人员
        if ($dashboardConfig['sharepersonnel']) {
            $shareData = DiaryShare::model()->fetchByAttributes(array('uid' => $uid));
            $params['deftoid'] = isset($shareData['deftoid']) ? $shareData['deftoid'] : '';
        }
        $this->ajaxReturn($params, Mobile::dataType());
    }

    /**
     * 从日程中读取数据作为这天的原计划
     */
    public function actionPlanFromSchedule()
    {
        $params = array(
            "isSuccess" => true,
            "msg" => Ibos::lang('Call Success')
        );
        $uid = Ibos::app()->user->uid;
        $todayDate = $_GET['todayDate'];
        // $todayDate = '2016-07-12';
        $st = intval(strtotime($todayDate));
        $et = $st + 24 * 60 * 60 - 1;
        $calendars = Calendars::model()->listCalendarByRange($st, $et, $uid);
        $plans = $calendars['events'];
        foreach ($plans as $k => $v) {  //处理完成度输出数据
            $plans[$k]['schedule'] = $v['status'] ? self::COMPLETE_FALG : 0;
            if ($v['isfromdiary']) {
                unset($plans[$k]);
            }
        }
        $params['data'] = array_values($plans);
        $this->ajaxReturn($params, Mobile::dataType());
    }

    /**
     * 根据是否安装了日程模块,取出一天的开始工作时间
     * @param type $isInstallCalendar
     * @return mixed
     */
    protected function getWorkStart($isInstallCalendar)
    {
        $workTime = array('start' => 6, 'end' => 20);

        if ($isInstallCalendar) {  // 若已安装日程，取出配置的开始工作时间
            $workingTime = Ibos::app()->setting->get('setting/calendarworkingtime');
            list($start, $end) = explode(',', $workingTime);
            if ($start < 0) {
                $start = 0;
            }
            if ($end > 24) {
                $end = 24;
            }
            $workTime['start'] = intval($start);
            $workTime['end'] = intval($end);
        }

        return $workTime;
    }

    /* $_POST = array(
     	'type' => 'comment',
     	'rowid' => '26',
     	'moduleuid' => '6',
     	'touid' => '7',
     	'content' => '寒战',
     	'url' => '/?r=diary/default/show&diaryid=26',
     	'detail' => '评论{realname}的日志<a href="/?r=diary/default/show&diaryid=26"> “咏春”</a>'
     	);*/
    /**
     * 评论日志
     */
    public function actionAddComment()
    {
        // 参数处理
        $checkParams = array(
            "type",
            "rowid",
        );
        foreach ($checkParams as $param) {
            $paramValue = Env::getRequest($param);
            if (empty($paramValue)) {
                $msg = Ibos::lang("Lack of params") . "请检查是否提供 {$param} 参数。";

                return $this->ajaxReturn(array(
                    "isSuccess" => false,
                    "msg" => $msg
                ), Mobile::dataType());
            }
            // 在这里讲设置 $type、$rowid 的值
            $$param = $paramValue;
        }

        // $type 参数只支持：comment（评论）和 reply（回复）
        if (!in_array($type, array(
            "comment",
            "reply"
        ))
        ) {
            $msg = Ibos::lang("Error param") . "请检查 \$type 参数";

            return $this->ajaxReturn(array(
                "isSuccess" => false,
                "msg" => $msg
            ), Mobile::dataType());
        }
        // 如果评论类型为：comment
        if ("comment" === $type) {
            $pk = Diary::model()->getTableSchema()->primaryKey;
            $sourceInfo = Diary::model()->fetch(array('condition' => "`{$pk}` = {$rowid}"));
            $touid = (int)$sourceInfo['uid'];
            $_POST["module"] = "diary";
            $_POST["table"] = "diary";
            $_POST['touid'] = $touid;
        } elseif ("reply" === $type) {
            $_POST["module"] = "message";
            $_POST["table"] = "comment";
        }
        $widget = Ibos::app()->getWidgetFactory()->createWidget($this,
            'application\modules\diary\widgets\DiaryComment');

        return $widget->addComment();
    }


    /**
     * 路由映射
     * @return array
     */
    public function routeMap()
    {
        return array(
            "mobile/diary/index" => "diary/default/index",
            "mobile/diary/allsubs" => "diary/review/index",
            "mobile/diary/other" => array(
                'diary/share/index',
                'diary/attention/index'
            ),
//            "mobile/diary/show" => array('diary/review/show', 'diary/attention/show', 'diary/share/show'),
            "mobile/diary/personal" => 'diary/review/personal',
            "mobile/diary/add" => "diary/default/add",
            "mobile/diary/planfromschedule" => "diary/default/add",
            "mobile/diary/save" => "diary/default/add",
            "mobile/diary/edit" => "diary/default/edit",
            "mobile/diary/update" => "diary/default/edit",
            "mobile/diary/del" => "diary/default/del",
            "mobie/diary/addcomment" => "diary/comment/addcomment",
            "mobile/diary/setting" => "diary/default/index",
            "mobile/diary/attention" => "diary/attention/edit",
            "mobile/diary/unattention" => "diary/attention/edit",
        );
    }


    /**
     * 验证权限和日志是否锁定等
     * @param $uid
     * @param $diary
     * @return bool
     */
    private function checkTheDiary($uid, $diary)
    {
        // 日志是否存在
        if (empty($diary)) {
            $this->error(Ibos::lang('Diary empty'), $this->createUrl('index'));
        }
        // 权限判断
        if (!ICDiary::checkReadScope($uid, $diary)) {
            $this->error(Ibos::lang('You do not have permission to edit the log'),
                $this->createUrl('index'));
        }
        //日志是否被锁定，锁定则不能修改
        $dashboardConfig = $this->dashboardconfig;
        if (!empty($dashboardConfig['lockday'])) {
            $isLock = (time() - $diary['addtime']) > $dashboardConfig['lockday'] * 24 * 60 * 60;
            if ($isLock) {
                $this->error(Ibos::lang('The diary is locked'),
                    $this->createUrl('index'));
            }
        }
        // 日志是否开启评阅后锁定，评阅后则锁定不能修改
        if ($dashboardConfig['reviewlock'] == 1) {
            if ($diary['isreview'] == 1) {
                $this->error(Ibos::lang('The diary is locked'),
                    $this->createUrl('index'));
            }
        }

        return true;
    }

    /**
     * 把某篇日志改成已评阅
     * @param $diary
     * @param $stamp
     * @internal param type $diaryid
     */
    private function changeIsreview($diary, $stamp)
    {
        // 判断是否是直属上司，只给直属上司自动评阅
        if (UserUtil::checkIsUpUid($diary['uid'], Ibos::app()->user->uid)) {
            if ($diary['stamp'] == 0) {
                Diary::model()->modify($diary['diaryid'],
                    array('isreview' => 1, 'stamp' => $stamp));
                DiaryStats::model()->scoreDiary($diary['diaryid'],
                    $diary['uid'], $stamp);
            } else {
                Diary::model()->modify($diary['diaryid'],
                    array('isreview' => 1));
            }
        }
    }

    /**
     * 由于saas不能添加数据表，所以判断下，没有就给用户加个表
     * @return bool
     */
    private function productDirectTable()
    {
        // 判断数据表是否存在，如果不存在，则创建
        $tableName = DiaryDirect::model()->tableName();
        if (!Model::tableExists($tableName)) {
            Mobile::createDirectTable($tableName);
        }
        return true;
    }
}
