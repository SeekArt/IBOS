<?php

/**
 * 信息中心模块------ article_category表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author Ring <Ring@ibos.com.cn>
 */
/**
 * 信息中心模块------  article_category表的数据层操作类，继承ICModel
 * @package application.modules.article.model
 * @version $Id: ArticleCategory.php 8703 2016-10-24 02:02:23Z php_lwd $
 * @author Ring <Ring@ibos.com.cn>
 */

namespace application\modules\article\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\modules\dashboard\model\Approval;

class ArticleCategory extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{article_category}}';
    }

    /**
     * 根据父类Id，查找其所有的子类Id，返回catid的数组
     * @param integer $pid 父类Id
     * @return array
     */
    public function fetchAllSubCatidByPid($pid)
    {
        $result = array();
        $datas = $this->fetchAll(array('select' => 'catid', 'condition' => "pid=$pid", 'order' => 'sort ASC'));
        foreach ($datas as $data) {
            $result[] = $data['catid'];
        }
        return $result;
    }

    /**
     * 查询出所有的字段catid、pid的数据，处理成一个方便递归调用的数组，并返回<br/>
     * 返回的数组示例:
     * <pre>
     * array(
     *        [0] = array(
     *            [67] = array('catid' = 89, 'pid' = 67)
     *        ),
     *        ...
     * )
     * </pre>
     * @return array
     */
    public function fetchAllCatidAndPid()
    {
        $result = array();
        $datas = $this->fetchAll(array('order' => 'pid'));
        foreach ($datas as $data) {
            $pid = $data['pid'];
            $array = array();
            $array[$pid] = array('catid' => $data['catid'], 'pid' => $pid);
            array_push($result, $array);
        }
        return $result;
    }

    /**
     * 根据分类id获取分类的名字 原名(getcatname)
     * @param integer $catid
     * @return string
     */
    public function fetchCateNameByCatid($catid)
    {
        $data = $this->fetch(array('select' => 'name', 'condition' => "catid='$catid'"));
        return !empty($data) ? $data['name'] : '';
    }

    /**
     * 找出指定catid下的所有子类catid。返回一个字符串 (原名：querycat)
     * @param integer $catid default=0;
     * @return string
     * 标记不用
     */
    public function fetchSubCatidByCatid($catid = 0)
    {
        $categoryAllDatas = $this->fetchAllCatidAndPid();
        $str = $this->fetchCatidByPid($categoryAllDatas, $catid);
        return $str;
    }

    /**
     * 根据pid从一个数组中筛选出它的所有子类catid，返回逗号分割的字符串
     * @param type $categoryData
     * @param type $pid
     * @param type $flag 标识符 是否附加原来的pid,默认不附加
     * @return array
     */
    public function fetchCatidByPid($pid, $flag = false)
    {
        $categoryAllData = $this->fetchAllCatidAndPid();
        $list = array();
        foreach ($categoryAllData as $key => $value) {
            foreach ($value as $cate) {
                $list[$key]['catid'] = $cate['catid'];
                $list[$key]['pid'] = $cate['pid'];
            }
        }
        $catids = '';
        $result = $this->fetchCategoryList($list, $pid, 0);

        foreach ($result as $value) {
            $catids .= $value['catid'] . ',';
        }
        if ($flag) {
            return trim($pid . ',' . $catids, ',');
        } else {
            return trim($catids);
        }
    }

    private function fetchCategoryList($list, $pid, $level)
    {
        static $result = array();
        foreach ($list as $category) {
            if ($category['pid'] == $pid) {
                $category['level'] = $level;
                $result[] = $category;
                array_merge($result, $this->fetchCategoryList($list, $category['catid'], $level + 1));
            }
        }
        return $result;
    }

    /**
     * 根据catid判断该分类是否存在子类
     * @param type $catid
     */
    public function checkHaveChild($catid)
    {
        $count = $this->count('pid=:pid', array(':pid' => $catid));
        return $count > 0 ? true : false;
    }

    /**
     * 获取uid在某个分类下是否有直接发布权限
     * @param integer $catid 分类id
     * @param integer $uid 用户id
     * @return boolean
     */
    public function checkIsAllowPublish($catid, $uid)
    {
        $allowPublish = 0;
        if (empty($catid)) {
            $catid = 1;
        }
        $category = $this->fetchByPk($catid);
        if (empty($category)) {
            return $allowPublish;
        } elseif ($category['aid'] == 0) {
            return 1;
        }
        $approval = Approval::model()->fetchByPk($category['aid']);
        if (!empty($catid) && !empty($category)) {
            if ($category['aid'] == 0) {
                // 没有设置审批流程的分类，可以直接分布
                $allowPublish = 1;
            } elseif (!empty($approval) && in_array($uid, explode(',', $approval['free']))) {
                // 这个审批流程的免审人有发布权限
                $allowPublish = 1;
            }
        }
        return $allowPublish;
    }

    /**
     * 获得所有的分类中存在的审批流程id
     * @return array
     */
    public function fetchAids()
    {
        $categorys = $this->fetchAll();
        $aids = Convert::getSubByKey($categorys, 'aid');
        $aids = array_unique($aids);
        $aids = array_filter($aids);
        return $aids;
    }

    /**
     * 获取某个分类的审批流程id
     * @param integer $catid 分类id
     * @return integer 审批流程id，没有就返回0
     */
    public function fetchAidByCatid($catid)
    {
        $aid = 0;
        if (!empty($catid)) {
            $record = $this->fetchByPk($catid);
            $aid = $record['aid'];
        }
        return $aid;
    }

    /**
     * 判断某个uid是否是某个分类的其中一名审批人
     * @param integer $catid 分类id
     * @param integer $uid 用户id
     * @return boolean
     */
    public function checkIsApproval($catid, $uid)
    {
        $aid = $this->fetchAidByCatid($catid);
        $approvalUids = Approval::model()->fetchApprovalUidsByIds($aid);
        $res = in_array($uid, $approvalUids);
        return $res;
    }

    /**
     * 获取某个uid所有审批的分类
     * @param type $uid
     * @return type
     */
    public function fetchAllApprovalCatidByUid($uid)
    {
        $res = array();
        $categorys = $this->fetchAll();
        foreach ($categorys as $cate) {
            if ($this->checkIsApproval($cate['catid'], $uid)) {
                $res[] = $cate['catid'];
            }
        }
        return $res;
    }

    /**
     * 获取某个分类下是否有审批流程(1: 无审批流程, 0:有审批流程)
     * @param integer $catid
     * @return integer
     */
    public function fetchIsProcessByCatid($catid)
    {
        $aitVerify = 0;
        if (empty($catid)) {
            $catid = 1;
        }
        $category = $this->fetchByPk($catid);
        if (empty($category)) {
            $aitVerify = 0;
        } elseif ($category['aid'] == 0) {
            $aitVerify = 1;
        }
        return $aitVerify;
    }

    /*
     * 根据分类ID获得对应的审核流程层数
     * @param integer $catid 分类ID，分类ID为0，则没有审核步骤，否则有审核步骤
     */
    public function getLevelByCatid($catid)
    {
        $record = $this->fetchByPk($catid);
        $aid = $record['aid'];
        if ($aid != 0) {
            $approvel = Approval::model()->fetchByPk($aid);
            return $approvel['level'];
        } else {
            return 0;
        }
    }
}
