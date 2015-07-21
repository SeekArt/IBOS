<div class="statistics-box">
	<span class="fsl log-title">招聘过程</span>
	<div class="statistics-area">
		<div id="tendency_pic" class="statistics-content">
			<div class="statistics-pic"></div>
		</div>
	</div>
</div>
<div class="statistics-box clearfix">
	<div class="half-statistics-box">
		<span class="fsl log-title">性别比例</span>
		<div class="half-statistics-area">
			<div id="sex_ratio" class="half-statistics-content">
				<div class="half-statistics-pic"></div>
			</div>
		</div>
	</div>
	<div class="half-statistics-box">
		<span class="fsl log-title">年龄结构</span>
		<div class="half-statistics-area">
			<div id="age_structure" class="half-statistics-content">
				<div class="half-statistics-pic"></div>
			</div>
		</div>
	</div>
</div>
<div class="statistics-box clearfix">
	<div class="half-statistics-box">
		<span class="fsl log-title">学历分布</span>
		<div class="half-statistics-area">
			<div id="education_distribute" class="half-statistics-content">
				<div class="half-statistics-pic"></div>
			</div>
		</div>
	</div>
	<div class="half-statistics-box">
		<span class="fsl log-title">工作年限</span>
		<div class="half-statistics-area">
			<div id="entry_time" class="half-statistics-content">
				<div class="half-statistics-pic"></div>
			</div>
		</div>
	</div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/echarts/echarts-plain.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript">
	// @Todo: 待拯救......
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

		//人才流动统计图
		var tendency = echarts.init(document.getElementById('tendency_pic'));
		tendency.setOption({
			color: ['#ffaa49', '#fd917b', '#4fa4e6', '#a9d36a', '#83a3c8'],
			tooltip: {
				trigger: 'axis',
				padding: '10',
				backgroundColor: 'rgba(75, 79, 84, 1)',
				formatter: function(params, ticket, callback) {
					var res = params[0][1] + '<br/>';
					for (var i = 0, l = params.length; i < l; i++) {
						res += '<span class="tcm">' + params[i][0] + '</span>' + 　'<span class="fill-mm">:</span>' + parseInt(params[i][2]) + '<br/>';
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
			/*toolbox: {
				show: true,
				feature: {
					mark: false,
					dataView: {
						readOnly: false,
						lang: [' ', '关闭', '刷新']
					},
					magicType: ['line', 'bar'],
					restore: true,
					saveAsImage: true
				}
			},*/
			calculable: true,
			xAxis: [
				{
					type: 'category',
					boundaryGap: false,
					splitLine: {show: false},
					axisTick: {show: true},
					data: [<?php foreach ( $talentFlow->getXaxis() as $date ): ?>'<?php echo $date; ?>',<?php endforeach; ?>],
	//				data: ['2014-11-12', '2014-11-13', '2014-11-14', '2014-11-15', '2014-11-16', '2014-11-17', '2014-11-18'],
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
			series: [
			<?php foreach ($talentFlow->getSeries() as $series): ?>
				{
					name: '<?php echo $series['name']; ?>',
					type: 'line',
					symbol: 'circle',
					itemStyle: {
						normal: {
							lineStyle: {// 系列级个性化折线样式
								width: 2,
							}
						},
						emphasis: {
							lineStyle: {// 系列级个性化折线样式
								width: 4,
							}
						}
					},
					data: [<?php echo implode(',', $series['list']); ?>],
				},
			<?php endforeach; ?>
			]
		});

		//性别比例统计图
		var sex = echarts.init(document.getElementById('sex_ratio'));
		sex.setOption({
			color: ['#51a4e6', '#fd917b'],
			tooltip: {
				trigger: 'item',
				formatter: "{a} <br/><span class='tcm'>{b}</span> : {c} (<span class='fill-mm'>{d}%</span>)",
				padding: '10',
				backgroundColor: 'rgba(75, 79, 84, 1)'
			},
			legend: {
				orient: 'vertical',
				x: 'right',
				y: 'bottom',
				data: ['男', '女'],
				textStyle: {
					color: '#82939e'
				}
			},
			/*toolbox: {
				show: true,
				feature: {
					mark: false,
					dataView: {
						readOnly: false,
						lang: [' ', '关闭', '刷新']
					},
					restore: true,
					saveAsImage: true
				}
			},*/
			calculable: true,
			series: [
				{
					name: '性别比例',
					type: 'pie',
					radius: '55%',
					center: ['50%', '50%'],
					itemStyle: {
						normal: {
							label: {
								show: false
							},
							labelLine: {
								show: false
							}
						}
					},
					data: [
						<?php foreach ($sexRatio->getSeries() as $series): ?>
							{value: <?php echo $series['count']; ?>, name: "<?php echo $series['sex']; ?>"},
						<?php endforeach; ?>
					]
				}
			]
		});

		//年龄结构统计图
		var age = echarts.init(document.getElementById('age_structure'));
		age.setOption({
			color: ['#81cdb6', '#b0c780', '#ff9458', '#e86f71', '#748895'],
			tooltip: {
				trigger: 'item',
				formatter: "{a} <br/><span class='tcm'>{b}</span> : {c} (<span class='fill-mm'>{d}%</span>)",
				padding: '10',
				backgroundColor: 'rgba(75, 79, 84, 1)'
			},
			legend: {
				orient: 'vertical',
				x: 'right',
				y: 'bottom',
				data: ['23岁以下', '24-26岁', '27-30岁', '31-40岁', '41岁以上'],
				textStyle: {
					color: '#82939e'
				}
			},
			/*toolbox: {
				show: true,
				feature: {
					mark: false,
					dataView: {
						readOnly: false,
						lang: ['数据视图', '关闭', '刷新']
					},
					restore: true,
					saveAsImage: true
				}
			},*/
			calculable: true,
			series: [
				{
					name: '年龄结构',
					type: 'pie',
					radius: '55%',
					center: ['46%', '50%'],
					itemStyle: {
						normal: {
							label: {
								show: false
							},
							labelLine: {
								show: false
							}
						}
					},
					data: [
						<?php foreach($age->getSeries() as $series): ?>
							{value: <?php echo $series['count']; ?>, name: "<?php echo $series['name']; ?>"},
						<?php endforeach; ?>
					]
				}
			]
		});

		//学历分布统计图
		var education = echarts.init(document.getElementById('education_distribute'));
		education.setOption({
			color: ['#748895', '#97abeb', '#76b6fe', '#a9d36a', '#ffd365', '#ffaa49', '#e86f71'],
			tooltip: {
				trigger: 'item',
				formatter: "{a} <br/><span class='tcm'>{b}</span> : {c} (<span class='fill-mm'>{d}%</span>)",
				padding: '10',
				backgroundColor: 'rgba(75, 79, 84, 1)'
			},
			legend: {
				orient: 'vertical',
				x: 'right',
				y: 'bottom',
				data: ['初中', '高中', '中专', '大专', '本科', '硕士', '博士'],
				textStyle: {
					color: '#82939e'
				}
			},
			/*toolbox: {
				show: true,
				feature: {
					mark: false,
					dataView: {
						readOnly: false,
						lang: [' ', '关闭', '刷新']
					},
					restore: true,
					saveAsImage: true
				}
			},*/
			calculable: true,
			series: [
				{
					name: '学历分布',
					type: 'pie',
					radius: '55%',
					center: ['50%', '50%'],
					itemStyle: {
						normal: {
							label: {
								show: false
							},
							labelLine: {
								show: false
							}
						}
					},
					data: [
						<?php foreach($degree->getSeries() as $series): ?>
							{value: <?php echo $series['count']; ?>, name: "<?php echo $series['name']; ?>"},
						<?php endforeach; ?>
					]
				}
			]
		});

		//工作年限统计图   
		var entry = echarts.init(document.getElementById('entry_time'));
		entry.setOption({
			color: ['#748895', '#97abeb', '#76b6fe', '#a9d36a', '#ffd365', '#ffaa49', '#e86f71'],
			tooltip: {
				trigger: 'item',
				formatter: "{a} <br/><span class='tcm'>{b}</span> : {c} (<span class='fill-mm'>{d}%</span>)",
				padding: '10',
				backgroundColor: 'rgba(75, 79, 84, 1)'
			},
			legend: {
				orient: 'vertical',
				x: 'right',
				y: 'bottom',
				data: ['应届生', '一年以上', '两年以上', '三年以上', '五年以上', '十年以上'],
				textStyle: {
					color: '#82939e'
				}
			},
			/*toolbox: {
				show: true,
				feature: {
					mark: false,
					dataView: {
						readOnly: false,
						lang: [' ', '关闭', '刷新']
					},
					restore: true,
					saveAsImage: true
				}
			},*/
			calculable: true,
			series: [
				{
					name: '学历分布',
					type: 'pie',
					radius: '55%',
					center: ['46%', '50%'],
					itemStyle: {
						normal: {
							label: {
								show: false
							},
							labelLine: {
								show: false
							}
						}
					},
					data: [
						<?php foreach($workYears->getSeries() as $series): ?>
							{value: <?php echo $series['count']; ?>, name: "<?php echo $series['name']; ?>"},
						<?php endforeach; ?>
					]
				}
			]
		});
	})();


</script>