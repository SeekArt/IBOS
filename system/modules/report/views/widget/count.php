<?php

use application\core\utils\String;

?>
<div class="statistics-box">
	<span class="fsl log-title">总结得分</span>
	<div class="statistics-area">
		<div id="log_score" class="statistics-content">
			<div class="statistics-pic"></div>
		</div>
	</div>
</div>
<div class="statistics-box">
	<span class="fsl log-title">图章分布</span>
	<div class="statistics-area">
		<div id="seal_distribute" class="statistics-content">
			<div class="statistics-pic"></div>
		</div>
	</div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/echarts/echarts-plain.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript">
    // @Todo: 待拯救...
    (function(){
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

        //总结得分图表的初始化
        var log_score = echarts.init(document.getElementById('log_score'));
        log_score.setOption({
            //统计模块，视图中颜色色值数组，会根据色值数组循环
            color:['#fd917b','#b7da83','#ffaa4b','#ffce53','#51a4e6','#97abeb','#e86f71','#83a3c8','#748895','#81cdb6',
                '#ff9456','#76b6fe','#e69ee3','#8ce0bf','#b0c780','#bd7f42','#7acb9e','#cfac5d','#71dbdb','#9282f1'],
            <?php if($score->getIsPersonal()): ?>
            tooltip: {
                trigger: 'axis',
                padding: '10',
                backgroundColor: 'rgba(75, 79, 84, 1)',
                formatter: function(params,ticket,callback) {
                    var res = '<span class="xwb">' +  params[0][1] + '</span>' + '<br/>';
                    for (var i = 0, l = params.length; i < l; i+=2) {
                        res +='<span class="tcm">' +  params[i][0] + '</span>' +　'<span class="fill-mm">:</span>' + parseInt(params[i][2]) + '<br/>';
                    }
                    return res;
                },
                axisPointer :{
                    type : 'line',
                    lineStyle : {
                        color: '#82939e',
                        width: 2,
                        type: 'solid'
                    }
                },
            },
            <?php else: ?>
            tooltip : {
                trigger: 'axis',
                padding:'10',
                formatter: function(params,ticket,callback) {
                    var res ='<span class="xwb">' +  params[0][1] + '</span>' + '<br/>';
                    for (var i = 0, l = params.length; i < l; i++) {
                        res +='<span class="tcm">' +  params[i][0] + '</span>' + '<span class="fill-mm">' + ':' + '</span>'  + parseInt(params[i][2]) +'<br/>';
                    }
                    return res;
                },
                axisPointer :{
                    type : 'line',
                    lineStyle : {
                        color: '#82939e',
                        width: 2,
                        type: 'solid'
                    }
                },
            },
            legend: {
                x:'right',
                y:'bottom',
                    data:[<?php echo $score->getUserName(); ?>]
            },
            <?php endif; ?>
            /*toolbox: {
                show : true,
                feature : {
                    mark : false,
                    dataView : {
                        readOnly: false,
                        lang : [' ','关闭','刷新']
                    },
                    magicType:['line', 'bar'],
                    restore : true,
                    saveAsImage : true
                }
            },*/
            calculable: true,
            xAxis: [
                {
                    type: 'category',
                    splitLine: {show: false},
                    axisTick: {show: false},
                    data: [<?php foreach ( $score->getXaxis() as $date ): ?>'<?php echo $date; ?>',<?php endforeach; ?>],
                    axisLine: {show: true,
                        lineStyle: {
                            color: '#b2c0d1',
                            width: 2,
                            type: 'solid'
                        }
                    },
                    axisLabel : {
                        show : true,
                        textStyle : {
                            color : '#82939e'
                        }
                    }
                }
            ],
            yAxis : [
                        {
                            type : 'value',       
                            splitArea : {show : false},
                            axisLine: {show: true,
                                    lineStyle:{
                                    color: '#b2c0d1',
                                    width: 2,
                                    type: 'solid'
                                }
                            },
                            splitLine : {
                                show:true,
                                lineStyle: {
                                    color: '#dadfe6',
                                    type: 'solid',
                                    width: 1
                                }
                            },
                            axisLabel : {
                                show : true,
                                textStyle : {
                                    color : '#82939e'
                                }
                            }
                        }
                    ],
            series: [
                
                    <?php foreach ($score->getSeries() as $series): ?>
                    {name: '<?php echo $series['name']; ?>',
                    type: 'bar',
    //              itemStyle: {normal: {areaStyle: {type: 'default'}}},
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
                    },<?php endforeach; ?>
                
            ]
        });

        //图章分布图表的初始化
        var seal = echarts.init(document.getElementById('seal_distribute'));
        seal.setOption({
            <?php if($stamp->getIsPersonal()): ?>
            color:['#a9d36a','#b7da83','#ffaa4b','#ffce53','#51a4e6'],
            tooltip: {
                trigger: 'axis',
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'shadow',        // 默认为直线，可选为：'line' | 'shadow'
                    areaStyle :{
                        color: 'rgba(150,150,150,0.1)'
                    }        
                },
                formatter: function(params,ticket,callback) {
                    var res = '<span class="xwb">图章分布</span>' +  '<br/>';
                    for (var i = 0, l = params.length; i < l; i+=2) {
                        res +='<span class="tcm">' +  params[0][1] + '</span>' +　'<span class="fill-mm">:</span>' + parseInt(params[i][2]) + '<br/>';
                    }
                    return res;
                },
                padding: '10',
                backgroundColor: 'rgba(75, 79, 84, 1)'
            },
            /*toolbox: {
                    show : true,
                    feature : {
                        mark : false,
                        dataView : {
                            readOnly: false,
                            lang : [' ','关闭','刷新']
                        },
                    restore : true,
                    saveAsImage : true
                }
            },*/
            calculable: true,       
            xAxis: [
                {
                    type: 'category',
                    splitLine: {show: false},
                    data: [<?php echo $stamp->getStampName(); ?>],
                    axisLine: {
                        show: true,
                        lineStyle:{
                            color: '#b2c0d1',
                            width: 2,
                            type: 'solid'
                        }
                    },
                    axisTick:'false',
                    axisLabel : {
                        show : true,
                        textStyle : {
                            color : '#82939e'
                        }
                    }
                }
            ],
            yAxis : [
                {
                    type : 'value',
                    axisLine: {show: true,
                        lineStyle:{
                            color: '#b2c0d1',
                            width: 2,
                            type: 'solid'
                        }
                    },
                    splitLine : {
                        show:true,
                        lineStyle: {
                            color: '#dadfe6',
                            type: 'solid',
                            width: 1
                        }
                    },
                    axisLabel : {
                        show : true,
                        textStyle : {
                            color : '#82939e'
                        }
                    }
                }
            ],
            series: [
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
            //统计模块，视图中颜色色值数组，会根据色值数组循环
                    color:['#fd917b','#b7da83','#ffaa4b','#ffce53','#51a4e6','#97abeb','#e86f71','#83a3c8','#748895','#81cdb6',
                           '#ff9456','#76b6fe','#e69ee3','#8ce0bf','#b0c780','#bd7f42','#7acb9e','#cfac5d','#71dbdb','#9282f1'],
                    tooltip : {
                        trigger: 'axis',
                        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                            type : 'shadow',
                            areaStyle :{
                                color: 'rgba(150,150,150,0.1)'
                            }        // 默认为直线，可选为：'line' | 'shadow'
                        },
                        formatter: function(params,ticket,callback) {
                            var res = '<span class="xwb">' +  params[0][1] + '</span>' +  '<br/>';
                            for (var i = 0, l = params.length; i < l; i+=2) {
                                res +='<span class="tcm">' +  params[i][0] + '</span>' +　'<span class="fill-mm">:</span>' + parseInt(params[i][2]) + '<br/>';
                            }
                            return res;
                        },
                        padding:'10'
                    },
                    legend: {
                        y: 'bottom',
                        x:'right',
                        itemGap : document.getElementById('seal_distribute').offsetWidth / 100,
                        data:[<?php echo $stamp->getStampName(); ?>],
                        textStyle:{
                            color : '#82939e'
                        }
                    },
                    /*toolbox: {
                        show : true,
                        feature : {
                            mark : false,
                            dataView : {
                                readOnly: false,
                                lang : [' ','关闭','刷新']
                            },
                            restore : true,
                            saveAsImage : true
                        }
                    },*/
                    xAxis : [
                        {
                            type : 'value',
                            position: 'bottom',
                            splitLine: {show: false},
                            axisLabel: {show: false},
                            axisLine: {show: true,
                                lineStyle:{
                                    color: '#b2c0d1',
                                    width: 2,
                                    type: 'solid'
                                }
                            },
                        }
                    ],
                    yAxis : [
                        {
                            type : 'category',
                            splitLine: {show: false},
                            axisLine: {show: true,
                                lineStyle:{
                                    color: '#b2c0d1',
                                    width: 2,
                                    type: 'solid'
                                }
                            },
                            axisTick: {show:false},
                            data : [<?php echo $stamp->getUserName(); ?>],
                            axisLabel : {
                                show : true,
                                textStyle : {
                                    color : '#82939e'
                                }
                            }
                        }
                    ],
                    series : [
                        <?php foreach ($stamp->getSeries() as $series): ?>
                        <?php $count = explode( ',', trim($series['count'],',')); ?>
                        {
                            name:'<?php echo $series['name']; ?>',
                            type:'bar',
                            stack: '总量',
                            itemStyle : dataStyle,
                            data:[<?php echo String::iImplode( $count); ?>]
                        },
                        {
                            name:'<?php echo $series['name']; ?>',
                            type:'bar',
                            stack: '总量',
                            itemStyle: placeHoledStyle,
                            data:[<?php foreach ( $count as $number ): ?><?php echo $stamp->getMax()-$number; ?>,<?php endforeach; ?>]
                        },
                        <?php endforeach; ?>
                    ]
            <?php endif; ?>
        });
    })();
	
</script>