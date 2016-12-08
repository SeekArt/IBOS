<?php

/**
 * 信息中心模块------分类组件文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 信息中心模块------分类组件 继承ICCategory
 * @package application.modules.article.components
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\article\core;

use application\core\components\Category;
use application\modules\article\model\Article as ArticleModel;
use application\modules\article\model\ArticleCategory as ArticleCategoryModel;

class ArticleCategory extends Category
{

    /**
     * 删除分类
     * @param integer $catid
     * @return boolean
     */
    public function delete($catid)
    {
        $clear = false;
        $ids = $this->fetchAllSubId($catid);
        $idStr = implode(',', array_unique(explode(',', trim($ids, ','))));
        if (empty($idStr)) {
            $idStr = $catid;
        } else {
            $idStr .= ',' . $catid;
        }
        //判断这些分类下是否存在文章
        $count = ArticleModel::model()->count("catid IN ($idStr)");
        if ($count) {
            return -1;
        }
        // 有关联表，获取关联表里有无关联分类id
        if (!is_null($this->_related)) {
            $count = $this->_related->count("`{$this->index}` IN ($idStr)");
            !$count && $clear = true;
        } else {
            $clear = true;
        }
        if ($clear) {
            $status = $this->_category->deleteAll("FIND_IN_SET({$this->index},'$idStr')");
            $this->afterDelete();
            return $status;
        } else {
            return false;
        }
    }

    /**
     * 文章分类 - 获取zTree ajax树数据
     * @param array $data
     * @return array
     */
    public function getAjaxCategory($data = array())
    {
        foreach ($data as $row) {
            $row['id'] = $row['catid'];
            $row['pId'] = $row['pid'];
            $row['name'] = $row['name'];
            $row['target'] = '_self';
            $row['url'] = 'javascript:;';
            $row['catid'] = $row['catid'];
            $row['open'] = true;
            $return[] = $row;
        }
        return $return;
    }

    /**
     * 文章分类树获取数据方法
     * @param string $condition 兼容
     * @return array
     */
    public function getData($condition = '')
    {
        $categoryData = ArticleCategoryModel::model()->fetchAll($condition);
        return $categoryData;
    }

}
