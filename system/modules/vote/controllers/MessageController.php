<?php
/**
 * @namespace application\modules\vote\controllers
 * @filename MessageController.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/26 19:48
 */

namespace application\modules\vote\controllers;


use application\core\utils\Ibos;
use application\modules\vote\utils\MessageUtil;

class MessageController extends BaseController
{
    public function actionUnRead()
    {
        $uid = Ibos::app()->user->uid;

        return $this->ajaxBaseReturn(true, array(
            // 未参与投票未读消息个数
            'vote_unjoined' => MessageUtil::getInstance()->fetchUnreadUnJoinedMsgNum($uid),
        ));
    }
}
