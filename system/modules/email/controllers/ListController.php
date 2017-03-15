<?php

namespace application\modules\email\controllers;

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\email\model\Email;
use application\modules\email\utils\Email as EmailUtil;
use application\modules\user\model\User;
use application\modules\email\model\EmailBody;

class ListController extends BaseController
{

    public function init()
    {
        parent::init();
        // 文件夹ID
        $this->fid = intval(Env::getRequest('fid'));
        // 外部邮箱ID
        $this->webId = intval(Env::getRequest('webid'));
        // 分类存档ID
        $this->archiveId = intval(Env::getRequest('archiveid'));
        // 子动作
        $this->subOp = Env::getRequest('subop') . '';
        // 设置列表显示条数
        if (isset($_GET['pagesize'])) {
            $this->setListPageSize($_GET['pagesize']);
            $fid = Ibos::app()->session['fid'];
            $op = Ibos::app()->session['op'];
            switch ($op) {
                case 'folder':
                    $this->redirect($this->createUrl('list/index', array('op' => 'folder', 'fid' => $fid)));
                    break;
                case 'inbox':
                    $this->redirect($this->createUrl('list/index', array('op' => 'inbox')));
                    break;
                case 'todo':
                    $this->redirect($this->createUrl('list/index', array('op' => 'todo')));
                    break;
                case 'draft':
                    $this->redirect($this->createUrl('list/index', array('op' => 'draft')));
                    break;
                case 'send':
                    $this->redirect($this->createUrl('list/index', array('op' => 'send')));
                    break;
                case 'archive':
                    $this->redirect($this->createUrl('list/index', array('op' => 'archive')));
                    break;
                case 'del':
                    $this->redirect($this->createUrl('list/index', array('op' => 'del')));
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * 列表页
     * @return void
     */
    public function actionIndex()
    {
        $op = Env::getRequest('op');
        Ibos::app()->session['op'] = $op;
        $opList = array(
            'inbox', 'todo', 'draft',
            'send', 'folder', 'archive',
            'del'
        );
        if ($this->allowWebMail) {
            $opList[] = 'web';
        }
        if ($op == 'folder') {
            Ibos::app()->session['fid'] = Env::getRequest('fid');
        }
        if (!in_array($op, $opList)) {
            $op = 'inbox';
        }
        $data = $this->getListData($op);
        //文件夹名和邮件主题存在xss漏洞
        $this->setPageTitle(Ibos::lang('Email center'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Personal Office')),
            array('name' => Ibos::lang('Email center'), 'url' => $this->createUrl('list/index', array('op' => $op))),
        ));
        $this->render('index', $data);
    }

    /**
     * 查询
     */
    public function actionSearch()
    {
        // 参数处理
        $uid = (int)Ibos::app()->user->uid;
        $op = Env::getRequest('op');
        Ibos::app()->session['op'] = $op;
        $search = Env::getRequest('search');
        $type = Env::getRequest('type');
        // 参数判断，需要考虑外部邮件的时候
        $opArr = array(
            "draft",
            "send",
            "inbox",
            "todo",
            "del",
            "folder",
            "web"
        );
        if (!in_array($op, $opArr)) {
            $op = "inbox";
        }
        $typeArr = array(
            "normal_search",
            "advanced_search",
        );
        if (!in_array($type, $typeArr)) {
            $type = "normal_search";
        }
        if (!isset($search["keyword"])) {
            $search["keyword"] = "";
        }

        // 搜索
        if ("normal_search" === $type) {
            $command = Email::model()->normalSearch($uid, $op, $search["keyword"]);
        } elseif ("advanced_search" === $type) {
            $command = Email::model()->advancedSearch($uid, $op, $search);
        } else {
            return $this->error(Ibos::lang("Invalid params"), $this->createUrl('email/list'));
        }
        $conditionStr = $command->getWhere();
        $emailData = $command->queryAll();
        $emailData = Email::model()->handleSearchData($emailData);

        // 获取分页数据 & 输出视图
        $count = count($emailData);
        $pages = Page::create($count, $this->getListPageSize(), false);
        $pages->params = array('condition' => $conditionStr);
        $list = array_slice($emailData, $pages->getOffset(), $pages->getLimit(), false);
        foreach ($list as $index => &$mail) {
            $mail['fromuser'] = $mail['fromid'] ? User::model()->fetchRealnameByUid($mail['fromid']) : "";
        }
        //修改了一下如果的外部邮件的需要修改的发件人。
        for ($i = 0; $i < count($list); $i++) {
            if (empty($list[$i]['fromuser'])) {
                $list[$i]['fromuser'] = $list[$i]['fromwebmail'];
            }
        }
        $data = array(
            'op' => Env::getRequest('op'),
            'list' => $list,
            'pages' => $pages,
            'condition' => $conditionStr,
            'folders' => $this->folders
        );
        $this->setPageTitle(Ibos::lang('Search result'));
        $this->setPageState('breadCrumbs', array(
            array('name' => Ibos::lang('Personal Office')),
            array('name' => Ibos::lang('Email center'), 'url' => $this->createUrl('list/index')),
            array('name' => Ibos::lang('Search result'))
        ));
        $this->render('search', $data);
    }

    /**
     * 邮件箱列表
     * @return void
     */
    private function getListData($operation)
    {
        $data['op'] = $operation;
        $data['fid'] = $this->fid;
        Ibos::app()->session['fid'] = $this->fid;
        $data['webId'] = $this->webId;
        $data['folders'] = $this->folders;
        $data['archiveId'] = $this->archiveId;
        $data['allowRecall'] = Ibos::app()->setting->get('setting/emailrecall');
        $uid = $this->uid;
        // 归档列表要确认子动作
        if ($operation == 'archive') {
            if (!in_array($this->subOp, array('in', 'send'))) {
                $this->subOp = 'in';
            }
        }
        $data['subOp'] = $this->subOp;
        $count = Email::model()->countByListParam($operation, $uid, $data['fid'], $data['archiveId'], $data['subOp']);
        $pages = Page::create($count, $this->getListPageSize());
        $data['pages'] = $pages;
        $data['unreadCount'] = Email::model()->countUnreadByListParam($operation, $uid, $data['fid'], $data['archiveId'], $data['subOp']);
        $data['list'] = Email::model()->fetchAllByListParam($operation, $uid, $data['fid'], $data['archiveId'], $pages->getLimit(), $pages->getOffset(), $data['subOp']);
        return $data;
    }

}
