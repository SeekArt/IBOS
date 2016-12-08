<?php

use application\core\utils\StringUtil;

?>
<div class="statistics-box">
    <span class="fsl log-title">总结得分</span>
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
                    data: [<?php echo '"' . implode('","', $series['list']) . '"'; ?>],
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
<script src='<?php echo $this->getController()->getAssetUrl(); ?>/js/report_count.js?<?php echo VERHASH; ?>'></script>
