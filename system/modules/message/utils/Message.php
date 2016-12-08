<?php

/**
 * 消息模块函数库文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 消息模块函数库类
 * @package application.modules.message.utils
 * @version $Id$
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\modules\message\utils;

use application\core\utils\Cloud;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\main\model\Setting;
use application\modules\message\core as MessageCore;
use application\modules\message\model\NotifySms;
use application\modules\user\model\User;
use CJSON;

class Message
{

    /**
     * 发送短信
     * @param string $mobile 发送的手机号码
     * @param string $content 要发送的内容
     * @param string $module 隶属模块
     * @param integer $touid 发送给谁
     * @param integer $uid 由谁发送 （0为 系统发送）
     * @return boolean 发送成功与否
     */
    public static function sendSms($mobile, $content = '', $module = '', $touid = 0, $uid = 0)
    {
        $content = StringUtil::filterCleanHtml($content);
        $data = array(
            'uid' => $uid,
            'touid' => $touid,
            'node' => '',
            'module' => $module,
            'mobile' => $mobile,
            'content' => $content
        );
        // 云服务API
        if (Cloud::getInstance()->isOpen()) {
            $params = array(
                'objects' => array(
                    array(
                        'type' => 'sms',
                        'to' => $mobile,
                        'message' => $content,
                        'params' => array(
                            'urlparam' => Cloud::getInstance()->getCloudAuthParam(true)
                        )
                    )
                )
            );
            $rs = Cloud::getInstance()->fetchPush($params);
            $data['posturl'] = Cloud::getInstance()->getUrl();
            if (!is_array($rs)) {
                $data['return'] = $rs;
            } else {
                $data['return'] = CJSON::encode($rs);
            }

            /**
             * 这里直接把返回的结果保存在了notify_sms表里
             * ps：返回的提示信息参见：http://cloud.ibos.cn/下的Application.Api.Core.ApiCode.class.php
             * 嗯，我是指源代码，上面的无法直接访问的~~~
             */
            NotifySms::model()->sendSms($data);
            return true;
        } else {
            // 客户自定义接口，待项目版定制
            /* $setting = Ibos::app()->setting->get( 'setting' );
              if ( $setting['smsenabled'] && in_array( $module, $setting['smsmodule'] ) ) {

              } */
        }
        return false;
    }

    /**
     * 导出短信记录为csv格式
     * @param mixed $id 短信记录ID
     * @return void
     */
    public static function exportSms($id)
    {
        $ids = is_array($id) ? $id : explode(',', $id);
        header("Content-Type:application/vnd.ms-excel");
        $fileName = Convert::iIconv(Ibos::lang('SMS export name', 'dashboard.default', array('{date}' => date('Ymd'))), CHARSET, 'gbk');
        header("Content-Disposition: attachment;filename={$fileName}.csv");
        header('Cache-Control: max-age = 0');
        $head = array(
            'ID',
            Ibos::lang('Sender', 'dashboard.default'),
            Ibos::lang('Recipient', 'dashboard.default'),
            Ibos::lang('Membership module', 'dashboard.default'),
            Ibos::lang('Recipient phone number', 'dashboard.default'),
            Ibos::lang('Content', 'dashboard.default'),
            Ibos::lang('Result', 'dashboard.default'),
            Ibos::lang('Send time', 'dashboard.default')
        );
        foreach ($head as &$header) {
            // CSV的Excel支持GBK编码，一定要转换，否则乱码
            $header = Convert::iIconv($header, CHARSET, 'gbk');
        }
        // 打开PHP文件句柄，php://output 表示直接输出到浏览器
        $fp = fopen('php://output', 'a');
        // 将数据通过fputcsv写到文件句柄
        fputcsv($fp, $head);
        // 计数器
        $cnt = 0;
        // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100;
        $system = Ibos::lang('System', 'dashboard.default');
        foreach (NotifySms::model()->fetchAll(sprintf("FIND_IN_SET(id,'%s')", implode(',', $ids))) as $row) {
            //刷新一下输出buffer，防止由于数据过多造成问题
            if ($limit == $cnt) {
                ob_flush();
                flush();
                $cnt = 0;
            }
            $data = array(
                $row['id'],
                Convert::iIconv(($row['uid'] == 0 ? $system : User::model()->fetchRealnameByUid($row['uid'])), CHARSET, 'gbk'),
                Convert::iIconv(User::model()->fetchRealnameByUid($row['touid']), CHARSET, 'gbk'),
                $row['module'],
                $row['mobile'],
                Convert::iIconv($row['content'], CHARSET, 'gbk'),
                Convert::iIconv($row['return'], CHARSET, 'gbk'),
                date('Y-m-d H:i:s', $row['ctime'])
            );
            fputcsv($fp, $data);
        }
        exit();
    }

    /**
     * 检查某个IM类型有无开启
     * @param string $type
     * @return boolean
     */
    public static function getIsImOpen($type)
    {
        $setting = Setting::model()->fetchSettingValueByKey('im');
        $arrays = StringUtil::utf8Unserialize($setting);
        if (is_array($arrays) && isset($arrays[$type])) {
            if (isset($arrays[$type]['open']) && $arrays[$type]['open'] == '1') {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查某个IM是否绑定成功
     * @param string $type IM类型
     * @param array $im 该IM配置数组
     * @return boolean
     */
    public static function getIsImBinding($type, $im)
    {
        $className = 'application\modules\message\core\IM' . ucfirst($type);
        if (class_exists($className)) {
            $adapter = new $className($im);
            return $adapter->check() ? true : $adapter->getError();
        }
        return false;
    }

    /**
     * 消息推送
     * @param string $type 推送类型，会调用各个IM适配器的$setter方法
     * @param mixed $toUid 发送的用户
     * @param string $push 推送的内容
     * @return void
     */
    public static function push($type, $toUid, $push)
    {
        !is_array($toUid) && $toUid = explode(',', $toUid);
        $imCfg = array();
        foreach (Ibos::app()->setting->get('setting/im') as $imType => $config) {
            if ($config['open'] == '1') {
                $className = 'application\modules\message\core\IM' . ucfirst($imType);
                $imCfg = $config;
                break;
            }
        }
        if (!empty($imCfg)) {
            $factory = new MessageCore\IMFactory();
            $properties = array_merge($push, array('uid' => $toUid, 'pushType' => $type));
            $adapter = $factory->createAdapter($className, $imCfg, $properties);
            return $adapter !== false ? $adapter->push() : '';
        }
    }

}
