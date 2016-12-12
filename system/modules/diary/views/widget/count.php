<?php

use application\core\utils\StringUtil;
use application\modules\diary\utils\Diary;

?>
<div class="statistics-box">
    <span class="fsl log-title">上交时间</span>
    <div class="statistics-area">
        <div id="hand_in_time" class="statistics-content">
        </div>
    </div>
</div>
<div class="statistics-box">
    <span class="fsl log-title">日志得分</span>
    <div class="statistics-area">
        <div id="log_score" class="statistics-content">
        </div>
    </div>
</div>
<div class="statistics-box">
    <span class="fsl log-title">图章分布</span>
    <div class="statistics-area">
        <div id="seal_distribute" class="statistics-content">
        </div>
    </div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/echarts/echarts.min.js?<?php echo VERHASH; ?>'></script>
<script>
    //上交时间图表的初始化
    <?php
    $offWorkTimes = '';
    $offTime = Diary::getOffTime();
    for ($i = 1; $i <= count($time->getXaxis()); $i++) {
        $offWorkTimes .= $offTime . ',';
    }
    ?>

    //图表统计中鼠标悬停切换时，图表样式的设置
    var placeHoledStyle = {
        normal: {
            borderColor: 'rgba(0,0,0,0)',
            color: 'rgba(0,0,0,0)'
        },
        emphasis: {
            borderColor: 'rgba(0,0,0,0)',
            color: 'rgba(0,0,0,0)'
        }
    };
    var dataStyle = {
        normal: {
            label: {
                show: true,
                position: 'inside',
                formatter: '{c}'
            }
        }
    };


    Ibos.app.s({
        isPersonal: <?php if ($score->getIsPersonal()) {
        echo 1;
    } else {
        echo 0;
    }; ?>,
        // 上交时间
        time: {
            userName: [<?php echo $time->getUserName(); ?>],
            xAxis: [<?php foreach ( $time->getXaxis() as $date ): ?>'<?php echo $date; ?>',<?php endforeach; ?>],
            series: [
                <?php foreach ($time->getSeries() as $series): ?>
                {
                    name: '<?php echo $series['name']; ?>',
                    type: 'line',
                    symbol: 'circle',
                    symbolSize: 3,
                    lineStyle: {        // 系列级个性化折线样式
                        normal: {
                            width: 2,
                        },
                        emphasis: {     // 系列级个性化折线样式
                            width: 4,
                        }
                    },
                    data: [<?php foreach ($series['list'] as $data): ?><?php if(!$data):?>{
                        value: <?php echo $data ?>,
                        symbol: 'emptypin'
                    },<?php else: ?><?php echo $data; ?>,<?php endif; ?><?php endforeach; ?>]
                },<?php endforeach; ?>

                //下班时间，作为分割线，data数组里面的数值统一都为日程后台设置的下班时间
                {
                    name: '下班时间',
                    type: 'line',
                    symbol: 'none',
                    lineStyle: {        // 系列级个性化折线样式
                        normal: {
                            width: 2,
                            type: 'dashed'
                        }
                    },
                    itemStyle: {
                        normal: {
                            color: '#e26f50'
                        }
                    },
                    //后台输出日程后台设置的下班时间作为一条额外数据
                    //data:[18.00, 18.00, 18.00, 18.00, 18.00, 18.00, 18.00]
                    data: [<?php echo trim($offWorkTimes, ','); ?>]
                }
            ]
        },
        // 日志得分
        score: {
            userName: [<?php echo $score->getUserName(); ?>],
            xAxis: [<?php foreach ( $score->getXaxis() as $date ): ?>'<?php echo $date; ?>',<?php endforeach; ?>],
            series: [
                <?php foreach ($score->getSeries() as $series): ?>
                {
                    name: '<?php echo $series['name']; ?>',
                    type: 'bar',
                    symbol: 'circle',
                    itemStyle: {
                        normal: {
                            lineStyle: {        // 系列级个性化折线样式
                                width: 2,
                            }
                        },
                        emphasis: {
                            lineStyle: {        // 系列级个性化折线样式
                                width: 4,
                            }
                        }
                    },
                    data: [<?php echo implode(',', $series['list']); ?>],
                },
                <?php endforeach; ?>
            ]
        },
        // 图章分布
        seal: {
            stampName: [<?php echo $stamp->getStampName(); ?>],
            userName: [<?php echo $stamp->getUserName(); ?>],
            series:
            <?php if( $stamp->getIsPersonal() ): ?>
                [
                    {
                        type: 'bar',
                        barCategoryGap: '50%',
                        itemStyle: {
                            normal: {
                                color: '#a9d36a',
                                label: {
                                    show: true, position: 'inside'
                                }
                            }
                        },
                        data: [<?php foreach ($stamp->getSeries() as $series): ?>'<?php echo $series['count']; ?>',<?php endforeach; ?>]
                    }
                ]
                <?php else: ?>
                    [
                <?php foreach ($stamp->getSeries() as $series): ?>
                <?php $count = explode(',', trim($series['count'], ',')); ?>
                    {
                        name: '<?php echo $series['name']; ?>',
                        type: 'bar',
                        stack: '总量',
                        itemStyle: dataStyle,
                        label: {
                            normal: {
                                position: 'inside'
                            }
                        },
                        data: [<?php echo StringUtil::iImplode($count); ?>]
                    },
                    {
                        name: '<?php echo $series['name']; ?>',
                        type: 'bar',
                        stack: '总量',
                        itemStyle: placeHoledStyle,
                        label: {
                            normal: {
                                position: 'inside'
                            }
                        },
                        data: [<?php foreach ( $count as $number ): ?><?php echo $stamp->getMax() - $number; ?>,<?php endforeach; ?>]
                    },
                <?php endforeach; ?>
                    ]
            <?php endif; ?>
        }
    });
</script>
<script src='<?php echo $this->getController()->getAssetUrl(); ?>/js/diary_count.js?<?php echo VERHASH; ?>'></script>
