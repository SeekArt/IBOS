<?php

namespace application\modules\email\utils;

use application\core\utils\Ibos;

class EmailApi
{

    private $_indexTab = array('inbox', 'unread', 'todo');

    /**
     *
     * @return type
     */
    public function renderIndex()
    {
        $return = array();
        $viewAlias = 'application.modules.email.views.indexapi.email';
        $data['lang'] = Ibos::getLangSource('email.default');
        $data['assetUrl'] = Ibos::app()->assetManager->getAssetsUrl('email');
        foreach ($this->_indexTab as $tab) {
            $data['emails'] = $this->loadEmail($tab);
            $data['tab'] = $tab;
            $return[$tab] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
        }
        return $return;
    }

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting()
    {
        return array(
            'name' => 'email/email',
            'title' => Ibos::lang('My email', 'email.default'),
            'style' => 'in-email',
            'tab' => array(
                array(
                    'name' => 'inbox',
                    'title' => Ibos::lang('Inbox', 'email.default'),
                    'icon' => 'o-mal-inbox'
                ),
                array(
                    'name' => 'unread',
                    'title' => Ibos::lang('Unread', 'email.default'),
                    'icon' => 'o-mal-unread'
                ),
                array(
                    'name' => 'todo',
                    'title' => Ibos::lang('Todo', 'email.default'),
                    'icon' => 'o-mal-todo'
                )
            )
        );
    }

    /**
     * 获取最新邮件
     * @return integer
     */
    public function loadNew()
    {
        $uid = Ibos::app()->user->uid;
        $command = Ibos::app()->db->createCommand();
        $count = $command->select('count(emailid)')
            ->from('{{email}}')
            ->where("`toid`='{$uid}' AND `fid`= 1 AND `isdel` = 0 AND `isread` = 0")
            ->queryScalar();
        return intval($count);
    }

    /**
     * 加载指定$num条$type的邮件内容
     * @param string $type 查找类型
     * @param integer $num
     * @return array
     */
    private function loadEmail($type = 'inbox', $num = 4)
    {
        $uid = Ibos::app()->user->uid;
        $command = Ibos::app()->db->createCommand();
        $command->select('emailid,b.bodyid,toid,isread,ismark,fromid,subject,content,sendtime,attachmentid,important,u.realname')
            ->from('{{email}} e')
            ->leftJoin('{{email_body}} b', 'e.bodyid = b.bodyid')
            ->leftJoin('{{user}} u', 'b.fromid = u.uid');
        switch ($type) {
            case "inbox":
                $command->where("`toid`='{$uid}' AND `fid`= 1 AND `isdel` = 0 AND `isweb` != 1");
                break;
            case "unread":
                $command->where("`toid`='{$uid}' AND (`isread`='' OR `isread` = 0) AND `isdel`= 0 AND `fid` = 1");
                break;
            case "todo":
                $command->where("`toid` ='{$uid}' AND `ismark` = 1 AND `isdel` = 0");
                break;
            default :
                return false;
        }
        $records = $command->order('e.emailid DESC')
            ->offset(0)
            ->limit($num)
            ->queryAll();

        return $records;
    }

}
