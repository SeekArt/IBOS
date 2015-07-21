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
				<div class="main-top posr">
					<i class="o-top-bg"></i>
					<div class="version-info"><?php echo IBOS_VERSION_FULL; ?></div>
                </div>
				<form action="index.php?op=installing&init=1" method="post" id="user_form" class="form-horizontal form-narrow">
					<div class="specific-content">
						<div class="">
                            <table class="table table-module">
                                <tbody>
                                    <tr>
                                        <th><?php echo $lang['Sys module']; ?></th>
                                        <td>
											<?php foreach ( $coreModulesParams as $coreModuleName => $coreModuleParam ): ?>
												<label class="checkbox dib ml">
	                                                <input type="checkbox" name="coreModules[]" value="<?php echo $coreModuleName; ?>" checked disabled />
	                                                <span><?php echo $coreModuleParam['name']; ?></span>
	                                            </label>
											<?php endforeach; ?>
                                        </td>
                                    </tr>
                                    <tr>
										<th><?php echo $lang['Fun module']; ?></th>
                                        <td style="padding-right: 20px;">
											<?php foreach ( $customModulesParams as $custommModuleName => $custommModuleParam ): ?>
	                                            <label class="checkbox dib mbg ml">
	                                                <input type="checkbox" name="customModules[]" value="<?php echo $custommModuleName; ?>" checked />
	                                                <span><?php echo $custommModuleParam['name']; ?></span>
	                                            </label>
											<?php endforeach; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
						</div>
						<div class="content-foot clearfix nbt">
							<div class="pull-left ml">
								<a href="javascript:history.back();" class="btn btn-large"><?php echo $lang['Previous']; ?></a>
							</div>
							<div class="pull-right">
								<input type="submit" name="submitInstallModule" class="btn btn-large btn-primary" value="<?php echo $lang['Install now']; ?>" />
							</div>
						</div>
				</form>
			</div>
		</div>
        </div>
        <script src="<?php echo IBOS_STATIC; ?>js/src/core.js"></script>
        <script src="<?php echo IBOS_STATIC; ?>js/src/base.js"></script>
        <script src="<?php echo IBOS_STATIC; ?>js/src/common.js"></script>
    </body>
</html>