<!DOCTYPE HTML>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $lang['Install guide']; ?></title>
        <meta name="keywords" content="IBOS" />
        <meta name="generator" content="IBOS 2.1 (Revolution!)" />
        <meta name="author" content="IBOS Team" />
        <meta name="coryright" content="2013 IBOS Inc." />
        <link href="<?php echo IBOS_STATIC; ?>css/base.css" type="text/css" rel="stylesheet" />
        <link href="<?php echo IBOS_STATIC; ?>css/common.css" type="text/css" rel="stylesheet" />
        <link href="static/installation_guide.css" type="text/css" rel="stylesheet" />
        <!-- IE8 fixed -->
        <!--[if lt IE 9]>
            <link rel="stylesheet" href="<?php echo IBOS_STATIC; ?>/css/iefix.css">
        <![endif]-->
    </head>
    <body>
        <div class="main">
            <div class="main-content">
				<div class="main-top">
					<i class="o-top-bg"></i>
                </div>
                <div class="specific-content">
					<div class="fill-nn">
						<div class="mb ovh posr">
							<span class="ic-divider"></span>
							<span class="check-project-title prs">
								<?php if ( $envCheck['envCheckRes'] ): ?>
									<i class="o-normal-tip"></i>
									<span class="mlm"><?php echo $lang['Env check']; ?></span>
								<?php else: ?>
									<i class="o-warm-tip"></i>
									<span class="mlm warn"><?php echo $lang['Env check'] . $lang['Failed to pass']; ?></span>
								<?php endif; ?>
							</span>
							<a href="javascript:;" class="pull-right showmore">
								<span><?php echo $lang['Pack up']; ?></span>
								<i class="o-pack-up"></i>
							</a>
						</div>
						<!--环境检测-->
						<div class="environment-check">
							<table class="table table-condensed table-check">
								<tbody>
									<tr>
										<td><?php echo $lang['Env test']; ?></td>
										<td><?php echo $lang['Icenter required']; ?></td>
										<td><?php echo $lang['Recommended']; ?></td>
										<td><?php echo $lang['Curr server']; ?></td>
										<td width="20"></td>
									</tr>
									<?php foreach ( $envCheck['envItems'] as $item => $val ): ?>
										<tr>
											<td><?php echo lang( $item ); ?></td>
											<td><?php echo lang( $val['r'] ); ?></td>
											<td><?php echo lang( $val['b'] ); ?></td>
											<td><?php echo lang( $val['current'] ); ?></td>
											<td>
												<i class="<?php if ( $val['status'] ): ?>o-normal-pass<?php else: ?>o-not-pass<?php endif; ?>"></i>
											</td>
										<?php endforeach; ?>
								</tbody>
							</table>
						</div>
						<!--文件、目录权限检查-->
						<div class="mb ovh posr">
							<span class="ic-divider"></span>
							<span class="check-flie-title prs">
								<?php if ( $dirfileCheck['dirfileCheckRes'] ): ?>
									<i class="o-normal-tip"></i>
									<span class="mlm"><?php echo $lang['Priv check']; ?></span>
								<?php else: ?>
									<i class="o-warm-tip"></i>
									<span class="mlm warn"><?php echo $lang['Priv check'] . $lang['Failed to pass']; ?></span>
								<?php endif; ?>
							</span>
							<a href="javascript:;" class="pull-right showmore">
								<span><?php echo $lang['Open up']; ?></span>
								<i class="o-pack-down"></i>
							</a>
						</div>
						<div class="file-check">
							<table class="table table-condensed table-file">
								<tbody>
									<tr>
										<td><?php echo $lang['Directory file']; ?></td>
										<td><?php echo $lang['Required state']; ?></td>
										<td><?php echo $lang['Current status']; ?></td>
										<td width="20"></td>
									</tr>
									<?php foreach ( $dirfileCheck['dirfileItems'] as $item => $val ): ?>
										<tr>
											<td><?php echo $val['path']; ?></td>
											<td><?php echo $lang['Writeable']; ?></td>
											<td><?php echo $val['msg']; ?></td>
											<td>
												<i class="<?php if ( $val['status'] ): ?>o-normal-pass<?php else: ?>o-not-pass<?php endif; ?>"></i>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
						<!--函数依赖性检查-->
						<div class="mb ovh posr">
							<span class="ic-divider"></span>
							<span class="check-flie-title prs">
								<?php if ( $funcCheck['funcCheckRes'] && $filesorkCheck['filesorkCheckRes'] && $extLoadedCheck['extLoadedCheckRes'] ): ?>
									<i class="o-normal-tip"></i>
									<span class="mlm"><?php echo $lang['Func depend']; ?></span>
								<?php else: ?>
									<i class="o-warm-tip"></i>
									<span class="mlm warn"><?php echo $lang['Func depend'] . $lang['Failed to pass']; ?></span>
								<?php endif; ?>
							</span>
							<a href="javascript:;" class="pull-right showmore">
								<span><?php echo $lang['Open up']; ?></span>
								<i class="o-pack-down"></i>
							</a>
						</div>
						<div class="function-check">
							<table class="table table-condensed table-function">
								<tbody>
									<tr>
										<td><?php echo $lang['Func name']; ?></td>
										<td><?php echo $lang['Check result']; ?></td>
										<td><?php echo $lang['Suggestion']; ?></td>
										<td width="20"></td>
									</tr>
									<?php foreach ( $funcCheck['funcItems'] as $item => $val ): ?>
										<tr>
											<td><?php echo $item; ?></td>
											<td>
												<?php if ( $val['status'] ) {
													echo $lang['Supportted'];
												} else {
													echo $lang['Unsupportted'];
												} ?>
											</td>
											<td><?php echo $val['advice']; ?></td>
											<td><i class="<?php if ( $val['status'] ): ?>o-normal-pass<?php else: ?>o-not-pass<?php endif; ?>"></i></td>
										</tr>
									<?php endforeach; ?>
									<?php if ( !$filesorkCheck['filesorkCheckRes'] ): ?>
										<?php foreach ( $filesorkCheck['filesockItems'] as $item => $val ): ?>
											<tr>
												<td><?php echo $item; ?></td>
												<td>
													<?php if ( $val['status'] ) {
														echo $lang['Supportted'];
													} else {
														echo $lang['Unsupportted'];
													} ?>
												</td>
												<td><?php echo $val['advice']; ?></td>
												<td><i class="<?php if ( $val['status'] ): ?>o-normal-pass<?php else: ?>o-not-pass<?php endif; ?>"></i></td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
									<?php if ( !$extLoadedCheck['extLoadedCheckRes'] ): ?>
										<?php foreach ( $extLoadedCheck['extLoadedItems'] as $item => $val ): ?>
											<tr>
												<td><?php echo $item; ?></td>
												<td>
													<?php if ( $val['status'] ) {
														echo $lang['Supportted'];
													} else {
														echo $lang['Unsupportted'];
													} ?>
												</td>
												<td width="360"><?php echo $val['advice']; ?></td>
												<td><i class="<?php if ( $val['status'] ): ?>o-normal-pass<?php else: ?>o-not-pass<?php endif; ?>"></i></td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
                    </div>
                    <div class="content-foot clearfix">
                        <a href="index.php?op=envCheck" class="btn btn-large btn-primary pull-right"><?php echo $lang['Check again']; ?></a>
                    </div>
                </div>
            </div>
        </div>
        <script src="<?php echo IBOS_STATIC; ?>js/src/core.js"></script>
        <script src="<?php echo IBOS_STATIC; ?>js/src/base.js"></script>
        <script src="<?php echo IBOS_STATIC; ?>js/src/common.js"></script>
        <script src="static/installation_check.js"></script>
    </body>
</html>