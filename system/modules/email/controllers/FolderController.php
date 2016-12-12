<?php

namespace application\modules\email\controllers;

use application\core\utils\Convert;
use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\email\model\Email;
use application\modules\email\model\EmailFolder;
use application\modules\email\utils\Email as EmailUtil;

class FolderController extends BaseController
{

    public $layout = false;

    public function init()
    {
        $this->fid = intval(Env::getRequest('fid'));
        parent::init();
    }

    /**
     * 文件夹设置
     */
    public function actionIndex()
    {
        $uid = $this->uid;
        $total = 0;
        $folders = $this->folders;
        // 用户自定义文件夹
        foreach ($folders as &$folder) {
            $size = EmailFolder::model()->getFolderSize($uid, $folder['fid']);
            $folder['size'] = Convert::sizeCount($size);
            $total += $size;
        }
        // 4个系统文件夹
        $inbox = EmailFolder::model()->getSysFolderSize($uid, 'inbox');
        $web = EmailFolder::model()->getSysFolderSize($uid, 'web');
        $sent = EmailFolder::model()->getSysFolderSize($uid, 'send');
        $deleted = EmailFolder::model()->getSysFolderSize($uid, 'del');
        // 用户当前用量
        $userSize = EmailUtil::getUserSize($uid);
        $data = array(
            'folders' => $folders,
            'inbox' => Convert::sizeCount($inbox),
            'web' => Convert::sizeCount($web),
            'sent' => Convert::sizeCount($sent),
            'deleted' => Convert::sizeCount($deleted),
            'userSize' => $userSize,
            'total' => Convert::sizeCount(array_sum(array($total, $inbox, $web, $sent, $deleted)))
        );
        $this->setPageTitle(Ibos::lang('Folder setting'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Personal Office')),
            array('name' => Ibos::lang('Email center'), 'url' => $this->createUrl('list/index')),
            array('name' => Ibos::lang('Folder setting'))
        ));
        $this->render('index', $data);
    }

    /**
     * 增加操作
     */
    public function actionAdd()
    {
        $sort = Env::getRequest('sort');
        $name = Env::getRequest('name');
        if (!empty($name)) {
            //添加对文件夹名name的xss安全过滤
            StringUtil::ihtmlSpecialCharsUseReference($name);
            $data = array(
                'sort' => intval($sort),
                'name' => $name,
                'uid' => $this->uid
            );
            $newId = EmailFolder::model()->add($data, true);
            $this->ajaxReturn(array(
                'isSuccess' => true,
                'fid' => $newId,
                'sort' => intval($sort),
                'name' => $name,));
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Save failed', 'message')));
        }
    }

    /**
     * 编辑操作
     */
    public function actionEdit()
    {
        $fid = $this->fid;
        $sort = Env::getRequest('sort');
        $name = Env::getRequest('name');
        if (!empty($name)) {
            StringUtil::ihtmlSpecialCharsUseReference($name);
            EmailFolder::model()->modify($fid, array('sort' => intval($sort), 'name' => $name));
            $this->ajaxReturn(array(
                'isSuccess' => true,
                'sort' => intval($sort),
                'name' => $name,));
        } else {
            $this->ajaxReturn(array(
                'isSuccess' => false,
                'msg' => Ibos::lang('Save failed', 'message')));
        }
    }

    /**
     * 删除操作
     */
    public function actionDel()
    {
        $fid = $this->fid;
        $cleanAll = Env::getRequest('delemail');
        $emailIds = Email::model()->fetchAllEmailIdsByFolderId($fid, $this->uid);
        if ($cleanAll) {
            $emailIds && Email::model()->completelyDelete($emailIds, $this->uid);
        } else {
            // 如果文件夹中有邮件，移回默认收件箱
            $emailIds && Email::model()->updateByPk($emailIds, array('fid' => parent::INBOX_ID));
        }
        $deleted = EmailFolder::model()->deleteByPk($fid);
        if ($deleted) {
            $this->ajaxReturn(array('isSuccess' => true));
        } else {
            $this->ajaxReturn(array('isSuccess' => false, 'errorMsg' => Ibos::lang('Del failed', 'message')));
        }
    }

}
