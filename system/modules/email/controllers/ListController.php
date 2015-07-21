<?php

namespace application\modules\email\controllers;

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Page;
use application\modules\email\model\Email;
use application\modules\email\utils\Email as EmailUtil;
use application\modules\user\model\User;

class ListController extends BaseController {

    public function init() {
        parent::init();
        // 文件夹ID
        $this->fid = intval( Env::getRequest( 'fid' ) );
        // 外部邮箱ID
        $this->webId = intval( Env::getRequest( 'webid' ) );
        // 分类存档ID
        $this->archiveId = intval( Env::getRequest( 'archiveid' ) );
        // 子动作
        $this->subOp = Env::getRequest( 'subop' ) . '';
        // 设置列表显示条数
        if ( isset( $_GET['pagesize'] ) ) {
            $this->setListPageSize( $_GET['pagesize'] );
        }
    }

    /**
     * 列表页
     * @return void 
     */
    public function actionIndex() {
        $op = Env::getRequest( 'op' );
        $opList = array(
            'inbox', 'todo', 'draft',
            'send', 'folder', 'archive',
            'del'
        );
        if ( $this->allowWebMail ) {
            $opList[] = 'web';
        }
        if ( !in_array( $op, $opList ) ) {
            $op = 'inbox';
        }
        $data = $this->getListData( $op );
        $this->setPageTitle( IBOS::lang( 'Email center' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Personal Office' ) ),
            array( 'name' => IBOS::lang( 'Email center' ), 'url' => $this->createUrl( 'list/index' ) ),
        ) );
        $this->render( 'index', $data );
    }

    /**
     * 查询
     */
    public function actionSearch() {
        $condition = array();
        if ( IBOS::app()->request->getIsPostRequest() ) {
            $search = $_POST['search'];
            $condition = EmailUtil::mergeSearchCondition( $search, $this->uid );
            $conditionStr = base64_encode( serialize( $condition ) );
        } else {
            $conditionStr = Env::getRequest( 'condition' );
            $condition = unserialize( base64_decode( $conditionStr ) );
        }
        if ( empty( $condition ) ) {
            $this->error( IBOS::lang( 'Request tainting', 'error' ), $this->createUrl( 'list/index' ) );
        }
        $emailData = Email::model()->fetchAllByArchiveIds( '*', $condition['condition'], $condition['archiveId'], array( 'e', 'eb' ), null, null, SORT_DESC, 'emailid' );
        $count = count( $emailData );
        $pages = Page::create( $count, $this->getListPageSize(), false );
        $pages->params = array( 'condition' => $conditionStr );
        $list = array_slice( $emailData, $pages->getOffset(), $pages->getLimit(), false );
        foreach ( $list as $index => &$mail ) {
            $mail['fromuser'] = $mail['fromid'] ? User::model()->fetchRealnameByUid( $mail['fromid'] ) : "";
        }
        $data = array(
            'list' => $list,
            'pages' => $pages,
            'condition' => $conditionStr,
            'folders' => $this->folders
        );
        $this->setPageTitle( IBOS::lang( 'Search result' ) );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => IBOS::lang( 'Personal Office' ) ),
            array( 'name' => IBOS::lang( 'Email center' ), 'url' => $this->createUrl( 'list/index' ) ),
            array( 'name' => IBOS::lang( 'Search result' ) )
        ) );
        $this->render( 'search', $data );
    }

    /**
     * 邮件箱列表
     * @return void
     */
    private function getListData( $operation ) {
        $data['op'] = $operation;
        $data['fid'] = $this->fid;
        $data['webId'] = $this->webId;
        $data['folders'] = $this->folders;
        $data['archiveId'] = $this->archiveId;
        $data['allowRecall'] = IBOS::app()->setting->get( 'setting/emailrecall' );
        $uid = $this->uid;
        // 归档列表要确认子动作
        if ( $operation == 'archive' ) {
            if ( !in_array( $this->subOp, array( 'in', 'send' ) ) ) {
                $this->subOp = 'in';
            }
        }
        $data['subOp'] = $this->subOp;
        $count = Email::model()->countByListParam( $operation, $uid, $data['fid'], $data['archiveId'], $data['subOp'] );
        $pages = Page::create( $count, $this->getListPageSize() );
        $data['pages'] = $pages;
        $data['unreadCount'] = Email::model()->countUnreadByListParam( $operation, $uid, $data['fid'], $data['archiveId'], $data['subOp'] );
        $data['list'] = Email::model()->fetchAllByListParam( $operation, $uid, $data['fid'], $data['archiveId'], $pages->getLimit(), $pages->getOffset(), $data['subOp'] );
        return $data;
    }

}
