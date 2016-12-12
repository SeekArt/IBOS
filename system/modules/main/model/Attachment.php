<?php

namespace application\modules\main\model;

use application\core\model\Model;
use application\core\utils\Ibos;

class Attachment extends Model
{

    private $_tableIds = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{attachment}}';
    }

    /**
     * 获取所有附件大小
     * @return type
     */
    public function getTotalFilesize()
    {
        $attachSize = 0;
        foreach ($this->_tableIds as $id) {
            $table = 'attachment_' . $id;
            $attachSize += Ibos::app()->db->createCommand()
                ->select('SUM(filesize)')
                ->from(sprintf('{{%s}}', $table))
                ->queryScalar();
        }
        return $attachSize;
    }

    public function fetchAllByKeywordFileName($keyword = '', $conditions = '', $offset = null, $length = null, $order = SORT_DESC, $sort = 'dateline')
    {
        $data = array();
        $return = array();
        if (!empty($keyword)) {
            $conditions .= ' AND `filename` LIKE \'%' . $keyword . '%\'';
            $sortRefer = array();
            foreach ($this->_tableIds as $tableId) {
                $subData = Ibos::app()->db->createCommand()->select('*')
                    ->from("{{attachment_{$tableId}}}")
                    ->where($conditions)
                    ->queryAll();
                foreach ($subData as $row) {
                    $sortRefer[] = $row[$sort];
                    $data[] = $row;
                }
            }
            array_multisort($sortRefer, $order, $data);
            if (!is_null($offset) && !is_null($length)) {
                $data = array_slice($data, $offset, $length, false);
            }
            foreach ($data as $value) {
                $return[$value['aid']] = $value;
            }
        }
        return $return;
    }

    /**
     * 更新下载统计
     * @param mixed $aid 要更新的附件id
     * @param integer $count
     * @return integer the number of rows being updated
     */
    public function updateDownload($aid, $count = 1)
    {
        $aid = is_array($aid) ? implode(',', $aid) : trim($aid, ',');
        return $this->updateCounters(array('downloads' => "{$count}"), "aid IN ('{$aid}')");
    }

}
