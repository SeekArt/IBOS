(function(){
	var commonOption = {
		tooltip: {
			trigger: 'item',
			formatter: "{a} <br/><span class='tcm'>{b}</span> : {c} (<span class='fill-mm'>{d}%</span>)",
			padding: 10,
			backgroundColor: 'rgba(75, 79, 84, 1)'
		},
		legend: {
			orient: 'vertical',
			x: 'right',
			y: 'bottom',
			textStyle: {
				color: '#82939e'
			}
		},
		calculable: true,
		series: [
			{
				type: 'pie',
				radius: '55%',
				itemStyle: {
					normal: {
						label: {
							show: false
						},
						labelLine: {
							show: false
						}
					}
				}
			}
		]
	},
		getData = Ibos.app.g;

	//人才流动统计图
	var tendency = echarts.init(document.getElementById('tendency_pic')),
		tendencyData = getData("tendency");
	tendency.setOption({
		color: ['#ffaa49', '#fd917b', '#4fa4e6', '#a9d36a', '#83a3c8'],
		tooltip: {
			trigger: 'axis',
			padding: 10,
			backgroundColor: 'rgba(75, 79, 84, 1)',
			formatter: function(params, ticket, callback) {
				var res = '招聘过程<br/>';
				for (var i = 0, l = params.length; i < l; i++) {
					res += '<span class="tcm">' + params[i].seriesName + '</span>' + 　'<span class="fill-mm">:</span>' + parseInt(params[i].data) + '<br/>';
				}
				return res;
			},
			axisPointer: {
				type: 'line',
				lineStyle: {
					color: '#82939e',
					width: 2,
					type: 'solid'
				}
			},
		},
		legend: {
			x: 'right',
			y: 'bottom',
			data: ['新增简历', '待安排', '面试', '录用', '淘汰'],
			textStyle: {
				color: '#82939e'
			}
		},
		calculable: true,
		xAxis: [
			{
				type: 'category',
				boundaryGap: false,
				splitLine: {show: false},
				axisTick: {show: true},
				data: tendencyData.xAxis,
				axisLine: {
					show: true,
					lineStyle: {
						color: '#b2c0d1',
						width: 2,
						type: 'solid'
					}
				},
				axisLabel: {
					show: true,
					textStyle: {
						color: '#82939e'
					}
				}
			}
		],
		yAxis: [
			{
				type: 'value',
				splitArea: {show: false},
				axisLine: {
					show: true,
					lineStyle: {
						color: '#b2c0d1',
						width: 2,
						type: 'solid'
					}
				},
				splitLine: {
					show: true,
					lineStyle: {
						color: '#dadfe6',
						type: 'solid',
						width: 1
					}
				},
				axisLabel: {
					show: true,
					textStyle: {
						color: '#82939e'
					}
				}
			}
		],
		series: tendencyData.series
	});

	//性别比例统计图
	var sex = echarts.init(document.getElementById('sex_ratio'));
	sex.setOption($.extend(true, {}, commonOption, {
		color: ['#51a4e6', '#fd917b'],
		legend: {
			data: ['男', '女'],
		},
		series: [
			{
				name: '性别比例',
				center: ['50%', '50%'],
				data: getData("sex")
			}
		]
	}));

	//年龄结构统计图
	var age = echarts.init(document.getElementById('age_structure'));
	age.setOption($.extend(true, {}, commonOption, {
		color: ['#81cdb6', '#b0c780', '#ff9458', '#e86f71', '#748895'],
		legend: {
			data: ['23岁以下', '24-26岁', '27-30岁', '31-40岁', '41岁以上']
		},
		series: [
			{
				name: '年龄结构',
				center: ['46%', '50%'],
				data: getData("age")
			}
		]
	}));

	//学历分布统计图
	var education = echarts.init(document.getElementById('education_distribute'));
	education.setOption($.extend(true, {}, commonOption, {
		color: ['#748895', '#97abeb', '#76b6fe', '#a9d36a', '#ffd365', '#ffaa49', '#e86f71'],
		legend: {
			data: ['初中', '高中', '中专', '大专', '本科', '硕士', '博士']
		},
		series: [
			{
				name: '学历分布',
				center: ['50%', '50%'],
				data: getData("education")
			}
		]
	}));

	//工作年限统计图   
	var entry = echarts.init(document.getElementById('entry_time'));
	entry.setOption($.extend(true, {}, commonOption, {
		color: ['#748895', '#97abeb', '#76b6fe', '#a9d36a', '#ffd365', '#ffaa49', '#e86f71'],
		legend: {
			data: ['应届生', '一年以上', '两年以上', '三年以上', '五年以上', '十年以上'],
		},
		series: [
			{
				name: '工作年限',
				center: ['46%', '50%'],
				data: getData("entry")
			}
		]
	}));
})();