<?php

namespace application\modules\message\model;

use application\core\model\Model;
use application\core\utils\Cache as CacheUtil;
use application\core\utils\Cloud;
use application\core\utils\IBOS;
use application\core\utils\Mail;
use application\core\utils\StringUtil;
use application\modules\main\model\Setting;
use application\modules\message\utils\Message as MessageUtil;
use application\modules\user\model\User;
use application\modules\user\model\UserBinding;
use application\modules\user\model\UserProfile;

class Notify extends Model {

    public function init() {
        $this->cacheLife = 0;
        parent::init();
    }

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{notify_node}}';
    }

    public function afterSave() {
        CacheUtil::update( 'NotifyNode' );
        CacheUtil::load( 'NotifyNode' );
        parent::afterSave();
    }

    /**
     * 全局发送提醒接口
     * @param array $toIds 接收消息的用户ID数组
     * @param string $node 节点Key值
     * @param array $config 配置数据
     * @return void
     */
    public function sendNotify( $toIds, $node, $config = array() ) {
        $nodeInfo = $this->getNode( $node );
        if ( !$nodeInfo ) {
            return false;
        }
        // 根据通知节点初始化通知数据，返回一个固定格式的数组
        $data = $this->getNotifyData( $nodeInfo, $config );
        if ( !is_array( $toIds ) ) {
            $toIds = explode( ',', $toIds );
        }
        // 统一推送数组，此数组将在后面的步骤中逐渐赋值
        $pushDatas = array();
        // 设置微信推送
        $this->setWechatPush( $toIds, $data, $pushDatas );
        $this->setImPush( $toIds, $data );
        $this->setPushByUser( $toIds, $data, $nodeInfo, $pushDatas );
        if ( !empty( $pushDatas ) ) {
            //推送消息到云服务，然后分发给桌面端、安卓端和IOS端
            /**
             * todo:这里没有处理失败的情况，如果需要的话……
             */
            Cloud::getInstance()->fetchPush( array( 'objects' => $pushDatas ) );
        }
        return true;
    }

    /**
     * 获取指定节点信息
     * @param string $node 节点Key值
     * @return array 指定节点信息
     */
    public function getNode( $node ) {
        $list = $this->getNodeList();
        return isset( $list[$node] ) ? $list[$node] : false;
    }

    /**
     * 获取节点列表
     * @return array 节点列表数据
     */
    public function getNodeList() {
        // 缓存处理
        $list = CacheUtil::get( 'NotifyNode' );
        if ( !$list ) {
            $list = $this->fetchAllSortByPk( 'node', array( 'order' => '`module` DESC' ) );
            CacheUtil::set( 'NotifyNode', $list );
        }
        return $list;
    }

    /**
     * 获取推送消息所需数组
     * @param array $nodeInfo 节点数组
     * @param array $config 节点配置数组
     * @return array
     */
    protected function getNotifyData( $nodeInfo, $config = array() ) {
        $data = array(
            'node' => $nodeInfo['node'],
            'module' => $nodeInfo['module'],
            'url' => isset( $config['{url}'] ) ? $config['{url}'] : '',
            'title' => IBOS::lang( $nodeInfo['titlekey'], '', $config ),
            //比如查看id=1的日志，这里的id就是1
            'id' => isset( $config['id'] ) ? $config['id'] : '',
        );
        if ( empty( $nodeInfo['contentkey'] ) ) {
            $data['body'] = $data['title'];
            $data['hasContent'] = false;
        } else {
            $data['body'] = IBOS::lang( $nodeInfo['contentkey'], '', $config );
            $data['hasContent'] = true;
        }
        return $data;
    }

    /**
     * 推送IM消息
     * @param array $toIds 要发送的用户身份标识
     * @param array $data 推送消息数组
     * @return void
     */
    protected function setImPush( $toIds, $data ) {
        // 推送IM提醒
        MessageUtil::push( 'notify', $toIds, array( 'message' => $data['title'], 'url' => $data['url'] ) );
    }

    /**
     * 根据推送的用户及其设置，设置全局推送数据
     * @param array $toIds 要发送的用户ID
     * @param array $data 推送消息数组
     * @param array $node 节点数组
     * @param array $pushDatas 全局推送数组
     * @return void
     */
    protected function setPushByUser( $toIds, $data, $node, &$pushDatas ) {
        $isCloud = Cloud::getInstance()->isOpen();
        $uidString = implode( ',', $toIds );
        $userInfo = IBOS::app()->db->createCommand()
                ->select( 'u.uid,u.email,up.remindsetting,u.mobile' )
                ->from( User::model()->tableName() . ' u' )
                ->leftJoin( UserProfile::model()->tableName() . ' up', "u.uid = up.uid" )
                ->where( " FIND_IN_SET( `u`.`uid`, '{$uidString}')" )
                ->queryAll();
        $sendArray = array();
        if ( !empty( $userInfo ) ) {//刁民，怎么会有传空的过来呢？
            foreach ( $userInfo as $user ) {
                $data['uid'] = $user['uid'];
                // 推送系统消息
                if ( !empty( $node['sendmessage'] ) ) {
                    $sendArray[] = NotifyMessage::model()->sendMessage( $data );
                }
                // 加载个人提醒设置
                $setting = !empty( $user['remindsetting'] ) ? StringUtil::utf8Unserialize( $user['remindsetting'] ) : array();
                $pushToCO[$user['uid']] = isset( $setting[$node['node']] ) && isset( $setting[$node['node']]['app'] ) && $setting[$node['node']]['app'] == 1;

                if ( !empty( $node['sendemail'] ) ) {
                    $pushToEmail = isset( $setting[$node['node']] ) && isset( $setting[$node['node']]['email'] ) && $setting[$node['node']]['email'] == 1;
                    // 如果存在该节点并且用户允许发送邮件，发送之
                    $pushToEmail and $this->setEmailPush( $user['email'], $data, $isCloud, $pushDatas );
                }
                if ( !empty( $node['sendsms'] ) ) {
                    $pushToSms = isset( $setting[$node['node']] ) && isset( $setting[$node['node']]['sms'] ) && $setting[$node['node']]['sms'] == 1;
                    // 如果存在该节点并且用户允许发送短信，发送之
                    $pushToSms and $this->setSmsPush( $user['mobile'], $data, $isCloud, $pushDatas );
                }
            }
            // 如果存在该节点并且用户允许推送至酷办公，发送之
            $this->setCoPush( array_keys( $pushToCO ), $data, $pushDatas );
            if ( !empty( $sendArray ) ) {
                $connection = IBOS::app()->db;
                $transaction = $connection->beginTransaction();
                try {
                    foreach ( $sendArray as $send ) {
                        $connection->schema->commandBuilder->createInsertCommand( '{{notify_message}}', $send )->execute();
                    }
                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }
    }

    /**
     * 设置微信推送数据
     * @param array $toIds 要发送的用户身份标识，此处指绑定的weixinID
     * @param array $data 推送消息数组
     * @param array $pushData 全局推送数组
     * @return void
     */
    protected function setWechatPush( $toIds, $data, &$pushData ) {
        $userIds = UserBinding::model()->fetchValuesByUids( $toIds, 'wxqy' );
        if ( !empty( $userIds ) ) {
            $corpId = Setting::model()->fetchSettingValueByKey( 'corpid' );
            $body = $data['title'] . "\n\n" . ( isset( $data['{orgContent}'] ) ? $data['{orgContent}'] . "\n" : '' );
            $pushData[] = array(
                'type' => 'wechat',
                'to' => $userIds,
                'message' => $body,
                'params' => array(
                    'appFlag' => $data['module'],
                    'url' => $data['url'],
                    'corpid' => $corpId,
                )
            );
        }
    }

    /**
     * 设置酷办公推送数据
     * @param array $toIds 要发送的用户身份标识，此处指绑定的酷办公唯一标识
     * @param array $data 推送消息数组
     * @param array $pushData 全局推送数组
     * @return void
     */
    protected function setCoPush( $toIds, $data, &$pushData ) {
        $cobinding = Setting::model()->fetchSettingValueByKey( 'cobinding' );
        $aeskey = Setting::model()->fetchSettingValueByKey( 'aeskey' );
        $userIds = UserBinding::model()->fetchValuesByUids( $toIds, 'co' );
        if ( !empty( $userIds ) and $cobinding == '1' ) {
            $pushData[] = array(
                'type' => 'co',
                'to' => $userIds,
                'message' => $data['title'],
                'params' => array(
                    'module' => $data['module'],
                    'aeskey' => $aeskey,
                    'url' => $data['url'],
                    'id' => $data['id'],
                )
            );
        }
    }

    /**
     * 设置邮件推送数组
     * @param string $to 要发送的邮箱地址
     * @param array $data 推送消息数组
     * @param boolean $isCloud 是否开通了云端邮件推送
     * @param array $pushData 全局推送数组
     * @return void
     */
    protected function setEmailPush( $to, $data, $isCloud, &$pushData ) {
        if ( !empty( $to ) ) {
            // TODO:邮箱格式验证
            $body = NotifyEmail::model()->formatEmailNotify( $data );
            if ( $isCloud ) {
                $pushData[] = array(
                    'type' => 'email',
                    'to' => $to,
                    'message' => $body,
                    'params' => array(
                        'title' => $data['title'],
                        'urlparam' => Cloud::getInstance()->getCloudAuthParam( true )
                    )
                );
            } else {
                $row = array(
                    'uid' => intval( $data['uid'] ),
                    'node' => $data['node'],
                    'email' => $to,
                    'module' => $data['module'],
                    'issend' => 0,
                    'sendtime' => 0,
                    'ctime' => TIMESTAMP,
                    'body' => $body,
                    'title' => $data['title']
                );
                NotifyEmail::model()->add( $row );
                Mail::sendMail( $row['email'], $row['title'], $row['body'] );
            }
        }
    }

    /**
     *
     * @param string $to
     * @param array $data 推送消息数组
     * @param boolean $isCloud 是否开通了云端短信推送
     * @param array $pushData 全局推送数组
     */
    protected function setSmsPush( $to, $data, $isCloud, &$pushData ) {
        $row = array(
            'uid' => 0,
            'touid' => $data['uid'],
            'node' => $data['node'],
            'module' => $data['module'],
            'mobile' => $to,
            'content' => $data['title'],
        );
        if ( $isCloud ) {
            $row['posturl'] = $row['return'] = '1';
            $pushData[] = array(
                'type' => 'sms',
                'to' => $to,
                'message' => $data['title'],
                'params' => array(
                    'urlparam' => Cloud::getInstance()->getCloudAuthParam( true )
                )
            );
        } else {
            // 客户自定义接口，待项目版定制
        }
        NotifySms::model()->sendSms( $row );
    }

}
