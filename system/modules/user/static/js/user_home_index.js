/**
 * 用户--个人中心--首页
 * @author 		inaki
 * @version 	$Id$
 */
var HomeIndex = {
	op : {
		/**
		 * 重新检测
		 * [recheck
		 * @param  {Object} param 传入JSON格式数据
		 * @return {Object}       返回deffered对象
		 */
		recheck : function(param){
			var url = Ibos.app.url("user/home/checkSecurityRating");
			param = $.extend({}, param, {"uid": Ibos.app.g("uid") });
			return $.get(url, param, $.noop, 'json');
		}
	}
};
$(function() {
	var contacts = document.getElementById('contacts');
	var seriesData = Ibos.app.g("seriesData");
	// 如果有人脉信息，则生成人脉图
	if (seriesData) {
		var nodes = seriesData[0].nodes,
			type = {
				"#82939E": Ibos.l("USER.ONESELF"),
				"#3497DB": Ibos.l("USER.IMMEDIATE_LEADER"),
				"#91CE31": Ibos.l("USER.COLLEAGUE")
			},
			categories = [
				{
					name: Ibos.l("USER.ONESELF"),
					itemStyle: {
						normal: {
							color: '#82939E'
						}
					}
				}, {
					name: Ibos.l("USER.IMMEDIATE_LEADER"),
					itemStyle: {
						normal: {
							color: '#3497DB'
						}
					}
				}, {
					name: Ibos.l("USER.COLLEAGUE"),
					itemStyle: {
						normal: {
							color: '#91CE31'
						}
					}
				}
			],
			options = {
				tooltip: {
					trigger: 'item',
					formatter: function(params){
						var data = params.data;
						if( data === undefined ){
							return type[params.color] + " : " + params.name;
						}
						var source = data.source,
							target = data.target;
						return nodes[source].name + " - " + nodes[target].name;
					}
				},
				legend: {
					orient: 'vertical',
					x: 'right',
					y: 'bottom',
					data: [
						{
							name: Ibos.l("USER.IMMEDIATE_LEADER"),
							icon: "circle"
						}, 
						{
							name: Ibos.l("USER.COLLEAGUE"),
							icon: "circle"
						}
					]	
				},
				series: [
					{
						type: 'graph',
						layout: 'force',
						animation: true,
						categories: categories,
						label: {
							normal:{
								show: true,
								formatter: "{b}",
								textStyle: {
									color: '#FFFFFF'
								}
							}
						},
						itemStyle: {
							normal: {
								borderWidth: 10,
								borderColor: 'rgba(130, 147, 158, 0.4)',
							},
							emphasis: {
								borderColor: 'rgba(0, 0, 0, .1)'
							}
						},
						lineStyle: {
							normal: {
								color: '#B2C0D1',
								width: 1
							}
						},
						force: {
							repulsion: 1000
						}
					}
				]
			};
		Ibos.statics.load(Ibos.app.getStaticUrl("/js/lib/echarts/echarts.min.js"))
			.done(function() {
				echarts.init(contacts).setOption($.extend(true, options, {
					series: seriesData
				}));
			});
		// 否则显示 “暂无信息”
	} else {
		$(contacts).html('<div class="no-data-tip"></div>');
	}

	// 当进入自己的主页时， 自动检测安全状况
	if (Ibos.app.g("uid") == Ibos.app.g("currentUid")) {
		var $securityProgress = $("#security_progress"),
			$securityPoint = $("#security_point"),
			$securityDesc = $("#security_point_desc"),
			$recheck = $("#recheck"),
			progress = new Progress($securityProgress);

		$securityProgress.on({
			"rolling": function(evt, data) {
				var styles = "xsr xco xcgn",
					style,
					className,
					level;

				if (data.value < 60) {
					style = "danger";
					className = "xcr";
					level = 1;
				} else if (data.value < 100) {
					style = "warning";
					className = "xco";
					level = 2;
				} else if (data.value === 100) {
					style = "success";
					className = "xcgn";
					level = 3;
				}
				progress.setStyle(style);

				$securityPoint.html(data.value).parent().removeClass(styles).addClass(className);
				$securityDesc.html( Ibos.l("USER.SECURITY_LEVEL_"+ level) ).removeClass(styles).addClass(className);
			},
			"rollstart": function() {
				$securityDesc.css("visibility", "hidden");
			},
			"rollend": function() {
				$securityDesc.css("visibility", "");
			}
		});

		progress.setValue(Ibos.app.g("securityRating"));

		$recheck.on('click', function() {
			var $that = $(this);
			$that.button('loading');

			HomeIndex.op.recheck(null).done( function(res) {
				if (res.IsSuccess) {
					progress.setValue(res.rating);
				} else {
					Ui.tip("@OPERATION_FAILED", 'danger');
				}
				$that.button('reset');
			});
		});
	}
});
