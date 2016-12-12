<?php

/**
 * WxEmailCallback class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 微信企业号邮件中心应用回调处理器类
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.message.core.wx
 * @version $Id$
 */

namespace application\modules\message\core\wx\callback;

use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\email\model\Email as EM;
use application\modules\email\model\EmailBody;
use application\modules\message\core\wx\Callback;
use application\modules\message\core\wx\Code;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class Email extends Callback
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
     *
     * @return string
     */
    protected function handleByText()
    {
        $parts = explode('/', $this->getMessage());
        if (count($parts) != 3) {
            return $this->resText('邮件格式错误，请按[姓名/标题/邮件内容]的形式提交。');
        }
        $toUser = User::model()->findByRealname($parts[0]);
        if (empty($toUser)) {
            return $this->resText('无法找到发送的用户:' . $parts[0]);
        }
        if (empty($parts[1])) {
            return $this->resText('邮件标题不能为空');
        }
        if (empty($parts[2])) {
            return $this->resText('邮件正文不能为空');
        }

        $uid = Ibos::app()->user->uid;
        $data = array(
            'fromid' => $uid,
            'toids' => StringUtil::wrapId($toUser['uid']),
            'subject' => $parts[1],
            'content' => $parts[2],
            'sendtime' => TIMESTAMP,
            'attachmentid' => '',
            'issend' => 1,
        );
        $bodyData = EmailBody::model()->handleEmailBody($data);
        $bodyId = EmailBody::model()->add($bodyData, true);
        EM::model()->send($bodyId, $bodyData);

        UserUtil::updateCreditByAction('postmail', $uid);

        return $this->resText('邮件已发送给:' . $toUser['realname']);
    }

}
