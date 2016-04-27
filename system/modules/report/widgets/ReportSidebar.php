<?php

namespace application\modules\report\widgets;

use application\core\utils\IBOS;

class ReportSidebar extends StatReportBase {

    const VIEW = 'application.modules.report.views.widget.sidebar';

    public function run() {
        $data = array(
            'lang' => IBOS::getLangSource( 'report.default' ),
            'id' => $this->getController()->getId()
        );
        $this->render( self::VIEW, $data );
    }

}
