
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Update cache']; ?></h1>
	</div>
	<div>
		<div class="ctb">
			<section class="update-item" style="display: none;">
				<div style="width: 500px; text-align: center; margin: 0 auto;">
					<p class="progress_txt pb">更新中...</p>
					<div id="progress_bar" class="progress progress-striped active" title="Progress-bar">
						<div class="progress-bar" style="width: 0%;"></div>
					</div>
				</div>
			</section>
			<section class="update-item">
				<h2 class="st"><?php echo $lang['Update cache']; ?></h2>
				<div class="alert trick-tip">
					<div class="trick-tip-title">
						<i></i>
						<strong><?php echo $lang['Skills prompt']; ?></strong>
					</div>
					<div class="trick-tip-content">
						<?php echo $lang['Update cache tip']; ?>
					</div>
				</div>
				<form class="form-horizontal" action="#">
					<div>
						<div class="control-group">
							<div class="controls" id="update_type">
								<label class="checkbox">
									<input type="checkbox" value="data" checked name="updatetype[]" /><?php echo $lang['Data cache']; ?>
								</label>
								<label class="checkbox">
									<input type="checkbox" value="static" name="updatetype[]" /><?php echo $lang['Static cache']; ?>
								</label>
								<label class="checkbox">
									<input type="checkbox" value="module" name="updatetype[]"><?php echo $lang['Module setting']; ?>
								</label>
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
								<input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
								<button type="button" data-action="submitForm" class="btn btn-primary btn-large btn-submit"><?php echo $lang['Sure']; ?></button>
							</div>
						</div>
					</div>
				</form>
			</section>
		</div>
	</div>
</div>
<script>
	var url = Ibos.app.url("dashboard/update/index"),
			$section = $(".update-item"),
			$progress_bar = $("#progress_bar > div"),
			$progress_txt = $(".progress_txt"),
			type = {
				"data": "数据缓存",
				"static": "静态文件缓存",
				"module": "模块配置文件"
			};

	Ibos.evt.add({
		submitForm: function () {
			var updatetype = U.getCheckedValue("updatetype[]").split(","),
					index = 0,
					total = 0,
					i = 0;

			if (updatetype[0] === '')
				return;
			$section.eq(0).show().siblings().hide();

			$progress_txt.text("即将更新【" + type[updatetype[index]] + "】...");

			sync(updatetype[index], 0);

			function sync(op, offset) {
				$.post(url, {
					op: op,
					offset: offset
				}, function (res) {
					var data = res.data;
					if (res.isSuccess) {
						if (data.total != 0) {
							total = data.total;
						}

						$progress_txt.text(res.msg);
						$progress_bar.css("width", Math.ceil((++i / total) * 100) + "%");

						if (data.process == "end") {
							index += 1;
							i = 0;
						}
						updatetype[index] ? sync(updatetype[index], data.offset) : (function () {
							setTimeout(function(){
								$("[name='updatetype[]']").label("uncheck");
								$section.eq(1).show().siblings().hide();
								U.setCookie((G.uid || 0) + "_update_lock", "");
								$progress_bar.css("width", "0%");
								Ui.tip("更新完成");
							}, 1000);
						})();
					}
				}, "json");
			}
		}
	});



</script>
