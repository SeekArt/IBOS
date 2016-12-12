<?php

/**
 * 移动端消息控制器文件
 *
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 移动端消息控制器文件
 *
 * @package application.modules.mobile.controllers
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @version $Id$
 */

namespace application\modules\mobile\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\message\model\MessageContent;
use application\modules\message\model\NotifyMessage;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;

class MsgController extends BaseController
{

    /**
     * 默认页,获取主页面各项数据统计
     * @return void
     */
    public function actionIndex()
    {
        //$this->ajaxReturn( this->getList($type,$catid,$search),Mobile::dataType());
        $uid = Ibos::app()->user->uid;
        $list = NotifyMessage::model()->fetchAllNotifyListByUid($uid, 'ctime DESC');
        $module = Ibos::app()->getEnabledModule();
        $datas = array();
        if (!empty($list)) {
            $i = 0;
            foreach ($list as $key => $value) {
                $datas[$i] = $value;
                if (array_key_exists('newlist', $value)) {
                    $datas[$i]["unread"] = count($value['newlist']);
                } else {
                    $datas[$i]["unread"] = 0;
                }
                if (isset($module[$key])) {
                    $datas[$i]["name"] = $module[$key]["name"];
                } else {
                    $datas[$i]["name"] = "";
                }
                $datas[$i]["id"] = $key;
                $i++;
            }
        }
        $this->ajaxReturn($datas, Mobile::dataType());
    }

    public function actionList()
    {
        $uid = Ibos::app()->user->uid;
        $module = $_GET["module"];
        $list = NotifyMessage::model()->fetchAllDetailByTimeLine($uid, $module);
        //$list = NotifyMessage::model()->fetchAllNotifyListByUid( $uid, 'ctime DESC' );
        NotifyMessage::model()->setReadByModule($uid, $module);
        $data = array(
            'datas' => $list
        );

        $this->ajaxReturn($data, Mobile::dataType());
        //var_dump($data);
    }

    public function actionShow()
    {
        $message = MessageContent::model()->fetchAllMessageByListId(Env::getRequest('id'), Ibos::app()->user->uid, intval(Env::getRequest('sinceid')), intval(Env::getRequest('maxid')), 10);
        $message['data'] = array_reverse($message['data']);
        foreach ($message['data'] as $key => $value) {
            $tmpuser = User::model()->fetchByUid($value['fromuid']);
            $message['data'][$key]['fromrealname'] = $tmpuser['realname'];
            $message['data'][$key]['avatar_small'] = $tmpuser['avatar_small'];
            unset($tmpuser);
        }
        $this->ajaxReturn($message, Mobile::dataType());
    }

}
