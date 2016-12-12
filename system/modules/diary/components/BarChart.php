<?php

namespace application\modules\diary\components;

class BarChart extends Chart
{

    // 图章数的最大值
    private $_max = 0;

    public function getSeries()
    {
        $datas = $this->getCounter()->getCount();
        $list = $max = array();
        foreach ($datas as $uid => $series) {
            foreach ($series['list'] as $stampId => $stamp) {
                $max[] = $stamp['count'];
                if (isset($list[$stampId])) {
                    $list[$stampId]['count'] .= ',' . $stamp['count'];
                } else {
                    $list[$stampId] = array('name' => $stamp['name'], 'count' => $stamp['count']);
                }
            }
        }
        $this->setMax(max($max));
        return $list;
    }

    public function setMax($max)
    {
        $this->_max = $max;
    }

    public function getMax()
    {
        return $this->_max;
    }


}
