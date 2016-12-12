<?php

namespace application\modules\dashboard\controllers;

use application\core\utils as util;
use application\modules\dashboard\model\Announcement;

class AnnouncementController extends BaseController
{

    public function actionSetup()
    {
        $formSubmit = util\Env::submitCheck('announcementSubmit');
        if ($formSubmit) {
            $sort = $_POST['sort'];
            foreach ($sort as $id => $value) {
                Announcement::model()->modify($id, array('sort' => $value));
            }
            $this->success(util\Ibos::lang('Save succeed', 'message'));
        } else {
            $data = array();
            $count = Announcement::model()->count(array('select' => 'id'));
            $pages = util\Page::create($count);
            $list = Announcement::model()->fetchAllOnList($pages->getLimit(), $pages->getOffset());
            $data['list'] = $list;
            $data['pages'] = $pages;
            $this->render('setup', $data);
        }
    }

    public function actionAdd()
    {
        $formSubmit = util\Env::submitCheck('announcementSubmit');
        if ($formSubmit) {
            $this->beforeSave();
            $_POST['author'] = util\Ibos::app()->user->realname;
            $data = Announcement::model()->create();
            $rs = Announcement::model()->add($data);
            $this->success(util\Ibos::lang('Save succeed', 'message'));
        } else {
            $this->render('add');
        }
    }

    public function actionEdit()
    {
        $id = util\Env::getRequest('id');
        $formSubmit = util\Env::submitCheck('announcementSubmit');
        if ($formSubmit) {
            $this->beforeSave();
            $data = Announcement::model()->create();
            Announcement::model()->updateByPk($id, $data);
            $this->success(util\Ibos::lang('Save succeed', 'message'));
        } else {
            $data = array();
            if (intval($id)) {
                $data['id'] = $id;
                $data['record'] = Announcement::model()->fetchByPk($id);
                $this->render('edit', $data);
            }
        }
    }

    public function actionDel()
    {
        $formSubmit = util\Env::submitCheck('announcementSubmit');
        if ($formSubmit) {
            $ids = util\Env::getRequest('id');
            $id = implode(',', $ids);
            $this->announcementDelete($id);
            $this->success(util\Ibos::lang('Save succeed', 'message'));
        } else {
            $id = util\Env::getRequest('id');
            if ($this->announcementDelete($id)) {
                $this->success(util\Ibos::lang('Del succeed', 'message'));
            } else {
                $this->error(util\Ibos::lang('Del failed', 'message'));
            }
        }
    }

    protected function beforeSave()
    {
        $_POST['subject'] = $_POST['subject'];
        $_POST['message'] = $_POST['message'];
        $_POST['starttime'] = strtotime($_POST['starttime']);
        $_POST['endtime'] = strtotime($_POST['endtime']);
        if ($_POST['starttime'] > $_POST['endtime']) {
            $this->error(util\Ibos::lang('Sorry, you did not enter the start time or the end time you input is not correct', 'error'));
        }
    }

    /**
     * 删除公告
     * @param integer $id 公告ID
     * @return boolean
     */
    private function announcementDelete($id)
    {
        return Announcement::model()->deleteById($id);
    }

}
