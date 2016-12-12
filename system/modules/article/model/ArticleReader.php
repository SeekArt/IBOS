<?php

/**
 * 信息中心模块------ article_reader表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 信息中心模块------  article_reader表的数据层操作类，继承ICModel
 * @package application.modules.article.model
 * @version $Id: ArticleReader.php 117 2013-06-07 09:29:09Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\article\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\modules\department\utils\Department;
use application\modules\user\model\User;

class ArticleReader extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{article_reader}}';
    }

    /**
     * 通过文章id取得一条读者数据
     * @param type $articleid
     */
    public function fetchReaderByArticleId($articleid)
    {
        $condition = 'articleid=:articleid';
        $params = array(':articleid' => $articleid);
        $reader = $this->fetch($condition, $params);
        return $reader;
    }

    /**
     * 判断用户是否已经读过这篇文章
     * @param type $articleid
     */
    public function checkIsRead($articleid, $uid)
    {
        $result = false;
        $condition = 'articleid=:articleid AND uid=:uid';
        $params = array(':articleid' => $articleid, ':uid' => $uid);
        $count = $this->count($condition, $params);
        if ($count) {
            $result = true;
        }
        return $result;
    }

    /**
     * 获取某个用户所有已读新闻id
     * @param integer $uid 用户id
     * @return array
     */
    public function fetchReadArtIdsByUid($uid)
    {
        $read = $this->fetchAll("uid = {$uid}");
        $readArtIds = Convert::getSubByKey($read, 'articleid');
        return $readArtIds;
    }

    /**
     * 添加阅读者信息
     * @param integer $articleid 文章Id
     * @param integer $uid 用户Id
     * @return boolean 添加成功或失败
     */
    public function addReader($articleid, $uid)
    {
        if ($this->checkIsRead($articleid, $uid) == false) {
            $user = User::model()->fetchByUid($uid);
            $articleReader = array(
                'articleid' => $articleid,
                'uid' => $uid,
                'addtime' => TIMESTAMP,
                'readername' => $user['realname']
            );
            return $this->add($articleReader);
        }
    }

    /**
     * 通过uid取得所有有关的articleids
     * <pre>
     *        array(1=>15,2=>25...)
     * </pre>
     * @param type $uid
     * @return array
     */
    public function fetchArticleidsByUid($uid)
    {
        $result = array();
        $readerList = $this->fetchAll('uid=:uid', array(':uid' => $uid));
        if (!empty($readerList)) {
            foreach ($readerList as $reader) {
                $result[$reader['readerid']] = $reader['articleid'];
            }
        }
        return $result;
    }

    /**
     * 取得一篇文章中一个部门的阅读人员名单
     * @param integer $articleId 新闻id
     * @param integer $deptId 部门id
     * @return string 逗号隔开的名单
     */
    public function fetchArticleReaderByDeptid($articleId, $deptId)
    {
        $result = array();
        $data = $this->fetchAll('articleid=:articleid', array(':articleid' => $articleId));
        if (!empty($data)) {
            foreach ($data as $k => $reader) {
                $user = User::model()->fetchByUid($reader['uid']);
                if (!empty($user)) {
                    $did = $user['deptid'];
                    if ($did == $deptId) {
                        $result[] = $reader['readername'];
                    }
                } else {
                    unset($data[$k]);
                }
            }
            $temp = implode(',', $result);
            $result = $temp;
        }
        return $result;
    }

    //下面的这些方法都是为兼容h5新闻接口而这样写
    public function getReader($articleid)
    {
        $readerData = ArticleReader::model()->fetchAll('articleid=:articleid', array(':articleid' => $articleid));
        $departments = Department::loadDepartment();
        $res = $tempDeptids = $users = array();
        foreach ($readerData as $reader) {
            $user = User::model()->fetchByUid($reader['uid']);
            $users[] = $user;
            $deptid = $user['deptid'];
            $tempDeptids[] = $user['deptid'];
        }
        $deptids = array_unique($tempDeptids);
        foreach ($deptids as $deptid) {
            $deptName = isset($departments[$deptid]['deptname']) ? $departments[$deptid]['deptname'] : '--';
            foreach ($users as $k => $user) {
                if ($user['deptid'] == $deptid) {
                    $res[$deptName][] = $user;
                    unset($users[$k]);
                }
            }
        }

        return $res;
    }
}

