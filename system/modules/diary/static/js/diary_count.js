$(function(){
    var getData = Ibos.app.g,
        isPersonal = getData("isPersonal"),
        color = ['#fd917b','#b7da83','#ffaa4b','#ffce53','#51a4e6','#97abeb','#e86f71','#83a3c8','#748895','#81cdb6',
            '#ff9456','#76b6fe','#e69ee3','#8ce0bf','#b0c780','#bd7f42','#7acb9e','#cfac5d','#71dbdb','#9282f1'];

    var time = echarts.init(document.getElementById('hand_in_time')),
        timeData = getData("time");
    var time_options = {
        color: color,
        tooltip: {// Option config. Can be overwrited by series or data
            trigger: 'axis',
            formatter: function(params,ticket,callback) {
                var res = '<span class="xwb">' +  params[params.length - 1].name + '</span>' +  '<br/>';
                for (var i = 0, l = params.length; i < l; i++) {
                    if(params[i].value === 0){
                       res +='<span class="tcm">' +  params[i].seriesName + '</span>' +　'<span class="fill-mm">:</span>' + '<span>迟提交</span>' +  '<br/>';
                    }else if(params[i].value == '-'){
                        res +='<span class="tcm">' +  params[i].seriesName + '</span>' +　'<span class="fill-mm">:</span>' + '<span>未提交</span>' +  '<br/>';
                    }else{
                        if(parseInt((params[i].value - parseInt(params[i].value))*100) >=10){
                            res +='<span class="tcm">' +  params[i].seriesName + '</span>' +　'<span class="fill-mm">:</span>' +
                                parseInt(params[i].value) + ':' + parseInt((params[i].value - parseInt(params[i].value))*100) +  '<br/>';
                        }else{
                            res +='<span class="tcm">' +  params[i].seriesName + '</span>' +　'<span class="fill-mm">:</span>' +
                                parseInt(params[i].value) + ':0' + parseInt((params[i].value - parseInt(params[i].value))*100) +  '<br/>';
                        }
                    }
                }
                return res;
            },
            //设置鼠标悬停在列表时，提示线的样式
            axisPointer :{
                type : 'line',
                lineStyle : {
                    color: '#82939e',
                    width: 2,
                    type: 'solid'
                }
            },
            padding: 10,
            backgroundColor: 'rgba(75, 79, 84, 1)'
        },
        calculable: true,
        xAxis: [
            {
                type: 'category',
                boundaryGap: false,
                splitLine: {show: false},
                data: timeData.xAxis,
                axisLine: {
                    show: true,
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
                scale:true,
                max:'24',
                min:'16',
                splitNumber:'8',
                splitArea : {show : false},
                axisLine: {
                    show: true,
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
                    formatter: '{value} : 00',
                    show : true,
                    textStyle : {
                        color : '#82939e'
                    }
                }
            }
        ],
        series: timeData.series
    };
    if( !isPersonal ){
        $.extend( log_options, {
            legend: {
                y: 'bottom',
                x:'right',
                data: timeData.userName,
                textStyle:{
                    color : '#82939e'
                }
            }
        });   
    }
    time.setOption(time_options);


    //日志得分图表的初始化
    var log_score = echarts.init(document.getElementById('log_score')),
        score = getData("score");
    var log_options = {
        //统计模块，视图中颜色色值数组，会根据色值数组循环
        color: color,
        tooltip: {
            trigger: 'axis',
            padding: 10,
            formatter: function(params,ticket,callback) {
                var res = '<span class="xwb">' +  params[0].name + '</span>' + '<br/>';
                for (var i = 0, l = params.length; i < l; ) {
                    var value = isNaN(parseInt(params[i].value)) ? "暂无" : parseInt(params[i].value);
                    res +='<span class="tcm">' +  params[i].seriesName + '</span>' +　'<span class="fill-mm">:</span>' + value + '<br/>';
                    
                    i = isPersonal ? i+2 : i+1;
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
        calculable: true,
        xAxis: {
            type: 'category',
            splitLine: {show: false},
            axisTick: {show: false},
            data: score.xAxis,
            axisLine: {
                show: true,
                lineStyle: {
                    color: '#b2c0d1',
                    width: 2,
                    type: 'solid'
                }
            },
            axisLabel: {
                show : true,
                textStyle : {
                    color : '#82939e'
                }
            }
        },
        yAxis: {
            type : 'value',       
            splitArea : { show : false },
            axisLine: {
                show: true,
                lineStyle: {
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
            },
            max: 5
        },
        series: score.series
    };
    if( !isPersonal ){
        $.extend( log_options, {
            legend: {
                x:'right',
                y:'bottom',
                    data: score.userName
            }
        });
    }
    log_score.setOption(log_options);


    //图章分布图表的初始化
    var seal = echarts.init(document.getElementById('seal_distribute')),
        sealData = getData("seal");
    var seal_options = {
        //统计模块，视图中颜色色值数组，会根据色值数组循环
        color: color,
        tooltip : {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow',
                areaStyle :{
                    color: 'rgba(150,150,150,0.1)'
                }        // 默认为直线，可选为：'line' | 'shadow'
            },
            formatter: function(params,ticket,callback) {
                var res = '<span class="xwb">图章分布</span>' +  '<br/>';
                for (var i = 0, l = params.length; i < l; i+=2) {
                    var name = isPersonal ? params[0].name : params[i].seriesName;
                    res +='<span class="tcm">' +  name + '</span>' +　'<span class="fill-mm">:</span>' + parseInt(params[i].value) + '<br/>';
                }
                return res;
            },
            padding: 10,
            backgroundColor: 'rgba(75, 79, 84, 1)'
        }   
    };

    if( isPersonal ){
        $.extend(seal_options, {
            calculable: true,       
            xAxis: {
                type: 'category',
                splitLine: {show: false},
                data: sealData.stampName,
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
            },
            yAxis: {
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
                },
                max: 5
            },
            series: sealData.series
        });
    }else{
        $.extend(seal_options, {
            legend: {
                y: 'bottom',
                x:'right',
                itemGap : document.getElementById('seal_distribute').offsetWidth / 100,
                data: sealData.stampName,
                textStyle:{
                    color : '#82939e'
                }
            },
            xAxis: {
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
            },
            yAxis: {
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
                data : sealData.userName,
                axisLabel : {
                    show : true,
                    textStyle : {
                        color : '#82939e'
                    }
                },
                max: 5
            },
            series: sealData.series
        });
    }

    seal.setOption(seal_options);
});