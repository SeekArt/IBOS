<?php

namespace application\modules\recruit\utils;

use application\core\utils\IBOS;
use application\modules\recruit\model\Resume;

class RecruitApi {

    private $_indexTab = array( 'TalentManagement', 'InterviewManagement' );

    /**
     * 渲染首页视图
     * @return type
     */
    public function renderIndex() {
        $data = array(
            'lant' => IBOS::getLangSource( 'recruit.default' ),
            'assetUrl' => IBOS::app()->assetManager->getAssetUrl( 'recruit' )
        );
        foreach ( $this->_indexTab as $tab ) {
            $data['recruits'] = $this->loadRecruit( $tab );
            $data['tab'] = $tab;
        }
        $viewAlias = 'application.modules.recruit.views.indexapi.recruit';
        $return['recruit'] = IBOS::app()->getController()->renderPartial( $viewAlias, $data, true );
        return $return;
    }

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting() {
        return array(
            'name' => 'recruit',
            'title' => IBOS::lang( 'Recruitment management', 'recruit.default' ),
            'style' => 'in-recruit'
        );
    }

    private function loadRecruit( $type = 'TalentManagement', $num = 4 ) {
        $uid = IBOS::app()->user->uid;
        switch ( $type ) {
            case 'TalentManagement':
                $status = 4;
                break;
            case 'InterviewManagement':
                $status = 1;
                break;
            default :
                return false;
        }
        $criteria = array(
            'select' => 'resumeid,input,suitableposition,uptime,status',
            'condition' => "`status`=$status",
            'order' => '`uptime` DESC',
            'offset' => 0,
            'limit' => $num
        );
        $resume = Resume::model()->findAll( $criteria );
        var_dump( $resume );
        die;
    }

}
