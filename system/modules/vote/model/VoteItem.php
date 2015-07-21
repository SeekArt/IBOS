<?php

/**
 * 投票模块------投票项数据表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 投票模块------投票项数据表操作类
 * @package application.modules.vote.model
 * @version $Id: VoteItem.php 164 2013-05-29 16:55:50Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\vote\model;

use application\core\model\Model;

class VoteItem extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{vote_item}}';
    }

    /**
     * 通过投票项id或者投票项id数组修改该投票项票数+1
     * @param mixed $itemids 字符串可以为单个投票项id，也可以是多个投票项id样式为1,2,3,4,5 逗号分割,用数组则为如下样式array(1,2,3,4,5)
     * @return 影响的行数，没影响则返回0
     */
    public function updateNumber( $itemids ) {
        $result = 0;
        //如果是单个id
        if ( is_numeric( $itemids ) ) {
            $voteItem = $this->findByPk( $itemids );
            $result = $this->updateByPk( $voteItem['itemid'], array( 'number' => $voteItem['number'] + 1 ) );
            //如果是数组
        } else if ( is_array( $itemids ) ) {
            foreach ( $itemids as $itemid ) {
                $voteItem = $this->findByPk( $itemid );
                $result = $this->updateByPk( $itemid, array( 'number' => $voteItem['number'] + 1 ) );
            }
        } else {
            //逗号分割的字符串
            $itemids = explode( ',', rtrim( $itemids, ',' ) );
            foreach ( $itemids as $itemid ) {
                $voteItem = $this->findByPk( $itemid );
                $result = $this->updateByPk( $itemid, array( 'number' => $voteItem['number'] + 1 ) );
            }
        }
        return $result;
    }

}
