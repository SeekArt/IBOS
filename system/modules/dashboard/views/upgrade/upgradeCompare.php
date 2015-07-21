
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css">
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
	</div>
	<div>
		<form action="" class="form-horizontal">
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
				<div class="">
					<div class="brc mb clearfix">
						<a class="finish-step" href="javascript:;">
							<i class="o-finish-step"></i>
							<span class="ml"><?php echo $lang['Upgrade get file'] ?></span>
						</a>
						<a class="finish-step" href="javascript:;">
							<i class="o-finish-step"></i>
							<span class="ml"><?php echo $lang['Upgrade download'] ?></span>
						</a>
						<a class="active" href="javascript:;">
							<span class="circle">3</span>
							<span class="ml"><?php echo $lang['Upgrade compare'] ?></span>
						</a>
						<a href="javascript:;">
							<span class="circle">4</span>
							<span class="ml xcg"><?php echo $lang['Upgradeing'] ?></span>
						</a>
						<a href="javascript:;">
							<span class="circle">5</span>
							<span class="ml xcg"><?php echo $lang['Upgrade complete'] ?></span>
						</a>
					</div>
					<div class="alert trick-tip">
						<div class="trick-tip-title">
							<i></i>
							<strong><?php echo $lang['Tips']; ?></strong>
						</div>
						<div class="trick-tip-content">
							<?php echo $data['msg']; ?>
						</div>
					</div>
					<table class="table table-bordered table-striped table-hover mbl">
						<thead>
							<tr>
								<th><?php echo $lang['Upgrade file']; ?></th>
								<th width="100"><?php echo $lang['Comepare result']; ?></th>
							</tr>
						</thead>
						<tbody>
							<!--新增文件-->
							<?php if ( isset( $data['list']['newfile'] ) ): ?>
								<?php foreach ( $data['list']['newfile'] as $newfile ): ?>
									<tr>
										<td><?php echo $newfile; ?></td>
										<td>
											<i class="o-new-add"></i>
											<span class="mlm"><?php echo $lang['New add']; ?></span>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
							<!--差异文件-->
							<?php if ( isset( $data['list']['diff'] ) ): ?>
								<?php foreach ( $data['list']['diff'] as $diff ): ?>
									<tr>
										<td>
											<span class="difference-tip"><?php echo $diff; ?></span>
										</td>
										<td>
											<i class="o-file-difference"></i>
											<span class="mlm"><?php echo $lang['Diff']; ?></span>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
					<button type="button" class="btn btn-large btn-primary" id="upgradeStart" data-url="<?php echo $data['url'] ?>"><?php echo $lang['Upgrade sure']; ?></button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	(function() {
		$('#upgradeStart').on('click', function() {
			window.location.href = $(this).attr('data-url');
		});
	})();
</script>