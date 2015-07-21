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
                <div class="specific-content">
                	<div class="mlg nht">
                    	<div class="dib vam">
                        	<i class="o-install-failure"></i>
                        </div>
                        <div class="dib vam">
                        	<p class="mb"><i class="o-failure-tip"></i></p>
                            <span class="dib mb"><?php echo $lang['Install failed message']; ?></span>
                            <div class="failure-info scroll">
                                <ul class="failure-info-list">
                                    <li>
                                        <i class="o-not-pass"></i>
                                        <span class="mlm"><?php echo $errorMsg; ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="content-foot clearfix">
                        <a href="javascript:history.back();" class="btn btn-large btn-primary pull-right"><?php echo $lang['Return']; ?></a>
                    </div>
                </div>
            </div>
        </div>
        <script src="<?php echo IBOS_STATIC; ?>js/src/core.js"></script>
        <script src="<?php echo IBOS_STATIC; ?>js/src/base.js"></script>
        <script src="<?php echo IBOS_STATIC; ?>js/src/common.js"></script>
    </body>
</html>