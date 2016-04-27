<?php

namespace application\modules\message\model;

use application\core\model\Model;
use application\core\utils\IBOS;
use application\core\utils\String;

class NotifyMessage extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{notify_message}}';
    }

    /**
     * 消息提醒列表页，以模块分类，最新消息在前
     * @param integer $uid 用户ID
     * @param string $order 排序
     * @param integer $limit 每页条数
     * @param integer $offset 页数偏移量
     * @return array
     */
    public function fetchAllNotifyListByUid( $uid, $order = 'ctime DESC', $limit = 10, $offset = 0 ) {
        $criteria = array(
            'condition' => 'uid = :uid',
            'params' => array( ':uid' => intval( $uid ) ),
            'group' => 'module',
            'order' => $order,
            'limit' => $limit,
            'offset' => $offset
        );
        $return = array();
        $records = $this->findAll( $criteria );
        if ( !empty( $records ) ) {
            foreach ( $records as $record ) {
                $msg = $record->attributes;
                $return[$msg['module']] = array();
                $criteria = array(
                    'condition' => 'isread = 0 AND module = :module AND uid = :uid',
                    'params' => array( ':module' => $msg['module'], ':uid' => $uid ),
                    'order' => 'ctime DESC',
                );
                $new = $this->fetchAll( $criteria );
                if ( !empty( $new ) ) {
                    $return[$msg['module']]['newlist'] = $new;
                } else {
                    $return[$msg['module']]['latest'] = $this->fetch(
                            array(
                                'condition' => 'module = :module AND uid = :uid',
                                'params' => array( ':uid' => $uid, ':module' => $msg['module'] ),
                                'order' => 'ctime DESC'
                            )
                    );
                }
            }
        }
        return $return;
    }

    /**
     * 提醒详情页面，按时间轴排序
     * @param integer $uid 用户ID
     * @param string $module 模块名称
     * @param integer $limit 每页条数
     * @param integer $offset 页数偏移量
     * @return array
     */
    public function fetchAllDetailByTimeLine( $uid, $module, $limit = 10, $offset = 0 ) {
        $criteria = array(
            'condition' => 'uid = :uid AND module = :module',
            'params' => array( ':uid' => intval( $uid ), 'module' => $module ),
            'order' => 'ctime DESC',
            'limit' => $limit,
            'offset' => $offset
        );
        $return = array();
        $records = $this->findAll( $criteria );
        if ( !empty( $records ) ) {
            foreach ( $records as $record ) {
                $msg = $record->attributes;
                $index = date( 'Yn', $msg['ctime'] );
                $return[$index][$msg['id']] = $msg;
            }
        }
        return $return;
    }

    /**
     * 获取指定用户未读消息的总数
     * @param integer $uid 用户ID
     * @return integer 指定用户未读消息的总数
     */
    public function countUnreadByUid( $uid ) {
        return $this->count( '`uid` = :uid AND `isread` = :isread', array( ':uid' => $uid, ':isread' => 0 ) );
    }

    /**
     * 更改指定用户的消息从未读为已读
     * @param integer $uid 用户ID
     * @return mixed 更改失败返回false，更改成功返回影响消息ID
     */
    public function setRead( $uid ) {
        return $this->updateAll( array( 'isread' => 1 ), 'uid = :uid', array( ':uid' => intval( $uid ) ) );
    }

    /**
     * 更改指定用户指定模块的消息从未读为已读
     * @param integer $uid 用户ID
     * @param string $module 模块名称
     * @return mixed 更改失败返回false，更改成功返回影响消息ID
     */
    public function setReadByModule( $uid, $module ) {
        return $this->updateAll( array( 'isread' => 1 ), "uid = :uid AND FIND_IN_SET(module,:module)", array( ':uid' => intval( $uid ), ':module' => $module ) );
    }

    /**
     * 根据用户访问的 url 地址修改对应的消息为已读
     * @param integer $uid 用户 ID
     * @param string $url 用户访问的 URL
     * @return mixed 更改失败返回false，更改成功返回影响消息ID
     */
    public function setReadByUrl( $uid, $url ) {
        return $this->updateAll( array( 'isread' => 1 ), "uid = :uid AND FIND_IN_SET(url, :url)", array( ':uid' => intval( $uid ), ':url' => $url ) );
    }

    /**
     * 发送一条消息提醒
     * @param array $data 发送消息提醒所需数组
     * @return boolean
     */
    public function sendMessage( $data ) {
        if ( empty( $data['uid'] ) ) {
            return false;
        }
        $s['uid'] = intval( $data['uid'] );
        $s['node'] = String::filterCleanHtml( $data['node'] );
        $s['module'] = String::filterCleanHtml( $data['module'] );
        $s['isread'] = 0;
        $s['title'] = String::filterCleanHtml( $data['title'] );
        $s['body'] = String::filterDangerTag( $data['body'] );
        $s['ctime'] = time();
        $s['url'] = $data['url'];
        return $s;
    }

    /**
     * 根据ID或模块删除通知
     * @param mixed $id 通知ID或模块
     * @return mixed 删除失败返回false，删除成功返回删除的条数
     */
    public function deleteNotify( $id, $type = 'id' ) {
        $uid = IBOS::app()->user->uid;
        if ( $type == 'id' ) {
            return $this->deleteAll( 'uid = :uid AND FIND_IN_SET(id,:id)', array( ':uid' => $uid, ':id' => $id ) );
        } else if ( $type == 'module' ) {
            return $this->deleteAll( 'uid = :uid AND FIND_IN_SET(module,:module)', array( ':uid' => $uid, ':module' => $id ) );
        }
    }

    /**
     * 根据uid查找有多少个模块有消息，用于分页
     * @param integer $uid 用户uid
     * @return integer 符合条件的条数，注：是根据模块分组
     */
    public function fetchPageCountByUid( $uid ) {
        $pageCount = $this->count( array(
            'select' => 'id',
            'condition' => 'uid=:uid',
            'params' => array( ':uid' => $uid ),
            'group' => 'module'
                ) );
        return $pageCount;
    }

}
