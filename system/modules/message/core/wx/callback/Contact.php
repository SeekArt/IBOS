<?php

/**
 * WxNewsCallback class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号信息中心应用回调处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\callback;

use application\core\utils\Org;
use application\modules\message\core\wx\Callback;
use application\modules\message\core\wx\Code;
use application\modules\message\core\wx\WxApi;
use application\modules\user\model\User;

class Contact extends Callback
{

    /**
     * 回调的处理方法
     * @return string
     */
    public function handle()
    {
        switch ($this->resType) {
            case self::RES_TEXT:
                $res = $this->handleByText();
                break;
            case self::RES_EVENT:
                $res = $this->resText();
                break;
            default:
                $res = $this->resText(Code::UNSUPPORTED_RES_TYPE);
                break;
        }
        return $res;
    }

    /**
     * 根据关键字查询用户信息
     * @return string
     */
    protected function handleByText()
    {
        $criteria = array(
            'condition' => "realname LIKE '%" . $this->getMessage() . "%' AND status IN(0,1)",
            'order' => 'uid ASC',
            'limit' => 9,
        );
        $lists = User::model()->fetchAll($criteria);
        if (empty($lists)) {
            return $this->resText('通讯录里无法找到该用户：' . $this->getMessage());
        }
        if (count($lists) == 1) {
            return $this->resByOne(array_shift($lists));
        } else {
            return $this->resByList($lists);
        }
    }

    /**
     * 找到单个联系人的处理
     * @param array $row 该联系人的数组
     * @return string
     */
    private function resByOne($row)
    {
        $user = User::model()->fetchByUid($row['uid']);
        $res = <<<EOT
姓名:{$user['realname']}
手机:{$user['mobile']}
电话:{$user['telephone']}
邮箱:{$user['email']}
EOT;
        return $this->resText($res);
    }

    /**
     * 找到多个联系人的处理
     * @param arary $lists 多个联系人的数组列表
     * @return string
     */
    private function resByList($lists)
    {
        $hostInfo = WxApi::getInstance()->getHostInfo();
        $items[0] = array(
            'title' => "为您找到以下人员",
            'description' => '',
            'picurl' => 'http://app.ibos.cn/img/banner/contact.png',
            'url' => ''
        );
        foreach ($lists as $row) {
            $route = 'http://app.ibos.cn?host=' . urlencode($hostInfo) . '/#/contacts/detail/' . $row['uid'];

            $item = array(
                'title' => $row['realname'], 'description' => '',
                'picurl' => $hostInfo . "/" . Org::getDataStatic($row['uid'], 'avatar', 'middle'),
                'url' => WxApi::getInstance()->createOauthUrl($route, $this->appId)
            );
            $items[] = $item;
        }

        return $this->resNews($items);
    }

}
