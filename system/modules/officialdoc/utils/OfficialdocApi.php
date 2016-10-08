<?php

namespace application\modules\officialdoc\utils;

use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\modules\message\utils\MessageApi;
use application\modules\officialdoc\model\Officialdoc;
use application\modules\officialdoc\model\OfficialdocReader;
use application\modules\user\model\User;

class OfficialdocApi extends MessageApi {

    /**
     * 渲染首页视图
     * @return void
     */
    public function renderIndex() {
        $data = array(
            'docs' => $this->loadNewDoc(),
            'lang' => Ibos::getLangSource( 'officialdoc.default' ),
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl( 'officialdoc' ),
        );
        $viewAlias = 'application.modules.officialdoc.views.indexapi.officialdoc';
        $return['officialdoc/officialdoc'] = Ibos::app()->getController()->renderPartial( $viewAlias, $data, true );
        return $return;
    }

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting() {
        return array(
            'name' => 'officialdoc/officialdoc',
            'title' => Ibos::lang( 'Officialdoc', 'officialdoc.default' ),
            'style' => 'in-officialdoc'
        );
    }

    /**
     * 获取最新公文条数
     * @return integer
     */
    public function loadNew() {
        $uid = Ibos::app()->user->uid;
        $allDeptId = Ibos::app()->user->alldeptid . '';
        $allPosId = Ibos::app()->user->allposid . '';
        $condition = " ( ((deptid='alldept' OR FIND_IN_SET('{$allDeptId}',deptid) OR FIND_IN_SET('{$allPosId}',positionid) OR FIND_IN_SET('{$uid}',uid)) OR (deptid='' AND positionid='' AND uid='') OR (author='{$uid}') OR (approver='{$uid}')) ) AND `status`='1'";
        $docs = Officialdoc::model()->fetchAll( $condition );
		$docIds = Convert::getSubByKey( $docs, 'docid' );
		$readedIds = OfficialdocReader::model()->fetchAll( sprintf( "uid=%d", $uid ) );
		$rDocIds = Convert::getSubByKey( $readedIds, 'docid' );
		$count = count( array_diff($docIds, $rDocIds) );
        return intval( $count );
    }

    /**
     * 加载指定$num条的公文
     * @param integer $num
     * @return array
     */
    private function loadNewDoc( $num = 3 ) {
        $uid = Ibos::app()->user->uid;
        $allDeptId = Ibos::app()->user->alldeptid . '';
        $allPosId = Ibos::app()->user->allposid . '';
        //$condition = " ( ((deptid='alldept' OR FIND_IN_SET('{$allDeptId}',deptid) OR FIND_IN_SET('{$allPosId}',positionid) OR FIND_IN_SET('{$uid}',uid)) OR (deptid='' AND positionid='' AND uid='') OR (author='{$uid}') OR (approver='{$uid}')) ) AND `status`='1'";
        $condition = "((deptid='alldept' OR FIND_IN_SET('{$allDeptId}',deptid) OR FIND_IN_SET('{$allPosId}',positionid) OR FIND_IN_SET('{$uid}',uid)) OR (deptid='' AND positionid='' AND uid='')) AND `status`='1'";
        $criteria = array(
            'select' => 'docid,subject,author,addtime',
            'condition' => $condition,
            'order' => '`istop` DESC, `addtime` DESC',
            'offset' => 0
        );
        $docs = Officialdoc::model()->fetchAll( $criteria );
        $unSign = array();
        $signed = array();
        if ( !empty( $docs ) ) {
            foreach ( $docs as &$doc ) {
                $doc['author'] = User::model()->fetchRealnameByUid( $doc['author'] );
                $doc['sign'] = OfficialdocReader::model()->fetchByAttributes( array( 'docid' => $doc['docid'], 'uid' => $uid ) );
                $doc['isSign'] = empty( $doc['sign'] ) ? 0 : $doc['sign']['issign'];
                if ( $doc['isSign'] == 0 ) {
                    $unSign[] = $doc;
                } elseif ( $doc['isSign'] == 1 ) {
                    $signed[] = $doc;
                }
            }
            // 作用是排序，把未签收的放到前面，且不打乱时间顺序
            $docs = array_merge( $unSign, $signed );
        }
        if ( count( $docs ) > $num ) {  // 取$num条数据
            $docs = array_slice( $docs, 0, $num );
        }
        return $docs;
    }

}
