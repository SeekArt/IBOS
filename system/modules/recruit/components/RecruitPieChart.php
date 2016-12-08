<?php

namespace application\modules\recruit\components;

class RecruitPieChart extends RecruitChart
{

    public function getSeries()
    {
        $datas = $this->getCounter()->getCount();
        return $datas;
    }

}
