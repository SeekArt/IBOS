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
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\extensions\ThinkImage\ThinkImage;
use application\modules\main\components\CommonAttach;
use application\modules\message\model\MessageContent;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;

class PmController extends BaseController
{

    /**
     * 默认页,获取主页面各项数据统计
     * @return void
     */
    public function actionIndex()
    {
        //$this->ajaxReturn( $article->getList($type,$catid,$search),Mobile::dataType());
    }

    public function actionList()
    {
        $uid = Ibos::app()->user->uid;
        // 获取有多少个未读新对话
        $unreadCount = MessageContent::model()->countUnreadList($uid);
        $pageCount = MessageContent::model()->countMessageListByUid($uid, array(MessageContent::ONE_ON_ONE_CHAT, MessageContent::MULTIPLAYER_CHAT));
        $pages = Page::create($pageCount);
        $list = MessageContent::model()->fetchAllMessageListByUid($uid, array(MessageContent::ONE_ON_ONE_CHAT, MessageContent::MULTIPLAYER_CHAT), $pages->getLimit(), $pages->getOffset());
        $data = array(
            'datas' => $list,
            'pages' => $pages,
            'unreadCount' => $unreadCount
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

    public function actionSend()
    {
        $content = StringUtil::filterCleanHtml($_GET['content']);
        $id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
        $touid = intval(isset($_GET['touid']) ? $_GET['touid'] : 0);

        if (!$id && $touid) {
            $data = array(
                'content' => $content,
                'touid' => $touid,
                'type' => 1
            );
            $res = MessageContent::model()->postMessage($data, Ibos::app()->user->uid);

            $message = array(
                'listid' => $res,
                'IsSuccess' => true
            );
        } else {
            $res = MessageContent::model()->replyMessage($id, $content, Ibos::app()->user->uid);

            if ($res) {
                $message = array('IsSuccess' => true, 'data' => Ibos::lang('Private message send success'));
            } else {
                $message = array('IsSuccess' => false, 'data' => Ibos::lang('Private message send fail'));
            }
        }
        $this->ajaxReturn($message, Mobile::dataType());
    }

    public function actionPostimg()
    {
        $upload = new CommonAttach('pmimage', 'mobile');
        $upload->upload();
        if (!$upload->getIsUpoad()) {
            echo "出错了";
        } else {
            $info = $upload->getUpload()->getAttach();
            $upload->updateAttach($info['aid']);
            $file = File::getAttachUrl() . '/' . $info['type'] . '/' . $info['attachment'];
            $fileUrl = File::fileName($file);

            // 存放路径
            $filePath = File::getAttachUrl() . '/' . $info['type'] . '/' . $info["attachdir"];
            // 三种尺寸的地址
            $filename = "tumb_" . $info['attachname'];
            // 如果是本地环境，先确定文件路径要存在
            if (LOCAL) {
                File::makeDirs($filePath . dirname($filename));
            }
            // 先创建空白文件
            File::createFile($filePath . $filename, '');
            // 加载类库
            $imgObj = new ThinkImage(THINKIMAGE_GD);

            // 生成缩略图
            $imgObj->open($fileUrl)->thumb(180, 180, 1)->save($filePath . $filename);

            // 插入消息内容
            $content = "<a href='" . $fileUrl . "'><img src='" . $filePath . $filename . "' /></a>";
            $id = intval(isset($_POST['pmid']) ? $_POST['pmid'] : 0);
            $touid = intval(isset($_POST['pmtouid']) ? $_POST['touid'] : 0);

            if (!$id && $touid) {
                $data = array(
                    'content' => $content,
                    'touid' => $touid,
                    'type' => 1
                );
                $res = MessageContent::model()->postMessage($data, Ibos::app()->user->uid);

                $message = array(
                    'listid' => $res,
                    'IsSuccess' => true
                );
            } else {
                $res = MessageContent::model()->replyMessage($id, $content, Ibos::app()->user->uid);

                if ($res) {
                    $message = array('IsSuccess' => true, 'data' => Ibos::lang('Private message send success'));
                } else {
                    $message = array('IsSuccess' => false, 'data' => Ibos::lang('Private message send fail'));
                }
            }
            $this->ajaxReturn($message, Mobile::dataType());
        }
    }

}
