<?php

namespace application\modules\main\utils;

use application\core\utils\Ibos;
use application\modules\main\model\Setting;

class VoiceConferenceApi
{

    /**
     * 渲染首页视图
     * @return array
     */
    public function renderIndex()
    {
        $data = array(
            'lant' => Ibos::getLangSource('main.default'),
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('main')
        );
        $viewAlias = 'application.modules.main.views.indexapi.voiceConference';
        $return['main/voiceConference'] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
        return $return;
    }

    /**
     * 是否强制关闭该模块，会议在这里跟云服务相关，所以如果没有开通云服务，会议也不显示
     * @return type
     */
    public function close()
    {
        $isOpenCloud = Setting::model()->getIbosCloudIsOpen();
        return $isOpenCloud ? false : true;
    }

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting()
    {
        return array(
            'name' => 'main/voiceConference',
            'title' => Ibos::lang('会议', 'main.default'),
            'style' => 'in-main'
        );
    }

    /**
     * 获取最新待办 不作处理，返回0
     * @return integer
     */
    public function loadNew()
    {
        return intval(0);
    }

}
