<?php

/**
 * 公文模块------ doc表的数据层操作文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

/**
 * 公文模块------  doc表的数据层操作类，继承ICModel
 * @package application.modules.officialDoc.model
 * @version $Id: RcType.php 4064 2014-09-03 09:13:16Z zhangrong $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\officialdoc\model;

use application\core\model\Model;

class RcType extends Model {
	
	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{rc_type}}';
	}
}
