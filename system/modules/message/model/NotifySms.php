<?php

namespace application\modules\message\model;

use application\core\model\Model;
use application\core\utils\StringUtil;

class NotifySms extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{notify_sms}}';
    }

    /**
     * 发送短信
     * @param array $data 消息的相关数据
     * @return mix 添加失败返回false，添加成功返回新数据的ID
     */
    public function sendSms($data)
    {
        $s['uid'] = intval($data['uid']);
        $s['touid'] = intval($data['touid']);
        $s['mobile'] = StringUtil::filterCleanHtml($data['mobile']);
        $s['posturl'] = StringUtil::filterCleanHtml($data['posturl']);
        $s['node'] = StringUtil::filterCleanHtml($data['node']);
        $s['module'] = StringUtil::filterCleanHtml($data['module']);
        $s['return'] = StringUtil::filterCleanHtml($data['return']);
        $s['content'] = StringUtil::filterDangerTag($data['content']);
        $s['ctime'] = time();
        return $this->add($s, true);
    }

}
