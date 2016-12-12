<?php

namespace application\modules\message\model;

use application\core\model\Model;
use application\core\model\Source;
use application\core\utils\Ibos;
use application\core\utils\Convert;
use application\modules\user\model\User;

class Atme extends Model
{

    private $_atRegex = "/@(.+?)(?=[\s|:]|$)/is"; //"/@{uid=([^}]*)}/";    // @正则规则

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{atme}}';
    }

    /**
     * 获取指定用户所有@ 我的列表
     * @param integer $uid
     * @param integer $limit
     * @param integer $offset
     * @param type $order
     * @return type
     */
    public function fetchAllAtmeListByUid($uid, $limit, $offset, $order = 'atid DESC')
    {
        $criteria = array(
            'condition' => '`uid` = :uid',
            'params' => array(':uid' => $uid),
            'order' => $order,
            'limit' => $limit,
            'offset' => $offset
        );
        $data = $this->fetchAll($criteria);
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = Source::getSourceInfo($v['table'], $v['rowid'], false, $v['module']);
                $data[$k]['url'] = $v['url'];
                $data[$k]['detail'] = $v['detail'];
            }
        }
        // 重置@Me的未读数
        $uid && UserData::model()->resetUserCount($uid, 'unread_atme', 0);
        return $data;
    }

    /**
     * 添加@Me数据
     * @param string $content @Me的相关内容
     * @param integer $row_id 资源ID
     * @param array $extraUids 额外@用户ID
     * @param array $lessUids 去除@用户ID
     * @param string $url 消息来源路径
     * @param string $detail 来源信息描述
     * @return integer 添加成功后的@ID
     */
    public function addAtme($module, $table, $content, $rowId, $extraUids = null, $lessUids = null, $url = '', $detail = '')
    {
        // 去除重复，空值与自己
        $extraUids = array_diff((array)$extraUids, array(Ibos::app()->user->uid));
        $extraUids = array_unique($extraUids);
        $extraUids = array_filter($extraUids);

        $lessUids[] = (int)Ibos::app()->user->uid;
        $lessUids = array_unique($lessUids);
        $lessUids = array_filter($lessUids);
        // 获取@用户的UID数组
        $uids = $this->getUids($content, $extraUids, $lessUids);
        // 添加@信息
        $result = $this->saveAtme($module, $table, $uids, $rowId, $url, $detail);
        return $result;
    }

    /**
     * 更新最近@的人
     * @param string $content 原创微博内容
     */
    public function updateRecentAt($content)
    {
        // 获取@用户的UID数组
        preg_match_all($this->_atRegex, $content, $matches);
        $unames = $matches[1];
        if (isset($unames[0])) {
            $curUid = Ibos::app()->user->uid;
            $map = array('select' => 'uid', 'condition' => "realname in ('" . implode("','", $unames) . "') AND uid!=" . $curUid);
            $userIds = User::model()->fetchAllSortByPk('uid', $map);
            $matchUids = Convert::getSubByKey($userIds, 'uid');
            $value = UserData::model()->fetchKeyValueByUid($curUid, 'user_recentat');
            if ($value) {
                $atData = Convert::getSubByKey($value, 'id');
                $atData && $matchUids = array_merge($matchUids, $atData);
                $matchUids = array_slice(array_unique($matchUids), 0, 10);
                foreach ($matchUids as $uid) {
                    $user = User::model()->fetchByUid($uid);
                    $udata[] = array('id' => $user['uid'], 'name' => $user['realname'], 'imgUrl' => $user['avatar_small']);
                }
                // 更新userdata表里面的最近@的人的信息
                UserData::model()->updateAll(array('value' => serialize($udata)), "`key`='user_recentat' AND uid=" . $curUid);
            } else {
                $udata = array();
                $matchUids = array_slice($matchUids, 0, 10);
                foreach ($matchUids as $uid) {
                    $user = User::model()->fetchByUid($uid);
                    $udata[] = array('id' => $user['uid'], 'name' => $user['realname']);
                }
                $data['uid'] = $curUid;
                $data['key'] = 'user_recentat';
                $data['value'] = serialize($udata);
                UserData::model()->resetUserCount($data['uid'], $data['key'], $data['value']);
            }
        }
    }

    /**
     * 获取@内容中的@用户
     * @param string $content @Me的相关内容
     * @param array $extra_uids 额外@用户UID
     * @param integer $row_id 资源ID
     * @param array $less_uids 去除@用户ID
     * @return array 用户UID数组
     */
    public function getUids($content, $extraUids = null, $lessUids = null)
    {
        // 正则匹配内容
        preg_match_all($this->_atRegex, $content, $matches);
        if (empty($matches[1])) {
            return array();
        }
        $unames = $matches[1];
        $map = "realname in ('" . implode("','", $unames) . "')";
        $ulist = User::model()->fetchAll($map);
        $matchUids = Convert::getSubByKey($ulist, 'uid');
        // 如果内容匹配中没有用户
        if (empty($matchUids) && !empty($extraUids)) {
            // 去除@用户ID
            if (!empty($lessUids)) {
                foreach ($lessUids as $k => $v) {
                    if (in_array($v, $extraUids)) {
                        unset($extraUids[$k]);
                    }
                }
            }
            return is_array($extraUids) ? $extraUids : array($extraUids);
        }
        // 如果匹配内容中存在用户
        $suid = array();
        foreach ($matchUids as $v) {
            !in_array($v, $suid) && $suid[] = (int)$v;
        }
        // 去除@用户ID
        if (!empty($lessUids)) {
            foreach ($suid as $k => $v) {
                if (in_array($v, $lessUids)) {
                    unset($suid[$k]);
                }
            }
        }
        return array_unique(array_filter(array_merge($suid, (array)$extraUids)));
    }

    /**
     * 添加@Me信息操作
     * @param string $module 模块
     * @param string $table 所在表
     * @param array $uids 用户UID数组
     * @param integer $rowId 资源ID
     * @param string $url 来源地址
     * @param string $detail 来源信息说明
     * @return integer 添加成功后的@ID
     */
    private function saveAtme($module, $table, $uids, $rowId, $url = '', $detail = '')
    {
        $self = Ibos::app()->user->uid;
        foreach ($uids as $uid) {
            // 去除自己@自己的数据
            if ($uid == $self) {
                continue;
            }
            $data[] = array(
                'module' => $module,
                'table' => $table,
                'rowid' => $rowId,
                'uid' => $uid,
                'url' => $url,
                'detail' => $detail
            );
            // 更新@Me的未读数目
            UserData::model()->updateUserCount($uid, 'unread_atme', 1);
        }
        $res = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                $res[] = $this->add($value, true);
            }
        }
        return !empty($res) ? implode(',', $res) : '';
    }

    /**
     * 删除@Me数据
     * @param string $table 表
     * @param string $content @Me的相关内容
     * @param integer $rowId 资源ID
     * @param array $extraUids 额外@用户UID
     * @return boolean 是否删除成功
     */
    public function deleteAtme($table, $content, $rowId, $extraUids = null)
    {
        $uids = $this->getUids($content, $extraUids);
        $result = $this->_deleteAtme($table, $uids, $rowId);
        return $result;
    }

    /**
     * 删除@Me信息操作
     * @param array $uids 用户UID数组
     * @param integer $row_id 资源ID
     * @return boolean 是否删除成功
     */
    private function _deleteAtme($table, $uids, $rowId)
    {
        $result = false;
        if (!empty($uids)) {
            $res = $this->deleteAll(
                array(
                    'condition' => '`table` = :table AND `rowid` = :rowid AND `uid` IN (:uid)',
                    'params' => array(
                        ':table' => $table,
                        ':rowid' => $rowId,
                        ':uid' => implode(',', $uids)
                    )
                )
            );
            $res !== false && $result = true;
        } else {
            $res = $this->deleteAll(
                array(
                    'condition' => '`table` = :table AND `rowid` = :rowid',
                    'params' => array(
                        ':table' => $table,
                        ':rowid' => $rowId
                    )
                )
            );
            $res !== false && $result = true;
        }
        return $result;
    }

}
