/**
 * 用户--个人中心--首页
 * @author 		inaki
 * @version 	$Id$
 */

$(function() {
	var contacts = document.getElementById('contacts');
	var seriesData = Ibos.app.g("seriesData");
	// 如果有人脉信息，则生成人脉图
	if (seriesData) {
		Ibos.statics.load(Ibos.app.getStaticUrl("/js/lib/echarts/echarts-plain.js"))
			.done(function() {
				echarts.init(contacts).setOption($.extend(true, {
					tooltip: {
						trigger: 'item',
						formatter: '{a} : {b}'
					},
					legend: {
						orient: 'vertical',
						x: 'right',
						y: 'bottom',
						data: [Ibos.l("USER.IMMEDIATE_LEADER"), Ibos.l("USER.COLLEAGUE")]
					},
					series: [{
						type: 'force',
						categories: [{
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
						}],
						itemStyle: {
							normal: {
								label: {
									show: true,
									textStyle: {
										color: '#FFFFFF'
									}
								},
								nodeStyle: {
									brushType: 'both',
									strokeColor: 'rgba(130, 147, 158, 0.4)',
									lineWidth: 10
								},
								linkStyle: {
									strokeColor: '#B2C0D1'
								}
							},
							emphasis: {
								label: {
									show: false
								},
								nodeStyle: {
									r: 30,
									strokeColor: 'rgba(0, 0, 0, .1)'
								}
							}
						},
						minRadius: 20,
						maxRadius: 30,
						density: 0.05,
						attractiveness: 1.2
					}]
				}, {
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
					desc;
				if (data.value < 60) {
					progress.setStyle("danger");
					style = "xcr";
					desc = Ibos.l("USER.SECURITY_LEVEL_1");
				} else if (data.value < 100) {
					progress.setStyle("warning");
					style = "xco";
					desc = Ibos.l("USER.SECURITY_LEVEL_2");
				} else if (data.value === 100) {
					progress.setStyle("success");
					style = "xcgn";
					desc = Ibos.l("USER.SECURITY_LEVEL_3");
				}
				$securityPoint.html(data.value).parent().removeClass(styles).addClass(style);
				$securityDesc.html(desc).removeClass(styles).addClass(style);
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

			$.get(Ibos.app.url("user/home/checkSecurityRating", {
				"uid": Ibos.app.g("uid")
			}), function(res) {
				if (res.IsSuccess) {
					progress.setValue(res.rating)
				} else {
					Ui.tip("@OPERATION_FAILED", 'danger');
				}
				$that.button('reset');
			}, 'json');
		});
	}

});