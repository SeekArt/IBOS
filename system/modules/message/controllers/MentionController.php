<?php

namespace application\modules\message\controllers;

use application\core\utils\Ibos;
use application\core\utils\Page;
use application\core\utils\Convert;
use application\modules\message\model\Atme;
use application\modules\message\model\FeedDigg;
use application\modules\message\model\UserData;

class MentionController extends BaseController
{

    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        //获取未读@Me的条数
        $unreadAtMe = UserData::model()->countUnreadAtMeByUid($uid);
        $pageCount = Atme::model()->countByAttributes(array('uid' => $uid));
        // 获取@Me微博列表
        $pages = Page::create($pageCount);
        $atList = Atme::model()->fetchAllAtmeListByUid($uid, $pages->getLimit(), $pages->getOffset());
        $feedIds = Convert::getSubByKey($atList, 'feedid');
        $diggArr = FeedDigg::model()->checkIsDigg($feedIds, $uid);
        $data = array(
            'unreadAtmeCount' => $unreadAtMe,
            'list' => $atList,
            'pages' => $pages,
            'digg' => $diggArr
        );
        $this->setPageTitle(Ibos::lang('Mention me'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Message center'), 'url' => $this->createUrl('mention/index')),
            array('name' => Ibos::lang('Mention me'))
        ));
        $this->render('index', $data);
    }

}
