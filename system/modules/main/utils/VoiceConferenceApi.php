<?php

namespace application\modules\main\utils;

use application\core\utils\IBOS;

class VoiceConferenceApi{
	
	/**
     * 渲染首页视图
     * @return array
     */
    public function renderIndex() {
        $data = array(
            'lant' => IBOS::getLangSource( 'main.default' ),
            'assetUrl' => IBOS::app()->assetManager->getAssetsUrl( 'main' )
        );
        $viewAlias = 'application.modules.main.views.indexapi.voiceConference';
        $return['main/voiceConference'] = IBOS::app()->getController()->renderPartial( $viewAlias, $data, true );
        return $return;
    }

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting() {
        return array(
            'name' => 'main/voiceConference',
            'title' => IBOS::lang( '会议', 'main.default' ),
            'style' => 'in-main'
        );
    }

    /**
     * 获取最新待办 不作处理，返回0
     * @return integer
     */
    public function loadNew() {
        return intval( 0 );
    }
}

