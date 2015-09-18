	$(function () {
		// 输出已绑定的对应信息
        function renderResultTable(){
            var getbindingurl = '';
            $.post(getbindingurl, {_csrf:Ibos.app.g('csrftoken')}, function (res) {
                if (res.isSuccess) {
                	 $('#binding_user').html(res.bindinguser);
                    $.each(res.data, function (i, n) {
                        var oaobj = $('#oaUser').find("option[value='" + n.oaid + "']");
                        var iboscoObj = $('#iboscoUser').find("option[value='" + n.coid + "']");
                        fieldMatchUp.add(n.oaid, n.coid, oaobj.text(), iboscoObj.text());
                    });
                }
            }, 'json');
        }

        // 输出酷办公用户
        var getuserurl = '';
        $.post(getuserurl, {_csrf:Ibos.app.g('csrftoken')}, function (res) {
			if (res.isSuccess) {
				$.each(res.data, function (i, n) {
					var row = '<option value="'+n.uid+'">'+n.realname+'</option>';
					$("#oaUser").append(row);
				});
                renderResultTable();
			}
		}, 'json');

		var $oaUser = $("#oaUser"),
			$iboscoUser = $("#iboscoUser"),
			$relativeBtn = $("#relative_btn"),
			$addCount = $("#addCount"),
			oaUserValue, iboscoValue, oaUserText, iboscoText;

		var refreshRelativeBtnStatus = function () {
			oaUserValue = $oaUser.val();
			oaUserText = $oaUser.find('option:selected').text();
			iboscoValue = $iboscoUser.val();
			iboscoText = $iboscoUser.find('option:selected').text();
			$relativeBtn.prop("disabled", !(oaUserValue && iboscoValue));
		};
		$oaUser.on("change", refreshRelativeBtnStatus);
		$iboscoUser.on("change", refreshRelativeBtnStatus);


		var $mapbody = $("#result_info_table>tbody"),
			tpl = '<tr data-ibosco="<%=ibosco%>" data-oa="<%=oa%>">' +
			'<td><span><%=iboscoText%></span></td>' +
			'<td><span><%=oaText%></span></td>' +
			'<td><a href="javascript:;" title="删除" class="remove-item" data-type="removeMap" data-id="<%=id%>"><i class="o-co-trash"></i></a></td>' +
			'</tr>';
		
		var fieldMatchUp = {
			$container: $mapbody,
			$input: $("#result_map_value"),
			list: new Ibos.List($mapbody, tpl),
			refreshValue: function () {
				var val = "", data = this.list.getItemData(), rel;
				if (data.length) {
					for (var i = 0; i < data.length; i++) {
						rel = data[i].ibosco + "=" + data[i].oa;
						val += (val === "") ? rel : "," + rel;
					}
				}
				this.$input.val(val);
			},
			add: function (oa, ibosco, oaText, iboscoText, auto) {
				var id = ibosco;
				if (this.list.hasId(id)) {
					if (!auto) {
						Ui.tip('已存在', "warning");
						return;
					}
				} else {
					this.list.addItem({
						id: id,
						oa: oa,
						ibosco: ibosco,
						oaText: oaText,
						iboscoText: iboscoText
					});
					this.refreshValue();
					var addCount = +$addCount.text() + 1;
                    $addCount.text(addCount);
				}
			},
			remove: function (id) {
				this.list.removeItem(id);
				this.refreshValue();
				var addCount = +$addCount.text() - 1;
                $addCount.text(addCount);
			},
			clear: function () {
				this.list.clear();
				this.refreshValue();
			}
		};

		$mapbody.on("click", "[data-type='removeMap']", function () {
			fieldMatchUp.remove($.attr(this, "data-id"));
		});

		Ibos.evt.add({
			// 切换显示内容
			"infoDisplayToggle": function (param, elem) {
				var $this = $(this),
                    target = $this.data("target"),
                    self = $this.data("self"),
                    $target = $("#" + target),
                    $self = $("#" + self);
                $target.slideToggle();
                $self.slideToggle();
                $this.toggleClass("active");
                $("[data-self='" + target + "']").toggleClass("active");
			},
			// 匹配OA和酷办公用户名字
			"matchAction": function (param, elem) {
				// 要对比的数组
				var oauser = document.getElementById('oaUser').options;
				var iboscoUser = document.getElementById('iboscoUser').options;
				// 这两个数组记录每个对比数组的option的值
				var oaVal = [], iboscoVal = [];
				// 用以验证的数组
				var hash = {};
				for (var index in oauser) {
					var i = oauser[index].text;
					oaVal["" + i] = oauser[index].value;
					var temp = hash["" + i];
					if (!temp) {
						hash["" + i] = 2;
					} else {
						hash["" + i] = temp * 2;
					}
				}
				for (var index in iboscoUser) {
					var i = iboscoUser[index].text;
					iboscoVal["" + i] = iboscoUser[index].value;
					var temp = hash["" + i];
					if (!temp) {
						hash["" + i] = 5;
					} else {
						hash["" + i] = temp * 5;
					}
				}
				for (var i  in  hash) {
					var temp = hash["" + i];
					if (temp % 10 == 0) {
						var oaValue = oaVal["" + i];
						var iboscoValue = iboscoVal["" + i];
						if (oaValue && iboscoValue) {
							var oaobj = $('#oaUser').find("option[value='" + oaValue + "']");
							var ibsocoObj = $('#iboscoUser').find("option[value='" + iboscoValue + "']");
							fieldMatchUp.add(oaValue, iboscoValue, oaobj.text(), ibsocoObj.text(), true);
						}
					}
				}
			},
			// 建立绑定关系
			"ceratRelationship": function (param, elem) {
				fieldMatchUp.add(oaUserValue, iboscoValue, oaUserText, iboscoText);
			},
			"clearAll": function (param, elem) {
				var $items = $(".remove-item");
				$.each($items, function (index, item) {
					var id = $(item).data("id");
					fieldMatchUp.remove(id);
				});
			}
		});
	});