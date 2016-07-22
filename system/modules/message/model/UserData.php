<?php

namespace application\modules\message\model;

use application\core\model\Model;
use application\core\utils\Cache;
use application\core\utils\IBOS;
use application\core\utils\StringUtil;
use application\modules\message\model\MessageContent;
use application\modules\message\model\NotifyMessage;
use application\modules\assignment\model\AssignmentRemind;
use application\modules\assignment\model\Assignment;
use application\modules\user\model\User;

class UserData extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{user_data}}';
    }

    /**
     *
     * @param type $uid
     * @param type $key
     * @return type
     */
    public function fetchKeyValueByUid( $uid, $key ) {
        $criteria = array(
            'select' => 'value',
            'condition' => "`key` = :key AND uid=:uid",
            'params' => array( ':key' => $key, ':uid' => $uid )
        );
        $res = $this->fetch( $criteria );
        return !empty( $res['value'] ) ? StringUtil::utf8Unserialize( $res['value'] ) : array();
    }

    /**
     * 获取指定用户的最近at
     * @param integer $uid
     * @return array
     */
    public function fetchRecentAt( $uid ) {
        $criteria = array(
            'select' => 'value',
            'condition' => "`key` = 'user_recentat' AND uid=:uid",
            'params' => array( ':uid' => $uid )
        );
        $res = $this->fetch( $criteria );
        return !empty( $res['value'] ) ? StringUtil::utf8Unserialize( $res['value'] ) : array();
    }

    /**
     * 获取活跃成员 (暂时是以微博数最高为排序)
     * @param integer $name 要获取的数量
     */
    public function fetchActiveUsers( $nums = 10 ) {
        $criteria = array(
            'select' => 'uid,value',
            'condition' => "`key` = 'feed_count'",
            'order' => 'value*1 DESC', // 表字段类型为字符串类型，但这个feedcount存的是int类型，因此要让它自然排序
            'limit' => $nums
        );
        $res = $this->fetchAll( $criteria );
        foreach ( $res as &$v ) {
            $v['user'] = User::model()->fetchByUid( $v['uid'] );
        }
        return $res;
    }

    /**
     * 更新指定用户的通知统计数目
     * @param integer $uid 用户UID
     * @param string $key 统计数目的Key值
     * @param integer $rate 数目变动的值
     * @return void
     */
    public function updateUserCount( $uid, $key, $rate ) {
        $this->updateKey( $key, $rate, true, $uid );
    }

    /**
     * 更新某个用户的指定Key值的统计数目
     * Key值：
     * feedcount：动态总数
     * followingcount：关注数
     * followercount：粉丝数
     * unreadcomment：评论未读数
     * unreadatme：@Me未读数
     * @param string $key Key值
     * @param integer $nums 更新的数目
     * @param boolean $add 是否添加数目，默认为true
     * @param integer $uid 用户UID
     * @return array 返回更新后的数据
     */
    public function updateKey( $key, $nums, $add = true, $uid = '' ) {
        // 不需要修改
        if ( $nums == 0 ) {
            $this->addError( 'updateKey', IBOS::lang( 'Dont need to modify', 'message.default' ) );
            return false;
        }
        // 若更新数目小于0，则默认为减少数目
        $nums < 0 && $add = false;
        $key = StringUtil::filterCleanHtml( $key );
        // 获取当前设置用户的统计数目
        $data = $this->getUserData( $uid );
        if ( empty( $data ) || !$data ) {
            $data = array();
            $data[$key] = $nums;
        } else {
            $data[$key] = $add ? ((int) @$data[$key] + abs( $nums )) : ((int) @$data[$key] - abs( $nums ));
        }

        $data[$key] < 0 && $data[$key] = 0;

        $map['uid'] = empty( $uid ) ? IBOS::app()->user->uid : $uid;
        $map['key'] = $key;
        $this->deleteAll( '`key` = :key AND uid = :uid', array( ':key' => $key, ':uid' => $map['uid'] ) );
        $map['value'] = $data[$key];
        $map['mtime'] = date( 'Y-m-d H:i:s' );
        $this->add( $map );
        Cache::rm( 'userData_' . $map['uid'] );
        return $data;
    }

    /**
     * 获取指定用户的统计数据
     * @param integer $uid 用户UID
     * @return array 指定用户的统计数据
     */
    public function getUserData( $uid = '' ) {
        // 默认为设置的用户
        if ( empty( $uid ) ) {
            $uid = IBOS::app()->user->uid;
        }
        if ( ($data = Cache::get( 'userData_' . $uid )) === false || count( $data ) == 1 ) {
            $data = array();
            $list = $this->fetchAll( '`uid` = :uid', array( ':uid' => $uid ) );
            if ( !empty( $list ) ) {
                foreach ( $list as $v ) {
                    $data[$v['key']] = (int) $v['value'];
                }
            }
            Cache::set( 'userData_' . $uid, $data, 60 ); // 1分钟缓存
        }
        return $data;
    }

    /**
     * 获取指定用户的通知统计数目
     * @param integer $uid 用户UID
     * @return array 指定用户的通知统计数目
     */
    public function getUnreadCount( $uid ) {
        $userData = $this->getUserData( $uid );
        // 指定用户的提醒通知统计数目
        $count = NotifyMessage::model()->count( " uid = {$uid} and isread = 0 " );
        // 未读提醒通知数目
        $return = $this->getNotifyCount( $uid );
        // 未读总通知数目
        $return['unread_notify'] = intval( NotifyMessage::model()->countUnreadByUid( $uid ) );
        // 未读@Me数目
        $return['unread_atme'] = isset( $userData['unread_atme'] ) ? intval( $userData['unread_atme'] ) : 0;
        // 未读评论数目
        $return['unread_comment'] = isset( $userData['unread_comment'] ) ? intval( $userData['unread_comment'] ) : 0;
        // 未读短信息数目
        $return['unread_message'] = MessageContent::model()->countUnreadMessage( $uid, array( MessageContent::ONE_ON_ONE_CHAT, MessageContent::MULTIPLAYER_CHAT ) );
        // 新的关注数目
        $return['new_folower_count'] = isset( $userData['new_folower_count'] ) ? intval( $userData['new_folower_count'] ) : 0;
        // 未读标题总通知数目
        $return['unread_total'] = $count + $return['unread_atme'] + $return['unread_comment'] + $return['unread_message'] + $return['new_folower_count'];
        return $return;
    }

    /**
     * 获取指定用户的提醒通知统计
     * @param integer $uid 用户ID
     * @return array 指定用户的提醒通知统计
     */
    public function getNotifyCount( $uid ) {
        $notifyMessageList = NotifyMessage::model()->fetchAll( " uid = {$uid} and isread = 0 " );
        $return = array();
        foreach ( $notifyMessageList as $notifyMessage ) {
            if ( in_array( $notifyMessage['module'], $notifyMessage ) ) {
                $return[$notifyMessage['module']][] = count( $notifyMessage['module'] );
            }
        }
        foreach ( $return as $key => $value ) {
            $return[$key] = count( $value );
        }
        return $return;
    }

    /**
     * 设置指定用户指定Key值的统计数目
     * @param integer $uid 用户UID
     * @param string $key Key值
     * @param integer $value 设置的统计数值
     * @return void
     */
    public function setKeyValue( $uid, $key, $value ) {
        $map['uid'] = $uid;
        $map['key'] = $key;
        $this->deleteAllByAttributes( $map );
        $map['value'] = $value;
        $map['mtime'] = date( 'Y-m-d H:i:s' );
        $this->add( $map );
        // 清掉该用户的缓存
        Cache::rm( 'userData_' . $uid );
    }

    /**
     * 统计指定用户的@ 条数
     * @param integer $uid 用户ID
     * @return integer 条数
     */
    public function countUnreadAtMeByUid( $uid ) {
        $criteria = array(
            'select' => 'value',
            'condition' => '`uid` = :uid AND `key` = :key',
            'params' => array( ':uid' => $uid, 'key' => 'unread_atme' )
        );
        $res = $this->fetch( $criteria );
        return !empty( $res ) ? intval( $res['value'] ) : 0;
    }

    /**
     * 重置指定用户的通知统计数目
     * @param integer $uid 用户UID
     * @param string $key 统计数目的Key值
     * @param integer $value 统计数目变化的值，默认为0
     * @return void
     */
    public function resetUserCount( $uid, $key, $value = 0 ) {
        $this->setKeyValue( $uid, $key, $value );
    }

}
