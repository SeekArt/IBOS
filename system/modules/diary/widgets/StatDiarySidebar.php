<?php

namespace application\modules\diary\widgets;

use CWidget;

class StatDiarySidebar extends CWidget
{

    private $_hasSub;
    private $_fromController = 'review';

    const VIEW = 'application.modules.diary.views.widget.sidebar';

    public function setHasSub($hasSub)
    {
        $this->_hasSub = $hasSub;
    }

    public function getHasSub()
    {
        return $this->_hasSub;
    }

    public function setFromController($fromController)
    {
        $this->_fromController = $fromController;
    }

    public function getFromController()
    {
        return $this->_fromController;
    }

    /**
     *
     * @return type
     */
    public function run()
    {
        $id = $this->getController()->getId();
        $action = $this->getController()->getAction()->getId();
        $data = array(
            'inPersonal' => $id == 'stats' && $action == 'personal',
            'inReview' => $id == 'stats' && $action == 'review',
            'hasSub' => $this->getHasSub(),
            'fromController' => $this->getFromController()
        );
        $this->render(self::VIEW, $data);
    }

}
