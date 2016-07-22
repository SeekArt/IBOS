<?php

/**
 * 移动端新闻控制器文件
 *
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 移动端新闻控制器文件
 * 
 * @package application.modules.mobile.controllers
 * @author Aeolus <Aeolus@ibos.com.cn>
 * @version $Id: NewsController.php 4484 2014-10-29 02:07:53Z gzpjh $
 */

namespace application\modules\mobile\controllers;

use application\core\utils\Attach;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\article\model as model;
use application\modules\mobile\components\Article;
use application\modules\mobile\utils\Mobile;
use application\modules\user\model\User;

class NewsController extends BaseController {

	/**
	 * 默认页,获取主页面各项数据统计
	 * @return void 
	 */
	public function actionIndex() {
		$catid = Env::getRequest( 'catid' );
		$type = Env::getRequest( 'type' );
		$search = Env::getRequest( 'search' );

		//手机端特殊判断,待定 todo::
		if ( Mobile::dataType() == 'jsonp' ) {
			if ( $catid == -1 ) {
				$type = 'new';
				$catid = 0;
			}
			if ( $catid == -2 ) {
				$type = 'old';
				$catid = 0;
			}
		}
		$article = new Article();
		$articleList = $article->getList( $type, $catid, $search );
		if ( $catid == 0 ) {
			$category = model\ArticleCategory::model()->fetchAll( "pid = 0" );
		} else {
			$category = model\ArticleCategory::model()->fetchAll( "pid = {$catid}" );
		}
		$this->ajaxReturn( array( 'datas' => $articleList['datas'], 'pages' => $articleList['pages'], 'category' => $category ), Mobile::dataType() );
	}

	public function actionCategory() {
		$article = new Article();
		$this->ajaxReturn( $article->getCategory(), Mobile::dataType() );
	}

	public function actionShow() {
		$newsid = Env::getRequest( 'id' );
		$article = new Article();
		$data = $article->getNews( $newsid );
		if ( !empty( $data ) ) {
			if ( !empty( $data['attachmentid'] ) ) {
				$data["attach"] = Attach::getAttach( $data["attachmentid"] );
				$attachmentArr = explode( ",", $data['attachmentid'] );
			}
		}
		$this->ajaxReturn( $data, Mobile::dataType() );
	}

	public function actionRead() {
		$data['articleid'] = Env::getRequest( 'articleid' );
		$data['uid'] = IBOS::app()->user->uid;
		$data['addtime'] = TIMESTAMP;
		$data['readername'] = User::model()->fetchRealnameByUid( IBOS::app()->user->uid );
		$artReader = model\ArticleReader::model()->add( $data );
		if($artReader > 0){
			$message = array( 'isSuccess' => true) ;
		}else{
			$message = array( 'isSuccess' => false) ;
		}
		$this->ajaxReturn( $message, Mobile::dataType() );
	}

}
