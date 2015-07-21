<?php

/**
 * 投票模块------vote_item_count表操作类文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 投票模块------vote_item_count表操作类
 * @package application.modules.vote.model
 * @version $Id: VoteItemCount.php 164 2013-05-29 16:55:50Z gzwwb $
 * @author gzwwb <gzwwb@ibos.com.cn>
 * @deprecated
 */

namespace application\modules\vote\model;

use application\core\model\Model;

class VoteItemCount extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{vote_item_count}}';
    }

}
