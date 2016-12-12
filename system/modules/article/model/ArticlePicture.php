<?php

/**
 * 信息中心模块------ article_picture表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author Ring <Ring@ibos.com.cn>
 */
/**
 * 信息中心模块------  article_picture表的数据层操作类，继承ICModel
 * @package application.modules.article.model
 * @version $Id: ArticlePicture.php 117 2013-06-07 09:29:09Z gzzyb $
 * @author Ring <Ring@ibos.com.cn>
 */

namespace application\modules\article\model;

use application\core\model\Model;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\Image;
use application\core\utils\StringUtil;

class ArticlePicture extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{article_picture}}';
    }

    /**
     * 更新上传图片的所属文章
     * @param integer $pk 图片主键
     * @param integer $articleid 文章id
     * @param integer $sort 排序号
     * @return integer
     */
    public function updateArticleidAndSortByPk($pk, $articleid, $sort)
    {
        return $this->updateByPk($pk, array('articleid' => $articleid, 'sort' => $sort));
    }

    /**
     * 根据图片id字符串，删除所有图片
     * @param string $ids
     * @return integer
     */
    public function deleteAllByPictureIds($ids)
    {
        return $this->deleteAll("FIND_IN_SET(picid,'$ids')");
    }

    /**
     * 根据articleIds删除所有符合条件的数据
     * @param string $ids
     * @return integer
     */
    public function deleteAllByArticleIds($ids)
    {
        return $this->deleteAll("articleid IN ($ids)");
    }

    /**
     * 根据id获取数据，返回数组集合
     * @param integer $articleid 文章id
     * @return array
     */
    public function fetchPictureByArticleId($articleid)
    {
        return $this->fetchAll("articleid='$articleid' ORDER BY sort Desc");;
    }

    /**
     * 添加图片信息
     * @param array $attach 图片信息
     * @param integer $articleId 文章id
     */
    public function addPicture($attach, $articleId)
    {
        $sort = 0;
        $attachUrl = File::getAttachUrl() . '/';
        foreach ($attach as $value) {
            $picture = array(
                'articleid' => $articleId,
                'aid' => $value['aid'],
                'sort' => $sort,
                'addtime' => TIMESTAMP,
                'postip' => StringUtil::getSubIp(),
                'filename' => $value['filename'],
                'title' => '',
                'type' => StringUtil::getFileExt($value['filename']),
                'size' => $value['filesize'],
                'filepath' => $attachUrl . $value['attachment']
            );
            if (Ibos::app()->setting->get('setting/articlethumbenable')) {
                list($thumbWidth, $thumbHeight) = explode(',', Ibos::app()->setting->get('setting/articlethumbwh'));
                $imageInfo = Image::getImageInfo(File::fileName($picture['filepath']));
                if ($imageInfo['width'] < $thumbWidth && $imageInfo['height'] < $thumbHeight) {
                    $picture['thumb'] = 0;
                } else {
                    $sourceFileName = explode('/', $picture['filepath']);
                    $sourceFileName[count($sourceFileName) - 1] = 'thumb_' . $sourceFileName[count($sourceFileName) - 1];
                    $thumbName = implode('/', $sourceFileName);
                    if (LOCAL) {
                        Image::thumb($picture['filepath'], $thumbName, $thumbWidth, $thumbHeight);
                    } else {
                        $tempFile = File::getTempPath() . 'tmp.' . $picture['type'];
                        $orgImgname = uIbos::engine()->IO()->file()->fetchTemp(File::fileName($picture['filepath']), $picture['type']);
                        Image::thumb($orgImgname, $tempFile, $thumbWidth, $thumbHeight);
                        File::createFile($thumbName, file_get_contents($tempFile));
                    }
                    $picture['thumb'] = 1;
                }
            }
            $this->add($picture);
            $sort++;
        }
    }
}
