namespace application\modules\messageboard\controllers;

use application\core\controllers\Controller;

class ListController extends Controller {


    /**
     * 列表页
     * @return void 
     */
    public function actionIndex() {
        // 创建一个查询标准
        $criteria = array(
            'order' => 'time DESC',
            'condition' => 'status > 0'
        );
        // 获取留言条数
        $count = MessageBoard::model()->count( $criteria );
        // 调用分页组件
        $pages = PageUtil::create( $count );
        $criteria['limit'] = $pages->getLimit();
        $criteria['offset'] = $pages->getOffset();
        $data = array(
            'page' => $pages,
            'list' => MessageBoard::model()->fetchAll( $criteria ) // 查询数据
        );
        $this->setPageTitle( '留言版' );
        $this->setPageState( 'breadCrumbs', array(
            array( 'name' => '测试' ),
            array( 'name' => '留言板', 'url' => $this->createUrl( 'messageboard/list' ) ),
        ) );
        $this->render( 'index', $data );
    }

}