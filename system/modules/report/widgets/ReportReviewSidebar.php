<?php

namespace application\modules\report\widgets;

use application\modules\report\utils\Report;
use CWidget;

class ReportReviewSidebar extends CWidget
{

    const VIEW = 'application.modules.report.views.widget.reviewSidebar';

    /**
     *
     * @return type
     */
    public function run()
    {
        $data = array(
            'config' => Report::getSetting(),
            'id' => $this->getController()->getId(),
        );
        $this->render(self::VIEW, $data);
    }

}
