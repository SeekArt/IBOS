<?php

namespace application\modules\file\utils;

use application\core\utils\Ibos;

class FileApi
{

    /**
     * 渲染首页视图
     * @return array
     */
    public function renderIndex()
    {
        $data = array(
            'lant' => Ibos::getLangSource('file.default'),
            'assetUrl' => Ibos::app()->assetManager->getAssetsUrl('file')
        );
        $viewAlias = 'application.modules.file.views.indexapi.file';
        $return['file/file'] = Ibos::app()->getController()->renderPartial($viewAlias, $data, true);
        return $return;
    }

    /**
     * 提供给接口的模块首页配置方法
     * @return array
     */
    public function loadSetting()
    {
        return array(
            'name' => 'file/file',
            'title' => Ibos::lang('Folder', 'file.default'),
            'style' => 'in-file'
        );
    }

    /**
     * 获取最新文件 不作处理，返回0
     * @return integer
     */
    public function loadNew()
    {
        return intval(0);
    }

}

