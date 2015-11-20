namespace application\modules\messageboard\model;

use application\core\model\Model;

class MessageBoard extends Model {

    public static function model( $className = __CLASS__ ) {
        return parent::model( $className );
    }

    public function tableName() {
        return '{{messageboard}}';
    }

}