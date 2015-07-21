<form class="form-horizontal form-narrow fill" id="bind_form" method="post">
	<div class="control-group">
		<label class="control-label">绑定用户</label>
		<div class="controls">
			<div class="row mb">
				<div class="span6">
					<label for="oauser">OA用户</label>
					<select id="oauser" size="10">
						<?php foreach ( $ibosUsers as $user ): ?>
							<option value="<?php echo $user['uid']; ?>"><?php echo $user['realname']; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="span6">
					<label for="bqquser">企业QQ用户</label>
					<?php if ( !empty( $bqqUsers ) ): ?>
						<select id="bqquser" size="10">
							<?php foreach ( $bqqUsers as $buser ): ?>
								<option value="<?php echo $buser['open_id']; ?>"><?php echo $buser['realname']; ?></option>
							<?php endforeach; ?>
						</select>
					<?php else: ?>
						无法获取企业QQ用户列表
					<?php endif; ?>
				</div>	
			</div>
			<button type="button" class="btn" id="relative_btn">建立对应关系</button>
			<button type="button" class="btn" id="filter_same">筛选相同姓名</button>
		</div>
	</div>
	<div class="control-group">
		<label for="subflow_field_map" class="control-label">绑定关系</label>
		<div class="controls controls-content span6">
			<ul id="field_map_list"></ul>
			<input type="hidden" name="map" id="field_map_value" value="">
			<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>">
		</div>
	</div>
</form>
<script>
	var $subflowType = $("#subflow_type"), $oaUser = $("#oauser"), $bqqUser = $("#bqquser"),
			$filterBtn = $('#filter_same'), $relativeBtn = $("#relative_btn"), oaUserValue, bQQvalue, oaUserText, bQQText;

	var refreshRelativeBtnStatus = function() {
		oaUserValue = $oaUser.val();
		oaUserText = $oaUser.find('option:selected').text();
		bQQvalue = $bqqUser.val();
		bQQText = $bqqUser.find('option:selected').text();
		$relativeBtn.prop("disabled", !(oaUserValue && bQQvalue));
	};

	$oaUser.on("change", refreshRelativeBtnStatus);
	$bqqUser.on("change", refreshRelativeBtnStatus);
	var $mapList = $("#field_map_list");
	var fieldMatchUp = {
		$container: $mapList,
		$input: $("#field_map_value"),
		list: new Ibos.List($mapList, "<li data-oa=<%=oa%> data-bqq=<%=bqq%>><%=oatext%>=&gt;<%=bqqtext%><a href='javascript:;' class='close' data-type='removeMap' data-id=<%=id%>>x</a></li>"),
		refreshValue: function() {
			var val = "", data = this.list.getItemData(), rel;
			if (data.length) {
				for (var i = 0; i < data.length; i++) {
					rel = data[i].oa + "=" + data[i].bqq;
					val += (val === "") ? rel : "," + rel;
				}
			}
			this.$input.val(val);
		},
		add: function(oa, bqq, oatext, bqqtext, auto) {
			var id = bqq;
			if (this.list.hasId(id)) {
				if (!auto) {
					Ui.tip('已存在', "warning");
					return;
				}
			} else {
				this.list.addItem({
					id: id,
					oa: oa,
					bqq: bqq,
					oatext: oatext,
					bqqtext: bqqtext
				});
				this.refreshValue();
			}
		},
		remove: function(id) {
			this.list.removeItem(id);
			this.refreshValue();
		},
		clear: function() {
			this.list.clear();
			this.refreshValue();
		}
	};

	$relativeBtn.on("click", function() {
		fieldMatchUp.add(oaUserValue, bQQvalue, oaUserText, bQQText);
	});
	$mapList.on("click", "[data-type='removeMap']", function() {
		fieldMatchUp.remove($.attr(this, "data-id"));
	});
	// 对比两个数组，筛选出相同的真实姓名
	$filterBtn.on('click', function() {
		// 要对比的数组
		var oauser = document.getElementById('oauser').options;
		var bqquser = document.getElementById('bqquser').options;
		// 这两个数组记录每个对比数组的option的值
		var oaval = [], bqqval = [];
		// 用以验证的数组
		var hash = {};
		for (var index in oauser) {
			var i = oauser[index].text;
			oaval["" + i] = oauser[index].value;
			var temp = hash["" + i];
			if (!temp) {
				hash["" + i] = 2;
			} else {
				hash["" + i] = temp * 2;
			}
		}
		for (var index in bqquser) {
			var i = bqquser[index].text;
			bqqval["" + i] = bqquser[index].value;
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
				var ovalue = oaval["" + i];
				var bval = bqqval["" + i];
				if (ovalue && bval) {
					var oaobj = $('#oauser').find("option[value='" + ovalue + "']");
					var bqqobj = $('#bqquser').find("option[value='" + bval + "']");
					fieldMatchUp.add(ovalue, bval, oaobj.text(), bqqobj.text(), true);
				}
			}
		}
	});
<?php if ( !empty( $binds ) ): ?>
		var data = '<?php echo json_encode( $binds ); ?>';
		var obj = $.parseJSON(data);
		$.each(obj, function(i, n) {
			var oaobj = $('#oauser').find("option[value='" + n.uid + "']");
			var bqqobj = $('#bqquser').find("option[value='" + n.bindvalue + "']");
			fieldMatchUp.add(n.uid, n.bindvalue, oaobj.text(), bqqobj.text());
		});
<?php endif; ?>
</script>