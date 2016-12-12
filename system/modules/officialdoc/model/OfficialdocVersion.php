<?php

/**
 * 通知模块------ doc_version表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author Ring <Ring@ibos.com.cn>
 */
/**
 * 通知模块------  doc_version表的数据层操作类，继承ICModel
 * @package application.modules.officialDoc.model
 * @version $Id: OfficialdocVersion.php 117 2013-06-07 09:29:09Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\model;

use application\core\model\Model;
use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\officialdoc\utils\Officialdoc as OfficialdocUtil;

class OfficialdocVersion extends Model
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{doc_version}}';
    }

    /**
     * 通过docid取得所有的版本数据
     * @param integer $docid
     * @return array
     */
    public function fetchAllByDocid($docid)
    {
        $versionData = $this->fetchAll('docid=:docid ORDER BY version DESC', array(':docid' => $docid));

        if (!empty($versionData)) {
            $uidArray = array();
            foreach ($versionData as $data) {
                $uidArray[] = $data['editor'];
            }
            $realnameArray = User::model()->findRealnameIndexByUid($uidArray);
            foreach ($versionData as $key => $version) {
                $versionData[$key]['uptime'] = Convert::formatDate($version['uptime'], 'u');
                $versionData[$key]['editor'] = !empty($realnameArray[$version['editor']]) ? $realnameArray[$version['editor']] : '--';
                $versionData[$key]['showVersion'] = OfficialdocUtil::changeVersion($version['version']);
            }
        }
        return $versionData;
    }

    /**
     * 根据docid插入一个历史版本
     * @param integer $docid 通知id
     * @param integer $uid 用户Id
     * @param string $nextVersion 下一个版本号
     * @return boolean 成功/失败
     */
    public function insertVersion($docid, $uid, $nextVersion)
    {
        $version = array(
            'docid' => $docid,
            'author' => $uid,
            'addtime' => TIMESTAMP,
            'version' => $nextVersion
        );
        return $this->add($version);
    }

    /**
     * 根据文章id，删除所有符合的数据
     * @param string $ids
     * @return integer
     */
    public function deleteAllByDocids($ids)
    {
        return $this->deleteAll("docid IN ($ids)");
    }

}
