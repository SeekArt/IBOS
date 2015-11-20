namespace application\modules\messageboard\model;

use application\core\model\Model;

class MessageBoardReply extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{messageboard_reply}}';
    }

}
